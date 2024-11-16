<?php

namespace MotoPress\Appointment\Emails\Tags;

use MotoPress\Appointment\Entities\InterfaceEntity;

interface InterfaceTags extends InterfaceTag {

	/**
	 * @return InterfaceTag[]
	 */
	public function getTags(): array;

	public function add( InterfaceTag $tag );

	public function remove( InterfaceTag $component );

	public function setEntity( InterfaceEntity $entity );
}
