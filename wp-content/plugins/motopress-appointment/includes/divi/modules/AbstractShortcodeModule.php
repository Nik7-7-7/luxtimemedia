<?php

namespace MotoPress\Appointment\Divi\Modules;

use ET_Builder_Module;
use MotoPress\Appointment\Shortcodes\AbstractShortcode;

/**
 * @since 1.19.1
 */
abstract class AbstractShortcodeModule extends ET_Builder_Module {

	/**
	 * @return AbstractShortcode
	 */
	abstract protected function getMPAShortcode(): AbstractShortcode;

	/**
	 * Preparing Divi module props to use like shortcode attributes.
	 * If one of the $fieldName does not match the attribute name of shortcode,
	 * you can override the method in your class and specify the attributeName -> fieldData match.
	 *
	 * @return array
	 */
	protected function prepareProps(): array {
		$preparedProps = array();

		$shortcodeAttributes = $this->getMPAShortcode()->getAttributes();

		foreach ( $this->get_fields() as $fieldName => $fieldData ) {
			if ( array_key_exists( $fieldName, $shortcodeAttributes ) ) {
				$preparedProps[ $fieldName ] = $this->props[ $fieldName ];
			}
		}

		return $preparedProps;
	}

	public function render( $attrs, $content, $render_slug ) {
		$shortcode     = $this->getMPAShortcode();
		$preparedProps = $this->prepareProps();

		// Relevant for AbstractPostShortcode
		foreach ( $attrs as $attrName => $attrValue ) {
			if ( ! array_key_exists( $attrName, $preparedProps ) ) {

				// Rendering the shortcode without using Divi module props.
				// Because this is not a Divi module, but a shortcode, the attributes of which are specified in the post. (Appointments > Shortcodes)
				// The condition is necessary because Divi uses shortcode syntax to mark up its modules.
				// Because of this, shortcodes with the same names as modules are executed as modules and not as shortcodes.
				if ( ! empty( $attrs['post'] ) && method_exists( $shortcode, 'getPostArgs' ) ) {
					return $shortcode->render( $attrs, $content );
				}

				// Proxying undefined attributes into a shortcode.
				// Because it's possible that this is a shortcode with the same name, and not the current module.
				// Perhaps these attributes are implemented in the shortcode.
				$preparedProps[ $attrName ] = $attrValue;
			}
		}

		return $shortcode->render( $preparedProps, $content );
	}
}