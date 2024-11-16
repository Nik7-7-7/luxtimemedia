<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class GroupSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'number'     => $data['number'] ?? 0,
			'group_name' => $data['name'],
			'students'   => $data['students'],
			'courses'    => $data['courses'],
			'orders'     => $data['orders'],
			'group_id'   => $data['group_id'],
			'revenue'    => $data['revenue'],
		);
	}
}
