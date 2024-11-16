<?php

namespace PixelYourSite\SuperPack;

use function PixelYourSite\isEddActive;
use function PixelYourSite\isWooCommerceActive;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SpPostTypesCondition extends SpCondition {

	public function register_sub_conditions() {
		$post_types = get_public_post_types();
		// Product form WooCommerce and EDD are handled separately.
		if ( isWooCommerceActive() ) {
			unset( $post_types[ 'product' ] );
		}
		if ( isEddActive() ) {
			unset( $post_types[ 'download' ] );
		}

		foreach ( $post_types as $slug => $post_type ) {
			$option = new SpPostTypeSingleCondition( [
				'post_type' => $slug,
			] );

			$this->options[] = $option;
			SpPixelCondition()->registerOption( $option );
		}
	}

	public function get_label() {
		return 'Post types';
	}

	public function get_name() {
		return 'post_types';
	}

	public function get_all_label() {
		return 'All Post types';
	}

	public function get_controls() {
		$options = array();

		foreach ( $this->options as $option ) {
			$options[] = array(
				'title' => $option->get_label(),
				'item'  => $option->get_name()
			);
		}

		return [
			'type'    => 'select_titled_array',
			'name'    => 'options',
			'options' => $options,

		];
	}

	public function check( $args ) {
		return true;
	}
}