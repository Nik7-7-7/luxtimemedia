<?php
/**
 * Plugin Name:           AutomatorWP - Kali Forms
 * Plugin URI:            https://automatorwp.com/add-ons/kali-forms/
 * Description:           Connect AutomatorWP with Kali Forms.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-kali-forms
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Kali_Forms
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Kali_Forms {

    /**
     * @var         AutomatorWP_Integration_Kali_Forms $instance The one true AutomatorWP_Integration_Kali_Forms
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Kali_Forms self::$instance The one true AutomatorWP_Integration_Kali_Forms
     */
    public static function instance() {
        if( ! self::$instance ) {

            self::$instance = new AutomatorWP_Integration_Kali_Forms();

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
        define( 'AUTOMATORWP_KALI_FORMS_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_KALI_FORMS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_KALI_FORMS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_KALI_FORMS_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_KALI_FORMS_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_KALI_FORMS_DIR . 'includes/triggers/submit-form.php';
            
            // Anonymous Triggers
            require_once AUTOMATORWP_KALI_FORMS_DIR . 'includes/triggers/anonymous-submit-form.php';

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

        automatorwp_register_integration( 'kali_forms', array(
            'label' => 'Kali Forms',
            'icon'  => AUTOMATORWP_KALI_FORMS_URL . 'assets/kali-forms.svg',
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

        if ( ! class_exists( 'KaliForms\Inc\KaliForms' ) ) {
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

        if ( ! class_exists( 'AutomatorWP_Kali_Forms' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Kali_Forms instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Kali_Forms The one true AutomatorWP_Integration_Kali_Forms
 */
function AutomatorWP_Integration_Kali_Forms() {
    return AutomatorWP_Integration_Kali_Forms::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Kali_Forms' );
