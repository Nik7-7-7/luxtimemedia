<?php

namespace MotoPress\Appointment\Metaboxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class CustomMetabox extends AbstractMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function renderRegular(): string {
		return $this->renderMetabox();
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function renderSide(): string {
		$output      = '<div class="mpa-side-metabox">';
			$output .= $this->renderMetabox();
		$output     .= '</div>';

		return $output;
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	abstract protected function renderMetabox(): string;

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function parseValues( int $postId, \WP_Post $post ): array {
		return array();
	}

	/**
	 * @param array $values
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @since 1.2
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {}
}
