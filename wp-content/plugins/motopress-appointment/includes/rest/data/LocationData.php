<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Location;

class LocationData extends AbstractPostData {

	/**
	 * @var Location
	 */
	public $entity;

	public static function getRepository() {
		return mpapp()->repositories()->location();
	}

	public static function getProperties() {
		return array(
			'id'          => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'       => array(
				'type'     => 'string',
				'context'  => array( 'embed', 'view', 'edit' ),
				'required' => true,
			),
			'description' => array(
				'type'    => 'string',
				'context' => array( 'view', 'edit' ),
			),
		);
	}

	public function getTitle() {
		return $this->entity->getName();
	}

	public function getDescription() {
		return $this->entity->getInfo();
	}

	public function setTitle( $title ) {
		$this->entity->setName( $title );
	}

	public function setDescription( $descriptiom ) {
		return $this->entity->setInfo( $descriptiom );
	}
}
