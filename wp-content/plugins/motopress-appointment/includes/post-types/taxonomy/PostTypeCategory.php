<?php

namespace MotoPress\Appointment\PostTypes\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
trait PostTypeCategory {

	/**
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function registerCategory() {
		$args = array(
			'labels' => $this->getCategoryLabels(),
		);

		$args += $this->getCategoryArgs();

		register_taxonomy( self::CATEGORY_NAME, self::POST_TYPE, $args );
		register_taxonomy_for_object_type( self::CATEGORY_NAME, self::POST_TYPE );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getCategory() {
		return self::CATEGORY_NAME;
	}

	/**
	 * @param \WP_Term|int|string $term The term object, ID, or slug whose link
	 *     will be retrieved.
	 * @return string Link on success, empty string if category does not exist.
	 *
	 * @since 1.2
	 */
	public function getCategoryLink( $term ) {
		return mpa_get_term_link( $term, self::CATEGORY_NAME );
	}
}
