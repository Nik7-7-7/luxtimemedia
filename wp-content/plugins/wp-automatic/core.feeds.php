<?php

/*
 * ---* feed process camp ---
 */

// Main Class
require_once 'core.php';
class WpAutomaticFeeds extends wp_automatic
{
    public function feeds_get_post($camp)
    {

        // feeds
        $feeds = $camp->feeds;
        $feeds = explode("\n", $feeds);

        $msg = "Processing " . count($feeds) . " Feeds for this campaign " . get_the_title($camp->camp_id);
        echo '<br>' . $msg;

        if (count($feeds) > 0) {
            $this->log('Process Feeds', $msg);
        }

        $n = 0;
        $max = 3;

        foreach ($feeds as $feed) {

            if ($n == 3) {
                $max = (int) wp_automatic_trim(get_option('wp_automatic_feed_max', 3));
            }

            if ($n == $max) {
                echo '<br>Processed ' . $max . ' feeds with nothing to find, will die now, Please activate rotating if you are not.';
                break;
            }

            if (wp_automatic_trim($feed) != '') {


                //if feed contains BingNews: then search Bing News API
                if (stristr($feed, 'BingNews:')) {

                     //replace keyword by local feed URL
                     $feed= wp_automtic_keyword_to_feed_bing_wrap($feed,$camp);
                }

                // fix //feeds
                if (!stristr($feed, 'http') && preg_match('{^//}', wp_automatic_trim($feed))) {
                    $feed = 'http:' . $feed;
                }

                // process feed
                echo '<b><br><br>Processing Feed:</b> ' . $feed;

                update_post_meta($camp->camp_id, 'last_feed', wp_automatic_trim($feed));

                $cont = $this->feed_process_link($feed, $camp);
                $n++;

                if (isset($cont['cont']) && wp_automatic_trim($cont['cont']) != '') {
                    return $cont;
                }
            }
        }

        return false;
    }

    /*
     * ---* processing feed link ---
     */
    public function feed_process_link($feed, $camp)
    {

        //correct google news link from news.google.com/search to news.google.com/rss/search
        if (stristr($feed, 'news.google.com/search')) {
            $feed = str_replace('news.google.com/search', 'news.google.com/rss/search', $feed);
            echo '<br>Google news link corrected to: ' . $feed;
        }

        //news.google.com/topics link correction
        if (stristr($feed, 'news.google.com/topics')) {
            $feed = str_replace('news.google.com/topics', 'news.google.com/rss/topics', $feed);
            echo '<br>Google news link corrected to: ' . $feed;
        }

        // add a random number as parameter rand to the end of the feed if it contains .xml , wp-content/uploads
        if (stristr($feed, '.xml') && stristr($feed, 'wp-content/uploads')) {
            $feed = $feed . '?rand=' . rand(1, 30000);
            echo '<br>Feed URL contains wp-content/uploads and .xml , adding a random number to the end of the URL to avoid caching';
        }

        // detect encoding mbstring
        if (!function_exists('mb_detect_encoding')) {
            echo '<br><span style="color:red;">mbstring PHP extension that is responsible for text encoding is not installed,Install it. You may get encoding problems.</span>';
        }

        // php-xml check
        if (!function_exists('simplexml_load_string')) {
            echo '<br><span style="color:red;">php-xml PHP extension that is responsible for parsing XML is not installed. Please <a href="https://stackoverflow.com/questions/38793676/php-xml-extension-not-installed">install it</a> and try again</span>';
        }

        // ini
        if (stristr($camp->camp_general, 'a:')) {
            $camp->camp_general = base64_encode($camp->camp_general);
        }

        $camp_general = unserialize(base64_decode($camp->camp_general));
        $camp_general = array_map('wp_automatic_stripslashes', $camp_general);
        $camp_opt = unserialize($camp->camp_options);

        // Feed extraction method old format adaption
        if (in_array('OPT_FEED_CUSTOM_R', $camp_opt)) {
            $camp_general['cg_feed_custom_regex'] = array(
                $camp_general['cg_feed_custom_regex'],
                $camp_general['cg_feed_custom_regex2'],
            );
        }

        if (in_array('OPT_FEED_CUSTOM', $camp_opt)) {

            $camp_general['cg_feed_extraction_method'] = 'css';

            $camp_general['cg_custom_selector'] = array(
                $camp_general['cg_custom_selector'],
                $camp_general['cg_custom_selector2'],
                $camp_general['cg_custom_selector3'],
            );
            $camp_general['cg_feed_custom_id'] = array(
                $camp_general['cg_feed_custom_id'],
                $camp_general['cg_feed_custom_id2'],
                $camp_general['cg_feed_custom_id3'],
            );

            $cg_feed_css_size = array();
            $cg_feed_css_size[] = in_array('OPT_SELECTOR_SINGLE', $camp_options) ? 'single' : 'all';
            $cg_feed_css_size[] = in_array('OPT_SELECTOR_SINGLE2', $camp_options) ? 'single' : 'all';
            $cg_feed_css_size[] = in_array('OPT_SELECTOR_SINGLE3', $camp_options) ? 'single' : 'all';
            $camp_general['cg_feed_css_size'] = $cg_feed_css_size;

            $cg_feed_css_wrap = array();
            $cg_feed_css_wrap[] = in_array('OPT_SELECTOR_INNER', $camp_options) ? 'inner' : 'outer';
            $cg_feed_css_wrap[] = in_array('OPT_SELECTOR_INNER2', $camp_options) ? 'inner' : 'outer';
            $cg_feed_css_wrap[] = in_array('OPT_SELECTOR_INNER3', $camp_options) ? 'inner' : 'outer';
            $camp_general['cg_feed_css_wrap'] = $cg_feed_css_wrap;
        }

        if (isset($camp_general['cg_feed_extraction_method'])) {
            switch ($camp_general['cg_feed_extraction_method']) {

                case 'summary':
                    $camp_opt[] = 'OPT_SUMARRY_FEED';
                    break;

                case 'css':
                    $camp_opt[] = 'OPT_FEED_CUSTOM';
                    break;

                case 'auto':
                    $camp_opt[] = 'OPT_FULL_FEED';
                    break;

                case 'regex':
                    $camp_opt[] = 'OPT_FEED_CUSTOM_R';
                    break;

                case 'visual':
                    $camp_opt[] = 'OPT_FEED_CUSTOM';
                    $camp_general['cg_feed_extraction_method'] = 'css';

                    $camp_general['cg_feed_custom_id'] = $camp_general['cg_feed_visual'];

                    $cg_feed_css_size = array();
                    $cg_feed_css_wrap = array();
                    $cg_custom_selector = array();

                    foreach ($camp_general['cg_feed_visual'] as $singleVisual) {
                        $cg_feed_css_size[] = 'single';
                        $cg_feed_css_wrap[] = 'outer';
                        $cg_custom_selector[] = 'xpath';
                    }

                    $camp_general['cg_feed_css_size'] = $cg_feed_css_size;
                    $camp_general['cg_feed_css_wrap'] = $cg_feed_css_wrap;
                    $camp_general['cg_custom_selector'] = $cg_custom_selector;

                    break;
            }
        }

        if (in_array('OPT_STRIP_CSS', $camp_opt) && !is_array($camp_general['cg_feed_custom_strip_id'])) {

            $cg_feed_custom_strip_id[] = $camp_general['cg_feed_custom_strip_id'];
            $cg_feed_custom_strip_id[] = $camp_general['cg_feed_custom_strip_id2'];
            $cg_feed_custom_strip_id[] = $camp_general['cg_feed_custom_strip_id3'];

            $cg_custom_strip_selector[] = $camp_general['cg_custom_strip_selector'];
            $cg_custom_strip_selector[] = $camp_general['cg_custom_strip_selector2'];
            $cg_custom_strip_selector[] = $camp_general['cg_custom_strip_selector3'];

            $cg_feed_custom_strip_id = array_filter($cg_feed_custom_strip_id);
            $cg_custom_strip_selector = array_filter($cg_custom_strip_selector);

            $camp_general['cg_feed_custom_strip_id'] = $cg_feed_custom_strip_id;
            $camp_general['cg_custom_strip_selector'] = $cg_custom_strip_selector;
        }

        $feedMd5 = md5($feed);
        $isItemsEndReached = get_post_meta($camp->camp_id, $feedMd5 . '_isItemsEndReached', 1);
        $lastProcessedFeedUrl = get_post_meta($camp->camp_id, $feedMd5 . '_lastProcessedFeedUrl', 1);
        $lastFirstFeedUrl = get_post_meta($camp->camp_id, $feedMd5 . '_lastFirstFeedUrl', 1);

        // check last time adition
        $feed = wp_automatic_trim($feed);
        $myfeed = addslashes($feed);

        // removed @ v3.24
        /*
         * $query = "select * from {$this->wp_prefix}automatic_feeds_list where feed='$myfeed' and camp_id = '$camp->camp_id' limit 1";
         * $feeds = $this->db->get_results ( $query );
         * $feed_o = $feeds [0];
         */

        // report processed feed
        $this->log('Process Feed', '<a href="' . $feed . '">' . $feed . '</a>');

        // If force feed

        if (in_array('OPT_FEED_FORCE', $camp_opt)) {

            if (!function_exists('wp_automatic_force_feed')) {
                add_action('wp_feed_options', 'wp_automatic_force_feed', 10, 1);
                function wp_automatic_force_feed($feed)
                {
                    $feed->force_feed(true);

                }
            }
        }

        //disable order by date for multi-page scraper
        if (isset($camp->camp_sub_type) && $camp->camp_sub_type == 'Multi') {
            add_action('wp_feed_options', 'wp_automatic_order_feed', 10, 1);

            if (!function_exists('wp_automatic_order_feed')) {

                function wp_automatic_order_feed($feed)
                {
                    $feed->enable_order_by_date(false);
                }

            }

        }

        // loading SimplePie
        include_once ABSPATH . WPINC . '/feed.php';

        // Add action to fix the problem of curl transfer closed without complete data
        // Wrong feed length fix
        if (!function_exists('wp_automatic_setup_curl_options')) {
            // feed timeout
            function wp_automatic_setup_curl_options($curl)
            {
                if (is_resource($curl)) {
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                        'Expect:',
                    ));
                }
            }
        }

        if (in_array('OPT_FEED_NOLENGTH', $camp_opt)) {
            echo ' expecting none';
            add_action('http_api_curl', 'wp_automatic_setup_curl_options');
        }

        if (!function_exists('wp_automatic_wp_feed_options')) {
            function wp_automatic_wp_feed_options($args)
            {
                $args->set_useragent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 ');
            }
            add_action('wp_feed_options', 'wp_automatic_wp_feed_options');
        }

        // Trim returned feed content because some feed add empty spaces before feed content
        if (!function_exists('wp_automatic_trim_feed_content')) {
            function wp_automatic_trim_feed_content($args)
            {
                $args['body'] = wp_automatic_trim($args['body']);

                // $args['body'] = preg_replace('{article/(\d+?)/}' , "article/$1wpp/", wp_automatic_trim($args['body']) ) ;

                return $args;
            }
        }

        add_filter('http_response', 'wp_automatic_trim_feed_content');

        if (stristr($feed, 'news.google') && !function_exists('wp_automatic_feed_options')) {

            // Fix Google news image stripped
            echo '<br>Google news feed found, disabling sanitization...';
            function wp_automatic_feed_options($feed)
            {
                $feed->set_sanitize_class('SimplePie_Sanitize');
                $feed->sanitize = new SimplePie_Sanitize();
            }
            add_action('wp_feed_options', 'wp_automatic_feed_options');
        }

        // If proxified download the feed content to a test file for
        // exclude multipage scraper as it is already a local feed /wp-content/uploads/277d7822fe5662e66d660a42eaae4910_837.xml
        $localFeed = '';

        if ($this->isProxified && !(stristr($feed, '.xml') && stristr($feed, 'wp-content'))) {
            // downloading the feed content
            // print_r( $this->download_file
            $downloadedFileUrl = $this->download_file($feed, '.html');

            echo '<br>Using a proxy, trying to download the feed to a local file...';

            if (wp_automatic_trim($downloadedFileUrl) != '') {
                echo '<br>Feed downloaded using a proxy ' . $downloadedFileUrl;
                $localFeed = $downloadedFileUrl . '?key=' . md5(wp_automatic_trim($feed));
            } else {
                echo '<br>Feed download failed, using the direct URL instead';
            }
        }

        // fix ssl verification
        add_filter('https_ssl_verify', '__return_false');

        // Fetch feed content
        if (wp_automatic_trim($localFeed) == '') {

            timer_start();
            $rss = fetch_feed(stripslashes($feed));
            echo '<br>Time taken to load the feed from the source:' . timer_stop();

        } else {
            echo '<br>Loaded locally';
            $rss = fetch_feed($localFeed);

            //log that the feed was loaded locally
            wp_automatic_log_new('Feed', 'Feed loaded locally');
        }

        //if $rss->status_code is set, echo
        if (isset($rss->status_code)) {
            echo '<br>HTTP code: ' . $rss->status_code;
        }

        $is_error = is_wp_error($rss);

        //if rss is a wordpress error and the feed URL contains wp-content/uploads and .xml then build then get the content of the local file from the uploads folder and build the rss object manually
        //on 19/09/24 added wp_automatic_temp.html to the condition to also fix the problem when using proxies and the feed was downloaded
        $is_multi_page_error = stristr($feed, 'wp-content/uploads') &&  stristr($feed, '.xml');
        $is_local_feed_error = stristr($localFeed,'wp_automatic_temp.html');
        
        
        if ( is_wp_error($rss)  && ($is_multi_page_error || $is_local_feed_error)  ){

            //report loading file by URL failed, tring to build the rss object manually
            echo '<br>Failed to load the feed by URL, tring to build the rss object manually';

            //get the local file content from the uploads folder not using the URL

            //1 get the file name
            $feed_file_name = basename($feed);

            //if localFeed is set then use it
            if(stristr($localFeed,'wp_automatic_temp.html')){
                $feed_file_name = basename($localFeed);
            }

            //remove any parameters from the file name
            $feed_file_name = explode('?', $feed_file_name);
            $feed_file_name = $feed_file_name[0];

            //2 get the uploads folder path
            $upload_dir = wp_upload_dir();

            //3 get the file path
            $local_file_path = $upload_dir['basedir'] . '/' . $feed_file_name;

            //4 get the file content
            $local_file_content = file_get_contents($local_file_path);

            //if file content contains <rss then it is a valid rss file
            if (stristr($local_file_content, '<rss')) {
                echo '<br>File content contains rss then it is a valid rss file, building the feed object manually';

                //build the rss object manually
                $rss = new SimplePie();

                // Set the feed content
                $rss->set_raw_data($local_file_content);

                // Initialize the feed
                $rss->init();

            } else {
                echo '<br>File content does not contain rss then it is not a valid rss file, deleting...';

                //delete the file
                unlink($local_file_path);

            }

        }

        // Remove added filter
        remove_filter('http_response', 'wp_automatic_trim_feed_content');

        if (!is_wp_error($rss)) { // Checks that the object is created correctly

            if (wp_automatic_trim($rss->raw_data) == '') {
                echo '<br>Feed was loaded from cache';
            } else {
                echo '<br>Feed was freshly loaded from the source';

                //report final feed URL
                echo '<br>Final feed URL: ' . $rss->feed_url;

            }

            // Figure out how many total items there are, but limit it to 5.
            $maxitems = @$rss->get_item_quantity();

            //fix 404 code may return site posts as some sites display a feed on 404 pages ticket:23627
            //so if the code is 404 and there is posts, set the maxitems to 0
            //also delete the cached file URL to force a fresh load next time wp_automatic_cache meta
            if ($rss->status_code == 404 && $maxitems > 0 && stristr($feed, 'wp-content/uploads')) {
                echo '<br>Feed status code is 404 and contains posts, setting maxitems to 0 and deleting the cached file URL to force a fresh load next time';

                //delete the cached file URL to force a fresh load next time
                delete_post_meta($camp->camp_id, 'wp_automatic_cache');

                $maxitems = 0;
            }

            echo '<br>Feed contains ' . $maxitems . ' posts';

            //log the feed load time
            wp_automatic_log_new('Feed', 'Feed load time:' . timer_stop() . ' and contains (' . $maxitems . ') posts');

            // Build an array of all the items, starting with element 0 (first element).
            $rss_items = $rss->get_items(0, $maxitems);

            // feed name
            $res['feed_name'] = $rss->get_title();

            // remove the expect again as it makes jetpack publicize to not work
            remove_action('http_api_curl', 'wp_automatic_setup_curl_options');
        } else {
            $error_string = $rss->get_error_message();

            //check if error is `403` and report that this page maybe protected by cloudflare and user should check the tutorial on how to import from this site
            if (stristr($error_string, '403')) {

                //error message inside a span with color red
                $error_string = '<br><span style="color:red">403 error: This page maybe protected by cloudflare, Check <a href="https://valvepress.com/how-to-import-from-rss-feeds-protected-with-cloudflare/" target="blank"> THIS TUTURIAL</a> on how to import from this site</span>';

                //if local feed i.e the url contains wp-content/uploads and .xml then overwite error message to 403 error, your server is not able to load links exists on your domain, please refer to your hosting support and ask them to correct this issue, ask them why this feed URL [include URL] is not loading from the server
                if (stristr($feed, 'wp-content/uploads') && stristr($feed, '.xml')) {
                    $error_string = '<br><span style="color:red">403 error: Your server is not able to load links exists on your domain, please refer to your hosting support and ask them to correct this issue, ask them why this URL ' . $feed . ' is not loading when the server load it while works normally on the browser.</span>';
                }

                echo $error_string;

            } else {

                echo '<br><strong>Error:</strong>' . $error_string;

            }

        }

        if (!isset($maxitems) || $maxitems == 0) {
            return false;
        } else

        // reverse order if exists
        if (in_array('OPT_FEED_REVERSE', $camp_opt)) {
            echo '<br>Reversing order';
            $rss_items = array_reverse($rss_items);
        }

        // Loop through each feed item and display each item as a hyperlink.
        $i = 0;
        $i2 = 0; // refer to items number in feed

        foreach ($rss_items as $item):

            $url = esc_url($item->get_permalink());

            if (wp_automatic_trim($url) == '') {
                echo '<br>Feed item does not contain a URL, what a bad feed, will use a fake URL';
                $url = md5($item->get_title());
            }

            $original_url = $url; // used for exclusion because the effective_url may change below

            // google news links correction
            if (stristr($url, 'news.google') && stristr($url, 'url=')) {
                $urlParts = explode('url=', $url);
                $correctUrl = $urlParts[1];
                $url = $correctUrl;
            } elseif (stristr($url, 'news.google') && stristr($url, '/articles/')) {
            echo '<br>Google news link found (' . $url . '), Finding original link now...<br>';

            //api call to get the real link
            try {

                //title
                $title = $item->get_title();

                //echo
                echo '<br>- Title: ' . $title;

                //get domain from the source tag <source url="https://www.campaignindia.in">Campaign India</source>
                $source = $item->data['child']['']['source'][0]['attribs']['']['url'];

                echo '<br>- Source: ' . $source;

                $new_link = $this->get_google_news_link($url);

                $url = $new_link;

                echo ' Correct link: ' . $url;

            } catch (Exception $e) {
                //report error with color red
                echo '<br><span style="color:red">Error: ' . $e->getMessage() . '</span>';

                //if exception contains sorry, return
                if (stristr($e->getMessage(), 'captcha')) {
                    return false;
                }

            }

        }

        // Google alerts links correction
        if (stristr($feed, 'alerts/feeds') && stristr($feed, 'google')) {
            preg_match('{url\=(.*?)[&]}', $url, $urlMatches);
            $correctUrl = $urlMatches[1];

            if (wp_automatic_trim($correctUrl) != '') {
                $url = $correctUrl;
            }
        }

        // check if no new links: the last first url is the same
        if ($i2 == 0) {

            if ($isItemsEndReached == 'yes') {

                // Last time there were no new links check if the case didn't change
                if ($lastFirstFeedUrl == md5($url)) {

                    // delete transient cache
                    delete_transient('feed_' . $feedMd5);

                    if (!in_array('OPT_LINK_NOCACHE', $camp_opt)) {

                        // still no new links stop checking now
                        echo '<br>First url in the feed:' . $url;

                        //generate nonce wp_automatic_ajax
                        $nonce = wp_create_nonce('wp_automatic_ajax');

                        echo '<br>This link was the same as the last time we did not find new links so ... skipping till new posts get added <a href="#" class="wp_automatic_ajax" data-action="wp_automatic_ajax" data-nonce="' . $nonce . '" data-function="forget_lastFirstFeedUrl" data-data="' . $feedMd5 . '" data-camp="' . $camp->camp_id . '" >Forget this fact Now</a>.';

                        return false;
                    }

                } else {
                    // new links found remove the isItemsEndReached flag
                    delete_post_meta($camp->camp_id, $feedMd5 . '_isItemsEndReached');
                }
            }
        }

        // Record first feed url
        if ($i2 == 0) {
            update_post_meta($camp->camp_id, $feedMd5 . '_lastFirstFeedUrl', md5($url));
        }

        // one more link, increase index2
        $i2++;

        if (wp_automatic_trim($url) == '') {
            echo '<br>item have no url skipping';
            continue;
        }

        // current post url
        echo '<br>post URL: ' . $url;

        // post date
        $wpdate = ''; //initialize
        $publish_date = '';

        $wpdate = $item->get_date();
        

        if (wp_automatic_trim($wpdate) != '') {
            echo '<br>- Published: ' . $wpdate;
        }

        if (wp_automatic_trim($wpdate) != '') {

            //IST fix timesofindia
            $wpdate = wp_automatic_str_replace(' IST', ' +05:30', $wpdate);

            $wpdate = date("Y-m-d H:i:s", strtotime($wpdate));
            echo ' == ' . $wpdate . ' GMT';

            $publish_date = get_date_from_gmt($wpdate);

        }

        

        // post categories
        $cats = ($item->get_categories());

        // separate categories with commas
        $cat_str = '';
        if (isset($cats)) {
            foreach ($cats as $cat) {
                if (wp_automatic_trim($cat_str) != '') {
                    $cat_str .= ',';
                }

                $cat_str .= $cat->term;
            }
        }

        // fix empty titles
        if (wp_automatic_trim($item->get_title()) == '') {

            //if not OPT_FEED_EMPTY_TITLE skip
            if (!in_array('OPT_FEED_EMPTY_TITLE', $camp_opt)) {
                echo '<--Empty title skipping';
                continue;
            } else {
                echo '<--Empty title';
            }

        }

        // &# encoded chars
        if (stristr($url, '&#')) {
            $url = html_entity_decode($url);
        }

        // check if execluded link due to exact match does not exists
        if ($this->is_execluded($camp->camp_id, $url)) {
            echo '<-- Excluded link';
            continue;
        }

        // check if older than minimum date
        if ($wpdate != '' && $this->is_link_old($camp->camp_id, strtotime($wpdate))) {
            echo '<--old post execluding...';
            continue;
        }

        // check media images
        unset($media_image_url);
        $enclosures = $item->get_enclosures();

        $i = 0;

        $enclosure_link = ''; // reset enclosure link

        foreach ($enclosures as $enclosure) {

            if (wp_automatic_trim($enclosure->type) != '') {

                $enclosure_link = $enclosure->link;

                $res['enclosure_link_' . $i] = $enclosure_link;

                if (isset($enclosure->type) && stristr($enclosure->type, 'image') && isset($enclosure->link)) {
                    $media_image_url = $enclosure->link;
                }
            }
            $i++;
        }

        // Duplicate check
        if (!$this->is_duplicate($url)) {

            echo '<-- new link';

            $title = strip_tags($item->get_title());

            //if title is empty and option OPT_FEED_EMPTY_TITLE is enabled, set the title to (notitle)
            if (wp_automatic_trim($title) == '' && in_array('OPT_FEED_EMPTY_TITLE', $camp_opt)) {
                $title = '(notitle)';
            }

            // fix &apos;
            $title = wp_automatic_str_replace('&amp;apos;', "'", $title);
            $title = wp_automatic_str_replace('&apos;', "'", $title);

            // check if there is a post published with the same title
            if (in_array('OPT_FEED_TITLE_SKIP', $camp_opt)) {
                if ($this->is_title_duplicate($title, $camp->camp_post_type)) {
                    echo '<-- duplicate title skipping..';
                    continue;
                }
            }

            $i++;

            // post content
            $html = $item->get_content();

            // fix single quotes
            $html = $this->convert_single_quotes($html);

            $postAuthor = $item->get_author();
            $authorName = isset($postAuthor->name) ? $postAuthor->name : '';

            if (wp_automatic_trim($authorName) == '') {
                $authorName = isset($postAuthor->email) ? $postAuthor->email : '';
            }

            if (wp_automatic_trim($authorName) == '') {
                @$authorName = isset($postAuthor->link) ? $postAuthor->link : '';
            }

            $res['author'] = $authorName;
            $res['author_link'] = isset($postAuthor->link) ? $postAuthor->link : '';

            // source domain
            $res['source_domain'] = $parse_url = parse_url($url, PHP_URL_HOST);

            // encoded URL
            $res['source_url_encoded'] = urlencode($url);

            // decode URL
            $res['source_url_decoded'] = urldecode($url);

            // If empty content make content = title
            if (wp_automatic_trim($html) == '') {
                if (wp_automatic_trim($title) != '') {
                    $html = $title;
                }

            }

            // loging the feeds
            $md5 = md5($url);

            // if not image escape it
            $res['cont'] = $html;
            $res['original_content'] = $html;
            $res['title'] = $title;
            $res['original_title'] = $title;
            $res['matched_content'] = $html;
            $res['source_link'] = $url;
            $res['publish_date'] = $publish_date;

            // if feed variable does not contain getpocket add set the date
            if (!stristr($feed, 'getpocket')) {
                $res['wpdate'] = $wpdate;
            }

            $res['cats'] = $cat_str;
            $res['tags'] = '';
            $res['enclosure_link'] = isset($enclosure_link) ? $enclosure_link : '';

            // custom atributes
            $arr = array();
            $arrValues = array_values($item->data['child']);

            foreach ($arrValues as $arrValue) {
                if (is_array($arrValue)) {
                    $arr = array_merge($arr, $arrValue);
                }
            }

            $res['attributes'] = $arr;

            $og_title_enabled = in_array('OPT_FEED_OG_TTL', $camp_opt) || (isset($camp_general['cg_ml_ttl_method']) && wp_automatic_trim($camp_general['cg_ml_ttl_method']) != 'auto');

            // original meta description, add the regex extraction rule and activate the specific part to custom field method if not enabled
            if (in_array('OPT_ORIGINAL_META_DESC', $camp_opt)) {

                //add specific part to custom field extraction if not enabled
                if (!in_array('OPT_FEED_PTF', $camp_opt)) {
                    $camp_opt[] = 'OPT_FEED_PTF';

                    if (!isset($camp_general['cg_part_to_field'])) {
                        $camp_general['cg_part_to_field'] = '';
                    }

                }

                $seo_field_name = class_exists('WPSEO_Options') ? '_yoast_wpseo_metadesc' : 'rank_math_description';

                $camp_general['cg_part_to_field'] .= "\n" . 'regex|<meta name="description" content="(.*?)"|' . $seo_field_name;

            }

            // original post content
            if (in_array('OPT_FULL_FEED', $camp_opt) || in_array('OPT_FEED_CUSTOM', $camp_opt) || in_array('OPT_FEED_CUSTOM_R', $camp_opt) || in_array('OPT_ORIGINAL_META', $camp_opt) || in_array('OPT_ORIGINAL_CATS', $camp_opt) || in_array('OPT_ORIGINAL_TAGS', $camp_opt) || in_array('OPT_ORIGINAL_AUTHOR', $camp_opt) || in_array('OPT_FEED_PTF', $camp_opt) || in_array('OPT_FEEDS_OG_IMG', $camp_opt) || $og_title_enabled) {

                echo '<br>Loading original post content ...';

                // get content
                $x = 'error';
                curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim(html_entity_decode($url)));

                // encoding
                if (in_array('OPT_FEED_ENCODING', $camp_opt)) {
                    echo '<br>Clearing encoding..';
                    curl_setopt($this->ch, CURLOPT_ENCODING, "");
                }

                // cookie
                $cg_sn_cookie = $camp_general['cg_ml_cookie'];

                if (wp_automatic_trim($cg_sn_cookie) != '') {
                    $headers[] = "Cookie: $cg_sn_cookie ";
                    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
                }

                $first_page_index = 1;
                $last_max_page_index = 10;

                //loop from first page to last page and increment the page index by 1 each time
                $all_pages_original_content = '';
                for ($i = $first_page_index + 1; $i <= $last_max_page_index; $i++) {

                    // Source page html
                    timer_start();

                    if (in_array('OPT_FEED_APIFY', $camp_opt)) {

                        echo '<br>Loading the content using APIFY.COM service....';

                        //get the apify key
                        $wp_automatic_apify_key = wp_automatic_single_item('wp_automatic_apify_key');

                        require_once 'inc/class.apify.php';
                        $apify = new ValvePress_APIFY($wp_automatic_apify_key, html_entity_decode($url), $this->ch);

                        try {

                            // cg_apify_wait_for_single
                            $cg_apify_wait_for_single = isset($camp_general['cg_apify_wait_for_single']) ? $camp_general['cg_apify_wait_for_single'] : 0;

                            //report
                            echo '<br>Waiting for ' . $cg_apify_wait_for_single . ' mill seconds after loading the content...';

                            $apify_content = $apify->apify($cg_apify_wait_for_single, $cg_sn_cookie);
                            $original_cont = $apify_content;

                        } catch (Exception $e) {
                            echo '<br>Error:' . $e->getMessage() . ' ....loading the post content directly';
                            $original_cont = $this->curl_exec_follow($this->ch);
                        }

                    } elseif (in_array('OPT_FEED_NORED', $camp_opt)) {
                        $original_cont = curl_exec($this->ch);
                    } else {
                        $original_cont = $this->curl_exec_follow($this->ch);
                    }

                    $x = curl_error($this->ch);

                    echo ' <--' . strlen($original_cont) . ' chars returned in ' . timer_stop() . ' seconds';

                    //append the returned content to all_pages_original_content
                    $all_pages_original_content .= $original_cont;

                    //if option OPT_FEED_MULTI_PAGE is enabled, check if the content contains the next part of page URL
                    if (in_array('OPT_FEED_MULTI_PAGE', $camp_opt)) {

                        //get the next page URL
                        $cg_multi_paged_link = $camp_general['cg_multi_paged_link'];

                        //if empty, set to default which is [current_page]/[page_number]
                        if (wp_automatic_trim($cg_multi_paged_link) == '') {
                            $cg_multi_paged_link = '[current_page]/[page_number]';
                        }

                        //remove the last slash from the URL
                        $url = rtrim($url, '/');

                        //replace [current_page] with the current url
                        $cg_multi_paged_link = wp_automatic_str_replace('[current_page]', $url, $cg_multi_paged_link);

                        //replace [page_number] with the current page number
                        $cg_feed_next_page = wp_automatic_str_replace('[page_number]', $i, $cg_multi_paged_link);

                        //if next page URL is not empty
                        if (wp_automatic_trim($cg_feed_next_page) != '') {

                            //if the content contains the next page URL
                            if (stristr($original_cont, $cg_feed_next_page)) {

                                //report the next page URL found
                                echo '<br>Next page URL found: ' . $cg_feed_next_page;

                                //set the curl URL to the next page URL
                                curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim(html_entity_decode($cg_feed_next_page)));

                            } else {
                                //report next page URL is not found and stop the loop
                                echo '<br>Next page URL is not found: ' . $cg_feed_next_page;

                                break;
                            }

                        } else {
                            //report next page URL is not found and stop the loop
                            echo '<br>Next page URL is not found: ' . $cg_feed_next_page;

                            break;
                        }

                    } else {
                        //stop the loop
                        break;
                    }

                }

                // copy of the original content for later use that is unmodified
                $original_cont_raw = $original_cont = $all_pages_original_content;

                // converting encoding
                if (in_array('OPT_FEED_CONVERT_ENC', $camp_opt)) {
                    echo '<br>Converting encoding from ' . $camp_general['cg_feed_encoding'] . ' to utf-8';
                    $original_cont = iconv(wp_automatic_trim($camp_general['cg_feed_encoding']) . '//IGNORE', "UTF-8//IGNORE", $original_cont);
                }

                // fix single quote used instead of regular quote
                $original_cont = $this->convert_single_quotes($original_cont);

                // remove pargrphs containing a specific word, if enabled, add OPT_STRIP_VISUAL to camp_opt and add words to cg_feed_visual_strip
                if (in_array('OPT_STRIP_BY_WORD', $camp_opt) && !empty($camp_general['cg_post_strip_by_words'])) {

                    // add OPT_STRIP_VISUAL to camp_opt
                    $camp_opt[] = 'OPT_STRIP_VISUAL';

                    // if cg_feed_visual_strip is not an array, set it to an empty array
                    if (!is_array($camp_general['cg_feed_visual_strip'])) {
                        $camp_general['cg_feed_visual_strip'] = array();
                    }

                    // split cg_post_strip_by_words by new line, filter empty valaues and add xpaths to cg_feed_visual_strip
                    $cg_post_strip_by_words = array_filter(explode("\n", $camp_general['cg_post_strip_by_words']));

                    // xpath example //p[contains(., "payroll")]
                    foreach ($cg_post_strip_by_words as $cg_post_strip_by_word) {
                        $camp_general['cg_feed_visual_strip'][] = '//p[contains(., "' . wp_automatic_trim($cg_post_strip_by_word) . '")]';
                        $camp_general['cg_feed_visual_strip'][] = '//p/span[contains(., "' . wp_automatic_trim($cg_post_strip_by_word) . '")]';
                        $camp_general['cg_feed_visual_strip'][] = '//p/a[contains(., "' . wp_automatic_trim($cg_post_strip_by_word) . '")]';
                    }

                }

                // strip parts using xpaths stored on $camp_general['cg_feed_visual_strip']
                if (in_array('OPT_STRIP_VISUAL', $camp_opt)) {

                    //require class.dom.php
                    require_once 'inc/class.dom.php';
                    $wpAutomaticDom = new wpAutomaticDom($original_cont);

                    $cg_feed_visual_strip = array_filter($camp_general['cg_feed_visual_strip']);

                    //if is array and not empty
                    if (is_array($cg_feed_visual_strip) && !empty($cg_feed_visual_strip)) {
                        $newContent = $wpAutomaticDom->removeElementsByXpath($cg_feed_visual_strip);

                        //if new content is not empty then replace the original content
                        if (!empty($newContent)) {
                            $original_cont = $newContent;
                        }

                    }

                }

                // fix lazy loading
                if (in_array('OPT_FEED_LAZY', $camp_opt)) {

                    $cg_feed_lazy = wp_automatic_trim($camp_general['cg_feed_lazy']);

                    if ($cg_feed_lazy == '') {
                        $cg_feed_lazy = 'data-src';
                    }

                    preg_match_all('{<img .*?>}s', $original_cont, $imgsMatchs);

                    $imgsMatchs = $imgsMatchs[0];

                    foreach ($imgsMatchs as $imgMatch) {

                        if (stristr($imgMatch, $cg_feed_lazy)) {

                            $newImg = $imgMatch;
                            $newImg = wp_automatic_str_replace(' src=', ' bad-src=', $newImg);
                            $newImg = preg_replace('{ bad-src=[\'|"].*?["|\'] }s', ' ', $newImg);
                            $newImg = wp_automatic_str_replace($cg_feed_lazy, 'src', $newImg);
                            $original_cont = wp_automatic_str_replace($imgMatch, $newImg, $original_cont);
                        }
                    }
                } else {
                    $original_cont = $this->lazy_loading_auto_fix($original_cont);
                }

                //if option OPT_FEED_LAZY_NOSCRIPT_DISABLE is not enabled
                if (!in_array('OPT_FEED_LAZY_NOSCRIPT_DISABLE', $camp_opt)) {
                    // fix <noscript><img lazy loading
                    $original_cont = $this->fix_noscript_lazy_loading($original_cont);
                }

                // fix images
                $original_cont = wp_automatic_fix_relative_paths($original_cont, $url);

            } // end-content-extraction

            // date if not existing
            if ((in_array('OPT_ORIGINAL_TIME', $camp_opt) || in_array('OPT_YT_DATE', $camp_opt)) && !isset($res['wpdate'])) {

                $found_date = ''; // ini

                echo '<br>Finding original date... ';

                $cg_original_time_regex = isset($camp_general['cg_original_time_regex']) ? wp_automatic_trim($camp_general['cg_original_time_regex']) : '';

                // if regex is empty use default
                if ($cg_original_time_regex == '') {
                    $cg_original_time_regex = '20\d{2}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d*)?.\d{2}:\d{2}';
                }

                preg_match("!{$cg_original_time_regex}!s", $original_cont, $date_matches);

                //timestamp instead  data-time="1649495312">A
                if (isset($date_matches[0]) && wp_automatic_trim($date_matches[0]) != '') {
                    // nice found
                    $found_date = $date_matches[0];
                    echo ' match found:' . $found_date;
                } else {
                    //not found lets find timestamp
                    preg_match('!"(1\d{9})"!', $original_cont, $timestamp_matches);

                    if (isset($timestamp_matches[1]) && wp_automatic_trim($timestamp_matches[1]) != '') {
                        echo ' Found possible timestamp:' . $timestamp_matches[1];

                        $possible_date = date("Y-m-d H:i:s", $timestamp_matches[1]);
                        if (preg_match('!20\d{2}-!', $possible_date)) {
                            echo ' approving:' . $possible_date;
                            $found_date = $possible_date;
                        }
                    } else {

                        //extract using format 2023-03-25 08:57:07
                        //2024-09-11T13:30:00
                        preg_match('!20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}!', $original_cont, $date_matches);

                        if (isset($date_matches[0]) && wp_automatic_trim($date_matches[0]) != '') {
                            echo ' Found possible date:' . $date_matches[0];
                            $found_date = $date_matches[0];
                        }else{

                            ////2024-09-11T13:30:00
                            preg_match('!20\d{2}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}!', $original_cont, $date_matches);

                            if (isset($date_matches[0]) && wp_automatic_trim($date_matches[0]) != '') {
                                echo ' Found possible date:' . $date_matches[0];
                                $found_date = $date_matches[0];
                            }


                        }

                        


                    }

                }

                if ($found_date != '') {
                    echo '<-- Found:' . $found_date;

                    $res['wpdate'] = $wpdate = date("Y-m-d H:i:s", strtotime($found_date));

                    // gmt date
                    $publish_date = get_date_from_gmt($res['wpdate']);
                    $res['publish_date'] = $publish_date;

                    // check if older than minimum date
                    if ($wpdate != '' && $this->is_link_old($camp->camp_id, strtotime($wpdate))) {
                        echo '<--old post execluding...';
                        $this->link_execlude($camp->camp_id, $original_url);
                        continue;
                    }

                } else {
                    echo '<- can not find';
                }
            }

            // custom config for web-stories support. if the post type is web-story and the opt_full_feed is enabled then we need to remove it from camp_opt and add OPT_FEED_CUSTOM
            if (in_array('OPT_FULL_FEED', $camp_opt) && $camp->camp_post_type == 'web-story' && strpos($original_cont, '<amp-story') !== false) {

                echo '<br>Web story detected...Extracing whole html';

                // remove OPT_FULL_FEED
                $camp_opt = array_diff($camp_opt, array(
                    'OPT_FULL_FEED',
                ));

                // add OPT_FEED_CUSTOM
                $camp_opt[] = 'OPT_FEED_CUSTOM';

                // add custom config to simulate visual selector method and picking whole html
                $camp_general['cg_feed_extraction_method'] = 'css';

                $camp_general['cg_feed_custom_id'] = array('/html');
                $cg_feed_css_size = array();
                $cg_feed_css_wrap = array();
                $cg_custom_selector = array();

                foreach ($camp_general['cg_feed_visual'] as $singleVisual) {
                    $cg_feed_css_size[] = 'single';
                    $cg_feed_css_wrap[] = 'outer';
                    $cg_custom_selector[] = 'xpath';
                }

                $camp_general['cg_feed_css_size'] = $cg_feed_css_size;
                $camp_general['cg_feed_css_wrap'] = $cg_feed_css_wrap;
                $camp_general['cg_custom_selector'] = $cg_custom_selector;

            }

            // FULL CONTENT
            if (in_array('OPT_FULL_FEED', $camp_opt)) {

                // reset fullContentSuccess flag to true
                $this->fullContentSuccess = true;

                // test url
                // $url ="http://news.jarm.com/view/75431";

                //copy of the original content to restore after extraction as the auto-detct method will modify and remove scripts
                $original_cont_snapshot = $original_cont;

                // get scripts
                $postponedScripts = array();
                preg_match_all('{<script.*?</script>}s', $original_cont, $scriptMatchs);
                $scriptMatchs = $scriptMatchs[0];

                foreach ($scriptMatchs as $singleScript) {
                    if (stristr($singleScript, 'connect.facebook')) {
                        $postponedScripts[] = $singleScript;
                    }

                    $original_cont = wp_automatic_str_replace($singleScript, '', $original_cont);
                }

                $x = curl_error($this->ch);
                $url = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

                // Redability instantiate
                require_once 'inc/wp_automatic_readability/wp_automatic_Readability.php';
                $wp_automatic_Readability = new wp_automatic_Readability($original_cont, $url);

                //restore the original content snapshot
                $original_cont = $original_cont_snapshot;

                //destroy the snapshot
                unset($original_cont_snapshot);

                $wp_automatic_Readability->debug = false;
                $result = $wp_automatic_Readability->init();

                if ($result) {

                    // Redability title
                    $title = $wp_automatic_Readability->getTitle()->textContent;

                    // Redability Content
                    $content = $wp_automatic_Readability->getContent()->innerHTML;

                    // twitter embed fix
                    if (stristr($content, 'twitter.com') && !stristr($content, 'platform.twitter')) {
                        $content .= '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
                    }

                    // Remove wp_automatic_Readability attributes
                    $content = preg_replace('{ wp_automatic_Readability\=".*?"}s', '', $content);

                    // Fix iframe if exists
                    preg_match_all('{<iframe[^<]*/>}s', $content, $ifrMatches);
                    $iframesFound = $ifrMatches[0];

                    foreach ($iframesFound as $iframeFound) {

                        $correctIframe = wp_automatic_str_replace('/>', '></iframe>', $iframeFound);
                        $content = wp_automatic_str_replace($iframeFound, $correctIframe, $content);
                    }

                    // add postponed scripts
                    if (count($postponedScripts) > 0) {
                        $content .= implode('', $postponedScripts);
                    }

                    // Cleaning redability for better memory
                    unset($wp_automatic_Readability);
                    unset($result);

                    // Check existence of title words in the content
                    $title_arr = explode(' ', $title);

                    $valid = '';
                    $nocompare = array(
                        'is',
                        'Is',
                        'the',
                        'The',
                        'this',
                        'This',
                        'and',
                        'And',
                        'or',
                        'Or',
                        'in',
                        'In',
                        'if',
                        'IF',
                        'a',
                        'A',
                        '|',
                        '-',
                    );
                    foreach ($title_arr as $title_word) {

                        if (strlen($title_word) > 3) {

                            if (!in_array($title_word, $nocompare) && preg_match('/\b' . preg_quote(wp_automatic_trim($title_word), '/') . '\b/ui', $content)) {
                                echo '<br>Title word ' . $title_word . ' exists on the content, approving.';

                                // echo $content;
                                $valid = 'yeah';
                                break;
                            } else {
                                // echo '<br>Word '.$title_word .' does not exists';
                            }
                        }
                    }

                    if (wp_automatic_trim($valid) != '') {

                        $res['cont'] = $content;
                        $res['matched_content'] = $content;
                        $res['og_img'] = '';

                        if (!in_array('OPT_FEED_TITLE_NO', $camp_opt)) {
                            $res['title'] = $title;
                            $res['original_title'] = $title;
                        }

                        // let's find og:image may be the content we got has no image
                        // rumble format <meta property=og:image content=https://sp.rmbl.ws/s8/1/c/_/c/y/c_cyk.qR4e-small--DEATH-DELETE-ANOTHER-DAY-O.jpg>
                        preg_match('{<meta[^<]*?property=["|\']og:image["|\'][^<]*?>}s', $html, $plain_og_matches);

                        if (isset($plain_og_matches[0]) && @stristr($plain_og_matches[0], 'og:image')) {
                            preg_match('{content=["|\'](.*?)["|\']}s', $plain_og_matches[0], $matches);
                            $og_img = $matches[1];

                            if (wp_automatic_trim($og_img) != '') {

                                $res['og_img'] = $og_img;
                            }
                        } // If og:image
                    } else {
                        echo '<br>Can not make sure if the returned content is the full content, using excerpt instead.';
                    }
                } else {
                    echo '<br>Looks like we couldn\'t find the full content. :( returning summary';

                    //set the fullContentSuccess flag to false, this flag is to set the post as pending if posting from a specific list of urls and auto detect method is enabled
                    $this->fullContentSuccess = false;

                }

                // Class or ID extraction
            } elseif (in_array('OPT_FEED_CUSTOM', $camp_opt)) {

                // Load dom
                require_once 'inc/class.dom.php';
                $wpAutomaticDom = new wpAutomaticDom($original_cont);

                $cg_custom_selector = $camp_general['cg_custom_selector'];
                $cg_feed_custom_id = $camp_general['cg_feed_custom_id'];
                $cg_feed_custom_id = array_filter($cg_feed_custom_id);
                $cg_feed_css_size = $camp_general['cg_feed_css_size'];
                $cg_feed_css_wrap = $camp_general['cg_feed_css_wrap'];

                echo '<br>Extracting content from original post for ';

                // test url
                // $url = "http://news.jarm.com/view/75431";
                $wholeFound = '';
                if (1) {
                    $i = 0;
                    foreach ($cg_feed_custom_id as $cg_selecotr_data) {

                        $cg_selector = $cg_custom_selector[$i];
                        $cg_feed_css_size_s = $cg_feed_css_size[$i];
                        $cg_feed_css_wrap_s = $cg_feed_css_wrap[$i];

                        echo '<br>' . $cg_selector . ' = "' . $cg_selecotr_data . '"';

                        if ($cg_feed_css_wrap_s == 'inner') {
                            $inner = true;
                        } else {
                            $inner = false;
                        }

                        if ($cg_selector == 'xpath') {
                            $ret = $wpAutomaticDom->getContentByXPath(stripslashes($cg_selecotr_data), $inner);
                        } elseif ($cg_selector == 'class') {
                            $ret = $wpAutomaticDom->getContentByClass($cg_selecotr_data, $inner);
                        } elseif ($cg_selector == 'id') {
                            $ret = $wpAutomaticDom->getContentByID($cg_selecotr_data, $inner);
                        }

                        $extract = '';

                        foreach ($ret as $itm) {

                            $extract .= $itm;

                            if ($cg_feed_css_size_s == 'single') {
                                break;
                            }
                        }

                        $rule_num = $i + 1;
                        $res['rule_' . $rule_num] = $extract;
                        $res['rule_' . $rule_num . '_plain'] = strip_tags($extract);

                        if (wp_automatic_trim($extract) == '') {
                            echo '<br>Nothing found to extract for this rule';
                        } else {
                            echo ' <-- ' . strlen($extract) . ' chars extracted';
                            $wholeFound = (wp_automatic_trim($wholeFound) == '') ? $extract : $wholeFound . '<br>' . $extract;
                        }
                        $i++;
                    }
                    if (wp_automatic_trim($wholeFound) != '') {
                        $res['cont'] = $wholeFound;
                        $res['matched_content'] = $wholeFound;
                    }
                } else {
                    echo '<br>could not parse the content returning summary';
                }

                // REGEX EXTRACT
            } elseif (in_array('OPT_FEED_CUSTOM_R', $camp_opt)) {

                echo '<br>Extracting content using REGEX ';
                $cg_feed_custom_regex = $camp_general['cg_feed_custom_regex'];

                $finalmatch = ''; //all content found using REGEX
                $x = 0;
                foreach ($cg_feed_custom_regex as $cg_feed_custom_regex_single) {

                    $cg_feed_custom_regex_single = html_entity_decode($cg_feed_custom_regex_single);

                    if (wp_automatic_trim($cg_feed_custom_regex_single) != '') {

                        $finalmatch2 = ''; // all content found using this REGEX rule

                        // we have a regex
                        echo '<br>Regex :' . wp_automatic_htmlspecialchars($cg_feed_custom_regex_single);

                        // extracting
                        if (wp_automatic_trim($original_cont) != '') {
                            preg_match_all('!' . $cg_feed_custom_regex_single . '!is', $original_cont, $matchregex);
                            $c = 0;

                            if (!isset($matchregex)) {
                                $matchregex = array();
                            }
                            //fix count(null) in next line

                            for ($i = 1; $i < count($matchregex); $i++) {

                                foreach ($matchregex[$i] as $newmatch) {

                                    if (wp_automatic_trim($newmatch) != '') { //good we have a new extraction

                                        //json part to fix?  "(.*?)"
                                        if (stristr($cg_feed_custom_regex_single, '"(.*?)')) {
                                            echo '<-- Fixing JSON Part';
                                            $suggestedFixedContent = wp_automatic_fix_json_part($newmatch);

                                            //overwriting
                                            if (wp_automatic_trim($suggestedFixedContent) != '') {
                                                echo '<-- Fix success overwriting ';
                                                $newmatch = $suggestedFixedContent;
                                            }

                                        }

                                        // update the final match for all found extraction from all rules
                                        if (wp_automatic_trim($finalmatch2) != '') {
                                            $finalmatch2 .= '<br>' . $newmatch;

                                        } else {
                                            $finalmatch2 .= $newmatch;

                                        }

                                        if (wp_automatic_trim($finalmatch) != '') {
                                            $finalmatch .= '<br>' . $newmatch;

                                        } else {
                                            $finalmatch .= $newmatch;

                                        }
                                    }

                                    $c++;

                                    //break the loop if a single match only is needed
                                    if (in_array('OPT_FEED_REGEX_SINGLE_' . $i, $camp_opt)) {
                                        echo '<-- Single match break';
                                        break;
                                    }

                                }
                            }

                            $rule_num = $x + 1;
                            $res['rule_' . $rule_num] = $finalmatch2;
                            $res['rule_' . $rule_num . '_plain'] = strip_tags($finalmatch2);

                            echo '<-- ' . strlen($finalmatch2) . ' chars found total of ' . $c . ' matches';
                        } else {
                            echo '<br>Can not load original content.';
                        }
                    } // rule not empty

                    $x++;

                } // foreach rule

                if (wp_automatic_trim($finalmatch) != '') {
                    // overwirte
                    echo '<br>' . strlen($finalmatch) . ' chars extracted using REGEX';
                    $res['cont'] = $finalmatch;
                    $res['matched_content'] = $finalmatch;
                } else {
                    echo '<br>Nothing extracted using REGEX using summary instead..';
                }
            }

            //Issue fix no title but URL instead of the title when title is set to auto-detect
            if ($camp_general['cg_ml_ttl_method'] == 'auto' && stristr($title, 'http')) {
                echo '<br>Extracting a good title....';

                $possible_title = $this->extract_title_auto($title, $original_cont);

                if (wp_automatic_trim($possible_title) != '') {
                    echo '<-- Good title found';
                    $res['title'] = $title = $possible_title;
                } else {
                    echo '<-- No title found';
                }
            }

            // redirect_url tag finding
            $redirect = '';
            $redirect = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

            if (wp_automatic_trim($redirect) != '') {
                $res['redirect_url'] = $redirect;
            } else {
                $res['redirect_url'] = $res['source_link'];
            }

            // Stripping content using id or class from $res[cont]
            if (in_array('OPT_STRIP_CSS', $camp_opt)) {

                echo '<br>Stripping content using:- ';

                $cg_selector = $camp_general['cg_custom_strip_selector'];
                $cg_selecotr_data = $camp_general['cg_feed_custom_strip_id'];
                $cg_selecotr_data = array_filter($cg_selecotr_data);

                // Load dom
                $final_doc = new DOMDocument();

                // getting encoding

                preg_match_all('{charset=["|\']([^"]+?)["|\']}', $original_cont, $encMatches);
                $possibleCharSet = $encMatches[1];

                $possibleCharSet = isset($possibleCharSet[0]) ? $possibleCharSet[0] : '';

                if (wp_automatic_trim($possibleCharSet) == '') {
                    $possibleCharSet = 'UTF-8';
                }

                // overwrite to utf if already utf-8
                if ($possibleCharSet != 'UTF-8' && function_exists('mb_detect_encoding') && mb_detect_encoding($res['cont']) == 'UTF-8') {

                    echo '<br>Source encoding is ' . $possibleCharSet . ' but we still think it is utf-8 resetting...';
                    $possibleCharSet = 'UTF-8';
                }

                $charSetMeta = '<meta http-equiv="content-type" content="text/html; charset=' . $possibleCharSet . '"/>';

                $full_html = '<head>' . $charSetMeta . '</head><body>' . $res['cont'] . '</body>';

                @$final_doc->loadHTML($full_html);
                $selector = new DOMXPath($final_doc);

                $html_to_count = $final_doc->saveHTML($final_doc->documentElement);

                $i = 0;
                $inner = false;
                foreach ($cg_selecotr_data as $cg_selector_data_single) {

                    echo '<br> - ' . $cg_selector[$i] . ' = "' . $cg_selector_data_single . '" ';

                    if (wp_automatic_trim($cg_selector_data_single) != '') {

                        if ($cg_selector[$i] == 'class') {
                            $query_final = '//*[contains(attribute::class, "' . wp_automatic_trim($cg_selector_data_single) . '")]';
                        } elseif ($cg_selector[$i] == 'id') {
                            $query_final = "//*[@id='" . wp_automatic_trim($cg_selector_data_single) . "']";
                        }

                        $countBefore = $this->chars_count($html_to_count);

                        foreach ($selector->query($query_final) as $e) {
                            $e->parentNode->removeChild($e);
                        }

                        $html_to_count = $final_doc->saveHTML($final_doc->documentElement);

                        $countAfter = $this->chars_count($html_to_count);

                        echo '<-- ' . ($countBefore - $countAfter) . ' chars removed';
                    }

                    $i++;
                }

                $contentAfterReplacement = $final_doc->saveHTML($final_doc->documentElement);
                $contentAfterReplacement = wp_automatic_str_replace(array(
                    '<html>',
                    '</html>',
                    '<body>',
                    '</body>',
                    $charSetMeta,
                ), '', $contentAfterReplacement);
                $contentAfterReplacement = preg_replace('{<head>.*?</head>}', '', $contentAfterReplacement);

                $res['cont'] = wp_automatic_trim($contentAfterReplacement);

                // overwirte
                $res['matched_content'] = $res['cont'];
            }

            // Stripping content using REGEX
            if (in_array('OPT_STRIP_R', $camp_opt)) {
                $current_content = $res['matched_content'];
                $current_title = $res['title'];
                $cg_post_strip = html_entity_decode($camp_general['cg_post_strip']);

                $cg_post_strip = explode("\n", $cg_post_strip);
                $cg_post_strip = array_filter($cg_post_strip);

                foreach ($cg_post_strip as $strip_pattern) {
                    if (wp_automatic_trim($strip_pattern) != '') {

                        // $strip_pattern ='<img[^>]+\\>';

                        echo '<br>Stripping using REGEX:' . wp_automatic_htmlentities($strip_pattern);

                        $countBefore = $this->chars_count($current_content);

                        $current_content = preg_replace('{' . wp_automatic_trim($strip_pattern) . '}is', '', $current_content);

                        // replacing in rules
                        $i = 1;
                        while (isset($res["rule_$i"])) {

                            $res["rule_$i"] = preg_replace('{' . wp_automatic_trim($strip_pattern) . '}is', '', $res["rule_$i"]);
                            $i++;
                        }

                        $countAfter = $this->chars_count($current_content);

                        echo ' <-- ' . ($countBefore - $countAfter) . ' chars removed';

                        $current_title = preg_replace('{' . wp_automatic_trim($strip_pattern) . '}is', '', $current_title);
                    }
                }

                if (wp_automatic_trim($current_content) != '') {
                    $res['matched_content'] = $current_content;
                    $res['cont'] = $current_content;
                }

                if (wp_automatic_trim($current_title) != '') {
                    $res['matched_title'] = $current_title;
                    $res['original_title'] = $current_title;
                    $res['title'] = $current_title;
                }
            } // end regex replace

            // if option active OPT_STRIP_IMGS then remove all images
            if (in_array('OPT_STRIP_IMGS', $camp_opt)) {

                echo '<br>Stripping images...';

                //count before removing
                $countBefore = $this->chars_count($res['matched_content']);

                $res['matched_content'] = preg_replace('{<img[^>]+\\>}', '', $res['matched_content']);
                $res['cont'] = preg_replace('{<img[^>]+\\>}', '', $res['cont']);

                //count after removing
                $countAfter = $this->chars_count($res['matched_content']);

                echo '<-- ' . ($countBefore - $countAfter) . ' chars removed';

                // replacing in rules
                $i = 1;
                while (isset($res["rule_$i"])) {

                    $res["rule_$i"] = preg_replace('{<img[^>]+\\>}', '', $res["rule_$i"]);
                    $i++;
                }
            }

            // strip tags
            if (in_array('OPT_STRIP_T', $camp_opt)) {

                echo '<br>Stripping html tags...';

                $cg_allowed_tags = wp_automatic_trim($camp_general['cg_allowed_tags']);

                if (!stristr($cg_allowed_tags, '<script')) {
                    $res['matched_content'] = preg_replace('{<script.*?script>}s', '', $res['matched_content']);
                    $res['cont'] = preg_replace('{<script.*?script>}s', '', $res['cont']);

                    $res['matched_content'] = preg_replace('{<noscript.*?noscript>}s', '', $res['matched_content']);
                    $res['cont'] = preg_replace('{<noscript.*?noscript>}s', '', $res['cont']);
                }

                $res['matched_content'] = strip_tags($res['matched_content'], $cg_allowed_tags);
                $res['cont'] = strip_tags($res['cont'], $cg_allowed_tags);
            }

            // validate content size

            // MUST CONTENT
            if (in_array('OPT_MUST_CONTENT', $camp_opt)) {

                if (wp_automatic_trim($res['cont']) == '') {
                    echo '<--No content excluding';
                    $this->link_execlude($camp->camp_id, $original_url);
                    continue;
                }
            }

            //if camp sub type camp_sub_type is BingNews then add min lenth of 300
            if (! in_array('OPT_MIN_LENGTH', $camp_opt) && isset($camp->camp_sub_type) && ($camp->camp_sub_type == 'BingNews' || $camp->camp_sub_type == 'GoogleNews'  )) {
                $camp_opt[] = 'OPT_MIN_LENGTH';
                $camp_general['cg_min_length'] = 300;
            } 
            

            // limit content
            if (in_array('OPT_MIN_LENGTH', $camp_opt) || in_array('OPT_MAX_LENGTH', $camp_opt)) {

                $contentTextual = preg_replace('{<script.*?script>}s', '', $res['cont']);
                $contentTextual = strip_tags($contentTextual);
                $contentTextual = wp_automatic_str_replace(' ', '', $contentTextual);

                if (function_exists('mb_strlen')) {
                    $contentLength = mb_strlen($contentTextual);
                } else {
                    $contentLength = strlen($contentTextual);
                }

                unset($contentTextual);

                echo '<br>Content length:' . $contentLength;

                if (in_array('OPT_MIN_LENGTH', $camp_opt)) {
                    if ($contentLength < $camp_general['cg_min_length']) {
                        echo '<--Shorter than the minimum(' . $camp_general['cg_min_length'] . ')... Excluding';
                        $this->link_execlude($camp->camp_id, $original_url);
                        continue;
                    } else {
                        echo '<--Valid Min length.. i.e > (' . $camp_general['cg_min_length'] . ') ';
                    }
                }

                if (in_array('OPT_MAX_LENGTH', $camp_opt)) {
                    if ($contentLength > $camp_general['cg_max_length']) {
                        echo '<--Longer than the maximum(' . $camp_general['cg_max_length'] . ')... Excluding';
                        $this->link_execlude($camp->camp_id, $original_url);
                        continue;
                    } else {
                        echo '<--Valid Max length i.e < (' . $camp_general['cg_max_length'] . ')';
                    }
                }

            }

            // Entity decode
            if (in_array('OPT_FEED_ENTITIES', $camp_opt)) {
                echo '<br>Decoding html entities';

                // php 5.3 and lower convert &nbsp; to invalid charchters that broke everything

                $res['original_title'] = wp_automatic_str_replace('&nbsp;', ' ', $res['original_title']);
                $res['matched_content'] = wp_automatic_str_replace('&nbsp;', ' ', $res['matched_content']);

                $res['original_title'] = html_entity_decode($res['original_title'], ENT_QUOTES | ENT_HTML401);
                $res['title'] = html_entity_decode($res['title'], ENT_QUOTES | ENT_HTML401);

                $res['matched_content'] = html_entity_decode($res['matched_content'], ENT_QUOTES | ENT_HTML401);
                $res['cont'] = $res['matched_content'];
            } // end entity decode

            // Clean googleads and <script tag
            $res['cont'] = preg_replace('{<ins.*?ins>}s', '', $res['cont']);
            $res['cont'] = preg_replace('{<ins.*?>}s', '', $res['cont']);

            if (!in_array('OPT_FEED_SCRIPT', $camp_opt)) {
                $res['cont'] = preg_replace('{<script.*?script>}s', '', $res['cont']);
            }

            $res['cont'] = preg_replace('{\(adsbygoogle.*?\);}s', '', $res['cont']);
            $res['matched_content'] = $res['cont'];

            // meta tags
            $found_tags_count = 0;
            if (in_array('OPT_ORIGINAL_META', $camp_opt)) {

                // echo
                echo '<br>Extracting original post meta tags ';

                // extract all metas
                preg_match_all('{<meta.*?>}s', $original_cont, $metaMatches);

                $allMeta = $metaMatches[0];

                foreach ($allMeta as $singleMeta) {

                    if (stristr($singleMeta, 'keywords')) {

                        if (preg_match('{name[\s]?=[\s]?["\']keywords["\']}', $singleMeta)) {

                            preg_match_all('{content[\s]?=[\s]?[\'"](.*?)[\'"]}s', $singleMeta, $realTagsMatches);
                            $realTagsMatches = $realTagsMatches[1];

                            if (wp_automatic_trim($realTagsMatches[0]) != '') {

                                echo '<br>Meta tags:' . $realTagsMatches[0];
                                $res['tags'] = $realTagsMatches[0];

                                $found_tags_count++;
                            }
                        }
                    }
                }

                ////if count < 1 then try to get Scheme keywords "keywords":["keyword1","keyword2","keyword3"]
                if ($found_tags_count < 1) {
                    echo '<br>No tags found in meta tags, Trying to get keywords from Schema...';

                    //preg match "keywords":["keyword1","keyword2","keyword3"]
                    preg_match('{keywords":\[(.*?)\]}s', $original_cont_raw, $schemeKeywordsMatches);

                    //if not empty $schemeKeywordsMatches [1]
                    if (!empty($schemeKeywordsMatches[1])) {

                        //keywords found
                        $schemeKeywordsMatches = $schemeKeywordsMatches[1];

                        //remove quotes from keywords
                        $schemeKeywordsMatches = wp_automatic_str_replace('"', '', $schemeKeywordsMatches);

                        //reported found keywords
                        echo '<br>Keywords found in Schema: ' . ($schemeKeywordsMatches);

                        //set the tags
                        $res['tags'] = $schemeKeywordsMatches;

                    } else {
                        echo '<br>No keywords found in Schema';
                    }

                }
            }

            // Extract cats from original source
            if (in_array('OPT_ORIGINAL_CATS', $camp_opt) && wp_automatic_trim($camp_general['cg_feed_custom_id_cat']) != '') {

                echo '<br>Extracting original post categories ';

                $cg_selector_cat = $camp_general['cg_custom_selector_cat'];
                $cg_selecotr_data_cat = $camp_general['cg_feed_custom_id_cat'];
                $inner = false;

                echo ' for ' . $cg_selector_cat . ' = ' . $cg_selecotr_data_cat;

                // dom class
                if (!isset($wpAutomaticDom)) {
                    require_once 'inc/class.dom.php';
                    $wpAutomaticDom = new wpAutomaticDom($original_cont);
                }

                if (1) {

                    $extract = '';

                    if ($cg_selector_cat == 'class') {
                        $extract = $wpAutomaticDom->getContentByClass($cg_selecotr_data_cat, $inner);
                    } elseif ($cg_selector_cat == 'id') {
                        $extract = $wpAutomaticDom->getContentByID($cg_selecotr_data_cat, $inner);
                    } elseif ($cg_selector_cat == 'xpath') {

                        //if cg_selecotr_data_cat ends with /a/span then remove the /span from the end of the xpath using REGEX
                        if (preg_match('{/span$}', $cg_selecotr_data_cat)) {
                            echo '<br>Removing /span from the end of the xpath for category link extraction...';
                            $cg_selecotr_data_cat = preg_replace('{/span$}', '', $cg_selecotr_data_cat);
                        }

                        $extract = $wpAutomaticDom->getContentByXPath(stripslashes($cg_selecotr_data_cat), $inner);
                    }

                    if (is_array($extract)) {

                        if (in_array('OPT_SELECTOR_SINGLE_CAT', $camp_opt)) {
                            $extract = $extract[0];
                        } else {
                            $extract = implode(' ', $extract);
                        }
                    }

                    if (wp_automatic_trim($extract) == '') {
                        echo '<br>Nothing found to extract for this category rule';
                    } else {
                        echo '<br>Cat Rule extracted ' . strlen($extract) . ' charchters ';

                        if (stristr($extract, '<a')) {
                            preg_match_all('{<a .*?>(.*?)</a}su', $extract, $cats_matches);

                            $cats_founds = $cats_matches[1];
                            $cat_founds = array_map('strip_tags', $cats_founds);

                            if (in_array('OPT_ORIGINAL_CATS_REPLACE', $camp_opt)) {
                                $post_cat_replace = wp_automatic_trim($camp_general['cg_cat_replace']);
                                $post_cat_replace_arr = array_filter(explode("\n", $post_cat_replace));

                                foreach ($post_cat_replace_arr as $post_cat_replace_arr_single) {
                                    if (stristr($post_cat_replace_arr_single, '|')) {
                                        $post_cat_replace_arr_single = wp_automatic_trim($post_cat_replace_arr_single);
                                        $post_cat_replace_arr_single_arr = explode('|', $post_cat_replace_arr_single);
                                        echo '<br>Replacing ' . $post_cat_replace_arr_single_arr[0] . ' with ' . $post_cat_replace_arr_single_arr[1] . ' in found categories...';

                                        foreach ($cat_founds as $key => $val) {

                                            if (stristr($val, ';')) {
                                                $val = html_entity_decode($val);
                                            }

                                            if (wp_automatic_trim($val) == wp_automatic_trim($post_cat_replace_arr_single_arr[0])) {
                                                $cat_founds[$key] = $post_cat_replace_arr_single_arr[1];
                                                echo '<-- found';
                                            }

                                        }

                                    }
                                }

                            }

                            $cats_str = implode(',', $cat_founds);

                            echo ' found cats:' . $cats_str;
                            $res['cats'] = $cats_str;
                        } else {
                            echo '<-- No links found';
                        }
                    }
                }
            }

            // Extract tags from original source
            if (in_array('OPT_ORIGINAL_TAGS', $camp_opt) && wp_automatic_trim($camp_general['cg_feed_custom_id_tag']) != '' && wp_automatic_trim($original_cont) != '') {

                echo '<br>Extracting original post tags ';

                $cg_selector_tag = $camp_general['cg_custom_selector_tag'];
                $cg_selecotr_data_tag = $camp_general['cg_feed_custom_id_tag'];
                $inner = false;

                echo ' for ' . $cg_selector_tag . ' = ' . $cg_selecotr_data_tag;

                // dom class
                if (!isset($wpAutomaticDom)) {
                    require_once 'inc/class.dom.php';
                    $wpAutomaticDom = new wpAutomaticDom($original_cont);
                }

                if (1) {

                    $extract = '';

                    if ($cg_selector_tag == 'class') {
                        $extract = $wpAutomaticDom->getContentByClass($cg_selecotr_data_tag, $inner);
                    } elseif ($cg_selector_tag == 'id') {
                        $extract = $wpAutomaticDom->getContentByID($cg_selecotr_data_tag, $inner);
                    } elseif ($cg_selector_tag == 'xpath') {
                        $extract = $wpAutomaticDom->getContentByXPath(stripslashes($cg_selecotr_data_tag), $inner);
                    }

                    if (is_array($extract)) {

                        if (in_array('OPT_SELECTOR_SINGLE_TAG', $camp_opt)) {
                            $extract = $extract[0];
                        } else {
                            $extract = implode(' ', $extract);
                        }
                    }

                    if (wp_automatic_trim($extract) == '') {
                        echo '<br>Nothing found to extract for this tag rule';
                    } else {
                        echo '<br>Tag Rule extracted ' . strlen($extract) . ' charchters ';

                        if (stristr($extract, '<a')) {
                            preg_match_all('{<a .*?>(.*?)</a}su', $extract, $tags_matches);

                            $tags_founds = array(); //init
                            $tags_founds = $tags_matches[1];
                            $tags_founds = array_map('strip_tags', $tags_founds);
                            $tags_founds = array_map('trim', $tags_founds);

                            // if option OPT_ORIGINAL_TAGS_REPLACE is set replace the tags
                            if (in_array('OPT_ORIGINAL_TAGS_REPLACE', $camp_opt)) {

                                // replace tags
                                if (wp_automatic_trim($camp_general['cg_tag_replace']) != '') {

                                    $post_tag_replace_arr = explode("\n", $camp_general['cg_tag_replace']);

                                    //report count of available tags
                                    echo '<br> - ' . count($tags_founds) . ' tags found: ' . implode(', ', $tags_founds);

                                    foreach ($post_tag_replace_arr as $post_tag_replace) {

                                        $post_tag_replace = wp_automatic_trim($post_tag_replace);

                                        if (wp_automatic_trim($post_tag_replace) != '') {

                                            $post_tag_replace_arr = explode('|', $post_tag_replace);

                                            $tag_from = wp_automatic_trim($post_tag_replace_arr[0]);
                                            $tag_to = wp_automatic_trim($post_tag_replace_arr[1]);

                                            echo '<br> - Replacing tag ' . $tag_from . ' with ' . $tag_to;

                                            $tags_founds = wp_automatic_str_replace($tag_from, $tag_to, $tags_founds);
                                        }
                                    }
                                }
                            }

                            $tags_str = implode(',', $tags_founds);

                            echo '<br>Found tags:' . $tags_str;
                            $res['tags'] = $tags_str;
                        }
                    }
                }
            } elseif (in_array('OPT_ORIGINAL_TAGS', $camp_opt)) {

                echo '<br>You must add a valid ID/Class to Extract tags, No tags will get extracted.';
            } // extract tags

            // extract author from original source
            if (in_array('OPT_ORIGINAL_AUTHOR', $camp_opt) && wp_automatic_trim($camp_general['cg_feed_custom_id_author']) != '') {

                echo '<br>Extracting original post author ';

                $cg_selector_author = $camp_general['cg_custom_selector_author'];
                $cg_selecotr_data_author = $camp_general['cg_feed_custom_id_author'];
                $inner = false;

                echo ' for ' . $cg_selector_author . ' = ' . $cg_selecotr_data_author;

                // dom class
                if (!isset($wpAutomaticDom)) {
                    require_once 'inc/class.dom.php';
                    $wpAutomaticDom = new wpAutomaticDom($original_cont);
                }

                if (1) {

                    $extract = '';

                    if ($cg_selector_author == 'class') {
                        $extract = $wpAutomaticDom->getContentByClass($cg_selecotr_data_author, $inner);
                    } elseif ($cg_selector_author == 'id') {
                        $extract = $wpAutomaticDom->getContentByID($cg_selecotr_data_author, $inner);
                    } elseif ($cg_selector_author == 'xpath') {
                        $extract = $wpAutomaticDom->getContentByXPath(stripslashes($cg_selecotr_data_author), $inner);
                    }

                    if (is_array($extract)) {

                        if (in_array('OPT_SELECTOR_SINGLE_AUTHOR', $camp_opt)) {
                            $extract = $extract[0];
                        } else {
                            $extract = implode(' ', $extract);
                        }
                    }

                    // Validate returned author
                    if (wp_automatic_trim($extract) == '') {
                        echo '<br>Nothing found to extract for this author rule';
                    } else {
                        echo '<br>author Rule extracted ' . strlen($extract) . ' charchters ';

                        if (stristr($extract, '<a')) {
                            preg_match_all('{<a .*?>(.*?)</a}su', $extract, $author_matches);

                            $author_founds = $author_matches[1];
                            $author_str = strip_tags($author_founds[0]);

                            echo ' Found author:' . $author_str;
                            $res['author'] = $author_str;
                        } else {

                            //no links
                            $author_str = strip_tags($extract);

                            if (strlen($author_str) < 100) {
                                echo ' Found author:' . $author_str;
                                $res['author'] = $author_str;
                            }

                        }
                    }
                }
            } elseif (in_array('OPT_ORIGINAL_AUTHOR', $camp_opt)) {

                if (wp_automatic_trim($res['author']) == '') {

                    echo '<br>You did not set a specific config to Extract Author, The plugin will try to auto-detect... ';

                    // try to auto detect author using extract_author_auto
                    if (isset($original_cont_raw)) {
                        $author_str = $this->extract_author_auto($original_cont_raw);
                    } else {
                        $author_str = $this->extract_author_auto($original_cont);
                    }

                    if (wp_automatic_trim($author_str) != '') {
                        echo '<br>Auto detected author:' . $author_str;
                        $res['author'] = $author_str;
                    } else {
                        echo '<br>Auto detection failed';
                    }

                }
            } // extract author

            if (!in_array('OPT_ENCLUSURE', $camp_opt)) {
                if (isset($media_image_url) && !stristr($res['cont'], '<img')) {
                    echo '<br>enclosure image:' . $media_image_url;
                    $res['cont'] = '<img src="' . $media_image_url . '" /><br>' . $res['cont'];
                }
            }

            // Part to custom field OPT_FEED_PTF

            // Extracted custom fields ini
            $customFieldsArr = array();
            $ruleFields = array(); // fields names

            if (in_array('OPT_FEED_PTF', $camp_opt)) {

                echo '<br>Specific Part to custom field extraction';

                // Load rules
                $cg_part_to_field = wp_automatic_trim(html_entity_decode($camp_general['cg_part_to_field']));
                $cg_part_to_field_parts = explode("\n", $cg_part_to_field);

                // Process rules
                foreach ($cg_part_to_field_parts as $cg_part_to_field_part) {
                    echo '<br>Rule:' . wp_automatic_htmlentities($cg_part_to_field_part);

                    // Validate format |
                    if (!stristr($cg_part_to_field_part, '|')) {
                        echo '<- Wrong format...';
                        continue;
                    }

                    // Parse rule
                    $rule_parts = explode('|', $cg_part_to_field_part);
                    $rule_single = 0; //ini

                    //case regex with | used as alternatives
                    if (count($rule_parts) > 3 && $rule_parts[0] == 'regex') {
                        $rule_method = wp_automatic_trim($rule_parts[0]); //first thing

                        $rule_field = wp_automatic_trim(end($rule_parts));

                        // case |1 for the single value instead of many
                        if (wp_automatic_trim($rule_field) == 1) {
                            $rule_single = 1;
                            array_pop($rule_parts); // remove the 1 from the array
                            $rule_field = wp_automatic_trim(end($rule_parts)); // field name is now the last child
                        }

                        $rule_value_parts = array();
                        for ($i = 1; $i < count($rule_parts) - 1; $i++) {
                            $rule_value_parts[] = $rule_parts[$i];
                        }

                        $rule_value = implode('|', $rule_value_parts);

                    } else {
                        $rule_method = wp_automatic_trim($rule_parts[0]);
                        $rule_value = wp_automatic_trim($rule_parts[1]);
                        $rule_field = wp_automatic_trim($rule_parts[2]);

                        //set single or all
                        if (isset($rule_parts[3]) && wp_automatic_trim($rule_parts[3]) == 1) {
                            $rule_single = 1;
                        }

                    }

                    // Validate rule
                    if (wp_automatic_trim($rule_method) == '' || wp_automatic_trim($rule_value) == '' || wp_automatic_trim($rule_field) == '') {
                        echo '<- Wrong format...';
                        continue;
                    }

                    $ruleFields[] = $rule_field;

                    // Validate rule method: class,id,regex,xpath
                    if ($rule_method != 'id' && $rule_method != 'class' && $rule_method != 'regex' && $rule_method != 'xpath') {
                        echo '<- Wrong Method:' . $rule_method;
                        continue;
                    }

                    // id,class,xPath
                    if ($rule_method == 'id' || $rule_method == 'class' || $rule_method == 'xpath') {

                        // Dom object
                        $doc = new DOMDocument();

                        // Load Dom
                        @$doc->loadHTML($original_cont);

                        // xPath object
                        $xpath = new DOMXPath($doc);

                        // xPath query
                        if ($rule_method != 'xpath') {
                            $query = "//*[@" . $rule_method . "='" . $rule_value . "']";
                            echo '<-- query:' . $query;
                            $xpathMatches = $xpath->query("//*[@" . $rule_method . "='" . $rule_value . "']");
                        } else {
                            $xpathMatches = $xpath->query("$rule_value");
                        }

                        echo '<-- ' . count($xpathMatches) . ' matches found';

                        // Single item ?
                        if ($rule_single) {
                            $xpathMatches = array(
                                $xpathMatches->item(0),
                            );
                        }

                        // Rule result ini
                        $rule_result = '';

                        foreach ($xpathMatches as $xpathMatch) {

                            if ($rule_field == 'tags' || $rule_field == 'categories' || stristr($rule_field, 'taxonomy_')) {
                                $rule_result .= ',' . $xpathMatch->nodeValue;
                            } else {
                                $rule_result .= $xpathMatch->nodeValue;
                            }
                        }

                        // Store field to be added
                        if (wp_automatic_trim($rule_result) != '') {
                            echo ' <--' . $this->chars_count($rule_result) . ' chars extracted';

                            if ($rule_field == 'categories') {
                                $res['cats'] = $rule_result;
                            } else {
                                $customFieldsArr[] = array(
                                    $rule_field,
                                    $rule_result,
                                );
                            }
                        } else {
                            echo '<-- nothing found';
                        }
                    } else {

                        // Regex extract
                        $matchregex = array();
                        $finalmatch = '';

                        //echo ' the rule value ' . $rule_value;

                        // Match
                        preg_match_all('{' . wp_automatic_trim($rule_value) . '}is', $original_cont, $matchregex);

                        if (isset($matchregex[1])) {
                            echo '<-- ' . count($matchregex[1]) . ' matches ';
                        } else {
                            echo '<-- Added REGEX seems not to contain (.*?)  the part to really parse, please add it';
                        }

                        $matchregex_vals = isset($matchregex[1]) ? $matchregex[1] : array();

                        if (isset($matchregex[2])) {
                            $matchregex_vals = array_merge($matchregex_vals, $matchregex[2]);
                        }

                        // single match
                        if ($rule_single && count($matchregex_vals) > 1) {
                            $matchregex_vals = array(
                                $matchregex_vals[0],
                            );
                        }

                        // Read matches
                        foreach ($matchregex_vals as $newmatch) {

                            //fix json part
                            //json part to fix?  "(.*?)"
                            if (stristr($rule_value, '"(.*?)')) {
                                echo '<-- Fixing JSON Part';
                                $suggestedFixedContent = wp_automatic_fix_json_part($newmatch);

                                //overwriting
                                if (wp_automatic_trim($suggestedFixedContent) != '') {
                                    echo '<-- Fix success overwriting ';
                                    $newmatch = $suggestedFixedContent;
                                }

                            }

                            if (wp_automatic_trim($newmatch) != '') {

                                if (wp_automatic_trim($finalmatch) == '') {
                                    $finalmatch .= '' . $newmatch;
                                } else {

                                    if ($rule_field == 'tags' || $rule_field == 'categories' || stristr($rule_field, 'taxonomy_')) {

                                        $finalmatch .= ',' . $newmatch;
                                    } else {
                                        $finalmatch .= $newmatch;
                                    }
                                }
                            }
                        }

                        // Store field to be added
                        if (wp_automatic_trim($finalmatch) != '') {

                            echo ' <--' . $this->chars_count($finalmatch) . ' chars extracted';

                            if ($rule_field == 'categories') {
                                $res['cats'] = $res['categories_to_set'] = $finalmatch;

                            } else {
                                $customFieldsArr[] = array(
                                    $rule_field,
                                    $finalmatch,
                                );
                            }
                        } else {
                            echo '<-- Nothing found';
                        }
                    }
                } // foreach rule
            } // if part to field enabled

            $res['custom_fields'] = $customFieldsArr;

            foreach ($ruleFields as $field_name) {

                $field_value = ''; // ini

                foreach ($customFieldsArr as $fieldsArray) {

                    if ($fieldsArray[0] == $field_name) {
                        $field_value = $fieldsArray[1];
                    }
                }

                $res[$field_name] = $field_value;

                //an array holding the custom field names that we found values for
                //to be used by the search and replace option so it can search and replace on
                //custom fields values

                //add current field name to the array
                $this->customFieldsFound[] = $field_name;

            }

            // og:image check

            // $url="kenh14.vn/kham-pha/5-hoi-chung-benh-ky-quac-nghe-thoi-cung-toat-mo-hoi-hot-20151219200950753.chn";

            $currentOgImage = ''; // for og:image found check

            if (in_array('OPT_FEEDS_OG_IMG', $camp_opt)) {

                // getting the og:image
                // let's find og:image

                // if no http
                $original_cont = wp_automatic_str_replace('content="//', 'content="http://', $original_cont);

                echo '<br>Extracting og:image...';

                // let's find og:image may be the content we got has no image
                preg_match('{<meta[^<]*?(?:property|name)=["|\']og:image["|\'][^<]*?>}s', $original_cont, $plain_og_matches);

                //if no match, try another pattern
                //<meta property=og:image content="https://www.etnatrasporti.it/wp-content/uploads/2024/06/etna1.jpg"/>

                if (empty($plain_og_matches[0])) {
                    preg_match('{<meta (?:property|name)=og:image[^<]*?>}s', $original_cont, $plain_og_matches);

                }

                if (isset($plain_og_matches[0]) && stristr($plain_og_matches[0], 'og:image')) {
                    preg_match('{content=["|\'](.*?)["|\']}s', $plain_og_matches[0], $matches);
                    $og_img = $matches[1];

                    if (wp_automatic_trim($og_img) != '') {

                        $og_img_short = preg_replace('{http://.*?/}', '', $og_img);
                        echo $og_img_short;
                        if (wp_automatic_trim($og_img_short) == '') {
                            $og_img_short = $og_img;
                        }

                        // get og_title
                        preg_match_all('/<img .*>/', $original_cont, $all_images);

                        $all_images = $all_images[0];
                        $foundAlt = ''; // ini
                        foreach ($all_images as $single_image) {
                            if (stristr($single_image, $og_img_short)) {
                                // extract alt text
                                preg_match('/alt=["|\'](.*?)["|\']/', $single_image, $alt_matches);
                                $foundAlt = (isset($alt_matches[1])) ? $alt_matches[1] : '';
                            }
                        }

                        $res['og_img'] = $og_img;

                        // no http format but //tec
                        if (!stristr($og_img, 'http') && stristr($og_img, '//')) {
                            $res['og_img'] = 'https:' . $og_img;
                        }

                        $res['og_img'] = wp_automatic_trim($res['og_img']);
                        $res['og_alt'] = $foundAlt;
                        $currentOgImage = $og_img;
                    }

                } elseif (stristr($original_cont, '<meta property=og:image content=')) {

                    //rumble form <meta property=og:image content=https://sp.rmbl.ws/s8/1/c/_/c/y/c_cyk.qR4e-small--DEATH-DELETE-ANOTHER-DAY-O.jpg>
                    preg_match('{<meta property=og:image content=([^\s]*?)>}s', $original_cont, $plain_og_matches);

                    //if is set $plain_og_matches[1] and contains http and does not contain a space or a quote or a bracket, set it as og_img
                    if (isset($plain_og_matches[1]) && stristr($plain_og_matches[1], 'http') && !stristr($plain_og_matches[1], ' ') && !stristr($plain_og_matches[1], '"') && !stristr($plain_og_matches[1], '\'') && !stristr($plain_og_matches[1], '<') && !stristr($plain_og_matches[1], '>')) {
                        $res['og_img'] = $plain_og_matches[1];
                        $res['og_alt'] = '';
                        $og_alt = '';
                    }

                } else {

                    // if a webstory is found, get the image from the poster-portrait-src attribute
                    if (stristr($original_cont, '<amp-story') && stristr($original_cont, 'poster-portrait-src')) {
                        preg_match_all('/poster-portrait-src=["|\'](.*?)["|\']/', $original_cont, $webstory_matches);
                        $webstory_matches = $webstory_matches[1];
                        if (isset($webstory_matches[0])) {

                            echo '<br>Webstory found, getting image from poster-portrait-src attribute: ' . $webstory_matches[0] . '';

                            $res['og_img'] = $webstory_matches[0];
                            $res['og_alt'] = '';
                            $og_alt = '';
                        }
                    }

                }
            }

            // fix FB embeds
            if (stristr($res['cont'], 'fb-post') && !stristr($res['cont'], 'connect.facebook')) {
                echo '<br>Possible Facebook embeds found adding embed scripts';
                $res['cont'] .= '<div id="fb-root"></div>
<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>';
            }

            // fix twitter embeds
            if (stristr($res['cont'], 'twitter.com') && !stristr($res['cont'], 'platform.twitter')) {
                echo '<br>Possible tweets found without twitter JS. adding JS';
                $res['cont'] .= '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
            }

            // fix instagram.com embeds <script async src="//www.instagram.com/embed.js"></script>
            if (stristr($res['cont'], 'instagram.com') && !stristr($res['cont'], 'instagram.com/embed.js')) {
                echo '<br>Possible Instagram embeds found without JS. adding JS';
                $res['cont'] .= '<script async src="//www.instagram.com/embed.js"></script>';
            }

            // fix tiktok embeds <script async src="//www.tiktok.com/embed.js"></script>
            if (stristr($res['cont'], 'tiktok.com') && !stristr($res['cont'], 'tiktok.com/embed.js')) {
                echo '<br>Possible Tiktok embeds found without JS. adding JS';
                $res['cont'] .= '<script async src="//www.tiktok.com/embed.js"></script>';
            }

            // fix youtube no height embeds
            if (stristr($res['cont'], 'youtube.com/embed')) {

                preg_match_all('{<iframe[^>]*?youtube.com/embed/(.*?)["|\?].*?>(?:</iframe>)?}', $res['cont'], $yt_matches);

                $yt_matches_full = $yt_matches[0];
                $yt_matches_ids = $yt_matches[1];

                if (count($yt_matches_full) > 0) {

                    $i = 0;
                    foreach ($yt_matches_full as $embed) {

                        echo '<br>Youtube video embed format changed to WordPress for video :' . $yt_matches_ids[$i];

                        $res['cont'] = wp_automatic_str_replace($embed, '[embed]https://www.youtube.com/watch?v=' . $yt_matches_ids[$i] . '[/embed]', $res['cont']);

                        $i++;
                    }
                }
            }

            // check if image or not
            if (in_array('OPT_MUST_IMAGE', $camp_opt) && !stristr($res['cont'], '<img') && wp_automatic_trim($currentOgImage) == '') {

                echo '<br>Post contains no images skipping it ...';

                // Excluding it
                $this->link_execlude($camp->camp_id, $original_url);
            } else {

                // og image fix
                if (isset($res['og_img']) && wp_automatic_trim($res['og_img']) != '') {

                    $og_img = $res['og_img'];

                    // make sure it has the domain
                    if (!stristr($og_img, 'http:')) {
                        if (stristr($og_img, '//')) {

                            $og_img = 'http:' . $og_img;
                        } else {

                            $host = parse_url($url, PHP_URL_HOST);

                            // no domain at all
                            $og_img = '/' . $og_img;
                            $og_img = wp_automatic_str_replace('//', '/', $og_img);
                            $og_img = 'http://' . $host . $og_img;
                            $res['og_img'] = $og_img;
                        }
                    }
                }

                // og title or custom title extraction method
                if (in_array('OPT_FEED_OG_TTL', $camp_opt) || (isset($camp_general['cg_ml_ttl_method']) && wp_automatic_trim($camp_general['cg_ml_ttl_method']) != 'auto')) {

                    if (in_array('OPT_FEED_OG_TTL', $camp_opt)) {
                        // let's find og:title may be the content we got has no
                        preg_match('{<meta[^<]*?property=["|\']og:title["|\'][^<]*?>}s', $original_cont, $plain_og_matches);

                        if (@stristr($plain_og_matches[0], 'og:title')) {
                            preg_match('{content[\s]*=[\s]*"(.*?)"}s', $plain_og_matches[0], $matches);

                            $og_ttl = $matches[1];

                            echo '<br>og:title:' . html_entity_decode(wp_automatic_htmlspecialchars_decode($og_ttl));

                            if (wp_automatic_trim($og_ttl) != '') {
                                $og_ttl = wp_automatic_htmlspecialchars_decode($og_ttl, ENT_QUOTES);
                                $res['title'] = html_entity_decode($og_ttl);
                            }
                        } // If og:title
                    } else {

                        // custom extraction method
                        $cg_ml_ttl_method = $camp_general['cg_ml_ttl_method'];

                        require_once 'inc/class.dom.php';
                        $wpAutomaticDom = new wpAutomaticDom($original_cont);

                        $finalContent = '';

                        if ($cg_ml_ttl_method == 'visual') {

                            $cg_ml_visual = $camp_general['cg_ml_visual'];
                            $path = isset($cg_ml_visual[0]) ? $cg_ml_visual[0] : '';

                            if (wp_automatic_trim($path) == '') {
                                echo '<br>No path found for pagination, please set the extraction rule for pagination if you want to make use of pagination.';
                            }

                            foreach ($cg_ml_visual as $xpath) {

                                echo '<br>Getting title for XPath:' . $path;

                                try {

                                    $finalContent = $wpAutomaticDom->getContentByXPath($xpath, false);

                                    if (is_array($finalContent)) {
                                        $finalContent = implode('', $finalContent);
                                    }

                                    echo '<-- ' . strlen($finalContent) . ' chars';
                                } catch (Exception $e) {
                                    echo '<br>Error:' . $e->getMessage();
                                }
                            }
                        } elseif ($cg_ml_ttl_method == 'css') {

                            $cg_ml_css_type = $camp_general['cg_ml_css_type'];
                            $cg_ml_css = $camp_general['cg_ml_css'];
                            $cg_ml_css_wrap = $camp_general['cg_ml_css_wrap'];
                            $cg_ml_css_size = $camp_general['cg_ml_css_size'];
                            $finalContent = '';

                            $i = 0;
                            foreach ($cg_ml_css_type as $singleType) {

                                if ($cg_ml_css_wrap[$i] == 'inner') {
                                    $inner = true;
                                } else {
                                    $inner = false;
                                }

                                echo '<br>Extracting content by ' . $cg_ml_css_type[$i] . ' : ' . $cg_ml_css[$i];

                                if ($cg_ml_css_type[$i] == 'class') {
                                    $content = $wpAutomaticDom->getContentByClass($cg_ml_css[$i], $inner);
                                } elseif ($cg_ml_css_type[$i] == 'id') {
                                    $content = $wpAutomaticDom->getContentByID($cg_ml_css[$i], $inner);
                                } elseif ($cg_ml_css_type[$i] == 'xpath') {
                                    $content = $wpAutomaticDom->getContentByXPath(stripslashes($cg_ml_css[$i]), $inner);
                                }

                                if (is_array($content)) {

                                    if ($cg_ml_css_size[$i] == 'single') {
                                        $content = $content[0];
                                    } else {
                                        $content = implode("\n", $content);
                                    }

                                    $finalContent .= $content . "\n";
                                }

                                echo '<-- ' . strlen($content) . ' chars';

                                $i++;
                            } // foreach rule
                        } elseif ($cg_ml_ttl_method == 'regex') {

                            $cg_ml_regex = $camp_general['cg_ml_regex'];
                            $finalContent = '';
                            $i = 0;
                            foreach ($cg_ml_regex as $cg_ml_regex_s) {

                                echo '<br>Extracting content by REGEX : ' . wp_automatic_htmlspecialchars(html_entity_decode($cg_ml_regex_s));

                                $content = $wpAutomaticDom->getContentByRegex(html_entity_decode($cg_ml_regex_s));

                                $content = implode($content);

                                echo '<-- ' . strlen($content) . ' chars';

                                if (wp_automatic_trim($content) != '') {
                                    $finalContent .= $content . "\n";
                                }

                                $i++;
                            }
                        }
                    }

                    //set title from extraction if available
                    if (isset($finalContent) && wp_automatic_trim($finalContent) != '') {
                        if (!in_array('OPT_FEED_NO_DECODE', $camp_opt)) {
                            $possibleTitle = '';
                            if (wp_automatic_trim($finalContent) != '') {
                                $possibleTitle = strip_tags($finalContent);
                            }

                            if (wp_automatic_trim($possibleTitle) != '') {
                                $res['original_title'] = html_entity_decode($possibleTitle, ENT_QUOTES | ENT_HTML401);
                                $res['title'] = html_entity_decode($possibleTitle, ENT_QUOTES | ENT_HTML401);
                            }

                        } else {
                            $res['original_title'] = $res['title'] = $finalContent;
                        }
                    }

                }

                return $res;
            }
        } else {

            // duplicated link
            echo ' <-- duplicate in post <a href="' . admin_url('post.php?post=' . $this->duplicate_id . '&action=edit') . '">#' . $this->duplicate_id . '</a>';
        }

        //top post only
        if (in_array('OPT_FEED_TOP', $camp_opt)) {
            break;
        }

        endforeach
        ;

        echo '<br>End of feed items reached.';

        if (isset($camp->camp_sub_type) && $camp->camp_sub_type == 'Multi') {

            delete_post_meta($camp->camp_id, 'wp_automatic_cache');
        } else {

            // Set isItemsEndReached flag to yes
            update_post_meta($camp->camp_id, $feedMd5 . '_isItemsEndReached', 'yes');
        }
    } // end function

    public function extract_title_auto($title, $cont)
    {

        //<h1>
        if (substr_count($cont, '<h1') == 1) {
            //get h1 title

            preg_match('!<h1.*?>(.*?)</h1>!', $cont, $title_matchs);

            if (wp_automatic_trim($title_matchs[1]) != '') {
                $title = $title_matchs[1];
                echo 'H1:' . $title;
                return $title;
            }

        }

        //<title>
        if (stristr($cont, '<title>')) {
            preg_match('!<title>(.*?)</title>!', $cont, $title_matchs);

            if (wp_automatic_trim($title_matchs[1]) != '') {
                $title = $title_matchs[1];
                echo 'title tag:' . $title;
                return $title;
            }
        }

        //"og:title" content="Stripe and Plaid suit up for battle &#8211; TechCrunch"

        if (stristr($cont, 'og:title')) {
            preg_match('!og:title" content="(.*?)"!', $cont, $title_matchs);

            if (wp_automatic_trim($title_matchs[1]) != '') {
                $title = $title_matchs[1];

                echo 'og:title:' . $title;
                return $title;
            }
        }

    }

    /**
     * Function takes the original HTML and try to find the author name
     * @param string $cont
     * @return string $author
     */
    public function extract_author_auto($cont)
    {

        $author = '';

        //<meta name="author" content="John Doe">
        if (stristr($cont, '<meta name="author"')) {
            preg_match('!<meta name="author" content="(.*?)"!', $cont, $author_matchs);

            if (wp_automatic_trim($author_matchs[1]) != '') {
                $author = $author_matchs[1];
                echo 'author meta:' . $author;
                return $author;
            }
        }

        //"@type": "Person", "name": "Rizwan Choudhury"
        //"@type":"Person","name":"Holly Ellyatt"
        //"@type":"Person","@id":"https://valvepress.com/#/schema/person/2e36bfac540bde918e245be55c976600","name":"Atef"

        if (stristr($cont, '"Person"') && stristr($cont, '"@type"')) {

            preg_match_all('!"@type"\s*:\s*"Person".*?"name"\s*:\s*"(.*?)"!s', $cont, $author_matchs);

            if (isset($author_matchs[1][0])) {
                $author = $author_matchs[1][0];
                echo 'author schema:' . $author;
                return $author;
            }
        }

        //"author":"Duncan Riley"
        if (stristr($cont, '"author"')) {

            preg_match_all('!"author"\s*:\s*"(.*?)"!', $cont, $author_matchs);

            if (isset($author_matchs[1][0])) {
                $author = $author_matchs[1][0];
                echo 'author json:' . $author;
                return $author;
            }
        }

        //<span class="author">Duncan Riley</span>
        if (stristr($cont, '<span class="author">')) {

            preg_match_all('!<span class="author">(.*?)</span>!', $cont, $author_matchs);

            if (isset($author_matchs[1][0])) {
                $author = $author_matchs[1][0];
                echo 'author span:' . $author;
                return $author;
            }
        }

    }

    /**
     * Function takes Google news link and returns the original link
     * Firstly it checks the cache if table automatic_links contains a record with link_url = md5($link) then the value will be the column link_title
     * If not found then it will make an api_call to google to get the original link
     * it will then cache the result in the table automatic_links for future use
     *@param string $link
     *@return string $link
    @throws Exception if the api call fails or the link is not found in google news
     */
    public function get_google_news_link_old($link)
    {

        global $wpdb;

        //check if the link is already cached
        $md5 = md5($link);
        $cache = $wpdb->get_row("select * from {$wpdb->prefix}automatic_links where link_url = '$md5' ");

        if (isset($cache->link_url) > 0) {
            //found in cache
            $link = $cache->link_title;
            echo ' <span style="color:blue">[cached]</span>';
        } else {
            //not found in cache
            $url = $link;

            try {
                $newUrl = $this->api_call('googleNewsLinkExtractor', array('googleNewsLink' => $url));

                if (wp_automatic_trim($newUrl) != '') {
                    $link = $url = $newUrl;

                    echo ' <span style="color:green">[new]</span>';

                    //cache the result
                    $wpdb->insert("{$wpdb->prefix}automatic_links", array(
                        'link_url' => $md5,
                        'link_title' => $link,
                    ));

                }

            } catch (Exception $e) {

                throw new Exception('Error in google news link extractor: ' . $e->getMessage());

            }

        }

        return $link;

    }

/**
 * Retrieves the Google News link for a given link.
 *
 * @param string $link The original link.
 * @return string The Google News link.
 */
    public function get_google_news_link($link)
    {

        global $wpdb;

        //check if the link is already cached
        $md5 = md5($link);
        $cache = $wpdb->get_row("select * from {$wpdb->prefix}automatic_links where link_url = '$md5' ");

        if (isset($cache->link_url) > 0) {
            //found in cache
            $link = $cache->link_title;
            echo ' <span style="color:blue">[cached]</span>';
        } else {
            //not found in cache
            $url = $link;

            try {

                $newUrl = $this->decode_google_news_link($url);

                if (wp_automatic_trim($newUrl) != '') {
                    $link = $url = $newUrl;

                    echo ' <span style="color:green">[new]</span>';

                    //cache the result
                    $wpdb->insert("{$wpdb->prefix}automatic_links", array(
                        'link_url' => $md5,
                        'link_title' => $link,
                    ));

                }

            } catch (Exception $e) {

                throw new Exception('Error in google news link extractor: ' . $e->getMessage());

            }

        }

        return $link;

    }

    //get_google_news_link v2
    //do a google custom search using
    //site:colorado.edu "Workshop taps into sports to energize history, social studies education - University of Colorado Boulder"
    public function get_google_news_link_v2($title, $source)
    {

        //remove https://, http://, www. from the source
        $source = preg_replace('/(https:\/\/|http:\/\/|www.)/', '', $source);

        //build query
        $query = 'site:' . $source . ' "' . $title . '"';

        echo '<br> - Google search query: ' . $query;

        //call custom_search_api
        try
        {
            $results = $this->custom_search_api($query);

        } catch (Exception $e) {
            throw new Exception('Error in google news link extractor: ' . $e->getMessage());
        }

        print_r($results);
        exit;

        //extract the first link
        if (isset($results['items'][0]['link']) && wp_automatic_trim($results['items'][0]['link']) != '') {
            return $results['items'][0]['link'];
        } else {
            throw new Exception('Could not find the link in google search results');
        }

    }

    /**
     * Function takes the google news link and returns the original link
     * @param string $url
     * @return string $url
     * @example https://news.google.com/rss/articles/CBMipgFBVV95cUxNM3VkRXF1TDFrOWVtWHRTb1lFTUFPQTljY2dtb1ZlVmRyTjBDRmNYcVgtSzRNTjJpZy1oMU5aZXlPYnA3aHhObGM0TE5YZXJ5Zzh4Z1ZDTFlpdTlYVWRNUk5uVko2d1RHXzlwMWtlM0NobWJfOFI5WjJyWG14SHhEMFV2VHU3UzhuTk1UTmVyRDU5czFVTDRDcjhBS0pfTlRDUlR5azRR?oc=5
     * @example old format CBMiV2h0dHBzOi8vd3d3LnBva2VybmV3cy5jb20vc3RyYXRlZ3kvaG9sZC1lbS13aXRoLWhvbGxvd2F5LXZvbC03Ny1qb3NlcGgtY2hlb25nLTMxODU4Lmh0bdIBAA
     */

    public function decode_google_news_link($url)
    {

        echo '<br>Decoding google news link: ' . $url;

        //url is on form https://news.google.com/rss/articles/CBMipgFBVV95cUxNM3VkRXF1TDFrOWVtWHRTb1lFTUFPQTljY2dtb1ZlVmRyTjBDRmNYcVgtSzRNTjJpZy1oMU5aZXlPYnA3aHhObGM0TE5YZXJ5Zzh4Z1ZDTFlpdTlYVWRNUk5uVko2d1RHXzlwMWtlM0NobWJfOFI5WjJyWG14SHhEMFV2VHU3UzhuTk1UTmVyRDU5czFVTDRDcjhBS0pfTlRDUlR5azRR?oc=5
        //get the last part after the last / and before the ?
        //remove ?.* from the end
        $link = explode('?', $url)[0];

        //get the last part after the last /articles/
        $base64_part = preg_match('/\/articles\/(.*?)$/', $link, $matches);

        if (isset($matches[1]) && wp_automatic_trim($matches[1]) != '') {
            $base64_part = $matches[1];
        } else {
            throw new Exception('Could not extract the base64 part');
        }

        //test for old format
        //$base64_part = 'CBMiV2h0dHBzOi8vd3d3LnBva2VybmV3cy5jb20vc3RyYXRlZ3kvaG9sZC1lbS13aXRoLWhvbGxvd2F5LXZvbC03Ny1qb3NlcGgtY2hlb25nLTMxODU4Lmh0bdIBAA';

        //decode the base64 part
        //example CBMipgFBVV95cUxNM3VkRXF1TDFrOWVtWHRTb1lFTUFPQTljY2dtb1ZlVmRyTjBDRmNYcVgtSzRNTjJpZy1oMU5aZXlPYnA3aHhObGM0TE5YZXJ5Zzh4Z1ZDTFlpdTlYVWRNUk5uVko2d1RHXzlwMWtlM0NobWJfOFI5WjJyWG14SHhEMFV2VHU3UzhuTk1UTmVyRDU5czFVTDRDcjhBS0pfTlRDUlR5azRR
        $decoded = base64_decode($base64_part);

        //if contains http then return it
        if (stristr($decoded, 'http://') || stristr($decoded, 'https://')) {

            //remove any string before http using regex /^.*http/
            $decoded = preg_replace('/^.*http/', 'http', $decoded);

            // remove \xd2\x01\x00
            $decoded = preg_replace('{\\xd2\\x01\\x00}', '', $decoded);

            //trim
            $decoded = wp_automatic_trim($decoded);

            return $decoded;
        }

        //now it does not contain http, check if new format containing AU_y in the decoded string
        if (stristr($decoded, 'AU_y')) {
            //new format

            try {
                $decoded = $this->decode_google_news_link_remotely($base64_part);

                if (wp_automatic_trim($decoded) != '') {
                    return $decoded;
                }

            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

        }

        throw new Exception('Could not extract the original link reached end of the process with no result');

    }

    /**
     * Google changed on 1 SEP and this function no more works as expected
     * Decodes the Google News link remotely.
     *
     * @param int $id The ID of the Google News link.
     * @return void
     */
    public function decode_google_news_link_remotely($id)
    {

        echo '<br>Decoding google news link remotely: ' . $id;

        //get decoding parameters
        try
        {
            $decoding_params = $this->get_decoding_params($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $articles = array($decoding_params);

        //now we have the decoding parameters
        $articles_reqs = [];

        foreach ($articles as $art) {
            $articles_reqs[] = [
                "Fbv4je",
                '["garturlreq",[["X","X",["X","X"],null,null,1,1,"US:en",null,1,null,null,null,null,null,0,1],"X","X",1,[1,1,1],1,1,null,0,0,null,0],"' . $art["gn_art_id"] . '",' . $art["timestamp"] . ',"' . $art["signature"] . '"]',
            ];
        }

        $payload = 'f.req=' . urlencode(json_encode([$articles_reqs]));

       

        // Initialize cURL session for POST request
        curl_setopt($this->ch, CURLOPT_URL, "https://news.google.com/_/DotsSplashUi/data/batchexecute");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded;charset=UTF-8"]);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $payload);

       

        $response = curl_exec($this->ch);

        //response code
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        //echo code
        echo '<br>HTTP Code: ' . $httpcode;

        //if 302 and redirect url contains sorry then throw exception
        if ($httpcode == 302) {
            $redirect_url = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL);

            if (stristr($redirect_url, 'sorry')) {
                throw new Exception('Could not extract the original link remotely, Google showed a captcha and unusual traffic detected');
            }
        }

        //code 429, ask the user to use proxies on the plugin settings page and enable using them on the campaign 
        if ($httpcode == 429) {
            throw new Exception('Could not extract the original link remotely, Google returned 429, please use private proxies on the plugin settings page and enable using them on the campaign');
        }

        //example input CBMipAFBVV95cUxQSHlIaUM0cEVIRV9UdUtoY3d5a2Z4QXJpSXdBWmFydnR1SC1SU0N2T1JVU01PeXRid1ZCRW1vMnEyWHU5aTB6QzZTalAzdTE4UG5ndTBhd256Tzc1U3E2RHVtdlY1aDAyR21HOXlEOW5EeDNOYnl0bHV5MjJoaXhmQ1oyRkYyd3lsY2hKYl95dTFoN2NfdlVRZHJ5ZG9rXzNOYjY2WA
        //response example https://pastebin.com/e9p644C4
        

        //if does not contain garturlres, trow exception
        if (!stristr($response, 'garturlres')) {
            throw new Exception('Could not extract the original link remotely, response does not contain expected data garturlres');
        }

        //strip slashes
        $response = stripslashes($response);

        //send api call to the api
        try {
            $newUrl = $this->api_call('googleNewsLinkExtractor', array('googleNewsLinkExec' => $response));

            if (wp_automatic_trim($newUrl) != '') {

                return $newUrl;

            }

        } catch (Exception $e) {

            throw new Exception($e->getMessage());

        }

        throw new Exception('Could not extract the original link remotely, reached end of the process with no result');

    }

    /**
     * Retrieves the decoding parameters for a given article ID.
     *
     * @param int $gn_art_id The ID of the article.
     * @return mixed The decoding parameters for the article.
     */
    public function get_decoding_params($gn_art_id)
    {
        $url = "https://news.google.com/articles/" . $gn_art_id;

        $url ="https://news.google.com/rss/articles/".$gn_art_id;

        //fix for issue #34
        //if not proxies, reset the curl handle 
        if (! $this->isProxified) {
            echo '<br>Resetting curl handle';
            $this->ch = curl_init();
        }
          
        
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        //useragent 
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');

        // follow redirects
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($this->ch);
 

        if (curl_errno($this->ch)) {
            throw new Exception('Failed to retrieve article page: ' . curl_error($this->ch));
        }

        //code
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        //if not 200 throw exception
        if ($httpcode != 200) {
            throw new Exception('Failed to retrieve article page: HTTP code ' . $httpcode);
        }

        //if response is empty throw exception
        if (wp_automatic_trim($response) == '') {
            throw new Exception('Failed to retrieve article page: empty response');
        }

        

        // Load HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXPath($dom);
        $div = $xpath->query("//c-wiz/div")->item(0);

        if (!$div) {
            throw new Exception('Failed to extract decoding parameters: div not found');
        }

        return [
            "signature" => $div->getAttribute("data-n-a-sg"),
            "timestamp" => $div->getAttribute("data-n-a-ts"),
            "gn_art_id" => $gn_art_id,
        ];
    }

}
