<?php

namespace MasterStudy\Lms\Pro\RestApi\Repositories\DataTable;

use MasterStudy\Lms\Pro\RestApi\Repositories\AnalyticsRepository;

/**
 * Helper Class for DataTables
 */
class DataTableAbstractRepository extends AnalyticsRepository {

	public int $start = 0;

	public int $limit = 10;

	/**
	 * Table of courses (default stm_lms_user_courses)
	 */
	public string $table_courses = '';

	/**
	 * Table of orders (default stm_lms_order_items)
	 */
	public string $table_orders = '';

	public string $sort_by = 'revenue';

	public string $post_type = '';

	public string $sort_dir = 'ASC';

	public array $select = array();

	public array $join = array();

	public array $group_by = array();

	public array $search_column = array();

	public string $search_value = '';

	public function __construct( string $date_from, string $date_to, int $start = 0, int $limit = 10, $search_value = '' ) {
		parent::__construct( $date_from, $date_to );

		$this->start         = $start;
		$this->limit         = $limit;
		$this->table_courses = stm_lms_user_courses_name( $this->db );
		$this->table_orders  = stm_lms_order_items_name( $this->db );
		$this->search_value  = esc_attr( $search_value );
	}
	/**
	 * Build SQL query
	 */
	public function get_query() {
		return $this;
	}

	/**
	 * Calculate total posts
	 *
	 * @param string $post_type
	 *
	 * @return string
	 */
	public function get_total( $status = array( 'publish' ) ) {
		$where = array(
			'p.post_type' => $this->post_type,
			array(
				'column'   => 'p.post_status',
				'operator' => 'IN',
				'value'    => $status,
			),
		);
		if ( ! empty( $this->search_value ) && ! empty( $this->search_column ) ) {
			$search_value = esc_sql( $this->search_value );
			foreach ( $this->search_column as $column ) {
				$where[] = array(
					'condition' => 'OR',
					array(
						'column'   => 'p.post_title',
						'operator' => 'LIKE',
						'value'    => '%' . $search_value . '%',
					),
				);
			}
		}

		if ( $this->is_current_user_instructor() ) {
			$where[] = array(
				'condition' => 'AND',
				array(
					'column'   => 'p.post_author',
					'operator' => '=',
					'value'    => $this->current_instructor_id,
				),
			);
		}

		$sql = $this->query_where_array_to_string( $where, true );

		return $this->db->get_var( "SELECT COUNT(*) as count, post_title FROM {$this->db->posts} p {$sql}" );
	}

	/**
	 * Calculate order field and direction and apply to values
	 * @param array $order
	 * @param array $columns
	 */
	public function apply_sort( $order = array(), $columns = array(), $default_sort_by = 'revenue', $default_direction = 'asc' ) {
		if ( ! empty( $columns ) && ! empty( $order ) ) {
			$order  = reset( $order );
			$column = $order['column'] ?? 1;
			$dir    = $order['dir'] ?? $default_direction;

			if ( ! empty( $columns[ $column ]['data'] ) && 'number' !== $columns[ $column ]['data'] ) {
				$this->sort_by  = $columns[ $column ]['data'];
				$this->sort_dir = strtoupper( $dir );
				return $this;
			}
		}

		$this->sort_by  = $default_sort_by;
		$this->sort_dir = strtoupper( $default_direction );

		return $this;
	}

	/**
	 * Add Order field and direction to SQL query
	 * @return string
	 */
	public function pagination_query() {
		$sql = "ORDER BY {$this->sort_by} $this->sort_dir LIMIT {$this->limit}";

		// Add pagination offset
		if ( $this->start > 1 ) {
			$sql .= ' OFFSET ' . $this->start;
		}

		return $sql;
	}

	/**
	 * Collect fields to GROUP as SQL string
	 * @return string
	 */
	public function group_query() {
		return 'GROUP BY ' . implode( ',', $this->group_by ) . "\n";
	}

	/**
	 * Return where query as SQL string
	 * @param array $args
	 * @return string
	 */
	public function where_query( array $args = array() ) {
		$where = array();
		$sql   = '';
		if ( ! empty( $this->search_value ) && ! empty( $this->search_column ) ) {
			$search_value = esc_sql( $this->search_value );
			$temp         = array();
			foreach ( $this->search_column as $column ) {
				$temp[] = array(
					'column'   => $column,
					'operator' => 'LIKE',
					'value'    => '%' . $search_value . '%',
				);
			}
			$where[] = array(
				'condition' => 'OR',
			) + $temp;
		}

		if ( ! empty( $args ) ) {
			$where = array_merge( $where, $args );
		}

		if ( ! empty( $where ) ) {
			$condition = 'AND';
			if ( ! empty( $where['condition'] ) ) {
				$condition = esc_sql( $where['condition'] );
			}

			$sql = 'WHERE ' . implode( " {$condition} ", $this->query_where_array_to_string( $where ) );
		}

		return $sql . "\n";
	}

	/**
	 * Convert where expressions from array to compact where
	 * @param array $query_where
	 * @param bool $return_sql
	 * @return string|array
	 */
	public function query_where_array_to_string( array $query_where, bool $return_sql = false ) {
		$sql_conditions = array();

		foreach ( $query_where as $key => $value ) {
			if ( is_array( $value ) && isset( $value['column'] ) ) { // Standard condition
				$column         = $value['column'];
				$operator       = esc_sql( $value['operator'] ?? '=' );
				$prepared_value = $value['value'] ?? '';

				if ( in_array( $operator, array( 'IN', 'NOT IN' ), true ) ) {
					if ( is_array( $prepared_value ) ) {
						$prepared_value = '(' . implode(
							', ',
							array_map(
								fn( $item ) => "'" . esc_sql( $item ) . "'",
								$prepared_value
							)
						) . ')';
					} elseif ( is_string( $prepared_value ) ) {
						$prepared_value = '(' . esc_sql( $prepared_value ) . ')';
					}
				} elseif ( in_array( $operator, array( 'BETWEEN' ), true ) && is_array( $prepared_value ) ) {
					$prepared_value = implode(
						' AND ',
						array_map(
							fn( $item ) => "'" . $item . "'",
							$prepared_value
						)
					);
				} else {
					if ( empty( $value['value'] ) && ! empty( $value['value_exp'] ) ) {
						$prepared_value = $value['value_exp'];
					} else {
						$prepared_value = "'" . esc_sql( $prepared_value ) . "'";
					}
				}

				$sql_conditions[] = "($column $operator $prepared_value)";
			} elseif ( is_array( $value ) && isset( $value['condition'] ) ) { // Nested condition
				$condition = strtoupper( $value['condition'] );
				unset( $value['condition'] );
				$nested_conditions = $this->query_where_array_to_string( $value );
				$sql_conditions[]  = '(' . implode( " $condition ", $nested_conditions ) . ')';
			} else { // Simple key-value condition
				if ( is_array( $value ) ) {
					foreach ( $value as $name => $item ) {
						$sql_conditions[] = "($name = '" . esc_sql( $item ) . "')";
					}
				} else {
					$sql_conditions[] = "($key = '" . esc_sql( $value ) . "')";
				}
			}
		}

		return $return_sql ? 'WHERE ' . implode( ' AND ', $sql_conditions ) . "\n" : $sql_conditions;
	}

}
