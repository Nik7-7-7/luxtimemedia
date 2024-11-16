<?php

namespace MasterStudy\Lms\Pro\addons\assignments\Repositories;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Repositories\CurriculumRepository;
use MasterStudy\Lms\Pro\addons\assignments\Repositories\AssignmentStudentRepository;

final class AssignmentTeacherRepository {

	public static function get_assignments( $params = array() ) {
		$assignments_args = array(
			'fields'         => 'ids',
			'post_type'      => PostType::ASSIGNMENT,
			'author'         => get_current_user_id(),
			'paged'          => $params['page'],
			'posts_per_page' => $params['per_page'],
		);

		if ( ! empty( $params['s'] ) ) {
			$assignments_args['s'] = sanitize_text_field( $params['s'] );
		}

		if ( ! empty( $params['status'] ) ) {
			$user_assignment_args = array(
				'post_type'      => PostType::USER_ASSIGNMENT,
				'fields'         => 'ids',
				'posts_per_page' => -1,
			);

			$assignment_status = sanitize_text_field( $params['status'] );

			if ( 'pending' === $assignment_status ) {
				$user_assignment_args['post_status'] = array( $assignment_status );
			} else {
				$user_assignment_args['meta_key']   = 'status';
				$user_assignment_args['meta_value'] = $assignment_status;
			}

			$filtered_assignments = array( 0 );
			$user_assignment_ids  = get_posts( $user_assignment_args );

			if ( ! empty( $user_assignment_ids ) ) {
				foreach ( $user_assignment_ids as $user_assignment_id ) {
					$assignment_id = get_post_meta( $user_assignment_id, 'assignment_id', true );

					if ( $assignment_id > 0 ) {
						$filtered_assignments[] = intval( $assignment_id );
					}
				}
			}

			$assignments_args['post__in'] = array_unique( $filtered_assignments );
		}

		$query = new \WP_Query( $assignments_args );

		$assignments = array(
			'assignments' => array(),
			'page'        => $params['page'],
			'found_posts' => $query->found_posts,
			'per_page'    => $params['per_page'],
			'max_pages'   => $query->max_num_pages,
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$assignment_id                = get_the_ID();
				$assignments['assignments'][] = array(
					'id'         => $assignment_id,
					'title'      => get_the_title(),
					'courses'    => self::related_posts( $assignment_id ),
					'total'      => self::user_assignments_count( $assignment_id ),
					'passed'     => self::user_assignments_count( $assignment_id, AssignmentStudentRepository::STATUS_PASSED ),
					'not_passed' => self::user_assignments_count( $assignment_id, AssignmentStudentRepository::STATUS_NOT_PASSED ),
					'pending'    => self::user_assignments_count( $assignment_id, '', true ),
					'more_link'  => \STM_LMS_User::user_page_url( get_current_user_id() ) . "assignments/{$assignment_id}",
				);
			}
			wp_reset_postdata();
		}

		return $assignments;
	}

	public static function related_posts( $assignment_id ) {
		$course_ids = ( new CurriculumRepository() )->get_lesson_course_ids( $assignment_id );
		$courses    = array();

		if ( ! empty( $course_ids ) ) {
			foreach ( $course_ids as $course_id ) {
				$courses[] = array(
					'id'    => $course_id,
					'title' => get_the_title( $course_id ),
					'link'  => get_the_permalink( $course_id ),
				);
			}
		}

		return $courses;
	}

	public static function user_assignments_count( int $assignment_id, string $assigment_status = '', bool $only_pending = false ): ?int {
		$args = array(
			'post_type'      => PostType::USER_ASSIGNMENT,
			'post_status'    => $only_pending ? array( 'pending' ) : array( 'pending', 'publish', 'future', 'draft' ),
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'assignment_id',
					'value' => $assignment_id,
				),
			),
		);

		if ( ! empty( $assigment_status ) ) {
			$args['meta_query'][] = array(
				'key'   => 'status',
				'value' => $assigment_status,
			);
		}

		$query = new \WP_Query( $args );

		return $query->found_posts;
	}

	public static function total_pending_assignments( int $user_id ): ?int {
		$assignment_ids = get_posts(
			array(
				'fields'         => 'ids',
				'author'         => $user_id,
				'post_type'      => PostType::ASSIGNMENT,
				'posts_per_page' => -1,
			)
		);

		if ( empty( $assignment_ids ) ) {
			return 0;
		}

		$args = array(
			'post_type'      => PostType::USER_ASSIGNMENT,
			'posts_per_page' => 1,
			'post_status'    => array( 'pending' ),
			'meta_query'     => array(
				array(
					'key'     => 'assignment_id',
					'value'   => $assignment_ids,
					'compare' => 'IN',
				),
			),
		);

		$user_assignments = new \WP_Query( $args );

		return $user_assignments->found_posts;
	}
}
