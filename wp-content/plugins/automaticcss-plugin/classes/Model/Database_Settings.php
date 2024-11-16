<?php
/**
 * Automatic.css Database_Settings file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model;

use Automatic_CSS\CSS_Engine\CSS_Engine;
use Automatic_CSS\Exceptions\Insufficient_Permissions;
use Automatic_CSS\Exceptions\Invalid_Form_Values;
use Automatic_CSS\Exceptions\Invalid_Variable;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\UI;
use Automatic_CSS\Traits\Singleton;

/**
 * Automatic.css Database_Settings class.
 */
final class Database_Settings {

	use Singleton;

	/**
	 * Stores the name of the plugin's database option
	 *
	 * @var string
	 */
	public const ACSS_SETTINGS_OPTION = 'automatic_css_settings';

	/**
	 * Stores the current value from the wp_options table.
	 *
	 * @var array|null
	 */
	private $plugin_wp_options = null;

	/**
	 * Capability needed to write settings
	 *
	 * @var string
	 */
	public const CAPABILITY = 'manage_options';

	/**
	 * Initialize the class
	 *
	 * @return Database_Settings The current instance of the class.
	 */
	public function init() {
		if ( is_admin() ) {
			// Handle database changes when the plugin is updated.
			add_filter( 'automaticcss_upgrade_database', array( $this, 'upgrade_database' ), 10, 3 );
			// Handle deleting database options when the plugin is deleted.
			add_action( 'automaticcss_delete_plugin_data_start', array( $this, 'delete_database_options' ) );
		}
		return $this;
	}

	/**
	 * Get the current VARS values from the wp_options database table.
	 *
	 * @return array
	 */
	public function get_vars() {
		if ( ! isset( $this->plugin_wp_options ) ) {
			$this->plugin_wp_options = (array) get_option( self::ACSS_SETTINGS_OPTION, array() );
		}
		return $this->plugin_wp_options;
	}

	/**
	 * Get the value for a specific variable from the wp_options database table.
	 *
	 * @param  string $var The variable name.
	 * @return mixed|null
	 */
	public function get_var( $var ) {
		$vars = $this->get_vars();
		if ( is_array( $vars ) && array_key_exists( $var, $vars ) ) {
			return $vars[ $var ];
		}
		return null;
	}

	/**
	 * Save the plugin's options to the database. Will work even if option doesn't exist (fresh start).
	 *
	 * @see    https://developer.wordpress.org/reference/functions/update_option/
	 * @param  array $values                 The plugin's options.
	 * @param  bool  $trigger_css_generation Trigger the CSS generation process upon saving or not.
	 * @return array Info about the saved options and the generated CSS files.
	 * @throws Invalid_Form_Values If the form values are not valid.
	 * @throws Insufficient_Permissions If the user does not have sufficient permissions to save the plugin settings.
	 */
	public function save_settings( $values, $trigger_css_generation = true ) {
		if ( ! is_array( $values ) || empty( $values ) ) {
			Logger::log( sprintf( '%s: received empty or not array of values to save - exiting early', __METHOD__ ) );
			return;
		}
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		$current_user_ID = get_current_user_id();
		// TODO: remove the following log line when we're done debugging.
		Logger::log( sprintf( '%s: saving settings - user ID %d - doing cron is %s', __METHOD__, $current_user_ID, $doing_cron ? 'true' : 'false' ) );
		if ( ! current_user_can( self::CAPABILITY ) && ! $doing_cron ) {
			throw new Insufficient_Permissions(
				sprintf(
					'The current user (ID=%d) does not have sufficient permissions to save the plugin settings. Make sure to save the settings with a user that has the %s capability.',
					$current_user_ID,
					self::CAPABILITY
				)
			);
		}
		$timer = new Timer();
		$return_info = array(
			'has_changed' => false,
			'generated_files' => array(),
			'generated_files_number' => 0,
		);
		$ui = new UI();
		$allowed_variables = $ui->get_all_settings();
		$sanitized_values = array();
		$errors = array();
		Logger::log( sprintf( '%s: triggering automaticcss_settings_save', __METHOD__ ) );
		do_action( 'automaticcss_settings_before_save', $values );
		// STEP: validate the form values and get the sanitized values.
		Logger::log( sprintf( '%s: allowed variables are %s', __METHOD__, print_r( $allowed_variables, true ) ), Logger::LOG_LEVEL_INFO );
		foreach ( $allowed_variables as $var_id => $var_options ) {
			// This makes it so that we ignore non allowed variables coming from the form (i.e. variables not in our config file).
			Logger::log( sprintf( '%s: checking variable %s', __METHOD__, $var_id ), Logger::LOG_LEVEL_INFO );
			$default_value = isset( $var_options['default'] ) ? $var_options['default'] : null;
			try {
				$sanitized_values[ $var_id ] = $this->get_validated_setting( $var_id, $values, $var_options, $default_value );
			} catch ( Invalid_Variable $e ) {
				$errors[ $var_id ] = $e->getMessage();
			}
		}
		// STEP: if there are errors, throw an exception.
		if ( ! empty( $errors ) ) {
			Logger::log( sprintf( "%s: errors found while saving settings:\n%s", __METHOD__, print_r( $errors, true ) ), Logger::LOG_LEVEL_ERROR );
			$error_message = 'The settings you tried to save contain errors. Make sure to fix them in the ACSS settings page and save again.';
			throw new Invalid_Form_Values( $error_message, $errors );
		}
		// STEP: save the sanitized values to the database.
		Logger::log( sprintf( "%s: saving these variables to the database:\n%s", __METHOD__, print_r( $sanitized_values, true ) ), Logger::LOG_LEVEL_NOTICE );
		/**
		 * We used to trigger save_vars only if the vars had changed, but the SCSS might have too.
		 * So now we may trigger CSS generation even if the vars haven't changed.
		 *
		 * @since 2.7.0
		 */
		$return_info['has_changed'] = update_option( self::ACSS_SETTINGS_OPTION, $sanitized_values );
		$this->plugin_wp_options = $sanitized_values;
		do_action( 'automaticcss_settings_after_save', $sanitized_values );
		// STEP: if the settings have changed and CSS generation is enabled, regenerate the CSS.
		if ( $trigger_css_generation ) {
			$return_info['generated_files'] = CSS_Engine::get_instance()->generate_all_css_files( $sanitized_values );
			$return_info['generated_files_number'] = count( $return_info['generated_files'] );
		}
		do_action( 'automaticcss_settings_after_regeneration', $sanitized_values );
		Logger::log(
			sprintf(
				'%s: done (saved settings: %b; regenerated CSS files: %s) in %s seconds',
				__METHOD__,
				$return_info['has_changed'],
				print_r( implode( ', ', $return_info['generated_files'] ), true ),
				$timer->get_time()
			)
		);
		return $return_info;
	}

	/**
	 * Validate a variable based on its type and value and return a sanitized value.
	 *
	 * @param  string $var_id      Variable's ID.
	 * @param  array  $all_values  All variables' values.
	 * @param  array  $var_options Variable's options.
	 * @param  mixed  $default_value The default value for the variable.
	 * @return mixed
	 * @throws Invalid_Variable Exception if the variable is invalid.
	 */
	private function get_validated_setting( $var_id, $all_values, $var_options, $default_value ) {
		$default_value = Flag::is_on( 'ACSS_FLAG_ADD_DEFAULTS_TO_SAVE_PROCESS' ) ? $default_value : null;
		$var_value = isset( $all_values[ $var_id ] ) ? $all_values[ $var_id ] : $default_value;
		// TODO: remove the ACSS_FLAG_BACKEND_VALIDATION flag when the code is stable.
		if ( ! Flag::is_on( 'ACSS_FLAG_BACKEND_VALIDATION' ) ) {
			return $var_value;
		}
		$type = isset( $var_options['type'] ) ? $var_options['type'] : null;
		if ( null === $type ) {
			$message = sprintf( '%s has no type defined.', $var_id );
			self::log_validation_error( $var_id, $message );
			throw new Invalid_Variable( $message );
		}
		// STEP: perform a basic sanitization on the form's field.
		$var_value = sanitize_text_field( $var_value );
		// STEP: check that the value is not empty, if required.
		$required = self::is_required( $var_id, $var_value, $var_options, $all_values );
		if ( ! $required && '' === $var_value ) {
			// nothing else to check.
			self::log_validation_error( $var_id, 'is not required and is empty, skipping its validation', Logger::LOG_LEVEL_INFO );
			return $var_value;
		} else if ( $required && '' === $var_value ) {
			self::log_validation_error( $var_id, 'cannot be empty.' );
			throw new Invalid_Variable( sprintf( '%s: cannot be empty.', $var_id ) );
		}
		// STEP: validate the value based on the type.
		$validation = array_key_exists( 'validation', $var_options ) ? $var_options['validation'] : array();
		switch ( $type ) {
			case 'text':
			case 'textarea':
			case 'codebox':
			case 'clone':
				break;
			case 'number':
			case 'px':
			case 'rem':
			case 'percent':
				// STEP: check that the value is a number.
				if ( ! is_numeric( $var_value ) ) {
					$message = sprintf( '%s: %s is not a number.', $var_id, $var_value );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				// STEP: convert it to the proper type.
				$var_value = strpos( $var_value, '.' ) !== false ? intval( $var_value ) : floatval( $var_value );
				// STEP: check that the value is within the allowed range.
				$min = isset( $validation['min'] ) ? $validation['min'] : null;
				$max = isset( $validation['max'] ) ? $validation['max'] : null;
				if ( null !== $min && $var_value < $min ) {
					$message = sprintf( '%s: %s is smaller than the minimum allowed value of %s.', $var_id, $var_value, $min );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				if ( null !== $max && $var_value > $max ) {
					$message = sprintf( '%s: %s is greater than the maximum allowed value of %s.', $var_id, $var_value, $max );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				break;
			case 'color':
				// STEP: check that the value is a hex color.
				if ( ! $var_value || '' === $var_value || ! preg_match( '/^#[a-f0-9]{6}$/i', $var_value ) ) {
					$message = sprintf( '%s: %s is not a valid hex color.', $var_id, $var_value );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				break;
			case 'select':
				// STEP: convert the value to the proper type (if it's a string, it stays that way).
				$var_value = self::get_converted_value( $var_value );
				// STEP: check if the value is in the list of allowed values.
				$options = isset( $var_options['options'] ) ? $var_options['options'] : null;
				if ( null === $options ) {
					$message = sprintf( '%s has no options defined.', $var_id );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				if ( ! in_array( $var_value, $options ) ) {
					$message = sprintf( '%s: %s is not a valid option.', $var_id, $var_value );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				break;
			case 'toggle':
				// STEP: check that the value is either 'on' or 'off'.
				if ( 'on' !== $var_value && 'off' !== $var_value ) {
					$message = sprintf( '%s: %s is not a valid toggle value.', $var_id, $var_value );
					self::log_validation_error( $var_id, $message );
					throw new Invalid_Variable( $message );
				}
				break;
		}
		// STEP: return the validated and sanitized value.
		return $var_value;
	}

	/**
	 * Check weather a variable is required based on its settings and possibly other variables' values.
	 *
	 * @param  string $var_id      Variable's ID.
	 * @param  mixed  $var_value   Variable's value.
	 * @param  array  $var_options Variable's options.
	 * @param  array  $all_values  All variables' values.
	 * @return boolean
	 */
	private static function is_required( $var_id, $var_value, $var_options, $all_values ) {
		// STEP: check if it has a default value.
		$validation = $var_options['validation'] ?? array();
		// Any input that doesn't have a "required" property is required.
		$required_by_base_validation = isset( $validation['required'] ) ? (bool) $validation['required'] : true;
		// STEP: check if another field requires this field.
		$required_by_condition = false;
		if ( ! empty( $var_options['displayWhen'] ) ) {
			/**
			 * Possible syntax for displayWhen:
			 * $var_options['displayWhen'] = 'setting_name' -> the field is required when setting_name is 'on'.
			 * $var_options['displayWhen'] = array( 'setting_name', 'value' ) -> the field is required when setting_name is 'value'.
			 * $var_options['displayWhen'] = array( array( 'setting_name', 'value' ), array( 'setting_name2', 'value2' ) ) -> the field is required when setting_name is 'value' AND setting_name2 is 'value2'.
			 *
			 * We'll reduce all of these to the last case, so that we can handle them all the same way.
			 *
			 * Tests (base "require"):
			 * - root-font-size: true
			 * - box-shadow-1-name: false
			 * -
			 * Tests (simple "displayWhen")
			 * - breakpoint-xxl if option-breakpoint-xxl is on: true
			 * - breakpoint-xxl if option-breakpoint-xxl is off: false
			 * - primary-dark-h if option-primary-clr is on: true
			 * - primary-dark-h if option-primary-clr is off: false
			 * - primary-dark-h-alt if option-primary-clr-alt is on: true
			 * - primary-dark-h-alt if option-primary-clr-alt is off: false
			 *
			 * Tests (multiple "displayWhen")
			 * - primary-medium-h if option-primary-clr is on AND option-medium-shade is on: true
			 * - primary-medium-h if option-primary-clr is off OR option-medium-shade is off: false
			 */
			$is_just_setting_name = is_string( $var_options['displayWhen'] );
			$is_multiple_conditions = is_array( $var_options['displayWhen'] ) && is_array( $var_options['displayWhen'][0] );
			// STEP: if it's just a string, set 'on' as the condition's value.
			if ( $is_just_setting_name ) {
				$var_options['displayWhen'] = array( $var_options['displayWhen'], 'on' );
			}
			// STEP: if it's just one condition, set it as the only condition in an array.
			if ( ! $is_multiple_conditions ) {
				$var_options['displayWhen'] = array( $var_options['displayWhen'] );
			}
			// STEP: determine if the field is required based on the condition.
			$required_by_condition = true; // 'AND' logic: start with true and set to false if any condition is not met.
			foreach ( $var_options['displayWhen'] as $condition ) {
				if ( count( $condition ) !== 2 || ! isset( $condition[0] ) || ! isset( $condition[1] ) ) {
					// Invalid condition.
					Logger::log( sprintf( '%s: invalid condition for %s', __METHOD__, $var_id ), Logger::LOG_LEVEL_ERROR );
					continue;
				}
				$condition_field = $condition[0];
				$condition_required_value = self::get_converted_value( $condition[1] );
				$condition_actual_value = isset( $all_values[ $condition_field ] ) ? self::get_converted_value( $all_values[ $condition_field ] ) : null;
				if ( $condition_actual_value !== $condition_required_value ) {
					$required_by_condition = false;
				}
			}
		}
		// STEP: return the result.
		$required = $required_by_base_validation || $required_by_condition;
		Logger::log(
			sprintf(
				'%s: %s is%s required (required = %s, required_by_condition = %s)',
				__METHOD__,
				$var_id,
				$required ? '' : ' not',
				$required_by_base_validation ? 'true' : 'false',
				$required_by_condition ? 'true' : 'false'
			),
			Logger::LOG_LEVEL_NOTICE
		);
		return $required;
	}

	/**
	 * Log a validation error.
	 *
	 * @param string $var_id      The variable ID.
	 * @param string $error_message The error message.
	 * @param string $log_level The log level.
	 * @return void
	 */
	private static function log_validation_error( $var_id, $error_message, $log_level = Logger::LOG_LEVEL_ERROR ) {
		Logger::log( sprintf( '%s: [%s] %s', __METHOD__, $var_id, $error_message ), $log_level );
	}

	/**
	 * Convert the value based on the type. Supports int, float and string.
	 *
	 * @param  mixed $value The input value.
	 * @return mixed
	 */
	private static function get_converted_value( $value ) {
		if ( self::is_int( $value ) ) {
			return intval( $value );
		} else if ( self::is_float( $value ) ) {
			return floatval( $value );
		}
		return $value;
	}

	/**
	 * Is this value an integer?
	 *
	 * @param  mixed $value The value to check.
	 * @return boolean
	 */
	private static function is_int( $value ) {
		return( ctype_digit( strval( $value ) ) );
	}

	/**
	 * Is this value a float?
	 *
	 * @param  mixed $value The value to check.
	 * @return boolean
	 */
	private static function is_float( $value ) {
		return (string) (float) $value === $value;
	}

	/**
	 * Update database fields and values upon plugin upgrade.
	 *
	 * @param  array  $values           The database values.
	 * @param  string $current_version  The version of the plugin we're upgrading to.
	 * @param  string $previous_version The version of the plugin we're upgrading from.
	 * @return array The (maybe modified) database values.
	 */
	public function upgrade_database( $values, $current_version, $previous_version ) {
		Logger::log( sprintf( '%s: upgrading from %s to %s', __METHOD__, $previous_version, $current_version ) );
		if ( ! is_array( $values ) || empty( $values ) ) {
			Logger::log( sprintf( '%s: received empty or not array of values to upgrade - exiting early', __METHOD__ ) );
			return;
		}
		if ( empty( $current_version ) || empty( $previous_version ) ) {
			Logger::log( sprintf( '%s: received empty current or previous version - exiting early', __METHOD__ ) );
			return;
		}
		if ( version_compare( $previous_version, '2.0.0', '<' ) && version_compare( $current_version, '2.0.0', '>=' ) ) {
			Logger::log( sprintf( '%s: running pre 2.0 -> 2.0 upgrade', __METHOD__ ) );
			// Handle section-padding-x -> section-padding-x-max conversion.
			if ( array_key_exists( 'section-padding-x', $values ) ) {
				Logger::log( sprintf( '%s: converting section-padding-x to section-padding-x-max', __METHOD__ ) );
				$values['section-padding-x-max'] = $values['section-padding-x'];
				unset( $values['section-padding-x'] );
			}
			// Handle primary-hover-var -> primary-hover-l conversion.
			$color_types = array( 'action', 'primary', 'secondary', 'base', 'accent', 'shade' );
			$color_variations = array( 'hover', 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark' );
			foreach ( $color_types as $color_type ) {
				foreach ( $color_variations as $color_variation ) {
					$old_var = $color_type . '-' . $color_variation . '-val';
					if ( array_key_exists( $old_var, $values ) ) {
						$new_var = $color_type . '-' . $color_variation . '-l';
						Logger::log(
							sprintf(
								'%s: converting %s to %s with value %s',
								__METHOD__,
								$old_var,
								$new_var,
								$values[ $old_var ]
							)
						);
						$values[ $new_var ] = $values[ $old_var ];
						unset( $values[ $old_var ] );
					}
				}
			}
			// Handle text overrides REM -> px conversion.
			$text_size_variations = array( 'xs', 's', 'm', 'l', 'xl', 'xxl' );
			$text_size_min_max_variations = array( 'min', 'max' );
			$root_font_size = array_key_exists( 'root-font-size', $values ) ? floatval( $values['root-font-size'] ) : 62.5;
			foreach ( $text_size_variations as $text_size_variation ) {
				foreach ( $text_size_min_max_variations as $min_max_variation ) {
					$text_size_var = 'text-' . $text_size_variation . '-' . $min_max_variation;
					// When these values were converted from REM to PX, they were divided by 10 and then adjusted for root-font-size.
					// So: new value = old value * 10 * root-font-size / 62.5.
					if ( array_key_exists( $text_size_var, $values ) && '' !== $values[ $text_size_var ] ) { // accept 0 though.
						$text_size_old_value = $values[ $text_size_var ];
						$text_size_new_value = $text_size_old_value * 10 * $root_font_size / 62.5;
						Logger::log( sprintf( '%s: converting %s from %s to %s', __METHOD__, $text_size_var, $text_size_old_value, $text_size_new_value ) );
						$values[ $text_size_var ] = $text_size_new_value;
					}
				}
			}
		}
		if ( version_compare( $previous_version, '2.2.0.2', '<=' ) && version_compare( $current_version, '2.2.0.2', '>' ) ) {
			Logger::log( sprintf( '%s: running post 2.2.0.2 upgrades', __METHOD__ ) );
			// Add new shade hue variables.
			$color_types = array( 'action', 'primary', 'secondary', 'base', 'accent', 'shade' );
			$color_variations = array( 'hover', 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark' );
			foreach ( $color_types as $color_type ) {
				foreach ( $color_variations as $color_variation ) {
					$hue_key = $color_type . '-' . $color_variation . '-h';
					$color_key = 'color-' . $color_type;
					if ( ! array_key_exists( $hue_key, $values ) && array_key_exists( $color_key, $values ) ) {
						$hue_value = ( new \Automatic_CSS\Helpers\Color( $values[ $color_key ] ) )->h;
						$values[ $hue_key ] = $hue_value;
						Logger::log( sprintf( '%s: adding %s with value %s', __METHOD__, $hue_key, $hue_value ) );
					}
				}
			}
		}
		if ( version_compare( $previous_version, '2.4', '<' ) && version_compare( $current_version, '2.4', '>=' ) ) {
			Logger::log( sprintf( '%s: running post 2.4.0 upgrades', __METHOD__ ) );
			if ( ( ! array_key_exists( 'breakpoint-xl', $values ) || '' === $values['breakpoint-xl'] ) && array_key_exists( 'vp-max', $values ) ) {
				Logger::log( sprintf( '%s: breakpoint-xl is now a variable, taking the value of vp-max', __METHOD__ ) );
				$values['breakpoint-xl'] = $values['vp-max'];
			}
		}
		if ( version_compare( $previous_version, '2.5', '<' ) && version_compare( $current_version, '2.5', '>=' ) ) {
			Logger::log( sprintf( '%s: running post 2.5.0 upgrades', __METHOD__ ) );
			// Migrate old form styling variables.
			$migration = array(
				// old variable => new variable.
				'f-light-label-size-min' => 'f-label-size-min',
				'f-light-label-size-max' => 'f-label-size-max',
				'f-light-label-font-weight' => 'f-label-font-weight',
				'f-light-label-padding-x' => 'f-label-padding-x',
				'f-light-label-padding-y' => 'f-label-padding-y',
				'f-light-label-margin-bottom' => 'f-label-margin-bottom',
				'f-light-label-text-transform' => 'f-label-text-transform',
				'f-light-legend-text-weight' => 'f-legend-text-weight',
				'f-light-legend-size-min' => 'f-legend-size-min',
				'f-light-legend-size-max' => 'f-legend-size-max',
				'f-light-legend-margin-bottom' => 'f-legend-margin-bottom',
				'f-light-legend-line-height' => 'f-legend-line-height',
				'f-light-help-size-min' => 'f-help-size-min',
				'f-light-help-size-max' => 'f-help-size-max',
				'f-light-help-line-height' => 'f-help-line-height',
				'f-light-field-margin-bottom' => 'f-field-margin-bottom',
				'f-light-fieldset-margin-bottom' => 'f-fieldset-margin-bottom',
				'f-light-grid-gutter' => 'f-grid-gutter',
				'f-light-input-border-top-size' => 'f-input-border-top-size',
				'f-light-input-border-right-size' => 'f-input-border-right-size',
				'f-light-input-border-bottom-size' => 'f-input-border-bottom-size',
				'f-light-input-border-left-size' => 'f-input-border-left-size',
				'f-light-input-border-radius' => 'f-input-border-radius',
				'f-light-input-text-size-min' => 'f-input-text-size-min',
				'f-light-input-text-size-max' => 'f-input-text-size-max',
				'f-light-input-font-weight' => 'f-input-font-weight',
				'f-light-input-height' => 'f-input-height',
				'f-light-input-padding-x' => 'f-input-padding-x',
				'f-light-btn-border-style' => 'f-btn-border-style',
				'f-light-btn-margin-top' => 'f-btn-margin-top',
				'f-light-btn-padding-y' => 'f-btn-padding-y',
				'f-light-btn-padding-x' => 'f-btn-padding-x',
				'f-light-btn-border-width' => 'f-btn-border-width',
				'f-light-btn-border-radius' => 'f-btn-border-radius',
				'f-light-btn-text-size-min' => 'f-btn-text-size-min',
				'f-light-btn-text-size-max' => 'f-btn-text-size-max',
				'f-light-btn-font-weight' => 'f-btn-font-weight',
				'f-light-btn-line-height' => 'f-btn-line-height',
				'f-light-btn-text-transform' => 'f-btn-text-transform',
				'f-light-btn-text-decoration' => 'f-btn-text-decoration',
				'f-light-option-label-font-weight' => 'f-option-label-font-weight',
				'f-light-option-label-font-size-min' => 'f-option-label-font-size-min',
				'f-light-option-label-font-size-max' => 'f-option-label-font-size-max',
				'f-light-progress-height' => 'f-progress-height',
				'f-light-tab-border-style' => 'f-tab-border-style',
				'f-light-tab-padding-y' => 'f-tab-padding-y',
				'f-light-tab-padding-x' => 'f-tab-padding-x',
				'f-light-tab-margin-x' => 'f-tab-margin-x',
				'f-light-tab-border-size' => 'f-tab-border-size',
				'f-light-tab-active-border-color' => 'f-dark-tab-border-color',
				'f-light-tab-border-radius' => 'f-tab-border-radius',
				'f-light-tab-text-size-min' => 'f-tab-text-size-min',
				'f-light-tab-text-size-max' => 'f-tab-text-size-max',
				'f-light-tab-text-weight' => 'f-tab-text-weight',
				'f-light-tab-active-text-weight' => 'f-tab-active-text-weight',
				'f-light-tab-text-line-height' => 'f-tab-text-line-height',
				'f-light-tab-text-transform' => 'f-tab-text-transform',
				'f-light-tab-text-align' => 'f-tab-text-align',
				'f-light-tab-text-decoration' => 'f-tab-text-decoration',
				'f-light-tab-active-border-bottom-size' => 'f-tab-active-border-bottom-size',
				'f-light-tab-group-padding-y' => 'f-tab-group-padding-y',
				'f-light-tab-group-padding-x' => 'f-tab-group-padding-x',
				'f-light-tab-group-border-bottom-size' => 'f-tab-group-border-bottom-size',
				'f-light-tab-group-border-bottom-style' => 'f-tab-group-border-bottom-style',
				'f-light-tab-group-margin-bottom' => 'f-tab-group-margin-bottom',
			);
			foreach ( $migration as $old_var_name => $new_var_name ) {
				if ( array_key_exists( $old_var_name, $values ) ) {
					Logger::log( sprintf( '%s: converting %s to %s', __METHOD__, $old_var_name, $new_var_name ) );
					$values[ $new_var_name ] = $values[ $old_var_name ];
					unset( $values[ $old_var_name ] );
				}
			}
		}
		if ( version_compare( $previous_version, '2.6', '<' ) && version_compare( $current_version, '2.6', '>=' ) ) {
			Logger::log( sprintf( '%s: running post 2.6.0 upgrades', __METHOD__ ) );
			$new_settings = array(
				// setting name => default value.
				'f-light-tab-inactive-text-color' => 'var(--shade-dark-trans-80)',
				'f-dark-tab-inactive-text-color' => 'var(--shade-light-trans-80)'
			);
			foreach ( $new_settings as $setting_name => $default_value ) {
				if ( ! array_key_exists( $setting_name, $values ) ) {
					Logger::log( sprintf( '%s: adding %s with default value %s', __METHOD__, $setting_name, $default_value ) );
					$values[ $setting_name ] = $default_value;
				}
			}
		}
		if ( version_compare( $previous_version, '2.7', '<' ) && version_compare( $current_version, '2.7', '>=' ) ) {
			Logger::log( sprintf( '%s: running 2.7.0 upgrades', __METHOD__ ) );
			$settings_to_migrate = array(
				'btn-weight' => 'btn-font-weight',
				'btn-text-style' => 'btn-font-style',
				'btn-width' => 'btn-min-width',
				'btn-pad-y' => 'btn-padding-block',
				'btn-pad-x' => 'btn-padding-inline',
				'btn-border-size' => 'btn-border-width',
				'outline-btn-border-size' => 'btn-outline-border-width',
				'btn-radius' => 'btn-border-radius',
			);
			foreach ( $settings_to_migrate as $old_var_name => $new_var_name ) {
				if ( array_key_exists( $old_var_name, $values ) ) {
					Logger::log( sprintf( '%s: converting %s to %s', __METHOD__, $old_var_name, $new_var_name ) );
					$values[ $new_var_name ] = $values[ $old_var_name ];
					unset( $values[ $old_var_name ] );
				}
			}
		}
		if ( version_compare( $previous_version, '2.8', '<' ) && version_compare( $current_version, '2.8', '>=' ) ) {
			Logger::log( sprintf( '%s: running 2.8.0 upgrades', __METHOD__ ) );
			$settings_to_migrate = array(
				'fr-bg-light' => 'bg-ultra-light',
				'fr-bg-dark' => 'bg-ultra-dark',
				'fr-text-light' => 'text-light',
				'fr-text-dark' => 'text-dark',
			);
			foreach ( $settings_to_migrate as $old_var_name => $new_var_name ) {
				if ( array_key_exists( $old_var_name, $values ) ) {
					Logger::log( sprintf( '%s: converting %s to %s', __METHOD__, $old_var_name, $new_var_name ) );
					$values[ $new_var_name ] = $values[ $old_var_name ];
					unset( $values[ $old_var_name ] );
				}
			}
		}
		if ( version_compare( $previous_version, '3.0', '<' ) && version_compare( $current_version, '3.0', '>=' ) ) {
			Logger::log( sprintf( '%s: running 3.0.0 upgrades', __METHOD__ ) );
			self::migrate_to_3_0( $values );
		}
		if ( version_compare( $previous_version, '3.0.8', '<' ) && version_compare( $current_version, '3.0.8', '>=' ) ) {
			Logger::log( sprintf( '%s: running 3.0.8 upgrades', __METHOD__ ) );
			// This is when we decided to set the default value for every null setting.
			// It inadvertently caused some settings, which should have stayed off, to turn themselves on.
			$settings_to_turn_off = array(
				'option-auto-radius',
			);
			foreach ( $settings_to_turn_off as $setting ) {
				if ( array_key_exists( $setting, $values ) && null === $values[ $setting ] ) {
					Logger::log( sprintf( '%s: turning off %s', __METHOD__, $setting ) );
					$values[ $setting ] = 'off';
				}
			}
		}
		if ( version_compare( $previous_version, '3.1.3', '<' ) && version_compare( $current_version, '3.1.3', '>=' ) ) {
			Logger::log( sprintf( '%s: running 3.1.3 upgrades', __METHOD__ ) );
			// set the following settings to empty on existing installs:
			// bg-[ultra-light|light|dark|ultra-dark]-link
			// bg-[ultra-light|light|dark|ultra-dark]-link-hover
			// bg-[ultra-light|light|dark|ultra-dark]-button
			// Note: these settings were introduced in 3.1.0. They should have had empty values for existing installs.
			// We didn't catch the lack of a migration until 3.1.3.
			// So people who already upgraded to 3.1.0, 3.1.1 or 3.1.2 have defaults we don't want to mess with.
			$settings_to_empty = array(
				'bg-ultra-light-link',
				'bg-ultra-light-link-hover',
				'bg-ultra-light-button',
				'bg-light-link',
				'bg-light-link-hover',
				'bg-light-button',
				'bg-dark-link',
				'bg-dark-link-hover',
				'bg-dark-button',
				'bg-ultra-dark-link',
				'bg-ultra-dark-link-hover',
				'bg-ultra-dark-button',
			);
			foreach ( $settings_to_empty as $setting ) {
				if ( ! array_key_exists( $setting, $values ) ) {
					Logger::log( sprintf( '%s: setting %s to empty', __METHOD__, $setting ) );
					$values[ $setting ] = '';
				}
			}
		}
		return $values;
	}

	/**
	 * Migrate variables from one version to another.
	 *
	 * @param array $values The setting values as an associative array of setting => value passed by reference.
	 * @return array The (maybe modified) setting values.
	 */
	public static function migrate_to_3_0( &$values ) {
		$settings_to_migrate = array(
			'option-cetering' => 'option-centering',
			'option-paragraph-fix' => 'option-smart-spacing',
			'default-paragraph-spacing' => 'paragraph-spacing',
			'default-list-spacing' => 'list-spacing',
			'default-list-item-spacing' => 'list-item-spacing',
			'default-heading-spacing' => 'heading-spacing',
			'btn-transition-duration' => 'transition-duration',
			'option-bricks-gallery-thumb-size' => 'option-bricks-template-gallery-enhancements',
			'heading-line-length' => 'h1-max-width',
			'h2-line-length' => 'h2-max-width',
			'h3-line-length' => 'h3-max-width',
			'h4-line-length' => 'h4-max-width',
			'h5-line-length' => 'h5-max-width',
			'h6-line-length' => 'h6-max-width',
			'text-xxl-lh' => 'text-xxl-line-height',
			'text-xl-lh' => 'text-xl-line-height',
			'text-l-lh' => 'text-l-line-height',
			'base-text-lh' => 'text-m-line-height',
			'text-s-lh' => 'text-s-line-height',
			'text-xs-lh' => 'text-xs-line-height',
			'heading-line-length' => 'heading-max-width',
			'h1-lh' => 'h1-line-height',
			'h2-lh' => 'h2-line-height',
			'h3-lh' => 'h3-line-height',
			'h4-lh' => 'h4-line-height',
			'h5-lh' => 'h5-line-height',
			'h6-lh' => 'h6-line-height',
			'text-xxl-length' => 'text-xxl-max-width',
			'text-xl-length' => 'text-xl-max-width',
			'text-l-length' => 'text-l-max-width',
			'text-m-length' => 'text-m-max-width',
			'text-s-length' => 'text-s-max-width',
			'text-xs-length' => 'text-xs-max-width',
			'text-xxl-lh' => 'text-xxl-line-height',
			'text-xl-lh' => 'text-xl-line-height',
			'text-l-lh' => 'text-l-line-height',
			'text-m-lh' => 'text-m-line-height',
			'text-s-lh' => 'text-s-line-height',
			'text-xs-lh' => 'text-xs-line-height',
			'color-scheme-locked-selectors' => 'colorscheme-locked-selectors',
		);
		foreach ( $settings_to_migrate as $old_var_name => $new_var_name ) {
			if ( array_key_exists( $old_var_name, $values ) ) {
				Logger::log( sprintf( '%s: converting %s to %s', __METHOD__, $old_var_name, $new_var_name ) );
				$values[ $new_var_name ] = $values[ $old_var_name ];
				unset( $values[ $old_var_name ] );
			}
		}
		// Rem conversion settings.
		$rem_convert_settings = array( 'btn-border-width', 'btn-outline-border-width' );
		foreach ( $rem_convert_settings as $setting ) {
			if ( array_key_exists( $setting, $values ) ) {
				$old_value = $values[ $setting ];
				$new_value = $old_value / 10 . 'rem';
				Logger::log( sprintf( '%s: converting %s from %s to %s', __METHOD__, $setting, $old_value, $new_value ) );
				$values[ $setting ] = $new_value;
			}
		}
		// Append "em" settings.
		$em_append_settings = array( 'col-rule-width-l', 'col-rule-width-m', 'col-rule-width-s' );
		foreach ( $em_append_settings as $setting ) {
			if ( array_key_exists( $setting, $values ) ) {
				$old_value = $values[ $setting ];
				$new_value = $old_value . 'em';
				Logger::log( sprintf( '%s: converting %s from %s to %s', __METHOD__, $setting, $old_value, $new_value ) );
				$values[ $setting ] = $new_value;
			}
		}
		// Append "rem" settings.
		$rem_append_settings = array( 'col-width-l', 'col-width-m', 'col-width-s' );
		foreach ( $rem_append_settings as $setting ) {
			if ( array_key_exists( $setting, $values ) ) {
				$old_value = $values[ $setting ];
				$new_value = $old_value . 'rem';
				Logger::log( sprintf( '%s: converting %s from %s to %s', __METHOD__, $setting, $old_value, $new_value ) );
				$values[ $setting ] = $new_value;
			}
		}
		// Lightness conversion settings.
		$lightness_convert_settings = array(
			'action-hover-l',
			'action-hover-l-alt',
			'primary-hover-l',
			'primary-hover-l-alt',
			'secondary-hover-l',
			'secondary-hover-l-alt',
			'accent-hover-l',
			'accent-hover-l-alt',
			'base-hover-l',
			'base-hover-l-alt',
			'shade-hover-l',
			'shade-hover-l-alt',
			'neutral-hover-l',
			'neutral-hover-l-alt',
			'success-hover-l',
			'success-hover-l-alt',
			'danger-hover-l',
			'danger-hover-l-alt',
			'info-hover-l',
			'info-hover-l-alt',
			'warning-hover-l',
			'warning-hover-l-alt',
		);
		foreach ( $lightness_convert_settings as $setting ) {
			// Old hover lightness was a multiplier, by default 1.15.
			// New hover multiplier is a % like every other lightness.
			// To convert, find the color's lightness, multiply it by the old value and append %.
			$color_name = 'color-' . str_replace( '-hover-l', '', $setting );
			if ( array_key_exists( $setting, $values ) && array_key_exists( $color_name, $values ) ) {
				$color = new \Automatic_CSS\Helpers\Color( $values[ $color_name ] );
				$old_value = $values[ $setting ];
				$new_value = $color->l * $old_value; // No need for % sign, it gets added while saving.
				if ( $new_value > 100 ) {
					$new_value = 100;
				} else if ( $new_value < 0 ) {
					$new_value = 0;
				}
				Logger::log( sprintf( '%s: converting %s from %s to %s', __METHOD__, $setting, $old_value, $new_value ) );
				$values[ $setting ] = $new_value;
			}
		}
		// Semi-light and semi-dark shades.
		$all_colors = array(
			'action',
			'primary',
			'secondary',
			'base',
			'accent',
			'shade',
			'neutral',
			'success',
			'danger',
			'info',
			'warning',
		);
		$semantic_colors = array( 'success', 'danger', 'info', 'warning' );
		$ui = new UI();
		$global_ui_settings = $ui->get_globals();
		$global_shade_settings = $global_ui_settings['color']['shades'] ?? array();
		foreach ( $global_shade_settings as $shade ) {
			if ( 'semi-light' === $shade['name'] ) {
				$semi_light_shade_lightness = $shade['l'];
			}
			if ( 'semi-dark' === $shade['name'] ) {
				$semi_dark_shade_lightness = $shade['l'];
			}
			if ( 'ultra-light' === $shade['name'] ) {
				$ultra_light_shade_lightness = $shade['l'];
			}
			if ( 'ultra-dark' === $shade['name'] ) {
				$ultra_dark_shade_lightness = $shade['l'];
			}
			if ( 'medium' === $shade['name'] ) {
				$medium_shade_lightness = $shade['l'];
			}
		}
		foreach ( $all_colors as $color_name ) {
			// Main color.
			$color_setting = 'color-' . $color_name;
			if ( array_key_exists( $color_setting, $values ) ) {
				$color_obj = new \Automatic_CSS\Helpers\Color( $values[ $color_setting ] );
				// Semi light.
				$values[ $color_name . '-semi-light-h' ] = $color_obj->h;
				$values[ $color_name . '-semi-light-s' ] = $color_obj->s;
				$values[ $color_name . '-semi-light-l' ] = $semi_light_shade_lightness;
				// Semi dark.
				$values[ $color_name . '-semi-dark-h' ] = $color_obj->h;
				$values[ $color_name . '-semi-dark-s' ] = $color_obj->s;
				$values[ $color_name . '-semi-dark-l' ] = $semi_dark_shade_lightness;
				Logger::log(
					sprintf(
						'%s: adding %s with H %d S %d and L %d',
						__METHOD__,
						$color_name . '-semi-light',
						$color_obj->h,
						$color_obj->s,
						$semi_light_shade_lightness
					)
				);
				Logger::log(
					sprintf(
						'%s: adding %s with H %d S %d and L %d',
						__METHOD__,
						$color_name . '-semi-dark',
						$color_obj->h,
						$color_obj->s,
						$semi_dark_shade_lightness
					)
				);
				if ( in_array( $color_name, $semantic_colors, true ) ) {
					// Semantic colors now have the medium, ultra-light and ultra-dark shades.
					// Add the medium shade.
					$values[ $color_name . '-medium-h' ] = $color_obj->h;
					$values[ $color_name . '-medium-s' ] = $color_obj->s;
					$values[ $color_name . '-medium-l' ] = $medium_shade_lightness;
					// Add the ultra-light shade.
					$values[ $color_name . '-ultra-light-h' ] = $color_obj->h;
					$values[ $color_name . '-ultra-light-s' ] = $color_obj->s;
					$values[ $color_name . '-ultra-light-l' ] = $ultra_light_shade_lightness;
					// Add the ultra-dark shade.
					$values[ $color_name . '-ultra-dark-h' ] = $color_obj->h;
					$values[ $color_name . '-ultra-dark-s' ] = $color_obj->s;
					$values[ $color_name . '-ultra-dark-l' ] = $ultra_dark_shade_lightness;
				}
			}
			// Alt color.
			$alt_color_setting = 'color-' . $color_name . '-alt';
			if ( array_key_exists( $alt_color_setting, $values ) ) {
				$color_obj = new \Automatic_CSS\Helpers\Color( $values[ $alt_color_setting ] );
				// Semi light.
				$values[ $color_name . '-semi-light-h-alt' ] = $color_obj->h;
				$values[ $color_name . '-semi-light-s-alt' ] = $color_obj->s;
				$values[ $color_name . '-semi-light-l-alt' ] = $semi_light_shade_lightness;
				// Semi dark.
				$values[ $color_name . '-semi-dark-h-alt' ] = $color_obj->h;
				$values[ $color_name . '-semi-dark-s-alt' ] = $color_obj->s;
				$values[ $color_name . '-semi-dark-l-alt' ] = $semi_dark_shade_lightness;
				Logger::log(
					sprintf(
						'%s: adding %s with H %d S %d and L %d',
						__METHOD__,
						$color_name . '-semi-light-alt',
						$color_obj->h,
						$color_obj->s,
						$semi_light_shade_lightness
					)
				);
				Logger::log(
					sprintf(
						'%s: adding %s with H %d S %d and L %d',
						__METHOD__,
						$color_name . '-semi-dark-alt',
						$color_obj->h,
						$color_obj->s,
						$semi_dark_shade_lightness
					)
				);
				if ( in_array( $color_name, $semantic_colors, true ) ) {
					// Semantic colors now have the medium, ultra-light and ultra-dark shades.
					// Add the medium shade.
					$values[ $color_name . '-medium-h-alt' ] = $color_obj->h;
					$values[ $color_name . '-medium-s-alt' ] = $color_obj->s;
					$values[ $color_name . '-medium-l-alt' ] = $medium_shade_lightness;
					// Add the ultra-light shade.
					$values[ $color_name . '-ultra-light-h-alt' ] = $color_obj->h;
					$values[ $color_name . '-ultra-light-s-alt' ] = $color_obj->s;
					$values[ $color_name . '-ultra-light-l-alt' ] = $ultra_light_shade_lightness;
					// Add the ultra-dark shade.
					$values[ $color_name . '-ultra-dark-h-alt' ] = $color_obj->h;
					$values[ $color_name . '-ultra-dark-s-alt' ] = $color_obj->s;
					$values[ $color_name . '-ultra-dark-l-alt' ] = $ultra_dark_shade_lightness;
				}
			}
		}
		// Semantic colors should follow the 'option-contextual-colors' toggle.
		$semantic_color_toggles = array(
			'option-success-clr',
			'option-danger-clr',
			'option-warning-clr',
			'option-info-clr',
		);
		$contextual_colors_value = array_key_exists( 'option-contextual-colors', $values ) ? $values['option-contextual-colors'] : 'off';
		foreach ( $semantic_color_toggles as $semantic_color_toggle ) {
			$values[ $semantic_color_toggle ] = $contextual_colors_value;
			Logger::log( sprintf( '%s: setting %s to %s', __METHOD__, $semantic_color_toggle, $contextual_colors_value ) );
		}
		// Settings that need to be on by default, no matter what, in new installs.
		$default_on_settings = array(
			'option-radius-sizes',
			'option-medium-shade',
			'option-comp-colors',
			'option-auto-object-fit',
		);
		foreach ( $default_on_settings as $default_on_setting ) {
			$values[ $default_on_setting ] = 'on';
			Logger::log( sprintf( '%s: setting %s to on', __METHOD__, $default_on_setting ) );
		}
		$default_off_settings = array(
			'option-auto-radius',
		);
		// Settings that need to be off by default, no matter what, in upgraded websites.
		foreach ( $default_off_settings as $default_off_setting ) {
			$values[ $default_off_setting ] = 'off';
			Logger::log( sprintf( '%s: setting %s to off', __METHOD__, $default_off_setting ) );
		}
		// Text-wrap and Heading-text-wrap need to be 'balance' if their 2.x options (which no longer exist) were 'on'.
		$text_wrap_settings = array(
			'option-balance-text' => 'text-wrap',
			'option-balance-headings' => 'heading-text-wrap',
		);
		foreach ( $text_wrap_settings as $old_setting => $new_setting ) {
			$new_value = 'balance';
			if ( array_key_exists( $old_setting, $values ) && 'on' === $values[ $old_setting ] ) {
				$values[ $new_setting ] = $new_value;
				Logger::log( sprintf( '%s: setting %s to %s', __METHOD__, $new_setting, $new_value ) );
			}
		}
		// If old 'option-padding' is 'on', set 'option-deprecated-padding' to 'on'.
		if ( array_key_exists( 'option-padding', $values ) && 'on' === $values['option-padding'] ) {
			$values['option-deprecated-padding'] = 'on';
			Logger::log( sprintf( '%s: setting option-deprecated-padding to on', __METHOD__ ) );
		}
		// Add neutral -trans options, which didn't exist in 2.x.
		$neutral_option_value = array_key_exists( 'option-neutral-clr', $values ) ? $values['option-neutral-clr'] : 'off';
		$neutral_trans_settings = array(
			'option-neutral-main-trans',
			'option-neutral-light-trans',
			'option-neutral-dark-trans',
			'option-neutral-ultra-dark-trans',
		);
		foreach ( $neutral_trans_settings as $neutral_setting ) {
			$values[ $neutral_setting ] = $neutral_option_value;
			Logger::log( sprintf( '%s: setting %s to %s', __METHOD__, $neutral_setting, $neutral_option_value ) );
		}
		// Update the timestamp to force the migration to happen.
		$values['timestamp'] = time();
		return $values;
	}

	/**
	 * Migrate variables from one version to another.
	 * Will execute a swap of old_setting => new_setting if:
	 * the current version is >= $target_version and the previous version is < $target_version (in case of upgrade)
	 * or
	 * the current version is < $target_version and the previous version is >= $target_version (in case of downgrade)
	 *
	 * @param array   $settings The settings to migrate, as an associative array of old_setting => new_setting.
	 * @param array   $values The setting values as an associative array of setting => value passed by reference.
	 * @param string  $target_version The target version for this migration.
	 * @param string  $current_version The version we're migrating to.
	 * @param string  $previous_version The version we're migrating from.
	 * @param boolean $no_downgrade If true, don't migrate downgrades.
	 * @return void
	 */
	private function migrate_variables( $settings, &$values, $target_version, $current_version, $previous_version, $no_downgrade = false ) {
		$operation = null;
		if ( version_compare( $previous_version, $target_version, '<' ) && version_compare( $current_version, $target_version, '>=' ) ) {
			$operation = 'upgrade';
		} else if ( true !== $no_downgrade && version_compare( $current_version, $target_version, '<' ) && version_compare( $previous_version, $target_version, '>=' ) ) {
			$operation = 'downgrade';
			$settings = array_flip( $settings );
		}
		if ( null !== $operation ) {
			// upgrading from $previous_version to $current_version.
			Logger::log( sprintf( '%s: executing migration to %s from %s to %s', __METHOD__, $operation, $previous_version, $current_version ) );
			foreach ( $settings as $old_setting => $new_setting ) {
				if ( array_key_exists( $old_setting, $values ) ) {
					Logger::log( sprintf( '%s: migrating %s to %s', __METHOD__, $old_setting, $new_setting ) );
					// STEP: migrate the value.
					$values[ $new_setting ] = $values[ $old_setting ];
					// STEP: remove the old setting.
					unset( $values[ $old_setting ] );
				}
			}
		}
	}

	/**
	 * In 3.0.1, base-heading-lh and base-text-lh were not wrapped in calc().
	 * This caused an error when saving settings.
	 * If the settings had been committed to the database, this would cause the plugin to no longer activate.
	 *
	 * @return void
	 */
	public static function hotfix_302() {
		$already_run = get_option( 'automatic_css__hotfix_302', false );
		if ( $already_run ) {
			return;
		}
		$settings = get_option( self::ACSS_SETTINGS_OPTION );
		if ( ! is_array( $settings ) ) {
			return;
		}
		$options_to_fix = array( 'base-heading-lh', 'base-text-lh' );
		// If the options to fix are not wrapped in a calc(), wrap them.
		foreach ( $options_to_fix as $option ) {
			if ( isset( $settings[ $option ] ) && false === strpos( $settings[ $option ], 'calc(' ) ) {
				$settings[ $option ] = 'calc(' . $settings[ $option ] . ')';
				update_option( 'automatic_css__hotfix_302', true );
			}
		}
		update_option( self::ACSS_SETTINGS_OPTION, $settings );
	}

	/**
	 * Delete the framework's database option(s).
	 *
	 * @return void
	 * @throws Insufficient_Permissions If the user does not have permission to delete the database options.
	 */
	public function delete_database_options() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			throw new Insufficient_Permissions( 'You do not have permission to delete the database options.' );
		}
		delete_option( self::ACSS_SETTINGS_OPTION );
	}
}
