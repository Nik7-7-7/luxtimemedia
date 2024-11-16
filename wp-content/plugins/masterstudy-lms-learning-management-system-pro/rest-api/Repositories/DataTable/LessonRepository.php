<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\DataTable;

class LessonRepository extends DataTableAbstractRepository {

	public function get_lessons_data( int $course_id, $columns = array(), $order = array(), $type = 'all' ) {
		$this->apply_sort( $order, $columns, 'CAST(materials.section_id AS SIGNED INTEGER)' );
		$lessons_table              = stm_lms_user_lessons_name( $this->db );
		$curriculum_materials_table = stm_lms_curriculum_materials_name( $this->db );
		$curriculum_sections_table  = stm_lms_curriculum_sections_name( $this->db );
		$user_quizzes_table         = stm_lms_user_quizzes_name( $this->db );

		$ts_from = $this->get_timestamp( $this->date_from );
		$ts_to   = $this->get_timestamp( $this->date_to );

		$where   = array();
		$where[] = array(
			'condition' => 'AND',
			array(
				'column'   => 'materials.section_id',
				'operator' => 'IN',
				'value'    => 'sections.section_id',
			),
		);
		if ( 'all' !== $type ) {
			if ( in_array( $type, array( 'zoom_conference', 'audio', 'video', 'stream' ), true ) ) {
				$where[] = array(
					'condition' => 'AND',
					array( 'materials.post_type' => 'stm-lessons' ),
					array( 'postmeta.meta_value' => $type ),
				);
			} elseif ( 'stm-lessons' === $type ) {
				$where[] = array(
					'condition' => 'AND',
					array( 'materials.post_type' => $type ),
					array(
						'condition' => 'OR',
						array(
							'postmeta.meta_value' => 'text',
						),
						array(
							'column'    => 'postmeta.meta_value',
							'operator'  => 'IS',
							'value_exp' => 'NULL',
						),
					),
				);
			} else {
				$where[] = array( 'materials.post_type' => $type );
			}
		}

		$this->search_column = array( 'posts.post_title' );

		$this->select   = array(
			'materials.post_id AS lesson_id',
			'posts.post_title AS lesson_name',
			'posts.post_date AS date_created',
			'materials.order AS order_field',
			'DATE_FORMAT(posts.post_date, "%d.%m.%Y") AS lesson_date',
			'(CASE 
				WHEN materials.post_type = "stm-quizzes" THEN "quiz" 
		        WHEN materials.post_type = "stm-lessons" THEN 
		            CASE
		                WHEN postmeta.meta_value != "" THEN postmeta.meta_value
		                WHEN posts.post_type = "stm-google-meets" THEN "google meet"
		                ELSE "text"
	                END
		        WHEN materials.post_type = "stm-assignments" THEN "assignment"
		        WHEN materials.post_type = "stm-google-meets" THEN "google meet"
		        ELSE " - " 
			 END) AS lesson_type',
			'(CASE
				WHEN materials.post_type = "stm-assignments" THEN COALESCE(ROUND((COUNT(DISTINCT assignment.post_id) / courses.course_in_users) * 100), 0)
		        WHEN materials.post_type = "stm-quizzes" THEN COALESCE(ROUND((COUNT(quizzes.progress)/courses.course_in_users) * 100), 0)
		        ELSE COALESCE(ROUND((lesson_progress.lessons_started / courses.course_in_users) * 100), 0)
		     END) AS completed',
			'(CASE
				WHEN materials.post_type = "stm-assignments" THEN COALESCE(100 - ROUND((COUNT(DISTINCT assignment.post_id) / courses.course_in_users) * 100), 100)
		        WHEN materials.post_type = "stm-quizzes" THEN COALESCE(ROUND(((courses.course_in_users - COUNT(quizzes.progress))/courses.course_in_users) * 100), 100)
		        ELSE COALESCE(100 - ROUND((lesson_progress.lessons_started / courses.course_in_users) * 100), 100)
		     END) AS dropped',
			'(CASE
				WHEN materials.post_type = "stm-assignments" THEN COALESCE(courses.course_in_users - COUNT(DISTINCT assignment.post_id), 0)
		        WHEN materials.post_type = "stm-quizzes" THEN COALESCE(courses.course_in_users - COUNT(quizzes.progress), 0)
		        ELSE COALESCE(courses.course_in_users - lesson_progress.lessons_started, courses.course_in_users)
		      END) AS not_completed',
			'COALESCE(courses.course_in_users, 0) AS total',
			'CONCAT(materials.section_id, materials.order) as section_id',
		);
		$this->group_by = array(
			'materials.id',
			'postmeta.meta_value',
		);

		$sql          = 'SELECT ' . implode( ',', $this->select ) . " FROM {$curriculum_materials_table} AS materials\n";
		$this->join[] = "LEFT JOIN {$this->db->posts} AS posts ON (posts.ID = materials.post_id)";
		$this->join[] = "LEFT JOIN {$this->db->postmeta} AS postmeta ON (postmeta.post_id = materials.post_id AND postmeta.meta_key = 'type' AND postmeta.meta_value != '')";
		$this->join[] = "LEFT JOIN (
			SELECT id AS section_id, course_id, `order` AS order_field
		    FROM {$curriculum_sections_table}
			ORDER BY order_field
		) AS sections ON sections.course_id = {$course_id}";

		// Calculate assignments
		$this->join[] = "LEFT JOIN (
			SELECT ass.post_id, ass.meta_key, ass.meta_value, meta_ass.meta_value AS lesson_id
			FROM {$this->db->postmeta} ass
			INNER JOIN {$this->db->postmeta} meta ON meta.post_id = ass.post_id AND meta.meta_key = 'status' AND meta.meta_value = 'passed'
			INNER JOIN {$this->db->postmeta} AS meta_ass ON meta_ass.post_id = ass.post_id AND meta_ass.meta_key = 'assignment_id'
		) AS assignment ON assignment.meta_key = 'course_id' AND assignment.meta_value = {$course_id} AND assignment.lesson_id = materials.post_id";

		$this->join[] = "LEFT JOIN (
		    SELECT course_id, COUNT(user_course_id) AS course_in_users
		    FROM {$this->table_courses}
		    WHERE start_time BETWEEN '{$ts_from}' AND '{$ts_to}'
		    GROUP BY course_id
		) AS courses ON courses.course_id = {$course_id}";

		$this->join[] = "LEFT JOIN (
	        SELECT uq.status, uq.course_id, uq.progress, uq.quiz_id, uq.user_id
	        FROM {$user_quizzes_table} uq
		    INNER JOIN (
		        SELECT quiz_id, user_id, MAX(user_quiz_id) as max_id
		        FROM wp_stm_lms_user_quizzes
		        GROUP BY quiz_id, user_id
		    ) AS latest ON uq.user_quiz_id = latest.max_id
		    WHERE uq.status = 'passed'
		) AS quizzes ON quizzes.course_id = {$course_id} AND quizzes.quiz_id = materials.post_id";

		$this->join[] = "LEFT JOIN (
			SELECT course_id, lesson_id, COUNT(user_lesson_id) AS lessons_started
		    FROM {$lessons_table}
		    WHERE start_time >= '{$ts_from}' AND end_time < '{$ts_to}'
		    GROUP BY lesson_id, course_id
		) AS lesson_progress ON lesson_progress.course_id = {$course_id} AND lesson_progress.lesson_id = materials.post_id";

		$sql .= implode( "\n", $this->join ) . "\n";

		$sql .= $this->where_query( $where );

		$sql .= $this->group_query();

		// Add order, limit & offset
		$sql    .= $this->pagination_query();
		$results = $this->db->get_results( $sql, ARRAY_A );
		usort(
			$results,
			function( $a, $b ) {
				if ( $a['section_id'] > $b['section_id'] ) {
					return 1;
				} elseif ( $a['section_id'] < $b['section_id'] ) {
					return -1;
				} else {
					return 0;
				}
			}
		);

		return $results;
	}

	public function get_lessons_by_users( int $course_id, $columns = array(), $order = array(), $sort = 'student_name' ) {
		if ( 'progress_down' === $sort ) {
			$this->apply_sort( $order, $columns, 'progress' );
		}
		if ( 'progress' === $sort ) {
			$this->apply_sort( $order, $columns, 'progress', 'desc' );
		}
		if ( 'student_name' === $sort ) {
			$this->apply_sort( $order, $columns, 'student_name' );
		}

		$lessons_table              = stm_lms_user_lessons_name( $this->db );
		$curriculum_materials_table = stm_lms_curriculum_materials_name( $this->db );
		$curriculum_sections_table  = stm_lms_curriculum_sections_name( $this->db );
		$user_quizzes_table         = stm_lms_user_quizzes_name( $this->db );

		$ts_from = $this->get_timestamp( $this->date_from );
		$ts_to   = $this->get_timestamp( $this->date_to );

		$this->search_column = array( 'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), users.user_login)' );

		$this->select   = array(
			'users.ID AS user_id',
			'COALESCE(NULLIF(CONCAT_WS(" ",first_name.meta_value, last_name.meta_value),""), users.user_login) as student_name',
			'GROUP_CONCAT(materials.completed) as completed',
			'SUM(SUBSTRING_INDEX(materials.completed, "|", -1)) AS progress',
		);
		$this->group_by = array(
			'users.ID',
			'users.user_login',
			'first_name.meta_value',
			'last_name.meta_value',
		);

		$instructor_from = "{$this->db->users} AS users";
		if ( $this->is_current_user_instructor() ) {
			$instructor_from = "(SELECT DISTINCT users.id AS ID, users.user_login AS user_login FROM {$this->db->users} users
				INNER JOIN {$this->db->prefix}stm_lms_user_courses course ON course.user_id = users.ID
				INNER JOIN {$this->db->posts} p ON p.post_type = 'stm-courses' AND p.post_status IN ('publish') AND p.post_author = {$this->current_instructor_id} AND course.course_id = p.ID
				WHERE users.ID != {$this->current_instructor_id}
			) AS users";
		}

		$sql          = 'SELECT ' . implode( ',', $this->select ) . " FROM {$instructor_from}\n";
		$this->join[] = "INNER JOIN {$this->db->usermeta} first_name ON users.ID = first_name.user_id AND first_name.meta_key = 'first_name'";
		$this->join[] = "INNER JOIN {$this->db->usermeta} last_name ON users.ID = last_name.user_id AND last_name.meta_key = 'last_name'";
		$this->join[] = "JOIN (
			SELECT DISTINCT user_id, course_id
		    FROM wp_stm_lms_user_courses
		    GROUP BY user_id, course_id
		) AS courses ON courses.course_id = {$course_id}";

		$this->join[] = "LEFT JOIN (
		    SELECT id AS section_id, course_id, 'order' AS order_field
		    FROM {$curriculum_sections_table}
		    ORDER BY order_field
		) AS sections ON sections.course_id = {$course_id}";

		$this->join[] = "LEFT JOIN (
			SELECT 
		        materials.id,
		        materials.section_id,
		        materials.post_id AS lesson_id,
		        posts.post_title AS lesson_name,
		        (CASE
					WHEN materials.post_type = 'stm-quizzes' THEN (
			            CASE 
			                WHEN quizzes.progress IS NULL THEN CONCAT(materials.post_id, '|0')
			                WHEN quizzes.status = 'passed' THEN CONCAT(materials.post_id, '|1')
			                ELSE CONCAT(materials.post_id, '|-1')
			            END)
		            WHEN materials.post_type = 'stm-assignments' THEN (
		                CASE
		                    WHEN assignment.status = '' THEN CONCAT(materials.post_id, '|-')
		                    WHEN assignment.status = 'passed' THEN CONCAT(materials.post_id, '|1')
		                    WHEN assignment.status = 'not_passed' THEN CONCAT(materials.post_id, '|-1')
		                    ELSE CONCAT(materials.post_id, '|0')
		                END)
					ELSE COALESCE(CONCAT(materials.post_id, '|', lessons.lessons_started), CONCAT(materials.post_id, '|0'))
		        END) AS completed,
    	        inner_users.ID AS user_id
    
            FROM {$curriculum_materials_table} AS materials
    
            LEFT JOIN {$this->db->posts} AS posts ON posts.ID = materials.post_id
    
            JOIN {$this->db->users} AS inner_users
            
            LEFT JOIN (
				SELECT post_author, post_type, post_status, assignment_meta.meta_value AS lesson_id, assignment_status.meta_value AS status
				FROM {$this->db->posts} AS posts
				LEFT JOIN {$this->db->postmeta} AS assignment_meta ON assignment_meta.post_id = posts.ID AND assignment_meta.meta_key = 'assignment_id'
				LEFT JOIN {$this->db->postmeta} AS assignment_status ON assignment_status.post_id = posts.ID AND assignment_status.meta_key = 'status'
				GROUP BY assignment_status.meta_value, post_author, post_type, post_status, assignment_meta.meta_value
			) AS assignment ON assignment.post_author = inner_users.ID AND assignment.post_type = 'stm-user-assignment' AND assignment.lesson_id = materials.post_id
    
		    LEFT JOIN (
		        SELECT course_id, lesson_id, COUNT(user_lesson_id) AS lessons_started, user_id
		        FROM {$lessons_table}
		        WHERE start_time >= '{$ts_from}' AND end_time < '{$ts_to}'
		        GROUP BY lesson_id, course_id, user_id
		    ) AS lessons ON lessons.course_id = {$course_id} AND lessons.lesson_id = materials.post_id AND lessons.user_id = inner_users.ID
    
		    LEFT JOIN (
		        SELECT uq.status, uq.course_id, uq.progress, uq.quiz_id, uq.user_id
		        FROM {$user_quizzes_table} uq
			    INNER JOIN (
			        SELECT quiz_id, user_id, MAX(user_quiz_id) as max_id
			        FROM wp_stm_lms_user_quizzes
			        GROUP BY quiz_id, user_id
			    ) AS latest ON uq.user_quiz_id = latest.max_id
			) AS quizzes ON quizzes.quiz_id = materials.post_id AND quizzes.user_id = inner_users.ID AND quizzes.course_id = {$course_id}
		
            GROUP BY materials.id, quizzes.progress, quizzes.status, lessons.lessons_started, inner_users.ID, assignment.post_status, assignment.status
    
		) AS materials ON materials.section_id IN (sections.section_id) AND materials.user_id = users.ID";

		$sql .= implode( "\n", $this->join ) . "\n";

		$sql .= $this->where_query(
			array(
				array(
					'column'    => 'courses.user_id',
					'operator'  => '=',
					'value_exp' => 'users.ID',
				),
			)
		);
		$sql .= $this->group_query();

		// Add order, limit & offset
		$sql .= $this->pagination_query();

		$results = $this->db->get_results( $sql, ARRAY_A );

		return $results;
	}

	public function get_total_users_lessons( $course_id ) {
		$instructor_from = "{$this->db->users} AS users";
		if ( $this->is_current_user_instructor() ) {
			$instructor_from = "(SELECT DISTINCT users.id AS ID, user_login FROM {$this->db->users} users
				INNER JOIN {$this->db->prefix}stm_lms_user_courses course ON course.user_id = users.ID
				INNER JOIN {$this->db->posts} p ON p.post_type = 'stm-courses' AND p.post_status IN ('publish') AND p.post_author = {$this->current_instructor_id} AND course.course_id = p.ID
				WHERE users.ID != {$this->current_instructor_id}
			) AS users";
		}
		$search_query = '';
		$search_join  = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND COALESCE(NULLIF(CONCAT_WS(' ',first_name.meta_value, last_name.meta_value),''), users.user_login) LIKE '%%{$this->search_value}%%'";
			$search_join  = "INNER JOIN {$this->db->usermeta} first_name ON users.ID = first_name.user_id AND first_name.meta_key = 'first_name'
			INNER JOIN {$this->db->usermeta} last_name ON users.ID = last_name.user_id AND last_name.meta_key = 'last_name'";
		}

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(users.ID) as count
				FROM $instructor_from
				JOIN (
					SELECT DISTINCT user_id,course_id
				    FROM {$this->db->prefix}stm_lms_user_courses
				) AS courses ON courses.course_id = %d
				{$search_join}
				WHERE courses.user_id = users.ID {$search_query}",
				$course_id
			)
		);
	}

	public function get_total_lessons( $course_id ) {
		$curriculum_materials_table = stm_lms_curriculum_materials_name( $this->db );
		$curriculum_sections_table  = stm_lms_curriculum_sections_name( $this->db );

		$search_query = '';
		if ( ! empty( $this->search_value ) ) {
			$search_query = "AND posts.post_title LIKE '%%{$this->search_value}%%'";
		}

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(materials.post_id) AS count
			FROM {$curriculum_materials_table} AS materials 			
			LEFT JOIN (
				SELECT id AS section_id, course_id FROM {$curriculum_sections_table}
			) AS sections ON sections.course_id = %d
			LEFT JOIN {$this->db->posts} AS posts ON (posts.ID = materials.post_id)
			WHERE materials.section_id IN (sections.section_id) $search_query",
				$course_id
			)
		);
	}
}
