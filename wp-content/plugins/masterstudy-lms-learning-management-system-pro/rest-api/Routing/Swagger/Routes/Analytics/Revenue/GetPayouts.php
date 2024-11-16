<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetPayouts extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'instructor_revenue' => array(
				'type'        => 'integer',
				'description' => 'Instructor Revenue amount.',
			),
			'admin_comission'    => array(
				'type'        => 'integer',
				'description' => 'Admin Comission amount.',
			),
		);
	}

	public function get_summary(): string {
		return 'Get Payouts';
	}

	public function get_description(): string {
		return 'Get Payouts for Instructor and Admin Comission.';
	}
}
