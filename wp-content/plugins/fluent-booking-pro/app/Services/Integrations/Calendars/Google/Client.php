<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\Framework\Support\Arr;

class Client
{
    public $clientId;
    public $clientSecret;
    public $redirectUrl;

    private $accessToken;

    public $revokeUrl = 'https://oauth2.googleapis.com/revoke';
    public $tokenUrl = 'https://oauth2.googleapis.com/token';
    private $refreshTokenUrl = 'https://www.googleapis.com/oauth2/v3/token';
    public $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    public $authScope = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events';

    public $calendarEvent = 'https://www.googleapis.com/calendar/v3/calendars/primary/events/';

    public function __construct($clientID, $clientSecret)
    {
        $this->clientId = $clientID;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = GoogleHelper::getAppRedirectUrl();
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function generateAuthCode($code)
    {
        $body = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        ];

        return $this->makeRequest($this->tokenUrl, $body, 'POST');
    }

    public function reGenerateToken($refreshToken)
    {
        $body = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken
        ];

        $tokens = $this->makeRequest($this->refreshTokenUrl, $body, 'POST', [
            'Content-Type'              => 'application/http',
            'Content-Transfer-Encoding' => 'binary',
            'MIME-Version'              => '1.0',
        ]);

        if (is_wp_error($tokens)) {
            return $tokens;
        }

        $tokens['expires_in'] += time();

        return $tokens;
    }

    public function getFreeBusy($args)
    {
        return $this->makeRequest('https://www.googleapis.com/calendar/v3/freeBusy', $args, 'POST', $this->getAuthorizationHeader());
    }

    public function getCalendarLists($accessToken = null)
    {
        $lists = $this->makeRequest('https://www.googleapis.com/calendar/v3/users/me/calendarList', [], 'GET', $this->getAuthorizationHeader($accessToken));

        if (is_wp_error($lists)) {
            return $lists;
        }

        $formattedLists = [];

        foreach ($lists['items'] as $item) {
            $formattedLists[] = [
                'id'        => $item['id'],
                'title'     => $item['summary'],
                'can_write' => in_array($item['accessRole'], ['owner', 'writer']) ? 'yes' : 'no'
            ];
        }

        return $formattedLists;
    }

    public function getCalendarEvents($id, $args = [])
    {
        $lists = $this->makeRequest('https://www.googleapis.com/calendar/v3/calendars/' . $id . '/events', $args, 'GET', $this->getAuthorizationHeader());

        if (is_wp_error($lists)) {
            return $lists;
        }

        $siteUid = GoogleHelper::getUniqueSiteIdHash();

        $formattedLists = [];
        foreach ($lists['items'] as $item) {
            $sharedData = Arr::get($item, 'extendedProperties.shared');
            if ($sharedData && Arr::get($sharedData, 'created_by') == 'fluent-booking-pro' && Arr::get($sharedData, 'site_uid') == $siteUid) {
                continue;
            }

            if (empty($item['status']) || $item['status'] == 'cancelled') {
                continue;
            }

            $recurrence = Arr::get($item, 'recurrence', []);

            if (!empty($item['start']['date'])) {
                if ($recurrence) {
                    $item['start']['dateTime'] = DateTimeHelper::convertToUtc($item['start']['date'], $lists['timeZone'], 'Y-m-d');
                } else {
                    $item['start']['dateTime'] = DateTimeHelper::convertToUtc($item['start']['date'], $lists['timeZone'], 'Y-m-d\TH:i:s\Z');
                }
            }

            if (empty($item['start']['dateTime'])) {
                continue;
            }

            if (!empty($item['end']['date'])) {
                if ($recurrence) {
                    $item['end']['dateTime'] = DateTimeHelper::convertToUtc($item['end']['date'], $lists['timeZone'], 'Y-m-d');
                } else {
                    $item['end']['dateTime'] = DateTimeHelper::convertToUtc($item['end']['date'], $lists['timeZone'], 'Y-m-d\TH:i:s\Z');
                }
            }

            if ($recurrence) {
                $sampleStart = Arr::get($item, 'start.dateTime');
                $recurrenceDate = RemoteCalendarHelper::getRruleDates($recurrence, [
                    $sampleStart,
                    Arr::get($item, 'end.dateTime'),
                ], $args['timeMin'], $args['timeMax'], [
                    'status'    => Arr::get($item, 'status'),
                    'rec_start' => $sampleStart
                ]);

                if ($recurrenceDate) {
                    $formattedLists = array_merge($formattedLists, $recurrenceDate);
                }
            } else {
                $formattedLists[] = [
                    'start'  => Arr::get($item, 'start.dateTime'),
                    'end'    => Arr::get($item, 'end.dateTime'),
                    'status' => Arr::get($item, 'status'),
                ];
            }
        }

        return $formattedLists;
    }

    public function createEvent($calendarId, $data, $args = [])
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarId . '/events';

        if (!empty($data['conferenceData'])) {
            $url .= '?conferenceDataVersion=1';
        }

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        return $this->makeRequest($url, $data, 'POST', $this->getAuthorizationHeader());
    }

    public function patchEvent($calendarId, $eventId, $data, $args = [])
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarId . '/events/' . $eventId;

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        return $this->makeRequest($url, $data, 'PATCH', $this->getAuthorizationHeader());
    }

    public function getEvent($calendarId, $eventId, $args = [])
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarId . '/events/' . $eventId;

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        return $this->makeRequest($url, '', 'GET', $this->getAuthorizationHeader());
    }

    public function revokeConnection()
    {
        return $this->makeRequest($this->revokeUrl, [
            'token' => $this->accessToken
        ], 'POST');
    }

    public function getAuthorizationHeader($accessToken = null)
    {
        if (!$accessToken) {
            $accessToken = $this->accessToken;
        }

        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json; charset=utf-8'
        ];
    }

    public function makeRequest($url, $body = null, $type = 'GET', $headers = null)
    {
        if (!$headers) {
            $headers = [
                'Content-Type'              => 'application/http',
                'Content-Transfer-Encoding' => 'binary',
                'MIME-Version'              => '1.0',
            ];
        }

        $args = [
            'headers' => $headers,
            'method'  => $type,
            'timeout' => 20
        ];

        if ($body) {
            if ($type == 'GET') {
                $url = add_query_arg($body, $url);
            } else {
                $args['body'] = wp_json_encode($body);
            }
        }

        $request = wp_remote_request($url, $args);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            Helper::debugLog([
                'message' => $message,
                'url'     => $url,
                'body'    => $body,
                'method'  => __METHOD__,
                'type'    => 'wp_request_error'
            ]);
            return new \WP_Error('wp_error', $message, $request->get_all_error_data());
        }

        $resCode = wp_remote_retrieve_response_code($request);

        $resBody = json_decode(wp_remote_retrieve_body($request), true);

        if ($resCode > 299) {
            $message = Arr::get($resBody, 'error_description', __('Unexpected error from google api', 'fluent-booking-pro'));

            Helper::debugLog([
                'message' => $resBody,
                'url'     => $url,
                'body'    => $body,
                'header'  => $headers,
                'method'  => __METHOD__,
                'type'    => 'api_error'
            ]);

            return new \WP_Error('api_error', $message, $resBody);
        }

        return $resBody;
    }

    public function getAuthUrl($userId)
    {
        if (GoogleHelper::isUsingNativeApp()) {
            return add_query_arg([
                'client_id'    => $this->clientId,
                'redirect_uri' => urlencode_deep(admin_url('admin-ajax.php?action=fluent_booking_g_auth&state=' . $userId)),
            ], $this->redirectUrl);
        }

        $authUrl = add_query_arg([
            'client_id'     => $this->clientId,
            'scope'         => urlencode_deep($this->authScope),
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'access_type'   => 'offline',
            'state'         => $userId,
            'prompt'        => 'consent'
        ], $this->authUrl);

        return $authUrl;
    }
}
