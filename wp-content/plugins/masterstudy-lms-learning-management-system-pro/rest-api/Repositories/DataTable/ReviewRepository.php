<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\DataTable;

use MasterStudy\Lms\Plugin\PostType;

class ReviewRepository extends DataTableAbstractRepository {
	public function get_reviewed_courses_data(): array {
		$this->apply_sort( array(), array(), 'reviews', 'desc' );

		$this->select   = array(
			'courses.ID AS course_id',
			'courses.post_title AS name',
			'COALESCE(COUNT(review.ID), 0) as reviews',
		);
		$this->group_by = array( 'courses.ID' );

		// Select aggregate tables
		$this->get_query();

		$instructor_from = "{$this->db->posts} AS courses";
		if ( $this->is_current_user_instructor() ) {
			$instructor_from = $this->db->prepare(
				"(SELECT ID, post_title, post_type FROM {$this->db->posts} WHERE post_author = %d) AS courses",
				$this->current_instructor_id
			);
		}

		// Select Groups
		$sql = 'SELECT ' . implode( ',', $this->select ) . " FROM {$instructor_from}\n";

		$this->join[] = "LEFT JOIN {$this->db->postmeta} AS meta ON meta.meta_value = courses.ID AND meta.meta_key = 'review_course'";
		$this->join[] = "LEFT JOIN {$this->db->posts} AS review ON review.ID = meta.post_id AND review.post_status = 'publish' AND review.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}' ";

		$sql .= implode( "\n", $this->join ) . "\n";
		$sql .= "WHERE courses.post_type = '%s'";

		$sql .= $this->group_query();
		// Add order, limit & offset
		$sql .= $this->pagination_query();

		return $this->db->get_results(
			$this->db->prepare( $sql, PostType::COURSE ),
			ARRAY_A
		);
	}

	public function get_reviewers_data(): array {
		$this->apply_sort( array(), array(), 'reviews', 'desc' );

		$this->select   = array(
			'users.ID AS user_id',
			'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), users.user_login) as name',
			'COUNT(reviews.ID) AS reviews',
		);
		$this->group_by = array(
			'users.ID',
			'first_name.meta_value',
			'last_name.meta_value',
		);

		$sql = 'SELECT ' . implode( ',', $this->select ) . " FROM {$this->db->users} AS users\n";

		$this->join[] = "INNER JOIN {$this->db->usermeta} first_name ON users.ID = first_name.user_id AND first_name.meta_key = 'first_name'";
		$this->join[] = "INNER JOIN {$this->db->usermeta} last_name ON users.ID = last_name.user_id AND last_name.meta_key = 'last_name'";
		$this->join[] = "LEFT JOIN {$this->db->posts} AS reviews ON reviews.post_author = users.ID AND reviews.post_type = %s AND reviews.post_status = 'publish' 
		 AND reviews.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}'";

		if ( $this->is_current_user_instructor() ) {
			$this->join[] = "LEFT JOIN {$this->db->postmeta} AS meta ON meta.post_id = reviews.ID AND meta.meta_key = 'review_course'";
			$this->join[] = $this->db->prepare(
				"INNER JOIN {$this->db->posts} AS course ON course.ID = meta.meta_value AND course.post_author = %d",
				$this->current_instructor_id
			);
		}

		$sql .= implode( "\n", $this->join ) . "\n";
		$sql .= $this->group_query();

		// Add order, limit & offset
		$sql .= $this->pagination_query();

		return $this->db->get_results(
			$this->db->prepare( $sql, PostType::REVIEW ),
			ARRAY_A
		);
	}

	public function get_reviews_data( string $status, $columns = array(), $order = array() ) {
		$this->apply_sort( $order, $columns, 'date_created' );

		$this->search_column = array(
			'course.post_title',
			'reviews.post_content',
			'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), users.user_login)',
		);

		$this->select = array(
			'reviews.ID AS review_id',
			'reviews.post_date AS date_created',
			'DATE_FORMAT(reviews.post_date, "%d.%m.%Y %k:%i") AS date_created_view',
			'course.post_title AS course_name',
			'reviews.post_content AS review',
			'rating.meta_value AS rating',
			'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), users.user_login) AS user_name',
		);

		$this->group_by = array(
			'reviews.ID',
			'first_name.meta_value',
			'last_name.meta_value',
			'course.post_title',
			'rating.meta_value',
		);

		$this->post_type = 'stm-reviews';

		$sql = 'SELECT ' . implode( ',', $this->select ) . " FROM {$this->db->posts} AS reviews\n";

		$this->join[]   = "INNER JOIN {$this->db->usermeta} first_name ON reviews.post_author = first_name.user_id AND first_name.meta_key = 'first_name'";
		$this->join[]   = "INNER JOIN {$this->db->usermeta} last_name ON reviews.post_author = last_name.user_id AND last_name.meta_key = 'last_name'";
		$this->join[]   = "INNER JOIN {$this->db->users} users ON reviews.post_author = users.ID";
		$this->join[]   = "LEFT JOIN {$this->db->postmeta} AS meta ON meta.post_id = reviews.ID AND meta.meta_key = 'review_course'";
		$this->join[]   = "LEFT JOIN {$this->db->postmeta} AS rating ON rating.post_id = reviews.ID AND rating.meta_key = 'review_mark'";
		$instructor_sql = "LEFT JOIN {$this->db->posts} AS course ON course.ID = meta.meta_value";
		if ( $this->is_current_user_instructor() ) {
			$instructor_sql = $this->db->prepare(
				"INNER JOIN {$this->db->posts} AS course ON course.ID = meta.meta_value AND course.post_author = %d",
				$this->current_instructor_id
			);
		}
		$this->join[] = $instructor_sql;

		$sql .= implode( "\n", $this->join ) . "\n";

		$sql .= $this->where_query(
			array(
				array(
					'column'   => 'reviews.post_status',
					'operator' => '=',
					'value'    => $status,
				),
				array(
					'column'   => 'reviews.post_type',
					'operator' => '=',
					'value'    => $this->post_type,
				),
				array(
					'column'   => 'reviews.post_date',
					'operator' => 'BETWEEN',
					'value'    => array(
						$this->date_from,
						$this->date_to,
					),
				),
			)
		);

		$sql .= $this->group_query();

		// Add order, limit & offset
		$sql .= $this->pagination_query();

		return $this->db->get_results(
			$sql,
			ARRAY_A
		) ?? array();

	}

	public function get_total( $status = array( 'publish' ) ) {
		$status         = array_map(
			function( $item ) {
				return '\'' . esc_sql( $item ) . '\'';
			},
			$status
		);
		$status         = implode( ', ', $status );
		$instructor_sql = '';
		if ( $this->is_current_user_instructor() ) {
			$instructor_sql = $this->db->prepare(
				"LEFT JOIN {$this->db->postmeta} AS meta ON meta.post_id = p.ID AND meta.meta_key = 'review_course'
				INNER JOIN {$this->db->posts} AS course ON course.ID = meta.meta_value AND course.post_author = %d",
				$this->current_instructor_id
			);
		}

		$search_query = '';
		$search_join  = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND (
			p.post_title LIKE '%{$this->search_value}%' OR
			p.post_content LIKE '%{$this->search_value}%' OR
			COALESCE(NULLIF(CONCAT_WS(' ',first_name.meta_value, last_name.meta_value),''), users.user_login) LIKE '%{$this->search_value}%' )";
			$search_join  = "INNER JOIN {$this->db->usermeta} first_name ON p.post_author = first_name.user_id AND first_name.meta_key = 'first_name'
             INNER JOIN {$this->db->usermeta} last_name ON p.post_author = last_name.user_id AND last_name.meta_key = 'last_name'";
		}

		return $this->db->get_var(
			"SELECT COUNT(*) as count FROM {$this->db->posts} p $instructor_sql
             INNER JOIN {$this->db->users} users ON p.post_author = users.ID
             {$search_join}
             WHERE p.post_type = '{$this->post_type}' AND p.post_status IN ({$status}) 
             	AND p.post_date BETWEEN '{$this->date_from}' AND '{$this->date_to}' $search_query"
		);
	}
}
