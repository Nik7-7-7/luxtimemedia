<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
final class DatabaseTables {

	const TABLE_CUSTOMERS = 'mpa_customers';

	/**
	 *  [ {tableName} => {handlerName}, ... ]
	 */
	const TABLES = [
		self::TABLE_CUSTOMERS => 'createCustomerTable',
	];

	private function __construct() {
	}

	/**
	 * Actions initialization:
	 * - Creates new tables for each new site from the multisite network.
	 * - Deletion of created tables when deleting a multisite.
	 */
	public static function addActionsForMultisite() {
		add_action( 'wp_insert_site', function ( $newSite ) {
			self::configureNewSiteTables( $newSite );
		}, 10, 1 );
		add_filter( 'wpmu_drop_tables', function ( $tables, $blogId ) {
			return self::dropTables( $tables, $blogId );
		}, 10, 2 );
	}

	/**
	 * @param \WP_Site $newSite
	 */
	private static function configureNewSiteTables( $newSite ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		/*
		 * Additional check in case the plugin is not network active.
		 */
		if ( ! is_plugin_active_for_network( plugin_basename( \MotoPress\Appointment\PLUGIN_FILE ) ) ) {
			return;
		}

		switch_to_blog( $newSite->id );
		self::createTables();
		restore_current_blog();
	}

	/**
	 * @param array $tables
	 * @param int $blogId
	 *
	 * @return array $tables
	 */
	private static function dropTables( $tables, $blogId ) {
		global $wpdb;

		switch_to_blog( $blogId );

		foreach ( self::TABLES as $table => $handler ) {
			$tables[] = $wpdb->prefix . $table;
		}

		restore_current_blog();

		return $tables;
	}

	public static function createCustomerTable() {
		global $wpdb;

		$tableName = $wpdb->prefix . self::TABLE_CUSTOMERS;
		$query     = "CREATE TABLE IF NOT EXISTS $tableName ("
		             . " id INT NOT NULL AUTO_INCREMENT,"
		             . " user_id INT NULL UNIQUE,"
		             . " name VARCHAR(60) NOT NULL,"
		             . " email VARCHAR(60) NULL UNIQUE,"
		             . " phone VARCHAR(20) NULL UNIQUE,"
		             . " date_registered DATETIME NOT NULL default '0000-00-00 00:00:00',"
		             . " last_active DATETIME NULL,"
		             . " PRIMARY KEY (id)"
		             . ") CHARSET=utf8 AUTO_INCREMENT=1";

		$wpdb->query( $query );
	}

	/**
	 * Creates all custom database tables needed for mpa
	 */
	public static function createTables() {
		foreach ( self::TABLES as $handler ) {
			if ( method_exists( __CLASS__, $handler ) ) {
				self::{$handler}();
			}
		}
	}
}