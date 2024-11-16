<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics;

use MasterStudy\Lms\Pro\RestApi\Http\Serializers\UsersSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\UserRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetUsersController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$users = ( new UserRepository( $this->get_date_from(), $this->get_date_to() ) )->get_users_data();

		return new WP_REST_Response(
			( new UsersSerializer() )->toArray( $users )
		);
	}
}
