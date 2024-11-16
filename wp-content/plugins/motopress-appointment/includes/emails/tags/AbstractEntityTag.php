<?php

namespace MotoPress\Appointment\Emails\Tags;

use MotoPress\Appointment\Entities\InterfaceEntity;

abstract class AbstractEntityTag extends AbstractTag {
	/**
	 * @var InterfaceEntity
	 */
	protected $entity;

	public function __construct() {
		$this->entity = $this->getEmptyEntity();
	}

	abstract protected function getEmptyEntity(): InterfaceEntity;

	public function isEntityFits( InterfaceEntity $entity ): bool {
		return is_a( $entity, get_class( $this->getEmptyEntity() ) );
	}

	/**
	 * @throws \Exception
	 */
	public function setEntity( InterfaceEntity $entity ) {

		if ( ! $this->isEntityFits( $entity ) ) {

			throw new \Exception(
				sprintf(
					'Argument #1 ($entity) must be of type %s',
					get_class( $this->getEmptyEntity() )
				)
			);
		}

		$this->entity = $entity;
	}

	public function getEntity(): InterfaceEntity {
		return $this->entity;
	}

	public function replaceTags( string $emailContent ): string {

		if ( $this->getEmptyEntity() == $this->getEntity() ) {

			return $emailContent;
		}

		return parent::replaceTags( $emailContent );
	}
}
