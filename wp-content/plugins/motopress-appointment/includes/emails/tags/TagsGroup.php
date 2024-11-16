<?php

namespace MotoPress\Appointment\Emails\Tags;

use MotoPress\Appointment\Entities\InterfaceEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TagsGroup extends AbstractTagElement implements InterfaceTags {
	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var AbstractTagElement[]
	 */
	protected $tags = array();

	public function __construct( string $name, string $description ) {
		$this->name        = $name;
		$this->description = $description;
	}

	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return InterfaceTag[]
	 */
	public function getTags(): array {
		return $this->tags;
	}

	public function getDescription(): string {
		$description = '';

		if ( $this->isHidden() ) {
			return $description;
		}

		if ( $this->description ) {
			$description .= '<b>' . $this->description . '</b>:<br>';
		}

		foreach ( $this->tags as $tag ) {
			if ( $tag->isHidden() ) {
				continue;
			}

			$description .= $tag->getDescription() . '<br>';
		}

		return $description;
	}

	public function add( InterfaceTag $tag ) {
		$name                = $tag->getName();
		$this->tags[ $name ] = $tag;

		return $this;
	}

	public function remove( InterfaceTag $component ) {

		$this->tags = array_filter(
			$this->tags,
			function ( $child ) use ( $component ) {
				return $child != $component;
			}
		);

		return $this;
	}

	public function setEntity( InterfaceEntity $entity ) {

		foreach ( $this->tags as $name => $tag ) {

			if ( method_exists( $tag, 'setEntity' ) &&
				( ! method_exists( $tag, 'isEntityFits' ) || $tag->isEntityFits( $entity ) )
			) {
				try {

					$tag->setEntity( $entity );

				} catch ( \Exception $e ) {
					// suppress exceptions for smooth email sending
				}
			}
		}
	}

	public function replaceTags( string $emailContent ): string {
		foreach ( $this->tags as $name => $tag ) {
			$emailContent = $tag->replaceTags( $emailContent );
		}

		return $emailContent;
	}
}
