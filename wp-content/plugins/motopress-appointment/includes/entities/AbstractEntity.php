<?php

namespace MotoPress\Appointment\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractEntity implements InterfaceEntity {

	/**
	 * @var int
	 *
	 * @since 1.0
	 * @since 1.19.0 protected
	 */
	protected $id = 0;

	/**
	 * @param int $id
	 * @param array $fieldValues Optional.
	 *
	 * @since 1.0
	 */
	public function __construct( $id, $fieldValues = array() ) {
		$this->id = $id;
		$this->setupFields( $fieldValues );
	}

	/**
	 * @param $fieldValues
	 *
	 * @since 1.0
	 */
	protected function setupFields( $fieldValues ) {

		foreach ( $fieldValues as $field => $value ) {

			$setFieldMethod = 'set' . ucfirst( $field );

			if ( method_exists( $this, $setFieldMethod ) ) {

				$this->$setFieldMethod( $value );

			} else {
				$this->$field = $value;
			}
		}
	}

	/**
	 * @return int
	 *
	 * @since 1.11.0
	 */
	public function getId(): int {
		return $this->id;
	}

	public function setId( int $id ) {
		$this->id = $id;
	}
}
