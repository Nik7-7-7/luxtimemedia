<?php

namespace MotoPress\Appointment\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class FieldsFactory {

	/**
	 * @param array $fields [Field name => Field args]
	 * @param array $values [Field name => Field value]. Optional.
	 * @return \MotoPress\Appointment\Fields\AbstractField[]
	 *
	 * @since 1.0
	 */
	public static function createFields( $fields, $values = array() ) {

		$instances = array();

		foreach ( $fields as $inputName => $args ) {

			$type = isset( $args['type'] ) ? $args['type'] : 'unknown';

			$value = isset( $values[ $inputName ] ) ? $values[ $inputName ] : null;

			// Pull values for nested fields
			if ( 'union' === $type && isset( $args['fields'] ) ) {

				if ( ! isset( $args['values'] ) ) {

					$args['values'] = array();
				}

				$args['values'] = array_merge(
					$args['values'],
					array_intersect_key( $values, $args['fields'] )
				);
			}

			// Create field
			$instances[ $inputName ] = static::createField( $inputName, $args, $value );
		}

		return $instances;
	}

	/**
	 * @param string $inputName Prefixed name.
	 * @param array $args
	 * @param mixed $value Optional. Null by default.
	 * @return \MotoPress\Appointment\Fields\AbstractField
	 *
	 * @since 1.0
	 */
	public static function createField( $inputName, $args, $value = null ) {

		$type  = isset( $args['type'] ) ? $args['type'] : 'unknown';
		$class = static::getClass( $type );

		if ( is_null( $value ) && isset( $args['value'] ) ) {
			$value = $args['value'];
		}

		if ( ! is_null( $value ) ) {

			return new $class( $inputName, $args, $value );

		} else {

			return new $class( $inputName, $args );
		}
	}

	/**
	 * @param string $type
	 * @return string Field class to instantiate.
	 *
	 * @since 1.0
	 */
	public static function getClass( $type ) {

		$complexFields = array(
			'attributes',
			'custom-workdays',
			'days-off',
			'edit-reservations',
			'service-variations',
			'timetable',
			'license-settings',
			'employee-user',
			'time-period',
			'trigger-period',
			'container'
		);

		$displayFields = array( 'payment-details' );

		if ( in_array( $type, $complexFields, true ) ) {

			$classGroup = 'Complex';

		} elseif ( in_array( $type, $displayFields, true ) ) {

			$classGroup = 'Display';

		} else {

			$classGroup = 'Basic';
		}

		$namespace = __NAMESPACE__;
		$className = mpa_str_to_class_name( $type ) . 'Field';
		$classFile = 'includes/fields/' . strtolower( $classGroup ) . "/{$className}.php";

		// If there is no such field in the current plugin,
		// then try to search in addons
		if ( ! file_exists( mpa_path_to( $classFile ) ) ) {
			$class = apply_filters( "mpa_{$type}_field_class", '' );

			// Use UnknownField if still nothing found
			if ( empty( $class ) ) {
				$class = "\\{$namespace}\\Basic\\UnknownField";
			}
		} else {
			$class = "\\{$namespace}\\{$classGroup}\\{$className}";
		}

		return $class;
	}

	/**
	 * @param array $fields [Field name => Field args]
	 * @return array
	 *
	 * @since 1.0
	 */
	public static function filterTranslatable( $fields ) {

		$translatableFields = array_filter(
			$fields,
			function ( $args ) {
				return isset( $args['translatable'] ) && $args['translatable'];
			}
		);

		return $translatableFields;
	}
}
