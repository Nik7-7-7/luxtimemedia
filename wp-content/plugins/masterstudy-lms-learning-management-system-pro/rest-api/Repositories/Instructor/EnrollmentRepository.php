<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Instructor;

class EnrollmentRepository extends InstructorRepository {

	public function get_enrollments_data(): array {
		$enrollments = $this->get_enrollments();

		list($interval, $date_format) = $this->get_period_interval_and_format();
		$periods                      = $this->get_date_periods( $interval, $date_format );
		$all_enrollments              = array_fill_keys( $periods, 0 );
		$unique_enrollments           = array_fill_keys( $periods, 0 );
		$enrolled_users               = array();

		if ( ! empty( $enrollments ) ) {
			foreach ( $enrollments as $enrollment ) {
				$enrollment_date = gmdate( $date_format, intval( $enrollment['start_time'] ) );

				$all_enrollments[ $enrollment_date ]++;

				if ( ! in_array( $enrollment['user_id'], $enrolled_users, true ) ) {
					$enrolled_users[] = $enrollment['user_id'];
					$unique_enrollments[ $enrollment_date ]++;
				}
			}
		}

		return array(
			'total_enrollments'  => count( $enrollments ),
			'unique_enrollments' => array_sum( $unique_enrollments ),
			'enrollments'        => array(
				'period' => $periods,
				'all'    => $all_enrollments,
				'unique' => $unique_enrollments,
			),
		);
	}

	public function get_instructor_data() {
		$data        = parent::get_instructor_data();
		$enrollments = $this->get_enrollments();

		$data['enrollments'] = count( $enrollments );
		$data['students']    = count( array_unique( array_column( $enrollments, 'user_id' ) ) );

		return $data;
	}

	public function get_enrollments() {
		return $this->db->get_results(
			$this->db->prepare(
				"SELECT user_id, start_time
				FROM {$this->db->prefix}stm_lms_user_courses
				WHERE course_id IN (SELECT ID FROM {$this->db->posts} WHERE post_author = %d)
				AND start_time BETWEEN %d AND %d",
				$this->instructor_id,
				$this->get_timestamp( $this->date_from ),
				$this->get_timestamp( $this->date_to )
			),
			ARRAY_A
		);
	}
}
