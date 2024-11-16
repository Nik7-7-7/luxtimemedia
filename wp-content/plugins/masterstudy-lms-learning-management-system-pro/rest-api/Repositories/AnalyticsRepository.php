<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

use MasterStudy\Lms\Pro\RestApi\Context\InstructorContext;

class AnalyticsRepository {
	protected $db;

	protected ?string $date_from;

	protected ?string $date_to;

	protected ?int $current_instructor_id;

	public function __construct( string $date_from = null, string $date_to = null ) {
		global $wpdb;

		$this->db        = $wpdb;
		$this->date_from = gmdate( 'Y-m-d 00:00:00', strtotime( $date_from ) );
		$this->date_to   = gmdate( 'Y-m-d 23:59:59', strtotime( $date_to ) );

		// Get current instructor ID from Middleware context
		$this->current_instructor_id = InstructorContext::get_instance()->get_instructor_id();
	}

	public function get_timestamp( string $date ): int {
		return strtotime( $date );
	}

	public function get_period_interval_and_format(): array {
		$from     = new \DateTime( $this->date_from );
		$to       = new \DateTime( $this->date_to );
		$interval = $from->diff( $to );
		$days     = $interval->days;

		if ( $days <= 61 ) {
			return array( 'P1D', 'm/d' ); // Daily
		} elseif ( $days <= 183 ) {
			return array( 'P1W', 'Y-\WW' ); // Weekly
		} else {
			return array( 'P1M', 'Y-m' ); // Monthly
		}
	}

	public function get_date_periods( $interval_format, $date_format ): array {
		$period = new \DatePeriod(
			new \DateTime( $this->date_from ),
			new \DateInterval( $interval_format ),
			new \DateTime( $this->date_to )
		);

		$periods = array();
		foreach ( $period as $date ) {
			$periods[] = $date->format( $date_format );
		}

		return $periods;
	}

	public function is_current_user_instructor(): bool {
		return null !== $this->current_instructor_id;
	}
}
