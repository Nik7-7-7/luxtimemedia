<?php

namespace FluentBookingPro\App\Services\Integrations\ZoomMeeting;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class ZoomHelper
{
    public static function updateZoomCredentials($userId, $zoomCredentials)
    {
        $zoomCredentials = Arr::only($zoomCredentials, ['account_id', 'origin_email', 'origin_display_name', 'access_token', 'expires_at', 'client_id', 'client_secret']);

        if (!empty($zoomCredentials['access_token'])) {
            $zoomCredentials['access_token'] = Helper::encryptKey($zoomCredentials['access_token']);
        }

        if (!empty($zoomCredentials['client_secret'])) {
            $zoomCredentials['client_secret'] = Helper::encryptKey($zoomCredentials['client_secret']);
        }

        $meta = Meta::where('object_type', 'user_meta')
            ->where('object_id', $userId)
            ->where('key', 'zoom_credentials')
            ->first();

        if (!$meta) {
            $meta = new Meta();
            $meta->object_type = 'user_meta';
            $meta->object_id = $userId;
            $meta->key = 'zoom_credentials';
        }

        $meta->value = $zoomCredentials;
        $meta->save();

        return $meta;
    }

    public static function getZoomClient($userId)
    {
        $meta = Meta::where('object_type', 'user_meta')
            ->where('object_id', $userId)
            ->where('key', 'zoom_credentials')
            ->first();

        if (!$meta) {
            return new \WP_Error('wp_error', __('No zoom credentials found', 'fluent-booking-pro'));
        }

        $config = $meta->value;

        $config['client_secret'] = Helper::decryptKey($config['client_secret']);
        $config['access_token'] = Helper::decryptKey($config['access_token']);

        if ($config['expires_at'] - 10 < time()) {
            $client = new Client($config['client_id'], $config['client_secret'], $config['account_id']);
            $newConfig = $client->generateAccessToken();

            if(is_wp_error($newConfig)) {
                return $newConfig;
            }

            $savingConfig = $config;
            $savingConfig['client_secret'] = Helper::encryptKey($config['client_secret']);
            $savingConfig['access_token'] = Helper::encryptKey($newConfig['access_token']);
            $savingConfig['expires_at'] = $newConfig['expires_in'] + time();
            $meta->value = $savingConfig;
            $meta->save();
            $config['access_token'] = $newConfig['access_token'];
        }

        $client = new Client($config['client_id'], $config['client_secret'], $config['account_id']);

        return $client->setAccessToken($config['access_token']);
    }

    public static function isZoomConfigured($userId, $returnSettings = false)
    {
        $meta = Meta::where('object_type', 'user_meta')
            ->where('object_id', $userId)
            ->where('key', 'zoom_credentials')
            ->first();

        if (!$meta) {
            return false;
        }

        $zoomCredentials = $meta->value;

        if (empty($zoomCredentials['access_token'])) {
            return false;
        }

        if ($returnSettings) {
            return $zoomCredentials;
        }

        return true;
    }

    public static function getZoomConnectionFields()
    {
        return [
            'account_id'    => [
                'type'        => 'input-text',
                'label'       => __('Zoom Account ID', 'fluent-booking-pro'),
                'placeholder' => __('Enter Your Account ID', 'fluent-booking-pro'),
            ],
            'client_id'     => [
                'type'        => 'input-text',
                'label'       => __('Zoom App Client ID', 'fluent-booking-pro'),
                'placeholder' => __('Enter Your App Client ID', 'fluent-booking-pro'),
            ],
            'client_secret' => [
                'type'        => 'input-text',
                'label'       => __('Zoom App Secret Key', 'fluent-booking-pro'),
                'placeholder' => __('Enter Your App Secret Key', 'fluent-booking-pro'),
            ],
        ];
    }
}
