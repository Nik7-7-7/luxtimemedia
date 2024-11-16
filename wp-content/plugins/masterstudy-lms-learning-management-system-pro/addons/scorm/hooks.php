<?php

add_filter(
	'masterstudy_lms_course_curriculum',
	function ( $curriculum, $course_id ) {
		$scorm = STM_LMS_Scorm_Packages::get_scorm_meta( $course_id );

		$curriculum['scorm'] = empty( $scorm ) ? null : $scorm;

		return $curriculum;

	},
	10,
	2
);

add_filter(
	'masterstudy_lms_course_player_data',
	function ( $data ) {
		$data['is_scorm_course'] = \STM_LMS_Scorm_Packages::is_scorm_course( $data['post_id'] );

		return $data;
	}
);
