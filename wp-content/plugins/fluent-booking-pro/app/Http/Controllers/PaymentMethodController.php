<?php

namespace FluentBookingPro\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBooking\Framework\Http\Request\Request;
use FluentBooking\Framework\Support\Arr;
use FluentBookingPro\App\Hooks\Handlers\GlobalPaymentHandler;


class PaymentMethodController extends Controller
{
    public function index(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            $gateways = $globalHandler->getAll();
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 422);
        }

        return [
            'gateways' => $gateways
        ];
    }

    public function store(Request $request)
    {
        $data = $request->get('settings', []);
        $method = $request->get('method', '');
        $isActive = Arr::get($data, 'is_active') === 'yes';

        if ($isActive) {
            if ($method == 'stripe') {
                $paymentMode = Arr::get($data, 'payment_mode', 'test');
                if (empty($data[$paymentMode . '_publishable_key']) || empty($data[$paymentMode . '_secret_key'])) {
                    return $this->sendError([
                        'message' => __('Please connect your Stripe account first.', 'fluent-booking-pro')
                    ]);
                }
            }
            if ($method == 'paypal') {
                $data['paypal_email'] = sanitize_email(Arr::get($data, 'paypal_email', ''));
                if (!$data['paypal_email'] || !is_email($data['paypal_email'])) {
                    return $this->sendError([
                        'message' => __('Please enter a valid email address', 'fluent-booking-pro')
                    ]);
                }
            }
        }

        do_action('fluent_booking/payment/payment_settings_update_' . $method, $data);
    }

    public function getSettings(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            return $globalHandler->getSettings($request->method);
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 422);
        }
    }

    public function currencies(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            return $globalHandler->currencies();
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 422);
        }
    }

    public function connectInfo(Request $request, GlobalPaymentHandler $globalHandler)
    {
        return $globalHandler->connectInfo(sanitize_text_field($request->method));
    }

    public function disconnect(Request $request, GlobalPaymentHandler $globalHandler)
    {
        return $globalHandler->disconnect(
            sanitize_text_field($request->method),
            sanitize_text_field($request->mode),
        );
    }

    public function getCalendarEventSettings($id, $event_id)
    {
        $calendarEvent = CalendarSlot::findOrFail($event_id);

        $data = [
            'settings' => $calendarEvent->getPaymentSettings(),
            'config'   => [
                'native_enabled'     => Helper::isPaymentEnabled(),
                'stripe_configured'  => Helper::isPaymentConfigured('stripe'),
                'paypal_configured'  => Helper::isPaymentConfigured('paypal'),
                'native_config_link' => Helper::getAppBaseUrl('settings/configure-integrations/payment/stripe'),
                'woo_config_link'    => Helper::getAppBaseUrl('settings/configure-integrations/global-modules'),
                'has_woo'            => defined('WC_PLUGIN_FILE'),
                'woo_enabled'        => defined('WC_PLUGIN_FILE') && Helper::isModuleEnabled('woo')
            ]
        ];

        return $data;
    }

    public function updateSettings($id, $event_id)
    {
        $data = $this->request->settings;

        $event = CalendarSlot::findOrFail($event_id);

        if (!$event) {
            return $this->sendError([
                'message' => __('Calendar not found', 'fluent-booking-pro')
            ], 422);
        }

        $isEnabled = Arr::get($data, 'enabled', 'no') === 'yes';

        $driver = Arr::get($data, 'driver');
        $eventType = $isEnabled ? 'paid' : 'free';
        $data['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();

        if ($isEnabled) {
            if (!Helper::isPaymentConfigured('stripe')) {
                $data['stripe_enabled'] = 'no';
            }
    
            if (!Helper::isPaymentConfigured('paypal')) {
                $data['paypal_enabled'] = 'no';
            }
            
            $stripeEnabled = Arr::get($data, 'stripe_enabled', 'no') === 'yes';
            $paypalEnabled = Arr::get($data, 'paypal_enabled', 'no') === 'yes';

            if (!$driver) {
                return $this->sendError([
                    'message' => __('Please select a payment method', 'fluent-booking-pro')
                ], 422);
            }

            if ($driver == 'native') {
                if (!$stripeEnabled && !$paypalEnabled) {
                    return $this->sendError([
                        'message' => __('Please enable at least one payment method', 'fluent-booking-pro')
                    ], 422);
                }
            }

            if ($driver == 'woo' && defined('WC_PLUGIN_FILE')) {
                $isMultiEnabled = Arr::get($data, 'multi_payment_enabled', 'no') === 'yes';
                if (!$isMultiEnabled) {
                    $productId = Arr::get($data, 'woo_product_id');
                    if (!$productId) {
                        return $this->sendError([
                            'message' => __('Please select a product', 'fluent-booking-pro')
                        ], 422);
                    }
    
                    $product = wc_get_product($productId);
                    if (!$product || !$product->get_id()) {
                        return $this->sendError([
                            'message' => __('Product not found. Please select a product', 'fluent-booking-pro')
                        ], 422);
                    }
                }

                $eventType = 'woo';
            }
        }

        $event->type = $eventType;
        $event->save();

        $event->updateMeta('payment_settings', $data);

        return $this->sendSuccess([
                'message' => __('Settings updated successfully', 'fluent-booking-pro')
            ]
        );
    }

    public function getWooProducts(Request $request)
    {
        if (!defined('WC_PLUGIN_FILE')) {
            return $this->sendError([
                'message' => __('WooCommerce is not installed', 'fluent-booking-pro')
            ], 422);
        }

        $products = wc_get_products([
            's'       => $request->getSafe('search', 'sanitize_text_field'),
            'limit'   => 100,
            'orderby' => 'title',
            'status'  => ['publish'],
            'order'   => 'ASC',
            'type'    => ['simple']
        ]);

        $data = [];

        $includeId = $request->getSafe('include_id', 'intval');

        $hadIncludeId = false;

        foreach ($products as $product) {
            $productId = (string)$product->get_id();
            if ($productId == $includeId) {
                $hadIncludeId = true;
            }
            $data[] = [
                'id'    => $productId,
                'title' => $product->get_title(),
                'price' => $product->get_price()
            ];
        }

        if (!$hadIncludeId) {
            $product = wc_get_product($includeId);
            if ($product) {
                $data[] = [
                    'id'    => $includeId,
                    'title' => $product->get_title(),
                    'price' => $product->get_price()
                ];
            }
        }

        return [
            'items'    => $data,
            'currency' => get_woocommerce_currency_symbol()
        ];
    }
}
