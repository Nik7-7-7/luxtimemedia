<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ServicesListShortcode extends AbstractPostsListShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'services_list' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Services List', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$servicesListAtts = array(
			'show_image'     => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show featured image.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_title'     => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show post title.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_excerpt'   => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show post excerpt.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_price'     => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show service price.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_duration'  => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show service duration.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_capacity'  => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show service capacity.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_employees' => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show service employees.', 'motopress-appointment' ),
				'default'     => true,
			),
			'services'       => array(
				'type'        => 'posts',
				'description' => esc_html__( 'Comma-separated slugs or IDs of services that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'employees'      => array(
				'type'        => 'posts',
				'description' => esc_html__( 'Comma-separated slugs or IDs of employees that perform these services.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'categories'     => array(
				'type'        => 'terms',
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'tags'           => array(
				'type'        => 'terms',
				'description' => esc_html__( 'Comma-separated slugs or IDs of tags that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
		);

		return $servicesListAtts + parent::getAttributes();
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

		return mpa_render_template( 'shortcodes/services-list.php', $args );
	}

	/**
	 * @param array $args
	 * @return \WP_Query
	 *
	 * @since 1.2
	 */
	protected function queryPosts( $args ) {
		// Replace slugs with IDs
		$serviceTaxonomies = array(
			mpa_service()->getCategory(),
			mpa_service()->getTag(),
		);

		$args = mpa_filter_post_slugs( $args, array( 'services', 'employees' ) );
		$args = mpa_filter_term_slugs( $args, array( 'categories', 'tags' ), $serviceTaxonomies );

		// Build query args
		$queryArgs = array(
			'ignore_sticky_posts' => true,
			'paged'               => mpa_get_paged(),
			'post_type'           => mpa_service()->getPostType(),
			'post_status'         => 'publish',
		);

		if ( 0 != $args['posts_per_page'] ) {
			$queryArgs['posts_per_page'] = $args['posts_per_page'];
		}

		if ( ! empty( $args['services'] ) ) {
			$queryArgs['post__in'] = $args['services'];
		}

		// Build meta query
		$metaQuery = array();

		if ( ! empty( $args['employees'] ) ) {
			foreach ( $args['employees'] as $employeeId ) {
				$metaQuery[] = array(
					'key'     => mpa_prefix( 'employees', 'private' ),
					'value'   => esc_sql( "%i:{$employeeId};%" ),
					'compare' => 'LIKE',
				);
			}
		}

		if ( ! empty( $metaQuery ) ) {
			$queryArgs['meta_query']             = $metaQuery;
			$queryArgs['meta_query']['relation'] = 'OR';
		}

		// Build tax query
		$taxQuery = array();

		if ( ! empty( $args['categories'] ) ) {
			$taxQuery[] = array(
				'taxonomy' => mpa_service()->getCategory(),
				'terms'    => $args['categories'],
			);
		}

		if ( ! empty( $args['tags'] ) ) {
			$taxQuery[] = array(
				'taxonomy' => mpa_service()->getTag(),
				'terms'    => $args['tags'],
			);
		}

		if ( ! empty( $taxQuery ) ) {
			$queryArgs['tax_query']             = $taxQuery;
			$queryArgs['tax_query']['relation'] = 'OR';
		}

		// Set order
		if ( 'price' == $args['orderby'] ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = mpa_prefix( 'price', 'private' );
		}

		$orderQuery = mpa_build_query_order_args( $args );

		$queryArgs += $orderQuery;

		// Query posts
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
				'show_image'     => in_array( 'image', $postArgs['show_items'] ),
				'show_title'     => in_array( 'title', $postArgs['show_items'] ),
				'show_excerpt'   => in_array( 'excerpt', $postArgs['show_items'] ),
				'show_price'     => in_array( 'price', $postArgs['show_items'] ),
				'show_duration'  => in_array( 'duration', $postArgs['show_items'] ),
				'show_capacity'  => in_array( 'capacity', $postArgs['show_items'] ),
				'show_employees' => in_array( 'employees', $postArgs['show_items'] ),
			);
		}

		return $filteredArgs;
	}
}
