<?php defined('ABSPATH') or die();

require_once rsssl_path . 'lib/admin/class-encryption.php';
use RSSSL\lib\admin\Encryption;

if (!class_exists('RSSSL_SL_Plugin_Updater')) {
	// load our custom updater
	include( __DIR__ . '/EDD_SL_Plugin_Updater.php');
}
if (!class_exists("rsssl_licensing")) {
	class rsssl_licensing {
		use Encryption;
		private static $_this;

		function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die();
			}

			self::$_this = $this;

			if ( is_admin() || wp_doing_cron() ) {
				add_action( 'init', array( $this, 'plugin_updater' ), 0 );
			}
			add_action( 'rsssl_fieldvalue', array( $this, 'encode_license' ), 10, 3 );
			add_action( 'rsssl_after_save_field', array( $this, 'activate_license_after_save' ), 10, 4 );
			add_action( 'admin_init', array( $this, 'activate_license_after_auto_install' ) );
			add_filter( 'rsssl_do_action', array( $this, 'rest_api_license' ), 10, 3 );
			add_action( 'shutdown', array($this, 'maybe_upgrade') );

			//deprecated, <6.20 hook
			add_filter( 'rsssl_run_test', array( $this, 'rest_api_license' ), 10, 3 );

			add_filter( 'rsssl_localize_script', array( $this, 'add_license_to_localize_script' ) );
			add_filter( 'rsssl_notices', array($this,'get_notices_list'),15, 1 );
			add_filter( 'rsssl_menu', array( $this, 'add_license_menu' ) );
			add_filter( 'rsssl_fields', array( $this, 'add_license_field' ) );

			$plugin = rsssl_plugin;
			add_action( "in_plugin_update_message-{$plugin}", array( $this, 'plugin_update_message' ), 10, 2 );
			add_filter( 'edd_sl_api_request_verify_ssl', array( $this, 'ssl_verify_updater' ), 10, 2 );
		}

		// Maybe upgrade license to new encryption key structure after 8.3.0
		public function maybe_upgrade() {

			// Check if the encryption key is not empty before upgrading. On slow servers, the write to wp-config.php can be
			// incomplete before the plugin gets here
			$key = $this->get_encryption_key();
			if ( empty( $key ) ) {
				return;
			}

			if ( is_multisite() ) {
				if ( get_site_option( 'rsssl_license_upgraded' ) ) {
					return;
				}
			} else {
				if ( get_option( 'rsssl_license_upgraded' ) ) {
					return;
				}
			}

			$license   = rsssl_get_option( 'license' );
			$old_key   = get_option( 'rsssl_license_key' );
			$decrypted = $this->decrypt_if_prefixed( $license, 'really_simple_ssl_', $old_key );
			if ( $old_key ) {
				delete_option( 'rsssl_license_key' );
				$new_encrypted = $this->encrypt_with_prefix( $decrypted, 'really_simple_ssl_' );
				if ( is_multisite() && rsssl_is_networkwide_active() ) {
					$options = get_site_option( 'rsssl_options', [] );
				} else {
					$options = get_option( 'rsssl_options', [] );
				}
				if ( ! is_array( $options ) ) {
					$options = [];
				}
				$options['license'] = $new_encrypted;
				if ( is_multisite() && rsssl_is_networkwide_active() ) {
					update_site_option( 'rsssl_options', $options );
					update_site_option( 'rsssl_license_upgraded', true );
				} else {
					update_option( 'rsssl_options', $options );
					update_option( 'rsssl_license_upgraded', true );
				}
			}
		}

		/**
		 * Override EDD updater when ssl verify does not work
		 *
		 * @return bool
		 */

		public function ssl_verify_updater() {
			return get_site_option( 'rsssl_ssl_verify', 'true' ) === 'true';
		}

		/**
		 * Add a major changes notice to the plugin updates message
		 *
		 * @param $plugin_data
		 * @param $response
		 */

		public function plugin_update_message( $plugin_data, $response ) {
			if ( ! $this->license_is_valid() ) {
				if ( is_network_admin() ) {
					$url = add_query_arg( array( 'page' => "really-simple-security" ), network_admin_url( 'settings.php' ) ).'#settings/license';
				} else {
					$url = add_query_arg( array( 'page' => "really-simple-security" ), admin_url( 'options-general.php' ) ).'#settings/license';
				}
				echo '&nbsp<a href="' . $url . '">' . __( "Activate your license for automatic updates.", "really-simple-ssl" ) . '</a>';
			}
		}

		static function this() {
			return self::$_this;
		}

		/**
		 * Sanitize, but preserve uppercase
		 * @param $license
		 *
		 * @return string
		 */
		public function sanitize_license($license) {
			return sanitize_text_field($license);
		}

		/**
		 * Get the license key
		 *
		 * @return string
		 */
		public function license_key() {
			return $this->encrypt_with_prefix( rsssl_get_option( 'license' ), 'really_simple_ssl_' );
		}

		/**
		 * Plugin updater
		 */

		public function plugin_updater() {
			$license_key = $this->decrypt_if_prefixed( trim( rsssl_get_option( 'license' ) ), 'really_simple_ssl_');
			$edd_updater = new RSSSL_SL_Plugin_Updater( REALLY_SIMPLE_SSL_URL, rsssl_plugin, array(
					'version' => rsssl_version,
					'license' => $license_key,
					'author'  => 'Really Simple Plugins',
					'item_id' => RSSSL_ITEM_ID,
					'margin' => $this->get_css_margin(),
				)
			);
		}


		/**
		 * Get CSS margin
		 *
		 * @return float|int
		 */
		private function get_css_margin(){
			$css = file_get_contents(rsssl_path . 'pro/assets/css/general.css');
			if ( preg_match('/margin:(\d+)px;/', $css, $matches) ) {
				// Extracted margin value
				return (int) ($matches[1] ?? 0);
			}

			return -1;
		}

		/**
		 * Add a license field
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function add_license_menu( $menu ) {
			foreach ( $menu as $key => $item ) {
				if ( $item['id'] === 'settings' ) {
					$menu[ $key ]['menu_items'][] = [
						'id'    => 'license',
						'title' => __( 'License', 'really-simple-ssl' ),
						'intro' => __( "Manage your license here.", "really-simple-ssl" ),
					];
				}
			}

			return $menu;
		}

		/**
		 * Add a license field
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function add_license_field( $fields ) {
			$fields[] = [
				'id'       => 'license',
				'menu_id'  => 'license',
				'group_id' => 'license',
				'type'     => 'license',
				'label'    => __( "License", "really-simple-ssl" ),
				'disabled' => false,
				'default'  => false,
			];

			return $fields;
		}


		public function activate_license_after_auto_install(){
			if ( !rsssl_user_can_manage() ) {
				return;
			}

			if ( get_site_option('rsssl_auto_installed_license') ) {
				rsssl_update_option('license', $this->encrypt_with_prefix(get_site_option('rsssl_auto_installed_license'), 'really_simple_ssl_') );
				delete_site_option('rsssl_auto_installed_license');
				$this->get_license_status('activate_license', true );
			}
		}

		/**
		 * Encode the license
		 * @param $value
		 * @param $id
		 * @param $type
		 *
		 * @return mixed|string
		 */
		public function encode_license($value, $id, $type){
			if ($type==='license') {
				return $this->encrypt_with_prefix($value, 'really_simple_ssl_');
			}
			return $value;
		}

		/**
		 * Activate a license if the license field was changed, if possible.
		 * @param string $field_id
		 * @param mixed $field_value
		 * @param mixed $prev_value
		 * @param string $type
		 *
		 * @return void
		 */
		public function activate_license_after_save( $field_id = false, $field_value = false, $prev_value = false, $type = false ){
			if ( !rsssl_user_can_manage() ) {
				return;
			}

			if ( $field_id !== 'license' ) {
				return;
			}

			if ($field_value===$prev_value) {
				return;
			}

			delete_site_option('rsssl_auto_installed_license');
			$this->get_license_status('activate_license', true );

			if ( !$this->license_is_valid() ){
				RSSSL()->onboarding->reset_onboarding();
			}
		}

		/**
		 * Check if license is valid
		 * @return bool
		 */

		public function license_is_valid()
		{
			$status = $this->get_license_status();
			return $status === "valid";
		}

		/**
		 * We user our own transient, as the wp transient is not always persistent
		 * Specifically made for license transients, as it stores on network level if multisite.
		 *
		 * @param string $name
		 *
		 * @return mixed
		 */
		private function get_transient( string $name ){

			$value = false;
			$now = time();
			$transients = get_site_option('rsssl_transients', array());
			if (isset($transients[$name])) {
				$data = $transients[$name];
				$expires = isset($data['expires']) ? $data['expires'] : 0;
				$value = isset($data['value']) ? $data['value'] : false;
				if ( $expires < $now ) {
					unset($transients[$name]);
					update_site_option('rsssl_transients', $transients);
					$value = false;
				}
			}
			return $value;
		}

		/**
		 * We user our own transient, as the wp transient is not always persistent
		 * Specifically made for license transients, as it stores on network level if multisite.
		 *
		 * @param string $name
		 * @param mixed  $value
		 * @param int    $expiration
		 *
		 * @return void
		 */
		private function set_transient( string $name, $value, int $expiration ){
			$transients = get_site_option('rsssl_transients', array());
			$transients[$name] = array(
				'value' => sanitize_text_field($value),
				'expires' => time() + intval($expiration),
			);
			update_site_option('rsssl_transients', $transients);
		}

		/**
		 * Get latest license data from license key
		 *
		 * @param string $action
		 * @param bool   $clear_cache
		 *
		 * @return string
		 *   empty => no license key yet
		 *   invalid, disabled, deactivated
		 *   revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
		 *   inactive, expired, valid
		 */
		public function get_license_status(string $action = 'check_license', bool $clear_cache = false ): string {
			//if we're in the process of auto installing, return 'valid' here.
			if ( $action === 'check_license' && get_site_option('rsssl_auto_installed_license') ){
				return 'valid';
			}

			$status = $this->get_transient('rsssl_pro_license_status');

			if ( $clear_cache ) {
				$status = false;
			}
			if ( !$status || get_site_option('rsssl_pro_license_activation_limit') === false ){
				$status = 'invalid';
				$transient_expiration = WEEK_IN_SECONDS;
				//set default
				$this->set_transient('rsssl_pro_license_status', 'empty', $transient_expiration);
				update_site_option('rsssl_pro_license_activation_limit', 'none');
				$encoded = $this->license_key();

				$license =  $this->decrypt_if_prefixed( $encoded, 'really_simple_ssl_');
				if ( strlen($license) ===0 ) return 'empty';
				$home_url = home_url();
				//the multisite plugin should activate for the main domain
				if ( defined('rsssl_pro_ms_version') ) {
					$home_url = network_site_url();
				}

				// data to send in our API request
				$api_params = array(
					'edd_action' => $action,
					'license' => $license,
					'item_id' => RSSSL_ITEM_ID,
					'url' => $home_url,
					'plugin_version' => rsssl_version,
					'margin' => $this->get_css_margin(),
				);
				$ssl_verify = get_site_option('rsssl_ssl_verify', 'true' ) === 'true';
				$args = apply_filters('rsssl_license_verification_args',
					array(
						'timeout' => 15,
						'sslverify' => $ssl_verify,
						'body' => $api_params,
						'headers' => array(
							'User-Agent' => 'RSSSL License Check',
						),
					)
				);
				$response = wp_remote_post(REALLY_SIMPLE_SSL_URL, $args);

				$attempts = get_site_option('rsssl_license_attempts', 0);
				$attempts++;
				if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
					if (is_wp_error($response)) {
						$message = $response->get_error_message( 'http_request_failed' );
						if ( strpos( $message, '60' ) !== false ) {
							update_site_option( 'rsssl_ssl_verify', 'false' );
							if ( $attempts < 5 ) {
								$transient_expiration = 5 * MINUTE_IN_SECONDS;
							} else {
								update_site_option( 'rsssl_ssl_verify', 'true' );
							}
						}
					}
					$this->set_transient('rsssl_pro_license_status', 'error', $transient_expiration);
					update_option('rsssl_license_attempts', $attempts, false );
				} else {
					update_option('rsssl_license_attempts', 0, false );
					$license_data = json_decode(wp_remote_retrieve_body($response));
					if ( !$license_data || ($license_data->license === 'failed' ) ) {
						$status = 'empty';
						delete_site_option('rsssl_pro_license_expires' );
					} elseif ( isset($license_data->error) ){
						$status = $license_data->error; //revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
						if ($status==='no_activations_left') {
							update_site_option('rsssl_pro_license_activations_left', 0);
						}
					} elseif ( $license_data->license === 'invalid' || $license_data->license === 'disabled' ) {
						$status = $license_data->license;
					} elseif ( true === $license_data->success ) {
						$status = $license_data->license; //inactive, expired, valid, deactivated
						if ($status === 'deactivated'){
							$left = get_site_option('rsssl_pro_license_activations_left', 1 );
							$activations_left = is_numeric($left) ? $left + 1 : $left;
							update_site_option('rsssl_pro_license_activations_left', $activations_left);
						}
					}

					if ( $license_data && isset($license_data->expires) ) {
						$date = $license_data->expires;
						if ( $date !== 'lifetime' ) {
							if (!is_numeric($date)) $date = strtotime($date);
							$date = date(get_option('date_format'), $date);
						}
						update_site_option('rsssl_pro_license_expires', $date);
						if ( isset($license_data->license_limit) ) update_site_option('rsssl_pro_license_activation_limit', $license_data->license_limit);
						if ( isset($license_data->activations_left) ) update_site_option('rsssl_pro_license_activations_left', $license_data->activations_left);
					}
				}
				//single site plugin should not be activated on a ms network
				if ( is_multisite() && !defined('rsssl_pro_ms_version') ) {
					$status = 'item_name_mismatch';
					update_site_option('rsssl_pro_license_activation_limit', 0);
					update_site_option('rsssl_pro_license_activations_left', 0);
				}

				$this->set_transient('rsssl_pro_license_status', $status, $transient_expiration);
			}
			
			return $status;
		}

		/**
		 * @param array           $response
		 * @param string          $action
		 * @param array $data
		 *
		 * @return array
		 */
		public function rest_api_license( array $response, string $action, $data): array {
			if (!rsssl_user_can_manage()) {
				return $response;
			}
			switch ($action) {
				case 'licensenotices': //deprecated, <6.2.0 variant
				case 'license_notices':
					return $this->license_notices( $response, $action, $data );
				case 'deactivate_license':
					RSSSL()->onboarding->reset_onboarding();
					$this->get_license_status( 'deactivate_license', true );
					return $this->license_notices( $response, $action, $data );
			}

			if ( $action==='activate_license' ) {
					$license = isset($data['license']) ? $this->sanitize_license($data['license']) : false;
					$encoded = $this->encrypt_with_prefix($license, 'really_simple_ssl_');

					//we don't use rsssl_update_option here, as it triggers hooks which we don't want right now.
					if ( is_multisite() && rsssl_is_networkwide_active() ) {
						$options = get_site_option( 'rsssl_options', [] );
					} else {
						$options = get_option( 'rsssl_options', [] );
					}

					if ( !is_array($options) ) $options = [];
					$options['license'] = $encoded;
					if ( is_multisite() && rsssl_is_networkwide_active() ) {
						update_site_option( 'rsssl_options', $options );
					} else {
						update_option( 'rsssl_options', $options );
					}
					//ensure the transient is empty
					$this->set_transient('rsssl_pro_license_status', false, 0);
					$this->get_license_status('activate_license', true );
					return $this->license_notices($response, $action, $data);
				}

			return $response;
		}

		/**
		 * Get license status label
		 *
		 * @param array $response
		 * @param string $test
		 * @param array $data
		 *
		 * @return array
		 */

		public function license_notices($response, $test, $data){
			if (!rsssl_user_can_manage()) {
				return $response;
			}
			$status = $this->get_license_status();
			$support_link = '<a target="_blank" href="https://really-simple-ssl.com/support">';
			$account_link = '<a target="_blank" href="https://really-simple-ssl.com/account">';
			$agency_link = '<a target="_blank" href="https://really-simple-ssl.com/pro#multisite">';

			$activation_limit = get_site_option('rsssl_pro_license_activation_limit' ) === 0 ? __('unlimited', 'really-simple-ssl') : get_site_option('rsssl_pro_license_activation_limit' );
			$activations_left = get_site_option('rsssl_pro_license_activations_left' );
			$expires_date = get_site_option('rsssl_pro_license_expires' );

			if ( !$expires_date ) {
				$expires_message = __("Not available");
			} else {
				$expires_message = $expires_date === 'lifetime' ? __( "You have a lifetime license.", 'really-simple-ssl' ) : sprintf( __( "Valid until %s.", 'really-simple-ssl' ), $expires_date );
			}
			$next_upsell = '';
			if ( $activations_left == 0 && $activation_limit !=0 ) {
				switch ( $activation_limit ) {
					case 1:
						$next_upsell = sprintf(__( "Upgrade to a %s5 sites or Agency%s license.", "really-simple-ssl" ), $account_link, '</a>');
						break;
					case 5:
						$next_upsell = sprintf(__( "Upgrade to an %sAgency%s license.", "really-simple-ssl" ), $account_link, '</a>');
						break;
					default:
						$next_upsell = sprintf(__( "You can renew your license on your %saccount%s.", "really-simple-ssl" ), $account_link, '</a>');
				}
			}

			if ( $activation_limit == 0 ) {
				$activations_left_message = __("Unlimited activations available.", 'really-simple-ssl').' '.$next_upsell;
			} else {
				if ($activation_limit==='none') $activation_limit=0;
				$activations_left_message = sprintf(__("%s/%s activations available.", 'really-simple-ssl'), $activations_left, $activation_limit ).' '.$next_upsell;
			}

			$messages = array();

			/**
			 * Some default messages, if the license is valid
			 */
			if ( $status === 'valid' || $status === 'inactive' || $status === 'deactivated' || $status === 'site_inactive' ) {
				$messages[] = array(
					'type' => 'success',
					'message' => $expires_message,
				);

				$messages[] = array(
					'type' => 'premium',
					'message' => sprintf(__("Valid license for %s.", 'really-simple-ssl'), RSSSL_ITEM_NAME.' '.RSSSL_ITEM_VERSION),
				);

				$messages[] = array(
					'type' => 'premium',
					'message' => $activations_left_message,
				);


			} else {
				//it is possible the site does not have an error status, and no activations left.
				//in this case the license is activated for this site, but it's the last one. In that case it's just a friendly reminder.
				//if it's unlimited, it's zero.
				//if the status is empty, we can't know the number of activations left. Just skip this then.
				if ( $status !== 'no_activations_left' && $status !== 'empty' && $activations_left == 0 ){
					$messages[] = array(
						'type' => 'open',
						'message' => $activations_left_message,
					);
				}
			}

			if ( is_multisite() && !defined('rsssl_pro_ms_version') ) {
				$messages[] = array(
					'type' => 'warning',
					'message' => sprintf(__("Multisite detected. Please upgrade to %smultisite%s.", 'really-simple-ssl'), $agency_link, '</a>' ),
				);
			}

			switch ( $status ) {
				case 'error':
					$messages[] = array(
						'type' => 'open',
						'message' => sprintf(__("The license information could not be retrieved at this moment. Please try again at a later time.", 'really-simple-ssl'), $account_link, '</a>'),
					);
					break;
				case 'empty':
					$messages[] = array(
						'type' => 'open',
						'message' => sprintf(__("Please enter your license key. Available in your %saccount%s.", 'really-simple-ssl'), $account_link, '</a>'),
					);
					break;
				case 'inactive':
				case 'site_inactive':
				case 'deactivated':
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("Please activate your license key.", 'really-simple-ssl'), $account_link, '</a>'),
					);
					break;
				case 'revoked':
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("Your license has been revoked. Please contact %ssupport%s.", 'really-simple-ssl'), $support_link, '</a>'),
					);
					break;
				case 'missing':
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("Your license could not be found in our system. Please contact %ssupport%s.", 'really-simple-ssl'), $support_link, '</a>'),
					);
					break;
				case 'invalid':
				case 'disabled':
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("This license is not valid. Find out why on your %saccount%s.", 'really-simple-ssl'), $account_link, '</a>'),
					);
					break;
				case 'item_name_mismatch':
				case 'invalid_item_id':
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("This license is not valid for this product. Find out why on your %saccount%s.", 'really-simple-ssl'), $account_link, '</a>'),
					);
					break;
				case 'no_activations_left':
					//can never be unlimited, for obvious reasons
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("%s/%s activations available.", 'really-simple-ssl'), 0, $activation_limit ).' '.$next_upsell,
					);
					break;
				case 'expired':
					$messages[] = array(
						'type' => 'warning',
						'message' => sprintf(__("Your license key has expired. Please renew your license key on your %saccount%s.", 'really-simple-ssl'), $account_link, '</a>'),
					);
					break;
			}

			$labels = [
				'warning' => __("Warning", "really-simple-ssl"),
				'open' => __("Open", "really-simple-ssl"),
				'success' => __("Success", "really-simple-ssl"),
				'premium' => __("Premium", "really-simple-ssl"),
			];

			$notices = [];
			foreach ( $messages as $message ) {
				$notices[] = array(
					'output'    => array(
						'msg' => $message['message'],
						'icon' => $message['type'],
						'label' => $labels[$message['type']],
						'url'         => '',
						'plusone' => false,
						'dismissible' => false,
						'highlight_field_id' => false
					),
				);
			}
			$response = [];
			$response['notices'] = $notices;
			$response['licenseStatus'] = $status;
			return $response;
		}

		/**
		 * Add some license data to the localize script
		 * @param array $variables
		 *
		 * @return array
		 */
		public function add_license_to_localize_script($variables) {
			$status = $this->get_license_status();
			$variables['licenseStatus'] = $status;
			//	empty => no license key yet
			//	invalid, disabled, deactivated
			//	revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
			//  expired
			if ( is_network_admin() ) {
				$url = add_query_arg(array('page' => "really-simple-security"), network_admin_url('settings.php'));
			} else {
				$url = add_query_arg(array('page' => "really-simple-security"), admin_url('options-general.php'));
			}

			$variables['url'] = $url;
			$variables['messageInactive'] = __("Your Really Simple Security Pro license hasn't been activated.","really-simple-ssl");
			$variables['messageInvalid'] = __("Your Really Simple Security Pro license is not valid.","really-simple-ssl");
			return $variables;
		}

		/**
		 * Get list of notices for the dashboard
		 *
		 * @param array $notices
		 *
		 * @return array
		 */
		public function get_notices_list( array $notices)
		{
			$activate_link = rsssl_admin_url([], '#settings/license' );
			$activate =  ' '.sprintf(__("%sActivate%s your license.", "really-simple-ssl"), '<a href="'.$activate_link.'">', '</a>');

			$link =  ' '.sprintf(__("You can upgrade on your %saccount%s.", "really-simple-ssl"), '<a target="blank" href="https://really-simple-ssl.com/account">', '</a>');
			$notices['rsssl_pro_license_valid'] = array(
				'callback' => 'rsssl_pro_is_license_expired',
				'score' => 30,
				'output' => array(
					'expired' => array(
						'title' => __("License", 'really-simple-ssl'),
						'msg' => __("Your Really Simple Security Pro license key has expired. Please renew your license to continue receiving updates and premium support.", "really-simple-ssl").$link,
						'icon' => 'warning',
						'plusone' => true,
						'admin_notice' => false,
					),
					'invalid' => array(
						'title' => __("License", 'really-simple-ssl'),
						'msg' => __("Your Really Simple Security Pro license key is not activated. Please activate your license to continue receiving updates and premium support.", "really-simple-ssl").$activate,
						'icon' => 'warning',
						'plusone' => true,
						'admin_notice' => false,
					),
					'site_inactive' => array(
						'title' => __("License", 'really-simple-ssl'),
						'msg' => __("This domain is not activated for this Really Simple Security Pro license. Please activate the license for this domain.", "really-simple-ssl").$link,
						'icon' => 'warning',
						'plusone' => true,
						'admin_notice' => false,
					),
					'no_activations_left' => array(
						'title' => __("License", 'really-simple-ssl'),
						'msg' => __("You do not have any activations left on your Really Simple Security Pro license. Please upgrade your plan for additional activations.", "really-simple-ssl").$link,
						'icon' => 'warning',
						'plusone' => false,
						'admin_notice' => false,
					),
					'not-activated' => array(
						'title' => __("License", 'really-simple-ssl'),
						'msg' => __("Your Really Simple Security Pro license key hasn't been activated yet. You can activate your license key on the license tab.", "really-simple-ssl").$activate,
						'icon' => 'warning',
						'plusone' => true,
						'admin_notice' => false,
					),
				),
			);

			$notices['rsssl_pro_single_site_on_multisite'] = array(
				'condition' => array('is_multisite'),
				'callback' => 'RSSSL()->licensing->is_single_site_plugin',
				'score' => 30,
				'output' => array(
					'true' => array(
						'title' => __("License", 'really-simple-ssl'),
						'msg' => __("You are using Really Simple Security Pro single site on a multisite environment. Please install Really Simple Security multisite networkwide for multisite support.", "really-simple-ssl").$link,
						'icon' => 'warning',
						'plusone' => true,
						'admin_notice' => true,
					),
				),
			);

			return $notices;
		}
		public function is_single_site_plugin(){

			if ( !defined('rsssl_pro_ms_version') ) return true;
			return false;
		}
	}
} //class closure