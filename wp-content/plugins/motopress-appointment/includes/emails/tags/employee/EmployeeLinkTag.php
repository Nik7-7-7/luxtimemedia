<?php

namespace MotoPress\Appointment\Emails\Tags\Employee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class EmployeeLinkTag extends AbstractEmployeeEntityTag {

	public function getName(): string {
		return 'employee_link';
	}

	protected function description(): string {
		return esc_html__( 'Employee link', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return sprintf( '<a href="%s">%s</a>', get_the_permalink( $id ), get_the_title( $id ) );
	}
}
