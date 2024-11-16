<?php
/**
 * Automatic.css Builder Input Validation class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Builder_Input_Validation;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Flag;

/**
 * Builder Input Validation class.
 */
class Builder_Input_Validation extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );
	}

	/**
	 * Enqueue scripts for the builder input validation feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$path = '/Builder_Input_Validation/js';
		$filename = 'builder-input-validation.min.js';
		wp_enqueue_script(
			'builder-input-validation',
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
		if ( 'builder-input-validation' == $handle ) {
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
