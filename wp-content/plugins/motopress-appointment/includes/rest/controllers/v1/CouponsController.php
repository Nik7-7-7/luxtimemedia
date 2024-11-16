<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.21.0
 */

namespace MotoPress\Appointment\Rest\Controllers\V1;

use MotoPress\Appointment\Rest\Controllers\AbstractRestObjectController;
use WP_REST_Request;

class CouponsController extends AbstractRestObjectController {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mpa/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'coupons';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mpa_coupon';

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['orderby'] = array(
			'description' => 'Order sort attribute ascending or descending.',
			'type'        => 'string',
			'enum'        => array( 'id', 'name' ),
			'default'     => 'name',
		);

		return $params;
	}

	/**
	 * Prepare objects query.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	public function prepareQuery( $request ) {
		$args = parent::prepareQuery( $request );

		if ( ! empty( $request['orderby'] ) ) {
			switch ( $request['orderby'] ) {
				case 'id':
					$args['orderby'] = $request['orderby'];
					break;
				case 'name':
					$args['orderby'] = 'title';
					break;
			}
		}

		return $args;
	}
}