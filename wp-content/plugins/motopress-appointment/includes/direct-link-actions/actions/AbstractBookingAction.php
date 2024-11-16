<?php

namespace MotoPress\Appointment\DirectLinkActions\Actions;

use MotoPress\Appointment\DirectLinkActions\AbstractAction;

/**
 * @since 1.15.0
 */
abstract class AbstractBookingAction extends AbstractAction {

	/**
	 * @return \MotoPress\Appointment\Repositories\BookingRepository
	 */
	protected function getEntityRepository() {
		return mpapp()->repositories()->booking();
	}
}
