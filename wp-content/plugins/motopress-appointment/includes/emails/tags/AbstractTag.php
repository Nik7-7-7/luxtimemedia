<?php

namespace MotoPress\Appointment\Emails\Tags;

abstract class AbstractTag extends AbstractTagElement {

	protected $deprecated = false;

	abstract protected function getTagContent(): string;

	abstract protected function description(): string;

	protected function getTag(): string {
		return '{' . $this->getName() . '}';
	}

	public function isDeprecated(): bool {
		return $this->deprecated;
	}

	public function setDeprecated( bool $isDeprecated ) {
		$this->deprecated = $isDeprecated;
	}

	public function getDescription(): string {
		$description = $this->description() . ' - <code>' . $this->getTag() . '</code>';

		if ( $this->isDeprecated() ) {
			$description = '<span class="mpa-deprecated">'
						. '<strong>' . esc_html__( 'Deprecated.', 'motopress-appointment' ) . '</strong>'
						. ' '
						. $description
						. '</span>';
		}

		return $description;
	}

	public function replaceTags( string $emailContent ): string {
		return str_ireplace( $this->getTag(), $this->getTagContent(), $emailContent );
	}
}
