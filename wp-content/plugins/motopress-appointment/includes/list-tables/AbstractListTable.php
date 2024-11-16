<?php

namespace MotoPress\Appointment\ListTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
abstract class AbstractListTable {

	/**
	 * @var string Unprefixed name.
	 *
	 * @since 1.1.0
	 */
	protected $name;

	/**
	 * @var array
	 *
	 * @since 1.1.0
	 */
	protected $settings = array();

	/**
	 * @var array
	 *
	 * @since 1.1.0
	 */
	protected $items = array();

	/**
	 * @var bool
	 *
	 * @since 1.1.0
	 */
	protected $isLoaded = false;

	/**
	 * @param string|array $args Name (singular) or array of args.
	 *     @param string $args['singular'] Required.
	 * @param array $items Optional.
	 *
	 * @since 1.1.0
	 */
	public function __construct( $args, $items = array() ) {

		$this->items = $items;

		// Init settings
		$defaultSettings = array();

		if ( is_array( $args ) ) {
			$this->settings = array_merge( $defaultSettings, $args );
		} else {
			$this->settings = array_merge( $defaultSettings, array( 'singular' => $args ) );
		}

		$this->name = $this->settings['singular'];
	}

	/**
	 * @since 1.1.0
	 */
	public function load() {

		if ( $this->isLoaded ) {
			return;
		}

		if ( empty( $this->items ) ) {
			$this->loadItems();
		}

		$this->isLoaded = true;
	}

	/**
	 * @since 1.1.0
	 */
	protected function loadItems() {
		// Do nothing by default. Maybe items already set through the constructor
	}

	/**
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function hasItems() {
		return ! empty( $this->items );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getId() {
		return mpa_tmpl_id( mpa_prefix( $this->name ) );
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	abstract public function getColumns();

	/**
	 * @since 1.1.0
	 */
	public function display() {
		// Did not load yet? Load now
		$this->load();

		mpa_display_template( 'private/fields/list-table.php', array( 'listTable' => $this ) );
	}

	/**
	 * Only intended to use in templates. See display() method.
	 *
	 * @access protected
	 *
	 * @since 1.1.0
	 */
	public function displayRows() {
		foreach ( $this->items as $item ) {
			$this->displayRow( $item );
		}
	}

	/**
	 * @param mixed $item
	 *
	 * @since 1.1.0
	 */
	protected function displayRow( $item ) {

		echo '<tr>';

		foreach ( $this->getColumns() as $columnName => $columnLabel ) {
			$columnClasses = "{$columnName} column-{$columnName}";

			$columnAtts = array(
				'class'        => $columnClasses,
				'data-colname' => wp_strip_all_tags( $columnLabel ), // Do like WP_List_Table does
			);

			if ( ! $this->displayOwnColumn( $columnName, $item, $columnAtts ) ) {
				echo '<td' . mpa_tmpl_atts( $columnAtts ) . '>';
					$this->displayColumn( $columnName, $item );
				echo '</td>';
			}
		}

		echo '</tr>';
	}

	/**
	 * @param string $columnName
	 * @param mixed $item
	 * @param array $columnAtts Default column attributes.
	 * @return bool False it this method does nothing and we need to call
	 *     displayColumn() instead.
	 *
	 * @since 1.1.0
	 */
	protected function displayOwnColumn( $columnName, $item, $columnAtts ) {
		return false; // Redirect to displayColumn()
	}

	/**
	 * @param string $columnName
	 * @param mixed $item
	 *
	 * @since 1.1.0
	 */
	abstract protected function displayColumn( $columnName, $item);
}
