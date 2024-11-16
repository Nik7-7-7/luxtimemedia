<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('Indeed_Payment_Via_UMP')){
	 return;
}
abstract class Indeed_Payment_Via_UMP{
	 public function __construct(){
       add_action('ihc_level_admin_html', array($this, 'level_admin_html'), 10, 1);
       add_filter('ihc_save_level_meta_names_filter', array($this, 'level_save'), 10, 1);
       add_action('ihc_new_subscription_action', array($this, 'create_order'), 10, 2);
       add_action('ihc_action_after_subscription_activated', array($this, 'order_completed'), 10, 2);
       add_action('ihc_action_after_subscription_delete', array($this, 'order_faild'), 10, 2);
       add_action('ihc_action_after_cancel_subscription', array($this, 'order_faild'), 10, 2);
   }
   abstract public function create_order($uid, $lid);
   abstract public function order_completed($uid, $lid);
   abstract public function order_faild($uid, $lid);
   abstract public function level_admin_html($level_data=array());
   abstract public function level_save($level_metas=array());
}
