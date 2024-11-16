<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeServicesListBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-services-list';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeServiceList()->render( $attributes, $content );
	}
}
