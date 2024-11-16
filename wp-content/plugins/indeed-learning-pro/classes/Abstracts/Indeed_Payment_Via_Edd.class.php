<?php
if (class_exists('Indeed_Payment_Via_Edd')){
	 return;
}
abstract class Indeed_Payment_Via_Edd{
	 public function __construct(){
       add_action('edd_insert_payment', array($this, 'insert_order'), 10, 2);
       add_action('edd_complete_purchase', array($this, 'make_completed'), 10, 1);
			 add_action('edd_update_payment_status', array($this, 'modify_status'), 99, 3);
       add_action('edd_meta_box_files_fields', array($this, 'html_meta_box_content'), 999, 1);
       add_action('edd_save_download', array($this, 'save_post_meta'), 99, 2);
   }
   abstract public function insert_order($payment_id=0, $payment_data=null);
   abstract public function make_completed($payment_id=0);
   abstract public function html_meta_box_content($post_id=0);
   abstract public function save_post_meta($post_id=0, $post=null);
	 abstract public function modify_status($payment_id=0, $new_status='', $old_status='');
}
