<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetGroups extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'group_name' => array(
				'type'        => 'string',
				'description' => 'Group name',
			),
			'students'   => array(
				'type'        => 'integer',
				'description' => 'Students in Group',
			),
			'courses'    => array(
				'type'        => 'integer',
				'description' => 'Courses in Group',
			),
			'orders'     => array(
				'type'        => 'integer',
				'description' => 'Orders in Group',
			),
			'group_id'   => array(
				'type'        => 'integer',
				'description' => 'Group ID',
			),
			'revenue'    => array(
				'type'        => 'float',
				'description' => 'Group Revenue',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Groups';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Revenue Groups list';
	}
}
