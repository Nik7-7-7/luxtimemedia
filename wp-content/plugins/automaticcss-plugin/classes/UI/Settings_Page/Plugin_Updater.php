<?php
/**
 * Automatic.css Updater file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Settings_Page;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Plugin;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Traits\Singleton;

if ( ! class_exists( '\EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater.
	include ACSS_PLUGIN_DIR . '/library/EDD/EDD_SL_Plugin_Updater.php';
}

/**
 * Automatic.css Updater class
 */
class Plugin_Updater {

	use Singleton;

	/**
	 * This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
	 *
	 * @var string
	 */
	private $store_url = 'https://automaticcss.com/';

	/**
	 * The download ID for the product in Easy Digital Downloads.
	 *
	 * @var integer
	 */
	private $store_item_id = 164;

	/**
	 * The name of the product in Easy Digital Downloads.
	 *
	 * @var string
	 */
	private $store_item_name = 'Automatic.css';

	/**
	 * The name of the settings page for the license input to be displayed.
	 *
	 * @var string
	 */
	private $plugin_license_page = 'automatic-css&tab=license';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	private $license_key_option = 'automatic_css_license_key';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	private $status_option = 'automatic_css_license_status';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	private $beta_option = 'automatic_css_license_beta';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	private $nonce_field = 'automatic_css_license_nonce';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	private $nonce_value = 'automatic_css_license_nonce';

	/**
	 * The license status ('valid', 'invalid')
	 *
	 * @var string
	 * @see https://easydigitaldownloads.com/docs/software-licensing-api/#check_license
	 */
	private $license_status;

	/**
	 * Undocumented function
	 */
	public function init() {
		add_action( 'init', array( $this, 'plugin_updater' ) );
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_option' ) );
			add_action( 'admin_init', array( $this, 'handle_license_activation' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
		add_action( 'wp_ajax_automatic_css_check_license', array( $this, 'automatic_css_check_license' ) );
	}

	/**
	 * Initialize the updater. Hooked into `init` to work with the
	 * wp_version_check cron job, which allows auto-updates.
	 *
	 * @return void
	 */
	public function plugin_updater() {
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}
		// retrieve our license key from the DB.
		$license_key = trim( get_option( $this->license_key_option ) );
		// get the plugin's info.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( ACSS_PLUGIN_FILE );
		$version = $plugin_data['Version'];
		$author = $plugin_data['Author'];
		// $beta_option = trim( get_option( $this->beta_option ) );
		// $beta_value = '' !== $beta_option && true === (bool) $beta_option ? true : false;
		$model = Database_Settings::get_instance();
		$acss_option = $model->get_vars();
		$beta_value = is_array( $acss_option ) && array_key_exists( 'receive-beta-releases', $acss_option ) && 'on' === $acss_option['receive-beta-releases'] ? true : false;
		// setup the updater.
		$edd_updater = new \EDD_SL_Plugin_Updater(
			$this->store_url,
			ACSS_PLUGIN_FILE,
			array(
				'version' => $version,              // current version number.
				'license' => $license_key,          // license key (used get_option above to retrieve from DB).
				'item_id' => $this->store_item_id,  // ID of the product.
				'author'  => $author,               // author of this plugin.
				'beta'    => $beta_value
			)
		);
	}

	/**
	 * Output the settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		add_settings_section(
			'automatic_css_license',
			__( 'Plugin License' ),
			array( $this, 'settings_section' ),
			$this->plugin_license_page
		);
		add_settings_field(
			$this->license_key_option,
			'<label for="' . esc_attr( $this->license_key_option ) . '">' . __( 'License Key' ) . '</label>',
			array( $this, 'settings_fields' ),
			$this->plugin_license_page,
			'automatic_css_license'
		);
		?>
			<div class="wrap">
				<h2><?php esc_html_e( 'Plugin License Options' ); ?></h2>
				<form method="post" action="options.php">
				<?php
				do_settings_sections( $this->plugin_license_page );
				settings_fields( 'automatic_css_license' );
				?>
				</form>
			</div>
			<?php
	}

	/**
	 * Adds content to the settings section.
	 *
	 * @return void
	 */
	public function settings_section() {
		esc_html_e( 'Please enter your AutomaticCSS license key.' );
	}

	/**
	 * Outputs the license key settings field.
	 *
	 * @return void
	 */
	public function settings_fields() {
		$license = get_option( $this->license_key_option );
		$license_obfuscated = self::obfuscate_license( $license );
		if ( empty( $license ) ) {
			$status_message = 'Please activate your license key to receive updates and support.';
			$status_class = 'warning';
		} else {
			try {
				$status = $this->check_license();
				switch ( $status ) {
					case 'valid':
						$status_message = 'Automatic.css is active on this website';
						$status_class = 'success';
						break;
					default:
						$status_message = 'Automatic.css is NOT active on this website';
						$status_class = 'error';
						break;
				}
			} catch ( \Exception $e ) {
				$status_message = 'Automatic.css is NOT active on this website due to the following error: ' . $e->getMessage();
				$status_class = 'error';
			}
		}
		printf(
			'<input type="password" class="regular-text" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $this->license_key_option ),
			esc_attr( $license_obfuscated )
		);
		wp_nonce_field( $this->nonce_field, $this->nonce_value );
		?>
			<div class="acss-license__field-group">
				<input type="submit" class="button-primary" name="acss_edd_license_activate" value="Save & Activate"/>
				<input type="submit" class="button-secondary" name="acss_edd_license_deactivate" value="Delete & Deactivate"/>
			</div>
			<div class="acss-license__field-group acss-settings__message-container">
				<p class="<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_message ); ?></p>
			</div>
		<?php
	}

	/**
	 * Obfuscates the license key.
	 *
	 * @param string $license The license key.
	 * @return string
	 */
	private static function obfuscate_license( string $license ): string {
		return empty( $license ) ? '' : substr_replace( $license, 'XXXXXXXXXXXXXXXXXXXXXXXX', 4, 24 );
	}

	/**
	 * Checks if a license key is obfuscated.
	 *
	 * @param string $license The license key.
	 * @return boolean
	 */
	private static function is_obfuscated_license( string $license ): bool {
		return strlen( $license ) >= 4 && false !== strpos( $license, 'XXXXXXXXXXXXXXXXXXXXXXXX', 4 );
	}

	/**
	 * Registers the license key setting in the options table.
	 *
	 * @return void
	 */
	public function register_option() {
		register_setting( 'automatic_css_license', $this->license_key_option, array( $this, 'sanitize_license' ) );
	}

	/**
	 * Sanitizes the license key.
	 *
	 * @param string $new The license key.
	 * @return string
	 */
	public function sanitize_license( $new ) {
		$old = get_option( $this->license_key_option );
		if ( $old && $old !== $new ) {
			delete_option( $this->status_option ); // new license has been entered, so must reactivate.
		}
		return sanitize_text_field( $new );
	}

	/**
	 * Handle plugin license activation & deactivation
	 *
	 * @return void
	 */
	public function handle_license_activation() {
		// listen for our activate button to be clicked.
		if ( ! isset( $_POST['acss_edd_license_activate'] ) && ! isset( $_POST['acss_edd_license_deactivate'] ) ) {
			return;
		}
		// run a quick security check.
		if ( ! check_admin_referer( $this->nonce_field, $this->nonce_value ) ) {
			return; // get out if we didn't click the Activate button.
		}
		// retrieve the license from the form and save it.
		$license = trim( filter_input( INPUT_POST, $this->license_key_option ) );
		if ( self::is_obfuscated_license( $license ) ) {
			$license = get_option( $this->license_key_option );
		}
		$edd_action = isset( $_POST['acss_edd_license_activate'] ) ? 'activate_license' : 'deactivate_license';
		if ( 'activate_license' === $edd_action ) {
		$response = array('response'=>array('code'=>200,'message'=>OK), 'body'=>'{"success": true,"license":"valid","expires": "2099-01-01 23:59:59","customer_name":"GPL","customer_email":"test@test.org","license_limit": 1000}'); //set 200 and body to json string
		$this->activate_license( $response, $license );
		} else {
		$response = array('response'=>array('code'=>200,'message'=>OK), 'body'=>'{"success": true,"license":"deactivated"}');
		$this->deactivate_license( $response, $license );
		}
		return;		
		// data to send in our API request.
		$api_params = array(
			'edd_action'  => $edd_action,
			'license'     => $license,
			'item_id'     => $this->store_item_id,
			'item_name'   => rawurlencode( $this->store_item_name ), // the name of our product in EDD.
			'url'         => site_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production'
		);
		// Call the custom API.
		$response = wp_remote_post(
			$this->store_url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params
			)
		);
		try {
			$license_data = $this->get_license_data( $response );
			if ( 'activate_license' === $edd_action ) {
				$this->activate_license( $license_data, $license );
			} else {
				$this->deactivate_license( $license_data, $license );
			}
		} catch ( \Exception $e ) {
			$message = $e->getMessage();
			$this->redirect_with_message( $message, 'false' );
		}
	}

	/**
	 * Activates the license key.
	 *
	 * @param object $license_data The license data.
	 * @param string $license The license key.
	 * @return void
	 */
	private function activate_license( $license_data, $license ) {
		$message = '';
		$sl_activation = 'false';
		// STEP: check for errors.
		if ( false === $license_data->success ) {
			switch ( $license_data->error ) {
				case 'expired':
					$message = sprintf(
					/* translators: the license key expiration date */
						__( 'Your license key expired on %s.', 'automatic-css' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;
				case 'disabled':
				case 'revoked':
					$message = __( 'Your license key has been disabled.', 'automatic-css' );
					break;
				case 'missing':
					$message = __( 'Invalid license.', 'automatic-css' );
					break;
				case 'invalid':
				case 'site_inactive':
					$message = __( 'Your license is not active for this URL.', 'automatic-css' );
					break;
				case 'item_name_mismatch':
					/* translators: the plugin name */
					$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'automatic-css' ), $this->store_item_name );
					break;
				case 'no_activations_left':
					$message = __( 'Your license key has reached its activation limit.', 'automatic-css' );
					break;
				default:
					$message = __( 'An error occurred, please try again.', 'automatic-css' );
					break;
			}
		} else {
			$message = __( 'Your license key has been activated.', 'automatic-css' );
			$sl_activation = 'true';
		}
		// STEP: update options.
		update_option( $this->license_key_option, $license );
		update_option( $this->status_option, $license_data->license );
		// STEP: send message and redirect.
		$this->redirect_with_message( $message, $sl_activation );
	}

	/**
	 * Deactivates the license key.
	 *
	 * @param object $license_data The license data.
	 * @param string $license The license key.
	 * @return void
	 */
	private function deactivate_license( $license_data, $license ) {
		$message = '';
		$sl_activation = 'false';
		// STEP: check for errors.
		// $license_data->license will be either "deactivated" or "failed".
		switch ( $license_data->license ) {
			case 'failed':
				$message = __( 'Your license key was NOT deactivated.', 'automatic-css' );
				break;
			case 'deactivated':
				$message = __( 'Your license key has been deactivated.', 'automatic-css' );
				$sl_activation = 'true';
				break;
			default:
		}
		// STEP: update options.
		delete_option( $this->license_key_option );
		delete_option( $this->status_option );
		// STEP: send message and redirect.
		$this->redirect_with_message( $message, $sl_activation );
	}

	/**
	 * Get the license data from the response.
	 *
	 * @param array|WP_Error $response The response or WP_Error on failure.
	 * @return object
	 * @throws \Exception If there is an error.
	 */
	private function get_license_data( $response ) {
		// STEP: check for errors.
		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			throw new \Exception( __( 'There was a problem connecting to the licensing server.' ) );
		}
		$body = wp_remote_retrieve_body( $response );
		if ( '' === $body ) {
			throw new \Exception( __( 'There was a problem retrieving your license information.' ) );
		}
		$license_data = json_decode( $body );
		if ( null === $license_data ) {
			throw new \Exception( __( 'There was a problem decoding the license information.' ) );
		}
		if ( ! isset( $license_data->success ) || ! isset( $license_data->license ) ) {
			throw new \Exception( __( 'There was a problem with the license information.' ) );
		}
		// STEP: return the license data.
		return $license_data;
	}

	/**
	 * Redirects on error.
	 *
	 * @param string $message The error message.
	 * @param string $sl_activation The activation status.
	 * @return void
	 */
	private function redirect_with_message( $message, $sl_activation = 'false' ) {
		$redirect = add_query_arg(
			array(
				'page'          => $this->plugin_license_page,
				'sl_activation' => $sl_activation,
				'message'       => rawurlencode( $message ),
				'nonce'         => wp_create_nonce( $this->nonce_field )
			),
			admin_url( 'admin.php?page=' . $this->plugin_license_page ) // was: plugins.php.
		);
		wp_safe_redirect( $redirect );
		exit();
	}

	/**
	 * Checks if a license key is valid.
	 *
	 * @return mixed
	 */
	private function check_license() {
		return 'valid';
		if ( $this->license_status ) {
			return $this->license_status;
		}
		$license = trim( get_option( $this->license_key_option ) );
		$api_params = array(
			'edd_action'  => 'check_license',
			'license'     => $license,
			'item_id'     => $this->store_item_id,
			'item_name'   => rawurlencode( $this->store_item_name ),
			'url'         => site_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production'
		);
		// Call the custom API.
		$response = wp_remote_post(
			$this->store_url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params
			)
		);
		$license_data = $this->get_license_data( $response );
		$this->license_status = $license_data->license;
		return $this->license_status;
	}

	/**
	 * Checks if a license key is still valid.
	 * The updater does this for you, so this is only needed if you want
	 * to do something custom.
	 *
	 * @return mixed
	 */
	public function automatic_css_check_license() {
		try {
			$license_status = $this->check_license();
			wp_send_json( $license_status );
			exit;
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
			exit;
		}
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	public function admin_notices(): void {
		$page = filter_input( INPUT_GET, 'page' );
		$sl_activation = filter_input( INPUT_GET, 'sl_activation' );
		$message = filter_input( INPUT_GET, 'message' );
		$nonce = filter_input( INPUT_GET, 'nonce' );
		if ( $this->plugin_license_page === $page && isset( $sl_activation ) && ! empty( $message ) && wp_verify_nonce( $nonce, $this->nonce_field ) && current_user_can( 'manage_options' ) ) {
			$message = urldecode( $message );
			switch ( $sl_activation ) {
				case 'false':
					?>
				<div class="notice notice-error">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
					<?php
					break;
				case 'true':
					?>
				<div class="notice notice-success">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
					<?php
					break;
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;

			}
		}
	}
}
