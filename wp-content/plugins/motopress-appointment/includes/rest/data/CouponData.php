<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.21.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Coupon;
use MotoPress\Appointment\Rest\ApiHelper;

class CouponData extends AbstractPostData {

	/**
	 * @var Coupon
	 */
	public $entity;

	/**
	 * @var string
	 */
	protected $wpTimezoneString;

	public function __construct( $entity ) {
		parent::__construct( $entity );

		$this->wpTimezoneString = wp_timezone_string();
	}

	public static function getRepository() {
		return mpapp()->repositories()->coupon();
	}

	public static function getProperties() {
		return array(
			'id'              => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'          => array(
				'description' => 'Status.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'code'            => array(
				'description' => 'Code.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'description'     => array(
				'description' => 'Description.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'type'            => array(
				'description' => 'Type.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'amount'          => array(
				'description' => 'Percent or fixed amount according to selected type.',
				'type'        => 'number',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'expiration_date' => array(
				'description' => 'Expiration Date.',
				'anyOf'       => array(
					array(
						'type'   => 'string',
						'format' => 'date',
					),
					array(
						'type'      => 'string',
						'maxLength' => 0,
					),
				),
				'context'     => array( 'view', 'edit' ),
			),
			'services'        => array(
				'description' => 'Services.',
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'min_date'        => array(
				'description' => 'Minimum date.',
				'anyOf'       => array(
					array(
						'type'   => 'string',
						'format' => 'date',
					),
					array(
						'type'      => 'string',
						'maxLength' => 0,
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'max_date'        => array(
				'description' => 'Maximum date.',
				'anyOf'       => array(
					array(
						'type'   => 'string',
						'format' => 'date',
					),
					array(
						'type'      => 'string',
						'maxLength' => 0,
					),
				),
				'context'     => array( 'view', 'edit' ),
			),
			'usage_limit'     => array(
				'description' => 'Usage limit.',
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			),
			'usage_count'     => array(
				'description' => 'Usage count.',
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	}

	public function getExpirationDate() {
		$expirationDate = $this->entity->getExpirationDate();
		if ( ! $expirationDate ) {
			return '';
		}

		return ApiHelper::prepareDateTimeResponse( $expirationDate, $this->wpTimezoneString );
	}

	public function getServices() {
		return $this->entity->getServiceIds();
	}

	public function getMinDate() {
		$maxDate = $this->entity->getMinDate();
		if ( ! $maxDate ) {
			return '';
		}

		return ApiHelper::prepareDateTimeResponse( $maxDate, $this->wpTimezoneString );
	}

	public function getMaxDate() {
		$minDate = $this->entity->getMaxDate();
		if ( ! $minDate ) {
			return '';
		}

		return ApiHelper::prepareDateTimeResponse( $minDate, $this->wpTimezoneString );
	}
}