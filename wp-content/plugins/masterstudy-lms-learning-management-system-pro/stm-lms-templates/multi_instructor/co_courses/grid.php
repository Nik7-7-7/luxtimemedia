<?php
/**
 * $current_user_id
 */

use MasterStudy\Lms\Pro\addons\MultiInstructors\Repository\MultiInstructorsRepository;

if ( empty( $current_user_id ) ) {
	$current_user_id = '';
}

if ( ! is_ms_lms_addon_enabled( 'multi_instructors' ) ) {
	return false;
}

$args = ( new MultiInstructorsRepository() )->getCoCourses( $current_user_id, true );

?>

<div class="stm_lms_instructor_co_courses">
	<div class="stm_lms_instructor_courses__top">
		<h3><?php esc_html_e( 'Co-courses', 'masterstudy-lms-learning-management-system-pro' ); ?></h3>
	</div>

	<?php STM_LMS_Templates::show_lms_template( 'courses/grid', array( 'args' => $args ) ); ?>
</div>
