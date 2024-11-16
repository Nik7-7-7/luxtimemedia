<?php

namespace MotoPress\Appointment\Emails\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class EmptyContentTag extends AbstractTag {

	protected $tag;

	public function __construct( InterfaceTag $tag ) {
		$this->tag = $tag;
		$this->hide();
	}

	public function getName(): string {
		return $this->tag->getName();
	}

	protected function description(): string {
		return $this->tag->getDescription();
	}

	public function getTagContent(): string {
		return '';
	}
}
