<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Bt')){
   return;
}
if (!interface_exists('Ulp_Payment_Service_Interface')){
   require_once ULP_PATH . 'classes/Payment_Services/Ulp_Payment_Service_Interface.php';
}
class Ulp_Bt implements Ulp_Payment_Service_Interface{
    private $_metas = array();
    private $_transaction_details = array();
    public function __construct(){
        $this->_metas = DbUlp::getOptionMetaGroup('bt');
    }
    public function setTransactionDetails($transaction_details=array()){
        $this->_transaction_details = $transaction_details;
        return $this;
    }
    public function pay(){
        add_filter('the_content', [$this, 'payment_message'], 99, 1);
		do_action('ulp_user_bank_transfer_order', $this->_transaction_details['uid'], $this->_transaction_details['course_id'], $this->_transaction_details['amount']);
    }
    public function payment_message($content=''){
        $this->_metas['ulp_bt_message'] = stripslashes($this->_metas['ulp_bt_message']);
        $message = ulp_replace_constants($this->_metas['ulp_bt_message'], $this->_transaction_details['uid'], $this->_transaction_details['course_id'], array('{amount}' => $this->_transaction_details['amount']));
        return '<div class="ulp-bt-message">' . $message . '</div>' . $content;
    }
}
