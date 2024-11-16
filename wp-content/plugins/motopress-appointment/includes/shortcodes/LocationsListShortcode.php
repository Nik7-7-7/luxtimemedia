<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class LocationsListShortcode extends AbstractPostsListShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'locations_list' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Locations List', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$locationsListAtts = array(
			'show_image'   => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show featured image.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_title'   => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show post title.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_excerpt' => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show post excerpt.', 'motopress-appointment' ),
				'default'     => true,
			),
			'locations'    => array(
				'type'        => 'posts',
				'description' => esc_html__( 'Comma-separated slugs or IDs of locations that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'categories'   => array(
				'type'        => 'terms',
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
		);

		return $locationsListAtts + parent::getAttributes();
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return string
	 *
	 * @since 1.2
	 */
	public function renderContent( $args, $content, $shortcodeTag ) {
		if ( ! is_admin() ) {
			mpa_assets()->enqueueStyle( 'mpa-public' );
		}

		$args['query'] = $this->queryPosts( $args );

		return mpa_render_template( 'shortcodes/locations-list.php', $args );
	}

	/**
	 * @param array $args
	 * @return \WP_Query
	 *
	 * @since 1.2
	 */
	protected function queryPosts( $args ) {
		// Replace slugs with IDs
		$args = mpa_filter_post_slugs( $args, array( 'locations' ) );
		$args = mpa_filter_term_slugs( $args, array( 'categories' ), mpa_location()->getCategory() );

		// Build query args
		$queryArgs = array(
			'ignore_sticky_posts' => true,
			'paged'               => mpa_get_paged(),
			'post_type'           => mpa_location()->getPostType(),
			'post_status'         => 'publish',
		);

		if ( 0 != $args['posts_per_page'] ) {
			$queryArgs['posts_per_page'] = $args['posts_per_page'];
		}

		if ( ! empty( $args['locations'] ) ) {
			$queryArgs['post__in'] = $args['locations'];
		}

		if ( ! empty( $args['categories'] ) ) {
			$queryArgs['tax_query'] = array(
				array(
					'taxonomy' => mpa_location()->getCategory(),
					'terms'    => $args['categories'],
				),
			);
		}

		$queryArgs += mpa_build_query_order_args( $args );

		return new \WP_Query( $queryArgs );
	}

	/**
	 * @param array $validArgs
	 * @param array $postArgs Source values.
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function filterPostArgs( $validArgs, $postArgs ) {
		$filteredArgs = parent::filterPostArgs( $validArgs, $postArgs );

		// Add show_* parameters
		if ( isset( $postArgs['show_items'] ) && is_array( $postArgs['show_items'] ) ) {
			$filteredArgs += array(
				'show_image'   => in_array( 'image', $postArgs['show_items'] ),
				'show_title'   => in_array( 'title', $postArgs['show_items'] ),
				'show_excerpt' => in_array( 'excerpt', $postArgs['show_items'] ),
			);
		}

		return $filteredArgs;
	}
}
