<?php

/**
 * @param WP_Term $term          Required.
 * @param string  $template_name Recommended. Shortcode or other template name.
 * @param int     $depth         Optional. Big number by default.
 * @param bool    $hide_empty    Optional. True by default.
 * @param bool    $show_count    Optional. False by default.
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
		'depth'         => PHP_INT_MAX,
		'hide_empty'    => true,
		'show_count'    => false,
	),
	EXTR_SKIP
);

// Prepare list args
$listArgs = array(
	'child_of'            => $term->term_id,
	'depth'               => $depth,
	'hide_empty'          => $hide_empty,
	'hide_title_if_empty' => true,
	'orderby'             => 'parent',
	'show_count'          => $show_count,
	'show_option_all'     => '', // Text to display for showing all categories
	'show_option_none'    => '', // "No categories" label
	'taxonomy'            => $term->taxonomy,
	'title_li'            => '', // Top title text
);

/**
 * @param array Default args.
 * @param WP_Term
 * @param array Template args.
 *
 * @since 1.2
 */
$listArgs = apply_filters( "{$template_name}_list_categories_args", $listArgs, $term, $template_args );

?>
<ul>
	<?php wp_list_categories( $listArgs ); ?>
</ul>
