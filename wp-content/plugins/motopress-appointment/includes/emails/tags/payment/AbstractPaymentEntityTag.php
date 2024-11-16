<?php

namespace MotoPress\Appointment\Emails\Tags\Payment;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractPaymentEntityTag extends AbstractEntityTag {

	protected function getEmptyEntity(): InterfaceEntity {
		return new Payment( 0, array( 'uid' => '' ) );
	}
}
