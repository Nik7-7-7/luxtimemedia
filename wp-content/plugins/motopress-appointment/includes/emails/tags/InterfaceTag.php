<?php

namespace MotoPress\Appointment\Emails\Tags;

interface InterfaceTag {

	public function getName(): string;

	public function getDescription(): string;

	public function replaceTags( string $emailContent ): string;
}
