<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart;

use MotoPress\Appointment\emails\tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class CancelationDetailsTag extends AbstractTemplatePartBookingEntityTag {

	public function getName(): string {
		return 'cancelation_details';
	}

	protected function description(): string {
		return esc_html__( 'Cancelation Details', 'motopress-appointment' );
	}

	protected function getTemplatePartTemplate(): string {
		return mpapp()->templates()->customerBookingCancellation()->renderTemplate();
	}

	protected function getTemplatePartEntities(): array {
		return array( $this->entity );
	}

	protected function getTemplatePartTemplateTags(): InterfaceTags {
		return EmailTagsHelper::CustomerBookingCancellationTemplatePartTags();
	}
}
