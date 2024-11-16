<?php
/**
 * Marcel Santing, Really Simple Plugins
 *
 * This PHP file contains the implementation of the Rsssl_Event_Type class.
 *
 * @author Marcel Santing
 * @company Really Simple Plugins
 * @email marcel@really-simple-plugins.com
 * @package RSSSL\Pro\Security\WordPress\Eventlog
 */

namespace RSSSL\Pro\Security\WordPress\Eventlog;

use RSSSL\Pro\Security\WordPress\Rsssl_Limit_Login_Attempts;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Geo_Location;

/**
 * Class Rsssl_Event_Type
 *
 * @package Really_Simple_SSL_Pro
 */
class Rsssl_Event_Type {

	/**
	 * Username involved in the event.
	 *
	 * @var string
	 */
	public $username;

	/**
	 * The sanitized ip address.
	 *
	 * @var mixed
	 */
	public $get_ip_address;

	/**
	 * Rsssl_Event_Type constructor.
	 *
	 * @param  string $ip  The ip address.
	 */
	public function __construct( string $ip = '' ) {
		$lla = new Rsssl_Limit_Login_Attempts();

		if ( '' === $ip ) {
			$ip_address = $lla->get_ip_address()[0];
		} else {
			$ip_address = $this->process_cidr( $ip );
		}

		$this->get_ip_address = $this->validate_ip( $ip_address );
	}

	/**
	 * Processes the ip address.
	 * If the ip address contains a CIDR notation, it will be processed.
	 *
	 * @param  string $ip  The ip address.
	 *
	 * @return string The processed ip address.
	 */
	private function process_cidr( string $ip ): string {
		if ( strpos( $ip, '/' ) !== false ) {
			list( $ip, $mask ) = explode( '/', $ip );
			$ip                = filter_var( $ip, FILTER_VALIDATE_IP );
			$mask              = filter_var( $mask, FILTER_VALIDATE_INT );

			if ( false === $ip || false === $mask ) {
				// Handle invalid IP or mask.
				return '';
			}

			return $ip . '/' . $mask;
		}

		return $ip;
	}

	/**
	 * Validates the ip address.
	 *
	 * @param  string $ip  The ip address.
	 *
	 * @return string The validated ip address.
	 */
	private function validate_ip( string $ip ): string {
		$valid_ip = filter_var( $ip, FILTER_VALIDATE_IP );
		if ( false === $valid_ip ) {
			// Handle invalid IP.
			return '';
		}

		return $valid_ip;
	}

	/**
	 * Sets the values needed for the event that is being logged.
	 *
	 * @param  string $username  The username.
	 * @param  string $code  The event code.
	 *
	 * @return array
	 */
	public static function login( string $username, string $code ): array {
		// sanitize the username.
		$username = sanitize_user( $username );
		$self     = new self();

		// system or admin user.
		$admin    = $username;
		// these are the codes where we only want system as a username:
		$system_codes = [
			1010, 1011, 1020, 1021
		];

		if ( in_array( ( int )$code, $system_codes, true ) ) {
			$admin = __( 'System', 'really-simple-ssl' );
		}

		$lla      = new Rsssl_Limit_Login_Attempts();
		$admin_ip = $self->get_ip_address;

		if ( is_user_logged_in() ) {
			$user     = wp_get_current_user();
			$admin    = $user->user_login;
			$admin_ip = filter_var( $lla->get_ip_address()[0], FILTER_VALIDATE_IP );

		}

		return array(
			'timestamp'   => time(), // Unix TimeStamp.
			'event_id'    => $code,
			'event_type'  => self::get_event_type_by_code( $code ),
			'event_name'  => self::get_short_description_based_on_code( $code ),
			'severity'    => self::get_severity_based_on_code( $code ),
			'username'    => $admin,
			'source_ip'   => $admin_ip, // is sanitized in the function.
			'description' => self::get_description_based_on_code( $code, $username, $self->get_ip_address ),
			'event_data'  => self::get_event_data_based_on_code( $code, $username, $self->get_ip_address ),
		);
	}


	/**
	 * Sets the values needed for the event that is being logged.
	 *
	 * @param  string      $code  The event code.
	 * @param  string|null $ip  The ip address.
	 * @param  string|null $username  The username.
	 * @param  string|null $country  The country.
	 *
	 * @return array
	 */
	public static function add_to_block(
		string $code,
		string $ip = null,
		string $username = null,
		$country = ''
	): array {
		// sanitize the username.
		$username = sanitize_user( $username );
		$self     = new self( $ip );
		// in case of adding a country we check if the country is not null.
		if ( '1026' === $code || '1027' === $code ) {
			if ( is_null( $country ) ) {
				return array();
			}
		}

		$lla      = new Rsssl_Limit_Login_Attempts();
		$admin_ip = $self->get_ip_address;
		$admin    = __( 'System', 'really-simple-ssl' );

		if ( is_user_logged_in() ) {
			$user     = wp_get_current_user();
			$admin    = $user->user_login;
			$admin_ip = filter_var( $lla->get_ip_address()[0], FILTER_VALIDATE_IP );

		}

		return array(
			'timestamp'   => time(), // Unix TimeStamp.
			'event_id'    => $code,
			'event_type'  => self::get_event_type_by_code( $code ),
			'severity'    => self::get_severity_based_on_code( $code ),
			'event_name'  => self::get_short_description_based_on_code( $code ),
			'username'    => $admin, // is sanitized in the function.
			'source_ip'   => $admin_ip, // is sanitized in the function.
			'description' => self::get_description_based_on_code( $code, $username, $self->get_ip_address, $country ),
			'event_data'  => self::get_event_data_based_on_code( $code, $username, $self->get_ip_address, $country ),
		);
	}

	/**
	 * Kept this function for backwards compatibility or even for additional changes.
	 *
	 * @param  string $username  The username.
	 * @param  string $code  The event code.
	 *
	 * @return array
	 */
	public static function login_blocked( string $username, string $code ): array {
		return self::login( $username, $code );
	}

	/**
	 * Kept this function for backwards compatibility or even for additional changes.
	 *
	 * @param  string $username  The username.
	 * @param  string $code  The event code.
	 *
	 * @return array
	 */
	public static function login_failed_by_user( string $username, string $code ): array {
		return self::login( $username, $code );
	}

	/**
	 * Kept this function for backwards compatibility or even for additional changes.
	 *
	 * @param  string $ip  The ip address.
	 * @param  string $code  The event code.
	 *
	 * @return array
	 */
	public static function login_failed_by_ip( string $ip, string $code ): array {
		return self::login( $ip, $code );
	}

	/**
	 * Fetches the description based on the event code.
	 *
	 * @param  string      $code  The event code.
	 * @param  string|null $username  The username.
	 * @param  null        $ip  The ip address.
	 * @param  null        $country  The country.
	 *
	 * @return string
	 */
	public static function get_description_based_on_code(
		string $code,
		string $username = null,
		$ip = null,
		$country = null
	): string {
		// if the ip is not null but the country is, we fetch the country based on the ip.
		if ( ! is_null( $ip ) && is_null( $country ) ) {
			$country = self::get_iso2_from_ip( $ip );
		}
		switch ( $code ) {
			case '1000':
				// translators: %s is replaced with the username.
				return sprintf( __( 'Login by %s was successful', 'really-simple-ssl' ), $username );
			case '1001':
				// translators: %s is replaced with the username.
				return sprintf( __( 'Login by %s failed (incorrect credentials)', 'really-simple-ssl' ), $username );
			case '1002':
				return __( 'REST API authentication successful', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1003':
				return __( 'REST API authentication failed', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1004':
				return __( 'XML-RPC authentication successful', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1005':
				return __( 'XML-RPC authentication failed', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1010':
				// translators: %s is replaced with the username.
				return sprintf( __( 'User %s added to temporary blocklist', 'really-simple-ssl' ), $username );
			case '1011':
				return sprintf(
				// translators: %s is replaced with the username.
					__( 'User %s removed from temporary blocklist', 'really-simple-ssl' ),
					$username
				);
			case '1012':
				// translators: %s is replaced with the username.
				return sprintf( __( 'User %s added to permanent blocklist', 'really-simple-ssl' ), $username );
			case '1013':
				// translators: %s is replaced with the username.
				return sprintf( __( 'User %s removed from permanent blocklist', 'really-simple-ssl' ), $username );
			case '1014':
				// translators: %s is replaced with the username.
				return sprintf( __( 'User %s added to trusted  IP list', 'really-simple-ssl' ), $username );
			case '1015':
				return sprintf(
				// translators: %s is replaced with the username.
					__( 'User %s removed from trusted IP list', 'really-simple-ssl' ),
					$username
				);
			case '1020':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'IP address %s added to temporary blocklist', 'really-simple-ssl' ), $ip );
			case '1021':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'IP address %s removed from temporary blocklist', 'really-simple-ssl' ), $ip );
			case '1022':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'IP address %s added to permanent blocklist', 'really-simple-ssl' ), $ip );
			case '1023':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'IP address %s removed from permanent blocklist', 'really-simple-ssl' ), $ip );
			case '1024':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'IP address %s added to trusted IP list', 'really-simple-ssl' ), $ip );
			case '1025':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'IP address %s removed from trusted IP list', 'really-simple-ssl' ), $ip );
			case '1026':
				return sprintf(
				// translators: %s is replaced with the country name.
					__( 'Country %s added to geo-IP blocklist', 'really-simple-ssl' ),
					self::get_country_by_iso2( $country )
				);
			case '1027':
				return sprintf(
					// translators: %s is replaced with the country name.
					__( 'Country %s removed from geo-IP blocklist', 'really-simple-ssl' ),
					self::get_country_by_iso2( $country )
				);
			case '1030':
				// translators: %s is replaced with the email address.
				return sprintf( __( 'Unblock link sent to %s', 'really-simple-ssl' ), '%email-address%' );
			case '1040':
				return sprintf(
					// translators: %s is replaced with the username.
					__( 'Login failed (User %s found in temporary blocklist)', 'really-simple-ssl' ),
					$username
				);
			case '1041':
				return sprintf(
				// translators: %s is replaced with the username.
					__( 'Login failed (User %s found in permanent blocklist)', 'really-simple-ssl' ),
					$username
				);
			case '1050':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'Login failed (IP %s found in temporary blocklist)', 'really-simple-ssl' ), $ip );
			case '1051':
				// translators: %s is replaced with the IP address.
				return sprintf( __( 'Login failed (IP %s found in permanent blocklist)', 'really-simple-ssl' ), $ip );
			case '1052':
				return sprintf(
					// translators: %s is replaced with the country name.
					__( 'Login failed (Country %s blocked by geo-IP blocklist )', 'really-simple-ssl' ),
					self::get_country_by_iso2( $country )
				);
			case '1100':
				return __( 'Login failed (incorrect MFA code)', 'really-simple-ssl' ) . ' (' . __(
					'MFA',
					'really-simple-ssl'
				) . ')';
			case '1110':
				return __( 'MFA setup required', 'really-simple-ssl' ) . ' (' . __( 'MFA', 'really-simple-ssl' ) . ')';

			default:
				return __( 'Unknown event', 'really-simple-ssl' );
		}
	}


	/**
	 * Fetches the event type based on the event code.
	 *
	 * @param  string $code The event code.
	 *
	 * @return string
	 */
	public static function get_event_type_by_code( string $code ): string {
		switch ( $code ) {
			case '1001':
			case '1002':
			case '1003':
			case '1004':
			case '1005':
			case '1000':
				return 'authentication';
			case '1011':
			case '1012':
			case '1013':
			case '1020':
			case '1021':
			case '1022':
			case '1023':
			case '1024':
			case '1025':
			case '1030':
			case '1040':
			case '1010':
			case '1026':
			case '1027':
			case '1014':
			case '1015':
			case '1041':
			case '1050':
			case '1051':
			case '1052':
				return 'login-protection';
			case '1110':
			case '1111':
			case '1100':
				return 'MFA';
			default:
				return 'unknown-event';
		}
	}

	/**
	 * Fetches the severity based on the event code.
	 *
	 * @param  string $code The event code.
	 *
	 * @return string
	 */
	public static function get_severity_based_on_code( string $code ): string {
		switch ( $code ) {
			case '1001':
			case '1003':
			case '1005':
			case '1010':
			case '1020':
			case '1040':
			case '1041':
			case '1050':
			case '1051':
			case '1052':
				return 'warning';
			default:
				return 'informational';
		}
	}

	/**
	 * Fetches the event data based on the event code.
	 *
	 * @param  string      $code The event code.
	 * @param  string      $username The username.
	 * @param  string      $get_ip_address The ip address.
	 * @param  string|null $country The country.
	 *
	 * @return string
	 */
	public static function get_event_data_based_on_code(
		string $code,
		string $username,
		string $get_ip_address,
		string $country = ''
	): string {
		switch ( $code ) {
			case '1000':
			default:
				if ( '' === $country ) {
					return wp_json_encode(
						array(
							'iso2_code'    => self::get_iso2_from_ip( $get_ip_address ),
							'country_name' => Rsssl_Geo_Location::get_country_by_iso2( self::get_iso2_from_ip( $get_ip_address ) ),
						)
					);
				}

				return wp_json_encode(
					array(
						'iso2_code'    => $country,
						'country_name' => Rsssl_Geo_Location::get_country_by_iso2( $country ),
					)
				);
		}
	}

	/**
	 * Fetches the country code based on the ip address.
	 *
	 * @param  string $get_ip_address The ip address.
	 * @param  bool   $get_named_country The named country.
	 *
	 * @return string
	 */
	public static function get_iso2_from_ip( string $get_ip_address, bool $get_named_country = false ): string {
		$code = Rsssl_Geo_Location::get_county_by_ip( $get_ip_address );
		if ( $get_named_country ) {
			return Rsssl_Geo_Location::get_country_by_iso2( $code );
		}

		return $code;
	}

	/**
	 * Fetches the country name based on the country code.
	 *
	 * @param  string $country The country code.
	 *
	 * @return string
	 */
	private static function get_country_by_iso2( string $country ): string {
		return Rsssl_Geo_Location::get_country_by_iso2( $country );
	}

	/**
	 * Fetches the short description based on the event code.
	 *
	 * @param  string $code The event code.
	 *
	 * @return string
	 */
	public static function get_short_description_based_on_code( string $code ): string {
		switch ( $code ) {
			case '1000':
				return __( 'Login successful', 'really-simple-ssl' );
			case '1001':
				return __( 'Login failed', 'really-simple-ssl' );
			case '1002':
				return __( 'REST API authentication successful', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1003':
				return __( 'REST API authentication failed', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1004':
				return __( 'XML-RPC authentication successful', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1005':
				return __( 'XML-RPC authentication failed', 'really-simple-ssl' ) . ' (' . __(
					'Authentication',
					'really-simple-ssl'
				) . ')';
			case '1010':
				return __( 'User locked out', 'really-simple-ssl' );
			case '1011':
			case '1013':
				return __( 'User removed from blocklist', 'really-simple-ssl' );
			case '1012':
				return __( 'User added to blocklist', 'really-simple-ssl' );
			case '1014':
				return __( 'User added to trusted list', 'really-simple-ssl' );
			case '1015':
				return __( 'User removed from trusted list', 'really-simple-ssl' );
			case '1020':
				return __( 'IP address locked-out', 'really-simple-ssl' );
			case '1023':
			case '1021':
				return __( 'IP removed from blocklist', 'really-simple-ssl' );
			case '1022':
				return __( 'IP added to blocklist', 'really-simple-ssl' );
			case '1024':
				return __( 'IP added to trusted list', 'really-simple-ssl' );
			case '1025':
				return __( 'IP removed from trusted list', 'really-simple-ssl' );
			case '1026':
			case '1027':
				return __( 'Geo-IP blocklist changed', 'really-simple-ssl' );
			case '1030':
				return __( 'Unblock link sent', 'really-simple-ssl' );
			case '1041':
			case '1040':
				return __( 'Login blocked by username', 'really-simple-ssl' );
			case '1050':
			case '1051':
				return __( 'Login blocked by IP address', 'really-simple-ssl' );
			case '1052':
				return __( 'Login blocked by Geo-IP list', 'really-simple-ssl' );

			default:
				return __( 'Unknown event', 'really-simple-ssl' );
		}
	}
}
