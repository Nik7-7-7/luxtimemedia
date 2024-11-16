<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Utils\ValidateUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ImageField extends AbstractField {

	/** @since 1.2 */
	const TYPE = 'image';

	/**
	 * @var string
	 *
	 * @since 1.2
	 */
	protected $thumbnailSize = 'full';

	/**
	 * @var int
	 *
	 * @since 1.2
	 */
	protected $default = 0;

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'thumbnail_size' => 'thumbnailSize',
		);
	}

	/**
	 * @param mixed $value
	 * @return int
	 *
	 * @since 1.2
	 */
	protected function validateValue( $value ) {
		if ( '' === $value ) {
			return $this->default;
		}

		// false, 0, 1, ...
		$thumbId = ValidateUtils::validateInt( $value, 0 );

		if ( $thumbId && wp_attachment_is_image( $thumbId ) ) {
			return $thumbId;
		} else {
			return $this->default;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function renderInput() {
		$mediaArgs = array(
			'add_button_label'    => esc_html__( 'Add Image', 'motopress-appointment' ),
			'remove_button_label' => esc_html__( 'Remove Image', 'motopress-appointment' ),
			'preview_id'          => $this->value,
			'preview_class'       => 'single-image-control',
		);

		return $this->renderMedia( $mediaArgs );
	}

	/**
	 * @param array $args
	 *     @param string $args['add_button_label'] Required.
	 *     @param string $args['remove_button_label'] Required.
	 *     @param int $args['preview_id'] Optional. 0 by default.
	 *     @param string $args['preview_class'] Optional. Only additional
	 *         classes, not the full list.
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function renderMedia( $args ) {
		$previewId     = isset( $args['preview_id'] ) ? $args['preview_id'] : 0;
		$previewImgSrc = '';

		$labelAddButton    = $args['add_button_label'];
		$labelRemoveButton = $args['remove_button_label'];

		$classPreview      = 'media-ctrl-thumbnail attachment-post-thumbnail size-post-thumbnail';
		$classAddButton    = 'button button-secondary mpa-add-media';
		$classRemoveButton = 'button button-secondary mpa-remove-media';

		if ( ! empty( $args['preview_class'] ) ) {
			$classPreview .= ' ' . $args['preview_class'];
		}

		// Get preview image URL
		if ( $previewId > 0 ) {
			$imageData = wp_get_attachment_image_src( $previewId, $this->thumbnailSize );

			if ( false !== $imageData ) {
				$previewImgSrc = $imageData[0];
			}

			$classAddButton .= ' mpa-hide';

		} else {
			$classPreview      .= ' mpa-hide';
			$classRemoveButton .= ' mpa-hide';
		}

		// Render media field
		$wrapperTag = $this->getWrapperTag();

		$output = '<input' . mpa_tmpl_atts( $this->inputAtts() ) . '>';

		$output     .= "<{$wrapperTag} class=\"mpa-preview-wrapper\">";
			$output .= '<img src="' . esc_url( $previewImgSrc ) . '" class="' . esc_attr( $classPreview ) . '">';
		$output     .= "</{$wrapperTag}>";

		$output .= '<button type="button" class="' . esc_attr( $classAddButton ) . '">' . $labelAddButton . '</button>';

		$output .= '<button type="button" class="' . esc_attr( $classRemoveButton ) . '">' . $labelRemoveButton . '</button>';

		return $output;
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function controlAtts() {
		$controlAtts           = parent::controlAtts();
		$controlAtts['class'] .= ' mpa-media-ctrl';

		return $controlAtts;
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function inputAtts() {
		return parent::inputAtts() + array(
			'type'           => 'hidden',
			'value'          => $this->value,
			'thumbnail-size' => $this->thumbnailSize,
		);
	}
}
