<?php
/**
 * Simply Schedule Appointments Block Booking.
 *
 * @since   2.4.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Block Booking.
 *
 * @since 2.4.0
 */
class SSA_Block_Booking {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.4.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  2.4.0
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  2.4.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_booking_block' ) );
	}
	
	public function should_render_appointment_type_views() {
		return ssa_is_new_booking_app();
	}

	public function register_booking_block() {
		if ( function_exists( 'register_block_type' ) ) {
			wp_register_script(
				'ssa-booking-block-js',
				$this->plugin->url( 'assets/js/block-booking.js' ),
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
			);
			wp_register_style(
				'ssa-booking-block-css',
				$this->plugin->url( 'assets/css/block-booking.css' )
			);

			$appointment_types = $this->plugin->appointment_type_model->query( array(
				'status' => 'publish',
			) );
			$ssa_appointment_key_values = wp_list_pluck( $appointment_types, 'title', 'slug' );
			asort( $ssa_appointment_key_values );

			wp_localize_script( 'ssa-booking-block-js', 'ssaAppointmentTypes', $ssa_appointment_key_values );

			$appointment_type_labels = $this->plugin->appointment_type_label_model->query();

			$ssa_appointment_label_key_values = wp_list_pluck( $appointment_type_labels, 'name', 'id' );
			asort( $ssa_appointment_label_key_values );

			wp_localize_script( 'ssa-booking-block-js', 'ssaAppointmentTypeLabels', $ssa_appointment_label_key_values );

			/* Booking flow */
			if ( ssa_should_render_booking_flow() ) {
				$booking_flow_options = array(
					'main_booking_flow' => array(
						array(
							'value' => 'appt_type_settings',
							'label' => __('Use default settings from appointment type', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'expanded',
							'label' => __('Expanded', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'express',
							'label' => __('Express', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'first_available',
							'label' => __('First available', 'simply-schedule-appointments'),
						),
					),
					'suggest_first_available' => array(
						'duration' => array(
							'value' => 0,
							'label' => __('Duration', 'simply-schedule-appointments'),
						),
						'duration_unit' => array(
							array(
								'value' => 'minutes',
								'label' => __('Minutes', 'simply-schedule-appointments'),
							),
							array(
								'value' => 'hours',
								'label' => __('Hours', 'simply-schedule-appointments'),
							),
							array(
								'value' => 'days',
								'label' => __('Days', 'simply-schedule-appointments'),
							),
							array(
								'value' => 'weeks',
								'label' => __('Weeks', 'simply-schedule-appointments'),
							),
						),
					),
					'fallback_flow' => array(
						array(
							'value' => 'expanded',
							'label' => __('Expanded', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'express',
							'label' => __('Express', 'simply-schedule-appointments'),
						),
					),
					'date_view' => array(
						array(
							'value' => 'week',
							'label' => __('Weekly', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'month',
							'label' => __('Monthly', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'only_available',
							'label' => __('Only available dates', 'simply-schedule-appointments'),
						),
					),
					'time_view' => array(
						array(
							'value' => 'time_of_day_columns',
							'label' => __('Time of day columns', 'simply-schedule-appointments'),
						),
						array(
							'value' => 'single_column',
							'label' => __('Single column', 'simply-schedule-appointments'),
						),
					)
				);
				wp_localize_script( 'ssa-booking-block-js', 'ssaBookingFlowOptions', $booking_flow_options );
			}
			/* End booking flow */

			/* Appointment types view */
			if ( $this->should_render_appointment_type_views() ) {
				$appointment_types_views_options = array(
					array(
						'value' => 'cardList',
						'label' => __('List', 'simply-schedule-appointments'),
					),
					array(
						'value' => 'cardGrid',
						'label' => __('Grid', 'simply-schedule-appointments'),
					),
					array(
						'value' => 'cardColumns',
						'label' => __('Two Columns', 'simply-schedule-appointments'),
					),
				);
				wp_localize_script( 'ssa-booking-block-js', 'ssaAppointmentTypesViewOptions', $appointment_types_views_options );
			}
			/* End appointment types view */

			register_block_type( 'ssa/booking', array(
				'editor_script' => 'ssa-booking-block-js',
				'editor_style'  => 'ssa-booking-block-css',
				'keywords' => array( 'ssa', 'appointments', 'simply', 'booking' ),
				'attributes' => array (
					'filter' => array (
						'type' => 'string',
						'default' => '',
					),
					'type' => array (
						'type' => 'string',
						'default' => '' ,
					),
					'types' => array (
						'type' => 'array',
						'default' => [] ,
					),
					'label' => array (
						'type' => 'string',
						'default' => '',
					),

					/* Appointment type config */

					'appointment_types_view' => array(
						'type' => 'string',
						'default' => 'cardList',
					),

					/* End appointment type config */

					/* Booking flow config */

					'suggest_first_available' => array (
						'type' => 'string',
						'default' => '',
					),
					'suggest_first_available_duration' => array (
						'type' => 'number',
						'default' => 1,
					),
					'suggest_first_available_duration_unit' =>  array(
						'type' => 'string',
						'default' => 'week',
					),
					'flow' => array (
						'type' => 'string',
						'default' => 'appt_type_settings',
					),
					'fallback_flow' => array (
						'type' => 'string',
						'default' => 'expanded',
					),
					'time_view' => array (
						'type' => 'string',
						'default' => 'time_of_day_columns',
					),
					'date_view' => array (
						'type' => 'string',
						'default' => 'week',
					),

					/* End booking flow config */

					'accent_color' => array (
						'type' => 'string',
						'default' => '',
					),
					'background' => array (
						'type' => 'string',
						'default' => '',
					),
					'padding' => array (
						'type' => 'number',
						'default' => 0,
					),
					'padding_unit' => array (
						'type' => 'string',
						'default' => '',
					),
				),

				'render_callback' => array( $this, 'render' ),
			) );
		}
	}

	function convertDurationToMinutes($duration, $unit) {
		// Validate if the duration is an integer
		$duration = filter_var($duration, FILTER_VALIDATE_INT);

		// If duration is not a valid integer, null, zero, or a negative integer return an error or null
		if ($duration === false || $duration <= 0) {
			return null;
		}

		switch ($unit) {
			case 'minutes':
				return $duration;
			case 'hours':
				return $duration * 60;
			case 'days':
				return $duration * 60 * 24;
			case 'weeks':
				return $duration * 60 * 24 * 7;
			default:
				return null;
		}
	}

	public function render( $settings ) {
		$attrs = array();
		
		if ( $settings['filter'] === 'types' ) {
			$settings['label'] = '';
			
		}
		elseif ( $settings['filter'] === 'label' ) {
			$settings['types'] = [];
			
		}
		if ( ! empty( $settings['types'] ) ) {
			
			if( in_array( 'All', $settings['types'] ) ) {
				$settings['types'] = [];
				$attrs['types'] = '';
			} else {
				$attrs['types'] = implode( ",", $settings['types'] );
			}
		}
		if ( ! empty( $settings['label'] ) ) {
			$attrs['types'] = '';

			if( $settings['label'] !== 'All' ) {
				$attrs['label'] = $settings['label'];
			}
		}
		
		// After introducing the new checkboxes for appointmnent types
		// We still wanna check for the old settings['type'] if set; not to break the existing/old shortcodes
		// If found convert type to types and clear out type
		if( $settings['type'] && $settings['type'] !== '' ) {
			$attrs['types'] = $settings['type'];
			$settings['type'] = '';
		}

		if( $settings['accent_color'] && $settings['accent_color'] !== '' ) {
			$attrs['accent_color'] = ltrim( $settings['accent_color'], '#');
		}
		if( $settings['background'] && $settings['background'] !== '' ) {
			$attrs['background'] = ltrim( $settings['background'], '#' );
		}
		if( $settings['padding'] && $settings['padding'] !== '' ) {
			// using '%' on the dropdown value causes a "malformed URI" issue on Gutenberg renderer, so this is
			// necessary
			$settings['padding_unit'] = $settings['padding_unit'] === 'percent'	? '%' : $settings['padding_unit'];

			$attrs['padding'] = $settings['padding'] . $settings['padding_unit'];
		}

		/* Appointment types view */
		if ( $this->should_render_appointment_type_views() ) {

			if ( $settings['appointment_types_view'] && $settings['appointment_types_view'] !== '' ) {

				$attrs['appointment_types_view'] = $settings['appointment_types_view'];
			}
		}

		/* Booking Flow */
		if ( ssa_should_render_booking_flow() ) {

			if ( $settings['flow'] !== 'appt_type_settings' && ! empty( $settings['flow'] ) ) {
	
				if ( $settings['flow'] === 'expanded' ) {
					$attrs['flow']      = $settings['flow'];
					$attrs['date_view'] = $settings['date_view'];
					$attrs['time_view'] = $settings['time_view'];
				}
	
				else if ( $settings['flow'] === 'express' ) {
					$attrs['flow']      = $settings['flow'];
					$attrs['time_view'] = $settings['time_view'];
				}
	
				else if ( $settings['flow'] === 'first_available' ) {
					$attrs['flow'] = $settings['flow'];
					$attrs['suggest_first_available'] = true;
	
					// Only available within
					$attrs['suggest_first_available_within_minutes'] = $this->convertDurationToMinutes(  $settings['suggest_first_available_duration'],  $settings['suggest_first_available_duration_unit'] );
	
					// Fallback flow
					if ( $settings['fallback_flow'] === 'expanded' ) {
						$attrs['fallback_flow'] = $settings['fallback_flow'];
						$attrs['date_view']     = $settings['date_view'];
						$attrs['time_view']     = $settings['time_view'];
					}
		
					else if ( $settings['fallback_flow'] === 'express' ) {
						$attrs['fallback_flow'] = $settings['fallback_flow'];
						$attrs['time_view']     = $settings['time_view'];
					}
	
				}
			}
		}
		
		return $this->plugin->shortcodes->ssa_booking( $attrs );
	}

}
