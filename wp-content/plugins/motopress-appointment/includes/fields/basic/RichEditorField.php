<?php

namespace MotoPress\Appointment\Fields\Basic;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class RichEditorField extends TextareaField {

	/** @since 1.1.0 */
	const TYPE = 'rich-editor';

	/**
	 * @var int
	 *
	 * @since 1.1.0
	 */
	public $rows = 10;

	/**
	 * @var array
	 *
	 * @since 1.1.0
	 */
	public $editorArgs = array();

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'editor_args' => 'editorArgs',
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderInput() {
		$editorArgs = array_merge(
			array(
				'wpautop'       => false,
				'media_buttons' => true,
				'textarea_name' => $this->inputName,
				'textarea_rows' => $this->rows,
				'editor_clas'   => $this->class,
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,spellchecker,wp_adv',
				),
			),
			$this->editorArgs
		);

		ob_start();
		wp_editor( stripslashes( $this->value ), $this->inputId . '-editor', $editorArgs );
		$editor = ob_get_clean();

		return $editor;
	}
}
