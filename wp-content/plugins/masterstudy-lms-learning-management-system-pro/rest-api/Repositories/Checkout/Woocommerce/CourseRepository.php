<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\WooCommerce;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\CourseAbstractRepository;

class CourseRepository extends CourseAbstractRepository {

	public function get_revenue_query(): string {

		return $this->db->prepare(
			"SELECT
                pm.post_id AS course_id,
                SUM(oi.quantity * oi.price) AS total_revenue
            FROM (
                SELECT DISTINCT oi.order_id, oi.object_id, oi.quantity, oi.price
                FROM {$this->db->prefix}stm_lms_order_items oi
            ) AS oi
            LEFT JOIN {$this->db->postmeta} pm ON pm.meta_value = oi.object_id
                AND pm.meta_key = 'stm_lms_product_id'
            WHERE pm.meta_value IS NOT NULL
            AND EXISTS (
                SELECT 1 
                FROM {$this->db->postmeta} pm_courses 
                WHERE pm_courses.post_id = oi.order_id 
                AND pm_courses.meta_key = 'stm_lms_courses'
            )
            AND EXISTS (
                SELECT 1 
                FROM {$this->db->prefix}wc_orders oi2
                WHERE oi2.id = oi.order_id
                AND oi2.status = 'wc-completed'
            )
            GROUP BY pm.post_id"
		);
	}
}

