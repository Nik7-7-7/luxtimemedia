<?php
/**
 *
 * @var $columns
 * @var $title
 * @var $posts_per_page
 * @var $atts
 */

STM_LMS_Templates::show_lms_template(
	'shortcodes/stm_lms_course_bundles',
	vc_map_get_attributes( $this->getShortcode(), $atts )
);
