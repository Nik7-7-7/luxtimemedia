<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('Indeed_Export')){
	 return;
}
class Indeed_Export{
	/*
	 * @var array
	 */
	protected $entities = array();
	/*
	 * @var string
	 */
	protected $file = '';
	private $_export_students = FALSE;
	private $_export_instructors = FALSE;
	private $_custom_post_types = FALSE;

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
	public function setStudentsEntity($value=FALSE){
			$this->_export_students = $value;
	}
	public function setInstructorsEntity($value=FALSE){
			$this->_export_instructors = $value;
	}
	public function setCustomPostTypesEntity($value=FALSE){
			$this->_custom_post_types = $value;
	}
	/*
	 * @param none
	 * @return boolean
	 */
	public function run(){
			if (empty($this->entities) && empty($this->_export_students) && empty($this->_export_instructors) && empty($this->_custom_post_types) ){
					 return FALSE;
			}
			global $wpdb;
			$xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
			$temp_entity = array();
			/// ULP TABLES && settings from wp_options
			if ($this->entities){
				///write info
				$temp_entity = $this->entities;
				foreach ($temp_entity as &$temp_arr){
					if (isset($temp_arr['values'])){
						unset($temp_arr['values']);
					} else if (isset($temp_arr['keys_to_select'])){
						unset($temp_arr['keys_to_select']);
					}
				}

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
						default:
							$db_data = $this->get_db_data_for_entity($table, $options);
							break;
					}
					if ($db_data){
							$this->array_to_xml(array($table=>$db_data), $xml_data);
							unset($db_data);
					}
				}
			} /// end of entities
			$users_xml_data = array();
			$usermeta_xml_data = array();
			/// users - students
			if ($this->_export_students){
						$student_data = DbUlp::getAllStudentsFromUsersTable();
						if ($student_data){
								foreach ($student_data as $student_object){
										$users_xml_data[] = (array)$student_object;
								}
						}
						$student_metas = DbUlp::getUserMetaForGroupType('students');
						if ($student_metas){
								foreach ($student_metas as $student_object){
										$usermeta_xml_data[] = (array)$student_object;
								}
						}
			}
			/// users - instructors
			if ($this->_export_instructors){
						$instructor_data = DbUlp::getAllInstructorsUsersTable();
						if ($instructor_data){
								foreach ($instructor_data as $student_object){
										$users_xml_data[] = (array)$student_object;
								}
						}
						$instructor_metas = DbUlp::getUserMetaForGroupType('instructors');
						if ($instructor_metas){
								foreach ($instructor_metas as $student_object){
										$usermeta_xml_data[] = (array)$student_object;
								}
						}
			}
			/// write to xml - users
			if (!empty($users_xml_data)){
						$temp_entity['users'] = array('full_table_name' => $wpdb->users, 'table_name' => $wpdb->users);
						$this->array_to_xml(array('users' => $users_xml_data), $xml_data);
			}
			/// write to xml - usermeta
			if (!empty($usermeta_xml_data)){
						$temp_entity['usermeta'] = array('full_table_name' => $wpdb->usermeta, 'table_name' => $wpdb->usermeta);
						$this->array_to_xml(array('usermeta' => $usermeta_xml_data), $xml_data);
			}
			/// courses, questions, quizes, lesson
  		if ($this->_custom_post_types){
						/// posts
						/// make this only for ulp
						$posts = DbUlp::getAllCustomPostTypeItems();
						if ($posts){
								foreach ($posts as $post_object){
										$posts_xml_data[] = (array)$post_object;
								}
								$this->array_to_xml(array('posts' => $posts_xml_data), $xml_data);
						}
						/// postmeta
						$postmeta= DbUlp::getAllCustomPostTypeMetas();
						if ($postmeta){
								foreach ($postmeta as $post_meta){
										$postmeta_xml_data[] = (array)$post_meta;
								}
								$this->array_to_xml(array('postmeta' => $postmeta_xml_data), $xml_data);
						}
						$temp_entity['posts'] = array('full_table_name' => $wpdb->posts, 'table_name' => $wpdb->posts);
						$temp_entity['postmeta'] = array('full_table_name' => $wpdb->postmeta, 'table_name' => $wpdb->postmeta);
			}
			$this->array_to_xml(array('import_info'=>$temp_entity), $xml_data);
			$result = $xml_data->asXML($this->file);
			return TRUE;
	}
	/*
	 * @param array, object
	 * @return none
	 */
	protected function array_to_xml($data=array(), &$xml_data=null){
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




}
