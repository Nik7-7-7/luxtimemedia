<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\AppleCalendar;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class AppleHelper
{
    public static function getApiConfig()
    {
        $defaults = [
            'is_enabled'   => 'no',
            'caching_time' => '5'
        ];

        $settings = get_option('_fcal_apple_calendar_client_details', []);

        $settings = wp_parse_args($settings, $defaults);

        return $settings;
    }

    public static function updateConfig($settings)
    {
        $settings = [
            'is_enabled'   => Arr::get($settings, 'is_enabled', 'no'),
            'caching_time' => Arr::get($settings, 'caching_time', 5)
        ];

        update_option('_fcal_apple_calendar_client_details', $settings, 'no');

        return $settings;
    }

    public static function getClientByMeta(Meta $meta)
    {
        $settings = $meta->value;
        $userName = Arr::get($settings, 'remote_email');
        $passWord = Helper::decryptKey(Arr::get($settings, 'remote_pass'));

        if (!$userName || !$passWord) {
            return new \WP_Error('invalid_credentials', __('Invalid credentials', 'fluent-booking-pro'));
        }

        return new IcloudClient($userName, $passWord);
    }
}
