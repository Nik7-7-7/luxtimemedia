<?php
/**
 * Automatic.css Bricks class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\Framework;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Traits\Builder as Builder_Trait;

/**
 * Automatic.css Bricks class.
 */
class Bricks extends Base implements Platform, Builder {

	use Builder_Trait {
		in_builder_context as in_builder_context_common;
		in_preview_context as in_preview_context_common;
		in_frontend_context as in_frontend_context_common;
	}

	/**
	 * Instance of the CSS file used only in the preview and frontend context.
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * Instance of the CSS file used only in the builder context.
	 *
	 * @var CSS_File
	 */
	private $in_builder_css_file;

	/**
	 * Used to namespace the global classes array.
	 */
	const CLASS_IMPORT_ID_PREFIX = 'acss_import_';

	/**
	 * Used to namespace the color palette array.
	 */
	const PALETTE_IMPORT_ID_PREFIX = 'acss_import_';

	/**
	 * Name of the color palette.
	 */
	const PALETTE_IMPORT_NAME_PREFIX = 'ACSS ';

	/**
	 * The class category ID.
	 */
	const CLASS_CATEGORY_ID = 'acss';

	/**
	 * Constructor
	 *
	 * @param array $database_settings The database settings.
	 */
	public function __construct( $database_settings ) {
		$this->builder_prefix = 'bricks'; // for the Builder trait.
		$this->css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-bricks',
				'automatic-bricks.css',
				array(
					'source_file' => 'platforms/bricks/automatic-bricks.scss',
					'imports_folder' => 'platforms/bricks'
				),
				array(
					'deps' => apply_filters( 'automaticcss_bricks_deps', array( 'bricks-frontend' ) )
				)
			)
		);
		$this->in_builder_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-bricks-in-builder',
				'automatic-bricks-in-builder.css',
				array(
					'source_file' => 'platforms/bricks/bricks-in-builder.scss',
					'imports_folder' => 'platforms/bricks'
				)
			)
		);
		if ( is_admin() ) {
			add_action( 'automaticcss_activate_plugin_end', array( $this, 'update_globals' ) );
			add_action( 'automaticcss_update_plugin_end', array( $this, 'update_globals' ) );
			add_action( 'automaticcss_deactivate_plugin_start', array( $this, 'delete_globals' ) ); // 20220630 - MG - used to hook into automaticcss_delete_plugin_data_end.
			// Inform the SCSS compiler that we're using the Bricks platform.
			add_filter( 'automaticcss_framework_variables', array( $this, 'inject_scss_enabler_option' ) );
			// Hook into the saving process to update the global classes and palettes.
			add_action( 'automaticcss_settings_after_save', array( $this, 'after_save_settings' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_builder_assets' ), 11 );
		}
	}

	/**
	 * Inject an SCSS variable in the CSS generation process to enable this module.
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return array
	 */
	public function inject_scss_enabler_option( $variables ) {
		$variables['option-bricks'] = 'on';
		return $variables;
	}

	/**
	 * Enqueue the Bricks assets.
	 *
	 * @return void
	 */
	public function enqueue_builder_assets() {
		if ( self::is_builder_context() ) {
			$this->in_builder_context();
		} else if ( self::is_preview_context() ) {
			$this->in_preview_context();
		} else {
			$this->in_frontend_context();
		}
	}

	/**
	 * Execute code in this Builder's builder context.
	 *
	 * @return void
	 */
	public function in_builder_context() {
		// Call the in builder context actions.
		// This will remove the ACSS scripts & styles from the builder context.
		$this->in_builder_context_common();
		// Enqueue a stylesheet that fixes some in editor issues.
		$this->in_builder_css_file->enqueue_stylesheet();
	}

	/**
	 * Execute code in this Builder's frontend context.
	 *
	 * @return void
	 */
	public function in_preview_context() {
		// Enqueue the overrides stylesheet.
		$this->css_file->enqueue_stylesheet();
		// Call the preview context actions.
		$this->in_preview_context_common();
	}

	/**
	 * Execute code in this Builder's frontend context.
	 *
	 * @return void
	 */
	public function in_frontend_context() {
		// Enqueue the overrides stylesheet.
		$this->css_file->enqueue_stylesheet();
		// Call the frontend context actions.
		$this->in_frontend_context_common();
	}

	/**
	 * Update the Bricks global classes and palettes after saving the settings.
	 *
	 * @since 3.0.7
	 * @return void
	 */
	public function after_save_settings() {
		Logger::log( sprintf( '%s: save detected - updating Bricks global classes and palettes', __METHOD__ ) );
		$acss_db = Database_Settings::get_instance();
		$should_refresh = 'on' === $acss_db->get_var( 'option-refresh-globals-on-save' );
		if ( ! $should_refresh ) {
			Logger::log( sprintf( '%s: option-refresh-globals-on-save is not enabled, skipping', __METHOD__ ) );
			return;
		}
		// Update the global classes and palettes.
		$this->delete_globals();
		$this->update_globals();
	}

	/**
	 * Add the framework's classes to Bricks for autocomplete.
	 *
	 * @return void
	 */
	public function update_globals() {
		Logger::log( sprintf( '%s: adding Automatic.css classes and palettes into Bricks global classes', __METHOD__ ) );
		// STEP: update the global and locked classes.
		$this->update_global_classes();
		// STEP: update the global colors.
		$this->update_global_colors();
		// Done.
		Logger::log( sprintf( '%s: done', __METHOD__ ) );
	}

	/**
	 * Update Brick's global classes and locked classes.
	 *
	 * @return void
	 */
	private function update_global_classes() {
		$acss_db = Database_Settings::get_instance();
		$pro_mode_classes_only = 'on' === $acss_db->get_var( 'option-remove-deactivated-classes-from-globals' ) ? true : false;
		$acss_classes = ( new Framework() )->get_classes( $pro_mode_classes_only );
		if ( is_array( $acss_classes ) && count( $acss_classes ) > 0 ) {
			$bricks_class_categories = (array) get_option( 'bricks_global_classes_categories', array() );
			$acss_category_id = array_search( self::CLASS_CATEGORY_ID, array_column( $bricks_class_categories, 'id' ) );
			if ( false === $acss_category_id ) {
				$bricks_class_categories[] = array(
					'id' => self::CLASS_CATEGORY_ID,
					'name' => 'Automatic.css',
				);
				update_option( 'bricks_global_classes_categories', $bricks_class_categories );
			}
			$bricks_global_classes = (array) get_option( 'bricks_global_classes', array() );
			$bricks_global_class_names = array_column( $bricks_global_classes, 'name' );
			$bricks_locked_classes = (array) get_option( 'bricks_global_classes_locked', array() );
			/**
			 * Global classes array structure:
			 * 0 => array (
			 *      'id' => a random string,
			 *      'name' => the actual class name,
			 *      'settings' => array()
			 *  )
			 *
			 * Locked classes array structure:
			 * array (
			 *  0 => 'align-content--baseline'
			 *  )
			 */
			foreach ( $acss_classes as $acss_class ) {
				// STEP: add our class to Bricks global classes, if it's not there yet.
				if ( ! in_array( $acss_class, $bricks_global_class_names ) ) {
					$bricks_global_classes[] = array(
						'id' => self::CLASS_IMPORT_ID_PREFIX . $acss_class,
						'name' => $acss_class,
						'settings' => array(),
						'category' => self::CLASS_CATEGORY_ID
					);
				}
				// STEP: add our class to Bricks locked classes, if it's not there yet.
				if ( ! in_array( self::CLASS_IMPORT_ID_PREFIX . $acss_class, $bricks_locked_classes ) ) {
					$bricks_locked_classes[] = self::CLASS_IMPORT_ID_PREFIX . $acss_class;
				}
			}
			// STEP: update the options.
			update_option( 'bricks_global_classes', $bricks_global_classes, false );
			update_option( 'bricks_global_classes_locked', $bricks_locked_classes, false );
			Logger::log( sprintf( '%s: Bricks classes updated', __METHOD__ ) );
		}
	}

	/**
	 * Update Brick's global colors.
	 *
	 * @return void
	 */
	private function update_global_colors() {
		// STEP: setup.
		$bricks_color_palette = (array) get_option( 'bricks_color_palette', array() );
		$acss_db = Database_Settings::get_instance();
		$acss_color_palettes = ( new Framework() )->get_color_palettes(
			array(
				'contextual_colors' => 'on' === $acss_db->get_var( 'option-status-colors' ),
				'deprecated_colors' => 'on' === $acss_db->get_var( 'option-shade-clr' ),
				'pro_active_only' => true,
			)
		);
		// STEP: add each palette to the Bricks global colors.
		foreach ( $acss_color_palettes as $acss_palette_id => $acss_palette_options ) {
			$acss_colors = array_key_exists( 'colors', $acss_palette_options ) ? $acss_palette_options['colors'] : array();
			// STEP: ensure there's a palette for this color.
			$bricks_this_palette_key = array_search( self::PALETTE_IMPORT_ID_PREFIX . $acss_palette_id, array_column( $bricks_color_palette, 'id' ) );
			if ( false === $bricks_this_palette_key ) {
				$bricks_color_palette[] = array(
					'id' => self::PALETTE_IMPORT_ID_PREFIX . $acss_palette_id,
					'name' => self::PALETTE_IMPORT_NAME_PREFIX . $acss_palette_options['name'],
					'colors' => array()
				);
				$bricks_this_palette_key = array_key_last( $bricks_color_palette );
			}
			// STEP: add each color to the palette.
			$bricks_this_palette_color_ids = array_column( $bricks_color_palette[ $bricks_this_palette_key ]['colors'], 'id' );
			foreach ( $acss_colors as $acss_color_name => $acss_color_value ) {
				if ( ! in_array( self::PALETTE_IMPORT_ID_PREFIX . $acss_color_name, $bricks_this_palette_color_ids ) ) {
					$bricks_color_palette[ $bricks_this_palette_key ]['colors'][] = array(
						'id' => self::PALETTE_IMPORT_ID_PREFIX . $acss_color_name,
						'name' => $acss_color_name,
						'raw' => $acss_color_value
					);
				}
			}
		}
		// STEP: update the option.
		update_option( 'bricks_color_palette', $bricks_color_palette, false );
		Logger::log( sprintf( '%s: Bricks color palette updated', __METHOD__ ) );
	}

	/**
	 * Remove the framework's classes to Bricks for autocomplete.
	 *
	 * @return void
	 */
	public function delete_globals() {
		Logger::log( sprintf( '%s: deleting Automatic.css global classes and palettes from Bricks', __METHOD__ ) );
		// STEP: delete global and locked classes.
		$this->delete_global_classes();
		// STEP: delete global colors.
		$this->delete_global_colors();
		// Done.
		Logger::log( sprintf( '%s: done', __METHOD__ ) );
	}

	/**
	 * Delete Bricks global classes and locked classes that were imported from ACSS.
	 *
	 * @return void
	 */
	private function delete_global_classes() {
		$acss_classes = ( new Framework() )->get_classes();
		if ( is_array( $acss_classes ) && count( $acss_classes ) > 0 ) {
			$bricks_global_classes = (array) get_option( 'bricks_global_classes', array() );
			$bricks_global_class_ids = array_column( $bricks_global_classes, 'id' );
			$bricks_locked_classes = (array) get_option( 'bricks_global_classes_locked', array() );
			foreach ( $acss_classes as $acss_class ) {
				// STEP: remove our class from Bricks global classes, if it's there.
				// Check that it was added there by us by using the CLASS_IMPORT_ID_PREFIX, so check the 'id' and not the 'name'.
				// We use array_keys and not array_search because we don't know if there's multiple instances of the class for some reason.
				$global_indexes = array_keys( $bricks_global_class_ids, self::CLASS_IMPORT_ID_PREFIX . $acss_class );
				if ( is_array( $global_indexes ) && count( $global_indexes ) > 0 ) {
					foreach ( $global_indexes as $global_index ) {
						unset( $bricks_global_classes[ $global_index ] );
					}
					// STEP: remove our class from Bricks locked classes, if it's there, and only if it was inserted in the globals by us.
					// The locked classes don't have IDs, just names, so we only check $acss_class with no CLASS_IMPORT_ID_PREFIX.
					$locked_indexes = array_keys( $bricks_locked_classes, self::CLASS_IMPORT_ID_PREFIX . $acss_class );
					if ( is_array( $locked_indexes ) && count( $locked_indexes ) > 0 ) {
						foreach ( $locked_indexes as $locked_index ) {
							unset( $bricks_locked_classes[ $locked_index ] );
						}
					}
				}
			}
			// STEP: update the options.
			update_option( 'bricks_global_classes', array_values( $bricks_global_classes ), false ); // array_values to fix holes in the array.
			update_option( 'bricks_global_classes_locked', array_values( $bricks_locked_classes ), false ); // array_values to fix holes in the array.
			Logger::log( sprintf( '%s: Bricks classes updated', __METHOD__ ) );
		}
	}

	/**
	 * Delete Bricks global colors that were imported from ACSS.
	 *
	 * @return void
	 */
	private function delete_global_colors() {
		$bricks_color_palette = (array) get_option( 'bricks_color_palette', array() );
		if ( ! empty( $bricks_color_palette ) ) {
			$bricks_color_palette_ids = array_column( $bricks_color_palette, 'id' );
			$acss_color_palettes = ( new Framework() )->get_color_palettes(); // get ALL color palettes even if some are turned off in the settings.
			// STEP: cycle through each palette to the Bricks global colors.
			foreach ( $acss_color_palettes as $acss_palette_id => $acss_palette_options ) {
				// STEP: ensure there's a palette for this color.
				$bricks_this_palette_key = array_search( self::PALETTE_IMPORT_ID_PREFIX . $acss_palette_id, $bricks_color_palette_ids );
				if ( false === $bricks_this_palette_key ) {
					// Palette not found, skip.
					continue;
				}
				// STEP: remove each color to the palette.
				$bricks_this_palette_color_ids = array_column( $bricks_color_palette[ $bricks_this_palette_key ]['colors'], 'id' );
				$acss_palette_colors = array_key_exists( 'colors', $acss_palette_options ) ? $acss_palette_options['colors'] : array();
				foreach ( $acss_palette_colors as $acss_color_name => $acss_color_value ) {
					$bricks_this_color_key = array_search( self::PALETTE_IMPORT_ID_PREFIX . $acss_color_name, $bricks_this_palette_color_ids );
					if ( false !== $bricks_this_color_key ) {
						unset( $bricks_color_palette[ $bricks_this_palette_key ]['colors'][ $bricks_this_color_key ] );
					}
				}
				// STEP: remove this palette if empty.
				if ( empty( $bricks_color_palette[ $bricks_this_palette_key ]['colors'] ) ) {
					unset( $bricks_color_palette[ $bricks_this_palette_key ] );
				} else {
					$bricks_color_palette[ $bricks_this_palette_key ]['colors'] = array_values( $bricks_color_palette[ $bricks_this_palette_key ]['colors'] ); // fix holes.
				}
			}
			// STEP: update the option.
			update_option( 'bricks_color_palette', array_values( $bricks_color_palette ), false ); // array_values to fix holes in the array.
			Logger::log( sprintf( '%s: Bricks color palette updated', __METHOD__ ) );
		}
		Logger::log( sprintf( '%s: Bricks color palette updated', __METHOD__ ) );
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		// I checked with class_exists( 'CT_Component' ), but it doesn't work here.
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$theme = wp_get_theme(); // gets the current theme.
		return 'Bricks' === $theme->name || 'Bricks' === $theme->parent_theme;
	}

	/**
	 * Are we in Bricks's builder context?
	 * That means we're in the builder, but not in the preview's iframe.
	 *
	 * @return bool
	 */
	public static function is_builder_context() {
		$is_builder = 'run' === filter_input( INPUT_GET, 'bricks' );
		$is_preview = self::is_preview_context();
		return $is_builder && ! $is_preview;
	}

	/**
	 * Are we in Bricks's iframe context?
	 * That means we're in NOT in the builder, just in the preview's iframe.
	 *
	 * @return bool
	 */
	public static function is_preview_context() {
		$is_preview = null !== filter_input( INPUT_GET, 'brickspreview' );
		return $is_preview;
	}

	/**
	 * Are we in Bricks's frontend context?
	 * That means we're in neither in the builder nor in the preview's iframe.
	 *
	 * @return bool
	 */
	public static function is_frontend_context() {
		return ! is_admin() && ! self::is_builder_context() && ! self::is_preview_context();
	}

	/**
	 * Get the version of Bricks.
	 *
	 * @return string
	 */
	public static function get_version() {
		if ( ! self::is_active() ) {
			return '';
		}
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$theme = wp_get_theme(); // gets the current theme.
		$version = $theme->get( 'Version' );
		return $version;
	}

}
