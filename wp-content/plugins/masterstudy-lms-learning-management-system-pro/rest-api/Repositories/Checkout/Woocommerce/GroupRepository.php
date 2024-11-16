<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\GroupAbstractRepository;

class GroupRepository extends GroupAbstractRepository {
	public function get_query() {
		// Get orders by group
		$this->join[] = "LEFT JOIN (
			SELECT meta.post_id, meta.meta_value AS value
		    FROM {$this->db->postmeta} meta
		    JOIN {$this->db->posts} p ON p.ID = meta.post_id
		    INNER JOIN {$this->db->prefix}wc_orders orders ON p.ID = orders.id AND orders.status = 'wc-completed'
			WHERE meta.meta_key = 'stm_lms_courses' AND p.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}'
		    GROUP BY meta.post_id,value
		) AS lms_orders ON lms_orders.value LIKE CONCAT('%',group_data.group_id,'%')";
	}
}
