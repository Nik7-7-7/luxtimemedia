<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbUserEntitiesRelationMetas')){
   return;
}
class DbUserEntitiesRelationMetas extends DbIndeedAbstract{
	/**
	 * @var string
	 */
	protected $table = '';
	/**
	 * @param none
	 * @reutrn none
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_user_entities_relations_metas';
	}
	/**
	 * @param int
	 * @param string
	 * @return mixed
	 */
	public function getMeta($user_entity_relation_id=0, $meta_key=''){
    global $wpdb;
		$user_entity_relation_id = sanitize_text_field($user_entity_relation_id);
		$meta_key = sanitize_text_field($meta_key);
		if (empty($user_entity_relation_id)){
       return FALSE;
    }
    $where = $wpdb->prepare( " user_entity_relation_id=%d AND meta_key=%s ", $user_entity_relation_id, $meta_key );
		$data = $this->getVar('meta_value', $where );
		return $data;
	}
	/**
	 * @param int
	 * @param string
	 * @param string
	 * @return bool
	 */
	public function saveMeta($user_entity_relation_id=0, $meta_key='', $meta_value=null){
		global $wpdb;
		$user_entity_relation_id = sanitize_text_field($user_entity_relation_id);
		$meta_key = sanitize_text_field($meta_key);
		$meta_value = sanitize_textarea_field($meta_value);

		if ($this->getMeta($user_entity_relation_id, $meta_key)!==null){
			/// update
      $update = $wpdb->prepare( " meta_value=%s ", $meta_value );
      $where = $wpdb->prepare( " user_entity_relation_id=%d AND meta_key=%s ", $user_entity_relation_id, $meta_key );
			return $this->update( $update, $where );
		} else {
			/// create
      $insert = $wpdb->prepare( "null, %d, %s, %s ", $user_entity_relation_id, $meta_key, $meta_value );
			return $this->insert( $insert );
		}
	}
	/**
	 * @param int (module id)
	 * @param int (item id)
	 * @return bool
	 */
	public function deleteItem($user_entity_relation_id=0, $meta_key=''){
    global $wpdb;
		$user_entity_relation_id = sanitize_text_field($user_entity_relation_id);
		$meta_key = sanitize_text_field($meta_key);
    $delete = $wpdb->prepare( " user_entity_relation_id=%d AND meta_key=%s ", $user_entity_relation_id, $meta_key );
		return $this->delete( $delete );
	}

	public function deleteAllRelation($user_entity_relation_id=0){
    global $wpdb;
		$user_entity_relation_id = sanitize_text_field($user_entity_relation_id);
    $delete = $wpdb->prepare( " user_entity_relation_id=%d ", $user_entity_relation_id );
		return $this->delete( $delete );
	}
}
