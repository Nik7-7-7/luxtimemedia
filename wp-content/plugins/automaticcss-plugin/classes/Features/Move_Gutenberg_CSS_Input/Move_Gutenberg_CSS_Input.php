<?php
/**
 * Automatic.css Move Gutenberg CSS Input class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Move_Gutenberg_CSS_Input;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Flag;

/**
 * Builder Move_Gutenberg_CSS_Input class.
 */
class Move_Gutenberg_CSS_Input extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {

		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );

	}

	/**
	 * Enqueue scripts for the move gutenberg css input feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$path = '/Move_Gutenberg_CSS_Input/js';
		$filename = 'script.min.js';
		wp_enqueue_script(
			'move-gutenberg-css-input-script',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);

		$path = '/Move_Gutenberg_CSS_Input/css';
		$filename = 'style.css';
		wp_enqueue_style(
			'move-gutenberg-css-input-style',
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
		if ( 'move-gutenberg-css-input-script' == $handle ) {
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
