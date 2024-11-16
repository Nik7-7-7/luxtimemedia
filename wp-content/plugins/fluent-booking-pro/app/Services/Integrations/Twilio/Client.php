<?php

namespace FluentBookingPro\App\Services\Integrations\Twilio;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\Helper;

class Client
{
	protected $apiUrl = 'https://api.twilio.com/2010-04-01/';
	
	protected $accountSid;
	protected $authToken;

	public function __construct($accountSid, $authToken)
	{	
        $this->accountSid = $accountSid;
		$this->authToken = $authToken;
	}

    private function getAccessHeader()
    {
        return [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($this->accountSid . ':' . $this->authToken)
        ];
    }

	public function makeRequest($url, $body = null, $method = 'GET', $headers = null )
	{
        if (!$headers) {
            $headers = $this->getAccessHeader();
        }

        $args = [
            'headers' => $headers,
            'method'  => $method,
            'timeout' => 20
        ];

        if ($body) {
            if ($method == 'GET') {
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
            $message = Arr::get($resBody, 'error_description', __('Unexpected error from remote api', 'fluent-booking-pro'));

            Helper::debugLog([
                'message' => $message,
                'url'     => $url,
                'body'    => $body,
                'method'  => __METHOD__,
                'type'    => 'api_error'
            ]);

            return new \WP_Error('api_error', $message, $resBody);
        }

        return $resBody;
	}
	
	public function authTest()
	{
	    return $this->makeRequest($this->apiUrl . 'Accounts.json', [], 'GET');
	}


	public function sendSMS($accountId, $data)
    {
        $url = $this->apiUrl . 'Accounts/'.\rawurlencode($accountId).'/Messages.json';

        $response = $this->makeRequest($url, $data, 'POST');

        return $response;
    }

}
