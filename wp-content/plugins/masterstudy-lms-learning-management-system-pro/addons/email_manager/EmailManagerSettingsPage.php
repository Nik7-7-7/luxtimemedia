<?php

namespace MasterStudy\Lms\Pro\addons\email_manager;

use STM_LMS_Helpers;

class EmailManagerSettingsPage {
	/**
	 * @param array $pages
	 */
	public static function setup( $pages ): array {
		$pages[] = array(
			'page'        => array(
				'parent_slug' => 'stm-lms-settings',
				'page_title'  => 'Email Manager',
				'menu_title'  => 'Email Manager',
				'menu_slug'   => 'email_manager_settings',
			),
			'fields'      => self::fields(),
			'option_name' => 'stm_lms_email_manager_settings',
		);

		return $pages;
	}

	private static function fields(): array {
		$sections = array(
			'instructors' => esc_html__(
				'Instructors',
				'masterstudy-lms-learning-management-system-pro'
			),
			'lessons'     => esc_html__( 'Lessons', 'masterstudy-lms-learning-management-system-pro' ),
			'account'     => esc_html__( 'Account', 'masterstudy-lms-learning-management-system-pro' ),
			'enterprise'  => esc_html__( 'Enterprise', 'masterstudy-lms-learning-management-system-pro' ),
			'order'       => esc_html__( 'Orders', 'masterstudy-lms-learning-management-system-pro' ),
			'course'      => esc_html__( 'Course', 'masterstudy-lms-learning-management-system-pro' ),
			'assignment'  => esc_html__( 'Assignment', 'masterstudy-lms-learning-management-system-pro' ),
		);

		if ( STM_LMS_Helpers::is_pro_plus() ) {
			$sections['reports'] = esc_html__( 'Reports', 'masterstudy-lms-learning-management-system-pro' );
		}

		$emails = require __DIR__ . '/emails.php';
		$emails = apply_filters( 'stm_lms_email_manager_emails', $emails );
		$data   = array();

		foreach ( $sections as $section_key => $section ) {
			$data[ $section_key ] = array(
				'name'   => $section,
				'fields' => array(),
			);
		}
		$email_settings = get_option( 'stm_lms_email_manager_settings', array() );

		$digest_mapping = array(
			'stm_lms_reports_student_checked'    => $email_settings['stm_lms_reports_student_checked_enable'] ?? false,
			'stm_lms_reports_instructor_checked' => $email_settings['stm_lms_reports_instructor_checked_enable'] ?? false,
		);

		foreach ( $emails as $email_key => $email ) {
			$value = $digest_mapping[ $email_key ] ?? true;

			$data[ $email['section'] ]['fields'][ "{$email_key}_enable" ] = array(
				'group' => 'started',
				'type'  => 'checkbox',
				'label' => $email['notice'],
				'value' => $value,
			);
			if ( $email['subject'] ?? null ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_subject" ] = array(
					'type'       => 'text',
					'label'      => esc_html__( 'Subject', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => $email['subject'] ?? null,
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}
			if ( $email['frequency'] ?? false ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_frequency" ] = array(
					'type'       => 'select',
					'label'      => esc_html__( 'Frequency', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => 'weekly',
					'options'    => array(
						'weekly'  => esc_html__( 'Weekly', 'masterstudy-lms-learning-management-system-pro' ),
						'monthly' => esc_html__( 'Monthly', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}
			if ( $email['period'] ?? false ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_period" ] = array(
					'type'       => 'select',
					'label'      => esc_html__( 'Day of week to send', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => 'monday',
					'options'    => array(
						'monday'    => esc_html__( 'Monday', 'masterstudy-lms-learning-management-system-pro' ),
						'tuesday'   => esc_html__( 'Tuesday', 'masterstudy-lms-learning-management-system-pro' ),
						'wednesday' => esc_html__( 'Wednesday', 'masterstudy-lms-learning-management-system-pro' ),
						'thursday'  => esc_html__( 'Thursday', 'masterstudy-lms-learning-management-system-pro' ),
						'friday'    => esc_html__( 'Friday', 'masterstudy-lms-learning-management-system-pro' ),
						'saturday'  => esc_html__( 'Saturday', 'masterstudy-lms-learning-management-system-pro' ),
						'sunday'    => esc_html__( 'Sunday', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}
			if ( $email['time'] ?? false ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_time" ] = array(
					'type'       => 'time',
					'label'      => esc_html__( 'Time', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => ( ( new EmailManagerSettingsPage )->get_email_time_period() ),// phpcs:ignore
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}
			if ( $email['title'] ?? false ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_title" ] = array(
					'type'       => 'text',
					'label'      => esc_html__( 'Title', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => esc_html__( 'Your Weekly Report', 'masterstudy-lms-learning-management-system-pro' ),
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}

			$email_textarea = 'hint_textarea';
			if ( defined( 'STM_WPCFTO_VERSION' ) && STM_LMS_Helpers::is_pro_plus() ) {
				$email_textarea = 'trumbowyg';
			}
			$data[ $email['section'] ]['fields'][ $email_key ] = array(
				'type'       => $email_textarea,
				'group'      => ( STM_LMS_Helpers::is_pro_plus() ) ? self::is_not_reports_email_key( $email_key ) ? '' : 'ended' : 'ended',
				'label'      => esc_html__( 'Message', 'masterstudy-lms-learning-management-system-pro' ),
				'value'      => $email['message'],
				'hints'      => $email['vars'],
				'dependency' => array(
					'key'   => "{$email_key}_enable",
					'value' => 'not_empty',
				),
			);
			if ( STM_LMS_Helpers::is_pro_plus() && self::is_not_reports_email_key( $email_key ) ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_hidden" ] = array(
					'type'       => 'send_email',
					'group'      => 'ended',
					'label'      => esc_html__( 'Hidden', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => $email_key,
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}
		}

		return apply_filters( 'stm_lms_email_manager_settings', $data );
	}

	private function get_email_time_period() {
		static $dynamic_value = 6;
		if ( $dynamic_value < 10 ) {
			$result = '0' . $dynamic_value . ':00';
		} else {
			$result = $dynamic_value . ':00';
		}
		$dynamic_value ++;

		return $result;
	}

	private static function is_not_reports_email_key( string $email_key ) {
		return 'stm_lms_reports_admin_checked' !== $email_key && 'stm_lms_reports_student_checked' !== $email_key && 'stm_lms_reports_instructor_checked' !== $email_key;
	}
}
