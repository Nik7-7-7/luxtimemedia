<?php
/**
 * Plugin Name:           AutomatorWP - ARForms
 * Plugin URI:            https://automatorwp.com/add-ons/arforms/
 * Description:           Connect AutomatorWP with ARForms.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-arforms
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\ARForms
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_ARForms {

    /**
     * @var         AutomatorWP_Integration_ARForms $instance The one true AutomatorWP_Integration_ARForms
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_ARForms self::$instance The one true AutomatorWP_Integration_ARForms
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_ARForms();
            
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
        define( 'AUTOMATORWP_ARFORMS_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_ARFORMS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_ARFORMS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_ARFORMS_URL', plugin_dir_url( __FILE__ ) );
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

            // Triggers
            require_once AUTOMATORWP_ARFORMS_DIR . 'includes/triggers/submit-form.php';
           
            // Anonymous Triggers
            require_once AUTOMATORWP_ARFORMS_DIR . 'includes/triggers/anonymous-submit-form.php';

            // Includes
            require_once AUTOMATORWP_ARFORMS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_ARFORMS_DIR . 'includes/functions.php';

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

        automatorwp_register_integration( 'arforms', array(
            'label' => 'ARForms',
            'icon'  => AUTOMATORWP_ARFORMS_URL . 'assets/arforms.svg',
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

        if ( ! class_exists('arformcontroller') ) { // PRO version
            if ( ! class_exists('arfliteformcontroller') ) { // Free version
                return false;
            }
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

        if ( ! class_exists( 'AutomatorWP_ARForms' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_ARForms instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_ARForms The one true AutomatorWP_Integration_ARForms
 */
function AutomatorWP_Integration_ARForms() {
    return AutomatorWP_Integration_ARForms::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_ARForms' );
