<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeContactsBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-contacts';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeContacts()->render( $attributes, $content );
	}
}
