<?php
namespace Indeed\Ihc\Gateways;

class PayPalExpressCheckout extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'paypal_express_checkout'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_paypal_express_checkout', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_paypal_express_return_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => 'ihc_paypal_express_return_page_on_cancel', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'Day',
                'weeksSymbol'              => 'Week',
                'monthsSymbol'             => 'Month',
                'yearsSymbol'              => 'Year',
                'daysSupport'              => true,
                'daysMinLimit'             => 1,
                'daysMaxLimit'             => 90,
                'weeksSupport'             => true,
                'weeksMinLimit'            => 1,
                'weeksMaxLimit'            => 52,
                'monthsSupport'            => true,
                'monthsMinLimit'           => 1,
                'monthsMaxLimit'           => 18,
                'yearsSupport'             => true,
                'yearsMinLimit'            => 1,
                'yearsMaxLimit'            => 1,
                'maximumRecurrenceLimit'   => 52, // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 2,
                'forceMaximumRecurrenceLimit'   => false,
    ];
    protected $intervalTrialRules             = [
                              'daysSymbol'               => 'Day',
                              'weeksSymbol'              => 'Week',
                              'monthsSymbol'             => 'Month',
                              'yearsSymbol'              => 'Year',
                              'supportCertainPeriod'     => true,
                              'supportCycles'            => false,
                              'cyclesMinLimit'           => 1,
                              'cyclesMaxLimit'           => '',
                              'daysSupport'              => true,
                              'daysMinLimit'             => 1,
                              'daysMaxLimit'             => 90,
                              'weeksSupport'             => true,
                              'weeksMinLimit'            => 1,
                              'weeksMaxLimit'            => 52,
                              'monthsSupport'            => true,
                              'monthsMinLimit'           => 1,
                              'monthsMaxLimit'           => 18,
                              'yearsSupport'             => true,
                              'yearsMinLimit'            => 1,
                              'yearsMaxLimit'            => 1,
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types

    protected $paymentTypeLabel               = 'PayPal Express Checkout'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $siteUrl = site_url();
        $siteUrl = trailingslashit( $siteUrl );
        $this->paymentOutputData['amount'] = number_format( (float)$this->paymentOutputData['amount'], 2, '.', '' );
        $token = '';

        if ( $this->paymentOutputData['is_recurring'] ){
            // recurring
            $body = array(
                  'USER' 								              => $this->paymentSettings['ihc_paypal_express_checkout_user'],
                  'PWD'								                => $this->paymentSettings['ihc_paypal_express_checkout_password'],
                  'SIGNATURE'							            => $this->paymentSettings['ihc_paypal_express_checkout_signature'],
                  'METHOD'							              => 'SetExpressCheckout',
                  'VERSION'							              => 86,
                  'L_BILLINGTYPE0'					          => 'RecurringPayments',
                  'L_BILLINGAGREEMENTDESCRIPTION0'	  => $this->paymentOutputData['level_label'],
                  'cancelUrl'							            => $siteUrl,//'?ihc_action=paypal_express_cancel_payment',
                  'returnUrl'							            => $siteUrl . '?ihc_action=paypal_express_complete_payment',
            );
            $response = $this->sendRequest( $body );
            if ( isset( $response['TOKEN'] ) ){
                $token = $response['TOKEN'];
            }

            // save recurring details in order meta
            $orderMeta->save( $this->paymentOutputData['order_id'], 'interval_type', $this->paymentOutputData['interval_type'] );
            $orderMeta->save( $this->paymentOutputData['order_id'], 'interval_value', $this->paymentOutputData['interval_value'] );
            $orderMeta->save( $this->paymentOutputData['order_id'], 'recurring_limit', $this->paymentOutputData['subscription_cycles_limit'] );
            $orderMeta->save( $this->paymentOutputData['order_id'], 'description', $this->paymentOutputData['level_label'] );
            if ( isset( $this->paymentOutputData['first_amount'] ) ){
                $orderMeta->save( $this->paymentOutputData['order_id'], 'access_trial_price', $this->paymentOutputData['first_amount'] );
            }
            if ( isset( $this->paymentOutputData['trial_type'] ) ){
                $orderMeta->save( $this->paymentOutputData['order_id'], 'access_trial_type', $this->paymentOutputData['trial_type'] );
            }
            if ( isset( $this->paymentOutputData['first_payment_interval_value'] ) ){
                $orderMeta->save( $this->paymentOutputData['order_id'], 'first_payment_interval_value', $this->paymentOutputData['first_payment_interval_value'] );
            }
            if ( isset( $this->paymentOutputData['first_payment_interval_type'] ) ){
                $orderMeta->save( $this->paymentOutputData['order_id'], 'first_payment_interval_type', $this->paymentOutputData['first_payment_interval_type'] );
            }
        } else {
            /// single
            $body = array(
                  'USER' 								              => $this->paymentSettings['ihc_paypal_express_checkout_user'],
                  'PWD'								                => $this->paymentSettings['ihc_paypal_express_checkout_password'],
                  'SIGNATURE'							            => $this->paymentSettings['ihc_paypal_express_checkout_signature'],
                  'METHOD'							              => 'SetExpressCheckout',
                  'VERSION'							              => 93,
                  'PAYMENTREQUEST_0_PAYMENTACTION'	  => 'SALE',
                  'PAYMENTREQUEST_0_AMT'	            => $this->paymentOutputData['amount'],
                  'PAYMENTREQUEST_0_CURRENCYCODE'     => $this->paymentOutputData['currency'],
                  'cancelUrl'							            => $siteUrl,
                  'returnUrl'							            => $siteUrl . '?ihc_action=paypal_express_single_payment_complete_payment',
            );
            $response = $this->sendRequest( $body );
            if ( isset( $response['TOKEN'] ) ){
                $token = $response['TOKEN'];
            }
            $tokenObject = new \Indeed\Ihc\Gateways\Libraries\PayPalExpress\PayPalExpressCheckoutHandleTemporaryTokens();
            $tokenObject->save( $token );
        }

        $this->redirectUrl = $this->getAuthorizeURL( $token );

        // save token as order meta
        $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $token );

        return $this;
    }

    /**
     * @param array
     * @return array
     */
    private function sendRequest( $body=[] )
    {
        if ( $this->paymentSettings['ihc_paypal_express_checkout_sandbox'] ){
            $endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $endpoint = 'https://api-3t.paypal.com/nvp';
        }
        try {
            $response = wp_remote_post( $endpoint, array(
                'timeout' 				=> 60,
                'sslverify' 			=> FALSE,
                'httpversion' 		=> '1.1',
                'body' 					  => $body,
              )
            );
            parse_str( wp_remote_retrieve_body( $response ), $bodyResponse );
            return $bodyResponse;
        } catch (Exception $e){
            return false;
        }
    }

    /**
     * @param string
     * @return string
     */
    public function getAuthorizeURL( $token='' )
    {
        if ( $token === false || $token == '' ){
            return '';
        }
        if ( $this->paymentSettings['ihc_paypal_express_checkout_sandbox'] ){
            return 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
        } else {
            return 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
        }
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $postData = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $postData[$keyval[0]] = urldecode($keyval[1]);
        }

        if ( empty( $postData ) ){
            echo esc_ump_content('============= Ultimate Membership Pro - PayPal Express Checkout IPN ============= ');
            echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
            exit;
        }

        if (isset($postData['txn_id'])){
            $transactionId = $postData['txn_id'];
            $orderIdentificator = $transactionId;
        }
        if (isset($postData['recurring_payment_id'])){
             $orderIdentificator = $postData['recurring_payment_id'];
         }
         if ( !isset( $transactionId ) && isset( $orderIdentificator ) ){
            $transactionId = $orderIdentificator;
         }

         if ( !isset( $transactionId ) || !isset( $orderIdentificator ) ){
            return;
         }

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );
        $orderObject = new \Indeed\Ihc\Db\Orders();
        $orderData = $orderObject->setId( $orderId )
                                 ->fetch()
                                 ->get();


        // extra check for old systems . ump > v9.3
        if ( empty( $orderData ) || empty ( $orderData->uid ) ){
            $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'txn_id', $orderIdentificator );
            $orderObject = new \Indeed\Ihc\Db\Orders();
            $orderData = $orderObject->setId( $orderId )
                                     ->fetch()
                                     ->get();
            if ( empty( $orderData ) || empty ( $orderData->uid ) ){
                $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'txn_id', (isset($postData['txn_id'])) ? $postData['txn_id'] : '' );
                $orderObject = new \Indeed\Ihc\Db\Orders();
                $orderData = $orderObject->setId( $orderId )
                                         ->fetch()
                                         ->get();
                if ( !empty( $orderData ) ){
                    // save the order_indentificator
                    $orderMeta->save( $orderId, 'order_identificator', (isset($postData['txn_id'])) ? $postData['txn_id'] : '' );
                }
            } else {
                // save the order_indentificator
                $orderMeta->save( $orderId, 'order_identificator', $orderIdentificator );
            }
        }
        // end of old systems

        $this->webhookData = [
                    'transaction_id'              => $transactionId,
                    'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                    'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                    'order_identificator'         => $orderIdentificator,
                    'subscription_id'             => isset( $postData['recurring_payment_id'] ) ? $postData['recurring_payment_id'] : '',
                    'amount'                      => isset( $postData['mc_gross'] ) ? $postData['mc_gross'] : '',
                    'currency'                    => isset( $postData['mc_currency'] ) ? $postData['mc_currency'] : '',
                    'payment_details'             => isset( $postData ) ? $postData : '',
                    'payment_status'              => '',
        ];

        if (isset($_POST['payment_status'])){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(" Payment IPN: Payment status is ", 'ihc') . $_POST['payment_status'], 'payments');
            switch ($_POST['payment_status']){
              case 'Processed':
              case 'Completed':
                $this->webhookData['payment_status'] = 'completed';
                break;
              case 'Pending':
                $this->webhookData['payment_status'] = 'pending';
                if ( isset( $_POST['reason_code'] ) && $_POST['reason_code'] == 'refund' ){
                    $this->webhookData['payment_status'] = 'refund';
                }
                break;
              case 'Reversed':
              case 'Refunded':
                $this->webhookData['payment_status'] = 'refund';
                break;
              case 'Denied':
                $this->webhookData['payment_status'] = 'failed';
                break;
              case 'Canceled':
                $this->webhookData['payment_status'] = 'cancel';
                break;
            }
        } else if (isset($_POST['txn_type']) && $_POST['txn_type']=='recurring_payment_profile_created'){
            // recurring
            if ( ((int)$postData['amount']==0) && ( trim( $postData['period_type'] ) == 'Trial' ) && ( trim( $postData['payer_status'] ) ) == 'verified' ){
                $this->webhookData['payment_status'] = 'completed';
                $this->webhookData['amount'] = $postData['amount'];
                $this->webhookData['currency'] = $postData['currency_code'];
            }

        }

        switch ($_POST['txn_type']) {
              case 'web_accept':
              case 'subscr_payment':
                  break;
              case 'subscr_signup':
                  break;
              case 'subscr_modify':
                  break;
              case 'subscr_cancel':
                  $this->webhookData['payment_status'] = 'cancel';
                  break;
              case 'recurring_payment_profile_cancel':
                $this->webhookData['payment_status'] = 'cancel';
                break;
              case 'recurring_payment_suspended':
              case 'recurring_payment_suspended_due_to_max_failed_payment':
              case 'recurring_payment_failed':
                $this->webhookData['payment_status'] = 'failed';
                break;
        }
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
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId ){
            $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        }

        if ( !isset( $subscriptionId ) || $subscriptionId == '' || $subscriptionId == null ){
            return false;
        }

        $body = array(
              			'USER' 				=> $this->paymentSettings[ 'ihc_paypal_express_checkout_user' ],
              			'PWD'					=> $this->paymentSettings[ 'ihc_paypal_express_checkout_password' ],
              			'SIGNATURE'		=> $this->paymentSettings[ 'ihc_paypal_express_checkout_signature' ],
              			'METHOD'			=> 'ManageRecurringPaymentsProfileStatus',
              			'PROFILEID'		=> $subscriptionId,
                    'ACTION' 			=> 'Cancel',
                    'VERSION'			=> 93,
        );
        if ( $this->paymentSettings['ihc_paypal_express_checkout_sandbox'] ){
            $endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $endpoint = 'https://api-3t.paypal.com/nvp';
        }

        return wp_remote_post( $endpoint, array(
        		'timeout' 				=> 60,
        		'sslverify' 			=> FALSE,
        		'httpversion' 		=> '1.1',
        		'body' 					  => $body,
        	)
        );

    }

    /**
     * This function will check if a subscription can be canceled.
     * @param int
     * @param int
     * @param string
     * @return boolean
     */
    public function canDoCancel( $uid=0, $lid=0, $transactionId='' )
    {
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId ){
            $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        }

        if ( !isset( $subscriptionId ) || $subscriptionId == '' || $subscriptionId == null ){
            return false;
        }
        $paypalUser = get_option( 'ihc_paypal_express_checkout_user' );
        $paypalPwd = get_option( 'ihc_paypal_express_checkout_password' );
        $paypalSignature = get_option( 'ihc_paypal_express_checkout_signature' );
        if ( $paypalUser === false || $paypalUser === '' || $paypalPwd === false || $paypalPwd === '' || $paypalSignature === false || $paypalSignature === '' ){
            return false;
        }
        return true;
    }

}
