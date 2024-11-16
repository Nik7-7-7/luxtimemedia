<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

class UserRepository extends AnalyticsRepository {
	public static string $instructor_role = 'stm_lms_instructor';

	public function get_users_data(): array {
		list($interval, $date_format) = $this->get_period_interval_and_format();
		$periods                      = $this->get_date_periods( $interval, $date_format );
		$users                        = array_fill_keys( $periods, 0 );
		$instructors                  = array_fill_keys( $periods, 0 );
		$users_by_date                = $this->get_users_by_date();
		$instructors_by_date          = $this->get_instructors_by_date();
		$total_users                  = 0;
		$total_instructors            = 0;

		foreach ( $users_by_date as $user ) {
			$period_key            = $this->get_period_date( $user['date'], $date_format );
			$users[ $period_key ] += $user['count'];
			$total_users          += $user['count'];
		}

		foreach ( $instructors_by_date as $instructor ) {
			$period_key                  = $this->get_period_date( $instructor['date'], $date_format );
			$instructors[ $period_key ] += $instructor['count'];
			$total_instructors          += $instructor['count'];
		}

		return array(
			'total_users'       => $total_users,
			'total_students'    => $total_users - $total_instructors,
			'total_instructors' => $total_instructors,
			'users'             => array(
				'period' => $periods,
				'values' => array_values( $users ),
			),
			'instructors'       => array(
				'period' => $periods,
				'values' => array_values( $instructors ),
			),
		);
	}

	public function get_users_by_date() {
		return $this->db->get_results(
			$this->db->prepare(
				"SELECT DATE(user_registered) as date, COUNT(*) as count FROM {$this->db->users}
				WHERE user_registered BETWEEN %s AND %s GROUP BY DATE(user_registered)",
				$this->date_from,
				$this->date_to
			),
			ARRAY_A
		);
	}

	public function get_instructors_by_date() {
		return $this->db->get_results(
			$this->db->prepare(
				"SELECT DATE(u.user_registered) as date, COUNT(*) as count 
						FROM {$this->db->users} u
						LEFT JOIN {$this->db->usermeta} um ON u.ID = um.user_id 
						WHERE um.meta_key = %s AND um.meta_value LIKE %s 
						AND u.user_registered BETWEEN %s AND %s 
						GROUP BY DATE(u.user_registered)",
				'wp_capabilities',
				'%\"' . self::$instructor_role . '\"%',
				$this->date_from,
				$this->date_to
			),
			ARRAY_A
		);
	}

	private function get_period_date( $date, $date_format ): string {
		return gmdate( $date_format, strtotime( $date ) );
	}
}
