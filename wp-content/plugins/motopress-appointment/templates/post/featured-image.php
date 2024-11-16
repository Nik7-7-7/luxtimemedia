<?php

/**
 * @param string $view Optional. 'single' by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the post has a featured image
if ( ! has_post_thumbnail() ) {
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
$thumbnailSize = mpa_get_post_thumbnail_size( get_post_type(), $view )

?>
<p class="post-thumbnail <?php echo esc_attr( "mpa-{$view}-post-thumbnail" ); ?>">
	<a href="<?php echo esc_url( get_the_permalink() ); ?>">
		<?php the_post_thumbnail( $thumbnailSize ); ?>
	</a>
</p>
