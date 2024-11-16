<?php
/**
 * Plugin Name:           AutomatorWP - Pretty Link integration
 * Plugin URI:            https://automatorwp.com/add-ons/pretty-link/
 * Description:           Connect AutomatorWP with Pretty Link.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-pretty-link
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Pretty_Link
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Pretty_Link {

    /**
     * @var         AutomatorWP_Integration_Pretty_Link $instance The one true AutomatorWP_Integration_Pretty_Link
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Pretty_Link self::$instance The one true AutomatorWP_Integration_Pretty_Link
     */
    public static function instance() {
        if( ! self::$instance ) {

            self::$instance = new AutomatorWP_Integration_Pretty_Link();

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
        define( 'AUTOMATORWP_PRETTY_LINK_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_PRETTY_LINK_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_PRETTY_LINK_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_PRETTY_LINK_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_PRETTY_LINK_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_PRETTY_LINK_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_PRETTY_LINK_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_PRETTY_LINK_DIR . 'includes/triggers/redirection.php';
            
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

        automatorwp_register_integration( 'pretty_link', array(
            'label' => 'Pretty Link',
            'icon'  => AUTOMATORWP_PRETTY_LINK_URL . 'assets/pretty-link.svg',
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

        if ( ! function_exists( 'prli_plugin_info' ) ) {
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

        if ( ! class_exists( 'AutomatorWP_Pretty_Link' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Pretty_Link instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_Pretty_Link The one true AutomatorWP_Integration_Pretty_Link
 */
function AutomatorWP_Integration_Pretty_Link() {
    return AutomatorWP_Integration_Pretty_Link::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_Pretty_Link' );
