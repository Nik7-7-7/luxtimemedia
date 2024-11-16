<?php

/**
 * @param WP_Term $term Required.
 * @param string  $view Optional. 'single' by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the term has a featured image
if ( ! mpa_term_has_thumbnail( $term ) ) {
	return;
}

// Initialize args
extract(
	array(
		'view' => 'single',
	),
	EXTR_SKIP
);

// Display template
$thumbnailSize = mpa_get_post_thumbnail_size( get_post_type(), $view );

?>
<p class="post-thumbnail term-thumbnail <?php echo esc_attr( "mpa-{$view}-post-thumbnail mpa-{$view}-term-thumbnail" ); ?>">
	<a href="<?php echo esc_url( mpa_get_term_link( $term ) ); ?>">
		<?php echo mpa_get_term_attachment_image( $term, $thumbnailSize ); ?>
	</a>
</p>
