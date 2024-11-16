<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Middleware;

use MasterStudy\Lms\Routing\MiddlewareInterface;

class Administrator implements MiddlewareInterface {
	public function process( $request, callable $next ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_REST_Response(
				array(
					'error_code' => 'forbidden_access',
					'message'    => esc_html__( 'Only Administrators can access this route!', 'masterstudy-lms-learning-management-system' ),
				),
				403
			);
		}

		return $next( $request );
	}
}
