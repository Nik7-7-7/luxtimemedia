<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Outlook;

use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Support\Arr;

class OutlookHelper
{
    public static function getApiConfig()
    {
        $options = get_option('_fcal_outlook_calendar_client_details', []);
        return apply_filters('fluent_booking/outlook_app_credentials', [
            'client_id'         => 'db98d3d0-c944-41f8-bb01-555c913a903b',
            'client_secret'     => 'B2J8Q~sOFsiiHrqk_ajYL3NcIAv0mEPu6hWuLbHV',
            'constant_defined'  => true,
            'is_system_defined' => 'yes',
            'caching_time'      => Arr::get($options, 'caching_time', 5)
        ]);
    }

    public static function updateApiConfig($settings)
    {
        $settings = Arr::only($settings, ['caching_time']);
        update_option('_fcal_outlook_calendar_client_details', $settings, 'no');
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

    public static function getApiClientByUserId($userId, $remoteId = null)
    {
        if (!self::isConfigured()) {
            return null;
        }

        $metas = Meta::query()->where('object_type', '_outlook_user_token')
            ->where('object_id', $userId)
            ->get();

        if ($metas->isEmpty()) {
            return null;
        }

        if ($metas->count() == 1 || !$remoteId) {
            return new OutlookCalendar($metas->first());
        }

        foreach ($metas as $meta) {
            $settings = $meta->value;
            $calendarLists = Arr::get($settings, 'calendar_lists', []);
            foreach ($calendarLists as $list) {
                if (Arr::get($list, 'id') == $remoteId) {
                    return new OutlookCalendar($meta);
                }
            }
        }

        return null;
    }

    public static function isConfigured()
    {
        return true;
        $config = self::getApiConfig();
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    public static function getEmailByIdToken($token)
    {
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload, true);

        if (empty($jwtPayload['email'])) {
            return new \WP_Error('payload_error', __('Sorry! There has an error when fetching data for outlook authentication. Please try again', 'fluent-booking-pro'));
        }

        return Arr::get($jwtPayload, 'email');
    }

    public static function getConflictCheckCalendars($hostIds)
    {
        $metaItems = Meta::where('object_type', '_outlook_user_token')
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
        return apply_filters('fluent_booking/outlook_app_redirect_url', 'https://fluentbooking.com/wp-json/fluent-api/outlook/');
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

        $hash = substr($hash, 0, 10);

        update_option('__fcal_unique_site_id', $hash, 'no');

        return $hash;
    }

}
