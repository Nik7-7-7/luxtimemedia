<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Entities\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ManageServicesPage extends ManagePostsPage {

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function customColumns() {
		return array(
			'price'    => esc_html__( 'Price', 'motopress-appointment' ),
			'duration' => esc_html__( 'Duration', 'motopress-appointment' ),
		);
	}

	/**
	 * @param string $columnName
	 * @param Service $entity
	 *
	 * @since 1.0
	 */
	protected function displayValue( $columnName, $entity ) {
		switch ( $columnName ) {
			case 'price':
				echo mpa_tmpl_price( $entity->getPrice() );
				break;
			case 'duration':
				echo mpa_minutes_to_duration( $entity->getDuration() );
				break;
		}
	}
}
