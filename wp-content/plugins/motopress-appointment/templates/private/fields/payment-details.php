<?php

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Payment;

/**
 * @since 1.5.0
 *
 * @param Booking   $booking    Required.
 * @param Payment[] $payments   Required.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$paymentStatuses   = mpapp()->postTypes()->payment()->statuses();
$completedStatuses = $paymentStatuses->getCompletedStatuses();

$totalPrice = $booking->getTotalPrice();
$totalPaid  = 0.0;

// Display template
?>
<table class="mpa-payments">
	<thead>
		<th class="column-payment"><?php esc_html_e( 'Payment ID', 'motopress-appointment' ); ?></th>
		<th class="column-status"><?php esc_html_e( 'Status', 'motopress-appointment' ); ?></th>
		<th class="column-amount"><?php esc_html_e( 'Amount', 'motopress-appointment' ); ?></th>
	</thead>

	<tbody>
		<?php if ( empty( $payments ) ) { ?>
			<tr>
				<td class="column-payment"><?php echo mpa_tmpl_placeholder(); ?></td>
				<td class="column-status"><?php echo mpa_tmpl_placeholder(); ?></td>
				<td class="column-amount"><?php echo mpa_tmpl_placeholder(); ?></td>
			</tr>
		<?php } else { ?>
			<?php
			foreach ( $payments as $payment ) {
				if ( $payment->isCompleted() ) {
					$totalPaid += $payment->getAmount();
				}
				?>

				<tr class="mpa-payment mpa-<?php echo $payment->getStatus(); ?>-payment">
					<td class="column-payment">
						<?php echo mpa_tmpl_edit_post_link( $payment->getId(), '#' . $payment->getId() ); ?>
					</td>

					<td class="column-status">
						<?php echo $paymentStatuses->getLabel( $payment->getStatus() ); ?>
					</td>

					<td class="column-amount">
						<?php echo mpa_tmpl_price_number( $payment->getAmount() ); ?>
					</td>
				</tr>
			<?php } // For each payment ?>
		<?php } // If have payments ?>
	</tbody>

	<tfoot>
		<tr class="mpa-total-paid-price">
			<th class="mpa-total-paid-label" colspan="2"><?php esc_html_e( 'Total Paid', 'motopress-appointment' ); ?></th>
			<th class="mpa-total-paid-amount"><?php echo mpa_tmpl_price_number( $totalPaid ); ?></th>
		</tr>

		<tr class="mpa-to-pay-price">
			<th class="mpa-to-pay-label" colspan="2"><?php esc_html_e( 'To Pay', 'motopress-appointment' ); ?></th>
			<th class="mpa-to-pay-amount"><?php echo mpa_tmpl_price_number( $totalPrice - $totalPaid ); ?></th>
		</tr>
	</tfoot>
</table>

<a href="<?php echo mpapp()->pages()->editPayment()->getNewPostUrl( array( 'booking_id' => $booking->getId() ) ); ?>" class="button button-primary mpa-add-payment-button">
	<?php esc_html_e( 'Add Payment Manually', 'motopress-appointment' ); ?>
</a>
