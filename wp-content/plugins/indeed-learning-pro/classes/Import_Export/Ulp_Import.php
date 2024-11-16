<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('Indeed_Import')){
	 require_once ULP_PATH . 'classes/Import_Export/Indeed_Import.php';
}
if (class_exists('Ulp_Import')){
	 return;
}
class Ulp_Import extends Indeed_Import{
	/*
	 * @param string ($entity_name)
	 * @param string ($entity_opt)
	 * @param object ($xml_object)
	 * @return none
	 */
	protected function do_import_custom_table($entity_name, $entity_opt, &$xml_object){
			global $wpdb;
			$table = $wpdb->prefix . $entity_name;
			if (!$xml_object->$entity_name->Count()){
				return;
			}
			foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$values = array();
					foreach ($object as $key => $value) {
							$values [] = "'$value'";
					}
					$insert_string = "VALUES(" . implode(',', $values) . ")";
					$this->do_basic_insert($table, $insert_string);
			}
	}
	/*
	 * @param string (table name)
	 * @param string (insert values)
	 * @return none
	 */
	private function do_basic_insert($table='', $insert_values=''){
			global $wpdb;
			$query = "INSERT IGNORE INTO $table $insert_values;";
			$wpdb->query( $query );
	}
}
