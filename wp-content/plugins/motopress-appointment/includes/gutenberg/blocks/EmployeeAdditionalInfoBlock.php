<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeAdditionalInfoBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-additional-info';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeAdditionalInfo()->render( $attributes, $content );
	}
}
