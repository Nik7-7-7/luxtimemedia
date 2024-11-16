<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ihc_Actions_On_Events')){
   return;
}

class Ihc_Actions_On_Events{

    public function __construct(){
        add_action('ihc_action_before_delete_order', array($this, 'do_action_before_delete_order'), 1, 1);
        add_action('ump_coupon_code_submited', array($this, 'do_action_on_coupon_code_submit'), 1, 3);
    }

    public function do_action_before_delete_order($order_id=0){
        /// if the user has used for this order a coupon, let's decrement it.
        if (empty($order_id)){
           return TRUE;
        }
        /// let's search for coupon_used
        require_once IHC_PATH . 'classes/Orders.class.php';
        $Orders = new Ump\Orders();
        $coupon = $Orders->get_meta_by_order_and_name($order_id, 'coupon_used');
        if ($coupon){
            Ihc_Db::decrement_coupon($coupon);
        }
        return TRUE;
    }

    public function do_action_on_coupon_code_submit($coupon='', $uid=0, $lid=0){
        if ($coupon && $uid && $lid){
            require_once IHC_PATH . 'classes/Ihc_User_Logs.class.php';
            $log = esc_html__('User has used the following coupon: ', 'ihc') . $coupon . esc_html__(' for acquire level: ', 'ihc') . Ihc_Db::get_level_name_by_lid($lid) . '.';
            Ihc_User_Logs::set_user_id($uid);
            Ihc_User_logs::set_level_id($lid);
            Ihc_User_Logs::write_log($log, 'user_logs');
        }
    }

}
