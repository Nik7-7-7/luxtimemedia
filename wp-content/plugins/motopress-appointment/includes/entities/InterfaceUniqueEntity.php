<?php

namespace MotoPress\Appointment\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
interface InterfaceUniqueEntity extends InterfaceEntity {

	public function getUid(): string;
}
