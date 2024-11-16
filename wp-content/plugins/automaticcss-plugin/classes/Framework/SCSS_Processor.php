<?php
/**
 * Automatic.css SCSS_Processor file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Model\Config\UI;

/**
 * Automatic.css SCSS_Processor class.
 */
class SCSS_Processor {

	/**
	 * Names of the settings that need to be processed.
	 *
	 * @var array
	 */
	private $settings_to_process = array();

	/**
	 * Variables that need to be processed after the main ones, because they depend on some of their values being determined.
	 *
	 * @var array
	 */
	private $settings_to_process_after_first_loop = array(
		'f-light-focus-color',
		'f-dark-focus-color',
		'f-light-input-placeholder-color',
		'f-dark-input-placeholder-color',
	);

	/**
	 * All of the setting values received from the database.
	 *
	 * @var array
	 */
	private $setting_values = array();

	/**
	 * All of the processed settings that are going to be sent as SCSS variables.
	 *
	 * @var array
	 */
	private $scss_values = array();

	/**
	 * The value for root font size in the database.
	 *
	 * @var float
	 */
	private $root_font_size_value;

	/**
	 * The default value for root font size.
	 *
	 * @var float
	 */
	private $root_font_size_default;

	/**
	 * Constructor
	 *
	 * @param array $setting_values The setting values received from the database.
	 */
	public function __construct( $setting_values ) {
		$this->settings_to_process = ( new UI() )->get_all_settings();
		ksort( $this->settings_to_process ); // to make debugging easier.
		$this->setting_values = $setting_values;
		$root_font_size_options = array_key_exists( 'root-font-size', $this->setting_values ) ? $this->setting_values['root-font-size'] : null;
		$this->root_font_size_default = is_array( $root_font_size_options ) && array_key_exists( 'default', $root_font_size_options ) ? floatval( $root_font_size_options['default'] ) : '62.5';
		$this->root_font_size_value = isset( $setting_values['root-font-size'] ) ? floatval( $setting_values['root-font-size'] ) : $this->root_font_size_default;
	}

	/**
	 * Process the database values, and return them with any necessary change.
	 *
	 * @return array
	 */
	public function get_processed_scss_variables() {
		$timer = new Timer();
		Logger::log( sprintf( '%s: starting', __METHOD__ ) );
		foreach ( $this->settings_to_process as $setting_name => $setting_options ) {
			if ( ! array_key_exists( $setting_name, $this->setting_values ) ) {
				continue;
			}
			$scss_processor_item = new SCSS_Processor_Item( $setting_name, $this->setting_values[ $setting_name ], $setting_options );
			if ( $scss_processor_item->should_skip( $this->settings_to_process_after_first_loop ) ) {
				continue;
			}
			$scss_processor_item->maybe_change_scss_variable_name();
			$scss_processor_item->maybe_inject_color_variables( $this->scss_values );
			$scss_processor_item->maybe_use_custom_scale_value( $this->scss_values );
			$scss_processor_item->maybe_convert_px_to_rem();
			$scss_processor_item->maybe_adjust_for_root_font_size( $this->root_font_size_value, $this->root_font_size_default );
			$scss_processor_item->maybe_wrap_css_var();
			$scss_processor_item->maybe_append_unit_to_value();
			$this->scss_values[ $scss_processor_item->setting_name ] = $scss_processor_item->get_output_value();
		}
		foreach ( $this->settings_to_process_after_first_loop as $setting_name ) {
			$scss_processor_item = new SCSS_Processor_Item( $setting_name, $this->setting_values[ $setting_name ], $setting_options );
			$scss_processor_item->inject_hsl_setting( $this->scss_values );
		}
		Logger::log( sprintf( '%s: done in %s seconds', __METHOD__, $timer->get_time() ) );
		return $this->scss_values;
	}

}
