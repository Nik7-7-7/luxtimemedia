<?php
namespace Indeed\Ihc\Gateways;

class TwoCheckout extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'twocheckout'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_twocheckout', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_twocheckout_return_url', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'DAY',
                'weeksSymbol'              => 'WEEK',
                'monthsSymbol'             => 'MONTH',
                'yearsSymbol'              => 'YEAR',
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
                              'daysSymbol'               => 'DAY',
                              'weeksSymbol'              => 'WEEK',
                              'monthsSymbol'             => 'MONTH',
                              'yearsSymbol'              => 'YEAR',
                              'supportCertainPeriod'     => false,
                              'supportCycles'            => true,
                              'cyclesMinLimit'           => 1,
                              'cyclesMaxLimit'           => 1,
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

    protected $paymentTypeLabel               = 'TwoCheckout'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        $secretKey = $this->paymentSettings['ihc_twocheckout_private_key']; //
        $sellerId = $this->paymentSettings['ihc_twocheckout_account_number']; //
        $secretWord = $this->paymentSettings['ihc_twocheckout_secret_word']; //

        $params = [
            //Billing information
            'email'                 => $this->paymentOutputData['customer_email'],
            'name'                  => $this->paymentOutputData['customer_name'],
            'country' 							=> get_user_meta( $this->paymentOutputData['uid'], 'country', true ),
            'state' 						  	=> get_user_meta( $this->paymentOutputData['uid'], 'thestate', true ),
            'city' 									=> get_user_meta( $this->paymentOutputData['uid'], 'city', true ),
            'address' 							=> get_user_meta( $this->paymentOutputData['uid'], 'addr1', true ),
        		'phone' 								=> get_user_meta( $this->paymentOutputData['uid'], 'phone', true ),
        		'zip' 								  => get_user_meta( $this->paymentOutputData['uid'], 'zip', true ),
            //Product information
            'dynamic'								=> 1,
            'expiration'						=> time() + 300, // 5minutes
        		'item-ext-ref'				  => $this->paymentOutputData['lid'], //LEVEL ID
            'customer-ext-ref'			=> $this->paymentOutputData['uid'],
            //'order-ext-ref'         => $this->paymentOutputData['order_id'],
            'currency'							=> $this->paymentOutputData['currency'],
            'prod'									=> $this->paymentOutputData['level_label'],
            'description'           => '',
            'price'									=> $this->paymentOutputData['amount'],
            'qty'										=> 1,
            'type'									=> 'PRODUCT',
            'tangible'							=> 0,
            //Cart behavior
            'return-url'						=> $this->returnUrlAfterPayment,
            'return-type'						=> 'redirect',
        		'tpl'										=> 'one-column', // default/one-column
            'language'							=> 'en',
            'order-ext-ref'					=> $this->paymentOutputData['order_identificator'],
        ];
        if ( $this->paymentOutputData['is_recurring'] ){
            if ( $this->paymentOutputData['first_amount'] !== false ){
                $params['price'] = $this->paymentOutputData['first_amount'];
            } else {
                $params['price'] = $this->paymentOutputData['amount'];
            }
            $params['renewal-price'] = $this->paymentOutputData['amount'];
            $params[ 'recurrence' ] = $this->paymentOutputData['interval_value'] . ':' . $this->paymentOutputData['interval_type'];
            if ( $this->paymentOutputData['subscription_cycles_limit'] == '' ){
                $params[ 'duration' ] = '1:FOREVER';
            } else {
                $params['duration']	= $this->paymentOutputData['subscription_cycles_limit'] . ':' . $this->paymentOutputData['interval_type'];
            }
        }

        if ( !empty( $this->paymentSettings['ihc_twocheckout_sandbox'] ) ){
            $params['test'] = 1;
        }
        $params['signature'] = $this->getSignature( $sellerId, $secretWord, $params );

        $this->redirectUrl = 'https://secure.2checkout.com/checkout/buy/?merchant=' . $sellerId;
        foreach ( $params as $key => $value ){
            $this->redirectUrl .= '&' . "$key=$value";
        }
        return $this;
    }

    private function getSignature( $sellerId='', $secretWord='', $payload=[] )
    {
    	$payload['merchant'] = $sellerId;
    	$payload = json_encode( $payload );
    	$merchantToken = $this->generateJwtToken(
    		$sellerId,
    		time(),
    		time() + 360,
    		$secretWord
    	);
    	$curl = curl_init();
    	curl_setopt_array($curl, [
    	    CURLOPT_URL            => "https://secure.2checkout.com/checkout/api/encrypt/generate/signature",
    	    CURLOPT_RETURNTRANSFER => true,
    	    CURLOPT_CUSTOMREQUEST  => 'POST',
    	    CURLOPT_POSTFIELDS     => $payload,
    	    CURLOPT_HTTPHEADER     => [
    	        'content-type: application/json',
    					'cache-control: no-cache',
    	        'merchant-token: ' . $merchantToken,
    	    ],
    	]);
    	$response = curl_exec($curl);
    	$err      = curl_error($curl);
    	curl_close($curl);

    	if ( $err ) {
    	    $signature = false;
    	} else {
    	    $responseObject = json_decode( $response );
    			$signature = isset( $responseObject->signature ) ? $responseObject->signature : false;
    	}
    	return $signature;
    }

    function generateJwtToken( $sub, $iat, $exp, $buy_link_secret_word )
    {
    	$header    = $this->encode( json_encode( [ 'alg' => 'HS512', 'typ' => 'JWT' ] ) );
    	$payload   = $this->encode( json_encode( [ 'sub' => $sub, 'iat' => $iat, 'exp' => $exp ] ) );
    	$signature = $this->encode(
    		hash_hmac( 'sha512', "$header.$payload", $buy_link_secret_word, true )
    	);

    	return implode( '.', [
    		$header,
    		$payload,
    		$signature
    	] );
    }

    private function encode( $data )
    {
    	 return str_replace( '=', '', strtr( base64_encode( $data ), '+/', '-_' ) );
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        if ( !isset( $_POST['REFNOEXT'] ) ){
            echo esc_ump_content('============= Ultimate Membership Pro - 2Checkout IPN ============= ');
            echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
            exit;
        }
        $this->webhookData = [
            'transaction_id'      => isset( $_POST['REFNO'] ) ? 'twocheckout_' . $_POST['REFNO'] : '',
            'order_identificator' => isset( $_POST['REFNOEXT'] ) ? $_POST['REFNOEXT'] : '',
            'uid'                 => '',
            'lid'                 => '',
            'amount'              => isset( $_POST['IPN_PRICE'] ) ? $_POST['IPN_PRICE'] : '',
            'currency'            => isset( $_POST['PAYOUT_CURRENCY'] ) ? $_POST['PAYOUT_CURRENCY'] : '',
        ];


        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $this->webhookData['order_identificator'] );
        $orderObject = new \Indeed\Ihc\Db\Orders();
        $orderData = $orderObject->setId( $orderId )
                                 ->fetch()
                                 ->get();

        $this->webhookData['uid'] = isset( $orderData->uid ) ? $orderData->uid : 0;
        $this->webhookData['lid'] = isset( $orderData->lid ) ? $orderData->lid : 0;

        switch ( $_POST['ORDERSTATUS'] ) {
          case 'COMPLETE':
            $subscriptionId = isset( $_POST['IPN_LICENSE_REF'][0] ) ? $_POST['IPN_LICENSE_REF'][0] : '';
            if ( $subscriptionId != '' ){
                $orderMeta->save( $orderId, 'subscription_id', $subscriptionId );
            }
            $this->webhookData['payment_status'] = 'completed';
            break;
          case 'PENDING':
            $this->webhookData['payment_status'] = 'pending';
            break;
          case 'REFUND':
            $this->webhookData['payment_status'] = 'refund';
            break;
          case 'INVALID':
          case 'REVERSED':
            $this->webhookData['payment_status'] = 'failed';
            break;
          case 'CANCELED':
            $this->webhookData['payment_status'] = 'cancel';
            break;
          default:
            $this->webhookData['payment_status'] = 'other';
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
        $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );

        if ( $subscriptionId === false || $subscriptionId === '' || $subscriptionId === null ){
            return false;
        }
        $host = 'https://api.2checkout.com/rpc/6.0/';

        // authentification
        $merchantCode = $this->paymentSettings['ihc_twocheckout_account_number'];//;
        $secretKey = $this->paymentSettings['ihc_twocheckout_private_key'];//
        $string = strlen( $merchantCode ) . $merchantCode . strlen( gmdate( 'Y-m-d H:i:s' ) ) . gmdate( 'Y-m-d H:i:s' );
        $hash = hash_hmac('md5', $string, $secretKey );
        $i = 1;
        $jsonRpcRequest = new \stdClass();
        $jsonRpcRequest->jsonrpc = '2.0';
        $jsonRpcRequest->method = 'login';
        $jsonRpcRequest->params = [ $merchantCode, gmdate('Y-m-d H:i:s'), $hash ];
        $jsonRpcRequest->id = $i++;

        // send request to stop the recurring
        $sessionID = $this->callRPC( $jsonRpcRequest, $host, false );
        $jsonRpcRequest = array (
        								'method' 			=> 'cancelSubscription',//'disableRecurringBilling',
        								'params' 			=> [ $sessionID, $subscriptionId ],
        								'id' 					=> $i++,
        								'jsonrpc' 		=> '2.0'
        );

        return $this->callRPC( (Object)$jsonRpcRequest, $host, false );
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function canDoCancel( $uid=0, $lid=0, $transactionId='' )
    {
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );

        if ( $subscriptionId === false || $subscriptionId === '' || $subscriptionId === null ){
            return false;
        }

        if ( $this->paymentSettings['ihc_twocheckout_account_number'] === false || $this->paymentSettings['ihc_twocheckout_account_number'] === ''
              || $this->paymentSettings['ihc_twocheckout_private_key'] === false || $this->paymentSettings['ihc_twocheckout_private_key'] === ''
        ){
            return false;
        }
        return true;
    }

    private function callRPC( $Request=null, $host=null, $Debug = true )
    {
        $curl = curl_init($host);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSLVERSION, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
        $RequestString = json_encode($Request);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $RequestString);
        $ResponseString = curl_exec($curl);
        if ( $Debug ) {
            return $ResponseString;
        }
        if (!empty($ResponseString)) {
            $Response = json_decode($ResponseString);
            if (isset($Response->result)) {
                return $Response->result;
            }
            if (!is_null($Response->error)) {
                return false;
            }
        } else {
            return null;
        }
        return false;
    }

}
