<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class PayoutsSerializer extends AbstractSerializer {
	/**
	 * @param object $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'instructor_revenue' => floatval( $data->instructor_revenue ?? 0 ),
			'admin_comission'    => floatval( $data->amount ?? 0 ) - floatval( $data->instructor_revenue ?? 0 ),
		);
	}
}
