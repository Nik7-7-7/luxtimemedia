<?php

namespace MotoPress\Appointment\Emails\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Tags extends TagsGroup implements InterfaceTags {

	public function __construct() {
		parent::__construct( 'tags', '' );
	}

	public function getDescription(): string {
		return '<p class="description">' . parent::getDescription() . '</p>';
	}
}
