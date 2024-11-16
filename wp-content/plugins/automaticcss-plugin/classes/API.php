<?php
/**
 * Automatic.css API class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Database_Settings;
use Exception;

/**
 * API class.
 */
class API {

	/**
	 * Get the ACSS vars.
	 *
	 * @return array The ACSS vars.
	 */
	public static function get_settings() {
		$database = Database_Settings::get_instance();
		return $database->get_vars();
	}

	/**
	 * Get a specific ACSS var.
	 *
	 * @param string $setting_key The key of the setting to get.
	 * @return mixed The value of the setting.
	 */
	public static function get_setting( $setting_key ) {
		$database = Database_Settings::get_instance();
		return $database->get_var( $setting_key );
	}

	/**
	 * Update ACSS vars and trigger CSS generation.
	 *
	 * @param array $new_vars The vars to update (will be merged over with existing ones).
	 * @param array $options Options for the update.
	 * @return array Info about the saved vars and the generated CSS files.
	 * @throws Invalid_Form_Values If any of the provided vars are not valid.
	 * @throws Insufficient_Permissions If the user does not have the required permissions.
	 */
	public static function update_settings( $new_vars, $options = array() ) {
		Logger::Log( sprintf( "%s: called with new_vars:\n%s", __METHOD__, print_r( $new_vars, true ) ), Logger::LOG_LEVEL_NOTICE );
		if ( ! is_array( $new_vars ) ) {
			$type = gettype( $new_vars );
			Logger::Log( sprintf( '%s: this function expects an array of settings => values; received %s', __METHOD__, $type ) );
			return;
		} else if ( empty( $new_vars ) ) {
			Logger::Log( sprintf( '%s: received an empty array, skipping', __METHOD__ ) );
			return;
		}
		$default_options = array(
			'regenerate_css' => true
		);
		$options = array_merge( $default_options, $options );
		// STEP: fix the saturation when overriding colors.
		$color_modifiers = array( 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark', 'hover', 'comp' );
		foreach ( $new_vars as $key => $value ) {
			if ( preg_match( '/color-(\w+)/', $key, $matches ) ) {
				$color_name = $matches[1];
				$color = new \Automatic_CSS\Helpers\Color( $value );
				$saturation = $color->s;
				foreach ( $color_modifiers as $color_modifier ) {
					$var_saturation_key = "{$color_name}-{$color_modifier}-s";
					if ( ! isset( $new_vars[ $var_saturation_key ] ) ) {
						$new_vars[ $var_saturation_key ] = $saturation;
						Logger::log( sprintf( '%s: setting saturation for %s to %s', __METHOD__, $var_saturation_key, $saturation ), Logger::LOG_LEVEL_NOTICE );
					}
				}
			}
		}
		Logger::Log( sprintf( "%s: overriding these var:\n%s", __METHOD__, print_r( $new_vars, true ) ), Logger::LOG_LEVEL_NOTICE );
		// STEP: try updating the database.
		$database = Database_Settings::get_instance();
		$old_vars = $database->get_vars();
		$save_vars = array_merge( $old_vars, $new_vars );
		return $database->save_vars( $save_vars, $options['regenerate_css'] );
	}

	/**
	 * Get all the recipes.
	 *
	 * @return array The recipes.
	 */
	public static function get_all_recipes() {
		return ( new \Automatic_CSS\Model\Config\Expansions() )->get_all_expansions();
	}

}
