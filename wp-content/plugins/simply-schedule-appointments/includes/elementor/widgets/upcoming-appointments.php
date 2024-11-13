<?php
/**
 * Elementor Upcoming Appointments Widget.
 *
 * Elementor widget that inserts a coverflow-style carousel into the page.
 *
 * @since 1.0.0
 */
class SSA_Elementor_Upcoming_Appointments_Widget extends \Elementor\Widget_Base {

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
		return 'ssa-upcoming-appointments';
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
		return __( 'Upcoming Appointments', 'simply-schedule-appointments' );
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
		return [ 'ssa', 'schedule', 'calendar', 'appointments', 'simply' ];
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
	 * Determines whether to show the Staff section in the Upcoming Appointments editor block.
	 *
	 * Checks if the Staff feature is installed, enabled, and has at least one published staff member.
	 * If these conditions are met, it indicates that the Staff section should be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the Staff section should be shown, false otherwise.
	 */
	public function should_show_staff_section() {
		$is_staff_installed = ssa()->settings_installed->is_installed( 'staff' );
		$is_staff_enabled = ssa()->settings_installed->is_enabled( 'staff' );
		$is_staff_initialized = false;

		if( $is_staff_enabled ) {

			$staff_object_query = ssa()->staff_model->query( array(
				'status' => 'publish',
			));
			
			if (is_array($staff_object_query) && count($staff_object_query) > 0) {
				$is_staff_initialized = true;
			}
		}

		$show_settings_block = false;

		if ( $is_staff_installed && $is_staff_initialized ) {
			$show_settings_block = true;
		}

		return $show_settings_block;
	}

	/**
	 * Determines whether to show the Resources section in the Upcoming Appointments editor block.
	 *
	 * Checks if the Resources feature is installed, enabled, and has Resource that are not of type identical.
	 * If these conditions are met, it indicates that the Resources section should be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the Resources section should be shown, false otherwise.
	 */
	public function should_show_resources_section() {
		$is_resources_installed = ssa()->settings_installed->is_installed( 'resources' );
		$is_resources_enabled = ssa()->settings_installed->is_enabled( 'resources' );
		$is_resources_initialized = false;

		if( $is_resources_enabled ) {

			$resources_object_query = ssa()->resource_group_model->query( array(
				'status' => 'publish',
			));

			$filtered_resources_object = array_values(array_filter($resources_object_query, function($resource) {
				return $resource['resource_type'] !== 'identical';
			}));
			
			if (is_array($filtered_resources_object) && count($filtered_resources_object) > 0) {
				$is_resources_initialized = true;
			}
		}

		$show_settings_block = false;

		if ( $is_resources_installed && $is_resources_initialized ) {
			$show_settings_block = true;
		}

		return $show_settings_block;
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
		/* Block header */
		$this->start_controls_section(
			'upcoming_appointments_block_section',
			[
				'label' => __( 'Block Description', 'simply-schedule-appointments' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$icon_url = $this->get_icon();

		$this->add_control(
			'title',
			[
				'raw' => '<div><i class="' . esc_attr($icon_url) . '"></i><span> Upcoming Appointments</span></div>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
			]
		);

		$this->add_control(
			'description',
			[
				'raw' => '<p>' .esc_html__('Displays Upcoming Appointments. You can select what to show in the appointment card.', 'simply-schedule-appointments') . '</p>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
			]
		);

		$this->end_controls_section();

		/* Display information block */
		$this->start_controls_section(
			'ssa_upcoming_appointments_display',
			[
				'label' => __( 'Display Information', 'simply-schedule-appointments' ),
			]
		);

		$display_option = [
			'Display Appointment Types' => __('Appointment Types', 'simply-schedule-appointments'),
		];
		$default_display_option = key($display_option);

		$this->add_control(
			'appointmentDisplay',
			[
				'label' => esc_html__( $display_option[$default_display_option], 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'textdomain' ),
				'label_off' => esc_html__( 'Hide', 'textdomain' ),
				'return_value' => $default_display_option,
				'default' => '',
			]
		);

		$this->end_controls_section();

		/* Display selectable resources */
		
		if ($this->should_show_resources_section()) {
			$this->start_controls_section(
				'ssa_upcoming_appointments_display_resources',
				[
					'label' => __( 'Display Resources', 'simply-schedule-appointments' ),
				]
			);

			$resource_display_setting = [
				'Disable' => __('Display Resources', 'simply-schedule-appointments'),
			];
	
			$default_resource_display_setting = key($resource_display_setting);
	
			$this->add_control(
				'disable_resources',
				[
					'label' => esc_html__( $default_resource_display_setting, 'simply-schedule-appointments'),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__('Hide', 'simply-schedule-appointments'),
					'label_off' => esc_html__('Show', 'simply-schedule-appointments'),
					'return_value' => $resource_display_setting[$default_resource_display_setting],
					'default' => $resource_display_setting[$default_resource_display_setting],
				]
			);
			
			$all_display_option = [
				'All' => __('All', 'simply-schedule-appointments'),
			];
	
			$default_all_display_setting = key($all_display_option);
	
			$this->add_control(
				'all_resources',
				[
					'label' => esc_html__( $default_all_display_setting, 'simply-schedule-appointments'),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__('Show', 'simply-schedule-appointments'),
					'label_off' => esc_html__('Hide', 'simply-schedule-appointments'),
					'return_value' => $all_display_option[$default_all_display_setting],
					'default' => $all_display_option[$default_all_display_setting],
					'condition' => [
						'disable_resources' => '',
					],
				]
			);

			$appointment_resources = ssa()->resource_group_model->query( array(
				'status' => 'publish'
			));
			$filtered_resources = array_values(array_filter($appointment_resources, function($resource) {
				return $resource['resource_type'] !== 'identical';
			}));
			$ssa_appointment_label_key_values = wp_list_pluck( $filtered_resources, 'title', 'id' );

			$this->add_control(
				'selected_resources',
				[
					'label' => esc_html__('Resources', 'simply-schedule-appointments'),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'options' => $ssa_appointment_label_key_values,
					'label_block' => true,
					'multiple' => true,
					'frontend_available' => true,
					'condition' => [
						'disable_resources' => '',
						'all_resources' => ''
					],
				]
			);
	
			$this->end_controls_section();
		}

		/* Display team member information */
		
		if ($this->should_show_staff_section()) {
			$this->start_controls_section(
				'ssa_upcoming_appointments_display_member_information',
				[
					'label' => __( 'Display Team Member Information', 'simply-schedule-appointments' ),
				]
			);

			$member_information_setting = [
				'Disable' => __('Display Team Members', 'simply-schedule-appointments'),
			];
	
			$default_member_information_setting = key($member_information_setting);
	
			$this->add_control(
				'disable_member_info',
				[
					'label' => esc_html__( $default_member_information_setting, 'simply-schedule-appointments'),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__('Hide', 'simply-schedule-appointments'),
					'label_off' => esc_html__('Show', 'simply-schedule-appointments'),
					'return_value' => $member_information_setting[$default_member_information_setting],
					'default' => $member_information_setting[$default_member_information_setting],
				]
			);
	
			$member_information = [
				'Display Member Names' => __('Team Member Names', 'simply-schedule-appointments'),
				'Display Member Images' => __('Team Member Images', 'simply-schedule-appointments'),
			];
			
			foreach ($member_information as $slug => $label) {
				$this->add_control(
					'toggle_' . $slug,
					[
						'label' => esc_html__('Show ', 'simply-schedule-appointments') . $label,
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__('Show', 'simply-schedule-appointments'),
					'label_off' => esc_html__('Hide', 'simply-schedule-appointments'),
					'return_value' => $slug,
					'default' => '',
					'condition' => [
						'disable_member_info' => '',
					],
					]
				);
			}
	
			$this->end_controls_section();
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

		if ($settings['appointmentDisplay']) {
			$attrs['appointmentDisplay'] = $settings['appointmentDisplay'];
		}

		if ($this->should_show_resources_section()) {
			if( ! empty($settings['disable_resources'])){
				$attrs['resourceDisplay'] = $settings['disable_resources'];
			}
			if ( ! empty( $settings['selected_resources'] ) ) {
				$attrs['resourceTypes'] = $settings['selected_resources'] ;
			} else {
				$attrs['resourceTypes'] = [];
			}
			if($settings['all_resources']){
				$attrs['allResourcesTypeOption'] = $settings['all_resources'];
			}
		} else {
			$attrs['resourceTypes'] = [];
		}

		$member_info = [];

		if ($this->should_show_staff_section()) {
			foreach ($settings as $key => $value) {
				if (strpos($key, 'toggle_') === 0) {
					if ($value && $key !== 'toggle_') {
						$slug = substr($key, 7);
						$member_info[] = $slug;
					}
				}
			}
			if (!empty($settings['disable_member_info'])) {
				$member_info[] = $settings['disable_member_info'];
			}
			if (!empty($member_info)) {
				$attrs['memberInfo'] = $member_info;
			}
		} else {
			$attrs['memberInfo'] = [];
		}
		?>
		<div class="elementor-ssa-upcoming-appointments-wrapper">
			<div <?php echo $this->get_render_attribute_string( 'upcoming_appointments' ); ?>>
				<div class="ssa-upcoming-appointments">
					<?php echo ssa()->shortcodes->ssa_upcoming_appointments( array(
						'block_settings' => $attrs
					) ); ?>
				</div>
			</div>
		</div>
		<?php
	}

}
