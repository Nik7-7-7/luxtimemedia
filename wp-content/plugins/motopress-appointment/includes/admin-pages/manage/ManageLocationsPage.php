<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Entities\Location;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ManageLocationsPage extends ManagePostsPage {

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function customColumns() {
		return array();
	}

	/**
	 * @param string $columnName
	 * @param Location $entity
	 *
	 * @since 1.0
	 */
	protected function displayValue( $columnName, $entity ) {
		switch ( $columnName ) {
			default:
				parent::displayValue( $columnName, $entity );
				break;
		}
	}
}
