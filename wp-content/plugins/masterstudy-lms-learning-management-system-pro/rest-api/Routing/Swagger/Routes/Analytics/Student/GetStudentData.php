<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetStudentData extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'revenue'      => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue',
			),
			'orders'       => array(
				'type'        => 'integer',
				'description' => 'Total Orders',
			),
			'bundles'      => array(
				'type'        => 'integer',
				'description' => 'Total Bundles',
			),
			'groups'       => array(
				'type'        => 'integer',
				'description' => 'Total Groups',
			),
			'reviews'      => array(
				'type'        => 'integer',
				'description' => 'Total Reviews',
			),
			'certificates' => array(
				'type'        => 'integer',
				'description' => 'Total Certificates',
			),
			'points'       => array(
				'type'        => 'integer',
				'description' => 'Total Points',
			),
			'courses'      => array(
				'type'        => 'object',
				'description' => 'Courses',
				'properties'  => array(
					'enrolled'    => array(
						'type'        => 'array',
						'description' => 'Enrolled Courses',
					),
					'not_started' => array(
						'type'        => 'array',
						'description' => 'Not Started Courses',
					),
					'in_progress' => array(
						'type'        => 'array',
						'description' => 'In Progress Courses',
					),
					'completed'   => array(
						'type'        => 'array',
						'description' => 'Completed Courses',
					),
				),
			),
			'quizzes'      => array(
				'type'        => 'object',
				'description' => 'Quizzes',
				'properties'  => array(
					'passed' => array(
						'type'        => 'array',
						'description' => 'Passed Quizzes',
					),
					'failed' => array(
						'type'        => 'array',
						'description' => 'Failed Quizzes',
					),
				),
			),
			'assignments'  => array(
				'type'        => 'object',
				'description' => 'Assignments',
				'properties'  => array(
					'in_progress' => array(
						'type'        => 'array',
						'description' => 'In Progress Assignments',
					),
					'pending'     => array(
						'type'        => 'array',
						'description' => 'Pending Assignments',
					),
					'passed'      => array(
						'type'        => 'array',
						'description' => 'Passed Assignments',
					),
					'failed'      => array(
						'type'        => 'array',
						'description' => 'Failed Assignments',
					),
				),
			),
			'enrollments'  => array(
				'type'        => 'object',
				'description' => 'Enrollments',
				'properties'  => array(
					'period'    => array(
						'type'        => 'array',
						'description' => 'Period',
					),
					'all     '  => array(
						'type'        => 'array',
						'description' => 'All Enrollments',
					),
					'completed' => array(
						'type'        => 'array',
						'description' => 'Completed Courses',
					),
				),
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Instructor Data';
	}

	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Instructor Revenue and Enrollments Data';
	}
}
