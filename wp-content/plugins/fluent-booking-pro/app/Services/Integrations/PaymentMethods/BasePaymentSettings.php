<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods;

abstract class BasePaymentSettings
{
    protected $settings;

    protected $methodHandler = 'fluent_booking_payment_settings_';

    public function __construct($slug)
    {
        $settings = get_option($this->methodHandler.$slug, []);
        $this->settings = wp_parse_args($settings, $this->getDefaultSettings());
    }

    abstract protected function getDefaultSettings();

    abstract public function isActive();

    public function get()
    {
        return $this->settings;
    }
    abstract public function getMode();

}
