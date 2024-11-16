<?php
namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetReviews extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'user_name'    => array(
				'type'        => 'string',
				'description' => 'User name',
			),
			'course_name'  => array(
				'type'        => 'string',
				'description' => 'Course name',
			),
			'review'       => array(
				'type'        => 'string',
				'description' => 'Review',
			),
			'rating'       => array(
				'type'        => 'number',
				'description' => 'Rating',
			),
			'date_created' => array(
				'type'        => 'string',
				'description' => 'Date created',
			),
			'review_id'    => array(
				'type'        => 'number',
				'description' => 'Review ID',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Reviews';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get All Reviews';
	}
}
