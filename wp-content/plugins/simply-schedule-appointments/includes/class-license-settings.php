<?php
/**
 * Simply Schedule Appointments License Settings.
 *
 * @since   0.1.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments License Settings.
 *
 * @since 0.1.0
 */
class SSA_License_Settings extends SSA_Settings_Schema {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.3
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.3
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		parent::__construct();
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.3
	 */
	public function hooks() {
		
	}

	protected $slug = 'license';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2023-09-14',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => '',
				),

				'license' => array(
					'name' => 'license',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'license_status' => array(
					'name' => 'license_status',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'license_status_message' => array(
					'name' => 'license_status_message',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'license_status_last_updated' => array(
					'name' => 'license_status_last_updated',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'license_expiration_date' => array(
					'name' => 'license_expiration_date',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'license_renewal_link' => array(
					'name' => 'license_renewal_link',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'license_upgrade_links' => array(
					'name' => 'license_upgrade_links',
					'default_value' => '',
					'required_capability' => 'ssa_manage_site_settings',
				),

			),
		);

		return $this->schema;
	}

	public function get() {
		return $this->plugin->settings->get()[$this->slug];
	}

	public function update( $new_settings ) {
		$this->plugin->settings->update_section( $this->slug, $new_settings );
	}

}
