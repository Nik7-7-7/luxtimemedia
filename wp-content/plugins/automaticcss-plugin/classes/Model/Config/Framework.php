<?php
/**
 * Automatic.css framework config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Plugin;

/**
 * Automatic.css framework config class.
 */
class Framework extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'framework.json' );
	}

	/**
	 * Get the plugin's config/framework.json content and store it / return it
	 *
	 * @return array
	 * @throws \Exception If it can't load the file or it doesn't have the right structure.
	 */
	public function load() {
		parent::load(); // contents stored in $this->contents.
		if ( ! array_key_exists( 'categories', $this->contents ) ) {
			throw new \Exception(
				sprintf(
					'%s: there is no "categories" item in the configuration file',
					__METHOD__
				)
			);
		}
		return $this->contents['categories'];
	}

	/**
	 * Get the classes and variables from the config file.
	 *
	 * @param boolean $pro_active_only Whether to get only the classes that are active in the current database settings.
	 * @return array
	 */
	public function get_framework_info( $pro_active_only = false ) {
		$timer = new Timer();
		$categories = $this->load();
		$classes = array();
		$variables = array();
		$active_settings = $pro_active_only ? $this->get_active_settings() : array();
		foreach ( $categories as $category ) {
			if ( $pro_active_only && ! $this->check_condition( $category, $active_settings ) ) {
				continue;
			}
			$classes = array_merge( $classes, $this->get_classes_from_array( $category ) );
			$variables = array_merge( $variables, $this->get_variables_from_array( $category ) );
			if ( array_key_exists( 'category_groups', $category ) ) {
				foreach ( $category['category_groups'] as $category_group ) {
					if ( $pro_active_only && ! $this->check_condition( $category_group, $active_settings ) ) {
						continue;
					}
					$classes = array_merge( $classes, $this->get_classes_from_array( $category_group ) );
					$variables = array_merge( $variables, $this->get_variables_from_array( $category_group ) );
					if ( array_key_exists( 'class_groups', $category_group ) ) {
						foreach ( $category_group['class_groups'] as $class_group ) {
							if ( $pro_active_only && ! $this->check_condition( $class_group, $active_settings ) ) {
								continue;
							}
							$classes = array_merge( $classes, $this->get_classes_from_array( $class_group ) );
							$variables = array_merge( $variables, $this->get_variables_from_array( $class_group ) );
						}
					}
				}
			}
		}
		$classes = array_values( array_unique( $classes ) );
		$variables = array_values( array_unique( $variables ) );
		$time = $timer->get_time();
		Logger::log( sprintf( '%s: done in %s seconds', __METHOD__, $time ), Logger::LOG_LEVEL_NOTICE );
		Logger::log( sprintf( "%s: classes:\n%s", __METHOD__, print_r( $classes, true ) ), Logger::LOG_LEVEL_NOTICE );
		Logger::log( sprintf( "%s: variables:\n%s", __METHOD__, print_r( $variables, true ) ), Logger::LOG_LEVEL_NOTICE );
		return array(
			'classes' => $classes,
			'variables' => $variables,
		);
	}

	/**
	 * Get the classes from the config file.
	 *
	 * @param boolean $pro_active_only Whether to get only the classes that are active in the current database settings.
	 * @return array
	 */
	public function get_classes( $pro_active_only = false ) {
		return $this->get_framework_info( $pro_active_only )['classes'];
	}

	/**
	 * Get the variables from the config file.
	 *
	 * @param boolean $pro_active_only Whether to get only the classes that are active in the current database settings.
	 * @return array
	 */
	public function get_variables( $pro_active_only = false ) {
		return $this->get_framework_info( $pro_active_only )['variables'];
	}

	/**
	 * Get the classes from a category array.
	 *
	 * @param array $array The category array.
	 * @return array
	 */
	private function get_classes_from_array( &$array ) {
		$classes = array();
		if ( array_key_exists( 'classes', $array ) ) {
			foreach ( $array['classes'] as $class => $options ) {
				$classes[] = $class;
				// if the class has variants, also add those.
				if ( array_key_exists( 'variants', $options ) ) {
					foreach ( $options['variants'] as $variant => $variant_options ) {
						$classes[] = $variant;
					}
				}

				// if the class starts with box-shadow--[m|l|xl], send it to the handle_box_shadow function.
				if ( preg_match( '/^box-shadow--(m|l|xl)/', $class, $matches ) ) {
					$modifier = $matches[1];
					$modifier_class = $this->handle_box_shadow( $modifier );
					if ( $modifier_class && ! in_array( $modifier_class, $classes ) ) {
						$classes[] = $modifier_class;
					}
				}
			}
		}
		return $classes;
	}

	/**
	 * Get the variables from the config file.
	 *
	 * @param array $array The category array.
	 * @return array
	 */
	private function get_variables_from_array( &$array ) {
		$variables = array();
		if ( array_key_exists( 'vars', $array ) ) {
			foreach ( $array['vars'] as $variable => $options ) {
				$variables[] = $variable;
				// if there are variants, also add those.
				if ( array_key_exists( 'variants', $options ) ) {
					foreach ( $options['variants'] as $variant => $variant_options ) {
						$variables[] = $variant;
					}
				}
			}
		}
		return $variables;
	}

	/**
	 * Get the box-shadow modifier class.
	 * Users can set the name of the box-shadow classes in the database.
	 * This function will return the correct class name based on the modifier.
	 *
	 * @param string $modifier_from_json The box-shadow modifier (m, l, xl) from the JSON file.
	 * @return string The box-shadow modifier class.
	 */
	private function handle_box_shadow( $modifier_from_json ) {
		$modifier = '';
		switch ( $modifier_from_json ) {
			case 'm':
				$modifier = '1';
				break;
			case 'l':
				$modifier = '2';
				break;
			case 'xl':
				$modifier = '3';
				break;
		}
		$database = Database_Settings::get_instance();
		$modifier_from_database = $database->get_var( "box-shadow-{$modifier}-name" );
		$modifier_class = "box-shadow--{$modifier_from_database}";
		return $modifier_class;
	}

	/**
	 * Get the color palettes.
	 *
	 * @param array $options The options.
	 * @return array
	 */
	public function get_color_palettes( $options = array() ) {
		$timer = new Timer();
		$defaults = array(
			'transparency_colors' => true,
			'contextual_colors' => true,
			'global_palette' => true,
			'deprecated_colors' => true,
			'separate_transparency' => false,
			'pro_active_only' => false,
		);
		$options = wp_parse_args( $options, $defaults );
		$palettes = array();
		if ( $options['global_palette'] ) {
			$palettes['global'] = array(
				'name' => 'Global',
				'colors' => array()
			);
		}
		$active_settings = $options['pro_active_only'] ? $this->get_active_settings() : array();
		$categories = $this->load();
		if ( ! array_key_exists( 'color', $categories ) ) {
			Logger::log( sprintf( '%s: there is no "color" item in the configuration file', __METHOD__ ) );
			return false;
		}
		$color_category = $categories['color'];
		if ( array_key_exists( 'category_groups', $color_category ) ) {
			foreach ( $color_category['category_groups'] as $main_color_name => $category_group ) {
				// Each category_group is a color palette, grouping one or more colors in its class_groups key.
				if ( $options['pro_active_only'] && ! $this->check_condition( $category_group, $active_settings ) ) {
					continue;
				}
				// STEP: add a palette for this category_group, unless it already exists.
				$palette_name = $main_color_name;
				if ( ! array_key_exists( $palette_name, $palettes ) ) {
					$palettes[ $palette_name ] = array(
						'name' => ucfirst( $palette_name ),
						'colors' => array()
					);
				}
				// STEP: add the main color, unless it has a key of "disable_own_palette_creation".
				if ( ! array_key_exists( 'disable_own_palette_creation', $category_group ) ) {
					$palettes[ $main_color_name ]['colors'][ $main_color_name ] = "var(--{$main_color_name})";
					if ( $options['global_palette'] ) {
						$palettes['global']['colors'][ $main_color_name ] = "var(--{$main_color_name})";
					}
				}
				// The actual colors are in the class_groups key.
				if ( array_key_exists( 'class_groups', $category_group ) ) {
					$trans = array(); // for temp storage of transparencies.
					foreach ( $category_group['class_groups'] as $class_group_key => $class_group ) {
						if ( $options['pro_active_only'] && ! $this->check_condition( $class_group, $active_settings ) ) {
							continue;
						}
						if ( array_key_exists( 'add_to_global_palette', $class_group ) ) {
							// STEP: add this class group's main color ($class_group_key) to the global palette.
							if ( $options['global_palette'] ) {
								$palettes['global']['colors'][ $class_group_key ] = "var(--{$class_group_key})";
							}
						}
						if ( array_key_exists( 'vars', $class_group ) ) {
							// Each class group is a color modifier.
							foreach ( $class_group['vars'] as $variable => $variable_options ) {
								// We purposefully skip the vars without variants because that only includes -h, -s, and -l vars, which we're not interested in here.
								if ( array_key_exists( 'variants', $variable_options ) ) {
									// On a var with variants the $variable is the color modifier. The variants is an object of transparencies.
									// STEP: add color modifiers (i.e. primary-dark).
									$palettes[ $main_color_name ]['colors'][ $variable ] = "var(--{$variable})";
									// STEP: add color transparencies (i.e. primary-trans-10).
									if ( $options['transparency_colors'] && ! array_key_exists( 'skip_variants_in_palette', $class_group ) ) {
										// Check if we need to skip deprecated colors.
										if ( false === $options['deprecated_colors'] && array_key_exists( 'deprecated_trans', $class_group ) ) {
											continue;
										}
										// We're good, generate the transparencies.
										foreach ( $variable_options['variants'] as $color_transparency => $color_transparency_options ) {
											if ( $options['separate_transparency'] ) {
												// In some cases, we want the main color and the transparency to be stored separately (i.e. Cwicly).
												$palettes[ $main_color_name ]['trans'][ $color_transparency ] = "var(--{$color_transparency})";
											} else if ( array_key_exists( 'trans_before_next_color', $category_group ) ) {
												// In some cases, we want a color's transparencies to be displayed before the next main color.
												$palettes[ $main_color_name ]['colors'][ $color_transparency ] = "var(--{$color_transparency})";
											} else {
												// In most cases, we want all main colors to be displayed before all transparencies.
												$trans[ $color_transparency ] = "var(--{$color_transparency})";
											}
										}
									}
								}
							}
						}
					}
					// STEP: if we're not separating transparencies, and they need to be displayed after all main colors, add them now.
					if ( $options['transparency_colors'] && ! empty( $trans ) ) {
						$palettes[ $main_color_name ]['colors'] = array_merge( $palettes[ $main_color_name ]['colors'], $trans );
					}
				}
			}
			// STEP: add extra globals.
			if ( $options['global_palette'] ) {
				$palettes['global']['colors']['transparent'] = 'transparent';
			}
		}
		$time = $timer->get_time();
		Logger::log( sprintf( '%s: done in %s seconds', __METHOD__, $time ), Logger::LOG_LEVEL_NOTICE );
		Logger::log( sprintf( "%s: palettes:\n%s", __METHOD__, print_r( $palettes, true ) ), Logger::LOG_LEVEL_NOTICE );
		return $palettes;
	}

	/**
	 * Check if the condition for a specific category is met.
	 *
	 * @param array $array The category array.
	 * @param array $active_settings The current database settings.
	 * @return boolean
	 */
	private function check_condition( $array, &$active_settings ) {
		if ( array_key_exists( 'condition', $array ) ) {
			$condition = $array['condition'];
			$setting_to_check = $condition['setting'];
			$value_to_check = $condition['value'];
			$operator = $condition['type'];
			if ( array_key_exists( $setting_to_check, $active_settings ) ) {
				$setting_value = $active_settings[ $setting_to_check ];
				switch ( $operator ) {
					case '=':
						return $setting_value === $value_to_check;
				}
			}
		}
		return true;
	}

	/**
	 * Get the current database settings.
	 *
	 * @return array
	 */
	private function get_active_settings() {
		$database = Database_Settings::get_instance();
		return $database->get_vars();
	}

	/**
	 * Output the classes to a file txt file for comparison.
	 *
	 * @return void
	 */
	public static function test_classes() {
		$framework = new Framework();
		$classes = $framework->get_classes();
		sort( $classes );
		$fp = fopen( Plugin::get_dynamic_css_dir() . '/classes.txt', 'w' );
		foreach ( $classes as $class ) {
			fwrite( $fp, $class . "\n" );
		}
		fclose( $fp );
	}

	/**
	 * Output the vars to a file txt file for comparison.
	 *
	 * @return void
	 */
	public static function test_variables() {
		$framework = new Framework();
		$vars = $framework->get_variables();
		sort( $vars );
		$fp = fopen( Plugin::get_dynamic_css_dir() . '/variables.txt', 'w' );
		foreach ( $vars as $var ) {
			fwrite( $fp, $var . "\n" );
		}
		fclose( $fp );
	}

}
