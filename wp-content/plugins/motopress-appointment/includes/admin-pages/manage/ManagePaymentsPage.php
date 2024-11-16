<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Entities\Payment;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class ManagePaymentsPage extends ManagePostsPage {

	/**
	 * @since 1.5.0
	 */
	protected function addActions() {
		parent::addActions();

		add_filter( 'posts_search', array( $this, 'searchByBookingId' ) );
	}

	/**
	 * @since 1.5.0
	 *
	 * @access protected
	 *
	 * @global wpdb $wpdb
	 *
	 * @param string $whereStr
	 * @return string
	 */
	public function searchByBookingId( $whereStr ) {
		global $wpdb;

		if ( ! empty( $_GET['booking_id'] ) ) {
			$whereStr .= $wpdb->prepare( " AND ($wpdb->posts.post_parent = %d)", $_GET['booking_id'] );
		}

		return $whereStr;
	}

	/**
	 * @since 1.5.0
	 */
	protected function enqueueScripts() {
		mpa_assets()->enqueueStyle( 'mpa-manage-posts' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function customColumns() {
		return array(
			'status'         => esc_html__( 'Status', 'motopress-appointment' ),
			'amount'         => esc_html__( 'Amount', 'motopress-appointment' ),
			'booking'        => esc_html__( 'Booking', 'motopress-appointment' ),
			'gateway'        => esc_html__( 'Payment Gateway', 'motopress-appointment' ),
			'payment_method' => esc_html__( 'Payment Method', 'motopress-appointment' ),
			'transaction_id' => esc_html__( 'Transaction ID', 'motopress-appointment' ),
			'mpa_date'       => esc_html__( 'Date' ),
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
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function customSortableColumns() {
		return array( 'amount' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $columnName
	 * @param Payment $payment
	 */
	protected function displayValue( $columnName, $payment ) {
		switch ( $columnName ) {
			case 'status':
				$paymentStatuses = mpapp()->postTypes()->payment()->statuses();
				echo '<span class="column-status-' . esc_attr( $payment->getStatus() ) . '">' . $paymentStatuses->getLabel( $payment->getStatus() ) . '</span>';
				break;

			case 'amount':
				echo mpa_tmpl_price( $payment->getAmount() );
				break;

			case 'booking':
				if ( $payment->getBookingId() !== 0 ) {
					echo mpa_tmpl_edit_post_link( $payment->getBookingId() );
				} else {
					echo mpa_tmpl_placeholder();
				}
				break;

			case 'gateway':
				$gateway = mpapp()->payments()->getGateway( $payment->getGatewayId() );
				echo ! is_null( $gateway ) ? $gateway->getName() : mpa_tmpl_placeholder();
				break;

			case 'payment_method':
				echo ! empty( $payment->getPaymentMethod() ) ? $payment->getPaymentMethod() : mpa_tmpl_placeholder();
				break;

			case 'transaction_id':
				echo ! empty( $payment->getTransactionId() ) ? $payment->getTransactionId() : mpa_tmpl_placeholder();
				break;

			case 'mpa_date':
				?>
				<abbr title="<?php echo esc_attr( get_the_date( mpapp()->settings()->getDateFormat(), $payment->getId() ) ); ?>">
					<?php echo get_the_date( 'Y/m/d', $payment->getId() ); ?>
				</abbr>
				<?php
				break;

			default:
				parent::displayValue( $columnName, $payment );
				break;
		}
	}
}
