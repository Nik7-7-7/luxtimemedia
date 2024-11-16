<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractReservationEntityTag extends AbstractEntityTag {

	protected function getEmptyEntity(): InterfaceEntity {
		return new Reservation( 0, array( 'uid' => '' ) );
	}
}
