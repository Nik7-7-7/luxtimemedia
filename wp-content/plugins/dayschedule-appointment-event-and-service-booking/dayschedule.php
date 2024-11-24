<?php
/**
 * DaySchedule
 *
 * @package       DAYSCHEDUL
 * @author        Dayschedule
 * @license       gplv2
 * @version       1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:   DaySchedule: Appointment, event and service booking
 * Plugin URI:    https://dayschedule.com/
 * Description:   Appointment scheduling widget to embed on WordPress website and display your available calendar slots for booking. Accept bookings payment with Stripe, PayPal, Razorpay and send automatic reminders on email, WhatsApp etc. to reduce no-shows. To get started: <a href="https://app.dayschedule.com/signup" target="_blank">Signup on DaySchedule</a> to create your scheduling page, add services then use the <code>[dayschedule url="your link here"]</code> shortcode to embed on your website.
 * Version:       1.0.1
 * Author:        DaySchedule
 * Author URI:    https://dayschedule.com/widget
 * Text Domain:   dayschedule
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with DaySchedule. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This file contains the main information about the plugin.
 * It is used to register all components necessary to run the plugin.
 * 
 * The comment above contains all information about the plugin 
 * that are used by WordPress to differenciate the plugin and register it properly.
 * It also contains further PHPDocs parameter for a better documentation
 * 
 * The function DAYSCHEDUL() is the main function that you will be able to 
 * use throughout your plugin to extend the logic. Further information
 * about that is available within the sub classes.
 * 
 * HELPER COMMENT END
 */

// Plugin name
define( 'DAYSCHEDUL_NAME',			'DaySchedule' );

// Plugin version
define( 'DAYSCHEDUL_VERSION',		'1.0.1' );

// Plugin Root File
define( 'DAYSCHEDUL_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'DAYSCHEDUL_PLUGIN_BASE',	plugin_basename( DAYSCHEDUL_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'DAYSCHEDUL_PLUGIN_DIR',	plugin_dir_path( DAYSCHEDUL_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'DAYSCHEDUL_PLUGIN_URL',	plugin_dir_url( DAYSCHEDUL_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once DAYSCHEDUL_PLUGIN_DIR . 'core/class-dayschedule.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Dayschedule
 * @since   1.0.0
 * @return  object|Dayschedule
 */
function DAYSCHEDUL() {
	return Dayschedule::instance();
}

DAYSCHEDUL();
