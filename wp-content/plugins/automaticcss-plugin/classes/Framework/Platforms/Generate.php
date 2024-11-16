<?php
/**
 * Automatic.css Generate class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Traits\Builder as Builder_Trait;

/**
 * Automatic.css Generate class.
 */
class Generate extends Base implements Platform {

	/**
	 * Generate, based on Gutenberg, does not implement the Builder interface, because it can't be considered a traditional page builder.
	 * See the comment on the Gutenberg file for the full explanation.
	 *
	 * On the other hand, Generate can use the Builder trait, like page builders.
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
	 * Is GeneratePress active?
	 *
	 * @var boolean
	 */
	private $is_generatepress_active = false;

	/**
	 * Is GenerateBlocks active?
	 *
	 * @var boolean
	 */
	private $is_generateblocks_active = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->builder_prefix = 'generate'; // for the Builder trait.
		$this->is_generatepress_active = self::is_generatepress_active();
		$this->is_generateblocks_active = self::is_generateblocks_active();
		$deps = array();
		if ( $this->is_generatepress_active ) {
			$deps[] = 'generate-style';
		}
		if ( $this->is_generateblocks_active ) {
			$deps[] = 'generateblocks';
		}
		$this->css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-generate',
				'automatic-generate.css',
				array(
					'source_file' => 'platforms/generate/automatic-generate.scss',
					'imports_folder' => 'platforms/generate',
				),
				array(
					'deps' => apply_filters( 'automaticcss_generate_deps', $deps ),
				)
			)
		);
		if ( is_admin() ) {
			add_action( 'acss/gutenberg/in_preview_context', array( $this, 'in_preview_context' ) );
			// Inform the SCSS compiler that we're using the platform.
			add_filter( 'automaticcss_framework_variables', array( $this, 'inject_scss_enabler_option' ) );
			add_filter( 'acss/gutenberg/allowed_post_types', array( $this, 'enable_loading_in_generatepress_post_types' ) );
		} else {
			add_action( 'acss/gutenberg/in_frontend_context', array( $this, 'in_frontend_context' ) );
		}
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'in_builder_context' ) );
	}

	/**
	 * Inject an SCSS variable in the CSS generation process to enable this module.
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return array
	 */
	public function inject_scss_enabler_option( $variables ) {
		if ( $this->is_generatepress_active ) {
			$variables['option-generate-press'] = 'on';
		}
		if ( $this->is_generateblocks_active ) {
			$variables['option-generate-blocks'] = 'on';
		}
		return $variables;
	}

	/**
	 * Execute code in Gutenberg's builder context when Generate is active.
	 *
	 * @return void
	 */
	public function in_builder_context() {
		// Call the in builder context actions.
		// This will remove the ACSS scripts & styles from the builder context.
		$this->in_builder_context_common();
	}

	/**
	 * Execute code in Gutenberg's frontend context when Generate is active.
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
	 * Execute code in Gutenberg's preview context when Generate is active.
	 *
	 * @return void
	 */
	public function in_preview_context() {
		if ( ! Gutenberg::is_allowed_post_type() ) {
			Logger::log( sprintf( '%s: enqueuing Gutenberg blocks on this post_type is not allowed', __METHOD__ ) );
			return;
		}
		if ( ! Gutenberg::is_block_editor() ) {
			Logger::log( sprintf( '%s: exiting early because we are not in the block editor', __METHOD__ ) );
			return;
		}
		// Inject the core stylesheets in the block editor.
		Logger::log( sprintf( '%s: injecting Generate stylesheets in Gutenberg block editor.', __METHOD__ ) );
		add_theme_support( 'editor-styles' ); // supposed to be not necessary, but it is when not using a FSE theme.
		add_editor_style( $this->css_file->file_url );
		// Call the preview context actions.
		$this->in_preview_context_common();
	}

	/**
	 * Allow the Gutenberg platform to load our stylesheets in the GeneratePress post types.
	 *
	 * @param array $post_types The post types where Gutenberg is allowed.
	 * @return array
	 */
	public function enable_loading_in_generatepress_post_types( $post_types ) {
		$gp_post_types = array( 'gp_elements', 'gblocks_templates', 'gblocks_global_style' );
		return array_merge( $post_types, $gp_post_types );
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		$is_generatepress_active = self::is_generatepress_active();
		$is_generateblocks_active = self::is_generateblocks_active();
		return $is_generatepress_active || $is_generateblocks_active;
	}

	/**
	 * Check if GeneratePress is active.
	 *
	 * @return boolean
	 */
	public static function is_generatepress_active() {
		$theme = wp_get_theme(); // gets the current theme.
		return 'GeneratePress' === $theme->name || 'GeneratePress' === $theme->parent_theme;
	}

	/**
	 * Check if GenerateBlocks is active.
	 *
	 * @return boolean
	 */
	public static function is_generateblocks_active() {
		return is_plugin_active( 'generateblocks/plugin.php' ) || is_plugin_active( 'generateblocks-pro/plugin.php' ) || is_plugin_active( 'generateblocks-release-1.8.0/plugin.php' ); // TODO: remove the 1.8.0 one.
	}
}
