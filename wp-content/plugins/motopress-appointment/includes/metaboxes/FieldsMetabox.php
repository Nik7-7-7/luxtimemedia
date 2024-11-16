<?php

namespace MotoPress\Appointment\Metaboxes;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Fields\FieldsFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class FieldsMetabox extends AbstractMetabox {

	/**
	 * @var AbstractField[] [Prefixed field name (postmeta name) => Field object]
	 *
	 * @see FieldsMetabox::loadMetabox()
	 *
	 * @since 1.0
	 */
	protected $fields = array();

	/**
	 * @return array [Unprefixed field name => Field args]
	 *
	 * @since 1.0
	 */
	abstract protected function theFields();

	/**
	 * @param string $fieldName Field name, like "total_price". Prefixed or
	 *      unprefixed.
	 * @return AbstractField|null
	 */
	public function getField( string $fieldName ) {
		$privateName = mpa_prefix( $fieldName, 'metabox' );

		if ( array_key_exists( $privateName, $this->fields ) ) {
			return $this->fields[ $privateName ];
		}

		// Don't forget about public visibility
		$publicName = mpa_prefix( $fieldName );

		if ( array_key_exists( $publicName, $this->fields ) ) {
			return $this->fields[ $publicName ];
		}

		// No such field
		return null;
	}

	/**
	 * @since 1.0
	 */
	protected function loadMetabox() {

		$fields = $this->theFields();
		/** @since 1.0 */
		$fields = apply_filters( "{$this->id}_fields", $fields ); // 'mpa_service_settings_metabox_fields'

		// Filter translatable fields
		if ( mpa_is_translation_page( $this->postType ) ) {
			$fields = FieldsFactory::filterTranslatable( $fields );
		}

		$postId = get_the_ID(); // False on Add New post page

		// Instantiate the fields
		foreach ( $fields as $name => $args ) {

			if ( isset( $args['visibility'] ) && 'public' === $args['visibility'] ) {

				$metaName = mpa_prefix( $name );

			} else {

				$metaName = mpa_prefix( $name, 'metabox' );
			}

			// Get the value
			if ( false !== $postId ) {

				$metaValue = $this->loadValue( $postId, $metaName );

			} else {

				$metaValue = null;
			}

			// Create the field
			$field = FieldsFactory::createField( $metaName, $args, $metaValue );

			$this->fields[ $metaName ] = $field;
		}

		// No fields - no metabox (for example, on translation page)
		if ( empty( $this->fields ) ) {
			remove_action( "mpa_register_{$this->entityType}_metaboxes", array( $this, 'register' ), 16 );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $postId
	 * @param string $metaName
	 * @return mixed
	 */
	protected function loadValue( int $postId, string $metaName ) {

		$metaValues = get_post_meta( $postId, $metaName );

		if ( 1 === count( $metaValues ) ) {

			return reset( $metaValues );

		} elseif ( 1 < count( $metaValues ) ) {

			return $metaValues;

		} else {

			return null;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function renderSide(): string {

		$output = '<div class="mpa-side-metabox">';

		foreach ( $this->fields as $field ) {

			$wrapperClass = 'mpa-meta-field';

			if ( 'hidden' == $field->getType() ) {

				$wrapperClass .= ' mpa-hide';
			}

			$output .= '<div class="' . $wrapperClass . '">';

			if ( $field->hasLabel() ) {

				$output .= '<div>' . $field->renderLabel() . '</div>';
			}

			$output .= $field->renderBody();

			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function renderRegular(): string {
		return mpa_render_template( 'private/fields/form-table.php', array( 'fields' => $this->fields ) );
	}

	protected function isFieldMustBeValidatedBeforeSave( AbstractField $field ): bool {
		return true;
	}

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 * @return array [add, update, delete]
	 *
	 * @since 1.0
	 */
	protected function parseValues( int $postId, \WP_Post $post ): array {

		$request = $_POST;

		$values = array(
			'add'    => array(),
			'update' => array(),
			'delete' => array(),
		);

		foreach ( $this->fields as $metaName => $field ) {

			if ( ! isset( $request[ $metaName ] ) ) {
				continue;
			}

			$rawValue = $request[ $metaName ];

			// Prepare single value
			if ( $field->isSingle() ) {

				$field->setValue( $rawValue, $this->isFieldMustBeValidatedBeforeSave( $field ) );

				// Save all values, not just the new values
				$values['update'][ $metaName ] = $field->getValue( 'save' );

				// Prepare multiple values
			} else {
				$oldValues = $field->getValue( 'save' );

				$field->setValue( $rawValue, $this->isFieldMustBeValidatedBeforeSave( $field ) );
				$newValues = $field->getValue( 'save' );

				// Get rid of empty values - ''. If we pass value '' to
				// delete_post_meta() then it will delete all postmetas
				$addValues    = array_filter( array_diff( $newValues, $oldValues ) );
				$deleteValues = array_filter( array_diff( $oldValues, $newValues ) );

				if ( ! empty( $addValues ) ) {
					$values['add'][ $metaName ] = $addValues;
				}

				if ( ! empty( $deleteValues ) ) {
					$values['delete'][ $metaName ] = $deleteValues;
				}
			}
		} // For each field

		return $values;
	}

	/**
	 * @param array $values [add, update, delete]
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @since 1.0
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {

		// $metaValues is an array of values to add
		foreach ( $values['add'] as $metaName => $metaValues ) {
			mpa_add_post_metas( $postId, $metaName, $metaValues );
		}

		// $metaValue is singular
		foreach ( $values['update'] as $metaName => $metaValue ) {
			update_post_meta( $postId, $metaName, $metaValue );
		}

		// $metaValues is an array of values to delete
		foreach ( $values['delete'] as $metaName => $metaValues ) {
			mpa_delete_post_metas( $postId, $metaName, $metaValues );
		}
	}
}
