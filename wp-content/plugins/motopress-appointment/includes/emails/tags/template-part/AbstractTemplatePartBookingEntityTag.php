<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart;

use MotoPress\Appointment\Entities;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractTemplatePartBookingEntityTag extends AbstractTemplatePartEntityTag {

	protected function getEmptyEntity(): InterfaceEntity {
		return new Entities\Booking( 0, array( 'uid' => '' ) );
	}
}
