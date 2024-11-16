<?php
/* TODO: This will be changed or removed after the user account private page update*/
function stm_lms_front_co_instructor() {
	STM_LMS_Templates::show_lms_template( 'multi_instructor/front/main' );
}
add_action( 'stm_lms_after_teacher_end', 'stm_lms_front_co_instructor' );

/* TODO: This will be changed or removed after the user account private page update*/
function stm_lms_co_courses() {
	STM_LMS_Templates::show_lms_template( 'multi_instructor/co_courses/main' );
}
add_action( 'stm_lms_instructor_courses_end', 'stm_lms_co_courses' );
