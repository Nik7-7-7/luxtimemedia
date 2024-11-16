<?php
/**
 * Simply Schedule Appointments Webhooks Settings.
 *
 * @since   1.9.3
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Webhooks Settings.
 *
 * @since 1.9.3
 */
class SSA_Webhooks_Settings extends SSA_Settings_Schema {
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

	protected $slug = 'webhooks';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2018-09-18 18:30',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'webhooks' => array(
					'name' => 'webhooks',
					'default_value' => array(),
					'required_capability' => 'ssa_manage_site_settings',
				),
			),
		);

		return $this->schema;
	}

	public function get_webhooks() {
		if ( ! $this->plugin->settings_installed->is_enabled( 'webhooks' ) ) {
			return array();
		}
		
		$settings = $this->get();
		if ( empty( $settings['webhooks'] ) ) {
			return array();
		}

		return $settings['webhooks'];
	}

}
