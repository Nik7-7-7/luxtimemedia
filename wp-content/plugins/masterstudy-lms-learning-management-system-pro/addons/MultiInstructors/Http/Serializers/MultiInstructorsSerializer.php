<?php

namespace MasterStudy\Lms\Pro\addons\MultiInstructors\Http\Serializers;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class MultiInstructorsSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'courses'     => $data['courses'] ?? array(),
			'pagination'  => $data['pagination'] ?? '',
			'total_pages' => intval( $data['total_pages'] ?? 1 ),
		);
	}
}
