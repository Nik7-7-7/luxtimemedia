<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $string
 * @return bool
 *
 * @since 1.2
 */
function mpa_filter_empty_string( $string ) {
	return '' !== $string;
}

/**
 * @param array $args Args to filter.
 * @param array $keys Optional. Keys to filter. All keys by default.
 * @return array Args, where slugs replaced with IDs.
 *
 * @since 1.2
 */
function mpa_filter_post_slugs( $args, $keys = array() ) {
	$queryArgs = array(
		'post_type' => 'any',
	);

	return _mpa_filter_object_slugs( 'post', $args, $keys, $queryArgs );
}

/**
 * @param array $args Args to filter.
 * @param array $keys Optional. Keys to filter. All keys by default.
 * @param string|array $taxonomy Optional. Taxonomy name, or array of
 *     taxonomies, to which filter should be limited. 'any' by default.
 * @return array Args, where slugs replaced with IDs.
 *
 * @since 1.2
 */
function mpa_filter_term_slugs( $args, $keys = array(), $taxonomy = 'any' ) {
	$queryArgs = array(
		'hide_empty' => false,
	);

	if ( 'any' !== $taxonomy ) {
		$queryArgs['taxonomy'] = $taxonomy;
	}

	return _mpa_filter_object_slugs( 'taxonomy', $args, $keys, $queryArgs );
}

/**
 * @param 'post'|'taxonomy' $objectType
 * @param array $args Args to filter.
 * @param array $keys Keys to filter.
 * @param array $queryArgs Additional query args.
 * @return array Args, where slugs replaced with IDs.
 *
 * @since 1.2
 */
function _mpa_filter_object_slugs( $objectType, $args, $keys, $queryArgs ) {
	if ( empty( $keys ) ) {
		$keys = array_keys( $args );
	}

	$is_slug = function ( $value ) {
		return ! is_numeric( $value );
	};

	// Filter only non-empty array values
	$keys = array_filter(
		$keys,
		function ( $key ) use ( $args ) {
			return array_key_exists( $key, $args ) && is_array( $args[ $key ] ) && ! empty( $args[ $key ] );
		}
	);

	if ( empty( $keys ) ) {
		// No keys found
		return $args;
	}

	// Pull all slugs
	$slugs = array();

	foreach ( $keys as $key ) {
		$moreSlugs = array_filter( $args[ $key ], $is_slug );
		$slugs     = array_merge( $slugs, $moreSlugs );
	}

	$slugs = array_unique( $slugs );

	if ( empty( $slugs ) ) {
		// No slugs found
		return $args;
	}

	// Get objects by slugs
	$ids = array(); // [Slug => ID]

	if ( 'post' == $objectType ) {
		$queryArgs['post_name__in'] = $slugs;

		// Add defaults
		$queryArgs += array(
			'post_type' => 'any',
			'orderby'   => 'none',
		);

		$posts = get_posts( $queryArgs );

		$ids = wp_list_pluck( $posts, 'ID', 'post_name' ); // returns [Slug => ID]

	} elseif ( 'taxonomy' == $objectType ) {
		$queryArgs['fields'] = 'id=>slug';
		$queryArgs['slug']   = $slugs;

		// Add defaults
		$queryArgs += array(
			'orderby' => 'none',
		);

		$terms = get_terms( $queryArgs );  // [ID => Slug] or WP_Error

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		$ids = array_flip( $terms ); // returns [Slug => ID]
	}

	// If $ids is empty - don't return $args here, we'll need to skip slugs
	// without IDs in the cycle below

	// Replace slugs with IDs
	foreach ( $keys as $key ) {
		$values = array();

		foreach ( $args[ $key ] as $value ) {
			if ( $is_slug( $value ) ) {
				// Get ID by slug or skip the slug
				if ( array_key_exists( $value, $ids ) ) {
					$values[] = $ids[ $value ];
				}
			} else {
				// Add value without changes
				$values[] = $value;
			}
		}

		$args[ $key ] = $values;
	}

	return $args;
}

/**
 * Transforms relative path to the asset file with an URL to it.
 *
 * @param string $asset Asset file (script or stylesheet).
 * @param string $pluginUrl Optional. Appointment Booking by default.
 * @return string URL to the file.
 *
 * @since 1.2.1
 * @since 1.5.0 skips the absolute URLs.
 */
function mpa_filter_asset( $asset, $pluginUrl = MotoPress\Appointment\PLUGIN_URL ) {
	// Don't change absolute URLs
	if ( strpos( $asset, 'http' ) === 0 ) {
		return $asset;
	}

	// Use unminified version of the file with SCRIPT_DEBUG enabled
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$asset = str_replace( array( '.min.js', '.min.css' ), array( '.js', '.css' ), $asset );
	}

	// Replace relative path with URL to the file
	$asset = mpa_url_to( $asset, $pluginUrl );

	return $asset;
}
