<?php

namespace MotoPress\Appointment\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class Customer implements InterfaceEntity {
	/**
	 * @var int
	 * @since 1.18.0
	 */
	protected $id = 0;

	/**
	 * @var int
	 * @since 1.18.0
	 */
	protected $userId = 0;

	/**
	 * @var string
	 * @since 1.18.0 protected
	 */
	protected $name = '';

	/**
	 * @var string
	 * @since 1.18.0 protected
	 */
	protected $email = '';

	/**
	 * @var string
	 * @since 1.18.0 protected
	 */
	protected $phone = '';

	/**
	 * @var \DateTime|null
	 * @since 1.18.0
	 */
	protected $dateRegistered = null;

	/**
	 * @var \DateTime|null
	 * @since 1.18.0
	 */
	protected $lastActive = null;

	/**
	 * @since 1.18.0
	 *
	 * @todo Backward compatible for mpa-woocommerce version 1.1.1 and below. Will be remove in future.
	 *
	 * @throws \Exception
	 *
	 * @param $property string
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		switch ( $property ) {
			case 'name':
				return $this->getName();
			case 'email':
				return $this->getEmail();
			case 'phone':
				return $this->getPhone();
			default:
				throw new \Exception( 'Property ' . $property . ' does not exist' );
		}
	}

	/**
	 * @since 1.18.0
	 *
	 * @todo Backward compatible for mpa-woocommerce version 1.1.1 and below. Will be remove in future.
	 *
	 * @throws \Exception
	 *
	 * @param $property
	 * @param $value
	 *
	 * @return void
	 */
	public function __set( $property, $value ) {
		switch ( $property ) {
			case 'name':
				$this->setName( $value );
				break;
			case 'email':
				$this->setEmail( $value );
				break;
			case 'phone':
				$this->setPhone( $value );
				break;
			default:
				throw new \Exception( 'The property ' . $property . ' cannot be set.' );
		}
	}

	/**
	 * @return int
	 * @since 1.18.0
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return int
	 * @since 1.18.0
	 */
	public function getUserId(): int {
		return $this->userId;
	}

	/**
	 * @return string
	 * @since 1.13.0
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 * @since 1.13.0
	 */
	public function getEmail(): string {
		return $this->email;
	}

	/**
	 * @return string
	 * @since 1.13.0
	 */
	public function getPhone(): string {
		return $this->phone;
	}

	/**
	 * @return \DateTime|null
	 * @since 1.18.0
	 */
	public function getDateRegistered() {
		return $this->dateRegistered;
	}

	/**
	 * @return \DateTime|null
	 * @since 1.18.0
	 */
	public function getLastActive() {
		return $this->lastActive;
	}

	/**
	 * @param int $id
	 *
	 * @since 1.18.0
	 */
	public function setId( int $id ) {
		$this->id = $id;
	}

	/**
	 * @param int $userId
	 *
	 * @since 1.18.0
	 */
	public function setUserId( int $userId ) {
		$this->userId = $userId;
	}

	/**
	 * @param string $name
	 *
	 * @since 1.18.0
	 */
	public function setName( string $name ) {
		$this->name = $name;
	}

	/**
	 * @param string $email Must be valid email
	 *
	 * @return void
	 * @throws \Exception Invalid email address.
	 * @since 1.18.0
	 */
	public function setEmail( string $email ) {
		if ( $email && ! is_email( $email ) ) {
			throw new \Exception( esc_html__( 'Please provide a valid email address.', 'motopress-appointment' ) );
		}

		$this->email = $email;
	}

	/**
	 * @param string $phone
	 *
	 * @since 1.18.0
	 */
	public function setPhone( string $phone ) {
		$this->phone = $phone;
	}

	/**
	 * @param \DateTime $dateRegistered
	 *
	 * @since 1.18.0
	 */
	public function setDateRegistered( \DateTime $dateRegistered ) {
		$this->dateRegistered = $dateRegistered;
	}

	/**
	 * @param \DateTime $lastActive
	 *
	 * @since 1.18.0
	 */
	public function setLastActive( \DateTime $lastActive ) {
		$this->lastActive = $lastActive;
	}
}
