<?php
/**
 * Automatic.css Main file.
 *
 * @package Automatic_CSS
 */

/**
 * Plugin Name:       Automatic.css
 * Plugin URI:        https://automaticcss.com/
 * Description:       The #1 Utility Framework for WordPress Page Builders.
 * Version:           3.1.4
 * Requires at least: 5.9
 * Requires PHP:      7.3
 * Author:            Kevin Geary, Matteo Greco
 * Author URI:        https://automaticcss.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://automaticcss.com/
 * Text Domain:       automatic-css
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Define plugin directories and urls.
 */
define( 'ACSS_PLUGIN_FILE', __FILE__ );
define( 'ACSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ACSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACSS_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets' );
define( 'ACSS_ASSETS_DIR', plugin_dir_path( __FILE__ ) . 'assets' );
define( 'ACSS_CONFIG_DIR', plugin_dir_path( __FILE__ ) . 'config' );
define( 'ACSS_CLASSES_URL', plugin_dir_url( __FILE__ ) . 'classes' );
define( 'ACSS_CLASSES_DIR', plugin_dir_path( __FILE__ ) . 'classes' );
define( 'ACSS_FEATURES_URL', plugin_dir_url( __FILE__ ) . 'classes/Features' );
define( 'ACSS_FEATURES_DIR', plugin_dir_path( __FILE__ ) . 'classes/Features' );
define( 'ACSS_FRAMEWORK_URL', plugin_dir_url( __FILE__ ) . 'classes/Framework' );
define( 'ACSS_FRAMEWORK_DIR', plugin_dir_path( __FILE__ ) . 'classes/Framework' );

/**
 * Define plugin flags.
 */
if ( ! defined( 'ACSS_FLAG_ADD_DEFAULTS_TO_SAVE_PROCESS' ) ) {
	define( 'ACSS_FLAG_ADD_DEFAULTS_TO_SAVE_PROCESS', true );
}
if ( ! defined( 'ACSS_FLAG_BACKEND_VALIDATION' ) ) {
	define( 'ACSS_FLAG_BACKEND_VALIDATION', false );
}
if ( ! defined( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_IN_FOOTER' ) ) {
	define( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_IN_FOOTER', true );
}
if ( ! defined( 'ACSS_FLAG_DEFER_DASHBOARD_SCRIPTS' ) ) {
	define( 'ACSS_FLAG_DEFER_DASHBOARD_SCRIPTS', true );
}
if ( ! defined( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) ) {
	define( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_AS_MODULE', true ); // keeps the dashboard scripts loaded as a module.
}

/**
 * Load the plugin.
 */
require_once ACSS_PLUGIN_DIR . '/classes/Autoloader.php';
\Automatic_CSS\Autoloader::register();
\Automatic_CSS\Model\Database_Settings::hotfix_302();
\Automatic_CSS\Plugin::get_instance()->init();
