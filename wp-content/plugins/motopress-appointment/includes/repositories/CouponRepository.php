<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Coupon;
use MotoPress\Appointment\Utils\ParseUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class CouponRepository extends AbstractRepository {

	/**
	 * @param string $couponCode
	 * @return Coupon|null
	 */
	public function findByCode( string $couponCode ) {
		$queryArgs = array(
			'title'          => $couponCode,
			'posts_per_page' => 1,
		);

		$coupons = $this->findAll( $queryArgs );

		if ( ! empty( $coupons ) ) {
			return reset( $coupons );
		} else {
			return null;
		}
	}

	/**
	 * @param Coupon|int $coupon
	 * @param int $usageCount
	 */
	public function saveUsageCount( $coupon, int $usageCount ) {
		if ( is_object( $coupon ) ) {
			$couponId = $coupon->getId();
		} else {
			$couponId = $coupon;
		}

		update_post_meta( $couponId, '_mpa_usage_count', $usageCount );
	}

	/**
	 * @return array
	 */
	protected function entitySchema() {
		return array(
			'post'     => array( 'ID', 'post_status', 'post_title' ),
			'postmeta' => array(
				'_mpa_description'     => true,
				'_mpa_type'            => true,
				'_mpa_amount'          => true,
				'_mpa_expiration_date' => true,
				'_mpa_services'        => true,
				'_mpa_min_date'        => true,
				'_mpa_max_date'        => true,
				'_mpa_usage_limit'     => true,
				'_mpa_usage_count'     => true,
			),
		);
	}

	/**
	 * @param array $postData
	 * @return Coupon
	 */
	protected function mapPostDataToEntity( $postData ) {
		$id = (int) $postData['ID'];

		$fields = array(
			'status'         => $postData['post_status'],
			'code'           => $postData['post_title'],
			'description'    => $postData['description'],
			'type'           => $postData['type'],
			'amount'         => (float) $postData['amount'],
			'expirationDate' => mpa_parse_date( $postData['expiration_date'], null ),
			'serviceIds'     => ParseUtils::parseIds( $postData['services'] ),
			'minDate'        => mpa_parse_date( $postData['min_date'], null ),
			'maxDate'        => mpa_parse_date( $postData['max_date'], null ),
			'usageLimit'     => (int) $postData['usage_limit'],
			'usageCount'     => (int) $postData['usage_count'],
		);

		return new Coupon( $id, $fields );
	}
}
