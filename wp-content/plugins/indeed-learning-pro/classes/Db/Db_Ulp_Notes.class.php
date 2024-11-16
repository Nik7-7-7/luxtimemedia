<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('Db_Ulp_Notes')){
   return;
}
/*
id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
uid BIGINT(20) NOT NULL,
course_id BIGINT(20),
note_title VARCHAR (400),
note_content LONGTEXT,
obtained_date TIMESTAMP NOT NULL DEFAULT 0
*/
class Db_Ulp_Notes extends DbIndeedAbstract{
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
        $this->table = $wpdb->prefix . 'ulp_notes';
    }
    public function save($uid=0, $course_id=0, $title='', $content=''){
        global $wpdb;
        $insert_date = date('Y-m-d H:i:s', time() );
        $uid = sanitize_text_field($uid);
        $course_id = sanitize_text_field($course_id);
        $title = sanitize_textarea_field($title);
        $content = sanitize_textarea_field($content);
        $insert = $wpdb->prepare( "null, %d, %d, %s, %s, %s", $uid, $course_id, $title, $content, $insert_date );
        return $this->insert( $insert );
    }
    public function delete($id=0){
        global $wpdb;
        $id = sanitize_text_field( $id );
        $delete = $wpdb->prepare( " id=%d ", $id );
        return parent::delete( $delete );
    }
    public function selectAll($uid=0, $course_id=0, $limit=0, $offset=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $course_id = sanitize_text_field($course_id);
        $limit = sanitize_text_field($limit);
        $offset = sanitize_text_field($offset);
        $q = $wpdb->prepare( "SELECT a.id, a.note_title, a.note_content, a.obtained_date, b.post_title as course FROM {$wpdb->prefix}ulp_notes a
                INNER JOIN {$wpdb->posts} b ON a.course_id=b.ID
                WHERE
                uid=%d
        ", $uid );
        if ($course_id){
            $q .= $wpdb->prepare( " AND course_id=%d ", $course_id );
        }
        if ($limit){
            $q .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
        }
        return $wpdb->get_results($q);
    }
}
