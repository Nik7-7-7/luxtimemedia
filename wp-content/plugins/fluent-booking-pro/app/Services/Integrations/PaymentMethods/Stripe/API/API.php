<?php

namespace FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\API;

use FluentBookingPro\App\Services\Integrations\PaymentMethods\Stripe\StripeSettings;
use FluentBooking\Framework\Support\Arr;

class API
{
    private $createSessionUrl;
    private $apiUrl = 'https://api.stripe.com/v1/';

    public function makeRequest($path, $data, $apiKey, $method = 'GET')
    {
        $sessionHeaders = array(
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $requestData = array(
            'headers' => $sessionHeaders,
            'body' => http_build_query($data),
            'method' => $method,
        );

        $url = $this->apiUrl . $path;

        $sessionResponse = wp_remote_post($url, $requestData);

        if (is_wp_error($sessionResponse)) {
            echo esc_html__("API Error: ", "fluent-booking-pro") . esc_html($sessionResponse->get_error_message());
            exit;
        }

        $sessionResponseData = wp_remote_retrieve_body($sessionResponse);

        $sessionData = json_decode($sessionResponseData, true);

        if (empty($sessionData['id'])) {
            $message = Arr::get($sessionData, 'detail');
            if (!$message) {
                $message = Arr::get($sessionData, 'error.message');
            }
            if (!$message) {
                $message = __('Unknown Stripe API request error', 'fluent-booking-pro');
            }

            return new \WP_Error(422, $message, $sessionData);
        }

        return $sessionData;
    }


    public function verifyIPN()
    {
        if (!isset($_REQUEST['fluent_booking_payment_listener'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $post_data = @file_get_contents('php://input');

        $data =  json_decode($post_data);

        if ($data->id) {
            status_header(200);
            return $data;
        } else {
            return false;
        }

        exit(200);
    }

    public function getInvoice($eventId)
    {
        $api = new ApiRequest();
        $api::set_secret_key((new StripeSettings())->getApiKey());
        return $api::request([], 'events/' . $eventId, 'GET');
    }
}
