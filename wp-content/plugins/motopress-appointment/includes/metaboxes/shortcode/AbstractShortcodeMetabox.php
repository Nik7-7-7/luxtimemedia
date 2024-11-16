<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class AbstractShortcodeMetabox extends FieldsMetabox {

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function register() {
		$shortcodeName = $this->getCurrentShortcodeName();

		if ( $this->isForShortcode( $shortcodeName ) ) {
			parent::register();
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function getCurrentShortcodeName() {

		if ( mpapp()->pages()->editShortcode()->isCurrentAddNewPage() ) {
			return isset( $_GET['shortcode'] ) ? sanitize_text_field( $_GET['shortcode'] ) : '';
		} else {
			return get_post_meta( get_the_ID(), mpa_prefix( 'name', 'private' ), true );
		}
	}

	/**
	 * @param string $shortcodeName
	 * @return bool
	 *
	 * @since 1.2
	 */
	protected function isForShortcode( $shortcodeName ) {
		return false;
	}
}
