<?php

/**
 * See full parameters list in ServiceCategoriesShortcode::getAttributes().
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templateArgs =
	array(
		'template_name' => $shortcode_name,
		'show_name'     => true,
		'show_children' => true,
	)
	+ $template_args;

mpa_display_template(
	'service/loop-terms.php',
	'term/loop-terms.php',
	$templateArgs
);
