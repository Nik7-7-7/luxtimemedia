<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('Db_Ulp_Dashboard_Notifications')){
   return;
}
/*
type VARCHAR(40) NOT NULL,
value INT(11) DEFAULT 0
*/
class Db_Ulp_Dashboard_Notifications extends DbIndeedAbstract{
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
        $this->table = $wpdb->prefix . 'ulp_dashboard_notifications';
    }
    public function save($type='', $value=''){
        global $wpdb;
        $type = sanitize_text_field($type);
        $value = sanitize_textarea_field($value);
        if ( $this->get($type)==null || $this->get($type) === false ){
            $insert = $wpdb->prepare( " %s, %d ", $type, $value );
            return parent::insert( $insert );
        }
        $update = $wpdb->prepare( " value=%d ", $value );
        $where = $wpdb->prepare( " type=%s ", $type );
        return parent::update( $update, $where );
    }
    public function delete($type=''){
        global $wpdb;
        $type = sanitize_text_field($type);
        $delete = $wpdb->prepare( " type=%s ", $type );
        return parent::delete( $delete );
    }
    public function get($type=''){
        global $wpdb;
        $type = sanitize_text_field($type);
        $where = $wpdb->prepare( " type=%s ", $type );
        $num = parent::getVar( 'value', $where );
        if ($num===FALSE || $num===null){
            $num = 0;
        }
        return $num;
    }
}
