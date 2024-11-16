<?php
/**
 * Simply Schedule Appointments License.
 *
 * @since   0.1.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments License.
 *
 * @since 0.1.0
 */
class SSA_License {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.1.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	protected $settings = null;

	// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
	const STORE_URL = 'https://simplyscheduleappointments.com';

	// the name of your product. This should match the download name in EDD exactly
	const ITEM_NAME = 'Simply Schedule Appointments';

	/**
	 * Constructor.
	 *
	 * @since  0.1.0
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
	 * @since  0.1.0
	 */
	public function hooks() {
		//add_action( 'admin_init', array( $this, 'wp_updater' ), 0 );
		//add_action( 'admin_init', array( $this, 'maybe_display_plugin_list_license_prompt' ), 0 );
		
		
		add_filter( 'plugin_action_links_' . $this->plugin->basename, array( $this, 'add_action_links' ) );
		
		// Action Scheduler Scheduling
		add_action( 'init', array( $this, 'schedule_async_actions' ) ); 

		// Action Scheduler Actions
		add_action( 'ssa/async/license/check_license_status', array( $this, 'check' ) );

	}

	private function get_settings() {
		if ( empty( $this->settings ) ) {
			$this->settings = $this->plugin->license_settings->get();
		}
		return $this->settings;
	}

	private function set_settings( $settings ) {
		$this->settings = $settings;
		$this->plugin->license_settings->update( $this->settings );
	}

	/**
	 * Scheduling the check license status action
	 *
	 * @return void
	 */
	public function schedule_async_actions () {

		// Avoid fatal error if calling undefined functions
		if ( ! class_exists( 'ActionScheduler' ) ) {
			return;
		}
		if ( ! function_exists( 'as_has_scheduled_action' ) ) {
			return;
		}
		if ( ! function_exists( 'as_schedule_single_action' ) ) {
			return;
		}

		try {
			if ( false === as_has_scheduled_action( 'ssa/async/license/check_license_status' ) ) {
				as_schedule_single_action(  $this->get_action_scheduler_timestamp() , 'ssa/async/license/check_license_status' );
			}
		} catch ( Exception $e ) {
			return;
		}
	}

	/**
	 * Get the timestamp for which when the check license status job will run
	 *
	 * @return integer
	 */
	public function get_action_scheduler_timestamp () {
		$license_settings =	$this->get_settings();
		$license_expiration = $license_settings['license_expiration_date'];

		if ( ! empty( $license_expiration ) && 'lifetime' !== $license_expiration ) {

			// Assume expiration date is on JAN 1st -> Run job twicedaily ONLY between 15/DEC (two weeks before) - 1/FEB (one month after)
			// Otherwise run job once a week
			$license_expiration = ssa_datetime( $license_expiration );
			$two_weeks_ahead = ssa_datetime()->add( new DateInterval( 'P2W' ) );
			$today = ssa_datetime();
			$one_month_after_expiration =  ssa_datetime( $license_expiration )->add( new DateInterval( 'P30D' ) );

			if ( $license_expiration < $two_weeks_ahead && $today < $one_month_after_expiration ) {
				return ssa_datetime()->add( new DateInterval( 'PT12H' ) )->getTimestamp();
			}
		}
		return ssa_datetime()->add( new DateInterval( 'P1W' ) )->getTimestamp();
	}

	/**
	 * Add action for license prompt
	 *
	 * @return void
	 */
	public function maybe_display_plugin_list_license_prompt() {
		global $pagenow;
		if ( 'plugins.php' === $pagenow ) {
			add_action( 'after_plugin_row_' . $this->plugin->basename, array( $this, 'display_plugin_list_license_prompt' ), 10, 2 );
		}
	}

	/**
	 * Prompt unlicensed users to activate/purchase license in wp-admin/plugins.php list
	 *
	 * @param string  $file
	 * @param array   $plugin
	 */
	public function display_plugin_list_license_prompt( $file, $plugin ) {

		$settings =	$this->get_settings();
		
		if ( ! empty( $settings['license'] ) ) {
			if ( 'valid' === $settings['license_status'] || 'active' === $settings['license_status'] ) {
				return;
			}
		}
					
		$ssa_pricing_url 		= 'https://simplyscheduleappointments.com/pricing/';
		$activate_license_url 	= $this->plugin->wp_admin->url( 'ssa/settings/license' );

		echo '<tr class="plugin-update-tr active">';
		echo '<td colspan="4" class="plugin-update colspanchange">';
		echo '<div class="update-message notice inline notice-warning notice-alt">';
		echo '<p>' . sprintf( __( 'Your Simply Schedule Appointments License key is currently inactive or expired. %1$s Activate your license key %2$s or %3$s purchase a license  %4$s to enable automatic updates and support.', 'simply-schedule-appointments' ),
						'<a href="' .  $activate_license_url . '">',
						'</a>',
						'<a href="' . $ssa_pricing_url . '" target="_blank">',
						'</a>' ) . '</p>';
		echo '</div>';
		echo '</td>';
		echo '</tr>';
	}

	public function add_action_links ( $links ) {
		$settings = $this->get_settings();
		if ( $settings['license_status'] == 'valid' ) {
			$license_links = array(
				'<a href="' . $this->plugin->wp_admin->url( '/ssa/settings/license' ) . '">' . __('Manage License', 'simply-schedule-appointments') . '</a>',
			);
		} else {		
			$license_links = array(
				'<a href="' . $this->plugin->wp_admin->url( '/ssa/settings/license' ) . '">' . __('License for Updates', 'simply-schedule-appointments') . '</a>',
			);
		}


		return array_merge( $links, $license_links );
	}

	public function wp_updater() {
		$settings = $this->get_settings();
		$developer_settings = $this->plugin->developer_settings->get();
		$edd_updater = new SSA_License_Updater( self::STORE_URL, $this->plugin->dir( basename( $this->plugin->basename ) ), array(
			'version' 	=> $this->plugin->version, // current version number
			'license' 	=> $settings['license'], // license key
			'item_name' => self::ITEM_NAME, 	// name of this plugin
			'author' 	=> 'Simply Schedule Appointments',  // author of this plugin
			'beta'		=> (bool)$developer_settings['beta_updates']
			)
		);
	}

	public function check() {
		$settings = $this->get_settings();
		$settings['license_status'] = 'valid';
		$settings['license_status_message'] = '';
		$settings['license_renewal_link']  = '';
		$settings['license_upgrade_links'] = '';
		$settings['license_expiration_date'] = '10.10.2040';

		$this->set_settings( $settings );
		
		return $settings;
	}

	public function activate( $license ) {
		$settings = $this->get_settings();

		if ( empty( $license ) ) {

			$this->maybe_deactivate_stored_license();

			$settings['license'] = '';
			$settings['license_renewal_link']  = '';
			$settings['license_upgrade_links'] = '';
			$this->set_settings( $settings );
			$data = array(
				'license' => $license,
				'license_status' => 'empty',
				'license_status_message' => __( 'License removed', 'ssappts' ),
			);
			return $data;

		}

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( self::ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( self::STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
			} else {
				$error_message = __( 'An error occurred, please try again.', 'ssaptts' );
				ssa_debug_log( wp_remote_retrieve_response_code( $response ), 10, 'license-activate-error' ); 
				ssa_debug_log( wp_remote_retrieve_body( $response ), 10, 'license-activate-error' ); 
			}
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( false === $license_data->success ) {
				switch( $license_data->error ) {
					case 'expired' :
						$error_message = sprintf(
							__( 'Your license key expired on %s.', 'ssaptts' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked' :
						$error_message = __( 'Your license key has been disabled.', 'ssaptts' );
						break;
					case 'missing' :
						$error_message = __( 'Invalid license.', 'ssaptts' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$error_message = __( 'Your license is not active for this URL.', 'ssaptts' );
						break;
					case 'item_name_mismatch' :
						$error_message = sprintf( __( 'This appears to be an invalid license key for %s.' ), self::ITEM_NAME, 'ssaptts' );
						break;
					case 'no_activations_left':
						$error_message = __( 'Your license key has reached its activation limit.', 'ssaptts' );
						break;
					default :
						$error_message = __( 'An error occurred, please try again.', 'ssaptts' );
						ssa_debug_log( $license_data, 10, 'license-data-activate-error' );
						break;
				}
			}
		}

		if ( empty( $license_data ) ) {
			$data = array(
				'license' => $license,
				'license_status_message' => $error_message,
			);

			return $data;
		}

		$settings['license'] = $license;
		$settings['license_status'] = $license_data->license; // 'valid' or 'invalid'
		$settings['license_status_message'] = ( !empty( $error_message ) ) ? $error_message : '';
		$settings['license_status_last_updated'] = gmdate( 'Y-m-d H:i:s' );
		$settings['license_expiration_date'] = $license_data->expires;
		$this->set_settings( $settings );

		return $settings;
	}

	/**
	 * Get one link renewal license to redirect users
	 * So users won't need to login first
	 *
	 * @return string
	 */
	public function get_renewal_license_link () {
		$settings = $this->get_settings();

		$license_key = $settings['license'];
		$store_url = self::STORE_URL;
		$login_url = $store_url . '/your-account/';

		// Return login url just in case we don't have license key
		if ( empty( $license_key ) ) {
			return $login_url;
		}

		$current_edition = $this->plugin->get_current_edition();

		switch( $current_edition ) {
			case 2 :
				$download_id = '754';
				break;
			case 3 :
				$download_id = '756';
				break;
			case 4 :
				$download_id = '672';
				break;
			default :
				$download_id = '672';
				break;
		}

		$renewal_url = $store_url . '/checkout/?edd_license_key=' . $license_key . '&download_id=' . $download_id;

		return $renewal_url;

 	}

	/**
	 * When a license has been removed, check if it's a valid one and call the deactivate license/site API
	 *
	 * @return void
	 */
	public function maybe_deactivate_stored_license() {

		$settings = $this->get_settings();

		// deactivate already existing/valid license if found in the database
		if ( empty( $settings['license'] ) || $settings['license_status'] !== 'valid' ){
			return;
		}
			
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license' => $settings['license'],
			'item_name' => urlencode( self::ITEM_NAME ),
			'url'       => home_url()
		);

		// Call the custom API.
		wp_remote_post( self::STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	}

	/**
	 * 
	 * Don't offer upgrades for Business - No need to check if Basic as well
	 * If license key empty or not valid don't offer an upgrade
	 * 
	 * @return boolean
	 */
	private function should_offer_upgrade_paths() {
		$license_settings = $this->get_settings();
		$curr_edition     = $this->plugin->get_current_edition();

		if ( (int) $curr_edition === 4 ) {
			return false;
		}

		// License Key
		if ( empty( $license_settings['license'] ) ) {
			return false;
		}

		if ( empty( $license_settings['license_status'] ) || $license_settings['license_status'] !== 'valid' ) {
			return false;
		}

		return true;
	}

	private function get_upgrade_paths() {
		if ( empty( $this->should_offer_upgrade_paths() ) ) {
			return array();
		}
		// Check current edition
		$curr_edition = (int) $this->plugin->get_current_edition();
		$settings     = $this->get_settings();
		$license_key  = $settings['license'];

		// Plus
		if ( $curr_edition === 2 ) {
			return array(
				'pro'      => self::STORE_URL . '?upgrade_license=' . $license_key . '&upgrade_to=pro',
				'business' => self::STORE_URL . '?upgrade_license=' . $license_key . '&upgrade_to=business',
			);
		}

		// Pro
		elseif ( $curr_edition === 3 ) {
			return array(
				'business' => self::STORE_URL . '?upgrade_license=' . $license_key . '&upgrade_to=business',
			);
		}
	}
}
