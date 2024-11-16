<?php
/**
 * Implementation of the Database Rsssl_Query_Builder for Really Simple Plugins.
 *
 * This file contains the Database Rsssl_Query_Builder which provides a structured
 * way to generate and manage database queries. Designed to offer flexible
 * and efficient database operations, it's part of the suite of tools
 * developed for Really Simple Plugins.
 *
 * @package ReallySimplePlugins
 * @author Marcel Santing
 * @version 1.0
 */

namespace RSSSL\Pro\Security\DynamicTables;

require_once rsssl_path . 'pro/security/dynamic-tables/class-rsssl-abstract-database-manager.php';

use Exception;
use RuntimeException;

/**
 * Class Rsssl_Query_Builder
 * Adds an extra layer of abstraction to the WordPress database and supports pagination
 *
 * @package security\wordpress\DynamicTables
 */
class Rsssl_Query_Builder extends Rsssl_Abstract_Database_Manager {
	/**
	 * The name of the table to query.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * An array of columns to be selected in the query.
	 *
	 * @var array
	 */
	protected $columns;

	/**
	 * An array of columns that have been fetched from the database.
	 *
	 * @var array
	 */
	protected $fetched_columns;

	/**
	 * Specifies the column or columns by which the result set will be ordered.
	 *
	 * @var string
	 */
	protected $order_by;

	/**
	 * The limit for the number of records to retrieve.
	 *
	 * @var int|string
	 */
	protected $limit;

	/**
	 * The offset from which to start retrieving records.
	 *
	 * @var int|string
	 */
	protected $offset;

	/**
	 * An array representing the column or columns used for sorting.
	 *
	 * @var array
	 */
	protected $sort_column;

	/**
	 * Represents an SQL JOIN clause.
	 *
	 * @var string|array
	 */
	protected $join;

	/**
	 * An array of raw columns that are not aliased or transformed.
	 *
	 * @var array
	 */
	protected $raw_columns;

	/**
	 * Represents an SQL WHERE clause.
	 *
	 * @var array
	 */
	protected $where;

	/**
	 * Represents an SQL WHERE clause.
	 *
	 * @var array
	 */
	protected $not_null;

	/**
	 * Represents an additional SQL WHERE clause to be combined with OR.
	 *
	 * @var array
	 */
	protected $or_where;

	/**
	 * Represents an SQL WHERE IN clause.
	 *
	 * @var array
	 */
	protected $where_in;

	/**
	 * Represents an SQL WHERE NOT IN clause.
	 *
	 * @var array
	 */
	protected $where_not_in;

	/**
	 * An array of results fetched from the database.
	 *
	 * @var array
	 */
	protected $results;

	/**
	 * Represents an SQL GROUP BY clause.
	 *
	 * @var string
	 */
	protected $group_by;

	/**
	 * Sets the columns to select.
	 *
	 * @param array|string $columns The columns to select.
	 *
	 * @return Rsssl_Query_Builder
	 */
	public function select_columns( $columns ): Rsssl_Query_Builder {
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Adds columns to the select.
	 *
	 * @param array|string $columns The columns to select.
	 *
	 * @return Rsssl_Query_Builder
	 */
	public function add_select( $columns ): Rsssl_Query_Builder {
		// if the column is an array we implode it.
		if ( is_array( $columns ) ) {
			$this->columns = implode( ', ', $columns );
		} else {
			$columns        = sanitize_text_field( $columns );
			$this->columns .= ", $columns";
		}

		return $this;
	}

	/**
	 * Joins a table.
	 *
	 * @param string $table The name of the table to join.
	 *
	 * @return Rsssl_Query_Builder
	 */
	public function join( string $table ): Rsssl_Query_Builder {
		$this->join['table'] = $table;

		return $this;
	}

	/**
	 * Sets the on clause for the join
	 *
	 * @param string $column1 The first column to join on.
	 * @param string $operator The operator to use for the join.
	 * @param string $column2 The second column to join on.
	 *
	 * @return Rsssl_Query_Builder
	 */
	public function on( string $column1, string $operator, string $column2 ): Rsssl_Query_Builder {
		$this->join['on'] = array(
			'column1'  => $column1,
			'operator' => $operator,
			'column2'  => $column2,
		);

		return $this;
	}

	/**
	 * Sets the alias for the join.
	 *
	 * @param string $alias The alias to use for the join.
	 *
	 * @return Rsssl_Query_Builder
	 */
	public function as( string $alias ): Rsssl_Query_Builder {
		$this->join['alias'] = $alias;

		return $this;
	}


	/**
	 * Sets the order by.
	 *
	 * @param string $column The column to order by.
	 * @param string $direction The direction to order by.
	 *
	 * @return Rsssl_Query_Builder
	 * @throws Exception If the direction is invalid.
	 */
	public function order_by( string $column, string $direction = 'ASC' ): Rsssl_Query_Builder {
		$column    = $this->validate_column( $column );
		$direction = strtoupper( sanitize_text_field( $direction ) );

		$this->sort_column = array(
			'column'    => $column,
			'direction' => $direction,
		);

		// Checking if the $direction is a valid direction using strict comparison.
		if ( ! in_array( $direction, array( 'ASC', 'DESC' ), true ) ) {
			$direction = 'DESC';
		}

		$this->order_by = "ORDER BY $column $direction";

		return $this;
	}

	/**
	 * Validates the column and returns a sanitized column name or throws an exception.
	 *
	 * @param string $column The column to validate.
	 *
	 * @return string
	 * @throws Exception If the column is invalid.
	 */
	private function validate_column( string $column ): string {
		$column = sanitize_text_field( $column );
		// it could be this is a raw column name. We check if it starts with raw.
		if ( ! in_array( $column, $this->raw_columns, true ) && ! in_array( $column, $this->get_columns(), true ) ) {
			if (!empty($this->raw_columns) && is_array($this->raw_columns) && is_string($this->raw_columns[0])) {
				return $this->raw_columns[0];
			} elseif (!empty($this->get_columns()) && is_array($this->get_columns()) && is_string($this->get_columns()[0])) {
				return $this->get_columns()[0];
			} else {
				return '';
			}
		}

		return $column;
	}

	/**
	 * Sets the limit and offset.
	 *
	 * @param int $limit The limit for the number of records to retrieve.
	 * @param int $offset The offset from which to start retrieving records.
	 *
	 * @return Rsssl_Query_Builder
	 */
	public function limit( int $limit, int $offset = 0 ): Rsssl_Query_Builder {
		$this->limit  = $limit;
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Sets a condition to filter rows where the specified column is not null.
	 *
	 * @param  string $column  The column to check for null values.
	 *
	 * @return Rsssl_Query_Builder Returns the query builder instance.
	 * @throws Exception If the column is invalid.
	 */
	public function not_null( string $column ): Rsssl_Query_Builder {
		$column           = $this->validate_column( $column );
		$this->not_null[] = array(
			'column'   => $column,
			'operator' => 'IS NOT NULL',
		);
		return $this;
	}

	/**
	 * Builds, sanitizes and returns the query.
	 *
	 * @param bool $skip_limit Whether to skip the limit and offset.
	 *
	 * @return string|null
	 * @throws Exception If the column is invalid.
	 */
	public function get_query( bool $skip_limit = false ): ?string {
		global $wpdb;

		$query = "SELECT $this->columns FROM $this->table";

		if ( ! empty( $this->join ) ) {
			$query .= " INNER JOIN {$this->join['table']} AS {$this->join['alias']} ON {$this->join['on']['column1']} {$this->join['on']['operator']} {$this->join['on']['column2']}";
			// Append $joinQuery to your main SQL query string.

		}

		// Handling the where clauses.
		if ( ! empty( $this->where ) ) {
			$query .= ' WHERE ';
			$this->handle_where_clauses( $query, $this->where );
		}

		if ( ! empty( $this->or_where ) ) {
			$query .= empty( $this->where ) ? ' WHERE (' : ' AND (';
			$this->handle_where_clauses( $query, $this->or_where, 'OR' );
			$query .= ')';
		}

		// Handling the not null clauses.
		if ( ! empty( $this->not_null ) ) {
			$query .= empty( $this->where ) && empty( $this->or_where ) ? ' WHERE ' : ' AND ';
			$this->handle_not_null( $query, $this->not_null );
		}
		// handling the where_in clauses.
		if ( ! empty( $this->where_in ) ) {
			$query .= empty( $this->where ) && empty( $this->or_where ) && empty( $this->not_null ) ? ' WHERE ' : ' AND ';

			// New empty array to accumulate our "column IN (values)" fragments.
			$in_conditions = array();

			foreach ( $this->where_in as $where_in ) {
				$column = $this->validate_column( $where_in['column'] );

				// Wrap each value in single quotes and then implode.
				$values = implode(
					',',
					array_map(
						static function ( $value ) {
							return "'{$value}'";
						},
						$where_in['values']
					)
				);

				$in_conditions[] = "$column IN ($values)";
			}

			// Join our "column IN (values)" fragments with ' AND '.
			$query .= implode( ' AND ', $in_conditions );
		}

		if ( ! empty( $this->order_by ) ) {
			$query .= " $this->order_by";
		}

		// Prepare the WHERE clause using $wpdb->prepare.
		if ( ! empty( $this->where ) ) {
			$where_values = array_map(
				static function ( $where ) {
					return $where['value'];
				},
				$this->where
			);
		} else {
			$where_values = array();
		}

		if ( ! empty( $this->or_where ) ) {
			$or_where_values = array_map(
				static function ( $or_where ) {
					return $or_where['value'];
				},
				$this->or_where
			);
		} else {
			$or_where_values = array();
		}

		if ( ! empty( $this->group_by ) ) {
			$query .= " $this->group_by";
		}

		if ( ! $skip_limit ) {
			if ( ! empty( $this->limit ) ) {
				$query .= " LIMIT $this->limit";
			}

			if ( ! empty( $this->offset ) ) {
				$query .= " OFFSET $this->offset";
			}
		}

		$where_values = array_merge( $where_values, $or_where_values );

		// we add the join. This is a bit of a hack, but it works.
		// phpcs:ignore WordPress.DB
		return $wpdb->prepare( $query, $where_values );
	}

	/**
	 * Handles the where clauses.
	 *
	 * @param  string $query The query to append the where clauses to.
	 * @param  array  $clauses The where clauses to handle.
	 * @param  string $connector The connector to use for the where clauses.
	 *
	 * @throws Exception If the column is invalid.
	 */
	private function handle_where_clauses( string &$query, array $clauses, string $connector = 'AND' ): void {
		foreach ( $clauses as $index => $clause ) {
			if ( $index > 0 ) {
				$query .= " $connector ";
			}
			$column   = $this->validate_column( $clause['column'] );
			$operator = $this->validate_operator( $clause['operator'] );
			$query    .= "$column $operator %s";
		}
	}


	/**
	 * Returns the results.
	 *
	 * @return array|object|null The results.
	 * @throws Exception If the column is invalid.
	 */
	public function get() {
		global $wpdb;

		// phpcs:ignore WordPress.DB
		$this->results = $wpdb->get_results( $this->get_query() );

		return $this->results;
	}

	/**
	 * Returns the query.
	 *
	 * @return string|null
	 * @throws Exception If the column is invalid.
	 */
	public function to_sql(): ?string {
		return $this->get_query();
	}

	/**
	 * Returns the count.
	 *
	 * @return string|null
	 * @throws Exception If the column is invalid.
	 */
	public function count(): ?string {
		global $wpdb;
		$query = $this->get_query( true );

		$count_query = "SELECT COUNT(*) as count FROM ($query) as subquery";

		// phpcs:ignore WordPress.DB
		return $wpdb->get_var( $count_query );
	}


	/**
	 * Adds a where clause.
	 *
	 * @param  string $column The column to use for the where clause.
	 * @param  string $operator The operator to use for the where clause.
	 * @param  string $value The value to use for the where clause.
	 *
	 * @return Rsssl_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function where( string $column, string $operator, string $value ): Rsssl_Query_Builder {
		// Sanitizing the values.
		$operator = $this->validate_operator( $operator );
		$value    = sanitize_text_field( $value );
		if ( 'LIKE' === $operator || 'NOT_LIKE' === $operator ) {
			$value = "%$value%";
		}
		$column = $this->validate_column( $column );

		$whereStatement = array(
			'column'   => $column,
			'operator' => $operator,
			'value'    => $value,
		);

		$this->where[] = $whereStatement;

		return $this;
	}

	/**
	 * Adds an or where clause.
	 *
	 * @param  string $column The column to use for the where clause.
	 * @param  string $operator The operator to use for the where clause.
	 * @param  string $value The value to use for the where clause.
	 *
	 * @return Rsssl_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function or_where( string $column, string $operator, string $value ): Rsssl_Query_Builder {
		// sanitizing the values.
		$operator = $this->validate_operator( $operator );
		$value    = sanitize_text_field( $value );
		// if the operator is like we add the % to the value.
		if ( 'LIKE' === $operator || 'NOT_LIKE' === $operator ) {
			$value = "%$value%";
		}
		$column = $this->validate_column( $column );

		$this->or_where[] = array(
			'column'   => $column,
			'operator' => $operator,
			'value'    => $value,
		);

		return $this;
	}

	/**
	 * Adds a where in clause.
	 *
	 * @param string $column The column to use for the where in clause.
	 * @param array $values The values to use for the where in clause.
	 *
	 * @return Rsssl_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function where_in( string $column, array $values ): Rsssl_Query_Builder {
		// sanitizing the values.
		$column = $this->validate_column( $column );
		foreach ( $values as $key => $value ) {
			$values[ $key ] = sanitize_text_field( $value );
		}

		$this->where_in[] = array(
			'column' => $column,
			'values' => $values,
		);

		return $this;
	}

	/**
	 * Adds a where not in clause.
	 *
	 * @param  string $column The column to use for the where not in clause.
	 * @param  array  $values The values to use for the where not in clause.
	 *
	 * @return Rsssl_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function where_not_in( string $column, array $values ): Rsssl_Query_Builder {
		// sanitizing the values.
		$column = $this->validate_column( $column );
		$values = array_map( 'sanitize_text_field', $values );

		$this->where_not_in[] = $this->where_not_in( $column, $values );

		return $this;
	}

	/**
	 * Gets a single result.
	 *
	 * @return mixed|null
	 * @throws Exception If the column is invalid.
	 */
	public function first() {
		$this->limit( 1 );
		$result = $this->get();

		return $result[0] ?? null;
	}

	/**
	 * Paginates the results.
	 *
	 * @param int $rows The number of rows to retrieve.
	 * @param int $page The page to retrieve.
	 *
	 * @return array
	 * @throws Exception If the column is invalid.
	 */
	public function paginate( int $rows = 0, int $page = 0 ): array {
		$page = max( 1, $page );
		$rows = max( 1, $rows );

		$this->limit( $rows, ( $page - 1 ) * $rows );
		$results   = $this->get();
		$total     = $this->count();
		$last_page = ceil( $total / $rows );

		return array(
			'data'       => $results,
			'sorting'    => array( $this->sort_column ),
			'pagination' => array(
				'totalRows'   => $total,
				'perPage'     => $rows,
				'currentPage' => $page,
				'lastPage'    => $last_page,
			),
			// if the debug option in WordPress is set to true, the query will be returned.
			'query'      => $this->to_sql(), // - uncomment this line if you want to see the query.
		);
	}

	/**
	 * Retrieves the results of the Data.
	 *
	 * @return array The results of the Data.
	 * @throws Exception
	 */
	public function get_results(): array {
		return array(
			'data' => $this->get(),
		);
	}

	/**
	 * Returns the columns for the table.
	 *
	 * @return array
	 */
	public function get_columns(): array {
		// we check if the columns are already set.
		if ( ! empty( $this->fetched_columns ) ) {
			return $this->fetched_columns;
		}

		global $wpdb;
		$query = "SHOW COLUMNS FROM $this->table";

		// phpcs:ignore WordPress.DB
		$result = $wpdb->get_results( $query );

		return array_column( $result, 'Field' );
	}

	/**
	 * Returns the table name.
	 *
	 * @return string
	 */
	public function get_table(): string {
		return $this->table;
	}

	/**
	 * Validates the operator.
	 *
	 * @param  string $operator The operator to validate.
	 *
	 * @return string
	 * @throws Exception If the operator is invalid.
	 */
	private function validate_operator( string $operator ): string {
		$operator = strtoupper( sanitize_text_field( $operator ) );
		if ( ! in_array( $operator, array( '=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE' ), true ) ) {
			$operator = '=';
		}

		return $operator;
	}

	/**
	 * Extract column name form a raw string value.
	 *
	 * @param string $column The column to extract the name from.
	 *
	 * @return string|null
	 */
	public function extract_column_name( string $column ): ?string {
		$pattern = '/\s+as\s+(\w+)/';
		if ( preg_match( $pattern, $column, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Adds a raw column.
	 *
	 * @param string $column The column to add as a raw column.
	 *
	 * @return void
	 */
	public function add_raw_column( string $column ): void {
		$this->raw_columns[] = sanitize_text_field( $this->extract_column_name( $column ) );
	}

	/**
	 * Sets the group by clause.
	 *
	 * @param array $columns The columns to group by.
	 *
	 * @return Rsssl_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function group_by( array $columns ): Rsssl_Query_Builder {
		$column         = implode(
			', ',
			array_map(
				function ( $column ) {
					return $this->validate_column( $column );
				},
				$columns
			)
		);
		$this->group_by = "GROUP BY $column";

		return $this;
	}

	/**
	 * Checks if the table exists.
	 *
	 * @return bool
	 */
	private function table_exists(): bool {
		global $wpdb;
		// phpcs:ignore WordPress.DB
		$result = $wpdb->get_results( "SHOW TABLES LIKE '$this->table'" );
		return ! empty( $result );
	}

	/**
	 * Handles not null columns in the query.
	 *
	 * @param  string $query  The query string to modify.
	 * @param  array  $not_null  The array of not null columns.
	 *
	 * @return void
	 *
	 * @throws Exception If the column is invalid.
	 */
	private function handle_not_null( string &$query, array $not_null ): void {
		if ( ! empty( $not_null ) ) {
			foreach ( $not_null as $index => $column ) {
				$column = $this->validate_column( $column['column'] );
				$query .= ( $index > 0 ? ' AND ' : '' ) . "$column IS NOT NULL";
			}
		}
	}
}
