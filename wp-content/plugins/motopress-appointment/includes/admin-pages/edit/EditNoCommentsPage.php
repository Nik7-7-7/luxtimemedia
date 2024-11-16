<?php

namespace MotoPress\Appointment\AdminPages\Edit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class EditNoCommentsPage extends EditPostPage {

	/**
	 * @since 1.5.0
	 */
	protected function addActions() {

		parent::addActions();

		add_action( "mpa_register_{$this->entityType}_metaboxes", array( $this, 'hideComments' ) );
	}

	/**
	 * @since 1.5.0
	 * @access protected
	 */
	public function hideComments() {

		remove_meta_box( 'commentsdiv', $this->postType, 'normal' );
		remove_meta_box( 'commentstatusdiv', $this->postType, 'normal' );
	}
}
