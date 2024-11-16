<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout;

use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\DataTableAbstractRepository;

class BundleAbstractRepository extends DataTableAbstractRepository {
	public function get_bundles_data( $columns = array(), $order = array() ): array {
		// If Bundle Groups Addon is not enabled
		if ( ! is_ms_lms_addon_enabled( 'course_bundle' ) ) {
			return array();
		}

		$this->apply_sort( $order, $columns );
		$this->search_column = array( 'bundle_data.bundle_name' );

		$this->select    = array(
			'bundle_data.bundle_name AS name',
			'bundle_data.bundle_id',
			'bundle_data.date_created',
			'COALESCE(SUM(SUBSTRING_INDEX(SUBSTRING_INDEX( courses_data.value, ":{", 1), ":", -1)), 0) AS courses_inside',
			'COALESCE(SUM(orders.total_revenue), 0) AS revenue',
			'COALESCE(orders.counts, 0) AS orders',
		);
		$this->group_by  = array(
			'bundle_data.bundle_id',
			'bundle_data.bundle_name',
			'bundle_data.date_created',
		);
		$this->post_type = 'stm-course-bundles';

		// Select aggregate tables
		$this->get_query();

		$instructor_where = '';
		if ( $this->is_current_user_instructor() ) {
			$instructor_where = $this->db->prepare( ' AND p.post_author = %d', $this->current_instructor_id );
		}

		// Select bundles
		$sql          = 'SELECT ' . implode( ',', $this->select ) . "
			FROM (
			    SELECT
			        p.ID AS bundle_id,
			        p.post_title AS bundle_name,
			        p.post_author as bundle_author,
			        p.post_date AS date_created
			    FROM {$this->db->posts} p
			    WHERE p.post_type = '{$this->post_type}' AND p.post_status IN ('publish') {$instructor_where}
			    GROUP BY p.ID, p.post_title, p.post_date
			) AS bundle_data\n";
		$this->join[] = "LEFT JOIN (
		    SELECT post_id AS bundle_id, meta.meta_value AS value
		    FROM {$this->db->postmeta} meta
		    WHERE meta.meta_key = 'stm_lms_bundle_ids'
		    GROUP BY post_id,value
		) AS courses_data ON courses_data.bundle_id = bundle_data.bundle_id";
		$sql         .= implode( "\n", $this->join ) . "\n";
		$sql         .= $this->where_query();
		$sql         .= $this->group_query();

		// Add order, limit & offset
		$sql    .= $this->pagination_query();
		$results = $this->db->get_results( $sql, ARRAY_A );

		return $results;
	}
}
