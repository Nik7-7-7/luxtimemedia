<?php

namespace MotoPress\Appointment\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
class WidgetsView {

	/**
	 * @var static
	 *
	 * @since 1.3
	 */
	protected static $instance = null;

	/**
	 * @since 1.3
	 */
	public function addAppointmentFormActions() {
		add_action( 'appointment_form_widget_steps', array( $this, 'appointmentFormStepServiceForm' ), 10 );
		add_action( 'appointment_form_widget_steps', array( $this, 'appointmentFormStepPeriod' ), 20 );
		add_action( 'appointment_form_widget_steps', array( $this, 'appointmentFormStepCart' ), 30 );
		add_action( 'appointment_form_widget_steps', array( $this, 'appointmentFormStepCheckout' ), 40 );
		add_action( 'appointment_form_widget_steps', array( $this, 'appointmentFormStepPayment' ), 50 );
		add_action( 'appointment_form_widget_steps', array( $this, 'appointmentFormStepBooking' ), 60 );
	}

	/**
	 * @param array $widgetArgs
	 *
	 * @since 1.3
	 */
	public function appointmentFormStepServiceForm( $widgetArgs ) {
		mpa_display_template(
			'widgets/booking/step-service-form.php',
			'shortcodes/booking/step-service-form.php',
			$widgetArgs
		);
	}

	/**
	 * @param array $widgetArgs
	 *
	 * @since 1.3
	 */
	public function appointmentFormStepPeriod( $widgetArgs ) {
		mpa_display_template(
			'widgets/booking/step-period.php',
			'shortcodes/booking/step-period.php',
			$widgetArgs
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $widgetArgs
	 */
	public function appointmentFormStepCart( $widgetArgs ) {
		mpa_display_template(
			'widgets/booking/step-cart.php',
			'shortcodes/booking/step-cart.php',
			$widgetArgs
		);
	}

	/**
	 * @param array $widgetArgs
	 *
	 * @since 1.3
	 */
	public function appointmentFormStepCheckout( $widgetArgs ) {
		mpa_display_template(
			'widgets/booking/step-checkout.php',
			'shortcodes/booking/step-checkout.php',
			$widgetArgs
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $widgetArgs
	 */
	public function appointmentFormStepPayment( $widgetArgs ) {
		mpa_display_template(
			'widgets/booking/step-payment.php',
			'shortcodes/booking/step-payment.php',
			$widgetArgs
		);
	}

	/**
	 * @param array $widgetArgs
	 *
	 * @since 1.3
	 */
	public function appointmentFormStepBooking( $widgetArgs ) {
		mpa_display_template(
			'widgets/booking/step-booking.php',
			'shortcodes/booking/step-booking.php',
			$widgetArgs
		);
	}

	/**
	 * @return static
	 *
	 * @since 1.3
	 */
	public static function getInstance() {
		if ( is_null( static::$instance ) ) {
			static::createInstance();
		}

		return static::$instance;
	}

	/**
	 * @since 1.3
	 */
	protected static function createInstance() {
		static::$instance = new static();
	}
}
