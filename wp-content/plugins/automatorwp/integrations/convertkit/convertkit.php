<?php
/**
 * Plugin Name:           AutomatorWP - ConvertKit
 * Plugin URI:            https://automatorwp.com/add-ons/convertkit/
 * Description:           Connect AutomatorWP with ConvertKit.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-convertkit
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\ConvertKit
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_ConvertKit {

    /**
     * @var         AutomatorWP_Integration_ConvertKit $instance The one true AutomatorWP_Integration_ConvertKit
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_ConvertKit self::$instance The one true AutomatorWP_Integration_ConvertKit
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_ConvertKit();
            
            if( ! self::$instance->pro_installed() ) {

                self::$instance->constants();
                self::$instance->includes();
                
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_CONVERTKIT_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_CONVERTKIT_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_CONVERTKIT_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_CONVERTKIT_URL', plugin_dir_url( __FILE__ ) );
    }


    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            // Includes
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/actions/add-user-form.php';
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/actions/add-user-sequence.php';
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/actions/add-user-tag.php';
            require_once AUTOMATORWP_CONVERTKIT_DIR . 'includes/actions/remove-user-tag.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );

    }

    
    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'convertkit', array(
            'label' => 'ConvertKit',
            'icon'  => AUTOMATORWP_CONVERTKIT_URL . 'assets/convertkit.svg',
        ) );

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */
    private function pro_installed() {

        if ( ! class_exists( 'AutomatorWP_ConvertKit' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_ConvertKit instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_ConvertKit The one true AutomatorWP_Integration_ConvertKit
 */
function AutomatorWP_Integration_ConvertKit() {
    return AutomatorWP_Integration_ConvertKit::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_ConvertKit' );
