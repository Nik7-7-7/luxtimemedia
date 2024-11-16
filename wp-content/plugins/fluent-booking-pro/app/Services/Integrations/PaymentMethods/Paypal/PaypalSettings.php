<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Paypal;

use FluentBooking\App\Services\Helper;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\PaymentHelper;

class PaypalSettings
{
    public $settings;

    protected $methodHandler = 'fluent_booking_payment_settings_paypal';

    public function __construct()
    {
        $settings = get_option($this->methodHandler, []);

        $settings = wp_parse_args($settings, static::getDefaults());

        $this->settings = $settings;
    }

    /**
     * @return array with default fields value
     */
    public static function getDefaults()
    {
        return [
            'is_active'                => 'no',
            'paypal_email'             => '',
            'payment_mode'             => 'test',
            'disable_ipn_verification' => 'no'
        ];
    }

    public function get()
    {
        return $this->settings;
    }

    public function isActive()
    {
        return $this->settings['is_active'] == 'yes';
    }

    public function isEnabledIPN()
    {
        return $this->settings['disable_ipn_verification'] != 'yes';
    }

    public function isTest()
    {
        return $this->getMode() == 'test';
    }

    public function getMode()
    {
        return $this->settings['payment_mode'];
    }

    public function getPaypalEmail()
    {
        return $this->settings['paypal_email'];
    }

    public static function getIpnUrl()
    {
        return (new PaymentHelper('paypal'))->listenerUrl();
    }
}
