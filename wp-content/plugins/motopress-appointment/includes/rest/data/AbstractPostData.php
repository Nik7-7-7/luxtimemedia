<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Rest\ApiHelper;

abstract class AbstractPostData extends AbstractData {

	/**
	 * @return \MotoPress\Appointment\Repositories\AbstractRepository
	 */
	abstract public static function getRepository();

	/**
	 * @param  int  $id
	 *
	 * @return static|null
	 */
	public static function findById( int $id ) {
		$entity = static::getRepository()->findById( $id );
		if ( is_null( $entity ) ) {
			return null;
		}
		return new static( $entity );
	}

	protected function getDateCreated() {
		return get_post_time( ApiHelper::DATETIME_FORMAT_ISO8601, false, $this->entity->getId() );
	}

	protected function getDateCreatedUtc() {
		return get_post_time( ApiHelper::DATETIME_FORMAT_ISO8601, true, $this->entity->getId() );
	}

	protected function getDateModified() {
		return get_post_modified_time( ApiHelper::DATETIME_FORMAT_ISO8601, false, $this->entity->getId() );
	}

	protected function getDateModifiedUtc() {
		return get_post_modified_time( ApiHelper::DATETIME_FORMAT_ISO8601, true, $this->entity->getId() );
	}
}
