<?php

if ( count( $gateways ) &&
	in_array(
		true,
		array_map(
			function ( $gateway ) {
				return $gateway->isOnlinePayment();
			},
			$gateways
		)
	) ) {
	?>

	<section class="mpa-deposit-section mpa-checkout-section mpa-hide">
		<div id="mpa-deposit-table"></div>
		<p class="mpa-input-wrapper">
			<label>
				<input
					type="checkbox"
					class="mpa-deposit-switcher"
					id="mpa-deposit-switcher-<?php echo esc_attr( $html_id ); ?>"
					name="mpa-deposit-switcher"
					disabled
				>
				<?php
					esc_html_e( 'I want to pay the total price now', 'motopress-appointment' )
				?>
			</label>
		</p>
	</section>

<?php } ?>
