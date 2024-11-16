<?php
/**
 * Google Maps Places API wrapper
 */

class GoogleMapsPlaces
{
    private $apiKey;
    private $curlHandle;

    //public token
    public $next_page_token = null;

    public function __construct($apiKey, $curlHandle = null)
    {
        $this->apiKey = $apiKey;
        $this->curlHandle = $curlHandle ?: curl_init();
    }

    public function __destruct()
    {
       // curl_close($this->curlHandle);
    }

    /**
     * @param string $searchQuery
     * @return array|string
     * @doc https://developers.google.com/maps/documentation/places/web-service/search-text
     */
    public function getPlacesList($searchQuery, $next_page_token = null, $languageCode = '')
    {

        //reset next_page_token
        $this->next_page_token = null;

        $query = urlencode($searchQuery);
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$query}&key={$this->apiKey}";

        //if next_page_token is set then add it to the url
        if ($next_page_token) {
            $url .= "&pagetoken={$next_page_token}";
        }

        //if language code is set then add it to the url
        if ($languageCode) {
            $url .= "&language={$languageCode}";
        }


        $response = $this->makeApiRequest($url);
        if ($response['status'] != 'OK') {

            //thorw error if status is not ok
            throw new Exception("Error: " . $response['status'] . ':' . $response['error_message']);

        }

        //set next_page_token
        $this->next_page_token = isset($response['next_page_token']) ? $response['next_page_token'] : null;

        $places = [];

        //if isset results then set places to the result array
        if (isset($response['results'])) {
            $places = $response['results'];
        }

        return $places;
    }

    /**
     * @param string $placeId
     * @return array|string
     * @doc https://developers.google.com/maps/documentation/places/web-service/details
     */
    public function getPlaceDetails($placeId , $languageCode = '')
    {
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&key={$this->apiKey}";

        //if language code is set then add it to the url
        if (trim($languageCode) != '') {
            $url .= "&language={$languageCode}";
        }


        $response = $this->makeApiRequest($url);
        if ($response['status'] != 'OK') {
            return 'Error: ' . $response['status'];
        }

        //print_r($response);

        return $response['result'];
    }

    private function makeApiRequest($url)
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($this->curlHandle);

        return json_decode($response, true);
    }

}
