<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Employee;
use MotoPress\Appointment\Handlers\SecurityHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see Employee
 */
class EmployeeRepository extends AbstractRepository {

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function entitySchema() {

		return array(
			'post'     => array( 'ID', 'post_title', 'post_content' ),
			'postmeta' => array(
				SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER => true,
				'_mpa_employee_phone' => true,
				'_mpa_contacts'        => true,
				'_mpa_social_networks' => true,
				'_mpa_additional_info' => true,
			),
		);
	}

	/**
	 * @param array $postData
	 * @return Employee
	 *
	 * @since 1.0
	 */
	protected function mapPostDataToEntity( $postData ) {

		$id = (int) $postData['ID'];

		$fields = array(
			'name'           => $postData['post_title'],
			'bio'            => $postData['post_content'],
			'contacts'       => empty( $postData['contacts'] ) ? array() : $postData['contacts'],
			'socialNetworks' => empty( $postData['social_networks'] ) ? array() : $postData['social_networks'],
			'additionalInfo' => empty( $postData['additional_info'] ) ? array() : $postData['additional_info'],
		);

		$employeeWPUserFieldName = mpa_unprefix( SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER );

		if ( ! empty( $postData[ $employeeWPUserFieldName ] ) ) {
			$fields['wpUserId'] = $postData[ $employeeWPUserFieldName ];
		}

		if ( ! empty( $postData['employee_phone'] ) ) {
			$fields['phoneNumber'] = $postData['employee_phone'];
		}

		return new Employee( $id, $fields );
	}

	/**
	 * @since 1.7.0
	 * @return int|null
	 *
	 * @todo Search ID instead of the whole entity.
	 */
	public function findIdByUserId( int $wpUserId = 0 ) {

		if ( 0 === $wpUserId && is_user_logged_in() ) {
			$wpUserId = get_current_user_id();
		}

		$userEmployee = $this->findByMeta( SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER, $wpUserId );

		return null !== $userEmployee ? $userEmployee->getId() : null;
	}

	/**
	 * @since 1.7.0
	 * @return Entities\Employee|null
	 */
	public function findByUserId( int $wpUserId = 0 ) {

		if ( 0 === $wpUserId && is_user_logged_in() ) {
			$wpUserId = get_current_user_id();
		}

		return $this->findByMeta( SecurityHandler::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER, $wpUserId );
	}
}
