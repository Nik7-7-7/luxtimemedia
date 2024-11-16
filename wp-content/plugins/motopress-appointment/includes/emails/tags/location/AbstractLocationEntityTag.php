<?php

namespace MotoPress\Appointment\Emails\Tags\Location;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Location;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractLocationEntityTag extends AbstractEntityTag {

	protected function getEmptyEntity(): InterfaceEntity {
		return new Location( 0 );
	}
}
