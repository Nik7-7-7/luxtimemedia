<?php

namespace Indeed\Ihc;
/*
@since 7.4
*/
class DoPayment
{
  private $attributes         = array();
  private $paymentGateway     = '';
  private $returnUrl          = '';

	public function __construct($params=array(), $paymentGateway='', $returnUrl='')
	{
		  $this->attributes       = $params;
      $this->paymentGateway   = $paymentGateway;
      $this->returnUrl        = $returnUrl;
	}

  public function insertOrder()
  {
      $createOrder = new \Indeed\Ihc\CreateOrder($this->attributes, $this->paymentGateway);
      $this->attributes['orderId'] = $createOrder->proceed()->getOrderId();
      return $this;
  }

	public function processing()
	{
		switch ($this->paymentGateway){
			case 'paypal':
        if (ihc_check_payment_available('paypal')){

              $paymentGatewayObject = new \Indeed\Ihc\Gateways\PayPalStandard();
              return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                          ->check()
                                          ->preparePayment()
                                          ->saveOrder()
                                          ->chargePayment()
                                          ->redirect(); // redirect to payment service

        }
				break;
			case 'mollie':
        if ( ihc_check_payment_available( 'mollie' ) ){

              $paymentGatewayObject = new \Indeed\Ihc\Gateways\Mollie();
              return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                          ->check()
                                          ->preparePayment()
                                          ->saveOrder()
                                          ->chargePayment()
                                          ->redirect(); // redirect to payment service

        }
				break;
      case 'paypal_express_checkout':
        if (ihc_check_payment_available('paypal_express_checkout')){

              $paymentGatewayObject = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
              return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                          ->check()
                                          ->preparePayment()
                                          ->saveOrder()
                                          ->chargePayment()
                                          ->redirect(); // redirect to payment service

        }
        break;
      case 'pagseguro':
        if (ihc_check_payment_available('pagseguro')){

              $paymentGatewayObject = new \Indeed\Ihc\Gateways\Pagseguro();
              return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                          ->check()
                                          ->preparePayment()
                                          ->saveOrder()
                                          ->chargePayment()
                                          ->redirect(); // redirect to payment service

        }
        break;
      case 'stripe_checkout_v2':
        if ( ihc_check_payment_available( 'stripe_checkout_v2' ) ){

              $paymentGatewayObject = new \Indeed\Ihc\Gateways\StripeCheckout();
              return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                          ->check()
                                          ->preparePayment()
                                          ->saveOrder()
                                          ->chargePayment()
                                          ->redirect(); // redirect to payment service

        }
        break;
      case 'bank_transfer':
        if ( ihc_check_payment_available( 'bank_transfer' ) ){
            $paymentGatewayObject = new \Indeed\Ihc\Gateways\BankTransfer();
            return $paymentGatewayObject->setInputData( $this->attributes ) /// attributes for payment ( lid, uid, coupon, etc)
                                        ->check()
                                        ->preparePayment()
                                        ->saveOrder()
                                        ->chargePayment()
                                        ->redirect(); // redirect to payment service
        }
        break;
      case 'twocheckout':
        $paymentGatewayObject = new \Indeed\Ihc\Gateways\TwoCheckout();
        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                    ->check()
                                    ->preparePayment()
                                    ->saveOrder()
                                    ->chargePayment()
                                    ->redirect(); // redirect to payment service
        break;
      case 'braintree':
        $paymentGatewayObject = new \Indeed\Ihc\Gateways\Braintree();
        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                    ->check()
                                    ->preparePayment()
                                    ->saveOrder()
                                    ->chargePayment()
                                    ->redirect(); // redirect to payment service
        break;
      case 'authorize':
        $paymentGatewayObject = new \Indeed\Ihc\Gateways\Authorize();
        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                    ->check()
                                    ->preparePayment()
                                    ->saveOrder()
                                    ->chargePayment()
                                    ->redirect(); // redirect to payment service
        break;
      case 'stripe_connect':
        $paymentGatewayObject = new \Indeed\Ihc\Gateways\StripeConnect();
        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                    ->check()
                                    ->preparePayment()
                                    ->saveOrder()
                                    ->chargePayment()
                                    ->redirect(); // redirect to payment service
        break;
      default:
        $paymentGatewayObject = apply_filters( 'ihc_payment_gateway_create_payment_object', false, $this->paymentGateway );
        // @description

        if ( !$paymentGatewayObject ){
            $this->doRedirectBack();
        }
        break;
		}

    //12.5.2
    if ( empty( $paymentGatewayObject ) ){
        $paymentGatewayObject = new \Indeed\Ihc\Gateways\VirtualPayment();
    }
    //12.5.2

    if (!empty($paymentGatewayObject)){
          return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                      ->check()
                                      ->preparePayment()
                                      ->saveOrder()
                                      ->chargePayment()
                                      ->redirect(); // redirect to payment service


    }
	}

  private function doRedirectBack()
  {
      if (empty($this->returnUrl)){
          return;
      }
      wp_redirect($this->returnUrl);
      exit;
  }

}
