<?php
/**
 * This file contains the implementation of the Rsssl_Limit_Login_Attempts class.
 * This class is used to check if a login attempt should be blocked or allowed.
 * It also contains the logic to block and unblock users and IP addresses.
 * It also contains the logic to add and remove users and IP addresses to the allowlist and blocklist.
 *
 * @package RSSSL\Pro\Security\WordPress
 * @company Really Simple Plugins
 * @website https://really-simple-plugins.com
 */

namespace RSSSL\Pro\Security\WordPress;

use DateTimeZone;
use RSSSL\Pro\Security\WordPress\Eventlog\Rsssl_Event_Type;
use Exception;
use RSSSL\Pro\Security\WordPress\LimitLogin\Rsssl_Geo_Location;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_IP_Fetcher;
use RSSSL\Pro\Security\WordPress\Rsssl_Event_Log;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Query_Builder;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Array_Query_Builder;
use stdClass;
use const FILTER_FLAG_IPV4;
use const FILTER_VALIDATE_IP;



if ( ! class_exists( 'Rsssl_Limit_Login_Attempts' ) ) {
	/**
	 * Class Rsssl_Limit_Login_Attempts
	 *
	 * This class is used to check if a login attempt should be blocked or allowed.
	 *
	 * @package RSSSL\Pro\Security\WordPress
	 * @company Really Simple Plugins
	 * @website https://really-simple-plugins.com
	 *
	 * @author Marcel Santing
	 */
	class Rsssl_Limit_Login_Attempts {

		const CACHE_EXPIRATION          = 3600;
		const EVENT_CODE_USER_BLOCKED   = '1012';
		const EVENT_CODE_USER_UNBLOCKED = '1013';
		const EVENT_CODE_IP_BLOCKED     = '1022';
		const EVENT_CODE_IP_UNBLOCKED   = '1023';

		const EVENT_CODE_IP_ADDED_TO_ALLOWLIST       = '1024';
		const EVENT_CODE_IP_REMOVED_FROM_ALLOWLIST   = '1025';
		const EVENT_CODE_USER_ADDED_TO_ALLOWLIST     = '1014';
		const EVENT_CODE_USER_REMOVED_FROM_ALLOWLIST = '1015';
		const EVENT_CODE_IP_UNLOCKED                 = '1021';
		const EVENT_CODE_USER_LOCKED                 = '1010';
		const EVENT_CODE_USER_UNLOCKED               = '1011';
		const EVENT_CODE_IP_LOCKED                   = '1020';
		const EVENT_CODE_IP_UNLOCKED_BY_ADMIN        = '1021';
		const EVENT_CODE_COUNTRY_BLOCKED             = '1026';
		const EVENT_CODE_COUNTRY_UNBLOCKED           = '1027';


		/**
		 *
		 * Process the request. Get the IP address(es) and check if they are present in the allowlist / blocklist.
		 *
		 * @return string
		 */
		public function check_request(): string {
			$ips = $this->get_ip_address();
			return $this->check_ip_address( $ips );
		}

		/**
		 * Check if the request is for a user and if so, check if the user is present in the allowlist / blocklist.
		 *
		 * @param  string $username The username to check.
		 *
		 * @return string
		 */
		public function check_request_for_user( string $username ): string {
			$usernames = array( $username );
			return $this->check_against_users( $usernames );
		}

		/**
		 * Check if the request is for a country and if so, check if the country is present in the allowlist / blocklist.
		 *
		 * @return string
		 */
		public function check_request_for_country(): string {
			$country = Rsssl_Geo_Location::get_county_by_ip( $this->get_ip_address()[0] );

			return $this->check_against_countries( array( $country ) );
		}

		/**
		 * Retrieves a list of unique, validated IP addresses from various headers.
		 *
		 * This function attempts to retrieve the client's IP address from a variety of HTTP headers,
		 * including 'X-Forwarded-For', 'X-Forwarded', 'Forwarded-For', and 'Forwarded'. The function
		 * prefers rightmost IPs in these headers as they are less likely to be spoofed. It also checks
		 * if each IP is valid and not in a private or reserved range. Duplicate IP addresses are removed
		 * from the returned array.
		 *
		 * Note: While this function strives to obtain accurate IP addresses, the nature of HTTP headers
		 * means that it cannot guarantee the authenticity of the IP addresses.
		 *
		 * @return array An array of unique, validated IP addresses. If no valid IP addresses are found,
		 *               an empty array is returned.
		 */
		public function get_ip_address(): array {
			$ip_addresses = ( new Rsssl_IP_Fetcher() )->get_ip_address();
			return array_values( array_unique( $ip_addresses ) );
		}

		/**
		 * Processes an IP or range and calls the appropriate function.
		 *
		 * This function determines whether the provided input is an IP address or an IP range,
		 * and then calls the appropriate function accordingly.
		 *
		 * @param array $ip_addresses The IP addresses to check.
		 *
		 * @return string Returns a status representing the check result: 'allowed' for allowlist hit, 'blocked' for blocklist hit, 'not found' for no hits.
		 */
		public function check_ip_address( array $ip_addresses ): string {
			$found_blocked_ip = false;
			foreach ( $ip_addresses as $ip ) {
				// Remove any white space around the input.
				$item = trim( $ip );
				// Validate the input to determine whether it's an IP or a range.
				if ( filter_var( $item, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
					// It's a valid IP address.
					$status = $this->check_against_ips( array( $item ) );
					// If not found in regular IP's, check against ranges.
					if ( 'not_found' === $status ) {
						$status = $this->get_ip_range_status( array( $item ) );
					}

					if ( 'allowed' === $status ) {
						return 'allowed';
					}

					if ( 'blocked' === $status ) {
						$found_blocked_ip = true;
					}
				}
			}

			if ( $found_blocked_ip ) {
				return 'blocked';
			}

			return 'not_found';
		}

		/**
		 * Checks if a given IP address is within a specified IP range.
		 *
		 * This function supports both IPv4 and IPv6 addresses, and can handle ranges in
		 * both standard notation (e.g. "192.0.2.0") and CIDR notation (e.g. "192.0.2.0/24").
		 *
		 * In CIDR notation, the function uses a bitmask to check if the IP address falls within
		 * the range. For IPv4 addresses, it uses the `ip2long()` function to convert the IP
		 * address and subnet to their integer representations, and then uses the bitmask to
		 * compare them. For IPv6 addresses, it uses the `inet_pton()` function to convert the IP
		 * address and subnet to their binary representations, and uses a similar bitmask approach.
		 *
		 * If the range is not in CIDR notation, it simply checks if the IP equals the range.
		 *
		 * @param  string $ip  The IP address to check.
		 * @param  string $range  The range to check the IP address against.
		 *
		 * @return bool True if the IP address is within the range, false otherwise.
		 */
		public function ip_in_range( string $ip, string $range ): bool {
			// Check if the IP address is properly formatted.
			if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
				return false;
			}
			// Check if the range is in CIDR notation.
			if ( strpos( $range, '/' ) !== false ) {
				// The range is in CIDR notation, so we split it into the subnet and the bit count.
				[ $subnet, $bits ] = explode( '/', $range );

				if ( ! is_numeric( $bits ) || $bits < 0 || $bits > 128 ) {
					return false;
				}

				// Check if the subnet is a valid IPv4 address.
				if ( filter_var( $subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
					// Convert the IP address and subnet to their integer representations.
					$ip     = ip2long( $ip );
					$subnet = ip2long( $subnet );

					// Create a mask based on the number of bits.
					$mask = - 1 << ( 32 - $bits );

					// Apply the mask to the subnet.
					$subnet &= $mask;

					// Compare the masked IP address and subnet.
					return ( $ip & $mask ) === $subnet;
				}

				// Check if the subnet is a valid IPv6 address.
				if ( filter_var( $subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
					// Convert the IP address and subnet to their binary representations.
					$ip     = inet_pton( $ip );
					$subnet = inet_pton( $subnet );
					// Divide the number of bits by 8 to find the number of full bytes.
					$full_bytes = floor( $bits / 8 );
					// Find the number of remaining bits after the full bytes.
					$partial_byte = $bits % 8;
					// Initialize the mask.
					$mask = '';
					// Add the full bytes to the mask, each byte being "\xff" (255 in binary).
					$mask .= str_repeat( "\xff", $full_bytes );
					// If there are any remaining bits...
					if ( 0 !== $partial_byte ) {
						// Add a byte to the mask with the correct number of 1 bits.
						// First, create a string with the correct number of 1s.
						// Then, pad the string to 8 bits with 0s.
						// Convert the binary string to a decimal number.
						// Convert the decimal number to a character and add it to the mask.
						$mask .= chr( bindec( str_pad( str_repeat( '1', $partial_byte ), 8, '0' ) ) );
					}

					// Fill in the rest of the mask with "\x00" (0 in binary).
					// The total length of the mask should be 16 bytes, so subtract the number of bytes already added.
					// If we added a partial byte, we need to subtract 1 more from the number of bytes to add.
					$mask .= str_repeat( "\x00", 16 - $full_bytes - ( 0 !== $partial_byte ? 1 : 0 ) );

					// Compare the masked IP address and subnet.
					return ( $ip & $mask ) === $subnet;
				}

				// The subnet was not a valid IP address.
				return false;
			}

			if ( ! filter_var( $range, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
				// The range was not in CIDR notation and was not a valid IP address.
				return false;
			}

			// The range is not in CIDR notation, so we simply check if the IP equals the range.
			return $ip === $range;
		}

		/**
		 * Checks a list of IP addresses against allowlist and blocklist.
		 *
		 * This function fetches explicit IP addresses from the database tables and checks if the supplied IPs are in the allowlist or blocklist.
		 * If an IP is found in the allowlist or blocklist, it is stored in the corresponding database table and a status is returned.
		 *
		 * @param  array $ip_addresses  The list of IP addresses to check.
		 *
		 * @return string|null Status representing the check result: 'allowed' for allowlist hit, 'blocked' for blocklist hit, 'not found' for no hits.
		 */
		public function check_against_ips( array $ip_addresses ): string {

			global $wpdb;

			$cache_key_allowlist = 'rsssl_allowlist_ips';
			$cache_key_blocklist = 'rsssl_blocklist_ips';

			// Try to get the lists from cache.
			$allowlist_ips = wp_cache_get( $cache_key_allowlist );
			$blocklist_ips = wp_cache_get( $cache_key_blocklist );

			// If not cached, fetch from the database and then cache.
			if ( false === $allowlist_ips ) {
				// phpcs:ignore WordPress.DB
				$allowlist_ips = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE status = 'allowed' AND attempt_type = 'source_ip' AND  attempt_value NOT LIKE '%/%'" );

				wp_cache_set( $cache_key_allowlist, $allowlist_ips, null, self::CACHE_EXPIRATION );
			}

			if ( false === $blocklist_ips ) {
				// phpcs:ignore WordPress.DB
				$blocklist_ips = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE status = 'blocked' AND attempt_type = 'source_ip' AND  attempt_value NOT LIKE '%/%'" );

				wp_cache_set( $cache_key_blocklist, $blocklist_ips, null, self::CACHE_EXPIRATION );
			}

			// Check the IP addresses.
			foreach ( $ip_addresses as $ip ) {
				if ( in_array( $ip, $allowlist_ips, true ) ) {
					return 'allowed';
				}
				if ( in_array( $ip, $blocklist_ips, true ) ) {
					return 'blocked';
				}
			}

			return 'not_found';
		}

		/**
		 * Checks a list of usernames against allowlist and blocklist.
		 *
		 * @param  array $usernames The list of usernames to check.
		 *
		 * @return string
		 */
		public function check_against_users( array $usernames ): string {

			global $wpdb;

			$cache_key_allowlist = 'rsssl_allowlist_users';
			$cache_key_blocklist = 'rsssl_blocklist_users';

			// Try to get the lists from cache.
			$allowlist_users = wp_cache_get( $cache_key_allowlist );
			$blocklist_users = wp_cache_get( $cache_key_blocklist );

			// If not cached, fetch from the database and then cache.
			if ( false === $allowlist_users ) {
				// phpcs:ignore WordPress.DB
				$allowlist_users = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE status = 'allowed' AND attempt_type = 'username' " );
				wp_cache_set( $cache_key_allowlist, $allowlist_users, null, self::CACHE_EXPIRATION );
			}

			if ( false === $blocklist_users ) {
				// phpcs:ignore WordPress.DB
				$blocklist_users = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE status = 'blocked' AND attempt_type = 'username' " );
				wp_cache_set( $cache_key_blocklist, $blocklist_users, null, self::CACHE_EXPIRATION );
			}

			// Check the users.
			foreach ( $usernames as $username ) {
				if ( in_array( $username, $allowlist_users, true ) ) {
					return 'allowed';
				}
				if ( in_array( $username, $blocklist_users, true ) ) {
					return 'blocked';
				}
			}

			return 'not_found';
		}

		/**
		 * Checks a list of countries against allowlist and blocklist.
		 *
		 * @param  array $countries The list of countries to check.
		 *
		 * @return string
		 */
		public function check_against_countries( array $countries ): string {
			global $wpdb;

			$cache_key_allowlist = 'rsssl_allowlist_countries';
			$cache_key_blocklist = 'rsssl_blocklist_countries';

			// Try to get the lists from cache.
			$allowlist_countries = wp_cache_get( $cache_key_allowlist );
			$blocklist_countries = wp_cache_get( $cache_key_blocklist );

			// If not cached, fetch from the database and then cache.
			if ( false === $allowlist_countries ) {
				// phpcs:ignore WordPress.DB
				$allowlist_countries = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE status = 'allowed' AND attempt_type = 'country' " );
				wp_cache_set( $cache_key_allowlist, $allowlist_countries, null, self::CACHE_EXPIRATION );
			}

			if ( false === $blocklist_countries ) {
				// phpcs:ignore WordPress.DB
				$blocklist_countries = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE status = 'blocked' AND attempt_type = 'country' " );
				wp_cache_set( $cache_key_blocklist, $blocklist_countries, null, self::CACHE_EXPIRATION );
			}

			// Check the countries.
			foreach ( $countries as $country ) {
				if ( in_array( $country, $allowlist_countries, true ) ) {
					return 'allowed';
				}
				if ( in_array( $country, $blocklist_countries, true ) ) {
					return 'blocked';
				}
			}

			return 'not_found';
		}

		/**
		 * Checks a list of IP addresses against allowlist and blocklist ranges.
		 *
		 * This function fetches IP ranges from the database tables and checks if the supplied IPs are within the allowlist or blocklist ranges.
		 * If an IP is found in the allowlist or blocklist range, it is stored in the corresponding database table and a status is returned.
		 *
		 * @param  array $ip_addresses  The list of IP addresses to check.
		 *
		 * @return string|null Status representing the check result: 'allowed' for allowlist hit, 'blocked' for blocklist hit, 'not found' for no hits.
		 */
		public function get_ip_range_status( array $ip_addresses ): string {

			global $wpdb;

			$cache_key_allowlist_ranges = 'rsssl_allowlist_ranges';
			$cache_key_blocklist_ranges = 'rsssl_blocklist_ranges';

			// Try to get the lists from cache.
			$allowlist_ranges = wp_cache_get( $cache_key_allowlist_ranges );
			$blocklist_ranges = wp_cache_get( $cache_key_blocklist_ranges );

			// If not cached, fetch from the database and then cache.
			if ( false === $allowlist_ranges ) {
				// phpcs:ignore WordPress.DB
				$allowlist_ranges = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE attempt_type = 'source_ip' AND status = 'allowed' AND attempt_value LIKE '%/%'" );
				wp_cache_set( $cache_key_allowlist_ranges, $allowlist_ranges, null, self::CACHE_EXPIRATION );
			}

			if ( false === $blocklist_ranges ) {
				// phpcs:ignore WordPress.DB
				$blocklist_ranges = $wpdb->get_col( 'SELECT attempt_value FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE attempt_type = 'source_ip' AND status = 'blocked' AND attempt_value LIKE '%/%'" );
				wp_cache_set( $cache_key_blocklist_ranges, $blocklist_ranges, null, self::CACHE_EXPIRATION );
			}

			// Check the IP addresses.
			foreach ( $ip_addresses as $ip ) {
				foreach ( $allowlist_ranges as $range ) {
					if ( $this->ip_in_range( $ip, $range ) ) {
						return 'allowed';
					}
				}
				foreach ( $blocklist_ranges as $range ) {
					if ( $this->ip_in_range( $ip, $range ) ) {
						return 'blocked';
					}
				}
			}

			return 'not_found';
		}

		/**
		 * Invalidates the cache for the specified table and IP address.
		 *
		 * This function clears the cache for the allowlist or blocklist based on the provided table and IP address.
		 * If the IP address is a range, it clears the cache for the corresponding range cache key. Otherwise, it clears
		 * the cache for the corresponding IP cache key.
		 *
		 * @param  string $table  The table name ('rsssl_allowlist' or 'rsssl_blocklist').
		 * @param  string $ip  The IP address or range.
		 *
		 * @return void
		 */
		public function invalidate_cache( string $table, string $ip ): void {

			if ( 'rsssl_allowlist' === $table ) {
				// Check if range or IP.
				if ( strpos( $ip, '/' ) !== false ) {
					wp_cache_delete( 'rsssl_allowlist_ranges' );
				} else {
					wp_cache_delete( 'rsssl_allowlist_ips' );
				}
			}

			if ( 'rsssl_blocklist' === $table ) {
				if ( strpos( $ip, '/' ) !== false ) {
					wp_cache_delete( 'rsssl_blocklist_ranges' );
				} else {
					wp_cache_delete( 'rsssl_blocklist_ips' );
				}
			}
		}

		/**
		 * Checks if a CIDR is valid.
		 *
		 * @param  string $cidr The CIDR to check.
		 *
		 * @return bool
		 */
		private function is_valid_cidr( string $cidr ): bool {
			$parts = explode( '/', $cidr );
			if ( 2 !== count( $parts ) ) {
				return false;
			}
			$ip      = $parts[0];
			$netmask = (int) $parts[1];

			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				// Validate IPv4 CIDR.
				if ( 0 > $netmask || 32 < $netmask ) {
					return false;
				}

				return true;
			} elseif ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				// Validate IPv6 CIDR.
				if ( 0 > $netmask || 128 < $netmask ) {
					return false;
				}

				return true;
			}

			// If not IPv4 or IPv6, return false.
			return false;
		}

		/**
		 * Adds a region to the allowlist or blocklist.
		 *
		 * @param  array $data The data to add.
		 *
		 * @throws Exception When an error occurs while adding the data.
		 */
		public function add_region_to_list( array $data ): array {
			global $wpdb;

			// based on the region we need to get the countries associated with it.
			$query = $wpdb->prepare(
				"SELECT iso2_code FROM {$wpdb->base_prefix}rsssl_country WHERE region_code = %s",
				$data['region']
			);

			// phpcs:ignore WordPress.DB
			$countries = $wpdb->get_results( $query );

			// now we add the countries to the list.
			foreach ( $countries as $key => $country ) {
				// we add a status property.
				$country->status        = $data['status'];
				$country->attempt_type  = 'country';
				$country->attempt_value = $country->iso2_code;

				$sql = $wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE attempt_value = %s AND attempt_type = 'country'",
					$country->iso2_code
				);

				// phpcs:ignore WordPress.DB
				$exists = $wpdb->get_var( $sql );

				if ( $exists ) {
					unset( $countries[ $key ] );
					continue;
				}

				// phpcs:ignore WordPress.DB
				$result = $wpdb->insert(
					$wpdb->base_prefix . 'rsssl_login_attempts',
					array(
						'attempt_value' => $country->iso2_code,
						'attempt_type'  => 'country',
						'status'        => $data['status'],
						'last_failed'   => time(),
					),
					array( '%s', '%s', '%s', '%s', '%d' )
				);

				if ( false === $result ) {
					return array( 'error', $wpdb->last_error, $wpdb->last_query );
				}
			}

			$this->log_event_data( $countries );
			wp_cache_delete( 'rsssl_allowlist_countries' );

			return array( 'success', $data, $wpdb->last_query );
		}

		/**
		 * Removes countries based on the regions.
		 *
		 * @param array $data The data to add.
		 *
		 * @return array
		 * @throws Exception When an error occurs while adding the data.
		 */
		public function remove_region_from_list( $data ) {
			global $wpdb;
			// based on the region we need to get the countries associated with it.
			// phpcs:ignore WordPress.DB
			$countries = $wpdb->get_results( 'SELECT iso2_code FROM ' . $wpdb->base_prefix . "rsssl_country WHERE region_code = '{$data['region']}'" );

			// now we add the countries to the list.
			foreach ( $countries as $key => $country ) {
				$sql = $wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE attempt_value = %s AND attempt_type = 'country'",
					$country->iso2_code
				);

				// we add a status property.
				$country->status        = $data['status'];
				$country->attempt_type  = 'country';
				$country->attempt_value = $country->iso2_code;

				// phpcs:ignore WordPress.DB
				$exists = $wpdb->get_var( $sql );

				if ( ! $exists ) {
					// we unset the country from the list.
					unset( $countries[ $key ] );
					continue;
				}

				// phpcs:ignore WordPress.DB
				$result = $wpdb->delete(
					$wpdb->base_prefix . 'rsssl_login_attempts',
					array(
						'attempt_value' => $country->iso2_code,
						'attempt_type'  => 'country',
					),
					array( '%s', '%s' )
				);

				if ( false === $result ) {
					return array( 'error', $wpdb->last_error, $wpdb->last_query );
				}
			}

			$this->log_event_data( $countries, true );

			return array( 'success', $data, $wpdb->last_query );
		}

		/**
		 * Adds regions to the allowlist or blocklist.
		 *
		 * @param  array $data The data to add.
		 *
		 * @throws Exception When an error occurs while adding the data.
		 */
		public function add_regions_to_list( array $data ): array {
			global $wpdb;
			$countries = array();

			// first we get the regions.
			$region_count = count( $data['regions'] );

			$query = $wpdb->prepare(
				// phpcs:ignore WordPress.DB
				'SELECT region_code FROM ' . $wpdb->base_prefix . 'rsssl_country WHERE id IN (' . implode( ',', array_fill( 0, $region_count, '%s' ) ) . ')',
				...$data['regions']
			);
			// phpcs:ignore WordPress.DB
			$regions = $wpdb->get_results( $query );

			// based on all the regions we need to get the countries associated with it.
			foreach ( $regions as $region ) {
				// first we get the countries.
				$query = $wpdb->prepare(
					'SELECT iso2_code FROM ' . $wpdb->base_prefix . 'rsssl_country WHERE region_code = %s',
					$region->region_code
				);

				$countries = array_merge(
					$countries,
					// phpcs:ignore WordPress.DB
					$wpdb->get_results( $query )
				);
			}

			// now we add the countries to the list.
			foreach ( $countries as $key => $country ) {
				$sql = $wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE attempt_value = %s AND attempt_type = 'country'",
					$country->iso2_code
				);

				// we add a status property.
				// phpcs:ignore WordPress.DB
				$exists = $wpdb->get_var( $sql );

				if ( $exists ) {
					unset( $countries[ $key ] );
					continue;
				}

				// phpcs:ignore WordPress.DB
				$result = $wpdb->insert(
					$wpdb->base_prefix . 'rsssl_login_attempts',
					array(
						'attempt_value' => $country->iso2_code,
						'attempt_type'  => 'country',
						'status'        => 'blocked',
						'last_failed'   => time(),
					),
					array( '%s', '%s', '%s', '%s', '%d' )
				);

				if ( false === $result ) {
					return array( 'error', $wpdb->last_error, $wpdb->last_query );
				}

				$country->status        = 'blocked';
				$country->attempt_type  = 'country';
				$country->attempt_value = $country->iso2_code;
			}
			$this->log_event_data( $countries );
			// we clear the cache.
			wp_cache_delete( 'rsssl_allowlist_countries' );

			return array( 'success', $data, $wpdb->last_query );
		}

		/**
		 * Removes regions from the allowlist or blocklist.
		 *
		 * @param  array $data The data to add.
		 *
		 * @return array
		 * @throws Exception When an error occurs while adding the data.
		 */
		public function remove_regions_from_list( array $data ): array {
			global $wpdb;
			$countries    = array();
			$placeholders = implode( ',', array_fill( 0, count( $data['regions'] ), '%s' ) );

			$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB
				'SELECT region_code FROM ' . $wpdb->base_prefix . 'rsssl_country WHERE id IN (' . $placeholders . ')',
				$data['regions']
			);

			// phpcs:ignore WordPress.DB
			$regions = $wpdb->get_results( $query );

			// based on all the regions we need to get the countries associated with it.
			foreach ( $regions as $region ) {
				$query = $wpdb->prepare(
					'SELECT iso2_code FROM ' . $wpdb->base_prefix . 'rsssl_country WHERE region_code = %s',
					$region->region_code
				);

				$countries = array_merge(
					$countries,
					// phpcs:ignore WordPress.DB
					$wpdb->get_results( $query )
				);
			}

			// now we add the countries to the list.
			foreach ( $countries as $country ) {
				$sql = $wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $wpdb->base_prefix . "rsssl_login_attempts WHERE attempt_value = %s AND attempt_type = 'country'",
					$country->iso2_code
				);

				// phpcs:ignore WordPress.DB
				$exists = $wpdb->get_var( $sql );

				if ( $exists ) {
					continue;
				}

				// now we remove the countries.
				// phpcs:ignore WordPress.DB
				$result = $wpdb->delete(
					$wpdb->base_prefix . 'rsssl_login_attempts',
					array(
						'attempt_value' => $country->iso2_code,
						'attempt_type'  => 'country',
					),
					array( '%s', '%s' )
				);

				$country->status        = 'blocked';
				$country->attempt_type  = 'country';
				$country->attempt_value = $country->iso2_code;

				if ( false === $result ) {
					return array( 'error', $wpdb->last_error, $wpdb->last_query );
				}
			}

			$this->log_event_data( $countries, true );
			// we clear the cache.
			wp_cache_delete( 'rsssl_allowlist_countries' );

			return array( 'success', $data, $wpdb->last_query );
		}

	}

	if ( defined( 'rsssl_path' ) ) {
		if ( ! function_exists( 'RSSSL\Pro\Security\WordPress\rsssl_ip_list_api' ) && rsssl_get_option( 'enable_limited_login_attempts' ) ) {
			/**
			 * This function is used to handle the api calls for the ip list.
			 *
			 * @param array  $response The response array.
			 * @param string $action The action to perform.
			 * @param  array  $data The data to use.
			 *
			 * @return array|null
			 * @throws Exception When an error occurs while updating the data.
			 */
			function rsssl_ip_list_api( array $response, string $action, array $data ): ?array {
				// if the option is not enabled, we return the response.
				if ( ! rsssl_admin_logged_in() ) {
					return $response;
				}

				switch ( $action ) {
					case 'add_region_to_list':
						$response = ( new Rsssl_Limit_Login_Attempts() )->add_region_to_list( $data );
						break;
					case 'remove_region_from_list':
						$response = ( new Rsssl_Limit_Login_Attempts() )->remove_region_from_list( $data );
						break;
					case 'remove_regions_from_list':
						$response = ( new Rsssl_Limit_Login_Attempts() )->remove_regions_from_list( $data );
						break;
					case 'add_regions_to_list':
						$response = ( new Rsssl_Limit_Login_Attempts() )->add_regions_to_list( $data );
						break;
					case 'delete_multi_entries':
						$response = ( new Rsssl_Limit_Login_Attempts() )->delete_multi_entries( $data );
						break;
					default:
						break;
				}

				return $response;
			}

			// Add the rsssl_ip_list_api function as a filter callback.
			add_filter( 'rsssl_do_action', 'RSSSL\Pro\Security\WordPress\rsssl_ip_list_api', 10, 3 );
		}
	}
}
