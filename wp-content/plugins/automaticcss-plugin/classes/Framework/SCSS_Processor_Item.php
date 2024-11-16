<?php
/**
 * Automatic.css SCSS_Processor_Item file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Model\Config\UI;

/**
 * Automatic.css SCSS_Processor_Item class.
 */
class SCSS_Processor_Item {

	/**
	 * The name of the setting.
	 *
	 * @var String
	 */
	private $setting_name;

	/**
	 * The value of the setting.
	 *
	 * @var mixed
	 */
	private $setting_value;

	/**
	 * The type of the setting.
	 *
	 * @var String
	 */
	private $setting_type;

	/**
	 * The unit of the setting.
	 *
	 * @var String
	 */
	private $unit;

	/**
	 * The options for the setting.
	 *
	 * @var Array
	 */
	private $setting_options;

	/**
	 * Initialize the SCSS_Processor_Item class.
	 *
	 * @param string $setting_name The name of the setting.
	 * @param mixed  $value The value of the setting.
	 * @param array  $setting_options The options for the setting.
	 */
	public function __construct( string $setting_name, mixed $value, array $setting_options ) {
		$this->setting_name = $setting_name;
		$this->setting_value = apply_filters( "automaticcss_input_value_{$setting_name}", $value );
		$this->setting_options = $setting_options;
		$this->setting_type = array_key_exists( 'type', $setting_options ) ? $setting_options['type'] : null;
		$this->unit = $this->get_unit();
	}

	/**
	 * Magic getter.
	 *
	 * @param String $name The name of the property to get.
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( array_key_exists( $name, $this->setting_options ) ) {
			return $this->setting_options[ $name ];
		}
	}

	/**
	 * Check if this Item should be skipped.
	 *
	 * @param Array $settings_to_process_after_first_loop The settings to process after the first loop.
	 * @return bool
	 */
	public function should_skip( $settings_to_process_after_first_loop ) {
		$should_skip = false;
		if ( in_array( $this->setting_name, $settings_to_process_after_first_loop ) ) {
			Logger::log( sprintf( '%s: Skipping %s because it depends on other settings', __METHOD__, $this->setting_name ) );
			$should_skip = true;
		}
		$skip_var_generation = array_key_exists( 'skip-css-var', $this->setting_options ) ? (bool) $this->setting_options['skip-css-var'] : false;
		if ( $skip_var_generation ) {
			Logger::log( sprintf( '%s: Skipping %s because it has the skip-css-var option', __METHOD__, $this->setting_name ) );
			$should_skip = true;
		}
		$skip_if_empty = array_key_exists( 'skip-if-empty', $this->setting_options ) ? true : false;
		if ( $skip_if_empty && empty( $this->setting_value ) ) {
			Logger::log( sprintf( '%s: Skipping %s because it is empty and has the skip-if-empty option', __METHOD__, $this->setting_name ) );
			$should_skip = true;
		}
		return $should_skip;
	}

	/**
	 * Change the SCSS variable name if necessary.
	 */
	public function maybe_change_scss_variable_name() {
		if ( array_key_exists( 'variable', $this->setting_options ) ) {
			$this->setting_name = $this->setting_options['variable'];
		}
	}

	/**
	 * Inject color variables into the SCSS values, if the setting is a color.
	 *
	 * @param Array $setting_values The setting values.
	 */
	public function maybe_inject_color_variables( &$setting_values ) {
		if ( 'color' !== $this->setting_type || null === $this->setting_value ) {
			return;
		}
		Logger::log( sprintf( '%s: handling %s value %s', __METHOD__, $this->setting_name, $this->setting_value ) );
		$color = new \Automatic_CSS\Helpers\Color( $this->setting_value );
		$this->setting_name = str_replace( 'color-', '', $this->setting_name ); // Color variables may have a "color-" prefix in the JSON file that needs to be stripped out.
		/* @since 2.0-beta3 - allow programmatic filtering of the value of color variables */
		$setting_values[ $this->setting_name . '-h' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-h", $color->h );
		$setting_values[ $this->setting_name . '-s' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-s", $color->s_perc );
		$setting_values[ $this->setting_name . '-l' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-l", $color->l_perc );
		/* @since 2.1.3 - pass HEX and RGB values to the SCSS compiler */
		$setting_values[ $this->setting_name . '-hex' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-hex", $color->hex );
		$setting_values[ $this->setting_name . '-r' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-r", $color->r );
		$setting_values[ $this->setting_name . '-g' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-g", $color->g );
		$setting_values[ $this->setting_name . '-b' ] = apply_filters( "automaticcss_output_value_{$this->setting_name}-b", $color->b );
	}

	/**
	 * Check if this setting is a custom scale.
	 *
	 * @return bool
	 */
	private function is_custom_scale() {
		$scale_settings = array( 'text-scale', 'mob-text-scale', 'heading-scale', 'mob-heading-scale', 'space-scale', 'mob-space-scale' );
		return in_array( $this->setting_name, $scale_settings ) && 0.0 === floatval( $this->setting_value );
	}

	/**
	 * Use the custom scale value instead of the one from the database, if the user selected a custom scale.
	 *
	 * @param Array $setting_values The setting values.
	 */
	public function maybe_use_custom_scale_value( &$setting_values ) {
		if ( ! $this->is_custom_scale() ) {
			return;
		}
		$custom_scale_setting_name = $this->settings_name . '-custom';
		$this->setting_value = array_key_exists( $custom_scale_setting_name, $setting_values ) ? $setting_values[ $custom_scale_setting_name ] : '';
	}

	/**
	 * Get the unit of the setting.
	 *
	 * @return String
	 */
	public function get_unit() {
		$unit = array_key_exists( 'unit', $this->setting_options ) ? $this->setting_options['unit'] : null;
		$type = array_key_exists( 'type', $this->setting_options ) ? $this->setting_options['type'] : null;
		if ( null === $unit ) {
			switch ( $type ) {
				case 'px':
				case 'rem':
					$unit = $type;
					break;
				case 'percent':
					$unit = '%';
					break;
			}
		}
		return $unit;
	}

	/**
	 * Check if this setting should skip unit conversion.
	 *
	 * @return bool
	 */
	public function should_skip_unit_conversion() {
		$skip_conversion = array_key_exists( 'skip-unit-conversion', $this->setting_options ) ? (bool) $this->setting_options['skip-unit-conversion'] : false;
		return $skip_conversion;
	}

	/**
	 * Maybe convert px to rem.
	 */
	public function maybe_convert_px_to_rem() {
		if ( $this->should_skip_unit_conversion() || 'px' === $this->unit ) {
			return;
		}
		// values in px need to be divided by 10 and then converted to rem.
		$this->setting_value = floatval( $this->setting_value ) / 10;
	}

	/**
	 * Maybe adjust for root font size.
	 *
	 * @param float $root_font_size_value The root font size value.
	 * @param float $root_font_size_default The root font size default.
	 */
	public function maybe_adjust_for_root_font_size( $root_font_size_value, $root_font_size_default ) {
		if ( $this->should_skip_unit_conversion() ) {
			return;
		}
		$should_adjust_root_font_size = array_key_exists( 'percentage-convert', $this->setting_options ) ? (bool) $this->setting_options['percentage-convert'] : false;
		if ( ! $should_adjust_root_font_size || empty( $root_font_size_value ) || empty( $root_font_size_default ) ) {
			// Check for empty() because 0 is not a valid value.
			return;
		}
		// current value : $root_font_size_default (62.5%) = new value : $root_font_size_value.
		$this->setting_value = floatval( $this->setting_value ) * $root_font_size_default / $root_font_size_value;
	}

	/**
	 * Maybe wrap the value in a CSS var.
	 */
	public function maybe_wrap_css_var() {
		// If the value starts with --, add var(.
		if ( null !== $this->setting_value && 0 === strpos( $this->setting_value, '--' ) ) {
			$this->setting_value = "var({$this->setting_value}";
			// If it also does not end with ), add ).
			if ( ')' !== substr( $this->setting_value, -1 ) ) {
				$this->setting_value .= ')';
			}
		}
	}

	/**
	 * Maybe append the unit to the value.
	 */
	public function maybe_append_unit_to_value() {
		// If the unit is % and the value does not end with %, add %.
		if ( ! $this->should_skip_unit_conversion() && '%' === $this->unit && substr( $this->setting_value, -1 ) !== '%' ) {
			$this->setting_value .= '%';
		}
		// If the has an appendunit option, append it.
		if ( array_key_exists( 'appendunit', $this->setting_options ) && '' !== $this->setting_options['appendunit'] ) {
			$this->setting_value .= $this->setting_options['appendunit'];
		}
	}

	/**
	 * Get the output value.
	 *
	 * @return mixed
	 */
	public function get_output_value() {
		return apply_filters( "automaticcss_output_value_{$this->setting_name}", $this->setting_value );
	}

	/**
	 * The SCSS code that generates a box-shadow for WSForms needs the -r, -g and -b partials of the color.
	 * These variables come from the dashboard and are supposed to contain a var(--[color]) value.
	 * So we extract it and use it to generate a new SCSS variable containing the -r, -g and -b partials.
	 *
	 * @param array $setting_values The setting values.
	 * @return void
	 * @since 2.5.0 'f-light-focus-color' & 'f-dark-focus-color'
	 * @since 2.6.0 'f-light-input-placeholder-color' & 'f-dark-input-placeholder-color'
	 */
	public function inject_hsl_setting( &$setting_values ) {
		Logger::log( sprintf( '%s: handling %s', __METHOD__, $this->setting_name ) );
		$matches = array();
		if ( preg_match( '/var\(--([A-Za-z]+)(-[A-Za-z-]+)?/', $this->setting_value, $matches ) ) {
			$color_name = $matches[1];
			$color_variation = isset( $matches[2] ) ? $matches[2] : '';
			$color_var = $color_name . $color_variation;
			$color_h = isset( $setting_values[ $color_var . '-h' ] ) ? $setting_values[ $color_var . '-h' ] : '';
			$color_s = isset( $setting_values[ $color_var . '-s' ] ) ? $setting_values[ $color_var . '-s' ] : '';
			$color_l = isset( $setting_values[ $color_var . '-l' ] ) ? $setting_values[ $color_var . '-l' ] : '';
			Logger::log( sprintf( '%s: matched %s, h: %s, s: %s, l: %s', __METHOD__, $color_var, $color_h, $color_s, $color_l ) );
			if ( '' !== $color_h && '' !== $color_s && '' !== $color_l ) {
				if ( preg_match( '/f-(light|dark)(-[A-Za-z-]+)?-color/', $this->setting_name, $setting_matches ) ) {
					$setting_prefix = $setting_matches[1];
					$setting_variation = $setting_matches[2];
					$var_name = 'f-' . $setting_prefix . $setting_variation . '-hsl';
					$var_value = "{$color_h} {$color_s} {$color_l}";
					$setting_values[ $var_name ] = $var_value;
					Logger::log( sprintf( '%s: injected %s value %s', __METHOD__, $var_name, $var_value ) );
				}
			}
		}
	}

}
