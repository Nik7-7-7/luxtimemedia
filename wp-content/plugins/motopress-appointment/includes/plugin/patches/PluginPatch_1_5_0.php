<?php

namespace MotoPress\Appointment\Plugin;

use \MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatch_1_5_0 extends AbstractPluginPatch {

	public static function getVersion(): string {
		return '1.5.0';
	}


	public static function execute(): bool {

		// 1) Remove option "Default Appointment Status".
		// 2) Set the actual value of the "Confirmation Mode" option.

		$defaultBookingStatus = get_option( 'mpa_default_booking_status', 'none' );

		if ( 'none' !== $defaultBookingStatus ) {

			$confirmationMode = ( BookingStatuses::STATUS_CONFIRMED === $defaultBookingStatus ) ? 'auto' : 'manual';

			update_option( 'mpa_confirmation_mode', $confirmationMode );
		}

		return true;
	}
}
