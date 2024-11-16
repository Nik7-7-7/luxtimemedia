<?php

namespace MotoPress\Appointment\AdminPages\Edit;

use MotoPress\Appointment\AdminPages\Traits\ShortcodeTitleActions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EditShortcodePage extends EditPostPage {

	use ShortcodeTitleActions;

	/**
	 * @since 1.2
	 */
	protected function addActions() {

		parent::addActions();

		add_action( 'admin_footer', array( $this, 'addTitleActions' ) );
	}
}
