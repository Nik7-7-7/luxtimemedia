<?php
function get_date_range( $role, $settings ) {
	$current_date      = current_time( 'Y-m-d' );
	$frequency_choosed = 'weekly';
	$role_choosed      = 'weekly';

	switch ( $role ) {
		case 'subscriber':
			$frequency = $settings['stm_lms_reports_student_checked_frequency'] ?? $frequency_choosed;
			break;
		case 'stm_lms_instructor':
			$frequency = $settings['stm_lms_reports_instructor_checked_frequency'] ?? $frequency_choosed;
			break;
		case 'admin':
		case 'administrator':
			$frequency = $settings['stm_lms_reports_admin_checked_frequency'] ?? $frequency_choosed;
			break;
		default:
			$frequency = $frequency_choosed;
			break;
	}

	if ( 'monthly' === $frequency ) {
		$date_from         = date( 'Y-m-01' );
		$date_to           = date( 'Y-m-t' );
		$frequency_choosed = 'monthly';
	} elseif ( 'weekly' === $frequency ) {
		$date_from = date( 'Y-m-d', strtotime( '-6 days', strtotime( $current_date ) ) );
		$date_to   = $current_date;
	} else {
		$date_from = date( 'Y-m-d', strtotime( '-6 days', strtotime( $current_date ) ) );
		$date_to   = $current_date;
	}

	return array(
		'date_from' => $date_from,
		'date_to'   => $date_to,
		'frequency' => $frequency_choosed,
		'role'      => $role_choosed,
	);
}

function get_subject_by_role( $role, $settings ) {
	$default_subject = esc_html__( 'Your Weekly Report', 'masterstudy-lms-learning-management-system-pro' );
	switch ( $role ) {
		case 'subscriber':
			$email_subject = $settings['stm_lms_reports_student_checked_title'] ?? $default_subject;
			break;
		case 'stm_lms_instructor':
			$email_subject = $settings['stm_lms_reports_instructor_checked_title'] ?? $default_subject;
			break;
		case 'admin':
		case 'administrator':
			$email_subject = $settings['stm_lms_reports_admin_checked_title'] ?? $default_subject;
			break;
		default:
			$email_subject = $default_subject;
			break;
	}

	return $email_subject;
}

function get_message_by_role( $role, $settings ) {
	$default_subject = esc_html__( 'Your Weekly Report', 'masterstudy-lms-learning-management-system-pro' );
	switch ( $role ) {
		case 'subscriber':
			$email_subject = $settings['stm_lms_reports_student_checked'] ?? $default_subject;
			break;
		case 'stm_lms_instructor':
			$email_subject = $settings['stm_lms_reports_instructor_checked'] ?? $default_subject;
			break;
		case 'admin':
		case 'administrator':
			$email_subject = $settings['stm_lms_reports_admin_checked'] ?? $default_subject;
			break;
		default:
			$email_subject = $default_subject;
			break;
	}

	return $email_subject;
}

if ( STM_LMS_Helpers::is_pro_plus() && is_ms_lms_addon_enabled( 'email_manager' ) ) {
	add_filter(
		'wpcfto_field_emails-links',
		function () {
			return STM_LMS_PRO_ADDONS . '/email_manager/components/emails-links/fields/emails-links.php';
		}
	);
}

function check_if_cron_event_exists( $hook, $args = array() ) {
	$event = wp_get_scheduled_event( $hook, $args );

	if ( $event ) {
		return true;
	} else {
		return false;
	}
}

function schedule_admin_email_digest() {
	if ( ! check_if_cron_event_exists( 'send_admin_email_digest_event' ) ) {
		$day            = 'Monday';
		$time           = '11:00 AM';
		$next_scheduled = strtotime( "next $day $time" );

		wp_schedule_event( $next_scheduled, 'weekly', 'send_admin_email_digest_event' );
	}
}

if ( ! check_if_cron_event_exists( 'send_admin_email_digest_event' ) ) {
	add_action( 'admin_init', 'schedule_admin_email_digest' );
}
