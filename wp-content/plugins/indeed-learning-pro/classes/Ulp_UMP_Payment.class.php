<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_UMP_Payment')){
   return;
}
if (!class_exists('Indeed_Payment_Via_UMP')){
   require_once ULP_PATH . 'classes/Abstracts/Indeed_Payment_Via_UMP.class.php';
}
class Ulp_UMP_Payment extends Indeed_Payment_Via_UMP{
    private $unique_identificator_prefix = 'ump_';
    public function create_order($uid, $lid){
        $course_id = DbUlp::get_course_id_for_ump_level($lid);
        if ($course_id){
            $ulp_order = new UlpOrder();
            $payment_details = [
                'unique_identificator' => $this->unique_identificator_prefix . $uid . '_' . $lid,
                'amount' => $this->_get_amount_for_uid_lid($uid, $lid)
            ];
            $ulp_order->save($uid, $course_id, $payment_details);
        }
    }
    public function order_completed($uid, $lid){
      $unique_identificator = $this->unique_identificator_prefix . $uid . '_' . $lid;
      $ulp_order = new UlpOrder();
      $ulp_order->modify_status($unique_identificator, 'ulp_complete');
    }
    public function order_faild($uid, $lid){
      $unique_identificator = $this->unique_identificator_prefix . $uid . '_' . $lid;
      $ulp_order = new UlpOrder();
      $ulp_order->modify_status($unique_identificator, 'ulp_fail');
    }
    public function level_admin_html($level_data=array()){
        $data['courses'] = DbUlp::getAllCourses();
        $data['current_value'] = isset($level_data['ump_ulp_course']) ? $level_data['ump_ulp_course'] : -1;
        $view = new ViewUlp();
        $view->setTemplate(ULP_PATH . 'views/admin/ump_html_into_levels.php');
        $view->setContentData($data);
        echo esc_ulp_content($view->getOutput());
    }
    public function level_save($level_metas=array()){
        /// just adding an extra attr to levels
        $level_metas ['ump_ulp_course'] = '';
        return $level_metas;
    }
    private function _get_amount_for_uid_lid($uid=0, $lid=0){
        global $wpdb;
        $query = $wpdb->prepare( "SELECT amount_value FROM {$wpdb->prefix}ihc_orders WHERE lid=%d AND uid=%d ORDER BY id DESC LIMIT 1;", $lid, $uid );
        return $wpdb->get_var( $query );
    }
}
