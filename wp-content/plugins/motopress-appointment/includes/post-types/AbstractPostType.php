<?php

namespace MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_post';

	/**
	 * @var string Like the post type, but without the prefix.
	 *
	 * @since 1.0
	 */
	protected $entityType;

	/**
	 * @since 1.0
	 */
	public function __construct() {
		$this->entityType = mpa_unprefix( static::POST_TYPE );

		$this->addActions();
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		add_action( 'init', array( $this, 'register' ), 5 );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract public function getLabel();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	abstract public function getSingularLabel();

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	abstract protected function getLabels();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getDescription() {
		return '';
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function register() {
		$texts = array(
			'label'           => $this->getLabel(),
			'labels'          => $this->getLabels(),
			'description'     => $this->getDescription(),
			'capability_type' => static::POST_TYPE,
			'capabilities'    => array( 'read' => 'read_' . static::POST_TYPE . 's' ),
			'map_meta_cap'    => true,
		);

		$args                         = array_merge( $texts, $this->registerArgs() );
		$args['register_meta_box_cb'] = array( $this, 'registerMetaboxes' );

		register_post_type( static::POST_TYPE, $args );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function registerMetaboxes() {
		/** @since 1.0 */
		do_action( "mpa_register_{$this->entityType}_metaboxes" );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	abstract protected function registerArgs();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getPostType() {
		return static::POST_TYPE;
	}
}
