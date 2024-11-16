<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('Db_Ulp_Student_Badges')){
   return;
}
class Db_Ulp_Student_Badges extends DbIndeedAbstract{
/*
uid BIGINT(20) NOT NULL,
badge_id BIGINT(20),
obtained_date TIMESTAMP NOT NULL DEFAULT 0
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
    		$this->table = $wpdb->prefix . 'ulp_student_badges';
  	}
    public function save($uid=0, $badge_id=0){
        global $wpdb;
        $create_date = date('Y-m-d H:i:s', time() );
        $uid = sanitize_text_field($uid);
        $badge_id = sanitize_text_field($badge_id);
        $create_date = sanitize_text_field($create_date);
        $insert = $wpdb->prepare( " null, %d, %d, %s ", $uid, $badge_id, $create_date );
        return parent::insert( $insert );
    }
    public function delete($uid=0, $badge_id=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $badge_id = sanitize_text_field($badge_id);
        $delete = $wpdb->prepare( " uid=%d AND badge_id=%d ", $uid, $badge_id );
        return parent::delete( $delete );
    }
    public function getAllForUser($uid=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $q = $wpdb->prepare( "SELECT a.badge_title, a.badge_content, a.badge_image, a.id
                                  FROM {$wpdb->prefix}ulp_badges a
                                  INNER JOIN {$wpdb->prefix}ulp_student_badges b ON  a.id=b.badge_id
                                  WHERE 1=1
                                  AND b.uid=%d
        ", $uid );
        return $wpdb->get_results($q);
    }
}
