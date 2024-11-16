<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
abstract class AbstractPostsListShortcode extends AbstractPostShortcode {

	/**
	 * @return array
	 *
	 * @since 1.3
	 */
	public function getAttributes() {
		$postListAtts = array(
			'posts_per_page' => array(
				'type'          => 'integer',
				'description'   => esc_html__( 'Posts per page.', 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => esc_html__( 'The number of posts set in the Settings > Reading', 'motopress-appointment' ),
			),
			'columns_count'  => array(
				'type'        => 'integer',
				'description' => esc_html__( 'The number of columns in the grid.', 'motopress-appointment' ),
				'default'     => 3,
			),
			'orderby'        => array(
				'type'          => 'string',
				'description'   => mpa_kses_link( __( 'Order of posts. <a href="https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters" target="_blank">Learn more about order parameters.</a>', 'motopress-appointment' ) ),
				'default'       => 'menu_order title',
				'default_label' => esc_html__( 'Page order with a fallback to a post title', 'motopress-appointment' ),
			),
			'order'          => array(
				'type'          => 'order',
				'description'   => esc_html__( 'Designates the ASC - ascending or DESC - descending order of sorting.', 'motopress-appointment' ),
				'default'       => 'ASC',
				'default_label' => esc_html__( 'ASC', 'motopress-appointment' ),
			),
		);

		return $postListAtts + parent::getAttributes();
	}
}
