<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class GoogleHelper
{
    public static function getApiConfig()
    {
        if (defined('FLUENT_BOOKING_G_AUTH_CLIENT_ID') && defined('FLUENT_BOOKING_G_AUTH_CLIENT_SECRET')) {
            return [
                'client_id'        => FLUENT_BOOKING_G_AUTH_CLIENT_ID,
                'client_secret'    => FLUENT_BOOKING_G_AUTH_CLIENT_SECRET,
                'constant_defined' => true,
                'caching_time'     => defined('FLUENT_BOOKING_G_API_CACHING_TIME') ? FLUENT_BOOKING_G_API_CACHING_TIME : '5'
            ];
        }

        $settings = get_option('_fcal_google_calendar_client_details', []);

        if (!$settings) {
            return [
                'client_id'        => '530691696829-0g22sbe8qnqtoh58v1rkc34efhrlld5c.apps.googleusercontent.com',
                'client_secret'    => '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',
                'driver_type'      => 'system_defined',
                'constant_defined' => true,
                'caching_time'     => '5'
            ];
        }

        if (empty($settings['client_id']) || empty($settings['client_secret']) || Arr::get($settings, 'driver_type') == 'system_defined') {
            return [
                'client_id'        => '530691696829-0g22sbe8qnqtoh58v1rkc34efhrlld5c.apps.googleusercontent.com',
                'client_secret'    => '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',
                'driver_type'      => 'system_defined',
                'constant_defined' => true,
                'caching_time'     => Arr::get($settings, 'caching_time', 5)
            ];
        }

        $defaults = [
            'client_id'     => '',
            'client_secret' => '',
            'caching_time'  => '5',
            'driver_type'   => 'custom_defined'
        ];

        $settings = get_option('_fcal_google_calendar_client_details', []);

        $settings = wp_parse_args($settings, $defaults);

        if (!empty($settings['client_secret'])) {
            $settings['client_secret'] = Helper::decryptKey($settings['client_secret']);
        }

        return $settings;
    }

    public static function updateApiConfig($settings)
    {
        if (defined('FLUENT_BOOKING_G_AUTH_CLIENT_ID') && defined('FLUENT_BOOKING_G_AUTH_CLIENT_SECRET')) {
            return [
                'client_id'        => FLUENT_BOOKING_G_AUTH_CLIENT_ID,
                'client_secret'    => FLUENT_BOOKING_G_AUTH_CLIENT_SECRET,
                'constant_defined' => true
            ];
        }

        $settings = Arr::only($settings, ['client_id', 'client_secret', 'driver_type', 'caching_time']);

        if (Arr::get($settings, 'driver_type') == 'system_defined') {
            $settings = [
                'caching_time' => Arr::get($settings, 'caching_time', 5),
                'driver_type'  => 'system_defined'
            ];
        } else if (!empty($settings['client_secret'])) {
            if ($settings['client_secret'] == '********************') {
                $oldSettings = self::getApiConfig();
                $settings['client_secret'] = $oldSettings['client_secret'];
            }
            $settings['client_secret'] = Helper::encryptKey($settings['client_secret']);
        }

        update_option('_fcal_google_calendar_client_details', $settings, 'no');

        return self::getApiConfig();
    }

    public static function getApiClient($accessToken = null)
    {
        $config = self::getApiConfig();
        $client = new Client($config['client_id'], $config['client_secret']);

        if ($accessToken) {
            $client = $client->setAccessToken($accessToken);
        }

        return $client;
    }

    public static function getApiClientByUserId($userId = null, $remoteId = null)
    {
        if (!self::isConfigured()) {
            return null;
        }

        $metas = Meta::query()->where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->get();

        if ($metas->isEmpty()) {
            return null;
        }

        if ($metas->count() == 1 || !$remoteId) {
            return new GoogleCalendar($metas->first());
        }

        foreach ($metas as $meta) {
            $settings = $meta->value;
            $calendarLists = Arr::get($settings, 'calendar_lists', []);
            foreach ($calendarLists as $list) {
                if (Arr::get($list, 'id') == $remoteId) {
                    return new GoogleCalendar($meta);
                }
            }
        }

        return null;
    }

    public static function isConfigured()
    {
        $config = self::getApiConfig();
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    public static function getEmailByIdToken($token)
    {
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload, true);

        if (empty($jwtPayload['email'])) {
            return new \WP_Error('payload_error', __('Sorry! There has an error when fetching data for google authentication. Please try again', 'fluent-booking-pro'));
        }

        return Arr::get($jwtPayload, 'email');
    }

    public static function getConflictCheckCalendars($hostIds)
    {
        $metaItems = Meta::where('object_type', '_google_user_token')
            ->whereIn('object_id', $hostIds)
            ->get();

        if ($metaItems->isEmpty()) {
            return [];
        }

        $calendars = [];
        foreach ($metaItems as $item) {
            $settings = $item->value;
            $checkIds = Arr::get($settings, 'conflict_check_ids', []);
            if (empty($checkIds)) {
                continue;
            }

            $itemValidCalendars = [];
            $allCalendars = Arr::get($settings, 'calendar_lists', []);
            foreach ($allCalendars as $calendar) {
                if (in_array($calendar['id'], $checkIds)) {
                    $itemValidCalendars[] = $calendar['id'];
                }
            }

            if ($itemValidCalendars) {
                $calendars[] = [
                    'item'      => $item,
                    'check_ids' => $itemValidCalendars
                ];
            }
        }

        return $calendars;
    }

    public static function getAppRedirectUrl()
    {
        if (self::isUsingNativeApp()) {
            return 'https://fluentbooking.com/wp-json/fluent-api/google-calendar';
        }

        if (defined('FLUENT_BOOKING_GOOGLE_REDIRECT_URL')) {
            return FLUENT_BOOKING_GOOGLE_REDIRECT_URL;
        }
        return admin_url('admin-ajax.php?action=fluent_booking_g_auth');
    }

    public static function getUniqueSiteIdHash()
    {

        static $hash = null;

        if ($hash) {
            return $hash;
        }

        if (defined('FLUENT_BOOKING_UNIQUE_SITE_ID')) {
            $hash = FLUENT_BOOKING_UNIQUE_SITE_ID;
            return $hash;
        }

        $exist = get_option('__fcal_unique_site_id');

        if ($exist) {
            $hash = (string)$exist;
            return $hash;
        }

        $hash = md5(site_url('/') . time());

        update_option('__fcal_unique_site_id', $hash, 'no');

        return $hash;
    }

    public static function getAppReirectUrl()
    {
        if (self::isUsingNativeApp()) {
            return 'https://fluentbooking.com/wp-json/fluent-api/google-calendar';
        }

        if (defined('FLUENT_BOOKING_GOOGLE_REDIRECT_URL')) {
            return FLUENT_BOOKING_GOOGLE_REDIRECT_URL;
        }

        return admin_url('admin-ajax.php?action=fluent_booking_g_auth');
    }

    public static function isUsingNativeApp()
    {
        $settings = self::getApiConfig();
        return Arr::get($settings, 'driver_type') == 'system_defined';
    }
}
