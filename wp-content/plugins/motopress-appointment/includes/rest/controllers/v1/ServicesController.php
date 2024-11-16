<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Controllers\V1;

use MotoPress\Appointment\Rest\Controllers\AbstractRestObjectController;
use MotoPress\Appointment\Rest\Data\ServiceData;
use WP_REST_Request;

class ServicesController extends AbstractRestObjectController {


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
	protected $rest_base = 'services';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mpa_service';


	/**
	 * Prepare links for the request.
	 *
	 * @param  ServiceData  $serviceData  Service data object.
	 * @param  WP_REST_Request  $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $serviceData, $request ) {
		$links = parent::prepare_links( $serviceData, $request );

		$employees = $serviceData->employees;
		if ( count( $employees ) ) {
			foreach ( $employees as $employee ) {
				$links['employees'][] = array(
					'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'employees', $employee ) ),
					'embeddable' => true,
				);
			}
		}

		return $links;
	}

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
			'enum'        => array( 'id', 'title', 'price', 'duration' ),
			'default'     => 'title',
		);

		return $params;
	}

	/**
	 * Prepare objects query.
	 *
	 * @param  WP_REST_Request  $request  Full details about the request.
	 *
	 * @return array
	 */
	public function prepareQuery( $request ) {
		$args = parent::prepareQuery( $request );

		if ( ! empty( $request['orderby'] ) ) {
			switch ( $request['orderby'] ) {
				case 'id':
				case 'title':
					$args['orderby'] = $request['orderby'];
					break;
				case 'price':
					$args['meta_key'] = '_mpa_price';
					$args['orderby']  = 'meta_value_num';
					break;
				case 'duration':
					$args['meta_key'] = '_mpa_duration';
					$args['orderby']  = 'meta_value_num';
					break;
			}
		}

		return $args;
	}
}
