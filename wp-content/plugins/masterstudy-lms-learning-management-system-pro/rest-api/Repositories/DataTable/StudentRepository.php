<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\DataTable;

class StudentRepository extends DataTableAbstractRepository {

	public function get_students_data( $columns = array(), $order = array() ): array {
		$this->apply_sort( $order, $columns, 'enrollments' );

		$where = $this->db->prepare(
			'WHERE u.user_registered BETWEEN %s AND %s',
			$this->date_from,
			$this->date_to
		);
		if ( ! empty( $this->search_value ) ) {
			$where .= " AND u.display_name LIKE '%%{$this->search_value}%%'";
		}

		$query = "
            SELECT 
                u.ID,
                u.display_name AS name,
                COALESCE(enrollments.enrollments_count, 0) AS enrollments,
                COALESCE(reviews.reviews_count, 0) AS reviews,
                u.user_registered AS joined
            FROM {$this->db->users} u
            LEFT JOIN (
                SELECT user_id, COUNT(course_id) AS enrollments_count
                FROM {$this->db->prefix}stm_lms_user_courses
                GROUP BY user_id
            ) enrollments ON u.ID = enrollments.user_id
            LEFT JOIN (
                SELECT p.post_author AS user_id, COUNT(*) AS reviews_count
                FROM {$this->db->posts} p
                INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'stm-reviews'
                AND p.post_status = 'publish'
                AND pm.meta_key = 'review_mark'
                GROUP BY p.post_author
            ) reviews ON u.ID = reviews.user_id
            {$where}
            ORDER BY {$this->sort_by} {$this->sort_dir}
            LIMIT {$this->limit} OFFSET {$this->start}
        ";

		return $this->db->get_results( $this->db->prepare( $query ), ARRAY_A );
	}

	public function get_instructor_students( $columns = array(), $order = array() ): array {
		$this->apply_sort( $order, $columns, 'enrollments' );

		$search_where = '';
		if ( ! empty( $this->search_value ) ) {
			$search_where = "AND u.display_name LIKE '%%{$this->search_value}%%'";
		}
		$instructor_from   = "{$this->db->users} u";
		$enrollments_where = '';
		$reviews_where     = '';
		if ( $this->is_current_user_instructor() ) {
			$instructor_from   = "(SELECT
		        DISTINCT u.id AS ID,
		        u.display_name AS display_name,
		        u.user_registered as user_registered
		        FROM {$this->db->users} u
				INNER JOIN {$this->db->prefix}stm_lms_user_courses course ON course.user_id = u.ID
				INNER JOIN {$this->db->posts} p ON p.post_type = 'stm-courses' AND p.post_status IN ('publish') AND 
					p.post_author = {$this->current_instructor_id} AND course.course_id = p.ID
				WHERE u.ID != {$this->current_instructor_id} AND p.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}' {$search_where}
			) AS u";
			$enrollments_where = "AND enrollments.author = {$this->current_instructor_id}";
			$reviews_where     = "AND reviews.author_course = {$this->current_instructor_id}";
		}

		$query = "
            SELECT 
                DISTINCT u.ID,
                u.display_name AS name,
                SUM(enrollments.enrollments_count) AS enrollments,
                COALESCE(reviews.reviews_count, 0) AS reviews,
                u.user_registered AS joined
            FROM {$instructor_from}
            LEFT JOIN (
                SELECT user_id, ic.post_author AS author, course_id, COUNT(course_id) AS enrollments_count
                FROM {$this->db->prefix}stm_lms_user_courses
                INNER JOIN {$this->db->posts} ic ON ic.post_type = 'stm-courses' AND ic.post_status IN ('publish') AND course_id = ic.ID
                GROUP BY user_id, ic.post_author, course_id
            ) enrollments ON u.ID = enrollments.user_id {$enrollments_where}
            LEFT JOIN (
                SELECT p.post_author AS user_id, course.post_author AS author_course, COUNT(*) AS reviews_count
                FROM {$this->db->posts} p
                INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
                LEFT JOIN {$this->db->postmeta} AS meta ON meta.post_id = p.ID AND meta.meta_key = 'review_course'
    			INNER JOIN {$this->db->posts} AS course ON course.ID = meta.meta_value
                WHERE p.post_type = 'stm-reviews'
                AND p.post_status = 'publish'
                AND pm.meta_key = 'review_mark'
                GROUP BY p.post_author, course.post_author
            ) reviews ON u.ID = reviews.user_id {$reviews_where}
			GROUP BY u.ID, u.display_name,  u.user_registered
            ORDER BY {$this->sort_by} {$this->sort_dir}
            LIMIT {$this->limit} OFFSET {$this->start}
        ";

		return $this->db->get_results( $this->db->prepare( $query ), ARRAY_A );
	}

	public function get_total_students(): int {
		$search_where = '';
		if ( ! empty( $this->search_value ) ) {
			$search_where = "AND u.display_name LIKE '%%{$this->search_value}%%'";
		}

		$instructor_from  = "{$this->db->users} u";
		$instructor_where = $this->db->prepare( 'WHERE u.ID != %d', $this->current_instructor_id );
		if ( $this->is_current_user_instructor() ) {
			$instructor_from = $this->db->prepare(
				"(SELECT DISTINCT u.id AS ID FROM {$this->db->users} u
			INNER JOIN {$this->db->prefix}stm_lms_user_courses course ON course.user_id = u.ID
			INNER JOIN {$this->db->posts} p ON p.post_type = 'stm-courses' AND p.post_status IN ('publish') AND p.post_author = %d AND course.course_id = p.ID
			WHERE u.ID != %d AND p.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}' {$search_where}
		) AS u",
				$this->current_instructor_id,
				$this->current_instructor_id
			);
		}

		return $this->db->get_var( "SELECT COUNT(*) as count FROM {$instructor_from} {$instructor_where}" );
	}


	public function get_total( $status = array( 'publish' ) ) {
		$date_condition = $this->db->prepare(
			'AND u.user_registered BETWEEN %s AND %s',
			$this->date_from,
			$this->date_to
		);

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->users} u WHERE u.display_name LIKE '%s' $date_condition",
				'%' . $this->search_value . '%',
			)
		);
	}
}
