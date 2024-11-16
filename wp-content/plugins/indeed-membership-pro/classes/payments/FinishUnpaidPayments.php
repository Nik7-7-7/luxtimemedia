<?php
namespace Indeed\Ihc\Payments;

class FinishUnpaidPayments
{
    /**
     * @var mixed
     */
    private $uid                  = false;
    /**
     * @var mixed
     */
    private $lid                  = false;
    /**
     * @var mixed
     */
    private $orderId              = false;
    /**
     * @var mixed
     */
    private $paymentType          = false;

    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
     * @param array
     * @return object
     */
    public function setInput( $args=[] )
    {
        $this->uid = isset( $args['uid'] ) ? $args['uid'] : false;
        $this->lid = isset( $args['lid'] ) ? $args['lid'] : false;
        $this->orderId = isset( $args['order_id'] ) ? $args['order_id'] : false;
        $this->paymentType = isset( $args['payment_type'] ) ? $args['payment_type'] : false;
        return $this;
    }


    /**
     * @param none
     * @return none
     */
    public function doIt()
    {
        if ( $this->uid === false ){
            return false;
        }
        if ( $this->lid === false ){
            return false;
        }

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();

        // payment gateway is not available
        if ( !ihc_check_payment_available( $this->paymentType ) ){
            return false;
        }

        $paymentObject = false;
        switch ( $this->paymentType ){
          case 'twocheckout':
            $paymentObject = new \Indeed\Ihc\Gateways\TwoCheckout();
            break;
          case 'bank_transfer':
            // since version 12.6.2
            $orderMetaObject = new \Indeed\Ihc\Db\OrderMeta();
            $paymentIdentificatorForBT = $orderMetaObject->get( $this->orderId, 'key' );
            setcookie( 'ihc_payment', $paymentIdentificatorForBT, time() + 3600 * 1, COOKIEPATH, COOKIE_DOMAIN );
            // end of since version 12.6.2

            $paymentObject = new \Indeed\Ihc\Gateways\BankTransfer();
            break;
          case 'stripe_checkout_v2':
            $paymentObject = new \Indeed\Ihc\Gateways\StripeCheckout();
            break;
          case 'pagseguro':
            $paymentObject = new \Indeed\Ihc\Gateways\Pagseguro();
            break;
          case 'paypal_express_checkout':
            $paymentObject = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
            break;
          case 'mollie':
            $paymentObject = new \Indeed\Ihc\Gateways\Mollie();
            break;
          case 'paypal':
            $paymentObject = new \Indeed\Ihc\Gateways\PayPalStandard();
            break;
          case 'authorize':
            $paymentObject = new \Indeed\Ihc\Gateways\Authorize();
            break;
          case 'braintree':
            $paymentObject = new \Indeed\Ihc\Gateways\Braintree();
            break;
          case 'stripe_connect':
            $paymentObject = new \Indeed\Ihc\Gateways\StripeConnect();
            break;
          default:
            $paymentObject = false;
            break;
        }

        $paymentObject = apply_filters( 'ihc_payment_gateway_create_payment_object', $paymentObject, $this->paymentType );

        if ( $paymentObject === false ){
            return false;
        }

        if ( $this->orderId === false ){
            return false;
        }

        $args = [
          'uid'                         => $this->uid,
          'customer_email'              => \Ihc_Db::user_get_email( $this->uid ),
          'customer_name'               => \Ihc_Db::getUserFulltName( $this->uid ),
          'lid'                         => $this->lid,
          'level_label'                 => $orderMeta->get( $this->orderId, 'level_label' ),
          'level_description'           => $orderMeta->get( $this->orderId, 'level_description' ),
          'amount'                      => $orderMeta->get( $this->orderId, 'amount' ),
          'base_price'                  => $orderMeta->get( $this->orderId, 'base_price' ),
          'discount_value'              => $orderMeta->get( $this->orderId, 'discount_value' ),
          'currency'                    => $orderMeta->get( $this->orderId, 'currency' ),
          'taxes'                       => $orderMeta->get( $this->orderId, 'taxes' ),
          'taxes_details'               => $orderMeta->get( $this->orderId, 'taxes_details' ),
          'dynamic_price'               => $orderMeta->get( $this->orderId, 'dynamic_price' ),
          'coupon_used'                 => $orderMeta->get( $this->orderId, 'coupon_used' ),
          'first_amount'                => $orderMeta->get( $this->orderId, 'first_amount' ),
          'first_amount_taxes'          => $orderMeta->get( $this->orderId, 'first_amount_taxes' ),
          'first_amount_taxes_details'  => $orderMeta->get( $this->orderId, 'first_amount_taxes_details' ),
          'first_discount'              => $orderMeta->get( $this->orderId, 'first_discount' ),
          'is_recurring'                => $orderMeta->get( $this->orderId, 'is_recurring' ),
          'interval_value'              => $orderMeta->get( $this->orderId, 'interval_value' ),
          'interval_type'               => $orderMeta->get( $this->orderId, 'interval_type' ),
          'subscription_cycles_limit'   => $orderMeta->get( $this->orderId, 'subscription_cycles_limit' ),
          'couponApplied'               => $orderMeta->get( $this->orderId, 'couponApplied' ),
          'taxes_amount'				        => $orderMeta->get( $this->orderId, 'taxes_amount' ),
          'order_id'					          => $this->orderId,
          'is_trial'                     => $orderMeta->get( $this->orderId, 'is_trial' ),
          'trial_type'                   => $orderMeta->get( $this->orderId, 'trial_type' ),
          'first_payment_interval_value' => $orderMeta->get( $this->orderId, 'first_payment_interval_value' ),
          'first_payment_interval_type'  => $orderMeta->get( $this->orderId, 'first_payment_interval_type' ),
        ];

        $inputData = [
                        'lid'                   => $this->lid,
                        'uid'                   => $this->uid,
                        'ihc_coupon'	  				=> $orderMeta->get( $this->orderId, 'coupon_used' ),
                        'ihc_country'						=> $this->theCountry(),
                        'ihc_state'							=> get_user_meta( $this->uid, 'ihc_state', true ),
                        'ihc_dynamic_price'			=> $orderMeta->get( $this->orderId, 'dynamic_price' ),
                        'defaultRedirect'				=> '',
                        'is_register'						=> false,
                        'currency'              => $orderMeta->get( $this->orderId, 'currency' ),

        ];
        return $paymentObject->setInputData( $inputData )
                             ->check() // added since version 11.7 for braintree compatiblity
                             ->setPaymentOutputData( $args )
                             ->chargePayment()
                             ->redirect();
    }

    /**
     * @param none
     * @return string
     */
    private function theCountry()
    {
        if ( $this->uid === false ){
            return false;
        }
        $country = '';
        $taxesSettings = ihc_return_meta_arr('ihc_taxes_settings');
        if ( !empty( $taxesSettings['ihc_enable_taxes'] ) ){
            $country = get_user_meta( $this->uid, 'ihc_country', true );
        }
        return $country;
    }

}
