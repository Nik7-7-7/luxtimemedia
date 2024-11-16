<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart\Customer;

use MotoPress\Appointment\Emails\Tags\TemplatePart\AbstractReservationsDetailsTag;

class ReservationsDetailsTag extends AbstractReservationsDetailsTag {

	protected function getTemplatePartTemplate(): string {
		return mpapp()->templates()->customerReservationDetails()->renderTemplate();
	}
}
