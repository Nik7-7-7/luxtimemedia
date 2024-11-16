<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractBookingEntityTag extends AbstractEntityTag {

	/**
	 * @return Booking
	 */
	public function getEntity(): InterfaceEntity {
		return parent::getEntity();
	}

	/**
	 * @return Booking
	 */
	protected function getEmptyEntity(): InterfaceEntity {
		return new Booking( 0, array( 'uid' => '' ) );
	}
}
