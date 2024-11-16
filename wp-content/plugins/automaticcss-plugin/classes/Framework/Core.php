<?php
/**
 * Automatic.css Framework's Core file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework;

use Automatic_CSS\Framework\Base;
use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Model\Config\UI;
use Automatic_CSS\Plugin;

/**
 * Automatic.css Framework's Core class.
 */
class Core extends Base {


	/**
	 * Instance of the core CSS file
	 *
	 * @var CSS_File
	 */
	private $core_css_file;

	/**
	 * Instance of the vars CSS file
	 *
	 * @var CSS_File
	 */
	private $vars_css_file;

	/**
	 * Instance of the vars CSS file
	 *
	 * @var CSS_File
	 */
	private $custom_css_file;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core_css_file = $this->add_css_file( new CSS_File( 'automaticcss-core', 'automatic.css', 'automatic.scss' ) );
		$this->vars_css_file = $this->add_css_file( new CSS_File( 'automaticcss-variables', 'automatic-variables.css', 'automatic-variables.scss' ) );
		$this->custom_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-custom',
				'automatic-custom-css.css',
				array(
					'source_file' => Plugin::get_dynamic_css_dir() . '/automatic-custom-css.scss',
					'no_source_prefix' => true,
					'imports_folder' => './',
					'skip_file_exists_check' => true,
				)
			)
		);
		// enqueue the framework's CSS file(s).
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_core_stylesheet' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_inline_styles' ) );
		// add the CSS file to the ones enqueued when the Oxygen editor is detected.
		add_action( 'acss/core/in_builder_context', array( $this, 'enqueue_variables_and_dequeue_core' ) );
		// hook into the saving process to generate the custom CSS.
		add_action( 'automaticcss_settings_after_save', array( $this, 'generate_custom_scss' ) );
	}

	/**
	 * Enqueue the core CSS file.
	 *
	 * @return void
	 */
	public function enqueue_core_stylesheet() {
		$this->core_css_file->enqueue_stylesheet();
	}

	/**
	 * Enqueue the custom CSS file.
	 *
	 * @return void
	 */
	public function enqueue_inline_styles() {
		$this->custom_css_file->enqueue_inline( $this->core_css_file->handle ); // Inline styles are attached to the core CSS file.
	}

	/**
	 * Enqueue the variables CSS file in place of the core CSS file.
	 *
	 * @return void
	 */
	public function enqueue_variables_and_dequeue_core() {
		Logger::log( sprintf( '%s: removing automaticcss-core and enqueuing automaticcss-variables', __METHOD__ ) );
		$this->core_css_file->dequeue_stylesheet();
		$this->vars_css_file->enqueue_stylesheet();
	}

	/**
	 * Generate the framework's CSS variables.
	 *
	 * @param  array $values CSS variable values.
	 * @return array
	 */
	public function get_framework_variables( $values ) {
		if ( Flag::is_on( 'ACSS_FLAG_USE_REFACTORED_SCSS_INJECTION_CODE' ) ) {
			$scss_processor = new SCSS_Processor( $values );
			return $scss_processor->get_processed_scss_variables();
		}
		$timer = new Timer();
		Logger::log( sprintf( '%s: starting', __METHOD__ ) );
		$variables = array();
		$vars = ( new UI() )->get_all_settings();
		// Variables that need to be processed after the main ones, because they depend on some of their values being determined.
		$after_color_vars = array(
			'f-light-focus-color',
			'f-dark-focus-color',
			'f-light-input-placeholder-color',
			'f-dark-input-placeholder-color',
		);
		ksort( $vars ); // to make debugging easier.
		// Get the root font size for later calculations.
		$root_font_size = array_key_exists( 'root-font-size', $vars ) ? $vars['root-font-size'] : null;
		// $root_font_size_default = is_array( $root_font_size ) && array_key_exists( 'default', $root_font_size ) ? floatval( $root_font_size['default'] ) : '62.5';
		// Always use 62.5 to correct size calculations
		// Using the typograph.json default root font size generate a wrong value if it was set as 100.
		$root_font_size_default = '62.5';
		foreach ( $vars as $var => $options ) {
			$skip_var_generation = array_key_exists( 'skip-css-var', $options ) ? (bool) $options['skip-css-var'] : false;
			if ( $skip_var_generation || in_array( $var, $after_color_vars ) ) {
				continue; // Some variables from the JSON file don't need a CSS variable generated for them. Skip them.
			}
			$type = $options['type'];
			if ( array_key_exists( $var, $values ) ) {
				/* @since 1.0.5 - allow programmatic filtering of the value of any CSS variable */
				$value = apply_filters( "automaticcss_input_value_{$var}", $values[ $var ] );
				$skip_if_empty = array_key_exists( 'skip-if-empty', $options ) ? true : false;
				if ( $skip_if_empty && '' === $value ) {
					continue; // Override variables only need to be in the CSS if they have a value. Skip them if empty.
				}
				if ( array_key_exists( 'variable', $options ) ) {
					$var = $options['variable'];
				}
				if ( 'color' === $type && null !== $value ) {
					$color = new \Automatic_CSS\Helpers\Color( $value );
					$var = str_replace( 'color-', '', $var ); // Color variables may have a "color-" prefix in the JSON file that needs to be stripped out.
					/* @since 3.0.9 - fix for color partials with -alt suffix */
					$is_alt_color = '-alt' === substr( $var, -4 );
					$var = $is_alt_color ? str_replace( '-alt', '', $var ) : $var;
					$var_suffix = $is_alt_color ? '-alt' : '';
					/* @since 2.0-beta3 - allow programmatic filtering of the value of color variables */
					$variables[ $var . '-h' . $var_suffix ] = apply_filters( "automaticcss_output_value_{$var}-h", $color->h );
					$variables[ $var . '-s' . $var_suffix ] = apply_filters( "automaticcss_output_value_{$var}-s", $color->s_perc );
					$variables[ $var . '-l' . $var_suffix ] = apply_filters( "automaticcss_output_value_{$var}-l", $color->l_perc );
					/* @since 2.1.3 - pass HEX and RGB values to the SCSS compiler */
					$variables[ $var . $var_suffix . '-hex' ] = apply_filters( "automaticcss_output_value_{$var}-hex", $color->hex ); // ALT hex settings are -alt-hex, not -hex-alt like everything else.
					$variables[ $var . '-r' . $var_suffix ] = apply_filters( "automaticcss_output_value_{$var}-r", $color->r );
					$variables[ $var . '-g' . $var_suffix ] = apply_filters( "automaticcss_output_value_{$var}-g", $color->g );
					$variables[ $var . '-b' . $var_suffix ] = apply_filters( "automaticcss_output_value_{$var}-b", $color->b );
				} else {
					/**
					 * Handle special fields.
					 */
					if ( 'text-scale' === $var && 0.0 === floatval( $value ) ) {
						// "custom" text-scale: use the text-scale-custom value instead.
						$value = array_key_exists( 'text-scale-custom', $values ) ? $values['text-scale-custom'] : '';
					} else if ( 'mob-text-scale' === $var && 0.0 === floatval( $value ) ) {
						// "custom" text-scale: use the mob-text-scale-custom value instead.
						$value = array_key_exists( 'mob-text-scale-custom', $values ) ? $values['mob-text-scale-custom'] : '';
					} else if ( 'heading-scale' === $var && 0.0 === floatval( $value ) ) {
						// "custom" heading-scale: use the heading-scale-custom value instead.
						$value = array_key_exists( 'heading-scale-custom', $values ) ? $values['heading-scale-custom'] : '';
					} else if ( 'mob-heading-scale' === $var && 0.0 === floatval( $value ) ) {
						// "custom" heading-scale: use the mob-heading-scale-custom value instead.
						$value = array_key_exists( 'mob-heading-scale-custom', $values ) ? $values['mob-heading-scale-custom'] : '';
					} else if ( 'space-scale' === $var && 0.0 === floatval( $value ) ) {
						// "custom" space-scale: use the space-scale-custom value instead.
						$value = array_key_exists( 'space-scale-custom', $values ) ? $values['space-scale-custom'] : '';
					} else if ( 'mob-space-scale' === $var && 0.0 === floatval( $value ) ) {
						// "custom" text-scale: use the mob-space-scale-custom value instead.
						$value = array_key_exists( 'mob-space-scale-custom', $values ) ? $values['mob-space-scale-custom'] : '';
					}
					/**
					 * Handle units.
					 */
					$unit = array_key_exists( 'unit', $options ) ? $options['unit'] : null;
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
					$skip_conversion = array_key_exists( 'skip-unit-conversion', $options ) ? (bool) $options['skip-unit-conversion'] : false;
					if ( ! $skip_conversion ) {
						// TODO: ensure $value is numeric or... convert to default?
						if ( 'px' === $unit ) {
							// values in px need to be divided by 10 and then converted to rem.
							$value = floatval( $value ) / 10;
						} else if ( '%' === $unit && substr( $value, -1 ) !== '%' ) {
							// add a '%' sign at the end of '%' variables that are missing it.
							$value .= '%';
						}
						/**
						 * Handle 62.5 <-> current root font size conversion (if necessary) before saving the value to the CSS directive.
						 */
						$convert = array_key_exists( 'percentage-convert', $options ) ? (bool) $options['percentage-convert'] : false;
						if ( $convert && null !== $root_font_size ) { // $root_font_size and $root_font_size_default were set earlier.
							$root_font_size_value = isset( $values['root-font-size'] ) ? floatval( $values['root-font-size'] ) : $root_font_size_default;
							// current value : $root_font_size_default (62.5%) = new value : $root_font_size_value.
							$value = floatval( $value ) * $root_font_size_default / $root_font_size_value;
						}
					}
					/**
					 * Handle CSS vars not wrapped in var().
					 */
					// If the value starts with --, add var(.
					if ( null !== $value && 0 === strpos( $value, '--' ) ) {
						$value = "var({$value}";
						// If it also does not end with ), add ).
						if ( ')' !== substr( $value, -1 ) ) {
							$value .= ')';
						}
					}
					/**
					 * Handle adding the unit at the end of the variable, if need be.
					 */
					if ( array_key_exists( 'appendunit', $options ) && '' !== $options['appendunit'] ) {
						$value .= $options['appendunit'];
					}
					/**
					 * Output the actual value.
					 */
					/* @since 1.1.0-beta2 - allow programmatic filtering of the value of any CSS variable */
					$variables[ $var ] = apply_filters( "automaticcss_output_value_{$var}", $value );
				}
			}
		}
		// Handle the variables that need to be processed after the main ones.
		foreach ( $after_color_vars as $var ) {
			if ( array_key_exists( $var, $values ) ) {
				$value = apply_filters( "automaticcss_input_value_{$var}", $values[ $var ] );
				/**
				 * The SCSS code that generates a box-shadow for WSForms needs the -r, -g and -b partials of the color.
				 * These variables come from the dashboard and are supposed to contain a var(--[color]) value.
				 * So we extract it and use it to generate a new SCSS variable containing the -r, -g and -b partials.
				 *
				 * @since 2.5.0 'f-light-focus-color' & 'f-dark-focus-color'
				 * @since 2.6.0 'f-light-input-placeholder-color' & 'f-dark-input-placeholder-color'
				 */
				Logger::log( sprintf( '%s: handling %s', __METHOD__, $var ) );
				$matches = array();
				if ( null !== $value && preg_match( '/var\(--([A-Za-z]+)(-[A-Za-z-]+)?/', $value, $matches ) ) {
					$color_name = $matches[1];
					$color_variation = isset( $matches[2] ) ? $matches[2] : '';
					$color_var = $color_name . $color_variation;
					$color_h = isset( $variables[ $color_var . '-h' ] ) ? $variables[ $color_var . '-h' ] : '';
					$color_s = isset( $variables[ $color_var . '-s' ] ) ? $variables[ $color_var . '-s' ] : '';
					$color_l = isset( $variables[ $color_var . '-l' ] ) ? $variables[ $color_var . '-l' ] : '';
					Logger::log( sprintf( '%s: matched %s, h: %s, s: %s, l: %s', __METHOD__, $color_var, $color_h, $color_s, $color_l ) );
					if ( '' !== $color_h && '' !== $color_s && '' !== $color_l ) {
						if ( preg_match( '/f-(light|dark)(-[A-Za-z-]+)?-color/', $var, $setting_matches ) ) {
							$setting_prefix = $setting_matches[1];
							$setting_variation = $setting_matches[2];
							$var_name = 'f-' . $setting_prefix . $setting_variation . '-hsl';
							$var_value = "{$color_h} {$color_s} {$color_l}";
							$variables[ $var_name ] = $var_value;
							Logger::log( sprintf( '%s: injected %s value %s', __METHOD__, $var_name, $var_value ) );
						}
					}
				}
				/**
				 * Output the actual value.
				 */
				/* @since 1.1.0-beta2 - allow programmatic filtering of the value of any CSS variable */
				$variables[ $var ] = apply_filters( "automaticcss_output_value_{$var}", $value );
			}
		}
		Logger::log( sprintf( '%s: done in %s seconds', __METHOD__, $timer->get_time() ) );
		return $variables;
	}

	/**
	 * Generate the custom SCSS file.
	 *
	 * @param array $values The settings array.
	 * @return void
	 * @throws \Exception If the file cannot be written.
	 */
	public function generate_custom_scss( $values ) {
		$custom_scss = $values['custom-global-css'] ?? '';
		// Load the template file from ACSS_ASSETS_DIR . '/scss/'.
		$template_file = ACSS_ASSETS_DIR . '/scss/front-end-editor.template.scss';
		$template_scss = file_get_contents( $template_file ) ?? '';
		$custom_scss = $template_scss . "\n" . $custom_scss;
		$file_path = Plugin::get_dynamic_css_dir() . '/automatic-custom-css.scss';
		if ( false === file_put_contents( $file_path, $custom_scss ) ) {
			throw new \Exception(
				sprintf(
					'%s: could not write CSS file to %s',
					__METHOD__,
					esc_html( $file_path )
				)
			);
		}
	}

}
