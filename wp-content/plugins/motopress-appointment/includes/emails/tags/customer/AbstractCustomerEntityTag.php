<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

use MotoPress\Appointment\Emails\Tags\AbstractEntityTag;
use MotoPress\Appointment\Entities\Customer;
use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
abstract class AbstractCustomerEntityTag extends AbstractEntityTag {

	/**
	 * @return Customer
	 */
	public function getEntity(): InterfaceEntity {
		return parent::getEntity();
	}

	protected function getEmptyEntity(): InterfaceEntity {
		return new Customer();
	}
}