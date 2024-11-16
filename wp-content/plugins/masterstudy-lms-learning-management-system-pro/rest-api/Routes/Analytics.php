<?php

use MasterStudy\Lms\Routing\Router;

/** @var Router $router */

// Administrator routes
$router->group(
	array(
		'middleware' => array(
			\MasterStudy\Lms\Routing\Middleware\Authentication::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Middleware\Administrator::class,
		),
		'prefix'     => '/analytics',
	),
	function ( Router $router ) {
		$router->get(
			'/users',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\GetUsersController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\GetUsers::class,
		);
		$router->post(
			'/instructors',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Instructor\GetInstructorsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Instructor\GetInstructors::class,
		);

	}
);

// Administrator and Instructor routes
$router->group(
	array(
		'middleware' => array(
			\MasterStudy\Lms\Routing\Middleware\Authentication::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Middleware\Instructor::class,
		),
		'prefix'     => '/analytics',
	),
	function ( Router $router ) {
		$router->get(
			'/revenue',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetRevenueController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetRevenue::class,
		);
		$router->get(
			'/revenue/payouts',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetPayoutsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetPayouts::class,
		);
		$router->post(
			'/revenue/courses',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetCoursesController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetCourses::class,
		);
		$router->post(
			'/revenue/groups',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetGroupsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetGroups::class,
		);
		$router->post(
			'/revenue/bundles',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetBundlesController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetBundles::class,
		);
		$router->post(
			'/revenue/students',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetStudentsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetStudents::class,
		);
		$router->get(
			'/engagement',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Engagement\GetEngagementController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Engagement\GetEngagement::class,
		);
		$router->post(
			'/engagement/courses',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Engagement\GetCoursesController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Engagement\GetCourses::class,
		);
		$router->post(
			'/engagement/students',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Engagement\GetStudentsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Engagement\GetStudents::class,
		);
		$router->get(
			'/instructor/short-report',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Instructor\GetInstructorReportController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Instructor\GetInstructorReport::class,
		);
		$router->get(
			'/instructor/{instructor_id}/data',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Instructor\GetInstructorDataController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Instructor\GetInstructorData::class,
		);
		$router->post(
			'/instructor/{instructor_id}/courses',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue\GetCoursesController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue\GetCourses::class,
		);
		$router->post(
			'/students',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Student\GetStudentsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student\GetStudents::class,
		);
		$router->get(
			'/course/{course_id}/data',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Course\GetCourseDataController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Course\GetCourseData::class,
		);
		$router->post(
			'/course/{course_id}/lessons',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Course\GetCourseLessonsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Course\GetCourseLessons::class,
		);
		$router->post(
			'/course/{course_id}/lessons-by-users',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Course\GetLessonsByUsersController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Course\GetLessonsByUsers::class,
		);
		$router->get(
			'/reviews-charts',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review\GetReviewChartsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review\GetReviewCharts::class,
		);
		$router->post(
			'/reviews-courses',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review\GetCoursesController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review\GetCourses::class,
		);
		$router->post(
			'/reviews-users',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review\GetUsersController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review\GetUsers::class,
		);
		$router->post(
			'/reviews-{status}',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review\GetReviewsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review\GetReviews::class,
		);
		$router->post(
			'/instructor-students',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Student\GetInstructorStudentsController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student\GetStudents::class,
		);
		$router->get(
			'/student/{user_id}/data',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Student\GetStudentDataController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student\GetStudentData::class,
		);
		$router->post(
			'/student/{user_id}/courses',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Student\GetStudentCoursesController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student\GetStudentCourses::class,
		);
	}
);
