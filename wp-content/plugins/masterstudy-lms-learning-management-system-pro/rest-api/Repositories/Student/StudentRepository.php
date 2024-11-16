<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Student;

use MasterStudy\Lms\Pro\RestApi\Repositories\AnalyticsRepository;

class StudentRepository extends AnalyticsRepository {
	public int $user_id;

	public function __construct( int $user_id, string $date_from, string $date_to ) {
		$this->user_id = $user_id;

		parent::__construct( $date_from, $date_to );
	}

	public function get_user_posts_count( string $post_type, array $post_status = array() ): int {
		$post_status_sql = '';

		if ( ! empty( $post_status ) ) {
			$post_status_sql = "AND post_status IN ('" . implode( "','", $post_status ) . "')";
		}

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->posts}
                WHERE post_author = %d AND post_type = %s {$post_status_sql} AND post_date BETWEEN %s AND %s",
				$this->user_id,
				$post_type,
				$this->date_from,
				$this->date_to
			)
		);
	}
}
