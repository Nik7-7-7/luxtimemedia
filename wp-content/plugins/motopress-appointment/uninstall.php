<?php

/**
 * Appointment Booking Uninstall
 *
 * @since 1.7.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'motopress-appointment.php';

use \MotoPress\Appointment\Handlers\SecurityHandler;

if ( is_multisite() &&
	is_plugin_active_for_network( basename( dirname( WP_UNINSTALL_PLUGIN ) ) . '/motopress-appointment.php' ) ) {

	$blogIds = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $blogIds as $blogId ) {
		switch_to_blog( $blogId );

		SecurityHandler::removeAppointmentRolesAndCapabilities();

		restore_current_blog();
	}
} else {
	SecurityHandler::removeAppointmentRolesAndCapabilities();
}

wp_cache_flush();
