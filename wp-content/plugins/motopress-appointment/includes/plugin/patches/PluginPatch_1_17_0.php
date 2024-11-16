<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatch_1_17_0 extends AbstractPluginPatch {

	public static function getVersion(): string {
		return '1.17.0';
	}


	public static function execute(): bool {

		// remove unused version history
		delete_option( 'mpa_db_history' );

		update_option( 'mpa_cash_payment_gateway_title', __( 'Pay on-site', 'motopress-appointment' ), true );

		// remove old crons because we changed some cron intervals
		$cronActionHookNames = array(
			'mpa_abandon_pending_payment_cron',
			'mpa_send_notifications_cron',
			'mpa_delete_draft_bookings_cron',
		);

		foreach ( $cronActionHookNames as $oldCronActionHookName ) {

			$cronNextScheduledTime = wp_next_scheduled( $oldCronActionHookName );

			if ( false !== $cronNextScheduledTime ) {

				wp_unschedule_event( $cronNextScheduledTime, $oldCronActionHookName );
			}
		}

		\MotoPress\Appointment\Handlers\CronsHandler::schedule_crons_after_plugin_activation();

		return true;
	}
}
