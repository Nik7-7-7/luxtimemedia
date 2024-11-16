<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('Db_User_Certificates')){
   return;
}
class Db_User_Certificates extends DbIndeedAbstract{
/*
uid BIGINT(20) NOT NULL,
course_id BIGINT(20),
certificate_id BIGINT(20) NOT NULL,
grade VARCHAR(10),
details TEXT,
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
  		$this->table = $wpdb->prefix . 'ulp_student_certificate';
  	}
    public function getAllCertificatesForUser($uid=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $q = $wpdb->prepare("
              SELECT  a.id,
                      a.uid,
                      a.course_id,
                      a.certificate_id,
                      a.grade,
                      a.details,
                      a.obtained_date,
                      b.post_title as certificate_title,
                      c.post_title as course_name
                  FROM
                  {$wpdb->prefix}ulp_student_certificate a
                  INNER JOIN {$wpdb->posts} b
                  ON a.certificate_id=b.ID
                  INNER JOIN {$wpdb->posts} c
                  ON a.course_id=c.ID
                  WHERE
                  a.uid=%d ;
        ", $uid );
        return $wpdb->get_results($q);
    }
    public function userHasCertificateForCourse($uid=0, $course_id=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $course_id = sanitize_text_field($course_id);
        $where = $wpdb->prepare( " uid=%d AND course_id=%d ", $uid, $course_id );
        $data = $this->getRow("`id`, `uid`, `course_id`, `certificate_id`, `grade`, `details`, `obtained_date`", $where );
        if ($data==null){
          return false;
        }
        return $data;
    }
    public function addCertificateForUser($uid=0, $course_id=0, $certificate_id=0, $grade='', $details=''){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $course_id = sanitize_text_field($course_id);
        $certificate_id = sanitize_text_field($certificate_id);
        $grade = sanitize_text_field($grade);
        $details = sanitize_textarea_field($details);
        $obtained_date = date('Y-m-d H:i:s', time() );

        $already_exists = $this->userHasCertificateForCourse($uid, $course_id);
        if ($already_exists && isset( $already_exists['certificate_id'] ) && (int)$already_exists['certificate_id'] == (int)$certificate_id){
            // update
            $update = $wpdb->prepare( " grade=%s, details=%s, obtained_date=%s ", $grade, $details, $obtained_date );
            $where = $wpdb->prepare( " id=%d ", $already_exists['id'] );
            $this->update( $update, $where );
            return do_action('ulp_user_receive_certificate', $uid, $course_id, $certificate_id, $grade);
        }

        $insert = $wpdb->prepare( "null, %d, %d, %d, %s, %s, %s ", $uid, $course_id, $certificate_id, $grade, $details, $obtained_date );
        $this->insert( $insert );
        do_action('ulp_user_receive_certificate', $uid, $course_id, $certificate_id, $grade);
    }
    public function getCertificateById($id=0){
        global $wpdb;
        $id = sanitize_text_field($id);
        $q = $wpdb->prepare( "SELECT course_id, certificate_id, uid, grade, details, obtained_date FROM {$wpdb->prefix}ulp_student_certificate WHERE id=%d ", $id );
        $data = $wpdb->get_row($q);
        return (array)$data;
    }

	public function delete($uid=0, $certificate_id=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $certificate_id = sanitize_text_field( $certificate_id );
        $delete = $wpdb->prepare( " uid=%d AND certificate_id=%d ", $uid, $certificate_id );
        return parent::delete( $delete );
    }
}
