<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Student;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\RestApi\Repositories\CourseCertificateRepository;

class EnrollmentRepository extends StudentRepository {

	public function get_enrollments_data(): array {
		$enrollments = $this->get_user_enrollments();
		$assignments = $this->get_user_assignments();

		$course_certificate_repository  = new CourseCertificateRepository();
		$is_certificate_allowed         = is_ms_lms_addon_enabled( 'certificate_builder' );
		$certificate_threshold          = intval( \STM_LMS_Options::get_option( 'certificate_threshold', 70 ) );
		list( $interval, $date_format ) = $this->get_period_interval_and_format();
		$periods                        = $this->get_date_periods( $interval, $date_format );
		$all_enrollments                = array_fill_keys( $periods, 0 );
		$completed_enrollments          = array_fill_keys( $periods, 0 );
		$not_started_courses            = 0;
		$in_progress_courses            = 0;
		$completed_courses              = 0;
		$certificates                   = 0;

		if ( ! empty( $enrollments ) ) {
			foreach ( $enrollments as $enrollment ) {
				$enrollment_date = gmdate( $date_format, intval( $enrollment['start_time'] ) );
				$progress        = intval( $enrollment['progress_percent'] );

				if ( $progress >= $certificate_threshold ) {
					$completed_enrollments[ $enrollment_date ]++;
					$completed_courses++;

					if ( $is_certificate_allowed && $course_certificate_repository->certificate_allowed( $enrollment['course_id'] ) ) {
						$certificates++;
					}
				} elseif ( 0 === $progress ) {
					$not_started_courses++;
				} else {
					$in_progress_courses++;
				}

				$all_enrollments[ $enrollment_date ]++;
			}
		}

		$data = array(
			'certificates' => $certificates,
			'points'       => class_exists( '\STM_LMS_Point_System' )
				? \STM_LMS_Point_System::total_points( $this->user_id )
				: 0,
			'courses'      => array(
				'enrolled'    => count( $enrollments ),
				'not_started' => $not_started_courses,
				'in_progress' => $in_progress_courses,
				'completed'   => $completed_courses,
			),
			'quizzes'      => $this->get_user_quizzes( wp_list_pluck( $enrollments, 'course_id' ) ),
			'assignments'  => array(
				'in_progress' => masterstudy_lms_count_assignments_by_status( $assignments, 'post_status', 'draft' ),
				'pending'     => masterstudy_lms_count_assignments_by_status( $assignments, 'post_status', 'pending' ),
				'passed'      => masterstudy_lms_count_assignments_by_status( $assignments, 'meta_status', 'passed' ),
				'failed'      => masterstudy_lms_count_assignments_by_status( $assignments, 'meta_status', 'not_passed' ),
			),
			'enrollments'  => array(
				'period'    => $periods,
				'all'       => $all_enrollments,
				'completed' => $completed_enrollments,
			),
		);

		if ( ! $this->is_current_user_instructor() ) {
			$data['groups']  = $this->get_user_posts_count( 'stm-ent-groups' );
			$data['reviews'] = $this->get_user_posts_count( PostType::REVIEW, array( 'publish' ) );
		}

		return $data;
	}

	private function get_user_enrollments() {
		$instructor_courses_clause = $this->is_current_user_instructor()
			? $this->db->prepare(
				"AND course_id IN (
					SELECT ID FROM {$this->db->posts}
					WHERE post_author = %d
				)",
				$this->current_instructor_id
			)
			: '';

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT course_id, progress_percent, start_time FROM {$this->db->prefix}stm_lms_user_courses
				WHERE user_id = %d AND start_time BETWEEN %d AND %d {$instructor_courses_clause}",
				$this->user_id,
				$this->get_timestamp( $this->date_from ),
				$this->get_timestamp( $this->date_to )
			),
			ARRAY_A
		);
	}

	private function get_user_quizzes( ?array $course_ids ): array {
		if ( empty( $course_ids ) ) {
			return array(
				'passed' => 0,
				'failed' => 0,
			);
		}

		$course_ids_placeholder = implode( ',', array_fill( 0, count( $course_ids ), '%d' ) );
		$passed_quizzes         = 0;
		$failed_quizzes         = 0;

		$quizzes = $this->db->get_results(
			$this->db->prepare(
				"SELECT uq.status FROM {$this->db->prefix}stm_lms_user_quizzes uq
				WHERE uq.user_quiz_id IN (
					SELECT MAX(user_quiz_id)
					FROM {$this->db->prefix}stm_lms_user_quizzes
					WHERE user_id = %d AND course_id IN ($course_ids_placeholder)
					GROUP BY user_id, quiz_id
				)",
				array_merge(
					array( $this->user_id ),
					$course_ids
				)
			),
			ARRAY_A
		);

		foreach ( $quizzes as $quiz ) {
			if ( 'passed' === $quiz['status'] ) {
				$passed_quizzes ++;
			} elseif ( 'failed' === $quiz['status'] ) {
				$failed_quizzes ++;
			}
		}

		return array(
			'passed' => $passed_quizzes,
			'failed' => $failed_quizzes,
		);
	}

	private function get_user_assignments(): array {
		$instructor_assignments_clause = $this->is_current_user_instructor()
			? $this->db->prepare(
				"AND pm2.meta_value IN (
					SELECT ID FROM {$this->db->posts}
					WHERE post_author = %d
				)",
				$this->current_instructor_id
			)
			: '';

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT p.ID, p.post_status, pm.meta_value as meta_status 
				FROM {$this->db->posts} p
				INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
				WHERE p.ID IN (
					SELECT MAX(p2.ID) FROM {$this->db->posts} p2
					INNER JOIN {$this->db->postmeta} pm2 ON p2.ID = pm2.post_id
					WHERE p2.post_author = %d AND p2.post_type = %s AND pm2.meta_key = 'assignment_id'
					{$instructor_assignments_clause}
					GROUP BY pm2.meta_value
				) AND pm.meta_key = 'status' AND p.post_date BETWEEN %s AND %s",
				$this->user_id,
				PostType::USER_ASSIGNMENT,
				$this->date_from,
				$this->date_to
			),
			ARRAY_A
		);
	}
}
