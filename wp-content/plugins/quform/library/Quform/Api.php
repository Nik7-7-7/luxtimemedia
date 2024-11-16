<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Api
{
    /**
     * @var string
     */
    const API_URL = 'https://api.quform.com/wp-json/quform/v1';

    /**
     * @var string
     */
    const API_URL_INSECURE = 'http://api.quform.com/wp-json/quform/v1';

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param Quform_Options $options
     */
    public function __construct(Quform_Options $options)
    {
        $this->options = $options;
    }

    /**
     * Send a request to the Quform API
     *
     * @param   string      $endpoint  The API endpoint to send the request to
     * @param   array       $data      The request data
     * @param   string      $method    The HTTP method to use
     * @return  array|bool             The response array or false on failure
     */
    public function request($endpoint, $data, $method = 'GET')
    {
        $url = $this->options->get('secureApiRequests') ? self::API_URL : self::API_URL_INSECURE;
        $url .= '/' . trim($endpoint, '/');

        $response = wp_remote_request($url, array(
            'method' => $method,
            'body' => $data,
            'timeout' => 10
        ));

        if (is_wp_error($response) || ! strlen($body = wp_remote_retrieve_body($response))) {
            return false;
        }

        $response = json_decode($body, true);

        if ( ! is_array($response)) {
            return false;
        }

        return $response;
    }

    /**
     * Send a GET request to the Quform API
     *
     * @param   string      $endpoint  The API endpoint to send the request to
     * @param   array       $data      The request data
     * @return  array|bool             The response array or false on failure
     */
    public function get($endpoint, $data)
    {
        return $this->request($endpoint, $data, 'GET');
    }

    /**
     * Send a POST request to the Quform API
     *
     * @param   string      $endpoint  The API endpoint to send the request to
     * @param   array       $data      The request data
     * @return  array|bool             The response array or false on failure
     */
    public function post($endpoint, $data)
    {
        return $this->request($endpoint, $data, 'POST');
    }
}
