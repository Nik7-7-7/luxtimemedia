<?php
/**
 * File: login-attempt.php
 *
 * This file is used to listen to login attempts and log them to the database.
 *
 * @package RSSSLPRO\Security\WordPress\limitlogin
 * @author Marcel Santing
 */

namespace RSSSL\Pro\Security\WordPress\Limitlogin;
require_once rsssl_path . '/lib/admin/class-helper.php';
use RSSSL\lib\admin\Helper;
require_once rsssl_path . 'pro/security/wordpress/eventlog/class-rsssl-event-type.php';
require_once rsssl_path . 'pro/security/wordpress/class-rsssl-event-log.php';

use Exception;
use RSSSL\Pro\Security\WordPress\Eventlog\Rsssl_Event_Type;
use RSSSL\Pro\Security\WordPress\Rsssl_Limit_Login_Attempts;
use RSSSL\Pro\Security\WordPress\Rsssl_Event_Log;

/**
 * Class Rsssl_Login_Attempt
 * This class is used to listen to events and log them to the database
 * and adds the appropriate actions to the bespoke events. Like limit login attempts
 *
 * @package RSSSLPRO\Security\wordpress\limitlogin
 *
 * @author Marcel Santing
 */
class Rsssl_Login_Attempt {
	use Helper;
	const TABLE     = 'rsssl_login_attempts';
	const USER_TYPE = 'username';

	const FAILED_BY_IP_CODE_LOCKOUT   = '1020'; // temp blocklist.
	const FAILED_BY_IP_CODE           = '1051'; // blocklist.
	const FAILED_BY_USER_CODE_LOCKOUT = '1010'; // temp blocklist.
	const FAILED_BY_USER_CODE         = '1041'; // blocklist.
	const FAILED_BY_COUNTRY_CODE      = '1052'; // blocklist.
	const BLOCK_REMOVED_IP            = '1021';

	const IP_TYPE = 'source_ip';

	// statuses.
	const LOCKED   = 'locked';
	const UNLOCKED = 'unlocked';

	const BLOCKED                    = 'blocked';
	const BLOCKED_BY_TEMP_USER_BLOCK = '1040';

	const BLOCKED_BY_TEMP_IP_BLOCK = '1050';


	/**
	 * The username.
	 *
	 * @var mixed
	 */
	private $username;
	/**
	 * The sanitized ip address.
	 *
	 * @var mixed
	 */
	private $ip;

	/**
	 * Whether the settings are validated.
	 *
	 * @var bool
	 */
	public $settings_validated = false;

	/**
	 * The limit of login attempts.
	 *
	 * @var int
	 */
	private $limit = 5;

	/**
	 * The duration of the block in seconds.
	 *
	 * @var int
	 */
	private $block_duration = 900;

	/**
	 * The duration of the block in seconds before locked account will be deleted.
	 *
	 * @var int
	 */
	private $account_blocked_duration = 1800;

	/**
	 * The block state.
	 *
	 * @var string
	 */
	public $block_state = '';


	/**
	 * LoginAttempt constructor.
	 *
	 * @param  string $username The username.
	 * @param  string $ip The sanitized ip address.
	 *
	 * @throws Exception If an error occurs during processing.
	 * @return void
	 */
	public function __construct( string $username, string $ip ) {

		// we sanitize the username and ip.
		$username       = sanitize_user( $username );
		$ip             = sanitize_text_field( $ip );
		$this->username = $username;
		$this->ip       = $ip;
		$this->validate_settings();
		add_action(
			'rsssl_five_minutes_cron',
			array( $this, 'cleanup_locked_accounts' )
		);
	}

	/**
	 * Determines whether the login attempt is allowed. Needs to be hooked before the login attempt.
	 *
	 * @return boolean
	 * @throws Exception If an error occurs during processing.
	 */
	public function is_login_blocked(): bool {
		if ( ! $this->settings_validated ) {
			// if the settings are not validated we return false.
			return false;
		}

		// we check if the ip is whitelisted whiteLIST entries or blocklisted based on the ip.
		$result_ip      = ( new Rsssl_Limit_Login_Attempts() )->check_request();
		$result_user    = ( new Rsssl_Limit_Login_Attempts() )->check_request_for_user( $this->username );
		$result_country = ( new Rsssl_Limit_Login_Attempts() )->check_request_for_country();

		if ( 'allowed' === $result_ip || 'allowed' === $result_user ) {
			return false;
		}

		// If we get here, then the IP is not whitelisted, and the user is not whitelisted, and the country is not whitelisted.
		// So we check if the IP is blocked.
		if ( 'blocked' === $result_ip || 'blocked' === $result_user || 'blocked' === $result_country ) {
			if ( 'blocked' === $result_ip ) {
				$this->log_event( Rsssl_Event_Type::login_blocked( $this->username, self::FAILED_BY_IP_CODE ) );
				// now we lockout also the user.
				$this->lockout( $this->username, self::USER_TYPE );
				$this->block_state = 'blocked';
				return true;
			}
			if ( 'blocked' === $result_user ) {
				$this->log_event( Rsssl_Event_Type::login_blocked( $this->username, self::FAILED_BY_USER_CODE ) );
				$this->lockout( $this->ip, self::IP_TYPE );
				$this->block_state = 'blocked';
				return true;
			}
			if ( 'blocked' === $result_country ) {
				$this->log_event( Rsssl_Event_Type::login_blocked( $this->username, self::FAILED_BY_COUNTRY_CODE ) );
				$this->block_state = 'blocked';
				return true;
			}
			$this->block_state = 'blocked';

			return true;
		}

		// If we are here all the criteria is not whitelisted or blocked, so we check if the login is locked out.
		if ( ! $this->exists_login_attempt( self::USER_TYPE ) && ! $this->exists_login_attempt( self::IP_TYPE ) ) {
			return false;
		}

		$attempts     = $this->get_login_attempts();
		$blocked      = false;
		$user_blocked = false;
		$ip_blocked   = false;
		foreach ( $attempts as $attempt ) {
			// at this point, the number of attempts passes the limit.
			$last_failed    = $attempt->last_failed; // last time the attempt failed.
			$block_duration = $this->block_duration; // duration of the block in seconds.
			$block_end      = $last_failed + $block_duration; // end of the block.
			$current_time   = time();

			// Check if current time is outside of block duration.
			$is_within_block_duration = $current_time <= $block_end;
			if ( true === (bool) $attempt->blocked ) {
				// if the attempt is blocked we check if the block is still valid.
				if ( $is_within_block_duration ) {
					// Check if the user is blocked.
					$user_blocked = self::USER_TYPE === $attempt->attempt_type;
					// Check if the IP is blocked.
					$ip_blocked = self::IP_TYPE === $attempt->attempt_type;
					$blocked    = true;

					// we uodate the counter.
					$this->update_failed_login_attempt( $attempt->attempt_type );

					// based on the correct attempt type we log the event.
					if ( self::USER_TYPE === $attempt->attempt_type ) {
						$this->log_event( Rsssl_Event_Type::login_blocked( $this->username, self::BLOCKED_BY_TEMP_USER_BLOCK ) );
					}
					if ( self::IP_TYPE === $attempt->attempt_type ) {
						$this->log_event( Rsssl_Event_Type::login_blocked( $this->username, self::BLOCKED_BY_TEMP_IP_BLOCK ) );
					}
				}
			}
		}

		// When the user or the ip is blocked we log an event for the non blocked attempt.
		if ( $blocked ) {
			// if the ip is blocked, but the user does not yet has an attempt we start the attempt.
			if ( $ip_blocked && ! $user_blocked ) {
				// we check if there is already an attempt for the user. if so we update.
				if ( $this->exists_login_attempt( self::USER_TYPE ) ) {
					$this->update_failed_login_attempt( self::USER_TYPE );
				} else {
					$this->start_failed_login_attempt( 'wp-login' );
				}
			}
			if ( $user_blocked && ! $ip_blocked ) {
				if ( $this->exists_login_attempt( self::IP_TYPE ) ) {
					$this->update_failed_login_attempt( self::IP_TYPE );
				} else {
					$this->start_failed_login_attempt( 'wp-login' );
				}
			}
			$this->block_state = 'locked_out';
		}

		return $blocked;
	}

	/**
	 * Logs an event.
	 *
	 * @param  array $event_type The event type.
	 *
	 * @return void
	 * @throws Exception If an error occurs during processing.
	 */
	private function log_event( array $event_type ) {
		Rsssl_Event_Log::log_event( (array) $event_type, 60 );
	}

	/**
	 * We start the failed login attempt.
	 *
	 * @param  string $endpoint The endpoint.
	 *
	 * @throws Exception If an error occurs during processing.
	 */
	public function start_failed_login_attempt( string $endpoint ): void {
		if ( ! $this->settings_validated ) {
			// there are no settings, so we just sit back and let loose the dogs of war.
			return;
		}
		$by_user = false;
		$by_ip   = false;
		// First, we check if there is already a failed login attempt for both IP and Username.
		if ( $this->exists_login_attempt( self::USER_TYPE ) ) {
			// If there are existing attempts, we update them.
			$this->update_failed_login_attempt( self::USER_TYPE );
			$by_user = true;
		}

		if ( $this->exists_login_attempt( self::IP_TYPE ) ) {
			$this->update_failed_login_attempt( self::IP_TYPE );
			$by_ip = true;
		}

		// Start the login by user attempt if none was there.
		if ( ! $by_user ) {
			// If there are no existing attempts, we add new login attempts for both IP and Username.
			$this->add_failed_login_attempt( self::USER_TYPE, $endpoint );
		}

		// Start the login by ip attempt if none was there.
		if ( ! $by_ip ) {
			$this->add_failed_login_attempt( self::IP_TYPE, $endpoint );
		}
	}

	/**
	 * We end the failed login attempt.
	 * We delete the login attempt from the database.
	 *
	 * @return void
	 * @throws Exception If an error occurs during processing.
	 */
	public function end_failed_login_attempt() {
		// we check if the settings are validated.
		if ( ! $this->settings_validated ) {
			// there are no settings, so we just sit back and let loose the dogs of war.
			return;
		}
		// We delete the login attempt from the database.
		$this->delete_failed_login_attempt();
	}

	/**
	 * We check if the login attempt is allowed.
	 *
	 * @param object $attempt The attempt object.
	 *
	 * @return boolean
	 */
	private function check_attempts( object $attempt ): bool {
		// if the attempts by username are more than the limit we return true.
		if ( $attempt->attempts >= $this->limit ) {
			return true;
		}

		return false;
	}

	/**
	 * We check if the setting is activated.
	 *
	 * @return bool
	 */
	public static function activated(): bool {
		$self = new self( '', '' );

		return $self->settings_validated;
	}

	/**
	 * We add a failed login attempt.
	 *
	 * @param string $attempt_type The attempt type.
	 * @param string $endpoint The endpoint of the url.
	 *
	 * @return void
	 */
	private function add_failed_login_attempt( string $attempt_type, string $endpoint ) {
		global $wpdb;
		$table      = self::TABLE;
		$now        = time();
		$ip         = filter_var( $this->ip, FILTER_VALIDATE_IP );
		$username   = sanitize_user( $this->username );
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$data = array(
			'first_failed'  => $now,
			'last_failed'   => $now,
			'attempt_type'  => $attempt_type,
			'user_agent'    => $user_agent,
			'attempt_value' => ( self::IP_TYPE === $attempt_type ) ? $ip : $username,
			'attempts'      => 1,
			'endpoint'      => $endpoint,
		);

		$format = array(
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->replace( $wpdb->base_prefix . $table, $data, $format );
	}

	/**
	 * Check if an entry exists for the given IP or username.
	 *
	 * @param  string $attempt_type  The type of attempt (IP or Username).
	 *
	 * @return bool
	 * @throws Exception If an error occurs during processing.
	 */
	private function exists_login_attempt( $attempt_type ) {
		global $wpdb;
		$ip       = $this->ip;
		$username = $this->username;

		// Check if the table exists using $wpdb functions.
		$table_name = $wpdb->base_prefix . self::TABLE;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$tables = $wpdb->get_col( 'SHOW TABLES', 0 );

		if ( ! in_array( $table_name, $tables, true ) ) {
			return false;
		}

		// We count the number of attempts for the ip or username.
		$sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM  ' . $table_name . ' WHERE attempt_type = %s AND attempt_value IN ( %s, %s )',
			$attempt_type,
			$ip,
			$username
		);
		// phpcs:ignore WordPress.DB
		$result = $wpdb->get_var( $sql );

		// Will return a string '1' if exists, '0' otherwise.
		return '1' === $result;
	}

	/**
	 * We update the failed login attempt.
	 *
	 * @param string $attempt_type The attempt type.
	 * @throws Exception If an error occurs during processing.
	 */
	private function update_failed_login_attempt( $attempt_type ) {

		global $wpdb;
		$table    = $wpdb->base_prefix . self::TABLE;
		$now      = time();
		$ip       = filter_var( $this->ip, FILTER_VALIDATE_IP );
		$username = sanitize_user( $this->username );
		try {
			$existing_attempts = $this->get_login_attempts( $attempt_type );
		} catch ( Exception $e ) {
			// handle exception in a better way.
			$this->log( $e->getMessage() );
		}

		$this->increment_attempts_for_all( $existing_attempts, $table, $now );

		$attempts = $this->get_login_attempts( $attempt_type );

		foreach ( $attempts as $attempt ) {
			if ( $this->does_login_attempt_need_to_be_blocked( $attempt ) ) {
				$this->log_event_if_blocked( $attempt_type );
			}
		}
	}

	/**
	 * We update the attempt count only.
	 *
	 * @param array  $existing_attempts The existing attempts already failed.
	 * @param string $table The table name.
	 * @param int    $time The current time.
	 *
	 * @return void
	 */
	private function increment_attempts_for_all( array $existing_attempts, string $table, int $time ) {
		global $wpdb;
		if ( $existing_attempts ) {
			foreach ( $existing_attempts as $existing_attempt ) {
				$data = array(
					'last_failed' => $time,
					'attempts'    => $existing_attempt->attempts + 1,
				);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update(
					$table,
					$data,
					array( 'id' => $existing_attempt->id ),
					array( '%d' )
				);
			}
		}
	}

	/**
	 * The one true logger.
	 *
	 * @param string $attempt_type The attempt type.
	 * @throws Exception If an error occurs during processing.
	 * @return void
	 */
	private function log_event_if_blocked( string $attempt_type ) {
		// if the user exists in the users table we log an event.
		if ( self::USER_TYPE === $attempt_type ) {
			// we log the event.
			$this->log_event( Rsssl_Event_Type::login_blocked( $this->username, self::FAILED_BY_USER_CODE_LOCKOUT ) );
		}

		if ( self::IP_TYPE === $attempt_type ) {
			// we log the event.
			$this->log_event( Rsssl_Event_Type::login_blocked( $this->ip, self::FAILED_BY_IP_CODE_LOCKOUT ) );
		}
	}


	/**
	 * We delete the login attempt from the database.
	 *
	 * @return void
	 * @throws Exception If an error occurs during processing.
	 */
	private function delete_failed_login_attempt(): void {
		if ( ! $this->exists_login_attempt( self::USER_TYPE ) && ! $this->exists_login_attempt( self::IP_TYPE ) ) {
			return;
		}

		global $wpdb;
		$table    = $wpdb->base_prefix . self::TABLE; // assuming your table has a WP prefix.
		$ip       = $this->ip;
		$username = $this->username;
		// We only delete if status is locked or null and the attempt_value is not $ip or $username.
		$sql = $wpdb->prepare(
			"DELETE FROM {$wpdb->base_prefix}rsssl_login_attempts  WHERE attempt_value IN ( %s, %s ) AND status IS NULL",
			$ip,
			$username
		);
		// phpcs:ignore WordPress.DB
		$wpdb->query( $sql );
	}

	/**
	 * We get the login attempt from the database.
	 *
	 * @param  string|null $attempt_type The attempt type.
	 * @return array
	 */
	private function get_login_attempts( string $attempt_type = null ): array {
		global $wpdb;
		$table    = $wpdb->base_prefix . self::TABLE;
		$ip       = $this->ip;
		$username = $this->username;

		if ( null !== $attempt_type ) {
			$sql = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE attempt_type = %s AND attempt_value IN ( %s, %s )",
				$attempt_type,
				$ip,
				$username
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE attempt_value IN ( %s, %s )",
				$ip,
				$username
			);
		}

		try {
			// phpcs:ignore WordPress.DB
			$results = $wpdb->get_results( $sql );
		} catch ( Exception $e ) {
			$this->log( $e->getMessage() );
		}

		return $results;
	}

	/**
	 * We check if the settings are turned on, if yes we continue.
	 *
	 * @return void
	 */
	private function validate_settings(): void {
		$enabled = rsssl_get_option( 'enable_limited_login_attempts' );

		if ( ! $enabled ) {
			return; // We split since we do not need to validate the settings.
		}

		// Something went wrong with the database so this will not work.
		if ( ! self::check_if_table_exists() ) {
			return;
		}

		if ( $enabled ) {
			$this->limit          = rsssl_get_option( 'limit_login_attempts_amount' );
			$this->block_duration = (int) rsssl_get_option( 'limit_login_attempts_duration' ) * 60;
			if ( rsssl_get_option( 'limit_login_attempts_locked_out_duration' ) ) {
				$this->account_blocked_duration = (int) rsssl_get_option( 'limit_login_attempts_locked_out_duration' ) * 60;
			}
			$this->settings_validated = true;
		}
	}


	/**
	 * We check if the login attempt needs to be blocked.
	 *
	 * @param object $attempt The attempt object.
	 * @throws Exception If an error occurs during processing.
	 */
	private function does_login_attempt_need_to_be_blocked( object $attempt ): bool {
		// We check if the login attempt needs to be blocked.
		$blocked = false;
		if ( $this->check_attempts( $attempt ) ) {
			// if the attempt is already blocked we do not need to block it again.
			if ( true === (bool) $attempt->blocked ) {
				return false;
			}
			// at this point, the number of attempts passes the limit.
			$last_failed    = $attempt->last_failed; // last time the attempt failed.
			$block_duration = $this->block_duration; // duration of the block in seconds.
			$block_end      = $last_failed + $block_duration; // end of the block.
			$current_time   = time();

			// Check if current time is outside of block duration.
			$is_within_block_duration = $current_time <= $block_end;
			if ( $is_within_block_duration ) {
				$blocked = true;
				$status  = $this::LOCKED;
				// we now set blocked to 1.
				global $wpdb;
				$table = $wpdb->base_prefix . self::TABLE;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update(
					$table,
					array(
						'blocked'     => $blocked,
						'status'      => $status,
						'last_failed' => $current_time,
					),
					array( 'id' => $attempt->id )
				);
			}
		}

		return $blocked;
	}

	/**
	 * We clean up the locked accounts after they passed their block duration.
	 *
	 * @return void
	 */
	public static function cleanup_locked_accounts(): void {
		global $wpdb;
		$self     = new self( '', '' );
		$table    = $wpdb->base_prefix . self::TABLE;
		$duration = $self->account_blocked_duration;

		$time = time() - $duration;

		// Modify the SQL query to check for NULL or LOCKED status.
		$sql = "DELETE FROM {$table} WHERE (status IS NULL OR status = %s) AND last_failed <= %d";
		// phpcs:ignore WordPress.DB
		$wpdb->query( $wpdb->prepare( $sql, self::LOCKED, $time ) );
	}

	/**
	 * Checks if the specified table exists in the database.
	 *
	 * @return bool True if the table exists, false otherwise.
	 * @global object $wpdb The WordPress database object.
	 *
	 */
	public static function check_if_table_exists(): bool {
		global $wpdb;
		$table_name = $wpdb->base_prefix . self::TABLE;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$tables = $wpdb->get_col( 'SHOW TABLES', 0 );
		if ( in_array( $table_name, $tables, true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * We block the ip address.
	 *
	 * @return void
	 */
	public function store_blocked_ip() {
		global $wpdb;
		$table = self::TABLE;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update(
			$wpdb->base_prefix . $table,
			array(
				'blocked' => 1,
				'status'  => self::LOCKED,
			),
			array(
				'attempt_type'  => self::IP_TYPE,
				'attempt_value' => $this->ip,
			)
		);
	}

	/**
	 * Retrieves the number of failed login attempts for a specific username or IP address.
	 *
	 * @return int The number of failed login attempts.
	 */
	public function get_failed_login_attempts(): int {
		global $wpdb;
		$table = self::TABLE;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->base_prefix}rsssl_login_attempts WHERE attempt_value IN ( %s, %s )",
				$this->username,
				$this->ip
			)
		);
		return $wpdb->num_rows;
	}

	/**
	 * Updates the attempt count only.
	 *
	 * @param object $attempt The attempt object.
	 * @return void
	 */
	private function update_attempt_count_only( object $attempt ) {
		global $wpdb;
		$table = $wpdb->base_prefix . self::TABLE;

		// Update the attempt in the database.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update(
			$table,
			array(
				'attempts'    => $attempt->attempts + 1,
				'last_failed' => time(),
			),
			array( 'id' => $attempt->id )
		);
	}

	/**
	 * Checks username and ip address if they are trusted to login.
	 *
	 * @return true|void
	 */
	public function is_login_allowed() {
		$result_ip   = ( new Rsssl_Limit_Login_Attempts() )->check_request();
		$result_user = ( new Rsssl_Limit_Login_Attempts() )->check_request_for_user( $this->username );
		if ( 'allowed' === $result_ip || 'allowed' === $result_user ) {
			return true;
		}
	}

	/**
	 * Locks out a user based on failed login attempts.
	 *
	 * @param  string $value  The value associated with the failed attempt (e.g., username or IP address).
	 * @param  string $type  The type of attempt (e.g., 'username' or 'ip').
	 * @param  string $endpoint  Optional. The login endpoint where the attempt was made. Defaults to empty string.
	 *
	 * @return void
	 */
	private function lockout( string $value, string $type, string $endpoint = '' ): void {
		global $wpdb;
		$table      = self::TABLE;
		$now        = time();
		$ip         = filter_var( $this->ip, FILTER_VALIDATE_IP );
		$username   = sanitize_user( $this->username );
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$data = array(
			'first_failed'  => $now,
			'last_failed'   => $now,
			'attempt_type'  => $type,
			'user_agent'    => $user_agent,
			'attempt_value' => $value,
			'attempts'      => 3,
			'endpoint'      => 'wp-login',
			'blocked'       => 1,
			'status'        => 'locked',
		);

		$format = array(
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%d',
			'%s',
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->replace( $wpdb->base_prefix . $table, $data, $format );
	}
}
