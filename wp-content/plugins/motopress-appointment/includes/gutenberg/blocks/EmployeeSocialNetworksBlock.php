<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class EmployeeSocialNetworksBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/employee-social-networks';
	}

	protected function getAttributes(): array {
		return array(
			'id' => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->employeeSocialNetworks()->render( $attributes, $content );
	}
}
