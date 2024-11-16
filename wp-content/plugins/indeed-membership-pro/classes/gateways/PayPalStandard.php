<?php
namespace Indeed\Ihc\Gateways;

class PayPalStandard extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'paypal'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_paypal', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_paypal_return_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => 'ihc_paypal_return_page_on_cancel', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'D',
                'weeksSymbol'              => 'W',
                'monthsSymbol'             => 'M',
                'yearsSymbol'              => 'Y',
                'daysSupport'              => true,
                'daysMinLimit'             => 1,
                'daysMaxLimit'             => 90,
                'weeksSupport'             => true,
                'weeksMinLimit'            => 1,
                'weeksMaxLimit'            => 52,
                'monthsSupport'            => true,
                'monthsMinLimit'           => 1,
                'monthsMaxLimit'           => 24,
                'yearsSupport'             => true,
                'yearsMinLimit'            => 1,
                'yearsMaxLimit'            => 5,
                'maximumRecurrenceLimit'   => 52, // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 2,
                'forceMaximumRecurrenceLimit'   => false,
    ];
    protected $intervalTrialRules             = [
                              'daysSymbol'               => 'D',
                              'weeksSymbol'              => 'W',
                              'monthsSymbol'             => 'M',
                              'yearsSymbol'              => 'Y',
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
                              'monthsMaxLimit'           => 24,
                              'yearsSupport'             => true,
                              'yearsMinLimit'            => 1,
                              'yearsMaxLimit'            => 5,
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types

    protected $paymentTypeLabel               = 'PayPal'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        if ( $this->paymentSettings['ihc_paypal_sandbox'] ){
            // sandbox
            $this->redirectUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': set Sandbox mode', 'ihc'), 'payments');
        } else {
            // live
            $this->redirectUrl = 'https://www.paypal.com/cgi-bin/webscr';
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': set Live mode', 'ihc'), 'payments');
        }

        $notifyUrl = site_url();
        $notifyUrl = trailingslashit($notifyUrl);
        $notifyUrl = add_query_arg('ihc_action', 'paypal', $notifyUrl);

        if ( $this->paymentOutputData['is_recurring'] ){
            /**************** recurring ******************/
            $this->redirectUrl .= '?cmd=_xclick-subscriptions' . '&';
            // coupon
            if ( $this->paymentOutputData['first_amount'] !== false && !empty( $this->paymentOutputData['first_payment_interval_value'] ) && !empty( $this->paymentOutputData['first_payment_interval_type'] ) ){
                $this->redirectUrl .= 'modify=0&';
                $this->redirectUrl .= 'a1=' . urlencode( $this->paymentOutputData['first_amount'] ) . '&';//price
                $this->redirectUrl .= 't1=' . urlencode( $this->paymentOutputData['first_payment_interval_type'] ) . '&';//type of time
                $this->redirectUrl .= 'p1=' . urlencode( $this->paymentOutputData['first_payment_interval_value'] ) . '&';// time value
            }

            $this->redirectUrl .= 'a3=' . urlencode( $this->paymentOutputData['amount'] ) . '&';
            $this->redirectUrl .= 't3=' . $this->paymentOutputData['interval_type'] . '&';
            $this->redirectUrl .= 'p3=' . $this->paymentOutputData['interval_value'] . '&';
            $this->redirectUrl .= 'src=1&';//set the rec
            $this->redirectUrl .= 'srt='. $this->paymentOutputData['subscription_cycles_limit'] . '&';//num of rec
            $this->redirectUrl .= 'no_note=1&';

            } else {
            /******************* single payment **************/
            $this->redirectUrl .= '?cmd=_xclick&';
            $this->redirectUrl .= 'amount=' . urlencode( $this->paymentOutputData['amount'] ) . '&';
            $this->redirectUrl .= 'paymentaction=sale&';
        }

        $this->redirectUrl .= 'business=' . urlencode( $this->paymentSettings['ihc_paypal_email'] ) . '&';
        $this->redirectUrl .= 'item_name=' . urlencode( $this->paymentOutputData['level_label'] ) . '&';
        $this->redirectUrl .= 'currency_code=' . $this->paymentOutputData['currency'] . '&';

        if ( $this->paymentSettings['ihc_paypapl_locale_code'] ){
            $this->redirectUrl .= 'lc=' . $this->paymentSettings['ihc_paypapl_locale_code'] . '&';
        } else {
            $this->redirectUrl .= 'lc=EN_US&';
        }
        $this->redirectUrl .= 'return=' . urlencode( $this->returnUrlAfterPayment ) . '&';
        $this->redirectUrl .= 'cancel_return=' . urlencode( $this->cancelUrlAfterPayment ) . '&';
        $this->redirectUrl .= 'notify_url=' . urlencode( $notifyUrl ) . '&';
        $this->redirectUrl .= 'rm=2&';
        $this->redirectUrl .= 'no_shipping=1&';
        $this->redirectUrl .= 'custom=' . json_encode( ['user_id' => $this->paymentOutputData['uid'], 'level_id' => $this->paymentOutputData['lid'], 'order_identificator' => $this->paymentOutputData['order_identificator'] ] );

        return $this;
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        if ( !isset($_POST['payment_status']) && !isset($_POST['txn_type']) && !isset($_POST['custom']) ){
            echo esc_ump_content('============= Ultimate Membership Pro - PAYPAL IPN ============= ');
            echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
            exit;
        }

        $raw_post_data = file_get_contents('php://input');
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Payment Webhook - Extract data from response.', 'ihc'), 'payments');
        $rawPostArray = explode('&', $raw_post_data);
        $PostData = array();
        foreach ( $rawPostArray as $keyval ) {
          $keyval = explode( '=', $keyval );
          if (count($keyval) == 2)
            $PostData[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';

        foreach ($PostData as $key => $value) {
          $value = urlencode($value);

          $req .= "&$key=$value";
        }
        if ( $this->paymentSettings['ihc_paypal_sandbox'] ){
          $paypalUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Payment Webhook -  Set Sandbox mode.', 'ihc'), 'payments');
        } else {
          $paypalUrl = "https://www.paypal.com/cgi-bin/webscr";
          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Payment Webhook -  Set live mode.', 'ihc'), 'payments');
        }

        $ch = curl_init( $paypalUrl );
        if ($ch == FALSE) {
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Payment Webhook -  End Process. No CURL Enabled on this server. ', 'ihc'), 'payments');
            exit;
        }
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Payment Webhook -  Send cURL request to PayPal.', 'ihc'), 'payments');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: membership-pro'));
        $res = curl_exec($ch);
        if (curl_errno($ch) != 0){ // cURL error
          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(": Payment Webhook -  cURL error - can't connect to PayPal to validate IPN message: ", 'ihc') . curl_error($ch) . PHP_EOL, 'payments');
          curl_close($ch);
          exit; /// out
        } else {
          //Log the entire HTTP response if debug is switched on.
          curl_close($ch);
        }
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));

        if ( isset( $_POST['custom'] ) ){
            $data = stripslashes( $_POST['custom'] );
            $data = json_decode( $data, true );
        }
        $this->webhookData['transaction_id'] = isset( $_POST['txn_id'] ) ? $_POST['txn_id'] : '';
        $this->webhookData['uid'] = isset( $data['user_id'] ) ? $data['user_id'] : 0;
        $this->webhookData['lid'] = isset( $data['level_id'] ) ? $data['level_id'] : -1;
        $this->webhookData['order_identificator'] = isset($data['order_identificator']) ? $data['order_identificator'] : false;
        $this->webhookData['amount'] = isset( $_POST['mc_gross'] ) ? $_POST['mc_gross'] : '';
        $this->webhookData['currency'] = isset( $_POST['mc_currency'] ) ? $_POST['mc_currency'] : '';
        $this->webhookData['subscription_id'] = isset( $_POST['subscr_id'] ) ? $_POST['subscr_id'] : '';
        $this->webhookData['payment_details'] = isset( $_POST ) ? $_POST : [];

        if ( $this->webhookData['transaction_id'] == '' ){
            $this->webhookData['transaction_id'] = "txn_" . indeed_get_unixtimestamp_with_timezone() . "_{$this->webhookData['uid']}_{$this->webhookData['lid']}";
        }

        if ( strcmp( $res, "VERIFIED") == 0 ) {
          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ": Payment Webhook - cURL request Verified.", 'ihc'), 'payments');
          if ( !$this->webhookData['uid'] || $this->webhookData['lid']<0 ){
              \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Payment Webhook -  No user id or level id for this transaction. Exit.', 'ihc'), 'payments');
              exit;
          }

          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Payment Webhook - Response: '.json_encode($_POST), 'ihc'), 'payments');
          \Ihc_User_Logs::set_user_id( $this->webhookData['uid'] );
          \Ihc_User_Logs::set_level_id( $this->webhookData['lid'] );
          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(": Payment Webhook -  set user id @ ", 'ihc') . $this->webhookData['uid'], 'payments');

          if (isset($_POST['payment_status'])){
              \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ": Payment Webhook - payment_status is ", 'ihc') . $_POST['payment_status'], 'payments');
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
                case 'Denied':
                case 'Refunded':
                  $this->webhookData['payment_status'] = 'refund';
                  break;
                case 'Canceled':
                  $this->webhookData['payment_status'] = 'cancel';
                  break;
              }
          } else if (isset($_POST['txn_type']) && $_POST['txn_type']=='subscr_signup'){
			        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ": Payment Webhook - txn_type is ", 'ihc') . $_POST['txn_type'], 'payments');
              if (!empty($_POST['period1'])){
				          if (isset($_POST['mc_amount1']) && (float)$_POST['mc_amount1']==0){
					            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ": Payment Webhook - period1 is ", 'ihc') . $_POST['period1'], 'payments');
                  		$this->webhookData['payment_status'] = 'completed';
				          } else {
               			  // Wait to receive the new response via 	payment_status = Completed
                      // this will exit
                      $this->waitForNewResponse( $_POST['txn_id'], $this->webhookData['order_identificator'] );

                      \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ": Payment Webhook - wait for a new response with  payment_status = Completed", 'ihc'), 'payments');
              	  }
              } else if (isset($_POST['mc_amount1']) && (int)$_POST['mc_amount1']==0){
				          \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ": Payment Webhook - mc_amount1 is ", 'ihc') . $_POST['mc_amount1'], 'payments');
                  // Recurring, first payment was 0
                  $this->webhookData['payment_status'] = 'completed';
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
              case 'recurring_payment_profile_canceled':
                $this->webhookData['payment_status'] = 'cancel';
                break;
              case 'subscr_cancel':
                $this->webhookData['payment_status'] = 'cancel';
                break;
              case 'recurring_payment_suspended':
              case 'recurring_payment_suspended_due_to_max_failed_payment':
              case 'recurring_payment_failed':
                $this->webhookData['payment_status'] = 'failed';
              break;
          }
        } else if (strcmp ($res, "INVALID") == 0) {
            $this->webhookData['payment_status'] = 'failed';
        }
    }

    /**
     * @param string
     * @param string
     * @return none
     */
    private function waitForNewResponse( $transtionId='', $orderIdentificator='' )
    {
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );

        if ( empty( $orderId ) ){
            $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'txn_id', $transtionId );
        }
        // get order id by order identificator
        if ( empty( $orderId ) ){
            $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );
        }

        /// send error to paypal
        if ( empty( $orderId ) ){
            http_response_code( 400 );
            exit;
        }

        $orders = new \Indeed\Ihc\Db\Orders();
        $orderStatus = $orders->setId( $orderId )->fetch()->getStatus();
        if ( $orderStatus == 'Completed' ){
            http_response_code( 200 );
            exit;
        }
        http_response_code( 400 );
        exit;
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function afterRefund( $uid=0, $lid=0 )
    {
        do_action( 'ump_paypal_user_do_refund', $uid, $lid, (isset($_POST['txn_id'])) ? $_POST['txn_id'] : '' );
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function cancel( $uid=0, $lid=0, $transactionId='' )
    {
        // new workflow
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId ){
            $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        }
        if ( isset( $subscriptionId ) && $subscriptionId !== '' ){
            //{recurring payment id}
            if ( $this->paymentSettings['ihc_paypal_sandbox'] ){
              $url = "https://www.sandbox.paypal.com/myaccount/autopay/connect/" . $subscriptionId;
            } else {
              $url = "https://www.paypal.com/myaccount/autopay/connect/" . $subscriptionId;
            }
            wp_redirect( $url );
            exit;
        }

        // old workflow
        $alias = $this->paymentSettings['ihc_paypal_email'];
        if ( $this->paymentSettings['ihc_paypal_merchant_account_id'] ){
          $alias = $this->paymentSettings['ihc_paypal_merchant_account_id'];
        }
        if ( $this->paymentSettings['ihc_paypal_sandbox'] ){
          $url = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=" . urlencode( $alias );
        } else {
          $url = "https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=" . urlencode( $alias );
        }
        wp_redirect($url);
        exit;
    }

    /**
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
        if ( isset( $subscriptionId ) && $subscriptionId !== '' ){
            return true;
        }

        // old workflow
        $alias = get_option( 'ihc_paypal_email' );
        $merchantCode = get_option( 'ihc_paypal_merchant_account_id' );
        if ( $merchantCode ){
            $alias = $merchantCode;
        }
        if ( $alias !== '' ){
            return true;
        }
        return false;
    }

}
