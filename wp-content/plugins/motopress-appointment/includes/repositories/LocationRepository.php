<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Location;
use MotoPress\Appointment\PostTypes\LocationPostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see Location
 */
class LocationRepository extends AbstractRepository {

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function entitySchema() {
		return array(
			'post' => array( 'ID', 'post_title', 'post_content' ),
		);
	}

	/**
	 * @param array $postData
	 * @return Location
	 *
	 * @since 1.0
	 */
	protected function mapPostDataToEntity( $postData ) {
		$id = (int) $postData['ID'];

		$fields = array(
			'name' => $postData['post_title'],
			'info' => $postData['post_content'],
		);

		return new Location( $id, $fields );
	}

	/**
	 * @param array $args Optional. Additional arguments for function get_terms().
	 *     [] by default.
	 * @return array [Term ID => \WP_Term]
	 *
	 * @since 1.0
	 */
	public function findCategories( $args = array() ) {
		return $this->getCategories( LocationPostType::CATEGORY_NAME, $args );
	}
}
