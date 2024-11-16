<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Instructor;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;
use MasterStudy\Lms\Pro\RestApi\Repositories\AnalyticsRepository;

class InstructorRepository extends AnalyticsRepository {
	public int $instructor_id;

	public function __construct( int $instructor_id, string $date_from, string $date_to ) {
		$this->instructor_id = $instructor_id;

		parent::__construct( $date_from, $date_to );
	}

	public function get_instructor_data() {
		$courses_by_date = $this->db->prepare(
			'AND post_date BETWEEN %s AND %s',
			$this->date_from,
			$this->date_to
		);

		$course_ids = $this->get_instructor_course_ids( $courses_by_date );

		return array(
			'courses'      => count( $course_ids ),
			'reviews'      => $this->get_reviews_count( $course_ids ),
			'certificates' => $this->get_posts_count( PostType::CERTIFICATE ),
			'bundles'      => $this->get_posts_count( CourseBundleRepository::POST_TYPE ),
		);
	}

	public function get_instructor_course_ids( string $additional_conditions = '' ) {
		return $this->db->get_col(
			$this->db->prepare(
				"SELECT ID FROM {$this->db->posts} WHERE post_type = %s AND post_author = %d {$additional_conditions}",
				PostType::COURSE,
				$this->instructor_id
			)
		);
	}

	public function get_instructor_course_ids_for_orders() {
		$course_ids = $this->get_instructor_course_ids();

		if ( ! empty( $course_ids ) ) {
			// Prepare each course ID to match its serialized format in stm_lms_bundle_ids
			$serialized_conditions = array();
			foreach ( $course_ids as $course_id ) {
				$serialized_course_id    = 's:' . strlen( $course_id ) . ':"' . $course_id . '";';
				$serialized_conditions[] = $this->db->prepare( 'pm.meta_value LIKE %s', '%' . $serialized_course_id . '%' );
			}
			$serialized_conditions_sql = implode( ' OR ', $serialized_conditions );

			$bundle_ids = $this->db->get_col(
				$this->db->prepare(
					"SELECT ID FROM {$this->db->posts} p
          			LEFT JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id
          			WHERE p.post_type = %s AND pm.meta_key = %s AND ($serialized_conditions_sql)",
					CourseBundleRepository::POST_TYPE,
					'stm_lms_bundle_ids'
				)
			);

			$course_ids = array_merge( $course_ids, $bundle_ids );
		}

		return $course_ids;
	}

	public function get_reviews_count( $course_ids ) {
		if ( empty( $course_ids ) ) {
			return 0;
		}

		$in_clause = implode( ',', array_map( 'intval', $course_ids ) );

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->posts} p
                INNER JOIN {$this->db->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'review_course'
				WHERE p.post_type = %s AND p.post_status = 'publish' AND pm.meta_value IN ($in_clause) AND p.post_date BETWEEN %s AND %s",
				PostType::REVIEW,
				$this->date_from,
				$this->date_to
			)
		);
	}

	public function get_posts_count( string $post_type ) {
		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->posts} p WHERE p.post_type = %s AND p.post_author = %d
				AND p.post_status = 'publish' AND p.post_date BETWEEN %s AND %s",
				$post_type,
				$this->instructor_id,
				$this->date_from,
				$this->date_to
			)
		);
	}
}
