<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class StudentSerializer extends AbstractSerializer {
	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		if ( \STM_LMS_Options::get_option( 'wocommerce_checkout', false ) && class_exists( 'WooCommerce' ) ) {
			return array(
				'number'           => $data['number'] ?? 0,
				'student_id'       => intval( $data['ID'] ) ?? 0,
				'courses'          => ( $data['courses'] + ( intval( $data['bundles'] ) / 2 ) + $data['purchased_groups'] ) ?? 0,
				'total_orders'     => $data['total_orders'] ?? 0,
				'name'             => $data['name'],
				'bundles'          => $data['bundle_count'],
				'revenue'          => $data['revenue'] ?? 0,
				'purchased_groups' => $data['purchased_groups'] ?? 0,
			);
		}

		return array(
			'number'           => $data['number'] ?? 0,
			'student_id'       => intval( $data['ID'] ) ?? 0,
			'courses'          => empty( $data['mix_ids'] ) ? 0 : count( explode( ',', $data['mix_ids'] ) ),
			'total_orders'     => $data['total_orders'],
			'name'             => $data['name'],
			'bundles'          => $data['bundles'],
			'revenue'          => $data['revenue'] / 10,
			'purchased_groups' => $this->isEnterpriceGroups( explode( ',', $data['mix_ids'] ), explode( ',', $data['order_ids'] ), ),
		);
	}

	public function isEnterpriceGroups( $post_ids, $order_ids ) {
		$stm_ent_groups_count = 0;

		foreach ( $post_ids as $post_id ) {
			$enterprice_id = get_post_meta( $post_id, 'stm_lms_enterprise_id', true );

			if ( $enterprice_id > 0 && in_array( $post_id, $order_ids, true ) ) {
				$stm_ent_groups_count ++;
			}
		}

		return $stm_ent_groups_count;

	}
}
