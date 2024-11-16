<?php

namespace PixelYourSite\SuperPack;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SpPostTypeSingleCondition extends SpCondition {

	private $post_type;

	public function __construct( $data ) {
		$this->post_type = get_post_type_object( $data[ 'post_type' ] );
		parent::__construct();
	}

	public function register_sub_conditions() {

	}

	public function get_label() {
		return $this->post_type->label;
	}

	public function get_name() {
		return sprintf( 'post_type_%s', $this->post_type->name );
	}

	public function get_post_type() {
		return $this->post_type->name;
	}

	public function get_all_label() {
		return 'All';
	}

	public function check( $args = array() ) {
		return is_singular( $this->get_post_type() );
	}
}