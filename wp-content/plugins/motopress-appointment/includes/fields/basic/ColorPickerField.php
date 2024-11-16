<?php

namespace MotoPress\Appointment\Fields\Basic;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class ColorPickerField extends TextField {

	/** @since 1.1.0 */
	const TYPE = 'color-picker';

	/**
	 * @var string 'color' | 'text' | 'component' | 'flat'
	 */
	public $colorpickerType = 'color';

	/**
	 * @var array Array of colors string.
	 * e.g. [ '#fff', '#000' ] or [ ['#fff'], ['#000'] ]
	 */
	public $palette = array();

	/**
	 * @var bool
	 */
	public $togglePaletteOnly = false;

	/**
	 * @var bool
	 */
	public $showPaletteOnly = false;

	/**
	 * @var bool
	 */
	public $showPalette = false;

	/**
	 * @var bool
	 */
	public $hideAfterPaletteSelect = false;

	protected function mapFields() {
		return parent::mapFields() + array(
			'colorpicker_type'          => 'colorpickerType',
			'palette'                   => 'palette',
			'toggle_palette_only'       => 'togglePaletteOnly',
			'show_palette_only'         => 'showPaletteOnly',
			'show_palette'              => 'showPalette',
			'hide_after_palette_select' => 'hideAfterPaletteSelect',
		);
	}

	/**
	 * @return string
	 */
	protected function convertPalleteArrayToString() {
		$colorPaletteArray = $this->palette;
		if ( is_null( $colorPaletteArray ) ) {
			return $colorPaletteArray;
		}

		$paletteAttribute = '[';

		foreach ( $colorPaletteArray as $colorsRow ) {
			$paletteAttribute .= '["' . implode( '","', $colorsRow ) . '"],';
		}
		$paletteAttribute = rtrim( $paletteAttribute, ',' );

		$paletteAttribute .= ']';

		return $paletteAttribute;
	}

	protected function getSpectrumDefaultAtts() {
		return array(
			'data-locale'           => mpapp()->i18n()->getCurrentLanguage(),
			'data-allow-empty'      => 'true',
			'data-preferred-format' => 'hex',
			'data-show-input'       => 'true',
			'data-show-initial'     => 'true',
			'data-show-alpha'       => 'false',
			'data-show-palette'     => 'false',
			'data-append-to'        => 'parent',
		);
	}

	protected function getSpectrumAtts() {
		$defaultAtts = $this->getSpectrumDefaultAtts();
		$atts        = array();
		if ( isset( $this->colorpickerType ) ) {
			$atts['data-type'] = $this->colorpickerType;
		}
		if ( count( $this->palette ) ) {
			$atts['data-palette'] = $this->convertPalleteArrayToString();
		}
		if ( $this->togglePaletteOnly ) {
			$atts['data-toggle-palette-only'] = 'true';
		}
		if ( $this->showPaletteOnly ) {
			$atts['data-show-palette-only'] = 'true';
		}
		if ( $this->showPalette ) {
			$atts['data-show-palette'] = 'true';
		}
		if ( $this->hideAfterPaletteSelect ) {
			$atts['data-hide-after-palette-select'] = 'true';
		}

		return array_merge( $defaultAtts, $atts );
	}

	protected function inputAtts() {
		$atts = parent::inputAtts() + array(
			'type'  => 'text',
			'value' => $this->value,
		) + $this->getSpectrumAtts();

		return $atts;
	}
}
