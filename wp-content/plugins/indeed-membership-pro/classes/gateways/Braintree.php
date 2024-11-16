<?php
namespace Indeed\Ihc\Gateways;

class Braintree extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'braintree'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => false, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_braintree', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => '',
                'weeksSymbol'              => '',
                'monthsSymbol'             => 'month',
                'yearsSymbol'              => '',
                'daysSupport'              => false,
                'daysMinLimit'             => false,
                'daysMaxLimit'             => false,
                'weeksSupport'             => false,
                'weeksMinLimit'            => false,
                'weeksMaxLimit'            => false,
                'monthsSupport'            => true,
                'monthsMinLimit'           => 1,
                'monthsMaxLimit'           => '',
                'yearsSupport'             => false,
                'yearsMinLimit'            => false,
                'yearsMaxLimit'            => false,
                'maximumRecurrenceLimit'   => 100, // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 2,
                'forceMaximumRecurrenceLimit'   => false,
    ];
    protected $intervalTrialRules             = [
                              'daysSymbol'               => 'day',
                              'weeksSymbol'              => '',
                              'monthsSymbol'             => 'month',
                              'yearsSymbol'              => '',
                              'supportCertainPeriod'     => true,
                              'supportCycles'            => false,
                              'cyclesMinLimit'           => 1,
                              'cyclesMaxLimit'           => '',
                              'daysSupport'              => true,
                              'daysMinLimit'             => 1,
                              'daysMaxLimit'             => '',
                              'weeksSupport'             => false,
                              'weeksMinLimit'            => false,
                              'weeksMaxLimit'            => false,
                              'monthsSupport'            => true,
                              'monthsMinLimit'           => 1,
                              'monthsMaxLimit'           => '',
                              'yearsSupport'             => false,
                              'yearsMinLimit'            => false,
                              'yearsMaxLimit'            => false,
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types

    protected $paymentTypeLabel               = 'Braintree'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];
    private $inputCardData                    = [];

    /**
     * @param none
     * @return none
     */
    private function auth()
    {
        $env = empty( $this->paymentSettings['ihc_braintree_sandbox'] ) ? 'production' : 'sandbox';

        if ( version_compare( phpversion(), '7.2', '>=' ) ){
						// v2
            require_once IHC_PATH . 'classes/gateways/libraries/braintree/Braintree.php';
            \Braintree\Configuration::environment( $env );
            \Braintree\Configuration::merchantId( $this->paymentSettings['ihc_braintree_merchant_id'] );
            \Braintree\Configuration::publicKey( $this->paymentSettings['ihc_braintree_public_key'] );
            \Braintree\Configuration::privateKey( $this->paymentSettings['ihc_braintree_private_key'] );
				} else {
						// v1
            require_once IHC_PATH . 'classes/gateways/libraries/braintree_v1/lib/Braintree.php';
            \Braintree_Configuration::environment( $env );
            \Braintree_Configuration::merchantId( $this->paymentSettings['ihc_braintree_merchant_id'] );
            \Braintree_Configuration::publicKey( $this->paymentSettings['ihc_braintree_public_key'] );
            \Braintree_Configuration::privateKey( $this->paymentSettings['ihc_braintree_private_key'] );
				}
  	}

    /**
     * @param none
     * @return object
     */
    public function check()
    {
        // default checks from abstract class
        parent::check();

        // check the payment details
        $checkFields = $this->checkFields( $_POST );
        if ( $checkFields['status'] === 0 ){
            $this->stopProcess = true;
            $this->errors[] =  implode( ',', $checkFields['errors'] );
        }

        return $this;
    }

    /**
     * @param array
     * @return array
     */
    public function checkFields( $input=[] )
    {
        $data['status'] = 1;
        $this->inputCardData['ihc_braintree_cardholderName'] = isset( $input['ihc_braintree_cardholderName'] ) ? sanitize_text_field( $input['ihc_braintree_cardholderName'] ) : '';
        $fullNameArray = explode( ' ', $this->inputCardData['ihc_braintree_cardholderName'] );
        $key = key( array_slice( $fullNameArray, -1, 1, true ) );
        $this->inputCardData['last_name'] = isset( $fullNameArray[$key] ) ? $fullNameArray[$key] : '';
        $this->inputCardData['first_name'] = str_replace( $this->inputCardData['last_name'], '', $this->inputCardData['ihc_braintree_cardholderName'] );
        $this->inputCardData['ihc_braintree_cvv'] = isset( $input['ihc_braintree_cvv'] ) ? sanitize_text_field( $input['ihc_braintree_cvv'] ) : '';
        $this->inputCardData['ihc_braintree_card_number'] = isset( $input['ihc_braintree_card_number'] ) ? sanitize_text_field( $input['ihc_braintree_card_number'] ) : '';
        $this->inputCardData['ihc_braintree_card_expire_month'] = isset( $input['ihc_braintree_card_expire_month'] ) ? sanitize_text_field( $input['ihc_braintree_card_expire_month'] ) : '';
        $this->inputCardData['ihc_braintree_card_expire_year'] = isset( $input['ihc_braintree_card_expire_year'] ) ? sanitize_text_field( $input['ihc_braintree_card_expire_year'] ) : '';

        if ( $this->inputCardData['ihc_braintree_cardholderName'] === '' ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_cardholderName'] = esc_html__('Please enter your first name and last name', 'ihc');
        }
        if ( $this->inputCardData['last_name'] === '' ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_cardholderName'] = esc_html__('Please enter your last name', 'ihc');
        }
        if ( $this->inputCardData['ihc_braintree_cvv'] === '' ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_cvv'] = esc_html__('Please enter your card CVV', 'ihc');
        }
        if ( strlen( (string)$this->inputCardData['ihc_braintree_cvv'] ) < 3 ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_cvv'] = esc_html__('CVV must contain 3 digits', 'ihc');
        }
        if ( $this->inputCardData['ihc_braintree_card_number'] === '' ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_card_number'] = esc_html__('Please enter your card number', 'ihc');
        }
        if ( $this->inputCardData['ihc_braintree_card_expire_month'] === '' ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_card_expire_month'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
        }
        if ( $this->inputCardData['ihc_braintree_card_expire_month'] > 12 || $this->inputCardData['ihc_braintree_card_expire_month'] < 1 ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_card_expire_month'] = esc_html__('Expiration card month is not valid', 'ihc');
        }
        if ( $this->inputCardData['ihc_braintree_card_expire_year'] === '' ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_card_expire_year'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
        }
        $currentYear = date("y");
        $currentYear = (int)$currentYear;

        if ( $this->inputCardData['ihc_braintree_card_expire_year'] < $currentYear ){
            $data['status'] = 0;
            $data['errors']['ihc_braintree_card_expire_year'] = esc_html__('Expiration card year is not valid', 'ihc');
        }

        $this->inputCardData['expire'] = $this->inputCardData['ihc_braintree_card_expire_month'] . '/20' . $this->inputCardData['ihc_braintree_card_expire_year'];
        return $data;
    }

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        $this->auth();

        if ( empty( $this->inputCardData ) ){
            return $this;
        }

        $customerData = [
          'firstName'   => $this->inputCardData['first_name'],
          'lastName'    => $this->inputCardData['last_name'],
          'email'       => $this->paymentOutputData['customer_email'],
          'creditCard'  => [
              'number'          => $this->inputCardData['ihc_braintree_card_number'],
              'expirationDate'  => $this->inputCardData['expire'],
              'cvv'             => $this->inputCardData['ihc_braintree_cvv'],
              'cardholderName'  => $this->inputCardData['ihc_braintree_cardholderName'],
          ]
        ];

        if ( version_compare( phpversion(), '7.2', '>=' ) ){
            // v2
            $customer = \Braintree\Customer::create( $customerData );
        } else {
            // v1
            $customer = \Braintree_Customer::create( $customerData );
        }

        if ( $this->paymentOutputData['is_recurring'] ){
            // recurring
            $subscriptionDataArray = [
                                  'paymentMethodToken'      => isset( $customer->customer->creditCards[0]->token ) ? $customer->customer->creditCards[0]->token : '',
                                  'planId'                  => $this->levelData['name'],
                                  'price'                   => $this->paymentOutputData['amount'],
                                  'numberOfBillingCycles'   => $this->paymentOutputData['subscription_cycles_limit'],
            ];
            if ( isset($this->paymentOutputData['is_trial']) ){
                $subscriptionDataArray['trialPeriod'] = true;
                $subscriptionDataArray['trialDuration'] = $this->paymentOutputData['first_payment_interval_value'];
                $subscriptionDataArray['trialDurationUnit'] = $this->paymentOutputData['first_payment_interval_type'];
            }
            if ( version_compare( phpversion(), '7.2', '>=' ) ){
                // v2
                $subscriptionData = \Braintree\Subscription::create( $subscriptionDataArray );
            } else {
                // v1
                $subscriptionData = \Braintree_Subscription::create( $subscriptionDataArray );
            }

            if ($subscriptionData->success){
              if (isset($subscriptionData->subscription) && isset($subscriptionData->subscription->id)){
                $subscriptionId = $subscriptionData->subscription->id;
                $transactionStatus = 'pending';
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                $orderMeta->save( $this->paymentOutputData['order_id'], 'subscription_id', $subscriptionId );
                $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $subscriptionId );
              }
            } else {
              $transactionStatus = 'error';
            }

            if ( !empty( $subscriptionDataArray['trialPeriod'] ) && $transactionStatus !== 'error' ){
                // make level completed
                $this->completeLevelPayment( $this->paymentOutputData );
            }

        } else {
            // single payment
            if (!empty($customer->customer) && !empty($customer->customer->id)){
                if ( version_compare( phpversion(), '7.2', '>=' ) ){
                    // v2
                    $transactionData = \Braintree\Transaction::sale( [
                                  'amount'      => $this->paymentOutputData['amount'],
                                  'customerId'  => $customer->customer->id,
                    ] );
                } else {
                    // v1
                    $transactionData = \Braintree_Transaction::sale( [
                                  'amount'      => $this->paymentOutputData['amount'],
                                  'customerId'  => $customer->customer->id,
                    ] );
                }
                $transactionId = $transactionData->transaction->id;
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $transactionId );
                $orderMeta->save( $this->paymentOutputData['order_id'], 'transaction_id', $transactionId );
            }
            if ( isset($transactionData->success) && $transactionData->success ){
              $status = 'pending';
              if ( version_compare( phpversion(), '7.2', '>=' ) ){
                  // v2
                  $response = \Braintree\Transaction::submitForSettlement( $transactionId );
              } else {
                  // v1
                  $response = \Braintree_Transaction::submitForSettlement( $transactionId );
              }
              if ( $response->success ){
                  $status = 'success';
              }
            } else {
                $status = 'error';
            }

            if ( $status === 'success'){
                // make level completed
                $this->completeLevelPayment( $this->paymentOutputData );
            }
        }

        return $this;
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        if ( empty($_POST) ){
            echo esc_ump_content('============= Ultimate Membership Pro - Braintree Webhook ============= ');
            echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
            exit;
        }

        $this->auth();

        if ( empty($_REQUEST["bt_signature"]) || empty($_REQUEST["bt_payload"]) ){
            return $this->testingWebhook();
        }

        if ( version_compare( phpversion(), '7.2', '>=' ) ){
            // v2
            $webhookNotification = \Braintree\WebhookNotification::parse( $_REQUEST["bt_signature"], $_REQUEST["bt_payload"] );
        } else {
            // v1
            $webhookNotification = \Braintree_WebhookNotification::parse( $_REQUEST["bt_signature"], $_REQUEST["bt_payload"] );
        }

        if ( empty($webhookNotification) || empty($webhookNotification->subscription) || empty($webhookNotification->subscription->id)){
            return;
        }
        $subscriptionId = $webhookNotification->subscription->id;

        $transactionId = time();
        if ( isset( $webhookNotification->subscription->transactions ) ){
            end( $webhookNotification->subscription->transactions );
            $lastKey = key( $webhookNotification->subscription->transactions );
            $transactionId = isset( $webhookNotification->subscription->transactions[$lastKey]->id ) ? $webhookNotification->subscription->transactions[$lastKey]->id : '';
        }

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
        if ( $orderId === false ){
            // out
            $this->webhookData['payment_status'] = 'other';
            return;
        }
        $orderObject = new \Indeed\Ihc\Db\Orders();
        $orderData = $orderObject->setId( $orderId )
                                 ->fetch()
                                 ->get();

        switch ( $webhookNotification->kind ){
          case 'subscription_charged_successfully':
            // success
            $this->webhookData = [
                  'subscription_id'     => $subscriptionId,
                  'transaction_id'      => $transactionId,
                  'order_identificator' => $subscriptionId,
                  'txn_id'              => $subscriptionId,
                  'uid'                 => isset( $orderData->uid ) ? $orderData->uid : 0,
                  'lid'                 => isset( $orderData->lid ) ? $orderData->lid : 0,
                  'amount'              => '',
                  'currency'            => '',
                  'payment_details'     => '',
                  'payment_status'      => 'completed', // values can be : completed, cancel, pending, failed
            ];
            break;
          case 'subscription_canceled':
            // cancel
            $this->webhookData = [
                  'subscription_id'     => $subscriptionId,
                  'transaction_id'      => '',
                  'order_identificator' => $subscriptionId,
                  'uid'                 => isset( $orderData->uid ) ? $orderData->uid : 0,
                  'lid'                 => isset( $orderData->lid ) ? $orderData->lid : 0,
                  'amount'              => '',
                  'currency'            => '',
                  'payment_details'     => '',
                  'payment_status'      => 'cancel', // values can be : completed, cancel, pending, failed
            ];
            break;
          case 'subscription_charged_unsuccessfully':
            // failed
            $this->webhookData = [
                  'subscription_id'     => $subscriptionId,
                  'transaction_id'      => '',
                  'order_identificator' => $subscriptionId,
                  'uid'                 => isset( $orderData->uid ) ? $orderData->uid : 0,
                  'lid'                 => isset( $orderData->lid ) ? $orderData->lid : 0,
                  'amount'              => '',
                  'currency'            => '',
                  'payment_details'     => '',
                  'payment_status'      => 'failed', // values can be : completed, cancel, pending, failed
            ];
            break;
          case 'subscription_expired':
            // expired
            $this->webhookData = [
                  'subscription_id'     => $subscriptionId,
                  'transaction_id'      => '',
                  'order_identificator' => $subscriptionId,
                  'uid'                 => isset( $orderData->uid ) ? $orderData->uid : 0,
                  'lid'                 => isset( $orderData->lid ) ? $orderData->lid : 0,
                  'amount'              => '',
                  'currency'            => '',
                  'payment_details'     => '',
                  'payment_status'      => 'failed', // values can be : completed, cancel, pending, failed
            ];
            break;
        }


    }

    /**
     * @param none
     * @return none
     */
    private function testingWebhook()
    {
        if ( version_compare( phpversion(), '7.2', '>=' ) ){
            // v2
            $sampleNotification = \Braintree\WebhookTesting::sampleNotification(
          	    \Braintree\WebhookNotification::SUBSCRIPTION_WENT_ACTIVE,
          	    $_REQUEST['transaction_id']
          	);
          	$webhookNotification = \Braintree\WebhookNotification::parse(
          	    $sampleNotification['bt_signature'],
          	    $sampleNotification['bt_payload']
          	);
        } else {
            // v1
            $sampleNotification = \Braintree_WebhookTesting::sampleNotification(
                \Braintree_WebhookNotification::SUBSCRIPTION_WENT_ACTIVE,
                $_REQUEST['transaction_id']
            );
            $webhookNotification = \Braintree_WebhookNotification::parse(
                $sampleNotification['bt_signature'],
                $sampleNotification['bt_payload']
            );
        }

        echo esc_html($webhookNotification->subscription->id);
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function afterRefund( $uid=0, $lid=0 )
    {

    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function cancel( $uid=0, $lid=0, $transactionId='' )
    {

    }

    public function getCheckoutform(){
      /*
       * @param none
       * @return string
       */
      $str = '';
      $meta = ihc_return_meta_arr('payment_braintree');

      $months = array();
      for ($i=1; $i<13; $i++){
        $months[$i] = $i;
      }
      $y = date("y");
      $payment_fields = array(
                  1 => array(
                        'name' => 'ihc_braintree_card_number',
                        'type' => 'number',
                        'label' => esc_html__('Card Number', 'ihc'),
                  ),
                  2 => array(
                        'name' => 'ihc_braintree_card_expire_month',
                        'type' => 'select',
                        'label' => esc_html__('Expiration Month', 'ihc'),
                        'multiple_values' => $months,
                        'value' => '',
                  ),
                  3 => array(
                        'name' => 'ihc_braintree_card_expire_year',
                        'type' => 'number',
                        'label' => esc_html__('Expiration Year', 'ihc'),
                        'min' => $y,
                        'max' => 2099,
                        'value' => $y,
                  ),
                  4 => array(
                        'name' => 'ihc_braintree_cvv',
                        'type' => 'number',
                        'label' => esc_html__('CVV', 'ihc'),
                        'max' => 9999,
                        'min' => 1,
                  ),
                  5 => array(
                        'name' => 'ihc_braintree_cardholderName',
                        'type' => 'text',
                        'label' => esc_html__('Name on the Card', 'ihc'),
                  ),
      );

      $fields = array(
                  'ihc_braintree_cardholderName' => array(
                        'value' => '',
                  ),
                  'ihc_braintree_card_number' => array(
                        'value' => '',
                  ),
                  'ihc_braintree_card_expire_month' => array(
                        'value' => '',
                  ),
                  'ihc_braintree_card_expire_year' => array(
                        'value' => '',
                        'min' => $y,
                        'max' => 99,
                  ),
                  'ihc_braintree_cvv' => array(
                        'value' => '',
                  ),

      );
      if ($meta['ihc_braintree_sandbox']){
        $fields['ihc_braintree_cardholderName']['value'] = 'John Doe';
        $fields['ihc_braintree_card_number']['value'] = '4500600000000061';
        $fields['ihc_braintree_card_expire_month']['value'] = '12';
        $fields['ihc_braintree_card_expire_year']['value'] = date('y', strtotime('+1 year'));
        $fields['ihc_braintree_cvv']['value'] = '123';
      }


      $params = [
                          'metaData'                       => $meta,
                          'fields'                         => $fields,
      ];
      $filename = 'checkout-braintree-form.php';
      $templateFile = IHC_PATH . 'public/views/checkout/checkout-braintree-form.php';
      $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

       $view = new \Indeed\Ihc\IndeedView();
      return $view->setTemplate( $templateFile )
                  ->setContentData( $params )
                  ->getOutput();

    }


}
