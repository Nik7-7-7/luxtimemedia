<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Orders;

use WP_REST_Request;
use WP_REST_Response;
use MasterStudy\Lms\Validation\Validator;
use MasterStudy\Lms\Pro\RestApi\Repositories\OrderRepository;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Order\OrderSerializer;

final class GetOrdersController {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validator = new Validator(
			$request->get_params(),
			array(
				'per_page'     => 'nullable|integer',
				'current_page' => 'nullable|integer',
			)
		);

		if ( $validator->fails() ) {
			return new WP_REST_Response(
				array(
					'status' => 'validation_failed',
					'errors' => $validator->get_errors_array(),
				),
				400
			);
		}

		$validated_data = $validator->get_validated();

		$orders_repository = new OrderRepository();
		$orders_data       = $orders_repository->get_all( $validated_data );

		return new WP_REST_Response(
			( new OrderSerializer() )->toArray( $orders_data )
		);
	}
}
