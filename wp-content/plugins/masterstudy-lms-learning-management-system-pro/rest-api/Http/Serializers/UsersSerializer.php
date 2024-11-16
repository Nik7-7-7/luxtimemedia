<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class UsersSerializer extends AbstractSerializer {
	/**
	 * @param object $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'total_users'       => intval( $data['total_users'] ?? 0 ),
			'total_students'    => intval( $data['total_students'] ?? 0 ),
			'total_instructors' => intval( $data['total_instructors'] ?? 0 ),
			'users'             => array(
				'period' => $data['users']['period'] ?? array(),
				'values' => $data['users']['values'] ?? array(),
			),
			'instructors'       => array(
				'period' => $data['instructors']['period'] ?? array(),
				'values' => $data['instructors']['values'] ?? array(),
			),
		);
	}
}
