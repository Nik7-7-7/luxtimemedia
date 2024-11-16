<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Crons\ExportBookingsCron;
use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Fields\Basic\DateRangeField;
use MotoPress\Appointment\Fields\FieldsFactory;
use MotoPress\Appointment\Handlers\SecurityHandler;
use MotoPress\Appointment\Helpers\AdminUIHelper;
use MotoPress\Appointment\Helpers\PriceCalculationHelper;
use MotoPress\Appointment\Utils\ParseUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ManageBookingsPage extends ManagePostsPage {

	private $bookingsFilterServiceDateRange = array(
		DateRangeField::VALUE_ARRAY_FROM => '',
		DateRangeField::VALUE_ARRAY_TO   => '',
	);

	private $bookingFilterServiceId  = 0;
	private $bookingFilterEmployeeId = 0;
	private $bookingFilterLocationId = 0;
	private $bookingFilterIdsIn      = [];

	public function __construct( $postType ) {

		parent::__construct( $postType );

		// add hook for process download for ExportBookingsCron::getExportFileUrl()
		add_action(
			'admin_init',
			function() {

				if ( isset( $_GET[ ExportBookingsCron::EXPORT_DOWNLOAD_URL_PARAMETER_NONCE ] ) ) {

					$nonce = sanitize_text_field( wp_unslash( $_GET[ ExportBookingsCron::EXPORT_DOWNLOAD_URL_PARAMETER_NONCE ] ) );

					$exportFileName = '';

					if ( isset( $_GET[ ExportBookingsCron::EXPORT_DOWNLOAD_URL_PARAMETER_FILE_NAME ] ) ) {

						$exportFileName = sanitize_text_field( wp_unslash( $_GET[ ExportBookingsCron::EXPORT_DOWNLOAD_URL_PARAMETER_FILE_NAME ] ) );

						if ( ! wp_verify_nonce( $nonce, $exportFileName ) ||
							! SecurityHandler::isUserCanViewOthersBookings()
						) {

							AdminUIHelper::addAdminNotice(
								AdminUIHelper::ADMIN_NOTICE_TYPE_ERROR,
								__( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-appointment' )
							);

							return;
						}

						$exportFilePath = mpapp()->getPluginUploadsPath( $exportFileName );

						$realFilePath = realpath( $exportFilePath );
						$realBasePath = realpath( mpapp()->getPluginUploadsPath() );

						if ( empty( $exportFilePath ) || ! file_exists( $exportFilePath ) ||
							// check is uploads folder in the real file path
							false === $realFilePath || 0 !== strpos( $realFilePath, $realBasePath )
						) {

							AdminUIHelper::addAdminNotice(
								AdminUIHelper::ADMIN_NOTICE_TYPE_ERROR,
								__( 'Booking export file does not exist.', 'motopress-appointment' )
							);

							return;
						}

						ignore_user_abort( true );
						nocache_headers();

						mpa_set_time_limit( 0 );

						$mime = wp_check_filetype( $exportFilePath );
						// phpcs:ignore
						$content = @file_get_contents( $exportFilePath );

						header( 'Content-Type: ' . $mime['type'] . '; charset=utf-8' );
						$exportFileNameForUser = 'bookings_' . ( new \DateTime() )->format( 'Y-m-d' ) . '.csv';
						header( 'Content-Disposition: attachment; filename=' . $exportFileNameForUser );
						header( 'Expires: 0' );

						// phpcs:ignore
						echo $content;

						// remove file after download
						// phpcs:ignore
						@unlink( $exportFilePath );

						exit();
					}
				}
			},
			0
		);
	}

	/**
	 * @since 1.18.0
	 */
	protected function addActions() {

		parent::addActions();

		// used by links on bookings count in customer's table
		$this->filterBookingsByCustomerId();

		$this->filterBookingsBySearchData();
		$this->filterBookingsByCustomFilters();

		// disable month dropdown filter
		add_filter(
			'disable_months_dropdown',
			function( $isDisableMonthDropdown, $postType ) {
				return mpapp()->postTypes()->booking()->getPostType() === $postType ? true : $isDisableMonthDropdown;
			},
			999999,
			2
		);

		// add bookings filters UI for bookings table
		add_action(
			'restrict_manage_posts',
			function( $postType, $topOrBottomBookingsTableNavBar ) {

				if ( 'top' === $topOrBottomBookingsTableNavBar &&
					mpapp()->postTypes()->booking()->getPostType() === $postType
				) {
					$this->echoBookingsTableFilters();
				}
			},
			10,
			2
		);

		add_action(
			'manage_posts_extra_tablenav',
			function ( string $topOrBottomBookingsTableNavBar ) {

				global $post_type;

				if ( 'top' === $topOrBottomBookingsTableNavBar &&
					mpapp()->postTypes()->booking()->getPostType() === $post_type &&
					// phpcs:ignore
					( ! isset( $_REQUEST['post_status'] ) || 'trash' !== $_REQUEST['post_status'] )
				) {
					$this->echoCSVExportBarAboveBookingsTable();
				}
			}
		);
	}

	protected function enqueueScripts() {

		wp_enqueue_style(
			'manage-posts',
			mpapp()->getPluginUrl( 'assets/css/manage-posts.min.css' ),
			array(),
			mpapp()->getVersion()
		);

		wp_enqueue_script(
			'manage-bookings',
			mpapp()->getPluginUrl( 'assets/js/manage-bookings.min.js' ),
			array( 'jquery' ),
			mpapp()->getVersion(),
			true
		);

		wp_localize_script(
			'manage-bookings',
			'mpaAjaxData',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'wpNonces' => \MotoPress\Appointment\Handlers\AjaxHandler::getAjaxActionWPNonces(
					array(
						\MotoPress\Appointment\Handlers\AjaxActions\ExportBookingsAction::getAjaxActionName(),
					)
				),
			)
		);
	}

	private function filterBookingsByCustomerId(): void {

		// phpcs:ignore
		if ( isset( $_GET['customer_id'] ) ) {

			// filter bookings list by customer
			// phpcs:ignore
			$customer = mpapp()->repositories()->customer()->findById( absint( $_GET['customer_id'] ) );

			if ( $customer ) {

				$filterNotice = $customer->getName() ? esc_html( $customer->getName() ) : sprintf( '#%d', $customer->getId() );

				if ( SecurityHandler::isUserCanEditCustomer() ) {

					$editUrl = mpapp()->pages()->customers()->getUrl(
						array(
							'id'     => $customer->getId(),
							'action' => 'edit',
						)
					);

					$filterNotice = sprintf(
						'<a href="%s" target="_blank">%s</a>',
						$editUrl,
						$filterNotice
					);
				}

				$filterNotice = sprintf( '%s: %s', __( 'Filtered bookings for customer', 'motopress-appointment' ), $filterNotice );

				$this->addFilterByPostMeta( '_mpa_customer_id', strval( $customer->getId() ), $filterNotice );
			}
		}
	}

	private function filterBookingsBySearchData(): void {

		// phpcs:ignore
		$search_param = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		if ( ! empty( $search_param ) ) {

			add_action(
				'pre_get_posts',
				function( \WP_Query $query ) use ( $search_param ) {

					if ( ! $query->is_main_query() || ! $this->isCurrentPageDBQuery( $query ) ) {
						return;
					}

					// remove default search query
					add_filter(
						'posts_search',
						function ( string $search, \WP_Query $query ) {

							if ( $query->is_main_query() && $this->isCurrentPageDBQuery( $query ) ) {
								$search = '';
							}

							return $search;
						},
						10,
						2
					);

					// add conditions for customer data in bookings meta
					// we use them when booking customer data is changed individually
					$meta_query = (array) $query->get( 'meta_query' );

					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => '_mpa_customer_name',
							'value'   => $search_param,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_mpa_customer_email',
							'value'   => $search_param,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_mpa_customer_phone',
							'value'   => $search_param,
							'compare' => 'LIKE',
						),
					);

					$query->set( 'meta_query', $meta_query );

					// add conditions for reqular customer data
					add_filter(
						'posts_where',
						function ( string $where, \WP_Query $query ) use ( $search_param ) {

							if ( $query->is_main_query() && $this->isCurrentPageDBQuery( $query ) ) {

								global $wpdb;

								$searchLikeParam = '%' . $wpdb->esc_like( $search_param ) . '%';

								$subquery = "(SELECT id FROM {$wpdb->prefix}mpa_customers AS c WHERE c.name LIKE '{$searchLikeParam}' OR c.email LIKE '{$searchLikeParam}' OR c.phone LIKE '{$searchLikeParam}')";
								$where   .= " OR ({$wpdb->prefix}postmeta.meta_key = '_mpa_customer_id' AND {$wpdb->prefix}postmeta.meta_value IN ({$subquery}))";
							}

							return $where;
						},
						999,
						2
					);
				}
			);
		}
	}


	private function filterBookingsByCustomFilters(): void {

		// phpcs:ignore
		if ( ! empty( $_REQUEST['mpa_service_date_range'][ DateRangeField::VALUE_ARRAY_FROM ] ) ) {

			$this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_FROM ] = sanitize_text_field(
				wp_unslash(
					// phpcs:ignore
					$_REQUEST['mpa_service_date_range'][ DateRangeField::VALUE_ARRAY_FROM ]
				)
			);
		}

		// phpcs:ignore
		if ( ! empty( $_REQUEST['mpa_service_date_range'][ DateRangeField::VALUE_ARRAY_TO ] ) ) {

			$this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ] = sanitize_text_field(
				wp_unslash(
					// phpcs:ignore
					$_REQUEST['mpa_service_date_range'][ DateRangeField::VALUE_ARRAY_TO ]
				)
			);
		}

		// phpcs:ignore
		if ( ! empty( $_REQUEST['mpa_service_id'] ) ) {
			// phpcs:ignore
			$this->bookingFilterServiceId = absint( wp_unslash( $_REQUEST[ 'mpa_service_id' ] ) );
		}

		// phpcs:ignore
		if ( ! empty( $_REQUEST['mpa_employee_id'] ) ) {
			// phpcs:ignore
			$this->bookingFilterEmployeeId = absint( wp_unslash( $_REQUEST[ 'mpa_employee_id' ] ) );
		}

		// phpcs:ignore
		if ( ! empty( $_REQUEST['mpa_location_id'] ) ) {
			// phpcs:ignore
			$this->bookingFilterLocationId = absint( wp_unslash( $_REQUEST[ 'mpa_location_id' ] ) );
		}

		if ( ! empty( $_REQUEST['mpa_booking_in'] ) ) {
			$this->bookingFilterIdsIn = ParseUtils::parseIds(
				wp_unslash(
					$_REQUEST['mpa_booking_in']
				)
			);
		}

		// customize booking query to process custom filters
		if ( ! empty( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_FROM ] )
			|| ! empty( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ] )
			|| ! empty( $this->bookingFilterServiceId )
			|| ! empty( $this->bookingFilterEmployeeId )
			|| ! empty( $this->bookingFilterLocationId )
			|| ! empty( $this->bookingFilterIdsIn )
		) {
			add_filter(
				'posts_where',
				function ( $where, $query ) {

					if ( ! $this->isCurrentPageDBQuery( $query ) ) {
						return $where;
					}

					return $where . $this->getBookingsSQLWhereClauseForCustomFilters();
				},
				10,
				2
			);
		}
	}


	private function getBookingsSQLWhereClauseForCustomFilters(): string {

		global $wpdb;

		$where = " AND {$wpdb->posts}.ID IN (
			SELECT DISTINCT posts_1.post_parent 
			FROM {$wpdb->posts} AS posts_1";

		if ( ! empty( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_FROM ] ) ) {

			$where .= $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS pm1 ON posts_1.ID = pm1.post_id AND pm1.meta_key = '_mpa_date' AND pm1.meta_value >= %s",
				$this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_FROM ]
			);

			if ( ! empty( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ] ) ) {

				$where .= $wpdb->prepare(
					' AND pm1.meta_value <= %s',
					$this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ]
				);
			}
		} elseif ( ! empty( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ] ) ) {

			$where .= $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS pm1 ON posts_1.ID = pm1.post_id AND pm1.meta_key = '_mpa_date' AND pm1.meta_value <= %s",
				$this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ]
			);
		}

		if ( ! empty( $this->bookingFilterServiceId ) ) {

			$where .= $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS pm2 ON posts_1.ID = pm2.post_id AND pm2.meta_key = '_mpa_service' AND pm2.meta_value = %s",
				$this->bookingFilterServiceId
			);
		}

		if ( ! empty( $this->bookingFilterEmployeeId ) ) {

			$where .= $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS pm3 ON posts_1.ID = pm3.post_id AND pm3.meta_key = '_mpa_employee' AND pm3.meta_value = %s",
				$this->bookingFilterEmployeeId
			);
		}

		if ( ! empty( $this->bookingFilterLocationId ) ) {

			$where .= $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS pm4 ON posts_1.ID = pm4.post_id AND pm4.meta_key = '_mpa_location' AND pm4.meta_value = %s",
				$this->bookingFilterLocationId
			);
		}

		$where .= ')';

		if ( ! empty( $this->bookingFilterIdsIn ) ) {
			$idsString = implode( ', ', $this->bookingFilterIdsIn );

			$where .= " AND {$wpdb->posts}.ID IN ({$idsString})";
		}

		return $where;
	}


	private function echoBookingsTableFilters() {

		echo( '<div class="mpa-entity-table-filter">' . esc_html__( 'Scheduled For', 'motopress-appointment' ) . ':&nbsp;' );

		// phpcs:ignore
		echo FieldsFactory::createField(
			'mpa_service_date_range',
			array(
				'type' => 'date-range',
			),
			$this->bookingsFilterServiceDateRange
		)->renderInput();

		echo '</div>';

		$allServices = mpapp()->repositories()->service()->findAll();

		if ( 1 < count( $allServices ) ) {

			$allServicesOptions = array(
				0 => esc_html__( 'All Services', 'motopress-appointment' ),
			);

			foreach ( $allServices as $service ) {

				$allServicesOptions[ $service->getId() ] = $service->getTitle();
			}

			// phpcs:ignore
			echo FieldsFactory::createField(
				'mpa_service_id',
				array(
					'type'    => 'select',
					'options' => $allServicesOptions,
					'default' => 0,
				),
				$this->bookingFilterServiceId
			)->renderInput();
		}

		$allEmployees = mpapp()->repositories()->employee()->findAll();

		if ( 1 < count( $allEmployees ) ) {

			$allEmployeesOptions = array(
				0 => esc_html__( 'All Employees', 'motopress-appointment' ),
			);

			foreach ( $allEmployees as $employee ) {

				$allEmployeesOptions[ $employee->getId() ] = $employee->getName();
			}

			// phpcs:ignore
			echo FieldsFactory::createField(
				'mpa_employee_id',
				array(
					'type'    => 'select',
					'options' => $allEmployeesOptions,
					'default' => 0,
				),
				$this->bookingFilterEmployeeId
			)->renderInput();
		}

		$allLocations = mpapp()->repositories()->location()->findAll();

		if ( 1 < count( $allLocations ) ) {

			$allLocationsOptions = array(
				0 => esc_html__( 'All Locations', 'motopress-appointment' ),
			);

			foreach ( $allLocations as $location ) {

				$allLocationsOptions[ $location->getId() ] = $location->getName();
			}

			// phpcs:ignore
			echo FieldsFactory::createField(
				'mpa_location_id',
				array(
					'type'    => 'select',
					'options' => $allLocationsOptions,
					'default' => 0,
				),
				$this->bookingFilterLocationId
			)->renderInput();
		}
	}

	private function echoCSVExportBarAboveBookingsTable() {

		// phpcs:ignore
		$viewingBookingStatus = ! empty( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : '';
		?>

		<div class="alignleft actions">
			<button class="button mpa-export-button mpa-hide" 
			<?php
			echo ( 'data-booking_status="' . esc_attr( $viewingBookingStatus ) . '"' .
			' data-service_date_from="' . esc_attr( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_FROM ] ) . '"' .
			' data-service_date_to="' . esc_attr( $this->bookingsFilterServiceDateRange[ DateRangeField::VALUE_ARRAY_TO ] ) . '"' .
			' data-service_id="' . esc_attr( $this->bookingFilterServiceId ) . '"' .
			' data-employee_id="' . esc_attr( $this->bookingFilterEmployeeId ) . '"' .
			' data-location_id="' . esc_attr( $this->bookingFilterLocationId ) . '"' );
			?>
			><?php esc_html_e( 'Export', 'motopress-appointment' ); ?></button>
			<button class="button mpa-cancel-export-button mpa-hide"><?php esc_html_e( 'Cancel Export', 'motopress-appointment' ); ?></button>
		</div>
		<progress class="mpa-export-progress-bar mpa-hide" max="100" value="0">0%</progress>

		<?php
	}

	/**
	 * @return array
	 */
	protected function customColumns() {

		$isMultibooking = mpapp()->settings()->isMultibookingEnabled();

		return array(
			'status'    => esc_html__( 'Status', 'motopress-appointment' ),
			'customer'  => esc_html__( 'Customer', 'motopress-appointment' ),
			'price'     => esc_html__( 'Price', 'motopress-appointment' ),
			'services'  => $isMultibooking ? esc_html__( 'Services', 'motopress-appointment' ) : esc_html__( 'Service', 'motopress-appointment' ),
			'employees' => $isMultibooking ? esc_html__( 'Employees', 'motopress-appointment' ) : esc_html__( 'Employee', 'motopress-appointment' ),
			'time'      => esc_html__( 'Time', 'motopress-appointment' ),
			'quantity'  => esc_html__( 'Quantity', 'motopress-appointment' ),
			'mpa_date'  => esc_html__( 'Date' ),
		);
	}

	/**
	 * @param array $columns
	 * @return array
	 */
	protected function filterColumns( $columns ) {

		$columns = parent::filterColumns( $columns );

		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * @param string $columnName
	 * @param Booking $booking
	 */
	protected function displayValue( $columnName, $booking ) {

		switch ( $columnName ) {

			case 'status':
				// phpcs:ignore
				echo '<span class="column-status-' . esc_attr( $booking->getStatus() ) . '">' . mpa_get_status_label( $booking->getStatus() ) . '</span>';
				break;

			case 'customer':
				$customerInfo = array();
				$customerId   = $booking->getCustomerId();
				$customerName = $booking->getCustomerName();

				if ( $customerId && SecurityHandler::isUserCanEditCustomer() ) {

					$editUrl        = mpapp()->pages()->customers()->getUrl(
						array(
							'id'     => $customerId,
							'action' => 'edit',
						)
					);
					$customerName   = $customerName ? esc_html( $customerName ) : sprintf( '#%d', $customerId );
					$customerInfo[] = sprintf( '<a href="%s">%s</a>', $editUrl, $customerName );

				} else {

					$customerInfo[] = $customerName;
				}

				$customerEmail = $booking->getCustomerEmail();

				if ( $customerEmail ) {

					$customerInfo[] = '<a href="mailto:' . esc_attr( $customerEmail ) . '">' . $customerEmail . '</a>';
				}

				$customerPhone = $booking->getCustomerPhone();

				if ( $customerPhone ) {

					$customerInfo[] = '<a href="tel:' . esc_attr( $customerPhone ) . '">' . $customerPhone . '</a>';
				}

				if ( ! empty( $customerInfo ) ) {
					// phpcs:ignore
					echo implode( '<br>', $customerInfo );
				} else {
					// phpcs:ignore
					echo mpa_tmpl_placeholder();
				}

				break;

			case 'price':
				// phpcs:ignore
				echo PriceCalculationHelper::formatPriceAsHTML( $booking->getTotalPrice() ) . '<br>';

				$paidLabel = sprintf(
					// Translators: %s: Paid amount.
					esc_html__( 'Paid: %s', 'motopress-appointment' ),
					PriceCalculationHelper::formatPriceAsHTML(
						$booking->getPaidPrice(),
						array(
							'literal_free' => false,
						)
					)
				);
				$paymentsUrl = mpapp()->pages()->managePayments()->getUrl( array( 'booking_id' => $booking->getId() ) );

				// phpcs:ignore
				printf( '<a href="%s">%s</a>', esc_url( $paymentsUrl ), $paidLabel );
				break;

			case 'services':
				// Pull employee names
				$services = array_map(
					function ( $reservation ) {

						$serviceName = get_the_title( $reservation->getServiceId() );
						return $serviceName ? $serviceName : mpa_tmpl_placeholder();
					},
					$booking->getReservations()
				);

				if ( ! empty( $services ) ) {
					// phpcs:ignore
					echo implode( '<br>', $services );
				} else {
					// phpcs:ignore
					echo mpa_tmpl_placeholder();
				}

				break;

			case 'employees':
				// Pull employee names
				$employees = array_map(
					function ( $reservation ) {

						$employeeName = get_the_title( $reservation->getEmployeeId() );
						return $employeeName ? $employeeName : mpa_tmpl_placeholder();
					},
					$booking->getReservations()
				);

				if ( ! empty( $employees ) ) {
					// phpcs:ignore
					echo implode( '<br>', $employees );
				} else {
					// phpcs:ignore
					echo mpa_tmpl_placeholder();
				}

				break;

			case 'time':
				// Pull dates
				$dates = array_map(
					function ( $reservation ) {

						$date = mpa_format_date( $reservation->getDate() );
						$time = $reservation->getServiceTime()->toString();

						return "{$date}, {$time}";
					},
					$booking->getReservations()
				);

				if ( ! empty( $dates ) ) {
					// phpcs:ignore
					echo implode( '<br>', $dates );
				} else {
					// phpcs:ignore
					echo mpa_tmpl_placeholder();
				}

				break;

			case 'quantity':
				$people = array_map(
					function ( $reservation ) {
						return $reservation->getCapacity();
					},
					$booking->getReservations()
				);

				if ( ! empty( $people ) ) {
					// phpcs:ignore
					echo implode( '<br>', $people );
				} else {
					// phpcs:ignore
					echo mpa_tmpl_placeholder();
				}

				break;

			case 'mpa_date':
				?>
				<abbr title="<?php echo esc_attr( get_the_date( mpapp()->settings()->getDateFormat(), $booking->getId() ) ); ?>">
					<?php echo get_the_date( 'Y/m/d', $booking->getId() ); ?>
				</abbr>
				<?php
				break;
		}
	}
}
