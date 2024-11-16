<?php
/**
 * Simply Schedule Appointments Mailchimp Settings.
 *
 * @since   1.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Mailchimp Settings.
 *
 * @since 1.0.1
 */
class SSA_Mailchimp_Settings extends SSA_Settings_Schema {
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
		// add_filter( 'update_'.$this->slug.'_settings', array( $this, 'auto_validate_api_key' ), 10, 2 );
	}

	protected $slug = 'mailchimp';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2018-06-21',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'api_key' => array(
					'name' => 'api_key',
					'default_value' => '',
					'required_capability' => 'ssa_manage_site_settings',
				),

				'account_details' => array(
					'name' => 'account_details',
					'default_value' => '',
					'required_capability' => 'ssa_manage_site_settings',
				),

				'lists' => array(
					'name' => 'lists',
					'default_value' => '',
					'required_capability' => 'ssa_manage_site_settings',
				),
			),
		);

		return $this->schema;
	}

}
