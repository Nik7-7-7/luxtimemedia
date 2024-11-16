<?php
namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout;

use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\DataTableAbstractRepository;

class GroupAbstractRepository extends DataTableAbstractRepository {
	public function get_groups_data( $columns = array(), $order = array() ): array {
		// If Enterprise Groups Addon is not enabled
		if ( ! is_ms_lms_addon_enabled( 'enterprise_courses' ) ) {
			return array();
		}

		$this->apply_sort( $order, $columns );
		$this->search_column = array( 'group_data.group_name' );

		$this->select    = array(
			'group_data.group_name AS name',
			'(CASE 
				WHEN LOCATE(users.user_email,group_data.emails) = 0 THEN LENGTH(group_data.emails) - LENGTH(REPLACE(group_data.emails, ",", "")) + 2
				ELSE LENGTH(group_data.emails) - LENGTH(REPLACE(group_data.emails, ",", "")) + 1
			END) as students',
			'group_data.group_id',
			'group_data.date_created',
			'COALESCE(COUNT(DISTINCT lms_orders.post_id), 0) AS orders',
			'COALESCE(SUM(DISTINCT orders.total_revenue), 0) AS revenue',
			'COUNT(DISTINCT courses_data.course_id) as courses',
		);
		$this->group_by  = array(
			'group_data.group_id',
			'group_data.group_name',
			'group_data.emails',
			'group_data.date_created',
		);
		$this->post_type = 'stm-ent-groups';

		// Select aggregate tables
		$this->get_query();

		$instructor_where = '';
		$instructor_join  = '';
		if ( $this->is_current_user_instructor() ) {
			$instructor_where = $this->db->prepare( ' AND (p.post_author = %d OR p.ID IN (courses.enterprise_id))', $this->current_instructor_id );
			$instructor_join  = $this->db->prepare(
				"LEFT JOIN {$this->db->posts} ins_c ON ins_c.post_type = 'stm-courses' AND ins_c.post_status IN ('publish') AND ins_c.post_author = %d
				LEFT JOIN {$this->table_courses} courses ON courses.course_id = ins_c.ID AND courses.enterprise_id != '0'",
				$this->current_instructor_id
			);
		}

		// Select Groups
		$sql = 'SELECT ' . implode( ',', $this->select ) . "
			FROM (
			    SELECT
			        p.ID AS group_id,
			        p.post_title AS group_name,
			        p.post_author as group_author,
			        COALESCE(MAX(CASE WHEN pm.meta_key = 'emails' THEN pm.meta_value END), '0') AS emails,
			        p.post_date AS date_created
			    FROM {$this->db->posts} p
			    LEFT JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'emails'
			    {$instructor_join}
			    WHERE p.post_type = '{$this->post_type}' AND p.post_status IN ('publish', 'draft' ) {$instructor_where}
			    GROUP BY p.ID, p.post_title, p.post_date
			) AS group_data\n";

		$this->join[] = "INNER JOIN {$this->db->users} users ON users.id = group_data.group_author";

		$this->join[] = "LEFT JOIN (
			SELECT enterprise_id, course_id, COUNT(DISTINCT course_id) AS courses
		    FROM {$this->table_courses}
		    GROUP BY enterprise_id,course_id
		) AS courses_data ON group_data.group_id = courses_data.enterprise_id";

		$this->join[] = "LEFT JOIN (
		    SELECT order_id, SUM(DISTINCT quantity * price) AS total_revenue, COUNT(order_id) as counts
		    FROM {$this->table_orders}
		    GROUP BY order_id
		) AS orders ON orders.order_id = lms_orders.post_id";
		$sql         .= implode( "\n", $this->join ) . "\n";
		$sql         .= $this->where_query();
		$sql         .= $this->group_query();

		// Add order, limit & offset
		$sql    .= $this->pagination_query();
		$results = $this->db->get_results( $sql, ARRAY_A );

		return $results;
	}
}
