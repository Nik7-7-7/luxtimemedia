<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

use MotoPress\Appointment\Metaboxes\CustomMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ShortcodeExampleMetabox extends CustomMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'shortcode_example_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Shortcode', 'motopress-appointment' );
	}

	/**
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function register() {
		// Don't register for draft posts
		if ( get_post_status() === 'publish' ) {
			parent::register();
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function renderMetabox(): string {
		$post = get_post();

		$shortcodeName = get_post_meta( get_the_ID(), '_mpa_name', true );
		$postName      = ! is_null( $post ) ? $post->post_name : '';

		$output = '<p>' . esc_html__( 'Copy this shortcode and paste to your page:', 'motopress-appointment' ) . '</p>';

		$output         .= '<p>';
			$output     .= '<code>';
				$output .= "[{$shortcodeName} post=\"{$postName}\"]";
			$output     .= '</code>';
		$output         .= '</p>';

		return $output;
	}
}
