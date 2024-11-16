<?php

/**
 * @param WP_Term $term       Required.
 * @param bool    $show_count Optional. False by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the term has a name
if ( '' === $term->name ) {
	return;
}

// Initialize args
extract(
	array(
		'show_count' => false,
	),
	EXTR_SKIP
);

// Display template
$titleArgs = array(
	'show_count' => $show_count,
);

?>
<h2 class="entry-title mpa-term-title">
	<a href="<?php echo esc_url( mpa_get_term_link( $term ) ); ?>">
		<?php echo esc_html( mpa_tmpl_term_title( $term, $titleArgs ) ); ?>
	</a>
</h2>
