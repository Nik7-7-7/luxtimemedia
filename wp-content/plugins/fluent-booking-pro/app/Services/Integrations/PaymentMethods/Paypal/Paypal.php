<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Paypal;

use FluentBookingPro\App\Services\Integrations\PaymentMethods\BasePaymentMethod;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\PaymentHelper;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\Paypal\API\IPN;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBookingPro\App\Services\OrderHelper;
use FluentBookingPro\App\Models\Order;

class Paypal extends BasePaymentMethod
{
    public $paypalSettings;
    
    public function __construct()
    {
        parent::__construct(
            __('Paypal', 'fluent-booking-pro'),
            'paypal',
            '#136196',
            $this->getLogo()
        );

        $this->paypalSettings = $this->paypalSettings();
    }

    public function register()
    {
        $this->init();

        add_action('fluent_booking/ipn_paypal_action_web_accept', [$this, 'confirmPaypalPayment'], 10, 3);
    }

    public function isEnabled(): bool
    {
        return $this->getActiveStatus();
    }

    /**
     * @return string path of the svg image which will be used as checkout logo
     */
    public function getLogo()
    {
        return FLUENT_BOOKING_URL . "assets/images/payment-methods/paypal.svg";
    }

    /**
     * @return string checkout methods short description
     * which will be shown to the checkout page and method settings page
     */
    public function getDescription()
    {
        return __("Paypal's payments platform lets you accept credit cards, debit cards, and popular payment methods around the world—all with a single integration", "fluent-booking-pro");
    }

    public function paypalSettings()
    {
        return new PaypalSettings();
    }

    public function getSettings()
    {
        return $this->paypalSettings->get();
    }

    public function makePayment($orderItem, $calendarEvent)
    {
        $items = $calendarEvent->getPaymentItems($orderItem->slot_minutes);
        if (!$items) {
            return;
        }

        $quantity = $orderItem->getMeta('quantity', 1);

        array_walk($items, function (&$item) use ($quantity) {
            $item['quantity'] = $quantity;
        });

        $paypalEmail = $this->paypalSettings->getPaypalEmail();

        $currency    = CurrenciesHelper::getGlobalCurrency();
        $cancelUrl   = $orderItem->getCancelUrl();
        $successUrl  = $this->getSuccessUrl($orderItem, $calendarEvent);
        $listenerUrl = $this->getListenerUrl(['booking_id' => $orderItem->id]);
        $listenerUrl = PaymentHelper::limitLength($listenerUrl, 255);

        $customArgs = [
            'fs_id'            => $orderItem->id,
            'transaction_hash' => $orderItem->hash
        ];

        $paymentArgs = [
            'cmd'           => '_cart',
            'upload'        => '1',
            'rm'            => is_ssl() ? 2 : 1,
            'business'      => $paypalEmail,
            'email'         => $orderItem->email,
            'no_shipping'   => '1',
            'shipping'      => '0',
            'no_note'       => '1',
            'currency_code' => $currency,
            'charset'       => 'UTF-8',
            'custom'        => wp_json_encode($customArgs),
            'return'        => esc_url_raw($successUrl),
            'notify_url'    => esc_url_raw($listenerUrl),
            'cancel_return' => esc_url_raw($cancelUrl)
        ];

        $paymentArgs = wp_parse_args($paymentArgs, $this->getCartSummery($items));

        $orderItem->payment_args = $paymentArgs;

        (new OrderHelper())->updateOrderStatus($orderItem->hash, 'pending');

        $redirectUrl = $this->getRedirectUrl($paymentArgs);

        wp_send_json_success([
            'nextAction'  => 'paypal',
            'actionName'  => 'custom',
            'redirect_to' => $redirectUrl,
            'data'        => $orderItem,
            'status'      => 'success',
            'message'     => __('Order has been placed successfully', 'fluent-booking-pro'),
        ], 200);
    }

    public function refundPayment($orderItem, $calendarSlot)
    {
        return;
    }

    private function getCartSummery($items)
    {
        $paypalArgs = [];

        $counter = 1;
        foreach ($items as $item) {
            if (!Arr::get($item, 'value')) {
                continue;
            }

            $paypalArgs['quantity_'  . $counter] = $item['quantity'];
            $paypalArgs['item_name_' . $counter] = PaymentHelper::formatPaymentItem($item['title']);
            $paypalArgs['amount_'    . $counter] = round($item['value'], 2);
            $counter++;
        }

        return $paypalArgs;
    }

    private function getRedirectUrl($args = [])
    {
        $sandbox = '';
        if ($this->paypalSettings->isTest()) {
            $sandbox = '.sandbox';
            $args['test_ipn'] = 1;
        }

        $paypalRedirect = 'https://www' . $sandbox .'.paypal.com/cgi-bin/webscr/?';

        return $paypalRedirect . http_build_query($args, '', '&');
    }

    public function confirmPaypalPayment($data, $bookingId, $booking)
    {
        $paymentStatus = strtolower(sanitize_text_field($data['payment_status']));

        $order = Order::where('parent_id', $bookingId)->first();
        if (!$order) {
            return;
        }

        if ($paymentStatus == 'refunded' || $paymentStatus == 'reversed') {
            $this->processRefund($data, $order, $booking);
            return;
        }

        if ($booking->getMeta('is_paypal_action_fired') == 'yes') {
            return;
        }

        if ('completed' == $paymentStatus || 'pending' == $paymentStatus) {
            $status = 'paid';

            if ($paymentStatus == 'pending') {
                $status = 'processing';
            }

            $metaData = [
                'payer_email'      => sanitize_text_field($data['payer_email']),
                'payer_name'       => $this->getPayerName($data),
                'shipping_address' => $this->getPayerAddress($data),
            ];

            $paymentData = [
                'vendor_charge_id' => sanitize_text_field($data['txn_id']),
                'total_paid'       => $data['mc_gross'] * 100,
                'status'           => $status,
                'meta'             => json_encode($metaData),
            ];

            if (strtolower($order->currency) != strtolower($data['mc_currency'])) {
                $paymentData['status'] = 'failed';
                $paymentData['failed_reason'] = __('Payment failed due to invalid currency in Paypal IPN', 'fluent-booking-pro');
            }
    
            if (number_format((float)($order->total_amount / 100), 2) - number_format((float)$data['mc_gross'], 2) > 1) {
                $paymentData['status'] = 'failed';
                $paymentData['failed_reason'] = __('Payment failed due to invalid amount in Paypal IPN', 'fluent-booking-pro');
            }

            if (Arr::get($data, 'pending_reason')) {
                $paymentData['status'] = 'pending';
                $paymentData['pending_reason'] = $this->getPendingReason($data['pending_reason']);
            }

            if ($paymentData['status'] != 'failed') {
                $booking->updateMeta('is_paypal_action_fired', 'yes');
            }

            $this->updateOrderData($order->uuid, $paymentData);
        }
    }

    private function processRefund($data, $order, $booking)
    {
        if ($booking->payment_status == 'refunded') {
            return;
        }

        $refundExist = $this->getTransaction($data['txn_id']);
        if ($refundExist) {
            return;
        }

        $transaction = $this->getTransaction($data['parent_txn_id']);
        if (!$transaction) {
            return;
        }

        $refundAmount = $data['mc_gross'] * -100;

        $this->updateRefundData($refundAmount, $order, $transaction, $booking, 'paypal', $data['txn_id'], 'Refund From Paypal');
    }

    public function renderDescription()
    {
        echo '<p>' . esc_html__('Pay with Paypal', 'fluent-booking-pro') . '</p>';
    }

    public function fields()
    {
        return [
            'label'        => __('Paypal Payments', 'fluent-booking-pro'),
            'description'  => __('Configure Paypal to accept payments on your booking events and monetize your time slots', 'fluent-booking-pro'),
            'is_active'    => [
                'value' => 'no',
                'label' => __('Enable Paypal payment for booking payment', 'fluent-booking-pro'),
                'type'  => 'inline_checkbox'
            ],
            'payment_mode' => [
                'value'   => 'test',
                'label'   => __('Payment Mode', 'fluent-booking-pro'),
                'options' => [
                    'test' => __('Sandbox Mode', 'fluent-booking-pro'),
                    'live' => __('Live Mode', 'fluent-booking-pro')
                ],
                'type'    => 'radio'
            ],
            'paypal_email' => [
                'value' => '',
                'label' => __('Paypal Email', 'fluent-booking-pro'),
                'type'  => 'email'
            ],
            'disable_ipn_verification' => [
                'value'       => 'no',
                'label'       => __('Disable Paypal IPN Verification', 'fluent-booking-pro'),
                'type'        => 'inline_switch',
                'inline_help' => __('If you are unable to use Payment Data Transfer and payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'fluent-booking-pro')
            ]
        ];
    }

    public function validSettingKeys()
    {
        return [
            'is_active',
            'paypal_email',
            'payment_mode',
            'disable_ipn_verification'
        ];
    }

    private function getPayerName($data)
    {
        $firstName = sanitize_text_field(Arr::get($data, 'first_name'));
        
        $lastName = sanitize_text_field(Arr::get($data, 'last_name'));

        return $firstName . ' ' . $lastName;
    }

    private function getPayerAddress($data)
    {
        $address = [];

        $fields = [
            'address_street' => 'address_line1',
            'address_city' => 'address_city',
            'address_state' => 'address_state',
            'address_zip' => 'address_zip',
            'address_country_code' => 'address_country'
        ];
    
        foreach ($fields as $dataKey => $addressKey) {
            if (Arr::get($data, $dataKey)) {
                $address[$addressKey] = sanitize_text_field($data[$dataKey]);
            }
        }
    
        return implode(', ', $address);
    }

    private function getPendingReason($reason)
    {
        $messages = [
            'echeck' => __('Payment made via eCheck and will clear automatically in 5-8 days', 'fluent-booking-pro'),
            'address' => __('Payment requires a confirmed customer address and must be accepted manually through PayPal', 'fluent-booking-pro'),
            'intl' => __('Payment must be accepted manually through PayPal due to international account regulations', 'fluent-booking-pro'),
            'multi-currency' => __('Payment received in non-shop currency and must be accepted manually through PayPal', 'fluent-booking-pro'),
            'paymentreview' => __('Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'fluent-booking-pro'),
            'regulatory_review' => __('Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'fluent-booking-pro'),
            'unilateral' => __('Payment was sent to non-confirmed or non-registered email address.', 'fluent-booking-pro'),
            'upgrade' => __('PayPal account must be upgraded before this payment can be accepted', 'fluent-booking-pro'),
            'verify' => __('PayPal account is not verified. Verify account in order to accept this payment', 'fluent-booking-pro'),
            'other' => __('Payment is pending for unknown reasons. Contact PayPal support for assistance', 'fluent-booking-pro')
        ];
    
        $reason = strtolower($reason);
    
        return $messages[$reason] ?? __('Payment marked as pending', 'fluent-booking-pro');
    }

    public function webHookPaymentMethodName()
    {
        return $this->slug;
    }

    public function onPaymentEventTriggered()
    {
        (new IPN())->verifyIPN();
    }

    public function render($method)
    {
        do_action('fluent-booking/before_render_payment_method_' . $this->slug, $method);
        return '
            <input checked value="' . esc_attr($this->slug) . '" name="' . esc_attr($this->slug) . '_payment_method' . '" type="radio"  id="' . esc_attr($this->slug) . '_payment_method">
            <label for="' . esc_attr($this->slug) . '_payment_method">
              ' . __('Paypal', 'fluent-booking-pro') . '
            </label>
        ';
    }
}
