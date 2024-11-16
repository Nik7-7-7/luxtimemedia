<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Crons\AbandonPendingPaymentCron;
use MotoPress\Appointment\Crons\SendNotificationsCron;
use MotoPress\Appointment\Crons\DeleteDraftBookingsCron;
use MotoPress\Appointment\Crons\ExportBookingsCron;
use MotoPress\Appointment\Plugin\PluginPatcherCron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class CronsHandler {

	const CRON_CLASSES = array(
		PluginPatcherCron::class,
		AbandonPendingPaymentCron::class,
		SendNotificationsCron::class,
		DeleteDraftBookingsCron::class,
		ExportBookingsCron::class,
	);


	public function __construct() {

		add_action(
			'cron_schedules',
			function( array $notDefaultCronSchedules ) {
				return $this->addCustomCronStartIntervals( $notDefaultCronSchedules );
			}
		);

		// add cron action hooks
		add_action(
			'plugins_loaded',
			function() {

				foreach ( static::CRON_CLASSES as $cronClass ) {

					new $cronClass();
				}

				// schedule cron each time to make sure deferred notifications will be sent
				SendNotificationsCron::schedule();
			}
		);
	}


	private function addCustomCronStartIntervals( array $notDefaultCronSchedules ) {

		foreach ( static::CRON_CLASSES as $cronClass ) {

			if ( empty( $notDefaultCronSchedules[ $cronClass::getCronStartIntervalId() ] ) ) {

				$notDefaultCronSchedules[ $cronClass::getCronStartIntervalId() ] = array(
					'interval' => $cronClass::getCronStartIntervalInSeconds(),
					'display'  => $cronClass::getCronStartIntervalDescription(),
				);
			}
		}

		return $notDefaultCronSchedules;
	}


	public static function schedule_crons_after_plugin_activation() {

		AbandonPendingPaymentCron::schedule();
		SendNotificationsCron::schedule();
		DeleteDraftBookingsCron::schedule();
	}

	public static function unschedule_crons_before_plugin_deactivation() {

		foreach ( static::CRON_CLASSES as $cronClass ) {

			$cronClass::unschedule();
		}
	}
}
