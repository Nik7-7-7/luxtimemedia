<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Student;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

class StudentSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'student_id'  => intval( $data['ID'] ),
			'name'        => $data['name'],
			'enrollments' => intval( $data['enrollments'] ),
			'reviews'     => intval( $data['reviews'] ),
			'joined'      => gmdate( 'd.m.Y H:i', strtotime( $data['joined'] ) ),
		);
	}
}
