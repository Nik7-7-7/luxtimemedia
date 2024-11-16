<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class Settings {

	use Settings\GeneralSettings;
	use Settings\EmailSettings;
	use Settings\NotificationSettings;
	use Settings\PaymentSettings;
	use Settings\IntegrationsSettings;
	use Settings\LicenseSettings;
	use Settings\MiscSettings;

	/**
	 * @since 1.0
	 * @since 1.5.0 added plugin name.
	 * @since 1.5.0 added payment settings.
	 *
	 * @return array
	 */
	public function getPublicSettings() {

		$publicSettings =
			array(
				'plugin_name' => mpapp()->getName(),
				// When working with dates in JS, sometimes we need to know what
				// the real date is in the location of the business (for example,
				// with big UTC-XX)
				'today'       => mpa_format_date( mpa_today(), 'internal' ),
			)
			+ $this->getGeneralSettings()
			+ $this->getPublicPaymentSettings();

		return $publicSettings;
	}
}
