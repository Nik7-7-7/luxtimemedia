<?php
/**
 * Automatic.css Contextual Menus class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Contextual_Menus;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\Logger;

/**
 * Builder Contextual Menus class.
 */
class Contextual_Menus extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_oxygen_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_bricks_scripts' ) );
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'enqueue_gutenberg_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );

	}

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$doing_cwicly = doing_action( 'acss/cwicly/in_builder_context' );
		$done_cwicly = did_action( 'acss/cwicly/in_builder_context' );
		Logger::log( sprintf( '%s: doing_cwicly: %s, done_cwicly: %s', __METHOD__, $doing_cwicly, $done_cwicly ) );
		if ( $doing_cwicly || $done_cwicly ) {
			// TODO: remove this check when cwicly is fully supported.
			return;
		}
		$path = '/Contextual_Menus/js';
		$filename = 'class-context-menu.min.js';
		wp_enqueue_script(
			'class-context-menu',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);

		// add acss settings object so acss settings can be checked within js.
		wp_localize_script(
			'class-context-menu',
			'p_acssSettings_object',
			array(
				'settings' => get_option( 'automatic_css_settings' ),
			)
		);

		$path = '/Contextual_Menus/js';
		$filename = 'var-context-menu.min.js';
		wp_enqueue_script(
			'var-context-menu',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);

		$path = '/Contextual_Menus/css';
		$filename = 'style.css';
		wp_enqueue_style(
			'plstr-context-menu-style',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);

		// add acss settings object so acss settings can be checked within js.
		wp_localize_script(
			'var-context-menu',
			'p_acssSettings_object',
			array(
				'settings' => get_option( 'automatic_css_settings' ),
			)
		);
	}

	/**
	 * Enqueue oxygen specific scripts and styles for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_oxygen_scripts() {
		$path = '/Contextual_Menus/css';
		$filename = 'balloon.css';
		wp_enqueue_style(
			'context-menu-balloon-css',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Enqueue gutenberg specific scripts and styles for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_gutenberg_scripts() {
		$doing_cwicly = doing_action( 'acss/cwicly/in_builder_context' );
		$done_cwicly = did_action( 'acss/cwicly/in_builder_context' );
		Logger::log( sprintf( '%s: doing_cwicly: %s, done_cwicly: %s', __METHOD__, $doing_cwicly, $done_cwicly ) );
		if ( $doing_cwicly || $done_cwicly ) {
			// TODO: remove this check when cwicly is fully supported.
			return;
		}
		$path = '/Contextual_Menus/css';
		$filename = 'balloon.css';
		wp_enqueue_style(
			'context-menu-balloon-css',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}


	/**
	 * Enqueue bricks specific scripts and styles for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_bricks_scripts() {
		$path = '/Contextual_Menus/css';
		$filename = 'bricks-enlarge-inputs.css';
		wp_enqueue_style(
			'context-menu-bricks-enlarge-inputs-css',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);

	}

	/**
	 * Adds 'type="module"' to the script tag
	 *
	 * @param string $tag The original script tag.
	 * @param string $handle The script handle.
	 * @param string $src The script source.
	 * @return string
	 */
	public static function add_type_attribute( $tag, $handle, $src ) {
		// if not correct script, do nothing and return original $tag.
		if ( 'class-context-menu' == $handle || 'var-context-menu' == $handle ) {
			$load_as_module =
			Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) || Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_FROM_VITE' ) ?
			' type="module"' :
			'';
			$tag = '<script' . $load_as_module . ' src="' . esc_url( $src ) . '"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		}
		// change the script tag by adding type="module" and return it.

		return $tag;

	}
}
