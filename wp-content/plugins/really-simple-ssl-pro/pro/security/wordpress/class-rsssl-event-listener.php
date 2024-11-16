<?php
/**
 * Marcel Santing, Really Simple Plugins
 *
 * This PHP file contains the implementation of the Rsssl_Event_Listener class.
 *
 * @author Marcel Santing
 * @company Really Simple Plugins
 * @email marcel@really-simple-plugins.com
 * @package RSSSL\Pro\Security\WordPress
 */

namespace RSSSL\Pro\Security\WordPress;

use RSSSL\Pro\Security\WordPress\Captcha\Rsssl_Captcha;
use RSSSL\Pro\Security\WordPress\Eventlog\Events\Rsssl_Login_Success_Event;
use RSSSL\Pro\Security\WordPress\Eventlog\Events\Rsssl_Login_Failed_Event;
use RSSSL\Pro\Security\WordPress\Eventlog\Rsssl_Event_Type;
use Exception;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Login_Attempt;
use RuntimeException;
use WP_Error;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'rsssl_event_listener' ) ) {
	/**
	 * Class rsssl_event_listener
	 *
	 * This class is used to listen to events and log them to the database
	 * and adds the appropriate actions to the bespoke events. Like limit login attempts
	 *
	 * @package RSSSL\Pro\Security\WordPress
	 */
	class Rsssl_Event_Listener {

		/**
		 * Event code for successful login.
		 */
		public const LOGIN_SUCCESS        = '1000'; // code for successful login.
		public const LOGIN_FAILED         = '1001'; // code for failed login.

		/**
		 * Endpoints
		 */
		public const LOGIN_ENDPOINT = 'wp-login';


		/**
		 * The sanitized ip address.
		 *
		 * @var mixed The sanitized ip address.
		 */
		public $sanitized_ip;

		/**
		 * Constructor for the rsssl_event_listener class.
		 * Initializes the hooks for login events.
		 *
		 * @throws Exception If an error occurs during processing.
		 */
		public function __construct() {
            // Disable LLA when disable LLA or safe is defined
			if ( ( defined( 'RSSSL_DISABLE_LLA' ) && RSSSL_DISABLE_LLA ) || ( defined( 'RSSSL_SAFE_MODE' ) && RSSSL_SAFE_MODE ) ) {
				return;
			}

			// Especially for WooCommerce we need to check if the user is blocked.
			if ( ! is_user_logged_in() ) {
				add_action('woocommerce_before_customer_login_form', array($this, 'display_locked_out_message_woocommerce'), 10);
				// get the sanitized ip.
				$limit_login_attempts = new Rsssl_Limit_Login_Attempts();
				$ip_addresses = $limit_login_attempts->get_ip_address();
				$this->sanitized_ip = isset( $ip_addresses[0] ) ? filter_var( $ip_addresses[0], FILTER_VALIDATE_IP ) : null;
				$this->hook_init();
			}
		}

		/**
		 * Hook initialization method.
		 * Attaches the appropriate actions to the WordPress hooks.
		 *
		 * @throws Exception If an error occurs during processing.
		 */
		public function hook_init(): void {

			add_action( 'login_form', array( $this, 'inject_nonce_to_login_form' ) );
			if ( ! is_user_logged_in() ) {
				// we hook into the login page to display a message if the user is blocked.
				add_filter( 'login_message', array( $this, 'user_was_blocked' ) );
				//phpcs:ignore

				if ( isset( $_POST['log'] ) ) {
					$this->listen_to_post_request();
				}

				if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
					$this->listen_to_post_request_woocommerce_login();
				}

				/*
				 * We hook into the login and login failed events.
				 */
				add_filter( 'wp_login', array( $this, 'listen_to_successful_login_attempt' ), 10, 2 );
				add_filter( 'wp_login_failed', array( $this, 'listen_to_failed_login_attempt' ), 10, 1 );

				add_filter( 'authenticate', array( $this, 'validate_captcha' ), 30, 3 );
			}
		}

		/**
		 * Injects the captcha to the WordPress login form.
		 *
		 * @return void
		 */
		public function inject_captcha_to_login_form(): void {
			// We only inject the captcha if it is enabled.
			if ( rsssl_get_option( 'captcha_fully_enabled' ) && rsssl_get_option( 'limit_login_attempts_captcha' ) ) {
				echo esc_html( Rsssl_Captcha::render() );
			}
		}

		/**
		 * Validate the captcha for limited login attempts.
		 *
		 * @param  mixed  $user  The user object.
		 * @param  string  $username  The username provided by the user.
		 * @param  string  $password  The password provided by the user.
		 *
		 * @return WP_Error|WP_User The WP_Error object if captcha validation fails, otherwise the user object.
		 */
		public function validate_captcha( $user, string $username, string $password ) {
			// Only proceed with CAPTCHA validation if it is enabled and necessary.
			if ( rsssl_get_option( 'enable_limited_login_attempts' ) &&
				get_transient( 'rsssl_failed_login_attempt_' . $this->create_unique_id() ) &&
				rsssl_get_option( 'limit_login_attempts_captcha' )
			) {
				// Initialize the CAPTCHA object.
				$captcha = new Rsssl_Captcha();

				// Retrieve the CAPTCHA response sent by the user.
				$captcha_response = $captcha->captcha_provider->get_response_value();

				// Validate the CAPTCHA response.
				if ( ! $captcha->captcha_provider->validate( $captcha_response ) ) {
					// If CAPTCHA validation fails, return a WP_Error object.
					return new WP_Error( 'captcha_failed', __( '<strong>Error</strong>: Captcha validation failed.', 'really-simple-ssl' ) );
				}
			}
			return $user;
		}

		/**
		 * Display a message to the user in WooCommerce if the user is locked out.
		 *
		 * @return void
		 */
		public function display_locked_out_message_woocommerce() {
			// Check if 'locked_out' query parameter is present and is 'true'
			if (isset($_GET['locked_out']) && 'true' === $_GET['locked_out'] ) {
				// Here you can define the error message
				$error_message = __('Your access has been denied due to too many failed login attempts.', 'really-simple-ssl');

				// Use WooCommerce's function to add an error notice
				wc_add_notice($error_message, 'error');
				wc_print_notices();
			}
		}


		/**
		 * Listen to the POST request and perform necessary actions.
		 *
		 * @return void
		 *
		 * @throws Exception If an error occurs during processing.
		 */
		public function listen_to_post_request(): void {
			if ( isset( $_POST['log'] ) ) {
				// Check if nonce is set and verify it.
				if ( ! isset( $_POST['rsssl_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rsssl_login_nonce'] ) ), 'rsssl_login_action' ) ) {
					// Nonce verification failed.
					$login_url = remove_query_arg( '_wpnonce' );
					wp_safe_redirect( $login_url );
					exit;
				}

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$login_attempt = new Rsssl_Login_Attempt( sanitize_user( $_POST['log'] ), $this->sanitized_ip );

				if ( $login_attempt->is_login_blocked() ) {
					try {
						$nonce     = wp_create_nonce( 'rsssl_block_message' );
						$login_url = wp_login_url() . '?' . $login_attempt->block_state . '=true&_wpnonce=' . $nonce;
						wp_safe_redirect( $login_url );
						exit;
					} catch ( Exception $e ) {

					}
				}
			}
		}

		/**
		 * @return void
		 * @throws Exception
		 *
		 * Handle WooCommerce login
		 */
		public function listen_to_post_request_woocommerce_login(): void {
			if ( isset( $_POST['username'] ) ) {
				// Check if nonce is set and verify it.
				if ( ! isset( $_POST['woocommerce-login-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-login-nonce'] ) ), 'woocommerce-login' ) ) {
					// Nonce verification failed.
					$login_url = wc_get_page_permalink('myaccount');
					wp_safe_redirect( $login_url );
					exit;
				}
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$login_attempt = new Rsssl_Login_Attempt( wp_unslash( $_POST['username'] ), $this->sanitized_ip );
				if ( $login_attempt->is_login_blocked() ) {
					try {
						$nonce     = wp_create_nonce( 'rsssl_block_message' );
						$error_message = __( 'Your access has been denied, too many login attempts', 'really-simple-ssl' );
						// Add the error before form submission
						add_filter('woocommerce_process_login_errors', function($validation_error, $username, $password ) use ($error_message) {
							return new WP_Error( 'error', $error_message );
						}, 10, 3);
						$login_url = wc_get_page_permalink('myaccount') . '?' . $login_attempt->block_state . '=true&_wpnonce=' . $nonce;
						wp_safe_redirect( $login_url );
						exit;
					} catch ( Exception $e ) {

					}
				}
			}
		}

		/**
		 * Display a message to the user if the user is blocked.
		 *
		 * @return string|null The message to display.
		 */
		public function display_blocked_user_message(): ?string {
			// Check if the 'blocked' query argument is set.
			return __( 'Your access has been denied, please contact the webmaster for support', 'really-simple-ssl' );
		}

		/**
		 * Display a message to the user if the user is blocked.
		 *
		 * @param string $message The message to display.
		 *
		 * @return string The message to display.
		 */
		public function user_was_blocked( $message ): string {

			// Verify nonce for 'blocked' or 'locked_out' state.
			if ( ( isset( $_GET['blocked'] ) && 'true' === $_GET['blocked'] ) ||
				( isset( $_GET['locked_out'] ) && 'true' === $_GET['locked_out'] ) ) {
				if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'rsssl_block_message' ) ) {
					// Nonce verification failed.
					// we redirect to the login page. We will remove the wp_nonce from the url.
					$login_url = remove_query_arg( '_wpnonce' );
					wp_safe_redirect( $login_url );
					exit;
				}
			}

			// Display appropriate block message.
			if ( isset( $_GET['blocked'] ) && 'true' === $_GET['blocked'] ) {
				$message .= '<div id="login_error" class="notice notice-error">' . __(
					'Your access has been denied, please contact the webmaster for support',
					'really-simple-ssl'
				) . '.</div>';
			}

			if ( isset( $_GET['locked_out'] ) && 'true' === $_GET['locked_out'] ) {
				$message .= '<div id="login_error" class="notice notice-error">' . __(
					'Your access has been denied, too many login attempts',
					'really-simple-ssl'
				) . '.</div>';
			}

			return $message ?? '';
		}


		/**
		 * Listens to a successful login attempt and logs the event.
		 *
		 * @param  string  $login_username The username used for login.
		 * @param  WP_User $logged_in_user The logged-in user.
		 *
		 * @return WP_User
		 * @throws Exception If an error occurs during processing.
		 */
		public function listen_to_successful_login_attempt( string $login_username, WP_User $logged_in_user ): WP_User {

			// now we end the failed login attempt.
			$login_attempt = new Rsssl_Login_Attempt( sanitize_user( $login_username ), $this->sanitized_ip );
			$login_attempt->end_failed_login_attempt();
			// We are happy and log a successfully login.
			Rsssl_Login_Success_Event::handle_event( [ 'user_login' => sanitize_user( $login_username ), 'ip_address' => $this->sanitized_ip ] );

			return $logged_in_user;
		}

		/**
		 * Listens to a failed login attempt and logs the event.
		 *
		 * @param  string $username  The username used for login.
		 *
		 * @throws Exception If an error occurs during processing.
		 */
		public function listen_to_failed_login_attempt( string $username ): void {
			// If we are on the WooCommerce we add Captcha there.
			if (isset( $_POST['woocommerce-login-nonce'] )) {
				add_action('woocommerce_login_form', array( $this, 'inject_captcha_to_login_form' ));
			} else {
				add_action( 'login_form', array( $this, 'inject_captcha_to_login_form' ) );
			}

			set_transient( 'rsssl_failed_login_attempt_' .$this->create_unique_id(), true, 60 * 10 ); // Expires in 10 minutes.

			// now we start the failed login attempt.
			$login_attempt = new Rsssl_Login_Attempt( $username, $this->sanitized_ip );
			Rsssl_Login_Failed_Event::handle_event( [ 'user_login' => $username, 'ip_address' => $this->sanitized_ip ]);
			// if the user or ip is allowed we do not log the failed login attempt.
			if ( $login_attempt->is_login_allowed() ) {
				return;
			}
			// if the user already is locked out we do not log the failed login attempt.
			if ( $login_attempt->is_login_blocked() ) {
				return;
			}
			$login_attempt->start_failed_login_attempt( self::LOGIN_ENDPOINT );
		}

		/**
		 * Create a unique ID and set it as a cookie if the ID does not already exist.
		 *
		 * @return string The hashed unique ID.
		 */
		private function create_unique_id(): string {
			$cookie_name = 'rsssl_captcha_uid';

			if (!isset($_COOKIE[$cookie_name])) {
				// Generate a unique ID using uniqid() function with more entropy for better uniqueness
				$unique_id = uniqid('', true);

				$expiry_time = time()+60*10;  // Expires in 10 minutes.
				setcookie($cookie_name, $unique_id, [
					'expires' => $expiry_time,
					'httponly' => true, // Made it HTTP only for better security
					'samesite' => 'Strict',
				]);
				if(!isset($_COOKIE[$cookie_name])){ // Check if cookie was set
					// If not, start session and store the unique_id there
					if (session_status() == PHP_SESSION_NONE) {
						session_start();
					}
					$_SESSION[$cookie_name] = $unique_id;
					return hash('md5', $_SESSION[$cookie_name]);
				}
				// Normal operation with cookies - hashing and set transient
				return hash('md5', $unique_id);
			}

			return hash('md5', $_COOKIE[$cookie_name]); // Hashing for safety.
		}

		/**
		 * Delete a cookie.
		 *
		 * This method deletes a cookie by unsetting it and setting its expiration time to a pastime.
		 *
		 * @return void
		 */
		public function delete_cookie(): void {
			$cookie_name = 'rsssl_captcha_uid';
			if (isset($_COOKIE[$cookie_name])) {
				unset($_COOKIE[$cookie_name]);
				setcookie($cookie_name, '', time() - 3600, '/');
			}

			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}

			// Check if the session variable exists
			if (isset($_SESSION[$cookie_name])) {
				// Remove the session variable
				unset($_SESSION[$cookie_name]);
			}
		}

		/**
		 * Inject a nonce field into the WordPress login form.
		 */
		public function inject_nonce_to_login_form(): void {
			wp_nonce_field( 'rsssl_login_action', 'rsssl_login_nonce' );
		}
	}
}

/**
 * Initializes the rsssl_event_listener class.
 *
 * @return void
 */
function rsssl_initialize_event_listener() {
	new Rsssl_Event_Listener();
}

// This is where you're adding it to the 'init' action.
add_action( 'init', '\RSSSL\Pro\Security\WordPress\rsssl_initialize_event_listener' );
