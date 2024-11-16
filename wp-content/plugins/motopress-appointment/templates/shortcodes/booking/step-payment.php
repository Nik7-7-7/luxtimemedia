<?php

/**
 * @since 1.5.0
 *
 * @param string $template_name Optional. Full shortcode name. 'appointment_form'
 *                              by default.
 * @param string $html_id       Optional. Unique ID of the shortcode instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name' => 'appointment_form',
		'html_id'       => '',
	),
	EXTR_SKIP
);

if ( empty( $html_id ) ) {
	$html_id = uniqid();
}

$gateways         = mpapp()->payments()->getActive();
$defaultGatewayId = mpapp()->settings()->getDefaultPaymentGateway();

if ( ! $defaultGatewayId || count( $gateways ) === 1 ) {
	$defaultGatewayId = mpa_first_key( $gateways );
}

// Display template
?>
<div class="mpa-booking-step mpa-booking-step-payment mpa-hide">
	<form class="mpa-checkout-form" method="POST" action="">
		<?php
			/**
			 * @since 1.11.0
			 *
			 * @hooked MotoPress\Appointment\Views\ShortcodesView::appointmentFormPaymentOrderSection()  - 10
			 * @hooked MotoPress\Appointment\Views\ShortcodesView::appointmentFormPaymentCouponSection() - 20
			 */
			do_action( "{$template_name}_payment_top_sections", $template_args );
		?>

		<?php mpa_display_template( 'shortcodes/booking/sections/accept-terms.php', array( 'html_id' => $html_id ) ); ?>

		<section class="mpa-billing-details mpa-checkout-section">
			<p class="mpa-shortcode-title mpa-payment-gateways-title">
				<?php esc_html_e( 'Payment Method', 'motopress-appointment' ); ?>
			</p>

			<?php if ( ! empty( $gateways ) ) { ?>
				<ul class="mpa-payment-gateways">
					<?php
					foreach ( $gateways as $gatewayId => $gateway ) {
						$inputId         = "mpa-payment-gateway-{$gatewayId}-{$html_id}";
						$description     = $gateway->getDescription();
						$checked         = checked( $defaultGatewayId, $gatewayId, false );
						$isOnlinePayment = (int) $gateway->isOnlinePayment();
						?>

						<li class="mpa-payment-gateway mpa-<?php echo esc_attr( $gatewayId ); ?>-payment-gateway">
							<input id="<?php echo esc_attr( $inputId ); ?>" type="radio" name="payment_gateway_id" value="<?php echo esc_attr( $gatewayId ); ?>" required="required" disabled="disabled" <?php echo $checked; ?> data-is-online-payment="<?php echo esc_attr( $isOnlinePayment ); ?>">

							<label for="<?php echo esc_attr( $inputId ); ?>" class="mpa-payment-gateway-title">
								<?php echo $gateway->getPublicName(); ?>
								<span class="mpa-preloader mpa-hide"></span>
							</label>

							<?php if ( ! empty( $description ) ) { ?>
								<p class="mpa-payment-gateway-description">
									<?php echo $description; ?>
								</p>
							<?php } ?>

							<div class="mpa-billing-fields mpa-hide">
								<?php echo $gateway->printBillingFields(); ?>
							</div>
						</li>
					<?php } // For each payment gateway ?>
				</ul>
			<?php } else { ?>
				<p class="mpa-no-payment-gateways">
					<?php esc_html_e( 'Sorry, it seems that there are no available payment methods.', 'motopress-appointment' ); ?>
				</p>
			<?php } ?>
		</section>

		<?php
		mpa_display_template(
			'shortcodes/booking/sections/deposit-section.php',
			array(
				'html_id'  => $html_id,
				'gateways' => $gateways,
			)
		);
		?>

        <p class="mpa-message mpa-error mpa-hide"></p>

		<p class="mpa-actions">
			<?php echo mpa_tmpl_button( esc_html__( 'Back', 'motopress-appointment' ), array( 'class' => 'button button-secondary mpa-button-back' ) ); ?>
			<?php
			echo mpa_tmpl_button(
				esc_html__( 'Reserve', 'motopress-appointment' ),
				array(
					'type'     => 'submit',
					'class'    => 'button button-primary mpa-button-next',
					'disabled' => 'disabled',
				)
			);
			?>
		</p>
	</form>

	<div class="mpa-loading"></div>
</div>
