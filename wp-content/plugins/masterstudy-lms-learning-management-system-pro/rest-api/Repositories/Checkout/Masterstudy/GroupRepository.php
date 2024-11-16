<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\GroupAbstractRepository;

class GroupRepository extends GroupAbstractRepository {
	public function get_query() {
		// Get orders by group id
		$this->join[] = "LEFT JOIN (
			SELECT meta.post_id, meta.meta_value AS value
		    FROM {$this->db->postmeta} meta
		    JOIN {$this->db->posts} p ON p.ID = meta.post_id
		    JOIN {$this->db->postmeta} m ON p.ID = m.post_id AND m.meta_key = 'status' AND m.meta_value = 'completed'
			WHERE meta.meta_key = 'items' AND p.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}'
		    GROUP BY meta.post_id,value
		) AS lms_orders ON lms_orders.value LIKE CONCAT('%',group_data.group_id,'%')";
	}
}
