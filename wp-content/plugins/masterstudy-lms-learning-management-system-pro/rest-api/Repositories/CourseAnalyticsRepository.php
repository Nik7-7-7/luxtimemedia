<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

class CourseAnalyticsRepository extends AnalyticsRepository {
	public ?int $course_id = null;

	public function __construct( string $date_from, string $date_to, ?int $course_id = null ) {
		$this->course_id = $course_id;

		parent::__construct( $date_from, $date_to );
	}

	public function get_subscribers(): array {
		list( $interval, $date_format ) = $this->get_period_interval_and_format();
		$periods                        = $this->get_date_periods( $interval, $date_format );
		$subscribers                    = array_fill_keys( $periods, 0 );
		$subscribers_count              = 0;
		$coming_soon_emails             = get_post_meta( $this->course_id, 'coming_soon_student_emails', true );

		if ( is_ms_lms_addon_enabled( 'coming_soon' ) && ! empty( $coming_soon_emails ) ) {
			foreach ( $coming_soon_emails as $email ) {
				$period_key = $email['time']->format( $date_format );

				if ( isset( $subscribers[ $period_key ] ) ) {
					$subscribers[ $period_key ]++;
					$subscribers_count++;
				}
			}
		}

		return array(
			'subscribers_count' => $subscribers_count,
			'subscribers'       => array(
				'period' => $periods,
				'values' => array_values( $subscribers ),
			),
		);
	}
}
