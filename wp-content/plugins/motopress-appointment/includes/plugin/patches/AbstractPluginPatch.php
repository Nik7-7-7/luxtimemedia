<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class AbstractPluginPatch {

	/**
	 * @return string the version 1.x.x of the plugin to which it updates the database
	 */
	abstract public static function getVersion(): string;


	/**
	 * @return true if all patch tasks are completed. If patch can spend a lot of time
	 * then it must split all execution to several tasks and process just one of them
	 * at a time and retun false until last task will be executed.
	 */
	abstract public static function execute(): bool;
}
