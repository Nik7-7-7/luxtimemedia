<?php
if (!class_exists('IndeedExport')):
class IndeedExport{
	/*
	 * @var array
	 */
	protected $entities = array();
	/*
	 * @var string
	 */
	protected $file = '';


	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){}


	/*
	 * @param array
	 * @return none
	 */
	public function setEntity($params=array()){
		if (!empty($params['table_name'])){
			$table_name = $params['table_name'];
			if (empty($this->entities[$table_name])){
				$this->entities[$table_name] = $params;
			}
		}
	}


	/*
	 * @param string
	 * @return none
	 */
	public function setFile($filename=''){
		$this->file = $filename;
	}


	/*
	 * @param none
	 * @return boolean
	 */
	public function run(){
		// remove old files
		$directory = IHC_PATH . 'temporary/';
		$files = scandir( $directory );
		foreach ( $files as $file ){
				$fileFullPath = $directory . $file;
				if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
						$extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
						if ( $extension == 'xml' ){
								unlink( $fileFullPath );
						}
				}
		}

		if ($this->entities){
			$xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
			///write info
			$temp_entity = $this->entities;
			foreach ($temp_entity as &$temp_arr){
				if (isset($temp_arr['values'])){
					unset($temp_arr['values']);
				} else if (isset($temp_arr['keys_to_select'])){
					unset($temp_arr['keys_to_select']);
				}
			}
			if (!empty($temp_entity['usermeta'])){
				$temp_entity['indeed_wp_capabilities'] = '';
			}
			$this->array_to_xml(array('import_info'=>$temp_entity), $xml_data);

			foreach ($this->entities as $table => $options){
				switch ($table){
					case 'options':
						$db_data = $options['values'];
						foreach ($db_data as $db_data_key=>$db_data_value){
							if (is_array($db_data_value)){
								$db_data[$db_data_key] = serialize($db_data_value);
							}
						}
						break;
					case 'postmeta':
						$db_data = $this->get_db_data_postmeta($options['keys_to_select']);
						break;
					case 'usermeta':
						global $wpdb;
						$options['selected_cols'] = " user_id, meta_key, meta_value ";
						$cap = $wpdb->get_blog_prefix() . 'capabilities';
						$options['where_clause'] = " AND meta_key NOT LIKE '$cap' ";
						$db_data = $this->get_db_data_for_entity($table, $options);

						/// write capabilities like a table
						$options['where_clause'] = " AND meta_key LIKE '$cap' ";
						$options['selected_cols'] = " user_id, meta_value ";
						$capabilities = $this->get_db_data_for_entity($table, $options);
						if ($capabilities){
							$this->array_to_xml(array('indeed_wp_capabilities'=>$capabilities), $xml_data);
						}
						break;
					case 'users':
					default:
						$db_data = $this->get_db_data_for_entity($table, $options);
					break;
				}

				if ($db_data){
					$this->array_to_xml(array($table=>$db_data), $xml_data);
					unset($db_data);
				}
			}
			$result = $xml_data->asXML($this->file);
			return TRUE;
		}
		return FALSE;
	}


	/*
	 * @param array, object
	 * @return none
	 */
	protected function array_to_xml( $data=array(), &$xml_data=null ){
		if (!empty($data)){
			foreach ($data as $key => $value){
				if (is_numeric($key)){
					$key = 'item' . $key;
				}
				if (is_array($value)){
					$subnode = $xml_data->addChild($key);
					$this->array_to_xml($value, $subnode);
				} else {
					$xml_data->addChild("$key", htmlspecialchars("$value")); ///htmlspecialchars("$value")
				}
			}
		}
	}


	/*
	 * @param string (name of table)
	 * @param array (options for query)
	 * @param bool (return data as object)
	 * @return array || object
	 */
	protected function get_db_data_for_entity($table='', $options=array()){
		global $wpdb;
		$array = array();
		if ($table){
			if (empty($options['selected_cols'])){
				$options['selected_cols'] = '*';
			}
			if (empty($options['where_clause'])){
				$options['where_clause'] = '';
			}
			if (empty($options['limit'])){
				$options['limit'] = '';
			}
			$table_name = $options['full_table_name'];
			$q = "SELECT {$options['selected_cols']}
						FROM $table_name
						WHERE 1=1
						{$options['where_clause']}
						{$options['limit']}
			";
			$data = $wpdb->get_results($q);

			if ($data){
				foreach ($data as $object){
					$array[] = (array)$object;
				}
			}
		}
		return $array;
	}


	/*
	 * @param array
	 * @return array
	 */
	protected function get_db_data_postmeta($keys_to_select=array()){
		$array = array();
		foreach ($keys_to_select as $key){
			$array[$key] = Ihc_Db::get_all_post_meta_data_for_meta_key($key);
		}
		return $array;
	}


}
endif;
