<?php
/**
 * The User Agent Block model.
 *
 * This class is responsible for getting, adding, updating and deleting 404 block entries.
 *
 * @package RSSSL\Pro\Security\WordPress\Firewall
 */

namespace RSSSL\Pro\Security\WordPress\Firewall\Models;

use Exception;
use wpdb;

/**
 * Class Rsssl_User_Agent_Block
 */
class Rsssl_User_Agent_Block {

	/**
	 * The cache key for the User Agent block cache.
	 *
	 * @var string $cache_key The cache key for the 404 block cache.
	 */
	private $cache_key = 'rsssl_user_agent_block_cache';

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

			$data_type = 'user-agent';
			// Construct the SQL query safely with placeholders for variable parts.
			$sql = "SELECT {$columns} FROM `{$this->table_name}` WHERE data_type = %s";

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
     * Inserts a new row into the database with the provided user agent and note.
     *
     * @param string $user_agent The user agent to insert.
     * @param mixed $note The note to insert.
     *
     * @return void
     */
	public function add( string $user_agent, $note ): void {
		global $wpdb;

        // Validating if the $user_agent already exists in the database.
        $existing_user_agent = $this->get( $user_agent );
        if ( $existing_user_agent ) {
            // update the existing user agent
            $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $this->table_name,
                array(
                    'note' => $note,
                    'permanent' => true,
                    'blocked' => true,
                    'deleted_at' => null,
                ),
                array(
                    'id' => $existing_user_agent->id,
                )
            );
            wp_cache_delete( $this->cache_key );
            return;
        }

		$wpdb->insert(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->table_name,
			array(
				'user_agent'    => $user_agent,
				'data_type'     => 'user-agent',
				'note'          => $note,
                'create_date'   => time(),
                'permanent'     => true,
                'blocked'       => true,
			)
		);

		// Clear the cache.
		wp_cache_delete( $this->cache_key );
	}

    /**
     * Retrieves an array of blocked user agents from the database.
     *
     * @param bool $deleted Whether to include soft-deleted rows in the search.
     *
     * @return array An array of objects representing the blocked user agents, or an empty array if no matches found.
     */
    public function get_blocked_user_agents(bool $deleted = false): array {
        global $wpdb;
        $deleted_condition = $deleted ? 'deleted_at IS NOT NULL' : 'deleted_at IS NULL';
        $sql = $wpdb->prepare(
            "SELECT id, user_agent, created_at, note, deleted_at FROM $this->table_name WHERE blocked = %d AND data_type = %s AND $deleted_condition",
            1,
            'user-agent'
        );
        return $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
    }

    public function get_agent_list():array
    {
        global $wpdb;
        $sql        = $wpdb->prepare(
            "SELECT user_agent FROM $this->table_name WHERE blocked = %d AND data_type = %s AND deleted_at IS NULL",
            1,
            'user-agent'
        );
        return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Sets the 'deleted_at' column to the current time for a row in the database based on the provided ID.
     *
     * @param int $id The ID of the row to soft delete.
     *
     * @return void
     */
    public function softDelete($id): void
    {
        global $wpdb;
        $wpdb->update(
            $this->table_name,
            array(
                'deleted_at' => time(),
            ),
            array(
                'id' => $id,
            )
        );
        wp_cache_delete( $this->cache_key );
    }


    /**
     * Retrieves a row from the database based on the provided user agent.
     *
     * @param string $user_agent The user agent to search for.
     * @param bool $deleted Whether to include soft-deleted rows in the search.
     *
     * @return object|null The matched row as an object, or null if no match found.
     */
	public function get( string $user_agent , bool $deleted = false ): ?object{
		global $wpdb;
		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE user_agent = %s AND data_type = %s",
                $user_agent,
				'user-agent'
			)
		);
	}

    public function get_by_id(int $id): ?object
    {
        global $wpdb;
        return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE id = %d",
                $id
            )
        );
    }

    public static function fetch($response, $deleted = false): array
    {
        $self = new self();
        $data = $self->get_blocked_user_agents($deleted);
        return $response['data'] = $data;
    }

    /**
     * Deletes a row from the database based on the provided ID.
     *
     * @param int $id The ID of the row to delete.
     *
     * @return void
     */
    public function delete($id): void
    {
        global $wpdb;
        $wpdb->delete(
            $this->table_name,
            array(
                'id' => $id,
            )
        );
        wp_cache_delete($this->cache_key);
    }

    /**
     * Restores a soft-deleted row in the database.
     *
     * @param int $id The ID of the row to restore.
     *
     * @return void
     */
    public function unSoftDelete(int $id): void
    {
        global $wpdb;

        // Check if the ID exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $this->table_name WHERE id = %d", $id));
        if ($existing == 0) {
            throw new Exception("ID not found in the database.");
        }

        // Update the deleted_at column to NULL
        $result = $wpdb->update(
            $this->table_name,
            array(
                'deleted_at' => null,
            ),
            array(
                'id' => $id,
            ),
            array(
                '%s',
            ),
            array(
                '%d',
            )
        );

        if ($result === false) {
            throw new Exception("Failed to update the deleted_at column.");
        }

        wp_cache_delete($this->cache_key);
    }
}
