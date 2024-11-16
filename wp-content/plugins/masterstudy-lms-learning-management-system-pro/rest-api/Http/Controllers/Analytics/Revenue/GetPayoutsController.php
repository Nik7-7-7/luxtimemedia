<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue\PayoutsSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\PayoutRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetPayoutsController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$payouts = ( new PayoutRepository( $this->get_date_from(), $this->get_date_to() ) )->get_payouts();

		return new WP_REST_Response(
			( new PayoutsSerializer() )->toArray( $payouts )
		);
	}
}
