<?php
/**
 * Automatic.css UI config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Plugin;

/**
 * Automatic.css UI config class.
 */
class UI extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'ui.json' );
	}

	/**
	 * Load the 'tabs' item from the ui.json file.
	 *
	 * @return array
	 * @throws \Exception If it can't load the file or it doesn't have the right structure.
	 */
	public function load() {
		// STEP: load the file and check if it has the right structure.
		parent::load(); // contents stored in $this->contents.
		if ( ! is_array( $this->contents['screens'] ) || empty( $this->contents['screens'] ) ) {
			throw new \Exception( 'The UI config file has an empty or non-array "screens" key.' );
		}
		if ( ! array_key_exists( 'globals', $this->contents ) ) {
			throw new \Exception( 'The UI config file is missing the "globals" key.' );
		}
		if ( ! is_array( $this->contents['globals'] ) || empty( $this->contents['globals'] ) ) {
			throw new \Exception( 'The UI config file has an empty or non-array "globals" key.' );
		}
		if ( ! array_key_exists( 'color', $this->contents['globals'] ) ) {
			throw new \Exception( 'The UI config file is missing the "color" key.' );
		}
		// STEP: iterate the screens and load their content.
		Logger::log( sprintf( '%s: loading screens', __METHOD__ ), Logger::LOG_LEVEL_NOTICE );
		$this->contents['content'] = array();
		foreach ( $this->contents['screens'] as $screen ) {
			Logger::log( sprintf( '%s: loading screen %s', __METHOD__, $screen ), Logger::LOG_LEVEL_NOTICE );
			$screen_json = ( new UI_Screen( $screen ) )->load();
			$this->contents['content'][] = $screen_json;
		}
		Logger::log( sprintf( '%s: screen contents: %s', __METHOD__, print_r( $this->contents, true ) ), Logger::LOG_LEVEL_INFO );
		return apply_filters( "acss/config/{$this->filename}/after_load", $this->contents );
	}

	/**
	 * Get the globals from the UI config file.
	 *
	 * @return array
	 */
	public function get_globals() {
		if ( empty( $this->contents ) ) {
			$this->load();
		}
		return $this->contents['globals'];
	}

	/**
	 * Get all the settings from the UI config file in a flat array.
	 *
	 * @param bool $add_missing_settings Whether to add missing settings from the JSON file.
	 * @return array
	 */
	public function get_all_settings( $add_missing_settings = true ) {
		Logger::log( sprintf( '%s: starting with add_missing_settings = %s', __METHOD__, $add_missing_settings ? 'true' : 'false' ) );
		$values = $this->parse_data( $this->load(), $add_missing_settings );
		// Inject the hidden 'timestamp' setting.
		$values['timestamp'] = array(
			'type' => 'hidden'
		);
		ksort( $values ); // to make debugging easier.
		return $values;
	}

	/**
	 * Get all the settings from UI config file set to their default values.
	 *
	 * @param bool $add_missing_settings Whether to add missing settings from the JSON file.
	 * @return array
	 */
	public function get_default_settings( $add_missing_settings = true ) {
		Logger::log( sprintf( '%s: starting with add_missing_settings = %s', __METHOD__, $add_missing_settings ? 'true' : 'false' ) );
		$vars = $this->get_all_settings( $add_missing_settings );
		$values = array();
		foreach ( $vars as $var => $options ) {
			if ( is_array( $options ) && array_key_exists( 'default', $options ) ) {
				$values[ $var ] = $options['default'];
			}
		}
		ksort( $values ); // to make debugging easier.
		return $values;
	}

	/**
	 * Get all the settings from the UI config file in a flat array.
	 *
	 * @param array $item The portion of the JSON object to extract objects from.
	 * @param bool  $add_missing_settings Whether to add missing settings from the JSON file.
	 * @return array
	 */
	private function parse_data( $item, $add_missing_settings = true ) {
		$result = array();
		$item_type = $item['type'] ?? null;

		if ( self::is_container( $item ) ) {
			// STEP: if this container, iterate its content and recursively call this function on each child.
			foreach ( $item['content'] as $subitem ) {
				$result = array_merge( $result, $this->parse_data( $subitem, $add_missing_settings ) );
			}
			return $result;
		}

		// STEP: if this is a setting, add it to the result.
		if ( self::is_setting( $item_type ) ) {
			// If this is a color, we need to make some adjustments.
			if ( 'color' === $item_type && $add_missing_settings ) {
				$this->handle_color( $item, $result );
			} else {
				// Remove the id from the item, since it's already the key in the result array.
				$item_id = $item['id'] ?? null;
				if ( $item_id ) {
					unset( $item['id'] );
					$result[ $item_id ] = $item;
				}
			}
			return $result;
		}

		// STEP: anything else, simply return the current result.
		return $result;
	}

	/**
	 * Check if an item is a setting.
	 *
	 * @param string $item_type The item type to check.
	 * @return boolean
	 */
	private static function is_setting( $item_type ) {
		return in_array( $item_type, array( 'text', 'textarea', 'select', 'toggle', 'color', 'number', 'px', 'rem', 'percent', 'codebox' ) );
	}

	/**
	 * Check if an item is a container.
	 *
	 * @param string $item The setting type to check.
	 * @return boolean
	 */
	private static function is_container( $item ) {
		return isset( $item['content'] ) && is_array( $item['content'] ) && ! empty( $item['content'] );
	}

	/**
	 * Adjust the color IDs and add the shade settings.
	 *
	 * @param array $item The item to adjust.
	 * @param array $result The result array to add the shade settings to.
	 * @return void
	 */
	private function handle_color( &$item, &$result ) {
		$item_id = $item['id'] ?? null;
		$item_id_alt = $item['id'] . '-alt';
		// Save the color name from the ID, removing the 'color-' prefix if present.
		$color_name = str_replace( 'color-', '', $item_id );
		$main_color = null;
		$alt_color = null;
		foreach ( $item['colors'] as $color ) {
			if ( 'default' === $color['name'] ) {
				$main_color = new \Automatic_CSS\Helpers\Color( $color['default'] );
			} else if ( 'alt' === $color['name'] ) {
				$alt_color = new \Automatic_CSS\Helpers\Color( $color['default'] );
			}
		}

		// STEP: add the main color.
		$color_id = 'color-' . $color_name;
		$color_item = array(
			'type' => 'color',
			'default' => $main_color->hex
		);
		$result[ $color_id ] = $color_item;

		// STEP: add all of the shade settings, which are missing from the UI config file.
		$this->handle_color_shades( $color_name, $main_color, $result );

		// STEP: repeat for the -alt color.
		$alt_color_name = $color_name . '-alt';
		$alt_color_id = 'color-' . $alt_color_name;
		$alt_color_item = array(
			'type' => 'color',
			'default' => $alt_color->hex
		);
		$result[ $alt_color_id ] = $alt_color_item;

		// STEP: add the -alt shades.
		$this->handle_color_shades( $color_name, $alt_color, $result, '-alt' );

		// STEP: add the options for the color.
		$option_extensions = array(
			'main' => array( 'clr', 'clr-alt' ),
			'trans' => array( 'dark-trans', 'light-trans', 'main-trans', 'ultra-dark-trans' ),
		);
		foreach ( $option_extensions as $extension_type => $extensions ) {
			foreach ( $extensions as $extension ) {
				$option_id = 'option-' . $color_name . '-' . $extension;
				$default = 'off';
				if ( 'trans' === $extension_type ||
					( 'main' === $extension_type && in_array( $color_name, array( 'primary', 'base', 'neutral' ) ) && 'clr-alt' !== $extension ) ) {
					$default = 'on';
				}
				$option_item = array(
					'type' => 'toggle',
					'default' => $default
				);
				$result[ $option_id ] = $option_item;
			}
		}

		// Remove the original options from the result array.
		if ( isset( $item_id ) ) {
			unset( $result[ $item_id ] );
		}
		if ( isset( $item_id_alt ) ) {
			unset( $result[ $item_id_alt ] );
		}
	}

	/**
	 * Add the shade settings to the result array.
	 *
	 * @param string $color_name The color name.
	 * @param Color  $color_obj The color object.
	 * @param array  $result The result array to add the shade settings to.
	 * @param string $color_postfix The color postfix.
	 * @return void
	 */
	private function handle_color_shades( $color_name, $color_obj, &$result, $color_postfix = '' ) {
		$globals = $this->get_globals();
		$shades = array( 'ultra-light', 'light', 'semi-light', 'medium', 'semi-dark', 'dark', 'ultra-dark', 'hover', 'comp' );
		$hsl = array( 'h', 's', 'l' );
		$shade_l_map = self::get_shades_map( $globals['color'], $color_name, $color_obj, $color_postfix );
		foreach ( $shades as $shade ) {
			foreach ( $hsl as $hsl_value ) {
				$shade_id = $color_name . '-' . $shade . '-' . $hsl_value . $color_postfix;
				switch ( $hsl_value ) {
					case 'h':
						$default = is_a( $color_obj, \Automatic_CSS\Helpers\Color::class ) ? (int) $color_obj->$hsl_value : '';
						$min = 0;
						$max = 360;
						break;
					case 's':
						$default = is_a( $color_obj, \Automatic_CSS\Helpers\Color::class ) ? (int) $color_obj->$hsl_value : '';
						$min = 0;
						$max = 100;
						break;
					case 'l':
						$default = $shade_l_map[ $shade ];
						$min = 0;
						$max = 100;
						break;
				}
				$shade_item = array(
					'type' => 'number',
					'default' => $default,
					'displayWhen' => array(
						'option-' . $color_name . '-clr' . $color_postfix,
						'on',
					),
					'validation' => array(
						'required' => false, // Will use displayWhen to determine if they're required.
						'min' => $min,
						'max' => $max
					),
				);
				if ( in_array( $hsl_value, array( 's', 'l' ) ) ) {
					$shade_item['appendunit'] = '%';
				}
				if ( 'medium' === $shade ) {
					$shade_item['displayWhen'] = array(
						$shade_item['displayWhen'],
						array( 'option-medium-shade', 'on' ),
					);
				}
				$result[ $shade_id ] = $shade_item;
			}
		}
	}

	/**
	 * Get the color settings from the UI config file.
	 *
	 * @param array  $color_globals The global color settings.
	 * @param string $color_name The color name.
	 * @param Color  $color_obj The color object.
	 * @param string $color_postfix The color postfix.
	 * @return array
	 */
	private static function get_shades_map( $color_globals, $color_name, $color_obj, $color_postfix ) {
		$shades = $color_globals['shades'] ?? array();
		$hover_multiplier = $color_globals['hoverMultiplier'] ?? null;
		$shade_map = array();
		// STEP: create the custom map for the shade and neutral colors.
		$custom_map = array();
		if ( in_array( $color_name, array( 'shade', 'neutral' ) ) ) {
			if ( '-alt' === $color_postfix ) {
				$custom_map = array(
					'ultra-light' => 5,
					'light' => 15,
					'dark' => 75,
					'ultra-dark' => 90,
				);
			} else {
				$custom_map = array(
					'ultra-light' => 95,
					'light' => 85,
					'dark' => 25,
					'ultra-dark' => 10,
				);
			}
		} else if ( in_array( $color_name, array( 'danger', 'warning', 'info', 'success' ) ) ) {
			$custom_map = array(
				'dark' => 15,  // TODO: check if dark status colors still need a different lightness than the default one.
			);
		}
		// STEP: iterate the shades and add them to the map.
		foreach ( $shades as $shade ) {
			$shade_map[ $shade['name'] ] = $shade['l'];
		}
		$shade_map = array_merge( $shade_map, $custom_map );
		// STEP: add the 'comp' shade.
		$shade_map['comp'] = is_a( $color_obj, \Automatic_CSS\Helpers\Color::class ) ? (int) $color_obj->l : '';
		// STEP: add the hover multiplier, if present.
		if ( null !== $hover_multiplier ) {
			$shade_map['hover'] = $color_obj->l * $hover_multiplier;
			if ( $shade_map['hover'] > 100 ) {
				$shade_map['hover'] = 100;
			} else if ( $shade_map['hover'] < 0 ) {
				$shade_map['hover'] = 0;
			}
			Logger::log( sprintf( '%s: color name: %s, hover multiplier: %s', __METHOD__, $color_name, $shade_map['hover'] ), Logger::LOG_LEVEL_INFO );
		}
		return $shade_map;
	}

}
