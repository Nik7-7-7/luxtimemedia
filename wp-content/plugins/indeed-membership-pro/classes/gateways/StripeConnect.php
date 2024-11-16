<?php
namespace Indeed\Ihc\Gateways;

class StripeConnect extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'stripe_connect';
    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_stripe_connect', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_stripe_connect_success_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => 'ihc_stripe_connect_cancel_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => 'ihc_stripe_connect_locale_code', // option name ( in wp_option table ) where it's stored the language code.
    ];

    protected $intervalSubscriptionRules      = [
                    'daysSymbol'                    => 'day',
                    'weeksSymbol'                   => 'week',
                    'monthsSymbol'                  => 'month',
                    'yearsSymbol'                   => 'year',
                    'daysSupport'                   => true,
                    'daysMinLimit'                  => 1,
                    'daysMaxLimit'                  => 365,
                    'weeksSupport'                  => true,
                    'weeksMinLimit'                 => 1,
                    'weeksMaxLimit'                 => 52,
                    'monthsSupport'                 => true,
                    'monthsMinLimit'                => 1,
                    'monthsMaxLimit'                => 12,
                    'yearsSupport'                  => true,
                    'yearsMinLimit'                 => 1,
                    'yearsMaxLimit'                 => 1,
                    'maximumRecurrenceLimit'        => 52, // leave this empty for unlimited
                    'minimumRecurrenceLimit'        => 2,
                    'forceMaximumRecurrenceLimit'   => false,
    ];

    protected $intervalTrialRules             = [
                              'daysSymbol'               => 'day',
                              'weeksSymbol'              => '',
                              'monthsSymbol'             => '',
                              'yearsSymbol'              => '',
                              'supportCertainPeriod'     => true,
                              'supportCycles'            => false,
                              'cyclesMinLimit'           => 1,
                              'cyclesMaxLimit'           => '',
                              'daysSupport'              => true,
                              'daysMinLimit'             => 1,
                              'daysMaxLimit'             => 365,
                              'weeksSupport'             => false,
                              'weeksMinLimit'            => '',
                              'weeksMaxLimit'            => '',
                              'monthsSupport'            => false,
                              'monthsMinLimit'           => '',
                              'monthsMaxLimit'           => '',
                              'yearsSupport'             => false,
                              'yearsMinLimit'            => '',
                              'yearsMaxLimit'            => '',
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types
    protected $paymentTypeLabel               = 'Stripe'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];
    protected $multiply                       = '';
    private   $appFeePercentage               = 1;//

    /**
     * @param int
     * @param string ( since version 12.1 )
     * @return string
     */
    public function getAuthUrl( $liveMode=1, $returnUrl='' )
    {
        // since version 12.1, the return url become dynamic
        if ( $returnUrl === '' ){
            $returnUrl = admin_url( 'admin.php?page=ihc_manage&tab=payment_settings&subtab=stripe_connect' );
        }
        $baseUrl = 'https://portal.ultimatemembershippro.com/stripe-connect-auth/' . time() . '/';
        $site_url = site_url();
        $site_url = trailingslashit($site_url);
        $l = new \Indeed\Ihc\OldLogs();
        $lsn = $l->GCP();
        $params = [
                      'return_url'        => urlencode( $returnUrl ),
                      'webhook'           => urlencode( $site_url ),
                      'sandbox'           => $liveMode ? 0 : 1,
                      'code'              => wp_create_nonce( 'ihc_stripe_connect_auth' ),
                      'lsn'               => $lsn,
                      'v'                 => get_option( 'iump_'.'lnk'.'_'.'v'),
        ];

        $baseUrl = add_query_arg( $params, $baseUrl );
        return $baseUrl;
    }

    /**
     * @param int
     * @return string
     */
    public function getDeauthUrl( $liveMode=1 )
    {
        $baseUrl = 'https://portal.ultimatemembershippro.com/stripe-connect-deauth/' . time() . '/';
        $site_url = site_url();
        $site_url = trailingslashit( $site_url );
        $notify_url = add_query_arg( 'ihc_action', 'stripe_connect', $site_url );
        if ( $liveMode ){
            $accountId = get_option( 'ihc_stripe_connect_account_id' );
        } else {
            $accountId = get_option( 'ihc_stripe_connect_test_account_id' );
        }
        $params = [
                      'sandbox'           => $liveMode ? 0 : 1,
                      'account_id'        => $accountId,
                      'code'              => wp_create_nonce( 'ihc_stripe_connect_deauth' ),
        ];

        $baseUrl = add_query_arg( $params, $baseUrl );
        return $baseUrl;
    }

    /**
     * @param array
     * @return array
     */
    public function createPaymentIntent( $data=[] )
    {
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
        } else {
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
        }

        if ( isset( $data['first_amount'] ) && $data['first_amount'] !== false && $data['first_amount'] !== '' ){
            $amount = $data['first_amount'];
        } else {
            $amount = $data['amount'];
        }

        $amount = $this->getAmount( $amount, $data['currency'] );
        $appFeeAmount = $this->getAppFeeAmount( $amount );

        $paymentIntentArgs = [
              'amount'                    => $amount,
              'currency'                  => $data['currency'],
              'application_fee_amount'    => $appFeeAmount,
              'setup_future_usage'        => 'off_session',
              'metadata'                  => [
                                'lid'                       => isset( $data['lid'] ) ? $data['lid'] : '',
                                'service'                   => 'stripe_connect',
              ],
              'payment_method'            => $data['payment_method'],

              // since version 12.0
              'automatic_payment_methods' => [
                  'enabled'         => true,
                  'allow_redirects' => 'never',
              ],
        ];

        // set uid
        if ( isset( $data['uid'] ) && $data['uid'] !== 0){
              $paymentIntentArgs['metadata']['uid'] = $data['uid'];
              $customerId = get_user_meta( $data['uid'], 'ihc_stripe_customer_id', true );
        }
        // set customer_id if we have one
        if ( isset( $customerId ) && $customerId !== null && $customerId !== '' ){
              try {
                $customerObject = $stripe->customers->retrieve(
                    $customerId,
                    []
                );
              } catch( \Exception $e ){
                  $customerObject = null;
              }
              if ( $customerObject !== null && isset( $customerObject->id ) ){
                  $paymentIntentArgs['customer'] = $customerId;
              }
        }

        if ( isset( $data['first_amount'] ) && $data['first_amount'] !== false && $data['first_amount'] !== '' ){
            $paymentIntentArgs['description'] = esc_html__('Initial Payment for ' . $data['level_label'] );
        } else if ( $data['level_label'] !== '' ){
            $paymentIntentArgs['description'] = $data['level_label'];
        }
        $statementDescriptor = $this->getStatementDescriptor();
        if ( $statementDescriptor !== null ){
            $paymentIntentArgs['statement_descriptor'] = $statementDescriptor;
        }

        try {
            $paymentIntent = $stripe->paymentIntents->create( $paymentIntentArgs );
        } catch ( \Exception $e ){
            return [ 'status' => 0, 'client_secret' => '', 'payment_intent_id' => '' ];
        }
        if ( isset( $paymentIntent ) && $paymentIntent !== false && isset( $paymentIntent->client_secret ) ){
            return [
                      'status'              => 1,
                      'client_secret'       => $paymentIntent->client_secret,
                      'payment_intent_id'   => $paymentIntent->id,
            ];
        }
    }

    /**
     * @param array
     * @return array
     */
    public function createSetupIntent( $data=[] )
    {
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
        } else {
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
        }
        try {
            $setupIntentArgs = [
                                  'payment_method'      => $data['payment_method'],
                                  // since version 12.0
                                  'automatic_payment_methods' => [
                                      'enabled'         => true,
                                      'allow_redirects' => 'never',
                                  ],
            ];

            $setupIntent = $stripe->setupIntents->create( $setupIntentArgs );
            if ( $setupIntent !== false && isset( $setupIntent->client_secret ) ){
                return [
                          'status'              => 1,
                          'client_secret'       => $setupIntent->client_secret,
                          'setup_intent_id'     => $setupIntent->id,
                ];
            }
        } catch ( \Exception $e ){
           return [
                      'status'            => 0,
                      'client_secret'     => '',
                      'setup_intent_id'   => ''
           ];
       }
    }

    /**
     * @param string
     * @param int
     * @return array
     */
    public function changePaymentMethodForUser( $paymentMethodId='', $umpSubscriptionId=0 )
    {
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }

        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
        } else {
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
        }
        if ( $paymentMethodId === false || $umpSubscriptionId === false ){
            return [
                                'status'        => 0,
                                'message'       => esc_html__( 'Card has not changed. Please try again later or contact the Administrator', 'ihc' ),
            ];
        }
        // getting stripe subscription id
        $subscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $umpSubscriptionId, 'ihc_stripe_subscription_id' );
        if ( $subscriptionId === false ){
            return [
                                'status'        => 0,
                                'message'       => esc_html__( 'Card has not changed. Please try again later or contact the Administrator', 'ihc' ),
            ];
        }
        // getting stripe customer id
        try {
            $subscription = $stripe->subscriptions->retrieve( $subscriptionId );
        } catch ( \Exception $e ){
            $subscription = null;
            $error = true;
        }

        $customerId = isset( $subscription->customer ) ? $subscription->customer : false;
        if ( $customerId === false ){
            return [
                                'status'        => 0,
                                'message'       => esc_html__( 'Card has not changed. Please try again later or contact the Administrator', 'ihc' ),
            ];
        }

        try {
            $attachPayment = $stripe->paymentMethods->attach( $paymentMethodId, [ 'customer' => $customerId ] );
        } catch ( \Exception $e ){
            $error = true;
        }

        try {
            $stripe->subscriptions->update( $subscriptionId, ['default_payment_method' => $paymentMethodId ] );
        } catch ( \Exception $e ){
            $error = true;
        }

        if ( empty( $error ) ){
            return [
                     'status'        => 1,
                     'message'       => esc_html__( 'Card has been succesfully changed!', 'ihc' )
            ];
        } else {
            return [
                     'status'        => 0,
                     'message'       => esc_html__( 'Card has not changed. Please try again later or contact the Administrator', 'ihc' )
            ];
        }

    }

    /**
     * @param array
     * @return string
     */
    public function getCheckoutform( $params = [] )
    {
        $filename = 'checkout-stripe_connect-form.php';
        $templateFile = IHC_PATH . 'public/views/checkout/checkout-stripe_connect-form.php';
        $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

        $view = new \Indeed\Ihc\IndeedView();
   			return $view->setTemplate( $templateFile )
   								->setContentData( $params )
   								->getOutput();
    }

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
        } else {
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
        }

        if ( isset( $_POST['ihc_stripe_connect_full_name'] ) && $_POST['ihc_stripe_connect_full_name'] !== '' ){
            update_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_connect_full_name', sanitize_text_field( $_POST['ihc_stripe_connect_full_name'] ) );
        }

        if ( isset( $_POST['ihc_stripe_connect_payment_methods'] ) && $_POST['ihc_stripe_connect_payment_methods'] !== 'new' ){
            $paymentMethod = sanitize_text_field( $_POST['ihc_stripe_connect_payment_methods'] );
        } else {
            $paymentMethod = '';
        }

        if ( $this->isRecurringLevel() ){
            $this->chargeRecurring( $stripe, $paymentMethod );
        } else {
            $this->chargeSinglePayment( $stripe, $paymentMethod );
        }
    }

    /**
     * SINGLE PAYMENT
     * @param object
     * @param string
     * @return object
     */
    public function chargeSinglePayment( $stripe=null, $paymentMethod='' )
    {
          $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
          if ( isset( $_POST['stripe_payment_intent'] ) && $_POST['stripe_payment_intent'] !== '' && $paymentMethod === '' ){
              // payment intent exists, so we get the payment method from payment intent
              $paymentIntentId = sanitize_text_field( $_POST['stripe_payment_intent'] );
              $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $paymentIntentId );

              try {
                  $paymentIntentObject = $stripe->paymentIntents->retrieve( $paymentIntentId, [] );
                  $paymentMethod = isset( $paymentIntentObject->payment_method ) ? $paymentIntentObject->payment_method : '';
              } catch ( \Exception $e ){
                  return false;
              }
          } else if ( $paymentMethod !== '' ) {
              // payment intent doesn't exists yet, but we have a payment method
              $createPaymentIntent = true;
          }

          if ( $paymentMethod === '' ){
              // no payment method -> out
              return false;
          }

          // check if we have customer id in db
          $customerId = get_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_customer_id', true );
          if ( $customerId !== null && $customerId !== '' ){
              try {
                $customerObject = $stripe->customers->retrieve( $customerId, [] );
              } catch( \Exception $e ){
                  $customerObject = null;
              }
              if ( $customerObject === null || !isset( $customerObject->id ) ){
                  $customerId = null;
              }
          }
          // Create Customer if it's case
          if ( $customerId === false || $customerId === '' ){
              try {
                  $customerFullName = get_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_connect_full_name', true );
                  if ( $customerFullName === '' || $customerFullName === false || $customerFullName === null ){
                      $customerFullName = $this->paymentOutputData['customer_name'];
                  }

                  $customer = $stripe->customers->create([
                      'email'             => $this->paymentOutputData['customer_email'],
                      'name'              => $customerFullName,
                      'metadata'          => [ 'uid' => $this->paymentOutputData['uid'] ],
                      'payment_method'    => $paymentMethod,
                  ]);

                  $customerId = isset( $customer->id ) ? $customer->id : '';
                  update_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_customer_id', $customerId );
              } catch ( \Exception $e ){
                return false;
              }
          } else if ( !empty( $createPaymentIntent ) ) {
              // attach payment method to customer
              try {
                  $stripe->paymentMethods->attach( $paymentMethod, [ 'customer' => $customerId ] );
              } catch ( \Exception $e ){
                  return false;
              }
          }

          // Create Payment Intent if we don't have one from previous step
          if ( !empty( $createPaymentIntent ) ){
              $amount = $this->getAmount( $this->paymentOutputData['amount'], $this->paymentOutputData['currency'] );
              $appFeeAmount = $this->getAppFeeAmount( $amount );

              $paymentIntentArgs = [
                'amount'                    => $amount,
                'currency'                  => $this->paymentOutputData['currency'],
                'customer'                  => $customerId,
                'payment_method'            => $paymentMethod,
                'application_fee_amount'    => $appFeeAmount,
                'off_session'               => true,
                'confirm'                   => true,
                'metadata'                  => [
                                  'order_identificator'       => $this->paymentOutputData['order_identificator'],
                                  'uid'                       => $this->paymentOutputData['uid'],
                                  'lid'                       => $this->paymentOutputData['lid'],
                                  'service'                   => 'stripe_connect',
                ],
                // since version 12.0
                'automatic_payment_methods' => [
                    'enabled'         => true,
                    'allow_redirects' => 'never',
                ],
              ];

              if ( $this->paymentOutputData['level_label'] !== '' ){
                $paymentIntentArgs['description'] = $this->paymentOutputData['level_label'];
              }
              $statementDescriptor = $this->getStatementDescriptor();
              if ( $statementDescriptor !== null ){
                  $paymentIntentArgs['statement_descriptor'] = $statementDescriptor;
              }

              // since version 12.2 - prorate on single payment
              if ( isset( $this->levelData['prorate_expire_time'] ) && $this->levelData['prorate_expire_time'] !== '' ){
                  $paymentIntentArgs['metadata']['prorate_expire_time'] = $this->levelData['prorate_expire_time'];
              }
              if ( isset( $this->levelData['prorate_from'] ) && $this->levelData['prorate_from'] !== '' ){
                  $paymentIntentArgs['metadata']['prorate_from'] = $this->levelData['prorate_from'];
              }
              // since version 12.2 - prorate on single payment

              try {
                  $paymentIntent = $stripe->paymentIntents->create( $paymentIntentArgs );
              } catch ( \Exception $e ){
                  return false;
              }
              $paymentIntentId = isset( $paymentIntent->id ) ? $paymentIntent->id : '';

              if ( $paymentIntentId !== false ){
                  $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $paymentIntentId );
              }
          }

          // make completed
          if ( !isset( $paymentIntentObject ) && !empty( $paymentIntentId ) ){
              $paymentIntentObject = $stripe->paymentIntents->retrieve( $paymentIntentId, [] );
          }

          if ( empty( $paymentIntentObject ) ){
              return false;
          }

          $chargeId = isset( $paymentIntentObject->charges->data[0]->id ) ? $paymentIntentObject->charges->data[0]->id : 0;

          if ( $chargeId === 0 ){
              return false;
          }

          $charge = $stripe->charges->retrieve( $chargeId , [] );

          if ( isset( $charge->status ) && $charge->status === 'succeeded' ){
              $this->paymentOutputData['transaction_id'] = $paymentIntentId;

              // since version 10.5 - prorate
              if ( isset( $this->levelData['prorate_expire_time'] ) && $this->levelData['prorate_expire_time'] !== '' ){
                  $this->paymentOutputData['expire_time'] = $this->levelData['prorate_expire_time'];
              }

              $this->completeLevelPayment( $this->paymentOutputData );

              return;
          }
    }



    /**
     * @param object
     * @param string
     * @return object
     */
    public function chargeRecurring( $stripe=null, $paymentMethod='' )
    {
       $orderMeta = new \Indeed\Ihc\Db\OrderMeta();

        if ( isset( $_POST['stripe_payment_intent'] ) && $_POST['stripe_payment_intent'] !== '' &&  $paymentMethod === '' ){
            // with payment intent
            $paymentIntentId = sanitize_text_field( $_POST['stripe_payment_intent'] );
            $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $paymentIntentId );
            try {
                $paymentIntentObject = $stripe->paymentIntents->retrieve( $paymentIntentId, [] );
            } catch ( \Exception $e ){
                return false;
            }
            $paymentMethod = isset( $paymentIntentObject->payment_method ) ? $paymentIntentObject->payment_method : '';
        } else if ( isset( $_POST['stripe_setup_intent'] ) && $_POST['stripe_setup_intent'] !== '' &&  $paymentMethod === '' ){
            // we have a setup intent, in this case we don't create payment intent
            $setupIntentId = sanitize_text_field( $_POST['stripe_setup_intent'] );

            if ( $setupIntentId !== '' && $paymentMethod === '' ){
                try {
                    $setupIntent = $stripe->setupIntents->retrieve( $setupIntentId );
                    if ( $setupIntent === null || $setupIntent === false ){
                        return false;
                    }
                    $paymentMethod = isset( $setupIntent->payment_method ) ? $setupIntent->payment_method : '';
                    $newPaymentMethod = true;
                } catch( \Exception $e ){
                    return false;
                }
            }
        } else if ( $paymentMethod !== '' ){
            // payment method is set but we don't have payment intent or setup intent. The payment it's made from account page based on saved card
            if ( !isset($this->paymentOutputData['first_amount']) || $this->paymentOutputData['first_amount'] === '' || $this->paymentOutputData['first_amount'] === false || (int)$this->paymentOutputData['first_amount'] !== 0 ){
                    $createPaymentIntent = true;
            }
        }

        if ( $paymentMethod === '' ){
            // no payment method -> out
            return false;
        }

        // check if the product exists in our db
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $productId = \Indeed\Ihc\Db\Memberships::getOneMeta( $this->paymentOutputData['lid'], 'ihc_stripe_product_id' );
        } else {
            $productId = \Indeed\Ihc\Db\Memberships::getOneMeta( $this->paymentOutputData['lid'], 'ihc_stripe_product_id-test' );
        }

        // check if product exists in stripe
        if ( $productId !== null && $productId !== ''  ){
            try {
            	$productObject = $stripe->products->retrieve( $productId,	[] );
            } catch( \Exception $e ){
            		$productObject = null;
            }
            if ( $productObject === null || !isset( $productObject->id ) ){
              	$productId = null;
            }
        }

        // create product
        if ( $productId === null || $productId === '' ){
            try {

              $productParams = [
                'name'                  => $this->paymentOutputData['level_label'],
              ];
              if ( $this->paymentOutputData['level_description'] !== '' ){
                  $productParams['description'] = $this->paymentOutputData['level_description'];
              }
              $statementDescriptor = $this->getStatementDescriptor();
              if ( $statementDescriptor !== null ){
                  $productParams['statement_descriptor'] = $statementDescriptor;
              }
              $product = $stripe->products->create( $productParams );
              $productId = isset( $product->id ) ? $product->id : '';

              // save product id in our db
              if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
                  \Indeed\Ihc\Db\Memberships::saveMeta( $this->paymentOutputData['lid'], 'ihc_stripe_product_id', $productId );
              } else {
                  \Indeed\Ihc\Db\Memberships::saveMeta( $this->paymentOutputData['lid'], 'ihc_stripe_product_id-test', $productId );
              }
            } catch( \Exception $e ){
                return false;
            }
        }

        // Create price for product
        $this->multiply = ihcStripeMultiplyForCurrency( $this->paymentOutputData['currency'] );
        try {
            $amount = $this->getAmount( $this->paymentOutputData['amount'], $this->paymentOutputData['currency'] );
            $appFeeAmount = $this->getAppFeeAmount( $amount );
            $price = $stripe->prices->create([
              'unit_amount' 		          => $amount,
              'currency' 				          => $this->paymentOutputData['currency'],
              'recurring' 			          => [
                                                'interval'        => $this->paymentOutputData['interval_type'],
                                                'interval_count'  => $this->paymentOutputData['interval_value']
              ],
              'product' 				          => $productId,
            ]);
            $priceId = isset( $price->id ) ? $price->id : '';
        } catch ( \Exception $e ){
            return false;
        }

        // check if customer exists in our db
        $customerId = get_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_customer_id', true );

        // check if customer exists in stripe
        if ( $customerId !== null && $customerId !== '' ){
            try {
              $customerObject = $stripe->customers->retrieve( $customerId, [] );
            } catch( \Exception $e ){
                $customerObject = null;
            }
            if ( $customerObject === null || !isset( $customerObject->id ) ){
                $customerId = null;
            }
        }

        // create customer if not exists
        if ( $customerId === null || $customerId === '' ){
            try {
                $customerFullName = get_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_connect_full_name', true );
                if ( $customerFullName === '' || $customerFullName === false || $customerFullName === null ){
                    $customerFullName = $this->paymentOutputData['customer_name'];
                }
                $customer = $stripe->customers->create([
                    'email'             => $this->paymentOutputData['customer_email'],
                    'name'              => $customerFullName,
                    'metadata'          => [ 'uid' => $this->paymentOutputData['uid'] ],
                    'payment_method'    => $paymentMethod,
                ]);
                $customerId = isset( $customer->id ) ? $customer->id : '';
                update_user_meta( $this->paymentOutputData['uid'], 'ihc_stripe_customer_id', $customerId );
            } catch ( \Exception $e ){
                return false;
            }
        } else if ( !empty( $newPaymentMethod ) ) {
            // attach payment method to customer
            try {
                $stripe->paymentMethods->attach( $paymentMethod, [ 'customer' => $customerId ] );
            } catch ( \Exception $e ){

            }
        }

        // create payment intent if it's case
        if ( !empty( $createPaymentIntent ) ){
          $paymentIntentDesc = $this->paymentOutputData['level_label'];
            if ( $this->paymentOutputData['first_amount'] !== false && $this->paymentOutputData['first_amount'] !== '' && $this->paymentOutputData['first_amount'] != 0 ){
              $amount = $this->getAmount( $this->paymentOutputData['first_amount'], $this->paymentOutputData['currency'] );
              $paymentIntentDesc = esc_html__('Initial Payment for ' . $this->paymentOutputData['level_label'] );
            } else {
              $amount = $this->getAmount( $this->paymentOutputData['amount'], $this->paymentOutputData['currency'] );
            }
            $appFeeAmount = $this->getAppFeeAmount( $amount );
            $initialPaymentParams = [
                'amount'                    => $amount,
                'currency'                  => $this->paymentOutputData['currency'],
                'customer'                  => $customerId,
                'payment_method'            => $paymentMethod,
                'application_fee_amount'    => $appFeeAmount,
                'metadata'                  => [
                            'order_identificator'       => $this->paymentOutputData['order_identificator'],
                            'uid'                       => $this->paymentOutputData['uid'],
                            'lid'                       => $this->paymentOutputData['lid'],
                            'service'                   => 'stripe_connect',
                ],
                'description'                => $paymentIntentDesc,
                'confirm'                   => true,
                'off_session'               => true,
                // since version 12.0
                'automatic_payment_methods' => [
                    'enabled'         => true,
                    'allow_redirects' => 'never',
                ],
            ];

            $statementDescriptor = $this->getStatementDescriptor();
            if ( $statementDescriptor !== null ){
                $initialPaymentParams['statement_descriptor'] = $this->getStatementDescriptor();
            }

            // since version 10.5 - prorate
            if ( isset( $this->levelData['prorate_expire_time'] ) && $this->levelData['prorate_expire_time'] !== '' ){
                $initialPaymentParams['metadata']['prorate_expire_time'] = $this->levelData['prorate_expire_time'];
            }
            if ( isset( $this->levelData['prorate_from'] ) && $this->levelData['prorate_from'] !== '' ){
                $initialPaymentParams['metadata']['prorate_from'] = $this->levelData['prorate_from'];
            }
            if ( isset( $this->levelData['prorate_old_subscription_id'] ) && $this->levelData['prorate_old_subscription_id'] !== '' ){
                $initialPaymentParams['metadata']['prorate_old_subscription_id'] = $this->levelData['prorate_old_subscription_id'];
            }

            try {
                $paymentIntentObject = $stripe->paymentIntents->create( $initialPaymentParams );
            } catch ( \Exception $e ){
                return false;
            }
            $paymentIntentId = isset( $paymentIntentObject->id ) ? $paymentIntentObject->id : '';
            if ( $paymentIntentId !== false ){
              $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $paymentIntentId );
            }
        }

        // set subscription params
        $subscriptionParams = [
                'customer'                => $customerId,
                'items'                   => [[
                                              'price'       => $priceId,
                ]],
                'expand'                  => ['latest_invoice.payment_intent'],
                'application_fee_percent' => $this->appFeePercentage,
                'default_payment_method'  => $paymentMethod,
                'off_session'             => true,
                'metadata'                => [
                                                'order_identificator'       => $this->paymentOutputData['order_identificator'],
                                                'uid'                       => $this->paymentOutputData['uid'],
                                                'lid'                       => $this->paymentOutputData['lid'],
                                                'service'                   => 'stripe_connect',
                ],
        ];

        // since version 10.5 - prorate
        if ( isset( $this->levelData['prorate_expire_time'] ) && $this->levelData['prorate_expire_time'] !== '' ){
            $subscriptionParams['metadata']['prorate_expire_time'] = $this->levelData['prorate_expire_time'];
        }
        if ( isset( $this->levelData['prorate_from'] ) && $this->levelData['prorate_from'] !== '' ){
            $subscriptionParams['metadata']['prorate_from'] = $this->levelData['prorate_from'];
        }
        if ( isset( $this->levelData['prorate_old_subscription_id'] ) && $this->levelData['prorate_old_subscription_id'] !== '' ){
            $subscriptionParams['metadata']['prorate_old_subscription_id'] = $this->levelData['prorate_old_subscription_id'];
        }

        $cancelAt = $this->getCancelAt();
        if ( $cancelAt !== false ){
            $subscriptionParams['cancel_at'] = $this->getCancelAt();
        }
        if ( isset( $this->paymentOutputData['first_payment_interval_value'] ) && $this->paymentOutputData['first_payment_interval_value'] !== '' ){
            // trial period
            $subscriptionParams['trial_period_days'] = $this->paymentOutputData['first_payment_interval_value'];
        } else {
            // normal recurring
            $subscriptionParams['billing_cycle_anchor'] = $this->getBillingCycleAnchor();
            $subscriptionParams['proration_behavior'] = 'none';
        }
        if ( isset( $paymentIntentId ) && $paymentIntentId !== '' ){
           $subscriptionParams['metadata']['initial_payment_intent_id'] = $paymentIntentId;
        }

        try {
            // create the subscription
            $subscription = $stripe->subscriptions->create( $subscriptionParams );
        } catch ( \Exception $e ){
            return false;
        }

        $subscriptionId = isset( $subscription->id ) ? $subscription->id : '';
        $invoiceId = isset( $subscription->latest_invoice->id ) ? $subscription->latest_invoice->id : '';
        //Keep Subscription_id to user-subscriptionMetas
        if( isset($subscriptionId) && $subscriptionId !=''){
            $user_subscription_id = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $this->paymentOutputData['uid'], $this->paymentOutputData['lid']);
            if(isset($user_subscription_id)){
                \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $user_subscription_id, 'ihc_stripe_subscription_id', $subscriptionId);
            }
            $paymentMethodDetails = $this->getPaymentMethodDetails( $paymentMethod, $stripe );
            if ( $paymentMethodDetails !== false ){
                \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $user_subscription_id, 'payment_method_exp_month', $paymentMethodDetails['exp_month']);
                \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $user_subscription_id, 'payment_method_exp_year', $paymentMethodDetails['exp_year']);
            }
        }

        $orderMeta->save( $this->paymentOutputData['order_id'], 'subscription_id', $subscriptionId );

        // make completed if it's case
        if ( !isset( $paymentIntentObject ) && !empty( $paymentIntentId ) ){
            $paymentIntentObject = $stripe->paymentIntents->retrieve( $paymentIntentId, [] );
        }

        if ( empty( $paymentIntentObject ) ){
            return false;
        }

        $chargeId = isset( $paymentIntentObject->charges->data[0]->id ) ? $paymentIntentObject->charges->data[0]->id : 0;

        if ( $chargeId === 0 ){
            //return false;
        }else{
          $charge = $stripe->charges->retrieve( $chargeId , [] );

          if ( isset( $charge->status ) && $charge->status === 'succeeded' ){
              $this->paymentOutputData['transaction_id'] = $paymentIntentId;

              // since version 10.5 - prorate
              if ( isset( $this->levelData['prorate_expire_time'] ) && $this->levelData['prorate_expire_time'] !== '' ){
                  $this->paymentOutputData['expire_time'] = $this->levelData['prorate_expire_time'];
              }

              $this->completeLevelPayment( $this->paymentOutputData );

              return;
          }
        }

    }

    /**
     * @param number
     * @param string
     * @return number ( int or float )
     */
    public function getAmount( $amount=0, $currency='' )
    {
        // check the number of decimanls, stripe accept maximum of 2. update since version 11.0
        $numberOfDecimals = (int) strpos(strrev($amount), ".");
        if ( $numberOfDecimals > 2 ){
            $amount = round( $amount, 2 );
        }

        $this->multiply = ihcStripeMultiplyForCurrency( $currency );
        $amount = $amount * $this->multiply;
        if ( $this->multiply==100 && $amount> 0 && $amount<50 ){
            $amount = 50;// 0.50 cents minimum amount for stripe transactions
        }
        return $amount;
    }

    /**
     * @param number
     * @return number
     */
    public function getAppFeeAmount( $amount=0 )
    {
      	$value = $this->appFeePercentage * $amount / 100;
        $value = round( $value );
      	if ( $this->multiply == 100 && $value < 1 ){
      		  $value = 1;
      	}
      	return $value;
    }

    /**
     * @param none
     * @return int
     */
    public function getBillingCycleAnchor()
    {
        // one subscription cycle
        $interval = (int)$this->paymentOutputData['interval_value'];
        $value = strtotime('+' . $interval . ' ' . $this->paymentOutputData['interval_type'] );

        if ( $this->paymentOutputData['interval_type'] === 'month' ){
            // extra test for month .
            $targetMonth = (int)date( 'm', strtotime( 'first day of +' . $interval . ' month' ) );
            $futureMonth = (int)date( 'm', $value );

            if ( $targetMonth !== $futureMonth ){
                while ( $targetMonth !== $futureMonth ){
                    if ( $targetMonth > $futureMonth ){
                        $value = $value + ( 24 * 60 * 60 );
                        $futureMonth = (int)date('m', $value);
                    } else if ( $targetMonth < $futureMonth ){
                        $value = $value - ( 24 * 60 * 60 );
                        $futureMonth = (int)date('m', $value);
                    }
                }
            }
        }

        $value = $value - 10;
        return $value;
    }

    /**
     * @param none
     * @return string
     */
    public function getCancelAt()
    {
        if ( !isset( $this->paymentOutputData['subscription_cycles_limit'] ) || $this->paymentOutputData['subscription_cycles_limit'] === '' ){
            return false;
        }
        $interval = (int)$this->paymentOutputData['interval_value'] * (int)$this->paymentOutputData['subscription_cycles_limit'];
        $cancelAt = strtotime('+' . $interval . ' ' . $this->paymentOutputData['interval_type'], indeed_get_unixtimestamp_with_timezone() );
        if ( !empty($this->paymentOutputData['is_trial']) && isset($this->paymentOutputData['first_payment_interval_value']) && $this->paymentOutputData['first_payment_interval_value'] !== false
          && $this->paymentOutputData['first_payment_interval_value'] !== 0 && $this->paymentOutputData['first_payment_interval_value'] !== '' ){
            $cancelAt = strtotime('+'.$this->paymentOutputData['first_payment_interval_value']. ' day', $cancelAt);
        }
        return $cancelAt;
    }

    /**
     * @param none
     * @return string
     */
    public function getStatementDescriptor()
    {
        $statementDescriptor = get_option( 'ihc_stripe_connect_descriptor' );
        if ( $statementDescriptor === false ){
            $statementDescriptor = get_option( 'blogname' );
        }
        if ( $statementDescriptor !== '' ){
            $statementDescriptor = substr( $statementDescriptor, 0, 21 );
        }
        if ( $statementDescriptor === '' || $statementDescriptor === false ) {
            return null;
        }
        return $statementDescriptor;
    }

    /**
     * @param int
     * @return mixed ( array or bool )
     */
    public function getPaymentMethodsForUser( $uid=0 )
    {
        if ( $uid === 0 ){
            return false;
        }
        $customerId = get_user_meta( $uid, 'ihc_stripe_customer_id', true );
        if ( $customerId === false || $customerId === '' ){
            // no payment methods for this user
            return false;
        }
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
        } else {
            $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
        }
        try {
            $response = $stripe->paymentMethods->all( [
              'customer'      => $customerId,
              'type'          => 'card',
            	'limit'         => 100,
            ] );
        } catch ( \Exception $e ){
            return false;
        }

        if ( !isset( $response->data ) || count( $response->data ) === 0 ){
            // no payment methods for this customer
            return false;
        }

        $returnData = [];

        foreach ( $response->data as $paymentData ){
            $returnData[] = [
                              'payment_method'              => $paymentData->id,
                              'name'                        => $paymentData->billing_details->name,
                              'brand'                       => $paymentData->card->brand,
                              'country'                     => $paymentData->card->country,
                              'exp_month'                   => $paymentData->card->exp_month,
                              'exp_year'                    => $paymentData->card->exp_year,
                              'last4'                       => $paymentData->card->last4,
            ];
        }
        return $returnData;
    }

    /**
     * @param string
     * @param object
     * @return array
     */
    public function getPaymentMethodDetails( $paymentMethodId='', $stripe=null )
    {
        $paymentData = $stripe->paymentMethods->retrieve(  $paymentMethodId );
        if ( $paymentData === null || $paymentData === false ){
            return [];
        }
        return [
                'name'                        => $paymentData->billing_details->name,
                'brand'                       => $paymentData->card->brand,
                'country'                     => $paymentData->card->country,
                'exp_month'                   => $paymentData->card->exp_month,
                'exp_year'                    => $paymentData->card->exp_year,
                'last4'                       => $paymentData->card->last4,
        ];
    }

    /**
     * workarround
     */
    public function setwebhookResponse( $postData=[] )
    {
        $this->webhookResponse = $postData;
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        $timestamp = indeed_get_unixtimestamp_with_timezone();
        if ( empty( $this->webhookResponse ) ){
            $response = @file_get_contents( 'php://input' );
            $responseData = json_decode( $response, true );
        } else {
            $responseData = $this->webhookResponse;
        }

        if ( empty( $responseData ) ){
          echo esc_ump_content('============= Ultimate Membership Pro - Stripe Connect Webhook ============= ');
          echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
          exit;
        }

        $currency = isset( $responseData['data']['object']['currency'] ) ? $responseData['data']['object']['currency'] : '';
        $this->multiply = ihcStripeMultiplyForCurrency( $currency );

        if ( !isset( $responseData['type'] ) ){
            return;
        }
        // Log that we have successfully received a webhook from Stripe.
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            update_option( 'ihc_stripe_connect_last_webhook_received_live', date( 'Y-m-d H:i:s' ) );
        }else{
            update_option( 'ihc_stripe_connect_last_webhook_received_test', date( 'Y-m-d H:i:s' ) );
        }

        // >= 12.1
        $linesData = [];
        if ( isset( $responseData['data']['object']['lines']['data'][0] ) && is_array( $responseData['data']['object']['lines']['data'][0] ) && count( $responseData['data']['object']['lines']['data'][0] ) > 0 ){
            $linesData = $responseData['data']['object']['lines']['data'][0];
        } else if ( isset( $responseData['data']['object']['lines']['data'][1] ) && is_array( $responseData['data']['object']['lines']['data'][1] ) && count( $responseData['data']['object']['lines']['data'][1] ) > 0 ){
            $linesData = $responseData['data']['object']['lines']['data'][1];
        } else {
            // payments made via stripe 13.0 sdk.
            $linesData = $responseData['data']['object'];
        }


        switch ( $responseData['type'] ){
            //case 'charge.captured':
            case 'charge.succeeded':
              /// Single Payment
              $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;

              // >= 12.1

              /*
              $skipWebhook = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['skip_webhook'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['skip_webhook'] : '';
              $service = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['service'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['service'] : '';
              */
              // >= 12.1
              $skipWebhook = isset( $linesData['metadata']['skip_webhook'] ) ? $linesData['metadata']['skip_webhook'] : '';
              $service = isset( $linesData['metadata']['service'] ) ? $linesData['metadata']['service'] : '';


              // since version 10.5 - prorate
              /*
              $prorateExpireTime = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_expire_time'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_expire_time'] : '';
              $prorateFrom = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_from'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_from'] : '';
              $prorateOldSubscription = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_old_subscription_id'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_old_subscription_id'] : '';
              */
              // >= 12.1
              $prorateExpireTime = isset( $linesData['metadata']['prorate_expire_time'] ) ? $linesData['metadata']['prorate_expire_time'] : '';
              $prorateFrom = isset( $linesData['metadata']['prorate_from'] ) ? $linesData['metadata']['prorate_from'] : '';
              $prorateOldSubscription = isset( $linesData['metadata']['prorate_old_subscription_id'] ) ? $linesData['metadata']['prorate_old_subscription_id'] : '';

              if ( $skipWebhook === 1 ){
                  break;
              }
              if ( $service === '' ){
                  return;
              }

              $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
              $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );

              // >=12.1
              if ( $orderId === false || $orderId === null ){
                  sleep( 5 ); // maybe its too fast to search for order id
              }
              $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );

              // >=12.1
              if (  empty( $responseData['via_cron'] ) && ( $orderId === false || $orderId === null )  ){
                  $responseData = indeed_sanitize_textarea_array( $responseData ); // sanitize array $responseData before store it
                  update_option( $transactionId, json_encode($responseData) );
                  $cronSaved = wp_schedule_single_event( time() + 10, 'iump_stripe_connect_queue', [ $transactionId ] );
                  if ( $cronSaved === true ){
                      $this->webhookData['payment_status'] = 'other';
                      break;
                  } else {
                      // something went wrong when trying to save a new cron so we force stripe to send the response again after one hour
                      $this->webhookData['payment_status'] = 'failed';
                      break;
                  }
              }

              if ( !isset($orderId) || $orderId == false ){
                  // out
                  $this->webhookData['payment_status'] = 'other';
                  break;
              }
              $orderIdentificator = $orderMeta->get( $orderId, 'order_identificator' );
              $orderObject = new \Indeed\Ihc\Db\Orders();
              $orderData = $orderObject->setId( $orderId )
                                       ->fetch()
                                       ->get();

              $amount = isset( $responseData['data']['object']['amount'] ) ? $responseData['data']['object']['amount'] : 0;
              if ( $amount > 0 ){
                  $amount = $amount / $this->multiply;
              }

              $this->webhookData = [
                                      'transaction_id'              => $transactionId,
                                      'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                                      'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                                      'order_identificator'         => $orderIdentificator,
                                      'amount'                      => $amount,
                                      'currency'                    => $currency,
                                      'payment_details'             => $responseData,
                                      'payment_status'              => 'completed',
              ];

              // since version 10.5 - prorate
              if ( isset( $prorateExpireTime ) && $prorateExpireTime !== '' && $prorateExpireTime > time() ){
                  $this->webhookData['expire_time'] = $prorateExpireTime;
                  if ( $prorateFrom !== '' ){
                      if ( $prorateOldSubscription !== '' ){
                          // cancel old membership
                          $subscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $prorateOldSubscription, 'ihc_stripe_subscription_id' );
                          if ( $this->canDoCancel( $this->webhookData['uid'], $prorateFrom, $prorateOldSubscription ) ){
                              $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                              $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
                              $transactionId = $orderMeta->get( $orderId, 'transaction_id' );
                              $this->cancel( $this->webhookData['uid'], $prorateFrom, $transactionId );
                          }
                      }
                      // delete old membership
                      $this->webhookDoCancel( $this->webhookData['uid'], $prorateFrom );
                      \Indeed\Ihc\UserSubscriptions::deleteOne( $this->webhookData['uid'], $prorateFrom );

                  }
              }
              break;
            case 'invoice.payment_succeeded':
              //Recurring Subscriptions Payments

              if ( !class_exists('\Stripe\StripeClient') ){
                  require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
              }
              if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
                  $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
              } else {
                  $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
              }
              // set the subscription id
              $subscriptionId = $responseData['data']['object']['subscription'];
              // set the payment intent id
              $paymentIntentId = $responseData['data']['object']['payment_intent'];
              /*
              if ( $paymentIntentId === null ){
                  $paymentIntentId = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['initial_payment_intent_id'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['initial_payment_intent_id'] : '';
              }
              if ( $paymentIntentId === null ){
                  $paymentIntentId = isset( $responseData['data']['object']['lines']['data'][1]['metadata']['initial_payment_intent_id'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata']['initial_payment_intent_id'] : '';
              }
              */
              // >= 12.1
              if ( $paymentIntentId === null ){
                  $paymentIntentId = isset( $linesData['metadata']['initial_payment_intent_id'] ) ? $linesData['metadata']['initial_payment_intent_id'] : '';
              }

              // set the customer id
              $customerId = $responseData['data']['object']['customer'];
             // set the invoice id

              // check if it's made from stripe connect
              //$service = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['service'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['service'] : '';
              // >= 12.1
              $service = isset( $linesData['metadata']['service'] ) ? $linesData['metadata']['service'] : '';
              // since version 10.5 - prorate
              /*
              $prorateExpireTime = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_expire_time'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_expire_time'] : '';
              $prorateFrom = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_from'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_from'] : '';
              $prorateOldSubscription = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_old_subscription_id'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['prorate_old_subscription_id'] : '';
              */
              // >= 12.1
              $prorateExpireTime = isset( $linesData['metadata']['prorate_expire_time'] ) ? $linesData['metadata']['prorate_expire_time'] : '';
              $prorateFrom = isset( $linesData['metadata']['prorate_from'] ) ? $linesData['metadata']['prorate_from'] : '';
              $prorateOldSubscription = isset( $linesData['metadata']['prorate_old_subscription_id'] ) ? $linesData['metadata']['prorate_old_subscription_id'] : '';

              $invoiceId = $responseData['data']['object']['id'];
              // set the order identificator
              /*
              $orderIdentificator = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['order_identificator'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['order_identificator'] : '';
              if ( $orderIdentificator === '' ){
                  $orderIdentificator = isset( $responseData['data']['object']['lines']['data'][1]['metadata']['order_identificator'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata']['order_identificator'] : '';
              }
              */
              // >= 12.1
              $orderIdentificator = isset( $linesData['metadata']['order_identificator'] ) ? $linesData['metadata']['order_identificator'] : '';

              // since 12.5. stripe checkout migration, older type of data
              if ( empty( $orderIdentificator  ) ){
                  $orderIdentificator = isset( $linesData['plan']['metadata']['order_identificator'] ) ? $linesData['plan']['metadata']['order_identificator'] : '';
              }
              if ( empty( $orderIdentificator  ) ){
                  $orderIdentificator = isset( $linesData['price']['metadata']['order_identificator'] ) ? $linesData['price']['metadata']['order_identificator'] : '';
              }
              // end of 12.5

              // set the transaction id
              $transactionId = ($paymentIntentId === null || $paymentIntentId==='') ? $invoiceId : $paymentIntentId;

              $orderId = false;
              $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
              if ( $orderIdentificator !== '' ){
                  // we got order_identificator from stripe, so we get order_id based on order_identificator
                  $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );
              }

              if ( $orderId == false ){
                  // getting order id by subscription id ( in case stripe didn't send the order_identificator)
                  $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
                  // getting order identificator by order id
                  $orderIdentificator = $orderMeta->get( $orderId, 'order_identificator' );
              }

              // since version 11.5
              if ( $orderIdentificator === null || $orderIdentificator === false || $orderIdentificator === '' ){
                  /*
                  $orderIdentificator = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['initial_payment_intent_id'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['initial_payment_intent_id'] : '';
                  if ( $orderIdentificator === '' ){
                      $orderIdentificator = isset( $responseData['data']['object']['lines']['data'][1]['metadata']['initial_payment_intent_id'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata']['initial_payment_intent_id'] : '';
                  }
                  */
                  // >= 12.1
                  $orderIdentificator = isset( $linesData['metadata']['initial_payment_intent_id'] ) ? $linesData['metadata']['initial_payment_intent_id'] : '';

                  // set again order id. this will be the first order, not the parent
                  $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );


                  // >=12.1
                  if ( $orderId === false || $orderId === null ){
                      sleep( 5 ); // maybe its too fast to search for order id
                  }
                  $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );

                  // >=12.1
                  if ( empty( $responseData['via_cron'] ) && ( $orderId === false || $orderId === null ) ){
                       // maybe its too fast to search for order id
                      $responseData = indeed_sanitize_textarea_array( $responseData ); // sanitize array $responseData before store it
                      update_option( $transactionId, json_encode($responseData) );
                      $cronSaved = wp_schedule_single_event( time() + 10, 'iump_stripe_connect_queue', [ $transactionId ] );
                      if ( $cronSaved === true ){
                          $this->webhookData['payment_status'] = 'other';
                          break;
                      } else {
                          // something went wrong so we force stripe to send the response again after one hour
                          $this->webhookData['payment_status'] = 'failed';
                          break;
                      }
                  }

              }
              // since version 11.5


              if ( $orderId == false ){
                  // out
                  $this->webhookData['payment_status'] = 'other';
                  break;
              }
              $orderObject = new \Indeed\Ihc\Db\Orders();
              $orderData = $orderObject->setId( $orderId )
                                       ->fetch()
                                       ->get();


              if ( ( $responseData['data']['object']['billing_reason'] == 'subscription_create' ||  $responseData['data']['object']['default_payment_method'] === null )
                    && $paymentIntentId !== null && $paymentIntentId !== '' ) {
                      try {
                          # Retrieve the payment intent used to pay the subscription
                          $paymentIntent = $stripe->paymentIntents->retrieve( $paymentIntentId, [] );
                          // set the default payment method
                          if ( isset( $paymentIntent->payment_method ) ){
                              $stripe->subscriptions->update( $subscriptionId, [ 'default_payment_method' => $paymentIntent->payment_method ] );
                          }
                      } catch( \Exception $e ){

                      }

              }

              $amount = isset( $responseData['data']['object']['amount_paid'] ) ? $responseData['data']['object']['amount_paid'] : 0;
              if ( $amount > 0 ){
                  $amount = $amount / $this->multiply;
              }

              // switch to connect if it's case
              $stripeCheckoutEnabled = get_option( 'ihc_stripe_checkout_v2_status' );
              if ( empty($service ) ){
                  if ( empty( $stripeCheckoutEnabled ) ){
                    $this->migrateToConnect( [
                        'uid'                 => isset( $orderData->uid ) ? $orderData->uid : 0,
                        'lid'                 => isset( $orderData->lid ) ? $orderData->lid : 0,
                        'service' 				    => $service,
                        'level_label' 			  => '',
                        'level_description' 	=> '',
                        'subscription_id' 		=> $subscriptionId,
                        'order_identificator' => $orderIdentificator,
                        'order_id' 				    => $orderId,
                    ], $stripe );
                  } else {
                      // payment from stripe checkout
                      return;
                  }
              }

              // make subscription completed
              $this->webhookData = [
                                      'transaction_id'              => $transactionId,
                                      'subscription_id'             => $subscriptionId,
                                      'order_identificator'         => $orderIdentificator,
                                      'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                                      'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                                      'amount'                      => $amount,
                                      'currency'                    => $currency,
                                      'payment_details'             => $responseData,
                                      'payment_status'              => 'completed',
              ];

              // since version 10.5 - prorate
              if ( isset( $prorateExpireTime ) && $prorateExpireTime !== '' && $prorateExpireTime > time() ){
                  $this->webhookData['expire_time'] = $prorateExpireTime;
                  if ( $prorateFrom !== '' ){
                      if ( $prorateOldSubscription !== '' ){
                          // cancel old membership
                          $subscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $prorateOldSubscription, 'ihc_stripe_subscription_id' );
                          if ( $this->canDoCancel( $this->webhookData['uid'], $prorateFrom, $prorateOldSubscription ) ){
                              $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                              $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
                              $transactionId = $orderMeta->get( $orderId, 'transaction_id' );
                              $this->cancel( $this->webhookData['uid'], $prorateFrom, $transactionId );
                          }
                      }
                      // delete old membership
                      $this->webhookDoCancel( $this->webhookData['uid'], $prorateFrom );
                      \Indeed\Ihc\UserSubscriptions::deleteOne( $this->webhookData['uid'], $prorateFrom );

                  }
              }
              break;
           case 'customer.subscription.deleted':
                //Cancel Subscription Event
                $subscriptionId = isset($responseData['data']['object']['id']) ? $responseData['data']['object']['id'] : false;
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
                if ( !$orderId ){
                    // out
                    $this->webhookData['payment_status'] = 'other';
                    break;
                }
                $orderObject = new \Indeed\Ihc\Db\Orders();
                $orderData = $orderObject->setId( $orderId )
                                         ->fetch()
                                         ->get();
                $transactionId = $orderMeta->get( $orderId, 'transaction_id' );
                $orderIdentificator = $transactionId;
                $this->webhookData = [
                                        'transaction_id'              => $transactionId,
                                        'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                                        'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                                        'order_identificator'         => $orderIdentificator,
                                        'amount'                      => '',
                                        'currency'                    => '',
                                        'payment_details'             => $responseData,
                                        'payment_status'              => 'cancel',
                ];
                break;
          //REFUND
          case 'charge.refunded':
            /// REFUND
            $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;
            $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
            $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );
            if ( $orderId == false ){
                // out
                $this->webhookData['payment_status'] = 'other';
                break;
            }
            $orderObject = new \Indeed\Ihc\Db\Orders();
            $orderData = $orderObject->setId( $orderId )
                                     ->fetch()
                                     ->get();

            $orderIdentificator = $transactionId;
            $this->webhookData = [
                                    'transaction_id'              => $transactionId,
                                    'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                                    'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                                    'order_identificator'         => $orderIdentificator,
                                    'amount'                      => '',
                                    'currency'                    => '',
                                    'payment_details'             => $responseData,
                                    'payment_status'              => 'refund',
            ];
            break;
          case 'subscription_schedule.canceled':
            // set the subscription id
            $subscriptionId = $responseData['data']['object']['subscription'];
            // set the payment intent id
            $paymentIntentId = $responseData['data']['object']['payment_intent'];
            /*
            if ( $paymentIntentId === null ){
                $paymentIntentId = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['initial_payment_intent_id'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['initial_payment_intent_id'] : '';
            }
            if ( $paymentIntentId === null ){
                $paymentIntentId = isset( $responseData['data']['object']['lines']['data'][1]['metadata']['initial_payment_intent_id'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata']['initial_payment_intent_id'] : '';
            }
            */
            // >= 12.1
            if ( $paymentIntentId === null ){
                $paymentIntentId = isset( $linesData['metadata']['initial_payment_intent_id'] ) ? $linesData['metadata']['initial_payment_intent_id'] : '';
            }

            // set the customer id
            $customerId = $responseData['data']['object']['customer'];
            // set the invoice id
            $invoiceId = $responseData['data']['object']['id'];
            // set the order identificator
            /*
            $orderIdentificator = isset( $responseData['data']['object']['lines']['data'][0]['metadata']['order_identificator'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata']['order_identificator'] : '';
            if ( $orderIdentificator === '' ){
                $orderIdentificator = isset( $responseData['data']['object']['lines']['data'][1]['metadata']['order_identificator'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata']['order_identificator'] : '';
            }
            */
            // >= 12.1
            $orderIdentificator = isset( $linesData['metadata']['order_identificator'] ) ? $linesData['metadata']['order_identificator'] : '';

            // set the transaction id
            $transactionId = ($paymentIntentId === null || $paymentIntentId==='') ? $invoiceId : $paymentIntentId;
            $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
            if ( $orderIdentificator !== '' ){
                // we got order_identificator from stripe, so we get order_id based on order_identificator
                $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $orderIdentificator );
            } else {
                // getting order id by subscription id ( in case stripe didn't send the order_identificator)
                $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
                // getting order identificator by order id
                $orderIdentificator = $orderMeta->get( $orderId, 'order_identificator' );
            }
            if ( $orderId === false ){
                // out
                $this->webhookData['payment_status'] = 'other';
                break;
            }

            $this->webhookData = [
                                        'transaction_id'              => $transactionId,
                                        'uid'                         => isset( $metaData['uid'] ) ? $metaData['uid'] : 0,
                                        'lid'                         => isset( $metaData['lid'] ) ? $metaData['lid'] : 0,
                                        'order_identificator'         => $orderIdentificator,
                                        'subscription_id'             => isset( $subscriptionId ) ? $subscriptionId : '',
                                        'amount'                      => '',
                                        'currency'                    => '',
                                        'payment_details'             => $responseData,
                                        'payment_status'              => 'cancel',
            ];
            break;
          case 'charge.dispute.funds_withdrawn':
            /// make Membership expired - failed
            $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;
            $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
            $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );
            if ( $orderId == false ){
                // out
                $this->webhookData['payment_status'] = 'other';
                break;
            }
            $orderObject = new \Indeed\Ihc\Db\Orders();
            $orderData = $orderObject->setId( $orderId )
                                     ->fetch()
                                     ->get();

            $orderIdentificator = $transactionId;
            $this->webhookData = [
                                    'transaction_id'              => $transactionId,
                                    'uid'                         => isset( $orderData->uid ) ? $orderData->uid : 0,
                                    'lid'                         => isset( $orderData->lid ) ? $orderData->lid : 0,
                                    'order_identificator'         => $orderIdentificator,
                                    'amount'                      => '',
                                    'currency'                    => '',
                                    'payment_details'             => $responseData,
                                    'payment_status'              => 'failed',
            ];
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
    public function canDoCancel( $uid=0, $lid=0, $subscriptionId='' )
    {
      if ( !$subscriptionId ){
          return false;
      }
      if ( !class_exists('\Stripe\StripeClient') ){
          require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
      }
      if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
      } else {
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
      }

     $StripeSubscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'ihc_stripe_subscription_id' );
      if ( isset( $StripeSubscriptionId ) && $StripeSubscriptionId !== '' ){
            try {
                $subscription =  $stripe->subscriptions->retrieve( $StripeSubscriptionId);
            } catch ( \Exception $e ){
                $subscription = false;
            }
          if ( $subscription && isset( $subscription['status'] ) && ($subscription['status'] == 'active' || $subscription['status'] === 'trialing') ){
              return true;
          } else {
              return false;
          }
      }
      return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function cancel( $uid=0, $lid=0, $transactionId='' )
    {
      if ( !$transactionId ){
          return false;
      }
      if ( !class_exists('\Stripe\StripeClient') ){
          require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
      }
      if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
      } else {
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
      }
      // try to get subscription_id from order meta ( new workflow )
      $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
      $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
      if ( $orderId ){
          $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
      }
      if ( isset( $subscriptionId ) && $subscriptionId !== '' ){
          try {
              $subscription =  $stripe->subscriptions->retrieve( $subscriptionId );
          } catch ( \Exception $e ){
              $subscription = false;
          }
          if ( $subscription && isset( $subscription['status'] ) && ($subscription['status'] == 'active' || $subscription['status'] === 'trialing') ){
            try {
                $result = $subscription->cancel();
            } catch (Stripe\Error\InvalidRequest $e){}
            if ( isset( $result->status ) && $result->status === 'canceled' ){
                return true;
            } else {
                return false;
            }
          } else {
              return false;
          }
      }
      return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function canDoPause( $uid=0, $lid=0, $subscriptionId='' )
    {
      if ( !$subscriptionId ){
          return false;
      }
      if ( !class_exists('\Stripe\StripeClient') ){
          require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
      }
      if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
      } else {
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
      }
      $StripeSubscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'ihc_stripe_subscription_id' );
      if ( isset( $StripeSubscriptionId ) && $StripeSubscriptionId !== '' ){
        try {
            $subscription =  $stripe->subscriptions->retrieve( $StripeSubscriptionId );
        } catch ( \Exception $e ){
            $subscription = false;
        }
          if ( $subscription && isset( $subscription['status'] ) && ($subscription['status'] == 'active' || $subscription['status'] === 'trialing') ){
              return true;
          } else {
              return false;
          }
      }
      return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function pause( $uid=0, $lid=0, $subscriptionId='' )
    {
      if ( !$subscriptionId ){
          return false;
      }
      if ( !class_exists('\Stripe\StripeClient') ){
          require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
      }
      if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
      } else {
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
      }
      $StripeSubscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'ihc_stripe_subscription_id' );

      if ( isset( $StripeSubscriptionId ) && $StripeSubscriptionId !== '' ){
          try {
              $subscription =  $stripe->subscriptions->retrieve( $StripeSubscriptionId );
          } catch ( \Exception $e ){
              $subscription = false;
          }

          if ( $subscription && isset( $subscription->status ) && ($subscription->status == 'active' || $subscription->status === 'trialing') ){
            try {
                $stripe->subscriptions->update(
                  $StripeSubscriptionId,
                  [
                    'pause_collection' => [
                                            'behavior' => 'void',
                                          ],
                  ]
                );
            } catch ( \Exception $e ){
                return false;
            }
            return true;
          } else {
              return false;
          }
      }
      return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function canDoResume( $uid=0, $lid=0, $subscriptionId='' )
    {
      if ( !$subscriptionId ){
          return false;
      }
      if ( !class_exists('\Stripe\StripeClient') ){
          require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
      }
      if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
      } else {
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
      }
      $StripeSubscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'ihc_stripe_subscription_id' );

      if ( isset( $StripeSubscriptionId ) && $StripeSubscriptionId !== '' ){
          try {
              $subscription =  $stripe->subscriptions->retrieve( $StripeSubscriptionId );
          } catch ( \Exception $e ){
              $subscription = false;
          }

          if ( $subscription && isset( $subscription->status ) && ($subscription->status == 'active' || $subscription->status === 'trialing') ){
              return true;
          } else {
              return false;
          }
      }
      return false;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function resume( $uid=0, $lid=0, $subscriptionId='' )
    {
      if ( !$subscriptionId ){
          return false;
      }
      if ( !class_exists('\Stripe\StripeClient') ){
          require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
      }
      if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_client_secret'] );
      } else {
          $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_connect_test_client_secret'] );
      }
      $StripeSubscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'ihc_stripe_subscription_id' );
      if ( isset( $StripeSubscriptionId ) && $StripeSubscriptionId !== '' ){
          try {
              $subscription =  $stripe->subscriptions->retrieve( $StripeSubscriptionId );
          } catch ( \Exception $e ){
              $subscription = false;
          }

          if ( $subscription && isset( $subscription->status ) && ($subscription->status === 'active' || $subscription->status === 'trialing') ){
            try {
              $stripe->subscriptions->update(
                $StripeSubscriptionId,
                [
                  'pause_collection' => '',
                ]
              );
            } catch ( \Exception $e ){
                return false;
            }
            return true;
          } else {
              return false;
          }
      }
      return false;
    }

    /**
     * @param array [ 'uid' => '', 'lid' => '', 'service' => '', 'level_label' => '', 'level_description' => '', 'subscription_id' => '', 'order_identificator' => '', 'order_id' => '' ]
     * @param object
     * @return none
     */
    public function migrateToConnect( $attr=[], $stripe=null )
    {
        // no subscription id -> out
        if ( !isset( $attr['subscription_id'] ) ){
            return;
        }

        $subscriptionIdInDb = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $attr['uid'], $attr['lid'] );
        $attr['level_label'] = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'level_label' );
        $attr['level_description'] = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'level_description' );

        // check if the product exists in our db
        if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
            $productId = \Indeed\Ihc\Db\Memberships::getOneMeta( $attr['lid'], 'ihc_stripe_product_id' );
        } else {
            $productId = \Indeed\Ihc\Db\Memberships::getOneMeta( $attr['lid'], 'ihc_stripe_product_id-test' );
        }

        // check if product exists in stripe
        if ( $productId !== null && $productId !== ''  ){
            try {
            	  $productObject = $stripe->products->retrieve( $productId,	[] );
            } catch( \Exception $e ){
            		$productObject = null;
            }
            if ( $productObject === null || !isset( $productObject->id ) ){
              	$productId = null;
            }
        }

        // create product
        if ( $productId === null || $productId === '' ){
            try {

              $productParams = [
                'name'                  => $attr['level_label'],
              ];
              if ( isset( $attr['level_description'] ) && $attr['level_description'] !== '' ){
                  $productParams['description'] = $attr['level_description'];
              }
              $statementDescriptor = $this->getStatementDescriptor();
              if ( $statementDescriptor !== null ){
                  $productParams['statement_descriptor'] = $statementDescriptor;
              }
              $product = $stripe->products->create( $productParams );
              $productId = isset( $product->id ) ? $product->id : '';

              // save product id in our db
              if ( $this->paymentSettings['ihc_stripe_connect_live_mode'] ){
                  \Indeed\Ihc\Db\Memberships::saveMeta( $attr['lid'], 'ihc_stripe_product_id', $productId );
              } else {
                  \Indeed\Ihc\Db\Memberships::saveMeta( $attr['lid'], 'ihc_stripe_product_id-test', $productId );
              }
            } catch( \Exception $e ){
                return false;
            }
        }

        try {
            $subscriptionObject = $stripe->subscriptions->retrieve( $attr['subscription_id'], [] );
        } catch ( \Exception $e ){
            return false;
        }

        $customerEmail = \Ihc_Db::user_get_email( $attr['uid'] );
        $amount = isset( $subscriptionObject->plan->amount ) ? $subscriptionObject->plan->amount : '';
        $currency = isset( $subscriptionObject->plan->currency ) ? $subscriptionObject->plan->currency : '';
        $intervalType = isset( $subscriptionObject->plan->interval ) ? $subscriptionObject->plan->interval : '';
        if ( $intervalType === '' ){
            $intervalType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'interval_type' );
        }
        $intervalValue = isset( $subscriptionObject->plan->interval_count ) ? $subscriptionObject->plan->interval_count : '';
        if ( $intervalValue === '' ){
            $intervalValue = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'interval_value' );
        }
        $paymentMethod = isset( $subscriptionObject->default_payment_method ) ? $subscriptionObject->default_payment_method : '';
        $customerId = isset( $subscriptionObject->customer ) ? $subscriptionObject->customer : '';
        $created = isset( $subscriptionObject->created ) ? $subscriptionObject->created : '';
        if ( $created === '' ){
            $created = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'created_at' );
        }
        // subscriptions cycles
        $subscriptionCyclesLimit = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'subscription_cycles_limit' );
        if ( $subscriptionCyclesLimit === false ){
            $billingType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'billing_type' );
            if(isset($billingType) && $billingType == 'bl_limited'){
              $subscriptionCyclesLimit = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'billing_limit_num' );
            }
        }

        if ( $subscriptionCyclesLimit === false || $subscriptionCyclesLimit < 1){
          return false;
        }

        // Create price for product
        $this->multiply = ihcStripeMultiplyForCurrency( $currency );
        try {
            $price = $stripe->prices->create([
                      'unit_amount' 		          => $amount,
                      'currency' 				          => $currency,
                      'recurring' 			          => [
                                                        'interval'        => $intervalType,
                                                        'interval_count'  => $intervalValue
                      ],
                      'product' 				          => $productId,
            ]);
            $priceId = isset( $price->id ) ? $price->id : '';
          } catch ( \Exception $e ){
              return false;
          }

          // set subscription params
          $subscriptionParams = [
                        'customer'                => $customerId,
                        'items'                   => [[
                                                      'price'       => $priceId,
                        ]],
                        'expand'                  => ['latest_invoice.payment_intent'],
                        'application_fee_percent' => $this->appFeePercentage,
                        'default_payment_method'  => $paymentMethod,
                        'off_session'             => true,
                        'metadata'                => [
                                                        'order_identificator'       => $attr['order_identificator'],
                                                        'uid'                       => $attr['uid'],
                                                        'lid'                       => $attr['lid'],
                                                        'service'                   => 'stripe_connect',
                        ],
          ];

          // cancel at
          if ( isset( $subscriptionCyclesLimit ) && $subscriptionCyclesLimit != '' ){
              $interval = (int)$intervalValue * (int)$subscriptionCyclesLimit;
              $cancelAt = strtotime('+' . $interval . ' ' . $intervalType, $created );
              $subscriptionParams['cancel_at'] = $cancelAt;
          }

          // do not pay this cycle
          $interval = (int)$intervalValue;
          $billingCyclesAnchor = strtotime('+' . $interval . ' ' . $intervalType );
          $billingCyclesAnchor = $billingCyclesAnchor - 10;
          $subscriptionParams['billing_cycle_anchor'] = $billingCyclesAnchor;
          $subscriptionParams['proration_behavior'] = 'none';

          // cancel the current subscription
          try {
              $stripe->subscriptions->cancel( $attr['subscription_id'], [] );
          } catch ( \Exception $e ){
              return false;
          }

          try {
              // create the subscription
              $subscription = $stripe->subscriptions->create( $subscriptionParams );
          } catch ( \Exception $e ){
              return false;
          }

          $subscriptionId = isset( $subscription->id ) ? $subscription->id : '';
          $customerId = isset( $subscription->customer ) ? $subscription->customer : false;

          // save subscription meta
          \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionIdInDb, 'payment_gateway', 'stripe_connect' );
          \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionIdInDb, 'ihc_stripe_subscription_id', $subscriptionId );

          // save customer id
          update_user_meta( $attr['uid'], 'ihc_stripe_customer_id', $customerId );

    }

}
