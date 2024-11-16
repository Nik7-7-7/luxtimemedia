<?php
defined( 'ABSPATH' ) or die( "you do not have access to this page!" );

if ( rsssl_admin_logged_in() ) {
	require_once( rsssl_path . '/pro/upgrade.php' );
	require_once(rsssl_path . '/pro/csp-violation-endpoint.php');
	require_once(rsssl_path . '/pro/class-headers.php' );
	require_once(rsssl_path . '/pro/class-admin.php');
	require_once(rsssl_path . '/pro/class-scan.php');
	require_once(rsssl_path . '/pro/class-importer.php' );
	require_once(rsssl_path . '/pro/class-support.php');
	require_once(rsssl_path . '/pro/settings/settings.php');
}

require_once( rsssl_path . '/pro/security/security.php' );
require_once( rsssl_path . '/pro/front-end.php' );
require_once( rsssl_path . '/pro/csp-endpoint-public.php' );
require_once( rsssl_path . '/pro/class-licensing.php' );