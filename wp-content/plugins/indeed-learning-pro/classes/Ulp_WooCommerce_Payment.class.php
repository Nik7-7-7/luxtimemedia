<?php
if (class_exists('Ulp_WooCommerce_Payment')){
   return;
}
if (!class_exists('Indeed_Payment_Via_Woo')){
   require_once ULP_PATH . 'classes/Abstracts/Indeed_Payment_Via_Woo.class.php';
}
class Ulp_WooCommerce_Payment extends Indeed_Payment_Via_Woo{
    private $unique_identificator_prefix = 'woocommerce_';
    public function add_product_tab($product_tabs=array()){
        $product_tabs['ulp'] = array(
    									'label'  => esc_html__('Ultimate Learning Pro', 'ulp'),
    									'target' => 'ulp_options',
    									'class'  => array('hide_if_grouped'),
    		);
    		return $product_tabs;
    }
    public function product_tab_html(){
        global $woocommerce, $post;
        $data['courses'] = DbUlp::getAllCourses();

        $data['current_value'] = '';
        if(isset($post->ID)){
          $data['current_value'] = get_post_meta($post->ID, 'ulp_woo_product_course_relation', TRUE);
        }

        $view = new ViewUlp();
        $view->setTemplate(ULP_PATH . 'views/admin/woocommerce_select_course.php');
        $view->setContentData($data);
        echo esc_ulp_content($view->getOutput());
    }
    public function admin_save($post_id=0){
        if ($post_id && isset($_POST['ulp_woo_product_course_relation']) && $_POST['ulp_woo_product_course_relation']!=-1){
           //$_POST['ulp_woo_product_course_relation'] = sanitize_text_field( $_POST['ulp_woo_product_course_relation'] );
 		 	     update_post_meta($post_id, 'ulp_woo_product_course_relation', sanitize_text_field( $_POST['ulp_woo_product_course_relation'] ) );
 		   }
    }
    public function create_order($order_id=0){
        if ($order_id){
       		 	$order = new WC_Order($order_id);
       			$items = $order->get_items();
       			$amount = 0;
       			$extra_order_info = array();
       			$uid = $order->get_user_id();
       			$total_discount = $order->get_total_discount();
       			$total_amount = $order->get_total();
            if ($uid){
      			 	foreach ($items as $item){
      			 		$course_id = get_post_meta($item['product_id'], 'ulp_woo_product_course_relation', TRUE);
      			 		if ($course_id!==FALSE && $course_id!=-1 && $course_id!=''){
      						$amount = round($item['line_total'], 3);
      						if (!empty($item['line_tax'])){
      							$extra_order_info['tax_value'] = round($item['line_tax'], 3);
      							$amount += $extra_order_info['tax_value'];
      						}
      						$extra_order_info['txn_id'] = 'woocommerce_order_' . $order_id . '_' . $course_id;
      						if (!empty($total_discount)){
      							$product_discount = $this->calculate_discount_per_product($total_discount, $total_amount, $amount);
      							if ($product_discount){
      								$extra_order_info['discount_value'] = $product_discount;
      							}
      						}
                  ///do something with $extra_order_info
                  /// save course id - uid
                  $unique_identificator = $this->unique_identificator_prefix . $order_id;
                  $ulp_order = new UlpOrder();
                  $payment_details = [
                      'unique_identificator' => $unique_identificator,
                      'amount' => $amount,
                  ];
                  $ulp_order->save($uid, $course_id, $payment_details);
      			 		}
      			 	}
      			}
        }
    }
    public function order_completed($order_id=0){
        $unique_identificator = $this->unique_identificator_prefix . $order_id;
        $ulp_order = new UlpOrder();
        $ulp_order->modify_status($unique_identificator, 'ulp_complete');
    }
    public function order_fail($order_id=0){
        if ($order_id && is_int($order_id)){
            $unique_identificator = $this->unique_identificator_prefix . $order_id;
            $ulp_order = new UlpOrder();
            $ulp_order->modify_status($unique_identificator, 'ulp_fail');
        }
    }
}
