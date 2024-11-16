<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Student;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

class StudentCourseSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'name'     => $data['name'],
			'duration' => $data['duration'],
			'started'  => gmdate( 'd.m.Y H:i', intval( $data['started'] ) ),
			'progress' => (int) $data['progress'],
		);
	}
}
