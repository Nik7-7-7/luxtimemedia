<?php

/**
 * @param WP_Term $term Required.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the term has a name
if ( '' !== $term->description ) {
	echo wpautop( $term->description );
}
