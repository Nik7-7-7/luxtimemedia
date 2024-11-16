<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Entities\InterfaceEntity;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractReservationsDetailsTag extends AbstractTemplatePartBookingEntityTag {

	public function getName(): string {
		return 'reservations_details';
	}

	protected function description(): string {
		return esc_html__( 'Reservations details', 'motopress-appointment' );
	}

	/**
	 * @return array|InterfaceEntity[]
	 */
	protected function getTemplatePartEntities(): array {
		return $this->entity->getReservations();
	}

	protected function replaceTemplatePartTags( InterfaceEntity $reservation ): string {
		$templatePart = $this->getTemplatePart();

		foreach ( $this->tags as $name => $tag ) {

			$service  = mpapp()->repositories()->service()->findById( $reservation->getServiceId() );
			$location = mpapp()->repositories()->location()->findById( $reservation->getLocationId() );
			$employee = mpapp()->repositories()->employee()->findById( $reservation->getEmployeeId() );

			$tag->setEntity( $reservation );

			if ( $service ) {
				$tag->setEntity( $service );
			}
			if ( $location ) {
				$tag->setEntity( $location );
			}
			if ( $employee ) {
				$tag->setEntity( $employee );
			}

			$templatePart = $tag->replaceTags( $templatePart );
		}

		return $templatePart;
	}

	protected function getTemplatePartTemplateTags(): InterfaceTags {
		return EmailTagsHelper::ReservationDetailsTemplatePartTags();
	}
}
