<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Crons;

use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Helpers\PriceCalculationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron start data must be set with setStartTaskData() before schedule it.
 * Start data array contains keys from constants and their correspondednt values.
 */
class ExportBookingsCron extends AbstractTaskChainCron {

	// array with string statuses
	const TASK_DATA_KEY_BOOKING_STATUSES = 'booking_statuses';
	// Y-m-d string
	const TASK_DATA_KEY_RESERFATION_DATE_FROM = 'reservation_date_from';
	// Y-m-d string
	const TASK_DATA_KEY_RESERVATION_DATE_TO = 'reservation_date_to';
	const TASK_DATA_KEY_SERVICE_ID          = 'service_id';
	const TASK_DATA_KEY_EMPLOYEE_ID         = 'employee_id';
	const TASK_DATA_KEY_LOCATION_ID         = 'location_id';

	const TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT = 'all_found_bookings_count';
	const TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT = 'processed_bookings_count';
	const TASK_DATA_KEY_EXPORT_FILE_NAME         = 'export_file_name';

	const FILE_NAME_EXPORTED_BOOKINGS = 'mpa_bookings.csv';

	const EXPORT_DOWNLOAD_URL_PARAMETER_FILE_NAME = 'mpa_download_filename';
	const EXPORT_DOWNLOAD_URL_PARAMETER_NONCE     = 'mpa_nonce';

	const EXPORT_COLUMN_BOOKING_ID     = 'booking_id';
	const EXPORT_COLUMN_BOOKING_STATUS = 'booking_status';
	const EXPORT_COLUMN_CUSTOMER_NAME  = 'customer_name';
	const EXPORT_COLUMN_CUSTOMER_EMAIL = 'customer_email';
	const EXPORT_COLUMN_CUSTOMER_PHONE = 'customer_phone';
	const EXPORT_COLUMN_CUSTOMER_NOTES = 'customer_notes';

	const EXPORT_COLUMN_SERVICE_TITLE  = 'service_title';
	const EXPORT_COLUMN_LOCATION_TITLE = 'location_title';
	const EXPORT_COLUMN_EMPLOYEE_NAME  = 'employee_name';
	const EXPORT_COLUMN_SERVICE_DATE   = 'service_date';
	const EXPORT_COLUMN_SERVICE_TIME   = 'service_time';
	const EXPORT_COLUMN_CLIENTS_COUNT  = 'clients_count';

	// service price from service entity
	const EXPORT_COLUMN_SERVICE_PRICE = 'service_price';
	// multilpied for clients count
	const EXPORT_COLUMN_SERVICE_SUBTOTAL = 'service_subtotal';
	const EXPORT_COLUMN_COUPON_CODE      = 'coupon_code';
	const EXPORT_COLUMN_COUPON_DISCOUNT  = 'coupon_discount';
	// price with discount
	const EXPORT_COLUMN_SERVICE_TOTAL = 'service_total';
	const EXPORT_COLUMN_TOTAL_PAID    = 'total_paid_amount';
	const EXPORT_COLUMN_PAYMENTS      = 'payments';


	public static function getCronActionHookName(): string {
		return 'mpa_export_bookings_cron';
	}

	public static function getCronStartIntervalId(): string {
		return static::CRON_START_INTERVAL_ID_EVERY_3_MIN;
	}

	/**
	 * Init new task chain data. You can overwrite this method to add some custom initialisation.
	 * @throws Exception if cron already started or canceling.
	 */
	public static function initTaskChainDataAndStartCron( array $startTaskData ) {

		$startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ] = 'u' . get_current_user_id() . '_' . static::FILE_NAME_EXPORTED_BOOKINGS;

		$exportFilePath = mpapp()->getPluginUploadsPath( $startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ] );

		// delete previouse export file
		if ( file_exists( $exportFilePath ) ) {
			// phpcs:ignore
			@unlink( $exportFilePath );
		}

		parent::initTaskChainDataAndStartCron( $startTaskData );
	}

	public static function getExportFilePath(): string {

		$exportFilePath = '';
		$startTaskData  = static::getStartTaskData();

		if ( ! empty( $startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ] ) ) {

			$exportFilePath = mpapp()->getPluginUploadsPath( $startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ] );
		}

		return $exportFilePath;
	}

	public static function getExportFileUrl(): string {

		$exportFileUrl = '';
		$startTaskData = static::getStartTaskData();

		if ( ! empty( $startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ] ) ) {

			$exportFileUrl = add_query_arg(
				array(
					static::EXPORT_DOWNLOAD_URL_PARAMETER_FILE_NAME => $startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ],
					static::EXPORT_DOWNLOAD_URL_PARAMETER_NONCE     => wp_create_nonce( $startTaskData[ static::TASK_DATA_KEY_EXPORT_FILE_NAME ] ),
				),
				admin_url()
			);
		}

		return $exportFileUrl;
	}


	protected function calculateCurrentExecutionPercentage( array $taskData ): float {

		// if $taskData is empty then we have empty export
		if ( empty( $taskData ) ) {
			return 100;
		}

		$percentage = 0.0;

		if ( ! empty( $taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] ) &&
			! empty( $taskData[ static::TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT ] )
		) {
			$allBookingsCount       = absint( $taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] );
			$processedBookingsCount = absint( $taskData[ static::TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT ] );

			$percentage = round( $processedBookingsCount / $allBookingsCount * 100, 1, PHP_ROUND_HALF_DOWN );
		}

		return $percentage;
	}

	protected function processTask( array $taskData ): array {

		$exportFile = false;

		try {

			$exportColumns = $this->getBookingExportColumns();

			// we have no export columns
			if ( empty( $exportColumns ) ) {
				return array();
			}

			$exportFilePath = static::getExportFilePath();

			// phpcs:ignore
			$exportFile = fopen( $exportFilePath, 'a' );

			if ( false === $exportFile ) {
				throw new \Exception( 'Could not open file for export: ' . $exportFilePath );
			}

			$booking = $this->findNextBookingForExport( $taskData );

			// if we did not find any bookings then we have empty export
			// or previously found bookings were deleted
			if ( null === $booking &&
				empty( $taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] )
			) {
				return array();
			}

			// put column names if this is a first export raw
			if ( empty( $taskData[ static::TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT ] ) ) {

				fputcsv(
					$exportFile,
					// It's better to strip keys before calling fputcsv()
					// See https://www.php.net/manual/en/function.fputcsv.php#123807
					array_values( $exportColumns ),
					','
				);
			}

			if ( null !== $booking ) {

				foreach ( $booking->getReservations() as $reservation ) {

					$exportData = $this->getBookingReservationExportData( $reservation, $exportColumns );

					fputcsv(
						$exportFile,
						// It's better to strip keys before calling fputcsv()
						// See https://www.php.net/manual/en/function.fputcsv.php#123807
						array_values( $exportData ),
						','
					);
				}
			}
		} catch ( \Throwable $e ) {

			// phpcs:ignore
			error_log( '### BOOKING EXPORT ' . $e->__toString() );
		}

		if ( ! empty( $taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] ) ) {

			// increase processed bookings even if we did not find it
			// because it can be in case when booking was deleted during export
			// and we need to finish export anyway - get 100% execution percentage
			$taskData[ static::TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT ]++;
		}

		if ( false !== $exportFile ) {
			// phpcs:ignore
			fclose( $exportFile );
		}

		return $taskData;
	}

	private function findNextBookingForExport( array &$taskData ) {

		$booking = null;

		global $wpdb;

		if ( ! isset( $taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] ) ) {

			$query = $wpdb->prepare(
				// phpcs:ignore
				"SELECT COUNT( * ) FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = %s" . $this->getBookingsSQLWhereClause( $taskData ),
				mpapp()->postTypes()->booking()->getPostType()
			);

			// phpcs:ignore
			$allFoundBookingsCount = $wpdb->get_var( $query );

			$taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] = absint( $allFoundBookingsCount );
			$taskData[ static::TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT ] = 0;
		}

		if ( 0 < $taskData[ static::TASK_DATA_KEY_ALL_FOUND_BOOKINGS_COUNT ] ) {

			$query = $wpdb->prepare(
				"SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = %s" .
				// phpcs:ignore
				$this->getBookingsSQLWhereClause( $taskData ) .
				"ORDER BY {$wpdb->posts}.ID DESC LIMIT 1 OFFSET %d",
				mpapp()->postTypes()->booking()->getPostType(),
				absint( $taskData[ static::TASK_DATA_KEY_PROCESSED_BOOKINGS_COUNT ] )
			);

			// phpcs:ignore
			$bookingId = $wpdb->get_var( $query );

			if ( null !== $bookingId ) {

				$booking = mpapp()->repositories()->booking()->findById( $bookingId );
			}
		}

		return $booking;
	}

	private function getBookingsSQLWhereClause( array $taskData ): string {

		global $wpdb;

		$bookingStatuses = '\'' . implode( '\', \'', $taskData[ static::TASK_DATA_KEY_BOOKING_STATUSES ] ) . '\'';

		$where = " AND {$wpdb->posts}.post_status IN ({$bookingStatuses})";

		if ( ! empty( $taskData[ static::TASK_DATA_KEY_RESERFATION_DATE_FROM ] ) ||
			! empty( $taskData[ static::TASK_DATA_KEY_RESERVATION_DATE_TO ] ) ||
			! empty( $taskData[ static::TASK_DATA_KEY_SERVICE_ID ] ) ||
			! empty( $taskData[ static::TASK_DATA_KEY_EMPLOYEE_ID ] ) ||
			! empty( $taskData[ static::TASK_DATA_KEY_LOCATION_ID ] )
		) {

			$where = " AND {$wpdb->posts}.ID IN (
				SELECT DISTINCT posts_1.post_parent 
				FROM {$wpdb->posts} AS posts_1 ";

			if ( ! empty( $taskData[ static::TASK_DATA_KEY_RESERFATION_DATE_FROM ] ) ) {

				$where .= $wpdb->prepare(
					"INNER JOIN {$wpdb->postmeta} AS pm1 ON posts_1.ID = pm1.post_id AND pm1.meta_key = '_mpa_date' AND pm1.meta_value >= %s",
					$taskData[ static::TASK_DATA_KEY_RESERFATION_DATE_FROM ]
				);

				if ( ! empty( $taskData[ static::TASK_DATA_KEY_RESERVATION_DATE_TO ] ) ) {

					$where .= $wpdb->prepare(
						' AND pm1.meta_value <= %s',
						$taskData[ static::TASK_DATA_KEY_RESERVATION_DATE_TO ]
					);
				}
			} elseif ( ! empty( $taskData[ static::TASK_DATA_KEY_RESERVATION_DATE_TO ] ) ) {

				$where .= $wpdb->prepare(
					"INNER JOIN {$wpdb->postmeta} AS pm1 ON posts_1.ID = pm1.post_id AND pm1.meta_key = '_mpa_date' AND pm1.meta_value <= %s",
					$taskData[ static::TASK_DATA_KEY_RESERVATION_DATE_TO ]
				);
			}

			if ( ! empty( $taskData[ static::TASK_DATA_KEY_SERVICE_ID ] ) ) {

				$where .= $wpdb->prepare(
					"INNER JOIN {$wpdb->postmeta} AS pm2 ON posts_1.ID = pm2.post_id AND pm2.meta_key = '_mpa_service' AND pm2.meta_value = %s",
					$taskData[ static::TASK_DATA_KEY_SERVICE_ID ]
				);
			}

			if ( ! empty( $taskData[ static::TASK_DATA_KEY_EMPLOYEE_ID ] ) ) {

				$where .= $wpdb->prepare(
					"INNER JOIN {$wpdb->postmeta} AS pm3 ON posts_1.ID = pm3.post_id AND pm3.meta_key = '_mpa_employee' AND pm3.meta_value = %s",
					$taskData[ static::TASK_DATA_KEY_EMPLOYEE_ID ]
				);
			}

			if ( ! empty( $taskData[ static::TASK_DATA_KEY_LOCATION_ID ] ) ) {

				$where .= $wpdb->prepare(
					"INNER JOIN {$wpdb->postmeta} AS pm4 ON posts_1.ID = pm4.post_id AND pm4.meta_key = '_mpa_location' AND pm4.meta_value = %s",
					$taskData[ static::TASK_DATA_KEY_LOCATION_ID ]
				);
			}

			$where .= ')';
		}

		return $where;
	}

	private function getBookingExportColumns(): array {

		return apply_filters(
			'mpa_get_booking_export_columns',
			array(
				static::EXPORT_COLUMN_BOOKING_ID       => __( 'ID', 'motopress-appointment' ),
				static::EXPORT_COLUMN_BOOKING_STATUS   => __( 'Booking status', 'motopress-appointment' ),
				static::EXPORT_COLUMN_CUSTOMER_NAME    => __( 'Customer name', 'motopress-appointment' ),
				static::EXPORT_COLUMN_CUSTOMER_EMAIL   => __( 'Customer email', 'motopress-appointment' ),
				static::EXPORT_COLUMN_CUSTOMER_PHONE   => __( 'Customer phone', 'motopress-appointment' ),
				static::EXPORT_COLUMN_CUSTOMER_NOTES   => __( 'Customer notes', 'motopress-appointment' ),

				static::EXPORT_COLUMN_SERVICE_TITLE    => __( 'Service', 'motopress-appointment' ),
				static::EXPORT_COLUMN_LOCATION_TITLE   => __( 'Location', 'motopress-appointment' ),
				static::EXPORT_COLUMN_EMPLOYEE_NAME    => __( 'Employee name', 'motopress-appointment' ),
				static::EXPORT_COLUMN_SERVICE_DATE     => __( 'Date', 'motopress-appointment' ),
				static::EXPORT_COLUMN_SERVICE_TIME     => __( 'Time', 'motopress-appointment' ),
				static::EXPORT_COLUMN_CLIENTS_COUNT    => __( 'Number of clients', 'motopress-appointment' ),

				static::EXPORT_COLUMN_SERVICE_PRICE    => __( 'Service price', 'motopress-appointment' ),
				static::EXPORT_COLUMN_SERVICE_SUBTOTAL => __( 'Service subtotal', 'motopress-appointment' ),
				static::EXPORT_COLUMN_COUPON_CODE      => __( 'Coupon code', 'motopress-appointment' ),
				static::EXPORT_COLUMN_COUPON_DISCOUNT  => __( 'Coupon discount', 'motopress-appointment' ),
				static::EXPORT_COLUMN_SERVICE_TOTAL    => __( 'Total Price', 'motopress-appointment' ),
				static::EXPORT_COLUMN_TOTAL_PAID       => __( 'Total Paid', 'motopress-appointment' ),
				static::EXPORT_COLUMN_PAYMENTS         => __( 'Payments', 'motopress-appointment' ),
			)
		);
	}

	/**
	 * @param \MotoPress\Appointment\Entities\Reservation $reservation
	 * @param array $exportColumns - [ column_name => column_label, ... ]
	 * @return array [ column_name => column_value, ... ]
	 */
	private function getBookingReservationExportData( Reservation $reservation, array $exportColumns ): array {

		$reservationExportData = array();

		$booking = $reservation->getBooking();

		foreach ( $exportColumns as $columnName => $columnLabel ) {

			$columnValue = '';

			switch ( $columnName ) {

				case static::EXPORT_COLUMN_BOOKING_ID:
					$columnValue = $booking->getId();
					break;

				case static::EXPORT_COLUMN_BOOKING_STATUS:
					$columnValue = mpapp()->postTypes()->booking()->statuses()->getLabel( $booking->getStatus() );
					break;

				case static::EXPORT_COLUMN_CUSTOMER_NAME:
					$columnValue = $booking->getCustomerName();
					break;

				case static::EXPORT_COLUMN_CUSTOMER_EMAIL:
					$columnValue = $booking->getCustomerEmail();
					break;

				case static::EXPORT_COLUMN_CUSTOMER_PHONE:
					$columnValue = $booking->getCustomerPhone();
					break;

				case static::EXPORT_COLUMN_CUSTOMER_NOTES:
					$columnValue = $booking->getCustomerNotes();
					break;

				case static::EXPORT_COLUMN_SERVICE_TITLE:
					$service = $reservation->getService();

					$columnValue = '';

					if ( null !== $service ) {
						$columnValue = $service->getTitle();
					}
					break;

				case static::EXPORT_COLUMN_LOCATION_TITLE:
					$location = $reservation->getLocation();

					$columnValue = '';

					if ( null !== $location ) {
						$columnValue = $location->getName();
					}
					break;

				case static::EXPORT_COLUMN_EMPLOYEE_NAME:
					$employee = $reservation->getEmployee();

					$columnValue = '';

					if ( null !== $employee ) {
						$columnValue = $employee->getName();
					}
					break;

				case static::EXPORT_COLUMN_SERVICE_DATE:
					$columnValue = mpa_format_date( $reservation->getDate() );
					break;

				case static::EXPORT_COLUMN_SERVICE_TIME:
					$columnValue = $reservation->getServiceTime()->toString();
					break;

				case static::EXPORT_COLUMN_CLIENTS_COUNT:
					$columnValue = $reservation->getCapacity();
					break;

				case static::EXPORT_COLUMN_SERVICE_PRICE:
					$service = $reservation->getService();

					$columnValue = 0;

					if ( null !== $service ) {

						$columnValue = PriceCalculationHelper::formatPrice( $reservation->getService()->getPrice() );
					}
					break;

				case static::EXPORT_COLUMN_SERVICE_SUBTOTAL:
					$columnValue = PriceCalculationHelper::formatPrice( $reservation->getPrice() );
					break;

				case static::EXPORT_COLUMN_COUPON_CODE:
					$coupon      = $booking->getCoupon();
					$columnValue = null === $coupon ? '' : $coupon->getCode();
					break;

				case static::EXPORT_COLUMN_COUPON_DISCOUNT:
					$columnValue = PriceCalculationHelper::formatPrice( $reservation->getDiscount() );
					break;

				case static::EXPORT_COLUMN_SERVICE_TOTAL:
					$columnValue = PriceCalculationHelper::formatPrice( $reservation->getTotalPrice() );
					break;

				case static::EXPORT_COLUMN_TOTAL_PAID:
					$totalPaid = 0.0;

					if ( 0 < $booking->getTotalPrice() ) {

						$totalPaid = $reservation->getTotalPrice() / $booking->getTotalPrice() * $booking->getPaidPrice();
					}

					$columnValue = PriceCalculationHelper::formatPrice( $totalPaid );
					break;

				case static::EXPORT_COLUMN_PAYMENTS:
					$paymentStrings = array();

					$payments = mpapp()->repositories()->payment()->findAllByBooking(
						$booking->getId(),
						array(
							'post_status' => \MotoPress\Appointment\PostTypes\Statuses\PaymentStatuses::STATUS_COMPLETED,
						)
					);

					if ( ! empty( $payments ) ) {

						foreach ( $payments as $payment ) {

							$paymentString = '#' . $payment->getId() .
								':' . mpa_get_status_label( $payment->getStatus() ) .
								' ' . PriceCalculationHelper::formatPrice(
									$payment->getAmount(),
									array(
										'currency_symbol' => $payment->getCurrency(),
									)
								) .
								' ' . $payment->getGatewayId();

							if ( ! empty( $payment->getPaymentMethod() ) ) {
								$paymentString .= ': ' . $payment->getPaymentMethod();
							}

							if ( ! empty( $payment->getTransactionId() ) ) {
								$paymentString .= ' - ' . $payment->getTransactionId();
							}

							$paymentStrings[] = $paymentString;
						}
					}

					$columnValue = implode( ';', $paymentStrings );
					break;
			}

			$reservationExportData[ $columnName ] = $columnValue;
		}

		return apply_filters( 'mpa_get_booking_reservation_export_data', $reservationExportData, $reservation );
	}
}
