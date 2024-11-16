<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\Checkout;

use MasterStudy\Lms\Pro\RestApi\Repositories\CourseAnalyticsRepository;
use MasterStudy\Lms\Pro\RestApi\Repositories\Instructor\EnrollmentRepository;

abstract class OrderAbstractRepository extends CourseAnalyticsRepository {
	protected array $existing_students = array();

	public function get_revenue() {
		if ( $this->is_current_user_instructor() ) {
			$enrollment_repository = new EnrollmentRepository(
				$this->current_instructor_id,
				$this->date_from,
				$this->date_to
			);
			$orders                = $this->get_instructor_orders( $enrollment_repository->get_instructor_course_ids_for_orders() );
		} else {
			$orders = $this->get_orders();
		}

		$orders_count            = 0;
		$bundles_count           = 0;
		$preorders_count         = 0;
		$total_revenue           = 0;
		$courses_total           = 0;
		$bundles_total           = 0;
		$new_students_total      = 0;
		$existing_students_total = 0;

		list($interval, $date_format) = $this->get_period_interval_and_format();
		$periods                      = $this->get_date_periods( $interval, $date_format );
		$earnings                     = array_fill_keys( $periods, 0 );
		$preorders                    = array_fill_keys( $periods, 0 );

		foreach ( $orders as $order ) {
			$order_date     = $this->get_order_date( $order );
			$is_new_student = empty( $this->course_id ) && $this->is_new_student( $this->get_order_customer_id( $order ), $order_date );

			foreach ( $this->get_order_items( $order ) as $item ) {
				$item_total     = $this->get_item_total( $item );
				$total_revenue += $item_total;
				$is_bundle_item = $this->is_bundle_item( $item );

				if ( $is_bundle_item ) {
					$bundles_total += $item_total;
					$bundles_count++;
				} else {
					$courses_total += $item_total;
				}

				if ( $is_new_student ) {
					$new_students_total += $item_total;
				} else {
					$existing_students_total += $item_total;
				}

				$period_key = $this->format_order_date( $order, $date_format );

				if ( empty( $this->course_id ) || ! $is_bundle_item ) {
					$earnings[ $period_key ] += $item_total;
				}

				// Orders count
				if ( ! empty( $this->course_id ) ) {
					$course_id = $this->get_item_course_id( $item );
					if ( $course_id === $this->course_id ) {
						$orders_count++;
					}
				} else {
					$orders_count++;
				}

				if ( ! empty( $this->course_id ) && $this->is_preorder_item( $item, $order_date ) ) {
					if ( $this->is_group_item( $item ) ) {
						$group_users_count = $this->get_group_users_count( $this->get_item_group_id( $item ) );

						$preorders[ $period_key ] += $group_users_count;
						$preorders_count          += $group_users_count;
					} else {
						$preorders[ $period_key ]++;
						$preorders_count++;
					}
				}
			}
		}

		return array(
			'total_revenue'           => $total_revenue,
			'courses_total'           => $courses_total,
			'bundles_total'           => $bundles_total,
			'new_students_total'      => $new_students_total,
			'existing_students_total' => $existing_students_total,
			'orders_count'            => $orders_count,
			'bundles_count'           => $bundles_count,
			'preorders_count'         => $preorders_count,
			'earnings'                => array(
				'period' => $periods,
				'values' => array_values( $earnings ),
			),
			'preorders'               => array(
				'period' => $periods,
				'values' => array_values( $preorders ),
			),
		);
	}

	public function get_instructor_revenue( $instructor_course_ids ) {
		$orders        = $this->get_instructor_orders( $instructor_course_ids );
		$total_revenue = 0;
		$bundles       = 0;

		list( $interval, $date_format ) = $this->get_period_interval_and_format();

		$periods  = $this->get_date_periods( $interval, $date_format );
		$earnings = array_fill_keys( $periods, 0 );

		foreach ( $orders as $order ) {
			foreach ( $this->get_order_items( $order ) as $item ) {
				$item_total     = $this->get_item_total( $item );
				$total_revenue += $item_total;

				if ( $this->is_bundle_item( $item ) ) {
					$bundles++;
				}

				$period_key               = $this->format_order_date( $order, $date_format );
				$earnings[ $period_key ] += $item_total;
			}
		}

		return array(
			'orders'        => count( $orders ),
			'bundles'       => $bundles,
			'total_revenue' => $total_revenue,
			'earnings'      => array(
				'period' => $periods,
				'values' => array_values( $earnings ),
			),
		);
	}

	public function get_student_revenue( $user_id ) {
		if ( $this->is_current_user_instructor() ) {
			$enrollment_repository = new EnrollmentRepository(
				$this->current_instructor_id,
				$this->date_from,
				$this->date_to
			);

			$orders = $this->get_instructor_orders( $enrollment_repository->get_instructor_course_ids_for_orders(), $user_id );
		} else {
			$orders = $this->get_student_orders( $user_id );
		}

		$revenue = 0;
		$bundles = 0;

		foreach ( $orders as $order ) {
			foreach ( $this->get_order_items( $order ) as $item ) {
				$revenue += $this->get_item_total( $item );

				if ( $this->is_bundle_item( $item ) ) {
					$bundles++;
				}
			}
		}

		return array(
			'revenue' => $revenue,
			'orders'  => count( $orders ),
			'bundles' => $bundles,
		);
	}

	protected function is_new_student( $user_id, $order_date ) {

		$lastest_order = $this->get_user_lastest_order( $user_id, $order_date );

		$this->existing_students[] = $user_id;

		return empty( $lastest_order );
	}

	public function get_bundle_course_ids( $bundle_id ) {
		return get_post_meta( $bundle_id, 'stm_lms_bundle_ids', true );
	}

	public function get_group_users_count( $group_id ) {
		$email = get_post_meta( $group_id, 'emails', true );

		return count( explode( ',', $email ) );
	}
}
