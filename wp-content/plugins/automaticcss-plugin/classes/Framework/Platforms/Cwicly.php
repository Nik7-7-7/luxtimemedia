<?php
/**
 * Automatic.css Cwicly class file.
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
 * Automatic.css Cwicly class.
 */
class Cwicly extends Base implements Platform {

	/**
	 * Cwicly, based on Gutenberg, does not implement the Builder interface, because it can't be considered a traditional page builder.
	 * See the comment on the Gutenberg file for the full explanation.
	 *
	 * On the other hand, Cwicly can use the Builder trait, like page builders.
	 */

	use Builder_Trait {
		in_builder_context as in_builder_context_common;
		in_preview_context as in_preview_context_common;
		in_frontend_context as in_frontend_context_common;
	}

	/**
	 * Instance of the CSS file
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->builder_prefix = 'cwicly'; // for the Builder trait.
		$this->css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-cwicly',
				'automatic-cwicly.css',
				array(
					'source_file' => 'platforms/cwicly/automatic-cwicly.scss',
					'imports_folder' => 'platforms/cwicly'
				),
				array(
					'deps' => apply_filters( 'automaticcss_cwicly_deps', array( 'cwicly' ) )
				)
			)
		);
		if ( is_admin() ) {
			add_action( 'acss/gutenberg/in_preview_context', array( $this, 'in_preview_context' ) );
		} else {
			// Cwicly enqueues in 'wp_enqueue_scripts' with priority 10.
			add_action( 'acss/gutenberg/in_frontend_context', array( $this, 'in_frontend_context' ) );
		}
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'in_builder_context' ) );
		add_filter( 'cwicly_plugin_classes', array( $this, 'inject_classes' ), 10, 1 );
		add_filter( 'cwicly_global_colors', array( $this, 'inject_colors' ), 10, 1 );
	}

	/**
	 * Enqueue resets.
	 */
	public function enqueue_resets() {
		Logger::log( sprintf( '%s: enqueue_resets', __METHOD__ ) );
		$this->css_file->enqueue_stylesheet();
	}

	/**
	 * Inject the core stylesheets in the block editor.
	 *
	 * @return void
	 */
	public function inject_stylesheets_in_block_editor() {
		Logger::log( sprintf( '%s: injecting Cwicly stylesheets in Gutenberg block editor.', __METHOD__ ) );
		add_theme_support( 'editor-styles' ); // supposed to be not necessary, but it is when not using a FSE theme.
		add_editor_style( $this->css_file->file_url );
	}

	/**
	 * Execute code in Gutenberg's builder context when Cwicly is active.
	 *
	 * @return void
	 */
	public function in_builder_context() {
		// Call the in builder context actions.
		// This will remove the ACSS scripts & styles from the builder context.
		$this->in_builder_context_common();
	}

	/**
	 * Execute code in this Builder's frontend context.
	 *
	 * @return void
	 */
	public function in_preview_context() {
		// Inject the core stylesheets in the block editor.
		$this->inject_stylesheets_in_block_editor();
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
		$this->enqueue_resets();
		// Call the frontend context actions.
		$this->in_frontend_context_common();
	}

	/**
	 * Inject the plugin classes in the Cwicly plugin.
	 *
	 * @see https://docs.cwicly.com/miscellaneous/filters#custom-classes-column
	 *
	 * @param array $plugin_classes The plugin classes.
	 * @return array
	 */
	public function inject_classes( $plugin_classes ) {
		$plugin_classes['acss'] = array(
			'name' => 'Automatic.css',
			'classes' => ( new Framework() )->get_classes()
		);
		return $plugin_classes;
	}

	/**
	 * Inject the plugin colors in the Cwicly plugin.
	 *
	 * @param array $global_colors The global colors.
	 * @return array
	 */
	public function inject_colors( $global_colors ) {
		$prefix = 'acss_';
		$acss_db = Database_Settings::get_instance();
		$acss_color_palettes = ( new Framework() )->get_color_palettes(
			array(
				'contextual_colors' => 'on' === $acss_db->get_var( 'option-status-colors' ),
				'deprecated_colors' => 'on' === $acss_db->get_var( 'option-shade-clr' ),
				'separate_transparency' => true,
				'pro_active_only' => true,
			)
		);
		foreach ( $acss_color_palettes as $acss_palette_id => $acss_palette_options ) {
			$acss_colors = array_key_exists( 'colors', $acss_palette_options ) ? $acss_palette_options['colors'] : array();
			$acss_trans = array_key_exists( 'trans', $acss_palette_options ) ? $acss_palette_options['trans'] : array();
			$global_colors[ $prefix . $acss_palette_id ] = array(
				'name'   => 'ACSS ' . $acss_palette_options['name'],
				'colors' => array(),
				'palettes' => array()
			);
			foreach ( $acss_colors as $acss_color_id => $acss_color ) {
				$global_colors[ $prefix . $acss_palette_id ]['colors'][] = array(
					'color' => $acss_color,
				);
			}
			if ( $acss_trans ) {
				$global_colors[ $prefix . $acss_palette_id ]['palettes'][] = array(
					'name' => $acss_palette_options['name'] . ' transparency',
					'colors' => array_values( $acss_trans ),
				);
			}
		}
		return $global_colors;
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		$is_theme_active = self::is_theme_active();
		$is_plugin_active = self::is_plugin_active();
		Logger::log( sprintf( '%s: is_theme_active: %s, is_plugin_active: %s', __METHOD__, $is_theme_active ? 'true' : 'false', $is_plugin_active ? 'true' : 'false' ) );
		return $is_theme_active || $is_plugin_active;
	}

	/**
	 * Check if the Cwicly theme is active.
	 *
	 * @return boolean
	 */
	private static function is_theme_active() {
		$theme = wp_get_theme(); // gets the current theme.
		return 'Cwicly Theme' === $theme->name || 'Cwicly Theme' === $theme->parent_theme;
	}

	/**
	 * Check if the Cwicly plugin is active.
	 *
	 * @return boolean
	 */
	private static function is_plugin_active() {
		return is_plugin_active( 'cwicly/cwicly.php' );
	}

}
