<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class RevenueSerializer extends AbstractSerializer {
	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'total_revenue'           => floatval( $data['total_revenue'] ?? 0 ),
			'courses_total'           => floatval( $data['courses_total'] ?? 0 ),
			'bundles_total'           => floatval( $data['bundles_total'] ?? 0 ),
			'new_students_total'      => floatval( $data['new_students_total'] ?? 0 ),
			'existing_students_total' => floatval( $data['existing_students_total'] ?? 0 ),
			'orders_count'            => intval( $data['orders_count'] ?? 0, ),
			'memberships_count'       => intval( $data['memberships_count'] ?? 0 ),
			'earnings'                => array(
				'period' => $data['earnings']['period'] ?? array(),
				'values' => $data['earnings']['values'] ?? array(),
			),
		);
	}
}
