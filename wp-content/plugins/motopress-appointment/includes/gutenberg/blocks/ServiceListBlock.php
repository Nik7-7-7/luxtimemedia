<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class ServiceListBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/services-list';
	}

	protected function getAttributes(): array {
		return array(
			'show_image'     => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_title'     => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_excerpt'   => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_price'     => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_duration'  => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_capacity'  => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_employees' => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'services'       => array(
				'type' => 'string',
			),
			'employees'      => array(
				'type' => 'string',
			),
			'categories'     => array(
				'type' => 'string',
			),
			'tags'           => array(
				'type' => 'string',
			),
			'posts_per_page' => array(
				'type'    => 'number',
				'default' => '3',
			),
			'columns_count'  => array(
				'type'    => 'number',
				'default' => '3',
			),
			'orderby'        => array(
				'type'    => 'string',
				'default' => 'none',
			),
			'order'          => array(
				'type'    => 'string',
				'default' => 'desc',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->servicesList()->render( $attributes, $content );
	}
}
