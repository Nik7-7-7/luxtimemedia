<?php

/**
 * Plugin Name:           AutomatorWP - GeoDirectory
 * Plugin URI:            https://automatorwp.com/add-ons/geodirectory/
 * Description:           Connect AutomatorWP with WP Geo Directory.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-geodirectory
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\GeoDirectory
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_GeoDirectory
{

    /**
     * @var         AutomatorWP_Integration_GeoDirectory $instance The one true AutomatorWP_Integration_GeoDirectory
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_GeoDirectory self::$instance The one true AutomatorWP_Integration_GeoDirectory
     */
    public static function instance()
    {
        if( ! self::$instance ) {

            self::$instance = new AutomatorWP_Integration_GeoDirectory();

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
    private function constants()
    {
        // Plugin version
        define('AUTOMATORWP_GEODIRECTORY_VER', '1.0.0');

        // Plugin file
        define('AUTOMATORWP_GEODIRECTORY_FILE', __FILE__);

        // Plugin path
        define('AUTOMATORWP_GEODIRECTORY_DIR', plugin_dir_path(__FILE__));

        // Plugin URL
        define('AUTOMATORWP_GEODIRECTORY_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes()
    {

        if ($this->meets_requirements()) {

            // Triggers
            require_once AUTOMATORWP_GEODIRECTORY_DIR . 'includes/triggers/anonymous-comment-posted.php';
            require_once AUTOMATORWP_GEODIRECTORY_DIR . 'includes/triggers/comment-posted.php';
            require_once AUTOMATORWP_GEODIRECTORY_DIR . 'includes/triggers/place-added.php';
            
        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks()
    {
        add_action('automatorwp_init', array($this, 'register_integration'));

    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration()
    {

        automatorwp_register_integration('geodirectory', array(
            'label' => 'GeoDirectory',
            'icon'  => AUTOMATORWP_GEODIRECTORY_URL . 'assets/geodirectory.svg',
        ));
    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements()
    {

        if (!class_exists('AutomatorWP')) {
            return false;
        }

        if (!class_exists('GeoDirectory')) {
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

        if ( ! class_exists( 'AutomatorWP_GeoDirectory' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_GeoDirectory instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_GeoDirectory The one true AutomatorWP_Integration_GeoDirectory
 */
function AutomatorWP_Integration_GeoDirectory()
{
    return AutomatorWP_Integration_GeoDirectory::instance();
}

add_action('automatorwp_pre_init', 'AutomatorWP_Integration_GeoDirectory');
