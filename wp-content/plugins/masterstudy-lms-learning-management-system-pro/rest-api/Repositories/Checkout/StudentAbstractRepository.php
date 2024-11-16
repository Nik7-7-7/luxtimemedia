<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout;

use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\DataTableAbstractRepository;

class StudentAbstractRepository extends DataTableAbstractRepository {
	public function get_students_data( $columns = array(), $order = array() ): array {
		$this->apply_sort( $order, $columns );

		return $this->build_all();
	}

	public function get_total( $status = array( 'publish' ) ) {
		$sql = "{$this->db->users} u";
		if ( $this->is_current_user_instructor() ) {
			$sql = $this->db->prepare(
				"(SELECT DISTINCT u.id AS ID, user_login FROM {$this->db->users} u
				INNER JOIN {$this->db->prefix}stm_lms_user_courses course ON course.user_id = u.ID
				INNER JOIN {$this->db->posts} p ON p.post_type = 'stm-courses' AND p.post_status IN ('publish') AND p.post_author = %d AND course.course_id = p.ID
				WHERE u.ID != %d
			) AS u",
				$this->current_instructor_id,
				$this->current_instructor_id
			);
		}

		$search_query = '';
		$search_join  = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND COALESCE(NULLIF(CONCAT_WS(' ',first_name.meta_value, last_name.meta_value),''), u.user_login) LIKE '%{$this->search_value}%'";
			$search_join  = "INNER JOIN {$this->db->usermeta} first_name ON u.ID = first_name.user_id AND first_name.meta_key = 'first_name'
			INNER JOIN {$this->db->usermeta} last_name ON u.ID = last_name.user_id AND last_name.meta_key = 'last_name'";
		}

		$total_subscribers = $this->db->get_var(
			"SELECT COUNT(*)
			FROM $sql
			INNER JOIN {$this->db->usermeta} um ON u.ID = um.user_id
			{$search_join}
			WHERE um.meta_key = '{$this->db->prefix}capabilities'
			AND (
				um.meta_value LIKE '%subscriber%' OR
				um.meta_value LIKE '%administrator%' OR
				um.meta_value LIKE '%stm_lms_instructor%'
			) {$search_query}"
		);

		return $total_subscribers;
	}

	public function get_progress_students_data( $columns = array(), $order = array() ) {
		$this->apply_sort( $order, $columns, 'student_name' );

		$certificate_threshold = intval( \STM_LMS_Options::get_option( 'certificate_threshold', 70 ) );

		$this->search_column = array( 'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), u.user_login)' );

		$this->select   = array(
			'u.ID AS user_id',
			'DATE_FORMAT(u.user_registered, "%d.%m.%Y") AS joined_view',
			'u.user_registered AS joined',
			'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), u.user_login) as student_name',
			'COALESCE(courses.count, 0) AS total',
			'COALESCE(course_progress_unst.not_started, 0) AS not_started',
			'COALESCE(course_progress_inpr.in_progress, 0) AS in_progress',
			'COALESCE(course_progress_com.completed, 0) AS completed',
			'COALESCE(SUM(course_expired.meta_value), 0) AS expired',
			'COALESCE(COUNT(reviews.review_id), 0) AS reviews',
			'COALESCE(certificates.count, 0) AS certificates',
		);
		$this->group_by = array(
			'u.ID',
			'u.user_login',
			'first_name.meta_value',
			'last_name.meta_value',
		);

		$instructor_where = "{$this->db->users} u";
		if ( $this->is_current_user_instructor() ) {
			$instructor_where = $this->db->prepare(
				"(SELECT
		        DISTINCT u.id AS ID,
		        u.user_registered AS user_registered,
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

		// Select aggregate tables
		$this->get_query();

		$ts_from = $this->get_timestamp( $this->date_from );
		$ts_to   = $this->get_timestamp( $this->date_to );

		$sql          = 'SELECT ' . implode( ',', $this->select ) . " FROM {$instructor_where}\n";
		$this->join[] = "INNER JOIN {$this->db->usermeta} first_name ON u.ID = first_name.user_id AND first_name.meta_key = 'first_name'";
		$this->join[] = "INNER JOIN {$this->db->usermeta} last_name ON u.ID = last_name.user_id AND last_name.meta_key = 'last_name'";
		$this->join[] = "LEFT JOIN (
			SELECT COUNT(DISTINCT user_course_id) as count, user_id
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}'
		    GROUP BY user_id
		) AS courses ON courses.user_id = u.ID";
		$this->join[] = "LEFT JOIN (
		    SELECT DISTINCT user_id,COUNT(progress_percent) AS not_started
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}' AND {$this->table_courses}.progress_percent = 0
		    GROUP BY user_id
		) AS course_progress_unst ON course_progress_unst.user_id = u.ID";
		$this->join[] = "LEFT JOIN (
		    SELECT DISTINCT user_id,COUNT(progress_percent) AS in_progress
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}'
		    AND {$this->table_courses}.progress_percent <= {$certificate_threshold} AND {$this->table_courses}.progress_percent > 0
		    GROUP BY user_id
		) AS course_progress_inpr ON course_progress_inpr.user_id = u.ID";
		$this->join[] = "LEFT JOIN (
		    SELECT DISTINCT user_id,COUNT(progress_percent) AS completed
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}'
		    AND {$this->table_courses}.progress_percent > {$certificate_threshold}
		    GROUP BY user_id
		) AS course_progress_com ON course_progress_com.user_id = u.ID";
		$this->join[] = "LEFT JOIN (
			SELECT user_id, start_time, post_id, meta_key, meta.value AS meta_value
			FROM wp_stm_lms_user_courses
			INNER JOIN ( 
				SELECT DISTINCT meta_value AS value, post_id, meta_key
				FROM {$this->db->postmeta}
				WHERE meta_key = 'end_time' AND meta_value IS NOT NULL AND meta_value != '' AND meta_value != '0'
			) AS meta ON meta.post_id = course_id
			WHERE start_time >= '{$ts_from}' AND start_time <= '{$ts_to}' AND progress_percent < {$certificate_threshold} 
				AND UNIX_TIMESTAMP() > ((CAST(meta.value AS UNSIGNED) * 86400) + start_time)
		) AS course_expired ON course_expired.user_id = u.ID";
		$this->join[] = "LEFT JOIN (
		    SELECT p.ID as review_id, p.post_author AS author_id FROM {$this->db->posts} p
		    WHERE p.post_type = 'stm-reviews' AND p.post_status = 'publish'
		) AS reviews ON reviews.author_id = u.ID";
		$this->join[] = "LEFT JOIN (
		    SELECT DISTINCT user_id, COUNT(course_id) AS count
		    FROM {$this->table_courses}
		    WHERE end_time BETWEEN '{$ts_from}' AND '{$ts_to}'
		    GROUP BY user_id
		) AS certificates ON certificates.user_id = u.ID\n";

		$sql .= implode( "\n", $this->join ) . "\n";

		$sql .= $this->where_query();

		$sql .= $this->group_query();

		// Add order, limit & offset
		$sql .= $this->pagination_query();

		$results = $this->db->get_results( $sql, ARRAY_A );

		return $results;
	}
}
