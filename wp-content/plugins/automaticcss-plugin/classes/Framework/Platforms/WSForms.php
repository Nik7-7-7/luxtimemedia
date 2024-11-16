<?php
/**
 * Automatic.css WSForms class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\Framework\Base;

/**
 * Automatic.css WSForms class.
 */
class WSForms extends Base implements Platform {

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( is_admin() ) {// Inform the SCSS compiler that we're using the Bricks platform.
			add_filter( 'automaticcss_framework_variables', array( $this, 'inject_scss_enabler_option' ) );
		}
	}

	/**
	 * Inject an SCSS variable in the CSS generation process to enable this module.
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return array
	 */
	public function inject_scss_enabler_option( $variables ) {
		$variables['option-ws-form'] = 'on';
		return $variables;
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		// I checked with class_exists( 'CT_Component' ), but it doesn't work here.
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'ws-form/ws-form.php' ) || is_plugin_active( 'ws-form-pro/ws-form.php' );
	}

}
