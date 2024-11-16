<?php

namespace MotoPress\Appointment\Emails\Tags\Service;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Service;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractServiceEntityTag extends AbstractEntityTag {

	protected function getEmptyEntity(): InterfaceEntity {
		return new Service( 0 );
	}
}
