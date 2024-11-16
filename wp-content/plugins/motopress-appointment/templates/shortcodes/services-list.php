<?php

/**
 * See full parameters list in ServicesListShortcode::getAttributes().
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templateArgs =
	array(
		'template_name'   => $shortcode_name,
		'show_attributes' => $show_duration || $show_capacity,
		'show_extra'      => true,
	)
	+ $template_args;

mpa_display_template(
	'service/loop-posts.php',
	'post/loop-posts.php',
	$templateArgs
);
