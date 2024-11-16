<?php

namespace MotoPress\Appointment\Metaboxes\Booking;

use MotoPress\Appointment\Metaboxes\CustomMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.6.2
 */
class BookingLogMetabox extends CustomMetabox {


	/**
	 * @since 1.6.2
	 */
	protected function theName(): string {
		return 'booking_log_metabox';
	}

	/**
	 * @since 1.6.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Logs', 'motopress-appointment' );
	}

	/**
	 * @since 1.6.2
	 */
	protected function renderMetabox(): string {
		$post = get_post();

		$bookingLogs = mpapp()->postTypes()->booking()->logs()->getLogs( $post->ID );

		$wpDateFormat = get_option( 'date_format' );
		$wpTimeFormat = get_option( 'time_format' );

		$output = '<br>';

		$output .= '<textarea rows="3" name="_mpa_add_log" style="width:100%" placeholder="' . esc_attr( __( 'You can add a new log message here and press Update to save it', 'motopress-appointment' ) ) . '"></textarea>';

		$output .= '<br>';
		$output .= '<br>';

		foreach ( $bookingLogs as $log ) {
			$output .= '<strong>' . esc_html( mysql2date( $wpDateFormat . '@' . $wpTimeFormat, $log->comment_date ) ) . '</strong>';

			$output .= '<br>';

			$output .= '<span>' . esc_html( $log->comment_content ) . '</span>';

			$output .= '<br>';
			$output .= '<br>';
		}

		return $output;
	}

	/**
	 * @since 1.6.2
	 */
	protected function parseValues( int $postId, \WP_Post $post ): array {
		$parsedValues = array();

		if ( isset( $_POST['_mpa_add_log'] ) ) {

			$parsedValues['_mpa_add_log'] = sanitize_text_field( trim( $_POST['_mpa_add_log'] ) );
		}

		return $parsedValues;
	}

	/**
	 * @since 1.6.2
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {
		if ( ! empty( $values['_mpa_add_log'] ) ) {

			mpapp()->postTypes()->booking()->logs()->addLog( $postId, $values['_mpa_add_log'] );
		}
	}
}
