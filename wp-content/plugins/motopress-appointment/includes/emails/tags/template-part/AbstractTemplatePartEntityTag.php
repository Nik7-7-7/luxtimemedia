<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart;

use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractTemplatePartEntityTag extends AbstractTemplatePartTag {

	/**
	 * @var InterfaceEntity
	 */
	protected $entity;

	public function __construct() {
		parent::__construct();

		$this->entity = $this->getEmptyEntity();
	}

	abstract protected function getEmptyEntity();

	public function isEntityFits( InterfaceEntity $entity ): bool {
		return is_a( $entity, get_class( $this->getEmptyEntity() ) );
	}

	public function getEntity(): InterfaceEntity {
		return $this->entity;
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

	/**
	 * @return InterfaceEntity[]
	 * @todo: make a more strict type for each 'template part entities' and make error handlers
	 *
	 */
	abstract protected function getTemplatePartEntities(): array;

	protected function replaceTemplatePartTags( InterfaceEntity $entity ): string {
		$templatePart = $this->getTemplatePart();

		foreach ( $this->tags as $name => $tag ) {
			$tag->setEntity( $entity );
			$templatePart = $tag->replaceTags( $templatePart );
		}

		return $templatePart;
	}

	public function replaceTags( string $emailContent ): string {
		if ( $this->getEmptyEntity() == $this->getEntity() ) {
			return $emailContent;
		}

		return parent::replaceTags( $emailContent );
	}

	public function getTagContent(): string {
		$tagContent = '';

		$entities = $this->getTemplatePartEntities();

		if ( ! count( $entities ) ) {
			return '';
		}

		foreach ( $entities as $entity ) {
			$tagContent .= $this->replaceTemplatePartTags( $entity );
		}

		return $tagContent;
	}
}
