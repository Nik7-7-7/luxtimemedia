<?php
namespace Indeed\Ihc\Gateways;

class BankTransfer extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'bank_transfer'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_bank_transfer', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'D',
                'weeksSymbol'              => 'W',
                'monthsSymbol'             => 'M',
                'yearsSymbol'              => 'Y',
                'daysSupport'              => true,
                'daysMinLimit'             => 1,
                'daysMaxLimit'             => '',
                'weeksSupport'             => true,
                'weeksMinLimit'            => 1,
                'weeksMaxLimit'            => '',
                'monthsSupport'            => true,
                'monthsMinLimit'           => 1,
                'monthsMaxLimit'           => '',
                'yearsSupport'             => true,
                'yearsMinLimit'            => 1,
                'yearsMaxLimit'            => '',
                'maximumRecurrenceLimit'   => '', // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 1,
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
                              'daysMaxLimit'             => '',
                              'weeksSupport'             => true,
                              'weeksMinLimit'            => 1,
                              'weeksMaxLimit'            => '',
                              'monthsSupport'            => true,
                              'monthsMinLimit'           => 1,
                              'monthsMaxLimit'           => '',
                              'yearsSupport'             => true,
                              'yearsMinLimit'            => 1,
                              'yearsMaxLimit'            => '',
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types

    protected $paymentTypeLabel               = 'Bank Transfer Payment Gateway'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        // >= 12.2
        $redirect = get_option('ihc_thank_you_page');
        if ( isset($redirect) && $redirect > 0){
          $url = get_permalink( $redirect );
        }elseif ( isset( $_GET['urlr'] ) ){
           $url = urldecode( sanitize_text_field($_GET['urlr']) );
       } else {
           $url = IHC_PROTOCOL . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']);
       }
       // end of >= 12.2

        if ( !empty( $this->inputData['is_register'] ) && empty( $url ) ){ // >= 12.2 && && empty( $url )
           // purchase was made from register page
           $redirect = get_option('ihc_general_register_redirect');
           $redirect = apply_filters( 'ump_public_filter_redirect_page_after_register', $redirect );
        }

        if ( !empty( $redirect ) && $redirect!=-1 ){
           // custom redirect
           $url = get_permalink( $redirect );
        }

        /*
        // >= 12.2
        if ( !isset( $url ) ){
            // redirect after register is not set or user has purchased level from account page
             $redirect = get_option('ihc_thank_you_page');
             if ( isset($redirect) && $redirect > 0){
               $url = get_permalink( $redirect );
             }elseif ( isset( $_GET['urlr'] ) ){
                $url = urldecode( sanitize_text_field($_GET['urlr']) );
            } else {
                $url = IHC_PROTOCOL . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']);
            }
        }
        // >= 12.2
        */

        if ( !empty( $this->inputData['is_register'] ) ){
            $url = apply_filters( 'ihc_register_redirect_filter', $url, $this->paymentOutputData['uid'], $this->paymentOutputData['lid'] );
        }

        $btParams = array(
                  'ihc_success_bt'  => true,
                  'ihc_lid'         => $this->paymentOutputData['lid'],
                  'ihc_uid'         => $this->paymentOutputData['uid'],
          				'ihcbt'           => 'true',
        );
        $url = add_query_arg( $btParams, $url );
        $url .= '#ihc_bt_success_msg';

        do_action( 'ihc_bank_transfer_charge', $this->paymentOutputData );

        $this->redirectUrl = $url;

		    return $this;
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        // not applied to bank transfer
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
    public function cancelSubscription( $uid=0, $lid=0, $transactionId='' )
    {

    }
}
