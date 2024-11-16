<?php
/**
 * Automatic.css Contextual Menus class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Bem_Class_Generator;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Flag;

/**
 * Builder Contextual Menus class.
 */
class Bem_Class_Generator extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );
	}

	/**UNCOMMENT FOR PRODUCTION BUILD */

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$path     = '/Bem_Class_Generator/js';
		$filename = 'bem-class-generator.min.js';
		wp_enqueue_script(
			'bem-class-generator',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
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
		if ( 'bem-class-generator' === $handle ) {
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
