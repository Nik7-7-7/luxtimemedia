<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeImageBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-image';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeImage()->render( $attributes, $content );
	}
}
