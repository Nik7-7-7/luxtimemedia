<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_id = get_the_ID();
?>
<div class="stm-lms-course__sidebar">
	<?php
	STM_LMS_Templates::show_lms_template( 'global/wish-list', array( 'course_id' => $post_id ) );

	STM_LMS_Templates::show_lms_template( 'components/bundle-button', array( 'bundle_id' => $post_id ) );

	STM_LMS_Templates::show_lms_template( 'bundle/parts/dynamic_sidebar', array( 'course_id' => $post_id ) );
	?>
</div>
