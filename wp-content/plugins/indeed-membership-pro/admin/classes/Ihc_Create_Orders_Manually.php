<?php
if (class_exists('Ihc_Create_Orders_Manually')){
  return;
}

class Ihc_Create_Orders_Manually{
    private $_post_data = array();
    private $_status = 0;
    private $_reason = '';

    public function __construct($post_data=array()){
        $this->_post_data = $post_data;
    }

    public function process(){
        $uid = Ihc_Db::get_wpuid_by_username($this->_post_data['username']);
        if (empty($uid)){
            $this->_status = 0;
            $this->_reason = esc_html__('Wrong Username provided.', 'ihc');
            return;
        }

        if (empty($this->_post_data['create_date'])){
            $this->_status = 0;
            $this->_reason = esc_html__('No created date provided.', 'ihc');
            return;
        }

        if (empty($this->_post_data['lid'])){
            $this->_status = 0;
            $this->_reason = esc_html__('No level provided.', 'ihc');
            return;
        }

        if (empty($this->_post_data['ihc_payment_type'])){
            $this->_status = 0;
            $this->_reason = esc_html__('No payment gateway provided.', 'ihc');
            return;
        }

        if ( \Indeed\Ihc\UserSubscriptions::userHasSubscription( $uid, $this->_post_data['lid'] ) === false ){
            \Indeed\Ihc\UserSubscriptions::assign( $uid, $this->_post_data['lid'] );
        }

    		$orderData = indeed_sanitize_array($_POST);
        $orderData['uid'] = $uid;
        $orderData['status'] = 'pending';
        $orderData['extra_fields'] = [];
        $orderData['automated_payment'] = 1;
        $orderData['amount'] = isset( $orderData['amount_value'] ) ? $orderData['amount_value'] : 0;
        require_once IHC_PATH . 'classes/Orders.class.php';
        $object = new \Ump\Orders();
        $order_id = $object->do_insert( $orderData );

        if ($order_id){
            $this->_status = 1;
        } else {
            $this->_status = 0;
            $this->_reason = esc_html__('Error', 'ihc');
        }

    }

    public function get_status(){
        return $this->_status;
    }

    public function get_reason(){
        return $this->_reason;
    }

}
