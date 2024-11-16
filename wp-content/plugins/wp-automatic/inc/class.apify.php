<?php
class ValvePress_APIFY
{

    public $token;
    public $link;
    public $ch;
    private $json_raw;

    public function __construct($token, $link, $ch)
    {
        $this->json_raw = file_get_contents(dirname(__FILE__) . '/apify-template.json');
        $this->token = $token;
        $this->link = $link;
        $this->ch = $ch;
        //curl_setopt ( $this->ch, CURLOPT_TIMEOUT, 90 );

    }

    /**
     * Fetches the content from APIFY.COM
     * @param string $apify_wait_for_mills - wait for x milliseconds before fetching the content from APIFY default 0
     * @return string - the content from APIFY.COM
     * @throws Exception - if the APIFY token is empty or the reply is empty or the reply contains an error or the reply does not contain pageContent
     */
    public function apify($apify_wait_for_mills = 0, $initial_cookies = '')
    {

        //empty reply
        if (wp_automatic_trim($this->token) == '') {
            throw new Exception('<span style="color:red">ERROR: You have enabled the option to use APIFY.COM, please visit the plugin settings page and add the required APIFY API token</span>');
        }

        //replacing the link in the json
        $json_to_post = wp_automatic_str_replace('https://www.example.com', $this->link, $this->json_raw);

        //replacing the cookies in the json
        //initialCookies sent is on the format cookie1=value1;cookie2=value2
        //we will need to conver it to puppeteer setCookie format
        /* format is
        [{
        'name': 'cookie1',
        'value': 'val1'
        },{
        'name': 'cookie2',
        'value': 'val2'
        },{
        'name': 'cookie3',
        'value': 'val3'
        }];
         */
        $cookies = array();
        $initial_cookies = wp_automatic_trim($initial_cookies);
        if ($initial_cookies != '') {
            $initial_cookies = explode(';', $initial_cookies);

            //filter empty cookies
            $initial_cookies = array_filter($initial_cookies);

            //grab the name from the link
            $domain = parse_url($this->link, PHP_URL_HOST);

            foreach ($initial_cookies as $cookie) {
                $cookie = explode('=', $cookie);
                $cookies[] = array(
                    'name' => wp_automatic_trim($cookie[0]),
                    'value' => wp_automatic_trim($cookie[1]),
                    'domain' => $domain,
                );
            }

            //convert to json
            $cookies = json_encode($cookies);

            //replace the cookies in the json, current is "initialCookies": []
            $json_to_post = wp_automatic_str_replace('"initialCookies": []', '"initialCookies": ' . $cookies, $json_to_post);

        }

        /*
        echo $json_to_post;exit;
        exit;
         */

        //parse int $api_wait_for_mills
        $apify_wait_for_mills = intval($apify_wait_for_mills);

        //if apify_wait_for_mills is not 0 and is a number
        if ($apify_wait_for_mills != 0 && is_numeric($apify_wait_for_mills)) {

            // add await context.waitFor(1000); to the json before const pageTitle
            $json_to_post = wp_automatic_str_replace('const pageTitle', 'await context.waitFor(' . $apify_wait_for_mills . ');\n    const pageTitle', $json_to_post);

            //echo $json_to_post;exit;
        }

        $curlurl = "https://api.apify.com/v2/acts/apify~web-scraper/run-sync-get-dataset-items?token=" . $this->token;

        curl_setopt($this->ch, CURLOPT_URL, $curlurl);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $json_to_post);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        $x = 'error';
        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        //empty reply
        if (wp_automatic_trim($exec) == '') {
            throw new Exception('Empty reply from APIFY ' . $x);
        }

        $json = json_decode($exec);

        //error
        if (isset($json->error)) {
            throw new Exception('Error from APIFY ' . $json->error->message);
        }

        //no content pageContent
        if (!isset($json[0]->pageContent)) {
            throw new Exception('No content returned from APIFY ');
        }

        //hotfix for feed encoded html entities &lt;?xml
        if (stristr($json[0]->pageContent, '&lt;?xml ')) {
            $json[0]->pageContent = html_entity_decode($json[0]->pageContent);
        }

        return $json[0]->pageContent;

    }

}
