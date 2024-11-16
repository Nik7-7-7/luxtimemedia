<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbActivityUlp')){
   return;
}
class DbActivityUlp extends DbIndeedAbstract{
	/*
	 	============ WP_ULP_ACTIVITY ==============
		id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		uid INT(11) NOT NULL,
		entity_id INT(11) DEFAULT 0,
		entity_type VARCHAR(200),
		action VARCHAR(200),
		description TEXT,
		event_time TIMESTAMP NOT NULL DEFAULT 0,
		status TINYINT(1)
	 */
	/**
	 * @var string
	 */
	protected $table = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_activity';
	}
	/**
	 * @param int (user id)
	 * @param int (entity id)
	 * @param string (action)
	 * @return mixed
	 */
	public function getItem($uid=0, $entity_id=0, $action=''){
		  global $wpdb;
			$uid = sanitize_text_field($uid);
			$entity_id = sanitize_text_field($entity_id);
			$action = sanitize_text_field($action);
      $select = $wpdb->prepare( " uid=%d AND entity_id=%s AND action=%s ", $uid, $entity_id, $action );
			return $this->getVar( 'id', $select );
	}
	public function getAllByUid($uid=0, $limit=0, $offset=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$q = $wpdb->prepare( "SELECT `id`,`uid`,`entity_id`,`entity_type`,`action`,`description`,`event_time`,`status` FROM {$wpdb->prefix}ulp_activity
							WHERE 1=1
							AND uid=%d
							ORDER BY id DESC
			", $uid );
			if ($limit){
					$limit = sanitize_text_field($limit);
					$offset = sanitize_text_field($offset);
					$q .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset );
			}
			return $wpdb->get_results($q);
	}
	public function getCountAll($uid=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$q = $wpdb->prepare( "SELECT COUNT(id) as v FROM {$wpdb->prefix}ulp_activity
							WHERE 1=1
							AND uid=%d ", $uid );
			return $wpdb->get_var($q);
	}
	/**
	 * @param int (user id)
	 * @param int (entity id)
	 * @param string (entity type)
	 * @param string (action)
	 * @param string (description)
	 * @param int (status)
	 * @return int
	 */
	public function saveItem($uid=0, $entity_id=0, $entity_type='', $action='', $description='', $time='', $status=1){
		global $wpdb;
		$uid = sanitize_text_field($uid);
		$entity_id = sanitize_text_field($entity_id);
		$entity_type = sanitize_text_field($entity_type);
		$action = sanitize_text_field($action);
		$description = sanitize_textarea_field($description);
		$time = sanitize_text_field($time);
		$status = sanitize_text_field($status);
		if ($id=$this->getItem($uid, $entity_id, $action)){
			/// update
      $update = $wpdb->prepare( "uid=%d, entity_id=%d, entity_type=%s, action=%s, description=%s, event_time=%s, status=%s ",
                                    $uid, $entity_id, $entity_type, $action, $description, $time, $status
      );
      $where = $wpdb->prepare( " id=%d ", $id );
			$this->update( $update, $where );
			return $id;
		} else {
			/// create
      $insert = $wpdb->prepare( "null, %d, %d, %s, %s, %s, %s, %d ",
                                          $uid, $entity_id, $entity_type, $action, $description, $time, $status
      );
			return $this->insert( $insert );
		}
	}
	/**
	 * @param int (user id)
	 * @param int (entity id)
	 * @param string (action type)
	 * @return string (date)
	 */
	public function getItemTime($uid=0, $entity_id=0, $action=''){
    global $wpdb;
		$uid = sanitize_text_field($uid);
		$entity_id = sanitize_text_field($entity_id);
		$action = sanitize_text_field($action);
    $where = $wpdb->prepare( " uid=%d AND entity_id=%s AND action=%s ", $uid, $entity_id, $action );
		return $this->getVar( 'event_time', $where );
	}
	/**
	 * @param int
	 * @param int
	 * @return boolean
	 */
	public function is_lesson_completed($uid=0, $lesson_id=0){
    global $wpdb;
		if ($uid && $lesson_id){
			$uid = sanitize_text_field($uid);
			$lesson_id = sanitize_text_field($lesson_id);
      $where = $wpdb->prepare( " uid=%d AND entity_id=%d AND action='complete_lesson' ", $uid, $lesson_id );
			$data = $this->getVar( 'id', $where );
			if ($data){
				return TRUE;
			}
		}
		return FALSE;
	}
	public function delete($id=0){
      global $wpdb;
			$id = sanitize_text_field($id);
      $delete = $wpdb->prepare( " id=%d ", $id );
			return parent::delete( $delete );
	}
}
