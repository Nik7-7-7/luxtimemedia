<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Repository;

class CourseBundleSettings {
	const OPTION_NAME = 'stm_lms_course_bundle_settings';

	private array $settings;

	public function __construct() {
		$this->settings = get_option( self::OPTION_NAME, array() );
	}

	public function get_bundles_limit(): int {
		return intval( $this->settings['bundle_limit'] ?? 6 );
	}

	public function get_bundle_courses_limit(): int {
		return intval( $this->settings['bundle_courses_limit'] ?? 5 );
	}
}
