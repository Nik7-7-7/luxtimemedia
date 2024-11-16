<?php

namespace MotoPress\Appointment\Emails\Tags\Employee;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Employee;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractEmployeeEntityTag extends AbstractEntityTag {

	protected function getEmptyEntity(): InterfaceEntity {
		return new Employee( 0 );
	}
}
