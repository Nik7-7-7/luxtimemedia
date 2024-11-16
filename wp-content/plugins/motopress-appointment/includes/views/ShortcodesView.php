<?php

namespace MotoPress\Appointment\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ShortcodesView {

	/**
	 * @var static
	 *
	 * @since 1.2
	 */
	protected static $instance = null;

	/**
	 * @since 1.2.1
	 */
	public function addAppointmentFormActions() {
		// Use similar annotation in all parts of the plugin
		$shortcode_name = mpa_shortcodes()->appointmentForm()->getName();

		// Steps
		add_action( "{$shortcode_name}_shortcode_steps", array( $this, 'appointmentFormStepServiceForm' ), 10 );
		add_action( "{$shortcode_name}_shortcode_steps", array( $this, 'appointmentFormStepPeriod' ), 20 );
		add_action( "{$shortcode_name}_shortcode_steps", array( $this, 'appointmentFormStepCart' ), 30 );
		add_action( "{$shortcode_name}_shortcode_steps", array( $this, 'appointmentFormStepCheckout' ), 40 );
		add_action( "{$shortcode_name}_shortcode_steps", array( $this, 'appointmentFormStepPayment' ), 50 );
		add_action( "{$shortcode_name}_shortcode_steps", array( $this, 'appointmentFormStepBooking' ), 60 );

		// Sections
		add_action( "{$shortcode_name}_checkout_form_fields", array( $this, 'appointmentFormCheckoutFields' ), 10 );
		add_action( "{$shortcode_name}_checkout_bottom_sections", array( $this, 'appointmentFormCheckoutCouponSection' ), 10 );
		add_action( "{$shortcode_name}_checkout_bottom_sections", array( $this, 'appointmentFormCheckoutOrderSection' ), 20 );

		add_action( "{$shortcode_name}_payment_top_sections", array( $this, 'appointmentFormPaymentOrderSection' ), 10 );
		add_action( "{$shortcode_name}_payment_top_sections", array( $this, 'appointmentFormPaymentCouponSection' ), 20 );
	}

	/**
	 * @since 1.2
	 */
	public function addEmployeesListActions() {
		// Use similar annotation in all parts of the plugin
		$template_name = mpa_shortcodes()->employeesList()->getName();

		add_action( "{$template_name}_loop_item", array( __CLASS__, 'employeesListItem' ) );
		add_action( "{$template_name}_item_image", array( __CLASS__, 'employeesListItemImage' ) );
		add_action( "{$template_name}_item_title", array( __CLASS__, 'employeesListItemTitle' ) );
		add_action( "{$template_name}_item_excerpt", array( __CLASS__, 'employeesListItemExcerpt' ) );
		add_action( "{$template_name}_item_attributes", array( __CLASS__, 'employeesListItemContacts' ), 10 );
		add_action( "{$template_name}_item_attributes", array( __CLASS__, 'employeesListItemSocialNetworks' ), 20 );
		add_action( "{$template_name}_item_attributes", array( __CLASS__, 'employeesListItemAdditionalInfo' ), 30 );
		add_action( "{$template_name}_after_loop", array( __CLASS__, 'employeesListPagination' ), 10, 2 );
		add_action( "{$template_name}_not_found", array( __CLASS__, 'employeesListNotFound' ) );
	}

	/**
	 * @since 1.2
	 */
	public function addLocationsListActions() {
		// Use similar annotation in all parts of the plugin
		$template_name = mpa_shortcodes()->locationsList()->getName();

		add_action( "{$template_name}_loop_item", array( __CLASS__, 'locationsListItem' ) );
		add_action( "{$template_name}_item_image", array( __CLASS__, 'locationsListItemImage' ) );
		add_action( "{$template_name}_item_title", array( __CLASS__, 'locationsListItemTitle' ) );
		add_action( "{$template_name}_item_excerpt", array( __CLASS__, 'locationsListItemExcerpt' ) );
		add_action( "{$template_name}_after_loop", array( __CLASS__, 'locationsListPagination' ), 10, 2 );
		add_action( "{$template_name}_not_found", array( __CLASS__, 'locationsListNotFound' ) );
	}

	/**
	 * @since 1.2
	 */
	public function addServicesListActions() {
		// Use similar annotation in all parts of the plugin
		$template_name = mpa_shortcodes()->servicesList()->getName();

		add_action( "{$template_name}_loop_item", array( __CLASS__, 'servicesListItem' ) );
		add_action( "{$template_name}_item_image", array( __CLASS__, 'servicesListItemImage' ) );
		add_action( "{$template_name}_item_title", array( __CLASS__, 'servicesListItemTitle' ) );
		add_action( "{$template_name}_item_excerpt", array( __CLASS__, 'servicesListItemExcerpt' ) );
		add_action( "{$template_name}_item_attributes", array( __CLASS__, 'servicesListItemAttributes' ) );
		add_action( "{$template_name}_item_extra", array( __CLASS__, 'servicesListItemPrice' ), 10 );
		add_action( "{$template_name}_item_extra", array( __CLASS__, 'servicesListItemEmployees' ), 20 );
		add_action( "{$template_name}_after_loop", array( __CLASS__, 'servicesListPagination' ), 10, 2 );
		add_action( "{$template_name}_not_found", array( __CLASS__, 'servicesListNotFound' ) );
	}

	/**
	 * @since 1.2
	 */
	public function addServiceCategoriesActions() {
		// Use similar annotation in all parts of the plugin
		$template_name = mpa_shortcodes()->serviceCategories()->getName();

		add_action( "{$template_name}_loop_term", array( __CLASS__, 'serviceCategoriesTerm' ) );
		add_action( "{$template_name}_term_image", array( __CLASS__, 'serviceCategoriesImage' ) );
		add_action( "{$template_name}_term_name", array( __CLASS__, 'serviceCategoriesName' ) );
		add_action( "{$template_name}_term_description", array( __CLASS__, 'serviceCategoriesDescription' ) );
		add_action( "{$template_name}_term_children", array( __CLASS__, 'serviceCategoriesChildren' ) );
		add_action( "{$template_name}_not_found", array( __CLASS__, 'serviceCategoriesNotFound' ) );
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2.1
	 */
	public function appointmentFormStepServiceForm( $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/step-service-form.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2.1
	 */
	public function appointmentFormStepPeriod( $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/step-period.php',
			$shortcodeArgs
		);
	}

	/**
	 * @since 1.4.0
	 *
	 * @param array $shortcodeArgs
	 */
	public function appointmentFormStepCart( $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/step-cart.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.23.0
	 */
	public function appointmentFormStepCheckout( $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/step-checkout.php',
			$shortcodeArgs
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $shortcodeArgs
	 */
	public function appointmentFormStepPayment( $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/step-payment.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2.1
	 */
	public function appointmentFormStepBooking( $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/step-booking.php',
			$shortcodeArgs
		);
	}

	/**
	 * @since 1.23.0
	 */
	public function appointmentFormCheckoutFields( array $shortcodeArgs ) {
		mpa_display_template(
			'shortcodes/booking/sections/checkout-form-fields.php',
			$shortcodeArgs
		);
	}

	/**
	 * @since 1.11.0
	 */
	public function appointmentFormCheckoutCouponSection( array $shortcodeArgs ) {
		if ( mpapp()->settings()->isCouponsEnabled()
			// Display coupons only in conjunction with the order details template
			&& ! mpapp()->settings()->isPaymentsEnabled()
		) {
			mpa_display_template(
				'shortcodes/booking/sections/coupon-section.php',
				$shortcodeArgs
			);
		}
	}

	/**
	 * @since 1.11.0
	 */
	public function appointmentFormCheckoutOrderSection( array $shortcodeArgs ) {
		// Display order details on the payments step, if payments are enabled
		if ( ! mpapp()->settings()->isPaymentsEnabled() ) {
			mpa_display_template(
				'shortcodes/booking/sections/order-details-section.php',
				$shortcodeArgs
			);
		}
	}

	/**
	 * @since 1.11.0
	 */
	public function appointmentFormPaymentOrderSection( array $shortcodeArgs ) {
		// Display order details on the payments step only if payments are enabled
		if ( mpapp()->settings()->isPaymentsEnabled() ) {
			mpa_display_template(
				'shortcodes/booking/sections/order-details-section.php',
				$shortcodeArgs
			);
		}
	}

	/**
	 * @since 1.11.0
	 */
	public function appointmentFormPaymentCouponSection( array $shortcodeArgs ) {
		if ( mpapp()->settings()->isCouponsEnabled()
			// Display coupons only in conjunction with the order details template
			&& mpapp()->settings()->isPaymentsEnabled()
		) {
			mpa_display_template(
				'shortcodes/booking/sections/coupon-section.php',
				$shortcodeArgs
			);
		}
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItem( $shortcodeArgs ) {
		mpa_display_template(
			'employee/single-item.php',
			'post/single-item.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItemImage( $shortcodeArgs ) {
		mpa_display_template(
			'employee/featured-image.php',
			'post/featured-image.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItemTitle( $shortcodeArgs ) {
		mpa_display_template(
			'employee/title.php',
			'post/title.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItemExcerpt( $shortcodeArgs ) {
		mpa_display_template(
			'employee/excerpt.php',
			'post/excerpt.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItemContacts( $shortcodeArgs ) {
		if ( isset( $shortcodeArgs['show_contacts'] ) && ! $shortcodeArgs['show_contacts'] ) {
			return;
		}

		$templateArgs =
			array(
				'attributes' => 'contacts',
				'class'      => 'mpa-employee-contacts',
			)
			+ $shortcodeArgs;

		mpa_display_template(
			'employee/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItemSocialNetworks( $shortcodeArgs ) {
		if ( isset( $shortcodeArgs['show_social_networks'] ) && ! $shortcodeArgs['show_social_networks'] ) {
			return;
		}

		$templateArgs =
			array(
				'attributes' => 'socialNetworks',
				'class'      => 'mpa-employee-social-networks',
			)
			+ $shortcodeArgs;

		mpa_display_template(
			'employee/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListItemAdditionalInfo( $shortcodeArgs ) {
		if ( isset( $shortcodeArgs['show_additional_info'] ) && ! $shortcodeArgs['show_additional_info'] ) {
			return;
		}

		$templateArgs =
			array(
				'attributes' => 'additionalInfo',
				'class'      => 'mpa-employee-additional-info',
			)
			+ $shortcodeArgs;

		mpa_display_template(
			'employee/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @param \WP_Query $query
	 * @param array $shortcodeArgs Also has a $query argument.
	 *
	 * @since 1.2
	 */
	public static function employeesListPagination( $query, $shortcodeArgs ) {
		mpa_display_template(
			'employee/pagination.php',
			'post/pagination.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function employeesListNotFound( $shortcodeArgs ) {
		mpa_display_template(
			'employee/not-found.php',
			'post/not-found.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function locationsListItem( $shortcodeArgs ) {
		mpa_display_template(
			'location/single-item.php',
			'post/single-item.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function locationsListItemImage( $shortcodeArgs ) {
		mpa_display_template(
			'location/featured-image.php',
			'post/featured-image.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function locationsListItemTitle( $shortcodeArgs ) {
		mpa_display_template(
			'location/title.php',
			'post/title.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function locationsListItemExcerpt( $shortcodeArgs ) {
		mpa_display_template(
			'location/excerpt.php',
			'post/excerpt.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param \WP_Query $query
	 * @param array $shortcodeArgs Also has a $query argument.
	 *
	 * @since 1.2
	 */
	public static function locationsListPagination( $query, $shortcodeArgs ) {
		mpa_display_template(
			'location/pagination.php',
			'post/pagination.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function locationsListNotFound( $shortcodeArgs ) {
		mpa_display_template(
			'location/not-found.php',
			'post/not-found.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItem( $shortcodeArgs ) {
		mpa_display_template(
			'service/single-item.php',
			'post/single-item.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItemImage( $shortcodeArgs ) {
		mpa_display_template(
			'service/featured-image.php',
			'post/featured-image.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItemTitle( $shortcodeArgs ) {
		mpa_display_template(
			'service/title.php',
			'post/title.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItemExcerpt( $shortcodeArgs ) {
		mpa_display_template(
			'service/excerpt.php',
			'post/excerpt.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItemAttributes( $shortcodeArgs ) {
		$service = mpa_get_service();

		// Skip price. We'll show it separatelly on the hook "{$template_name}_item_extra"
		$skipAttributes               = $shortcodeArgs;
		$skipAttributes['show_price'] = false;

		$templateArgs =
			array(
				'attributes' => mpa_get_service_attributes( $service, $skipAttributes ),
			)
			+ $shortcodeArgs;

		mpa_display_template(
			'service/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItemPrice( $shortcodeArgs ) {
		if ( isset( $shortcodeArgs['show_price'] ) && ! $shortcodeArgs['show_price'] ) {
			return;
		}

		mpa_display_template(
			'service/price.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListItemEmployees( $shortcodeArgs ) {
		if ( isset( $shortcodeArgs['show_employees'] ) && ! $shortcodeArgs['show_employees'] ) {
			return;
		}

		mpa_display_template(
			'service/employees.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param \WP_Query $query
	 * @param array $shortcodeArgs Also has a $query argument.
	 *
	 * @since 1.2
	 */
	public static function servicesListPagination( $query, $shortcodeArgs ) {
		mpa_display_template(
			'service/pagination.php',
			'post/pagination.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function servicesListNotFound( $shortcodeArgs ) {
		mpa_display_template(
			'service/not-found.php',
			'post/not-found.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function serviceCategoriesTerm( $shortcodeArgs ) {
		mpa_display_template(
			'service/single-term.php',
			'term/single-term.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function serviceCategoriesImage( $shortcodeArgs ) {
		mpa_display_template(
			'service/term-image.php',
			'term/term-image.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function serviceCategoriesName( $shortcodeArgs ) {
		mpa_display_template(
			'service/term-name.php',
			'term/term-name.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function serviceCategoriesDescription( $shortcodeArgs ) {
		mpa_display_template(
			'service/term-description.php',
			'term/term-description.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function serviceCategoriesChildren( $shortcodeArgs ) {
		mpa_display_template(
			'service/term-children.php',
			'term/term-children.php',
			$shortcodeArgs
		);
	}

	/**
	 * @param array $shortcodeArgs
	 *
	 * @since 1.2
	 */
	public static function serviceCategoriesNotFound( $shortcodeArgs ) {
		mpa_display_template(
			'service/no-categories.php',
			'term/no-categories.php',
			$shortcodeArgs
		);
	}

	/**
	 * @return static
	 *
	 * @since 1.2
	 */
	public static function getInstance() {
		if ( is_null( static::$instance ) ) {
			static::createInstance();
		}

		return static::$instance;
	}

	/**
	 * @since 1.2
	 */
	protected static function createInstance() {
		static::$instance = new static();
	}
}
