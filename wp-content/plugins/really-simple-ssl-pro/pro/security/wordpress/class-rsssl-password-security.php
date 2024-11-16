<?php
/**
 * Filename: password-security.php
 *
 * @author Marcel Santing
 * @package Really_Simple_SSL
 */

namespace RSSSL\Pro\Security\WordPress;

use Traversable;
use WP_Error;
use WP_User;
use WP_User_Query;

/**
 * Class Password_Security
 * This class handles the password security settings
 * It disables to allow weak passwords
 * It enforces password protection with a strong password
 * It checks if the password needs to be changed
 * It sends an email to the user if the password needs to be changed
 * It redirects the user to the password change page if the password needs to be changed
 * It checks if the password is changed
 * It stores the last password change in the user metadata
 * It checks if the password needs to be changed
 *
 * @category   Really_Simple_SSL
 * @package    Really_Simple_SSL
 *
 * @author Marcel Santing
 */
class Rsssl_Password_Security {

	// Settings defaults.

	/**
	 * Enabled or disabled value for the password_security feature.
	 *
	 * @var mixed|string
	 */
	private $enforce_password_security_enabled;

	/**
	 * Feature enforce strong password.
	 *
	 * @var mixed|string
	 */
	private $enforce_frequent_password_change;

	/**
	 * Frequency of password change.
	 *
	 * @var mixed|string
	 */
	private $password_change_frequency; // Number of months. Default is 12 months.

	/**
	 * Roles that need to change their password.
	 *
	 * @var mixed|string
	 */
	private $password_change_roles;


	/**
	 * Password_Security constructor.
	 */
	public function __construct() {
		// Add settings.
		$this->enforce_password_security_enabled = rsssl_get_option( 'enforce_password_security_enabled' );

		if ( $this->enforce_password_security_enabled ) {
			$this->enforce_frequent_password_change = rsssl_get_option( 'enforce_frequent_password_change' );
			$this->password_change_roles            = rsssl_get_option( 'password_change_roles' );
			$this->password_change_frequency        = rsssl_get_option( 'password_change_frequency' ) ?? 12;

			$this->init();

			if ( is_user_logged_in() && is_admin() ) {
				$this->admin_init();
			}
		}
	}

	/**
	 * Initialize the password security settings. for the front end. So when no user is logged in.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_notices', array( &$this, 'password_change_required_notice' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'disable_allow_of_weak_passwords' ), 20 );
		if ( $this->enforce_frequent_password_change ) {
			// checks login if password needs to be changed.
			add_action( 'wp_login', array( $this, 'need_redirect' ), 9999, 2 );
		}
	}

	/**
	 * Initialize the password security settings. This is admin panel related
	 *
	 * @return void
	 */
	public function admin_init() {
		// disables allow of weak passwords.
		add_action( 'admin_enqueue_scripts', array( $this, 'disable_allow_of_weak_passwords' ), 20 );
		// enforce password protection with a strong password.
		add_filter( 'user_profile_update_errors', array( &$this, 'validate_password_reset' ), 10, 4 );
		add_filter( 'registration_errors', array( &$this, 'validate_password_reset' ), 10, 4 );
		add_filter( 'validate_password_reset', array( &$this, 'validate_password_reset' ), 10, 4 );

		add_action( 'after_password_reset', array( $this, 'add_last_password_change_to_user_meta' ), 10, 2 );
		// if the $enforce_frequency_password_change is true we add a cronjob to check for users that need to change their password.
		if ( $this->enforce_frequent_password_change ) {
			add_action( 'user_register', array( $this, 'store_last_password_meta_on_register' ) );
			// stores metadata after a password reset.
			add_action( 'profile_update', array( $this, 'on_profile_update_password_change' ), 10, 2 );
			// checks the url for the password change required parameter and displays a message.
			add_action( 'rsssl_daily_cron', array( $this, 'check_for_password_change_required_users' ), 10 );
			add_filter( 'manage_users_columns', array( $this, 'add_password_expires_on_column' ) ); // NOTE: for future development.
			add_filter( 'manage_users_custom_column', array( $this, 'add_password_expires_on_value_column' ), 10, 3 ); // NOTE: for future development.
			// we run this function only once after the enforce_frequent_password_change is enabled.
			if ( ! get_option( 'rsssl_pro_password_change_required_users_checked' ) ) {
				$this->add_meta_last_password_change_to_user_meta();
				update_option( 'rsssl_pro_password_change_required_users_checked', true, false );
			}
		}
	}

	/**
	 * Adds a column to the user.php page.
	 *
	 * @param  array $column The columns on the user.php page.
	 *
	 * @return array
	 */
	public function add_password_expires_on_column( array $column ): array {
		$column['password_expires_on'] = __( 'Password Expires On', 'really-simple-ssl' );
		return $column;
	}


	/**
	 * Adds a value to the column on the user.php page.
	 *
	 * @param  string $value The value of the column.
	 * @param  string $column_name The name of the column.
	 * @param  int    $user_id The user ID.
	 *
	 * @return string|null
	 */
	public function add_password_expires_on_value_column( string $value, string $column_name, int $user_id ): ?string {
		if ( 'password_expires_on' === $column_name ) {
			// Now we check the users' role.
			$user       = get_user_by( 'id', $user_id );
			$user_data  = get_userdata( $user->ID );
			$user_roles = (array) $user_data->roles;
			$password_change_roles = (array) $this->password_change_roles;

			// Check all roles of the user; if any of them are in the password_change_roles array.
			foreach ( $user_roles as $user_role ) {
				if ( in_array( $user_role, $password_change_roles, true ) ) {
					$last_password_change = (int) get_user_meta( $user->ID, 'rsssl_last_password_change', true );
					if ( $last_password_change ) {
						$expiration_time = strtotime(
							'+' . $this->password_change_frequency . ' months',
							$last_password_change
						);
						$value           = sprintf(
						// translators: %1$s: The date the password expires. %2$d: The number of days until the password expires.
							__( '%1$s - expires in %2$d days', 'really-simple-ssl' ),
							gmdate( 'Y-m-d', $expiration_time ),
							$this->get_diff_in_days( $expiration_time, time() )
						);
					}
				} else {
					// translators: %s: Not required.
					$value = __( 'Not required', 'really-simple-ssl' );
				}
			}
		}
		return $value;
	}

	/**
	 * Adds a password last change to meta when creating a new user.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return void
	 */
	public function store_last_password_meta_on_register( int $user_id ) {
		// we update the last_password_change metadata.
		update_user_meta( $user_id, 'rsssl_last_password_change', time() );
	}

	/**
	 * Adds metadata to store the last password change.
	 *
	 * @return void
	 */
	public function add_meta_last_password_change_to_user_meta() {
		if ( is_user_logged_in() ) { //
			// first we fetch all users.
			$users = get_users();
			foreach ( $users as $user ) {
				// we add the last_password_change metadata.
				$one_month_ago   = strtotime( '-1 month' );
				$one_month_ahead = strtotime( '+1 month' );
				$timestamp       = wp_rand( $one_month_ago, $one_month_ahead );
				update_user_meta( $user->ID, 'rsssl_last_password_change', $timestamp );
			}
		}
	}

	/**
	 * Redirects the user to the password reset if the password needs to be changed.
	 * This function is used in the cronjob.
	 * We use this function to check for password change required users in a multisite environment.
	 * We switch to the blog and check for password change required users.
	 * We switch back to the current blog.
	 * We run this function in the cronjob.
	 *
	 * @return void
	 */
	public function check_for_password_change_required_users_multisite() {
		$sites = get_sites();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$this->check_for_password_change_required_users();
			restore_current_blog();
		}
	}

	/**
	 * Checks for password change required users.
	 *
	 * @return void
	 */
	public function check_for_password_change_required_users() {
		$frequency_in_months = (int) $this->password_change_frequency;
		$meta_value          = strtotime( "-$frequency_in_months months" );

		$time_difference   = strtotime( '-8 days', $meta_value );
		$meta_value_7_less = min( $meta_value, $time_difference );

		$time_difference   = strtotime( '+8 days', $meta_value );
		$meta_value_7_more = max( $meta_value, $time_difference );

		$roles = $this->password_change_roles;

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role ) {
				$users_query_args = array(
					'role'       => $role,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'rsssl_last_password_change',
							'value'   => $meta_value_7_less,
							'compare' => '>=', // Date is greater than or equal to 7 days ago.
						),
						array(
							'key'     => 'rsssl_last_password_change',
							'value'   => $meta_value_7_more,
							'compare' => '<=', // Date is less than or equal to 7 days in the future.
						),
					),
				);

				$users_query = new WP_User_Query( $users_query_args );
				$users       = $users_query->get_results();
				if ( is_array( $users ) || $users instanceof Traversable ) {
					foreach ( $users as $user ) {
						// Check if the user needs to change his password.
						if ( $this->check_user_password_change( $user->user_login, $user ) ) {
							// Get the last password change metadata.
							$last_password_change = (int) get_user_meta(
								$user->ID,
								'rsssl_last_password_change',
								true
							);
							$expiration_time      = strtotime(
								'+' . $this->password_change_frequency . ' months',
								$last_password_change
							);

							$today = time();

							// If the diff in days is 0, send an email to the user.
							if ( (int) $this->get_diff_in_days( $expiration_time, $today ) === 0 ) {
								// we check if the first mail was send.
								$last_password_change_notification = (int) get_user_meta(
									$user->ID,
									'rsssl_last_password_change_notification',
									true
								);
								if ( $last_password_change_notification ) {
									// we don't send the mail again.
									continue;
								}
								// Send a password reset email.
								$this->send_password_reset( $user );
								// we update the meta that the first mail was send.
								update_user_meta( $user->ID, 'rsssl_last_password_change_notification', $today );
							}
							// If the diff in days is 7, generate a new password and send an email to the user.
							if ( (int) $this->get_diff_in_days( $expiration_time, $today ) === - 7 ) {
								// Generate a new password.
								$new_password = $this->generate_random_strong_password();
								// Update the user password.
								wp_set_password( $new_password, $user->ID );
								// Update the last password change metadata.
								update_user_meta( $user->ID, 'rsssl_last_password_change', time() );
								// Send a password reset email.
								$this->send_password_reset( $user, true );
								// we remove the meta that the first mail was send.
								delete_user_meta( $user->ID, 'rsssl_last_password_change_notification' );
							}
						}
					}
				}
			}
		}
	}


	/**
	 * Calculates the difference in days between two dates.
	 *
	 * @param  int $date1  The first date.
	 * @param  int $date2  The second date.
	 *
	 * @return float|int
	 */
	private function get_diff_in_days( int $date1, int $date2 ) {
		// Calculate the difference in seconds.
		$diff = abs( $date1 - $date2 );

		// Convert the diff in days.
		$diff = floor( $diff / ( 60 * 60 * 24 ) );

		// If date1 is less than date2, make the result negative.
		if ( $date1 < $date2 ) {
			if ( $diff > 0 ) {
				$diff = -$diff;
			}
		}

		return $diff;
	}

	/**
	 * Checks the url for the password change required parameter and displays a message.
	 *
	 * @return void
	 */
	public function password_change_required_notice() {
		if ( ! isset( $_GET['password_change_required'] ) || ! isset( $_GET['password_change_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_GET['password_change_nonce'] ) );  // Unslash the value.

		// Verify nonce.
		if ( ! wp_verify_nonce( $nonce, 'password_change_nonce_action' ) ) {
			// Nonce did not verify; possibly malicious intent.
			return;
		}
		?>
        <div class="notice notice-warning is-dismissible">
            <p>
				<?php
				esc_html_e(
					'Your password has expired. Please change your password.',
					'really-simple-ssl'
				);
				?>
            </p>
        </div>
		<?php
	}


	/**
	 * Validate password reset and add the time of the last password change to user meta.
	 *
	 * @param  WP_User $user  The WordPress user object.
	 */
	public function add_last_password_change_to_user_meta( WP_User $user ) {
		if ( is_user_logged_in() ) {
			// we update the last_password_change metadata.
			update_user_meta( $user->ID, 'rsssl_last_password_change', time() );
		}
	}

	/**
	 * Updates the last password change metadata.
	 *
	 * @param  int    $user_id  The user's ID.
	 * @param  object $old_user_data  The old user data.
	 *
	 * @return void
	 */
	public function on_profile_update_password_change( int $user_id, object $old_user_data ) {

		if ( is_user_logged_in() ) {
			// Always check for nonce before processing the form data. on the pass1 and pass2 fields.
			if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce(
					sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ),
					'update-user_' . $user_id
				) ) {
				return;
			}

			if ( ! isset( $_POST['pass1'] ) || ! isset( $_POST['pass2'] ) ) {
				return;
			}
			// Sanitize the POST data.
			$pass1 = sanitize_text_field( wp_unslash( $_POST['pass1'] ) );
			$pass2 = sanitize_text_field( wp_unslash( $_POST['pass2'] ) );

			// Check if the password is set and has changed.
			if ( $pass1 && ( empty( $pass2 ) || $pass1 === $pass2 ) ) {
				if ( wp_check_password( $pass1, $old_user_data->user_pass, $user_id ) ) {
					return; // Password hasn't changed.
				}

				// Password has changed.
				update_user_meta( $user_id, 'rsssl_last_password_change', time() );
			}
		}
	}


	/**
	 * Redirects the user to the password reset if the password needs to be changed.
	 *
	 * @param  string  $user_login  The user's login name.
	 * @param  WP_User $user  The user object.
	 *
	 * @return void
	 */
	public function need_redirect( $user_login, $user ) {
		if ( is_user_logged_in() ) {
			if ( $this->check_user_password_change( $user_login, $user ) ) {
				// We Log out the user.
				wp_logout();
				// Redirect to the password change page.
				$this->redirect_to_change_password_page();
			}
		}
	}

	/**
	 * Check user for last password change; if not, enforce change password at login.
	 *
	 * @param  string  $user_login  The user's login name.
	 * @param  WP_User $user  The user object.
	 *
	 * @return bool Whether the user needs to change their password.
	 */
	private function check_user_password_change( string $user_login, WP_User $user ): bool {
		$current_site_id = function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 1;
		if ( is_user_member_of_blog( $user->ID, $current_site_id ) ) {
			$user_data  = get_userdata( $user->ID );
			$user_roles = (array) $user_data->roles;
			$password_change_roles = (array) $this->password_change_roles;

			// Check all roles of the user; if any of them are in the password_change_roles array.
			foreach ( $user_roles as $user_role ) {
				if ( in_array( $user_role, $password_change_roles, true ) ) {
					$last_password_change = (int) get_user_meta( $user->ID, 'rsssl_last_password_change', true );
					if ( $last_password_change ) {
						// Determine if we need to redirect to the password change page.
						$expiration_time = $last_password_change + ( (int) $this->password_change_frequency * 30 * 24 * 60 * 60 ); // Convert frequency from months to seconds.
						if ( time() > $expiration_time ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}


	/**
	 * Disable checkbox to allow unsafe passwords.
	 *
	 */
	public function disable_allow_of_weak_passwords( $hook ) {
		// Check if our nonce is set and verify it.
		if ( isset( $_GET['action'], $_GET['_wpnonce'] ) && 'rp' === $_GET['action'] ) {
			$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'rp' ) ) {
				// The nonce is invalid.
				return;
			}
		}
		if ( 'user-new.php' !== $hook && 'profile.php' !== $hook && 'user-edit.php' !== $hook && ( ! isset( $_GET['action'] ) || 'rp' !== $_GET['action'] ) ) {
			return;
		}
		$style = '.pw-weak { display: none !important; }';
		echo '<style>.pw-weak { display: none !important; } .disabled-button {
        opacity: 0.5;
        cursor: not-allowed;
        border-color: #dcdcde!important;
        color: #dcdcde!important;
        pointer-events: none;
    }</style>';
		wp_add_inline_style( 'wp-admin', $style );
		wp_enqueue_script( 'disable-weak-passwords', '', array( 'jquery', 'user-profile' ), false, true );
		$script = '
			jQuery(document).ready(function($) {
				let enforceDisableInterval;
				$("#pass1").off("keyup");
				$("#pass1").off("change");
				function enforceButtonState() {
					var strength = wp.passwordStrength.meter($("#pass1").val(), wp.passwordStrength.userInputDisallowedList());
					if (strength <= 3) {
						$("#submit, #wp-submit, #createusersub").addClass("button-default");
						$("#submit, #wp-submit, #createusersub").removeClass("button-primary");
						$("#submit, #wp-submit, #createusersub").addClass("disabled-button");
						$("#submit, #wp-submit, #createusersub").prop("disabled", true);
					} else {
						$("#submit, #wp-submit, #createusersub").prop("disabled", false);
						$("#submit, #wp-submit, #createusersub").removeClass("button-default");
						$("#submit, #wp-submit, #createusersub").addClass("button-primary");
						$("#submit, #wp-submit, #createusersub").removeClass("disabled-button");
						if (enforceDisableInterval) {
							clearInterval(enforceDisableInterval);
							enforceDisableInterval = null;
						}
					}
				}
		
				$("#pass1").on("keyup", function() {
					// Clear any existing interval to prevent multiple intervals running
					if (enforceDisableInterval) {
						clearInterval(enforceDisableInterval);
					}
					enforceDisableInterval = setInterval(enforceButtonState, 100);
					enforceButtonState(); // Also enforce immediately
				});
		
				// Optional: Clear the interval on form submit if the button is enabled
				$("form").on("submit", function() {
					if (enforceDisableInterval) {
						clearInterval(enforceDisableInterval);
					}
				});
			});
		';
		// Add the script to the user profile page.
		wp_add_inline_script(
			'user-profile',
			$script
		);
	}


	/**
	 * Validate password reset.
	 *
	 * @param  WP_Error $errors  A WP_Error object containing any errors encountered during registration.
	 * @param  bool     $update  Whether this is a user update.
	 * @param  null     $user  WP_User object if a user is logged in, null if reset is attempted without logging in.
	 *
	 * @return WP_Error Updated errors.
	 */
	public function validate_password_reset( WP_Error $errors, bool $update, $user ): WP_Error {
		if ( ! is_user_logged_in() ) {
			return $errors;
		}

		if ( isset( $_POST['pass1'] ) && ( isset( $_POST['_wpnonce'] ) || isset( $_POST['_wpnonce_create-user'] ) ) ) {
			$nonce = '';
			if ( isset( $_POST['_wpnonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
			} elseif ( isset( $_POST['_wpnonce_create-user'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce_create-user'] ) );
			}
			$password = sanitize_text_field( wp_unslash( $_POST['pass1'] ) );

			// if the user is not logged in, we check the nonce for the create-user action.
			if ( ! $update ) {
				if ( ! wp_verify_nonce( $nonce, 'create-user' ) ) {
					// We return a WP_Error.
					$errors = new WP_Error( 'pass', __( 'Invalid nonce.', 'really-simple-ssl' ) );
				}
			} elseif ( ! wp_verify_nonce( $nonce, 'update-user_' . $user->ID ) ) {
				// if the user is logged in, we check the nonce for the update-user action.
				$errors = new WP_Error( 'pass', __( 'Invalid nonce.', 'really-simple-ssl' ) );
			}
			// Check if this is a new user creation or an existing user password reset.
			if ( ! $update ) {
				if ( 'weak' === $this->check_password_strength( $password ) ) {
					// New user creation: Validate password based on your criteria.
					$weakness_reasons = $this->get_weakness_reasons( $password );

					if ( ( '' !== $weakness_reasons ) ) {
						$hint_message = $this->get_weakness_reasons( $password );
						$errors->add(
							'pass',
							sprintf(
								'<strong>%s</strong>',
								$hint_message
							)
						);
					}
				}
			} elseif ( 'weak' === $this->check_password_strength( $password, $user->ID ) ) {
				$weakness_reasons = $this->get_weakness_reasons( $password, $user->ID );
				if ( ( '' !== $weakness_reasons ) ) {
					$hint_message = $this->get_weakness_reasons( $password, $user->ID );
					$errors->add(
						'pass',
						sprintf(
							'<strong>%s</strong>',
							$hint_message
						)
					);
				}
			}
		}
		return $errors;
	}


	/**
	 * Get weakness reasons for a weak password.
	 *
	 * @param  string   $password  The password to check for weaknesses.
	 * @param  int|null $user_id  Optional. The ID of the user for whom to check the password. Default null.
	 *
	 * @return string A message describing the reason for the password's weakness, or an empty string if there's no reason found.
	 */
	public function get_weakness_reasons( string $password, $user_id = null ): string {
		$identity_values = $this->get_identity_values( $user_id );

		foreach ( $identity_values as $value ) {
			if ( '' !== $value && false !== strpos( $password, $value ) ) {
				return __(
					'Your password contains (part of) your (user)name or email address. Choose a different password',
					'really-simple-ssl'
				);
			}
		}

		return '';
	}


	/**
	 * Get identity values for the user.
	 *
	 * @param  int|null $user_id  The user ID. Default null.
	 *
	 * @return array An array containing identity values.
	 */
	private function get_identity_values( int $user_id = null ): array {

		if ( null === $user_id && is_user_logged_in() ) {
			// this is a new user, so we check the nonce again so that Rogier is happy as a kite.
			if ( ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_wpnonce_create-user'] ?? '' ) ),
				'create-user'
			) ) {
				// We return something.
				return array();
			}

			// Ensure $_POST variables are set before accessing them.
			$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';
			$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
			$last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
			$nickname   = isset( $_POST['nickname'] ) ? sanitize_text_field( wp_unslash( $_POST['nickname'] ) ) : '';
			$user_email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';

			$identity_values = array( $user_login, $first_name, $last_name, $nickname );

			// If the user_email is not set, we don't need to check it or when it's even set badly or incomplete.
			if ( '' === $user_email || false === strpos( $user_email, '@' ) ) {
				return array_filter( $identity_values ); // Remove empty values.
			}

			$email_parts       = explode( '@', $user_email );
			$identity_values[] = $email_parts[0];

			$domain_parts      = isset( $email_parts[1] ) ? explode( '.', $email_parts[1] ) : array();
			$identity_values[] = $domain_parts[0];

			return array_filter( $identity_values ); // Remove empty values.
		} else {
			// Fetching user data.
			$user_data = get_userdata( $user_id )->data;

			// Fetching user_metadata.
			$user_meta       = get_user_meta( $user_id );
			$identity_values = array(
				$user_data->user_login,
				$user_meta['first_name'][0] ?? '',
				$user_meta['last_name'][0] ?? '',
				$user_meta['nickname'][0] ?? '',
			);

			// If the user_email is not set, we don't need to check it or when it's even set badly or incomplete.
			if ( '' === $user_data->user_email || false === strpos( $user_data->user_email, '@' ) ) {
				return array_filter( $identity_values ); // Remove empty values.
			}

			$email_parts       = explode( '@', $user_data->user_email );
			$identity_values[] = $email_parts[0];

			$domain_parts      = isset( $email_parts[1] ) ? explode( '.', $email_parts[1] ) : array();
			$identity_values[] = $domain_parts[0];

			return array_filter( $identity_values ); // Remove empty values.
		}
	}


	/**
	 * Validating password strength.
	 *
	 * @param  string   $password  The password to check.
	 * @param  int|null $user_id  The user ID. Default null.
	 *
	 * @return string
	 */
	public function check_password_strength( string $password, int $user_id = null ): string {
		$identity_values = $this->get_identity_values( $user_id );
		foreach ( $identity_values as $value ) {
			if ( '' !== $value && false !== strpos( $password, $value ) ) {
				return 'weak';
			}
		}
		return 'strong';
	}


	/**
	 * Generates a random strong password.
	 *
	 * @return string
	 */
	private function generate_random_strong_password(): string {
		$length   = 12;
		$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
		$password = substr( str_shuffle( $chars ), 0, $length );

		return $password;
	}

	/**
	 * Redirects the user to the password reset if the password needs to be changed.
	 *
	 * @return void
	 */
	private function redirect_to_change_password_page() {
		// Base URL for the lost password page.
		$lost_password_url = network_site_url( 'wp-login.php?action=lostpassword' );

		// Add custom query arguments.
		$redirect_url = add_query_arg(
			array(
				'password_change_required' => 'true',
				'password_change_nonce'    => wp_create_nonce( 'password_change_nonce_action' ),
			),
			$lost_password_url
		);

		// Escape the URL and redirect.
		wp_safe_redirect( esc_url( $redirect_url ) );
		exit;
	}

	/**
	 * Sends an email to the user with a link for a password reset.
	 *
	 * @param WP_User $user // name for the user.
	 * @param bool $locked
	 *
	 * @return void
	 */
	private function send_password_reset( WP_User $user, $locked = false ): void {
		// We email the user with a link for a password reset.
		$reset_key = get_password_reset_key( $user );
		if ( empty( $reset_key ) ) {
			// If the reset key is not generated, we don't send an email
			return;
		}

		$reset_url = add_query_arg( 'key', $reset_key, wp_lostpassword_url() ); // Use the reset key to generate the reset URL

		// Initialize the mailer
		$mailer             = new rsssl_mailer();
		$mailer->branded    = false;
		$mailer->to         = $user->user_email;
		$reset_password_url = home_url( '/wp-login.php?action=lostpassword' );
		if ( $locked ) {
			$mailer->subject     = __( 'Your account is locked', 'really-simple-ssl' );
			$mailer->title       = __( 'Your account is locked', 'really-simple-ssl' );
			$message             = sprintf(
				__( 'Hi %1$s, Your password expired on %2$s , please change your password.', 'really-simple-ssl' ),
				$user->display_name, // Assuming this is how you get the user's display name
				wp_parse_url( home_url(), PHP_URL_HOST ) // Assuming this is how you get the domain
			);
			$mailer->button_text = __( 'Unlock Account', 'really-simple-ssl' );
			$message_block       = sprintf(
				__(
					'Copy this URL to your browser to change your password: %1$s ',
					'really-simple-ssl'
				),
				esc_url( $reset_password_url )
			);

			// Construct the message with blocks if needed
			$blocks[] = array(
				'title'   => __( 'Account Locked', 'really-simple-ssl' ),
				'message' => $message_block,
				'url'     => $reset_password_url,
			);
		} else {
			$mailer->subject     = __( 'Your password will expire in 7 days', 'really-simple-ssl' );
			$mailer->title       = __( 'Your password will expire in 7 days', 'really-simple-ssl' );
			$message             = sprintf(
				__( 'Hi %1$s, Your password on %2$s will expire in 7 days, please change your password.', 'really-simple-ssl' ),
				$user->display_name, // Assuming this is how you get the user's display name
				wp_parse_url( home_url(), PHP_URL_HOST ) // Assuming this is how you get the domain
			);
			$mailer->button_text = __( 'Change password', 'really-simple-ssl' );
			$message_block       = sprintf(
				__(
					'Copy this URL to your browser to change your password: %1$s ',
					'really-simple-ssl'
				),
				esc_url( $reset_password_url )
			);

			// Construct the message with blocks if needed
			$blocks[] = array(
				'title'   => __( 'Change your password', 'really-simple-ssl' ),
				'message' => $message_block,
				'url'     => $reset_password_url,
			);
		}

		// Set the message and blocks
		$mailer->message        = $message;
		$mailer->warning_blocks = $blocks; // Adjust as needed for the context of a password reset
		// Send the email
		$mailer->send_mail();
	}
}

$really_simple_ssl_password_protection = new Rsssl_Password_Security();

if ( rsssl_admin_logged_in() && ! is_multisite() ) {
	// Adding an hourly cronjob to check for users that need to change their password.
	add_action(
		'rsssl_three_hours_cron',
		array( $really_simple_ssl_password_protection, 'check_for_password_change_required_users' ),
		10
	);
} elseif ( rsssl_admin_logged_in() && is_multisite() ) {
	// Adding an hourly cronjob to check for users that need to change their password.
	add_action(
		'rsssl_three_hours_cron',
		array( $really_simple_ssl_password_protection, 'check_for_password_change_required_users_multisite' ),
		10
	);
}