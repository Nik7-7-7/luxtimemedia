<?php

/**
 * See full parameters list in EmployeesListShortcode::getAttributes().
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templateArgs =
	array(
		'template_name'        => $shortcode_name,
		'show_attributes'      => $show_contacts || $show_social_networks || $show_additional_info,
		'attributes_separator' => '',
	)
	+ $template_args;

mpa_display_template(
	'employee/loop-posts.php',
	'post/loop-posts.php',
	$templateArgs
);
