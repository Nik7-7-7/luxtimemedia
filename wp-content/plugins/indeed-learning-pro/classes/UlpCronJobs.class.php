<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('UlpCronJobs')){
   return;
}
class UlpCronJobs{
  public function __construct(){
      //if (!wp_get_schedule('ulp_general_cron', array() )){
	  $cron = wp_next_scheduled('ulp_general_cron');
	  if (empty($cron)){
          wp_schedule_event(time(), 'daily', 'ulp_general_cron');
      }
      add_action('ulp_general_cron_trigger', array($this, 'general_actions'));
  }
  public function general_actions(){
      do_action('ulp_general_cron');
  }
}
