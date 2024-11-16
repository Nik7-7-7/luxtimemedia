<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories;

use MasterStudy\Lms\Plugin\PostType;

class ReviewRepository extends AnalyticsRepository {
	public function get_reviews_data() {
		list( $interval_format, $date_format ) = $this->get_period_interval_and_format();

		$periods           = $this->get_date_periods( $interval_format, $date_format );
		$instructor_filter = '';

		if ( $this->is_current_user_instructor() ) {
			$current_instructor_id = get_current_user_id();
			$instructor_filter = $this->db->prepare(
				"AND EXISTS (
				SELECT 1 
				FROM {$this->db->postmeta} pm_course
				INNER JOIN {$this->db->posts} p_course ON pm_course.meta_value = p_course.ID
				WHERE pm_course.meta_key = 'review_course' 
				AND pm_course.post_id = {$this->db->posts}.ID 
				AND p_course.post_author = %d
			)",
				$current_instructor_id
			);
		}

		$total_reviews = $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->posts} WHERE post_type = %s AND post_status = 'publish' AND post_date BETWEEN %s AND %s $instructor_filter",
				PostType::REVIEW,
				$this->date_from,
				$this->date_to
			)
		);

		$period_reviews     = $this->get_reviews_by_period( $periods, $instructor_filter );
		$total_by_type      = $this->get_reviews_by_type( $this->date_from, $this->date_to );
		$reviews_type_chart = $this->get_reviews_by_type_and_period( $periods );

		return array(
			'total'              => $total_reviews,
			'courses_chart'      => array(
				'period' => array_keys( $period_reviews ),
				'items'  => array(
					array(
						'label'  => 'Reviews',
						'values' => array_values( $period_reviews ),
					),
				),
			),
			'total_by_type'      => $total_by_type,
			'reviews_type_chart' => array(
				'period' => array_keys( $reviews_type_chart['periods'] ),
				'items'  => $reviews_type_chart['items'],
			),
		);
	}

	public function get_reviews_by_period( $periods, $instructor_filter ) {
		$reviews_by_period = array_fill_keys( $periods, 0 );
		$date_format       = $this->is_large_date_range() ? '%%Y-%%m' : '%%m/%%d';

		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT DATE_FORMAT(post_date, '$date_format') AS period, COUNT(*) AS reviews_count
             FROM {$this->db->posts}
             WHERE post_type = %s AND post_status = 'publish' 
             AND post_date BETWEEN %s AND %s
             $instructor_filter
             GROUP BY period",
				PostType::REVIEW,
				$this->date_from,
				$this->date_to
			),
			ARRAY_A
		);

		foreach ( $results as $result ) {
			if ( isset( $reviews_by_period[ $result['period'] ] ) ) {
				$reviews_by_period[ $result['period'] ] = $result['reviews_count'];
			}
		}

		return $reviews_by_period;
	}

	public function get_reviews_by_type( $date_from, $date_to ) {
		$types = array_fill( 1, 5, 0 );

		$instructor_filter = '';
		$query_params      = array( PostType::REVIEW, $date_from, $date_to );

		if ( $this->is_current_user_instructor() ) {
			$current_instructor_id = get_current_user_id();
			$instructor_filter     = "AND EXISTS (
			SELECT 1 
			FROM {$this->db->postmeta} pm_course
			INNER JOIN {$this->db->posts} p_course ON pm_course.meta_value = p_course.ID
			WHERE pm_course.meta_key = 'review_course' 
			AND pm_course.post_id = p.ID
			AND p_course.post_author = %d
		)";
			$query_params[]        = $current_instructor_id;
		}

		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT meta_value AS review_mark, COUNT(*) AS reviews_count
             FROM {$this->db->postmeta} pm
             INNER JOIN {$this->db->posts} p ON pm.post_id = p.ID
             WHERE pm.meta_key = 'review_mark' AND p.post_type = %s AND p.post_status = 'publish' AND p.post_date BETWEEN %s AND %s
             $instructor_filter
             GROUP BY review_mark",
				...$query_params
			),
			ARRAY_A
		);

		foreach ( $results as $result ) {
			$types[ intval( $result['review_mark'] ) ] = $result['reviews_count'];
		}

		return array_values( $types );
	}

	public function get_reviews_by_type_and_period( $periods ) {
		$reviews_by_type_and_period = array(
			'periods' => array_fill_keys( $periods, 0 ),
			'items'   => array(
				array(
					'label'  => '5 stars',
					'values' => array_fill( 0, count( $periods ), 0 ),
				),
				array(
					'label'  => '4 stars',
					'values' => array_fill( 0, count( $periods ), 0 ),
				),
				array(
					'label'  => '3 stars',
					'values' => array_fill( 0, count( $periods ), 0 ),
				),
				array(
					'label'  => '2 stars',
					'values' => array_fill( 0, count( $periods ), 0 ),
				),
				array(
					'label'  => '1 stars',
					'values' => array_fill( 0, count( $periods ), 0 ),
				),
			),
		);

		$instructor_filter = '';
		if ( $this->is_current_user_instructor() ) {
			$current_instructor_id = get_current_user_id();
			$instructor_filter     = $this->db->prepare(
				"AND EXISTS (
				SELECT 1 
				FROM {$this->db->postmeta} pm_course
				INNER JOIN {$this->db->posts} p_course ON pm_course.meta_value = p_course.ID
				WHERE pm_course.meta_key = 'review_course' 
				AND pm_course.post_id = p.ID 
				AND p_course.post_author = %d
			)",
				$current_instructor_id
			);
		}

		$date_format = ( $this->is_large_date_range() ) ? '%%Y-%%m' : '%%m/%%d';

		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT DATE_FORMAT(p.post_date, '$date_format') AS period, pm.meta_value AS review_mark, COUNT(*) AS reviews_count
				FROM {$this->db->posts} p
				INNER JOIN {$this->db->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type = %s 
				AND p.post_status = 'publish' 
				AND pm.meta_key = 'review_mark'
				$instructor_filter
				GROUP BY period, review_mark",
				PostType::REVIEW
			),
			ARRAY_A
		);

		$sorted_periods = array_keys( $reviews_by_type_and_period['periods'] );

		foreach ( $results as $result ) {
			$period = $result['period'];
			$mark   = intval( $result['review_mark'] );
			$count  = intval( $result['reviews_count'] );

			$position = array_search( $period, $sorted_periods, true );

			if ( false !== $position ) {
				$index = 5 - $mark;

				$reviews_by_type_and_period['items'][ $index ]['values'][ $position ] = $count;
			}
		}

		array_walk(
			$reviews_by_type_and_period['items'],
			function ( &$item ) {
				array_walk(
					$item['values'],
					function ( &$value ) {
						$value = $value ?? 0;
					}
				);
			}
		);

		return $reviews_by_type_and_period;
	}

	private function is_large_date_range() {
		$start    = new \DateTime( $this->date_from );
		$end      = new \DateTime( $this->date_to );
		$interval = $start->diff( $end );

		return $interval->m >= 2;
	}
}
