<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ServiceCategoriesShortcode extends AbstractTermsListShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'service_categories' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Service Categories', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$serviceCategoriesAtts = array(
			'show_image'         => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show featured image.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_count'         => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show the count of the associated services near the title.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_description'   => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show description of the each item.', 'motopress-appointment' ),
				'default'     => true,
			),
			'parent'             => array(
				'type'          => 'term',
				'description'   => esc_html__( 'Parent term slug or ID to retrieve direct-child terms from.', 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => null,
			),
			'categories'         => array(
				'type'        => 'terms',
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'exclude_categories' => array(
				'type'        => 'terms',
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will not be shown.', 'motopress-appointment' ),
				'default'     => array(),
			),
			'hide_empty'         => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Hide terms not assigned to any posts.', 'motopress-appointment' ),
				'default'     => true,
			),
			'depth'              => array(
				'type'          => 'integer',
				'description'   => esc_html__( 'Display depth of child categories.', 'motopress-appointment' ),
				'default'       => PHP_INT_MAX,
				'default_label' => esc_html__( 'all categories', 'motopress-appointment' ),
			),
		);

		return $serviceCategoriesAtts + parent::getAttributes();
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

		$args['terms'] = $this->queryTerms( $args );

		return mpa_render_template( 'shortcodes/service-categories.php', $args );
	}

	/**
	 * @param array $args
	 * @return \WP_Query
	 *
	 * @since 1.2
	 */
	protected function queryTerms( $args ) {
		// Replace slugs with IDs
		$args = mpa_filter_term_slugs( $args, array( 'categories', 'exclude_categories' ), mpa_service()->getCategory() );

		if ( ! empty( $args['parent'] ) && ! is_numeric( $args['parent'] ) ) {
			$term = get_term_by( 'slug', $args['parent'], mpa_service()->getCategory() );

			if ( false !== $term ) {
				$args['parent'] = $term->term_id;
			} else {
				$args['parent'] = $this->attributes['parent']['default'];
			}
		}

		// Build query args
		$queryArgs = array(
			'hide_empty' => $args['hide_empty'],
			'orderby'    => $args['orderby'],
			'order'      => $args['order'],
		);

		// Don't set parent=0 when searching by exact categories
		if ( empty( $args['categories'] ) || 0 != $args['parent'] ) {
			$queryArgs['parent'] = $args['parent'];
		}

		// Don't limit the results when we have the exact set of ID
		if ( $args['number'] > 0 && empty( $args['categories'] ) ) {
			$queryArgs['number'] = $args['number'];
		}

		if ( ! empty( $args['categories'] ) ) {
			$queryArgs['include'] = $args['categories'];
		}

		if ( ! empty( $args['exclude_categories'] ) ) {
			$queryArgs['exclude'] = $args['exclude_categories'];
		}

		return mpa_get_service_categories( 0, 'all', $queryArgs );
	}

	/**
	 * @param array $args
	 * @return array
	 *
	 * @since 1.2
	 */
	public function validateArgs( $args ) {
		$validArgs = parent::validateArgs( $args );

		// Remove repeating values from "employees" and "exclude_employees"
		if ( ! empty( $validArgs['categories'] ) && ! empty( $validArgs['exclude_categories'] ) ) {
			mpa_array_diff_all( $validArgs['categories'], $validArgs['exclude_categories'] );
		}

		return $validArgs;
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

		// Don't replace the default depth with empty value (0)
		if ( isset( $postArgs['depth'] ) && '' === $postArgs['depth'] ) {
			unset( $filteredArgs['depth'] );
		}

		// Add show_* parameters
		if ( isset( $postArgs['show_items'] ) && is_array( $postArgs['show_items'] ) ) {
			$filteredArgs += array(
				'show_image'       => in_array( 'image', $postArgs['show_items'] ),
				'show_count'       => in_array( 'count', $postArgs['show_items'] ),
				'show_description' => in_array( 'description', $postArgs['show_items'] ),
			);
		}

		return $filteredArgs;
	}
}
