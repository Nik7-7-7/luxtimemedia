<?php

namespace MotoPress\Appointment\DirectLinkActions;

use MotoPress\Appointment\Entities\InterfaceUniqueEntity;
use MotoPress\Appointment\Repositories\AbstractRepository;

/**
 * @since 1.15.0
 */
abstract class AbstractAction {

	public function __construct() {
		add_action( 'init', array( $this, 'initAction' ) );
	}

	/**
	 * @return AbstractRepository
	 */
	abstract protected function getEntityRepository();

	/**
	 * @return string
	 */
	abstract protected function getActionName();

	/**
	 * @param InterfaceUniqueEntity $entity
	 *
	 * @return void
	 */
	abstract protected function action( InterfaceUniqueEntity $entity );

	public function initAction() {
		if ( ! $this->isCurrentActionRequest() ) {
			return;
		}

		$entity = $this->getEntity();

		if ( $entity !== null ) {
			$this->action( $entity );
		}
	}

	/**
	 * @return bool
	 */
	protected function isCurrentActionRequest() {
		if ( empty( $_GET['mpa_action'] ) ||
			empty( $_GET['id'] ) ||
			empty( $_GET['uid'] ) ||
			empty( $_GET['token'] )
		) {
			return false;
		}

		return true;
	}

	protected function isValidToken() {
		if ( ! $this->isCurrentActionRequest() ) {
			return false;
		}

		$id    = absint( $_GET['id'] );
		$uid   = sanitize_text_field( $_GET['uid'] );
		$token = sanitize_text_field( $_GET['token'] );

		return $this->generateToken( $id, $uid ) === $token;
	}

	/**
	 * @return \MotoPress\Appointment\Entities\AbstractEntity|null
	 */
	protected function getEntity() {
		if ( ! $this->isValidToken() ) {
			return null;
		}

		$id = absint( $_GET['id'] );

		return $this->getEntityRepository()->findById( $id );
	}

	protected function generateToken( $id, $uid ) {
		$args = array(
			'mpa_action' => $this->getActionName(),
			'id'         => $id,
			'uid'        => $uid,
		);

		$query = build_query( $args );

		return wp_hash( $query );
	}


	/**
	 * @return string
	 */
	public function getActionURL( InterfaceUniqueEntity $uniqueEntity ) {
		$id   = $uniqueEntity->getId();
		$uid  = $uniqueEntity->getUid();
		$args = array(
			'mpa_action' => $this->getActionName(),
			'id'         => $id,
			'uid'        => $uid,
			'token'      => $this->generateToken( $id, $uid ),
		);

		return add_query_arg( $args, site_url() );
	}
}
