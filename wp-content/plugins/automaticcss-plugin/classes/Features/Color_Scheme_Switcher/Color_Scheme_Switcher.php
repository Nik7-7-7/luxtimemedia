<?php
/**
 * Automatic.css Color Scheme Switcher class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Color_Scheme_Switcher;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Database_Settings;

/**
 * Builder Color Scheme Switcher class.
 */
class Color_Scheme_Switcher extends Base {

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		// Hook into Oxygen's builder and frontend context.
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_oxygen_assets' ) );
		add_action( 'acss/oxygen/in_frontend_context', array( $this, 'enqueue_frontend_assets' ) );
		// Hook into Bricks's builder and frontend context.
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_bricks_assets' ) );
		add_action( 'acss/bricks/in_frontend_context', array( $this, 'enqueue_frontend_assets' ) );
		// Hook into Cwicly's builder and frontend context.
		add_action( 'acss/cwicly/in_builder_context', array( $this, 'enqueue_cwicly_assets' ) );
		add_action( 'acss/cwicly/in_frontend_context', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		Logger::log( sprintf( '%s: enqueue_frontend_assets', __METHOD__ ) );
		$path     = '/Color_Scheme_Switcher/js';
		$filename = 'frontend.min.js';
		wp_enqueue_script(
			'color-scheme-switcher-frontend',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);

		$model = Database_Settings::get_instance();
		wp_localize_script(
			'color-scheme-switcher-frontend',
			'acss',
			array(
				'color_mode' => $model->get_var( 'website-color-scheme' ),
				'enable_client_color_preference' => 'on' === $model->get_var( 'option-prefers-color-scheme' ) ? 'true' : 'false',
			)
		);

		$path = '/Color_Scheme_Switcher/css';
		$filename = 'frontend.css';
		wp_enqueue_style(
			'color-scheme-switcher-frontend',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Enqueue scripts for Oxygen.
	 *
	 * @return void
	 */
	public function enqueue_oxygen_assets() {
		Logger::log( sprintf( '%s: enqueue_oxygen_assets', __METHOD__ ) );
		// Dequeue the frontend assets, which would otherwise still run in Oxygen's builder context.
		wp_dequeue_script( 'color-scheme-switcher-frontend' );
		wp_dequeue_style( 'color-scheme-switcher-frontend' );
		// Script.
		$path     = '/Color_Scheme_Switcher/js';
		$filename = 'oxygen.min.js';
		wp_enqueue_script(
			'color-scheme-switcher-oxygen',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
		// Stylesheet.
		$path = '/Color_Scheme_Switcher/css';
		$filename = 'oxygen.css';
		wp_enqueue_style(
			'color-scheme-switcher-oxygen',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Enqueue scripts for Bricks.
	 *
	 * @return void
	 */
	public function enqueue_bricks_assets() {
		Logger::log( sprintf( '%s: enqueue_bricks_assets', __METHOD__ ) );
		// Dequeue the frontend assets, which would otherwise still run in Bricks's builder context.
		wp_dequeue_script( 'color-scheme-switcher-frontend' );
		wp_dequeue_style( 'color-scheme-switcher-frontend' );
		// Script.
		$path     = '/Color_Scheme_Switcher/js';
		$filename = 'bricks.min.js';
		wp_enqueue_script(
			'color-scheme-switcher-bricks',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
		// Stylesheet.
		$path = '/Color_Scheme_Switcher/css';
		$filename = 'bricks.css';
		wp_enqueue_style(
			'color-scheme-switcher-bricks',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Enqueue scripts for Cwicly.
	 *
	 * @return void
	 */
	public function enqueue_cwicly_assets() {
		Logger::log( sprintf( '%s: enqueue_cwicly_assets', __METHOD__ ) );
		// Dequeue the frontend assets, which would otherwise still run in Cwicly's builder context.
		wp_dequeue_script( 'color-scheme-switcher-frontend' );
		wp_dequeue_style( 'color-scheme-switcher-frontend' );

		// Enqueue Popper.js.
		wp_enqueue_script(
			'popperjs-core',
			'https://unpkg.com/@popperjs/core@2',
			array(),
			'2.0.0',
			true
		);

		// Enqueue Tippy.js with dependency on Popper.js.
		wp_enqueue_script(
			'tippyjs',
			'https://unpkg.com/tippy.js@6',
			array( 'popperjs-core' ),
			'6.0.0',
			true
		);

		// Script.
		$path     = '/Color_Scheme_Switcher/js';
		$filename = 'cwicly.min.js';
		wp_enqueue_script(
			'color-scheme-switcher-cwicly',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
		// Stylesheet.
		$path = '/Color_Scheme_Switcher/css';
		$filename = 'cwicly.css';
		wp_enqueue_style(
			'color-scheme-switcher-cwicly',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}
}
