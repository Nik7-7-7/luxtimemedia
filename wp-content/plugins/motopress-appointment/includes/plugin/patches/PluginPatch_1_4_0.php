<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatch_1_4_0 extends AbstractPluginPatch {

	public static function getVersion(): string {
		return '1.4.0';
	}


	public static function execute(): bool {

		// Removes:
		// 1) admin and customer emails (only subjects, headers and messages);
		// 2) template parts.

		global $wpdb;

		$sql = "DELETE FROM {$wpdb->options}"
			. " WHERE `option_name` LIKE 'mpa_%_email_subject'"
				. " OR `option_name` LIKE 'mpa_%_email_header'"
				. " OR `option_name` LIKE 'mpa_%_email_message'"
				. " OR `option_name` LIKE 'mpa_%_reservation_details'";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql );

		return true;
	}
}
