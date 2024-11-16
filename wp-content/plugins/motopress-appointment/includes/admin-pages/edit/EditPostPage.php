<?php

namespace MotoPress\Appointment\AdminPages\Edit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class EditPostPage {

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $postType;

	/**
	 * @var string Like the post type, but without the prefix.
	 *
	 * @since 1.0
	 */
	protected $entityType;

	/**
	 * @param string $postType
	 *
	 * @since 1.0
	 */
	public function __construct( $postType ) {

		$this->postType   = $postType;
		$this->entityType = mpa_unprefix( $postType );

		// Global variable $typenow is not ready on action 'admin_init'. Wait
		// until the real page loads
		add_action( 'load-post.php', array( $this, 'onLoad' ), 15 );
		add_action( 'load-post-new.php', array( $this, 'onLoad' ), 15 );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function onLoad() {

		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$this->addActions();

		/** @since 1.0 */
		do_action( "mpa_edit_{$this->entityType}_page_loaded" );
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function enqueueScripts() {
		mpa_assets()->enqueueBundle( 'mpa-edit-post' );
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isCurrentPage() {
		return $this->isCurrentAddNewPage() || $this->isCurrentEditPage();
	}

	/**
	 * @return bool
	 *
	 * @global string $pagenow
	 * @global string $typenow
	 *
	 * @since 1.0
	 */
	public function isCurrentEditPage() {
		global $pagenow, $typenow;

		return is_admin() && 'post.php' === $pagenow && $typenow === $this->postType;
	}

	/**
	 * @return bool
	 *
	 * @global string $pagenow
	 * @global string $typenow
	 *
	 * @since 1.0
	 */
	public function isCurrentAddNewPage() {
		global $pagenow, $typenow;

		return is_admin() && 'post-new.php' === $pagenow && $typenow === $this->postType;
	}

	/**
	 * Requests "Add New" and "Edit" are handled through post.php. Method checks
	 * if this is a such save request.
	 *
	 * @return bool
	 */
	public function isCurrentSaveNewPage(): bool {
		// Use isCurrentEditPage() since it checks if this a post.php
		return $this->isCurrentEditPage() && isset( $_POST['auto_draft'] ) && (bool) $_POST['auto_draft'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $postId
	 * @param array $additionalArgs Optional.
	 * @return string
	 */
	public function getUrl( $postId, $additionalArgs = array() ) {

		$args = array_merge(
			array(
				'post'   => $postId,
				'action' => 'edit',
			),
			$additionalArgs
		);

		$url = add_query_arg( $args, admin_url( 'post.php' ) );

		return $url;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $additionalArgs Optional.
	 * @return string
	 */
	public function getNewPostUrl( $additionalArgs = array() ) {

		$args = array_merge(
			array(
				'post_type' => $this->postType,
			),
			$additionalArgs
		);

		$url = add_query_arg( $args, admin_url( 'post-new.php' ) );

		return $url;
	}
}
