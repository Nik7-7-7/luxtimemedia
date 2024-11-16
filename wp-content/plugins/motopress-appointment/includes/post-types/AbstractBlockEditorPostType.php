<?php

declare(strict_types=1);

namespace MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractBlockEditorPostType extends AbstractPostType {

	protected function addActions() {
		parent::addActions();

		add_filter( 'use_block_editor_for_post_type', array( $this, 'filterBlockEditerState' ), 10, 2 );
	}

	/**
	 * @access protected
	 */
	public function filterBlockEditerState( bool $enableBlockEditor, string $postType ): bool {
		if ( $postType === $this->getPostType() ) {
			$enableBlockEditor = true;
		}

		return $enableBlockEditor;
	}

	/**
	 * @return array
	 */
	protected function registerArgs() {
		return array(
			'show_in_rest' => true, // Must be true to enable the Gutenberg editor
		);
	}
}
