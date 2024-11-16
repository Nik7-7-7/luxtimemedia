<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
abstract class AbstractTermsListShortcode extends AbstractPostShortcode {

	/**
	 * @return array
	 *
	 * @since 1.3
	 */
	public function getAttributes() {
		// See https://developer.wordpress.org/reference/classes/wp_term_query/
		$termsListAtts = array(
			'number'        => array(
				'type'          => 'integer',
				'description'   => esc_html__( 'Maximum number of categories to show.', 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => esc_html__( 'all categories', 'motopress-appointment' ),
			),
			'columns_count' => array(
				'type'        => 'integer',
				'description' => esc_html__( 'The number of columns in the grid.', 'motopress-appointment' ),
				'default'     => 3,
			),
			'orderby'       => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Sort retrieved terms.', 'motopress-appointment' ),
				'default'       => 'name',
				'default_label' => esc_html__( 'name', 'motopress-appointment' ),
			),
			'order'         => array(
				'type'          => 'order',
				'description'   => esc_html__( 'Designates the ASC - ascending or DESC - descending order of sorting.', 'motopress-appointment' ),
				'default'       => 'ASC',
				'default_label' => esc_html__( 'ASC', 'motopress-appointment' ),
			),
		);

		return $termsListAtts + parent::getAttributes();
	}
}
