<?php
namespace Indeed\Ihc\Gateways;

class Mollie extends \Indeed\Ihc\Gateways\PaymentAbstract
{
    protected $paymentType                    = 'mollie'; // slug. cannot be empty.

    protected $paymentRules                   = [
                'canDoRecurring'						                  => true, // does current payment gateway supports recurring payments.
                'canDoTrial'							                    => true, // does current payment gateway supports trial subscription
                'canDoTrialFree'						                  => true, // does current payment gateway supports free trial subscription
                'canDoTrialPaid'						                  => true, // does current payment gateway supports paid trial subscription
                'canApplyCouponOnRecurringForFirstPayment'		=> true, // if current payment gateway support coupons on recurring payments only for the first transaction
                'canApplyCouponOnRecurringForFirstFreePayment'=> true, // if current payment gateway support coupons with 100% discount on recurring payments only for the first transaction.
                'canApplyCouponOnRecurringForEveryPayment'	  => true, // if current payment gateway support coupons on recurring payments for every transaction
                'paymentMetaSlug'                             => 'payment_mollie', // payment gateway slug. exenple: paypal, stripe, etc.
                'returnUrlAfterPaymentOptionName'             => 'ihc_mollie_return_page', // option name ( in wp_option table ) where it's stored the return URL after a payment is done.
                'returnUrlOnCancelPaymentOptionName'          => '', // option name ( in wp_option table ) where it's stored the return URL after a payment is canceled.
                'paymentGatewayLanguageCodeOptionName'        => '', // option name ( in wp_option table ) where it's stored the language code.
    ]; // some payment does not support all our features
    protected $intervalSubscriptionRules      = [
                'daysSymbol'               => 'days',
                'weeksSymbol'              => 'weeks',
                'monthsSymbol'             => 'months',
                'yearsSymbol'              => '',
                'daysSupport'              => true,
                'daysMinLimit'             => 1,
                'daysMaxLimit'             => 365,
                'weeksSupport'             => true,
                'weeksMinLimit'            => 1,
                'weeksMaxLimit'            => 52,
                'monthsSupport'            => true,
                'monthsMinLimit'           => 1,
                'monthsMaxLimit'           => 12,
                'yearsSupport'             => false,
                'yearsMinLimit'            => '',
                'yearsMaxLimit'            => '',
                'maximumRecurrenceLimit'   => '', // leave this empty for unlimited
                'minimumRecurrenceLimit'   => 1,
                'forceMaximumRecurrenceLimit'   => false,
    ];
    protected $intervalTrialRules             = [
                              'daysSymbol'               => 'days',
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
                              'weeksMinLimit'            => 1,
                              'weeksMaxLimit'            => 52,
                              'monthsSupport'            => false,
                              'monthsMinLimit'           => 1,
                              'monthsMaxLimit'           => 18,
                              'yearsSupport'             => false,
                              'yearsMinLimit'            => 1,
                              'yearsMaxLimit'            => 1,
    ];

    protected $stopProcess                    = false;
    protected $inputData                      = []; // input data from user
    protected $paymentOutputData              = [];
    protected $paymentSettings                = []; // api key, some credentials used in different payment types

    protected $paymentTypeLabel               = 'Mollie'; // label of payment
    protected $redirectUrl                    = ''; // redirect to payment gateway or next page
    protected $defaultRedirect                = ''; // redirect home
    protected $errors                         = [];

    /**
     * @param none
     * @return object
     */
    public function charge()
    {
        include IHC_PATH . 'classes/gateways/libraries/mollie/vendor/autoload.php';
        $mollie = new \Mollie\Api\MollieApiClient();
        $siteUrl = trailingslashit( site_url() );
        $webhook = add_query_arg( 'ihc_action', 'mollie', $siteUrl );
        $locale = get_locale();
        $this->paymentOutputData['amount'] = number_format((float)$this->paymentOutputData['amount'], 2, '.', '');

        try {
            $mollie->setApiKey( $this->paymentSettings['ihc_mollie_api_key'] );
        } catch ( \Mollie\Api\Exceptions\ApiException $e ){
            return $this;
        }

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();

        if ( $this->paymentOutputData['is_recurring'] ){
            // recurring

            // Create customer.
            $customer = $mollie->customers->create([
                "name"    => $this->paymentOutputData['customer_name'],
                "email"   => $this->paymentOutputData['customer_email'],
            ]);

            $recurring_first_payment_vars = $this->paymentOutputData['uid'] . '-' . $this->paymentOutputData['lid'] . '-' . time();

            $paymentParams = [
                "amount"          => [
                                    "currency" => $this->paymentOutputData['currency'],
                                    "value"    => $this->paymentOutputData['amount'],
                ],
                "description"     => esc_html__('Buy ', 'ihc') . $this->paymentOutputData['level_label'],
                "redirectUrl"     => isset( $this->returnUrlAfterPayment ) ? $this->returnUrlAfterPayment : $siteUrl,
                "webhookUrl"      => $webhook,
                "locale"          => $locale,
				        "method"		      => 'creditcard',
                "metadata"        => [
                                    'order_id'                     => $this->paymentOutputData['order_id'],
                                    'uid'                          => $this->paymentOutputData['uid'],
                                    'lid'                          => $this->paymentOutputData['lid'],
                                    'order_identificator'          => $this->paymentOutputData['order_identificator'],
                                    'recurring_first_payment_vars' => $recurring_first_payment_vars,
                ],
                "sequenceType"    => \Mollie\Api\Types\SequenceType::SEQUENCETYPE_FIRST,
            ];
            if ( isset( $this->paymentOutputData['first_amount'] ) && $this->paymentOutputData['first_amount'] !== false ){
                $paymentParams['amount']['value'] = number_format( (float)$this->paymentOutputData['first_amount'], 2, '.', '' );
                $paymentParams['description'] = esc_html__('Buy ', 'ihc') . $this->paymentOutputData['level_label']
                                                . esc_html__( '. For trial period you pay: ', 'ihc' )
                                                . $paymentParams['amount']['value'] . $this->paymentOutputData['currency']
                                                . esc_html__( '. After trial period you will pay: ', 'ihc' )
                                                . $this->paymentOutputData['amount'] . $this->paymentOutputData['currency'];
            }

            $payment = $customer->createPayment( $paymentParams );

            $startTime = date( 'Y-m-d' );
            if ( isset( $this->paymentOutputData['first_payment_interval_value'] ) && $this->paymentOutputData['first_payment_interval_value'] != '' ){
                // Trail Period
                $temporaryDate = $this->paymentOutputData['first_payment_interval_value'] . ' days';
            } else {
                // Standard Recurring Period
                $temporaryDate = $this->paymentOutputData['interval_value'] . ' ' . $this->paymentOutputData['interval_type'];
            }

            if ( strpos( $temporaryDate, 's') !== false ){
                $temporaryInterval = str_replace( 's', '', $temporaryDate );
                $startTime = strtotime( $startTime );
                $startTime = strtotime( $temporaryInterval, $startTime );
                $startTime = date( 'Y-m-d', $startTime );
            }

            // Charging periodically with subscriptions
            $SubscriptionParams = [
                "amount"      => [
                    "currency"    => $this->paymentOutputData['currency'],
                    "value"       => $this->paymentOutputData['amount'],
                ],
                "startDate"   => $startTime,
                "interval"    => $this->paymentOutputData['interval_value'] . ' ' . $this->paymentOutputData['interval_type'],
                "description" => esc_html__('Sign for ', 'ihc') . $this->paymentOutputData['level_label'] .esc_html__(' subscription', 'ihc'),
                "webhookUrl"  => $webhook,
                'metadata'    => [
                                'order_id'              => $this->paymentOutputData['order_id'],
                                'uid'                   => $this->paymentOutputData['uid'],
                                'lid'                   => $this->paymentOutputData['lid'],
                                'order_identificator'   => $this->paymentOutputData['order_identificator'],
                ],
            ];
            if ( isset( $this->paymentOutputData['subscription_cycles_limit'] ) && $this->paymentOutputData['subscription_cycles_limit'] != '' ){
                $SubscriptionParams['times'] = $this->paymentOutputData['subscription_cycles_limit'];
            }
            update_user_meta( $this->paymentOutputData['uid'], $recurring_first_payment_vars, $SubscriptionParams );

        } else {
            // single payment
            $payment = $mollie->payments->create([
                "amount" => [
                    "currency"    => $this->paymentOutputData['currency'],
                    "value"       => $this->paymentOutputData['amount'],
                ],
                "description"     => esc_html__('Buy ', 'ihc') . $this->paymentOutputData['level_label'],
                "redirectUrl"     => isset( $this->returnUrlAfterPayment ) ? $this->returnUrlAfterPayment : $siteUrl,
                "webhookUrl"      => $webhook,
                "locale"          => $locale,
				        "method"		      => NULL,
            ]);

            $orderMeta->save( $this->paymentOutputData['order_id'], 'order_identificator', $payment->id );
        }

        $this->redirectUrl = $payment->getCheckoutUrl();
		    return $this;
    }

    /**
     * @param none
     * @return none
     */
    public function webhook()
    {
        if ( !isset( $_POST["id"] ) ){
            echo esc_ump_content('============= Ultimate Membership Pro - Mollie IPN ============= ');
            echo esc_ump_content('<br/><br/>No Payments details sent. Come later');
            exit;
        }
        include IHC_PATH . 'classes/gateways/libraries/mollie/vendor/autoload.php';

        // process the data from payment gateway, ussualy comes on $_POST variables.
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey( $this->paymentSettings['ihc_mollie_api_key'] );
        $transactionId = sanitize_text_field( $_POST["id"] );
		\Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__("  Payment Webhook - transaction_id is ", 'ihc') . $_POST["id"], 'payments');

		$payment = $mollie->payments->get( $transactionId );
		\Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__("  Payment Webhook - Payment details ", 'ihc') .  serialize($payment), 'payments');

        $amount = isset( $payment->amount->value ) ? $payment->amount->value : '';
        $currency = isset( $payment->amount->currency ) ? $payment->amount->currency : '';

        $this->webhookData = [
                                'transaction_id'              => $transactionId,
                                'order_identificator'         => '',
                                'amount'                      => $amount,
                                'currency'                    => $currency,
                                'payment_details'             => $payment,
                                'payment_status'              => '',
        ];

        // recurring new api - since version 11.1
        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {

            $this->webhookData['payment_status'] = 'completed';
            $this->webhookData['uid'] = isset( $payment->metadata->uid ) ? $payment->metadata->uid : false;
            $this->webhookData['lid'] = isset( $payment->metadata->lid ) ? $payment->metadata->lid : false;
            $this->webhookData['order_identificator'] = isset( $payment->metadata->order_identificator ) ? $payment->metadata->order_identificator : '';

            // since version 12.6.2
            if ( !empty( $payment->customerId ) ){
                $this->webhookData['customer_id'] = $payment->customerId;
            }
            if ( !empty( $payment->subscriptionId ) ){
                $this->webhookData['subscription_id'] = $payment->subscriptionId;
            }
            // end of since version 12.6.2

            if ( isset( $payment->metadata->recurring_first_payment_vars ) && get_user_meta( $this->webhookData['uid'], $payment->metadata->recurring_first_payment_vars, false ) !== false ){
                $mandateId = isset( $payment->mandateId ) ? $payment->mandateId : false;
                $customerId = isset( $payment->customerId ) ? $payment->customerId : false;


                if ( $customerId !== false && $customerId !== '' && $mandateId !== false && $mandateId !== '' ){

                    $customer = $mollie->customers->get( $customerId );
                    $SubscriptionParams = get_user_meta( $this->webhookData['uid'], $payment->metadata->recurring_first_payment_vars, true );
                    // remove option
                    delete_user_meta( $this->webhookData['uid'], $payment->metadata->recurring_first_payment_vars );

                    $SubscriptionParams['mandateId'] = $mandateId;

                    try {
                        $subscriptionObject = $customer->createSubscription( $SubscriptionParams );
                        if ( isset( $subscriptionObject->id  ) ){
                            $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                            $orderMeta->save( $payment->metadata->order_id, 'subscription_id', $subscriptionObject->id );
                        }
                        if ( isset( $customer->id ) ){
                            $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                            $orderMeta->save( $payment->metadata->order_id, 'customer_id', $customer->id );
                        }
                    } catch ( \Exception $e ){

                    }

                }

            }
            // end of recurring new api - since version 11.1

			      \Ihc_User_Logs::write_log( $this->paymentTypeLabel . esc_html__("  Payment Webhook - Order Identificator is ", 'ihc') .  $this->webhookData['order_identificator'], 'payments');

            // Reccurring Subscription on old systems > UMP v 9.3
            if ( !empty( $payment->subscriptionId ) && empty( $this->webhookData['uid'] ) && empty( $this->webhookData['lid'] ) ){
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                $firstOrderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $payment->subscriptionId );
                $orderObject = new \Indeed\Ihc\Db\Orders();
                $firstOrderDetails = $orderObject->setId( $firstOrderId )->fetch()->get();
                $this->webhookData['uid'] = isset( $firstOrderDetails->uid ) ? $firstOrderDetails->uid : false;
                $this->webhookData['lid'] = isset( $firstOrderDetails->lid ) ? $firstOrderDetails->lid : false;
            }
            // end of old systems

            if ( $this->webhookData['uid'] == false && $this->webhookData['lid'] == false && $this->webhookData['order_identificator'] == '' ){
                // single payment
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'order_identificator', $transactionId );

                $orderObject = new \Indeed\Ihc\Db\Orders();
                $orderData = $orderObject->setId( $orderId )
                                         ->fetch()
                                         ->get();

                $this->webhookData['uid'] = isset( $orderData->uid ) ? $orderData->uid : 0;
                $this->webhookData['lid'] = isset( $orderData->lid ) ? $orderData->lid : 0;
                $this->webhookData['order_identificator'] = $transactionId;
            }

        } elseif ($payment->isOpen()) {
        } elseif ($payment->isPending()) {
            /// pending
            $this->webhookData['payment_status'] = 'pending';
        } elseif ($payment->isFailed()) {
            // fail
            $this->webhookData['payment_status'] = 'failed';
        } elseif ($payment->isExpired()) {
        } elseif ($payment->isCanceled()) {
            /// cancel
            $this->webhookData['payment_status'] = 'cancel';
        } elseif ($payment->hasRefunds()) {
            /// fail
            $this->webhookData['payment_status'] = 'refund';
        } elseif ($payment->hasChargebacks()) {}
        //create this array with informations about the transaction, subscription and user .

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
        include IHC_PATH . 'classes/gateways/libraries/mollie/vendor/autoload.php';

        $mollie = new \Mollie\Api\MollieApiClient();
        try {
            $mollie->setApiKey( $this->paymentSettings['ihc_mollie_api_key'] );
        } catch ( \Mollie\Api\Exceptions\ApiException $e ){
            return false;
        }
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId == null || $orderId == '' ){
            return false;
        }
        $customerId = $orderMeta->get( $orderId, 'customer_id' );
        if ( $customerId == null || $customerId == '' ){
            return false;
        }
        $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        if ( $subscriptionId == null || $subscriptionId == '' ){
            return false;
        }
        $customer = $mollie->customers->get( $customerId );
        return $customer->cancelSubscription( $subscriptionId );
    }

    /**
     * This function will check if a subscription can be canceled.
     * @param int
     * @param int
     * @param string
     * @return boolean
     */
    public function canDoCancel( $uid=0, $lid=0, $transactionId='' )
    {
        include IHC_PATH . 'classes/gateways/libraries/mollie/vendor/autoload.php';

        $mollie = new \Mollie\Api\MollieApiClient();
        try {
            $mollie->setApiKey( $this->paymentSettings['ihc_mollie_api_key'] );
        } catch ( \Mollie\Api\Exceptions\ApiException $e ){
            return false;
        }
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'transaction_id', $transactionId );
        if ( $orderId == null || $orderId == '' ){
            return false;
        }
        $customerId = $orderMeta->get( $orderId, 'customer_id' );
        if ( $customerId == null || $customerId == '' ){
            return false;
        }
        $subscriptionId = $orderMeta->get( $orderId, 'subscription_id' );
        if ( $subscriptionId == null || $subscriptionId == '' ){
            return false;
        }
        $customer = $mollie->customers->get( $customerId );
        if ( !$customer ){
            return false;
        }
        return true;
    }

    /**
     * @param string
     * @return array
     */
    private function getCustomerAndSubscriptionIdByTransaction( $transactionId='' )
    {
        include IHC_PATH . 'classes/gateways/libraries/mollie/vendor/autoload.php';
        $mollie = new \Mollie\Api\MollieApiClient();
        try {
            $mollie->setApiKey( $this->paymentSettings[] );
        } catch ( \Mollie\Api\Exceptions\ApiException $e ){
            return [];
        }
        $transaction = $mollie->payments->get( $transactionId );
        if ( $transaction === null || $transaction === false ){
            return [];
        }
        if ( !isset( $transaction->subscriptionId ) || !isset( $transaction->customerId ) ){
            return [];
        }
        return [
            'subscriptionId'        => $transaction->subscriptionId,
            'customerId'            => $transaction->customerId
        ];
    }

}
