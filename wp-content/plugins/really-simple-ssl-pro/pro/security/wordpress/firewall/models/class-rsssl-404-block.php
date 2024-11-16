<?php
/**
 * The 404 block model.
 *
 * This class is responsible for getting, adding, updating and deleting 404 block entries.
 *
 * @package RSSSL\Pro\Security\WordPress\Firewall
 */

namespace RSSSL\Pro\Security\WordPress\Firewall\Models;

use Exception;

/**
 * Class Rsssl_404_Block
 */
class Rsssl_404_Block {

	/**
	 * The cache key for the 404 block cache.
	 *
	 * @var string $cache_key The cache key for the 404 block cache.
	 */
	private $cache_key = 'rsssl_404_block_cache';

	/**
	 * The name of the table.
	 *
	 * @var string $table_name Name of the rsssl_geo_block table
	 */
	private $table_name = 'rsssl_geo_block';

	/**
	 * Constructor for the class. Sets the table name based on the WordPress database prefix.
	 *
	 * @return void
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->base_prefix . $this->table_name;
	}


	/**
	 * Get all rows from the table with optional select fields.
	 *
	 * @param array $select The select fields to include in the query.
	 *
	 * @return array An array of row objects from the database.
	 */
	public function get_all( array $select = array() ): array {
		global $wpdb;

		// Try to get cached results.
		$results = wp_cache_get( $this->cache_key );

		if ( false === $results ) {
			// If results are not in cache, perform the query.
			if ( empty( $select ) ) {
				$columns = '*';
			} else {
				// Safely format the column names to avoid SQL injection.
				$columns = implode(
					', ',
					array_map(
						function ( $column ) {
							return '`' . esc_sql( $column ) . '`';
						},
						$select
					)
				);
			}

			$data_type = 404;
			// Construct the SQL query safely with placeholders for variable parts.
			$sql = "SELECT {$columns} FROM `{$this->table_name}` WHERE data_type = %d";

			// Prepare the SQL statement using placeholders for variables.
			$safe_sql = $wpdb->prepare( $sql, $data_type ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Fetch all results.
			$results = $wpdb->get_results( $safe_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

			// Save the query results to cache.
			wp_cache_set( $this->cache_key, $results );
		}

		// Return these results.
		return $results;
	}

	/**
	 * Add a new IP address to the database.
	 *
	 * @param string $ip_address The IP address to add.
	 *
	 * @return void
	 */
	public function add( string $ip_address ): void {
		global $wpdb;
		$wpdb->insert(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->table_name,
			array(
				'ip_address'    => $ip_address,
				'data_type'     => '404',
				'attempt_count' => 1,
				'note'          => __( 'Added to watchlist by 404 interceptor', 'really-simple-ssl' ),
                'create_date'   => time(),
			)
		);

		// Clear the cache.
		wp_cache_delete( $this->cache_key );
	}

	/**
	 * Increase the attempt count for a given IP address and return the updated row object.
	 *
	 * @param string $ip_address The IP address to update the attempt count for.
	 *
	 * @return object The updated row object from the database.
	 */
	public function up_count( string $ip_address ): object {
		global $wpdb;

		// we validate the correct ip address.
		if ( filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
			//We get the current time.
			$now = new \DateTime();
			// Direct increment using SQL statement.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"UPDATE {$this->table_name}
					SET attempt_count = attempt_count + 1, last_attempt = %s 
					WHERE ip_address = %s AND data_type = %s",
					time(),
					$ip_address,
					'404'
				)
			);

			// Clear the cache.
			wp_cache_delete( $this->cache_key ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

			// Return the updated row object.
			return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$this->table_name} WHERE ip_address = %s AND data_type = %s",
					$ip_address,
					'404'
				)
			) ?? (object) array();
		}

		// We return an empty object if the ip address is not valid.
		return (object) array();
	}

	/**
	 * Block an IP address by updating the `blocked` status and adding a note to the row.
	 *
	 * @param string $ip_address The IP address to block.
	 *
	 * @return void
	 */
	public function block_ip( string $ip_address ): void {
		global $wpdb;
		//We prepare the SQL statement to block the IP address.
		$sql = $wpdb->prepare(
			"UPDATE $this->table_name SET blocked = %d, note = %s WHERE ip_address = %s AND data_type = %s",
			1,
			__( '404 threshold exceeded', 'really-simple-ssl' ),
			$ip_address,
			'404'
		);
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		wp_cache_delete( $this->cache_key );
	}

	/**
	 * Remove the temporary entry for a given IP address.
	 *
	 * @param string $ip_address The IP address to remove the temporary entry for.
	 *
	 * @return void
	 */
	public function remove_temporary_block( string $ip_address ): void {
		global $wpdb;

		// We get the row object for the IP address.
		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE ip_address = %s AND data_type = %s AND blocked = %d",
				$ip_address,
				'404',
				0
			)
		);

		// If the row object is not empty, we delete the row.
		if ( ! empty( $row ) ) {
			// We delete the entry by id.
			$wpdb->delete( $this->table_name, array( 'id' => $row->id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

		wp_cache_delete( $this->cache_key );
	}

	/**
	 * Get all blocked IP addresses from the database.
	 *
	 * @return array An array of blocked IP addresses.
	 */
	public function get_blocked_ips(): array {
		global $wpdb;
		$sql        = $wpdb->prepare(
			"SELECT ip_address FROM $this->table_name WHERE blocked = %d AND data_type = %s",
			1,
			'404'
		);

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Set the captcha status for an IP address.
	 * 1 = captcha required, 0 = captcha not required. 2 = captcha already shown.
	 *
	 * @param string $ip_address The IP address to set the captcha status for.
	 *
	 * @return void
	 */
	public function set_captcha( string $ip_address ): void {
		global $wpdb;
		// We get the current captcha status.
		$captcha = $this->get_captcha( $ip_address );

		++$captcha;

		// We update the captcha status.

		//Preparing the sql statement to update the captcha status.
		$sql = $wpdb->prepare(
			"UPDATE $this->table_name SET captcha = %d WHERE ip_address = %s AND data_type = %s",
			$captcha,
			$ip_address,
			'404'
		);
		$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		// Clear the cache.
		wp_cache_delete( $this->cache_key );
	}

	/**
	 * Get the captcha status for an IP address.
	 *
	 * @param string $ip_address The IP address to get the captcha status for.
	 *
	 * @return int The captcha status for the IP address.
	 */
	public function get_captcha( string $ip_address ): int {
		global $wpdb;
		$captcha = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT captcha FROM $this->table_name WHERE ip_address = %s AND data_type = %s",
				$ip_address,
				'404'
			)
		);
		if ( null === $captcha ) {
			return 0;
		}

		return (int) $captcha;
	}

	/**
	 * Delete non-blocked entries with data_type '404' that are older than a given time span.
	 *
	 * @param int $time_span The time span in seconds.
	 * @param int $duration
	 *
	 * @return void
	 */
	public function delete_non_blocked_404_entries( int $time_span, int $duration ): void {
		global $wpdb;
		$safe_sql = $wpdb->prepare(
            "DELETE FROM $this->table_name WHERE blocked = %d AND data_type = %s AND (UNIX_TIMESTAMP(NOW()) - CAST(create_date AS UNSIGNED)) > %d",
			0,
			'404',
			$time_span
		);
		$wpdb->query( $safe_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

		// We also clear the blocked accounts if the last_attempt passed the duration.
		$safe_sql = $wpdb->prepare(
            "DELETE FROM $this->table_name WHERE blocked = %d AND data_type = %s AND permanent = %d AND (UNIX_TIMESTAMP(NOW()) - CAST(last_attempt AS UNSIGNED)) > %d",
			1,
			'404',
			0,
			$duration
		);

		$wpdb->query( $safe_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

		wp_cache_delete( $this->cache_key );
	}

    /**
     * Delete blocked entries of type 404 older than a given duration.
     *
     * @param int $duration The duration in seconds.
     *
     * @return void
     * @throws Exception When the time cannot be calculated.
     */
	public function delete_blocked_entries_404( int $duration ): void {

        $entries = $this->get_all();
        foreach ( $entries as $entry ) {
            if(null === $entry->last_attempt)
                continue;

            if (  0 === (int)$entry->permanent && $this->get_time_left( $entry->last_attempt ) === 0){
                $this->delete_blocked_by_ip( $entry->ip_address );
            }
        }
		wp_cache_delete( $this->cache_key );
	}

    /**
     * Get the time left until the max duration is reached.
     *
     * @param string $last_attempt The attempt time.
     *
     * @return int The time left in seconds.
     * @throws Exception When the time cannot be calculated.
     */
    public function get_time_left(string $last_attempt ) {
        $last_attempt = (int)$last_attempt;
        $max_duration = (int)rsssl_get_option('404_blocking_lockout_duration');

		//below should not be necessary if 404 is only activated after save.
	    //we leave it for now.
		$max_duration = $max_duration < -1 ? 30 : $max_duration;
        $current_time = time();
        $duration_seconds = $max_duration * 60;
        $end_time = $last_attempt + $duration_seconds;
        if ($end_time < $current_time) {
            return 0;
        }
        return $end_time - $current_time;
    }

	/**
	 * Delete rows from the database by IP address.
	 *
	 * @param string $ip_address The IP address to delete rows for.
	 *
	 * @return void
	 */
	public function delete_by_ip( string $ip_address ): void {
		global $wpdb;
		$safe_sql = $wpdb->prepare(
			"DELETE FROM $this->table_name WHERE ip_address = %s AND data_type = %s AND blocked = %d",
			$ip_address,
			'404',
			0
		);

		$wpdb->query( $safe_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		wp_cache_delete( $this->cache_key );
	}

    /**
     * Deletes blocked entries from the database based on IP address.
     *
     * @param string $ip_address The IP address to filter the entries.
     * @return void
     */
    public function delete_blocked_by_ip(string $ip_address ): void {
        global $wpdb;
        $safe_sql = $wpdb->prepare(
            "DELETE FROM $this->table_name WHERE ip_address = %s AND data_type = %s AND blocked = %d",
            $ip_address,
            '404',
            1
        );

        $wpdb->query( $safe_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
        wp_cache_delete( $this->cache_key );
    }

	/**
	 * Check if a given IP address is blocked.
	 *
	 * @param string $ip_address The IP address to check.
	 *
	 * @return bool True if the IP address is blocked, false otherwise.
	 */
	public function is_blocked( string $ip_address ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT blocked FROM $this->table_name WHERE ip_address = %s AND data_type = %s",
				$ip_address,
				'404'
			)
		);
	}

	/**
	 * Get a record from the database based on the provided IP address.
	 *
	 * @param string $ip_address The IP address used to filter the records.
	 *
	 * @return object|null The record object matching the given IP address and data type '404', or null if not found.
	 */
	public function get( string $ip_address ): ?object{
		global $wpdb;
		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE ip_address = %s AND data_type = %s",
				$ip_address,
				'404'
			)
		);
	}
}
