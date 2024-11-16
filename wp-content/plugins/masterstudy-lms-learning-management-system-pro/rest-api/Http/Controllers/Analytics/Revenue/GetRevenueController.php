<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue\RevenueSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\MembershipRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetRevenueController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$orders_provider = $this->get_checkout_provider()->get_provider( 'orders' );

		$revenue = ( new $orders_provider( $this->get_date_from(), $this->get_date_to() ) )->get_revenue();

		$revenue['memberships_count'] = ( new MembershipRepository( $this->get_date_from(), $this->get_date_to() ) )->get_count();

		return new WP_REST_Response(
			( new RevenueSerializer() )->toArray( $revenue ),
		);
	}
}
