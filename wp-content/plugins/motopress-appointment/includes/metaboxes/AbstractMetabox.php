<?php

namespace MotoPress\Appointment\Metaboxes;

use MotoPress\Appointment\Helpers\AdminUIHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractMetabox {

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
	 * @var string "{$entityType}_{$type}_metabox", like 'service_setting_metabox'.
	 *
	 * @see AbstractMetabox::theName()
	 *
	 * @since 1.0
	 */
	protected $name;

	/**
	 * @var string "mpa_{$name}", like 'mpa_service_settings_metabox'.
	 *
	 * @since 1.0
	 */
	protected $id;

	/**
	 * @var string 'side', 'normal' or 'advanced'.
	 *
	 * @since 1.0
	 */
	public $context;

	/**
	 * @var string 'low', 'default' or 'high'.
	 *
	 * @since 1.0
	 */
	public $priority;

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $capability = 'edit_post';

	/**
	 * @var string "mpa_save_{$name}", like 'mpa_save_service_settings_metabox'.
	 *
	 * @since 1.0
	 */
	protected $saveAction;

	/**
	 * @var string "_mpa_{$name}_nonce", like '_mpa_service_settings_metabox_nonce'.
	 *
	 * @since 1.0
	 */
	protected $nonceField;

	/**
	 * @var bool
	 *
	 * @since 1.0
	 */
	protected $isLoaded = false;

	/**
	 * @var bool
	 *
	 * @since 1.0
	 */
	protected $isSaved = false;

	/**
	 * @param string $postType
	 * @param string $context Optional. 'side', 'normal' or 'advanced'. 'normal'
	 *     by default.
	 * @param string $priority Optional. 'low', 'default' or 'high'. 'default'
	 *     by default.
	 *
	 * @since 1.0
	 */
	public function __construct( $postType, $context = 'normal', $priority = 'default' ) {
		$this->postType   = $postType;               // 'mpa_service'
		$this->entityType = mpa_unprefix( $postType ); // 'service'

		$this->context  = $context;
		$this->priority = $priority;

		$this->name       = mpa_unprefix( $this->theName() );               // 'service_settings_metabox'
		$this->id         = mpa_prefix( $this->name );                      // 'mpa_service_settings_metabox'
		$this->saveAction = mpa_prefix( "save_{$this->name}" );             // 'mpa_save_service_settings_metabox'
		$this->nonceField = mpa_prefix( "{$this->name}_nonce", 'private' ); // '_mpa_service_settings_metabox_nonce'

		$this->addActions();
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract protected function theName(): string;

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		// Triggers only on $postType's edit page (WordPress logic)
		add_action( "mpa_register_{$this->entityType}_metaboxes", array( $this, 'load' ), 15 );

		// Add +1 to priority so load() can cancel the register function; otherwise
		// (with the same priority) load() will not do anything with remove_action()
		add_action( "mpa_register_{$this->entityType}_metaboxes", array( $this, 'register' ), 16 );

		add_action( "save_post_{$this->postType}", array( $this, 'save' ), 15, 2 );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function load() {
		if ( $this->isLoaded ) {
			return;
		}

		$this->loadMetabox();

		$this->isLoaded = true;
	}

	/**
	 * @since 1.0
	 */
	protected function loadMetabox() {}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function register() {
		add_meta_box(
			$this->id,
			$this->getLabel(),
			array( $this, 'display' ),
			$this->postType,
			$this->context,
			$this->priority
		);
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function display() {

		wp_nonce_field( $this->saveAction, $this->nonceField );

		/** @since 1.0 */
		do_action( "mpa_display_{$this->name}" ); // 'mpa_display_service_settings_metabox'

		if ( 'side' === $this->context ) {
			echo $this->renderSide();
		} else {
			echo $this->renderRegular();
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract protected function renderSide(): string;

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract protected function renderRegular(): string;

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function save( int $postId, \WP_Post $post ) {

		try {
			if ( $this->isSaved ) {
				return;
			}

			// Method load() was not called at the moment of save
			$this->load();

			if ( ! $this->canSave( $postId, $post ) ) {
				return;
			}

			$values = $this->parseValues( $postId, $post );

			// Update $isSaved before the method saveValues(): if it uses something
			// like wp_update_post(), then the action "save_post_{$postType}" may
			// trigger multiple times
			$this->isSaved = true;

			$this->saveValues( $values, $postId, $post );

		} catch ( \Throwable $e ) {

			AdminUIHelper::addAdminNotice( AdminUIHelper::ADMIN_NOTICE_TYPE_ERROR, $e->getMessage() );
		}
	}

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected function canSave( int $postId, \WP_Post $post ): bool {

		// Don't save anything for revisions and autosaves
		if ( mpa_is_post_autosave( $post ) || mpa_is_post_revision( $post ) ) {
			return false;
		}

		// Don't trigger for other posts
		if ( empty( $_POST['post_ID'] ) || (int) $_POST['post_ID'] != $postId ) {
			return false;
		}

		// Check capabilities
		if ( ! current_user_can( $this->capability, $postId ) ) {
			return false;
		}

		// Is this a valid request?
		if ( ! mpa_verify_nonce( $this->saveAction, $this->nonceField ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 * @return array See the exact format in the child classes.
	 *
	 * @since 1.0
	 */
	abstract protected function parseValues( int $postId, \WP_Post $post ): array;

	/**
	 * @param array $values
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @since 1.0
	 */
	abstract protected function saveValues( array $values, int $postId, \WP_Post $post );

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract public function getLabel(): string;
}
