<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeTitleBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-title';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeTitle()->render( $attributes, $content );
	}
}
