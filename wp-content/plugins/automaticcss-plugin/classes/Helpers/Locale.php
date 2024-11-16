<?php
/**
 * Automatic.css Locale file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

/**
 * Automatic.css Locale class.
 */
class Locale {

	/**
	 * Holds the $GLOBALS key for the locale.
	 *
	 * @var string
	 */
	public static $global_locale_variable = 'acss_original_locale';

	/**
	 * Fix the locale to safely use floats.
	 *
	 * @return void
	 */
	public static function fix_locale() {
		if ( self::is_locale_fix_enabled() ) {
			$GLOBALS[ self::$global_locale_variable ] = get_locale();
			setlocale( LC_NUMERIC, 'en_US' );
		}
	}

	/**
	 * Restore the locale.
	 *
	 * @return void
	 */
	public static function restore_locale() {
		if ( self::is_locale_fix_enabled() && self::is_locale_changed() ) {
			setlocale( LC_NUMERIC, $GLOBALS[ self::$global_locale_variable ] );
		}
	}

	/**
	 * Check if the locale has been changed.
	 *
	 * @return bool
	 */
	public static function is_locale_changed() {
		if ( self::is_locale_fix_enabled() ) {
			return isset( $GLOBALS[ self::$global_locale_variable ] ) && get_locale() !== $GLOBALS[ self::$global_locale_variable ];
		}
		return false;
	}

	/**
	 * Check if the locale fix is enabled.
	 *
	 * @return boolean
	 */
	public static function is_locale_fix_enabled() {
		return defined( 'AUTOMATICCSS_FIX_LOCALE' ) && true === (bool) constant( 'AUTOMATICCSS_FIX_LOCALE' );
	}

}
