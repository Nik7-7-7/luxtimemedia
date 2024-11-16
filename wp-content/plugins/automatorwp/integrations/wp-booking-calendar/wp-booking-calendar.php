<?php
/**
 * Plugin Name:           AutomatorWP - WP Booking Calendar
 * Plugin URI:            https://automatorwp.com/add-ons/wp-booking-calendar/
 * Description:           Connect AutomatorWP with WP Booking Calendar.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-wp-booking-calendar
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\WP_Booking_Calendar
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_WP_Booking_Calendar {

    /**
     * @var         AutomatorWP_Integration_WP_Booking_Calendar $instance The one true AutomatorWP_Integration_WP_Booking_Calendar
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_WP_Booking_Calendar self::$instance The one true AutomatorWP_Integration_WP_Booking_Calendar
     */
    public static function instance() {
        if( ! self::$instance ) {

            self::$instance = new AutomatorWP_Integration_WP_Booking_Calendar();

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
        define( 'AUTOMATORWP_WP_BOOKING_CALENDAR_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_WP_BOOKING_CALENDAR_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_WP_BOOKING_CALENDAR_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_WP_BOOKING_CALENDAR_URL', plugin_dir_url( __FILE__ ) );
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

            // Functions
            require_once AUTOMATORWP_WP_BOOKING_CALENDAR_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_WP_BOOKING_CALENDAR_DIR . 'includes/ajax-functions.php';

            // Triggers
            require_once AUTOMATORWP_WP_BOOKING_CALENDAR_DIR . 'includes/triggers/set-booking-approved.php';
            require_once AUTOMATORWP_WP_BOOKING_CALENDAR_DIR . 'includes/triggers/booking-cancelled.php';
            
            // Tags
            require_once AUTOMATORWP_WP_BOOKING_CALENDAR_DIR . 'includes/tags.php';
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

        automatorwp_register_integration( 'wp_booking_calendar', array(
            'label' => 'WP Booking Calendar',
            'icon'  => AUTOMATORWP_WP_BOOKING_CALENDAR_URL . 'assets/wp-booking-calendar.svg',
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

        if ( ! class_exists( 'Booking_Calendar' ) ) {
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

        if ( ! class_exists( 'AutomatorWP_WP_Booking_Calendar' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_WP_Booking_Calendar instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_WP_Booking_Calendar The one true AutomatorWP_Integration_WP_Booking_Calendar
 */
function AutomatorWP_Integration_WP_Booking_Calendar() {
    return AutomatorWP_Integration_WP_Booking_Calendar::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_WP_Booking_Calendar' );
