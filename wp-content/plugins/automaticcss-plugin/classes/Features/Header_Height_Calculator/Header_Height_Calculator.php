<?php
/**
 * Automatic.css Header Height Calculator class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Header_Height_Calculator;

use Automatic_CSS\Features\Base;

/**
 * Builder Header_Height_Calculator class.
 */
class Header_Height_Calculator extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts for the feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$path = '/Header_Height_Calculator/js';
		$filename = 'header-height-calculator.min.js';
		wp_enqueue_script(
			'header-height-Calculator',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);
	}

}
