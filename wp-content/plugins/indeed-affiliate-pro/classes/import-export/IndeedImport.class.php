<?php
if (!class_exists('IndeedImport')):
class IndeedImport{
	/*
	 * @var string
	 */
	protected $file = '';

	/*
	 * @var array
	 */
	protected $entities = array();

	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){}


	/*
	 * @param string
	 * @return none
	 */
	public function setFile($filename=''){
		if ($filename){
			$this->file = $filename;
		}
	}


	/*
	 * @param none
	 * @return none
	 */
	public function run(){
		$xml_object = simplexml_load_file($this->file);

		if (!empty($xml_object) && !empty($xml_object->import_info)){
			$this->entities = (array)$xml_object->import_info;
			if ($this->entities){
				foreach ($this->entities as $entity_name => $entity_opt){
					$this->do_import($entity_name, $entity_opt, $xml_object);
				}
			}
		}

		/// delete the file
		unlink($this->file);
	}


	/*
	 * @param string ($entity_name)
	 * @param string ($entity_opt)
	 * @param object ($xml_object)
	 * @return none
	 */
	protected function do_import($entity_name, $entity_opt, &$xml_object){
		global $wpdb, $indeed_db;
		//echo esc_uap_content($entity_name),'<br/>';
		switch ($entity_name){
			case 'users':
				foreach ($xml_object->$entity_name->children() as $object_key=>$object){
					$user_data = (array)$object;
					$user = get_user_by('ID', $user_data['ID']);
					if ($user){
						continue;
					}
					$user = get_user_by('user_login', $user_data['user_login']);
					if ($user){
						continue;
					}
					$user = get_user_by('user_email', $user_data['user_email']);
					if ($user){
						continue;
					}
					if ( !isset($user_data['role']) || $user_data['role'] === '' ){
						$user_data['role'] = get_option('uap_register_new_user_role');
					}
					if ( !isset($user_data['role']) || $user_data['role'] === '' ){
						$user_data['role'] = get_option('default_role');
					}
					$indeed_db->custom_insert_user_with_ID($user_data);
					// set a role
					//$u = new \WP_User( $user_data['ID'] );
					//$u->add_role( $user_data['role'] );
				}
				break;
			case 'options':
					foreach ($xml_object->$entity_name->children() as $meta_name=>$meta_value){
						$meta_value = (string)$meta_value;
						$temp_array = @unserialize($meta_value);
						if ($temp_array!==FALSE){
							$meta_value = $temp_array;
						}
						update_option($meta_name, $meta_value);
					}
				break;
			case 'usermeta':
					foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$array = (array)$object;
						if ( count( $array ) === 0 || !isset( $array['user_id'] ) || !isset( $array['meta_key'] ) || !isset( $array['meta_value'] ) || (string)$array['meta_value'] === '' ){
							continue;
						}
						if ( !$indeed_db->does_usermeta_exists( $array['user_id'], $array['meta_key'] ) ){
							/// post meta does not exists
							$indeed_db->custom_insert_usermeta( $array['user_id'], $array['meta_key'], (string)$array['meta_value'] );
						}
					}
				break;
			case 'indeed_wp_capabilities':
					global $wpdb;
					$meta_key = $wpdb->get_blog_prefix() . 'capabilities';
					foreach ($xml_object->$entity_name->children() as $object){
						$array = (array)$object;
						if ( count( $array ) === 0 || !isset( $array['user_id'] ) || !isset( $array['meta_value'] ) || (string)$array['meta_value'] === '' ){
							continue;
						}
						$indeed_db->custom_insert_usermeta( $array['user_id'], $meta_key, (string)$array['meta_value'] );
					}
				break;
			default:
					/// indeed custom tables here
					$this->do_import_custom_table($entity_name, $entity_opt, $xml_object);
				break;
		}
	}


	/*
	 * @param string ($entity_name)
	 * @param string ($entity_opt)
	 * @param object ($xml_object)
	 * @return none
	 */
	protected function do_import_custom_table($entity_name, $entity_opt, &$xml_object){}
}
endif;
