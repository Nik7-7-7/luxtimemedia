<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Order;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class OrderSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'orders'       => array_map(
				function( $order ) {
					return array(
						'id'             => $order['id'] ?? null,
						'user_id'        => $order['user_id'] ?? null,
						'status'         => $order['status'] ?? null,
						'status_name'    => $order['status_name'] ?? null,
						'order_key'      => $order['order_key'] ?? null,
						'payment_code'   => $order['payment_code'] ?? null,
						'date_formatted' => $order['date_formatted'] ?? null,
						'total'          => $order['total'] ?? null,
						'billing'        => array(
							'first_name' => $order['billing']['first_name'] ?? '',
							'last_name'  => $order['billing']['last_name'] ?? '',
							'address_1'  => $order['billing']['address_1'] ?? '',
							'city'       => $order['billing']['city'] ?? '',
							'postcode'   => $order['billing']['postcode'] ?? '',
							'country'    => $order['billing']['country'] ?? '',
							'email'      => $order['billing']['email'] ?? '',
							'phone'      => $order['billing']['phone'] ?? '',
						),
						'i18n'           => $order['i18n'] ?? array(),
						'items'          => array_map(
							function( $item ) {
								return array(
									'item_id'       => $item['item_id'] ?? null,
									'price'         => $item['price'] ?? null,
									'quantity'      => $item['quantity'] ?? null,
									'enterprise_id' => $item['enterprise_id'] ?? null,
									'bundle_id'     => 908,
								);
							},
							$order['items'] ?? array()
						),
						'cart_items'     => array_map(
							function( $cart_item ) {
								return array(
									'bundle_courses_count' => $cart_item['bundle_courses_count'] ?? null,
									'enterprise_name'      => $cart_item['enterprise_name'] ?? '',
									'title'                => $cart_item['title'] ?? '',
									'link'                 => $cart_item['link'] ?? '',
									'price_formatted'      => $cart_item['price_formatted'] ?? '',
									'image'                => $cart_item['image'] ?? '',
									'placeholder'          => $cart_item['placeholder'] ?? '',
									'terms'                => is_array( $cart_item['terms'] ) ? $cart_item['terms'] : array(),
								);
							},
							$order['cart_items']
						),
						'user'           => array(
							'id'     => $order['user']['id'] ?? null,
							'login'  => $order['user']['login'] ?? '',
							'email'  => $order['user']['email'] ?? '',
							'avatar' => $order['user']['avatar_url'] ?? '',
						),
					);
				},
				$data['orders'] ?? array()
			),
			'total'        => $data['total'] ?? null,
			'total_orders' => $data['total_orders'] ?? null,
			'pages'        => $data['pages'] ?? null,
			'current_page' => $data['current_page'] ?? null,
			'i18n'         => $data['i18n'] ?? array(),
		);
	}
}
