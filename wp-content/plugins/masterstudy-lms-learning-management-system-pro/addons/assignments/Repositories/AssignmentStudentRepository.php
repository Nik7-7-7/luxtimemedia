<?php

namespace MasterStudy\Lms\Pro\addons\assignments\Repositories;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\addons\assignments\Assignments;

final class AssignmentStudentRepository {
	const ATTACHMENT_META   = 'student_attachments';
	const STATUS_PASSED     = 'passed';
	const STATUS_NOT_PASSED = 'not_passed';

	public static function get_assignments( $params = array() ) {
		$query_params = array(
			'post_type'      => PostType::USER_ASSIGNMENT,
			'posts_per_page' => intval( $params['per_page'] ?? 10 ),
			'post_status'    => ! empty( $params['post_status'] ) ? $params['post_status'] : array( 'pending', 'publish' ),
			'meta_query'     => array(
				array(
					'key'   => 'assignment_id',
					'value' => $params['assignment_id'],
				),
			),
		);

		if ( ! empty( $params['page'] ) ) {
			$query_params['paged'] = $params['page'];
		}

		if ( ! empty( $params['s'] ) ) {
			$query_params['s'] = sanitize_text_field( $params['s'] );
		}

		if ( ! empty( $params['status'] ) ) {
			if ( 'pending' === $params['status'] ) {
				$query_params['post_status'] = array( 'pending' );
			} else {
				$query_params['meta_query']['relation'] = 'AND';
				$query_params['meta_query'][]           = array(
					'key'   => 'status',
					'value' => sanitize_text_field( $params['status'] ),
				);
			}
		}
		// sorting data.
		if ( ! empty( $params['sortby'] ) && ! empty( $params['sort_order'] ) ) {
			if ( 'date' === $params['sortby'] ) {
				$query_params['orderby'] = $params['sortby'];
				$query_params['order']   = $params['sort_order'];
			} else {
				$query_params['meta_query'][] = array(
					'sorting_clause' => array(
						'key' => $params['sortby'],
					),
				);

				$query_params['orderby'] = array(
					'sorting_clause' => strtoupper( $params['sort_order'] ),
				);
			}
		}

		if ( ! empty( $params['student_id'] ) && empty( $params['status'] ) ) {
			$query_params['meta_query']['relation'] = 'AND';
			$query_params['meta_query'][]           = array(
				'key'   => 'student_id',
				'value' => intval( $params['student_id'] ),
			);
		}

		$query = new \WP_Query( $query_params );

		if ( ! empty( $params['return_query'] ) ) {
			return $query;
		}

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
				$id     = get_the_ID();
				$status = get_post_status( $id );

				if ( ! in_array( $status, array( 'draft', 'pending' ), true ) ) {
					$status = get_post_meta( get_the_ID(), 'status', true );
				}

				$userdata  = get_userdata( get_post_meta( $id, 'student_id', true ) );
				$course_id = get_post_meta( $id, 'course_id', true );

				$assignments['assignments'][] = array(
					'id'          => $id,
					'title'       => str_replace( array( '&#8220;', '&#8221;' ), '"', get_the_title() ),
					'student'     => array(
						'first_name' => $userdata->first_name,
						'last_name'  => $userdata->last_name,
						'email'      => $userdata->user_email,
					),
					'course'      => array(
						'id'    => $course_id,
						'title' => get_the_title( $course_id ),
						'link'  => get_permalink( $course_id ),
					),
					'date'        => get_the_date( 'd.m.Y' ),
					'try_num'     => get_post_meta( $id, 'try_num', true ),
					'status'      => array(
						'slug'  => $status,
						'title' => self::get_status( $status, false ),
					),
					'review_link' => \STM_LMS_User::user_page_url( get_current_user_id() ) . "user-assignment/$id",
				);
			}
		}

		return $assignments;
	}

	public static function enclose_attachment( int $assignment_id, int $attachment_id ): void {
		$attachments   = get_post_meta( $assignment_id, self::ATTACHMENT_META, true );
		$attachments   = ! empty( $attachments ) ? $attachments : array();
		$attachments[] = $attachment_id;

		update_post_meta( $assignment_id, self::ATTACHMENT_META, array_unique( $attachments ) );
	}

	public static function get_display_name( int $assignment_id ): string {
		$student_id   = get_post_meta( $assignment_id, 'student_id', true );
		$student      = get_userdata( $student_id );
		$display_name = \STM_LMS_User::display_name( $student );
		return $display_name ?? '';
	}

	public static function get_status( string $status, $show_icon = true ): string {
		$status = empty( $status ) ? 'pending' : $status;

		$statuses = Assignments::statuses();

		if ( $show_icon ) {
			return "{$statuses[ $status ]['icon']} {$statuses[ $status ]['title'] }";
		}

		return $statuses[ $status ]['title'] ?? '';
	}

	public static function count_by_status( string $status ): ?int {
		$query = new \WP_Query(
			array(
				'post_type'      => PostType::USER_ASSIGNMENT,
				'posts_per_page' => 1,
				'meta_key'       => 'status',
				'meta_value'     => $status,
			)
		);

		return $query->found_posts;
	}

	public static function get_all_students() {
		global $wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} AS pm INNER JOIN {$wpdb->posts} AS p 
                ON pm.post_id = p.ID WHERE pm.meta_key = 'student_id' AND CAST(pm.meta_value AS SIGNED) > 0 AND p.post_type = %s",
				PostType::USER_ASSIGNMENT
			)
		);
	}
}
