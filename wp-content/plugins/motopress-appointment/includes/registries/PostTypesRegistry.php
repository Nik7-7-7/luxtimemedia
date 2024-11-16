<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\PostTypes\Taxonomy;
use MotoPress\Appointment\PostTypes;
use MotoPress\Appointment\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class PostTypesRegistry {

	/**
	 * @var PostTypes\AbstractPostType[]
	 *
	 * @since 1.0
	 */
	protected $postTypes = array();

	/**
	 * @param string $postType
	 * @return PostTypes\AbstractPostType|null
	 *
	 * @since 1.0
	 */
	public function getPostType( $postType ) {
		$typeId = mpa_unprefix( $postType );

		if ( method_exists( $this, $typeId ) ) {
			return $this->$typeId();
		} else {
			return null;
		}
	}

	/**
	 * @since 1.7.0
	 */
	public function getPostTypes(): array {
		return $this->postTypes;
	}

	/**
	 * @return PostTypes\EmployeePostType
	 *
	 * @since 1.0
	 */
	public function employee() {
		if ( ! isset( $this->postTypes['employee'] ) ) {
			$this->postTypes['employee'] = new PostTypes\EmployeePostType();

			// Add pseudo template for single post page
			Views\PostTypesView::getInstance()->addEmployeeActions();
			new Views\PostTypePseudoTemplate( PostTypes\EmployeePostType::POST_TYPE );
		}

		return $this->postTypes['employee'];
	}

	/**
	 * @return PostTypes\SchedulePostType
	 *
	 * @since 1.0
	 */
	public function schedule() {
		if ( ! isset( $this->postTypes['schedule'] ) ) {
			$this->postTypes['schedule'] = new PostTypes\SchedulePostType();
		}

		return $this->postTypes['schedule'];
	}

	/**
	 * @return PostTypes\LocationPostType
	 *
	 * @since 1.0
	 */
	public function location() {
		if ( ! isset( $this->postTypes['location'] ) ) {
			$this->postTypes['location'] = new PostTypes\LocationPostType();
		}

		return $this->postTypes['location'];
	}

	/**
	 * @return PostTypes\ServicePostType
	 *
	 * @since 1.0
	 */
	public function service() {
		if ( ! isset( $this->postTypes['service'] ) ) {
			$this->postTypes['service'] = new PostTypes\ServicePostType();

			// Add pseudo template for single post page
			Views\PostTypesView::getInstance()->addServiceActions();
			new Views\PostTypePseudoTemplate( PostTypes\ServicePostType::POST_TYPE );

			// Add Featured Image to service categories
			new Taxonomy\EditTermFeaturedImage(
				'featured_image',
				$this->postTypes['service']->getCategory(),
				array(
					'label' => esc_html__( 'Featured image', 'motopress-appointment' ),
				)
			);
		}

		return $this->postTypes['service'];
	}

	/**
	 * @return PostTypes\BookingPostType
	 *
	 * @since 1.0
	 */
	public function booking() {
		if ( ! isset( $this->postTypes['booking'] ) ) {
			$this->postTypes['booking'] = new PostTypes\BookingPostType();
		}

		return $this->postTypes['booking'];
	}

	/**
	 * @return PostTypes\ReservationPostType
	 *
	 * @since 1.0
	 */
	public function reservation() {
		if ( ! isset( $this->postTypes['reservation'] ) ) {
			$this->postTypes['reservation'] = new PostTypes\ReservationPostType();
		}

		return $this->postTypes['reservation'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return PostTypes\PaymentPostType
	 */
	public function payment() {
		if ( ! isset( $this->postTypes[ __FUNCTION__ ] ) ) {
			$this->postTypes[ __FUNCTION__ ] = new PostTypes\PaymentPostType();
		}

		return $this->postTypes[ __FUNCTION__ ];
	}

	/**
	 * @return PostTypes\ShortcodePostType
	 *
	 * @since 1.2
	 */
	public function shortcode() {
		if ( ! isset( $this->postTypes['shortcode'] ) ) {
			$this->postTypes['shortcode'] = new PostTypes\ShortcodePostType();
		}

		return $this->postTypes['shortcode'];
	}

	/**
	 * @return PostTypes\CouponPostType
	 *
	 * @since 1.11.0
	 */
	public function coupon() {
		if ( ! isset( $this->postTypes[ __FUNCTION__ ] ) ) {
			$this->postTypes[ __FUNCTION__ ] = new PostTypes\CouponPostType();
		}

		return $this->postTypes[ __FUNCTION__ ];
	}

	/**
	 * @return PostTypes\NotificationPostType
	 *
	 * @since 1.13.0
	 */
	public function notification() {
		if ( ! isset( $this->postTypes[ __FUNCTION__ ] ) ) {
			$this->postTypes[ __FUNCTION__ ] = new PostTypes\NotificationPostType();
		}

		return $this->postTypes[ __FUNCTION__ ];
	}

	/**
	 * @since 1.0
	 */
	public function registerAll() {
		$this->booking();
		$this->payment();
		$this->employee();
		$this->location();
		$this->schedule();
		$this->service();
		$this->reservation();
		$this->coupon();
		$this->notification();
		$this->shortcode();
	}
}
