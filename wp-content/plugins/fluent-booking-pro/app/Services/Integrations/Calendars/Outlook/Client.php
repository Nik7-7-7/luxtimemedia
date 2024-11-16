<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Outlook;

use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class Client
{
    public $clientId;
    public $clientSecret;
    public $redirectUrl;

    private $accessToken;

    public $revokeUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/logout';
    public $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private $refreshTokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    public $authScope = 'Calendars.ReadWrite offline_access openid';

    public $calendarEvent = 'https://graph.microsoft.com/v1.0/me/calendars/{calendarId}/events';

    public function __construct($clientID, $clientSecret)
    {
        $this->clientId = $clientID;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = OutlookHelper::getAppRedirectUrl();
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getUserDetails()
    {
        $headers = [
            'Authorization' => 'Bearer ' . '',
            'Content-Type'  => 'application/json'
        ];

        return $this->makeRequest('https://graph.microsoft.com/v1.0/me', [], 'GET', $headers);
    }

    public function generateAuthCode($code)
    {
        $body = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->authScope,
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

        $tokens = $this->makeRequest($this->tokenUrl, $body, 'POST');

        if (is_wp_error($tokens)) {
            return $tokens;
        }

        $tokens['expires_in'] += time();

        return $tokens;
    }

    public function getCalendarLists($accessToken = null)
    {
        // $accessToken = 'EwCIA8l6BAAUAOyDv0l6PcCVu89kmzvqZmkWABkAASga72p5KU1A+axQ2za80P3eAtKsT5fjFv0JwtXYfvB1fYg94WdF8+erqQnO9wJYD4v6rQXETp3+8nx/LXEgAibHnVnM4wE6cb0Hwrpx5nSop8spLnaB7eacC4OAw2B0x1dj0SfgYR+dw3yN1BsY3CqUbW8rIsPy9jsEg+KFSdSkcxPKoQkFbvXyBBJ0CZ+COVJyCEyDTD0kYQnJVTwg38ml/jF0CQTqHsosMSBTqOebVR9U1Gifx+COJTD564c/xejCGB4AncjEGZKKcAFL9QwHomdkIB+9Loep1hfFzu3EXUQAXCKAAaxUHipfyWeHQIVN2yywaY6cX2uTdLa1AGgDZgAACHFReuEP4026WAKGE9/CWgm7p3Wmled/5hjbduPj7PUPR4xqeHo0KtKXCrbNqZtUrIaOFJIFu0/UHDj2IjOhCY+R9MofltIuuy3AMS3nxhEgTLXgoUqOB+RHrDo8hLwUmuBhB04ZMESsxiX4hnikmFWOZchjwBW4o/HKrwx12gogOo4GwoFPJ/a8PE5jN3wBFaOtsB0N08DWMJGfYwTXopCmKR3sURWU8RrRpEKsc3hLxBecMny3W+Ntu49oHYg0In8cZai9AyO2AcHvf0BrdNJ+sWIFOL3zLU7gIYBmGp9H55+VkXSONO5rXQlB0V+GDGscRl5JTiJqbo4ggTv6NRO93JmQxUC9slXblVqbuGfxNZz55i2Np0vuMpDjcz3PoP65ARU7Rpi/Ij3If8GESr5hmZ6+t/eltGe3LL9DYszZhu9jGcE3NfddeNn3Vz+BD+hqGiOr8B4cQ/7+CYOFxieydgFbOlNBhD/VV+GCXmXaR3R7O95Y4D2FCbszKFJev1+wiDRQPR80LNubrItqihUaLbBEaYrV7H8yH7/o9qIDcvzPqKLDXNbniMbrJ7fhoExxobTLCD7tOu9hf2wA2Hoanez5a+vXOmfecPEQ1AF3/78BrZvRJg33xqONVh05a+JqlONmlJETjhvEDRvBkEtipHzVJymVCm9ZSXUPQ3zn8nTxNyg1BRUgoMynTl+fe+1mXcmEW9ZiP52+4p88obPElivnz+/Vb8HCwLpF1SARX1DMmgArKTC8dzL/vxP3w7CJ0G3VFg1+BhuQTDnGuVxR4K/qYKR3zChk0z3csILNduqQAg==';
        $lists = $this->makeRequest('https://graph.microsoft.com/v1.0/me/calendars', [], 'GET', $this->getAuthorizationHeader($accessToken));

        if (is_wp_error($lists)) {
            return $lists;
        }

        $formattedLists = [];

        foreach ($lists['value'] as $item) {
            $formattedLists[] = [
                'id'        => $item['id'],
                'title'     => $item['name'] . ' (' . Arr::get($item, 'owner.address') . ')',
                'can_write' => Arr::isTrue($item, 'canEdit') ? 'yes' : 'no'
            ];
        }

        return $formattedLists;
    }

    public function getCalendarEvents($id, $args = [])
    {

        $minDate = $args['startDateTime'];
        $maxDate = $args['endDateTime'];

        $url = 'https://graph.microsoft.com/v1.0/me/calendars/' . $id . '/calendarview';
        $url .= '?$select=subject,recurrence,showAs,start,end,subject,isAllDay,transactionId&startdatetime=' . $minDate . '&enddatetime=' . $maxDate . '&$top=' . $args['maxResults'];

        // recurrence,showAs,start,end,subject

        $lists = $this->makeRequest($url, [], 'GET', $this->getAuthorizationHeader());

        if (is_wp_error($lists)) {
            return $lists;
        }

        $formattedLists = [];
        foreach ($lists['value'] as $item) {
            if (empty($item['start']['dateTime']) || Arr::get($item, 'showAs') == 'free') {
                continue;
            }

            $formattedLists[] = [
                'id'     => $item['id'],
                'start'  => Arr::get($item, 'start.dateTime'),
                'end'    => Arr::get($item, 'end.dateTime'),
                'status' => Arr::get($item, 'showAs')
            ];

        }

        return $formattedLists;
    }

    public function createEvent($calendarId, $data, $args = [])
    {
        $url = 'https://graph.microsoft.com/v1.0/me/calendars/' . $calendarId . '/events';

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        $siteUid = OutlookHelper::getUniqueSiteIdHash();

        $data['transactionId'] = $siteUid . '__' . $data['transactionId'];

        return $this->makeRequest($url, wp_json_encode($data), 'POST', $this->getAuthorizationHeader());
    }

    public function patchEvent($eventId, $data, $args = [])
    {
        $url = 'https://graph.microsoft.com/v1.0/me/events/' . $eventId;

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        return $this->makeRequest($url, wp_json_encode($data), 'PATCH', $this->getAuthorizationHeader());
    }

    public function getEvent($calendarId, $eventId, $args = [])
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarId . '/events/' . $eventId;

        if ($args) {
            $url = add_query_arg($args, $url);
        }

        return $this->makeRequest($url, '', 'GET', $this->getAuthorizationHeader());
    }

    public function deleteEvent($eventId)
    {
        return $this->makeRequest("https://graph.microsoft.com/v1.0/me/events/{$eventId}", [], 'DELETE', $this->getAuthorizationHeader());
    }

    public function revokeConnection()
    {
        return $this->makeRequest($this->revokeUrl, [], 'GET', $this->getAuthorizationHeader());
    }

    public function getAuthorizationHeader($accessToken = null)
    {
        if (!$accessToken) {
            $accessToken = $this->accessToken;
        }

        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json'
        ];
    }

    public function makeRequest($url, $body = null, $type = 'GET', $headers = null, $xtraArgs = [])
    {
        if (!$headers) {
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
        }

        $args = [
            'headers'     => $headers,
            'method'      => $type,
            'timeout'     => 20,
            'httpversion' => '1.1',
        ];

        if ($body) {
            if ($type == 'GET') {
                $url = add_query_arg($body, $url);
            } else {
                $args['body'] = $body;
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
            $message = Arr::get($resBody, 'error_description', __('Unexpected error from outlook api', 'fluent-booking-pro'));

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

    public function makeCurlPost($url, $body, $header)
    {
        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($body));

        // Execute the cURL session
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return new \WP_Error('curl_error', $error_msg);
        }

        // Close cURL session
        curl_close($ch);

        // Decode the response
        return json_decode($response, true);
    }

    public function getAuthUrl($userId)
    {
        return add_query_arg([
            'client_id'    => $this->clientId,
            'redirect_uri' => urlencode_deep(add_query_arg([
                'state' => $userId
            ], admin_url('admin-ajax.php?action=fluent_booking_outlook_auth')))
        ], $this->redirectUrl);
    }
}
