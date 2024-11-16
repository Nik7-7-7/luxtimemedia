<?php

namespace MotoPress\Appointment\PostTypes\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
trait PostTypeTag {

	/**
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function registerTag() {
		$args = array(
			'labels' => $this->getTagLabels(),
		);

		$args += $this->getTagArgs();

		register_taxonomy( self::TAG_NAME, self::POST_TYPE, $args );
		register_taxonomy_for_object_type( self::TAG_NAME, self::POST_TYPE );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getTag() {
		return self::TAG_NAME;
	}

	/**
	 * @param \WP_Term|int|string $term The term object, ID, or slug whose link
	 *     will be retrieved.
	 * @return string Link on success, empty string if tag does not exist.
	 *
	 * @since 1.2
	 */
	public function getTagLink( $term ) {
		return mpa_get_term_link( $term, self::TAG_NAME );
	}
}
