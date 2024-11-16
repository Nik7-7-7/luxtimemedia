<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class BundleSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'number'         => $data['number'] ?? 0,
			'bundle_name'    => $data['name'],
			'courses_inside' => $data['courses_inside'],
			'orders'         => $data['orders'],
			'bundle_id'      => intval( $data['bundle_id'] ),
			'revenue'        => $data['revenue'],
		);
	}
}
