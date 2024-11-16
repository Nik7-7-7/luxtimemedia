<?php
namespace Indeed\Ihc\Gateways;
/*
created @version 9.2

for payment :
$object = new \Indeed\Ihc\PaymentGateway\{ Payment Name }();
$object->setInputData( $attributes ) /// attributes for payment ( lid, uid, ihc_coupon, dynamic_price, etc )
       ->check()
       ->preparePayment()
       ->saveOrder()
       ->chargePayment()
       ->redirect(); // redirect to payment service

for cart:
$object = new \Indeed\Ihc\PaymentGateway\{ Payment Name }();
$object->setInputData( $attributes ) /// attributes for payment ( lid, uid, ihc_coupon, dynamic_price, etc )
       ->check()
       ->preparePayment()
       ->getPaymentOutputData();
*/
abstract class PaymentAbstract
{
    /* ====> Required unique payment indentificator
		Slug cannot be empty.
	  */
    protected $paymentType                    = '';

    /* ====> Relative Variables
		Should be Overwritten for each Payment service
		Default values common used are declared by default
	  */
    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => '', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ];
    // what types of recurring subscription the payment gaateway supports.
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'days',
                'weeksSymbol'              => 'weeks',
                'monthsSymbol'             => 'months',
                'yearsSymbol'              => 'years',
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
                'maximumRecurrenceLimit'   => 52, // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 2,
                'forceMaximumRecurrenceLimit'   => false,
    ];
    protected $intervalTrialRules             = [
                'supportCertainPeriod'     => true,
                'supportCycles'            => true,
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
    protected $paymentTypeLabel               = 'Default Payment label'; // label of payment

	  /* <==== Relative Variables
	  */

    /* ====> Local Variables
		Used into Payment process
    */
	  protected $redirectUrl                    = ''; // Redirect to payment gateway or next page. Default is Home
    protected $notifyUrl                      = ''; // Webhook link. Used in some payment gateways.
    protected $returnUrlAfterPayment          = ''; // Where buyer is redirected after payment step. Available only on some payment gateways. Default is Home
    protected $cancelUrlAfterPayment          = '';
    protected $stopProcess                    = false; // This will stop the entire process, including save order
	  protected $errors                         = [];

	  protected $inputData                      = []; // Input data from User inrerface

	  protected $paymentSettings                = []; // Payment settings for current Payment service (API Key, Credentials, etc) available in DataBase
    protected $levelData                      = []; // Membership details available in DataBase

	  protected $paymentOutputData              = []; // Payment details about PreparePayment() process.

    protected $webhookResponse                = null;

    //protected $couponApplied                  = false;

    /**
     * used in webhook
     */
    protected $webhookData                    = [
                      'payment_type'        => 'paypal',
                      'payment_status'      => '',
                      'transaction_id'      => '',
                      'uid'                 => 0,
                      'lid'                 => 0,
                      'is_trial'            => false,
                      'is_recurring'        => false,
                      'order_id'            => 0,
                      'order_status'        => '',
                      'order_identificator' => '',
                      'amount'              => 0,
                      'currency'            => '',
                      'payment_details'     => [],
    ];



	/* <==== Local Variables
	*/

    /**
     * set currency, set payment gateway settings ( credentials, sandbox, etc )
     * @param none
     * @return none
     */
    public function __construct()
    {
        if ( empty( $this->paymentType ) ){
            $this->stopProcess = true;
            $this->errors[] = esc_html__( ' Payment process - Error - Payment Type is not set by Developer.', 'ihc' );
            \Ihc_User_Logs::write_log( esc_html__( ' Payment process - Error - Payment Type is not set by Developer. Process stop!', 'ihc'), 'payments' );
            return false;
        }

        if ( isset( $this->paymentRules['paymentMetaSlug'] ) ){
            $metaSlug = $this->paymentRules['paymentMetaSlug'];
        } else {
            $metaSlug = $this->paymentType;
        }

		    // Get Payment settings for current Payment service
        $this->paymentSettings = ihc_return_meta_arr( $metaSlug );
        if ( empty( $this->paymentSettings ) ){
            $this->stopProcess = true;
            $this->errors[] = esc_html__( 'Payment process - Payment Settings are not set by Administrator.', 'ihc' );
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Payment process - Error - Payment Settings are not set by Administrator.', 'ihc'), 'payments' );
            return false;
        }

        $this->setNotifyUrl();
        $this->setReturnUrlAfterPayment();
        $this->setCancelUrlAfterPayment();
    }


    /**
     * @param none
     * @return none
     */
    public function setNotifyUrl()
    {
        $this->notifyUrl = add_query_arg('ihc_action', $this->paymentType, trailingslashit( site_url() ) );
        return $this;
    }

    /**
    * This is optional feature. depends on payment gateway. It creates the URL where to return after the payment is made.
    * Updated on v.10.1: Thank You page redirect have been added
    *
    * @param none
    * @return string
    */
    public function setReturnUrlAfterPayment()
    {
        if ( !empty( $this->paymentRules['returnUrlAfterPaymentOptionName'] ) ){
            $this->returnUrlAfterPayment = get_option( $this->paymentRules['returnUrlAfterPaymentOptionName'] );
        }

        if ( empty( $this->returnUrlAfterPayment ) || $this->returnUrlAfterPayment == -1 ){
          $this->returnUrlAfterPayment = get_option('ihc_thank_you_page');
        }

        if ( empty( $this->returnUrlAfterPayment ) || $this->returnUrlAfterPayment == -1 ){
            $this->returnUrlAfterPayment = get_option( 'page_on_front' );
        }
        $this->returnUrlAfterPayment = get_permalink( $this->returnUrlAfterPayment );
        if ( !$this->returnUrlAfterPayment ){
            $this->returnUrlAfterPayment = get_home_url();
        }
        return $this->returnUrlAfterPayment;
    }

    /**
    * @param none
    * @return string
    */
    public function setCancelUrlAfterPayment()
    {
        if ( !empty( $this->paymentRules['returnUrlOnCancelPaymentOptionName'] ) ){
            $this->cancelUrlAfterPayment = get_option( $this->paymentRules['returnUrlOnCancelPaymentOptionName'] );
        }
        if ( empty( $this->returnUrlAfterPayment ) || $this->returnUrlAfterPayment == -1 ){
            $this->cancelUrlAfterPayment = get_option( 'page_on_front' );
        }
        $this->cancelUrlAfterPayment = get_permalink( $this->cancelUrlAfterPayment );
        if ( !$this->cancelUrlAfterPayment ){
            $this->cancelUrlAfterPayment = get_home_url();
        }
        return $this->cancelUrlAfterPayment;
    }

    /**
     * Optional. Use this function in order to avoid: ->setInputData()->check()->preparePayment()->saveOrder() and fire directly chargePayment()->redirect().
     * @param array
     * @return object
     */
    public function setPaymentOutputData( $args=[] )
    {
        $this->paymentOutputData = $args;
        return $this;
    }

    /**
	* Set Data provided from Payment step
     * @param array
     * @return object
     */
    public function setInputData( $params=[] )
    {
        $this->inputData = $params;
        $this->inputData = apply_filters( 'ihc_filter_payments_input_data', $this->inputData );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Set Input data - Values: ', 'ihc') . serialize( $params ), 'payments' );

        // Check if Currency have been provided. Otherwise, takes the system default currency
        if ( empty( $this->inputData['currency'] ) ){
            $this->inputData['currency'] = get_option('ihc_currency');
        }
        // Set the Membership data and Redirect URL
        $this->setLevelData();
        $this->setDefaultRedirect();

        return $this;
    }

    /**
     * Set Subscripiton details based on settings stored into Database and related to chosen Subscripiton via on $this->inputData['lid']
     * @param none
     * @return object
     */
    protected function setLevelData()
    {
        if ( $this->inputData['lid'] < 0 ){
            return [];
        }
        $this->levelData = \Indeed\Ihc\Db\Memberships::getOne( $this->inputData['lid'] );

        // since version 10.5 - prorate
        $this->levelData = apply_filters( 'ihc_filter_prepare_payment_level_data', $this->levelData, $this->inputData, $this->paymentType );

        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Set Membership Data - Values: ', 'ihc') . serialize( $this->levelData ), 'payments' );
        return $this;
    }

	 /**
	  *
    * Updated on v.10.1: Thank You page redirect have been added
    *
    * @param none
    * @return object
    */
    protected function setDefaultRedirect()
    {
        if ( isset( $this->inputData['defaultRedirect'] ) && $this->inputData['defaultRedirect'] !='' ){
           $this->redirectUrl = $this->inputData['defaultRedirect'];
           return $this;
        }

        $this->redirectUrl = IHC_PROTOCOL . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']);

        //Thank You Page
        $redirect = get_option('ihc_thank_you_page');
        if (!$redirect || $redirect < 0){

          //Default Register Page
          $redirect = get_option('ihc_general_register_redirect');
          if (!$redirect || $redirect < 0){
              return $this;
          }
        }

        $url = get_permalink( $redirect );
        if ($url){
            $this->redirectUrl = $url;
        }

        if ( !empty( $this->inputData['is_register'] ) ){
            $this->redirectUrl = apply_filters('ihc_register_redirect_filter', $this->redirectUrl, $this->inputData['uid'], $this->inputData['lid']);
        }
        $url = ihc_get_redirect_link_by_label($redirect, $this->inputData['uid']);
        if ( $url && strpos( $url, IHC_PROTOCOL . $_SERVER['HTTP_HOST'] ) !== 0 ){
            $this->redirectUrl = $url;
        }

        return $this;
    }

    /**
     * Check if mandotry data exist and is properly setup. Otherwise the Payment process will be stopped.
     * @param none
     * @return object
     */
    public function check()
    {

		    do_action( 'ihc_action_before_check', $this->inputData );
        	\Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Check process - Start.', 'ihc'), 'payments');

        if ( empty( $this->inputData ) ){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Check process - Error, Input Data is not provided.', 'ihc'), 'payments' );
            $this->stopProcess = true;
            $this->errors[] =  esc_html__( "Input Data is not provided.", 'ihc' );
            return $this;
        }
        \Ihc_User_Logs::set_user_id((isset($this->inputData['uid'])) ? $this->inputData['uid'] : '');
        \Ihc_User_Logs::set_level_id((isset($this->inputData['lid'])) ? $this->inputData['lid'] : '');

        if ( empty( $this->inputData['uid'] ) ){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Check process - Error, User ID is not provided.', 'ihc'), 'payments');
            $this->stopProcess = true;
            $this->errors[] =  esc_html__( "User ID is not provided.", 'ihc' );
            return $this;
        }
        if ( empty( $this->inputData['lid'] ) ){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Check process - Error, Membership ID is not provided.', 'ihc'), 'payments');
            $this->stopProcess = true;
            $this->errors[] =  esc_html__( "Level ID is not provided.", 'ihc' );
            return $this;
        }
        if ( empty( $this->levelData ) ){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Check process - Error, Membership details are not available.', 'ihc'), 'payments');
            $this->stopProcess = true;
            $this->errors[] =  esc_html__( "Level details are not available.", 'ihc' );
            return $this;
        }
        if ( empty( $this->inputData['currency'] ) ){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Check process - Error, Currency is not set.', 'ihc'), 'payments');
            $this->stopProcess = true;
            $this->errors[] =  esc_html__( "Currency is not set.", 'ihc' );
            return $this;
        }


		    do_action( 'ihc_action_after_check', $this->inputData );
        	\Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Check process - Finish.', 'ihc'), 'payments');

        return $this;
    }

    /**
	* Prepare payment details and calculate the amount based on each Payment gateway workflow and additional options (coupons, taxes, etc).
     * @param none
     * @return object
     */
    public function preparePayment()
    {
        $this->paymentOutputData = apply_filters( 'ihc_filter_before_prepare_payment', $this->paymentOutputData );
        do_action( 'ihc_action_before_prepare_payment', $this->paymentOutputData );
		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Prepare Payment process - Start', 'ihc'), 'payments');

        if ( $this->isRecurringLevel() ){
            $this->preparePaymentRecurring();
        } else {
            $this->preparePaymentSingle();
        }

        $this->paymentOutputData = apply_filters( 'ihc_filter_after_prepare_payment', $this->paymentOutputData ); // old : ihc_filter_payments_output_data
        do_action( 'ihc_action_after_prepare_payment', $this->paymentOutputData );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Prepare Payment process - Finish - Set Payment Output data. Values: ', 'ihc') . serialize( $this->paymentOutputData), 'payments');

		    return $this;
    }


    /**
     * Create $paymentOutputData for single payment.
     * Updated on v.10.1: $dynamicPrice, $dynamicPriceUsed, $initialPrice, $key Params have been calculated and added to paymentOutputData
     *
     * @param none
     * @return object
     */
    protected function preparePaymentSingle()
    {
        // Membership Amount
        $amount = $this->levelData['price'];
        $initialPrice = $amount;

        $couponApplied = false;

        // If Dynamic Price is On: Replace default Membership Amount with chosen amount.
        $dynamicPrceData = $this->dynamicPrice( $amount );
        $amount = $dynamicPrceData['amount'];
        $dynamicPrice = $dynamicPrceData['amount'];
        $dynamicPriceUsed = $dynamicPrceData['used'];

        // Apply Coupon if exist
        if ( isset( $this->inputData['ihc_coupon'] ) && $this->inputData['ihc_coupon'] != '' ){
            $couponObject = new \Indeed\Ihc\Payments\Coupons();
            $couponObject->setCode( $this->inputData['ihc_coupon'] )
                         ->setLid( $this->inputData['lid'] )
                         ->getData();
            if ( $couponObject->isValid() ){
                \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Prepare Payment process - User used the following coupon: ', 'ihc') . $this->inputData['ihc_coupon'], 'payments');
                $discount = $couponObject->getDiscountValue( $amount );
                $amount = $amount - $discount;
                $couponApplied = true;
                if ( $amount < 0 ){
                    $amount = 0;
                }
            }
        }

         // If Taxes is On: Calculate taxes for Membership amount
        $taxesDetails = $this->getTaxes( $amount );
        $taxes = isset( $taxesDetails['total'] ) ? $taxesDetails['total'] : 0;
        $amount = isset( $taxesDetails['amount'] ) ? $taxesDetails['amount'] : $amount;
        //$initialPrice = isset( $taxesDetails['amount'] ) ? $taxesDetails['amount'] : $initialPrice;
        $dynamicPrice = isset( $taxesDetails['amount'] ) ? $taxesDetails['amount'] : $dynamicPrice;
        $amount = $amount + $taxes;

        $this->paymentOutputData = [
                                      'uid'                 => $this->inputData['uid'],
                                      'customer_email'      => \Ihc_Db::user_get_email( $this->inputData['uid'] ),
                                      'customer_name'       => \Ihc_Db::getUserFulltName( $this->inputData['uid'] ),
                                      'lid'                 => $this->inputData['lid'],
                                      'level_label'         => isset( $this->levelData['label'] ) ? $this->levelData['label'] : '',
                                      'level_description'   => isset( $this->levelData['short_description'] ) ? $this->levelData['short_description'] : '',
                                      'initial_price'       => $initialPrice,
                                      'amount'              => $amount,
                                      'base_price'          => $amount - $taxes,
                                      'currency'            => $this->inputData['currency'],
                                      'taxes_amount'        => $taxes,
                                      'taxes_details'       => $taxesDetails,
                                      'dynamic_price'       => isset( $dynamicPrice ) ? $dynamicPrice : false,
                                      'dynamic_price_used'  => isset( $dynamicPriceUsed ) ? $dynamicPriceUsed : false,
                                      'coupon_used'         => isset( $this->inputData['ihc_coupon'] ) ? $this->inputData['ihc_coupon'] : false,
                                      'discount_value'      => isset( $discount ) ? $discount : false,
                                      'is_recurring'        => false,
                                      'couponApplied'       => $couponApplied,
                                      'key'                 => strtolower(md5($this->inputData['uid'].$this->inputData['lid'].date( 'Y-m-d H:i:s' ).uniqid( 'ihc', true ))),
        ];

        // if the amount is 0, the system will stop the process before redirect to payment, and make the order and level completed.
        if ( $this->paymentOutputData['amount'] == 0 || $this->paymentOutputData['amount'] == 0.00 ){
            $this->paymentOutputData['no_charge'] = true;
        }
        return $this;
    }

    /**
     * Updated on v.10.1: $dynamicPrice, $dynamicPriceUsed, $initialPrice, $initialfirstAmount, $key Params have been calculated and added to paymentOutputData
     *
     * @param none
     * @return object
     */
    protected function preparePaymentRecurring()
    {
		    //Subscription - Access Type: Regular Period
        if ( empty( $this->paymentRules['canDoRecurring'] ) ){
            $this->errors[] = $this->paymentTypeLabel .  esc_html__( ": Prepare Payment process - Recurring Payment it's not available for this payment gateway.", 'ihc' );
            $this->stopProcess = true;
            return $this;
        }

        // Recurring Period setup
        $intervals = $this->getIntervalValues( $this->levelData['access_regular_time_value'], $this->levelData['access_regular_time_type'], $this->intervalSubscriptionRules );
        if ( empty( $intervals ) ){
            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Prepare Payment process -  Recurring cannot be set with this dates. Error on date intervals! ', 'ihc'), 'payments');
            $this->stopProcess = true;
            return $this;
        }

		    // Subscription Price
        $amount = $this->levelData['price'];
        $initialPrice = $amount;

		    //Billing Recurrences setup
        if ( $this->levelData['billing_type'] == 'bl_ongoing' && isset( $this->intervalSubscriptionRules['forceMaximumRecurrenceLimit'] ) &&
              $this->intervalSubscriptionRules['forceMaximumRecurrenceLimit'] ){
            $intervals['subscriptionCyclesLimit'] = $this->intervalSubscriptionRules['maximumRecurrenceLimit'];
        } else if ( $this->levelData['billing_type'] == 'bl_ongoing' ) {
            // on going
            $intervals['subscriptionCyclesLimit'] = '';
        } else if ( $this->levelData['billing_type'] == 'bl_limited' ){
            // Limited Recurring Subscription
            $intervals['subscriptionCyclesLimit'] = $this->levelData['billing_limit_num'];
            if ( $this->intervalSubscriptionRules['maximumRecurrenceLimit'] && $intervals['subscriptionCyclesLimit'] > $this->intervalSubscriptionRules['maximumRecurrenceLimit'] ){
                $intervals['subscriptionCyclesLimit'] = $this->intervalSubscriptionRules['maximumRecurrenceLimit']; // 0; !!!!!
            }
            if ( $this->intervalSubscriptionRules['minimumRecurrenceLimit'] && $intervals['subscriptionCyclesLimit'] < $this->intervalSubscriptionRules['minimumRecurrenceLimit'] ){
                $intervals['subscriptionCyclesLimit'] = $this->intervalSubscriptionRules['minimumRecurrenceLimit']; // 0; !!!!!
            }
        }

        // Check for Trial/First Payment settings
        $isTrial = false;
        if ( !empty( $this->paymentRules['canDoTrial'] ) && !empty( $this->levelData['access_trial_type'] )
        && ( (!empty( $this->levelData['access_trial_time_value'] ) && ($this->levelData['access_trial_type']==1))
        || (!empty( $this->levelData['access_trial_couple_cycles'] ) && ($this->levelData['access_trial_type']==2)) ) ){

          if ( !empty( $this->paymentRules['canDoTrialFree'] ) && ($this->levelData['access_trial_price'] == 0 ||
$this->levelData['access_trial_price'] == '' )){
        				$firstAmount = 0;
                $initialfirstAmount = 0;
        		} else if ( $this->levelData['access_trial_price'] > 0 ) {

              if ( !empty( $this->paymentRules['canDoTrialPaid'] ) ){
          			  $firstAmount = $this->levelData['access_trial_price'];
                  $initialfirstAmount = $firstAmount;
              }else{
                  $firstAmount = 0;
                  $initialfirstAmount = 0;
              }

        		}

        		if ( isset( $firstAmount ) ){
        				// Get Trial Interval
        				$trialInterval = $this->getTrialInterval( $firstAmount );
                $isTrial = true;
        		}

        }

        // If Dynamic Price is On: Replace default Subscription Amount with chosen amount.
        $dynamicPrceData = $this->dynamicPrice( $amount );
        $amount = $dynamicPrceData['amount'];
        $dynamicPrice = $dynamicPrceData['amount'];
        $dynamicPriceUsed = $dynamicPrceData['used'];

        // Apply Coupon if exist
        $couponApplied = false;
        if ( isset( $this->inputData['ihc_coupon'] ) && $this->inputData['ihc_coupon'] != '' ){
            $couponObject = new \Indeed\Ihc\Payments\Coupons();
            $couponData = $couponObject->setCode( $this->inputData['ihc_coupon'] )
                         ->setLid( $this->inputData['lid'] )
                         ->getData();

            if ( $couponObject->isValid() && $couponData ){

                if ( !empty( $this->paymentRules['canApplyCouponOnRecurringForEveryPayment'] ) && !empty( $couponData['reccuring'] )  ){
                    // On Memberships with Billing Recurrence apply the Discount: Forever
                    $discount = $couponObject->getDiscountValue( $amount );
                    $amount = $amount - $discount;
                    if ( $amount < 0 ){
                        $amount = 0;
                    }
                    // If Trial/First Payment exist apply Coupon
                    if ( isset( $firstAmount ) ){
                        $temporaryDiscountForFirstTime = $couponObject->getDiscountValue( $firstAmount );
                        $temporaryFirstAmount = $firstAmount - $temporaryDiscountForFirstTime;
                        if ( $temporaryFirstAmount < 0 ){
                            $temporaryFirstAmount = 0;
                        }
                        if ( $temporaryFirstAmount == 0 ){
                            // If Trial/First Payment becomes free
                            if ( !empty( $this->paymentRules['canApplyCouponOnRecurringForFirstFreePayment'] ) ){
                                $firstAmount = $temporaryFirstAmount;
                                $firstDiscount = $temporaryDiscountForFirstTime;
                            }
                        } else if ( !empty( $this->paymentRules['canApplyCouponOnRecurringForFirstPayment'] ) ){
                              // If Trial/First Payment charge required
                            $firstAmount = $temporaryFirstAmount;
                            $firstDiscount = $temporaryDiscountForFirstTime;
                        }
                    }
                    $couponApplied = true;
                } else if ( !empty( $this->paymentRules['canApplyCouponOnRecurringForFirstPayment'] ) && empty( $couponData['reccuring'] ) ) {
                    // On Memberships with Billing Recurrence apply the Discount: Just Once
                    // Only for payments that supports first payment with a different amount than the rest.

                    if ( isset( $firstAmount ) ){
                         // If Trial/First Payment exist apply Coupon
                        $temporaryDiscount = $couponObject->getDiscountValue( $firstAmount );
                        $temporaryAmount = $firstAmount - $temporaryDiscount;
                    } else {
                         // Create First Amount
                        $initialfirstAmount =  $amount;
                        $temporaryDiscount = $couponObject->getDiscountValue( $amount );
                        $temporaryAmount = $amount - $temporaryDiscount;
                        ///!!!! - Trial/First payment Interval must be created based on default Subscription intervals

                        $temporary = $this->getIntervalValues( $this->levelData['access_regular_time_value'], $this->levelData['access_regular_time_type'], $this->intervalTrialRules );
                        if ( isset( $temporary['intervalValue'] ) && isset( $temporary['intervalSymbol'] ) ){
                            $trialInterval = [
                                      'firstPaymentIntervalValue'   => $temporary['intervalValue'],
                                      'firstPaymentIntervalSymbol'  => $temporary['intervalSymbol'],
                                      'trialType'                   => 'certainPeriod',
                            ];
                        }

                    }

                    if ( $temporaryAmount < 0 ){
                        $temporaryAmount = 0;
                    }

                    if ( $temporaryAmount == 0 ){
                       // If Trial/First Payment becomes free
                        if ( !empty( $this->paymentRules['canApplyCouponOnRecurringForFirstFreePayment'] ) ){
                            $firstAmount = $temporaryAmount;
                            $firstDiscount = $temporaryDiscount;
                            $couponApplied = true;
                        }
                    } else if ( !empty( $this->paymentRules['canDoTrialPaid'] ) ){
                        // If Trial/First Payment charge required
                        $firstAmount = $temporaryAmount;
                        $firstDiscount = $temporaryDiscount;
                        $couponApplied = true;
                    }

                }
            }
        }

        // If Taxes is On: Calculate taxes for First Payment
        if ( isset( $firstAmount ) && $firstAmount !== false ){
            $taxesForFirstAmountDetails = $this->getTaxes( $firstAmount );
            $firstAmount = isset( $taxesForFirstAmountDetails['amount'] ) ? $taxesForFirstAmountDetails['amount'] : $firstAmount;
            $taxesForFirstAmount = isset( $taxesForFirstAmountDetails['total'] ) ? $taxesForFirstAmountDetails['total'] : 0;
            $firstAmount = $firstAmount + $taxesForFirstAmount;
        }

        // If Taxes is On: Calculate taxes for main Subscription amount
        $taxesDetails = $this->getTaxes( $amount );
        $taxes = isset( $taxesDetails['total'] ) ? $taxesDetails['total'] : 0;
        $amount = isset( $taxesDetails['amount'] ) ? $taxesDetails['amount'] : $amount;
        $initialPrice = isset( $taxesDetails['amount'] ) ? $taxesDetails['amount'] : $initialPrice;
        $dynamicPrice = isset( $taxesDetails['amount'] ) ? $taxesDetails['amount'] : $dynamicPrice;
        $amount = $amount + $taxes;

        $this->paymentOutputData = [
                                      'uid'                         => $this->inputData['uid'],
                                      'customer_email'              => \Ihc_Db::user_get_email( $this->inputData['uid'] ),
                                      'customer_name'               => \Ihc_Db::getUserFulltName( $this->inputData['uid'] ),
                                      'lid'                         => $this->inputData['lid'],
                                      'level_label'                 => isset( $this->levelData['label'] ) ? $this->levelData['label'] : '',
                                      'level_description'           => isset( $this->levelData['short_description'] ) ? $this->levelData['short_description'] : '',
                                      'initial_price'               => $initialPrice,
                                      'amount'                      => $amount,
                                      'base_price'                  => $amount - $taxes,
                                      'discount_value'              => isset( $discount ) ? $discount : false,
                                      'currency'                    => $this->inputData['currency'],
                                      'taxes'                       => $taxes,
                                      'taxes_details'               => $taxesDetails,
                                      'dynamic_price'               => isset( $dynamicPrice ) ? $dynamicPrice : false,
                                      'dynamic_price_used'          => isset( $dynamicPriceUsed ) ? $dynamicPriceUsed : false,
                                      'coupon_used'                 => isset( $this->inputData['ihc_coupon'] ) ? $this->inputData['ihc_coupon'] : '',
                                      'initial_first_amount'        => isset( $initialfirstAmount ) ? $initialfirstAmount : false,
                                      'first_amount'                => isset( $firstAmount ) ? $firstAmount : false, // used for trial/coupon
                                      'first_amount_taxes'          => isset( $taxesForFirstAmount ) ? $taxesForFirstAmount : false,
                                      'first_amount_taxes_details'  => isset( $taxesForFirstAmountDetails ) ? $taxesForFirstAmountDetails : false,
                                      'first_discount'              => isset( $firstDiscount ) ? $firstDiscount : false,
                                      'is_recurring'                => true,
                                      'interval_value'              => $intervals['intervalValue'],
                                      'interval_type'               => $intervals['intervalSymbol'],
                                      'subscription_cycles_limit'   => $intervals['subscriptionCyclesLimit'],
                                      'couponApplied'               => $couponApplied,
                                      'key'                         => strtolower(md5($this->inputData['uid'].$this->inputData['lid'].date( 'Y-m-d H:i:s' ).uniqid( 'ihc', true ))),
        ];

		    //Set Trial/First Payment if exist
        if ( isset( $trialInterval['firstPaymentIntervalSymbol'] ) && isset( $trialInterval['firstPaymentIntervalValue'] ) && isset( $trialInterval['trialType'] ) ){

            $this->paymentOutputData['is_trial'] = $isTrial;

            // since version 10.5 - prorate
            if ( isset( $this->levelData['prorate_from'] ) && $this->levelData['prorate_from'] !== ''
            && isset( $this->levelData['prorate_force_trial'] ) && $this->levelData['prorate_force_trial'] !== '' ){
                unset( $this->paymentOutputData['is_trial'] );
            }

            $this->paymentOutputData['trial_type'] = $trialInterval['trialType'];
            $this->paymentOutputData['first_payment_interval_value'] = $trialInterval['firstPaymentIntervalValue'];
            $this->paymentOutputData['first_payment_interval_type'] = $trialInterval['firstPaymentIntervalSymbol'];
        }

        // if the amount is 0, the system will stop the process before redirect to payment, and make the order and level completed.
        if ( $this->paymentOutputData['amount'] == 0 || $this->paymentOutputData['amount'] == 0.00 ){
            $this->paymentOutputData['no_charge'] = true;
        }

        return $this;
    }

	/**
	* Reeturns Payment details after have been calculated via preparePayment. Those details can be used into Cart section.
     * @param none
     * @return array
     */
    public function getPaymentOutputData()
    {
        return $this->paymentOutputData;
    }


    /**
	 * Establish Interval period for main Periof of recurring Subscription. Triggered in preparePaymentRecurring()
     * @param int
     * @param string
     * @param array
     * @return array
     */
    public function getIntervalValues( $intervalValue=0, $intervalType='', $rules=[] )
    {
        $data['intervalValue'] = $intervalValue;
        switch ( $intervalType ){
          case 'D':
            if ( empty( $rules['daysSupport'] ) ){
                $data = $this->convertInterval( 'D', $intervalValue, $rules );
            } else {
                if ( $rules['daysMinLimit'] != '' && $data['intervalValue'] < $rules['daysMinLimit'] ){
                    $data['intervalValue'] = $rules['daysMinLimit'];
                }
                if ( $rules['daysMaxLimit'] != '' && $data['intervalValue'] > $rules['daysMaxLimit'] ){
                    $data['intervalValue'] = $rules['daysMaxLimit'];
                }
                $data['intervalSymbol'] = $rules['daysSymbol'];
            }
            break;
          case 'W':
            if ( empty( $rules['weeksSupport'] ) ){
                $data = $this->convertInterval( 'W', $intervalValue, $rules );
            } else {
                if ( $rules['weeksMinLimit'] != '' && $data['intervalValue'] < $rules['weeksMinLimit'] ){
                    $data['intervalValue'] = $rules['weeksMinLimit'];
                }
                if ( $rules['weeksMaxLimit'] != '' && $data['intervalValue'] > $rules['weeksMaxLimit'] ){
                    $data['intervalValue'] =$rules['weeksMaxLimit'];
                }
                $data['intervalSymbol'] = $rules['weeksSymbol'];
            }
            break;
          case 'M':
            if ( empty( $rules['monthsSupport'] ) ){
                $data = $this->convertInterval( 'M', $intervalValue, $rules );
            } else {
                if ( $rules['monthsMinLimit'] != '' && $data['intervalValue'] < $rules['monthsMinLimit'] ){
                    $data['intervalValue'] = $rules['monthsMinLimit'];
                }
                if ( $rules['monthsMaxLimit'] != '' && $data['intervalValue'] > $rules['monthsMaxLimit'] ){
                    $data['intervalValue'] = $rules['monthsMaxLimit'];
                }
                $data['intervalSymbol'] = $rules['monthsSymbol'];
            }
            break;
          case 'Y':
            if ( empty( $rules['yearsSupport'] ) ){
                $data = $this->convertInterval( 'Y', $intervalValue, $rules );
            } else {
                if ( $rules['yearsMinLimit'] != '' && $data['intervalValue'] < $rules['yearsMinLimit'] ){
                    $data['intervalValue'] = $rules['yearsMinLimit'];
                }
                if ( $rules['yearsMaxLimit'] != '' && $data['intervalValue'] > $rules['yearsMaxLimit'] ){
                    $data['intervalValue'] = $rules['yearsMaxLimit'];
                }
                $data['intervalSymbol'] = $rules['yearsSymbol'];
            }
            break;
        }
        return $data;
    }

    /**
	 * Establish Interval period for Trial/First Payment of recurring Subscription if a such exist. Triggered in preparePaymentRecurring()
     * @param none
     * @return array
     */
    public function getTrialInterval( $trialAmount=0, $accessTrialType='', $intervalTyppe='', $intervalValue=0 )
    {
        if ( empty( $this->paymentRules['canDoTrial'] ) ){
            return false;
        }
        if ( empty( $this->paymentRules['canDoTrialFree'] ) && ( $trialAmount == 0 || $trialAmount == 0.00 ) ){
            return false;
        }

        if ( $this->levelData['access_trial_type'] == 1 && $this->intervalTrialRules['supportCertainPeriod'] ){
            // certain period

            $temporary = $this->getIntervalValues( $this->levelData['access_trial_time_value'], $this->levelData['access_trial_time_type'], $this->intervalTrialRules );
            if ( isset( $temporary['intervalValue'] ) && isset( $temporary['intervalSymbol'] ) ){
                $data = [
                          'firstPaymentIntervalValue'   => $temporary['intervalValue'],
                          'firstPaymentIntervalSymbol'  => $temporary['intervalSymbol'],
                          'trialType'                   => 'certainPeriod',
                ];
            }
        } else if ( $this->levelData['access_trial_type'] == 2 && $this->intervalTrialRules['supportCycles'] ){
            // couple cycles
            $data = [
                      'firstPaymentIntervalValue'    => $this->levelData['access_trial_couple_cycles'],
                      'firstPaymentIntervalSymbol'   => '',
                      'trialType'                    => 'coupleOfCycles',
            ];
            if ( $this->intervalTrialRules['cyclesMinLimit'] != '' && $data['firstPaymentIntervalValue'] > $this->intervalTrialRules['cyclesMinLimit'] ){
                $data['firstPaymentIntervalValue'] = $this->intervalTrialRules['cyclesMinLimit'];
            }
            if ( $this->intervalTrialRules['cyclesMaxLimit'] != '' && $this->intervalTrialRules['cyclesMaxLimit'] < $data['firstPaymentIntervalValue'] ){
                $data['firstPaymentIntervalValue'] = $this->intervalTrialRules['cyclesMaxLimit'];
            }
        } else if ( $this->levelData['access_trial_type'] == 2  && !$this->intervalTrialRules['supportCycles'] && $this->intervalTrialRules['supportCertainPeriod'] ){
            // force couple of cycles to certain period type

            $temporary = $this->getIntervalValues( $this->levelData['access_regular_time_value'], $this->levelData['access_regular_time_type'], $this->intervalTrialRules );
            if ( isset( $temporary['intervalValue'] ) && isset( $temporary['intervalSymbol'] ) ){
                $data = [
                          'firstPaymentIntervalValue'   => $temporary['intervalValue'] * $this->levelData['access_trial_couple_cycles'],
                          'firstPaymentIntervalSymbol'  => $temporary['intervalSymbol'],
                          'trialType'                   => 'certainPeriod',
                ];
            }
        } else if ( $this->levelData['access_trial_type'] == 1  && $this->intervalTrialRules['supportCycles'] && !$this->intervalTrialRules['supportCertainPeriod'] ){
            // payment gateway supports cycles but level is set to certain period. we force to a single cycle trial
            $data = [
                      'firstPaymentIntervalValue'    => 1,
                      'firstPaymentIntervalSymbol'   => '',
                      'trialType'                    => 'coupleOfCycles',
            ];
        }

        if ( empty( $data ) ){
            return [];
        }
        return $data;
    }



    /**
	   * If Subscription setup period is not supported by Payment service is converted to an eligible format.
     * this method will convert days to weeks, months, etc. weeks to days, months, etc. ...
     * @param string
     * @param int
     * @param array
     * @return array
     */
    public function convertInterval( $currentType='', $currentValue=0, $rules=[] )
    {
        switch ( $currentType ){
            case 'D': ////// days to weeks, months or years
              // weeks
              if ( !empty( $rules['weeksSupport'] ) ){
                  $data['intervalSymbol'] = $rules['weeksSymbol'];//'W';
                  $data['intervalValue'] = $currentValue / 7;
                  if ( isset( $rules['weeksMinLimit'] ) && $rules['weeksMinLimit'] != '' && $data['intervalValue'] < $rules['weeksMinLimit'] ){
                      $data['intervalValue'] = $rules['weeksMinLimit'];
                  }
                  if ( isset( $rules['weeksMaxLimit'] ) && $rules['weeksMaxLimit'] != '' && $data['intervalValue'] > $rules['weeksMaxLimit'] ){
                      $data['intervalValue'] = $rules['weeksMaxLimit'];
                  }
              }
              // months
              if ( empty( $data ) && !empty( $rules['monthsSupport'] ) ){
                  $data['intervalSymbol'] = $rules['monthsSymbol'];//'M';
                  $data['intervalValue'] = $currentValue / 30;
                  if ( isset( $rules['monthsMinLimit'] ) && $rules['monthsMinLimit'] != '' && $data['intervalValue'] < $rules['monthsMinLimit'] ){
                      $data['intervalValue'] = $rules['monthsMinLimit'];
                  }
                  if ( isset( $rules['monthsMaxLimit'] ) && $rules['monthsMaxLimit'] != '' && $data['intervalValue'] > $rules['monthsMaxLimit'] ){
                      $data['intervalValue'] = $rules['monthsMaxLimit'];
                  }
              }
              // years
              if ( empty( $data ) && !empty( $rules['yearsSupport'] ) ){
                  $data['intervalSymbol'] = $rules['yearsSymbol'];//'Y';
                  $data['intervalValue'] = $currentValue / 365;
                  if ( isset( $rules['yearsMinLimit'] ) && $rules['yearsMinLimit'] != '' && $data['intervalValue'] < $rules['yearsMinLimit'] ){
                      $data['intervalValue'] = $rules['yearsMinLimit'];
                  }
                  if ( isset( $rules['yearsMaxLimit'] ) && $rules['yearsMaxLimit'] != '' && $data['intervalValue'] > $rules['yearsMaxLimit'] ){
                      $data['intervalValue'] = $rules['yearsMaxLimit'];
                  }
              }
              break;
            case 'W': ////// weeks to days, months or years
              // days
              if ( !empty( $rules['daysSupport'] ) ){
                  $data['intervalSymbol'] = $rules['daysSymbol'];//'D';
                  $data['intervalValue'] = $currentValue * 7;
                  if ( isset( $rules['daysMinLimit'] ) && $rules['daysMinLimit'] != '' && $data['intervalValue'] < $rules['daysMinLimit'] ){
                      $data['intervalValue'] = $rules['daysMinLimit'];
                  }
                  if ( isset( $rules['daysMaxLimit'] ) && $rules['daysMaxLimit'] != '' && $data['intervalValue'] > $rules['daysMaxLimit'] ){
                      $data['intervalValue'] = $rules['daysMaxLimit'];
                  }
              }
              // months
              if ( empty( $data ) && !empty( $rules['monthsSupport'] ) ){
                  $data['intervalSymbol'] = $rules['monthsSymbol'];//'M';
                  $data['intervalValue'] = $currentValue / 4;
                  if ( isset( $rules['monthsMinLimit'] ) && $rules['monthsMinLimit'] != '' && $data['intervalValue'] < $rules['monthsMinLimit'] ){
                      $data['intervalValue'] = $rules['monthsMinLimit'];
                  }
                  if ( isset( $rules['monthsMaxLimit'] ) && $rules['monthsMaxLimit'] != '' && $data['intervalValue'] > $rules['monthsMaxLimit'] ){
                      $data['intervalValue'] = $rules['monthsMaxLimit'];
                  }
              }
              // years
              if ( empty( $data ) && !empty( $rules['yearsSupport'] ) ){
                  $data['intervalSymbol'] = $rules['yearsSymbol'];//'Y';
                  $data['intervalValue'] = $currentValue / 52;
                  if ( isset( $rules['yearsMinLimit'] ) && $rules['yearsMinLimit'] != '' && $data['intervalValue'] < $rules['yearsMinLimit'] ){
                      $data['intervalValue'] = $rules['yearsMinLimit'];
                  }
                  if ( isset( $rules['yearsMaxLimit'] ) && $rules['yearsMaxLimit'] != '' && $data['intervalValue'] > $rules['yearsMaxLimit'] ){
                      $data['intervalValue'] = $rules['yearsMaxLimit'];
                  }
              }
              break;
            case 'M': ////// moths to days, weeks or years
              // days
              if ( !empty( $rules['daysSupport'] ) ){
                  $data['intervalSymbol'] = $rules['daysSymbol'];//'D';
                  $data['intervalValue'] = $currentValue * 30;
                  if ( isset( $rules['daysMinLimit'] ) && $rules['daysMinLimit'] != '' && $data['intervalValue'] < $rules['daysMinLimit'] ){
                      $data['intervalValue'] = $rules['daysMinLimit'];
                  }
                  if ( isset( $rules['daysMaxLimit'] ) && $rules['daysMaxLimit'] != '' && $data['intervalValue'] > $rules['daysMaxLimit'] ){
                      $data['intervalValue'] = $rules['daysMaxLimit'];
                  }
              }
              // weeks
              if ( empty( $data ) && !empty( $rules['weeksSupport'] ) ){
                  $data['intervalSymbol'] = $rules['weeksSymbol'];//'W';
                  $data['intervalValue'] = $currentValue * 4;
                  if ( isset( $rules['weeksMinLimit'] ) && $rules['weeksMinLimit'] != '' && $data['intervalValue'] < $rules['weeksMinLimit'] ){
                      $data['intervalValue'] = $rules['weeksMinLimit'];
                  }
                  if ( isset( $rules['weeksMaxLimit'] ) && $rules['weeksMaxLimit'] != '' && $data['intervalValue'] > $rules['weeksMaxLimit'] ){
                      $data['intervalValue'] = $rules['weeksMaxLimit'];
                  }
              }
              // years
              if ( empty( $data ) && !empty( $rules['yearsSupport'] ) ){
                  $data['intervalSymbol'] = $rules['yearsSymbol'];//'Y';
                  $data['intervalValue'] = $currentValue / 12;
                  if ( isset( $rules['yearsMinLimit'] ) && $rules['yearsMinLimit'] != '' && $data['intervalValue'] < $rules['yearsMinLimit'] ){
                      $data['intervalValue'] = $rules['yearsMinLimit'];
                  }
                  if ( isset( $rules['yearsMaxLimit'] ) && $rules['yearsMaxLimit'] != '' && $data['intervalValue'] > $rules['yearsMaxLimit'] ){
                      $data['intervalValue'] = $rules['yearsMaxLimit'];
                  }
              }
              break;
            case 'Y': ////// years to days, weeks or months
              // days
              if ( !empty( $rules['daysSupport'] ) ){
                  $data['intervalSymbol'] = $rules['daysSymbol'];//'D';
                  $data['intervalValue'] = $currentValue * 365;
                  if ( isset( $rules['daysMinLimit'] ) && $rules['daysMinLimit'] != '' && $data['intervalValue'] < $rules['daysMinLimit'] ){
                      $data['intervalValue'] = $rules['daysMinLimit'];
                  }
                  if ( isset( $rules['daysMaxLimit'] ) && $rules['daysMaxLimit'] != '' && $data['intervalValue'] > $rules['daysMaxLimit'] ){
                      $data['intervalValue'] = $rules['daysMaxLimit'];
                  }
              }
              // weeks
              if ( empty( $data ) && !empty( $rules['weeksSupport'] ) ){
                  $data['intervalSymbol'] = $rules['weeksSymbol'];//'W';
                  $data['intervalValue'] = $currentValue * 52;
                  if ( isset( $rules['weeksMinLimit'] ) && $rules['weeksMinLimit'] != '' && $data['intervalValue'] < $rules['weeksMinLimit'] ){
                      $data['intervalValue'] = $rules['weeksMinLimit'];
                  }
                  if ( isset( $rules['weeksMaxLimit'] ) && $rules['weeksMaxLimit'] != '' && $data['intervalValue'] > $rules['weeksMaxLimit'] ){
                      $data['intervalValue'] = $rules['weeksMaxLimit'];
                  }
              }
              // months
              if ( empty( $data ) && !empty( $rules['monthsSupport'] ) ){
                  $data['intervalSymbol'] = $rules['monthsSymbol'];//'M';
                  $data['intervalValue'] = $currentValue * 12;
                  if ( isset( $rules['monthsMinLimit'] ) && $rules['monthsMinLimit'] != '' && $data['intervalValue'] < $rules['monthsMinLimit'] ){
                      $data['intervalValue'] = $rules['monthsMinLimit'];
                  }
                  if ( isset( $rules['monthsMaxLimit'] ) && $rules['monthsMaxLimit'] != '' && $data['intervalValue'] > $rules['monthsMaxLimit'] ){
                      $data['intervalValue'] = $rules['monthsMaxLimit'];
                  }
              }
              break;
        }
        if ( empty( $data ) ){
            return [];
        }
        $data['intervalValue'] = (int)$data['intervalValue'];
        return $data;
    }

    /**
	* Check if Subscription is free or charge. If yes, will become automatically Active once is assigned to User
     * Check if current level is free.
     * @param none
     * @return bool
     */
    protected function isLevelFree()
    {
        $isFree = false;
        if ( $this->levelData['payment_type'] == 'free' || $this->levelData['price'] == '' || !$this->levelData['price'] ){
            $isFree = true;
        }
        return $isFree;
    }

    /**
	* Check if is about recurring Subscription or one-time Membership
     * @param none
     * @return bool
     */
    protected function isRecurringLevel()
    {
        if (isset($this->levelData['access_type']) && $this->levelData['access_type']=='regular_period'){
            return true;
        }
        return false;
    }

    /**
	  * Calculates Taxes for current Subscription based on chosen Country if those exists
    * @param float
    * @return array
    */
    protected function getTaxes( $amount=0 )
    {
        $country = (isset($this->inputData['ihc_country'])) ? $this->inputData['ihc_country'] : '';
        $state = (isset($this->inputData['ihc_state'])) ? $this->inputData['ihc_state'] : '';
        if ( $amount > 0 ){
            $taxes = new \Indeed\Ihc\Db\TaxesForTransaction();
            return $taxes->setAmount( $amount )
                         ->setCountry( $country )
                         ->setState( $state )
                         ->setCurrency( $this->inputData['currency'] )
                         ->getAll();
        }
        return 0;
    }

    /**
	   * Check if a dynamic Price have been submitted and replace the default Subscription amount value
     * Updated on v.10.1: Will return the altered amount and what dynamic price have been used.
     *
    * @param int or float
    * @return int or float
    */
    protected function dynamicPrice( $amount=0 )
    {
        // dynamic price
        $dynamicPrceData = array();
        $dynamicPrceData = [
                  'amount'    => $amount,
                  'used'   => '',
        ];

        if ( !isset( $this->inputData['ihc_dynamic_price'] ) || $this->inputData['ihc_dynamic_price'] == '' ){
            return $dynamicPrceData;
        }
        $dynamicPriceObject = new \Indeed\Ihc\DynamicPrice();
        if ( $dynamicPriceObject->checkPrice( $this->inputData['lid'], $this->inputData['ihc_dynamic_price'] ) ){
            $dynamicPrceData['amount'] = $this->inputData['ihc_dynamic_price'];
            $dynamicPrceData['used'] = $this->inputData['ihc_dynamic_price'];

            \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(' : Dynamic price on - Amount is set by the user @ ', 'ihc') . $amount . $this->inputData['currency'], 'payments');
        }

        return $dynamicPrceData;
    }

    /**
	   * save Order into the system once the  payment have been Prepared and before Charging.
     * Updated on v.10.1: setCookieKey() step have been added
     *
     * @param none
     * @return object
     */
    public function saveOrder()
    {
		    do_action( 'ihc_action_before_save_order', $this->paymentOutputData );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Save Order process - Start.', 'ihc'), 'payments');

        if ( $this->stopProcess ){
            return $this;
        }
        $amount = isset( $this->paymentOutputData['amount'] ) ? $this->paymentOutputData['amount'] : 0;
        if ( isset( $this->paymentOutputData['first_amount'] ) && $this->paymentOutputData['first_amount'] !== false ){
            $amount = $this->paymentOutputData['first_amount'];
        }
        $orderData = [
            'uid'               => $this->inputData['uid'],
            'lid'               => $this->inputData['lid'],
            'amount_type'       => $this->paymentOutputData['currency'],
            'amount'            => $amount,
            'status'            => isset($this->inputData['status']) ? $this->inputData['status'] : 'pending',
            'ihc_payment_type'  => $this->paymentType,
            'extra_fields'      => isset( $this->inputData['extra_fields'] ) ? $this->inputData['extra_fields'] : '',
        ];
        $orderData = apply_filters( 'ihc_filter_payments_before_insert_order', $orderData );

        require_once IHC_PATH . 'classes/Orders.class.php';
        $object = new \Ump\Orders();
        $this->paymentOutputData['order_id'] = $object->do_insert( $orderData ); // before 12.5 $this->paymentOutputData['is_recurring']
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Save Order process - Order with Values: ' . serialize( $orderData ), 'ihc'), 'payments');
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Save Order process - Completed.', 'ihc'), 'payments');

        //save order metas
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Save Order process - Save Metas.', 'ihc'), 'payments');
        $exclude = [ 'order_id' ];
        $orderMetas = $this->paymentOutputData;
        $orderMetas['ihc_payment_type'] = $this->paymentType;
        foreach ( $orderMetas as $metaKey => $metaValue ){
            if ( $metaValue === false || in_array( $metaKey, $exclude ) ){
                continue;
            }
            if ( is_array( $metaValue ) ){
                $metaValue = serialize( $metaValue );
            }
            $orderMeta->save( $this->paymentOutputData['order_id'], $metaKey, $metaValue );
        }
        $orderMeta->save( $this->paymentOutputData['order_id'], 'is_parent_order', 1 );
        // at this point we must increment the coupon use.
        $this->submitCoupon();

        //Setup Payment Key into Cookies for further use
        $this->setCookieKey();

		    do_action( 'ihc_action_before_after_order', $this->paymentOutputData );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Save Order process - Finish.', 'ihc'), 'payments');

        return $this;
    }

    /**
     * @param none
     * @return bool
     */
    protected function submitCoupon()
    {
      if ( !isset($this->paymentOutputData['coupon_used']) || $this->paymentOutputData['coupon_used'] == '' || empty($this->paymentOutputData['couponApplied']) ){
          return false;
      }
      $couponObject = new \Indeed\Ihc\Payments\Coupons();
      return $couponObject->setCode( $this->paymentOutputData['coupon_used']  )
                          ->setLid( $this->inputData['lid'] )
                          ->setUid( $this->inputData['uid'] )
                          ->submitCode();

    }

    /**
      * Set Cookie for buyer when his Order have been saved. Will be used for Thank Page template.
      * Added on v.10.1
      *
     * @param none
     * @return bool
     */
    protected function setCookieKey()
    {
      if ( ! headers_sent() ) {
          return  setcookie( 'ihc_payment', $this->paymentOutputData['key'], time() + 3600 * 1, COOKIEPATH, COOKIE_DOMAIN );
      }
      return false;
    }

    /**
	 * Charge wrapper. Charging process is triggered
     * @param none
     * @return object
     */
    public function chargePayment()
    {
        do_action( 'ihc_action_before_charge_payment', $this->paymentOutputData );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Charge Payment process - Start.', 'ihc'), 'payments');

        // free level
  		  if ( $this->isLevelFree() ){
  			  $this->completeLevelPayment( $this->paymentOutputData );
  			  \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Charge Payment process - Membership is free.', 'ihc'), 'payments');
  			  return $this;
  		  }

  		  if ( $this->stopProcess ){
  			  return $this;
  		  }

  		  // no charge
  		  if ( !empty( $this->paymentOutputData['no_charge'] ) ){
    			  \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Charge Payment process - No payment for this subscription.', 'ihc'), 'payments');
            $this->paymentOutputData['uid'] = $this->inputData['uid'];
            $this->paymentOutputData['lid'] = $this->inputData['lid'];
    			  $this->completeLevelPayment( $this->paymentOutputData );
    			  return $this;
  		  }

        $this->paymentOutputData['order_identificator'] = $this->inputData['uid'] . '_' . $this->inputData['lid'] . '_' . time() . '_' . rand(1, 100000);
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $this->paymentOutputData['order_identificator'] );

		    $this->charge();

		    do_action( 'ihc_action_after_charge_payment', $this->paymentOutputData );
        	\Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Charge Payment process - Finish.', 'ihc'), 'payments');

        return $this;
    }

    /**
	 * Rewrite this Method on each Payment service.
     * @param none
     * @return object
     */
    public function charge()
    {
        return $this;
    }

    /**
     * @param none
     * @return none
     */
    public function redirect()
    {
        if ( !empty( $this->redirectUrl ) ){
            header( 'location:' . $this->redirectUrl );
            exit;
        }
    }

    /**
	 * Webhook wrapper triggered when a Payment confirmation is received.
     * @param none
     * @return mixed
     */
    public function webhookPayment()
    {
         do_action( 'ihc_action_before_webhook' );
		     \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook - Start.', 'ihc'), 'payments');

        $this->webhook();// rewrite $this->webhookData

        \Ihc_User_Logs::set_user_id( (isset($this->webhookData['uid'])) ? $this->webhookData['uid'] : '' );
        \Ihc_User_Logs::set_level_id( (isset($this->webhookData['lid'])) ? $this->webhookData['lid'] : '' );

        $this->processingWebhookData();

        do_action( 'ihc_action_after_webhook' );
		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook  - Finish.', 'ihc'), 'payments');

        $this->webhookRedirect();// redirect if it's case
    }

    /**
	   * Rewrite this Method on each Payment service.
     * in this method you must create the $this->webhookData array with the details the came on webhook.
     * @param none
     * @return none
     */
    public function webhook()
    {
        echo esc_ump_content('============= Ultimate Membership Pro - PAYMENT WEBHOOK ============= ');
        echo esc_ump_content('<br/><br/>Payment Webhook is on test mode.');
        exit;
    }


    /**
      * @param none
      * @return none
      */
    public function processingWebhookData()
    {
        // check if level exists
        if ( !isset( $this->webhookData['lid'] ) || !\Ihc_Db::does_level_exists( $this->webhookData['lid'] ) ){
            return false;
        }
        // check if user exists
        if ( !isset( $this->webhookData['uid'] ) || !\Ihc_Db::does_user_exists( $this->webhookData['uid'] ) ){
            return false;
        }

		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Processing Webhook Data - Start.', 'ihc'), 'payments');

        if ( !isset( $this->webhookData['transaction_id'] ) || $this->webhookData['transaction_id'] == '' ){
            return false;
        }

        //force currency to uppercase
        $this->webhookData['currency'] = strtoupper( $this->webhookData['currency'] );

        switch ( $this->webhookData['payment_status'] ){
            case 'completed':

                // STEP 1. check the transaction id if it's completed
                \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Processing Webhook Data - Check Order based on transaction_id: ', 'ihc'). $this->webhookData['transaction_id'], 'payments');
                $orderId = $this->webhookDoesTransactionExists( $this->webhookData['transaction_id'] );
                //check if order exists for this transaction
                if ( $orderId && $this->webhookIsTransactionCompleted( $this->webhookData['transaction_id'] ) ){
                   \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(' Processing Webhook Data - This order has already been completed. Exit.', 'ihc'), 'payments');
                   return;
                }

                // we search for txn_id in order metas. it's case when the plugin update was made between transaction.
                if ( empty( $orderId ) ){
                    $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                    $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'txn_id', $this->webhookData['transaction_id'] );
                }

                // STEP 2. check the order identificator
                if ( empty( $orderId ) ){
                    $orderId = $this->webhookGetOrderIdByOrderIdentificator( $this->webhookData['order_identificator'] );
                }

                if ( empty( $orderId ) || ( $orderId && $this->webhookIsOrderCompleted( $orderId ) ) ){
                    // insert new order
                    $orderData = [
                          'transaction_id'              => $this->webhookData['transaction_id'],
                          'order_identificator'         => $this->webhookData['order_identificator'],
                          'uid'                         => $this->webhookData['uid'],
                          'lid'                         => $this->webhookData['lid'],
                          'amount'                      => $this->webhookData['amount'],
                          'currency'                    => $this->webhookData['currency'],
                    ];
                    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Processing Webhook Data - No Order found. Insert a new one.', 'ihc').json_encode( $orderData), 'payments');
                    $orderId = $this->webhookInsertOrder( $orderData );
                }

      				  if ( empty( $orderId ) ){
      					    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Processing Webhook Data - No Order exist or Inserted. Exit.', 'ihc'), 'payments');
      					    return false;
      				  }

                if ( isset( $this->webhookData['payment_details'] ) && $this->webhookData['payment_details'] && is_array( $this->webhookData['payment_details'] ) ){
                    $paymentData = array_merge( $this->webhookData['payment_details'], [
                                        'uid'               => $this->webhookData['uid'],
                                        'lid'               => $this->webhookData['lid'],
                                        'order_id'          => $orderId,
                                        'transaction_id'    => $this->webhookData['transaction_id'],
                    ]);
                } else {
                    $paymentData = [
                                        'uid'               => $this->webhookData['uid'],
                                        'lid'               => $this->webhookData['lid'],
                                        'order_id'          => $orderId,
                                        'transaction_id'    => $this->webhookData['transaction_id'],
                    ];
                }

                // save subscription id into order meta if it's set
                if ( isset( $this->webhookData['subscription_id'] ) ){
                    $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                    $orderMeta->save( $orderId, 'subscription_id', $this->webhookData['subscription_id'] );
                }

                // save customer id into oder meta if its set - since version 12.6.2
                if ( isset( $this->webhookData['customer_id'] ) ){
                    $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                    $orderMeta->save( $orderId, 'customer_id', $this->webhookData['customer_id'] );
                }
                // end of 12.6.2

                \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Processing Webhook Data - Finish - Call completeLevelPayment.', 'ihc'), 'payments');
                $this->completeLevelPayment( $paymentData );
                break;
            case 'pending':

                break;
            case 'refund':
                \Indeed\Ihc\UserSubscriptions::deleteOne( $this->webhookData['uid'], $this->webhookData['lid'] );
                do_action( 'ump_paypal_user_do_refund', $this->webhookData['uid'], $this->webhookData['lid'], $this->webhookData['transaction_id'] );
                break;
            case 'failed':
                \Indeed\Ihc\UserSubscriptions::deleteOne( $this->webhookData['uid'], $this->webhookData['lid'] );
                break;
            case 'cancel':
              $this->webhookDoCancel( $this->webhookData['uid'], $this->webhookData['lid'] );
              break;
            default:

                break;
        }
    }

    public function webhookIsOrderCompleted( $orderId = 0 )
    {
      return false;
        if ( !$orderId ){
            return false;
        }
        $orderObject = new \Indeed\Ihc\Db\Orders();
        $status = $orderObject->setId( $orderId )
                    ->fetch()
                    ->getStatus();
        if ( $status === 'Completed' ){
            return true;
        }
        return false;
    }

    /**
     * @param string
     * @return mixed int or bool
     */
    public function webhookGetOrderIdByOrderIdentificator( $orderIdentificator='' )
    {
        // does order_identificator exists in database
        if ( empty( $orderIdentificator ) ){
            return false;
        }
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );
        if ( !$orderId ){
            return false; /// must insert new order
        }
        $orders = new \Indeed\Ihc\Db\Orders();
        $orderStatus = $orders->setId( $orderId )->fetch()->getStatus();
        if ( $orderStatus == 'Completed' ){
            return false; /// order for order_identificator is completed so we must insert new order
        } else {
            return $orderId; // this order is not completed
        }
    }

    /**
     * @param array
     * @return int ( order id )
     */
    public function webhookInsertOrder( $params=[] )
    {
        if ( !$params ){
            return false;
        }
        $orderData = [
            'uid'               => $params['uid'],
            'lid'               => $params['lid'],
            'amount'            => $params['amount'],
            'amount_type'       => $params['currency'],
            'status'            => 'pending',
            'ihc_payment_type'  => $this->paymentType,
            'extra_fields'      => '',
        ];
        require_once IHC_PATH . 'classes/Orders.class.php';
        $object = new \Ump\Orders();
        $orderId = $object->do_insert( $orderData, 1); // 1 since version 12.5.before was $this->isRecurringLevel()

		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook Insert Order - New Order ID is: ', 'ihc'). $orderId, 'payments');

        // get all metas from parent and save them
    		$orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $parentOrderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $params['order_identificator'] );
        if ( !$parentOrderId ){
			     \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook Insert Order - No Parent Order have been found. Return ', 'ihc'), 'payments');
           return $orderId;
        }
        $metas = $orderMeta->getAllByOrderId( $parentOrderId ); ///!!!!!!!! DOES NOT EXIST
        if ( !$metas ){
			      \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook Insert Order - No Metas for Parent Order have been found. Return', 'ihc'), 'payments');
            return $orderId;
        }
		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook Insert Order - Save additional Order Metas: ', 'ihc').json_encode( $metas), 'payments');
        $exclude = [  'order_id', 'order_identificator', 'transaction_id', 'txn_id', 'is_trial', 'is_parent_order', 'code', 'ihc_payment_type' ];
        foreach ( $metas as $metaKey => $metaValue ){
            if ( in_array( $metaKey, $exclude ) || $metaValue === false ){
                continue;
            }
            $orderMeta->save( $orderId, $metaKey, $metaValue );
        }

        // save order_identificator as order_referrence
        $orderMeta->save( $orderId, 'order_referrence', $parentOrderId );

		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Webhook Insert Order - Finish ', 'ihc'), 'payments');
        return $orderId;
    }

    /**
     * @param string
     * @param int
     */
    public function webhookDoesTransactionExists( $transactionId='' )
    {
        if ( empty( $transactionId ) ){
            return false;
        }
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId ){
            return $orderId;
        }
        return 0;
    }

    /**
     * @param string
     * @param bool
     */
    public function webhookIsTransactionCompleted( $transactionId='' )
    {
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        $orders = new \Indeed\Ihc\Db\Orders();
        $orderStatus = $orders->setId( $orderId )->fetch()->getStatus();
        if ( $orderStatus == 'Completed' ){
            return true;
        }
        return false;
    }

    /**
     * @param int
     * @param int
     * @return bool
     */
    public function refundSubscription( $uid=0, $lid=0 )
    {
        $deleteLevelForUser = apply_filters( 'ihc_filter_delete_level_for_user_on_payment_refund', true, $uid, $lid );
		    do_action( 'ihc_action_payments_before_refund', $uid, $lid );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Refund Subscription - Start.', 'ihc'), 'payments');
        if ( $deleteLevelForUser ){
            \Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
        }
        do_action( 'ihc_action_payments_after_refund', $uid, $lid );
        $this->afterRefund( $uid, $lid );
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function webhookDoCancel( $uid=0, $lid=0 )
    {
        $statusData = \Indeed\Ihc\UserSubscriptions::getStatus( $uid, $lid );
        $status = isset( $statusData['status'] ) ? $statusData['status'] : 1;
        if ( $status !== 0 ){
            \Indeed\Ihc\UserSubscriptions::updateStatus( $uid, $lid, 0 );
            do_action( 'ihc_action_after_cancel_subscription', $uid, $lid );
        }

      }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function afterRefund( $uid=0, $lid=0 )
    {
		    return;
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function paymentFailed( $uid=0, $lid=0 )
    {
        $deleteLevelForUser = apply_filters( 'ihc_filter_delete_level_for_user_on_payment_failed', true,  $uid, $lid );
        do_action( 'ihc_action_payment_failed', $uid, $lid );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Payment Failed.', 'ihc'), 'payments');

        if ( $deleteLevelForUser ){
            \Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
        }
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function cancelSubscription( $uid=0, $lid=0, $transactionId='' )
    {
    	  $cancelSubscription = apply_filters( 'ihc_filter_cancel_subscription', true, $uid, $lid );
        do_action( 'ihc_action_payments_before_cancel', $uid, $lid );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Trigger cancel Subscription.', 'ihc'), 'payments');

        if ( $cancelSubscription ){
            do_action( 'ihc_action_cancel_subscription', $uid, $lid );
            return $this->cancel( $uid, $lid, $transactionId );
        }
		    return false;
    }

    /**
	   * Rewrite this method on each payment type if is necessary
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function cancel( $uid=0, $lid=0, $transactionId='' )
    {
		    return;
    }

    /**
	   * Rewrite this method on each payment type if is necessary
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function canDoCancel( $uid=0, $lid=0, $transactionId='' )
    {
        return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function pauseSubscription( $uid=0, $lid=0, $transactionId='' )
    {
    	  $pauseSubscription = apply_filters( 'ihc_filter_pause_subscription', true, $uid, $lid );
        do_action( 'ihc_action_payments_before_pause', $uid, $lid );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Trigger Pause Subscription.', 'ihc'), 'payments');

        if ( $pauseSubscription ){
            do_action( 'ihc_action_pause_subscription', $uid, $lid );
            return $this->pause( $uid, $lid, $transactionId );
        }
  	    return false;
    }

    /**
     * Rewrite this method on each payment type if is necessary
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function pause( $uid=0, $lid=0, $transactionId='' )
    {
  	    return;
    }

    /**
     * Rewrite this method on each payment type if is necessary
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function canDoPause( $uid=0, $lid=0, $transactionId='' )
    {
        return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function resumeSubscription( $uid=0, $lid=0, $transactionId='' )
    {
    	  $resumeSubscription = apply_filters( 'ihc_filter_resume_subscription', true, $uid, $lid );
        do_action( 'ihc_action_payments_before_resume', $uid, $lid );
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Trigger Resume Subscription.', 'ihc'), 'payments');

        if ( $resumeSubscription ){
            do_action( 'ihc_action_resume_subscription', $uid, $lid );
            return $this->resume( $uid, $lid, $transactionId );
        }
  	    return false;
    }

    /**
     * Rewrite this method on each payment type if is necessary
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function resume( $uid=0, $lid=0, $transactionId='' )
    {
  	    return;
    }


    /**
	   * Rewrite this method on each payment type if is necessary
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function canDoResume( $uid=0, $lid=0, $transactionId='' )
    {
        return false;
    }
    /*
    Mandatory $paymentData should cointain [
                       'uid'                           => 0,// @var int
                       'lid'                           => 0,// @var int
                       'order_id'                      => 0,// @var int
                       'transaction_id'                => '',// @var string
                       'amount_value'                  => 0.00,// @var float
                       'amount_type'                   => 'USD',// @var string
   ];
   */
      /**
     *
     * Updated on v.10.1: Check if paymentData has transaction_id in case of free payments.
     *
     * @param array
     * @return none
     */
    protected function completeLevelPayment( $paymentData=[] )
    {
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__( ': Complete Payment process - Start.', 'ihc'), 'payments');
        if ( !$paymentData['uid'] || $paymentData['lid'] < 0 ){
            return;
        }
        // set level data
        if ( empty( $this->levelData ) ){
            $this->inputData['lid'] = $paymentData['lid'];
            $this->setLevelData();
        }

        // Step 1: Update User Membership Expire time
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $isTrial = $orderMeta->get( $paymentData['order_id'], 'is_trial' );

        $makeCompleteArgs = [ 'payment_gateway' => $this->paymentType ];

        // since version 10.5 - prorate
        if ( isset( $paymentData['expire_time'] ) && $paymentData['expire_time'] !== '' ){
            $makeCompleteArgs['expire_time'] = date( 'Y-m-d H:i:s', $paymentData['expire_time'] );
        }

        if ( $isTrial ){
            // trial subscription
            \Indeed\Ihc\UserSubscriptions::makeComplete( $paymentData['uid'], $paymentData['lid'], true, $makeCompleteArgs );
        } else {
            // normal subscription
            \Indeed\Ihc\UserSubscriptions::makeComplete( $paymentData['uid'], $paymentData['lid'], false, $makeCompleteArgs );
        }

        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(": Complete Membership Payment - Updated User (".$paymentData['uid'].") Membership (".$paymentData['lid'].") expire time.", 'ihc'), 'payments');

        //Step 2: Make Order completed
        \Ihc_Db::updateOrderStatus( $paymentData['order_id'], 'Completed' );
        // add transaction_id to order meta
        if(isset($paymentData['transaction_id'])){
          $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
          $orderMeta->save( $paymentData['order_id'], 'transaction_id', $paymentData['transaction_id'] );

          //Step 3: Store Transaction !!!!!!!!!!!!!!!!!!!!!!!!!! DEPRECATED !!!!!!!!!!!!!!!!!!!!!!

          $IndeedMembersPayments = new \Indeed\Ihc\Db\IndeedMembersPayments();
          $IndeedMembersPayments->setTxnId( $paymentData['transaction_id'] )
                                ->setUid( $paymentData['uid'] )
                                ->setPaymentData( $paymentData )
                                ->setHistory( $paymentData )
                                ->setOrders( $paymentData['order_id'] )
                                ->save();
        }


        //Step 4: Extra Actions during Payment Complete process
        //Switch User WP Role based Automatically Switch Role option from Register form Settings.
        //ihc_switch_role_for_user( $paymentData['uid'] );

        /// Action on payment completed
        do_action( 'ihc_payment_completed', $paymentData['uid'], $paymentData['lid'], $this->levelData, $paymentData['order_id'] );// since version 12.5 adding $paymentData['order_id']
        // @description run on payment complete. @param user id (integer), level id (integer)
        \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(': Complete Membership Payment - Finish.', 'ihc'), 'payments');

    }

    /**
     * @param none
     * @return array
     */
    public function getPaymentRules()
    {
        return $this->paymentRules;
    }

    /**
     * @param none
     * @return array
     */
    public function getIntervalSubscriptionRules()
    {
        return $this->intervalSubscriptionRules;
    }

    /**
     * @param none
     * @return array
     */
    public function getIntervalTrialRules()
    {
        return $this->intervalTrialRules;
    }

    /**
     * @param none
     * @return array
     */
    public function getAllPaymentRules()
    {
        return array_merge( $this->paymentRules, $this->intervalSubscriptionRules, $this->intervalTrialRules );
    }

   /**
     * @param none
     * @return none
     */
    public function webhookRedirect()
    {
        switch ( $this->webhookData['payment_status'] ){
            case 'completed':
			         $this->webhookSuccessRedirect();
    			     break;
            case 'pending':
              $this->webhookSuccessRedirect();
              break;
      			case 'failed':
      			  $this->webhookFailRedirect();
      			  break;
            case 'other':
            default:
              $this->webhookSuccessRedirect();
              break;
		    }
    }

    /**
     * @param none
     * @return none
     */
    public function webhookSuccessRedirect()
    {
		    \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(' Payment Webhook: Success Redirect 200. Exit.', 'ihc'), 'payments');
        http_response_code(200);
        exit;
    }

    /**
     * @param none
     * @return none
     */
    public function webhookFailRedirect()
    {
		   \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__(' Payment Webhook: Fail Redirect 400. Exit.', 'ihc'), 'payments');
       http_response_code(400);
       exit;
    }

}
