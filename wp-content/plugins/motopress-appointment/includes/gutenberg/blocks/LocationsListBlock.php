<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class LocationsListBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/locations-list';
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
			'locations'      => array(
				'type' => 'string',
			),
			'categories'     => array(
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
		return mpa_shortcodes()->locationsList()->render( $attributes, $content );
	}
}
