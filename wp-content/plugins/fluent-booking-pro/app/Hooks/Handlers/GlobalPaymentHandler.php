<?php

namespace FluentBookingPro\App\Hooks\Handlers;

use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\CurrenciesHelper;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\Stripe;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\ConnectConfig;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\Paypal\Paypal;
use FluentBooking\Framework\Support\Arr;

class GlobalPaymentHandler
{
    public function register()
    {
        add_action('init', [$this, 'init'], 1);
        add_filter('fluent_booking/public_event_vars', [$this, 'maybePushPaymentVars'], 10, 2);
    }

    public function init()
    {
        (new Stripe())->register();
        (new Paypal())->register();

        //This hook will allow others to register their payment method with ours
        do_action('fluent_booking/register_payment_methods');

        $this->verifyStripeConnect();
        $this->initIpnListener();
    }

    public function maybePushPaymentVars($eventVars, CalendarSlot $calendarEvent)
    {
        $eventVars['slot']['total_payment'] = '';
        
        if (!$calendarEvent->isPaymentEnabled() && !$calendarEvent->isWooEnabled()) {
            return $eventVars;
        }
        
        $paymentSettings = $calendarEvent->getPaymentSettings();

        $driver = Arr::get($paymentSettings, 'driver');

        if ($driver == 'native') {
            $eventVars['slot']['currency'] = CurrenciesHelper::getGlobalCurrencySign();
            $eventVars['slot']['total_payment'] = $calendarEvent->getPricingTotal();
        }

        if ($driver == 'woo' && defined('WC_PLUGIN_FILE')) {
            $eventVars['slot']['currency'] = get_woocommerce_currency_symbol();
            $eventVars['slot']['total_payment'] = $calendarEvent->getWooProductPrice();
        }

        return $eventVars;
    }

    public function initIpnListener()
    {
        if (isset($_REQUEST['fluent_booking_payment_listener'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_action('wp', function () {
                $paymentMethod = sanitize_text_field($_REQUEST['payment_method']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                do_action('fluent_booking/payment/ipn_endpoint_' . $paymentMethod);
            });
        }
    }

    public function verifyStripeConnect()
    {
        if (isset($_GET['source']) && $_GET['source'] == 'fluent_booking') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (!current_user_can('manage_options')) {
                return;
            }

            $ret = false;
            if (isset($_GET['ff_stripe_connect'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $data = Arr::only($_GET, ['ff_stripe_connect', 'mode', 'state', 'code', 'source']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $ret = ConnectConfig::verifyAuthorizeSuccess($data);
            }

            if ($ret) {
                echo wp_kses_post($ret);
                exit();
            }
            wp_redirect(admin_url('admin.php?page=fluent-booking#/settings/configure-integrations/payment/stripe'));
        }

    }

    public function connectInfo($method)
    {
        return apply_filters('fluent_booking/get_payment_connect_info_' . sanitize_text_field($method), []);
    }

    public function disconnect($method, $mode)
    {
        return apply_filters('fluent_booking/get_payment_settings_disconnect_' . $method, ['mode' => sanitize_text_field($mode)]);
    }

    public function getSettings($method)
    {
        return apply_filters('fluent_booking/payment/get_global_payment_settings_' . sanitize_text_field($method), []);
    }

    public function getAll()
    {
        return apply_filters('fluent_booking/payment/get_global_payment_methods', []);
    }

    public function currencies()
    {
        return [
            'data' => CurrenciesHelper::getFormattedCurrencies()
        ];
    }

    public static function getAllMethods()
    {
        return apply_filters('fluent_booking/payment/get_all_methods', []);
    }
}
