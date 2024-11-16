<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce;

use MasterStudy\Lms\Pro\RestApi\Interfaces\OrderInterface;
use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\OrderAbstractRepository;

final class OrderRepository extends OrderAbstractRepository implements OrderInterface {
	public function get_orders( $args = array() ) {
		$query = new \WC_Order_Query(
			array(
				'limit'        => -1,
				'type'         => 'shop_order',
				'status'       => 'completed',
				'date_created' => $this->date_from . '...' . $this->date_to,
				'order'        => 'ASC',
			)
		);

		return $query->get_orders();
	}

	public function get_instructor_orders( $instructor_course_ids, $user_id = null ) {
		if ( empty( $instructor_course_ids ) ) {
			return array();
		}

		$courses_in_clause = implode( ',', array_map( 'intval', $instructor_course_ids ) );
		$object_ids        = $this->db->get_col(
			$this->db->prepare(
				"SELECT p.ID FROM {$this->db->posts} p
				INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
				WHERE pm.meta_key = %s AND pm.meta_value IN ($courses_in_clause)",
				'stm_lms_product_id'
			)
		);

		if ( empty( $object_ids ) ) {
			return array();
		}

		$in_clause = implode( ',', array_map( 'intval', $object_ids ) );
		$order_ids = $this->db->get_col(
			$this->db->prepare(
				"SELECT order_id FROM {$this->db->prefix}stm_lms_order_items oi WHERE oi.object_id IN ($in_clause)"
			)
		);

		if ( empty( $order_ids ) ) {
			return array();
		}

		// TODO - Replace get_orders() with get_orders_by_ids()
		$orders    = $this->get_orders();
		$order_ids = array_map( 'intval', $order_ids );

		return array_filter(
			$orders,
			function( $order ) use ( $order_ids, $user_id ) {
				$checked = in_array( $order->get_id(), $order_ids, true );

				if ( $this->is_current_user_instructor() && ! empty( $user_id ) ) {
					$checked = $checked && $order->get_customer_id() === $user_id;
				}

				return $checked;
			}
		);
	}

	public function get_student_orders( $user_id ) {
		// TODO - Replace get_orders() with get_orders_by_author()
		$orders = $this->get_orders();

		return array_filter(
			$orders,
			function( $order ) use ( $user_id ) {
				return $order->get_customer_id() === $user_id;
			}
		);
	}

	public function get_order_total( $order ): float {
		return floatval( $order->get_total() );
	}

	public function get_order_customer_id( $order ) {
		return $order->get_customer_id();
	}

	public function get_order_date( $order ) {
		return $order->get_date_created()->date( 'Y-m-d H:i:s' );
	}

	public function get_order_items( $order ) {
		$filtered_items = array();

		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item->get_product_id() );

			if ( $product && 'stm_lms_product' === $product->get_type() ) {
				// Condition for Single Course Analytics
				if ( ! empty( $this->course_id ) ) {
					$lms_product_id = $this->get_item_course_id( $item );

					// TODO - Remove after fixing WooCommerce Group Purchases issue
					if ( $this->is_group_item( $item ) ) {
						$lms_product_id = $this->get_item_group_id( $item );
					}

					if ( $this->is_bundle_item( $item, $lms_product_id ) ) {
						$bundle_item_ids = $this->get_bundle_course_ids( $lms_product_id );
						if ( ! in_array( $this->course_id, array_map( 'intval', $bundle_item_ids ), true ) ) {
							continue;
						}
					} elseif ( $lms_product_id !== $this->course_id ) {
						continue;
					}
				}

				$filtered_items[] = $item;
			}
		}

		return $filtered_items;
	}

	public function get_item_total( $item ): float {
		return floatval( $item->get_total() );
	}

	public function is_bundle_item( $item, $source_id = null ): bool {
		if ( ! $source_id ) {
			$source_id = $this->get_item_course_id( $item );
		}

		return ! empty( $source_id ) && 'stm-course-bundles' === get_post_type( $source_id );
	}

	public function is_group_item( $item ): bool {
		return ! empty( $this->get_item_group_id( $item ) );
	}

	public function get_item_group_id( $item ): int {
		return (int) get_post_meta( $item->get_product_id(), 'stm_lms_enterprise_id', true );
	}

	public function is_preorder_item( $item, $order_date ): bool {
		$lms_course_id = $this->get_item_course_id( $item );

		// TODO - Remove after fixing WooCommerce Group Purchases issue
		if ( empty( $lms_course_id ) ) {
			$lms_course_id = $this->get_item_group_id( $item );
		}

		$coming_soon_date = get_post_meta( $lms_course_id, 'coming_soon_date', true );

		if ( ! empty( $coming_soon_date ) ) {
			return intval( $coming_soon_date ) > strtotime( $order_date );
		}

		return false;
	}

	public function get_item_course_id( $item ): int {
		return (int) get_post_meta( $item->get_product_id(), 'stm_lms_product_id', true );
	}

	public function format_order_date( $order, $date_format ) {
		return $order->get_date_created()->format( $date_format );
	}

	public function get_user_lastest_order( $user_id, $order_date ) {
		return wc_get_orders(
			array(
				'customer_id'  => $user_id,
				'limit'        => 1,
				'status'       => 'completed',
				'date_created' => '<' . $order_date,
			)
		);
	}
}
