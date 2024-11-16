<?php
/**
 * Simply Schedule Appointments Woocommerce.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Woocommerce.
 *
 * @since 2.0.1
 */
class SSA_Woocommerce {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.0.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	public $parent_slug = 'payments';

	/**
	 * Constructor.
	 *
	 * @since  2.0.1
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
	 * @since  2.0.1
	 */
	public function hooks() {

	}
}
