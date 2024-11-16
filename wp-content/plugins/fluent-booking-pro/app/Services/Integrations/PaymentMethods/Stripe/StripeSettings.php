<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe;

use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\CurrenciesHelper;

class StripeSettings
{

    public $settings;

    protected $methodHandler = 'fluent_booking_payment_settings_stripe';

    public function __construct()
    {
        $settings = get_option($this->methodHandler, []);

        if (!$settings) {
            $settings['provider'] = 'connect';
        }

        $settings = wp_parse_args($settings, static::getDefaults());

        if ($settings['provider'] == 'connect' && apply_filters('fluent_booking_form_disable_stripe_connect', false)) {
            $settings['provider'] = 'api_keys';
        }

        if (isset($settings['test_secret_key'])) {
            $settings['test_secret_key'] = Helper::decryptKey($settings['test_secret_key']);
        }

        if (isset($settings['live_secret_key'])) {
            $settings['live_secret_key'] = Helper::decryptKey($settings['live_secret_key']);
        }

        $this->settings = $settings;
    }

    /**
     * @return array with default fields value
     */
    public static function getDefaults()
    {
        $currency = CurrenciesHelper::getGlobalCurrency();

        return [
            'is_active'            => 'no',
            'test_publishable_key' => '',
            'test_secret_key'      => '',
            'live_publishable_key' => '',
            'live_secret_key'      => '',
            'payment_mode'         => 'test',
            'provider'             => 'api_keys',
            'test_account_id'      => '',
            'live_account_id'      => '',
            'checkout_mode'        => 'onsite',
            'currency'             => $currency,
        ];
    }

    public function isActive()
    {
        return $this->settings['is_active'] == 'yes';
    }

    public function get()
    {
        return $this->settings;
    }

    public function getMode()
    {
        return $this->settings['payment_mode'];
    }

    public function getPublicKey()
    {
        if ($this->getMode() === 'live') {
            return $this->get()['live_publishable_key'];
        }

        return $this->get()['test_publishable_key'];
    }

    public function getApiKey()
    {
        if ($this->getMode() === 'live') {
            return $this->get()['live_secret_key'];
        }

        return $this->get()['test_secret_key'];
    }

    public static function getPaymentDescriptor($calendarEvent)
    {
        $descriptor = $calendarEvent->title;
        
        // Check if the string contains at least one Latin character
        if (!preg_match('/[a-zA-Z]/', $descriptor)) {
            $descriptor = 'Event: ' . $descriptor;
        }
        $descriptor = stripslashes($descriptor);

        // Remove illegal characters
        $descriptor = str_replace(['<', '>', '"', "'"], '', $descriptor);
        $descriptor = preg_replace('/[^a-zA-Z ]/', '', $descriptor);

        // Descriptor should be 22 characters max
        $descriptor = substr($descriptor, 0, 22);

        if (!$descriptor || strlen($descriptor) < 5) {
            $descriptor = 'FluentBooking';
        }
        return $descriptor;
    }
}
