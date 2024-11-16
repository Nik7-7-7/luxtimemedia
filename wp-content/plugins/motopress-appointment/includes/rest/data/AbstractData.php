<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Rest\ApiHelper;

abstract class AbstractData {

	public $entity;
	protected $_entity_init_state;

	public function __construct( $entity ) {
		$this->entity             = $entity;
		$this->_entity_init_state = clone $entity;
	}

	/**
	 * @param $property
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get( $property ) {

		$propertyCamelCase = ApiHelper::convertSnakeToCamelString( $property );

		$propertyNameCamelCase = lcfirst( $propertyCamelCase );
		$getterCallback        = 'get' . $propertyCamelCase;
		$getterCallbackForBool = 'is' . $propertyCamelCase;

		if ( method_exists( $this, $getterCallback ) ) {

			return $this->{$getterCallback}();

		} elseif ( method_exists( $this->entity, $getterCallbackForBool ) ) {

			return $this->entity->{$getterCallbackForBool}();

		} elseif ( method_exists( $this->entity, $propertyCamelCase ) ) {

			// Method name = boolean property name. For exmaple:
			//     $service->isGroupService() ↔ Service::$isGroupService
			return $this->entity->{$propertyCamelCase}();

		} elseif ( method_exists( $this->entity, $getterCallback ) ) {

			return $this->entity->{$getterCallback}();

		} elseif ( property_exists( $this->entity, $propertyNameCamelCase ) ) {

			return $this->entity->{$propertyNameCamelCase};

		} else {
			throw new \Exception( sprintf( 'You need to implement method %s in class %s.', $getterCallback, static::class ) );
		}
	}


	/**
	 * @param $property
	 * @param $value
	 *
	 * @throws \Exception
	 */
	public function __set( $property, $value ) {

		$setterCallback = 'set' . ApiHelper::convertSnakeToCamelString( $property );
		if ( method_exists( $this, $setterCallback ) ) {
			$this->{$setterCallback}( $value );
			return;
		}
		if ( method_exists( $this->entity, $setterCallback ) ) {
			$this->entity->{$setterCallback}( $value );
			return;
		}
		$writableFields = static::getWritableFields();
		if ( ! isset( $writableFields[ $property ] ) && ! property_exists( $this, $property ) ) {
			throw new \Exception( sprintf( 'You cannot set readonly property: %s to class %s.', $property, static::class ) );
		}
		$this->$property = $value;
	}

	/**
	 * @return array
	 */
	abstract public static function getProperties();


	/**
	 * @return array
	 */
	public static function getFields() {
		return array_keys( static::getProperties() );
	}

	/**
	 * @return array
	 */
	public static function getRequiredFields() {
		return array_keys(
			array_filter(
				static::getProperties(),
				function ( $schema ) {
					return ! empty( $schema['required'] );
				}
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getRequiredFieldKeys() {
		return array_keys( static::getRequiredFields() );
	}

	/**
	 * @return array
	 */
	public static function getWritableFields() {
		return array_filter(
			static::getProperties(),
			function ( $schema ) {
				return empty( $schema['readonly'] );
			}
		);
	}

	/**
	 * @return array
	 */
	public static function getWritableFieldKeys() {
		return array_keys( static::getWritableFields() );
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getData() {
		$data   = array();
		$fields = static::getFields();
		foreach ( $fields as $field ) {
			$data[ $field ] = $this->{$field};
		}

		return $data;
	}


	public static function getSchema( $title ) {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => sanitize_title( $title ),
			'type'       => 'object',
			'properties' => static::getProperties(),
		);
	}
}
