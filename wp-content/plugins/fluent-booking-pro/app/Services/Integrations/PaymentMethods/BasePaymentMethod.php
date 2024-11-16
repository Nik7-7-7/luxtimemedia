<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods;

use FluentBookingPro\App\Hooks\Handlers\GlobalPaymentHandler;
use FluentBooking\App\Models\Booking;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\Validator;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBooking\App\Services\Helper;
use FluentBookingPro\App\Models\Order;
use FluentBookingPro\App\Models\Transactions;
use FluentBookingPro\App\Services\OrderHelper;


abstract class BasePaymentMethod implements BasePaymentInterface
{
    public $slug;

    public $title;

    public $logo;

    public $brandColor = '#ccc';

    protected $methodHandler;

    public static $methods = [];

    public static $routes = [];

    abstract public function getLogo();

    abstract public function getDescription();

    abstract public function getSettings();

    abstract public function fields();

    abstract public function validSettingKeys();

    /**
     * This method should return the name of the method that will be passed from webhook to listen payment events
     * TODO need testing, if it breaks something
     * eg: www.test-site.com/?fluent_booking_payment_listener='true'&method='nameReturnedFromTheMethod'
     * @return string
     */
    abstract public function webHookPaymentMethodName();

    abstract public function onPaymentEventTriggered();

    abstract public function makePayment($orderItem, $calendarSlot);

    abstract public function refundPayment($orderItem, $calendarSlot);

    public function resolveOrderHash($orderItem)
    {
        return $orderItem->hash;
    }

    public function __construct($title, $slug, $brandColor, $logo)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->brandColor = $brandColor;
        $this->logo = $logo;
        $this->methodHandler = 'fluent_booking_payment_settings_' . $slug;
    }

    public function init()
    {
        add_filter('fluent_booking/payment/get_global_payment_settings_' . $this->slug, [$this, 'globalFields']);
        add_filter('fluent_booking/payment/get_global_payment_methods', [$this, 'register']);
        add_action('fluent_booking/payment/payment_settings_update_' . $this->slug, [$this, 'update'], 10, 1);
        add_filter('fluent_booking/payment/payment_method_settings_routes', [$this, 'setRoutes']);
        add_action('fluent_booking/payment/pay_order_with_' . $this->slug, [$this, 'makePayment'], 10, 2);
        add_action('fluent_booking/refund_payment_' . $this->slug, [$this, 'refundPayment'], 10, 2);
        add_action('fluent_booking/payment/ipn_endpoint_' . $this->webHookPaymentMethodName(), [$this, 'onPaymentEventTriggered']);
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 12, 1);

        add_filter('fluent_booking/payment/get_all_methods', [$this, 'getAllMethods'], 10, 1);

        add_filter('fluent_booking/payment_methods_renderer', [$this, 'getMethodsTemplate'], 10, 1);

        add_filter('fluent_booking/public_event_vars', [$this, 'addPaymentRendererTemplates'], 10, 2);

        add_action('fluent_booking/pre_after_booking_pending', [$this, 'afterBookingPending'], 10, 3);

        add_filter('fluent_booking/booking_data', [$this, 'addPaymentMethodToBookingData'], 10, 3);
    }

    public function addPaymentMethodToBookingData($bookingData, $calendarSlot, $customData)
    {
        if (Arr::get($bookingData, 'source') != 'web') {
            return $bookingData;
        }
        
        $duration = Arr::get($bookingData, 'slot_minutes');
        if ($calendarSlot->isPaymentEnabled($duration)) {
            $bookingData['status'] = 'pending';
            $bookingData['payment_status'] = 'pending';
            $bookingData['payment_method'] = Arr::get($customData, 'payment_method', '');
        }

        return $bookingData;
    }

    public function afterBookingPending($booking, $calendarSlot, $bookingData)
    {
        $paymentMethod = Arr::get($bookingData, 'payment_method');

        if ($calendarSlot->isPaymentEnabled($booking->slot_minutes) && $booking->source === 'web' && $paymentMethod) {
            (new OrderHelper())->processDraftOrder($booking, $calendarSlot, $bookingData); // make draft order
            do_action('fluent_booking/payment/pay_order_with_' . sanitize_text_field($paymentMethod), $booking, $calendarSlot);
        }
    }

    public function getAllMethods()
    {
        static::$methods[$this->slug] = [
            'title'  => $this->title,
            'image'  => $this->logo,
            "status" => $this->isEnabled()
        ];
        return static::$methods;
    }

    public function addPaymentRendererTemplates($vars, $slot)
    {
        $paymentSettings = $slot->getPaymentSettings();

        $driver = Arr::get($paymentSettings, 'driver');
        $isMultiEnabled = Arr::get($paymentSettings, 'multi_payment_enabled') === 'yes';

        if (Arr::get($paymentSettings, 'enabled') === 'yes') {
            if ($driver === 'native') {
                $vars['payment_methods'] = $this->getMethodsTemplate(['templates' => '']);
                $vars['payment_items'] = Arr::get($paymentSettings, 'items');
                $vars['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();
                if ($slot->isMultiDurationEnabled() && $isMultiEnabled) {
                    $vars['multi_payment_items'] = Arr::get($paymentSettings, 'multi_payment_items');
                }
            }
            
            if ($driver == 'woo' && defined('WC_PLUGIN_FILE')) {
                if ($slot->isMultiDurationEnabled() && $isMultiEnabled) {
                    $productIds = Arr::get($paymentSettings, 'multi_payment_woo_ids', []);
                    $vars['multi_payment_woo_ids'] = $slot->getWooProductPriceByDuration($productIds);
                }
            }
        }
        
        return $vars;
    }

    public function handleRedirectData()
    {
        return '';
    }

    public function addGlobalMenu($menuItems)
    {
        $menuItems[$this->slug]['disable'] = false;

        return $menuItems;
    }

    public function setRoutes()
    {
        static::$routes[] = [
            'path' => $this->slug,
            'name' => $this->slug,
            'meta' => [
                'title' => $this->title
            ]
        ];
        return static::$routes;
    }

    public function register()
    {
        static::$methods[] = [
            "title"       => $this->title,
            "route"       => $this->slug,
            "description" => $this->getDescription(),
            "logo"        => $this->getLogo(),
            "status"      => $this->isEnabled(),
            "brand_color" => $this->brandColor
        ];

        return static::$methods;
    }

    public function getMode()
    {
        $settings = $this->getSettings();

        return Arr::get($settings, 'payment_mode', 'test');
    }

    public function getActiveStatus()
    {
        $settings = $this->getSettings();

        return Arr::get($settings, 'is_active', 'no') === 'yes';
    }

    public function hasLiveRefund()
    {
        return false;
    }

    public function getTitle($scope = 'admin')
    {
        return $this->title;
    }

    public function renderDescription()
    {
        echo '';
    }

    public function supportedCurrencies()
    {
        return ['*'];
    }

    public function processCartOrder($order)
    {
        return $order;
    }

    public function getVenodPaymentLink($payment)
    {
        return false;
    }

    public function capturePayment($payment)
    {
        $payment->updateStatus('paid');
        return $payment;
    }

    public function update($data)
    {
        wp_send_json_success(
            $this->updateSettings($data)
        );
    }

    public function updateSettings($data)
    {
        $settings = $this->getSettings();

        $settings = wp_parse_args($data, $settings);

        $settings = Arr::only($settings, $this->validSettingKeys());

        $settings = apply_filters('fluent_booking/payment/payment_settings_before_update_' . $this->slug, $settings);

        update_option($this->methodHandler, $settings, 'no');

        return $this->getSettings();
    }

    public function globalFields()
    {
        return [
            'fields'   => $this->fields(),
            'settings' => $this->getSettings()
        ];
    }

    public function sanitize($data, $fields)
    {
        foreach ($fields as $key => $value) {
            if (isset($data[$key])) {
                if ('email' === $value['type']) {
                    $data[$key] = sanitize_email($data[$key]);
                } else {
                    $data[$key] = sanitize_text_field($data[$key]);
                }
            }
        }

        return $data;
    }

    protected function getSuccessUrl($orderItem, $calendarEvent, $args = null)
    {
        return (new PaymentHelper($this->slug))->successUrl($orderItem, $calendarEvent, $args);
    }

    protected function getListenerUrl($args = null)
    {
        return (new PaymentHelper($this->slug))->listenerUrl($args);
    }

    protected function getTransaction($chargeId)
    {
        return Transactions::where('vendor_charge_id', $chargeId)->first();
    }

    public function updateOrderData($orderHash, $orderData = [])
    {
        $this->updateOrder($orderHash, $orderData);
        $this->updateTransaction($orderHash, $orderData);
        $this->updateBooking($orderHash, $orderData);
    }

    public function updateOrder($orderHash, $data)
    {
        $order = Order::where('uuid', $orderHash)->first();

        if (!$order) {
            return;
        }

        if ($data['status'] == 'paid') {
            $data['completed_at'] = gmdate('Y-m-d H:i:s'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        $order->update($data);
    }

    public function updateTransaction($orderHash, $data)
    {
        $transaction = Transactions::where('uuid', $orderHash)->first();

        if (!$transaction) {
            return;
        }
        
        $transaction->update($data);
    }

    public function updateBooking($orderHash, $data)
    {
        $booking = Booking::with(['calendar_event', 'calendar'])
            ->where('hash', $orderHash)
            ->first();

        if (!$booking) {
            return;
        }

        $calendarEvent = $booking->calendar_event;

        $isRequireConfirmation = $calendarEvent->isConfirmationRequired($booking->start_time, $booking->created_at);

        if ($data['status'] == 'paid' && !$isRequireConfirmation) {
            $booking->status = 'scheduled';
        }

        $booking->payment_status = $data['status'];
        $booking->save();

        do_action('fluent_booking/payment/update_payment_status_' . $data['status'], $booking);

        $this->maybeUpdateChildBookings($booking, $data, $calendarEvent);

        if ($booking->payment_status == 'paid') {
            do_action('fluent_booking/log_booking_activity', $this->getSuccessLog($booking->id, $data));

            do_action('fluent_booking/pre_after_booking_' . $booking->status, $booking, $calendarEvent, $data);

            // We are just renewing this as this may have been changed by the pre hook
            $booking = Booking::with(['calendar_event', 'calendar'])->find($booking->id);

            do_action('fluent_booking/after_booking_' . $booking->status, $booking, $calendarEvent, $data);
        }

        if ($booking->payment_status == 'pending') {
            do_action('fluent_booking/log_booking_activity', $this->getPendingLog($booking->id, $data));
        }

        if ($booking->payment_status == 'failed') {
            do_action('fluent_booking/log_booking_activity', $this->getFailedLog($booking->id, $data));
        }
    }

    private function maybeUpdateChildBookings($booking, $data, $calendarEvent)
    {
        $childBookingIds = Booking::where('parent_id', $booking->id)
            ->pluck('id')
            ->toArray();

        if (!$childBookingIds) {
            return;
        }

        $logMethods = [
            'paid'    => 'getSuccessLog',
            'pending' => 'getPendingLog',
            'failed'  => 'getFailedLog'
        ];

        foreach ($childBookingIds as $bookingId) {
            $childBooking = Booking::find($bookingId);

            if (!$childBooking) {
                continue;
            }
        
            $childBooking->update([
                'status'         => $booking->status,
                'payment_status' => $booking->payment_status,
            ]);

            if ($booking->status == 'scheduled') {
                do_action('fluent_booking/after_booking_scheduled', $childBooking, $calendarEvent, $childBooking);
            }

            if (!isset($logMethods[$booking->payment_status])) {
                continue;
            }

            $method = $logMethods[$booking->payment_status];
            do_action('fluent_booking/log_booking_activity', $this->$method($bookingId, $data));
        }
    }

    public function getSuccessLog($bookingId, $data)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Payment Successfully Completed', 'fluent-booking-pro'),
            'description' => sprintf(__('Transaction marked as paid and %s Transaction ID: %s ', 'fluent-booking-pro'), $this->title, $data['vendor_charge_id'])
        ];
    }

    public function getPendingLog($bookingId, $data)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'info',
            'type'        => 'error',
            'title'       => sprintf(__('%s Payment Pending', 'fluent-booking-pro'), $this->title),
            'description' => $data['pending_reason']
        ];
    }

    public function getFailedLog($bookingId, $data)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'closed',
            'type'        => 'error',
            'title'       => sprintf(__('%s Payment Failed', 'fluent-booking-pro'), $this->title),
            'description' => $data['failed_reason']
        ];
    }

    public function updateRefundData($refundAmount, $order, $transaction, $booking, $method = '', $refundId = '', $refundNote = 'Refunded')
    {
        $status = 'refunded';

        $alreadyRefunded = $this->getRefundTotal($order->id);

        $totalRefund = intval($refundAmount + $alreadyRefunded);

        if ($totalRefund < $transaction->total) {
            $status = 'partially-refunded';
        }

        $order->total_paid = $order->total_amount - $totalRefund;
        $order->refunded_at = gmdate('Y-m-d H:i:s'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $order->status = $status;
        $order->note = $refundNote;
        $order->save();

        $transaction->status = $status;
        $transaction->save();

        $booking->payment_status = $status;
        $booking->save();

        do_action('fluent_booking/payment/update_payment_status_' . $status, $booking);

        $uniqueHash = md5('refund_' . $booking->id . '-' . time() . '-' . mt_rand(100, 999));

        $refundData = [
            'uuid' => $uniqueHash,
            'object_id' => $order->id,
            'object_type' => 'order',
            'vendor_charge_id' => $refundId,
            'transaction_type' => 'refund',
            'payment_method' => $order->payment_method,
            'total' => $refundAmount,
            'status' => $status,
            'rate' => 1
        ];

        Transactions::create($refundData);

        $logData = [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Payment Refunded Successfully', 'fluent-booking-pro'),
            'description' => sprintf(__('Amount %s refunded successfully. Transaction ID: %s ', 'fluent-booking-pro'),
                CurrenciesHelper::getGlobalCurrencySign() . number_format($refundAmount / 100, 2), $refundId)
        ];

        do_action('fluent_booking/log_booking_activity', $logData);
    }

    public function getRefundTotal($orderId)
    {
        $totalRefund = Transactions::where('object_id', $orderId)
            ->where('object_type', 'order')
            ->where('transaction_type', 'refund')
            ->sum('total');
        
        return $totalRefund;
    }

    public function maybeUpdatePayment()
    {
        return false;
    }

    public function render($method)
    {
        return '';
    }

    public function getMethodsTemplate($data)
    {
        $methods = GlobalPaymentHandler::getAllMethods();

        if (!Helper::isPaymentEnabled()) {
            return $data['template'] = '<div class="fluent_booking_payment_methods">' . __('Please activate payment first!', 'fluent-booking-pro') . '</div>';
        }

        $templates = [
            'template' => '',
        ];

        $hasActiveMethod = false;
        $radio = "<div class='payment-methods-radio fluent_booking_payment_methods'><div style='display: flex; gap: 20px;'>" . __('Pay with:', 'fluent-booking-pro');
        foreach ($methods as $slug => $methodData) {
            if (Arr::isTrue($methodData, 'status')) {
                $hasActiveMethod = true;
                $radio .= $this->render($slug);
            }
        }
        $radio .= "</div>";

        if (!$hasActiveMethod) {
            return $data['template'] = '<p style="color:#fb7373; font-size:16px; margin: 0 auto;">'. __('Please active at least one payment method!', 'fluent-booking-pro') .'</p>';
        }
        
        $templates['template'] = $radio;

        return $data['template'] = $templates;
    }

    protected function validate($data, array $rules = [])
    {
        $validator = (new Validator())->make($data, $rules);
        if ($validator->validate()->fails()) {
            wp_send_json_error($validator->errors(), 422);
        }
    }
}

