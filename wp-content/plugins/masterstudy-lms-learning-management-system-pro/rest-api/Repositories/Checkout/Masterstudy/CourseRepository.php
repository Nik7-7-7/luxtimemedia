<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\MasterStudy;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\CourseAbstractRepository;

class CourseRepository extends CourseAbstractRepository {

	public function get_revenue_query(): string {
		$date_from = $this->get_timestamp( $this->date_from );
		$date_to   = $this->get_timestamp( $this->date_to );

		return "SELECT
            oi.object_id AS course_id,
            SUM(oi.quantity * oi.price) AS total_revenue
        FROM {$this->db->prefix}stm_lms_order_items oi
        INNER JOIN {$this->db->postmeta} om ON oi.order_id = om.post_id
            AND om.meta_key = 'status' AND om.meta_value = 'completed'
        WHERE oi.order_id IN (
            SELECT post_id 
            FROM {$this->db->postmeta} 
            WHERE meta_key = 'date'
            AND meta_value BETWEEN '$date_from' AND '$date_to'
        )
        GROUP BY oi.object_id";
	}
}

