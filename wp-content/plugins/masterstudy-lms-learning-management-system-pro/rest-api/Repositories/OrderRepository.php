<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

final class OrderRepository {
	public function get_all( array $request = array() ) : array {
		$user     = get_current_user_id();
		$per_page = $request['per_page'] ?? 10;
		$page     = $request['current_page'] ?? 1;
		$offset   = ( $page - 1 ) * $per_page;

		$user_orders = apply_filters( 'stm_lms_user_orders', array(), $user, $per_page, $offset );

		$posts = $user_orders['posts'] ?? array();
		$total = $user_orders['pages'] ?? 0;

		return array(
			'success'      => true,
			'orders'       => $posts,
			'pages'        => (int) ceil( $total / $per_page ),
			'current_page' => (int) $page,
			'total_orders' => (int) $total,
			'total'        => ( $total <= $offset + $per_page ),
		);
	}
}
