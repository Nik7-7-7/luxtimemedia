<?php

/**
 * @param string $template_name        Optional. 'mpa_single_employee' by default.
 * @param bool   $show_contacts        Optional. False by default.
 * @param bool   $show_social_networks Optional. False by default.
 * @param bool   $show_attributes      Optional. False by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name'        => mpa_prefix( 'single_employee' ),
		'show_contacts'        => false,
		'show_social_networks' => false,
		'show_additional_info' => false,
	),
	EXTR_SKIP
);

// Hide content of the password protected posts
if ( post_password_required() ) {

	$show_additional_info = false;
	$show_social_networks = false;
	$show_contacts        = false;
}

// Display template
$postType = mpa_employee()->getPostType();

$templateArgs =
	array(
		'template_name'        => $template_name,
		'show_contacts'        => $show_contacts,
		'show_social_networks' => $show_social_networks,
		'show_additional_info' => $show_additional_info,
	)
	+ $template_args;

?>
<?php
if ( $show_contacts || $show_social_networks || $show_additional_info ) {
	/**
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$postType}_single_post_attributes", $templateArgs );
}

