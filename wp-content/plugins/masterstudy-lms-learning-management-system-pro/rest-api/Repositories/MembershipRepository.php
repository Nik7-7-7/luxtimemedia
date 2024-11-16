<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

class MembershipRepository extends CourseAnalyticsRepository {
	public function get_count() {
		if ( ! defined( 'PMPRO_VERSION' ) || $this->is_current_user_instructor() ) {
			return 0;
		}

		return $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(DISTINCT user_id) as count FROM {$this->db->prefix}pmpro_membership_orders WHERE timestamp BETWEEN %s AND %s AND status = 'success'",
				$this->date_from,
				$this->date_to
			)
		);
	}
}
