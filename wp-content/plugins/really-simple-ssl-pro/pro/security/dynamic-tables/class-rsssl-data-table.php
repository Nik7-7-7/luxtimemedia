<?php
/**
 * Implementation of the Database Rsssl_Data_Table for Really Simple Plugins.
 *
 * This file contains the Database Rsssl_Data_Table which provides a structured
 * way to generate and manage datatable actions. Designed to offer flexible
 * and efficient operations, it's part of the suite of tools
 * developed for Really Simple Plugins.
 *
 * @package ReallySimplePlugins
 * @author Marcel Santing
 * @version 1.0
 */

namespace RSSSL\Pro\Security\DynamicTables;

use Exception;
use RuntimeException;

/**
 * Class Rsssl_Data_Table
 *
 * @package ReallySimplePlugins
 * @since 1.0
 * @version 1.0
 */
class Rsssl_Data_Table {

	/**
	 * The POST variable from the ajax request.
	 *
	 * @var mixed
	 */
	public $post;

	/**
	 * The paging parameters.
	 *
	 * @var array|int[]
	 */
	private $paging;

	/**
	 * The query builder instance.
	 *
	 * @var Rsssl_Query_Builder $query_builder
	 */
	private $query_builder;

	/**
	 * The columns to validate as raw columns.
	 *
	 * @var array $validate_raw
	 */
	private $validate_raw;

	/**
	 * Rsssl_Data_Table constructor.
	 *
	 * @param array                                       $post // the POST variable from the ajax request.
	 * @param  $query_builder // the query builder instance.
	 */
	public function __construct( array $post, $query_builder ) {
		$this->post          = $post;
		$this->validate_raw  = array();
		$this->query_builder = $query_builder;
	}

	/**
	 * Sets a not null condition on a column in the query builder.
	 *
	 * @param  string $column  The column to set the not null condition on.
	 *
	 * @return $this
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function not_null( string $column ): Rsssl_Data_Table {
		$this->query_builder->not_null( $column );
		return $this;
	}


	/**
	 * This class validates all sorting parameters.
	 * it validates existence of the parameters and columns.
	 *
	 * @param array $array_data // the default sorting parameters.
	 *
	 * @throws Exception // throws an exception if the sortColumn or sortDirection is invalid.
	 */
	public function validate_sorting( array $array_data = array() ): Rsssl_Data_Table {
		// First, we check if the sortColumn and sortDirection are set.
		if ( isset( $this->post['sortColumn']['column'] ) && isset( $this->post['sortDirection'] ) ) {
			// Sanitize the sort column and direction.
			$sort_column    = sanitize_key( $this->post['sortColumn']['column'] );
			$sort_direction = sanitize_key( $this->post['sortDirection'] );

			// Check if the sortColumn is a valid column.
			if ( ! in_array( $sort_column, $this->query_builder->get_columns(), true )
				&& ! in_array( $sort_column, $this->validate_raw, true ) ) {
				throw new Exception( 'Invalid sort column' );
			}

			// Check if the sortDirection is a valid direction using strict comparison.
			if ( ! in_array( $sort_direction, array( 'asc', 'desc' ), true ) ) {
				throw new Exception( 'Invalid sort direction' );
			}

			$this->query_builder->order_by( $sort_column, $sort_direction );
		} elseif ( ! empty( $array_data ) ) {
			// If the sortColumn and sortDirection are not set, use the default sorting.
			$this->query_builder->order_by( $array_data['column'], $array_data['direction'] );
		}

		return $this;
	}

	/**
	 * Fetches the columns from the query_builder.
	 *
	 * @return array
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	private function get_columns(): array {
		return $this->query_builder->get_columns();
	}


	/**
	 * Sets the columns for selection in the query.
	 *
	 * @param  array $array_data  // the columns to select.
	 *
	 * @return $this // returns the class instance
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function set_select_columns( array $array_data ): Rsssl_Data_Table {
		// we loop through the array and check if the column is valid.
		// and if the column starts with raw: we exclude it from the check.
		$raw_columns = array();
		foreach ( $array_data as $key => $column ) {
			$column = sanitize_text_field( $column );
			if ( false === strpos( $column, 'raw:' ) ) {
				//@todo maybe set a default here. Removed the log as it doesn't help anybody
			} else {
				// we remove the column from the array and add it to the rawColumns array.
				unset( $array_data[ $key ] );
				$raw_columns[] = str_replace( 'raw:', '', $column );
				// also we tell the query builder that this is a raw column.
				$this->query_builder->add_raw_column( $column );
			}
		}

		// we get the first array element and add it to the query.
		if ( isset( $array_data[0] ) ) {
			$this->query_builder->select_columns( $array_data[0] );
		}

		// we loop through the rest of the array and add it to the query.
		$array_count = count( $array_data ); // Store the count in a variable.
		for ( $i = 1; $i < $array_count; $i++ ) {
			if ( isset( $array_data[ $i ] ) ) {
				$this->query_builder->add_select( $array_data[ $i ] );
			}
		}

		// we add the raw columns to the query.
		foreach ( $raw_columns as $raw_column ) {
			// Remove curly braces from the raw column, if any.
			$raw_column = str_replace( array( '{', '}' ), '', $raw_column );
			$this->query_builder->add_select( $raw_column );

			$exploded = explode( ' as ', $raw_column );
			if ( isset( $exploded[1] ) ) {
				$column_name          = $exploded[1];
				$this->validate_raw[] = $column_name;
			}
		}

		return $this;
	}

	/**
	 * Return the results from the query with pagination.
	 *
	 * @return array
	 * @throws Exception // throws an exception if the page or perPage is invalid.
	 */
	public function get_results(): array {
		return $this->query_builder->paginate( ...$this->paging );
	}

	public function get_results_without_paginate(): array {
		return $this->query_builder->get_results( ...$this->paging );
	}

	/**
	 * Validates and sets the pagination parameters.
	 *
	 * @return $this
	 * @throws Exception // throws an exception if the page or perPage is invalid.
	 */
	public function validate_pagination(): Rsssl_Data_Table {
		$per_page = 10;
		$page     = 1;
		// we check if the paging parameters are set.
		if ( isset( $this->post['page'] ) ) {
			// we check if the page is a number.
			if ( ! is_numeric( $this->post['page'] ) ) {
				throw new Exception( 'Invalid page number' );
			}
			$page = $this->post['page'];
		}

		if ( isset( $this->post['currentRowsPerPage'] ) ) {
			// we check if the perPage is a number.
			if ( ! is_numeric( $this->post['currentRowsPerPage'] ) ) {
				throw new Exception( 'Invalid per page number' );
			}
			$per_page = $this->post['currentRowsPerPage'];
		}
		$this->paging = array( $per_page, $page );

		return $this;
	}

	/**
	 * Validates and sets the search parameters.
	 *
	 * @return $this
	 * @throws Exception // throws an exception if the search column is invalid.
	 */
	public function validate_search(): Rsssl_Data_Table {

		if ( isset( $this->post['search'] ) && count( $this->post['searchColumns'] ) > 0 ) {

			// we check if the searchColumns are valid.
			foreach ( $this->post['searchColumns'] as $column ) {
				$column = sanitize_text_field( $column );

				if ( ! in_array( $column, $this->get_columns(), true ) ) {
					// We check if it is in the raw columns.
					if ( ! in_array( $column, $this->validate_raw, true ) ) {
						throw new Exception( esc_html( 'Invalid search column ' . $column ) );
					}
				}
			}
			// we add the search to the query.
			foreach ( $this->post['searchColumns'] as $column ) {
				// if the column is a raw column we add it as a raw column.
				if ( in_array( $column, $this->validate_raw, true ) ) {
					$this->query_builder->or_where( $column, 'LIKE', $this->post['search'] );
					continue;
				}
				$column = sanitize_text_field( $column );
				$this->query_builder->or_where( $column, 'LIKE', $this->post['search'] );
			}
		}

		return $this;
	}

	/**
	 * Validates and sets the filter parameters.
	 *
	 * @return $this
	 * @throws Exception // throws an exception if the filter column is invalid.
	 */
	public function validate_filter(): Rsssl_Data_Table {
		if ( isset( $this->post['filterValue'] ) && '' !== $this->post['filterColumn'] ) {
			if ( ! in_array( $this->post['filterColumn'], $this->get_columns(), true ) ) {
				throw new Exception( esc_html( 'Invalid filter column ' . $this->post['filterColumn'] ) );
			}

			$ignored_values = array( 'all', 'All', 'ALL', 'none', 'None', 'NONE' );

			if ( in_array( $this->post['filterValue'], $ignored_values, true ) ) {
				return $this;
			}

			$this->query_builder->where(
				sanitize_text_field( $this->post['filterColumn'] ),
				'=',
				sanitize_text_field( $this->post['filterValue'] )
			);
			// we add the filter to the query.
		}

		return $this;
	}

	/**
	 * Sets the table for the query to join.
	 *
	 * @param string $table // the table to join.
	 *
	 * @return $this
	 */
	public function join( $table ): Rsssl_Data_Table {
		$this->query_builder->join( $table );
		return $this;
	}

	/**
	 * Sets the on clause for the query.
	 *
	 * @param string $column1 // the first column to join on.
	 * @param string $operator // the operator to join on.
	 * @param string $column2 // the second column to join on.
	 *
	 * @return $this
	 */
	public function on( string $column1, string $operator, string $column2 ): Rsssl_Data_Table {
		$this->query_builder->on( $column1, $operator, $column2 );
		return $this;
	}

	/**
	 * Sets the alias for the table.
	 *
	 * @param string $alias // the alias to use for the table.
	 *
	 * @return $this
	 */
	public function as( string $alias ): Rsssl_Data_Table {
		$this->query_builder->as( $alias );
		return $this;
	}

	/**
	 * Sets the where clause for the query.
	 *
	 * @param  array $array_data // the where clause.
	 *
	 * @return $this
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function set_where( array $array_data ): Rsssl_Data_Table {
		$this->query_builder->where( $array_data[0], $array_data[1], $array_data[2] );

		return $this;
	}

	/**
	 * Set the Where In clause.
	 *
	 * @param string $column // the column to check.
	 * @param array  $array_data // the array of values to check.
	 *
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function set_where_in( string $column, array $array_data ): Rsssl_Data_Table {
		$this->query_builder->where_in( $column, $array_data );
		return $this;
	}

	/**
	 * Set the Where Not In clause.
	 *
	 * @param  array $columns // the columns to group by.
	 *
	 * @return Rsssl_Data_Table
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function group_by( $columns ): Rsssl_Data_Table {
		if ( ! is_array( $columns ) ) {
			$columns = array( $columns );
		}
		$this->query_builder->group_by( $columns );
		return $this;
	}

	/**
	 * Set the Where Not In clause.
	 *
	 * @param string $column // the column to check.
	 * @param array  $values // the array of values to check.
	 *
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function set_where_not_in( string $column, array $values ): Rsssl_Data_Table {
		$this->query_builder->where_not_in( $column, $values );
		return $this;
	}

	/**
	 * Fetches the query from the query builder.
	 *
	 * @throws Exception // throws an exception if the column is invalid.
	 */
	public function get_query() {
		return $this->query_builder->get_query();
	}
}
