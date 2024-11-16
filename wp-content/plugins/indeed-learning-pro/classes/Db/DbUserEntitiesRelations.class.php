<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbUserEntitiesRelations')){
   return;
}
class DbUserEntitiesRelations extends DbIndeedAbstract{
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
		$this->table = $wpdb->prefix . 'ulp_user_entities_relations';
	}
	/**
	 * @param int (user id)
	 * @param int (entity id)
	 * @return mixed
	 */
	public function getRelationId($uid=0, $entity_id=0){
    global $wpdb;
		$uid = sanitize_text_field($uid);
		$entity_id = sanitize_text_field($entity_id);
    $where = $wpdb->prepare( " user_id=%d AND entity_id=%d ", $uid, $entity_id );
		return $this->getVar( 'id', $where );
	}
	/**
	 * @param int
	 * @param int
	 * @param int
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	public function saveRelation($uid=0, $entity_id=0, $entity_type=0, $start_time='', $end_time='', $status=0){
		global $wpdb;
		$uid = sanitize_text_field($uid);
		$entity_id = sanitize_text_field($entity_id);
		$entity_type = sanitize_text_field($entity_type);
		$start_time = sanitize_text_field($start_time);
		$end_time = sanitize_text_field($end_time);
		$status = sanitize_text_field($status);
		if ($id = $this->getRelationId($uid, $entity_id)){
			/// update
			$q = $wpdb->prepare( "entity_type=%s, status=%d ", $entity_type, $status );
			if ($start_time!=''){
				$q .= $wpdb->prepare( ", start_time=%s ", $start_time );
			}
			if ($end_time!=''){
				$q .= $wpdb->prepare( ", end_time=%s ", $end_time );
			}
      $where = $wpdb->prepare( " id=%d ", $id );
			$this->update( $q, $where );
			return $id;
		} else {
			/// save
			return $this->do_Insert($uid, $entity_id, $entity_type, $start_time, $end_time, $status);
		}
	}
	/**
	 * @param int
	 * @param int
	 * @param int
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	public function do_Insert($uid=0, $entity_id=0, $entity_type=0, $start_time='', $end_time='', $status=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$entity_id = sanitize_text_field($entity_id);
			$entity_type = sanitize_text_field($entity_type);
			$start_time = sanitize_text_field($start_time);
			$end_time = sanitize_text_field($end_time);
			$status = sanitize_text_field($status);
      $query = $wpdb->prepare("null, %d, %d, %s, %s, %s, %d ",
                                $uid, $entity_id, $entity_type, $start_time, $end_time, $status
      );
			$this->insert( $query );
			return $wpdb->insert_id;
	}
	/**
	 * @param int (user id)
	 * @param int (entity id)
	 * @return bool
	 */
	public function deleteRelation($uid=0, $entity_id=0){
    global $wpdb;
		$uid = sanitize_text_field($uid);
		$entity_id = sanitize_text_field($entity_id);
		$relaton_id = $this->getRelationId($uid,$entity_id);
		if ( $relaton_id === null ){
				return false;
		}

		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
		$DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();

		$DbUserEntitiesRelationMetas->deleteAllRelation($relaton_id);

    $query = $wpdb->prepare( "user_id=%d AND entity_id=%d", $uid, $entity_id );
		return $this->delete( $query );
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @return bool
	 */
	public function userCanSeeCourse($uid=0, $course_id=0){
		/// do not forget to add the status
    global $wpdb;
		$returnValue = false;
		$uid = sanitize_text_field($uid);
		$course_id = sanitize_text_field($course_id);
    $where = $wpdb->prepare( "user_id=%d AND entity_id=%d AND entity_type='ulp_course' ORDER BY id DESC LIMIT 1 ", $uid, $course_id );
		$temp = $this->getRow('start_time, end_time', $where ); ///  AND status=1

		if ($temp){
			$now = time();
			if (strtotime($temp['start_time'])<$now && strtotime($temp['end_time'])>$now){
				$returnValue = true;
			}
		}
		$returnValue = apply_filters('ulp_filter_does_user_can_access_course', $returnValue, $uid, $course_id);
		return $returnValue;
	}
	/**
	 * This will select the last relation on the table to see if user can access the course
	 * @param int (user id)
	 * @param int (course id)
	 * @return bool
	 */
	public function isUserEnrolledOnCourse($uid=0, $course_id=0){
    global $wpdb;
		$uid = sanitize_text_field($uid);
		$course_id = sanitize_text_field($course_id);
    $where = $wpdb->prepare( "user_id=%d AND entity_id=%d AND entity_type='ulp_course' ORDER BY id DESC LIMIT 1", $uid, $course_id );
		$temp = $this->getVar('id', $where ); ///  AND status=1
		if ($temp){
			if ($this->userCanSeeCourse($uid, $course_id)){
         return TRUE;
      }
		}
		return FALSE;
	}
	public function how_many_times_user_enroll_course($uid=0, $course_id=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$course_id = sanitize_text_field($course_id);
			$q = $wpdb->prepare( "SELECT IFNULL(COUNT(id), 0) as v FROM {$wpdb->prefix}ulp_user_entities_relations
			 					WHERE user_id=%d
								AND entity_id=%d
								AND entity_type='ulp_course'
			", $uid, $course_id );
			return $wpdb->get_var($q);
	}
	public function get_enroll_date($uid=0, $cid=0){
			global $wpdb;
			$uid = sanitize_text_field($uid);
			$cid = sanitize_text_field($cid);
			$q = $wpdb->prepare( "SELECT start_time FROM  {$wpdb->prefix}ulp_user_entities_relations WHERE user_id=%d AND entity_id=%d;", $uid, $cid );
			return $wpdb->get_var($q);
	}
	/**
	 * @param int (user id)
	 * @param int (entity)
	 * @param string (what to select)
	 * @return value
	 */
	public function getRelationColValue($uid=0, $entity_id=0, $col=''){
			global $wpdb;
  		$uid = sanitize_text_field($uid);
  		$entity_id = sanitize_text_field($entity_id);
  		$col = sanitize_text_field($col);
      $where = $wpdb->prepare( " user_id=%d AND entity_id=%d ", $uid, $entity_id );
  		return $this->getVar("$col", $where );
	}
	/**
	 * @param int (course id)
	 * @return int
	 */
	public function getCountUsersForCourse($course_id=0){
			global $wpdb;
  		$course_id = sanitize_text_field($course_id);
      $where = $wpdb->prepare( " entity_id=%s AND entity_type='ulp_course' ", $course_id );
  		return $this->getVar( 'COUNT(DISTINCT user_id)', $where );
	}
	/**
	 * @param int
	 * @return bool
	 */
	public function is_user_student($uid=0){
      global $wpdb;
  		$uid = sanitize_text_field($uid);
      $where = $wpdb->prepare( " user_id=%d AND entity_type='ulp_course' ", $uid );
  		return $this->getVar( 'id', $where );
	}
	/**
	 * @param int
	 * @return array
	 */
	public function get_user_courses($uid=0){
    global $wpdb;
		$array = array();
		$uid = sanitize_text_field( $uid );
    $where = $wpdb->prepare( " user_id=%d AND entity_type='ulp_course' ", $uid );
		$temp_array = $this->getResults('entity_id', $where );
		if ($temp_array){
			foreach ($temp_array as $temp){
				$array[] = $temp['entity_id'];
			}
		}
		return $array;
	}

	public function deleteAllEntriesByEntity( $entityId=0 )
	{
			global $wpdb;
			if ( !$entityId ){
					return false;
			}
			$query = $wpdb->prepare( "DELETE FROM {$this->table} WHERE entity_id=%d", $entityId );
			return $wpdb->query( $query );
	}

}
