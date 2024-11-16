<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\WooCommerce;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\StudentAbstractRepository;

class StudentRepository extends StudentAbstractRepository {

	public function build_query(): string {
		$this->select = array(
			'u.ID AS ID',
			'u.display_name AS name',
			'revenue_data.total_orders',
			'revenue_data.order_ids',
			'revenue_data.revenue AS revenue',
			'COUNT(DISTINCT CASE WHEN bundle_meta.meta_value LIKE \'a:%\' THEN bundle_meta.post_id ELSE NULL END) AS bundle_count',
			'SUM(
                CASE 
                    WHEN bundle_meta.meta_value LIKE \'a:%\' THEN 
                        (LENGTH(bundle_meta.meta_value) - LENGTH(REPLACE(bundle_meta.meta_value, \'s:\', \'\'))) / LENGTH(\'s:\')
                    ELSE 0
                END
            ) AS bundles',
			'COUNT(DISTINCT CASE WHEN bundle_meta.meta_value IS NULL THEN pm_product.meta_value ELSE NULL END) AS courses',
			'COUNT(DISTINCT enterprise_meta.meta_value) AS purchased_groups',
		);

		$this->join = array(
			"LEFT JOIN (
                SELECT 
                    wco.customer_id,
                    COUNT(DISTINCT wco.ID) AS total_orders,
                    GROUP_CONCAT(DISTINCT wco.ID ORDER BY wco.ID ASC) AS order_ids,
                    COALESCE(SUM(wco.total_amount), 0) AS revenue  -- Assuming total_amount is the column for order revenue
                FROM wp_wc_orders wco
                WHERE wco.status = 'wc-completed'
                AND wco.date_created_gmt BETWEEN '{$this->date_from}' AND '{$this->date_to}'  -- Filter by date range
                GROUP BY wco.customer_id
            ) AS revenue_data ON u.ID = revenue_data.customer_id",
			"LEFT JOIN wp_wc_orders wco ON u.ID = wco.customer_id AND wco.status = 'wc-completed'",
			'LEFT JOIN wp_stm_lms_order_items si ON wco.ID = si.order_id',
			"LEFT JOIN {$this->db->postmeta} pm_bundle ON si.object_id = pm_bundle.post_id AND pm_bundle.meta_key = 'stm_lms_bundle_ids'",
			"LEFT JOIN {$this->db->postmeta} pm_product ON si.object_id = pm_product.post_id AND pm_product.meta_key = 'stm_lms_product_id'",
			"LEFT JOIN {$this->db->postmeta} bundle_meta ON (pm_product.meta_value = bundle_meta.post_id OR pm_bundle.meta_value = bundle_meta.post_id) AND bundle_meta.meta_key = 'stm_lms_bundle_ids'",
			"LEFT JOIN {$this->db->postmeta} enterprise_meta ON si.object_id = enterprise_meta.post_id AND enterprise_meta.meta_key = 'stm_lms_enterprise_id'",
			"LEFT JOIN {$this->db->posts} p_e ON p_e.ID = bundle_meta.post_id AND p_e.post_type = 'stm-ent-groups'",
		);

		$search_query = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND u.display_name LIKE '%{$this->search_value}%'";
		}

		$where = "
            WHERE um.meta_key = '{$this->db->prefix}capabilities'
            AND (
                um.meta_value LIKE '%subscriber%' OR
                um.meta_value LIKE '%administrator%' OR
                um.meta_value LIKE '%stm_lms_instructor%'
            )
            {$search_query}
            GROUP BY u.ID, u.display_name, revenue_data.total_orders, revenue_data.order_ids, revenue_data.revenue
            ORDER BY {$this->sort_by} {$this->sort_dir}
            LIMIT {$this->limit} OFFSET {$this->start}";

		$select = implode( ', ', $this->select );
		$join   = implode( "\n", $this->join );

		$sql = "{$this->db->users} u";
		if ( $this->is_current_user_instructor() ) {
			$sql = $this->db->prepare(
				"(SELECT
		        DISTINCT u.id AS ID,
		        u.display_name AS display_name,
		        u.user_login as user_login
		        FROM {$this->db->users} u
		
				INNER JOIN {$this->db->prefix}stm_lms_user_courses course ON course.user_id = u.ID
				INNER JOIN {$this->db->posts} p ON p.post_type = 'stm-courses' AND p.post_status IN ('publish') AND p.post_author = %d AND course.course_id = p.ID
				WHERE u.ID != %d
			) AS u",
				$this->current_instructor_id,
				$this->current_instructor_id
			);
		}

		return "SELECT $select
                FROM $sql
                INNER JOIN {$this->db->usermeta} um ON u.ID = um.user_id
                $join
                $where";
	}

	public function build_all(): array {
		return $this->db->get_results(
			$this->build_query(),
			ARRAY_A
		);
	}
}
