<?php
/**
 * Abstract class for managing database tables and queries.
 *
 * This abstract class provides a foundation for managing database tables and building queries.
 *
 * @package ReallySimplePlugins
 * @subpackage Database
 * @category Abstract Classes
 * @author Marcel Santing
 * @version 1.0
 */

namespace RSSSL\Pro\Security\DynamicTables;

/**
 * Abstract class for managing database tables and queries.
 *
 * This abstract class provides a foundation for managing database tables and building queries.
 *
 * @package ReallySimplePlugins
 * @author Marcel Santing
 * @version 1.0
 */
abstract class Rsssl_Abstract_Database_Manager {
	/**
	 * The name of the table to interact with.
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
	 * An array representing the column or columns used for sorting.
	 *
	 * @var array
	 */
	protected $sort_column;

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
	 * Represents an SQL JOIN clause.
	 *
	 * @var string|array
	 */
	protected $join;

	/**
	 * Represents an SQL WHERE clause.
	 *
	 * @var array
	 */
	protected $where;

	/**
	 * Represents an additional SQL WHERE clause to be combined with OR.
	 *
	 * @var string|array
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
	 * Represents an SQL GROUP BY clause.
	 *
	 * @var string
	 */
	protected $group_by;
	/**
	 * @var array
	 */
	protected $fetched_columns;
	/**
	 * @var array
	 */
	protected $raw_columns;

	/**
	 * Constructor to set the table name.
	 *
	 * @param  string  $table  The name of the table to interact with.
	 */
	public function __construct( string $table ) {
		$this->table           = $table;
		$this->where           = array();
		$this->or_where        = array();
		$this->columns         = '*';
		$this->fetched_columns = array();
		$this->raw_columns     = array();
		$this->join            = array();
	}
}
