<?php

namespace MasterStudy\Lms\Pro\RestApi\Interfaces;

interface OrderInterface {
	public function get_orders( $args = array() );

	public function get_instructor_orders( $instructor_course_ids );

	public function get_student_orders( $user_id );

	public function get_order_total( $order ): float;

	public function get_order_customer_id( $order );

	public function get_order_date( $order );

	public function get_order_items( $order );

	public function get_item_total( $item ): float;

	public function is_bundle_item( $item, $source_id = null ): bool;

	public function is_group_item( $item ): bool;

	public function get_item_group_id( $item ): int;

	public function is_preorder_item( $item, $order_date ): bool;

	public function get_item_course_id( $item ): int;

	public function format_order_date( $order, $date_format );

	public function get_user_lastest_order( $user_id, $order_date );
}
