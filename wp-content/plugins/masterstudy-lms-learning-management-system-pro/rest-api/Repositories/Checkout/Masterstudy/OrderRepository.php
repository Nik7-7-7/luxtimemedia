<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\RestApi\Interfaces\OrderInterface;
use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\OrderAbstractRepository;

final class OrderRepository extends OrderAbstractRepository implements OrderInterface {
	public function get_orders( $args = array() ) {
		$default_args = array(
			'post_type'      => PostType::ORDER,
			'posts_per_page' => -1,
			'date_query'     => array(
				'after'  => $this->date_from,
				'before' => $this->date_to,
			),
			'meta_query'     => array(
				array(
					'key'     => 'status',
					'value'   => 'completed',
					'compare' => '=',
				),
			),
			'order'          => 'ASC',
		);

		$query = new \WP_Query(
			wp_parse_args( $args, $default_args )
		);

		return $query->posts;
	}

	public function get_instructor_orders( $instructor_course_ids, $student_id = null ) {
		if ( empty( $instructor_course_ids ) ) {
			return array();
		}

		$in_clause   = implode( ',', array_map( 'intval', $instructor_course_ids ) );
		$extra_query = '';

		if ( $this->is_current_user_instructor() && ! empty( $student_id ) ) {
			$extra_query = $this->db->prepare( 'AND p.post_author = %d', $student_id );
		}

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT p.ID, p.post_date
				FROM {$this->db->prefix}stm_lms_order_items oi
				LEFT JOIN {$this->db->posts} p ON p.ID = oi.order_id
				LEFT JOIN {$this->db->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'status'
				WHERE oi.object_id IN ($in_clause) AND p.post_type = %s AND p.post_date BETWEEN %s AND %s AND pm.meta_value = %s $extra_query GROUP BY p.ID",
				PostType::ORDER,
				$this->date_from,
				$this->date_to,
				'completed'
			)
		);
	}

	public function get_student_orders( $user_id ) {
		return $this->get_orders( array( 'author' => $user_id ) );
	}

	public function get_order_total( $order ): float {
		return floatval( get_post_meta( $order->ID, '_order_total', true ) );
	}

	public function get_order_customer_id( $order ) {
		return $order->post_author ?? null;
	}

	public function get_order_date( $order ) {
		return $order->post_date;
	}

	public function get_order_items( $order ) {
		$order_items    = get_post_meta( $order->ID, 'items', true );
		$filtered_items = array();

		foreach ( $order_items as $item ) {
			// Condition for Single Course Analytics
			if ( ! empty( $this->course_id ) ) {
				$item_id = (int) $item['item_id'];

				if ( $this->is_bundle_item( $item ) ) {
					$bundle_item_ids = $this->get_bundle_course_ids( $item_id );
					if ( ! in_array( $this->course_id, array_map( 'intval', $bundle_item_ids ), true ) ) {
						continue;
					}
				} elseif ( $item_id !== $this->course_id ) {
					continue;
				}
			}

			$filtered_items[] = $item;
		}

		return $filtered_items;
	}

	public function get_item_total( $item ): float {
		return floatval( $item['price'] );
	}

	public function is_bundle_item( $item, $source_id = null ): bool {
		return ! empty( $item['bundle'] );
	}

	public function is_group_item( $item ): bool {
		return ! empty( $item['enterprise'] );
	}

	public function get_item_group_id( $item ): int {
		return intval( $item['enterprise'] ?? 0 );
	}

	public function is_preorder_item( $item, $order_date ): bool {
		$coming_soon_date = get_post_meta( $item['item_id'], 'coming_soon_date', true );

		if ( ! empty( $coming_soon_date ) ) {
			return intval( $coming_soon_date ) > strtotime( $order_date );
		}

		return false;
	}

	public function get_item_course_id( $item ): int {
		return (int) $item['item_id'];
	}

	public function format_order_date( $order, $date_format ) {
		return gmdate( $date_format, strtotime( $order->post_date ) );
	}

	public function get_user_lastest_order( $user_id, $order_date ) {
		return $this->db->get_var( $this->db->prepare( "SELECT COUNT(1) FROM {$this->db->posts} WHERE post_type = %s AND post_author = %d AND post_date < %s", PostType::ORDER, $user_id, $order_date ) );
	}
}
