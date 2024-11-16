<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbCoursesModulesUlp')){
   return;
}
class DbCoursesModulesUlp extends DbIndeedAbstract{
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
		$this->table = $wpdb->prefix . 'ulp_courses_modules';
	}
	/**
	 * @param int (module id)
	 * @param string (module name)
	 * @param int (course id)
	 * @param int (order)
	 * @param int (tinyint status 1 or 0)
	 * @return int (module id)
	 */
	public function saveModule($module_id=0, $module_name='', $course_id=0, $module_order=0, $status=0){
		global $wpdb;
		$module_id = sanitize_text_field($module_id);
		$module_name = sanitize_text_field($module_name);
		$course_id = sanitize_text_field($course_id);
		$module_order = sanitize_text_field($module_order);
		$status = sanitize_text_field($status);
		if ($this->getModule($module_id)){
      $update = $wpdb->prepare( " course_id=%d, module_name=%s, module_order=%d, status=%d ", $course_id, $module_name, $module_order, $status );
      $where = $wpdb->prepare( " module_id=%d ", $module_id );
			$this->update( $update, $where );
			do_action( 'ulp_course_modules_update', $module_id );
			return $module_id;
		} else {
      $insert = $wpdb->prepare( " null, %s, %d, %d, %d ",  $module_name, $course_id, $module_order, $status );
			$module_id = $this->insert( $insert );
			do_action( 'ulp_course_modules_save', $module_id );
			return $module_id;
		}
	}
	/**
	 * @param int
	 * @return bool
	 */
	public function deleteModule($module_id=0){
    global $wpdb;
		$module_id = sanitize_text_field($module_id);
    $delete = $wpdb->prepare( "module_id=%d ", $module_id );
		return $this->delete( $delete );
	}
	/**
	 * @param int (module id)
	 * @return array
	 */
	public function getModule($module_id=0){
    global $wpdb;
		$module_id = sanitize_text_field($module_id);
    $where = $wpdb->prepare( " module_id=%d ", $module_id );
		return $this->getRow('course_id, module_name, module_order, status', $where );
	}
	/**
	 * @param int (course id)
	 * @return array
	 */
	public function getAllModulesForCourse($course_id=0, $limit=0, $offset=0){
    global $wpdb;
		$course_id = sanitize_text_field($course_id);
		$limit = sanitize_text_field($limit);
		$offset = sanitize_text_field($offset);
		$what_to_get = $wpdb->prepare( " course_id=%d ", $course_id );
		$what_to_get .= " ORDER BY module_order ASC ";
		if ($limit){
			$what_to_get .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
		}
		return $this->getResults("module_id, module_name, module_order, status", $what_to_get);

	}
	/**
	 * @param int
	 * @return int
	 */
	public function countModules($course_id=0){
      global $wpdb;
			$course_id = sanitize_text_field($course_id);
      $where = $wpdb->prepare( " course_id=%d ", $course_id );
			return $this->getVar("COUNT(module_id)", $where );
	}
	/**
	 * @param int
	 * @return int
	 */
	public function getLastModuleForCourse($course_id=0){
		///dynamic order???
    global $wpdb;
		$course_id = sanitize_text_field($course_id);
    $where = $wpdb->prepare( " course_id=%d ORDER BY module_order DESC LIMIT 1 ", $course_id );
		return $this->getVar( "module_id", $where ); ///or quiz
	}
}
