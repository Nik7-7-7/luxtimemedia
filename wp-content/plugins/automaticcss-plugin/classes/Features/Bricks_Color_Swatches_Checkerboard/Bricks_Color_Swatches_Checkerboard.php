<?php
/**
 * Automatic.css Bricks_Color_Swatches_Checkerboard class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Bricks_Color_Swatches_Checkerboard;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Flag;

/**
 * Builder Bricks_Color_Swatches_Checkerboard class.
 */
class Bricks_Color_Swatches_Checkerboard extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		// add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) ); // commented out.
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		// add_filter('script_loader_tag', array($this,'add_type_attribute') , 10, 3); // commented out.
	}

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		$path = '/Bricks_Color_Swatches_Checkerboard/css';
		$filename = 'checkerboard.css';
		wp_enqueue_style(
			'bricks-color-swatches-checkerboard',
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
		if ( 'keyboard-nav-hover-preview-script' === $handle ) {
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
