<?php
/*
This file it's included by default if the plugin is active.
$unique_identificator = woo commerce order id, ump transaction_id, etc
$ulp_order = new UlpOrder();
# Buy Course:
$payment_details = [ 'unique_identificator' => $unique_identificator, ... ]
$ulp_order->save($user_id, $course_id, $payment_details);
# Make it completed:
$ulp_order->make_completed($unique_identificator);
*/
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('UlpOrder')){
   return;
}
class UlpOrder{
    private $entity_type = 'ulp_order';
    private $pending_status = 'ulp_pending';
    private $complete_status = 'ulp_complete';
    private $fail_status = 'ulp_fail';
    private $order_meta_object = null;
    public function __construct(){
      require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
      $this->order_meta_object = new DbUlpOrdersMeta();
    }
    public function save($uid=0, $course_id=0, $order_meta=array()){
        /// insert post
        $post_data = array(
              'ping_status' => 'closed',
              'comment_status' => 'closed',
              'post_title' => 'ULP ORDER @ ' . date('Y-m-d H:i:s', time()) . ' by ' . $uid . ' for course id : ' . $course_id . '.',
              'menu_order' => 0,
              'post_type' => $this->entity_type,
              'post_status' => $this->pending_status,
        );
        $order_id = wp_insert_post($post_data);
        $order_meta ['code'] = $this->_generate_order_code($order_id);
        /// insert order metas
        if ($order_id){
            $order_meta['user_id'] = $uid;
            $order_meta['course_id'] = $course_id;
            if (empty($order_meta['unique_identificator'])){
                $order_meta['unique_identificator'] = $uid . '_' . $course_id . '_' . time();
            }
            $order_meta ['used'] = 0;
            foreach ($order_meta as $meta_key=>$meta_value){
                $this->order_meta_object->save($order_id, $meta_key, $meta_value);
            }
        }

        do_action('ulp_new_order', $order_id, $uid, $course_id);

        return $order_id;
    }
    public function delete($uid=0, $course_id=0){
        $order_id = $this->getOrderId($uid, $course_id);
        if ($order_id){
            $this->order_meta_object->deleteAllByOrderId($order_id);
        }
    }
    /**
     * @param int (user id)
     * @param int (course id)
     * @return int ( ulp_order custom post type id ), 0 if not exists
     */
    public function getOrderId($uid=0, $course_id=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $course_id = sanitize_text_field($course_id);
        $table_name = $wpdb->prefix . 'ulp_order_meta';
        $q = $wpdb->prepare("SELECT a.order_id FROM
              	$table_name a
              	INNER JOIN $table_name b
              	ON a.order_id=b.order_id
              	where
              	a.meta_key='user_id'
              	AND
              	a.meta_value=%d
              	AND
              	b.meta_key='course_id'
              	AND
              	b.meta_value=%d;", $uid, $course_id );
        $order_id = $wpdb->get_var($q);
        if ($order_id==FALSE){
            $order_id = 0;
        }
        return $order_id;
    }
    public function getOrderByUniqueIdentificator($unique_identificator=''){
        global $wpdb;
        $unique_identificator = sanitize_text_field($unique_identificator);
        $where = $wpdb->prepare(" meta_key='unique_identificator' AND meta_value=%s ORDER BY order_id DESC ", $unique_identificator );
        $order_id = $this->order_meta_object->getVar('order_id', $where);
        if ($order_id==FALSE){
            $order_id = 0;
        }
        return $order_id;
    }
    public function getUniqueByOrderId($id=0){
        $id = sanitize_text_field($id);
        return $this->order_meta_object->getVar('meta_value', " meta_key='unique_identificator' AND order_id=$id ");
    }

    public function modify_status($unique_identificator='', $new_status='', $only_hooks=FALSE){
        $order_id = $this->getOrderByUniqueIdentificator($unique_identificator);
        if ($order_id){
            switch ($new_status){
                case 'pending':
                case 'ulp_pending':
                  $status = $this->pending_status;
                  break;
                case 'complete':
                case 'ulp_complete':
                  $status = $this->complete_status;
                  break;
                case 'fail':
                case 'ulp_fail':
                default:
                  $status = $this->fail_status;
                  break;
            }
            if (empty($only_hooks)){
                $this->updateStatus( $order_id, $status );
            }
            if ($status==$this->complete_status){
                do_action('ulp_make_order_complete', $order_id);
            } else {
                $this->make_order_unused($order_id);
                do_action('ulp_remove_user_from_course', $order_id);
            }

            do_action('ulp_order_update_the_status', $order_id, $status);
        }
    }
    public function got_access($uid=0, $course_id=0){
        $order_id = $this->getOrderId($uid, $course_id);
        if ($order_id){
            $status = DbUlp::get_post_status($order_id);
            if ($status==$this->complete_status){
                return TRUE;
            }
        }
        return FALSE;
    }
    public function user_got_order_unused_for_course($uid=0, $course_id=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $course_id = sanitize_text_field($course_id);
        $q = $wpdb->prepare("
            SELECT a.order_id
            FROM {$wpdb->prefix}ulp_order_meta a
            INNER JOIN {$wpdb->prefix}ulp_order_meta b
            ON a.order_id=b.order_id
            INNER JOIN {$wpdb->prefix}ulp_order_meta c
            ON b.order_id=c.order_id
            INNER JOIN {$wpdb->posts} d
            ON c.order_id=d.ID
            WHERE
            a.meta_key='user_id'
            AND a.meta_value=%d
            AND b.meta_key='course_id'
            AND b.meta_value=%d
            AND c.meta_key='used'
            AND (c.meta_value=0 OR c.meta_value IS NULL)
            AND d.post_status='ulp_complete'
        ", $uid, $course_id );
        return $wpdb->get_var($q);
    }
    public function is_order_used($order_id=0){
        global $wpdb;
        $order_id = sanitize_text_field($order_id);
        $query = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ulp_order_meta WHERE meta_key='order_used' AND order_id=%d AND meta_value=1;", $order_id );
        return $wpdb->get_var( $query );
    }
    public function make_order_used($order_id=0){
        global $wpdb;
        $order_id = sanitize_text_field($order_id);
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ulp_order_meta SET meta_value=1 WHERE order_id=%d AND meta_key='used'; ", $order_id );
        return $wpdb->query( $query );
    }
    public function make_order_unused($order_id=0){
        global $wpdb;
        $order_id = sanitize_text_field($order_id);
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ulp_order_meta SET meta_value=0 WHERE order_id=%d AND meta_key='used'; ", $order_id );
        return $wpdb->query( $query );
    }
    public function save_service_transaction_details($order_id=0, $value=''){
        global $wpdb;
        $key = '_ulp_service_transaction_details';
        $order_id = sanitize_text_field($order_id);
        $value = sanitize_text_field($value);
        $query = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ulp_order_meta WHERE meta_key=%s AND order_id=%d;", $key, $order_id );
        $exists = $wpdb->get_var( $query );
        if ($exists){
            $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ulp_order_meta SET meta_value=%s WHERE meta_key=%s AND order_id=%d;", $value, $key, $order_id );
            $wpdb->query( $query );
        } else {
            $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ulp_order_meta VALUES(NULL, %d, %s, %s ); ", $order_id, $key, $value );
            $wpdb->query( $query );
        }
    }
    private function _generate_order_code($order_id=0){
        $prefix = get_option('ulp_order_prefix_code');
        if (empty($prefix)){
          $prefix = 'ULP';
        }
        while (strlen($order_id)<6){
          $order_id = '0' . $order_id;
        }
        $the_code = $prefix . $order_id;
        return $the_code;
    }

    /**
     * @param int
     * @param string
     * @return bool
     */
    private function updateStatus( $id=0, $status='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status=%s WHERE ID=%d;", $status, $id );
        return $wpdb->query( $query );
    }
}
