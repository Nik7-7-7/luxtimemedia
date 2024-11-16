<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Engagement;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Engagement\EngagementSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\EngagementRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetEngagementController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$engagement = ( new EngagementRepository( $this->get_date_from(), $this->get_date_to() ) )->get_all_data();

		return new WP_REST_Response(
			( new EngagementSerializer() )->toArray( $engagement )
		);
	}
}
