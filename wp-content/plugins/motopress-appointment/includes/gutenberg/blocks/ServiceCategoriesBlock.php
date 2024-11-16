<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class ServiceCategoriesBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/service-categories';
	}

	protected function getAttributes(): array {
		return array(
			'show_image'         => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_count'         => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_description'   => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'parent'             => array(
				'type' => 'string',
			),
			'categories'         => array(
				'type' => 'string',
			),
			'exclude_categories' => array(
				'type' => 'string',
			),
			'hide_empty'         => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'depth'              => array(
				'type'    => 'number',
				'default' => '3',
			),
			'number'             => array(
				'type'    => 'number',
				'default' => '3',
			),
			'columns_count'      => array(
				'type'    => 'number',
				'default' => '3',
			),
			'orderby'            => array(
				'type'    => 'string',
				'default' => 'none',
			),
			'order'              => array(
				'type'    => 'string',
				'default' => 'desc',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->serviceCategories()->render( $attributes, $content );
	}
}
