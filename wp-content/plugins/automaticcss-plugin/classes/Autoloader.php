<?php
/**
 * Automatic.css Autoloader class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Autoloader class.
 */
class Autoloader {

	/**
	 * Directories to look for files in.
	 *
	 * @var array{string}
	 */
	public static $directories = array( 'classes', 'library' );

	/**
	 * Try and locate the class file and load it.
	 *
	 * @param String $class_name The class name to load.
	 * @return void
	 */
	public static function autoload( $class_name ) {
		// STEP: check that the class is in the plugin namespace.
		if ( 0 !== strpos( $class_name, __NAMESPACE__ ) ) {
			return;
		}
		// STEP: find the class file.
		$class_name = str_replace( __NAMESPACE__ . '\\', '', $class_name );
		foreach ( self::$directories as $directory ) {
			$file = realpath( ACSS_PLUGIN_DIR . '/' . $directory . '/' . str_replace( '\\', '/', $class_name ) . '.php' );
			if ( file_exists( $file ) ) {
				// STEP: load the class file.
				require_once $file; // phpcs:ignore PHPCS_SecurityAudit.Misc.IncludeMismatch.ErrMiscIncludeMismatchNoExt
				return;
			}
		}
	}

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( new self(), 'autoload' ) );
	}

}
