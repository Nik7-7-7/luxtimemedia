<?php

namespace MotoPress\Appointment\Emails\Tags\General;

use MotoPress\Appointment\Emails\Tags\AbstractTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class AppointmentTag extends AbstractTag {

	public function getName(): string {
		return 'appointment';
	}

	protected function description(): string {
		return esc_html__( 'The plugin name', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpapp()->getName();
	}
}
