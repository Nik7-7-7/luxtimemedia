<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Stripe')){
   return;
}
if (!interface_exists('Ulp_Payment_Service_Interface')){
   require_once ULP_PATH . 'classes/Payment_Services/Ulp_Payment_Service_Interface.php';
}
/*
Updated since version 3.6, also the stripe sdk library was changed.
*/
class Ulp_Stripe implements Ulp_Payment_Service_Interface
{
    /**
     * @var array
     */
    private $_metas               = [];
    /**
     * @var array
     */
    private $_transaction_details = [];
    /**
     * @var array
     */
    private $_payment_data        = [];

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        $this->_metas = DbUlp::getOptionMetaGroup('stripe');
    }

    /**
     * @param array
     * @return object
     */
    public function setTransactionDetails( $transaction_details=[] )
    {
        $this->_transaction_details = $transaction_details;
        return $this;
    }

    /**
     * @param array
     * @return none ( redirect )
     */
    public function pay($input_data=array())
    {
        require_once ULP_PATH . 'classes/services/Stripe/vendor/autoload.php';
        \Stripe\Stripe::setApiKey( $this->_metas['ulp_stripe_secret_key'] );

        $multiply = $this->multiplyForCurrency( $this->_transaction_details['currency'] );

        $amount = $this->_transaction_details['amount'] * $multiply;
        if ( $multiply==100 && $amount > 0 && $amount < 50){
            $amount = 50;// 0.50 cents minimum amount for stripe transactions
        }
        $amount = round( $amount, 0 );
        $homeUrl = get_home_url();

        // build session
        $sessionData = [
          'payment_method_types'    => ['card'],
          "line_items" => [[
                  'price_data'  => [
                                    'currency'          => $this->_transaction_details['currency'],
                                    'unit_amount'       => $amount,
                                    'product_data'      => [
                                              "name"        => $this->_transaction_details['course_label'],
                                              'metadata'    => [
                                                                'order_id'  => $this->_transaction_details['order_id'],
                                                                'uid'       => $this->_transaction_details['uid'],
                                                                'course_id' => $this->_transaction_details['course_id'],
                                              ],
                                    ],
                  ],
                  "quantity"    => 1,

          ]],
          'metadata'                  => [
                                      'order_id'  => $this->_transaction_details['order_id'],
                                      'uid'       => $this->_transaction_details['uid'],
                                      'course_id' => $this->_transaction_details['course_id'],
          ],
          'client_reference_id'       => $this->_transaction_details['uid'] . '_' . $this->_transaction_details['course_id'] . '_' . $this->_transaction_details['order_id'], // {uid}_{course_id}_{order_id}
          'success_url'               => $homeUrl,
          'cancel_url'                => $homeUrl,
          //'locale'                    => '',
          'mode'                      => 'payment',
          'customer_creation'         => 'always',
          'customer_email'            => \DbUlp::user_get_email($this->_transaction_details['uid'])
        ];


        $session = \Stripe\Checkout\Session::create( $sessionData );

        $sessionId =  isset( $session->id ) ? $session->id : 0;
        // end of session

        if ( $sessionId ){
            $url = ULP_URL . 'classes/services/Stripe/redirect.php?sessionId=' . $sessionId . '&key=' . $this->_metas['ulp_stripe_publishable_key'];
        } else {
            $checkout_page = get_option('ulp_default_page_checkout');
            if ($checkout_page>0){
                $url = get_permalink($checkout_page);
            }
            if ( empty( $url ) ){
                $url = $homeUrl;
            }
        }
        header( 'location:' . $url  );
        exit;
    }

    /**
     * @param none
     * @return none
     */
    public function ipn()
    {
        require_once ULP_PATH . 'classes/services/Stripe/vendor/autoload.php';
        if (empty($this->_metas['ulp_stripe_secret_key'])){
            die;
        }
        \Stripe\Stripe::setApiKey($this->_metas['ulp_stripe_secret_key']);
        $body = @file_get_contents('php://input');
        $responseData = json_decode($body, TRUE);

        if(isset($responseData['id'])){
            $event = \Stripe\Event::retrieve($responseData['id']);
        } else {
            echo esc_ulp_content('============= Indeed Learning Pro - STRIPE WEBHOOK ============= ');
            echo esc_ulp_content('<br/><br/>No Event sent. Come later');
            die;
        }

        if ($event && isset($event->data->object->id)){

            if ( isset( $responseData['object']['payment_status'] ) && $responseData['object']['payment_status'] !== 'paid' ){
                die;
            }
            $metaData = isset( $responseData['data']['object']['lines']['data'][0]['metadata'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata'] : '';
            if ( empty( $metaData ) ){
                $metaData = isset( $responseData['data']['object']['lines']['data'][1]['metadata'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata'] : '';
            }
            if ( empty( $metaData ) ){
                $metaData = isset( $responseData['data']['object']['metadata'] ) ? $responseData['data']['object']['metadata'] : '';
            }

            $order_id = isset($metaData['order_id']) ? $metaData['order_id'] : 0;

            if (empty($order_id)) {
                 die;
            }

            $UlpOrder = new \UlpOrder();
            $unique = $UlpOrder->getUniqueByOrderId($order_id);
            switch ($event->type){
                case 'checkout.session.completed':
                      /// PAYMENT MADE
                      $UlpOrder->modify_status($unique, 'ulp_complete');
                      $UlpOrder->save_service_transaction_details($order_id, serialize($event_arr));
                  break;
                case 'customer.subscription.created':
                  if ($event->data->object->status=="trialing"){
                      /// TRIAL
                  }
                  break;
                case 'charge.failed':
                case 'payment_intent.payment_failed':
                case 'invoice.payment_failed':
                  $UlpOrder->modify_status($unique, 'ulp_fail');
                  $UlpOrder->save_service_transaction_details($order_id, serialize($event_arr));
                  break;
            }
        }
        die;
    }

    /**
     * @param string
     * @return string
     */
    private function multiplyForCurrency( $currency='' )
    {
    		$zeroDecimal = [
    											'BIF',
    											'CLP',
    											'DJF',
    											'GNF',
    											'JPY',
    											'KMF',
    											'KRW',
    											'MGA',
    											'PYG',
    											'RWF',
    											'UGX',
    											'VND',
    											'VUV',
    											'XAF',
    											'XOF',
    											'XPF',
    		];
    		$currency = strtoupper( $currency );
    		if ( in_array( $currency, $zeroDecimal ) ){
    				return 1;
    		}
    		return 100;
    }
}
