<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

use MasterStudy\Lms\Plugin\PostType;

class PayoutRepository extends AnalyticsRepository {
	public function get_payouts() {
		$instructor_join  = '';
		$instructor_where = '';

		if ( $this->is_current_user_instructor() ) {
			$instructor_join  = "LEFT JOIN {$this->db->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'author_payout'";
			$instructor_where = $this->db->prepare( 'AND pm4.meta_value = %d', $this->current_instructor_id );
		}

		return $this->db->get_row(
			$this->db->prepare(
				"SELECT SUM(pm1.meta_value) as amount, SUM(pm2.meta_value) as instructor_revenue 
				FROM {$this->db->posts} p
				LEFT JOIN {$this->db->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'amounts'
				LEFT JOIN {$this->db->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'fee_amounts'
				LEFT JOIN {$this->db->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'status'
				$instructor_join
				WHERE p.post_type = %s 
				AND p.post_status = 'publish' 
				AND pm3.meta_value ='SUCCESS'
			    $instructor_where
				AND p.post_date BETWEEN %s AND %s",
				PostType::PAYOUT,
				$this->date_from,
				$this->date_to
			)
		);
	}
}
