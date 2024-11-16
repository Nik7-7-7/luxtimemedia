<?php

namespace MotoPress\Appointment\Metaboxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SubmitMetabox extends AbstractMetabox {

	/**
	 * @var \MotoPress\Appointment\AdminPages\Edit\EditPostPage|null
	 *
	 * @since 1.0
	 */
	protected $editPage = null;

	/**
	 * @var \MotoPress\Appointment\PostTypes\Statuses\AbstractPostStatuses|null
	 *
	 * @since 1.0
	 */
	protected $statuses = null;

	/**
	 * @param string $postType
	 * @param string $context Optional. 'side', 'normal' or 'advanced'. 'normal'
	 *     by default.
	 * @param string $priority Optional. 'low', 'default' or 'high'. 'default'
	 *     by default.
	 *
	 * @since 1.0
	 */
	public function __construct( $postType, $context = 'side', $priority = 'default' ) {
		parent::__construct( $postType, $context, $priority );

		$this->id = $this->name; // 'submitdiv'
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'submitdiv';
	}

	/**
	 * @since 1.0
	 */
	protected function loadMetabox() {
		// Get edit page class
		$this->editPage = mpapp()->pages()->getEditPage( $this->postType );

		// Get post type statuses
		$postType = mpapp()->postTypes()->getPostType( $this->postType );

		if ( ! is_null( $postType ) && method_exists( $postType, 'statuses' ) ) {
			$this->statuses = $postType->statuses();
		}
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function register() {
		remove_meta_box( 'submitdiv', $this->postType, 'side' );

		parent::register();
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function renderRegular(): string {
		// Not intended for 'normal' or 'advanced' contexts
		return $this->renderSide();
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 *
	 * @todo Add filter "{$postType}_statuses".
	 */
	protected function renderSide(): string {

		// Get current post
		$postId = get_the_ID();
		$post   = false !== $postId ? get_post( $postId ) : null;

		if ( is_null( $post ) ) {

			$post = new \WP_Post( new \stdClass() );

			$post->ID        = 0;
			$post->post_type = $this->postType;
		}

		// Is Add New page?
		$isNew = ! is_null( $this->editPage ) && $this->editPage->isCurrentAddNewPage();

		// Get available statuses
		$statuses = ! is_null( $this->statuses ) ? $this->statuses->getManualStatuses() : array();

		// Render metabox
		return mpa_render_template(
			'private/metaboxes/custom-submit-metabox.php',
			array(
				'post'           => $post,
				'is_new_post'    => $isNew,
				'statuses'       => $statuses,
				'default_status' => $this->statuses->getDefaultManualStatus(),
			)
		);
	}

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function parseValues( int $postId, \WP_Post $post ): array {

		if ( ! empty( $_POST['mpa_post_status'] ) ) {

			$postStatus = sanitize_text_field( $_POST['mpa_post_status'] );

			if ( ! is_null( $this->statuses ) && $this->statuses->hasStatus( $postStatus ) ) {

				return array( 'post_status' => $postStatus );
			}
		}

		return array();
	}

	/**
	 * @param array $values
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @since 1.0
	 *
	 * @todo Fix the double saving (original update + update of the post here).
	 * @todo Test with WPML: sometimes it's impossible to add translations after
	 *     the wp_update_post().
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {
		// Update post status
		if ( isset( $values['post_status'] ) ) {
			mpa_update_post_status( $postId, $values['post_status'] );
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		$postType  = mpapp()->postTypes()->getPostType( $this->postType );
		$typeLabel = ! is_null( $postType ) ? $postType->getSingularLabel() : esc_html_x( 'Post', 'Post type', 'motopress-appointment' );

		// Translators: %s: The post type name, like "Service".
		return sprintf( esc_html__( 'Update %s', 'motopress-appointment' ), $typeLabel );
	}
}
