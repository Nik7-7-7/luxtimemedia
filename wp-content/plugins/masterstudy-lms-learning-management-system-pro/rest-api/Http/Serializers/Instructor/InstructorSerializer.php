<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Instructor;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

class InstructorSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'instructor_id' => intval( $data['ID'] ),
			'name'          => $data['name'],
			'enrollments'   => intval( $data['enrollments'] ),
			'own_courses'   => intval( $data['own_courses'] ),
			'reviews'       => intval( $data['reviews'] ),
			'joined'        => gmdate( 'd.m.Y H:i', strtotime( $data['joined'] ) ),
		);
	}
}
