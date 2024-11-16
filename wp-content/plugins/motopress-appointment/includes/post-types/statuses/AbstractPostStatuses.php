<?php

namespace MotoPress\Appointment\PostTypes\Statuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractPostStatuses {

	/** @since 1.0 */
	const STATUS_AUTO_DRAFT = 'auto-draft';

	/** @since 1.13.0 */
	const STATUS_DRAFT = 'draft';

	/** @since 1.0 */
	const STATUS_TRASH = 'trash';

	/** @since 1.0 */
	const STATUS_PUBLISH = 'publish';

	/**
	 * @var string 'mpa_entity_name'
	 *
	 * @since 1.0
	 */
	protected $postType;

	/**
	 * @var string 'entity_name'
	 *
	 * @since 1.0
	 */
	protected $entityType;

	/**
	 * @var array Array of [label, label_count, is_public, is_internal, is_manual].
	 * "is_manual" - user can set this status in
	 *     the admin panel.
	 *
	 * @since 1.0
	 */
	protected $statuses = array();

	/**
	 * @param string $postType
	 *
	 * @since 1.0
	 */
	public function __construct( $postType ) {
		$this->postType   = $postType;
		$this->entityType = mpa_unprefix( $postType );

		$this->initStatuses();
		$this->addActions();
	}

	/**
	 * @since 1.0
	 */
	abstract protected function initStatuses();

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		add_action( 'init', array( $this, 'registerStatuses' ), 5 );
		add_action( 'transition_post_status', array( $this, 'transitionStatus' ), 10, 3 );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function registerStatuses() {
		foreach ( $this->statuses as $statusName => $args ) {
			$registerArgs = array(
				'label'       => $args['label'],
				'label_count' => $args['label_count'],
				'public'      => $args['is_public'],
				'internal'    => $args['is_internal'],
			);

			register_post_status( $statusName, $registerArgs );
		}
	}

	/**
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param \WP_Post $post
	 *
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function transitionStatus( $newStatus, $oldStatus, $post ) {
		if ( $post->post_type !== $this->postType || $newStatus === $oldStatus ) {
			return;
		}

		// Don't log new/auto-draft posts
		if ( $this->isNewPost( $newStatus ) ) {
			return;
		}

		// Prevent status transition, for example, on import
		/** @since 1.0 */
		$preventTransition = apply_filters( "mpa_prevent_{$this->entityType}_status_transition", false );

		if ( $preventTransition ) {
			return;
		}

		// Load entity
		$repository = mpapp()->repositories()->getByPostType( $this->postType );
		$entity     = ! is_null( $repository ) ? $repository->findById( $post->ID, true ) : null;

		if ( is_null( $entity ) ) {
			return;
		}

		// Make transition
		$this->notifyTransition( $newStatus, $oldStatus, $entity );
		$this->finishTransition( $newStatus, $oldStatus, $entity );
	}

	/**
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param \MotoPress\Appointment\Entities\AbstractEntity $entity
	 *
	 * @since 1.0
	 *
	 * @todo Add support of status 'trash'.
	 */
	protected function notifyTransition( $newStatus, $oldStatus, $entity ) {
		$isNewPost = $this->isNewPost( $oldStatus );

		if ( $isNewPost ) {
			/**
			 * Action example: 'mpa_new_booking_created'.
			 *
			 * For entities like booking it's triggering too early. No meta fields
			 * (customer) or reservation are set. Use action "mpa_booking_placed_by_user"
			 * instead or something alike.
			 *
			 * @since 1.0
			 */
			do_action( "mpa_new_{$this->entityType}_created", $entity, $newStatus );

		} else {
			/**
			 * Action example: 'mpa_booking_status_changed'.
			 *
			 * Entities like booking will have all data on this action.
			 *
			 * @since 1.0
			 */
			do_action( "{$this->postType}_status_changed", $entity, $newStatus, $oldStatus );
		}

		/**
		 * Action examples: 'mpa_booking_confirmed', 'mpa_booking_cancelled' etc.
		 *
		 * @since 1.0
		 */
		do_action( "{$this->postType}_{$newStatus}", $entity, $oldStatus, $isNewPost );
	}

	/**
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param \MotoPress\Appointment\Entities\AbstractEntity $entity
	 *
	 * @since 1.0
	 */
	abstract protected function finishTransition( $newStatus, $oldStatus, $entity);

	/**
	 * @param string $oldStatus
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected function isNewPost( $oldStatus ) {
		return self::STATUS_AUTO_DRAFT == $oldStatus;
	}


	/**
	 * @return array [Status => Label]
	 *
	 * @since 1.0
	 */
	public function getManualStatuses() {
		return wp_list_pluck(
			array_filter(
				$this->statuses,
				function ( $status ) {
					return $status['is_manual'];
				}
			),
			'label'
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	abstract public function getDefaultManualStatus();

	/**
	 * @param string $status
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function hasStatus( $status ) {
		return isset( $this->statuses[ $status ] );
	}

	/**
	 * @param string $status
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel( $status ) {
		if ( isset( $this->statuses[ $status ] ) ) {
			return $this->statuses[ $status ]['label'];
		} else {
			return mpa_get_status_label( $status );
		}
	}

	/**
	 * @param string[] $statuses
	 * @return array [Status => Label]
	 *
	 * @since 1.0
	 */
	public function getLabels( $statuses ) {
		$labels = array_map( array( $this, 'getLabel' ), $statuses );

		return array_combine( $statuses, $labels );
	}
}
