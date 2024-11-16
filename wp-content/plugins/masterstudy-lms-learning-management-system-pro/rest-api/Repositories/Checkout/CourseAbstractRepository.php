<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\DataTableAbstractRepository;

class CourseAbstractRepository extends DataTableAbstractRepository {
	public function get_all_course_data( $columns = array(), $order = array(), $user_id = null ): array {
		$this->apply_sort( $order, $columns );

		$revenue_query = $this->get_revenue_query();

		$user_courses_where = '';

		if ( null !== $user_id ) {
			$user_courses_where = 'AND p.post_author = %d';
		}
		$this->search_column = array( 'course_data.course_name' );
		$search_query        = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "WHERE course_data.course_name LIKE '%%{$this->search_value}%%'";
		}

		$instructor_where = '';
		if ( $this->is_current_user_instructor() ) {
			$instructor_where = $this->db->prepare( 'AND p.post_author = %d', $this->current_instructor_id );
		}

		$query = "SELECT
					course_data.course_id,
					course_data.course_name,
					course_data.course_slug,
					CAST(course_data.views AS UNSIGNED) AS views,
					CAST(course_data.price AS DECIMAL(10,2)) AS price,
					COALESCE(courses.counts, 0) AS enrollments,
					COALESCE(revenue_data.total_revenue, 0) AS revenue,
					course_data.date_created
				FROM (
					SELECT
						p.ID AS course_id,
						p.post_title AS course_name,
						p.post_name AS course_slug,
						COALESCE(MAX(CASE WHEN pm.meta_key = 'views' THEN pm.meta_value END), '0') AS views,
						COALESCE(MAX(CASE WHEN pm.meta_key = 'price' THEN pm.meta_value END), '0') AS price,
						COALESCE(MAX(CASE WHEN pm.meta_key = 'current_students' THEN pm.meta_value END), '0') AS enrollments,
						p.post_date AS date_created
					FROM {$this->db->posts} p
					LEFT JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
						AND pm.meta_key IN ('current_students', 'views', 'price')
					WHERE p.post_type = %s AND p.post_status = 'publish' $instructor_where
					$user_courses_where
					GROUP BY p.ID, p.post_title, p.post_date
				) AS course_data
			    LEFT JOIN (
					SELECT course_id, COUNT(course_id) as counts FROM {$this->db->prefix}stm_lms_user_courses
					GROUP BY course_id
				) AS courses ON courses.course_id = course_data.course_id
				LEFT JOIN (
					$revenue_query
				) AS revenue_data ON course_data.course_id = revenue_data.course_id
				$search_query
				ORDER BY {$this->sort_by} {$this->sort_dir}
				LIMIT %d OFFSET %d";

		$this->post_type = PostType::COURSE;

		if ( null !== $user_id ) {
			$query = $this->db->prepare( $query, PostType::COURSE, $user_id, $this->limit, $this->start );
		} else {
			$query = $this->db->prepare( $query, PostType::COURSE, $this->limit, $this->start );
		}

		return $this->db->get_results(
			$query,
			ARRAY_A
		);
	}

	public function get_total_instructor_courses( $author_id = null ) {
		$search_query = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND p.post_title LIKE '%{$this->search_value}%'";
		}
		$sql = "SELECT COUNT(*) as count FROM {$this->db->posts} p
            WHERE p.post_type = '{$this->post_type}' AND p.post_status = 'publish' $search_query";

		if ( null !== $author_id ) {
			$author_id = esc_sql( $author_id );
			$sql      .= " AND p.post_author = '{$author_id}'";
		}

		if ( $this->is_current_user_instructor() ) {
			$sql .= $this->db->prepare( ' AND p.post_author = %d', $this->current_instructor_id );
		}

		return $this->db->get_var( $sql );
	}


	public function get_progress_courses_data( $columns = array(), $order = array() ) {
		$this->apply_sort( $order, $columns, 'course_name' );

		$certificate_threshold = intval( \STM_LMS_Options::get_option( 'certificate_threshold', 70 ) );

		$this->post_type = 'stm-courses';

		$this->search_column = array( 'courses_data.course_name' );

		$this->select   = array(
			'courses_data.course_name AS name',
			'courses_data.course_id',
			'courses_data.course_slug',
			'DATE_FORMAT(courses_data.date_created, "%d.%m.%Y %k:%i") AS date_created_view',
			'courses_data.date_created AS date_created',
			'COALESCE(course_progress_unst.not_started, 0) AS not_started',
			'COALESCE(course_progress_inpr.in_progress, 0) AS in_progress',
			'COALESCE(course_progress_com.completed, 0) AS completed',
			'COALESCE(SUM(course_expired.meta_value), 0) AS expired',
			'COALESCE(COUNT(DISTINCT reviews.review_id), 0) AS reviews',
		);
		$this->group_by = array(
			'courses_data.course_id',
			'courses_data.course_name',
		);

		$ts_from = $this->get_timestamp( $this->date_from );
		$ts_to   = $this->get_timestamp( $this->date_to );

		$instructor_where = '';
		if ( $this->is_current_user_instructor() ) {
			$instructor_where = $this->db->prepare( 'AND p.post_author = %d', $this->current_instructor_id );
		}

		$query = 'SELECT ' . implode( ',', $this->select ) . "
			FROM (
			    SELECT
			        p.ID AS course_id,
			        p.post_title AS course_name,
			        p.post_name AS course_slug,
			        p.post_author as course_author,
			        p.post_date AS date_created
			    FROM {$this->db->posts} p
			    WHERE p.post_type = '{$this->post_type}' AND p.post_status IN ('publish') {$instructor_where}
			    GROUP BY p.ID, p.post_title, p.post_date
			) AS courses_data\n";

		// Add not started courses
		$this->join[] = "LEFT JOIN (
			SELECT DISTINCT course_id,COUNT(progress_percent) AS not_started
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}'
		    AND {$this->table_courses}.progress_percent = 0
		    GROUP BY course_id
		) AS course_progress_unst ON course_progress_unst.course_id = courses_data.course_id";

		// Add in progress courses
		$this->join[] = "LEFT JOIN (
			SELECT DISTINCT course_id,COUNT(progress_percent) AS in_progress
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}'
		    AND {$this->table_courses}.progress_percent <= {$certificate_threshold} AND {$this->table_courses}.progress_percent > 0
		    GROUP BY course_id
		) AS course_progress_inpr ON course_progress_inpr.course_id = courses_data.course_id";

		// Add completed courses
		$this->join[] = "LEFT JOIN (
			SELECT DISTINCT course_id,COUNT(progress_percent) AS completed
		    FROM {$this->table_courses}
		    WHERE start_time >= '{$ts_from}' AND end_time <= '{$ts_to}'
		    AND {$this->table_courses}.progress_percent > {$certificate_threshold}
		    GROUP BY course_id
		) AS course_progress_com ON course_progress_com.course_id = courses_data.course_id";

		// Add expired courses
		$this->join[] = "LEFT JOIN (
			SELECT course_id, start_time, post_id, meta_key, meta.value AS meta_value
			FROM {$this->table_courses}
			INNER JOIN (
		        SELECT DISTINCT meta_value AS value, post_id, meta_key 
		        FROM {$this->db->postmeta} 
		        WHERE meta_key = 'end_time' AND meta_value IS NOT NULL AND meta_value != ''  AND meta_value != '0'
			) AS meta ON meta.post_id = course_id
			WHERE start_time >= '{$ts_from}' AND start_time <= '{$ts_to}'AND progress_percent < {$certificate_threshold} 
				AND UNIX_TIMESTAMP() > ((CAST(meta.value AS UNSIGNED) * 86400) + start_time)
		) AS course_expired ON course_expired.post_id = courses_data.course_id";

		// Add reviews counts
		$this->join[] = "LEFT JOIN (
		    SELECT p.ID as review_id, meta.meta_value AS course_id
		    FROM {$this->db->posts} p
		    JOIN {$this->db->postmeta} AS meta ON meta.post_id = p.ID AND meta.meta_key = 'review_course'
		    WHERE post_status = 'publish'
		) AS reviews ON reviews.course_id = courses_data.course_id";

		$query .= implode( "\n", $this->join ) . "\n";

		$query .= $this->where_query();

		$query .= $this->group_query();

		// Add order, limit & offset
		$query .= $this->pagination_query();

		return $this->db->get_results(
			$query,
			ARRAY_A
		) ?? array();
	}
}
