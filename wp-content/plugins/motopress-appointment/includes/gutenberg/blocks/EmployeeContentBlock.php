<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeContentBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-content';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeContent()->render( $attributes, $content );
	}
}
