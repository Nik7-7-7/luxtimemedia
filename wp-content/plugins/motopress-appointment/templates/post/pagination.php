<?php

/**
 * @param WP_Query $query         Required.
 * @param string   $template_name Recommended. Shortcode or other template name.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't show pagination with only one page
if ( 1 == $query->max_num_pages ) {
	return;
}

// Initialize args
extract(
	array(
		'template_name' => mpa_prefix( 'post' ),
	),
	EXTR_SKIP
);

// Prepare pagination args
$bigInt     = PHP_INT_MAX; // Need an unlikely integer to replace it in the URL string
$readerText = esc_html__( 'Page', 'motopress-appointment' );

$paginationArgs = array(
	'base'               => str_replace( $bigInt, '%#%', get_pagenum_link( $bigInt ) ),
	'before_page_number' => '<span class="screen-reader-text">' . $readerText . ' </span>',
	'current'            => mpa_get_paged(),
	'format'             => '?paged=%#%',
	'total'              => $query->max_num_pages,
);

/**
 * @param array Default pagination args.
 * @param WP_Query
 *
 * @since 1.2
 */
$paginationArgs = apply_filters( "{$template_name}_pagination_args", $paginationArgs, $query );

?>
<nav class="navigation pagination mpa-pagination" role="navigation">
	<div class="nav-links"><?php echo paginate_links( $paginationArgs ); ?></div>
</nav>
