<?php

namespace MotoPress\Appointment\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see \MotoPress\Appointment\Repositories\LocationRepository
 */
class Location extends AbstractEntity {

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
	protected $info = '';


	public function getName(): string {
		return $this->name;
	}

	public function setName( string $name ) {
		$this->name = $name;
	}

	public function getInfo(): string {
		return $this->info;
	}

	public function setInfo( string $info ) {
		$this->info = $info;
	}
}
