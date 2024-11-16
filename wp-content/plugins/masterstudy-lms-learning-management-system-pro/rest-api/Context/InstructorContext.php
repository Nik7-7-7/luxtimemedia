<?php

namespace MasterStudy\Lms\Pro\RestApi\Context;

class InstructorContext {
	private static $instance = null;

	private $instructor_id = null;

	private function __construct() {}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// Set Current Instructor ID
	public function set_instructor_id( $instructor_id ) {
		$this->instructor_id = $instructor_id;
	}

	// Get Current Instructor ID
	public function get_instructor_id() {
		return $this->instructor_id;
	}
}
