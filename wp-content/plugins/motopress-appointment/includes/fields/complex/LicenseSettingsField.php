<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LicenseSettingsField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'license-settings';

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $default = '';

	/**
	 * @param string $value
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function validateValue( $value ) {

		if ( '' === $value ) {
			return $this->default;
		}

		$value = sanitize_text_field( $value );

		return $value;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {

		$licenseKey = mpapp()->settings()->getLicenseKey();

		if ( $licenseKey ) {
			$licenseData = mpapp()->settings()->checkLicense();
		}

		$output = '';

		ob_start();

		?>

		<i><?php _e( "The License Key is required in order to get automatic plugin updates and support. You can manage your License Key in your personal account. <a href='https://motopress.zendesk.com/hc/en-us/articles/202812996-How-to-use-your-personal-MotoPress-account' target='_blank'>Learn more</a>.", 'motopress-appointment' ); ?></i>

		<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<?php esc_html_e( 'License Key', 'motopress-appointment' ); ?>
				</th>
				<td>
					<input id="mpa_edd_license_key" name="mpa_edd_license_key" type="password"
						class="regular-text" value="<?php esc_attr_e( $licenseKey ); ?>"/>

					<?php if ( $licenseKey ) { ?>
					<i style="display:block;"><?php echo str_repeat( '&#8226;', 20 ) . substr( $licenseKey, -7 ); ?></i>
					<?php } ?>
				</td>
			</tr>
			<?php if ( isset( $licenseData, $licenseData->license ) ) { ?>
			<tr valign="top">
				<th scope="row" valign="top">
				<?php esc_html_e( 'Status', 'motopress-appointment' ); ?>
				</th>
				<td>
				<?php
				switch ( $licenseData->license ) {
					case 'inactive':
					case 'site_inactive':
						esc_html_e( 'Inactive', 'motopress-appointment' );
						break;
					case 'valid':
						if ( $licenseData->expires !== 'lifetime' ) {
							$date    = ( $licenseData->expires ) ? new \DateTime( $licenseData->expires ) : false;
							$expires = ( $date ) ? ' ' . $date->format( 'd.m.Y' ) : '';
							echo __( 'Valid until', 'motopress-appointment' ) . $expires;
						} else {
							esc_html_e( 'Valid (Lifetime)', 'motopress-appointment' );
						}
						break;
					case 'disabled':
						esc_html_e( 'Disabled', 'motopress-appointment' );
						break;
					case 'expired':
						esc_html_e( 'Expired', 'motopress-appointment' );
						break;
					case 'invalid':
						esc_html_e( 'Invalid', 'motopress-appointment' );
						break;
					case 'item_name_mismatch':
						_e( "Your License Key does not match the installed plugin. <a href='https://motopress.zendesk.com/hc/en-us/articles/202957243-What-to-do-if-the-license-key-doesn-t-correspond-with-the-plugin-license' target='_blank'>How to fix this.</a>", 'motopress-appointment' );
						break;
					case 'invalid_item_id':
						esc_html_e( 'Product ID is not valid', 'motopress-appointment' );
						break;
				}
				?>
				</td>
			</tr>

				<?php if ( in_array( $licenseData->license, array( 'inactive', 'site_inactive', 'valid', 'expired' ) ) ) { ?>

			<tr valign="top">
				<th scope="row" valign="top">
					<?php esc_html_e( 'Action', 'motopress-appointment' ); ?>
				</th>
				<td>
					<?php
					if ( 'inactive' === $licenseData->license || 'site_inactive' === $licenseData->license ) {
						wp_nonce_field( 'mpa_edd_nonce', 'mpa_edd_nonce' );
						?>
					<input type="submit" class="button-secondary" name="edd_license_activate"
						value="<?php esc_attr_e( 'Activate License', 'motopress-appointment' ); ?>"/>

					<?php } elseif ( 'valid' === $licenseData->license ) { ?>
						<?php wp_nonce_field( 'mpa_edd_nonce', 'mpa_edd_nonce' ); ?>

					<input type="submit" class="button-secondary" name="edd_license_deactivate"
						value="<?php esc_attr_e( 'Deactivate License', 'motopress-appointment' ); ?>"/>

					<?php } elseif ( 'expired' === $licenseData->license ) { ?>

					<a href="<?php echo esc_url( mpapp()->settings()->getRenewUrl() ); ?>"
						class="button-secondary"
						target="_blank">
						<?php esc_html_e( 'Renew License', 'motopress-appointment' ); ?>
					</a>

						<?php
					}
					?>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
		</table>

		<?php
		$output = ob_get_contents();

		if ( $output ) {
			ob_end_clean();
		}

		return $output;
	}
}
