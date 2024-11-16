<?php

namespace MotoPress\Appointment\Emails\Tags\Employee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class EmployeeNameTag extends AbstractEmployeeEntityTag {

	public function getName(): string {
		return 'employee_name';
	}

	protected function description(): string {
		return esc_html__( 'Employee name', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getName();
	}
}
