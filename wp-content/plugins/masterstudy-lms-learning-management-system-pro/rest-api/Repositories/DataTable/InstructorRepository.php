<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\DataTable;

use MasterStudy\Lms\Plugin\PostType;

class InstructorRepository extends DataTableAbstractRepository {

	public function get_instructors_data( $columns = array(), $order = array() ): array {
		// Specify the default sort column here
		$this->apply_sort( $order, $columns, 'own_courses' );

		$this->search_column = array( 'u.display_name' );

		$search_query = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = " AND u.display_name LIKE '%%$this->search_value%%'";
		}

		$date_condition = $this->db->prepare(
			'AND u.user_registered BETWEEN %s AND %s',
			$this->date_from,
			$this->date_to
		);

		$query = "
            SELECT 
                u.ID,
                u.display_name AS name,
                COALESCE(enrollments.total_enrollments, 0) AS enrollments,
                COALESCE(own_courses.own_courses_count, 0) AS own_courses,
                COALESCE(marks.total_marks, 0) AS reviews,
                u.user_registered AS joined
            FROM {$this->db->users} u
            INNER JOIN {$this->db->usermeta} um ON u.ID = um.user_id AND um.meta_key = %s
            LEFT JOIN (
                SELECT 
                    p.post_author AS instructor_id,
                    COALESCE(SUM(CASE WHEN pm.meta_key = 'current_students' THEN pm.meta_value END), 0) AS total_enrollments
                FROM {$this->db->posts} p
                LEFT JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = %s AND p.post_status = 'publish'
                GROUP BY p.post_author
            ) enrollments ON u.ID = enrollments.instructor_id
            LEFT JOIN (
                SELECT 
                    post_author AS instructor_id,
                    COUNT(ID) AS own_courses_count
                FROM {$this->db->posts}
                WHERE post_type = %s
                GROUP BY post_author
            ) own_courses ON u.ID = own_courses.instructor_id
            LEFT JOIN (
                SELECT post_author, SUM(
                    (LENGTH(pm.meta_value) - LENGTH(REPLACE(pm.meta_value, ';', ''))) / 2
                ) AS total_marks
                FROM {$this->db->posts} p
                LEFT JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
                WHERE pm.meta_key = %s AND p.post_type = %s
                GROUP BY post_author
            ) marks ON u.ID = marks.post_author
            WHERE (um.meta_value LIKE %s OR um.meta_value LIKE %s) {$date_condition} {$search_query}
            ORDER BY {$this->sort_by} {$this->sort_dir}
            LIMIT %d OFFSET %d
        ";

		$prepared_query = $this->db->prepare(
			$query,
			"{$this->db->prefix}capabilities",
			PostType::COURSE,
			PostType::COURSE,
			'course_marks',
			PostType::COURSE,
			'%stm_lms_instructor%',
			'%administrator%',
			$this->limit,
			$this->start
		);

		return $this->db->get_results( $prepared_query, ARRAY_A );
	}

	public function get_total_instructors() {
		$date_condition = $this->db->prepare(
			'AND u.user_registered BETWEEN %s AND %s',
			$this->date_from,
			$this->date_to
		);

		$search_query = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = " AND u.display_name LIKE '%%$this->search_value%%'";
		}

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) 
                FROM {$this->db->users} u
                INNER JOIN {$this->db->usermeta} um ON u.ID = um.user_id
                WHERE um.meta_key = %s 
                AND ( um.meta_value LIKE %s OR um.meta_value LIKE %s ) {$search_query}
                $date_condition",
				"{$this->db->prefix}capabilities",
				'%stm_lms_instructor%',
				'%administrator%'
			)
		);
	}
}
