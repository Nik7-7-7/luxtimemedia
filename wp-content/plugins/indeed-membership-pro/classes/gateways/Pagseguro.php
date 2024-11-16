<?php
namespace Indeed\Ihc\Gateways;

class Pagseguro extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'pagseguro'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_pagseguro', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'Days',
                'weeksSymbol'              => 'Weekly',
                'monthsSymbol'             => 'Monthly',
                'yearsSymbol'              => 'Yearly',
                'daysSupport'              => true,
                'daysMinLimit'             => 1,
                'daysMaxLimit'             => 365,
                'weeksSupport'             => true,
                'weeksMinLimit'            => 1,
                'weeksMaxLimit'            => 52,
                'monthsSupport'            => true,
                'monthsMinLimit'           => 1,
                'monthsMaxLimit'           => 12,
                'yearsSupport'             => true,
                'yearsMinLimit'            => 1,
                'yearsMaxLimit'            => 1,
                'maximumRecurrenceLimit'   => '', // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 2,
                'forceMaximumRecurrenceLimit'   => false,
    ];
    protected $intervalTrialRules             = [
                              'daysSymbol'               => 'Days',
                              'weeksSymbol'              => '',
                              'monthsSymbol'             => '',
                              'yearsSymbol'              => '',
                              'supportCertainPeriod'     => true,
                              'supportCycles'            => false,
                              'cyclesMinLimit'           => 1,
                              'cyclesMaxLimit'           => '',
                              'daysSupport'              => true,
                              'daysMinLimit'             => 1,
                              'daysMaxLimit'             => 1000000,
                              'weeksSupport'             => false,
                              'weeksMinLimit'            => 1,
                              'weeksMaxLimit'            => 52,
                              'monthsSupport'            => false,
                              'monthsMinLimit'           => 1,
                              'monthsMaxLimit'           => 24,
                              'yearsSupport'             => false,
                              'yearsMinLimit'            => 1,
                              'yearsMaxLimit'            => 5,
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types

    protected $paymentTypeLabel               = 'Pagseguro Payment'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        $siteUrl = site_url();
        $siteUrl = trailingslashit( $siteUrl );
        $webhook = add_query_arg( 'ihc_action', 'pagseguro', $siteUrl );

        // force currency to BRL
        if ( $this->paymentOutputData['currency'] != 'BRL' ){
            $this->paymentOutputData['currency'] = 'BRL';
        }

		    // check amount - must be min 1.00 - max  2000.00.
        if ( $this->paymentOutputData['amount'] > 2000 ){
            $this->paymentOutputData['amount'] = 2000;
        }
        if ( $this->paymentOutputData['amount'] < 1 ){
            $this->paymentOutputData['amount'] = 1;
        }

        $this->paymentOutputData['amount'] = number_format( (float)$this->paymentOutputData['amount'], 2, '.', '' );


        if ( $this->paymentOutputData['is_recurring'] ){
          // Recurring payment

          /// check time and convert if it's case
          $this->paymentOutputData['interval_type'] = $this->convertIntervalType();

          $requestData = array(
                                  'email'                                   => $this->paymentSettings['ihc_pagseguro_email'],
                                  'token'                                   => $this->paymentSettings['ihc_pagseguro_token'],
                                  'currency'                                => $this->paymentOutputData['currency'],
                                  'preApprovalName'                         => $this->paymentOutputData['level_label'],
                                  'preApprovalCharge'                       => 'auto',
                                  'preApprovalPeriod'                       => $this->paymentOutputData['interval_type'],
                                  'preApprovalAmountPerPayment'             => $this->paymentOutputData['amount'],
								                  'preApprovaldetails'                      => $this->paymentOutputData['level_label'],
                                  'reference'                               => $this->paymentOutputData['order_id'],
                                  'redirectURL'                             => $siteUrl,
                                  'notificationURL'                         => $webhook,
        );

        // charge limit
        if ( isset( $this->paymentOutputData['subscription_cycles_limit'] ) && $this->paymentOutputData['subscription_cycles_limit'] != '' ){
            $preApprovalMaxTotalAmount = $this->paymentOutputData['subscription_cycles_limit'] * $this->paymentOutputData['amount'];
            $preApprovalMaxTotalAmount = number_format( (float)$preApprovalMaxTotalAmount, 2, '.', '' );
            $requestData[ 'preApprovalMaxTotalAmount' ] = $preApprovalMaxTotalAmount; //optional - if the level is limited
        }

        if ( isset( $this->paymentOutputData['first_amount'] ) && $this->paymentOutputData['first_amount'] != '' ){
            $this->paymentOutputData['first_amount'] = number_format( (float)$this->paymentOutputData['first_amount'], 2, '.', '' );
            $requestData['preApprovalMembershipFee'] = $this->paymentOutputData['first_amount'];
        }

        if ( isset( $this->paymentOutputData['first_payment_interval_value'] ) && $this->paymentOutputData['first_payment_interval_value'] != '' ){
            $requestData['preApprovalTrialPeriodDuration'] = $this->paymentOutputData['first_payment_interval_value'];
        }

        $requestData = http_build_query($requestData);

		 if ( $this->paymentSettings['ihc_pagseguro_sandbox'] ){
              $url = 'https://ws.sandbox.pagseguro.uol.com.br/v2/pre-approvals/request';
          } else {
              $url = 'https://ws.pagseguro.uol.com.br/v2/pre-approvals/request';
          }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);
        $xml= curl_exec($curl);

        if ( $xml == 'Unauthorized' ){
           return $this;
        }
        curl_close( $curl );

        $xml = simplexml_load_string( $xml );
        if( count( $xml->error ) > 0){
            return $this;
        }
        $token = (string)$xml->code;
        if ( $this->paymentSettings['ihc_pagseguro_sandbox'] ){
              $this->redirectUrl = 'https://sandbox.pagseguro.uol.com.br/v2/pre-approvals/request.html?code=' . $token;
        } else {
              $this->redirectUrl = 'https://pagseguro.uol.com.br/v2/pre-approvals/request.html?code=' . $token;
        }

      } else {
        // Single Payment
        $xml = xmlwriter_open_memory();
        xmlwriter_set_indent($xml, 1);
        $res = xmlwriter_set_indent_string($xml, ' ');

        xmlwriter_start_document($xml, '1.0', 'UTF-8', 'yes');
        xmlwriter_start_element($xml, 'checkout');

          /// currency
          xmlwriter_start_element( $xml, 'currency' );
          xmlwriter_text($xml, $this->paymentOutputData['currency'] );
          xmlwriter_end_element($xml);

          /// reference
          xmlwriter_start_element($xml, 'reference');
              xmlwriter_start_cdata($xml);
                  xmlwriter_text( $xml, $this->paymentOutputData['order_id'] ); /// generate this or put the order id here
              xmlwriter_end_cdata($xml);
          xmlwriter_end_element($xml);

          xmlwriter_start_element($xml, 'sender');
            /// email
            xmlwriter_start_element($xml, 'email');
                xmlwriter_start_cdata($xml);
                    xmlwriter_text($xml, $this->paymentOutputData['customer_email'] );
                xmlwriter_end_cdata($xml);
            xmlwriter_end_element($xml);
          xmlwriter_end_element($xml);

          /// items
          xmlwriter_start_element( $xml, 'items' );
              /// item
              xmlwriter_start_element( $xml, 'item' );
                  /// id
                  xmlwriter_start_element( $xml, 'id' );
                    xmlwriter_text( $xml, $this->paymentOutputData['lid'] . '_' . $this->paymentOutputData['uid'] );
                  xmlwriter_end_element( $xml );
                  /// description
                  xmlwriter_start_element( $xml, 'description' );
                    xmlwriter_start_cdata( $xml );
                        xmlwriter_text( $xml, $this->paymentOutputData['level_label'] );
                    xmlwriter_end_cdata( $xml );
                  xmlwriter_end_element( $xml );
                  /// amount
                  xmlwriter_start_element( $xml, 'amount' );
                    xmlwriter_text( $xml, $this->paymentOutputData['amount'] );
                  xmlwriter_end_element( $xml );
                  /// quantity
                  xmlwriter_start_element( $xml, 'quantity' );
                    xmlwriter_text( $xml, '1' );
                  xmlwriter_end_element( $xml );
              xmlwriter_end_element( $xml );
          xmlwriter_end_element( $xml );

          /// redirectURL
          xmlwriter_start_element( $xml, 'redirectURL' );
            xmlwriter_start_cdata( $xml );
                xmlwriter_text( $xml, $siteUrl );
            xmlwriter_end_cdata( $xml );
          xmlwriter_end_element( $xml );
          /// notificationURL
          xmlwriter_start_element( $xml, 'notificationURL' );
            xmlwriter_start_cdata( $xml );
                xmlwriter_text( $xml, $webhook );
            xmlwriter_end_cdata( $xml );
          xmlwriter_end_element( $xml );
          /// maxUses
          xmlwriter_start_element( $xml, 'maxUses' );
            xmlwriter_text( $xml, '1' );
          xmlwriter_end_element( $xml );
          /// maxAge
          xmlwriter_start_element( $xml, 'maxAge' );
            xmlwriter_text( $xml, '120' );
          xmlwriter_end_element( $xml );

        xmlwriter_end_element( $xml );
        xmlwriter_end_document( $xml );

        if ( $this->paymentSettings['ihc_pagseguro_sandbox'] ){
            $checkoutUrl = 'https://ws.sandbox.pagseguro.uol.com.br/v2/checkout';
        } else {
            $checkoutUrl = 'https://ws.pagseguro.uol.com.br/v2/checkout';
        }

        $xml = xmlwriter_output_memory($xml);
        $url = add_query_arg( [
                              'email' => $this->paymentSettings['ihc_pagseguro_email'],
                              'token' => $this->paymentSettings['ihc_pagseguro_token'],
        ], $checkoutUrl );

        $params = array(
          'method'  	       => 'POST',
          'timeout' 	       => 60,
          'body' 		         => $xml,
          'headers'	         => array( 'Content-Type' => 'application/xml;charset=UTF-8' ),
        );

        $response = wp_safe_remote_post( $url, $params );

        $domObject = new \DOMDocument();
        $responseData = $domObject->loadXML( $response['body'] );
        $finalResponse = simplexml_import_dom( $domObject );
        $token = (string)$finalResponse->code;

        if ( $this->paymentSettings['ihc_pagseguro_sandbox'] ){
            $this->redirectUrl = 'https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html?code=' . $token;
        } else {
            $this->redirectUrl = 'https://pagseguro.uol.com.br/v2/checkout/payment.html?code=' . $token;
        }

        // end of single payment
      }

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $this->paymentOutputData['order_id'] );

		    return $this;
    }

    /**
     * @param none
     * @return string
     */
    public function convertIntervalType()
    {
        switch ( $this->paymentOutputData['interval_type'] ){
            case 'Days':
              if ( $this->paymentOutputData['interval_value'] < 15 ){
                  return 'Weekly';
              } else if ( $this->paymentOutputData['interval_value'] > 14 && $this->paymentOutputData['interval_value'] < 49 ){
                  return 'MONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 49 && $this->paymentOutputData['interval_value'] < 77 ){
                  return 'BIMONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 77 && $this->paymentOutputData['interval_value'] < 140 ){
                  return 'TRIMONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 140 && $this->paymentOutputData['interval_value'] < 280 ){
                  return 'SEMIANNUALLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 280 ){
                  return 'Yearly';
              }
              break;
            case 'Weekly':
              if ( $this->paymentOutputData['interval_value'] == 1 || $this->paymentOutputData['interval_value'] == 2){
                  return 'Weekly';
              } else if ( $this->paymentOutputData['interval_value'] > 2 && $this->paymentOutputData['interval_value'] < 7 ){
                  return 'MONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 7 && $this->paymentOutputData['interval_value'] < 11 ){
                  return 'BIMONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 11 && $this->paymentOutputData['interval_value'] < 20 ){
                  return 'TRIMONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 20 && $this->paymentOutputData['interval_value'] < 40 ){
                  return 'SEMIANNUALLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 40 ){
                  return 'Yearly';
              }
              break;
            case 'Monthly':
              if ( $this->paymentOutputData['interval_value'] == 1 ){
                  return 'Monthly';//'MONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] == 2 ){
                  return 'BIMONTHLY';
              }else if ( $this->paymentOutputData['interval_value'] >= 3 && $this->paymentOutputData['interval_value'] < 5 ){
                  return 'TRIMONTHLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 5 && $this->paymentOutputData['interval_value'] < 10 ){
                  return 'SEMIANNUALLY';
              } else if ( $this->paymentOutputData['interval_value'] >= 10 ){
                  return 'Yearly';
              }
              break;
        }
        return $this->paymentOutputData['interval_type'];
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        if ( empty($_POST) ){
            return false;
        }
        if ( empty($_POST['notificationCode']) || empty($_POST['notificationType']) || $_POST['notificationType']!='transaction' ){
            return false;
        }
        $filename = IHC_PATH . 'temporary/' . sanitize_text_field($_POST['notificationCode']) . '.log';
        if ( file_exists( $filename ) ){
            sleep( 30 );
        }
        if ( file_exists( $filename ) ){
            sleep( 30 );
        }
        if ( file_exists( $filename ) ){
            unlink( $filename );
        }
        file_put_contents( $filename, '' );

        if ( $this->paymentSettings['ihc_pagseguro_sandbox'] ){
            $notificationUrl = 'https://ws.sandbox.pagseguro.uol.com.br/v2/transactions/notifications/';
        } else {
            $notificationUrl = 'https://ws.pagseguro.uol.com.br/v2/transactions/notifications/';
        }

        $notificationUrl .= sanitize_text_field( $_POST['notificationCode'] );
        $notificationUrl = add_query_arg( [
                                  'email' => $this->paymentSettings['ihc_pagseguro_email'],
                                  'token' => $this->paymentSettings['ihc_pagseguro_token'],
        ], $notificationUrl );

        $params = array(
            'method'        => 'GET',
            'timeout'       => 60,
        );
        $response = wp_safe_remote_post( $notificationUrl, $params );

        if ( empty( $response['body'] ) ){
            unlink( $filename );
            exit;
        }

        $dom = new \DOMDocument();
        $dom->loadXML( $response['body'] );

        if ( !isset( $dom->getElementsByTagName('reference')->item(0)->nodeValue ) || !isset($dom->getElementsByTagName('status')->item(0)->nodeValue) || !isset( $dom->getElementsByTagName('code')->item(0)->nodeValue ) ){
            unlink( $filename );
            exit;
        }

        $orderId = $dom->getElementsByTagName('reference')->item(0)->nodeValue;
        $status = $dom->getElementsByTagName('status')->item(0)->nodeValue;
        $transactionId = $dom->getElementsByTagName('code')->item(0)->nodeValue;
        $transactionId = str_replace( '-', '', $transactionId );
        $amount = $dom->getElementsByTagName('grossAmount')->item(0)->nodeValue;

        $orderObject = new \Indeed\Ihc\Db\Orders();
        $orderData = $orderObject->setId( $orderId )
                                 ->fetch()
                                 ->get();

        //create this array with informations about the transaction, subscription and user .
        $this->webhookData = [
                                'transaction_id'              => $transactionId,
                                'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                                'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                                'order_identificator'         => $orderId,
                                'amount'                      => $amount,
                                'currency'                    => 'BRL',
                                'payment_details'             => '',
                                'payment_status'              => '',
        ];

        switch ( $status ){
            case 1:
              break;
            case 2:
              break;
            case 3:
            case 4:
              $this->webhookData['payment_status'] = 'completed';
              break;
            case 5:
              /// on hold
              break;
            case 6:
              // returned
              $this->webhookData['payment_status'] = 'refund';
              break;
            case 7:
              /// cancelled
              $this->webhookData['payment_status'] = 'cancel';
              break;
        }
        unlink( $filename );
        // after you create this array, the system will automatically know what to do.
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
     * @return none
     */
    public function cancel( $uid=0, $lid=0, $preApprovalCode='' )
    {
        // not working in sandbox
        if (empty($preApprovalCode)){
           return false;
        }
        if ( $this->paymentSettings['ihc_pagseguro_sandbox'] ){
            $url = 'https://ws.sandbox.pagseguro.uol.com.br/v2/pre-approvals/cancel/';
        } else {
            $url = 'https://ws.pagseguro.uol.com.br/v2/pre-approvals/cancel/';
        }

        $url .= $preApprovalCode . '?email=' . $this->paymentSettings['ihc_pagseguro_email'] . '&token=' . $this->paymentSettings['ihc_pagseguro_token'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return boolean
     */
    public function canDoCancel( $uid=0, $lid=0, $preApprovalCode='' )
    {
        if ( $preApprovalCode === false || $preApprovalCode === null || $preApprovalCode === '' ){
            return false;
        }
        if ( get_option( 'ihc_pagseguro_email' ) == '' ){
            return false;
        }
        if ( get_option( 'ihc_pagseguro_token' ) == '' ){
            return false;
        }
        return true;
    }

}
