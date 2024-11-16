<?php
defined( 'ABSPATH' ) or die();

add_filter( 'rsssl_integrations_path', 'rsssl_pro_integrations_path', 10, 3 );
function rsssl_pro_integrations_path( $path, $plugin, $details ) {
	if ( $details['is_pro'] ) {
		$path = rsssl_path . 'pro/';
	}

	return $path;
}

function rsssl_pro_integrations( $integrations ) {
	$premium_integrations = [
//		'file-change-detection' => array(
//			'folder'    => 'wordpress/file-change-detection',
//			'option_id' => 'file_change_detection',
//			'admin_only' => true,
//			'has_deactivation' => true,
//		),
		'permission-detection' => array(
			'folder'    => 'wordpress/permission-detection',
			'option_id' => 'permission_detection',
			'admin_only' => true,
		),
		'disable-http-methods'          => array(
			'folder'    => 'wordpress',
			'option_id' => 'disable_http_methods',
		),
		'xmlrpc'                        => array(
			'folder'         => 'wordpress',
			'always_include' => true,
		),
		'debug-log'                     => array(
			'folder'           => 'wordpress',
			'option_id'        => 'change_debug_log_location',
			'always_include'   => false,
			'has_deactivation' => true,
		),
		'rename-db-prefix'              => array(
			'folder'        => 'wordpress',
			'learning_mode' => false,
			'option_id'     => 'rename_db_prefix',
		),
		'application-passwords'         => array(
			'folder'           => 'wordpress',
			'learning_mode'    => false,
			'option_id'        => 'disable_application_passwords',
			'always_include'   => false,
			'has_deactivation' => true,
		),
		'change-login-url'              => array(
			'folder'         => 'wordpress',
			'option_id'      => 'change_login_url',
			'always_include' => false,
		),
		'vulnerabilities-pro'           => array(
			'folder'         => 'wordpress',
			'option_id'      => 'enable_vulnerability_scanner',
			'always_include' => false,
			'admin_only'     => true,
		),
		'class-rsssl-event-log'         => array(
			'folder'         => 'wordpress',
			'option_id'      => 'event_log_enabled',
			'always_include' => false,
			'admin_only'     => false,
		),
		'class-rsssl-event-listener'    => array(
			'folder'         => 'wordpress',
			'option_id'      => 'enable_limited_login_attempts',
			'always_include' => false,
			'admin_only'     => false,
		),
		'class-rsssl-limit-login-admin'  => array(
			'folder'         => 'wordpress',
			'option_id'      => 'enable_limited_login_attempts',
			'admin_only'     => true,
			'always_include' => false,
		),
		'class-rsssl-geo-block'  => array(
			'folder'         => 'wordpress',
			'option_id'      => 'enable_firewall',
			'admin_only'     => true,
			'always_include' => false,
			'has_deactivation' => true,
		),
		'class-rsssl-404-interceptor'        => array(
			'folder'         => 'wordpress/firewall',
			'option_id'      => 'enable_firewall',
			'always_include' => false,
			'admin_only'     => false,
		),
		'class-rsssl-admin-config-countries'  => array(
			'folder'         => 'wordpress/limitlogin',
			'option_id'      => 'enable_limited_login_attempts',
			'admin_only'     => true,
			'always_include' => false,
		),
		'class-rsssl-password-security' => array(
			'folder'         => 'wordpress',
			'option_id'      => 'enforce_password_security_enabled',
			'always_include' => false,
			'admin_only'     => false,
		),
		'block-admin-creation'          => array(
			'folder'         => 'wordpress',
			'option_id'      => 'block_admin_creation',
			'always_include' => false,
			'admin_only'     => false,
		),
		'login-cookie-expiration'   => array(
			'folder'         => 'wordpress',
			'option_id'      => 'login_cookie_expiration',
			'always_include' => true,
			'admin_only'     => false,
		),
		'class-rsssl-captcha-config'    => array(
			'folder'         => 'wordpress',
			'option_id'      => 'enabled_captcha_provider',
			'option_value'      => 'NOT none',
			'always_include' => false,
			'admin_only'     => true,
		),
		'hide-rememberme'   => array(
			'folder'         => 'wordpress',
			'option_id'      => 'hide_rememberme',
			'always_include' => false,
			'admin_only'     => false,
		),
		'hibp-password-check' => array(
			'folder'               => 'wordpress',
			'option_id'            => 'enable_hibp_check',
			'admin_only'           => false,
			'always_include'       => false,
		),
	];

	$premium_integrations = array_map( static function($value) {
		$value['is_pro'] = true;
		return $value;
	}, $premium_integrations);

	$integrations += $premium_integrations;

	return $integrations;
}

add_filter( 'rsssl_integrations', 'rsssl_pro_integrations' );

/**
 * Load only on back-end
 */
$path = rsssl_path . '/pro/security/';
if ( rsssl_admin_logged_in() ) {
	require_once( $path . 'tests.php' );
	require_once( $path . 'notices.php' );
}
