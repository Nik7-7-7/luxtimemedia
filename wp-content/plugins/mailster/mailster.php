<?php
/*
Plugin Name: Mailster - Email Newsletter Plugin for WordPress
Plugin URI: https://mailster.co
Description: Send Beautiful Email Newsletters in WordPress.
Version: 4.1.4
Author: EverPress
Author URI: https://everpress.co
Text Domain: mailster
*/
class MailsterFsNull {
function _get_license() {
return (object) ['secret_key'=>'*******'];
}

function is_registered() {
return true;
}

function is_trial() {
return false;
}

function is__premium_only() {
return true;
}

function can_use_premium_code() {
return true;
}

function can_use_premium_code__premium_only() {
return true;
}

function get_upgrade_url() {
return false;
}

function contact_url() {
return false;
}

function checkout_url() {
return false;
}

function get_id() {
return 1;
}

function get_plan() {
return 1;
}

function get_plan_name() {
return 'business';
}

function is_plan( $plan, $exact = false ) {
return false;
}

function is_whitelabeled() {
return false;
}

function get_user() {
return wp_get_current_user();
}

function get_account_url( $action = false, $params = array(), $add_action_nonce = true ) {
return '';
}

function has_affiliate_program() {
return false;
}

function is_activation_mode( $and_on = true ) {
return false;
}
}

if ( !function_exists( 'mailster_freemius' ) ) {
function mailster_freemius()
{
global $mailster_freemius;
if ( !isset( $mailster_freemius ) ) {
$mailster_freemius = new MailsterFsNull();
}
return $mailster_freemius;
}
do_action( 'mailster_freemius_loaded' );
mailster_freemius();
}

if ( defined( 'MAILSTER_VERSION' ) || ! defined( 'ABSPATH' ) ) {
	return;
}

define( 'MAILSTER_VERSION', '4.1.4' );
define( 'MAILSTER_BUILT', 1727251311 );
define( 'MAILSTER_ENVATO', true );
define( 'MAILSTER_DBVERSION', 20230517 );
define( 'MAILSTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAILSTER_URI', plugin_dir_url( __FILE__ ) );
define( 'MAILSTER_FILE', __FILE__ );
define( 'MAILSTER_SLUG', basename( MAILSTER_DIR ) . '/' . basename( __FILE__ ) );

$upload_folder = wp_upload_dir();

if ( ! defined( 'MAILSTER_UPLOAD_DIR' ) ) {
	define( 'MAILSTER_UPLOAD_DIR', $upload_folder['basedir'] . '/mailster' );
}
if ( ! defined( 'MAILSTER_UPLOAD_URI' ) ) {
	define( 'MAILSTER_UPLOAD_URI', $upload_folder['baseurl'] . '/mailster' );
}

require_once MAILSTER_DIR . 'vendor/autoload.php';
require_once MAILSTER_DIR . 'includes/check.php';
require_once MAILSTER_DIR . 'includes/functions.php';
require_once MAILSTER_DIR . 'includes/wp_mail.php';
require_once MAILSTER_DIR . 'includes/deprecated.php';
require_once MAILSTER_DIR . 'includes/3rdparty.php';
require_once MAILSTER_DIR . 'classes/mailster.class.php';

add_action( 'plugins_loaded', array( 'Mailster', 'get_instance' ), 1 );

register_activation_hook( MAILSTER_FILE, 'mailster_on_activate' );
register_deactivation_hook( MAILSTER_FILE, 'mailster_on_deactivate' );
