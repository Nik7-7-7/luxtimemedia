<?php

class bwp_multi_http
{
    private $requests = array();
    private $timeout;

    public function __construct($timeout = 30)
    {
        $this->timeout = $timeout;
    }

    public function addRequest($method, $url, $data = null)
    {
        $this->requests[] = array(
            'method' => $method,
            'url' => $url,
            'data' => $data,
        );
    }

    public function execute()
    {
        if (extension_loaded('curl')) {
            return $this->executeWithCurl();
        } else {
            return $this->executeWithFileGetContents();
        }
    }

    private function executeWithCurl()
    {
        $responses = array();
        $mh = curl_multi_init();
        $handles = array();

        foreach ($this->requests as $key => $request) {
            $ch = curl_init($request['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

            // Set custom User-Agent header
            curl_setopt($ch, CURLOPT_USERAGENT, 'BerqWP Bot');

            if ($request['method'] === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request['data']));
            }

            // Disable SSL certificate verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Add handle to multi handle
            curl_multi_add_handle($mh, $ch);
            $handles[$key] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        foreach ($handles as $key => $ch) {
            $response = curl_multi_getcontent($ch);
            if ($response === false) {
                $responses[] = array(
                    'error' => 'Error fetching URL: ' . $this->requests[$key]['url'],
                );
            } else {
                $responses[] = $response;
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);

        return $responses;
    }


    private function executeWithFileGetContents()
    {
        $responses = array();

        foreach ($this->requests as $request) {
            $contextOptions = array(
                'http' => array(
                    'method' => $request['method'],
                    'timeout' => $this->timeout,
                    'ignore_errors' => true, // Disables SSL certificate validation
                )
            );

            // Set custom User-Agent header
            $contextOptions['http']['header'] = "User-Agent: BerqWP Bot\r\n";

            if ($request['method'] === 'POST') {
                $contextOptions['http']['content'] = http_build_query($request['data']);
            }

            $context = stream_context_create($contextOptions);
            $response = file_get_contents($request['url'], false, $context);

            if ($response === false) {
                $responses[] = array(
                    'error' => 'Error fetching URL: ' . $request['url'],
                );
            } else {
                $responses[] = $response;
            }
        }

        return $responses;
    }
}
