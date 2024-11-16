<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ShortcodeTermsOrderMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'shortcode_terms_order_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Terms Order', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		$orderOptions = array(
			'none'       => esc_html__( 'No order', 'motopress-appointment' ),
			'name'       => esc_html__( 'Term name', 'motopress-appointment' ),
			'slug'       => esc_html__( 'Term slug', 'motopress-appointment' ),
			'term_id'    => esc_html__( 'Term ID', 'motopress-appointment' ),
			'parent'     => esc_html__( 'Parent ID', 'motopress-appointment' ),
			'count'      => esc_html__( 'Number of associated objects', 'motopress-appointment' ),
			'include'    => esc_html__( 'Keep the order of "IDs" parameter', 'motopress-appointment' ),
			'term_order' => esc_html__( 'Term order', 'motopress-appointment' ),
		);

		return array(
			'orderby' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Order By', 'motopress-appointment' ),
				'description' => esc_html__( 'Sort retrieved terms.', 'motopress-appointment' ),
				'options'     => $orderOptions,
				'default'     => 'name',
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
		return mpa_str_ends_with( $shortcodeName, '_categories' )
			|| mpa_str_ends_with( $shortcodeName, '_tags' );
	}
}
