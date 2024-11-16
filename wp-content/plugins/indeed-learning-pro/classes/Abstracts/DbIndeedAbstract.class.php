<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('DbIndeedAbstract')){
	 return;
}
abstract class DbIndeedAbstract{
	/**
	 * @var string
	 */
	protected $table;
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}
	/**
	 * @param string (select single column result ex " id ")
	 * @param string ( conditions after WHERE statement. ex ' x=1 AND y=2')
	 * @return mixed
	 */
	public function getVar($select='', $where=''){
		global $wpdb;
		$query = "SELECT $select FROM {$this->table} WHERE $where;";
		return $wpdb->get_var( $query );
	}
	/**
	 * @param string (select multiple columns result ex " id, status ")
	 * @param string ( conditions after WHERE statement. ex ' x=1 AND y=2')
	 * @return array
	 */
	protected function getRow($select='', $where=''){
		global $wpdb;
		$query = "SELECT $select FROM {$this->table} WHERE $where;";
		return (array)$wpdb->get_row( $query );
	}
	/**
	 * @param string (select multiple columns result ex " id, status ")
	 * @param string ( conditions after WHERE statement. ex ' x=1 AND y=2')
	 * @return array
	 */
	protected function getResults($select='', $where=''){
		global $wpdb;
		$query = "SELECT $select FROM {$this->table} WHERE $where;";
		$data = $wpdb->get_results( $query );
		return $data ? indeed_convert_to_array($data) : array();
	}
	/**
	 * @param string
	 * @return int (insert id)
	 */
	protected function insert($values=''){
		global $wpdb;
		$query = "INSERT INTO {$this->table} VALUES($values);";
		$wpdb->query( $query );
		return $wpdb->insert_id;
	}
	/**
	 * @param string
	 * @param string
	 * @return bool
	 */
	protected function update($set='', $where=''){
		global $wpdb;
		$q = "UPDATE {$this->table} SET $set WHERE $where;";
		return $wpdb->query( $q );
	}
	/**
	 * @param string
	 * @return bool
	 */
	protected function delete($where=''){
		global $wpdb;
		$query = "DELETE FROM {$this->table} WHERE $where;";
		return $wpdb->query( $query );
	}
}
