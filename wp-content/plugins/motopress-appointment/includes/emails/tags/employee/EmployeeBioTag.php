<?php

namespace MotoPress\Appointment\Emails\Tags\Employee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class EmployeeBioTag extends AbstractEmployeeEntityTag {

	public function getName(): string {
		return 'employee_bio';
	}

	protected function description(): string {
		return esc_html__( 'Employee description', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getBio();
	}
}
