<?php
/**
 * Simply Schedule Appointments Block Upcoming Appointments.
 *
 * @since   3.2.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Block Upcoming Appointments.
 *
 * @since 3.2.0
 */
class SSA_Block_Upcoming_Appointments {
	/**
	 * Parent plugin class.
	 *
	 * @since 3.2.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	var $is_staff_initialized = false;
	var $is_resources_initialized = false;


	/**
	 * Constructor.
	 *
	 * @since  3.2.0
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
	 * @since  3.2.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_upcoming_appointment_block' ) );
	}

	/**
	 * Register the block
	 *
	 * @since  3.2.0
	 */
	public function register_upcoming_appointment_block() {
		if ( function_exists( 'register_block_type' ) ) {
			wp_register_script(
				'ssa-upcoming-appointments-block-js',
				$this->plugin->url( 'assets/js/block-upcoming-appointments.js' ),
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
				Simply_Schedule_Appointments::VERSION
			);

			wp_register_style(
				'ssa-booking-block-css',
				$this->plugin->url( 'assets/css/block-booking.css' )
			);

			$is_resources_installed = $this->plugin->settings_installed->is_installed( 'resources' );
			$is_resources_enabled = $this->plugin->settings_installed->is_enabled( 'resources' );

			if( $is_resources_enabled ) {

				$resources_object_query = $this->plugin->resource_group_model->query( array(
					'status' => 'publish'
				));

				$filtered_resources = array_values(array_filter($resources_object_query, function($resource) {
					return $resource['resource_type'] !== 'identical';
				}));
				
				if (is_array($filtered_resources) && count($filtered_resources) > 0) {
					$ssa_resource_key_values = wp_list_pluck( $filtered_resources, 'title', 'id' );
					asort( $ssa_resource_key_values );
					$this->is_resources_initialized = true;

					wp_localize_script('ssa-upcoming-appointments-block-js', 'ssaResources', [
						'enabled' => $this->is_resources_initialized,
						'types' => $ssa_resource_key_values
					]);
				} else {
					$this->is_resources_initialized = false;
					wp_localize_script('ssa-upcoming-appointments-block-js', 'ssaResources', [
						'enabled' => $this->is_resources_initialized,
						'types' => []
					]);
				}
			}else {
				wp_localize_script('ssa-upcoming-appointments-block-js', 'ssaResources', [
					'enabled' => false,
					'types' => []
				]);
			}

			$is_staff_installed = $this->plugin->settings_installed->is_installed( 'staff' );
			$is_staff_enabled = $this->plugin->settings_installed->is_enabled( 'staff' );

			if( $is_staff_enabled ) {

				$staff_object_query = $this->plugin->staff_model->query( array(
					'status' => 'publish',
				));
				
				if (is_array($staff_object_query) && count($staff_object_query) > 0) {
					$this->is_staff_initialized = true;
					wp_localize_script('ssa-upcoming-appointments-block-js', 'staff', [
						'enabled' => $this->is_staff_initialized,
						'types' => []
					]);
				} else {
					$this->is_staff_initialized = false;
					wp_localize_script('ssa-upcoming-appointments-block-js', 'staff', [
						'enabled' => $this->is_staff_initialized,
						'types' => []
					]);
				}
			}else {

				wp_localize_script('ssa-upcoming-appointments-block-js', 'staff', [
					'enabled' => false,
					'types' => []
				]);
			}

			$show_settings_block = false;

			if ( $is_staff_installed && $is_resources_installed ) {
				$show_settings_block = true;
			}

			wp_localize_script('ssa-upcoming-appointments-block-js', 'showBlock', [
				'enabled' => $show_settings_block,
				'types' => []
			]);

			register_block_type( 'ssa/upcoming-appointments', array(
				'editor_script' => 'ssa-upcoming-appointments-block-js',
				'keywords' => array( 'ssa', 'appointments', 'simply' ),
				'attributes' => array (
					'no_results_message' => array (
						'type' => 'string',
						'default' => __( 'No upcoming appointments', 'simply-schedule-appointments' ),
					),
					'selectedResourceTypes' => array (
						'type' => 'array',
						'default' => [] ,
					),
					'memberInfo' => array (
						'type' => 'array',
						'default' => array(
							array(
								'value' => 'Display Team Members',
								'label' => 'Disable',
								'checked' => true,
							),
							array(
								'value' => 'Display Member Names',
								'label' => 'Team Member Names',
								'checked' => false,
							),
							array(
								'value' => 'Display Member Images',
								'label' => 'Team Member Images',
								'checked' => false,
							),
						),
					),
					'appointmentDisplay' => array (
						'type' => 'array',
						'default' => array(
							array(
								'value' => 'Display Appointment Types',
								'label' => 'Appointment Types',
								'checked' => false,
							),
							array(
								'value' => 'Display Team Members',
								'label' => 'Team Members',
								'checked' => false,
							),
						)
					),
					'resourceOptions' => array (
						'type' => 'array',
						'default' => array(
							array(
								'value' => 'Display Resources',
								'label' => 'Disable',
								'checked' => true,
							),
						),
					),
					'allResourcesTypeOption' => array (
						'type' => 'array',
						'default' => array(
							array(
								'value' => 'All',
								'label' => 'All',
								'checked' => false,
							),
						),
					)
				),

				'render_callback' => array( $this, 'render' ),
			) );
		}
	}

	/**
	 * Render the shortcode
	 *
	 * @since  3.2.0
	 */
	// public function render( $atts ) {
	// 	return $this->plugin->shortcodes->ssa_upcoming_appointments( $atts );
	// }
	public function render( $settings ) {
		$attrs = array();
		if ( $settings['no_results_message'] && $settings['no_results_message'] != 'No upcoming appointments' ) {
			$attrs['no_results_message'] = $settings['no_results_message'];
		}
		if ($this->is_resources_initialized) {
			if ( ! empty( $settings['selectedResourceTypes'] ) ) {
				$attrs['resourceTypes'] = $settings['selectedResourceTypes'] ;
			} else {
				$attrs['resourceTypes'] = [];
			}
		}
		if ($this->is_staff_initialized) {
			if( ! empty($settings['memberInfo'])){
				if( in_array( 'All', $settings['memberInfo'] ) ) {
					$attrs['memberInformation'] = '';
				} else {
					$attrs['memberInformation'] = $settings['memberInfo'];
				}
			}
		}
		if( ! empty($settings['appointmentDisplay'])){
				if( in_array( 'All', $settings['appointmentDisplay'] ) ) {
					$attrs['appointmentDisplay'] = '';
				} else {
					$attrs['appointmentDisplay'] = $settings['appointmentDisplay'];
				}
		}
		if( ! empty($settings['resourceOptions'])){
			$attrs['resourceDisplay'] = $settings['resourceOptions'];
		}
		if( ! empty($settings['allResourcesTypeOption'])){
			$attrs['allResourcesTypeOption'] = $settings['allResourcesTypeOption'];
		}
		
		return $this->plugin->shortcodes->ssa_upcoming_appointments( array('block_settings' => $attrs) );
	}

}