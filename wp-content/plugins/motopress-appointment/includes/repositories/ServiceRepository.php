<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Service;
use MotoPress\Appointment\PostTypes\ServicePostType;
use MotoPress\Appointment\Structures\Service\ServiceVariation;
use MotoPress\Appointment\Structures\Service\ServiceVariations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 * @see Service
 */
class ServiceRepository extends AbstractRepository {

	/**
	 * @since 1.0
	 *
	 * @return array
	 */
	protected function entitySchema() {
		return array(
			'post'     => array( 'ID', 'post_title', 'post_content' ),
			'postmeta' => array(
				'_mpa_price'                               => true,
				'_mpa_duration'                            => true,
				'_mpa_custom_time_step'                    => true,
				'_mpa_buffer_time_before'                  => true,
				'_mpa_buffer_time_after'                   => true,
				'_mpa_time_before_booking'                 => true,
				'_mpa_max_advance_time_before_reservation' => true,
				'_mpa_is_group_service'                    => true,
				'_mpa_min_capacity'                        => true,
				'_mpa_max_capacity'                        => true,
				'_mpa_multiply_price'                      => true,
				'_mpa_custom_quantity_label'               => true,
				'_mpa_color'                               => true,
				'_mpa_employees'                           => true,
				'_mpa_variations'                          => true,
				'_mpa_notification_notice_1'               => true,
				'_mpa_notification_notice_2'               => true,
				'_mpa_deposit_type'                        => true,
				'_mpa_deposit_amount'                      => true,
			),
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param array $postData
	 * @return Service
	 *
	 * @todo Move upgrade code to plugin upgrader.
	 */
	protected function mapPostDataToEntity( $postData ) {
		$id = (int) $postData['ID'];

		// $minCapacity = $maxCapacity = 0 for existing services without
		// postmetas (upgrade 1.3 to 1.4), so we need to set the minimum values
		$minCapacity = max( 1, (int) $postData['min_capacity'] );
		$maxCapacity = max( $minCapacity, (int) $postData['max_capacity'] );
		$color       = $postData['color'] ? $postData['color'] : Service::DEFAULT_COLOR;
		$depositType = $postData['deposit_type'] ? $postData['deposit_type'] : Service::DEPOSIT_TYPES[0];

		$fieldsData = array(
			'employeeIds'                     => array_map( 'mpa_posint', (array) $postData['employees'] ),
			'title'                           => $postData['post_title'],
			'description'                     => $postData['post_content'],
			'price'                           => (float) $postData['price'],
			'duration'                        => ! empty( $postData['duration'] ) ? (int) $postData['duration'] : 0,
			'customTimeStep'                  => ! empty( $postData['custom_time_step'] ) ? (int) $postData['custom_time_step'] : 0,
			'bufferTimeBefore'                => ! empty( $postData['buffer_time_before'] ) ? (int) $postData['buffer_time_before'] : 0,
			'bufferTimeAfter'                 => ! empty( $postData['buffer_time_after'] ) ? (int) $postData['buffer_time_after'] : 0,
			'timeBeforeBooking'               => null,
			'maxAdvanceTimeBeforeReservation' => null,
			'isGroupService'                  => (bool) $postData['is_group_service'],
			'minCapacity'                     => $minCapacity,
			'maxCapacity'                     => $maxCapacity,
			'multiplyPrice'                   => (bool) $postData['multiply_price'],
			'customQuantityLabel'             => $postData['custom_quantity_label'],
			'color'                           => $color,
			'notificationNotices'             => array(
				1 => $postData['notification_notice_1'],
				2 => $postData['notification_notice_2'],
			),
			'depositType'                     => $depositType,
			'depositAmount'                   => (float) $postData['deposit_amount'],
		);

		try {

			if ( ! empty( $postData['time_before_booking'] ) ) {

				$fieldsData['timeBeforeBooking'] = new \DateInterval( $postData['time_before_booking'] );
			}
			// phpcs:ignore
		} catch ( \Throwable $e ) {
			// ignore parse exception
		}

		try {

			if ( ! empty( $postData['max_advance_time_before_reservation'] ) ) {

				$fieldsData['maxAdvanceTimeBeforeReservation'] = new \DateInterval( $postData['max_advance_time_before_reservation'] );
			}
			// phpcs:ignore
		} catch ( \Throwable $e ) {
			// ignore parse exception
		}

		$fieldsData['variations'] = $this->buildVariations( $postData, $fieldsData );

		return new Service( $id, $fieldsData );
	}

	/**
	 * @since 1.3.1
	 *
	 * @param array $postData
	 * @param array $fieldsData
	 * @return ServiceVariations
	 *
	 * @todo Move upgrade code to plugin upgrader.
	 */
	protected function buildVariations( $postData, $fieldsData ) {

		if ( ! is_array( $postData['variations'] ) || empty( $postData['variations'] ) ) {

			return new ServiceVariations( array() );
		}

		$variations = array();

		foreach ( $postData['variations'] as $variationData ) {

			// Add optional fields (upgrade 1.3 to 1.4)
			$variationData += array(
				'min_capacity' => 1,
				'max_capacity' => 1,
			);

			if ( '' === $variationData['price'] ) {
				$variationData['price'] = $fieldsData['price'];
			}

			if ( '' === $variationData['min_capacity'] ) {
				$variationData['min_capacity'] = $fieldsData['minCapacity'];
			}

			if ( '' === $variationData['max_capacity'] ) {
				$variationData['max_capacity'] = $fieldsData['maxCapacity'];
			}

			$variationData['max_capacity'] = max( $variationData['min_capacity'], $variationData['max_capacity'] );

			// Add variation
			$employeeId                = (int) $variationData['employee'];
			$variations[ $employeeId ] = new ServiceVariation( $variationData );
		}

		return new ServiceVariations( $variations );
	}

	/**
	 * @since 1.0
	 *
	 * @param array $args Optional. Additional arguments for function get_terms().
	 *     [] by default.
	 * @return array [Term ID => WP_Term]
	 */
	public function findCategories( $args = array() ) {
		return $this->getCategories( ServicePostType::CATEGORY_NAME, $args );
	}

	/**
	 * @since 1.2
	 *
	 * @param int $employeeId
	 * @param array $args Optional.
	 * @return array
	 */
	public function findAllByEmployee( $employeeId, $args = array() ) {
		$employeesMeta = mpa_prefix( 'employees', 'private' );

		return $this->findAllByValueInMeta( $employeesMeta, $employeeId, $args );
	}
}
