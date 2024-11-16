<?php
/**
 * Automatic.css Keyboard Nav Hover Preview class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Keyboard_Nav_Hover_Preview;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Flag;

/**
 * Builder Keyboard_Nav_Hover_Preview class.
 */
class Keyboard_Nav_Hover_Preview extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );

	}

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$path = '/Keyboard_Nav_Hover_Preview/js';
		$filename = 'keyboard-nav-hover-preview.min.js';
		wp_enqueue_script(
			'keyboard-nav-hover-preview-script',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);

		$path = '/Keyboard_Nav_Hover_Preview/css';
		$filename = 'style.css';
		wp_enqueue_style(
			'keyboard-nav-hover-preview-style',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Adds 'type="module"' to the script tag
	 *
	 * @param string $tag    The original script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source.
	 * @return string
	 */
	public static function add_type_attribute( $tag, $handle, $src ) {
		// if not correct script, do nothing and return original $tag.
		if ( 'keyboard-nav-hover-preview-script' == $handle ) {
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
