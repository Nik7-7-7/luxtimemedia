<?php
if (class_exists('Ulp_Edd_Payment')){
   return;
}
if (!class_exists('Indeed_Payment_Via_Edd')){
   require_once ULP_PATH . 'classes/Abstracts/Indeed_Payment_Via_Edd.class.php';
}
class Ulp_Edd_Payment extends Indeed_Payment_Via_Edd{
  private $unique_identificator_prefix = 'edd_';
  public function insert_order($payment_id=0, $payment_data=null){
      $uid = $payment_data['user_info']['id'];
      if (empty($uid)){
         return;
      }
      foreach ($payment_data['cart_details'] as $cart){
          $product_id = $cart['id'];
          $course_id = get_post_meta($product_id, 'ulp_edd_product_course_relation', TRUE);
          if ($course_id && $course_id>-1 && $uid){
            $amount = round($cart['price'], 3);
            $amount = $amount + round($cart['tax']);
            $extra_order_info['txn_id'] = 'edd_order_' . $payment_id . '_' . $course_id;
            $unique_identificator = $this->unique_identificator_prefix . $payment_id;
            $ulp_order = new UlpOrder();
            $payment_details = [
                'unique_identificator' => $unique_identificator,
                'amount' => $amount
            ];
            $ulp_order->save($uid, $course_id, $payment_details);
          }
      }
  }
  public function make_completed($payment_id=0){
      $unique_identificator = $this->unique_identificator_prefix . $payment_id;
      $ulp_order = new UlpOrder();
      $ulp_order->modify_status($unique_identificator, 'ulp_complete');
  }
  public function html_meta_box_content($post_id=0){
      $data['courses'] = DbUlp::getAllCourses();
      $data['current_value'] = get_post_meta($post_id, 'ulp_edd_product_course_relation', TRUE);
      $view = new ViewUlp();
      $view->setTemplate(ULP_PATH . 'views/admin/edd_select_course.php');
      $view->setContentData($data);
      echo esc_ulp_content($view->getOutput());
  }
  public function save_post_meta($post_id=0, $post=null){
      if (isset($_POST['ulp_edd_product_course_relation']) && $_POST['ulp_edd_product_course_relation']>-1){
          //$_POST['ulp_edd_product_course_relation'] = sanitize_text_field( $_POST['ulp_edd_product_course_relation'] );
          update_post_meta( sanitize_text_field( $post_id ), 'ulp_edd_product_course_relation', ulp_sanitize_array( $_POST['ulp_edd_product_course_relation'] ) );
      }
  }
  public function modify_status($payment_id=0, $new_status='', $old_status=''){
      $unique_identificator = $this->unique_identificator_prefix . $payment_id;
      $ulp_order = new UlpOrder();
      $ulp_order->modify_status($unique_identificator, $new_status);
  }
}
