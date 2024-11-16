<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart\Admin;

use MotoPress\Appointment\Emails\Tags\TemplatePart\AbstractReservationsDetailsTag;

class ReservationsDetailsTag extends AbstractReservationsDetailsTag {

	protected function getTemplatePartTemplate(): string {
		return mpapp()->templates()->adminReservationDetails()->renderTemplate();
	}
}
