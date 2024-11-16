<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractBookingPaymentsTag extends AbstractTemplatePartBookingEntityTag {

	public function getName(): string {
		return 'booking_payments';
	}

	protected function description(): string {
		return esc_html__( 'Booking payments', 'motopress-appointment' );
	}

	/**
	 * @return array|\MotoPress\Appointment\Entities\InterfaceEntity[]
	 */
	protected function getTemplatePartEntities(): array {
		return $this->entity->getPayments() ? $this->entity->getPayments() : array();
	}

}
