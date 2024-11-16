<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\BundleAbstractRepository;

class BundleRepository extends BundleAbstractRepository {
	public function get_query() {
		// Get orders by bundle ids
		$this->join[] = "LEFT JOIN (
		    SELECT object_id AS course_id, SUM(quantity * price) AS total_revenue, COUNT(order_id) as counts
		    FROM {$this->table_orders} oi
		    LEFT JOIN {$this->db->postmeta} om ON oi.order_id = om.post_id AND om.meta_key = 'date'
		    JOIN {$this->db->postmeta} m ON oi.order_id = m.post_id AND m.meta_key = 'status' AND m.meta_value = 'completed'
			WHERE om.meta_value BETWEEN '{$this->get_timestamp($this->date_from)}' AND '{$this->get_timestamp($this->date_to)}'
		    GROUP BY object_id
		) AS orders ON orders.course_id = bundle_data.bundle_id";
	}
}
