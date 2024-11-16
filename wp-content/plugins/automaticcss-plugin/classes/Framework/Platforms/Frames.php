<?php
/**
 * Automatic.css Frames class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Traits\Disableable;

/**
 * Automatic.css Frames class.
 */
class Frames extends Base implements Platform {

	/**
	 * Allow the WooCommerce module to be disabled while running.
	 */
	use Disableable;

	/**
	 * Instance of the CSS file
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * Constructor
	 *
	 * @param boolean $is_enabled Is the WooCommerce module enabled or not.
	 */
	public function __construct( $is_enabled ) {
		$this->set_enabled( $is_enabled );
		$this->css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-frames',
				'automatic-frames.css',
				array(
					'source_file' => 'platforms/frames/automatic-frames.scss',
					'imports_folder' => 'platforms/frames',
				),
				array(
					'deps' => apply_filters( 'automaticcss_frames_deps', array() ),
				)
			)
		);
		if ( is_admin() ) {
			// Update the module's status before generating the framework's CSS.
			add_action( 'automaticcss_before_generate_framework_css', array( $this, 'update_status' ) );
		} else {
			// WooCommerce enqueues in 'wp_enqueue_scripts' with priority 10.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 11 );
		}
	}

	/**
	 * Update the enabled / disabled status of the WooCommerce module
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return void
	 */
	public function update_status( $variables ) {
		$enabled = isset( $variables['option-frames'] ) && 'on' === $variables['option-frames'] ? true : false;
		Logger::log( sprintf( '%s: setting the Frames module to %s', __METHOD__, $variables['option-frames'] ) );
		$this->set_enabled( $enabled );
		$this->css_file->set_enabled( $enabled );
	}

	/**
	 * Enqueue the Oxygen reset stylesheet.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->css_file->enqueue_stylesheet();
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return true;
	}

}
