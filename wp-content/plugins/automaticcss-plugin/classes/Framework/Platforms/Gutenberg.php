<?php
/**
 * Automatic.css Gutenberg class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\Framework;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Traits\Disableable;
use Automatic_CSS\Traits\Builder as Builder_Trait;

/**
 * Automatic.css Gutenberg class.
 */
class Gutenberg extends Base implements Platform {

	/**
	 * Gutenberg does not implement the Builder interface, because it can't be considered a traditional page builder.
	 * Traditional page builders load the editor and the preview in two distinct HTTP calls.
	 * You can write a static function that will tell if you are in the editor or in the preview, usually by looking at some GET params.
	 * This is not true with Gutenberg.
	 * Gutenberg loads the preview in an iframe, but through a JS call and a blob: object.
	 * Our plugin only loads once, and is in the editor or preview context depending on which action is currently being run (see doing_action()).
	 * There's no way to statically tell, because the answer depends on which point of WP's execution you're calling the function from.
	 *
	 * On the other hand, Gutenberg can use the Builder trait, like page builders.
	 */

	use Builder_Trait {
		in_builder_context as in_builder_context_common;
		in_preview_context as in_preview_context_common;
		in_frontend_context as in_frontend_context_common;
	}

	/**
	 * Allow the Gutenberg module to be disabled while running.
	 */
	use Disableable;

	/**
	 * Instance of the overrides CSS file
	 *
	 * @var CSS_File
	 */
	private $overrides_css_file;

	/**
	 * Instance of the editor CSS file
	 *
	 * @var CSS_File
	 */
	private $editor_css_file;

	/**
	 * Instance of the color palette CSS file
	 *
	 * @var CSS_File
	 */
	private $color_palette_css_file;

	/**
	 * Stores the root font size.
	 *
	 * @var string
	 */
	private $root_font_size;

	/**
	 * Stores whether the styling for the backend is enabled or not.
	 *
	 * @var boolean
	 */
	private $is_load_styling_backend_enabled;

	/**
	 * Stores whether the color palette is enabled or not.
	 *
	 * @var boolean
	 */
	private $is_generate_color_palette_enabled;

	/**
	 * Stores whether the other colors in the palette should be replaced or not.
	 *
	 * @var boolean
	 */
	private $is_replace_color_palette_enabled;

	/**
	 * Constructor
	 *
	 * @param boolean $is_enabled Is the Gutenberg module enabled or not.
	 */
	public function __construct( $is_enabled ) {
		$is_enabled = true; // TODO: remove once tested.
		$this->builder_prefix = 'gutenberg'; // for the Builder trait.
		$this->set_enabled( $is_enabled );
		// Grab the settings.
		$database_settings = Database_Settings::get_instance();
		$this->root_font_size = $database_settings->get_var( 'root-font-size' );
		$this->is_load_styling_backend_enabled = $is_enabled && $database_settings->get_var( 'option-gutenberg-load-styling-backend' ) === 'on' ? true : false;
		$this->is_generate_color_palette_enabled = $is_enabled && $database_settings->get_var( 'option-gutenberg-color-palette-generate' ) === 'on' ? true : false;
		$this->is_replace_color_palette_enabled = $is_enabled && $database_settings->get_var( 'option-gutenberg-color-palette-replace' ) === 'on' ? true : false;
		// Add the CSS files.
		$this->overrides_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-gutenberg',
				'automatic-gutenberg.css',
				array(
					'source_file' => 'platforms/gutenberg/automatic-gutenberg.scss',
					'imports_folder' => 'platforms/gutenberg'
				),
				array(
					'deps' => array( 'automaticcss-core' )
				)
			)
		);
		$this->overrides_css_file->set_enabled( $is_enabled );
		$this->editor_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-core-for-block-editor',
				'automatic-core-for-block-editor.css',
				array(
					'source_file' => 'platforms/gutenberg/automatic-core-for-block-editor.scss',
					'imports_folder' => 'platforms/gutenberg'
				)
			)
		);
		$this->editor_css_file->set_enabled( $this->is_load_styling_backend_enabled );
		$this->color_palette_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-gutenberg-color-palette',
				'automatic-gutenberg-color-palette.css',
				array(
					'source_file' => 'platforms/gutenberg/automatic-gutenberg-color-palette.scss',
					'imports_folder' => 'platforms/gutenberg'
				)
			)
		);
		$this->color_palette_css_file->set_enabled( $this->is_generate_color_palette_enabled );
		// Add the hooks.
		add_action( 'enqueue_block_assets', array( $this, 'in_frontend_context' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'in_builder_context' ) );
		add_action( 'after_setup_theme', array( $this, 'add_color_palette' ), 11 );
		add_action( 'acss/core/in_builder_context', array( $this, 'dequeue_block_editor_assets' ) );
		if ( is_admin() ) {
			add_action( 'current_screen', array( $this, 'in_preview_context' ) );
			// Update the module's status before generating the framework's CSS.
			add_action( 'automaticcss_before_generate_framework_css', array( $this, 'update_status' ) );
		}
	}

	/**
	 * Update the enabled / disabled status of the Gutenberg module
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return void
	 */
	public function update_status( $variables ) {
		// Main enable / disable.
		$is_enabled = true; // TODO: remove once tested.
		Logger::log( sprintf( '%s: setting the Gutenberg module to %s', __METHOD__, $is_enabled ? 'on' : 'off' ) );
		$this->set_enabled( $is_enabled );
		$this->overrides_css_file->set_enabled( $is_enabled );
		// Backend stylesheet enable / disable.
		$this->is_load_styling_backend_enabled = $is_enabled && isset( $variables['option-gutenberg-load-styling-backend'] ) && 'on' === $variables['option-gutenberg-load-styling-backend'] ? true : false;
		$this->editor_css_file->set_enabled( $this->is_load_styling_backend_enabled );
		// Color palette enable / disable.
		$this->is_generate_color_palette_enabled = $is_enabled && isset( $variables['option-gutenberg-color-palette-generate'] ) && 'on' === $variables['option-gutenberg-color-palette-generate'] ? true : false;
		$this->color_palette_css_file->set_enabled( $this->is_generate_color_palette_enabled );
	}

	/**
	 * Execute code in this Builder's frontend context.
	 * Enqueues:
	 * - the overrides stylesheet.
	 * - the color palette stylesheet.
	 *
	 * @return void
	 */
	public function in_frontend_context() {
		if ( ! $this->is_enabled() ) {
			Logger::log( sprintf( '%s: exiting early because the Gutenberg module is disabled', __METHOD__ ) );
			return;
		}
		if ( ! is_admin() ) {
			Logger::log( sprintf( '%s: enqueueing Gutenberg block assets', __METHOD__ ) );
			$this->overrides_css_file->enqueue_stylesheet();
			$this->color_palette_css_file->enqueue_stylesheet();
			$this->in_frontend_context_common();
		}
	}

	/**
	 * Execute code in this Builder's builder context.
	 * Enqueues:
	 * - the root font size fixer.
	 * - the Metabox WYSIWYG fixer.
	 *
	 * @return void
	 */
	public function in_builder_context() {
		if ( ! $this->is_enabled() ) {
			Logger::log( sprintf( '%s: exiting early because the Gutenberg module is disabled', __METHOD__ ) );
			return;
		}
		if ( ! self::is_allowed_post_type() ) {
			Logger::log( sprintf( '%s: enqueuing Gutenberg blocks on this post_type is not allowed', __METHOD__ ) );
			return;
		}
		Logger::log( sprintf( '%s: enqueueing Gutenberg block editor assets', __METHOD__ ) );
		// Call the in builder context actions.
		// This will remove the ACSS scripts & styles from the builder context.
		$this->in_builder_context_common();
		// Enqueue the root font size fixer.
		$filename = 'fix-block-editor-rfs';
		$fix_rfs_path = "/Platforms/Gutenberg/js/{$filename}.js";
		wp_enqueue_script(
			"acss-{$filename}",
			ACSS_FRAMEWORK_URL . $fix_rfs_path,
			array(), // wp-blocks works when not using FSE.
			filemtime( ACSS_FRAMEWORK_DIR . $fix_rfs_path ),
			true
		);
		wp_localize_script(
			"acss-{$filename}",
			'automatic_css_block_editor_options',
			array(
				'root_font_size' => $this->root_font_size
			)
		);
		// Enqueue the Metabox WYSIWYG fixer.
		$filename_metabox = 'fix-metabox-wysiwyg';
		$fix_metabox_path = "/Platforms/Gutenberg/js/{$filename_metabox}.js";
		wp_enqueue_script(
			"acss-{$filename_metabox}",
			ACSS_FRAMEWORK_URL . $fix_metabox_path,
			array(), // wp-blocks works when not using FSE.
			filemtime( ACSS_FRAMEWORK_DIR . $fix_metabox_path ),
			true
		);
		wp_localize_script(
			"acss-{$filename_metabox}",
			'automatic_css_block_editor_options',
			array(
				'root_font_size' => $this->root_font_size
			)
		);
	}

	/**
	 * Execute code in this Builder's preview context.
	 * Injects the core stylesheets in the block editor.
	 *
	 * @return void
	 */
	public function in_preview_context() {
		if ( ! $this->is_enabled() || ! $this->is_load_styling_backend_enabled ) {
			Logger::log( sprintf( '%s: exiting early because the Gutenberg module is disabled or "load styling in backend" is disabled', __METHOD__ ) );
			return;
		}
		if ( ! self::is_allowed_post_type() ) {
			Logger::log( sprintf( '%s: enqueuing Gutenberg blocks on this post_type is not allowed', __METHOD__ ) );
			return;
		}
		if ( ! self::is_block_editor() ) {
			Logger::log( sprintf( '%s: exiting early because we are not in the block editor', __METHOD__ ) );
			return;
		}
		// Inject the core and overrides stylesheets in the block editor.
		if ( $this->is_load_styling_backend_enabled ) {
			Logger::log( sprintf( '%s: injecting core stylesheets in Gutenberg block editor.', __METHOD__ ) );
			$this->enqueue_block_editor_assets();
			// $this->color_palette_css_file is not needed because WP inlines those styles in the editor.
			// Remove the default reset stylesheet from execution, as that causes layout issues.
			if ( ! in_array( 'wp-reset-editor-styles', wp_styles()->done, true ) ) {
				wp_styles()->done[] = 'wp-reset-editor-styles';
			}
		}
		// Call the preview context actions.
		$this->in_preview_context_common();
	}

	/**
	 * Enqueue assets to load only in editor view.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		if ( Flag::is_on( 'ACSS_FLAG_LOAD_GUTENBERG_STYLES_USING_ENQUEUE_STYLESHEET' ) ) {
			$this->editor_css_file->enqueue_stylesheet();
			$this->overrides_css_file->enqueue_stylesheet();
		} else {
			add_theme_support( 'editor-styles' ); // supposed to be not necessary, but it is when not using a FSE theme.
			add_editor_style( $this->editor_css_file->file_url );
			add_editor_style( $this->overrides_css_file->file_url );
		}
	}

	/**
	 * Dequeue assets to load only in editor view.
	 *
	 * @return void
	 */
	public function dequeue_block_editor_assets() {
		$this->overrides_css_file->dequeue_stylesheet();
		$this->color_palette_css_file->dequeue_stylesheet();
	}

	/**
	 * Add the color palette to the block editor.
	 *
	 * @return void
	 */
	public function add_color_palette() {
		if ( ! $this->is_enabled() || ! $this->is_generate_color_palette_enabled ) {
			return;
		}
		// STEP: get all colors and add them to the new palette.
		$acss_db = Database_Settings::get_instance();
		$acss_color_palettes = ( new Framework() )->get_color_palettes(
			array(
				'contextual_colors' => 'on' === $acss_db->get_var( 'option-status-colors' ),
				'deprecated_colors' => 'on' === $acss_db->get_var( 'option-shade-clr' ),
				'pro_active_only' => true,
			)
		);
		$gb_color_palette = array();
		foreach ( $acss_color_palettes as $acss_palette_id => $acss_palette_options ) {
			$acss_palette_colors = array_key_exists( 'colors', $acss_palette_options ) ? $acss_palette_options['colors'] : array();
			foreach ( $acss_palette_colors as $acss_color_name => $acss_color_value ) {
				$gb_color_palette[] = array(
					'name' => $acss_color_name,
					'slug' => $acss_color_name,
					'color' => $acss_color_value, // this is already a valid CSS color.
				);
			}
		}
		// STEP: merge in the current color palette, if the option to replace is not enabled.
		if ( ! $this->is_replace_color_palette_enabled ) {
			// Try to get the current theme default color palette.
			$gb_current_color_palette = current( (array) get_theme_support( 'editor-color-palette' ) );
			if ( false === $gb_current_color_palette && class_exists( 'WP_Theme_JSON_Resolver' ) ) {
				$settings = \WP_Theme_JSON_Resolver::get_core_data()->get_settings();
				if ( isset( $settings['color']['palette']['default'] ) ) {
					$gb_current_color_palette = $settings['color']['palette']['default'];
				}
			}
			if ( ! empty( $gb_current_color_palette ) ) {
				$gb_color_palette = array_merge( $gb_current_color_palette, $gb_color_palette );
			}
		}
		// STEP: save the color palette.
		add_theme_support( 'editor-color-palette', $gb_color_palette );
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return true;
	}

	/**
	 * Are we on a block editor page?
	 *
	 * @return boolean
	 */
	public static function is_block_editor() {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = \get_current_screen();
			$is_block_editor = method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();
			$is_site_editor = 'site-editor' === $current_screen->base;
			return $is_block_editor || $is_site_editor;
		}
		return false;
	}

	/**
	 * Is our Gutenberg integration allowed on the current post type?
	 *
	 * @return boolean
	 */
	public static function is_allowed_post_type() {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = \get_current_screen();
			if ( 'site-editor' === $current_screen->base ) {
				// Skip checking the post type if we're in the site editor.
				return true;
			}
			$post_type = $current_screen->post_type;
			$allowed_post_types = apply_filters( 'acss/gutenberg/allowed_post_types', array( 'page', 'post' ) );
			return $post_type && in_array( $post_type, $allowed_post_types, true );
		}
		return false;
	}

}
