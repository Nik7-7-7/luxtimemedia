<?php
namespace Indeed\Ihc\Gateways;

class VirtualPayment extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'virtual_payment'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'virtual_payment_gateway', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_virtual_payment_gateway_return_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => 'ihc_virtual_payment_gateway_cancel_url', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => 'ihc_virtual_payment_gateway_lang', // option name ( in wp_option table ) where it's stored the language code.
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

    protected $paymentTypeLabel               = 'Virtual Payment Gateway'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        $this->redirectUrl = ''; // url to payment gateway checkout
        return $this;
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
      if ( !isset($_POST) ){
          echo esc_ump_content('============= Ultimate Membership Pro - Sample Webhook ============= ');
          echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
          exit;
      }
        $this->webhookData = [
            'transaction_id'      => '',
            'order_identificator' => '',
            'uid'                 => '',
            'lid'                 => '',
            'amount'              => '',
            'currency'            => '',
            'payment_details'     => '',
            'subscription_id'     => '',
            'payment_status'      => '', // values can be : completed, cancel, pending, failed
        ];
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
}
