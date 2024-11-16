<?php

namespace MotoPress\Appointment\ListTables;

use MotoPress\Appointment\Handlers\SecurityHandler;

/**
 *
 * @since 1.18.0
 */
class CustomersListTable extends \WP_List_Table {

	/**
	 *
	 * @var string
	 */
	private $orderBy;

	/**
	 *
	 * @var string
	 */
	private $order;

	/**
	 *
	 * @var string
	 */
	private $dateFormat;

	/**
	 *
	 * @var string
	 */
	private $dateTimeFormat;

	public function __construct() {
		parent::__construct( array(
			'singular' => 'customer',
			'plural'   => 'customers',
			'ajax'     => false,
		) );

		$this->orderBy = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'date_registered';
		$this->orderBy = preg_replace( '/\s+.*/', '', $this->orderBy );
		$this->order   = ( isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'ASC' );

		if ( ! in_array( $this->order, array( 'ASC', 'DESC' ) ) ) {
			$this->order = 'ASC';
		}

		$this->dateFormat     = mpapp()->settings()->getDateFormat();
		$this->dateTimeFormat = mpapp()->settings()->getPostDateTimeFormat( ' ' );
	}

	protected function query_items() {

		/**
		 * @param int $postsPerPage
		 */
		$postsPerPage = apply_filters( 'mpa_filter_customers_per_page', 20 );

		$atts = array(
			'orderby'  => $this->orderBy,
			'order'    => $this->order,
			'per_page' => $postsPerPage,
			'paged'    => $this->get_pagenum(),
		);

		if ( isset( $_GET['s'] ) ) {
			$atts['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		}

		$customers = mpapp()->repositories()->customer()->findAll( $atts );

		$items = array_map( function ( $customer ) {
			return array(
				'id'              => $customer->getId(),
				'user_id'         => $customer->getUserId(),
				'name'            => $customer->getName(),
				'email'           => $customer->getEmail(),
				'phone'           => $customer->getPhone(),
				'bookings'        => mpapp()->repositories()->customer()->getTotalBookingsOfCustomer( $customer->getId() ),
				'date_registered' => $customer->getDateRegistered()->getTimestamp() ?? '',
				'last_active'     => $customer->getLastActive() ?? '',
			);
		}, $customers );

		$totalCustomers = mpapp()->repositories()->customer()->getTotalCustomers();
		$pagesCount     = ceil( $totalCustomers / $postsPerPage );

		$this->set_pagination_args( array(
			'total_items' => $totalCustomers,
			'per_page'    => $postsPerPage,
			'total_pages' => $pagesCount,
		) );

		return $items;
	}

	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = $this->query_items();
	}

	/**
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'name'            => array( 'name', ( $this->orderBy == 'name' ) ),
			'email'           => array( 'email', ( $this->orderBy == 'email' ) ),
			'bookings'        => array( 'bookings', ( $this->orderBy == 'bookings' ) ),
			'date_registered' => array( 'date_registered', ( $this->orderBy == 'date_registered' ) ),
			'last_active'     => array( 'last_active', ( $this->orderBy == 'last_active' ) ),
		);
	}

	/**
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_name( $item ) {
		$itemId    = absint( $item['id'] );
		$adminUrl  = admin_url( 'admin.php?page=mpa_customers' );
		$editUrl   = add_query_arg( [
			'id'     => $itemId,
			'action' => 'edit',
		], $adminUrl );
		$deleteUrl = add_query_arg( [
			'id'       => $itemId,
			'action'   => 'delete',
			'_wpnonce' => wp_create_nonce( 'mpa-delete-customer' ),
		], $adminUrl );

		$actions = array();

		if ( SecurityHandler::isUserCanEditCustomer() ) {
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', $editUrl, esc_html__( 'Edit Customer', 'motopress-appointment' ) );
		}

		if ( $item['user_id'] && SecurityHandler::isUserCanEditUsers() ) {
			$editUserProfileUrl   = admin_url( sprintf( 'user-edit.php?user_id=%d', $item['user_id'] ) );
			$actions['edit_user'] = sprintf( '<a href="%s">%s</a>', $editUserProfileUrl, esc_html__( 'Edit User', 'motopress-appointment' ) );
		}

		if ( SecurityHandler::isUserCanDeleteCustomer() && $item['bookings'] == 0 ) {
			$actions['delete'] = sprintf( '<a href="%s">%s</a>', $deleteUrl, esc_html__( 'Delete', 'motopress-appointment' ) );
		}

		$name = $item['name'] ? esc_html( $item['name'] ) : sprintf( '#%d', $item['id'] );

		if ( SecurityHandler::isUserCanEditCustomer() ) {
			return sprintf( '<a class="row-title" href="%s" aria-label="%s">%s</a>', $editUrl, $name, $name ) . $this->row_actions( $actions );
		} else {
			return $name . $this->row_actions( $actions );
		}
	}

	/**
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_email( $item ) {
		$email = esc_html( $item['email'] );

		return sprintf( '<a href="mailto:%s">%s</a>', $email, $email );
	}

	/**
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_phone( $item ) {
		$phone = esc_html( $item['phone'] );

		return sprintf( '<a href="tel:%s">%s</a>', $phone, $phone );
	}

	/**
	 *
	 * @return string 0 or link to all bookings of customer
	 */
	public function column_bookings( $item ) {
		$bookingsCount = absint( $item['bookings'] );

		if ( ! $bookingsCount ) {
			return $bookingsCount;
		}

		$bookingsUrl = mpapp()->pages()->manageBookings()->getUrl( [ 'customer_id' => absint( $item['id'] ) ] );

		return sprintf( '<a href="%s">%s</a>', esc_url( $bookingsUrl ), esc_html( $bookingsCount ) );
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_date_registered( $item ) {
		if ( ! $item['date_registered'] ) {
			return '-';
		}

		return '<abbr title="' . esc_attr( date_i18n( $this->dateTimeFormat, $item['date_registered'] ) ) . '">' .
		       esc_html( date_i18n( $this->dateFormat, $item['date_registered'] ) ) . '</abbr>';
	}

	public function column_last_active( $item ) {
		if ( ! $item['last_active'] ) {
			return '-';
		}

		return '<abbr title="' . esc_attr( date_i18n( $this->dateTimeFormat, $item['last_active'] ) ) . '">' .
		       esc_html( date_i18n( $this->dateFormat, $item['last_active'] ) ) . '</abbr>';
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_default( $item, $columnName ) {
		switch ( $columnName ) {
			default:
				return '<span aria-hidden="true">&#8212;</span>';
		}
	}

	/**
	 *
	 * @return array An associative array [ %slug% => %Title% ].
	 */
	public function get_columns() {
		return array(
			'name'            => esc_html__( 'Name', 'motopress-appointment' ),
			'email'           => esc_html__( 'Email', 'motopress-appointment' ),
			'phone'           => esc_html__( 'Phone', 'motopress-appointment' ),
			'bookings'        => esc_html__( 'Bookings', 'motopress-appointment' ),
			'date_registered' => esc_html__( 'Date Registered', 'motopress-appointment' ),
			'last_active'     => esc_html__( 'Last Active', 'motopress-appointment' ),
		);
	}

	/**
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		?>
        <div class="alignleft actions">
			<?php
			if ( 'top' === $which ) {
				?>
                <form id="posts-filter" method="get">
                    <input type="hidden" name="page" value="mpa_customers"/>
					<?php $this->search_box( __( 'Search', 'motopress-appointment' ), 'customers' ); ?>
                </form>
				<?php
			}
			?>
        </div>
		<?php
	}
}