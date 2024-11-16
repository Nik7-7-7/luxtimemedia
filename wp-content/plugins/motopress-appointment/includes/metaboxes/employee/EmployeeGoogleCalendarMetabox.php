<?php

namespace MotoPress\Appointment\Metaboxes\Employee;

use MotoPress\Appointment\Metaboxes\CustomMetabox;
use MotoPress\Appointment\Handlers\GoogleCalendarSyncHandler;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @since 1.10.0
 */
class EmployeeGoogleCalendarMetabox extends CustomMetabox {

	/**
	 * @param string $postType
	 * @param string $context Optional. 'side', 'normal' or 'advanced'. 'normal' by default.
	 * @param string $priority Optional. 'low', 'default' or 'high'. 'default' by default.
	 *
	 * @since 1.10.0
	 */
	public function __construct( $postType, $context = 'normal', $priority = 'default' ) {
		parent::__construct( $postType, $context, $priority );

		// constructor is called after load-post.php action so we can disconnect google calendar
		$mpa_employee_google_calendar_disconnect = ! empty( $_GET['mpa_employee_google_calendar_disconnect'] );

		if ( $mpa_employee_google_calendar_disconnect ) {

			$postId = absint( $_GET['post'] );

			GoogleCalendarSyncHandler::disconnectGoogleCalendarFromEmployee( $postId );

			wp_redirect( admin_url( 'post.php?action=edit&post=' . $postId ) );
			exit;
		}
	}

	/**
	 * @since 1.10.0
	 */
	protected function theName(): string {
		return 'employee_google_calendar_metabox';
	}

	/**
	 * @since 1.10.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Employee Google Calendar', 'motopress-appointment' );
	}

	/**
	 * @since 1.10.0
	 */
	protected function renderMetabox(): string {

		global $post;

		$result = '';

		

		if ( GoogleCalendarSyncHandler::isGoogleCalendarConnectedToEmployee( $post->ID ) ) {

			$result = '<p class="mpa-employee-google-calendar__status">' .
				esc_html__( 'Connected', 'motopress-appointment' ) .
				'</p>
                <p class="mpa-employee-google-calendar__connect-btn">
                    <a href="' . esc_attr( admin_url( 'post.php?action=edit&mpa_employee_google_calendar_disconnect=1&post=' . $post->ID ) ) . '">' .
						esc_html__( 'Disconnect Calendar', 'motopress-appointment' ) .
					'</a>
                </p>';

		} else {

			$connectURL = GoogleCalendarSyncHandler::getGoogleCalendarConnectionURL( $post->ID );
			$result    .= '<p class="mpa-employee-google-calendar__connect-btn"><a ' . ' href="' .
				esc_attr( $connectURL ) . '">' . esc_html__( 'Connect to Calendar', 'motopress-appointment' ) . '</a></p>';
		}

		return $result;
	}
}
