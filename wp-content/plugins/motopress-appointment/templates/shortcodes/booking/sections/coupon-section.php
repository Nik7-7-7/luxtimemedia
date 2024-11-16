<?php

/**
 * @since 1.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<section class="mpa-checkout-section mpa-coupon-details">
	<p class="mpa-shortcode-title">
		<?php esc_html_e( 'Coupon', 'motopress-appointment' ); ?>
	</p>

	<p class="mpa-input-wrapper mpa-coupon-code-wrapper">
		<input type="text" name="coupon_code" class="mpa-coupon-code" placeholder="<?php esc_html_e( 'Coupon code', 'motopress-appointment' ); ?>" autocomplete="off">
		<span class="mpa-preloader mpa-hide"></span>
	</p>

	<p class="mpa-input-wrapper mpa-apply-coupon-wrapper">
		<?php
		echo mpa_tmpl_button(
			esc_html__( 'Apply', 'motopress-appointment' ),
			array(
				'class' => 'button button-primary mpa-apply-coupon-button',
			)
		);
		?>
	</p>

	<p class="mpa-message-wrapper mpa-hide"></p>
</section>
