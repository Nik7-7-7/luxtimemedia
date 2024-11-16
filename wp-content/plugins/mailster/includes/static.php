<?php

$forms = wp_get_recent_posts(
	array(
		'post_type'   => 'mailster-form',
		'numberposts' => - 1,
		'post_status' => 'any',
	)
);

if ( $forms ) {
	$form_id = $forms[0]['ID'];
} else {
	$form_id = 0;
}

// check if we use the block editor on a "page"
$block_editor = apply_filters( 'use_block_editor_for_post_type', function_exists( 'has_blocks' ), 'page' );

if ( $block_editor ) {

	$str = sprintf( '{"submission":%d,"profile":%d,"unsubscribe":%d} ', $form_id, $form_id, $form_id );

	$content = '<!-- wp:mailster/homepage ' . $str . ' -->
<!-- wp:mailster/homepage-context {"type":"submission"} -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Signup for the newsletter', 'mailster' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:mailster/form /-->
<!-- /wp:mailster/homepage-context -->

<!-- wp:mailster/homepage-context {"type":"profile"} -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Update your preferences', 'mailster' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:mailster/form /-->
<!-- /wp:mailster/homepage-context -->

<!-- wp:mailster/homepage-context {"type":"unsubscribe"} -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Do you really want to unsubscribe?', 'mailster' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:mailster/form /-->
<!-- /wp:mailster/homepage-context -->

<!-- wp:mailster/homepage-context {"type":"subscribe"} -->
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Thanks for your interest!', 'mailster' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Thank you for confirming your subscription to our newsletter. We\'re excited to have you on board!', 'mailster' ) . '</p>
<!-- /wp:paragraph -->
<!-- /wp:mailster/homepage-context -->
<!-- /wp:mailster/homepage -->';

} else {

	$content = '[newsletter_signup]' . esc_html__( 'Signup for the newsletter', 'mailster' ) . sprintf( '[mailster_form id=%d]', $form_id ) . '[/newsletter_signup][newsletter_confirm]' . esc_html__( 'Thanks for your interest!', 'mailster' ) . '[/newsletter_confirm][newsletter_unsubscribe]' . esc_html__( 'Do you really want to unsubscribe?', 'mailster' ) . '[/newsletter_unsubscribe]';

}

$mailster_homepage = array(
	'post_title'   => esc_html__( 'Newsletter', 'mailster' ),
	'post_status'  => 'draft',
	'post_type'    => 'page',
	'post_name'    => esc_html_x( 'newsletter-signup', 'Newsletter Homepage page slug', 'mailster' ),
	'post_content' => $content,
);
