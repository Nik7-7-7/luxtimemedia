<?php

namespace MotoPress\Appointment\Metaboxes\Employee;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;
use \MotoPress\Appointment\Handlers\SecurityHandler;
use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeContactsMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'employee_contacts_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Contact Information', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		return array(
			'employee_wordpress_user' => array(
				'type'        => 'employee-user',
				'size'        => 'regular',
				'label'       => __( 'WordPress User Email', 'motopress-appointment' ),
				'description' => __( 'Employee\'s WordPress User account for getting access to the employee\'s schedule, bookings and payments.', 'motopress-appointment' ),
				'options'     => array(),
				'readonly'    => ! SecurityHandler::isUserCanAssignUserToEmployee(),
			),
			'employee_phone'          => array(
				'type'                   => 'phone',
				'label'                  => __( 'Employee Phone Number', 'motopress-appointment' ),
				'description'            => __( 'This phone number can be used for sending SMS notifications.', 'motopress-appointment' ),
				'size'                   => 'regular',
			),
			'contacts'                => array(
				'type'         => 'attributes',
				'translatable' => true,
			),
		);
	}

	protected function isFieldMustBeValidatedBeforeSave( AbstractField $field ): bool {

		$employeePhone = ! empty( $_POST['_mpa_employee_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['_mpa_employee_phone'] ) ) : '';

		$isFieldMustBeValidateBeforeSave = true;

		if ( 'employee_phone' === $field->getName() && empty( $employeePhone ) ) {
				$isFieldMustBeValidateBeforeSave = false;
		}

		return $isFieldMustBeValidateBeforeSave;
	}

	protected function parseValues( int $postId, \WP_Post $post ): array {

		$paresedValues = parent::parseValues( $postId, $post );

		if ( isset( $paresedValues['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ] ) &&
			! empty( $paresedValues['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ] )
		) {

			$wpUser = get_user_by( 'email', $paresedValues['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ] );

			if ( $wpUser ) {

				$employee = mpapp()->repositories()->employee()->findByUserId( $wpUser->ID );

				if ( null !== $employee && $post->ID !== $employee->getId() ) {

					throw new \Exception(
						sprintf(
							// translators: %s is the employee's name
							__( 'Invalid user email because another employee ( %s ) already has it!', 'motopress-appointment' ),
							$employee->getName()
						)
					);
				}
			}
		}

		return $paresedValues;
	}

	protected function saveValues( array $values, int $postId, \WP_Post $post ) {

		if ( isset( $values['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ] ) ) {

			if ( ! empty( $values['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ] ) ) {

				$userEmail = $values['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ];

				$wpUser = get_user_by( 'email', $userEmail );

				if ( $wpUser ) {

					update_post_meta( $postId, SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER, $wpUser->ID );
				}
			} else {

				update_post_meta( $postId, SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER, null );
			}

			unset( $values['update'][ SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER ] );
		}

		parent::saveValues( $values, $postId, $post );
	}


	protected function loadValue( int $postId, string $metaName ) {

		$metaValue = null;

		if ( SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER === $metaName ) {

			$metaValue = get_post_meta( $postId, $metaName, true );

			if ( ! empty( $metaValue ) ) {

				$wpUser = get_user_by( 'ID', $metaValue );

				if ( ! empty( $wpUser ) ) {

					$metaValue = $wpUser->user_email;
				}
			}
		} else {

			$metaValue = parent::loadValue( $postId, $metaName );
		}

		return $metaValue;
	}
}
