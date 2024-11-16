<?php
namespace Indeed\Ihc\Gateways;

class StripeCheckout extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'stripe_checkout_v2'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_stripe_checkout_v2', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_stripe_checkout_v2_success_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => 'ihc_stripe_checkout_v2_cancel_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => 'ihc_stripe_checkout_v2_locale_code', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
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

    protected $paymentTypeLabel               = 'Stripe Checkout V2'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];
    protected $multiply                       = '';


    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        //include IHC_PATH . 'classes/gateways/libraries/stripe-checkout/vendor/autoload.php';
        include IHC_PATH . 'classes/gateways/libraries/stripe-sdk/vendor/autoload.php';


        $this->multiply = ihcStripeMultiplyForCurrency( $this->paymentOutputData['currency'] );
        \Stripe\Stripe::setApiKey( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );


        /// locale
        $this->setLocale();

        $this->paymentOutputData['amount'] = $this->paymentOutputData['amount'] * $this->multiply;
        if ( $this->multiply==100 && $this->paymentOutputData['amount']> 0 && $this->paymentOutputData['amount']<50){
            $this->paymentOutputData['amount'] = 50;// 0.50 cents minimum amount for stripe transactions
        }
        $this->paymentOutputData['amount'] = round( $this->paymentOutputData['amount'], 0 );

        if ( $this->paymentOutputData['is_recurring'] ){
            // recurring
            $this->sessionId = $this->getSessionIdForRecurringPayment();
        } else {
            // single payment
            $this->sessionId = $this->getSessionIdForSinglePayment();
        }

        if ( $this->sessionId ){
            $this->redirectUrl = IHC_URL . 'classes/gateways/libraries/stripe-checkout/redirect.php?sessionId=' . $this->sessionId . '&key=' . $this->paymentSettings['ihc_stripe_checkout_v2_publishable_key'];
        } else {
            /// go home
            $this->redirectUrl = '';
        }

		    return $this;
    }

    /**
     * @param none
     * @return string
     */
    private function getSessionIdForSinglePayment()
    {
        $sessionData = [
          'payment_method_types'    => ['card'],
          "line_items" => [[
                  // included since ump version 10.10
                  'price_data'  => [
                                    'currency'          => $this->paymentOutputData['currency'],
                                    'unit_amount'       => $this->paymentOutputData['amount'],
                                    'product_data'      => [
                                              "name"        => $this->paymentOutputData['level_label'],
                                              "description" => " " . strip_tags( $this->paymentOutputData['level_description'] ),
                                              'metadata'    => [
                                                          'uid'                 => $this->paymentOutputData['uid'],
                                                          'lid'                 => $this->paymentOutputData['lid'],
                                                          'order_id'            => $this->paymentOutputData['order_id'],
                                                          'order_identificator' => $this->paymentOutputData['order_identificator'],
                                              ],
                                    ],
                  ],
                  "quantity"    => 1,

                  /*
                  // deprecated since version 10.10
                  "name"        => $this->paymentOutputData['level_label'],
                  "description" => " " . strip_tags( $this->paymentOutputData['level_description'] ),
                  "amount"      => $this->paymentOutputData['amount'],
                  "currency"    => $this->paymentOutputData['currency'],
                  */
          ]],

          'metadata'                  => [
                      'uid'                 => $this->paymentOutputData['uid'],
                      'lid'                 => $this->paymentOutputData['lid'],
                      'order_id'            => $this->paymentOutputData['order_id'],
                      'order_identificator' => $this->paymentOutputData['order_identificator'],
          ],

          'client_reference_id'       => $this->paymentOutputData['uid'] . '_' . $this->paymentOutputData['lid'], // {uid}_{lid}
          'success_url'               => $this->returnUrlAfterPayment,
          'cancel_url'                => $this->cancelUrlAfterPayment,
          'locale'                    => $this->locale,
          'mode'                      => 'payment', // added since ump version 10.10
          'customer_creation'         => 'always',
        ];

        if ( !empty( $this->paymentSettings['ihc_stripe_checkout_v2_use_user_email'] ) ){
            $sessionData['customer_email'] = $this->paymentOutputData['customer_email'];
        }

        $session = \Stripe\Checkout\Session::create( $sessionData );

        /// save payment intent
        $this->paymentIntent = isset( $session->payment_intent ) ? $session->payment_intent : '';
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();

        // since ump version 10.10
        $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $this->paymentOutputData['order_identificator'] );
        // $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $this->paymentIntent );


        return isset( $session->id ) ? $session->id : 0;
    }

    /**
     * @param none
     * @return string
     */
    private function getSessionIdForRecurringPayment()
    {

        $ihcPlanCode = $this->paymentOutputData['uid'] . '_' . $this->paymentOutputData['lid'] . '_' . indeed_get_unixtimestamp_with_timezone();
        $plan = array(
            "amount"          => $this->paymentOutputData['amount'],
            "interval_count"  => $this->paymentOutputData['interval_value'],
            "interval"        => $this->paymentOutputData['interval_type'],
            "product"         => array(
                                  "active"  => true,
                                  "name"    => $this->paymentOutputData['level_label'],
                                  'type'    => 'service',
                                  'metadata'                  => [
                                              'uid'                 => $this->paymentOutputData['uid'],
                                              'lid'                 => $this->paymentOutputData['lid'],
                                              'order_id'            => $this->paymentOutputData['order_id'],
                                              'order_identificator' => $this->paymentOutputData['order_identificator'],
                                  ],
            ),
            "active"          => true,
            "currency"        => $this->paymentOutputData['currency'],
            //"id"              => $ihcPlanCode,

            'metadata'                  => [
                        'uid'                 => $this->paymentOutputData['uid'],
                        'lid'                 => $this->paymentOutputData['lid'],
                        'order_id'            => $this->paymentOutputData['order_id'],
                        'order_identificator' => $this->paymentOutputData['order_identificator'],
            ],

        );
        if ( isset( $this->paymentOutputData['first_payment_interval_value'] ) ){
            $plan['trial_period_days'] = $this->paymentOutputData['first_payment_interval_value'];
        }
        $statementDescriptor = $this->getStatementDescriptor();
        if ( $statementDescriptor !== null ){
            $plan['product']['statement_descriptor'] = $statementDescriptor;
        }

        $return_data_plan = \Stripe\Plan::create( $plan );
        $planId = $return_data_plan->id;

        $sessionAttributes = [
            //'payment_method_types'      => ['card'],
            'line_items'                => [
              [
                'price'       => $planId,//$ihcPlanCode,//$price->id,
                "quantity"    => 1,
              ]
            ],
            //'client_reference_id'       => $this->paymentOutputData['order_id'], // {uid}_{lid}
            'success_url'               => $this->returnUrlAfterPayment,
            'cancel_url'                => $this->cancelUrlAfterPayment,
            'locale'                    => $this->locale,
            'mode'                      => 'subscription',
        ];

        if ( isset( $this->paymentOutputData['first_payment_interval_value'] ) ){
            $sessionAttributes['subscription_data']['trial_period_days'] = $this->paymentOutputData['first_payment_interval_value'];
        }

        if ( !empty( $this->paymentOutputData['first_amount'] ) ) {
              $this->paymentOutputData['first_amount'] = $this->paymentOutputData['first_amount'] * $this->multiply;
              if ( $this->multiply==100 && $this->paymentOutputData['first_amount']> 0 && $this->paymentOutputData['first_amount']<50){
                  $this->paymentOutputData['first_amount'] = 50;// 0.50 cents minimum amount for stripe transactions
              }
              $this->paymentOutputData['first_amount'] = round( $this->paymentOutputData['first_amount'], 0 );

            $sessionAttributes['line_items'][] = [
                    'price_data'  => [
                                      'currency'          => $this->paymentOutputData['currency'],
                                      'unit_amount'       => $this->paymentOutputData['first_amount'],
                                      'product_data'      => [
                                                "name"        => esc_html__('Initial payment', 'ihc'),
                                                "description" => esc_html__('Initial payment', 'ihc'),

                                                'metadata'                  => [
                                                            'uid'                 => $this->paymentOutputData['uid'],
                                                            'lid'                 => $this->paymentOutputData['lid'],
                                                            'order_id'            => $this->paymentOutputData['order_id'],
                                                            'order_identificator' => $this->paymentOutputData['order_identificator'],
                                                ],

                                      ],
                    ],
                    "quantity"    => 1,
            ];
            //$sessionAttributes['mode'] = 'subscription';
        }


        if ( !empty( $this->paymentSettings['ihc_stripe_checkout_v2_use_user_email'] ) ){
            $sessionAttributes['customer_email'] = $this->paymentOutputData['customer_email'];
        }


        $session = \Stripe\Checkout\Session::create( $sessionAttributes );

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        // since ump version 10.10
        //$orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $this->paymentOutputData['order_identificator'] );
        // $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $this->paymentIntent );

        return isset( $session->id ) ? $session->id : 0;
    }

    private function setLocale()
    {
        $this->locale = $this->paymentSettings['ihc_stripe_checkout_v2_locale_code'];

        if ( empty( $this->locale ) ){
            $this->locale = 'auto';
        }
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        //include IHC_PATH . 'classes/gateways/libraries/stripe-checkout/vendor/autoload.php';

        // process the data from payment gateway, ussualy comes on $_POST variables.
        $timestamp = indeed_get_unixtimestamp_with_timezone();
        $response = @file_get_contents( 'php://input' );
        $responseData = json_decode( $response, true );

        if ( empty( $responseData ) ){
          echo esc_ump_content('============= Ultimate Membership Pro - Stripe Checkout IPN ============= ');
          echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
          exit;
        }

        $currency = isset( $responseData['data']['object']['currency'] ) ? $responseData['data']['object']['currency'] : '';
        $this->multiply = ihcStripeMultiplyForCurrency( $currency );

        if ( !isset( $responseData['type'] ) ){
            return;
        }

        $isLastAPIVersion = true;
        $apiVersion = isset( $responseData['api_version'] ) ? $responseData['api_version'] : false;

        if ( $apiVersion !== null && $apiVersion !== '' ){
            if ( strtotime( $apiVersion ) < strtotime( '2022-08-01' ) ){
                $isLastAPIVersion = false;
            }
        }

        switch ( $responseData['type'] ){

            case 'checkout.session.completed':
              // since ump version 10.10 - this event its available only for single payments
              if ( isset($responseData['object']['mode']) && $responseData['object']['mode'] === 'subscription' ){
                  break;
              }
              if ( isset( $responseData['object']['payment_status'] ) && $responseData['object']['payment_status'] !== 'paid' ){
                  break;
              }
              $metaData = isset( $responseData['data']['object']['lines']['data'][0]['metadata'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata'] : '';
              if ( empty( $metaData ) ){
                  $metaData = isset( $responseData['data']['object']['lines']['data'][1]['metadata'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata'] : '';
              }
              if ( empty( $metaData ) ){
                  $metaData = isset( $responseData['data']['object']['metadata'] ) ? $responseData['data']['object']['metadata'] : '';
              }

              $service = isset( $metaData['service'] ) ? $metaData['service'] : false;
              if ( $service === 'stripe_connect' ){
                $this->webhookData['payment_status'] = 'other';
                break;
              }

              $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;
              $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
              $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );

              if ( $orderId == false && isset( $metaData['order_identificator'] ) ){
                  $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $metaData['order_identificator'] );
                  /*
                  if ( $transactionId == false ){
                      $transactionId = $metaData['order_identificator'];
                  }
                  */
              }

              if ( $orderId == false ){
                  // out
                  $this->webhookData['payment_status'] = 'other';
                  break;
              }
              $orderObject = new \Indeed\Ihc\Db\Orders();
              $orderData = $orderObject->setId( $orderId )
                                       ->fetch()
                                       ->get();

              $amount = isset( $responseData['data']['object']['amount'] ) ? $responseData['data']['object']['amount'] : 0;
              if ( $amount > 0 ){
                  $amount = $amount / $this->multiply;
              }
              $orderIdentificator = isset( $metaData['order_identificator'] ) ? $metaData['order_identificator'] : $transactionId;
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
              break;
            case 'charge.succeeded':
              /// Single Payment

              // check if it's made from stripe connect
              $metaData = isset( $responseData['data']['object']['lines']['data'][0]['metadata'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata'] : '';
              if ( empty( $metaData ) ){
                  $metaData = isset( $responseData['data']['object']['lines']['data'][1]['metadata'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata'] : '';
              }

              // payments made via stripe 13.0 sdk.
              if ( empty( $metaData) ){
                   $metaData = $responseData['data']['object'];
              }
              // end of payments made via stripe 13.0 sdk.

              // since ump version 10.10
              if ( empty( $metaData ) ){
                  $productId = isset( $responseData['data']['object']['lines']['data'][0]['price']['product'] ) ? $responseData['data']['object']['lines']['data'][0]['price']['product'] : false;
                  if ( $productId === false ){
                    $productId = isset( $responseData['data']['object']['lines']['data'][1]['price']['product'] ) ? $responseData['data']['object']['lines']['data'][1]['price']['product'] : false;
                  }
                  if ( !class_exists( 'Stripe\Stripe') ){
                      include IHC_PATH . 'classes/gateways/libraries/stripe-sdk/vendor/autoload.php';
                  }
                  if ( $productId ){
                      $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );
                      $productData = $stripe->products->retrieve( $productId, [] );
                      $metaData = isset( $productData->metadata ) ? $productData->metadata : false;
                  }

                  // search into invoice object - since version 11.7
                  if ( empty( $metaData ) ){
                      $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );
                      $invoiceId = isset( $responseData['data']['object']['invoice'] ) ? $responseData['data']['object']['invoice'] : '';
                      $invoice = $stripe->invoices->retrieve( $invoiceId, [] );
                      $metaData = isset( $invoice->lines->data[0]->metadata ) ? $invoice->lines->data[0]->metadata : '';
                      if ( empty( $metaData ) ){
                          $metaData = isset( $invoice->lines->data[1]->metadata ) ? $invoice->lines->data[1]->metadata : '';
                      }
                  }
              }

              $service = isset( $metaData['service'] ) ? $metaData['service'] : false;
              if ( $service === 'stripe_connect' ){
                $this->webhookData['payment_status'] = 'other';
                break;
              }
              // end of check if it's made from stripe connect

              $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;
              $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
              $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );

              // since ump version 10.10
              if ( $orderId == false && isset( $metaData['order_identificator'] ) ){
                  $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $metaData['order_identificator'] );
                  if ( $transactionId == false ){
                      $transactionId = $metaData['order_identificator'];
                  }
              }
              // end of since ump version 10.10

              if ( $orderId == false ){
                  // out
                  $this->webhookData['payment_status'] = 'other';
                  break;
              }
              $orderObject = new \Indeed\Ihc\Db\Orders();
              $orderData = $orderObject->setId( $orderId )
                                       ->fetch()
                                       ->get();

              $amount = isset( $responseData['data']['object']['amount'] ) ? $responseData['data']['object']['amount'] : 0;
              if ( $amount > 0 ){
                  $amount = $amount / $this->multiply;
              }
              //$orderIdentificator = $transactionId;
              $orderIdentificator = '';

              // >= 12.1
              if ( isset( $orderData->lid ) && \Indeed\Ihc\Db\Memberships::isRecurring( $orderData->lid ) ){
                  // out . its a recurring payment
                  $this->webhookData['payment_status'] = 'other';
                  break;
              }
              // end of >= 12.1

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
              break;

			//case 'invoice.payment_succeeded':  PREVIOUS STRIPE API VERSION
			case 'invoice.paid':

              // Recurring payment
              $subscriptionId = isset( $responseData['data']['object']['subscription'] ) ? $responseData['data']['object']['subscription'] : '';
              $amount = isset( $responseData['data']['object']['amount_paid'] ) ? $responseData['data']['object']['amount_paid'] : 0;
              if ( $amount > 0 ){
                  $amount = $amount / $this->multiply;
              }

              $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;
              if ( $transactionId === false ){
                  // is free trial
                  $transactionId = isset($responseData['data']['object']['id']) ? $responseData['data']['object']['id'] : false;
              }

              $metaData = isset( $responseData['data']['object']['lines']['data'][0]['metadata'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata'] : '';
              if ( empty( $metaData ) ){
                  $metaData = isset( $responseData['data']['object']['lines']['data'][1]['metadata'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata'] : '';
              }

              // payments made via stripe 13.0 sdk. => since version 12.5
              if ( empty( $metaData ) && isset( $responseData['data']['object']['lines']['data'][0]['price']['metadata'] ) && count( $responseData['data']['object']['lines']['data'][0]['price']['metadata'] ) > 0 ){
                   $metaData = $responseData['data']['object']['lines']['data'][0]['price']['metadata'];
              }
              // end of payments made via stripe 13.0 sdk. end of version 12.5

              // since version 10.10
              if ( empty( $metaData ) ){
                  $productId = isset( $responseData['data']['object']['lines']['data'][0]['price']['product'] ) ? $responseData['data']['object']['lines']['data'][0]['price']['product'] : false;
                  if ( $productId === false ){
                    $productId = isset( $responseData['data']['object']['lines']['data'][1]['price']['product'] ) ? $responseData['data']['object']['lines']['data'][1]['price']['product'] : false;
                  }
                  if ( !class_exists( 'Stripe\Stripe') ){
                      include IHC_PATH . 'classes/gateways/libraries/stripe-sdk/vendor/autoload.php';
                  }
                  $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );
                  $productData = $stripe->products->retrieve( $productId, [] );
                  $metaData = [
                                'lid'                   => isset( $productData->metadata->lid ) ? $productData->metadata->lid : 0,
                                'order_id'              => isset( $productData->metadata->order_id )? $productData->metadata->order_id : 0,
                                'order_identificator'   => isset( $productData->metadata->order_identificator )? $productData->metadata->order_identificator : 0,
                                'uid'                   => isset( $productData->metadata->uid )? $productData->metadata->uid : 0,
                  ];
              }

              // since version 10.10



              $orderIdentificator =  isset( $metaData['order_identificator'] ) ? $metaData['order_identificator'] : '';

              // update payment method for subscription
              $paymentIntentId = $transactionId;

              $this->webhookData = [
                                          'transaction_id'              => $transactionId,
                                          'uid'                         => isset( $metaData['uid'] ) ? $metaData['uid'] : 0,
                                          'lid'                         => isset( $metaData['lid'] ) ? $metaData['lid'] : 0,
                                          'order_identificator'         => $orderIdentificator,
                                          'subscription_id'             => isset( $subscriptionId ) ? $subscriptionId : '',
                                          'amount'                      => $amount,
                                          'currency'                    => $currency,
                                          //'payment_details'             => $responseData,
                                          'payment_status'              => 'completed',
              ];
              //

              // update stripe subscription
              $service =  isset( $metaData['service'] ) ? $metaData['service'] : '';
              if ( $service !== '' && $service === 'stripe_connect'){
                  $this->webhookData['payment_status'] = 'other';
                  break;
              }
              //$this->updateStripeSubscriptionDetails( $responseData );
              // update stripe subscription - set the cancel at


              $this->updateSubscriptionSetCancel([
                    'uid'                         => isset( $metaData['uid'] ) ? $metaData['uid'] : 0,
                    'lid'                         => isset( $metaData['lid'] ) ? $metaData['lid'] : 0,
                    'subscription_id'             => isset( $subscriptionId ) ? $subscriptionId : '',
              ]);


              break;

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
            case 'charge.dispute.funds_withdrawn':
              /// make level expired - failed
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
            case 'subscription_schedule.canceled':
              // CANCEL
              $subscriptionId = isset( $responseData['data']['object']['subscription'] ) ? $responseData['data']['object']['subscription'] : '';

              $transactionId = isset($responseData['data']['object']['payment_intent']) ? $responseData['data']['object']['payment_intent'] : false;
              if ( $transactionId === false ){
                  // is free trial
                  $transactionId = isset($responseData['data']['object']['id']) ? $responseData['data']['object']['id'] : false;
              }

              $metaData = isset( $responseData['data']['object']['lines']['data'][0]['metadata'] ) ? $responseData['data']['object']['lines']['data'][0]['metadata'] : '';
              if ( empty( $metaData ) ){
                  $metaData = isset( $responseData['data']['object']['lines']['data'][1]['metadata'] ) ? $responseData['data']['object']['lines']['data'][1]['metadata'] : '';
              }

              // payments made via stripe 13.0 sdk.
              if ( empty( $metaData) ){
                   $metaData = $responseData['data']['object'];
              }
              // end of payments made via stripe 13.0 sdk.

              $orderIdentificator =  isset( $metaData['order_identificator'] ) ? $metaData['order_identificator'] : '';

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
            case 'customer.subscription.deleted':

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
            default:
              $this->webhookData['payment_status'] = 'other';
              break;
        }

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
     * @param string
     * @return none
     */
    public function cancel( $uid=0, $lid=0, $transactionId='' )
    {
        //include IHC_PATH . 'classes/gateways/libraries/stripe-checkout/vendor/autoload.php';
        include IHC_PATH . 'classes/gateways/libraries/stripe-sdk/vendor/autoload.php';

        if ( !$transactionId ){
            return false;
        }
        if ( empty( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] ) ){
            return false;
        }

        \Stripe\Stripe::setApiKey( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );

        // try to get subscription_id from order meta ( new workflow )
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId ){
            $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        }
        if ( isset( $subscriptionId ) && $subscriptionId !== '' ){
          $subscription = \Stripe\Subscription::retrieve( $subscriptionId );
          if ( $subscription && isset( $subscription['status'] ) && ( $subscription['status'] == 'active' || $subscription['status'] == 'trialing' ) ){
              try {
                  $result = $subscription->cancel();
              } catch (Stripe\Error\InvalidRequest $e){}
          }
          if ( isset( $result->status ) && $result->status === 'canceled' ){
              return true;
          } else {
              return false;
          }
        }

        $orderId = \Ihc_Db::getLastOrderByTxnId( $transactionId );
        $orderMetas = new \Indeed\Ihc\Db\OrderMeta();
        $chargeId = $orderMetas->get( $orderId, 'charge_id' );
        $lid = \Ihc_Db::getLidByOrder( $orderId );

        if ( $chargeId === null || $chargeId == '' ){
            /// get from customer_id - lid . this is for the case when recurring is with trial without any payment made
            $customerId = $orderMetas->get( $orderId, 'customer_id' );
            if ( !$customerId ){
                return false;
            }
            $customer = \Stripe\Customer::retrieve( $customerId );
            if ( !$customer ){
                return false;
            }

            foreach ( $customer->subscriptions as $subscription ){
                if ( isset( $subscription['metadata']->lid ) && $subscription['metadata']->lid == $lid ){ /// add extra condition here
                    $subscriptionId = $subscription->id;
                    break;
                }
            }
        } else {
            $chargeObject = \Stripe\Charge::retrieve( $chargeId );
            $customerId = $chargeObject->customer;
            $invoiceObject = \Stripe\Invoice::retrieve( $chargeObject->invoice );

            if ( isset( $invoiceObject->lines->data[0]->id ) && strpos( $invoiceObject->lines->data[0]->id, 'sub' ) === 0 ){
                $subscriptionId = $invoiceObject->lines->data[0]->id;
            } else if ( isset( $invoiceObject->lines->data[0]->subscription ) && strpos( $invoiceObject->lines->data[0]->subscription, 'sub' ) === 0 ){
                $subscriptionId = $invoiceObject->lines->data[0]->subscription;
            }

            if ( empty( $subscriptionId ) ){
                for ( $i=1; $i<count($invoiceObject->lines->data); $i++ ){
                    if ( isset( $invoiceObject->lines->data[$i]->id ) && strpos( $invoiceObject->lines->data[$i]->id, 'sub' ) === 0 ){
                        $subscriptionId = $invoiceObject->lines->data[$i]->id;
                    } else if ( isset( $invoiceObject->lines->data[$i]->subscription ) && strpos( $invoiceObject->lines->data[$i]->subscription, 'sub' ) === 0 ){
                        $subscriptionId = $invoiceObject->lines->data[$i]->subscription;
                    }
                }
            }
        }

        if ( !$subscriptionId ){
            return false;
        }

        $subscription = \Stripe\Subscription::retrieve( $subscriptionId );
        if ( $subscription  && isset( $subscription['status'] ) && ( $subscription['status'] == 'active' || $subscription['status'] === 'trialing' ) ){
            try {
                $result = $subscription->cancel();
            } catch ( Stripe\Error\InvalidRequest $e ){}

            if ( isset( $result->status ) && $result->status === 'canceled' ){
                return true;
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
    public function canDoCancel( $uid=0, $lid=0, $transactionId='' )
    {
        //include IHC_PATH . 'classes/gateways/libraries/stripe-checkout/vendor/autoload.php';
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }
        //require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';

        if ( !$transactionId ){
            return false;
        }
        if ( empty( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] ) ){
            return false;
        }

        \Stripe\Stripe::setApiKey( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );

        // try to get subscription_id from order meta ( new workflow )
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId ){
            $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        }
        if ( isset( $subscriptionId ) && $subscriptionId !== '' ){
            $subscription = \Stripe\Subscription::retrieve( $subscriptionId );
            if ( $subscription && isset( $subscription['status'] ) && ($subscription['status'] == 'active' || $subscription['status'] === 'trialing') ){
                return true;
            } else {
                return false;
            }
        }

        if ( !$orderId ){
            $orderId = \Ihc_Db::getLastOrderByTxnId( $transactionId );
        }
        $chargeId = $orderMeta->get( $orderId, 'charge_id' );
        $lid = \Ihc_Db::getLidByOrder( $orderId );

        if ( $chargeId === null || $chargeId == '' ){
            /// get from customer_id - lid . this is for the case when recurring is with trial without any payment made
            $customerId = $orderMeta->get( $orderId, 'customer_id' );
            if ( !$customerId ){
                return false;
            }
            $customer = \Stripe\Customer::retrieve( $customerId );
            if ( !$customer ){
                return false;
            }

            foreach ( $customer->subscriptions as $subscription ){
                if ( isset( $subscription['metadata']->lid ) && $subscription['metadata']->lid == $lid ){ /// add extra condition here
                    $subscriptionId = $subscription->id;
                    break;
                }
            }
        } else {
            $chargeObject = \Stripe\Charge::retrieve( $chargeId );
            $customerId = $chargeObject->customer;
            $invoiceObject = \Stripe\Invoice::retrieve( $chargeObject->invoice );

            if ( isset( $invoiceObject->lines->data[0]->id ) && strpos( $invoiceObject->lines->data[0]->id, 'sub' ) === 0 ){
                $subscriptionId = $invoiceObject->lines->data[0]->id;
            } else if ( isset( $invoiceObject->lines->data[0]->subscription ) && strpos( $invoiceObject->lines->data[0]->subscription, 'sub' ) === 0 ){
                $subscriptionId = $invoiceObject->lines->data[0]->subscription;
            }

            if ( empty( $subscriptionId ) ){
                for ( $i=1; $i<count($invoiceObject->lines->data); $i++ ){
                    if ( isset( $invoiceObject->lines->data[$i]->id ) && strpos( $invoiceObject->lines->data[$i]->id, 'sub' ) === 0 ){
                        $subscriptionId = $invoiceObject->lines->data[$i]->id;
                    } else if ( isset( $invoiceObject->lines->data[$i]->subscription ) && strpos( $invoiceObject->lines->data[$i]->subscription, 'sub' ) === 0 ){
                        $subscriptionId = $invoiceObject->lines->data[$i]->subscription;
                    }
                }
            }
        }

        if ( !$subscriptionId ){
            return false;
        }

        $subscription = \Stripe\Subscription::retrieve( $subscriptionId );

        if ( $subscription && isset( $subscription['status'] ) && ($subscription['status'] == 'active' || $subscription['status'] === 'trialing') ){
            return true;
        }

        return false;
    }

    /**
     * this will update stripe subscription
     * @param array
     * @return none
     */
    public function updateStripeSubscriptionDetails( $transactionDetails=[] )
    {
        \Stripe\Stripe::setApiKey( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );
        if ( !isset( $transactionDetails['data']['object']['attempted'] ) || $transactionDetails['data']['object']['attempted'] != 1 ){
            return;
        }
        $subscriptionId = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $this->webhookData['uid'], $this->webhookData['lid'] );
        $billing_type = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'billing_type' );
        $billing_limit_num = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'billing_limit_num' );
        if ( $billing_type == 'bl_limited' && $billing_limit_num != '' ){
            return \Stripe\SubscriptionSchedule::create([
                    'customer'    => $transactionDetails['data']['object']['customer'],
                    'start_date'  => $transactionDetails['data']['object']['created'],
                    'end_behavior' => 'cancel',
                    'phases'      => [

                                        [

                                          'items' => [
                                            [
                                              'price'     => (isset($transactionDetails['data']['object']['lines']['data'][0]['plan']['id'])) ? $transactionDetails['data']['object']['lines']['data'][0]['plan']['id'] : '',
                                          //    'plan'      => (isset($transactionDetails['data']['object']['lines']['data'][0]['plan']['id'])) ? $transactionDetails['data']['object']['lines']['data'][0]['plan']['id'] : '',
                                              'quantity'  => 1,
                                            ],
                                          ],


                                          'iterations' => $billing_limit_num,
                                        ],
                    ],
            ]);
        }
    }

    /**
     * @param array [ 'subscription_id' => '', 'uid' => '', 'lid' => '' ]
     * @return none
     */
    public function updateSubscriptionSetCancel( $attr='' )
    {
        // since version 10.10
        if ( !class_exists( 'Stripe\Stripe') ){
            include IHC_PATH . 'classes/gateways/libraries/stripe-sdk/vendor/autoload.php';
        }
        //require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        if ( $attr['subscription_id'] === '' || $attr['uid'] === 0 || $attr['lid'] === 0 ){
            return false;
        }

        $stripe = new \Stripe\StripeClient( $this->paymentSettings['ihc_stripe_checkout_v2_secret_key'] );

        try {
            $subscriptionObject = $stripe->subscriptions->retrieve( $attr['subscription_id'], [] );
        } catch ( \Exception $e ){
            return false;
        }

        $subscriptionIdInDb = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $attr['uid'], $attr['lid'] );
        $created = isset( $subscriptionObject->created ) ? $subscriptionObject->created : '';
        if ( $created === '' ){
            $created = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'created_at' );
        }
        $intervalType = isset( $subscriptionObject->plan->interval ) ? $subscriptionObject->plan->interval : '';
        if ( $intervalType === '' ){
            $intervalType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'interval_type' );
        }
        $intervalValue = isset( $subscriptionObject->plan->interval_count ) ? $subscriptionObject->plan->interval_count : '';
        if ( $intervalValue === '' ){
            $intervalValue = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'interval_value' );
        }
        $cyclesLimit = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'subscription_cycles_limit' );
        if ( $cyclesLimit === false ){
            $billingType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'billing_type' );
            if(isset($billingType) && $billingType == 'bl_limited'){
              $cyclesLimit = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionIdInDb, 'billing_limit_num' );
            }
        }
        if ( $cyclesLimit === false || $cyclesLimit < 1){
          return false;
        }
        $intervalValue = (int)$intervalValue * (int)$cyclesLimit;
        try {
            $cancelAt = strtotime('+' . $intervalValue . ' ' . $intervalType, (int)$created );
        } catch ( \Exception $e ){
            return false;
        }

        try {
            $stripe->subscriptions->update(
                $attr['subscription_id'],
                [ 'cancel_at' => $cancelAt ]
            );
        } catch ( \Exception $e ){
            return false;
        }
    }

    /**
     * @param none
     * @return string
     */
    public function getStatementDescriptor()
    {
        $statementDescriptor = get_option( 'ihc_stripe_checkout_v2_descriptor', false );
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

}
