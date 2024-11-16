<?php

/**
 * See full parameters list in LocationsListShortcode::getAttributes().
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templateArgs =
	array(
		'template_name' => $shortcode_name,
	)
	+ $template_args;

mpa_display_template(
	'location/loop-posts.php',
	'post/loop-posts.php',
	$templateArgs
);
