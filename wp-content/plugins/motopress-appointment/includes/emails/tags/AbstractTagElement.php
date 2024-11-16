<?php

namespace MotoPress\Appointment\Emails\Tags;

abstract class AbstractTagElement implements InterfaceTag {

	/**
	 * @var bool
	 */
	protected $visibility = true;

	abstract public function getName(): string;

	abstract public function getDescription(): string;

	abstract public function replaceTags( string $emailContent ): string;

	public function isHidden(): bool {
		return ! $this->visibility;
	}

	public function hide() {
		$this->visibility = false;
	}

	public function show() {
		$this->visibility = true;
	}
}
