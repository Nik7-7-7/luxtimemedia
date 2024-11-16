<?php

use MasterStudy\Lms\Pro\addons\email_manager\EmailDataCompiler;
use MasterStudy\Lms\Pro\addons\email_manager\EmailManagerSettingsPage;

add_filter( 'stm_lms_filter_email_data', array( EmailDataCompiler::class, 'compile' ), 10, 1 );
add_filter( 'wpcfto_options_page_setup', array( EmailManagerSettingsPage::class, 'setup' ), 100 );

function get_user_types() {
	return array(
		'student'    => array(
			'enable'    => 'stm_lms_reports_student_checked_enable',
			'frequency' => 'stm_lms_reports_student_checked_frequency',
			'day'       => 'stm_lms_reports_student_checked_period',
			'time'      => 'stm_lms_reports_student_checked_time',
			'event'     => 'send_student_email_digest_event',
			'callback'  => 'send_student_email_digest_callback',
			'role'      => 'student',
		),
		'instructor' => array(
			'enable'    => 'stm_lms_reports_instructor_checked_enable',
			'frequency' => 'stm_lms_reports_instructor_checked_frequency',
			'day'       => 'stm_lms_reports_instructor_checked_period',
			'time'      => 'stm_lms_reports_instructor_checked_time',
			'event'     => 'send_instructor_email_digest_event',
			'callback'  => 'send_instructor_email_digest_callback',
			'role'      => 'instructor',
		),
		'admin'      => array(
			'enable'    => 'stm_lms_reports_admin_checked_enable',
			'frequency' => 'stm_lms_reports_admin_checked_frequency',
			'day'       => 'stm_lms_reports_admin_checked_period',
			'time'      => 'stm_lms_reports_admin_checked_time',
			'event'     => 'send_admin_email_digest_event',
			'callback'  => 'send_admin_email_digest_callback',
			'role'      => 'administrator',
		),
	);
}

function custom_cron_schedules( $schedules ) {
	if ( ! isset( $schedules['weekly'] ) ) {
		$schedules['weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly' ),
		);
	}
	if ( ! isset( $schedules['monthly'] ) ) {
		$schedules['monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Once Monthly' ),
		);
	}

	return $schedules;
}
add_filter( 'cron_schedules', 'custom_cron_schedules' );

function schedule_digest_cron() {
	$user_types = get_user_types();
	$settings   = get_option( 'stm_lms_email_manager_settings', array() );

	foreach ( $user_types as $user_type => $user_settings ) {
		$event_name = $user_settings['event'];

		$scheduled_timestamp = wp_next_scheduled( $event_name );
		if ( $scheduled_timestamp ) {
			wp_unschedule_event( $scheduled_timestamp, $event_name );
		}

		if ( ! empty( $settings[ $user_settings['enable'] ] ) ) {
			$frequency = $settings[ $user_settings['frequency'] ];
			$day       = $settings[ $user_settings['day'] ];
			$time      = $settings[ $user_settings['time'] ];

			if ( ! in_array( $frequency, array( 'weekly', 'monthly' ), true ) ) {
				continue;
			}

			if ( 'monthly' === $frequency ) {
				$next_scheduled = strtotime( "first day of next month $time" );
			} else {
				$next_scheduled = strtotime( "next $day $time" );
			}

			if ( false === $next_scheduled ) {
				continue;
			}

			wp_schedule_event( $next_scheduled, $frequency, $event_name );
		}
	}
}
add_action( 'wpcfto_after_settings_saved', 'schedule_digest_cron' );

function unschedule_digest_cron() {
	$user_types = get_user_types();
	foreach ( $user_types as $user_type => $user_settings ) {
		$event_name          = $user_settings['event'];
		$scheduled_timestamp = wp_next_scheduled( $event_name );
		if ( $scheduled_timestamp ) {
			wp_unschedule_event( $scheduled_timestamp, $event_name );
		}
	}
}
register_deactivation_hook( __FILE__, 'unschedule_digest_cron' );

function process_user_emails( $role_to_check ) {
	$number = 20;
	$page   = 1;
	$email_subject = get_subject_by_role( $role_to_check, get_option( 'stm_lms_email_manager_settings', array() ) );

	do {
		$user_query = new WP_User_Query(
			array(
				'number'     => $number,
				'paged'      => $page,
				'fields'     => array( 'ID', 'user_email' ),
				'meta_query' => array(
					array(
						'key' => 'disable_report_email_notifications',
						'compare' => 'NOT EXISTS',
					),
				),
				'role'       => $role_to_check,
			)
		);
		$users = $user_query->get_results();

		if ( ! empty( $users ) ) {
			add_filter( 'wp_mail_content_type', 'STM_LMS_Helpers::set_html_content_type' );

			foreach ( $users as $user ) {
				$user_id    = $user->ID;
				$user_email = $user->user_email;

				$settings = array();

				if ( class_exists( 'STM_LMS_Email_Manager' ) ) {
					$settings = STM_LMS_Email_Manager::stm_lms_get_settings();
				}

				$message = STM_LMS_Templates::load_lms_template(
					'emails/report-template',
					array(
						'email_manager' => $settings,
						'role'          => $role_to_check,
						'user_id'       => $user_id,
					)
				);

				wp_mail( $user_email, $email_subject, $message );
			}

			remove_filter( 'wp_mail_content_type', 'STM_LMS_Helpers::set_html_content_type' );
		}

		$page++;

	} while ( ! empty( $users ) );
}

// Helper function to check if a specific digest is enabled
function is_digest_enabled( $digest_key ) {
	$email_settings = get_option( 'stm_lms_email_manager_settings', array() );
	return isset( $email_settings[ $digest_key ] ) && $email_settings[ $digest_key ];
}

function send_student_email_digest_callback() {
	if ( is_digest_enabled( 'stm_lms_reports_student_checked_enable' ) ) {
		process_user_emails( 'subscriber' );
	}
}
add_action( 'send_student_email_digest_event', 'send_student_email_digest_callback' );

function send_instructor_email_digest_callback() {
	if ( is_digest_enabled( 'stm_lms_reports_instructor_checked_enable' ) ) {
		process_user_emails( 'stm_lms_instructor' );
	}
}
add_action( 'send_instructor_email_digest_event', 'send_instructor_email_digest_callback' );

function send_admin_email_digest_callback() {
	$email_settings = get_option( 'stm_lms_email_manager_settings', array() );

	if ( is_digest_enabled( 'stm_lms_reports_admin_checked_enable' ) || empty( $email_settings ) ) {
		process_user_emails( 'administrator' );
	}
}
add_action( 'send_admin_email_digest_event', 'send_admin_email_digest_callback' );
