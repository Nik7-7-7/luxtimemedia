<?php
/**
 * @var int $post_id
 * @var int $item_id
 * @var array $data
 */

$data = apply_filters( 'masterstudy_course_player_assignment_data', $item_id, $data );

$template_name = false !== $data['current_template']
	? 'course-player/assignments/current'
	: 'course-player/assignments/new';

STM_LMS_Templates::show_lms_template(
	$template_name,
	array(
		'post_id' => $post_id,
		'item_id' => $item_id,
		'data'    => $data,
	)
);
