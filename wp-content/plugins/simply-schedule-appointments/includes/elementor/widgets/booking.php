<?php
/**
 * Elementor Upcoming Appointments Widget.
 *
 * Elementor widget that inserts a coverflow-style carousel into the page.
 *
 * @since 1.0.0
 */

class SSA_Elementor_Booking_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve coverflow carousel widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'ssa-booking';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Upcoming Appointments widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Schedule an Appointment', 'simply-schedule-appointments' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Upcoming Appointments widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'ssa', 'schedule', 'calendar', 'appointments', 'simply', 'booking' ];
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Upcoming Appointments widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Retrieve the list of scripts the Upcoming Appointments widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [];
	}

	/**
	 * Retrieve the list of styles the Upcoming Appointments widget depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return array Widget styles dependencies.
	 */
	public function get_style_depends() {
		return [];
	}

	/**
	 * Register Upcoming Appointments widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'block_section',
			[
				'label' => __( 'Block Description', 'simply-schedule-appointments' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$icon_url = $this->get_icon();

		$this->add_control(
			'title',
			[
				'raw' => '<div><i class="' . esc_attr($icon_url) . '"></i><span> Appointment Booking Form</span></div>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
			]
		);

		$this->add_control(
			'description',
			[
				'raw' => '<p>' .esc_html__('Displays an Appointment Booking Form. You can customize the appointment type and styles.', 'simply-schedule-appointments') . '</p>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ssa_booking',
			[
				'label' => __( 'Select Appointment Types', 'simply-schedule-appointments' ),
			]
		);

		$appointment_type_labels = ssa()->appointment_type_label_model->query();
		$ssa_appointment_label_key_values = wp_list_pluck( $appointment_type_labels, 'name', 'id' );
		$ssa_appointment_label_key_values[''] = 'All';
		$ssa_appointment_label_key_values = array_reverse( $ssa_appointment_label_key_values, true );

		$appointment_types = ssa()->appointment_type_model->query( array(
			'number' => -1,
			'status' => 'publish',
		) );

		$filter_options = [
			'types' => __( 'Appointment types', 'simply-schedule-appointments' ),
			'label' => __( 'Label', 'simply-schedule-appointments' ),
		];

		$default_filter = key($filter_options);
		$map_appointment_type_ids_to_labels = wp_list_pluck( $appointment_types, 'title', 'slug' );
		$map_appointment_type_ids_to_labels = array_reverse( $map_appointment_type_ids_to_labels, true );
		$map_appointment_type_ids_to_labels[''] = 'All';
		$map_appointment_type_ids_to_labels = array_reverse( $map_appointment_type_ids_to_labels, true );

		$this->add_control(
			'appointment_types_filter',
			[
				'label' => __( 'FILTER BY', 'simply-schedule-appointments' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => $default_filter,
				'options' => $filter_options,
				'classes' => 'ssa-booking-block',
			]
		);

		$this->add_control(
			'label',
			[
				'label' => __( 'Labels', 'simply-schedule-appointments' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $ssa_appointment_label_key_values,
				'condition' => [
					'appointment_types_filter' => 'label',
				],
			]
		);

		$this->add_control(
			'header',
			[
				'raw' => '<h4>Select Appointment Types:</h4>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'separator' => 'after',
				'condition' => [
					'appointment_types' => 'types',
				],
			]
		);

		$this->add_control(
			'appointment_type',
			[
				'label' => esc_html__('Appointment Types', 'simply-schedule-appointments'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $map_appointment_type_ids_to_labels,
				'label_block' => true,
				'multiple' => true,
				'frontend_available' => true,
				'condition' => [
					'appointment_types_filter' => 'types',
				],
			]
		);

		$this->end_controls_section();

		/* Appointment type view */
		if ( ssa_is_new_booking_app()){
			$this->start_controls_section(
				'section_ssa_booking_type_view',
				[
					'label' => __( 'Appointment Types View', 'simply-schedule-appointments' ),
				]
			);

			$filter_types_options = [
				'cardList' => __('List', 'simply-schedule-appointments'),
				'cardGrid' => __('Grid', 'simply-schedule-appointments'),
				'cardColumns' => __('Two Columns', 'simply-schedule-appointments'),
			];
	
			$default_filter_type = key($filter_types_options);

			$this->add_control(
				'appointment_types_view',
				[
					'label' => __( 'Appointments types view', 'simply-schedule-appointments' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => $default_filter_type,
					'options' => $filter_types_options,
					'label_block' => true,
					'classes' => 'ssa-booking-block',
				]
			);
			
			$this->end_controls_section();

		}

		/* Booking Flow UI*/	
		if ( ssa_should_render_booking_flow()) {
			$this->start_controls_section(
				'section_ssa_booking_flow',
				[
					'label' => __( 'Booking Flow', 'simply-schedule-appointments' ),
				]
			);

			$filter_flow_options = [
				'appt_type_settings' => __('Use default settings from appointment type', 'simply-schedule-appointments'),
				'expanded' => __('Expanded', 'simply-schedule-appointments'),
				'express' => __('Express', 'simply-schedule-appointments'),
				'first_available' => __('First available', 'simply-schedule-appointments'),
			];

			$default_flow_filter = key($filter_flow_options);

			$this->add_control(
				'booking_layout',
				[
					'label' => esc_html__( 'Main Booking Layout', 'simply-schedule-appointments' ),
					'label_block' => true,
					'frontend_available' => true,
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => $default_flow_filter,
					'options' => $filter_flow_options,
				]
			);

			$this->add_control(
				'appt_duration',
				[
					'label' => esc_html__( 'Duration', 'simply-schedule-appointments' ),
					'label_block' => false,
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'default' => 1,
					'condition' => [
						'booking_layout' => 'first_available',
					]
				]
			);

			$filter_duration_units = [
				'minutes' => __('Minutes', 'simply-schedule-appointments'),
				'hours' => __('Hours', 'simply-schedule-appointments'),
				'days' => __('Days', 'simply-schedule-appointments'),
				'weeks' => __('Weeks', 'simply-schedule-appointments'),
			];

			$default_duration_unit = key($filter_duration_units);

			$this->add_control(
				'duration_unit',
				[
					'label' => esc_html__( 'Duration Unit', 'simply-schedule-appointments' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => $default_duration_unit,
					'options' => $filter_duration_units,
					'condition' => [
						'booking_layout' => 'first_available',
					],
				]
			);	

			$filter_fallback_options = [
				'expanded' => __('Expanded', 'simply-schedule-appointments'),
				'express' => __('Express', 'simply-schedule-appointments'),
			];

			$default_fallback_filter = key($filter_fallback_options);

			$this->add_control(
				'fallback_options',
				[
					'label' => esc_html__( 'Fallback Flow', 'simply-schedule-appointments' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => $default_fallback_filter,
					'options' => $filter_fallback_options,
					'condition' => [
						'booking_layout' => 'first_available',
					],
				]
			);

			$this->add_control(
				'view_type',
				[
					'label' => esc_html__( 'View Type', 'simply-schedule-appointments' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'frontend_available' => true,
					'options' => [
						'left' => [
							'title' => esc_html__( 'Date View', 'simply-schedule-appointments' ),
							'icon' => 'eicon-calendar',
						],
						'right' => [
							'title' => esc_html__( 'Time View', 'simply-schedule-appointments' ),
							'icon' => 'eicon-clock-o',
						],
					],
					'default' => 'left',
					'toggle' => true,
					'selectors_dictionary' => [
						'left' => is_rtl() ? 'end' : 'start',
						'right' => is_rtl() ? 'start' : 'end',
					],
					'conditions' => [
						'relation' => 'and',
						'terms' => [
							[
								'name' => 'booking_layout',
								'operator' => '!==',
								'value' => 'express',
							],
							[
								'name' => 'booking_layout',
								'operator' => '!==',
								'value' => 'appt_type_settings',
							],	
						]
					],
				]
			);
					
			$filter_date_options = [
				'week' => __('Weekly', 'simply-schedule-appointments'),
				'month' => __('Monthly', 'simply-schedule-appointments'),
				'only_available' =>__('Only available dates', 'simply-schedule-appointments'),
			];

			$default_date_filter = key($filter_date_options);

			$this->add_control(
				'date_view_options',
				[
					'label' => esc_html__( 'Date View', 'simply-schedule-appointments' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => $default_date_filter,
					'options' => $filter_date_options,
					'conditions' => [
						'relation' => 'and',
						'terms' => [
							[
								'name' => 'view_type',
								'operator' => '===',
								'value' => 'left',
							],
							[
								'relation' => 'and',
								'terms' => [
									[
										'name' => 'booking_layout',
										'operator' => '!==',
										'value' => 'express',
									],
									[
										'name' => 'booking_layout',
										'operator' => '!==',
										'value' => 'appt_type_settings',
									],
								],
							],
						],
					],
				]
			);

			$filter_time_options = [
				'time_of_day_columns' => __('Time of day columns', 'simply-schedule-appointments'),
				'single_column' => __('Single column', 'simply-schedule-appointments'),
			];

			$default_time_filter = key($filter_time_options);

			$this->add_control(
				'time_view_options',
				[
					'label' => esc_html__( 'Time View', 'simply-schedule-appointments' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => $default_time_filter,
					'options' => $filter_time_options,
					'conditions' => [
						'relation' => 'or',
						'terms' => [
							[
								'name' => 'booking_layout',
								'operator' => '===',
								'value' => 'express',
							],
							[
								'relation' => 'and',
								'terms' => [
									[
										'name' => 'view_type',
										'operator' => '===',
										'value' => 'right',
									],
									[
										'name' => 'booking_layout',
										'operator' => '!==',
										'value' => 'appt_type_settings',
									],
								],
							]
						]
					],
				]
			);

			$this->end_controls_section();
		}

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'   => __( 'Accent Color', 'simply-schedule-appointments' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'alpha'   => false,
			]
		);		

		$this->add_control(
			'background_color',
			[
				'label'   => __( 'Background Color', 'simply-schedule-appointments' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'alpha'   => false,
			]
		);		

		$this->add_control(
			'font_family',
			[
				'label'   => __( 'Font Family', 'simply-schedule-appointments' ),
				'type' => \Elementor\Controls_Manager::FONT,
			]
		);		

		$this->add_control(
			'padding',
			[
				'label'   => __( 'Padding', 'simply-schedule-appointments' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'vw', '%' ],	
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1
					],
					'em' => [
						'min' => 0,
						'max' => 100,
						'step' => 1
					],
					'rem' => [
						'min' => 0,
						'max' => 100,
						'step' => 1
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1
					],
				],							
			]
		);		

		$this->end_controls_section();
	}

	public function get_global_color($global_colors, $new_color){
		$new_color_id = substr($new_color, strpos($new_color, 'colors?id=')+10);

		foreach($global_colors as $global_color){
			if($global_color['_id']!= $new_color_id){
				continue;
			}

			$new_color = ltrim($global_color['color'], '#');
		}

		return $new_color;
	}

	/**
	 * Converts time units to minutes
	 *
	 * This function converts time units to minutes based on the duration and unit type passed to the function, returning their equivalent.
	 *
	 * @param int $duration - The amount of time that needs conversion.
	 * @param string $unit  - The time format (minutes, hours, days, weeks).
	 *
	 * @return int|null The amount after conversion, or null if the input is invalid.
	 */
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

	/**
	 * Render Upcoming Appointments widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( $settings['appointment_type'] === 'none' || $settings['appointment_type'] === 'all' ) {
			$settings['appointment_type'] = '';			
		}

		$attrs = array();

		$kit = Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
		$system_colors = $kit->get_settings_for_display( 'system_colors' );
		$custom_colors = $kit->get_settings_for_display( 'custom_colors' );
		$global_colors = array_merge( $system_colors, $custom_colors );

		if( $settings['appointment_type'] && $settings['appointment_type'] !== '') {
			if( is_array($settings['appointment_type'])){
				$attrs['types'] = implode(',', $settings['appointment_type']);
			}else {
				$attrs['types'] = $settings['appointment_type'];
				$settings['appointment_type'] = '';
			}
		}

		if( $settings['label'] && $settings['label'] !== '') {
			$attrs['label'] = $settings['label'];
		}

		if ( ssa_is_new_booking_app() ) {

			if ( $settings['appointment_types_view'] && $settings['appointment_types_view'] !== '' ) {

				$attrs['appointment_types_view'] = $settings['appointment_types_view'];
			}
		}

		if ( ssa_should_render_booking_flow() ) {

			if ( $settings['booking_layout'] !== 'appt_type_settings' && !empty( $settings['booking_layout'] ) ) {
		
				if ( $settings['booking_layout'] === 'expanded' ) {
					$attrs['flow']      = $settings['booking_layout'];
					$attrs['date_view'] = $settings['date_view_options'];
					$attrs['time_view'] = $settings['time_view_options'];
				}
	
				else if ( $settings['booking_layout'] === 'express' ) {
					$attrs['flow']      = $settings['booking_layout'];
					$attrs['time_view'] = $settings['time_view_options'];
				}
	
				else if ( $settings['booking_layout'] === 'first_available' ) {
					$attrs['flow'] = $settings['booking_layout'];
					$attrs['suggest_first_available'] = true;
	
					// Only available within
					$attrs['suggest_first_available_within_minutes'] = $this->convertDurationToMinutes(  $settings['appt_duration'],  $settings['duration_unit'] );
	
					// Fallback flow
					if ( $settings['fallback_options'] === 'expanded' ) {
						$attrs['fallback_flow'] = $settings['fallback_options'];
						$attrs['date_view']     = $settings['date_view_options'];
						$attrs['time_view']     = $settings['time_view_options'];
					}
		
					else if ( $settings['fallback_options'] === 'express' ) {
						$attrs['fallback_flow'] = $settings['fallback_options'];
						$attrs['time_view']     = $settings['time_view_options'];
					}
	
				}
			}
		}

		if( $settings['accent_color'] && $settings['accent_color'] !== '' ) {
			$attrs['accent_color'] = ltrim( $settings['accent_color'], '#');
		} else if ( ! empty( $settings['__globals__']['accent_color'] ) ) {
			$attrs['accent_color'] = $this->get_global_color($global_colors, $settings['__globals__']['accent_color']);
		}
		if( $settings['background_color'] && $settings['background_color'] !== '' ) {
			$attrs['background'] = ltrim( $settings['background_color'], '#' );
		} else if (!empty($settings['__globals__']['background_color'])) {
			$attrs['background'] = $this->get_global_color($global_colors, $settings['__globals__']['background_color']);
		}
		if( $settings['font_family'] && $settings['font_family'] !== '' ) {
			$attrs['font'] = $settings['font_family'];
		}
		if( $settings['padding'] && $settings['padding'] !== '' ) {
			$attrs['padding'] = $settings['padding']['size'] . $settings['padding']['unit'];
		}
		?>
		<div class="elementor-ssa-booking-wrapper">
			<div <?php echo $this->get_render_attribute_string( 'booking' ); ?>>
				<div class="ssa-booking">
					<?php echo ssa()->shortcodes->ssa_booking( $attrs ); ?>
				</div>
			</div>
		</div>
		<?php
	}

}
