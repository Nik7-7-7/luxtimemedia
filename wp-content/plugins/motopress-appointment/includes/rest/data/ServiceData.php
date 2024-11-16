<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Service;
use MotoPress\Appointment\Structures\Service\ServiceVariation;
use MotoPress\Appointment\Structures\Service\ServiceVariations;

class ServiceData extends AbstractPostData {

	/**
	 * @var Service
	 */
	public $entity;

	public static function getRepository() {
		return mpapp()->repositories()->service();
	}

	public static function getProperties() {
		return array(
			'id'                  => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'               => array(
				'description' => 'Title.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'description'         => array(
				'description' => 'Description.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'employees'           => array(
				'description' => 'Employee ids.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
				'items'       => array(
					'type' => 'integer',
				),
			),
			'categories'          => array(
				'description' => 'Category ids.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
				'items'       => array(
					'type' => 'integer',
				),
			),
			'price'               => array(
				'description' => 'Price.',
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'duration'            => array(
				'description' => 'Duration time, as minutes.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'buffer_time_before'  => array(
				'description' => 'Buffer time before, as minutes. Time needed to get prepared for the appointment, when another booking for the same service and employee cannot be made.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'buffer_time_after'   => array(
				'description' => 'Buffer time after, as minutes. Time after the appointment (rest, cleanup, etc.), when another booking for the same service and employee cannot be made.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'time_before_booking' => array(
				'description' => 'Time before booking, as minutes. Minimum period before the appointment when customers can submit a booking request.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'max_advance_time_before_reservation' => array(
				'description' => 'Maximum period (in minutes) before the appointment when customers can submit a booking request.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'required'    => false,
			),
			'is_group_service'    => array(
				'description' => 'Allow multiple bookings for the same time slot.',
				'type'        => 'string',
				// "embed" - required in the admin calendar
				'context'     => array( 'embed', 'view', 'edit' ),
				'default'     => false,
			),
			'min_capacity'        => array(
				'description' => 'Minimum capacity. Minimum number of persons per one booking of this service.',
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'max_capacity'        => array(
				'description' => 'Maximum capacity. Maximum number of persons per one booking of this service.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'multiply_price'      => array(
				'description' => 'Multiply price by the number of people.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'default'     => false,
			),
			'custom_quantity_label' => array(
				'description' => 'Custom label for the clients number.',
				'type'        => 'string',
				// "embed" - required in the admin calendar
				'context'     => array( 'embed', 'view', 'edit' ),
				'default'     => '',
			),
			'color'               => array(
				'description' => 'Color.',
				'type'        => 'string',
				'format'      => 'hex-color',
				'context'     => array( 'view', 'edit' ),
				'default'     => Service::DEFAULT_COLOR,
			),
			'variations'          => array(
				'description' => 'Services detail customization for eligible employees.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'employee_id'  => array(
							'type'     => 'integer',
							'required' => true,
						),
						'price'        => array(
							'type'     => 'number',
							'required' => true,
						),
						'duration'     => array(
							'type'     => 'integer',
							'required' => true,
						),
						'min_capacity' => array(
							'type'    => 'integer',
							'minimum' => 1,
							'default' => 1,
						),
						'max_capacity' => array(
							'type'    => 'integer',
							'minimum' => 1,
							'default' => 1,
						),
					),
				),
			),
		);
	}

	public function getEmployees() {
		return $this->entity->getEmployeeIds();
	}

	public function getCategories() {
		$categories    = array();
		$args          = array( 'post_id' => $this->entity->getId() );
		$categoriesRaw = self::getRepository()->findCategories( $args );

		if ( ! count( $categoriesRaw ) ) {
			return $categories;
		}

		foreach ( $categoriesRaw as $categoryRaw ) {
			$categories[] = array(
				'id'   => $categoryRaw->term_id,
				'name' => $categoryRaw->name,
			);
		}

		return $categories;
	}

	public function getVariations() {
		$variations     = array();
		$rawVarioations = $this->entity->getVariations()->toArray();
		if ( ! $rawVarioations || ! count( $rawVarioations ) ) {
			return $variations;
		}
		foreach ( $rawVarioations as $emploeeId => $variation ) {
			$variations[] = array(
				'employee_id'  => $emploeeId,
				'duration'     => $this->entity->getDuration( $emploeeId ),
				'min_capacity' => $this->entity->getMinCapacity( $emploeeId ),
				'max_capacity' => $this->entity->getMaxCapacity( $emploeeId ),
				'price'        => $this->entity->getPrice( $emploeeId ),
			);
		}

		return $variations;
	}

	public function setEmployees( $employees ) {
		$this->entity->setEmployeeIds( $employees );
	}

	public function setVariations( $variations ) {
		foreach ( $variations as $variationData ) {
			$employeeId                        = $variationData['employee_id'];
			$preparedVariations[ $employeeId ] = new ServiceVariation( $variationData );
		};
		$this->entity->setVariations( new ServiceVariations( $preparedVariations ) );
	}
}
