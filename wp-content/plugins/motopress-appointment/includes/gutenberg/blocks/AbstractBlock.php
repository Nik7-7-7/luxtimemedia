<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

/**
 * @since 1.19.0
 */
abstract class AbstractBlock {

	const ATTRIBUTE_NAME_IN_SHORTCODE = 'mpa_attribute_name_in_shortcode';

	const DEFAULT_ATTRIBUTES = array(
		'anchor'    => array(
			'type'                            => 'string',
			self::ATTRIBUTE_NAME_IN_SHORTCODE => 'html_id',
		),
		'className' => array(
			'type'                            => 'string',
			self::ATTRIBUTE_NAME_IN_SHORTCODE => 'html_class',
		),
	);

	public function __construct() {
		$this->registerBlock();
	}

	abstract protected function getBlockName(): string;

	abstract protected function renderCallback( $attributes, $content ): string;

	protected function registerBlock() {
		register_block_type( $this->getBlockName(), [
			'attributes'      => array_merge( $this->getDefaultAttributes(), $this->getAttributes() ),
			'render_callback' => [ $this, 'internalRender' ],
		] );
	}

	/**
	 * Override the method in class implementations and fill the array with block attributes.
	 *
	 * @return array
	 */
	protected function getAttributes(): array {
		return array();
	}

	protected function getDefaultAttributes(): array {
		return array_map( function ( $attribute ) {
			unset( $attribute[ self::ATTRIBUTE_NAME_IN_SHORTCODE ] );

			return $attribute;
		}, self::DEFAULT_ATTRIBUTES );
	}

	/**
	 * @access protected
	 *
	 * @param array $attributes
	 * @param string $content
	 *
	 * @return string
	 */
	public function internalRender( array $attributes, string $content ): string {
		$attributes = $this->transformAttributes( $attributes );

		return $this->renderCallback( $attributes, $content );
	}

	protected function transformAttributes( $attributes ): array {
		foreach ( self::DEFAULT_ATTRIBUTES as $oldAttributeName => $attributeValue ) {
			if ( isset( $attributes[ $oldAttributeName ] ) && isset( $attributeValue[ self::ATTRIBUTE_NAME_IN_SHORTCODE ] ) ) {
				$newAttributeName                = $attributeValue[ self::ATTRIBUTE_NAME_IN_SHORTCODE ];
				$attributes[ $newAttributeName ] = $attributes[ $oldAttributeName ];
				unset( $attributes[ $oldAttributeName ] );
			}
		}

		return $attributes;
	}
}