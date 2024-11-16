<?php

use MasterStudy\Lms\Models\Course;

add_filter(
	'masterstudy_lms_course_access_validation_rules',
	function ( $rules ) {
		$rules['shareware'] = 'boolean';
		return $rules;
	}
);

add_filter(
	'masterstudy_lms_course_hydrate',
	function ( Course $course, $meta ) {
		$course->shareware = ( $meta['shareware'][0] ?? 'off' ) === 'on';
		return $course;
	},
	10,
	2
);

add_action(
	'masterstudy_lms_course_update_access',
	function ( $course_id, $data ) {
		if ( isset( $data['shareware'] ) ) {
			update_post_meta( $course_id, 'shareware', $data['shareware'] ? 'on' : 'off' );
		}
	},
	10,
	2
);

add_filter(
	'masterstudy_lms_course_guest_trial_enabled',
	function ( $value, $course_id ) {
		$shareware_settings = get_option( 'stm_lms_shareware_settings' );

		return ( new STM_LMS_Shareware() )->is_shareware( $course_id ) ? $shareware_settings['shareware_guest_trial'] ?? true : false;
	},
	10,
	2
);
