<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeScheduleBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-schedule';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeSchedule()->render( $attributes, $content );
	}
}
