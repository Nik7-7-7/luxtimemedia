<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class AppointmentFormShortcode extends AbstractPostShortcode {

	private $uniqueFormClass = '';
	private $isGlobalStyleAdded = false;

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getName() {
		return 'appointment_form';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Appointment Form', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	public function getAttributes() {
		$appointmentFormAtts = array(
			'form_title'               => array(
				'type'    => 'string',
				'default' => '',
			),
			'show_category'            => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show the service category field in the form.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_service'             => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show the service field in the form.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_location'            => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show the location field in the form.', 'motopress-appointment' ),
				'default'     => true,
			),
			'show_employee'            => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show the employee field in the form.', 'motopress-appointment' ),
				'default'     => true,
			),
			'label_category'           => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Custom label for the service category field.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => esc_html__( 'Service Category', 'motopress-appointment' ),
			),
			'label_service'            => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Custom label for the service field.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => esc_html__( 'Service', 'motopress-appointment' ),
			),
			'label_location'           => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Custom label for the location field.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => esc_html__( 'Location', 'motopress-appointment' ),
			),
			'label_employee'           => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Custom label for the employee field.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => esc_html__( 'Employee', 'motopress-appointment' ),
			),
			'label_unselected'         => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Custom label for the unselected service field.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => esc_html__( '— Select —', 'motopress-appointment' ),
			),
			'label_option'             => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Custom label for the unselected service category, location and employee fields.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => esc_html__( '— Any —', 'motopress-appointment' ),
			),
			'default_category'         => array(
				'type'          => 'string',
				'description'   => esc_html__( 'Slug of the selected service category.', 'motopress-appointment' ),
				'default'       => '',
				'default_label' => null,
			),
			'default_service'          => array(
				'type'          => 'integer',
				'description'   => esc_html__( 'ID of the selected service.', 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => null,
			),
			'default_location'         => array(
				'type'          => 'integer',
				'description'   => esc_html__( 'ID of the selected location.', 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => null,
			),
			'default_employee'         => array(
				'type'          => 'integer',
				'description'   => esc_html__( 'ID of the selected employee.', 'motopress-appointment' ),
				'default'       => 0,
				'default_label' => null,
			),
			'timepicker_columns'       => array(
				'type'        => 'integer',
				'description' => esc_html__( 'The number of columns in the timepicker.', 'motopress-appointment' ),
				'default'     => 3,
			),
			'show_timepicker_end_time' => array(
				'type'        => 'bool',
				'description' => esc_html__( 'Show the time when the appointment ends.', 'motopress-appointment' ),
				'default'     => false,
			),
			'show_add_to_calendar'     => array(
				'type'        => 'bool',
				'description' => esc_html__( "Show 'Add to Your Calendar?' section.", 'motopress-appointment' ),
				'default'     => true,
			),
			'form_width'               => array(
				'type'        => 'string',
				'description' => esc_html__( 'Form width.', 'motopress-appointment' ),
				'default'     => '',
			),
			'primary_color'            => array(
				'type'        => 'string',
				'description' => esc_html__( 'Primary text color.', 'motopress-appointment' ),
				'default'     => '',
			),
			'primary_bg_color'         => array(
				'type'        => 'string',
				'description' => esc_html__( 'Primary background color.', 'motopress-appointment' ),
				'default'     => '',
			),
			'secondary_color'          => array(
				'type'        => 'string',
				'description' => esc_html__( 'Secondary text color.', 'motopress-appointment' ),
				'default'     => '',
			),
			'secondary_bg_color'       => array(
				'type'        => 'string',
				'description' => esc_html__( 'Secondary background color.', 'motopress-appointment' ),
				'default'     => '',
			),
			'buttons_padding'          => array(
				'type'        => 'string',
				'description' => esc_html__( 'Buttons padding.', 'motopress-appointment' ),
				'default'     => '',
			),
		);

		return $appointmentFormAtts + parent::getAttributes();
	}

	protected function addActions() {
		parent::addActions();

		add_filter( "{$this->name}_shortcode_wrapper_class", array( $this, 'filterWrapperClass' ), 10, 2 );
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderContent( $args, $content, $shortcodeTag ) {
		if ( ! is_admin() ) {
			$this->enqueueScripts( $args );
		}

		return mpa_render_template( 'shortcodes/appointment-form.php', $args );
	}

	/**
	 * @param array $validArgs
	 * @param array $postArgs Source values.
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function filterPostArgs( $validArgs, $postArgs ) {
		$filteredArgs = parent::filterPostArgs( $validArgs, $postArgs );

		// Add show_* parameters
		if ( isset( $postArgs['show_items'] ) && is_array( $postArgs['show_items'] ) ) {
			$filteredArgs += array(
				'show_category' => in_array( 'category', $postArgs['show_items'] ),
				'show_service'  => in_array( 'service', $postArgs['show_items'] ),
				'show_location' => in_array( 'location', $postArgs['show_items'] ),
				'show_employee' => in_array( 'employee', $postArgs['show_items'] ),
			);
		}

		return $filteredArgs;
	}

	/**
	 * Enqueue shortcode scripts:
	 * - front-end scripts
	 * - payment gateways api handler scripts
	 *
	 * @param array $args Shortcode attributes.
	 *
	 * @return void
	 */
	protected function enqueueScripts( $args ) {
		if ( ! $this->isGlobalStyleAdded ) {
			$globalStyleSettings = mpapp()->settings()->getFormStylesSettings();

			$this->isGlobalStyleAdded = wp_add_inline_style( 'mpa-public', $this->generateCSS( $globalStyleSettings, true ) );
		}

		wp_add_inline_style( 'mpa-public', $this->generateCSS( $args ) );

		// define customer data for autocompletion on the frontend
		if ( is_user_logged_in() ) {
			$userId   = get_current_user_id();
			$customer = mpapp()->repositories()->customer()->findByUserId( $userId );
			if ( $customer ) {
				mpa_assets()->addLocalizeData(
					'mpa-public',
					'currentCustomer',
					array(
						'name'  => $customer->getName(),
						'email' => $customer->getEmail(),
						'phone' => $customer->getPhone(),
					)
				);

			}
		}

		mpa_assets()->enqueueBundle( 'mpa-public' );

		if ( ! mpapp()->settings()->isPaymentsEnabled() ) {
			return;
		}

		$gateways = mpapp()->payments()->getActive();
		foreach ( $gateways as $gateway ) {
			if ( method_exists( $gateway, 'enqueueScripts' ) ) {
				$gateway->enqueueScripts();
			}
		}
	}

	/**
	 * Generates inline CSS from mpa_form_styles option.
	 * @see includes/bundles/SettingsBundle.php:513
	 *
	 * @param array $args Stortcode attributes.
	 * @param bool $isGlobalStyle Global style flag.
	 *
	 * @return string
	 */
	private function generateCSS( $args, $isGlobalStyle = false ) {

		$cssValues = shortcode_atts( array(
			'form_width' => '',
			'primary_color' => '',
			'primary_bg_color' => '',
			'secondary_color' => '',
			'secondary_bg_color' => '',
			'buttons_padding' => '',
		), $args );

		$mainClass = $isGlobalStyle ? 'appointment-form-shortcode' : $this->uniqueFormClass;

		$selectors = array(
			".{$mainClass} .mpa-booking-step-service-form" => array(
				'width' => 'form_width'
			),
			".{$mainClass} .button-secondary, .{$mainClass} .button-secondary:hover" => array(
				'color' => 'secondary_color',
				'background-color' => 'secondary_bg_color',
				'border-color' => 'secondary_bg_color',
				'padding' => 'buttons_padding',
			),
			".{$mainClass} .button-primary, " .
			".{$mainClass} .mpa-time-period-selected" => array(
				'color' => 'primary_color',
				'background-color' => 'primary_bg_color',
				'border-color' => 'primary_bg_color',
				'padding' => 'buttons_padding',
			),
			".{$mainClass} .flatpickr-day.selected" => array(
				'color' => 'primary_color',
				'background-color' => 'primary_bg_color',
				'border-color' => 'primary_bg_color',
			),
		);

		return mpa_generate_css( $selectors, $cssValues );

	}

	/**
	 * Filters shortcode wrapper class.
	 *
	 * @param string $wrapperClass
	 * @param array $shortcodeArgs
	 *
	 * @return string
	 */
	public function filterWrapperClass( $wrapperClass, $shortcodeArgs ) {

		if ( ! $this->hasCustomStyles( $shortcodeArgs ) ) {
			return $wrapperClass;
		}

		$this->uniqueFormClass = 'appointment-form-' . uniqid();

		return $wrapperClass . ' ' . $this->uniqueFormClass;
	}

	/**
	 * Checks if params have filled attributes needed for custom style generation.
	 *
	 * @param array $shortcodeArgs
	 *
	 * @return bool
	 */
	private function hasCustomStyles( $shortcodeArgs ) {

		$attributes = shortcode_atts( array(
			'form_width' => '',
			'primary_color' => '',
			'primary_bg_color' => '',
			'secondary_color' => '',
			'secondary_bg_color' => '',
			'buttons_padding' => '',
		), $shortcodeArgs );

		foreach ( $attributes as $attribute ) {
			if ( ! empty( $attribute ) ) {
				return true;
			}
		}

		return false;
	}

}
