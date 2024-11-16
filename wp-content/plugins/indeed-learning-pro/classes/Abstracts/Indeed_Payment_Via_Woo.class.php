<?php
if (class_exists('Indeed_Payment_Via_Woo')){
	 return;
}
abstract class Indeed_Payment_Via_Woo{
	public function __construct(){
      add_filter('woocommerce_product_data_tabs', array($this, 'add_product_tab'));
  		add_action('woocommerce_product_data_panels', array($this, 'product_tab_html'));
  		add_action('woocommerce_process_product_meta_simple', array($this, 'admin_save'));
  		add_action('woocommerce_process_product_meta_grouped', array($this, 'admin_save'));
  		add_action('woocommerce_process_product_meta_external', array($this, 'admin_save'));
  		add_action('woocommerce_process_product_meta_variable', array($this, 'admin_save'));
      /// PUBLIC
  		add_action('woocommerce_checkout_order_processed', array($this, 'create_order')); /// insert order
  		add_action('woocommerce_order_status_completed', array($this, 'order_completed')); /// order is completed
      /// order fail
      add_action('woocommerce_order_status_pending_to_cancelled', array($this, 'order_fail'));
  		add_action('woocommerce_order_status_pending_to_failed', array($this, 'order_fail'));
  		add_action('woocommerce_order_status_completed_to_refunded', array($this, 'order_fail'));
  		add_action('woocommerce_order_status_completed_to_cancelled', array($this, 'order_fail'));
  		add_action('woocommerce_order_status_processing_to_refunded', array($this, 'order_fail'));
  		add_action('woocommerce_order_status_processing_to_cancelled', array($this, 'order_fail'));
  		add_action('woocommerce_order_status_on-hold_to_refunded', array($this, 'order_fail'));
  		add_action('wc-on-hold_to_trash', array($this, 'order_fail'));
  		add_action('wc-processing_to_trash', array($this, 'order_fail'));
  		add_action('wc-completed_to_trash', array($this, 'order_fail'));
	}
	abstract public function add_product_tab($product_tabs=array());
  abstract public function product_tab_html();
  abstract public function admin_save($post_id=0);
  abstract public function create_order($order_id=0);
  abstract public function order_completed($order_id=0);
  abstract public function order_fail($order_id=0);
  protected function calculate_discount_per_product($total_discount=0, $total_amount=0, $product_amount=0){
      if ($total_discount && $total_amount && $product_amount){
         $discount_percent_per_product = 100 * $product_amount / $total_amount;
         if ($discount_percent_per_product){
             $discount_per_product = $discount_percent_per_product * $total_discount / 100;
             if ($discount_per_product){
                return round($discount_per_product, 2);
             }
         }
      }
      return 0;
  }
}
