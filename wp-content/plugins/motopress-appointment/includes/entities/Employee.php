<?php

namespace MotoPress\Appointment\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see \MotoPress\Appointment\Repositories\EmployeeRepository
 */
class Employee extends AbstractEntity {

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $name = '';

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $bio = '';

	/**
	 * @var int
	 */
	protected $wpUserId = 0;

	/**
	 * @var \WP_User|null
	 */
	protected $wpUser;

	/**
	 * @var string
	 */
	protected $phoneNumber = '';

	/**
	 * @var array [label, content, link, class]
	 *
	 * @since 1.2
	 */
	protected $contacts = array();

	/**
	 * @var array [label, content, link, class]
	 *
	 * @since 1.2
	 */
	protected $socialNetworks = array();

	/**
	 * @var array [label, content, link, class]
	 *
	 * @since 1.2
	 */
	protected $additionalInfo = array();


	public function getName(): string {
		return $this->name;
	}

	public function getBio(): string {
		return $this->bio;
	}

	public function getWPUserId(): int {
		return $this->wpUserId;
	}

	/**
	 * @return \WP_User|null
	 */
	public function getWPUser() {

		if ( ! isset( $this->wpUser ) ) {

			$employeeUserId = $this->getWPUserId();

			if ( 0 < $employeeUserId ) {

				$wpUser = get_userdata( (int) $employeeUserId );

				if ( ! empty( $wpUser ) ) {
					$this->wpUser = $wpUser;
				}
			}
		}

		return $this->wpUser;
	}

	public function getEmail(): string {

		$employeeUser = $this->getWPUser();

		return null !== $employeeUser ? $employeeUser->user_email : '';
	}

	public function getPhoneNumber(): string {
		return $this->phoneNumber;
	}

	public function getContacts(): array {
		return $this->contacts;
	}

	public function getSocialNetworks(): array {
		return $this->socialNetworks;
	}

	public function getAdditionalInfo(): array {
		return $this->additionalInfo;
	}
}
