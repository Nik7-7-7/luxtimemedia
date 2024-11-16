<?php

namespace MotoPress\Appointment\Emails\Tags\Employee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class EmployeeIdTag extends AbstractEmployeeEntityTag {

	public function getName(): string {
		return 'employee_id';
	}

	protected function description(): string {
		return esc_html__( 'Employee ID', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return strval( $id );
	}
}
