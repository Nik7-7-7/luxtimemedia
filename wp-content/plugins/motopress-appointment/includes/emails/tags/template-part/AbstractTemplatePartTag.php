<?php

namespace MotoPress\Appointment\Emails\Tags\TemplatePart;

use MotoPress\Appointment\Emails\Tags\TagsGroup;
use MotoPress\Appointment\Emails\Tags\InterfaceTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
abstract class AbstractTemplatePartTag extends TagsGroup {

	protected $templatePart = '';

	public function __construct() {
		parent::__construct( $this->getName(), $this->description() );

		$this->initTemplatePartTags();
	}

	abstract protected function getTagContent(): string;

	abstract protected function description(): string;

	abstract protected function getTemplatePartTemplate(): string;

	abstract protected function getTemplatePartTemplateTags(): InterfaceTags;

	protected function initTemplatePartTags() {
		$subTags = $this->getTemplatePartTemplateTags();

		$this->add( $subTags );
	}

	protected function getTemplatePart(): string {
		if ( ! $this->templatePart ) {
			$this->templatePart = $this->getTemplatePartTemplate();
		}

		return $this->templatePart;
	}

	protected function getTag(): string {
		return '{' . $this->getName() . '}';
	}

	public function getDescription(): string {
		return $this->description() . ' - <code>' . $this->getTag() . '</code>';
	}

	public function replaceTags( string $emailContent ): string {
		return str_ireplace( $this->getTag(), $this->getTagContent(), $emailContent );
	}
}
