<?php
namespace Indeed\Ihc\Gateways\Libraries\PayPalExpress;
/*
@since 9.1
*/
class PayPalExpressCheckoutNVP
{
    private $user			                    = '';
  	private $password		                  = '';
  	private $signature 		                = '';
  	private $sandbox		                  = 0;
  	private $returnUrl 		                = '';
  	private $cancelUrl		                = '';
  	private $token			                  = '';
  	private $payerId		                  = '';
  	private $isAuthorized	                = false;
    private $endpoint                     = '';
    private $siteUrl                      = '';
    private $standardDescription          = 'SignUp Subscription';

    public function __construct()
    {
        $this->siteUrl = site_url();
        $this->siteUrl = trailingslashit($this->siteUrl);

        $this->returnUrl	= $this->siteUrl . '?ihc_action=paypal_express_complete_payment';
        $this->cancelUrl	= $this->siteUrl;//. '?ihc_action=paypal_express_cancel_payment';
        $this->user			  = get_option('ihc_paypal_express_checkout_user');
        $this->password		= get_option('ihc_paypal_express_checkout_password');
        $this->signature	= get_option('ihc_paypal_express_checkout_signature');
        $this->sandbox		= get_option('ihc_paypal_express_checkout_sandbox');

        if ($this->sandbox){
            $this->endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $this->endpoint = 'https://api-3t.paypal.com/nvp';
        }
    }

    private function sendRequest($body='')
    {
        try {
            $response = wp_remote_post($this->endpoint, array(
                'timeout' 				=> 60,
                'sslverify' 			=> FALSE,
                'httpversion' 		=> '1.1',
                'body' 					  => $body,
              )
            );
            parse_str(wp_remote_retrieve_body($response), $bodyResponse);
            return $bodyResponse;
        } catch (Exception $e){
            return false;
        }
    }


    public function completeSinglePayment()
    {
        /// Single Payment - Step 2
        if ( empty( $_GET['token'] ) || empty( $_GET['PayerID'] ) ){
            return $this;
        }
        $this->token = sanitize_text_field($_GET['token']);
        $this->payerId = sanitize_text_field($_GET['PayerID']);

        $tokenObject = new \Indeed\Ihc\Gateways\Libraries\PayPalExpress\PayPalExpressCheckoutHandleTemporaryTokens();
        if (!$tokenObject->exists($this->token)){
            return $this;
        }
        $tokenObject->remove( $this->token );

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $this->token );
        $orderData = $orderMeta->getAllByOrderId( $orderId );
        if ( empty( $orderData ) ){
            return $this;
        }

        $body = array(
            'USER' 								              => $this->user,
            'PWD'								                => $this->password,
            'SIGNATURE'							            => $this->signature,
            'METHOD'							              => 'DoExpressCheckoutPayment',
            'VERSION'							              => 93,
            'TOKEN'                             => $this->token,
            'PAYERID'                           => $this->payerId,
            'PAYMENTREQUEST_0_PAYMENTACTION'	  => 'SALE',
            'PAYMENTREQUEST_0_AMT'	            => $orderData['amount'],
            'PAYMENTREQUEST_0_CURRENCYCODE'     => $orderData['currency'],
        );
        $response = $this->sendRequest($body);

        if (empty($response['PAYMENTINFO_0_TRANSACTIONID'])){
            return $this;
        }

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderMeta->updateValueByMetaNameAndValue( 'order_identificator', $this->token,  $response['PAYMENTINFO_0_TRANSACTIONID'] );

        return $this;
    }

  	public function confirmAuthorization()
  	{
    		/// Recurring Payment - STEP 2
    		if (!empty($_GET['token'])){
    			$this->isAuthorized = true;
          $this->token        = sanitize_textarea_field($_GET['token']);
    		}
    		return $this;
  	}

  	public function getExpressCheckoutDetails()
  	{
    		/// Recurring Payment - SETP 3
    		if (empty($this->isAuthorized)){
    			return $this;
    		}
    		$body = array(
    			'USER' 			   => $this->user,
    			'PWD'			     => $this->password,
    			'SIGNATURE'		 => $this->signature,
    			'METHOD'		   => 'GetExpressCheckoutDetails',
    			'VERSION'		   => 86,
    			'TOKEN'			   => $this->token,
    		);

    		$response = $this->sendRequest($body);
    		$this->token         = $response['TOKEN'];
        $this->payerId       = $response['PAYERID'];
        return $this;
  	}

  	public function createRecurringProfile()
  	{
    		/// Recurring Payment - STEP 4
        if (empty($this->isAuthorized)){
    			return $this;
    		}
    		if ( $this->token == '' || $this->payerId == '' ){
    			return $this;
    		}
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $this->token );
        $orderData = $orderMeta->getAllByOrderId( $orderId );
        if ( empty( $orderData ) ){
            return $this;
        }

        if (empty($orderData['description'])){
            $orderData['description'] = $this->standardDescription;
        }

        /// getting the country code
        $orderObject = new \Indeed\Ihc\Db\Orders();
        $orderDetails = $orderObject->setId( $orderId )->fetch()->get();
        $uid = isset( $orderDetails->uid ) ? $orderDetails->uid : 0;
        $countryCode = get_user_meta( $uid, 'ihc_country', true );

        $body = array(
    			'USER' 				          => $this->user,
    			'PWD'				            => $this->password,
    			'SIGNATURE'			        => $this->signature,
    			'METHOD'			          => 'CreateRecurringPaymentsProfile',
    			'VERSION'			          => 86,
    			'TOKEN'				          => $this->token,
    			'PAYERID'			          => $this->payerId,
    			'PROFILESTARTDATE'	    => date('Y-m-d H:i:s'),
    			'DESC'				          => $orderData['description'],
    			'BILLINGPERIOD' 	      => $orderData['interval_type'],
    			'BILLINGFREQUENCY'	    => $orderData['interval_value'],
          'TOTALBILLINGCYCLES'    => $orderData['recurring_limit'],
    			'AMT'				            => $orderData['amount'],
    			'CURRENCYCODE' 		      => $orderData['currency'],
    			'COUNTRYCODE' 		      => empty( $countryCode ) ? '' : $countryCode,
    			'MAXFAILEDPAYMENTS'	    => 2,
          //'TRIALAMT'              => '',
          //'INITAMT'               => '',
          //'FAILEDINITAMTACTION'   => 'ContinueOnFailure',
    		);

        if (!empty($orderData['first_payment_interval_value']) && isset($orderData['first_payment_interval_type']) ){
            // trial intervals
            $body['TRIALBILLINGFREQUENCY'] = $orderData['first_payment_interval_value'];
            $body['TRIALBILLINGPERIOD'] = $orderData['first_payment_interval_type'];

            /// how many cycles
            $body['TRIALTOTALBILLINGCYCLES'] = 1;

            // trial amount
            if (isset($orderData['access_trial_price'])){
                $body['TRIALAMT'] = $orderData['access_trial_price'];
            }
        }

    		$response = $this->sendRequest($body);

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        if ( isset( $response['PROFILEID']  ) ){
            $orderMeta->updateValueByMetaNameAndValue( 'order_identificator', $this->token, $response['PROFILEID'] );
        }

        return $this;
  	}

    public function redirectToSuccessPage()
    {
        $successPage = get_option( 'ihc_paypal_express_return_page' );
        if ( empty( $successPage ) || $successPage == -1 ){
          $successPage = get_option('ihc_thank_you_page');
        }
        if ( $successPage ){
            $successPage = get_permalink( $successPage );
        }
        if ( !$successPage ){
            $successPage = $this->siteUrl;
        }
        wp_redirect( $successPage, 302 );
        exit;
    }

    public function redirectToCancel()
    {
        $cancelPage = get_option( 'ihc_paypal_express_return_page_on_cancel' );
        if ( $cancelPage ){
            $cancelPage = get_permalink( $cancelPage );
        }
        if ( !$cancelPage ){
            $cancelPage = $this->siteUrl;
        }
        wp_redirect( $cancelPage, 302 );
        exit;
    }

    public function redirectHome()
    {
        wp_redirect( $this->siteUrl, 302 );
        exit;
    }
}
