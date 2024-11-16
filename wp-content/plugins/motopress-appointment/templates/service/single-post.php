<?php

/**
 * @param string $template_name   Optional. 'mpa_single_service' by default.
 * @param bool   $show_attributes Optional. False by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name'   => mpa_prefix( 'mpa_single_service' ),
		'show_attributes' => false,
	),
	EXTR_SKIP
);

// Hide content of the password protected posts
if ( post_password_required() ) {
	$show_attributes = false;
}

// Display template
$postType = mpa_service()->getPostType();

$templateArgs =
	array(
		'template_name'   => $template_name,
		'show_attributes' => $show_attributes,
	)
	+ $template_args;

?>
<?php
if ( $show_attributes ) {
	/**
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$postType}_single_post_attributes", $templateArgs );
}

