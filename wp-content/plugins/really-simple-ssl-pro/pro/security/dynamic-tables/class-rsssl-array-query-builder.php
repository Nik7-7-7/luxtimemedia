<?php
/**
 * Implementation of the Rsssl_Array_Query_Builder, an intermediary layer that
 * allows arrays to mimic database request behaviors suitable for Datatables.
 *
 * @package ReallySimplePlugins
 * @author Marcel Santing
 * @version 1.0
 */

namespace RSSSL\Pro\Security\DynamicTables;

require_once rsssl_path . 'pro/security/dynamic-tables/class-rsssl-abstract-database-manager.php';

/**
 * Rsssl_Array_Query_Builder serves as an intermediary layer for arrays,
 * enabling them to mimic behaviors of database requests. Designed to
 * integrate seamlessly with Datatables, it provides functionality similar to
 * database operations but works directly on arrays.
 *
 * @package ReallySimplePlugins
 * @author Marcel Santing
 */
class Rsssl_Array_Query_Builder extends Rsssl_Abstract_Database_Manager {

	/**
	 * Data array to be processed.
	 *
	 * @var array
	 */
	private $data_array;

	/**
	 * Array of where values.
	 *
	 * @var array
	 */
	protected $where = array();

	/**
	 * Represents an additional SQL WHERE clause to be combined with OR.
	 *
	 * @var array
	 */
	protected $or_where;

	/**
	 * String values for order of data.
	 *
	 * @var string
	 */
	protected $order_by;

	/**
	 * The Constructor class.
	 *
	 * @param array $data_array The Array of processable data.
	 */
	public function __construct( $data_array ) {
		parent::__construct( '' );
		$this->data_array = $data_array;
	}

	/**
	 * Sets the columns to select.
	 *
	 * @param  array|string  $columns  The columns to select.
	 *
	 * @return Rsssl_Array_Query_Builder
	 */
	public function select_columns( $columns ): self {
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Validates the given column.
	 *
	 * @param  string $column  The column to validate.
	 *
	 * @return string The validated column.
	 * @throws RuntimeException If the column is invalid.
	 */
	private function validate_column( string $column ): string {
		// We sanitize the string.
		$column = sanitize_text_field( $column );

		// now we check if the column exists in the $this->>data_array.
		if ( ! array_key_exists( $column, $this->data_array[0] ) ) {
				throw new RuntimeException( 'Invalid column' );
		}
		return $column;
	}


	/**
	 * Adds columns to the select.
	 *
	 * @param  array|string $columns  The columns to select.
	 *
	 * @return Rsssl_Array_Query_Builder
	 */
	public function add_select( $columns ): self {
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
	 * Adds an or where clause.
	 *
	 * @param  string $column The column to use for the where clause.
	 * @param  string $operator The operator to use for the where clause.
	 * @param  string $value The value to use for the where clause.
	 *
	 * @return Rsssl_Array_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function or_where( string $column, string $operator, string $value ): Rsssl_Array_Query_Builder {
		// sanitizing the values.
		$operator = $this->validate_operator( $operator );
		$value    = sanitize_text_field( $value );
		// if the operator is like we add the % to the value.
		if ( 'LIKE' === $operator || 'NOT_LIKE' === $operator ) {
			$value = "%$value%";
		}

		$column           = $this->validate_column( $column );
		$this->or_where[] = array(
			'column'   => $column,
			'operator' => $operator,
			'value'    => $value,
		);
		return $this;
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
		$total     = $this->count();
		$last_page = ceil( $total / $this->limit );
		$results   = $this->get();

		return array(
			'data'       => $results,
			'sorting'    => array( $this->sort_column ),
			'pagination' => array(
				'totalRows'   => $total,
				'perPage'     => $rows,
				'currentPage' => $page,
				'lastPage'    => $last_page,
			),
		);
	}

	/**
	 * Retrieves the results of the Data.
	 *
	 * @return array The results of the Data.
	 */
	public function get_results(): array {
		return $this->get();
	}

	/**
	 * Fetches the results of the Data.
	 *
	 * @return array
	 */
	public function get(): array {
		$results = $this->data_array;
		$results = $this->applyWhereConditions( $results );
		$results = $this->applyOrWhereConditions( $results );
		$results = $this->applyOrderBy( $results );
		$results = $this->applyLimitAndOffset( $results );
		return $results;
	}

	/**
	 * Applies WHERE conditions to an array of results.
	 *
	 * @param  array $results  The array of results to apply the WHERE conditions to.
	 *
	 * @return array The filtered array of results after applying the WHERE conditions.
	 */
	private function applyWhereConditions( array $results ): array {
		if ( ! empty( $this->where ) ) {
			$results = array_filter(
				$results,
				function ( $item ) {
					foreach ( $this->where as $where_clause ) {
						$column   = $where_clause['column'];
						$operator = $where_clause['operator'];
						$value    = $where_clause['value'];
						if ( ! $this->compare( $item[ $column ], $operator, $value ) ) {
							return false;
						}
					}
					return true;
				}
			);
		}
		return $results;
	}

	/**
	 * Applies the order by clause to the given array of results.
	 *
	 * @param  array $results  The array of results to apply the order by clause to.
	 *
	 * @return array The sorted array of results.
	 */
	private function applyOrderBy( array $results ): array {
		if ( ! empty( $this->order_by ) ) {
			[$column, $direction] = explode( ' ', $this->order_by );
			usort(
				$results,
				function ( $a, $b ) use ( $column, $direction ) {
					return $this->compareColumns( $a[ $column ], $b[ $column ], $direction );
				}
			);
		}
		return $results;
	}

	/**
	 * Compares two columns and returns the result based on the given direction.
	 *
	 * @param  mixed  $a  The first column to compare.
	 * @param  mixed  $b  The second column to compare.
	 * @param  string $direction  The direction of the comparison, either 'ASC' or 'DESC'.
	 *
	 * @return int Returns 0 if the columns are equal. Returns -1 if $a is less than $b when $direction is 'ASC',
	 *             and returns 1 if $a is greater than $b when $direction is 'ASC'. For 'DESC', the returns are reversed.
	 */
	private function compareColumns( $a, $b, $direction ): int {
		if ( $a === $b ) {
			return 0;
		}
		if ( 'ASC' === $direction ) {
			return $a < $b ? -1 : 1;
		}

		return $a > $b ? -1 : 1;
	}

	/**
	 * Applies the limit and offset to the given array of results.
	 *
	 * @param  array $results  The array of results.
	 *
	 * @return array The modified array of results with the limit and offset applied.
	 */
	private function applyLimitAndOffset( array $results ): array {
		if ( ! empty( $this->limit ) ) {
			$results = array_slice( $results, $this->offset, $this->limit );
		}
		return $results;
	}

	/**
	 * Returns the count processed data.
	 *
	 * @return string|null
	 */
	public function count(): ?string {
		return count( $this->data_array );
	}

	/**
	 * Since this is not an actual database query builder we return a string.
	 *
	 * @return string|null
	 */
	public function to_sql(): ?string {
		return 'Not available for ArrayQueryBuilder.';
	}

	/**
	 * This is used for comparisons with operator strings
	 *
	 * @param  string $item_value The value of the item.
	 * @param  string $operator The operator to use.
	 * @param  string $value The value to compare with.
	 *
	 * @return bool
	 */
	private function compare( string $item_value, string $operator, string $value ): bool {
		switch ( $operator ) {
			case '=':
				return $item_value === $value;
			case '!=':
				return $item_value !== $value;
			case '>':
				return $item_value > $value;
			case '<':
				return $item_value < $value;
			case '>=':
				return $item_value >= $value;
			case '<=':
				return $item_value <= $value;
			case 'LIKE':
				return stripos( $item_value, trim( $value, '%' ) ) !== false;
			case 'NOT LIKE':
				return stripos( $item_value, trim( $value, '%' ) ) === false;
			case 'IN':
				return in_array( $item_value, (array) $value, true );
			case 'NOT IN':
				return ! in_array( $item_value, (array) $value, true );
			default:
				return false;
		}
	}

	/**
	 * Sets the columns for selection in the query. Not applicable for ArrayQueryBuilder.
	 *
	 * @param  bool $skip_limit Whether to skip the limit.
	 *
	 * @return string|null
	 */
	public function get_query( bool $skip_limit = false ): ?string {
		return 'Not applicable for ArrayQueryBuilder.';
	}

	/**
	 * Fetches the columns from the queryBuilder.
	 *
	 * @return array
	 */
	public function get_columns(): array {
		if ( ! empty( $this->data_array ) ) {
			return array_keys( $this->data_array[0] );
		}
		return array();
	}

	/**
	 * Adds a raw column to the list of raw columns.
	 *
	 * @param  mixed $column  The column to add.
	 *
	 * @return void
	 */
	public function add_raw_column( $column ): void {
		$this->raw_columns[] = sanitize_text_field( $this->extract_column_name( $column ) );
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
	 * Builds a Where In clause.
	 *
	 * @param  string $column The column to use.
	 * @param  array  $values The values to use.
	 *
	 * @return Rsssl_Array_Query_Builder
	 */
	public function where_in( string $column, array $values ): Rsssl_Array_Query_Builder {
		$this->where[] = array(
			'column'   => $column,
			'operator' => 'IN',
			'value'    => $values,
		);
		return $this;
	}

	/**
	 * Builds a Where Not In clause.
	 *
	 * @param  string $column The column to use.
	 * @param  array  $values The values to use.
	 *
	 * @return Rsssl_Array_Query_Builder
	 */
	public function where_not_in( string $column, array $values ): Rsssl_Array_Query_Builder {
		$this->where[] = array(
			'column'   => $column,
			'operator' => 'NOT IN',
			'value'    => $values,
		);
		return $this;
	}

	/**
	 * Sets the limit and offset for the query.
	 *
	 * @param  mixed $limit  The maximum number of rows to retrieve.
	 * @param  float $offset  The number of rows to skip from the beginning.
	 *
	 * @return void
	 */
	private function limit( $limit, float $offset ): void {
		$this->limit  = $limit;
		$this->offset = $offset;
	}

	/**
	 * Adds an order by clause.
	 *
	 * @param  string $column The column to sort by.
	 * @param  string $direction The direction to sort in.
	 *
	 * @return Rsssl_Array_Query_Builder
	 * @throws Exception If the column is invalid.
	 */
	public function order_by( string $column, string $direction = 'ASC' ): self {
		$column    = $this->validate_column( $column );
		$direction = strtoupper( $direction );
		if ( ! in_array( $direction, array( 'ASC', 'DESC' ), true ) ) {
			throw new RuntimeException( 'Invalid order by direction' );
		}
		$this->order_by = "$column $direction";
		return $this;
	}

	/**
	 * Validates the given operator.
	 *
	 * @param  string $operator  The operator to validate.
	 *
	 * @return string The validated operator.
	 * @throws RuntimeException If the operator is invalid.
	 */
	private function validate_operator( string $operator ): string {
		$operator = strtoupper( sanitize_text_field( $operator ) );
		if ( ! in_array( $operator, array( '=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE' ), true ) ) {
			throw new RuntimeException( 'Invalid operator' );
		}

		return $operator;
	}

	/**
	 * Applies OR Where conditions to the given array of results.
	 *
	 * @param  array $results  The array of results to apply the conditions to.
	 *
	 * @return array
	 */
	private function applyOrWhereConditions( array $results ): array {
		if ( ! empty( $this->or_where ) ) {
			$results = array_filter(
				$results,
				function ( $item ) {
					foreach ( $this->or_where as $where_clause ) {
						$column   = $where_clause['column'];
						$operator = $where_clause['operator'];
						$value    = $where_clause['value'];
						if ( $this->compare( $item[ $column ], $operator, $value ) ) {
							return true;
						}
					}
					return false;
				}
			);
		}
		return $results;
	}
}
