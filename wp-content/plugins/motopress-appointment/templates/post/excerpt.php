<?php

/**
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the post has an excerpt
if ( ! has_excerpt() ) {
	return;
}

?>
<div class="entry-excerpt">
	<?php the_excerpt(); ?>
</div>
