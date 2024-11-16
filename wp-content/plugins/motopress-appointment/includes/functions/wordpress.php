<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param int $postId
 * @param string $metaName
 * @param array $metaValues
 *
 * @since 1.0
 */
function mpa_add_post_metas( $postId, $metaName, $metaValues ) {
	foreach ( $metaValues as $metaValue ) {
		add_post_meta( $postId, $metaName, $metaValue );
	}
}

/**
 * @param int $postId
 * @param string $metaName
 * @param array $metaValues
 *
 * @since 1.0
 */
function mpa_delete_post_metas( $postId, $metaName, $metaValues ) {
	foreach ( $metaValues as $metaValue ) {
		delete_post_meta( $postId, $metaName, $metaValue );
	}
}

/**
 * @since 1.5.0
 *
 * @param int $postId
 * @param string $postStatus
 * @param bool $wpError Optional. Whether to return a WP_Error on failure. False
 *      by default.
 * @return bool|WP_Error
 */
function mpa_update_post_status( $postId, $postStatus, $wpError = false ) {
	$response = wp_update_post(
		array(
			'ID'          => $postId,
			'post_status' => $postStatus,
		),
		true
	);

	if ( ! is_wp_error( $response ) ) {
		return true;
	} else {
		return $wpError ? $response : false;
	}
}

/**
 * Retrieve a post status label by it's name.
 *
 * @param string $status
 * @return string
 *
 * @since 1.0
 *
 * @todo Use translations from default WordPress textdomain.
 */
function mpa_get_status_label( $status ) {

	switch ( $status ) {
		case 'new':
			return esc_html_x( 'New', 'Post status', 'motopress-appointment' );
			break;

		case 'auto-draft':
			return esc_html_x( 'Auto Draft', 'Post status', 'motopress-appointment' );
			break;

		default:
			$statusObject = get_post_status_object( $status );
			return ! is_null( $statusObject ) && property_exists( $statusObject, 'label' ) ? $statusObject->label : '';
			break;
	}
}

/**
 * @param int $postId Gets all available terms for 0.
 * @param string $taxonomy
 * @param string|array $fields Optional. 'all', field name or key-value pair.
 *     ['slug' => 'name'] by default.
 * @param array $args Optional.
 * @return WP_Term[]|array
 *
 * @since 1.0
 */
function mpa_get_terms( $postId, $taxonomy, $fields = array( 'slug' => 'name' ), $args = array() ) {

	if ( 'all' === $fields ) {
		$args['fields'] = $fields;
	}

	// Query terms
	if ( 0 == $postId ) {
		$args += array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		);

		$terms = get_terms( $args );
	} else {
		$terms = wp_get_post_terms( $postId, $taxonomy, $args );
	}

	// Build output
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	} elseif ( is_string( $fields ) ) {
		if ( 'all' != $fields ) {
			$terms = wp_list_pluck( $terms, $fields );
		}
	} else {
		list($key, $value) = mpa_first_pair( $fields );

		$terms = wp_list_pluck( $terms, $value, $key );
	}

	return $terms;
}

/**
 * Notice: the function does not validate the values. Validate all fields first,
 * before passing them to the function.
 *
 * @param array $args Shortcode or other args.
 * @param array $failResponse Optional. [] by default.
 * @return array Order args or $failResponse.
 *
 * @see https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters
 *
 * @since 1.2
 */
function mpa_build_query_order_args( $args, $failResponse = array() ) {
	$defaults = array(
		// Default order for posts
		'orderby'   => 'date',
		'order'     => 'DESC',
		'meta_key'  => '',
		'meta_type' => '',
	);

	$args      = array_intersect_key( $args, $defaults );
	$orderArgs = array_merge( $defaults, $args );

	$orderby       = $orderArgs['orderby'];
	$isOrderByMeta = strpos( $orderby, 'meta_value' ) === 0;

	// Check required fields. "meta_key" must be present to order by meta value
	if ( empty( $orderby ) || ( $isOrderByMeta && empty( $orderArgs['meta_key'] ) ) ) {
		return $failResponse;

	} else {
		// OK, do a little validation
		if ( 'id' == $orderby ) {
			$orderArgs['orderby'] = 'ID';
		}

		return $orderArgs;
	}
}

/**
 * @return int
 *
 * @since 1.2
 */
function mpa_get_paged() {
	return max( 1, (int) get_query_var( 'paged', 1 ) );
}

/**
 * @param string $postName The post's slug.
 * @param string $postType
 * @param array $args Optional. Custom query args for get_posts(). [] by default.
 * @return WP_Post|null
 *
 * @since 1.2
 */
function mpa_get_post_by_name( $postName, $postType, $args = array() ) {
	$defaults = array(
		'post_status' => 'publish',
	);

	$queryArgs = array(
		'name'           => $postName,
		'post_type'      => $postType,
		'posts_per_page' => 1,
	);

	$queryArgs = $queryArgs + $args + $defaults;

	$posts = get_posts( $queryArgs );

	if ( ! empty( $posts ) ) {
		return reset( $posts );
	} else {
		return null;
	}
}

/**
 * Retrieve the classes for the post div.
 *
 * @param string|array $class Optional. One or more classes to add to the class
 *     list. '' by default.
 * @param int|WP_Post|null $postId Optional. Post ID or post object. Current
 *     post by default (null).
 * @return string Post classes.
 *
 * @since 1.2
 */
function mpa_get_post_class( $class = '', $postId = null ) {

	$classes = get_post_class( $class, $postId );

	// Remove "hentry"
	$hentryIndex = array_search( 'hentry', $classes );

	if ( false !== $hentryIndex ) {
		unset( $classes[ $hentryIndex ] );
	}

	// Join classes
	return implode( ' ', $classes );
}

/**
 * @param string $postName
 * @param string $postType
 * @param array $args Optional. Custom query args for get_posts(). [] by default.
 * @return int 0 if the post not found.
 *
 * @since 1.2
 */
function mpa_get_post_id_by_name( $postName, $postType, $args = array() ) {
	$queryArgs = array( 'fields' => 'ids' ) + $args;

	$post = mpa_get_post_by_name( $postName, $postType, $queryArgs );

	if ( ! is_null( $post ) ) {
		return $post;
	} else {
		return 0;
	}
}

/**
 * @param int $postId
 * @param string $metaName Optional. '' by default (all fields of the post).
 * @param bool $isSingle Optional. False by default.
 * @param mixed $default Optional. Default value for single meta. '' by default.
 * @return mixed The value of the meta field if $isSingle is true, an array of
 *     values otherwise. $default for an invalid post ID.
 *
 * @since 1.2
 */
function mpa_get_post_meta( $postId, $metaName = '', $isSingle = false, $default = '' ) {
	$values = get_post_meta( $postId, $metaName );

	// Invalid post ID
	if ( false === $values ) {
		if ( $isSingle && ! empty( $metaName ) ) {
			return '';
		} else {
			return array();
		}
	}

	// Parse single value
	if ( $isSingle && ! empty( $metaName ) ) {
		if ( ! empty( $values ) ) {
			$metaValue = reset( $values );
		} else {
			$metaValue = $default;
		}

		return $metaValue;

		// Parse multiple values
	} else {
		if ( ! empty( $metaName ) ) {
			$metaValues = $values;
		} else {
			$metaValues = array();

			foreach ( $values as $name => $value ) {
				$metaValues[ mpa_unprefix( $name ) ] = $value;
			}
		}

		if ( $isSingle ) {
			array_walk(
				$metaValues,
				function ( &$value ) {
					$value = reset( $value );
					$value = maybe_unserialize( $value );
				}
			);
		}

		return $metaValues;
	}
}

/**
 * @param string $postType
 * @param string $view Optional. '' by default.
 * @param string $defaultSize Optional. 'post-thumbnail' by default.
 * @return string
 *
 * @since 1.2
 */
function mpa_get_post_thumbnail_size( $postType, $view = '', $defaultSize = 'post-thumbnail' ) {
	/**
	 * @param string Default size.
	 *
	 * @since 1.2
	 */
	$thumbnailSize = apply_filters( "{$postType}_thumbnail_size", $defaultSize );

	if ( ! empty( $view ) ) {
		/**
		 * @param string Thumbnail size.
		 *
		 * @since 1.2
		 */
		$thumbnailSize = apply_filters( "{$postType}_{$view}_thumbnail_size", $thumbnailSize );
	}

	return $thumbnailSize;
}

/**
 * @return int
 *
 * @since 1.2
 */
function mpa_get_posts_per_page() {
	return (int) get_option( 'posts_per_page', 10 );
}

/**
 * @param WP_Term|int|string $term The term object, ID, or slug whose link will
 *     be retrieved.
 * @param string $taxonomy Optional. '' by default.
 * @return string Link on success, empty string if category does not exist.
 *
 * @since 1.2
 */
function mpa_get_term_link( $term, $taxonomy = '' ) {
	$termLink = get_term_link( $term, $taxonomy );

	if ( ! is_wp_error( $termLink ) ) {
		return $termLink;
	} else {
		return '';
	}
}

/**
 * @param WP_Term|int $term
 * @param string|int[] $size Optional. Image size. Accepts any registered image
 *     size name, or an array of width and height values in pixels (in that
 *     order). 'thumbnail' by default.
 * @return string HTML img element or empty string on failure.
 *
 * @since 1.2
 */
function mpa_get_term_attachment_image( $term, $size = 'thumbnail' ) {
	$thumbnailId = mpa_get_term_thumbnail_id( $term );

	if ( $thumbnailId > 0 ) {
		return wp_get_attachment_image( $thumbnailId, $size );
	} else {
		return '';
	}
}

/**
 * @param WP_Term|int $term
 * @param string|int[] $size Optional. Image size. Accepts any registered image
 *     size name, or an array of width and height values in pixels (in that
 *     order). 'thumbnail' by default.
 * @return array|false [0 => Image URL, 1 => Width in pixels, 2 => Height in
 *     pixels, 3 => Is resized] or false.
 *
 * @since 1.2
 */
function mpa_get_term_attachment_image_src( $term, $size = 'thumbnail' ) {
	$thumbnailId = mpa_get_term_thumbnail_id( $term );

	if ( $thumbnailId > 0 ) {
		return wp_get_attachment_image_src( $thumbnailId, $size );
	} else {
		return false;
	}
}

/**
 * @param WP_Term|int $term
 * @return int
 *
 * @since 1.2
 */
function mpa_get_term_thumbnail_id( $term ) {
	if ( is_object( $term ) ) {
		$termId = $term->term_id;
	} else {
		$termId = (int) $term;
	}

	$metaKey = mpa_prefix( 'featured_image', 'private' );

	$thumbnailId = get_term_meta( $termId, $metaKey, true );

	return mpa_posint( $thumbnailId );
}

/**
 * @param WP_Term|int $term
 * @return bool
 *
 * @since 1.2
 */
function mpa_term_has_thumbnail( $term ) {
	$thumbnailId = mpa_get_term_thumbnail_id( $term );

	return $thumbnailId > 0 && wp_attachment_is_image( $thumbnailId );
}

/**
 * @since 1.4.0
 *
 * @param int $postId
 * @param string|int[] $size Optional. Image size. Accepts any registered image
 *      size name, or an array of width and height values in pixels (in that
 *      order). 'thumbnail' by default.
 * @return string
 */
function mpa_get_post_attachment_image_url( $postId, $size = 'thumbnail' ) {
	$imageUrl = '';

	$thumbnailId = get_post_thumbnail_id( $postId );

	// Get image URL by thumbnail ID
	if ( false !== $thumbnailId ) {
		$imageData = wp_get_attachment_image_src( $thumbnailId, $size );

		if ( false !== $imageData ) {
			$imageUrl = $imageData[0];
		}
	}

	return $imageUrl;
}

/**
 * @global bool $mpaDoingContent
 *
 * @since 1.2
 */
function mpa_the_content() {
	global $mpaDoingContent;

	if ( isset( $mpaDoingContent ) && $mpaDoingContent ) {
		return;
	}

	$mpaDoingContent = true;

	the_content();

	$mpaDoingContent = false;
}

/**
 * @param string $string
 * @return string The sanitized value.
 *
 * @since 1.2
 */
function mpa_sanitize_html_classes( $string ) {
	$classes = preg_split( '/\\s+/', trim( $string ) );
	$classes = array_map( 'sanitize_html_class', $classes );
	$classes = array_filter( $classes, 'mpa_filter_empty_string' );

	return implode( ' ', $classes );
}

/**
 * @param string $phoneNumber
 *
 * @return string
 * @since 1.18.0
 */
function mpa_sanitize_phone( string $phoneNumber ): string {
	return preg_replace( '/[^\d+]/', '', $phoneNumber );
}

/**
 * @since 1.6.2
 *
 * @param string $version Version to check.
 * @return bool
 */
function mpa_wordpress_at_least( $version ) {
	return version_compare( get_bloginfo( 'version' ), $version, '>=' );
}
