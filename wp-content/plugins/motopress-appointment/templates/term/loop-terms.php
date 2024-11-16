<?php

/**
 * @param WP_Term[] $terms         Required.
 * @param string    $template_name Recommended. Shortcode or other template name.
 * @param int       $columns_count Optional. 3 by default.
 * @param string    $view          Optional. 'loop' by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'template_name' => mpa_prefix( 'term' ),
		'columns_count' => 3,
		'view'          => 'loop',
	),
	EXTR_SKIP
);

// Args for nested templates
$templateArgs = $template_args + array( 'view' => $view );

if ( ! empty( $terms ) ) {

	/**
	 * @param WP_Term[]
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_before_loop", $terms, $templateArgs );

	echo '<div class="mpa-terms-wrapper mpa-terms-loop ', esc_attr( "mpa-grid mpa-grid-columns-{$columns_count}" ), '">';

	foreach ( $terms as $singleTerm ) {
		// Pass term to nested templates
		$templateArgs['term'] = $singleTerm;

		/**
		 * @param array Template args.
		 *
		 * @since 1.2
		 */
		do_action( "{$template_name}_loop_term", $templateArgs );
	}

	echo '</div>';

	/**
	 * @param WP_Term[]
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_after_loop", $terms, $templateArgs );

} else {
	/**
	 * @param array Template args.
	 *
	 * @since 1.2
	 */
	do_action( "{$template_name}_not_found", $templateArgs );
}
