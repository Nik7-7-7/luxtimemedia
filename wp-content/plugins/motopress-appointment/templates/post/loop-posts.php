<?php

/**
 * @param WP_Query $query         Required.
 * @param string   $template_name Recommended. Shortcode or other template name.
 * @param int      $columns_count Optional. 3 by default.
 * @param string   $view          Optional. 'loop' by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name' => mpa_prefix( 'post' ),
		'columns_count' => 3,
		'view'          => 'loop',
	),
	EXTR_SKIP
);

// Args for nested templates
$templateArgs = $template_args + array( 'view' => $view );

if ( $query->have_posts() ) {

	/**
	 * @param WP_Query
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_before_loop", $query, $templateArgs );

	echo '<div class="mpa-posts-wrapper mpa-posts-loop ', esc_attr( "mpa-grid mpa-grid-columns-{$columns_count}" ), '">';

	while ( $query->have_posts() ) {
		$query->the_post();

		/**
		 * @param array Template args.
		 *
		 * @since 1.2
		 */
		do_action( "{$template_name}_loop_item", $templateArgs );
	}

	echo '</div>';

	wp_reset_postdata();

	/**
	 * @param WP_Query
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_after_loop", $query, $templateArgs );

} else {
	/**
	 * @param WP_Query
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_not_found", $query, $templateArgs );
}
