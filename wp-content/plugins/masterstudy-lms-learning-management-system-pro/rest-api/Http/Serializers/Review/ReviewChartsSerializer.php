<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Review;

class ReviewChartsSerializer {

	public function toArray( $data ) {
		return array(
			'total'              => $data['total'],
			'courses_chart'      => $this->serialize_courses_chart( $data['courses_chart'] ),
			'total_by_type'      => $data['total_by_type'],
			'reviews_type_chart' => $this->serialize_reviews_type_chart( $data['reviews_type_chart'] ),
		);
	}

	private function serialize_courses_chart( $courses_chart ) {
		return array(
			'period' => $courses_chart['period'],
			'items'  => array_map(
				function ( $item ) {
					return array(
						'label'  => $item['label'],
						'values' => $item['values'],
					);
				},
				$courses_chart['items']
			),
		);
	}

	private function serialize_reviews_type_chart( $reviews_type_chart ) {
		return array(
			'period' => $reviews_type_chart['period'],
			'items'  => array_map(
				function ( $item ) {
					return array(
						'label'  => $item['label'],
						'values' => $item['values'],
					);
				},
				$reviews_type_chart['items']
			),
		);
	}
}
