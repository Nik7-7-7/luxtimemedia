<?php

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\AdminPages\Traits\ShortcodeTitleActions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ManageShortcodesPage extends ManagePostsPage {

	use ShortcodeTitleActions;

	/**
	 * @since 1.2
	 */
	protected function addActions() {
		parent::addActions();

		add_action( 'admin_footer', array( $this, 'addTitleActions' ) );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function customColumns() {
		return array(
			'shortcode' => esc_html__( 'Shortcode', 'motopress-appointment' ),
		);
	}

	/**
	 * @param string $columnName
	 * @param Schedule $entity
	 * @param int $postId Optional (just for compatibility reasons).
	 *
	 * @since 1.2
	 */
	protected function displayValue( $columnName, $entity, $postId = 0 ) {

		if ( ! $postId ) {
			return parent::displayValue( $columnName, $entity );
		}

		switch ( $columnName ) {
			case 'shortcode':
				$post = get_post( $postId );

				$shortcodeName = get_post_meta( $postId, '_mpa_name', true );
				$postName      = ! is_null( $post ) ? $post->post_name : '';

				if ( ! empty( $shortcodeName ) && ! empty( $postName ) ) {
					echo '<code>', "[{$shortcodeName} post=\"{$postName}\"]", '</code>';
				}

				break;
		}
	}

	/**
	 * Fires only for custom columns.
	 *
	 * @param string $columnName
	 * @param int $postId
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function manageCustomColumn( $columnName, $postId ) {
		$this->displayValue( $columnName, null, $postId );
	}

	/**
	 * @since 1.2
	 */
	protected function enqueueScripts() {
		mpa_assets()->enqueueBundle( 'mpa-manage-posts' );
	}
}
