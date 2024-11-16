<?php
/**
 * @param array $products
 *
 * @see \MotoPress\Appointment\AdminPages\Custom\ExtensionsPage::$products
 *
 * @see MotoPress\Appointment\AdminPages\Custom\ExtensionsPage
 *
 * @since 1.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( ! empty( $products ) ) { ?>
	<p><?php esc_html_e( 'Extend the functionality of Appointment Booking with preferred add-ons.', 'motopress-appointment' ); ?></p>
	<div class="mpa-extensions">
		<?php foreach ( $products as $product ) { ?>
			<?php
			$utmLink = add_query_arg(
				array(
					'utm_source' => 'customer_website_dashboard',
					'utm_medium' => $product['slug'],
				),
				$product['link']
			);
			?>
			<div class="mpa-extension">
				<a href="<?php echo esc_url( $utmLink ); ?>" target="_blank">
					<img src="<?php echo esc_url( $product['thumbnail'] ); ?>"
						alt="<?php echo esc_url( $product['title'] ); ?>" class="mpa-extension-thumbnail"/>
				</a>
				<div class="mpa-extension-content">
					<h3 class="mpa-extension-title">
						<a href="<?php echo esc_url( $utmLink ); ?>" target="_blank">
							<?php echo esc_html( $product['title'] ); ?>
						</a>
					</h3>
					<p class="mpa-extension-excerpt">
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo wp_trim_words( esc_html( $product['excerpt'] ), 25 );
					?>
						</p>
					<a href="<?php echo esc_url( $utmLink ); ?>" class="mpa-extension-link button" target="_blank">
										<?php
										esc_html_e( 'Get this Extension', 'motopress-appointment' );
										?>
						</a>
				</div>
			</div>
		<?php } ?>
	</div>
<?php } else { ?>
	<p><?php esc_html_e( 'No extensions found.', 'motopress-appointment' ); ?></p>
<?php } ?>
