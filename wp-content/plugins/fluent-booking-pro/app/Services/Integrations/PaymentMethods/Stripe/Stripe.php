<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe;

use FluentBookingPro\App\Services\Integrations\PaymentMethods\BasePaymentMethod;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\API\API;
use FluentBookingPro\App\Services\OrderHelper;
use FluentBookingPro\App\Models\Transactions;
use FluentBookingPro\App\Models\Order;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class Stripe extends BasePaymentMethod
{
    /**
     * title, slug, brandColor
     */
    public function __construct()
    {
        parent::__construct(
            __('Stripe', 'fluent-booking-pro'),
            'stripe',
            '#136196',
            $this->getLogo()
        );
    }

    public function register()
    {
        $this->init();;
        add_filter('fluent_booking/get_payment_connect_info_' . $this->slug, [$this, 'getConnectInfo']);
        add_filter('fluent_booking/get_payment_settings_disconnect_' . $this->slug, [$this, 'disconnect']);
        add_action('fluent-booking/before_render_payment_method_' . $this->slug, [$this, 'loadCheckoutJs'], 10, 1);

        add_action('wp_ajax_nopriv_fluent_cal_confirm_stripe_payment', [$this, 'confirmStripePayment']);
        add_action('wp_ajax_fluent_cal_confirm_stripe_payment', [$this, 'confirmStripePayment']);
        add_filter('fluent_booking/payment/payment_settings_before_update_stripe', [$this, 'beforeUpdateSettings'], 10, 1);
    }

    public function disconnect($data)
    {
        return ConnectConfig::disconnect($data);
    }

    public function isEnabled(): bool
    {
        return $this->getActiveStatus();
    }

    /**
     * Connect configuration should return
     */
    public function getConnectInfo()
    {
        wp_send_json_success(ConnectConfig::getConnectConfig(), 200);
    }

    /**
     * @return string path of the svg image which will be used as checkout logo
     */
    public function getLogo()
    {
        return FLUENT_BOOKING_URL . "assets/images/payment-methods/stripe.svg";
    }

    /**
     * @return string checkout methods short description
     * which will be shown to the checkout page and method settings page
     */
    public function getDescription()
    {
        return __("Stripe's payments platform lets you accept credit cards, debit cards, and popular payment methods around the world—all with a single integration", "fluent-booking-pro");
    }

    public function beforeUpdateSettings($data)
    {
        //encrypt secret keys by Helper::encrypt
        if (!empty($data['test_secret_key'])) {
            $data['test_secret_key'] = Helper::encryptKey($data['test_secret_key']);
        }

        if (!empty($data['live_secret_key'])) {
            $data['live_secret_key'] = Helper::encryptKey($data['live_secret_key']);
        }

        return $data;
    }

    public function getSettings()
    {
        return (new StripeSettings())->get();
    }

    public function makePayment($orderItem, $calendarSlot)
    {
        $hash = $orderItem->hash;
        $stripeSettings = new StripeSettings($this->slug);
        $apiKey = $stripeSettings->getApiKey();
        $publicKey = $stripeSettings->getPublicKey();
        $stripeSetting = $this->getSettings();
        $currency = CurrenciesHelper::getGlobalCurrency();

        $items = $calendarSlot->getPaymentItems($orderItem->slot_minutes);
        if (!$items) {
            return;
        }

        $quantity = $orderItem->getMeta('quantity', 1);

        array_walk($items, function (&$item) use ($quantity) {
            $item['quantity'] = $quantity;
        });

        $paymentTotal = $this->getPayableAmount($items, $currency) * $quantity;

        $paymentArgs = [
            'client_reference_id' => $hash,
            'items'               => $items,
            'amount'              => (int)round($paymentTotal),
            'currency'            => strtolower($currency),
            'description'         => __('Payment for Order', 'fluent-booking-pro'),
            'customer_email'      => $orderItem->email,
            'success_url'         => $this->getSuccessUrl($orderItem, $calendarSlot),
        ];

        //Subscription only available for hosted, will implement onsite later
        if ($stripeSetting['checkout_mode'] === apply_filters('fluent_booking_global_checkout_mode', 'onsite')) {
            $paymentArgs['public_key'] = $publicKey;
            $this->handleOnsitePayment($orderItem, $paymentArgs, $apiKey);
        } else {
            $this->handleHostedPayment($orderItem, $paymentArgs, $apiKey);
        }
    }

    public function confirmStripePayment()
    {
        if (!isset($_REQUEST['intentId'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $intentId = $_REQUEST['intentId']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $path = 'payment_intents/' . $intentId;

        $api = new API();
        $response = $api->makeRequest($path, [], (new StripeSettings())->getApiKey());

        if (!$response || is_wp_error($response)) {
            return;
        }

        $orderHash = Arr::get($response, 'metadata.ref_id');
        $amount = intval(Arr::get($response, 'amount_received'));

        //verify order
        $order = (new OrderHelper())->getOrderByHash($orderHash);
        if (intval($order->total_amount) !== $amount) {
            return;
        }

        $status = Arr::get($response, 'status') === 'succeeded' ? 'paid' : 'pending';

        $last_4 = Arr::get($response, 'charges.data.0.payment_method_details.card.last4', '');
        $brand = Arr::get($response, 'charges.data.0.payment_method_details.card.brand', '');

        $metaData = [
            'charge_id' => Arr::get($response, 'charges.data.0.id', '')
        ];

        $updateData = [
            'status'           => sanitize_text_field($status),
            'vendor_charge_id' => sanitize_text_field($intentId),
            'payment_mode'     => Arr::get($response, 'livemode') ? 'live' : 'test',
            'card_last_4'      => sanitize_text_field($last_4),
            'card_brand'       => sanitize_text_field($brand),
            'total_paid'       => $amount,
            'meta'             => json_encode($metaData)
        ];

        $this->updateOrderData($orderHash, $updateData);
    }

    public function verifyInvoiceAndUpdate($eventId)
    {
        $invoice = (new API())->getInvoice($eventId);
        $orderHash = self::getOrderHash($invoice);

        if (!$invoice || is_wp_error($invoice)) {
            error_log(__('invoice not found', 'fluent-booking-pro'));
            return;
        }

        $updateData = [
            'status'           => sanitize_text_field($invoice->data->object->status),
            'vendor_charge_id' => sanitize_text_field($invoice->data->object->payment_intent)
        ];

        //card_info update
        if ($cardInfo = $invoice->data->object->payment_method_details->card) {
            $updateData['card_brand'] = sanitize_text_field($cardInfo->brand);
            $updateData['card_last_4'] = sanitize_text_field($cardInfo->last4);
        }

        if ($invoice->data->object->status === 'succeeded') {
            $updateData['status'] = 'paid';
            $updateData['payment_method_type'] = $invoice->data->object->payment_method_details->type;
            $updateData['payment_mode'] = $invoice->data->object->livemode ? 'live' : 'test';
        }

        $this->updateOrderData($orderHash, $updateData);
    }

    /**
     * @param $orderItem
     * @param $paymentArgs
     * @param $apiKey
     * @return void
     * Handle Onsite Payment Api for stripe
     **/
    public function handleOnsitePayment($orderItem, $paymentArgs, $apiKey)
    {
        try {
            $sessionData = $this->intentData($orderItem, $paymentArgs);
            $invoiceResponse = (new API())->makeRequest('payment_intents', $sessionData, $apiKey, 'POST');

            $orderItem->payment_args = $paymentArgs;

            wp_send_json_success([
                'nextAction' => 'stripe',
                'actionName' => 'custom',
                'status'     => 'success',
                'message'    => __('Order has been placed successfully', 'fluent-booking-pro'),
                'data'       => $orderItem,
                'intent'     => $invoiceResponse,
            ], 200);
        } catch (\Exception $e) {
            wp_send_json_error([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function refundPayment($orderItem, $calendarSlot)
    {
        if ($orderItem->payment_status == 'refunded') {
            return;
        }

        $order = Order::where('parent_id', $orderItem->id)->first();
        if (!$order) {
            return;
        }

        $transaction = Transactions::where('object_id', $order->id)->where('uuid', $order->uuid)->first();
        if (!$transaction) {
            return;
        }

        $transactionMeta = json_decode($transaction->meta, true);

        $refundData = [
            'charge' => Arr::get($transactionMeta, 'charge_id'),
            'amount' => intval($transaction->total)
        ];

        $apiKey = (new StripeSettings())->getApiKey();

        $response = (new API())->makeRequest('refunds', $refundData, $apiKey, 'POST');
        
        if (!$response || is_wp_error($response)) {
            return;
        }

        $this->updateRefundData($refundData['amount'], $order, $transaction, $orderItem, 'stripe', $refundData['charge'], 'Refund From Stripe');
    }

    public function getPayableAmount($items, $currency)
    {
        $total = 0;
        foreach ($items as $item) {
            if (!isset($item['value'])) {
                continue;
            }
            $total += intval($item['value']);
        }

        if (CurrenciesHelper::isZeroDecimal($currency)) {
            return $total;
        }
        return $total * 100;
    }

    public function intentData($booking, $args)
    {
        $currency = CurrenciesHelper::getGlobalCurrency();

        $bookingUrl = Helper::getAdminBookingUrl($booking->id) . '&period=upcoming';

        $sessionPayload = array(
            'amount'               => intval($args['amount']),
            'currency'             => $currency,
            'receipt_email'        => $booking->email,
            'description'          => $booking->getBookingTitle(),
            'statement_descriptor' => StripeSettings::getPaymentDescriptor($booking->calendar_event),
            'metadata'             => [
                'ref_id'      => $args['client_reference_id'],
                'guest_name'  => trim($booking->first_name . ' ' . $booking->last_name),
                'guest_email' => $booking->email,
                'booking_id'  => $booking->id,
                'booking_url' => $bookingUrl,
            ],
        );

        return $sessionPayload;
    }

    public function sessionData($booking, $args)
    {
        $items = $args['items'];
        $currency = CurrenciesHelper::getGlobalCurrency();

        $conversionFactor = 100;
        if (CurrenciesHelper::isZeroDecimal($currency)) {
            $conversionFactor = 1;
        }

        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'amount'   => intval($item['value'] * $conversionFactor),
                'currency' => $currency,
                'name'     => $item['title'],
                'quantity' => isset($item['quantity']) ? (int)$item['quantity'] : 1,
            ];
        }

        $bookingUrl = Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $booking->id);
        $invoiceData = [
            'account_tax_ids'   => [],
            'custom_fields'     => [],
            'description'       => __('Invoice for Order', 'fluent-booking-pro') . ' #' . $args['client_reference_id'],
            'footer'            => '',
            'metadata'          => [
                'ref_id'      => $args['client_reference_id'],
                'name'        => $booking->first_name . ' ' . $booking->last_name,
                'booking_id'  => $booking->id,
                'booking_url' => $bookingUrl,
            ],
            'rendering_options' => [],
        ];

        $sessionPayload = array(
            'client_reference_id' => $args['client_reference_id'],
            'success_url'         => $args['success_url'],
            'line_items'          => $lineItems,
            'mode'                => 'payment',
            'invoice_creation'    => array(
                'enabled'      => 'true',
                'invoice_data' => $invoiceData,
            ),
            'metadata'            => [
                'ref_id' => $args['client_reference_id'],
            ]
        );

        if (isset($args['payment_method_type'])) {
            $sessionPayload['payment_method_types'] = $args['payment_method_type'];
        }

        return $sessionPayload;
    }


    /*
    * @param $orderItem
     * @param $paymentArgs
     * @param $apiKey
     * @return void
     * Handle Onsite hosted checkout for stripe
    */
    public function handleHostedPayment($orderItem, $paymentArgs, $apiKey)
    {
        try {
            $sessionData = $this->sessionData($orderItem, $paymentArgs);

            $sessionData = apply_filters('fluent-booking/payment/stripe_checkout_session_args', $sessionData);

            $argsDefault = [
                'locale' => 'auto'
            ];
            $sessionData = array_merge($sessionData, $argsDefault);

            $invoiceResponse = (new API())->makeRequest('checkout/sessions', $sessionData, $apiKey, 'POST');

            is_wp_error($invoiceResponse) ? wp_send_json_error($invoiceResponse->get_error_message(), 422) : '';

            wp_send_json_success(
                [
                    'status'      => 'success',
                    'message'     => __('Order has been placed successfully', 'fluent-booking-pro'),
                    'data'        => $orderItem,
                    'redirect_to' => $invoiceResponse['url']
                ],
                200
            );
        } catch (\Exception $e) {
            wp_send_json_error([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ], 422);
        }

    }

    public function renderDescription()
    {
        echo '<p>' . esc_html__('Pay with Stripe', 'fluent-booking-pro') . '</p>';
    }

    public function fields()
    {
        return [
            'label'        => __('Stripe Payments', 'fluent-booking-pro'),
            'description'  => __('Configure stripe to accept payments on your booking events and monetize your time slots', 'fluent-booking-pro'),
            'is_active'    => [
                'value' => 'no',
                'label' => __('Enable Stripe payment for booking payment', 'fluent-booking-pro'),
                'type'  => 'inline_checkbox'
            ],
            'payment_mode' => [
                'value'   => 'test',
                'label'   => __('Payment Mode', 'fluent-booking-pro'),
                'options' => [
                    'test' => __('Test Mode', 'fluent-booking-pro'),
                    'live' => __('Live Mode', 'fluent-booking-pro')
                ],
                'type'    => 'radio'
            ],
//            'checkout_mode' => [
//                'value' => 'onsite',
//                'label' => __('Checkout Mode', 'fluent-booking'),
//                'options' => [
//                    'onsite' => __('Onsite', 'fluent-booking'),
//                    'hosted' => __('Hosted', 'fluent-booking')
//                ),
//                'type' => 'radio'
//            ),
            'provider'     => [
                'value' => 'connect',
                'label' => __('Provider', 'fluent-booking-pro'),
                'type'  => 'provider'
            ],
            // 'currency'     => [
            //     'value'   => 'USD',
            //     'label'   => __('Currency', 'fluent-booking-pro'),
            //     'options' => $currencies,
            //     'type'    => 'select'
            // ]
        ];
    }

    public function validSettingKeys()
    {
        return [
            'is_active',
            'test_publishable_key',
            'test_secret_key',
            'live_publishable_key',
            'live_secret_key',
            'payment_mode',
            'provider',
            'test_account_id',
            'live_account_id',
            'checkout_mode',
            'currency'
        ];
    }

    public function webHookPaymentMethodName()
    {
        return $this->slug;
    }


    public function onPaymentEventTriggered()
    {
        $data = (new API())->verifyIPN();

        if (!$data) {
            error_log('invalid data');
            return;
        }

        $this->verifyInvoiceAndUpdate($data->id);
    }


    public static function getOrderHash($event)
    {
        $eventType = $event->type;

        $metaDataEvents = [
            'checkout.session.completed',
            'charge.refunded',
            'charge.succeeded',
            'invoice.paid'
        ];

        if (in_array($eventType, $metaDataEvents)) {
            $data = $event->data->object;
            $metaData = (array)$data->metadata;
            return Arr::get($metaData, 'ref_id');
        }

        return false;
    }

    public function ShowModal($invoiceResponse)
    {
        $responseData = [
            'nextAction'       => 'stripe',
            'actionName'       => 'custom',
            'buttonState'      => 'hide',
            'invoice_response' => $invoiceResponse,
            'message_to_show'  => __('Payment Modal is opening, Please complete the payment', 'fluent-booking-pro'),
        ];
        wp_send_json_success($responseData, 200);
    }

    public function loadCheckoutJs($my_data)
    {
        wp_enqueue_script('fluent-booking-checkout-sdk-' . $this->slug, 'https://js.stripe.com/v3/', [], FLUENT_BOOKING_ASSETS_VERSION, true);
        wp_enqueue_script('fluent-booking-checkout-handler-' . $this->slug, FLUENT_BOOKING_URL . 'assets/public/js/stripe-checkout.js', ['fluent-booking-checkout-sdk-' . $this->slug], FLUENT_BOOKING_ASSETS_VERSION, true);
    }

    public function render($method)
    {
        do_action('fluent-booking/before_render_payment_method_' . $this->slug, $method);
        return '
            <input checked value="' . esc_attr($this->slug) . '" name="' . esc_attr($this->slug) . '_payment_method' . '" type="radio"  id="' . esc_attr($this->slug) . '_payment_method">
            <label for="' . esc_attr($this->slug) . '_payment_method">
              ' . __('Stripe', 'fluent-booking-pro') . '
            </label>
        ';
    }
}
