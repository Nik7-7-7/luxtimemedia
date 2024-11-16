<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ShortcodeOrderMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'shortcode_order_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Posts Order', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		$orderOptions = array(
			'none'             => esc_html__( 'No order', 'motopress-appointment' ),
			'ID'               => esc_html__( 'Post ID', 'motopress-appointment' ),
			'author'           => esc_html__( 'Post author', 'motopress-appointment' ),
			'title'            => esc_html__( 'Post title', 'motopress-appointment' ),
			'name'             => esc_html__( 'Post name (post slug)', 'motopress-appointment' ),
			'date'             => esc_html__( 'Post date', 'motopress-appointment' ),
			'modified'         => esc_html__( 'Last modified date', 'motopress-appointment' ),
			'rand'             => esc_html__( 'Random order', 'motopress-appointment' ),
			'relevance'        => esc_html__( 'Relevance', 'motopress-appointment' ),
			'menu_order'       => esc_html__( 'Page order', 'motopress-appointment' ),
			'menu_order title' => esc_html__( 'Page order and post title', 'motopress-appointment' ),
		);

		if ( $this->getCurrentShortcodeName() == mpa_shortcodes()->servicesList()->getName() ) {
			$orderOptions['price'] = esc_html__( 'Price', 'motopress-appointment' );
		}

		return array(
			'orderby' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Order By', 'motopress-appointment' ),
				'description' => esc_html__( 'Sort retrieved posts.', 'motopress-appointment' ),
				'options'     => $orderOptions,
				'default'     => 'menu_order title',
				'size'        => 'regular',
			),
			'order'   => array(
				'type'        => 'radio',
				'label'       => esc_html__( 'Order', 'motopress-appointment' ),
				'description' => esc_html__( 'Designates the ASC - ascending or DESC - descending order of sorting.', 'motopress-appointment' ),
				'options'     => array(
					'ASC'  => esc_html__( 'ASC — from lowest to highest values (1, 2, 3)', 'motopress-appointment' ),
					'DESC' => esc_html__( 'DESC — from highest to lowest values (3, 2, 1)', 'motopress-appointment' ),
				),
				'default'     => 'ASC',
			),
		);
	}

	/**
	 * @param string $shortcodeName
	 * @return bool
	 *
	 * @since 1.2
	 */
	protected function isForShortcode( $shortcodeName ) {
		return mpa_str_ends_with( $shortcodeName, '_list' );
	}
}
