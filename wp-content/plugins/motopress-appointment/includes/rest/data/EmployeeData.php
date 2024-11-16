<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Employee;

class EmployeeData extends AbstractPostData {

	/**
	 * @var Employee
	 */
	public $entity;

	public static function getRepository() {
		return mpapp()->repositories()->employee();
	}

	public static function getProperties() {
		return array(
			'id'              => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'name'            => array(
				'description' => 'Name.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'bio'             => array(
				'description' => 'Bio.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'contacts'        => array(
				'description' => 'Contacts.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'social_networks' => array(
				'description' => 'Social networks.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'additional_info' => array(
				'description' => 'Additional info.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
		);
	}
}
