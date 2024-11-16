<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\RestApi\Repositories\Instructor\EnrollmentRepository;

class EngagementRepository extends CourseAnalyticsRepository {
	private $instructor_course_ids = array();

	public function __construct( string $date_from, string $date_to, ?int $course_id = null ) {
		parent::__construct( $date_from, $date_to, $course_id );

		if ( $this->is_current_user_instructor() ) {
			$this->instructor_course_ids = ( new EnrollmentRepository(
				$this->current_instructor_id,
				$this->date_from,
				$this->date_to
			) )->get_instructor_course_ids();
		}
	}

	public function get_charts_data(): array {
		$enrollments      = $this->get_enrollments();
		$user_assignments = $this->get_user_assignments();

		$course_certificate_repository = new CourseCertificateRepository();
		$is_certificate_allowed        = is_ms_lms_addon_enabled( 'certificate_builder' );
		$certificate_threshold         = intval( \STM_LMS_Options::get_option( 'certificate_threshold', 70 ) );
		list($interval, $date_format)  = $this->get_period_interval_and_format();
		$periods                       = $this->get_date_periods( $interval, $date_format );
		$all_enrollments               = array_fill_keys( $periods, 0 );
		$unique_enrollments            = array_fill_keys( $periods, 0 );
		$enrolled_users                = array();
		$not_started_courses           = 0;
		$in_progress_courses           = 0;
		$completed_courses             = 0;
		$certificates                  = 0;

		if ( ! empty( $enrollments ) ) {
			foreach ( $enrollments as $enrollment ) {
				$enrollment_date = gmdate( $date_format, intval( $enrollment['start_time'] ) );
				$progress        = intval( $enrollment['progress_percent'] );

				$all_enrollments[ $enrollment_date ]++;

				if ( $progress >= $certificate_threshold ) {
					$completed_courses++;

					if ( $is_certificate_allowed && $course_certificate_repository->certificate_allowed( $enrollment['course_id'] ) ) {
						$certificates++;
					}
				} elseif ( 0 === $progress ) {
					$not_started_courses++;
				} else {
					$in_progress_courses++;
				}

				if ( empty( $this->course_id ) && ! in_array( $enrollment['user_id'], $enrolled_users, true ) ) {
					$enrolled_users[] = $enrollment['user_id'];
					$unique_enrollments[ $enrollment_date ]++;
				}
			}
		}

		return array(
			'total_enrollments'  => count( $enrollments ),
			'unique_enrollments' => array_sum( $unique_enrollments ),
			'total_assignments'  => count( $user_assignments ),
			'certificates'       => $certificates,
			'enrollments'        => array(
				'period' => $periods,
				'all'    => $all_enrollments,
				'unique' => $unique_enrollments,
			),
			'courses_by_status'  => array(
				'not_started' => $not_started_courses,
				'in_progress' => $in_progress_courses,
				'completed'   => $completed_courses,
			),
			'assignments'        => array(
				'in_progress' => masterstudy_lms_count_assignments_by_status( $user_assignments, 'post_status', 'draft' ),
				'pending'     => masterstudy_lms_count_assignments_by_status( $user_assignments, 'post_status', 'pending' ),
				'passed'      => masterstudy_lms_count_assignments_by_status( $user_assignments, 'meta_status', 'passed' ),
				'failed'      => masterstudy_lms_count_assignments_by_status( $user_assignments, 'meta_status', 'not_passed' ),
			),
		);
	}

	public function get_all_data(): array {
		$charts_data = $this->get_charts_data();
		$statistics  = array(
			'new_courses'        => $this->get_new_posts( PostType::COURSE ),
			'new_lessons'        => $this->get_new_posts( PostType::LESSON ),
			'new_quizzes'        => $this->get_new_posts( PostType::QUIZ ),
			'new_groups_courses' => $this->get_new_posts_with_meta( PostType::COURSE, 'enterprise_price' ),
			'new_trial_courses'  => $this->get_new_posts_with_meta( PostType::COURSE, 'shareware', 'on' ),
		);

		if ( ! $this->is_current_user_instructor() ) {
			$statistics['new_students'] = $this->get_new_students();
		} else {
			$statistics['new_assignments'] = $this->get_new_posts( PostType::ASSIGNMENT );
		}

		return array_merge( $charts_data, $statistics );
	}

	private function get_enrollments() {
		$check_course_id = ! empty( $this->course_id )
			? $this->db->prepare( 'AND course_id = %d', $this->course_id )
			: '';

		if ( $this->is_current_user_instructor() ) {
			if ( empty( $this->instructor_course_ids ) ) {
				return array();
			}

			$course_ids_placeholder = implode( ',', array_fill( 0, count( $this->instructor_course_ids ), '%d' ) );
			$check_course_id       .= $this->db->prepare(
				" AND course_id IN ($course_ids_placeholder)",
				...$this->instructor_course_ids
			);
		}

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT user_id, course_id, progress_percent, start_time
				FROM {$this->db->prefix}stm_lms_user_courses WHERE start_time BETWEEN %d AND %d {$check_course_id}",
				$this->get_timestamp( $this->date_from ),
				$this->get_timestamp( $this->date_to )
			),
			ARRAY_A
		);
	}

	private function get_user_assignments() {
		$check_course_id = ! empty( $this->course_id )
			? $this->db->prepare( 'AND pm2.meta_value = %d', $this->course_id )
			: '';

		if ( $this->is_current_user_instructor() ) {
			if ( empty( $this->instructor_course_ids ) ) {
				return array();
			}

			$course_ids_placeholder = implode( ',', array_fill( 0, count( $this->instructor_course_ids ), '%d' ) );
			$check_course_id       .= $this->db->prepare(
				" AND pm2.meta_value IN ($course_ids_placeholder)",
				...$this->instructor_course_ids
			);
		}

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT p.ID, p.post_status, pm.meta_value as meta_status FROM {$this->db->posts} p
				INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
				INNER JOIN {$this->db->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'course_id'
				WHERE p.post_type = %s AND pm.meta_key = 'status' {$check_course_id} AND p.post_date BETWEEN %s AND %s",
				PostType::USER_ASSIGNMENT,
				$this->date_from,
				$this->date_to
			),
			ARRAY_A
		);
	}

	public function get_reviews_count() {
		$join_course_id = ! empty( $this->course_id )
			? "INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'review_course'"
			: '';

		$check_course_id = ! empty( $this->course_id )
			? $this->db->prepare( 'AND pm.meta_value = %d', $this->course_id )
			: '';

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->posts} p {$join_course_id}
				WHERE p.post_type = %s AND p.post_status = 'publish' {$check_course_id} AND p.post_date BETWEEN %s AND %s",
				PostType::REVIEW,
				$this->date_from,
				$this->date_to
			)
		);
	}

	private function get_new_students() {
		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->users} WHERE user_registered BETWEEN %s AND %s",
				$this->date_from,
				$this->date_to
			)
		);
	}

	private function get_new_posts( string $post_type, string $additional_sql = '' ) {
		if ( $this->is_current_user_instructor() ) {
			$additional_sql = $this->db->prepare(
				'AND post_author = %d ',
				$this->current_instructor_id
			) . $additional_sql;
		}

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->posts} WHERE post_type = %s AND post_status = 'publish' AND post_date BETWEEN %s AND %s {$additional_sql}",
				$post_type,
				$this->date_from,
				$this->date_to
			)
		);
	}

	private function get_new_posts_with_meta( string $post_type, string $meta_key, string $meta_value = '' ) {
		$meta_value_sql = ! empty( $meta_value ) ? $this->db->prepare( '= %s', $meta_value ) : "!= ''";
		$additional_sql = $this->db->prepare(
			"AND EXISTS (SELECT 1 FROM {$this->db->postmeta} WHERE post_id = ID AND meta_key = %s AND meta_value {$meta_value_sql})",
			$meta_key
		);

		return $this->get_new_posts( $post_type, $additional_sql );
	}
}
