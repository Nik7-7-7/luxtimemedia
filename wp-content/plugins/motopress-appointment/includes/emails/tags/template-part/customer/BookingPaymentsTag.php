<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart\Customer;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Emails\Tags\TemplatePart\AbstractBookingPaymentsTag;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingPaymentsTag extends AbstractBookingPaymentsTag {

	protected function getTemplatePartTemplate(): string {
		return mpapp()->templates()->customerPaymentDetails()->renderTemplate();
	}

	protected function getTemplatePartTemplateTags(): InterfaceTags {
		return EmailTagsHelper::CustomerPaymentDetailsTemplatePartTags();
	}
}
