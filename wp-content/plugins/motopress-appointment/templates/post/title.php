<?php

/**
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2 class="entry-title mpa-post-title">
	<a href="<?php echo esc_url( get_the_permalink() ); ?>">
		<?php echo esc_html( get_the_title() ); ?>
	</a>
</h2>
