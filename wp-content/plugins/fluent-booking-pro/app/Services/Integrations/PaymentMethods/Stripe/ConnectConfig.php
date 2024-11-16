<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe;

use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\API\Account;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ConnectConfig
{
    private static $connectBase = 'https://apiv2.wpmanageninja.com/fluentform/';

    public static function getConnectConfig()
    {
        $configBase = self::$connectBase . 'stripe-connect';
        $hash = md5(site_url() . wp_generate_uuid4() . time());

        $liveArgs = [
            'url_base' => rawurlencode(admin_url('admin.php?stripe&source=fluent_booking&')),
            'mode'     => 'live',
            'hash'     => $hash,
            'source'   => 'fluent_calendar'
        ];

        $testArgs = [
            'url_base' => rawurlencode(admin_url('admin.php?stripe&source=fluent_booking&')),
            'mode'     => 'test',
            'hash'     => $hash
        ];

        $settings = (new StripeSettings())->get();

        return [
            'connect_config' => [
                'live_redirect' => add_query_arg($liveArgs, $configBase),
                'test_redirect' => add_query_arg($testArgs, $configBase),
                'image_url'     => FLUENT_BOOKING_URL . 'assets/images/payment-methods/stripe-icon.png',
            ],
            'test_account'   => self::getAccountInfo($settings, 'test'),
            'live_account'   => self::getAccountInfo($settings, 'live'),
            'settings'       => $settings
        ];
    }

    public static function verifyAuthorizeSuccess($data)
    {
        $response = wp_remote_post(self::$connectBase . 'stripe-verify-code', [
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'blocking'    => true,
            'headers'     => array(),
            'body'        => $data,
            'cookies'     => array()
        ]);

        if (is_wp_error($response)) {
            $message = $response->get_error_message();
            return '<div class="fct_message fct_message_error">' . esc_html($message) . '</div>';

        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($response['stripe_user_id'])) {
            $message = Arr::get($response, 'message');
            if (!$message) {
                $message = __('Invalid Stripe Request. Please configure stripe payment gateway again', 'fluent-booking-pro');
            }
            return  '<div class="fct_message fct_message_error">' . esc_html($message) . '</div>';

        }

        $settings = (new StripeSettings())->get();

        $settings['provider'] = 'connect';

        $settings['is_active'] = 'yes';

        if (!empty($response['livemode'])) {
            $settings['payment_mode'] = 'live';
            $settings['live_account_id'] = $response['stripe_user_id'];
            $settings['live_publishable_key'] = $response['stripe_publishable_key'];
            $settings['live_secret_key'] = $response['access_token'];
        } else {
            $settings['payment_mode'] = 'test';
            $settings['test_account_id'] = $response['stripe_user_id'];
            $settings['test_publishable_key'] = $response['stripe_publishable_key'];
            $settings['test_secret_key'] = $response['access_token'];
        }

        (new Stripe())->updateSettings($settings);

        return false;
    }

    private static function getAccountInfo($settings, $mode)
    {
        if ($settings['is_active'] != 'yes') {
            return false;
        }

        if ($settings['provider'] != 'connect') {
            return false;
        }

        $apiKey = $settings[$mode . '_secret_key'];

        $accountId = Arr::get($settings, $mode . '_account_id');

        if (!$accountId) {
            return false;
        }

        $account = Account::retrive($accountId, $apiKey);

        if (is_wp_error($account)) {
            return [
                'error' => $account->get_error_message()
            ];
        }

        // Find the email.
        $email = isset($account->email)
            ? esc_html($account->email)
            : '';

        // Find a Display Name.
        $display_name = isset($account->display_name)
            ? esc_html($account->display_name)
            : '';

        if (
            empty($display_name) &&
            isset($account->settings) &&
            isset($account->settings->dashboard) &&
            isset($account->settings->dashboard->display_name)
        ) {
            $display_name = esc_html($account->settings->dashboard->display_name);
        }

        return [
            'account_id'   => $accountId,
            'display_name' => $display_name,
            'email'        => $email
        ];

    }

    public static function disconnect($data, $sendResponse = true)
    {
        $mode = Arr::get($data, 'mode');
        $stripeSettings = (new StripeSettings())->get();

        if ($stripeSettings['is_active'] != 'yes') {
            if ($sendResponse) {
                wp_send_json_error([
                    'message' => __('Stripe mode is not active', 'fluent-booking-pro')
                ], 422);
            }
            return false;
        }


        if (empty($stripeSettings[$mode.'_account_id'])) {
            if ($sendResponse) {
                wp_send_json_error([
                    'message' => __('Selected Account does not exist', 'fluent-booking-pro')
                ], 422);
            }
            return false;
        }

        $stripeSettings[$mode.'_account_id'] = '';
        $stripeSettings[$mode.'_publishable_key'] = '';
        $stripeSettings[$mode.'_secret_key'] = '';

        if ($mode == 'live') {
            $alternateMode = 'test';
        } else {
            $alternateMode = 'live';
        }

        if (empty($stripeSettings[$alternateMode.'_account_id'])) {
            $stripeSettings['is_active'] = 'no';
            $stripeSettings['payment_mode'] = 'test';
        } else {
            $stripeSettings['payment_mode'] = $alternateMode;
        }
        
        $sendResponse = (new Stripe())->updateSettings($stripeSettings);

        if ($sendResponse) {
            wp_send_json_success([
                'message' => __('Stripe settings has been disconnected', 'fluent-booking-pro'),
                'settings' => $stripeSettings
            ], 200);
        }

        return true;
    }
}
