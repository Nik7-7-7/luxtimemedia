<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeesListShortcode extends AbstractPostsListShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'employees_list' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Employees List', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$employeesListAtts = array(
			'show_image'           => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show featured image.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_title'           => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show post title.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_excerpt'         => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show post excerpt.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_contacts'        => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show contact information.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_social_networks' => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show social networks.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_additional_info' => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show additional information.', 'motopress-appointment' ),
				'default'     => true,
			),
			'employees'            => array(
				'type'        => 'posts',
				'description' => esc_html__( 'Comma-separated slugs or IDs of employees that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'locations'            => array(
				'type'        => 'posts',
				'description' => esc_html__( 'Comma-separated slugs or IDs of locations.', 'motopress-appointment' ),
				'default'     => array(),
			),
		);

		return $employeesListAtts + parent::getAttributes();
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

		return mpa_render_template( 'shortcodes/employees-list.php', $args );
	}

	/**
	 * @param array $args
	 * @return \WP_Query
	 *
	 * @since 1.2
	 */
	protected function queryPosts( $args ) {
		// Replace slugs with IDs
		$args = mpa_filter_post_slugs( $args, array( 'employees', 'locations' ) );

		// Build query args
		$queryArgs = array(
			'ignore_sticky_posts' => true,
			'paged'               => mpa_get_paged(),
			'post_type'           => mpa_employee()->getPostType(),
			'post_status'         => 'publish',
		);

		if ( 0 != $args['posts_per_page'] ) {
			$queryArgs['posts_per_page'] = $args['posts_per_page'];
		}

		// Query by employees
		$employees = $args['employees'];

		if ( ! empty( $args['locations'] ) ) {
			$employeesByLocation = mpa_get_employees_by_location( $args['locations'], array( 'fields' => 'ids' ) );

			if ( ! empty( $employees ) ) {
				$employees = array_intersect( $employees, $employeesByLocation );
			} else {
				$employees = $employeesByLocation;
			}
		}

		if ( ! empty( $employees ) ) {
			$queryArgs['post__in'] = $employees;
		}

		// Add order args
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
				'show_image'           => in_array( 'image', $postArgs['show_items'] ),
				'show_title'           => in_array( 'title', $postArgs['show_items'] ),
				'show_excerpt'         => in_array( 'excerpt', $postArgs['show_items'] ),
				'show_contacts'        => in_array( 'contacts', $postArgs['show_items'] ),
				'show_social_networks' => in_array( 'social_networks', $postArgs['show_items'] ),
				'show_additional_info' => in_array( 'additional_info', $postArgs['show_items'] ),
			);
		}

		return $filteredArgs;
	}
}
