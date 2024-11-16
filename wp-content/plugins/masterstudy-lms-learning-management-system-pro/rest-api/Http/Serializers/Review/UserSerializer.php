<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Review;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

class UserSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'name'    => $data['name'],
			'reviews' => intval( $data['reviews'] ),
		);
	}
}
