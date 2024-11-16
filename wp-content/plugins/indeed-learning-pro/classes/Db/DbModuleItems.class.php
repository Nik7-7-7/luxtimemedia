<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbModuleItems')){
   return;
}
class DbModuleItems extends DbIndeedAbstract{
	/**
	 * @var string
	 */
	protected $table = '';
	/*
	protected static $all_quizes = [];
	protected static $all_lessons = [];
	*/
	protected static $all_items = array(); /// will contain all lessons and quizes
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_course_modules_items';
	}
	/**
	 * @param int (module id)
	 * @param string (meta name)
	 * @return mixed
	 */
	public function getItem($module_id=0, $item_id=0){
    global $wpdb;
		$module_id = sanitize_text_field($module_id);
		$item_id = sanitize_text_field($item_id);
    $where = $wpdb->prepare( " module_id=%d AND item_id=%s ", $module_id, $item_id );
		return $this->getVar('id', $where );
	}
	/**
	 * @param int (module)
	 * @param int (item id)
	 * @param string (course or quiz)
	 * @param int (the order)
	 * @param int (status 1 or 0)
	 * @return int
	 */
	public function saveItem($module_id=0, $course_id=0, $item_id=0, $item_type='', $item_order=0, $status=0){
		global $wpdb;
		$module_id = sanitize_text_field($module_id);
		$course_id = sanitize_text_field($course_id);
		$item_id = sanitize_text_field($item_id);
		$item_type = sanitize_text_field($item_type);
		$item_order = sanitize_text_field($item_order);
		$status = sanitize_text_field($status);
		if ($id=$this->getItem($module_id, $item_id)){
			/// update
      $update = $wpdb->prepare( " module_id=%d, course_id=%s, item_id=%d, item_type=%s, item_order=%d, status=%d ",
                                $module_id, $course_id, $item_id, $item_type, $item_order, $status
      );
      $where = $wpdb->prepare( " id=%d ", $id );
			$this->update( $update, $where );
			return $id;
		} else {
			/// create
      $insert = $wpdb->prepare( " null, %d, %d, %d, %s, %d, %d ",
                                  $module_id, $course_id, $item_id, $item_type, $item_order, $status
      );
			return $this->insert( $insert );
		}
	}
	/**
	 * @param int (module id)
	 * @param int (item id)
	 * @return bool
	 */
	public function deleteItem($module_id=0, $item_id=0){
    global $wpdb;
		$module_id = sanitize_text_field($module_id);
		$item_id = sanitize_text_field($item_id);
    $delete = $wpdb->prepare( " module_id=%d AND item_id=%d ", $module_id, $item_id );
		return $this->delete( $delete );
	}
	public function updateOrder($module_id=0, $item_id=0, $order=0){
			global $wpdb;
			$module_id = sanitize_text_field($module_id);
			$item_id = sanitize_text_field($item_id);
			$entry_id = $this->getItem($module_id, $item_id);
			if ($entry_id){
					$order = sanitize_text_field($order);
          $query = $wpdb->prepare( "UPDATE {$this->table} SET item_order=%d WHERE id=%d ;", $order, $entry_id );
					return $wpdb->query( $query );
			}
	}
	/**
	 * Return an array with all items for a module
	 * @param int (module id)
	 * @param string
	 * @return array
	 */
	public function getAllModuleItemsByModuleId($module_id=0, $type='', $onlyPublish=false){
			global $wpdb;
			$module_id = sanitize_text_field($module_id);
			$q = $wpdb->prepare( "SELECT a.item_id FROM
								{$this->table} a
								INNER JOIN {$wpdb->posts} b
								ON a.item_id=b.ID
								WHERE module_id=%d ", $module_id );
			if ($type){
				$type = sanitize_text_field($type);
				$q .= $wpdb->prepare(" AND item_type=%s ", $type );
			}
			if ($onlyPublish){
					$q .= " AND b.post_status='publish' ";
			}
			$q .= " ORDER BY item_order ASC ";
			$data = $wpdb->get_results($q);
			return $data ? indeed_convert_to_array($data) : array();
	}
	/**
	 * @param int (module id)
	 * @return bool
	 */
	public function deleteAllModuleItemsByModuleId($module_id=0){
    global $wpdb;
		$module_id = sanitize_text_field($module_id);
    $delete = $wpdb->prepare( " module_id=%d ", $module_id );
		return $this->delete( $delete );
	}
	/**
	 * @param int (module id)
	 * @param string
	 * @return int
	 */
	public function getCountModuleItems($module_id=0, $type='', $onlyPublish=false){
    global $wpdb;
		$module_id = sanitize_text_field($module_id);
		$type = sanitize_text_field($type);
		$q = $wpdb->prepare("SELECT COUNT(a.id) FROM {$this->table} a
              							INNER JOIN {$wpdb->posts} b
              							ON a.item_id=b.ID
              							WHERE
              							a.module_id=$module_id
              							AND
              							a.item_type=%s
		", $type );
		if ($onlyPublish){
				$q .= " AND b.post_status='publish' ";
		}
		return $wpdb->get_var($q);
	}
	/**
	 * @param int
	 * @return int
	 */
	public function getLastQuizForCourse($module_id=0){
    global $wpdb;
		$module_id = sanitize_text_field( $module_id );
    $where = $wpdb->prepare( " module_id=%d AND item_type='ulp_quiz' ORDER BY item_order DESC LIMIT 1 ", $module_id );
		return $this->getVar( "item_id", $where ); ///or quiz
	}

	public function getAllItems(){
			if (empty(self::$all_items)){
					self::$all_items = DbUlp::getAllQuizesAndLessons();
			}
			return self::$all_items;
	}

}
