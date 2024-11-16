<?php

namespace MotoPress\Appointment\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
abstract class AbstractUniqueEntity extends AbstractEntity implements InterfaceEntity, InterfaceUniqueEntity {

	/**
	 * Unique identifier. Most likely is UUID v4.
	 *
	 * @since 1.5.0
	 * @var string
	 */
	protected $uid = '';

	/**
	 * @since 1.5.0
	 *
	 * @param int $id
	 * @param array $fieldsData Optional.
	 */
	public function __construct( $id, $fieldsData = array() ) {

		$this->uid = mpa_generate_uuid4();

		parent::__construct( $id, $fieldsData );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getUid(): string {
		return $this->uid;
	}

	public function setUid( string $uid ) {
		$this->uid = $uid;
	}
}
