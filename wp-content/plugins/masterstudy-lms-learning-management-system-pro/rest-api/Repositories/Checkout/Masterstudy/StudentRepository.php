<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\MasterStudy;

use MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\StudentAbstractRepository;

class StudentRepository extends StudentAbstractRepository {

	public function build_query(): string {

		$dynamic_orders_table = 'o.ID';

		$this->select = array(
			'u.ID AS ID',
			'u.display_name AS name',
			"COUNT(DISTINCT {$dynamic_orders_table}) AS total_orders",
			'GROUP_CONCAT(DISTINCT CONCAT(o.ID, ",", oi.object_id) ORDER BY o.ID ASC) AS order_ids',
			'COALESCE(COUNT(DISTINCT p_b.ID), 0) AS bundles',
			'COALESCE(COUNT(DISTINCT p_e.ID), 0) AS purchased_groups',
			'COALESCE(COUNT(DISTINCT oi.object_id), 0) AS courses',
			"GROUP_CONCAT(DISTINCT REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(pm_bundle.meta_value, '\";', n.n), ':', -1), '\"', ''), ';', ''), '}', '') ORDER BY pm_bundle.post_id) AS bundle_ids",
			"GROUP_CONCAT(DISTINCT 
				CASE 
					WHEN p.ID IS NOT NULL AND p.post_type = 'stm-courses' THEN oi.object_id 
					ELSE REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(pm_bundle.meta_value, '\";', n.n), ':', -1), '\"', ''), ';', ''), '}', '')
				END
			) AS mix_ids",
			'COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue',
		);

		$this->join = array(
			"LEFT JOIN {$this->db->posts} o ON u.ID = o.post_author AND o.post_type = 'stm-orders' AND o.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}'",
			"LEFT JOIN {$this->db->postmeta} pm_user ON o.ID = pm_user.post_id AND pm_user.meta_key = 'user_id'",
			"LEFT JOIN {$this->db->postmeta} pm_date ON o.ID = pm_date.post_id AND pm_date.meta_key = 'date'",
			"LEFT JOIN {$this->db->postmeta} pm_status ON o.ID = pm_status.post_id AND pm_status.meta_key = 'status'",
			"LEFT JOIN {$this->db->prefix}stm_lms_order_items oi ON o.ID = oi.order_id",
			"LEFT JOIN {$this->db->postmeta} pm_bundle ON oi.object_id = pm_bundle.post_id AND pm_bundle.meta_key = 'stm_lms_bundle_ids'",
			"LEFT JOIN {$this->db->posts} p ON p.ID = oi.object_id",
			"LEFT JOIN {$this->db->posts} p_b ON p_b.ID = oi.object_id AND p_b.post_type = 'stm-course-bundles'",
			"LEFT JOIN {$this->db->posts} p_e ON p_e.ID = oi.object_id AND p_e.post_type = 'stm-ent-groups'",
			'JOIN ( SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) n',
		);

		$lms_where_alias = "WHERE (pm_date.meta_value BETWEEN '{$this->get_timestamp($this->date_from)}' AND '{$this->get_timestamp($this->date_to)}' OR pm_date.meta_value IS NULL) AND";
		$woo_where_alias = 'WHERE';
		$result_where    = $dynamic_orders_table ? $woo_where_alias : $lms_where_alias;

		$search_query = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND u.display_name LIKE '%{$this->search_value}%'";
		}

		if ( 'purchased_groups' === $this->sort_by ) {
			$this->sort_by = 'courses';
		}
		$where = "
		{$result_where} um.meta_key = '{$this->db->prefix}capabilities'
		AND (
			um.meta_value LIKE '%subscriber%' OR
			um.meta_value LIKE '%administrator%' OR
			um.meta_value LIKE '%stm_lms_instructor%'
		)
		{$search_query}
		GROUP BY u.ID, u.user_login
		ORDER BY {$this->sort_by} {$this->sort_dir}
		LIMIT {$this->limit} OFFSET {$this->start}";

		$select = implode( ', ', $this->select );
		$join   = implode( "\n", $this->join );

		$date_condition      = "AND (pm_date.meta_value BETWEEN '{$this->get_timestamp($this->date_from)}' AND '{$this->get_timestamp($this->date_to)}' OR pm_date.meta_value IS NULL)";
		$completed_condition = "AND (pm_status.meta_value = 'completed' OR pm_status.meta_value IS NULL)";

		$where = str_replace(
			'GROUP BY',
			"$date_condition $completed_condition GROUP BY",
			$where
		);

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

		return "SELECT 
            $select,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue  -- Calculate revenue
        FROM {$sql}
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
