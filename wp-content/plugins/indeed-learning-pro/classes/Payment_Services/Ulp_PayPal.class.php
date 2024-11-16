<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_PayPal')){
   return;
}
if (!interface_exists('Ulp_Payment_Service_Interface')){
   require_once ULP_PATH . 'classes/Payment_Services/Ulp_Payment_Service_Interface.php';
}
class Ulp_PayPal implements Ulp_Payment_Service_Interface{
    private $_metas = array();
    private $_transaction_details = array();
    public function __construct(){
        $this->_metas = DbUlp::getOptionMetaGroup('paypal');
    }
    public function setTransactionDetails($transaction_details=array()){
        $this->_transaction_details = $transaction_details;
        return $this;
    }
    public function pay(){
        /// this will redirect to PayPal
        $return_url = get_home_url();
        $site_url = site_url();
      	$site_url = trailingslashit($site_url);
      	$notify_url = add_query_arg('ulp_action', 'paypal', $site_url);
        if ($this->_metas['ulp_paypal_sandbox']){
          $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
          $url = 'https://www.paypal.com/cgi-bin/webscr';
        }
        $q = '?';
        $q .= 'cmd=_xclick&';
        $q .= 'business=' . urlencode($this->_metas['ulp_paypal_email']) . '&';
        $q .= 'item_name=' . urlencode($this->_transaction_details['course_label']) . '&';
        $q .= 'currency_code=' . $this->_transaction_details['currency'] . '&';
        $q .= 'amount=' . urlencode($this->_transaction_details['amount']) . '&';
        $q .= 'paymentaction=sale&';
        $q .= 'lc=EN_US&';
        $q .= 'return=' . urlencode($return_url) . '&';
        $q .= 'cancel_return=' . urlencode($return_url) . '&';
        $q .= 'notify_url=' . urlencode($notify_url) . '&';
        $q .= 'rm=2&';
        $q .= 'no_shipping=1&';
        $q .= 'custom=' . json_encode([
            'uid' => $this->_transaction_details['uid'],
            'course_id' => $this->_transaction_details['course_id'],
            'order_id' => $this->_transaction_details['order_id'],
        ]);
        header( 'location:' . $url . $q );
        exit();
    }
    public function ipn(){
        if (!isset($_POST['payment_status']) || !isset($_POST['txn_type']) || !isset($_POST['custom'])){
            echo esc_ulp_content('============= Ultimate Learning Pro - PAYPAL IPN ============= ');
          	echo esc_ulp_content('<br/><br/>No Payments details sent. Come later');
          	exit();
        }
        $debug = FALSE;
        $log_file = ULP_PATH . 'paypal.log';
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
        		$keyval = explode ('=', $keyval);
        		if (count($keyval) == 2)
        			$myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
        	$get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
        		if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        			$value = urlencode(stripslashes($value));
        		} else {
        			$value = urlencode($value);
        		}
        		$req .= "&" . $key . "=" . $value;
        }
        if ($this->_metas['ulp_paypal_sandbox']){
        		$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        } else {
        		$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
        }
        $ch = curl_init($paypal_url);
        if ($ch == FALSE) {
        		if ($debug) {
        			error_log(date('[Y-m-d H:i e] '). "No CURL Enabled on this server ", 3, $log_file);
        		}
        		echo esc_ulp_content("No CURL Enabled on this server ");
        		exit();
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        if ($debug) {
        		curl_setopt($ch, CURLOPT_HEADER, 1);
        		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: learning-pro'));
        $res = curl_exec($ch);
        if (curl_errno($ch) != 0){ // cURL error
        	  if ($debug) {
        			error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, $log_file);
        		}
        		curl_close($ch);
        		echo date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch);
        		exit; /// out
        } else {
        		//Log the entire HTTP response if debug is switched on.
        		if ($debug) {
        			error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, $log_file );
        			error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, $log_file);
        		}
        		curl_close($ch);
        }
        // Inspect IPN validation result and act accordingly
        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));
        if (strcmp ($res, "VERIFIED") == 0) {
        		if (isset($_POST['custom'])){
        			$transaction_custom_data = stripslashes( ulp_sanitize_array($_POST['custom']) );
        			$transaction_custom_data = json_decode($transaction_custom_data, true);
        		}
            $UlpOrder = new UlpOrder();
            $unique = $UlpOrder->getUniqueByOrderId($transaction_custom_data['order_id']);
        		switch ( sanitize_text_field($_POST['payment_status'])){
        				case 'Processed':
        				case 'Completed':
          				/// payment completed
                  $UlpOrder->modify_status($unique, 'ulp_complete');
        				  break;
        				case 'Pending':
                  $UlpOrder->modify_status($unique, 'ulp_pending');
        				  break;
        				case 'Reversed':
        				case 'Denied':
                    /// FAIL
                    $UlpOrder->modify_status($unique, 'ulp_fail');
        				  break;
        				case 'Refunded':
        				  break;
        		}
            /// save all details
            $UlpOrder->save_service_transaction_details($transaction_custom_data['order_id'], serialize(ulp_sanitize_array($_POST)) );
        		exit();
        }
    }
}
