<?php

namespace MotoPress\Appointment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mpaCustomPathList = array(
	\MotoPress\Appointment\Plugin\PluginPatcherCron::class => 'includes/plugin/PluginPatcherCron.php',
	\MotoPress\Appointment\Plugin\AbstractPluginPatch::class => 'includes/plugin/patches/AbstractPluginPatch.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_4_0::class => 'includes/plugin/patches/PluginPatch_1_4_0.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_5_0::class => 'includes/plugin/patches/PluginPatch_1_5_0.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_11_0::class => 'includes/plugin/patches/PluginPatch_1_11_0.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_17_0::class => 'includes/plugin/patches/PluginPatch_1_17_0.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_18_0::class => 'includes/plugin/patches/PluginPatch_1_18_0.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_20_0::class => 'includes/plugin/patches/PluginPatch_1_20_0.php',
	\MotoPress\Appointment\Plugin\PluginPatch_1_23_1::class => 'includes/plugin/patches/PluginPatch_1_23_1.php',

	\MotoPress\Appointment\Handlers\AbstractNotificationSender::class => 'includes/handlers/notification/AbstractNotificationSender.php',
	\MotoPress\Appointment\Handlers\EmailNotificationSender::class => 'includes/handlers/notification/EmailNotificationSender.php',
	\MotoPress\Appointment\Handlers\AbstractSMSNotificationSender::class => 'includes/handlers/notification/AbstractSMSNotificationSender.php',

	\MotoPress\Appointment\Handlers\AjaxActions\AbstractAjaxAction::class => 'includes/handlers/ajax-actions/AbstractAjaxAction.php',
	\MotoPress\Appointment\Handlers\AjaxActions\ExportBookingsAction::class => 'includes/handlers/ajax-actions/ExportBookingsAction.php',
);

spl_autoload_register(
	function ( $class ) use ( $mpaCustomPathList ) {

		if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
			return; // Not ours
		}

		$classNameWithNamespace = ltrim( $class, '\\' );

		if ( ! empty( $mpaCustomPathList[ $classNameWithNamespace ] ) ) {

			if ( file_exists( \MotoPress\Appointment\PLUGIN_DIR . $mpaCustomPathList[ $classNameWithNamespace ] ) ) {

				require_once \MotoPress\Appointment\PLUGIN_DIR . $mpaCustomPathList[ $classNameWithNamespace ];
				return;
			}
		}

		// Split into namespace and class name
		if ( ! preg_match( '/(.+\\\\)(.+)/', $class, $path ) ) {
			return; // Failed
		}

		$namespace = $path[1]; // Something like 'MotoPress\Appointment\Package\SubPackage\'
		$className = $path[2]; // Something like 'ClassX'

		// 'MotoPress\Appointment\Package\SubPackage\' -> 'includes/package/sub-package/'
		$namespace = str_replace( __NAMESPACE__, 'includes', $namespace );
		$namespace = str_replace( '\\', DIRECTORY_SEPARATOR, $namespace );
		$namespace = preg_replace( '/([a-z])([A-Z])/', '$1-$2', $namespace );
		$namespace = preg_replace( '/([A-Z])([A-Z][a-z])/', '$1-$2', $namespace );
		$namespace = strtolower( $namespace );

		// 'includes/package/sub-package/ClassX.php', leave the class name unchanged
		$file = $namespace . $className . '.php';

		if ( file_exists( \MotoPress\Appointment\PLUGIN_DIR . $file ) ) {

			require_once \MotoPress\Appointment\PLUGIN_DIR . $file;
		}
	}
);
