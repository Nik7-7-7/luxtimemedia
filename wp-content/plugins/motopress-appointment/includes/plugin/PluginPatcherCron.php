<?php

namespace MotoPress\Appointment\Plugin;

use MotoPress\Appointment\Crons\AbstractTaskChainCron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatcherCron extends AbstractTaskChainCron {

	const OPTION_DB_VERSION = 'mpa_db_version';

	// IMPORTANT: add patch classes in the order of their versions!
	const PATCH_CLASSES = array(
		PluginPatch_1_4_0::class,
		PluginPatch_1_5_0::class,
		PluginPatch_1_11_0::class,
		PluginPatch_1_17_0::class,
		PluginPatch_1_18_0::class,
		PluginPatch_1_20_0::class,
		PluginPatch_1_23_1::class,
	);

	public function __construct() {

		if ( ! empty( static::getStartTaskData() ) ) {

			static::schedule();
		}

		parent::__construct();
	}

	public static function getCronActionHookName(): string {
		return 'mpa_plugin_patch_cron';
	}

	public static function getCronStartIntervalId(): string {
		return static::CRON_START_INTERVAL_ID_EVERY_3_MIN;
	}

	/**
	 * @return array with patch classes which must be executed.
	 */
	protected static function getStartTaskData(): array {

		$pluginDBVersion      = get_option( static::OPTION_DB_VERSION, null );
		$currentPluginVersion = mpapp()->getVersion();

		if ( empty( $pluginDBVersion ) ) {

			// plugin just activated so we do not need to execute any patches
			update_option( static::OPTION_DB_VERSION, $currentPluginVersion );
			return array();
		}

		if ( version_compare( $pluginDBVersion, $currentPluginVersion, '=' ) ) {

			return array();
		}

		$patchClassesForExecution = array();

		foreach ( static::PATCH_CLASSES as $patchClass ) {

			if ( version_compare( $pluginDBVersion, $patchClass::getVersion(), '<' ) ) {

				$patchClassesForExecution[] = $patchClass;
			}
		}

		return $patchClassesForExecution;
	}


	protected function processTask( array $patchClasses ): array {

		try {

			$patchResult = $patchClasses[0]::execute();

			// if true then patch completly executed its tasks
			if ( $patchResult ) {

				$pluginDBVersion = $patchClasses[0]::getVersion();
				update_option( static::OPTION_DB_VERSION, $pluginDBVersion );

				// remove executed patch from list because it is completly done
				array_shift( $patchClasses );

				if ( empty( $patchClasses ) ) {

					update_option( static::OPTION_DB_VERSION, mpapp()->getVersion() );
				}
			}
		} catch ( \Throwable $e ) {

			// phpcs:ignore
			error_log( '### PATCH ERROR: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
		}

		return $patchClasses;
	}
}
