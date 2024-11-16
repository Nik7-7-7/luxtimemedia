<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbModulesMeta')){
   return;
}
class DbModulesMeta extends DbIndeedAbstract{
	/**
	 * @var string
	 */
	protected $table = '';

	/**
	 * @param none
	 * @return none
	 */
	public function __construct()
  {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_courses_modules_metas';
	}

	/**
	 * @param int (module id)
	 * @param string (meta name)
	 * @return mixed
	 */
	public function getModuleMeta($module_id=0, $meta_key='')
  {
      global $wpdb;
  		$module_id = sanitize_text_field($module_id);
  		$meta_key = sanitize_text_field($meta_key);
      $where = $wpdb->prepare( " module_id=%d AND meta_key=%s ", $module_id, $meta_key );
  		return $this->getVar('meta_value', $where );
	}

	/**
	 * @param int (module id)
	 * @param string (module meta key)
	 * @param mixed (module meta value)
	 * @return int (meta id)
	 */
	public function saveModuleMeta($module_id=0, $meta_key='', $meta_value='')
  {
  		global $wpdb;
  		$module_id = sanitize_text_field($module_id);
  		$meta_key = sanitize_text_field($meta_key);
  		$meta_value = sanitize_text_field($meta_value);
  		if ($this->getModuleMeta($module_id, $meta_key)===null){
        $insert = $wpdb->prepare( "null, %d, %s, %s ", $module_id, $meta_key, $meta_value );
  			return $this->insert( $insert );
  		} else {
        $update = $wpdb->prepare( " meta_value=%s ", $meta_value );
        $where = $wpdb->prepare( " module_id=%d AND meta_key=%s ", $module_id, $meta_key );
  			$this->update( $update, $where );
        $where = $wpdb->prepare( " module_id=%d AND meta_key=%s ", $module_id, $meta_key );
  			return $this->getVar('id', $where );
  		}
	}

	/**
	 * @param int (module id)
	 * @param string (meta key)
	 * @return bool
	 */
	public function deleteModuleMeta($module_id=0, $meta_key='')
  {
      global $wpdb;
  		$module_id = sanitize_text_field($module_id);
  		$meta_key = sanitize_text_field($meta_key);
      $delete = $wpdb->prepare( " module_id=%d AND meta_key=%s ", $module_id, $meta_key );
  		return $this->delete( $delete );
	}

	/**
	 * @param int (module id)
	 * @return bool
	 */
	public function deleteAllModuleMetas($module_id=0)
  {
      global $wpdb;
  		$module_id = sanitize_text_field($module_id);
      $delete = $wpdb->prepare( " module_id=%d ", $module_id );
  		return $this->delete( $delete );
	}

	/**
	 * @param int (module id)
	 * @return array ( ... key=>value pairs )
	 */
	public function getAllModuleMetas($module_id)
  {
      global $wpdb;
		  $module_id = sanitize_text_field($module_id);
      $where = $wpdb->prepare( " module_id=%d ", $module_id )
  		return $this->getResults( 'meta_key, meta_value', $where );
	}

}
