<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\BundleAbstractRepository;

class BundleRepository extends BundleAbstractRepository {
	public function get_query() {
		// Get bundle products
		$this->join[] = "LEFT JOIN (
			SELECT post_id AS product_id, meta.meta_value AS value
		    FROM {$this->db->postmeta} meta WHERE meta.meta_key = 'stm_lms_product_id'
		    GROUP BY post_id,value
		) AS bundle_products ON bundle_products.value = bundle_data.bundle_id";
		// Get all courses in order from bundle
		$this->join[]   = "LEFT JOIN (
		    SELECT object_id AS course_id, SUM(DISTINCT quantity * price) * COUNT(DISTINCT order_id) AS total_revenue, COUNT(DISTINCT order_id) as counts
		    FROM {$this->table_orders} ord
		    LEFT JOIN {$this->db->posts} p ON ord.order_id = p.ID
		    INNER JOIN {$this->db->prefix}wc_orders orders ON p.ID = orders.id AND orders.status = 'wc-completed'
			WHERE p.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}'
		    GROUP BY object_id
		) AS orders ON orders.course_id = bundle_products.product_id";
		$this->group_by = array_merge(
			$this->group_by,
			array(
				'orders.counts',
			)
		);
	}
}
