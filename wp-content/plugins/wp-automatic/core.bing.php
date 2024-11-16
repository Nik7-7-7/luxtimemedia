<?php
//docs https://learn.microsoft.com/en-us/bing/search-apis/bing-news-search/reference/query-parameters

// modify camp on the go
$campaign->camp_type = 'Feeds';
$campaign->camp_sub_type = 'BingNews';

//camp_keywords
$camp_keywords = $campaign->camp_keywords;

//explode by comma
$camp_keywords_arr = explode(',', $camp_keywords);

//add BingNews: text to the beginning of each keyword
$camp_keywords_arr = array_map(function ($keyword) {
    return 'BingNews: ' . $keyword;
}, $camp_keywords_arr);

//implode by new line
$camp_keywords_final = implode("\n", $camp_keywords_arr);

//set ->feeds
$campaign->feeds = $camp_keywords_final;

// feeds class
require_once 'core.feeds.php';

/**
 * Wraps the wp_automtic_keyword_to_feed_bing function to be used in the wp_automatic_feeds class.
 *
 * This function takes a feed and a campaign object, processes the feed, and generates a Bing search feed URL
 * based on the provided feed and campaign settings.
 *
 * @param string $feed The feed to be converted into a Bing search feed URL.
 * @param object $camp The campaign object containing settings and configurations.
 * @return string The generated Bing search feed URL.
 */
function wp_automtic_keyword_to_feed_bing_wrap($feed, $camp)
{

    //feed is BingNews: keyword

    //get keyword
    $keyword = str_replace('BingNews: ', '', $feed);

    //echo
    echo '<br> Converting keywrod to Bing News Feed: ' . $keyword;

    //get feed
    try {
        $feed_link = wp_automtic_keyword_to_feed_bing($keyword, $camp);
    } catch (Exception $e) {

        //echo in red
        echo '<br><span style="color:red;">' . $e->getMessage() . '</span>';

        //return
        return $feed;

    }

    //return feed
    return $feed_link;

}

/**
 * Converts a keyword into a Bing search feed URL.
 *
 * This function takes a keyword and a campaign object, processes the keyword,
 * and generates a Bing search feed URL based on the provided keyword and campaign settings.
 *
 * @param string $keyword The keyword to be converted into a Bing search feed URL.
 * @param object $camp The campaign object containing settings and configurations.
 * @return string The generated Bing search feed URL.
 */
function wp_automtic_keyword_to_feed_bing($keyword, $camp)
{

    //camp general
     
    if( stristr($camp->camp_general, 'a:') ) $camp->camp_general=base64_encode($camp->camp_general);
    $camp_general = unserialize (base64_decode( $camp->camp_general) );
    @$camp_general=array_map('wp_automatic_stripslashes', $camp_general);
    

    //$campaignID
    $campaignID = $camp->camp_id;

    //md5 hash of the keyword
    $md5 = md5($keyword);

    // Your Bing News Search API key wp_automatic_bing_key
    $apiKey = trim(get_option('wp_automatic_bing_key', ''));

    // validate key not empty
    if (empty($apiKey)) {
        throw new Exception('Bing API key is empty, please set it in the settings page');
    }

    // The endpoint for the Bing News Search API
    $endpoint = 'https://api.bing.microsoft.com/v7.0/news/search';

    // The query parameters
    $params = [
        'q' => urlencode($keyword), // The search keyword
    ];

    //freshness defaults to day cg_bing_freshness
    $cg_bing_freshness = $camp_general['cg_bing_freshness'];

    //validate freshness
    if (!empty($cg_bing_freshness)) {
        $params['freshness'] = $cg_bing_freshness;
    }

    //cg_bing_sortBy
    $cg_bing_sortBy = $camp_general['cg_bing_sortBy'];

    //validate sortBy
    if (!empty($cg_bing_sortBy)) {
        $params['sortBy'] = $cg_bing_sortBy;
    }

    //cg_bing_cc
    $cg_bing_cc = $camp_general['cg_bing_cc'];

    //validate cc
    if (!empty($cg_bing_cc)) {
        $params['cc'] = $cg_bing_cc;
    }

    //cg_bing_cat
    $cg_bing_cat = $camp_general['cg_bing_cat'];

    //validate cat
    if (!empty($cg_bing_cat)) {
        $params['category'] = $cg_bing_cat;
    }

    //cg_bing_setLang
    $cg_bing_setLang = $camp_general['cg_bing_setLang'];

    //validate setLang
    if (!empty($cg_bing_setLang)) {
        $params['setLang'] = $cg_bing_setLang;
    }

    //cg_bing_count
    $cg_bing_count = $camp_general['cg_bing_count'];

    //validate count
    if (!empty($cg_bing_count)) {
        $params['count'] = $cg_bing_count;
    }else{
        $params['count'] = 10;
    }


    // Build the full URL with query parameters
    $url = $endpoint . '?' . http_build_query($params);

    echo '<br> Bing News Search URL: ' . $url;

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Ocp-Apim-Subscription-Key: $apiKey",
    ]);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        throw new Exception('Error: ' . curl_error($ch));
    }

    //check if empty response
    if (empty($response)) {
        throw new Exception('Empty response');
    }

    // Close the cURL session
    curl_close($ch);

    // Convert the response to an associative array

    $response = json_decode($response, true);

    //check if error
    if (isset($response['error'])) {
        throw new Exception('Error: ' . $response['error']['message']);
    }

    //check if no results
    if (empty($response['value'])) {
        throw new Exception('No results found for keyword: ' . $keyword);
    }

    //loop and build found links and titles
    $found_links = array();
    $found_titles = array();

    foreach ($response['value'] as $news) {
        $found_links[] = $news['url'];
        $found_titles[] = $news['name'];
    }

    //echo
    echo '<br> Found ' . count($found_links) . ' links for keyword: ' . $keyword;

    //get feed url
    $feed_url = wp_automatic_links_to_feed($found_links, $found_titles, $md5, $campaignID);

    //return feed url
    return $feed_url;

}

/**
 * Converts an array of found links and titles into an RSS feed.
 *
 * This function takes an array of found links and titles, processes them, and generates an RSS feed
 * based on the provided links and titles.
 *
 * @param array $found_links An array of found links.
 * @param array $found_titles An array of found titles.
 * @param string $md5 The MD5 hash of the keyword.
 * @param int $campaignID The ID of the campaign.
 * @return string The generated RSS feed URL.
 */
function wp_automatic_links_to_feed($found_links, $found_titles, $md5, $campaignID)
{

    //site_domain_url from first found link
    $first_link = $found_links[0];

    //parse url
    $parsed_url = parse_url($first_link);

    //get host
    $host = $parsed_url['host'];

    //get http prefix
    $http_prefix = $parsed_url['scheme'] . '://';

    //get base url
    $base_url = $http_prefix . $host;

    //get site domain url
    $site_domain_url = $http_prefix . $host;

    // building feed content
    $i = 0;
    $rss = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>W3Schools Home Page</title>
  <link>' . $site_domain_url . '</link>
  <description>Free web building tutorials</description>';

    foreach ($found_links as $found_link) {

        $found_title = wp_automatic_htmlspecialchars($found_titles[$i]);

        $skipped_link = wp_automatic_htmlspecialchars($found_link);

        if (wp_automatic_trim($found_title) == '') {
            $found_title = $skipped_link;
        }

        $rss .= "
		<item>
		    <title>$found_title</title>
		    <link>$skipped_link</link>
		    <description>$found_title</description>
	    </item>";

        $i++;
    }

    $rss .= '
</channel>
</rss>';

    $upload_dir = wp_upload_dir();
    $fname = $md5 . '_' . $campaignID . '.xml';
    $filePath = $upload_dir['basedir'] . '/' . $fname;
    $fileUrl = $upload_dir['baseurl'] . '/' . $fname;
    file_put_contents($filePath, $rss);

    //return file url
    return $fileUrl;

}
