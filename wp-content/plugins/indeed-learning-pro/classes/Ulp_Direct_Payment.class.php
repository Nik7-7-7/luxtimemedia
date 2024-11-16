<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('Ulp_Direct_Payment')){
   return;
}
class Ulp_Direct_Payment{
    private $_uid = 0;
    private $_course_id = 0;
    private $_payment_type = ''; /// bt, paypal
    private $_payment_details = array();

    public function __construct(){}

    public function setUid($uid=0){
        $this->_uid = $uid;
        return $this;
    }

    public function getUid(){
        return $this->_uid;
    }

    public function setCourseId($course_id=0){
        $this->_course_id = $course_id;
        return $this;
    }

    public function getCourseId(){
        return $this->_course_id;
    }

    public function setPaymentType($payment_type=''){
        $this->_payment_type = $payment_type;
        return $this;
    }

    public function getPaymentType(){
        return $this->_payment_type;
    }

    public function setPaymentDetails($payment_details=array()){
        $this->_payment_details = $payment_details;
        return $this;
    }

    public function getPaymentDetails(){
        return $this->_payment_details;
    }

    public function do_payment(){
        switch ($this->_payment_type){
            case 'paypal':
              require_once ULP_PATH . 'classes/Payment_Services/Ulp_PayPal.class.php';
              $transaction_details = $this->_processing();
              $payment_object = new Ulp_PayPal();
              if (isset($payment_object)){
                  $payment_object->setTransactionDetails($transaction_details)->pay();
              }
              break;
            case 'bt':
              require_once ULP_PATH . 'classes/Payment_Services/Ulp_Bt.class.php';
              $transaction_details = $this->_processing();
              $payment_object = new Ulp_Bt();
              if (isset($payment_object)){
                  $payment_object->setTransactionDetails($transaction_details)->pay();
              }
              break;
            case 'stripe':
              require_once ULP_PATH . 'classes/Payment_Services/Ulp_Stripe.class.php';
              $payment_object = new Ulp_Stripe();
              /// create order and stuff
              $transaction_details = $this->_processing();
              $payment_object->setTransactionDetails($transaction_details)->pay();
              break;
            case '2checkout':
              $transaction_details = $this->_processing();
              $payment_object = new Indeed\Ulp\PaymentService\TwoCheckout();
              if (isset($payment_object)){
                  $payment_object->setTransactionDetails($transaction_details)->pay();
              }
              break;
        }
    }

    private function _processing(){
        $order_id = $this->_save_order();
        if (empty($this->_payment_details['amount']) || !$order_id){
            return;
        }
        $transaction_details = [
                'course_label' => DbUlp::getPostTitleByPostId($this->_course_id),
                'currency' => get_option('ulp_currency'),
                'amount' => $this->_payment_details['amount'],
                'course_id' => $this->_course_id,
                'uid' => $this->_uid,
                'order_id' => $order_id,
        ];
        return $transaction_details;
    }

    private function _save_order(){
        $ulp_order = new UlpOrder();
        $payment_details = [
            'unique_identificator' => $this->_payment_type . '_' . $this->_uid . '_' . $this->_course_id . '_' . time(),
            'amount' => $this->_payment_details['amount'],
        ];
        return $ulp_order->save($this->_uid, $this->_course_id, $payment_details);
    }

}
