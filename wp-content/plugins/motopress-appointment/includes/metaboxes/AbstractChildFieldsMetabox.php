<?php

namespace MotoPress\Appointment\Metaboxes;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Knows how to save parent entity ID into "post_parent" instead of postmeta.
 *
 * @since 1.5.0
 */
abstract class AbstractChildFieldsMetabox extends FieldsMetabox {

	/**
	 * @since 1.5.0
	 * @var string Prefixed postmeta name of the parent entity ID.
	 */
	public $parentPostmeta = '_mpa_parent_id';

	/**
	 * @since 1.5.0
	 *
	 * @param int $postId
	 * @param string $metaName
	 * @return mixed
	 */
	protected function loadValue( int $postId, string $metaName ) {
		if ( $metaName === $this->parentPostmeta ) {
			// Return post ID or "" (so as not to show "0" in the input field)
			$parentId = (int) wp_get_post_parent_id( $postId );
			return $parentId ? $parentId : '';
		} else {
			return parent::loadValue( $postId, $metaName );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $postId
	 * @param WP_Post $post
	 * @return array [add, update, delete, post?]
	 */
	protected function parseValues( int $postId, \WP_Post $post ): array {
		$values = parent::parseValues( $postId, $post );

		// Remove parent entity ID from postmetas
		if ( isset( $values['update'][ $this->parentPostmeta ] ) ) {
			$values['post']['post_parent'] = $values['update'][ $this->parentPostmeta ];

			unset( $values['update'][ $this->parentPostmeta ] );
		}

		return $values;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $values [add, update, delete, post?]
	 * @param int $postId
	 * @param WP_Post $post
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {
		parent::saveValues( $values, $postId, $post );

		// Save parent entity ID
		if ( isset( $values['post']['post_parent'] ) ) {
			wp_update_post(
				array(
					'ID'          => $postId,
					'post_parent' => $values['post']['post_parent'],
				)
			);
		}
	}
}
