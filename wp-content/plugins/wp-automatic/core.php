<?php

// spintax
require_once 'inc/class.spintax.php';

// youtube
require_once 'inc/youtube_class.php';

/*
 * ---* Auto Link Builder Class ---
 */
class wp_automatic
{

    // Class Variables
    public $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36';
    public $agent_mobile = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7';
    public $ch = '';
    public $db = '';
    public $spintax = '';
    public $plugin_url = '';
    public $wp_prefix = '';
    public $used_keyword = '';
    public $used_link = '';
    public $used_tags = '';
    public $duplicate_id = '';
    public $cached_file_path = '';
    public $minimum_post_timestamp = '';
    public $minimum_post_timestamp_camp = '';
    public $debug = false;
    public $translationSuccess;
    public $currentCampID;

    // Excluded links cache
    public $campExcludedLinks; // excluded links of the currecnt campaign comma separated
    public $campExcludedLinksFetched; // true if the excluded links fetched from the database

    // Duplicated links cache
    public $campOldDuplicateLinks; // duplicate links found from last run
    public $campOldDuplicateLinksFetched; // loaded or not?
    public $campNewDuplicateLinks; // new checked and found duplicate links
    public $campDuplicateLinksUpdate; // update them or not flag

    // Call limit
    public $sourceCallLimit; // number of times allowed to call the source if reached exists
    public $sourceCallTimes; // number of times the source was called

    // Link sufix
    public $isLinkSuffixed;

    // link once
    public $isLinkOnce;

    // proxy connected or not
    public $isProxified;

    // general banned words
    public $generalBannedWords;

    // if amazon location was simulated
    public $isAmazonLocationSimulated;
    public $soundCloudAPIKey = '';
    public $is_cookie_loaded = false;
    public $loaded_cookie_name = '';
    public $camp_opt = array();
    public $camp_general = array();

    // public variable to flag if there were any openai failed calls
    public $openaiFailed = false;

    // flag for full content extraction success or fail defaults to true
    public $fullContentSuccess = true;

    // currentSourceLink holds the currenly being processed source link: used for prompt caching to cache the prompt for each source post and also clean it when imported successfully
    public $currentSourceLink = '';

    //returned_pixabay_images holds the images returned from pixabay
    public $returned_pixabay_images = 0;

    //Specific extraction to a custom field, excerpt, tags or custom taxonomy found fields to set
    //will be used by the search and replace function to replace the found fields
    public $customFieldsFound = array();

    /*
     * ---* Class Constructor ---
     */
    public function __construct()
    {
        // plugin url
        $siteurl = get_bloginfo('url');
        $this->plugin_url = $siteurl . '/wp-content/plugins/alb/';

        // debug
        if (isset($_GET['debug'])) {
            $this->debug = true;
        }

        // db
        global $wpdb;
        $this->db = $wpdb;
        $this->wp_prefix = $wpdb->prefix;
        // $this->db->show_errors();
        @$this->db->query("set session wait_timeout=28800");

        // curl
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($this->ch, CURLOPT_REFERER, 'http://www.bing.com/');

        // user agent
        $this->reset_user_agent();

        //curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2');

        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 20); // Good leeway for redirections.
        @curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

        // cooke jar to save cookies, without a cookie jar, cURL will not remember any cookie set and will never send a cookie saved
        $cjname = $this->cookieJarName();

        @curl_setopt($this->ch, CURLOPT_COOKIEJAR, wp_automatic_str_replace('core.php', $cjname, __FILE__));
        @curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cjname);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        // verbose
        $verbose_eanbled = false;

        if ($verbose_eanbled) {
            $verbose = fopen(wp_automatic_str_replace('core.php', 'verbose.txt', __FILE__), 'w');
            curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
            curl_setopt($this->ch, CURLOPT_STDERR, $verbose);
        }

        // spintax
        $this->spintax = new Spintax();

        // Ini excluded links
        $this->campExcludedLinksFetched = false;
        $this->campOldDuplicateLinksFetched = false;
        $this->campDuplicateLinksUpdate = false;
        $this->campNewDuplicateLinks = array();

        // Link suffix
        $this->isLinkSuffixed = false;

        // Link once
        $this->isLinkOnce = false;

        // Call Limit
        $this->sourceCallLimit = 2;
        $this->sourceCallTimes = 0;

        // proxified
        $this->isProxified = false;

        // wp_automatic_ccc_stop
        $this->generalBannedWords = get_option('wp_automatic_ccc_stop', '');

        $this->isAmazonLocationSimulated = false;
    }

    /*
     * ---* Function Process Campaigns ---
     */
    public function process_campaigns($cid = false)
    {

        // DB prefix
        $prefix = $this->db->prefix;

        // Single or all check
        if (wp_automatic_trim($cid) == '') {

            // All campaings
            $last = get_option('gm_last_processed', 0);

            // get all the campaigns from the db lower than the last processed
            $query = "SELECT * FROM {$this->wp_prefix}automatic_camps  where camp_id < $last ORDER BY camp_id DESC";
            $camps = $this->db->get_results($query);

            // check if results returned with id less than the last processed or not if not using regular method
            $query = "SELECT * FROM {$this->wp_prefix}automatic_camps WHERE  camp_id >= $last ORDER BY camp_id DESC";
            $camps2 = $this->db->get_results($query);

            // merging 2 arrays
            $camps = array_merge($camps, $camps2);
        } else {

            // Single campaign process
            $query = "SELECT * FROM {$this->wp_prefix}automatic_camps  where camp_id = $cid ORDER BY camp_id DESC";
            $camps = $this->db->get_results($query);
        }

        // check if need to process camaigns or skip
        if (count($camps) == 0) {
            echo '<br>No valid campaigns to process ';
            return;
        } else {
            if (wp_automatic_trim($cid) == '') {
                echo '<br>DB contains ' . count($camps) . ' campaigns<br>';
            }

        }

        // now processing each fetched campaign
        $i = 0;
        foreach ($camps as $campaign) {

            // reading post status
            $status = get_post_status($campaign->camp_id);

            // if published process
            if ($status == 'publish') {
                if ($i != 0) {
                    echo '<br>';
                }

                echo "<b>Processing Campaign</b> $campaign->camp_name {  $campaign->camp_id  }";

                // updating the last id processed
                update_option('gm_last_processed', $campaign->camp_id);

                // check if deserve spinning now or not
                if (wp_automatic_trim($cid) == false) {

                    // read post every x minutes
                    if (stristr($campaign->camp_general, 'a:')) {
                        $campaign->camp_general = base64_encode($campaign->camp_general);
                    }

                    $camp_general = unserialize(base64_decode($campaign->camp_general));
                    $camp_general = array_map('wp_automatic_stripslashes', $camp_general);

                    if (!is_array($camp_general) || !isset($camp_general['cg_update_every'])) {
                        $camp_general = array(
                            'cg_update_every' => 60,
                            'cg_update_unit' => 1,
                        );
                    }

                    $post_every = $camp_general['cg_update_every'] * $camp_general['cg_update_unit'];

                    echo '<br>Campaign scheduled to process every ' . $post_every . ' minutes ';

                    // get last check time
                    $last_update = get_post_meta($campaign->camp_id, 'last_update', 1);
                    if (wp_automatic_trim($last_update) == '') {
                        $last_update = 1388692276;
                    }

                    // echo '<br>Last updated stamp '.$last_update;

                    $difference = $this->get_time_difference($last_update, time());

                    echo '<br> last processing was <strong>' . $difference . '</strong> minutes ago ';

                    if ($difference > $post_every) {
                        echo '<br>Campaign passed the time and eligible to be processed';
                        update_post_meta($campaign->camp_id, 'last_update', time());

                        $this->log('<strong>Cron</strong> >> eligible waiting campaign', $campaign->camp_name . '{' . $campaign->camp_id . '} last processing was <strong>' . $difference . '</strong> minutes ago ');

                        // process
                        $this->log('<strong>Cron</strong> >> Processing Campaign:' . $campaign->camp_id, $campaign->camp_name . '{' . $campaign->camp_id . '}');
                        $this->process_campaign($campaign);
                    } else {
                        echo '<br>Campaign still not passed ' . $post_every . ' minutes';
                    }
                } else {

                    // No cron just regular call

                    // update last run
                    update_post_meta($campaign->camp_id, 'last_update', time());

                    // process
                    $this->log('<strong>User</strong> >> Processing Campaign:' . $campaign->camp_id, $campaign->camp_name . '{' . $campaign->camp_id . '}');
                    $this->process_campaign($campaign);
                }

                $i++;
            } elseif (!$status) {
                /*
             * commented starting from 3.51.2
             * // deleting Camp record
             * $query = "delete from {$this->wp_prefix}automatic_camps where camp_id= '$campaign->camp_id'";
             * $this->db->query ( $query );
             * // deleting matching records for keywords
             * $query = "delete from {$this->wp_prefix}automatic_keywords where keyword_camp ='$campaign->camp_id'";
             * $this->db->query ( $query );
             */
            } else {
                echo 'Campaign should be published firstly to run..';
            }
        }
    }

    /*
     * ---* Processing Single Campaign Function ---
     */
    public function process_campaign($camp)
    {

        // Ini get options
        $this->currentCampID = $camp->camp_id;

        //reset openai flag
        $this->openaiFailed = false;

        $camp_post_every = $camp->camp_post_every;
        $wp_automatic_tw = get_option('wp_automatic_tw', 400);
        $wp_automatic_options = get_option('wp_automatic_options', array());
        $camp_type = $camp->camp_type;
        $camp_post_custom_k = unserialize($camp->camp_post_custom_k);
        $camp_post_custom_v = unserialize($camp->camp_post_custom_v);

        //if is not an array, make it an empty array
        if (!is_array($camp_post_custom_k)) {
            $camp_post_custom_k = array();
        }

        if (!is_array($camp_post_custom_v)) {
            $camp_post_custom_v = array();
        }



        // camp general options
        if (stristr($camp->camp_general, 'a:')) {
            $camp->camp_general = base64_encode($camp->camp_general);
        }

        $camp_general = unserialize(base64_decode($camp->camp_general));
        @$camp_general = array_map('wp_automatic_stripslashes', $camp_general);
        $this->camp_general = $camp_general;

        // get the count of posted posts so far
        $key = 'Posted:' . $camp->camp_id;
        $query = "select count(id) as count from {$this->wp_prefix}automatic_log where action='$key'";
        $temp = $this->db->get_results($query);
        $temps = $temp[0];
        $posted = $temps->count;

        // if maximum reached skip
        if ($camp_post_every <= $posted) {
            echo '<br>The set maximum number of posts was reached. You have set this campaign to post a maximum of ' . $camp_post_every . ' posts.';
            $this->log('Cancel Campaign', 'campaign reached maximum number of posts');
            return false;
        }

        // campaign options
        $camp_opt = unserialize($camp->camp_options);

        if (!is_array($camp_opt)) {
            $camp_opt = array();
        }

        $this->camp_opt = $camp_opt;

        //if OPT_CUSTOM is not set, set custom post fields to empty
        if (!in_array('OPT_CUSTOM', $camp_opt)) {
            $camp_post_custom_k = array();
            $camp_post_custom_v = array();
        }

        // link suffix
        if (in_array('OPT_LINK_PREFIX', $camp_opt) || in_array('OPT_LINK_PREFIX_POLY', $camp_opt) || in_array('OPT_LINK_PREFIX_MIL', $camp_opt)) {
            $this->isLinkSuffixed = true;
        }

        // never post same link flag
        if (in_array('OPT_LINK_ONCE', $camp_opt)) {
            $this->isLinkOnce = true;
        }

        // reading keywords that need to be processed
        $rawKeywords = wp_automatic_trim($camp->camp_keywords);
        if (!stristr($rawKeywords, ',')) {

            $newLinesCount = substr_count($rawKeywords, "\n");

            if ($newLinesCount > 0) {
                $keywords = explode("\n", $rawKeywords);

                $rawKeywords = implode(',', $keywords);
                echo '<br>keywords suspected to be one per line adapting...';
            }
        }

        $keywords = explode(',', $rawKeywords);
        $keywords = array_filter($keywords);
        $keywords = array_map('trim', $keywords);

        // set minimum item date if exists
        if (in_array('OPT_YT_DATE', $camp_opt)) {

            // check if dynamic date
            if (in_array('OPT_YT_DATE_T', $camp_opt) && is_numeric(wp_automatic_trim($camp_general['cg_yt_dte_minutes']))) {

                $cg_yt_dte_minutes = wp_automatic_trim($camp_general['cg_yt_dte_minutes']);
                $current_time = time();

                $minimum_time = $current_time - $cg_yt_dte_minutes * 60;
                // echo '<br>Minimum timestamp:'.$minimum_time;
                $this->minimum_post_timestamp = $minimum_time;
            } else {
                $this->minimum_post_timestamp = strtotime($camp_general['cg_yt_dte_year'] . '-' . $camp_general['cg_yt_dte_month'] . '-' . $camp_general['cg_yt_dte_day'] . 'T00:00:00.000Z');
            }

            $this->minimum_post_timestamp_camp = $camp->camp_id;
        }

        // Rotate Keywords
        if (in_array('OPT_ROTATE', $camp_opt)) {
            echo '<br>Rotating Keywords Enabled';

            // last used keyword
            $last_keyword = get_post_meta($camp->camp_id, 'last_keyword', 1);

            if (!wp_automatic_trim($last_keyword) == '') {
                // found last keyword usage let's split
                echo '<br>Last Keyword: ' . $last_keyword;

                // add all keywords after the last keyword
                $add = false;
                foreach ($keywords as $current_keword) {
                    if ($add) {
                        // set add flag to add all coming keywords
                        $rotatedKeywords[] = $current_keword;
                    } elseif (wp_automatic_trim($current_keword) == wp_automatic_trim($last_keyword)) {
                        $add = true;
                    }
                }

                // add all keywords before the last keyword
                foreach ($keywords as $current_keword) {
                    $rotatedKeywords[] = $current_keword;
                    if (wp_automatic_trim($current_keword) == wp_automatic_trim($last_keyword)) {
                        break;
                    }

                }

                // set keywords to rotated keywords
                if (count($rotatedKeywords) != 0) {
                    $keywords = $rotatedKeywords;
                }

                $keywordsString = implode(',', $rotatedKeywords);
                $camp->camp_keywords = $keywordsString;
            }
        } else {
            $camp->camp_keywords = implode(',', $keywords);
        }

        // Rotate feeds
        if (in_array('OPT_ROTATE_FEEDS', $camp_opt)) {
            echo '<br>Rotating feeds Enabled';

            // last used feed
            $last_feed = get_post_meta($camp->camp_id, 'last_feed', 1);

            if (!wp_automatic_trim($last_feed) == '') {
                // found last feed usage let's split
                echo '<br>Last feed: ' . $last_feed;

                // add all feeds after the last feed
                $add = false;
                $feeds = explode("\n", $camp->feeds);
                $feeds = array_filter($feeds);

                foreach ($feeds as $current_feed) {

                    if ($add) {
                        // set add flag to add all coming feeds
                        $rotatedfeeds[] = $current_feed;
                    } elseif (wp_automatic_trim($current_feed) == wp_automatic_trim($last_feed)) {
                        $add = true;
                    }
                }

                // add all feeds before the last feed
                foreach ($feeds as $current_feed) {
                    $rotatedfeeds[] = $current_feed;
                    if (wp_automatic_trim($current_feed) == wp_automatic_trim($last_feed)) {
                        break;
                    }

                }

                // set feeds to rotated feeds
                if (count($rotatedfeeds) != 0) {
                    $feeds = $rotatedfeeds;
                }

                $feedsString = implode("\n", $rotatedfeeds);
                $camp->feeds = $feedsString;
            }
        }

        $post_content = stripslashes($camp->camp_post_content);
        $post_title = stripslashes($camp->camp_post_title);

        if (in_array('OPT_USE_PROXY', $camp_opt) && $camp_type != 'Articles' && $camp_type != 'ArticlesBase') {
            $this->fire_proxy();
        }

        // ini content
        $abcont = '';
        $title = ''; // ini

        if ($camp_type == 'gpt3') {

            $article = $this->gpt3_get_post($camp);

            if (isset($article['item_content'])) {

                $abcont = $article['item_content'];
                $title = $article['item_title'];
                $source_link = $article['item_url'];

            }

            $img = $article;

        } elseif ($camp_type == 'Articles') {

            // proxyfy
            $this->fire_proxy();

            $article = $this->articlebase_get_post($camp);
            $abcont = $article['cont'];
            $title = $article['title'];
            $source_link = $article['source_link'];
            $img = $article;
        } elseif ($camp_type == 'ArticlesBase') {

            // proxyfy
            $this->fire_proxy();

            $article = $this->articlebase_get_post($camp);
            $abcont = $article['cont'];
            $title = $article['title'];
            $source_link = $article['source_link'];
            $img = $article;
        } elseif ($camp_type == 'Feeds') {
            // feeds posting
            echo '<br>Should get content from feeds';

            $article = $this->feeds_get_post($camp);

            if (isset($article['title'])) {
                $abcont = $article['cont'];
                $title = $article['title'];
                $source_link = $article['source_link'];
            }
            $img = $article;
        } elseif ($camp_type == 'Amazon') {
            echo '<br>Trying to post a new Amazon product...';
            $product = $this->amazon_get_post($camp);

            // update offer url to add to chart

            if (in_array('OPT_LINK_CHART', $camp_opt)) {

                $product['product_link'] = $product['chart_url'];
            }

            $img = $product;

            $abcont = @$product['offer_desc'];
            $title = @$product['offer_title'];
            $source_link = @$product['source_link'];
            $product_img = @$product['offer_img'];
            $product_price = @$product['offer_price'];
        } elseif ($camp_type == 'Clickbank') {

            echo '<br>Clickbank product is required';
            $img = $product = $this->clickbank_get_post($camp);
            $abcont = $product['offer_desc'];
            $title = $product['title'];
            $source_link = $product['offer_link'];
            $product_img = $product['img'];
            $product_original_link = $product['original_link'];

            // print_r($product);
        } elseif ($camp_type == 'Youtube') {

            $_SERVER['REQUEST_SCHEME'] = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';

            // refer restrictions
            curl_setopt($this->ch, CURLOPT_REFERER, $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']);

            echo '<br>Youtube Vid is required';
            $img = $vid = $this->youtube_get_post($camp);

            if (isset($img['vid_title'])) {
                $abcont = $vid['vid_desc'];
                $original_title = $vid['vid_title'];
                $title = $vid['vid_title'];
                $source_link = $vid['vid_url'];
            }
        } elseif ($camp_type == 'Vimeo') {

            echo '<br>Vimeo campaign let\'s get vimeo vid';

            $img = $vid = $this->vimeo_get_post($camp);

            // set player width and hieght
            if (isset($vid['vid_title'])) {
                $abcont = $vid['vid_description'];
                $original_title = $vid['vid_title'];
                $title = $vid['vid_title'];
                $source_link = $vid['vid_url'];
            }
        } elseif ($camp_type == 'Flicker') {
            echo '<br>Flicker image is required';
            $img = $this->flicker_get_post($camp);

            if (isset($img['img_title'])) {
                $abcont = $img['img_description'];
                $original_title = $img['img_title'];
                $title = $img['img_title'];
                $source_link = $img['img_link'];
            }
        } elseif ($camp_type == 'eBay') {
            echo '<br>eBay item is required';
            $img = $this->ebay_get_post($camp);

            if (isset($img['item_title'])) {
                $abcont = $img['item_desc'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_link'];
            }

            // affiliate link
            if (isset($img['item_affiliate_link']) && wp_automatic_trim($img['item_affiliate_link']) != '') {
                $img['item_link'] = $img['item_affiliate_link'];
            }

        } elseif ($camp_type == 'Spintax') {

            echo '<p>Processing spintax campaign';

            $abconts = $post_title . '(99999)' . $post_content;

            if (in_array('OPT_TBS', $camp_opt)) {
                $abconts = $this->spin($abconts);
            }

            if (in_array('OPT_SP_SIMILAR', $camp_opt)) {
                $abconts = $this->spintax->spin($abconts, true);
            } else {
                $abconts = $this->spintax->spin($abconts);
            }

            $tempz = explode('(99999)', $abconts);

            // Rewrite the title
            if (!in_array('OPT_TBS_TTL', $camp_opt)) {
                echo '<br>Spinning the title';
                $post_title = $tempz[0];
            }

            $post_content = $tempz[1];
            $title = wp_automatic_trim($post_title);
            $img = array();
        } elseif ($camp_type == 'Facebook') {

            $img = $this->fb_get_post($camp);

            if (isset($img['original_title'])) {
                $abcont = @$img['matched_content'];
                $original_title = @$img['original_title'];
                $title = @$img['original_title'];
                $source_link = @$img['original_link'];
            }
        } elseif ($camp_type == 'Pinterest') {

            $img = $this->pinterest_get_post($camp);

            if (isset($img['pin_title'])) {
                $abcont = $img['pin_description'];
                $original_title = $img['pin_title'];
                $title = $img['pin_title'];
                $source_link = $img['pin_url'];
            }
        } elseif ($camp_type == 'Instagram') {

            $img = $this->instagram_get_post($camp);

            if (isset($img['item_title'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }
        } elseif ($camp_type == 'Aliexpress') {

            $img = $this->aliexpress_get_post($camp);

            $title = '';
            if (isset($img['item_title'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }
        } elseif ($camp_type == 'TikTok') {

            $img = $this->tiktok_get_post($camp);

            $title = '';
            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }
        } elseif ($camp_type == 'Twitter') {

            $img = $this->twitter_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }
        } elseif ($camp_type == 'SoundCloud') {

            $img = $this->sound_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }
        } elseif ($camp_type == 'Craigslist') {

            $img = $this->craigslist_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_link'];
            }
        } elseif ($camp_type == 'Reddit') {

            $img = $this->reddit_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }

        } elseif ($camp_type == 'telegram') {

            $img = $this->telegram_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }

        } elseif ($camp_type == 'Rumble') {

            $img = $this->rumble_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }

        } elseif ($camp_type == 'Places') {

            $img = $this->places_get_post($camp);

            if (isset($img['item_title'])) {
                $abcont = $img['item_title'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }

        } elseif ($camp_type == 'Careerjet') {

            $img = $this->careerjet_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_url'];
            }
        } elseif ($camp_type == 'Itunes') {
            $img = $this->itunes_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_link'];
            }
        } elseif ($camp_type == 'Envato') {

            $img = $this->envato_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_link'];
            }
        } elseif ($camp_type == 'DailyMotion') {

            $img = $this->DailyMotion_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_link'];
            }
        } elseif ($camp_type == 'Walmart') {

            $img = $this->walmart_get_post($camp);

            if (isset($img['item_description'])) {
                $abcont = $img['item_description'];
                $original_title = $img['item_title'];
                $title = $img['item_title'];
                $source_link = $img['item_link'];
            }
        } elseif ($camp_type == 'Single') {

            $img = $this->single_get_post($camp);

            if (isset($img['original_title'])) {
                $abcont = $img['matched_content'];
                $original_title = $img['original_title'];
                $title = $img['original_title'];
                $source_link = $img['source_link'];
            }
        }

        // set currently being processed source link
        if (isset($source_link)) {
            $this->currentSourceLink = $source_link;
        }

        // add a now time to img array
        //fix utomatic conversion of false to array is deprecated
        if (!isset($img) || !is_array($img)) {
            $img = array();
        }

        $img['now'] = time();

        // default tags fill
        if (in_array('OPT_DEFAULT_TAGS', $camp_opt)) {
            $cg_default_tags = $camp_general['cg_default_tags'];
            $cg_default_tags_arr = array_filter(explode("\n", $cg_default_tags));

            foreach ($cg_default_tags_arr as $cg_default_tags_single) {

                if (wp_automatic_trim($cg_default_tags_single) != '' && stristr($cg_default_tags_single, '|')) {

                    $cg_default_tags_single_parts = explode('|', $cg_default_tags_single);
                    $cg_default_tags_single_key = $cg_default_tags_single_parts[0];

                    if (!isset($img[$cg_default_tags_single_key]) || wp_automatic_trim($img[$cg_default_tags_single_key]) == '') {
                        $img[$cg_default_tags_single_key] = $cg_default_tags_single_parts[1];
                    }
                }
            }
        }

        // adjust tags OPT_DEFAULT_TAGS
        if (in_array('OPT_ADJUST_TAGS', $camp_opt)) {
            $cg_adjust_tags = $camp_general['cg_adjust_tags'];
            $cg_adjust_tags_arr = array_filter(explode("\n", $cg_adjust_tags));

            foreach ($cg_adjust_tags_arr as $cg_adjust_tags_single) {

                echo '<br>Processing numeric adjustment rule: ' . $cg_adjust_tags_single;

                if (wp_automatic_trim($cg_adjust_tags_single) != '' && stristr($cg_adjust_tags_single, '|')) {

                    $cg_adjust_tags_single_parts = explode('|', $cg_adjust_tags_single);
                    $cg_adjust_tags_single_key = $cg_adjust_tags_single_parts[0];

                    // adjust
                    echo '<br> - Adjusting tag named:' . $cg_adjust_tags_single_key;

                    if (isset($img[$cg_adjust_tags_single_key])) {

                        // get the value to adjust
                        $valueToAdjust = $img[$cg_adjust_tags_single_key];

                        // strip tags from value
                        $valueToAdjust = strip_tags($valueToAdjust);

                        //remove any commas or spaces
                        $valueToAdjust = wp_automatic_str_replace(',', '', $valueToAdjust);
                        $valueToAdjust = wp_automatic_str_replace(' ', '', $valueToAdjust);

                        echo '<br> - Value to adjust:' . wp_automatic_htmlentities($valueToAdjust);

                        // if the value is not numeric, extract the numeric part and use it
                        if (!is_numeric($valueToAdjust)) {

                            // extract the numeric part
                            $numericPart = preg_replace('/[^0-9.]+/', '', $valueToAdjust);

                            echo '<br> - Extracted numeric part:' . wp_automatic_htmlentities($numericPart);

                            // if the numeric part is numeric, use it
                            if (is_numeric($numericPart)) {
                                $valueToAdjust = $numericPart;
                            }

                        }

                        if (is_numeric($valueToAdjust)) {

                            // set the value to adjust
                            $img[$cg_adjust_tags_single_key] = $valueToAdjust;

                            // get the adjustment rule
                            $adjust_rule = $cg_adjust_tags_single_parts[1];

                            if (stristr($adjust_rule, '*')) {
                                $adjust_rule_arr = explode('*', $adjust_rule);

                                if (wp_automatic_trim($adjust_rule_arr[0]) == $cg_adjust_tags_single_key && is_numeric(wp_automatic_trim($adjust_rule_arr[1]))) {
                                    $img[$cg_adjust_tags_single_key] = $img[$cg_adjust_tags_single_key] * wp_automatic_trim($adjust_rule_arr[1]);
                                    echo '<--adjusted';
                                } else {
                                    echo '<-- Invalid format';
                                }
                            } elseif (stristr($adjust_rule, '+')) {
                                $adjust_rule_arr = explode('+', $adjust_rule);

                                if (wp_automatic_trim($adjust_rule_arr[0]) == $cg_adjust_tags_single_key && is_numeric(wp_automatic_trim($adjust_rule_arr[1]))) {
                                    $img[$cg_adjust_tags_single_key] = $img[$cg_adjust_tags_single_key] + wp_automatic_trim($adjust_rule_arr[1]);
                                    echo '<--adjusted';
                                } else {
                                    echo '<-- Invalid format';
                                }
                            } elseif (stristr($adjust_rule, '-')) {
                                $adjust_rule_arr = explode('-', $adjust_rule);

                                if (wp_automatic_trim($adjust_rule_arr[0]) == $cg_adjust_tags_single_key && is_numeric(wp_automatic_trim($adjust_rule_arr[1]))) {
                                    $img[$cg_adjust_tags_single_key] = $img[$cg_adjust_tags_single_key] - wp_automatic_trim($adjust_rule_arr[1]);
                                    echo '<--adjusted';
                                } else {
                                    echo '<-- Invalid format';
                                }
                            }
                        } else {
                            echo '<-- Tag value is not numeric, skipping';
                        }
                    } else {
                        echo '<-- Tag not found, skipping';
                    }

                } else {
                    echo '<- Invalid format, skipping';
                }
            }
        }

        // source domain

        // featured_img_local_source ini
        $img['featured_img_local_source'] = '';
        $img['featured_img_source'] = '';

        // link suffix
        if ($this->isLinkSuffixed == true && isset($source_link)) {
            if (stristr($source_link, '?')) {
                $source_link = $source_link . '&rand=' . $this->currentCampID;
            } else {
                $source_link = $source_link . '?rand=' . $this->currentCampID;
            }
        }

        // limit the content returned
        if (in_array('OPT_LIMIT', $camp_opt)) {
            echo '<br>Triming post content to ' . $camp_general['cg_content_limit'] . ' chars';
            $abcont = $this->truncateHtml($abcont, $camp_general['cg_content_limit']);
        }

        if (in_array('OPT_LIMIT_TITLE', $camp_opt) && wp_automatic_trim($title) != '') {
            echo '<br>Triming post title to ' . $camp_general['cg_title_limit'] . ' chars';

            $titleCharsCount = $this->chars_count($title);

            if ($camp_general['cg_title_limit'] < $titleCharsCount) {

                $non_truncated_title = $title;

                if (function_exists('mb_substr')) {
                    $title = mb_substr($title, 0, $camp_general['cg_title_limit']);
                } else {
                    $title = substr($title, 0, $camp_general['cg_title_limit']);
                }

                $title = $this->removeEmoji($title);

                // remove last truncated word
                if (in_array('OPT_LIMIT_NO_TRUN', $camp_opt)) {

                    // get last truncated word
                    $truncated_title_parts = explode(' ', $title);
                    $last_truncated_word = $truncated_title_parts[count($truncated_title_parts) - 1];

                    // check if really truncated
                    $non_truncated_title_parts = explode(' ', $non_truncated_title);

                    foreach ($non_truncated_title_parts as $non_truncated_word) {

                        if ($non_truncated_word === $last_truncated_word) {

                            // last truncated word is not truncated
                            break;
                        }
                    }

                    if ($non_truncated_word === $last_truncated_word) {
                    } else {
                        unset($truncated_title_parts[count($truncated_title_parts) - 1]);
                        $title = implode(' ', $truncated_title_parts);
                    }
                }

                if (!in_array('OPT_LIMIT_NO_DOT', $camp_opt)) {
                    $title = $title . '...';
                }

            }
        }

        // check if valid content fetched before filling the template
        if (wp_automatic_trim($title) != '') {

            // Validate if the content contains wanted or execluded texts

            $valid = true;

            $exact = $camp->camp_post_exact;
            $execl = $camp->camp_post_execlude;
            $execr = '';
            $execr = @$camp_general['cg_camp_post_regex_exact'];
            $excludeRegex = @$camp_general['cg_camp_post_regex_exclude'];

            // Before filling the template, check banned and must exist words and length
            $valid = $this->validate_exacts($abcont, $title, $camp_opt, $camp, false, $camp_general);

            // If valid validate criteria
            if ($valid && in_array('OPT_CRITERIA', $camp_opt)) {
                $valid = $this->validate_criterias($img, $camp_opt, $camp, $camp_general);
            }

            // If valid validate must exact criteria
            if ($valid && in_array('OPT_CRITERIA_MUST', $camp_opt)) {
                $valid = $this->validate_criterias_must($img, $camp_opt, $camp, $camp_general);
            }

            // duplicate title check
            if ($valid == true) {
                // check if there is a post published with the same title
                if (in_array('OPT_FEED_TITLE_SKIP', $camp_opt)) {

                    $title_to_check = $title;

                    if ($title != '[original_title]') {
                        $title_to_check = @str_replace('[original_title]', strip_tags($title), $camp->camp_post_title);
                    }

                    if ($this->is_title_duplicate($title_to_check, $camp->camp_post_type)) {
                        echo '<-- duplicate title skipping..';
                        $valid = false;
                    }
                }
            }

            // if not valid process the campaign again and exit
            if ($valid == false) {

                // blacklisting the link so we don'g teg it again and cause a loop
                $this->link_execlude($camp->camp_id, $source_link);
                $this->process_campaign($camp);
                exit();
            }

            // strip links
            if (in_array('OPT_STRIP', $camp_opt)) {

                echo '<br>Striping links ';
                // $abcont = strip_tags ( $abcont, '<p><img><b><strong><br><iframe><embed><table><del><i><div>' );

                // domain
                $leave_external = false;

                if (in_array('OPT_STRIP_EXT', $camp_opt)) {
                    $leave_external = true;
                    $source_domain = (parse_url($source_link, PHP_URL_HOST));
                }

                if ($leave_external || strpos($abcont, 'twitter.com')) {
                    preg_match_all('{<a .*?>(.*?)</a>}s', $abcont, $allLinksMatchs);

                    $allLinksTexts = $allLinksMatchs[1];
                    $allLinksMatchs = $allLinksMatchs[0];

                    // print_r ( $allLinksMatchs );

                    $allLinksMatchs_sorted = $allLinksMatchs; // copy of the original links
                    usort(($allLinksMatchs_sorted), 'wp_automatic_sort');

                    $j = 0;
                    foreach ($allLinksMatchs_sorted as $singleLink) {

                        // index on the original non-sorted array
                        $original_key = array_search($singleLink, $allLinksMatchs);

                        $singleLink_no_images = preg_replace('{<img.*?>}', '', $singleLink);

                        if (!stristr($singleLink, 'twitter.com') && !($leave_external && !stristr($singleLink_no_images, $source_domain))) {
                            $abcont = wp_automatic_str_replace($singleLink, $allLinksTexts[$original_key], $abcont);
                        }

                        $j++;
                    }
                } else {

                    $abcont = preg_replace('{<a .*?>}s', '', $abcont);
                    $abcont = wp_automatic_str_replace(array(
                        '</a>',
                        '</ a>',
                        '< /a>',
                    ), '', $abcont);
                }

                if ($camp_type == 'Youtube') {
                    echo '...striping inline links';
                    $abcont = preg_replace('/https?:\/\/[^<\s]+/', '', $abcont);
                }

                // inline links removal
                if (in_array('OPT_STRIP_INLINE', $camp_opt)) {

                    echo '<br>Stripping inline links';
                    $abcont_no_html = preg_replace('{<.*?>}s', '', $abcont);
                    $abcont_no_html = preg_replace('{<.*?>}s', '', $abcont);
                    $abcont_no_html = strip_shortcodes($abcont_no_html);

                    // find links
                    preg_match_all('/https?:\/\/[^<\s]+/s', $abcont_no_html, $inline_matches);

                    $inline_matches = $inline_matches[0];

                    foreach ($inline_matches as $inline_matches_link) {

                        if (!stristr($inline_matches_link, '[')) {

                            echo '<br>Removing link:' . $inline_matches_link;
                            $abcont = wp_automatic_str_replace($inline_matches_link, '', $abcont);
                        }
                    }
                }
            }

            // links in new tab
            if (in_array('OPT_LNK_BLNK', $camp_opt)) {
                $abcont = wp_automatic_str_replace('<a ', '<a target="_blank" ', $abcont);
            }

            // nofollow attribute
            if (in_array('OPT_LNK_NOFOLLOW', $camp_opt)) {
                $abcont = wp_automatic_str_replace('<a ', '<a rel="nofollow" ', $abcont);
            }

            // translate the cotent
            $img['content_to_translate'] = '';
            if (in_array('OPT_TRANSLATE', $camp_opt) && wp_automatic_trim($abcont) != '' && $camp->camp_translate_from != '' && $camp->camp_translate_from != 'no' && $camp->camp_translate_to != '') {
                echo '<br>Translating the post...' . $title;

                $img['content_to_translate'] = $abcont;

                // to translate tags
                $tagsToTranslate = '';
                if (isset($img['tags']) && wp_automatic_trim($img['tags']) != '') {
                    $tagsToTranslate = wp_automatic_trim($img['tags']);
                }

                if (isset($img['custom_fields'])) {
                    foreach ($img['custom_fields'] as $customFieldArr) {
                        if ($customFieldArr[0] == 'tags') {
                            $tagsToTranslate = $customFieldArr[1];
                            break;
                        }
                    }
                }

                // YT tags
                if (wp_automatic_trim($this->used_tags) != '') {
                    $tagsToTranslate = $this->used_tags;
                }

                if (wp_automatic_trim($tagsToTranslate) != '') {

                    $tagsToTranslate = explode(',', $tagsToTranslate);
                    $tagsToTranslate = array_filter($tagsToTranslate);
                    $tagsToTranslate = array_map('trim', $tagsToTranslate);
                    $tagsToTranslate = implode('[t]', $tagsToTranslate);

                    $abcont = $abcont . '  [tagsToTranslate]' . $tagsToTranslate;
                }

                // Translation Method
                $translationMethod = $camp_general['cg_translate_method'];

                if ($translationMethod != 'googleTranslator' && $translationMethod != 'yandexTranslator' && $translationMethod != 'deeplTranslator') {
                    $translationMethod = 'microsoftTranslator';
                }

                // Translation success flag ini
                $this->translationSuccess = false;

                // fix translation wrong config en->no->fr
                if ($camp->camp_translate_from != 'no') {

                    // en->no->fr
                    if ($camp->camp_translate_to == 'no' && $camp->camp_translate_to_2 != 'no') {
                        $camp->camp_translate_to = $camp->camp_translate_to_2;
                        $camp->camp_translate_to_2 = 'no';
                    }
                }

                $translation = $this->gtranslate($title, $abcont, $camp->camp_translate_from, $camp->camp_translate_to, $translationMethod);

                if (in_array('OPT_TRANSLATE_TITLE', $camp_opt)) {
                    $title = $translation[0];
                }

                $abcont = $translation[1];

                // check if another translation needed
                if (wp_automatic_trim($camp->camp_translate_to_2) != 'no' && wp_automatic_trim($camp->camp_translate_to_2) != '') {
                    // another translate

                    echo '<br>translating the post another time ';
                    $translation = $this->gtranslate($title, $abcont, $camp->camp_translate_to, $camp->camp_translate_to_2, $translationMethod);

                    if (in_array('OPT_TRANSLATE_TITLE', $camp_opt)) {
                        $title = $translation[0];
                    }

                    $abcont = $translation[1];
                }

                // strip tagstotransate
                if (stristr($abcont, 'tagsToTranslate') || stristr($abcont, '(t)')) {

                    $abcont = wp_automatic_str_replace('(t )', '(t)', $abcont);
                    $abcont = wp_automatic_str_replace('(tagsToTranslate)', '[tagsToTranslate]', $abcont);
                    $abcont = wp_automatic_str_replace('(t)', '[t]', $abcont);

                    preg_match('{\[tagsToTranslate\](.*)}', $abcont, $tagMatchs);
                    $tagsTranslated = $tagMatchs[1];
                    $tagsTranslated = wp_automatic_str_replace('[t]', ',', $tagsTranslated);

                    // strip the tags
                    $abcont = preg_replace('{\[tagsToTranslate.*}', '', $abcont);

                    if (stristr($abcont, '[t]')) {

                        preg_match('{\[t\].*}', $abcont, $tagMatchs);
                        $tagsTranslated = $tagMatchs[0];
                        $tagsTranslated = explode('[t]', $tagsTranslated);
                        $tagsTranslated = implode(',', array_filter($tagsTranslated));

                        $abcont = preg_replace('{\[t\].*}', '', $abcont);
                    }

                    // restore tags
                    if (isset($img['tags']) && wp_automatic_trim($img['tags']) != '') {
                        $img['tags'] = $tagsTranslated;
                    } elseif (wp_automatic_trim($this->used_tags) != '') {
                        $this->used_tags = $tagsTranslated;
                    }

                    $newFields = array();
                    if (isset($img['custom_fields'])) {
                        foreach ($img['custom_fields'] as $customFieldArr) {
                            if ($customFieldArr[0] == 'tags') {
                                $newFields[] = array(
                                    'tags',
                                    $tagsTranslated,
                                );
                            } else {
                                $newFields[] = $customFieldArr;
                            }
                        }
                        $img['custom_fields'] = $newFields;
                    }
                }

                // translated values overwrite for custom field geneation
                $img['original_title'] = $title;
                $img['matched_content'] = $abcont;
            }

            // title words as hashtags
            if (stristr($camp->camp_post_content . $camp->camp_post_title, '[title_words_as_hashtags]')) {
                $separate_tags = $this->wp_automatic_generate_tags($title);

                $title_as_hash = '';
                foreach ($separate_tags as $separate_tag) {
                    $title_as_hash .= " #{$separate_tag}";
                }

                $img['title_words_as_hashtags'] = wp_automatic_trim($title_as_hash);
            }

            //generate a slug using wordpress function
            $title_as_slug = sanitize_title($title);
            $img['title_words_as_slug'] = wp_automatic_trim($title_as_slug);

            $post_slug = ''; // post slug init

            //if option enabled OPT_CUSTOM_SLUG enabled
            if (in_array('OPT_CUSTOM_SLUG', $camp_opt)) {
                $post_slug = $camp_general['cg_custom_slug'];
            }

            // replacing general terms title and source link
            if ($camp_type != 'Facebook') {
                if (isset($source_link)) {
                    $post_content = wp_automatic_str_replace('[source_link]', $source_link, $post_content);
                }

            }

            $post_title = @str_replace('[original_title]', strip_tags($title), $post_title);
            $post_content = wp_automatic_str_replace('[original_title]', $title, $post_content);
            $post_slug = wp_automatic_str_replace('[original_title]', $title, $post_slug);

            //add the origina title to the img array for custom fields section to have access to it
            $img['original_title'] = $title;

            if ($camp_type == 'Feeds' || $camp_type == 'Articles' || $camp_type == 'ArticlesBase' || $camp_type == 'gpt3') {

                $post_content = wp_automatic_str_replace('[matched_content]', $abcont, $post_content);

                //add the matched content to the img array for custom fields section to have access to it
                $img['matched_content'] = $abcont;

            } elseif ($camp_type == 'Amazon') {

                $post_content = wp_automatic_str_replace('[product_desc]', $abcont, $post_content);
                $post_content = wp_automatic_str_replace('[product_img]', $product_img, $post_content);
                // $post_content =wp_automatic_str_replace( '[product_link]', $source_link, $post_content );
                $post_content = wp_automatic_str_replace('[product_price]', $product_price, $post_content);

                // remove built-in gallery for amazon products when a woo gallery is used
                if ($camp->camp_post_type == 'product' && in_array('OPT_AM_GALLERY', $camp_opt)) {
                    $post_content = wp_automatic_str_replace('[product_imgs_html]', '', $post_content);
                }
            } elseif ($camp_type == 'Clickbank') {
                $post_content = wp_automatic_str_replace('[product_desc]', $abcont, $post_content);
                $post_content = wp_automatic_str_replace('[product_img]', $product_img, $post_content);
                //$post_content =wp_automatic_str_replace( '[product_link]', $source_link, $post_content );
                $post_content = wp_automatic_str_replace('[product_original_link]', $product_original_link, $post_content);
            } elseif ($camp_type == 'Youtube') {

                $post_content = wp_automatic_str_replace('[vid_player]', addslashes($vid['vid_player']), $post_content);
                $post_content = wp_automatic_str_replace('[vid_desc]', $abcont, $post_content);
                $post_content = wp_automatic_str_replace('[vid_views]', $vid['vid_views'], $post_content);
                $post_content = wp_automatic_str_replace('[vid_rating]', $vid['vid_rating'], $post_content);
                $post_content = wp_automatic_str_replace('[vid_img]', $vid['vid_img'], $post_content);
            } elseif ($camp_type == 'eBay') {

                $post_content = wp_automatic_str_replace('[item_desc]', $abcont, $post_content);

                // remove built-in gallery for amazon products when a woo gallery is used
                if ($camp->camp_post_type == 'product' && in_array('OPT_EB_GALLERY', $camp_opt) && is_array($img['item_images'])) {
                    $post_content = wp_automatic_str_replace('[item_images]', '', $post_content);
                } elseif (stristr($post_content, '[item_images]') && is_array($img['item_images'])) {

                    $cg_eb_full_img_t = html_entity_decode($camp_general['cg_eb_full_img_t']);

                    $imgs = $img['item_images'];

                    if (!stristr($cg_eb_full_img_t, '[img_src]')) {
                        $cg_eb_full_img_t = '<img src="[img_src]" class="wp_automatic_gallery" />';
                    }

                    $contimgs = '';
                    foreach ($imgs as $newimg) {
                        $tempimg = $cg_eb_full_img_t;
                        $contimgs .= wp_automatic_str_replace('[img_src]', $newimg, $tempimg);
                    }

                    $post_content = wp_automatic_str_replace('[item_images]', $contimgs, $post_content);

                    //overwrite item images array with the contimages to be subistituted in custom fields section ticket:23174
                    $img['item_images'] = $contimgs;

                }
            } elseif ($camp_type == 'Flicker') {

                $post_content = wp_automatic_str_replace('[img_description]', $abcont, $post_content);
            } elseif ($camp_type == 'Vimeo') {

                $post_content = wp_automatic_str_replace('[vid_description]', $abcont, $post_content);

                // set player width and height
                $vm_width = $camp_general['cg_vm_width'];
                $vm_height = $camp_general['cg_vm_height'];

                if (wp_automatic_trim($vm_width) != '') {
                    $img['vid_embed'] = $vid['vid_embed'] = wp_automatic_str_replace('width="560"', 'width="' . $vm_width . '"', $vid['vid_embed']);
                }

                if (wp_automatic_trim($vm_height) != '') {
                    $img['vid_embed'] = $vid['vid_embed'] = wp_automatic_str_replace('height="315"', 'height="' . $vm_height . '"', $vid['vid_embed']);
                }
            } elseif ($camp_type == 'Pinterest') {

                $post_content = wp_automatic_str_replace('[pin_description]', $abcont, $post_content);
            } elseif ($camp_type == 'Instagram') {

                $post_content = wp_automatic_str_replace('[item_description]', $abcont, $post_content);

                // if video hide it's image
                if (stristr($abcont, '[embed') && !in_array('OPT_IT_NO_VID_IMG_HIDE', $camp_opt)) {
                    echo '<br>Hiding vid image';
                    $post_content = wp_automatic_str_replace('[item_img]"', '[item_img]" style="display:none;" ', $post_content);
                    $post_content = wp_automatic_str_replace('[item_img]\"', '[item_img]\" style="display:none;" ', $post_content);
                }
            } elseif ($camp_type == 'Twitter') {

                $post_content = wp_automatic_str_replace('[item_description]', $abcont, $post_content);
            } elseif ($camp_type == 'TikTok') {

                $post_content = wp_automatic_str_replace('[item_description]', $abcont, $post_content);
            } elseif ($camp_type == 'Craigslist') {

                if ($camp->camp_post_type == 'product' && in_array('OPT_CL_GALLERY', $camp_opt)) {
                    $post_content = wp_automatic_str_replace('[item_imgs_html]', '', $post_content);
                }
            } elseif ($camp_type == 'Aliexpress') {

                // remove built-in gallery for amazon products when a woo gallery is used
                if ($camp->camp_post_type == 'product' && in_array('OPT_AE_GALLERY', $camp_opt)) {
                    $post_content = wp_automatic_str_replace('[item_imgs_html]', '', $post_content);
                }

                $post_content = wp_automatic_str_replace('[item_description]', $abcont, $post_content);
            } elseif ($camp_type == 'Facebook' || $camp_type == 'Single') {
                $post_content = wp_automatic_str_replace('[matched_content]', $abcont, $post_content);
            } elseif ($camp_type == 'SoundCloud' || $camp_type == 'Craigslist' || $camp_type == 'Itunes' || $camp_type == 'Envato' || $camp_type == 'DailyMotion' || $camp_type == 'Reddit' || $camp_type == 'Walmart' || $camp_type == 'Careerjet' || 'telegram') {
                $post_content = wp_automatic_str_replace('[item_description]', $abcont, $post_content);
            } else {
                $post_content .= "<br>$abcont";
            }

            // Replacing generic tags
            if (stristr($this->used_keyword, '_')) {
                $pan = explode('_', $this->used_keyword);
                $this->used_keyword = $pan[1];
            }

            // used keyword ini
            if (isset($this->used_keyword)) {
                $img['keyword'] = $this->used_keyword;
            }

            $post_content = wp_automatic_str_replace('[keyword]', $this->used_keyword, $post_content);
            $post_title = wp_automatic_str_replace('[keyword]', $this->used_keyword, $post_title);
            $post_slug = wp_automatic_str_replace('[keyword]', $this->used_keyword, $post_slug);

            // replacing attributes
            foreach ($img as $key => $val) {

                if (!is_array($val)) {
                    $post_content = wp_automatic_str_replace('[' . $key . ']', $val, $post_content);
                    $post_title = wp_automatic_str_replace('[' . $key . ']', $val, $post_title);
                    $post_slug = wp_automatic_str_replace('[' . $key . ']', $val, $post_slug);
                }
            }

            // openai gpt 3 tags replacement for content and title if available

            //snapshot of the content before replacement
            $post_content_before_openai_replacement = $post_content;

            $post_content = $this->openai_gpt3_tags_replacement($post_content);
            $post_title = $this->openai_gpt3_tags_replacement($post_title);

            

            //if title is wrapped in " then remove them
            if (substr($post_title, 0, 1) == '"' && substr($post_title, -1) == '"') {
                echo '<br>Title wrapped in " then remove them...';
                $post_title = substr($post_title, 1, -1);
            }

            // replacing custom attributes for feeds
            if ($camp_type == 'Feeds') {

                $attributes = $img['attributes'];

                foreach ($attributes as $attributeKey => $attributeValue) {

                    $post_content = wp_automatic_str_replace('[' . $attributeKey . ']', $attributeValue[0]['data'], $post_content);
                    $post_title = wp_automatic_str_replace('[' . $attributeKey . ']', $attributeValue[0]['data'], $post_title);
                }
            }

            // formated date
            if (stristr($post_content, 'formated_date') || stristr($post_title, 'formated_date')) {

                $tags = array(
                    'formated_date',
                );
                global $shortcode_tags;
                $_tags = $shortcode_tags; // store temp copy
                foreach ($_tags as $tag => $callback) {
                    if (!in_array($tag, $tags)) // filter unwanted shortcode
                    {
                        unset($shortcode_tags[$tag]);
                    }

                }

                $post_title = do_shortcode($post_title);
                $post_content = do_shortcode($post_content);

                $shortcode_tags = $_tags; // put all shortcode back
            }

            // replacing the keywords with affiliate links
            if (in_array('OPT_REPLACE', $camp_opt)) {
                foreach ($keywords as $keyword) {

                    $keyword = wp_automatic_trim($keyword);

                    if (wp_automatic_trim($keyword != '')) {
                        // $post_content =wp_automatic_str_replace( $keyword, '<a href="' . $camp->camp_replace_link . '">' . $keyword . '</a>', $post_content );

                        $post_content = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/', '<a href="' . $camp->camp_replace_link . '">' . $keyword . '</a>', $post_content);
                    }
                }
            }

            // replace keywords with specific links

            if (in_array('OPT_REPLACE_KEYWORD', $camp_opt)) {

                $cg_keywords_replace = $camp_general['cg_keywords_replace'];
                $cg_keywords_replace_arr = array_filter(explode("\n", $cg_keywords_replace));
                $cg_keywords_replace_all = array(); // init array for all keywords

                foreach ($cg_keywords_replace_arr as $cg_keywords_replace_rule) {

                    // validating rule
                    if (stristr($cg_keywords_replace_rule, '|')) {

                        $cg_keywords_replace_rule_parts = explode('|', $cg_keywords_replace_rule);
                        $cg_keywords_replace_rule_kewyrod = $cg_keywords_replace_rule_parts[0];
                        $cg_keywords_replace_rule_link = wp_automatic_trim($cg_keywords_replace_rule_parts[1]);

                        $cg_keywords_replace_rule_kewyrod_arr = explode(',', $cg_keywords_replace_rule_kewyrod);

                        foreach ($cg_keywords_replace_rule_kewyrod_arr as $cg_keywords_replace_rule_kewyrod_arr_single) {
                            $cg_keywords_replace_all[] = $cg_keywords_replace_rule_kewyrod_arr_single;
                            $cg_keywords_replace_all_vals[md5($cg_keywords_replace_rule_kewyrod_arr_single)] = $cg_keywords_replace_rule_link;
                        }
                    } // valid rule if
                }

                // sort by length
                usort(($cg_keywords_replace_all), 'wp_automatic_sort');

                $limit = -1; // -1 for no limit default

                //it optin OPT_REPLACE_KEYWORD_ONCE is set, set limit to 1
                if (in_array('OPT_REPLACE_KEYWORD_ONCE', $camp_opt)) {
                    $limit = 1;
                }

                // replace found keywords with {number}
                $i = 0;
                foreach ($cg_keywords_replace_all as $cg_keywords_replace_all_single) {
                    $post_content = preg_replace('/\b' . preg_quote($cg_keywords_replace_all_single, '/') . '\b/iu', '{' . $i . '}', $post_content, $limit);
                    $i++;
                }

                // replace found {number} with correct link
                foreach ($cg_keywords_replace_all as $key => $cg_keywords_replace_all_single) {
                    $replace_link = $cg_keywords_replace_all_vals[md5($cg_keywords_replace_all_single)];
                    echo '<br>Hyperlinking the keyword:' . $cg_keywords_replace_all_single . ' with this link:' . $replace_link;
                    $post_content = wp_automatic_str_replace('{' . $key . '}', '<a href="' . $replace_link . '">' . $cg_keywords_replace_all_single . '</a>', $post_content);
                }
            }

            // replacing patterns
            if (in_array('OPT_RGX_REPLACE', $camp_opt)) {

                $separator = '|';
                if (in_array('OPT_RGX_REPLACE_SEP', $camp_opt)) {
                    $separator = '#';
                }

                $regex_patterns = wp_automatic_trim($camp_general['cg_regex_replace']);
                echo '<br>Replacing using REGEX';

                // protecting tags
                if (in_array('OPT_RGX_REPLACE_PROTECT', $camp_opt)) {
                    echo '..protecting tags.';

                    preg_match_all("/<[^<>]+>/is", $post_content, $matches, PREG_PATTERN_ORDER);
                    $htmlfounds = $matches[0];

                    // extract all fucken shortcodes
                    $pattern = "\[.*?\]";
                    preg_match_all("/" . $pattern . "/s", $post_content, $matches2, PREG_PATTERN_ORDER);
                    $shortcodes = $matches2[0];
                    $htmlfounds = array_merge($htmlfounds, $shortcodes);
                    $htmlfounds = array_filter(array_unique($htmlfounds));

                    $i = 1;
                    foreach ($htmlfounds as $htmlfound) {
                        $post_content = wp_automatic_str_replace($htmlfound, "[" . str_repeat('*', $i) . "]", $post_content);
                        $i++;
                    }
                }

                if (stristr($regex_patterns, $separator)) {
                    $regex_patterns_arr = explode("\n", $regex_patterns);

                    foreach ($regex_patterns_arr as $regex_pattern) {

                        $regex_pattern = wp_automatic_trim($regex_pattern);

                        if (stristr($regex_pattern, $separator)) {

                            // title only flag
                            $isTitleOnly = false;
                            $isContentOnly = false;

                            if (stristr($regex_pattern, $separator . 'titleonly')) {
                                $isTitleOnly = true;
                                $regex_pattern = wp_automatic_str_replace($separator . 'titleonly', '', $regex_pattern);
                            }

                            if (stristr($regex_pattern, $separator . 'contentonly')) {
                                $isContentOnly = true;
                                $regex_pattern = wp_automatic_str_replace($separator . 'contentonly', '', $regex_pattern);
                            }

                            $regex_pattern_parts = explode($separator, $regex_pattern);

                            $regex_pattern_search = $regex_pattern_parts[0];

                            if (count($regex_pattern_parts) > 2) {

                                $regex_pattern_replace = $regex_pattern_parts[rand(1, count($regex_pattern_parts) - 1)];
                            } else {
                                $regex_pattern_replace = $regex_pattern_parts[1];
                            }

                            // space in replacement
                            $regex_pattern_replace = wp_automatic_str_replace('\s', ' ', $regex_pattern_replace);

                            echo '<br>*Replacing ' . wp_automatic_htmlentities($regex_pattern_search) . ' with ' . wp_automatic_htmlentities($regex_pattern_replace);

                            $replacements_count = 0;

                            // echo $post_content;
                            // exit;

                            if ((!$isTitleOnly) || $isContentOnly) {
                                if (!in_array('OPT_RGX_REPLACE_WORD', $camp_opt)) {

                                    // replacing in content
                                    $post_content = preg_replace('{' . $regex_pattern_search . '}su', $regex_pattern_replace, $post_content, -1, $replacements_count);

                                    // replacing in rules
                                    $i = 1;
                                    while (isset($img["rule_$i"])) {

                                        $img["rule_$i"] = preg_replace('{' . $regex_pattern_search . '}su', $regex_pattern_replace, $img["rule_$i"]);

                                        $i++;
                                    }

                                    //replace in extracted custom fields using the option Specific extraction to a custom field, excerpt, tags or custom taxonomy

                                    //loop $this->customFieldsFound
                                    foreach ($this->customFieldsFound as $customFieldFound) {
                                        
                                        if (isset($img[$customFieldFound])) {
                                            $img[$customFieldFound] = preg_replace('{' . $regex_pattern_search . '}su', $regex_pattern_replace, $img[$customFieldFound], -1, $replacements_count);

                                            //if replacemnt > 0 echo
                                            if ($replacements_count > 0) {
                                                echo '<br> - Replaced in custom field: ' . $customFieldFound. ' count: ' . $replacements_count;
                                            }

                                        }
                                        
                                    }

                                   

                                } else {
                                    $post_content = preg_replace('{\b' . preg_quote($regex_pattern_search) . '\b}su', $regex_pattern_replace, $post_content, -1, $replacements_count);

                                    // replacing in rules
                                    $i = 1;
                                    while (isset($img["rule_$i"])) {

                                        $img["rule_$i"] = preg_replace('{\b' . preg_quote($regex_pattern_search) . '\b}su', $regex_pattern_replace, $img["rule_$i"]);
                                        $i++;
                                    }

                                    //replace in extracted custom fields using the option Specific extraction to a custom field, excerpt, tags or custom taxonomy

                                    //loop $this->customFieldsFound
                                    foreach ($this->customFieldsFound as $customFieldFound) {
                                        
                                        if (isset($img[$customFieldFound])) {
                                            $img[$customFieldFound] = preg_replace('{\b' . preg_quote($regex_pattern_search) . '\b}su', $regex_pattern_replace, $img[$customFieldFound], -1, $replacements_count);

                                            //if replacemnt > 0 echo
                                            if ($replacements_count > 0) {
                                                echo '<br> - Replaced in custom field: ' . $customFieldFound. ' count: ' . $replacements_count;
                                            }

                                        }
                                        
                                    }

                                }

                                echo '<-- ' . $replacements_count . ' replacements done in content ... ';
                            } else {
                                echo ' on titles only';
                            }

                            if ((in_array('OPT_RGX_REPLACE_TTL', $camp_opt) || $isTitleOnly) && !$isContentOnly) {

                                if (!in_array('OPT_RGX_REPLACE_WORD', $camp_opt)) {
                                    $post_title = preg_replace('{' . $regex_pattern_search . '}su', $regex_pattern_replace, $post_title, -1, $replacements_count);
                                } else {
                                    $post_title = preg_replace('{\b' . preg_quote($regex_pattern_search) . '\b}su', $regex_pattern_replace, $post_title, -1, $replacements_count);
                                }

                                echo ' & ' . $replacements_count . ' replacements in title ... ';
                            }
                        }
                    }
                } else {
                    echo '<-- added config for replacement is not correct/empty';
                }

                // restore protected tags
                if (isset($htmlfounds) and count($htmlfounds) > 0) {

                    // restoring
                    $i = 1;
                    foreach ($htmlfounds as $htmlfound) {
                        $post_content = wp_automatic_str_replace('[' . str_repeat('*', $i) . ']', $htmlfound, $post_content);
                        $i++;
                    }
                }
            }

            // gallery will download images ini cache and add to media
            if (in_array('OPT_GALLERY_ALL', $camp_opt)) {
                $camp_opt[] = 'OPT_CACHE';
                $camp_opt[] = 'OPT_FEED_MEDIA';
            }

            // cache images locally ?
            $attachements_to_attach = array();
            $already_cached_imgs = array();

            // cache images if option is enabled or IG or FB
            if (!in_array('OPT_REMOVE_IMAGES', $camp_opt) && (in_array('OPT_CACHE', $camp_opt) || $camp_type == 'Instagram' || $camp_type == 'Places' || $camp_type == 'Clickbank' || $camp_type == 'Facebook' || $camp_type == 'telegram') ) {

                // strip srcset srcset=
                if (!in_array('OPT_FEED_SRCSET', $camp_opt)) {
                    $post_content = preg_replace('{srcset=".*?"}', '', $post_content);
                    $post_content = preg_replace('{sizes=".*?"}', '', $post_content);
                }

                preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', stripslashes($post_content), $matches);

                $srcs = $matches[1];
                $srcs = array_unique($srcs);
                $current_host = parse_url(home_url(), PHP_URL_HOST);

                $first_image_cache = true; // copy of the first image if used for the featured image

                foreach ($srcs as $image_url) {

                    // check inline images
                    if (stristr($image_url, 'data:image')) {
                        continue;
                    }

                    // instantiate so we replace . note we modify image_url
                    $image_url_original = $image_url;

                    // decode html entitiies
                    $image_url = html_entity_decode($image_url);

                    // file name to store
                    $filename = basename($image_url);

                    if (stristr($image_url, '%') || stristr($filename, '%')) {
                        $filename = urldecode($filename);
                    }

                    //trim long filenames to the 100 first chars
                    if (strlen($filename) > 100) {

                        //if function exists mb substr else normal substr
                        if (function_exists('mb_substr')) {
                            $filename = mb_substr($filename, 0, 100);
                        } else {
                            $filename = substr($filename, 0, 100);
                        }
                    }

                    // clean names
                    if (in_array('OPT_CACHE_CLEAN', $camp_opt) || ($camp_type == 'Instagram' && in_array('OPT_THUMB_CLEAN', $camp_opt))) {

                        $post_title_to_generate_from = $this->spintax->spin($post_title);
                        $post_title_to_generate_from = wp_automatic_str_replace(array(
                            '[nospin]',
                            '[/nospin]',
                        ), '', $post_title_to_generate_from);

                        $clean_name = $this->file_name_from_title($post_title_to_generate_from);

                        if (wp_automatic_trim($clean_name) != "") {

                            // get the image extension \.\w{3}
                            $ext = pathinfo($filename, PATHINFO_EXTENSION);

                            if ($camp_type == 'Instagram' || $camp_type == 'Facebook') {
                                $ext = 'jpg';
                            }

                            if (stristr($ext, '?')) {
                                $ext_parts = explode('?', $ext);
                                $ext = $ext_parts[0];
                            }

                            // clean parameters after filename
                            $filename = wp_automatic_trim($clean_name);

                            if (wp_automatic_trim($ext) != '') {
                                $filename = $filename . '.' . $ext;
                            }
                        }
                    }

                    if (stristr($image_url, ' ')) {
                        $image_url = wp_automatic_str_replace(' ', '%20', $image_url);
                    }

                    $imghost = parse_url($image_url, PHP_URL_HOST);

                    if (stristr($imghost, 'http://')) {
                        $imgrefer = $imghost;
                    } else {
                        $imgrefer = 'http://' . $imghost;
                    }

                    if ($imghost != $current_host) {

                        //if contains i2.wp.com , remove ?blahblah
                        if (stristr($image_url, 'i2.wp.com')) {
                            echo '<br>Removing query string from i2.wp.com';
                            $image_url = preg_replace('/\?.*/', '', $image_url);
                        }
                         

                        echo '<br>Caching image    : ' . $image_url;

                        

                        // let's cache this image
                        // set thumbnail
                        $upload_dir = wp_upload_dir();

                        // curl get
                        $x = 'error';
                        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($image_url));

                        // empty referal
                        if (!in_array('OPT_CACHE_REFER_NULL', $camp_opt)) {
                            curl_setopt($this->ch, CURLOPT_REFERER, $imgrefer);
                        } else {
                            curl_setopt($this->ch, CURLOPT_REFERER, '');
                        }

                        // amazon fix https://m.media-amazon.com/images/I/41Vg3dKd4WL.jpg returns fastly error
                        if (strpos($image_url, 'amazon') || strpos($image_url, 'alicdn')) {
                            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                                'Host: ' . $imghost,
                            ));
                        }

                        curl_setopt($this->ch, CURLOPT_HEADER, 0);

                        $image_data = $this->curl_exec_follow($this->ch);

                        $x = curl_error($this->ch);

                        if (wp_automatic_trim($x) != '') {
                            echo ' <-- Error: ' . $x;
                        }

                        $contentType = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);

                        if (!stristr($contentType, 'image') && !stristr($image_data, 'WEBP')) {
                            echo '<-- can not verify if the content returned is an image skipping returned ' . $contentType;

                            continue;
                        }

                        // now we know the mime, lets add an ext to fname if not existing
                        $filename = $this->append_file_ext($filename, $contentType);

                        $image_data_md5 = md5($image_data);

                        // check if already cached before
                        $is_cached = $this->is_cached($image_url, $image_data_md5);

                        if ($is_cached != false) {
                            echo '<--already cached *';
                            $post_content = wp_automatic_str_replace($image_url_original, $is_cached, $post_content);

                            $already_cached_imgs[] = $is_cached;

                            continue;
                        }

                        $x = curl_error($this->ch);

                        //response code
                        $response_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                        if (wp_automatic_trim($image_data) != '') {

                            $x = curl_error($this->ch);

                            if (stristr($filename, '?')) {
                                $farr = explode('?', $filename);
                                $filename = $farr[0];
                            }

                            if (wp_mkdir_p($upload_dir['path'])) {
                                $file = $upload_dir['path'] . '/' . $filename;
                            } else {
                                $file = $upload_dir['basedir'] . '/' . $filename;
                            }

                            // check if same image name already exists

                            if (file_exists($file)) {
                                $filename = time() . '_' . rand(0, 999) . '_' . $filename;

                                if (wp_mkdir_p($upload_dir['path'])) {
                                    $file = $upload_dir['path'] . '/' . $filename;
                                } else {
                                    $file = $upload_dir['basedir'] . '/' . $filename;
                                }

                            } else {
                            }

                            file_put_contents($file, $image_data);
                            $file_link = $upload_dir['url'] . '/' . $filename;
                            $guid = $upload_dir['url'] . '/' . basename($filename);

                            // replace original src with new file link
                            $post_content = wp_automatic_str_replace($image_url_original, $file_link, $post_content);
                            $this->img_cached($image_url_original, $file_link, $image_data_md5, $file);

                            echo '<-- cached';

                            if ($first_image_cache) {

                                $first_image_cache = false;
                                $first_cached_image_data = $image_data;
                                $first_cached_image_link = $file_link;
                                $first_cached_image_type = $contentType;
                            }

                            // add to media library and attach to the post
                            if (in_array('OPT_FEED_MEDIA', $camp_opt)) {

                                // atttatchment check if exists or not
                                global $wpdb;

                                $query = "select * from $wpdb->posts where guid = '$guid' limit 1";
                                $already_saved_attachment = $wpdb->get_row($query);

                                if (isset($already_saved_attachment->ID)) {

                                    $attach_id = $already_saved_attachment->ID;
                                } else {

                                    $wp_filetype = wp_check_filetype($filename, null);

                                    if ($wp_filetype['type'] == false) {
                                        $wp_filetype['type'] = 'image/jpeg';
                                    }

                                    // Title handling

                                    $imgTitle = sanitize_file_name($filename);
                                    if (in_array('OPT_THUMB_ALT', $camp_opt)) {
                                        $imgTitle = wp_trim_words($post_title, 10, '');
                                    }

                                    $attachment = array(
                                        'guid' => $guid,
                                        'post_mime_type' => $wp_filetype['type'],
                                        'post_title' => $imgTitle,
                                        'post_content' => '',
                                        'post_status' => 'inherit',
                                    );

                                    // add attachements to attach after post publish
                                    $attachements_to_attach[] = array(
                                        $file,
                                        $attachment,
                                    );
                                }
                            }
                        } else {
                            echo '<-- can not get image content: ' . $x . ' code:' . "$response_code";
                        }
                    }
                } // end foreach image

                // Instagram added images for caching to delete class="delete"
                $post_content = preg_replace('{<img class="delete.*?>}', '', $post_content);
            }

            // attaching media
            $attach_ids = array();
            if (isset($attachements_to_attach) && count($attachements_to_attach) > 0) {

                require_once ABSPATH . 'wp-admin/includes/image.php';

                foreach ($attachements_to_attach as $attachements_to_attach_single) {

                    $file = $attachements_to_attach_single[0];

                    $attachment = $attachements_to_attach_single[1];
                    // $post_id = $id;

                    if (!function_exists('wp_automatic_filter_image_sizes')) {
                        function wp_automatic_filter_image_sizes($sizes)
                        {
                            $sizes = array();
                            return $sizes;
                        }
                    }

                    if (!in_array('OPT_FEED_MEDIA_ALL', $camp_opt)) {
                        add_filter('intermediate_image_sizes_advanced', 'wp_automatic_filter_image_sizes');
                    }

                    $attach_id = wp_insert_attachment($attachment, $file);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                    $attach_ids[] = $attach_id; // add attach ID for gallery option OPT_GALLERY_ALL
                }

                // remove sizes filter
                remove_filter('intermediate_image_sizes_advanced', 'wp_automatic_filter_image_sizes');
            }

            // replacing words that should be replaced
            $sets = stripslashes(get_option('wp_automatic_replace', ''));

            $sets_arr = array_filter(explode("\n", $sets));

            if (count($sets_arr) > 0 && !in_array('OPT_REPLACE_NO_PROTECT', $wp_automatic_options)) {

                preg_match_all("/<[^<>]+>/is", $post_content, $matches, PREG_PATTERN_ORDER);
                $htmlfounds = $matches[0];

                // extract all shortcodes
                $pattern = "\[.*?\]";
                preg_match_all("/" . $pattern . "/s", $post_content, $matches2, PREG_PATTERN_ORDER);
                $shortcodes = $matches2[0];
                $htmlfounds = array_merge($htmlfounds, $shortcodes);
                $htmlfounds = array_filter(array_unique($htmlfounds));

                $i = 1;
                foreach ($htmlfounds as $htmlfound) {
                    $post_content = wp_automatic_str_replace($htmlfound, "[" . str_repeat('*', $i) . "]", $post_content);
                    $i++;
                }
            }

            foreach ($sets_arr as $set) {
                if (wp_automatic_trim($set) != '' && stristr($set, '|')) {

                    // valid set let's replace
                    $set_words = explode('|', $set);

                    // cleaning empty words
                    $i = 0;
                    foreach ($set_words as $setword) {
                        if (wp_automatic_trim($setword) == '') {
                            unset($set_words[$i]);
                        }
                        $i++;
                    }

                    if (count($set_words) > 1) {
                        // word 1

                        $word1 = wp_automatic_trim($set_words[0]);

                        // randomize replacing word
                        $rand = rand(1, count($set_words) - 1);
                        $replaceword = wp_automatic_trim($set_words[$rand]);

                        echo '<br>replacing "' . $word1 . '" by "' . $replaceword . '"';

                        if (in_array('OPT_REPLACE_NO_REGEX', $wp_automatic_options)) {

                            $post_title = wp_automatic_str_replace($word1, $replaceword, $post_title);
                            $post_content = wp_automatic_str_replace($word1, $replaceword, $post_content);
                        } else {

                            $post_title = preg_replace('/\b' . wp_automatic_trim(preg_quote($word1, '/')) . '\b/iu', $replaceword, $post_title);
                            $post_content = preg_replace('/\b' . wp_automatic_trim(preg_quote($word1, '/')) . '\b/iu', $replaceword, $post_content);
                        }
                    }
                }
            }

            // restore protected tags
            if (isset($htmlfounds) and count($htmlfounds) > 0) {

                // restoring
                $i = 1;
                foreach ($htmlfounds as $htmlfound) {
                    $post_content = wp_automatic_str_replace('[' . str_repeat('*', $i) . ']', $htmlfound, $post_content);
                    $i++;
                }
            }

            $abcontTxt = $camp->camp_post_content;
            $abtitleTxt = $camp->camp_post_title;

            // spin the content
            if (in_array('OPT_TBS', $camp_opt) && wp_automatic_trim($abcontTxt) != '' || stristr($abcontTxt, '{') && stristr($abcontTxt, '}') && stristr($abcontTxt, '|') || stristr($abtitleTxt, '{') && stristr($abtitleTxt, '}') && stristr($abtitleTxt, '|')) {

                if ($camp_type != 'Spintax') {

                    echo '<br>Spin the content enabled';

                    $abconts = $post_title . '(99999)' . $post_content;

                    if (in_array('OPT_TBS', $camp_opt)) {
                        $abconts = $this->spin($abconts);
                    }

                    $abconts = $this->spintax->spin($abconts);
                    $tempz = explode('(99999)', $abconts);

                    // Rewrite the title
                    if (!in_array('OPT_TBS_TTL', $camp_opt)) {
                        echo '<br>Spinning the title';
                        $post_title = $tempz[0];
                    }

                    $post_content = $tempz[1];

                    // remove nospin tags
                    $post_title = wp_automatic_str_replace('[nospin]', '', $post_title);
                    $post_title = wp_automatic_str_replace('[/nospin]', '', $post_title);
                    $post_content = wp_automatic_str_replace('[nospin]', '', $post_content);
                    $post_content = wp_automatic_str_replace('[/nospin]', '', $post_content);
                } // not spintax
            }

            // categories for post
            if (stristr($camp->camp_post_category, ',')) {
                $categories = array_filter(explode(',', $camp->camp_post_category));
            } else {
                $categories = array(
                    $camp->camp_post_category,
                );
            }

            // check if dummy title (notitle)
            $post_title = wp_automatic_str_replace('(notitle)', '', $post_title);

            // Keyword to category
            $new_categories = array();

            if (in_array('OPT_KEYWORD_CAT', $camp_opt) && wp_automatic_trim($camp_general['cg_keyword_cat']) != '') {
                echo '<br>Keyword to category check started...';

                $content_to_check = in_array('OPT_KEYWORD_NO_CNT', $camp_opt) ? '' : $post_content;
                $content_to_check .= in_array('OPT_KEYWORD_TTL', $camp_opt) ? ' ' . $post_title : '';

                $cg_keyword_cat = $camp_general['cg_keyword_cat'];
                $cg_keyword_cat_rules = array_filter(explode("\n", $cg_keyword_cat));

                foreach ($cg_keyword_cat_rules as $cg_keyword_cat_rule) {
                    if (stristr($cg_keyword_cat_rule, '|')) {

                        $cg_keyword_cat_rule = wp_automatic_trim($cg_keyword_cat_rule);

                        $cg_keyword_cat_rule_parts = explode('|', $cg_keyword_cat_rule);

                        $cg_keyword_cat_rule_keyword = $cg_keyword_cat_rule_parts[0];
                        $cg_keyword_cat_rule_category = $cg_keyword_cat_rule_parts[1];

                        $was_found = true;

                        $cg_keyword_cat_rule_keywords = explode(',', $cg_keyword_cat_rule_keyword);

                        foreach ($cg_keyword_cat_rule_keywords as $cg_keyword_cat_rule_keyword_single) {

                            if (in_array('OPT_KEYWORD_EXACT_NO', $camp_opt)) {

                                // exact match

                                if (!stristr($content_to_check, wp_automatic_trim($cg_keyword_cat_rule_keyword_single))) {

                                    $was_found = false;
                                    break;
                                }
                            } else {

                                // word match
                                if (!(preg_match('{\b' . preg_quote($cg_keyword_cat_rule_keyword_single) . '\b}siu', $content_to_check) || (stristr($cg_keyword_cat_rule_keyword_single, '#') && stristr($content_to_check, wp_automatic_trim($cg_keyword_cat_rule_keyword_single))))) {

                                    $was_found = false;
                                    break;
                                }
                            }
                        }

                        if ($was_found) {

                            echo '<br><- Key ' . $cg_keyword_cat_rule_keyword . ' exists adding category:' . $cg_keyword_cat_rule_category;

                            if (is_numeric($cg_keyword_cat_rule_category)) {
                                $new_categories[] = $cg_keyword_cat_rule_category;
                            } elseif (stristr($cg_keyword_cat_rule_category, ',')) {
                                $new_categories = array_merge($new_categories, explode(',', $cg_keyword_cat_rule_category));
                            }
                        }
                    }
                }

                // now new categories are available to consider
                if (count($new_categories) > 0) {

                    if (in_array('OPT_KEYWORD_CAT_FORGET', $camp_opt)) {
                        $categories = $new_categories;
                    } else {
                        $categories = array_merge($categories, $new_categories);
                    }
                }
            }

            // post status
            if ( ( in_array('OPT_DRAFT_PUBLISH', $camp_opt) || in_array('OPT_EXCLUDE_SPIN', $camp_opt) ) && $camp->camp_post_status == 'publish' ) {
                echo '<br>Setting post status to draft temporarily';
                $postStatus = 'draft';
            } else {

                $postStatus = $camp->camp_post_status;
            }

            // Gallery from found images, delete images and add gallery

            // prepare gallery attachments
            if (in_array('OPT_GALLERY_ALL', $camp_opt)) {
                $gallery_attach_ids = array();

                if (count($attach_ids) > 0) {
                    $gallery_attach_ids = $attach_ids;
                }
                // add already attached this run

                if (isset($already_cached_imgs) && count($already_cached_imgs) > 0) {
                    echo '<br>Found ' . count($already_cached_imgs) . ' cached images, finding ids for gallery attachments';

                    foreach ($already_cached_imgs as $already_cached_img) {
                        echo '<br> -- finding id for ' . $already_cached_img . ' ';
                        $query = "select * from {$this->wp_prefix}posts where guid = '$already_cached_img' limit 1";
                        $pres = $this->db->get_row($query);

                        if (isset($pres->ID)) {
                            echo ' <-- found ' . $pres->ID;
                            $gallery_attach_ids[] = $pres->ID;
                        } else {
                            echo '<-- not found';
                        }
                    }
                }
            }

            if ((in_array('OPT_GALLERY_ALL', $camp_opt) && count($gallery_attach_ids) > 0) && !(count($gallery_attach_ids) == 1 && !in_array('OPT_FEED_GALLERY_LIMIT', $camp_opt))) {

                echo '<br>Found ' . count($gallery_attach_ids) . ' images to attach as a gallery, creating the gallery';

                $gallery_content = '<!-- wp:gallery {"linkTo":"none"} -->
<figure class="wp-block-gallery has-nested-images columns-default is-cropped">';

                foreach ($gallery_attach_ids as $attach_ids_single) {

                    //if option OPT_FEED_GALLERY_LINK_MEDIA is enabled, link to media file
                    $linkTo = ''; //ini
                    if (in_array('OPT_FEED_GALLERY_LINK_MEDIA', $camp_opt)) {

                        //getting the media URL from the attachment ID
                        $linkTo = wp_get_attachment_url($attach_ids_single);

                    } elseif (in_array('OPT_FEED_GALLERY_LINK_POST', $camp_opt)) {

                        //getting the post URL from the attachment ID
                        $linkTo = get_permalink($attach_ids_single);

                    }

                    $gallery_content .= '<!-- wp:image {"id":' . $attach_ids_single . ',"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large">';

                    //build the image contnet
                    $img_content = '<img src="' . wp_get_attachment_image_url($attach_ids_single) . '" alt="" class="wp-image-' . $attach_ids_single . '"/>';

                    //if link to is not empty add the link
                    if (wp_automatic_trim($linkTo) != '') {
                        $img_content = '<a href="' . $linkTo . '">' . $img_content . '</a>';
                    }

                    //add the image to the gallery content
                    $gallery_content .= $img_content;

                    $gallery_content .= '</figure>
					<!-- /wp:image -->';

                }

                $gallery_content .= '</figure>
<!-- /wp:gallery -->';

                //if old format i.e the option OPT_FEED_GALLERY_OLD is enabled, overwrite the gallery content with the old format [gallery ids="83772,83770,83768"]
                if (in_array('OPT_FEED_GALLERY_OLD', $camp_opt)) {
                    $gallery_content = '[gallery ids="' . implode(',', $gallery_attach_ids) . '"]';
                }

                // remove content images?
                if (in_array('OPT_FEED_GALLERY_DELETE', $camp_opt)) {

                    // save content before images removal
                    $post_content_before_images_removal = $post_content;

                    // remove images
                    $post_content = preg_replace('!<img .*?>!s', '', $post_content);

                }

                if (stristr($post_content, '[the_gallery]')) {
                    $post_content = wp_automatic_str_replace('[the_gallery]', $gallery_content, $post_content);
                } else {
                    $post_content = $gallery_content . $post_content;
                }
            } elseif (stristr($post_content, '[the_gallery]')) {
                $post_content = wp_automatic_str_replace('[the_gallery]', '', $post_content);
            }

            // building post
            $my_post = array(
                'post_title' => ($post_title),
                'post_content' => $post_content,
                'post_status' => $postStatus,
                'post_author' => $camp->camp_post_author,
                'post_type' => $camp->camp_post_type,
                'post_category' => $categories,

            );

            // Pending for non english

            if (in_array('OPT_MUST_ENGLISH', $camp_opt)) {
                echo '<br>Checking If English or not';

                if ($this->is_english($post_title)) {
                    echo '<-- English ';
                } else {
                    echo '<--Guessed as Not English setting as pending';
                    $my_post['post_status'] = 'pending';
                }
            }

            // pending if openaiFailed is true and option OPT_OPENAI_PENDING is set
            if (in_array('OPT_OPENAI_PENDING', $camp_opt) && $this->openaiFailed == true) {
                echo '<br>OpenAI Failed, setting as pending';
                $my_post['post_status'] = 'pending';
            }

            // if posting from a specific list of posts and auto detect content method failed, set post to pending as content at this case will be just the link
            if (in_array('OPT_MULTI_FIXED_LIST', $camp_opt) && $this->fullContentSuccess == false) {
                echo '<br>Auto detect content failed, setting as pending';
                $my_post['post_status'] = 'pending';
            }

            // If the camp type is multi and the post title contains http:// or https:// set the post status to pending
            if ($camp_type == 'Multi' && (stristr($post_title, 'http://') || stristr($post_title, 'https://'))) {
                echo '<br>Post title contains http:// or https://, setting as pending';
                $my_post['post_status'] = 'pending';
            }

            // Pending for transation fail
            // skip if translation failed

            if (in_array('OPT_TRANSLATE', $camp_opt) && in_array('OPT_TRANSLATE_FTP', $camp_opt)) {

                echo '<br>Checking if translation faild..';

                if ($this->translationSuccess == false) {
                    echo ' Found Failed... set to pending..';
                    $my_post['post_status'] = 'pending';
                } else {
                    echo ' Found succeeded ';
                }
            }

            // prepare author
            if ($camp_type == 'Feeds' && isset($img['author']) && wp_automatic_trim($img['author']) != '' && in_array('OPT_ORIGINAL_AUTHOR', $camp_opt)) {
                echo '<br>Trying to set the post author to ' . $img['author'];
                $author_id = $this->get_user_id_by_display_name($img['author']);
                if ($author_id != false) {
                    $my_post['post_author'] = $author_id;
                }
            }

            kses_remove_filters();

            if ($camp_type == 'Feeds' && in_array('OPT_ORIGINAL_TIME', $camp_opt)) {

                if (isset($article['wpdate']) && wp_automatic_trim($article['wpdate']) != '') {

                    $wpdate = get_date_from_gmt($article['wpdate']);
                    echo '<br>Setting date for the post to ' . $wpdate;

                    // compare to current time to eliminate scheduled post
                    if (strtotime($wpdate) <= current_time('timestamp')) {
                        $my_post['post_date'] = $wpdate;
                    } else {
                        echo ' problem: asked time is higher than current time, ignoring...';
                    }
                } else {

                    echo '<br>No date found to set as the original post date';
                }
            }

            if ($camp_type == 'Craigslist' && in_array('OPT_CL_TIME', $camp_opt)) {

                $wpdate = get_date_from_gmt(gmdate('Y-m-d H:i:s', strtotime($img['item_date'])));
                echo '<br>Setting date for the post to ' . $wpdate;
                $my_post['post_date'] = $wpdate;
            }

            if ($camp_type == 'Reddit' && in_array('OPT_RD_TIME', $camp_opt)) {

                $wpdate = get_date_from_gmt(gmdate('Y-m-d H:i:s', ($img['item_date'])));
                echo '<br>Setting date for the post to ' . $wpdate;
                $my_post['post_date'] = $wpdate;
            }

            if ($camp_type == 'telegram' && in_array('OPT_TE_TIME', $camp_opt)) {

                $wpdate = get_date_from_gmt(gmdate('Y-m-d H:i:s', (strtotime($img['item_date']))));
                echo '<br>Setting date for the post to ' . $wpdate;
                $my_post['post_date'] = $wpdate;
            }

            if (($camp_type == 'Instagram' || $camp_type == 'TikTok') && in_array('OPT_IT_DATE', $camp_opt)) {
                echo '<br>Setting date for the post to ' . $img['item_created_date'];
                $my_post['post_date'] = $img['item_created_date'];
            }

            if (($camp_type == 'Twitter' && in_array('OPT_IT_DATE', $camp_opt)) || ($camp_type == 'SoundCloud' && in_array('OPT_SC_DATE', $camp_opt))) {

                $item_created_at = date('Y-m-d H:i:s', strtotime($img['item_created_at']));
                // $item_created_at = get_date_from_gmt($item_created_at);

                echo '<br>Setting date for the post to ' . $item_created_at;
                $my_post['post_date'] = $item_created_at;
            }

            if ($camp_type == 'Youtube' && in_array('OPT_YT_ORIGINAL_TIME', $camp_opt)) {

                $realDate = get_date_from_gmt(gmdate('Y-m-d H:i:s', $vid['vid_time']));
                echo '<br>Setting date for the post to ' . $realDate;
                $my_post['post_date'] = $realDate;
            }

            if ($camp_type == 'Youtube' && in_array('OPT_YT_AUTHOR', $camp_opt)) {

                echo '<br>Setting author for the post to ' . $img['vid_author_title'];

                $author_id = $this->get_user_id_by_display_name($img['vid_author_title']);
                if ($author_id != false) {
                    $my_post['post_author'] = $author_id;
                }
            }

            if ($camp_type == 'Twitter' && in_array('OPT_TW_AUTHOR', $camp_opt)) {

                echo '<br>Setting author for the post to ' . $img['item_author_name'];

                $author_id = $this->get_user_id_by_display_name($img['item_author_name']);
                if ($author_id != false) {
                    $my_post['post_author'] = $author_id;
                }
            }

            if ($camp_type == 'Reddit' && in_array('OPT_RD_AUTHOR', $camp_opt)) {

                echo '<br>Setting author for the post to ' . $img['item_author'];

                $author_id = $this->get_user_id_by_display_name($img['item_author']);
                if ($author_id != false) {
                    $my_post['post_author'] = $author_id;
                }
            }

            if ($camp_type == 'telegram' && in_array('OPT_TE_AUTHOR', $camp_opt)) {

                echo '<br>Setting author for the post to ' . $img['item_author'];

                $author_id = $this->get_user_id_by_display_name($img['item_author']);
                if ($author_id != false) {
                    $my_post['post_author'] = $author_id;
                }
            }

            if ($camp_type == 'DailyMotion' && in_array('OPT_DM_ORIGINAL_TIME', $camp_opt)) {

                $realDate = get_date_from_gmt(gmdate('Y-m-d H:i:s', $img['item_published_at']));

                echo '<br>Setting date for the post to ' . $realDate;
                $my_post['post_date'] = $realDate;
            }

            if ($camp_type == 'Vimeo' && in_array('OPT_VM_ORIGINAL_TIME', $camp_opt)) {
                $realDate = $vid['vid_created_time'];
                echo '<br>Setting date for the post to ' . $realDate;
                $my_post['post_date'] = $realDate;
            }

            if ($camp_type == 'Facebook' && in_array('OPT_ORIGINAL_FB_TIME', $camp_opt)) {
                $realDate = $img['original_date'];

                $my_post['post_date'] = $realDate;

                //when an event, set the start date of the event instead
                if (wp_automatic_trim($realDate) == '' && isset($img['start_time'])) {
                    $my_post['post_date'] = $img['start_time'];
                }

                echo '<br>Setting date for the post to ' . $my_post['post_date'];

            }

            // set excerpt of amazon product post type
            if ($camp_type == 'Amazon' && in_array('OPT_AMAZON_EXCERPT', $camp_opt)) {
                echo '<br>Setting product short description';
                $my_post['post_excerpt'] = $img['product_desc'];
            }

            // set excerpt of ebay product post type
            if ($camp_type == 'eBay' && $camp->camp_post_type == 'product' && in_array('OPT_EBAY_EXCERPT', $camp_opt)) {
                echo '<br>Setting product short description';
                $my_post['post_excerpt'] = $img['item_desc'];
            }

            // remove filter kses for security
            remove_filter('content_save_pre', 'wp_filter_post_kses');

            // fixing utf8

            // echo ' Fixing ... '.$my_post['post_content'];

            $my_post['post_content'] = $this->fix_utf8($my_post['post_content']);
            $my_post['post_title'] = $this->fix_utf8($my_post['post_title']);

            // truemag instagram remove embed
            if (($camp_type == 'Instagram') && stristr($abcont, '[embed]')) {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {

                    // extract video url
                    $my_post['post_content'] = preg_replace('{\[embed\](.*?)\[/embed\]}', '', $my_post['post_content']);
                }
            }

            // Exact match check after filling the template
            // validating exact
            $valid = true;

            $valid = $this->validate_exacts($my_post['post_content'], $title, $camp_opt, $camp, true, $camp_general);

            // if not valid process the campaign again and exit
            if ($valid == false) {

                // blacklisting the link so we don'g teg it again and cause a loop
                $this->link_execlude($camp->camp_id, $source_link);

                $this->process_campaign($camp);
                exit();
            }

            // fix html entities

            // Emoji fix

            if (!isset($wpdb)) {
                global $wpdb;
            }

            $emoji_fields = array(
                'post_title',
                'post_content',
                'post_excerpt',
            );

            foreach ($emoji_fields as $emoji_field) {
                if (isset($my_post[$emoji_field])) {
                    $charset = $wpdb->get_col_charset($wpdb->posts, $emoji_field);
                    if ('utf8' === $charset) {
                        $my_post[$emoji_field] = wp_encode_emoji($my_post[$emoji_field]);
                        $my_post[$emoji_field] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $my_post[$emoji_field]);
                    }
                }
            }

            // single campaign decide the decision
            if ($camp_type == 'Single') {

                $previousPostID = get_post_meta($camp->camp_id, 'wp_automatic_previous_id', 1);
                $previousHash = get_post_meta($camp->camp_id, 'wp_automatic_previous_hash', 1);
                $currentHash = md5($my_post['post_title'] . $my_post['post_content']);
                $cg_sn_after = $camp_general['cg_sn_after'];

                if ($cg_sn_after == 'justnew') {
                    // ignore all of that, a new post should be created ignoring last post
                } else {

                    if (wp_automatic_trim($previousPostID) == '') {
                        // inogre that, it is the first post ever
                        echo '<br>First time posting...';
                    } else {
                        // previous post exists

                        if ($cg_sn_after == 'delete') {
                            // always delete old post, lets delete it
                            echo '<br>Deleting previous post with ID:' . $previousPostID;
                            wp_delete_post($previousPostID, true);
                        } else {
                            // we have a previous post, let's see if new post is a differnet or the same

                            if ($previousHash == $currentHash) {
                                // nothing changed, it is the same post
                                echo '<br>Previous post contains the latest content as nothing changed...';
                                exit();
                            } else {
                                // post changed
                                if ($cg_sn_after == 'deletechange') {

                                    // delete and create new one
                                    echo '<br>Content changed, Deleting previous post with ID:' . $previousPostID;
                                    wp_delete_post($previousPostID, true);
                                } elseif ($cg_sn_after == 'new') {

                                    // don't delete anything, just create a new post
                                    echo '<br>Content changed, keeping old post and create a new one';
                                } elseif ($cg_sn_after == 'update') {

                                    // update an existing post
                                    echo '<br>Content changed, updating post with ID:' . $previousPostID;
                                    $my_post['ID'] = $previousPostID;
                                }
                            }
                        }
                    }
                }
            }

            // post parent
            if (in_array('OPT_PARENT', $camp_opt)) {
                $cg_post_parent = $camp_general['cg_post_parent'];
                if (is_numeric($cg_post_parent)) {
                    $my_post['post_parent'] = $cg_post_parent;
                }

            }

            // original slug
            if (in_array('OPT_FEED_ORIGINAL_SLUG', $camp_opt)) {

                $final_slug = wp_automatic_trim($this->get_final_slug($source_link));

                echo '<br>Source slug:' . $final_slug;

                if (wp_automatic_trim($final_slug) != '') {
                    $my_post['post_name'] = $final_slug;
                }

            }

            // custom slug if option enalbed OPT_CUSTOM_SLUG and not empty $post_slug
            if (in_array('OPT_CUSTOM_SLUG', $camp_opt) && wp_automatic_trim($post_slug) != '') {

                //if trim cg_custom_slug_word_limit is numeric and > 0, trim the slug to this number of words
                $cg_custom_slug_word_limit = $camp_general['cg_custom_slug_word_limit'];

                //trim
                $cg_custom_slug_word_limit = wp_automatic_trim($cg_custom_slug_word_limit);

                //if numeric and > 0
                if (is_numeric($cg_custom_slug_word_limit) && $cg_custom_slug_word_limit > 0) {

                    echo '<br>Trimming slug to ' . $cg_custom_slug_word_limit . ' words';

                    //trim to this number of words
                    $post_slug = wp_trim_words($post_slug, $cg_custom_slug_word_limit, '');
                }

                //replacing post title
                $post_slug = wp_automatic_str_replace('[post_title]', $post_title, $post_slug);

                $my_post['post_name'] = sanitize_title($post_slug);
            }

            // filter
            $my_post = apply_filters('wp_automatic_before_insert', $my_post);

            // fix \include where \ get removed https://core.trac.wordpress.org/ticket/54601#ticket ticked ID 19524
            if ($camp_type != 'Youtube') {
                if (stristr($my_post['post_title'], "\\")) {
                    $my_post['post_title'] = wp_slash($my_post['post_title']);
                }

                if (stristr($my_post['post_content'], "\\")) {
                    $my_post['post_content'] = wp_slash($my_post['post_content']);
                }

            }

            // remove emojis option
            if (in_array('OPT_REMOVE_EMOJI', $camp_opt)) {
                $my_post['post_content'] = $this->removeEmoji($my_post['post_content']);
                $my_post['post_title'] = $this->removeEmoji($my_post['post_title']);
            }

            //remove consequent <br> tags using REGEX if OPT_REMOVE_CONSEQ_BR is enabled
            if (in_array('OPT_REMOVE_CONSEQ_BR', $camp_opt)) {
                $my_post['post_content'] = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br>', $my_post['post_content']);
            }

            // remove images option
            if (in_array('OPT_REMOVE_IMAGES', $camp_opt)) {

                //echo number of images to be removed
                $images_count = substr_count($my_post['post_content'], '<img');

                echo '<br>Images to be removed: ' . $images_count;

                //remove images using REGEX
                $my_post['post_content'] = preg_replace('/<img[^>]+./', '', $my_post['post_content']);

            }

            // log ready to insert using wp_automatic_log_new function
            $title_without_emoji = $this->removeEmoji($my_post['post_title']);
            wp_automatic_log_new('INSERTING....', 'Inserting post with title ' . $title_without_emoji);

            // Insert the post into the database
            if ($my_post['post_type'] == 'topic' && function_exists('bbp_insert_topic')) {

                $cg_bb_fid = $camp_general['cg_bb_fid'];

                if (is_numeric($cg_bb_fid)) {
                    $my_post['post_parent'] = $cg_bb_fid;
                }

                $topicMeta = array();
                $topicMeta['forum_id'] = $cg_bb_fid;

                $id = bbp_insert_topic($my_post, $topicMeta);
            } else {

                if (isset($my_post['ID'])) {
                    // update exising post
                    $id = wp_update_post($my_post);
                } else {

                    // insert new post
                    $id = wp_insert_post($my_post);

                }
            }

            
            //copy of my post as it may get modified below
            $my_post_copy = $my_post;

            //log insert error if any

            if (is_wp_error($id)) {

                // log error
                wp_automatic_log_new('INSERTING....', 'Insertion error: ' . $id->get_error_message());

            }

            if ($id == 0) {
                echo '<br><span style="color:red">Error:Post Insertion failure</span>';

                //get the encoding of the posts table and echo it
                $posts_table_encoding = $wpdb->get_col_charset($wpdb->posts, 'post_content');
                echo '<br>Posts table encoding: ' . $posts_table_encoding;

                //if the encoding does not contain mp4, report it
                if (!stristr($posts_table_encoding, 'mb4')) {
                    echo '<br>Posts table encoding does not contain mb4, please change the encoding of the posts table to utf8mb4 to support inserting emojis and utf8mb4 encoded content or enable the option to delete emojis in the campaign options below';
                }

                // maybe contains emojis
            } else {

                $args['post_id'] = $id;
                $args['img'] = $img;
                $args['my_post'] = $my_post;
                do_action('wp_automatic_post_added', $args);
            }

            // attachements to attach from cached files old location
            /*
             * $attach_ids = array();
             * if (isset ( $attachements_to_attach ) && count ( $attachements_to_attach ) > 0) {
             *
             * require_once (ABSPATH . 'wp-admin/includes/image.php');
             *
             * foreach ( $attachements_to_attach as $attachements_to_attach_single ) {
             *
             * $file = $attachements_to_attach_single [0];
             * $attachment = $attachements_to_attach_single [1];
             * $post_id = $id;
             *
             * if (! function_exists ( 'wp_automatic_filter_image_sizes' )) {
             * function wp_automatic_filter_image_sizes($sizes) {
             * $sizes = array ();
             * return $sizes;
             * }
             * }
             *
             * if (! in_array ( 'OPT_FEED_MEDIA_ALL', $camp_opt )) {
             * add_filter ( 'intermediate_image_sizes_advanced', 'wp_automatic_filter_image_sizes' );
             * }
             *
             * $attach_id = wp_insert_attachment ( $attachment, $file, $post_id );
             * $attach_data = wp_generate_attachment_metadata ( $attach_id, $file );
             * wp_update_attachment_metadata ( $attach_id, $attach_data );
             *
             * $attach_ids[] = $attach_id; //add attach ID for gallery option OPT_GALLERY_ALL
             *
             * }
             *
             * // remove sizes filter
             * remove_filter ( 'intermediate_image_sizes_advanced', 'wp_automatic_filter_image_sizes' );
             * }
             *
             */

            // wpml internal cron patch
            if (!stristr($_SERVER['REQUEST_URI'], 'wp_automatic') && function_exists('icl_object_id')) {

                $wpml_options = get_option('icl_sitepress_settings');
                $default_lang = $wpml_options['default_language'];

                $args['element_id'] = $id;
                $args['element_type'] = 'post_' . $camp->camp_post_type;
                $args['language_code'] = $default_lang;

                do_action('wpml_set_element_language_details', $args);
            }

            // wpml integration
            if (in_array('OPT_WPML', $camp_opt) && function_exists('icl_object_id')) {
                include_once WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php';
                $language_code = wp_automatic_trim($camp_general['cg_wpml_lang']); // change the language code

                echo '<br>Setting WPML language to: ' . $language_code;
                if (function_exists('wpml_update_translatable_content')) {
                    wpml_update_translatable_content('post_' . $camp->camp_post_type, $id, ($language_code));

                    echo '<--Done';

                    // find if there is a previous instance in another language
                    global $sitepress;
                    $sitepress->switch_lang($language_code);

                    $cleanSource = preg_replace('{.rand\=.*}', '', $source_link);
                    $sourceMD5 = md5($cleanSource);
                    $keyName = $sourceMD5 . '_wpml';

                    $Qresults = $this->db->get_results("SELECT * FROM {$this->db->prefix}postmeta WHERE `meta_key` = '$keyName' limit 1", ARRAY_A);

                    if (count($Qresults) != 0) {

                        // we have a previous instance
                        $metaRow = $Qresults[0];
                        $metaValue = $metaRow['meta_value'];
                        $metaParts = explode('_', $metaValue);
                        echo '<br>Found a  previous instance of the post in a different langauge:' . $metaValue . ' connecting...';

                        $sitepress->set_element_language_details($id, $my_post['post_type'] . '_' . $my_post['post_type'], $metaParts[0], $language_code, $metaParts[1]);
                    } else {
                        // record the post for next translation
                        $trid = ($sitepress->get_element_trid($id));
                        add_post_meta($id, $sourceMD5 . '_wpml', $trid . '_' . $language_code);
                    }
                } else {
                    echo '<--Failed make sure wpml directory is named "sitepress-multilingual-cms"';
                }
            }

            // PolyLang
            if (in_array('OPT_POLY', $camp_opt) && function_exists('pll_set_post_language')) {

                $language_code = wp_automatic_trim($camp_general['cg_poly_lang']);

                echo '<br>Setting language of Polylang plugin to ' . $language_code;

                pll_set_post_language($id, $language_code);

                // connect older posts?
                $cleanSource = preg_replace('{.rand\=.*}', '', $source_link);
                $sourceMD5 = md5($cleanSource);
                $keyName = $sourceMD5 . '_poly';

                $Qresults = $this->db->get_results("SELECT * FROM {$this->db->prefix}postmeta WHERE `meta_key` = '$keyName' limit 1", ARRAY_A);

                if (count($Qresults) != 0) {

                    // we have a previous instance
                    $metaRow = $Qresults[0];

                    $metaValue = $metaRow['meta_value'];

                    echo '<br>Found a  previous instance of the post in a different langauge:' . $metaValue . ' connecting...';

                    PLL()->model->post->save_translations($id, array(
                        $metaValue => $metaRow['post_id'],
                    ));
                } else {

                    // mark the post for next translation
                    add_post_meta($id, $sourceMD5 . '_poly', $language_code);
                }
            }

            // setting categories for custom post types
            if (true | $camp->camp_post_type != 'post') {

                $customPostTaxonomies = get_object_taxonomies($camp->camp_post_type);

                if (count($customPostTaxonomies) > 0) {

                    foreach ($customPostTaxonomies as $tax) {

                        if (is_taxonomy_hierarchical($tax)) {
                            if (wp_automatic_trim($camp->camp_post_category) != '') {
                                echo '<br>Setting taxonomy ' . $tax . ' to ' . $camp->camp_post_category;
                            }

                            @wp_set_post_terms($id, $categories, $tax, true);
                        }
                    }
                }
            } else {
            }

            // Original Categories

            // hack to add any categories to categories_to_set so they get set on the fly
            if (isset($img['categories_to_set']) && wp_automatic_trim($img['categories_to_set']) != '' && !isset($img['cats'])) {
                $img['cats'] = $img['categories_to_set'];
            }

            if (($camp_type == 'Feeds' && in_array('OPT_ORIGINAL_CATS', $camp_opt) && wp_automatic_trim($img['cats'] != '')) || (isset($img['categories_to_set']) && wp_automatic_trim($img['categories_to_set']) != '')) {

                // Removed @v3.24.0
                // add_post_meta ( $id, 'original_cats', $img['cats'] );

                if (!in_array('OPT_ORIGINAL_CATS_TAGS', $camp_opt)) {
                    $img['cats'] = wp_automatic_fix_json_and_slashes($img['cats']);

                    echo '<br>Setting Categories to :' . $img['cats'];

                    // parent format? Electronics > Car & Vehicle Electronics
                    $is_parent_format = false;
                    if (stristr($img['cats'], ' > ')) {
                        $cats = array_filter(explode(' > ', $img['cats']));
                        $is_parent_format = true;
                    } else {
                        $cats = array_filter(explode(',', $img['cats']));
                    }

                    // taxonomy name
                    if ($camp->camp_post_type == 'post') {
                        $taxonomy = 'category';
                    } else {
                        $taxonomy = $camp_general['cg_camp_tax'];
                    }

                    echo ' Taxonomy:' . $taxonomy;

                    $new_cats = array();

                    // convert cats to ids
                    foreach ($cats as $cat_name) {

                        echo '<br>Processing cat:' . $cat_name;

                        $cat = get_term_by('name', $cat_name, $taxonomy);

                        // check existence
                        if ($cat == false) {

                            echo '<-- cat does not already exist... creating...';

                            // parent
                            $args = array();

                            if ($is_parent_format && isset($cat_id)) {
                                $args = array(
                                    'parent' => $cat_id,
                                ); // set the last cat created as the parent
                                echo '<br>Setting parent to ' . $cat_id . ' for cat ' . $cat_name;
                            } elseif (in_array('OPT_ORIGINAL_CATS_PARENT', $camp_opt) && is_numeric(wp_automatic_trim($camp_general['cg_parent_cat']))) {
                                $cg_parent_cat = $camp_general['cg_parent_cat'];
                                $args = array(
                                    'parent' => wp_automatic_trim($cg_parent_cat),
                                );
                            }

                            // cateogry not exist create it
                            $cat = wp_insert_term($cat_name, $taxonomy, $args);

                            if (!is_wp_error($cat)) {
                                // category id of inserted cat
                                $cat_id = $cat['term_id'];

                                echo '<-- Created with ID: ' . $cat_id;

                                $new_cats[] = $cat_id;
                            }
                        } else {

                            // category already exists let's get it's id
                            $cat_id = $cat->term_id;
                            $new_cats[] = $cat_id;

                            echo '<-- Cat exists with ID: ' . $cat_id;
                        }
                    }

                    // insert cats
                    if (count($new_cats) > 0) {
                        wp_set_post_terms($id, $new_cats, $taxonomy, true);
                    }

                    // delete uncategorized
                    if ($taxonomy == 'category') {
                        // get uncategorized slug by term id
                        $uncatObject = get_term(1);
                        $the_uncategorized_slug = isset($uncatObject->slug) ? $uncatObject->slug : 'uncategorized';
                        wp_remove_object_terms($id, $the_uncategorized_slug, $taxonomy);
                    }
                } elseif (in_array('OPT_ORIGINAL_CATS', $camp_opt)) {
                    // as tags instead
                    $camp_opt[] = 'OPT_ORIGINAL_TAGS'; // simulate tags option activated

                    // merge tags
                    $post_tags = array();
                    $new_tags = explode(',', $img['cats']);

                    if (count($new_tags) > 0) {
                        $post_tags = array_merge($post_tags, $new_tags);
                    }
                }
            } // end set original categories option

            // If post type== product, set the tags taxonomy to product_tags
            if ($camp->camp_post_type == 'product' && !in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                $camp_opt[] = 'OPT_TAXONOMY_TAG';
                $camp_general['cg_tag_tax'] = 'product_tag';
            }

            // Feeds part to field extraction set
            if ($camp_type == 'Feeds') {

                $customFieldsArr = $img['custom_fields'];

                if (is_array($customFieldsArr) && count($customFieldsArr) != 0) {

                    foreach ($customFieldsArr as $customFieldSet) {

                        if ($customFieldSet[0] == 'excerpt') {
                            $my_post = array(
                                'ID' => $id,
                                'post_excerpt' => $customFieldSet[1],
                            );

                            wp_update_post($my_post);
                        } elseif ($customFieldSet[0] == 'tags') {

                            // case 'microsoft', 'xbox-scarlett'
                            // fix josn
                            // remove slashes
                            $customFieldSet[1] = wp_automatic_fix_json_and_slashes($customFieldSet[1]);

                            echo '<br>Setting tags:' . $customFieldSet[1];

                            if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                                wp_set_post_terms($id, $customFieldSet[1], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                            } else {
                                wp_set_post_tags($id, $customFieldSet[1], true);
                            }
                        } elseif (stristr($customFieldSet[0], 'taxonomy_')) {

                            wp_set_post_terms($id, $customFieldSet[1], wp_automatic_str_replace('taxonomy_', '', $customFieldSet[0]), true);
                            print_r($customFieldSet[0]);
                            print_r($customFieldSet[1]);

                            //elseif starts with attribute, use function wp_automatic_add_product_attribute
                        } elseif (stristr($customFieldSet[0], 'attribute_')) {

                            $attribute_name = wp_automatic_str_replace('attribute_', '', $customFieldSet[0]);
                            $attribute_value = $customFieldSet[1];

                            wp_automatic_add_product_attribute($id, $attribute_name, $attribute_value);

                        } else {

                            if (!isset($my_post['ID'])) {
                                add_post_meta($id, $customFieldSet[0], $customFieldSet[1]);
                            }

                        }
                    } // foreach field
                } // if array
            } // if feed

            $post_id = $id;

            if (!isset($my_post['ID'])) {
                add_post_meta($id, 'wp_automatic_camp', $camp->camp_id);
            }

            if (isset($source_link)) {

                if (!isset($my_post['ID'])) {
                    $addedLink = add_post_meta($id, md5($source_link), $post_title);
                }

                if (!isset($addedLink) || $addedLink === false) {

                    if (!isset($my_post['ID'])) {
                        add_post_meta($id, md5($source_link), md5($source_link));
                    }

                }

                $set_original_link = $source_link; // for change permalink source
                if (!isset($my_post['ID'])) {

                    if ($camp_type == 'Amazon' && in_array('OPT_LINK_SOURSE', $camp_opt)) {

                        add_post_meta($id, 'original_link', $img['product_link']);
                        $set_original_link = $img['product_link'];
                    } elseif ($camp_type == 'Aliexpress' && in_array('OPT_LINK_SOURSE', $camp_opt)) {
                        add_post_meta($id, 'original_link', $img['item_affiliate_url']);
                        $set_original_link = $img['item_affiliate_url'];
                    } elseif ($camp_type == 'eBay' && in_array('OPT_LINK_SOURSE', $camp_opt)) {
                        add_post_meta($id, 'original_link', $img['item_link']);
                        $set_original_link = $img['item_link'];
                    } else {
                        add_post_meta($id, 'original_link', $source_link);
                    }
                }
            }

            // Record link if posted before
            if (in_array('OPT_LINK_ONCE', $camp_opt)) {

                $query = "insert into {$this->wp_prefix}automatic_links(	link_url ,link_keyword ) values ('" . md5($source_link) . "','" . $camp->camp_id . "')";
                $this->db->query($query);
            }

            // if link to source set flag
            if (in_array('OPT_LINK_SOURSE', $camp_opt)) {

                if (!isset($my_post['ID'])) {

                    if ($camp->camp_post_type == 'post') {
                        add_post_meta($id, '_link_to_source', 'yes');
                    } else {
                        if (!isset($set_original_link)) {
                            $set_original_link = $source_link;
                        }

                        add_post_meta($id, '_links_to', $set_original_link);
                    }
                }
            }

            // if link canonical _yoast_wpseo_canonical
            if (in_array('OPT_LINK_CANONICAL', $camp_opt)) {

                if (defined('WPSEO_VERSION')) {
                    if (!isset($my_post['ID'])) {
                        add_post_meta($id, '_yoast_wpseo_canonical', $source_link);
                    }

                } else {
                    if (!isset($my_post['ID'])) {
                        add_post_meta($id, 'canonical_url', $source_link);
                    }

                }
            }

            // add featured image
            if (in_array('OPT_REPLACE', $camp_opt)) {
                foreach ($keywords as $keyword) {

                    $keyword = wp_automatic_trim($keyword);

                    if (wp_automatic_trim($keyword != '')) {
                        $post_content = wp_automatic_str_replace($keyword, '<a href="' . $camp->camp_replace_link . '">' . $keyword . '</a>', $post_content);
                    }
                }
            }

            // Featured image
            if (in_array('OPT_THUMB', $camp_opt)) {

                $srcs = array(); // ini
                $srcs_alts = array();

                // if force og_img
                if (in_array('OPT_FEEDS_OG_IMG', $camp_opt) && isset($img['og_img']) && wp_automatic_trim($img['og_img']) != '') {

                    if (in_array('OPT_FEEDS_OG_IMG_REVERSE', $camp_opt) && stristr($post_content, '<img')) {
                        echo '<br>og:image found but will be skipped';
                        // here the image contains a first image and og:image should skipped

                        // set an og:image variable so it get appended to the end of found images. If width check is active and all images on post are not wide enough
                        $og_image = $img['og_img'];
                    } else {
                        $srcs = array(
                            $img['og_img'],
                        );
                        $srcs_alts[] = $img['og_alt'];
                    }
                }

                // if youtube set thumbnail to video thum
                if ($camp_type == 'Youtube' || $camp_type == 'Vimeo') {
                    // set youtube/vimeo image as featured image

                    // check if maxres exists
                    if (stristr($vid['vid_img'], 'hqdefault')) {
                        $maxres = wp_automatic_str_replace('hqdefault', 'maxresdefault', $vid['vid_img']);

                        $maxhead = wp_remote_head($maxres);

                        if (!is_wp_error($maxhead) && $maxhead['response']['code'] == 200) {
                            $vid['vid_img'] = $maxres;
                        }
                    }

                    $srcs = array(
                        $vid['vid_img'],
                    );

                    echo '<br>Vid Thumb:' . $vid['vid_img'];
                } elseif ($camp_type == 'DailyMotion' || $camp_type == 'Places') {

                    if (isset($img['item_image'])) {
                        $srcs = array(
                            $img['item_image'],
                        );
                    }

                } elseif ($camp_type == 'telegram' && isset($img['item_img']) && wp_automatic_trim($img['item_img']) != '') {
                    $srcs = array(
                        $img['item_img'],
                    );

                } elseif ($camp_type == 'SoundCloud') {

                    if (wp_automatic_trim($img['item_thumbnail']) != '') {
                        $srcs = array(
                            $img['item_thumbnail'],
                        );
                    } elseif (wp_automatic_trim($img['item_user_thumbnail']) != '') {
                        // $srcs = array($img['item_user_thumbnail']);
                    }
                } elseif ($camp_type == 'Twitter' && isset($img['item_image']) && wp_automatic_trim($img['item_image']) != '') {

                    $srcs = array(
                        $img['item_image'],
                    );
                } elseif ($camp_type == 'TikTok' || $camp_type == 'Craigslist' || $camp_type == 'Aliexpress') {

                    $srcs = array(
                        $img['item_img'],
                    );
                } elseif ($camp_type == 'Amazon') {

                    $srcs = array(
                        $img['product_img'],
                    );
                } elseif ($camp_type == 'eBay') {

                    $srcs = array(
                        $img['item_img'],
                    );
                } elseif (isset($srcs) && count($srcs) > 0) {
                } else {

                    $post_content_to_check_for_src_imgs = $post_content;

                    //if isset post_content_before_images_removal set it to the content to check instead
                    if (isset($post_content_before_images_removal) && wp_automatic_trim($post_content_before_images_removal) != '') {
                        $post_content_to_check_for_src_imgs = $post_content_before_images_removal;
                    }

                    //fix for images that disppaer from an openAI prompt issue:23701
                    //if no <img exist on content to check and post_content_before_openai_replacement is set and is not empty, use it for check
                    if (!stristr($post_content_to_check_for_src_imgs, '<img') && isset($post_content_before_openai_replacement) && wp_automatic_trim($post_content_before_openai_replacement) != '') {
                        $post_content_to_check_for_src_imgs = $post_content_before_openai_replacement;
                    }

                    $post_content_to_check_for_src_imgs = preg_replace('!src="data:image.*?"!', '', $post_content_to_check_for_src_imgs);

                    // extract first image
                    preg_match_all('/<img [^>]*src[\s]*=[\s]*"(.*?)".*?>/i', stripslashes($post_content_to_check_for_src_imgs), $matches);
                    $srcs = $matches[1];
                    $srcs_html = $matches[0];

                    foreach ($srcs_html as $src_html) {

                        preg_match('/alt[\s]*=[\s]*"(.*?)"/i', $src_html, $alt_matches);

                        if (isset($alt_matches[1])) {
                            $srcs_alts[] = $alt_matches[1];
                        } else {
                            $srcs_alts[] = '';
                        }
                    }

                    if (isset($og_image)) {
                        $og_arr = array();
                        $og_arr[] = $og_image;
                        $srcs = array_merge($srcs, $og_arr);
                    }
                }

                // may be a wp_automatic_Readability missed the image on the content get it from summary ?
                if (count($srcs) == 0 && $camp_type == 'Feeds' && in_array('OPT_FULL_FEED', $camp_opt)) {
                    echo '<br>Featured image is missing at full content searching for it in feed instead';
                    preg_match_all('/<img [^>]*src="([^"]+).*?/i', stripslashes($article['original_content']), $matches);
                    $srcs = $matches[1];
                    $srcs_html = $matches[0];

                    foreach ($srcs_html as $src_html) {

                        preg_match('/alt[\s]*=[\s]*"(.*?)"/i', $src_html, $alt_matches);
                        $srcs_alts[] = $alt_matches[1];
                    }

                    if (count($srcs) == 0) {
                        echo '<br>No image found at the feed summary';

                        if (wp_automatic_trim($img['og_img']) != '') {
                            echo '<br>Graph image thumb found';
                            $srcs = array(
                                $img['og_img'],
                            );
                        }
                    }
                }

                // No featured image found let's check if random image list found
                if (count($srcs) == 0 && in_array('OPT_THUMB_LIST', $camp_opt)) {
                    echo '<br>Trying to set random image as featured image';

                    $cg_thmb_list = $camp_general['cg_thmb_list'];

                    $cg_imgs = explode("\n", $cg_thmb_list);
                    $cg_imgs = array_filter($cg_imgs);
                    $cg_rand_img = wp_automatic_trim($cg_imgs[rand(0, count($cg_imgs) - 1)]);

                    // validate image
                    if (wp_automatic_trim($cg_rand_img) != '') {
                        $srcs = array(
                            $cg_rand_img,
                        );
                    }
                } elseif (in_array('OPT_THUMB_LIST', $camp_opt)) {

                    $cg_thmb_list = $camp_general['cg_thmb_list'];

                    $cg_imgs = explode("\n", $cg_thmb_list);
                    $cg_imgs = array_filter($cg_imgs);
                    $cg_rand_img = wp_automatic_trim($cg_imgs[rand(0, count($cg_imgs) - 1)]);

                    // validate image
                    if (wp_automatic_trim($cg_rand_img) != '') {
                        $srcs = array_merge($srcs, array(
                            $cg_rand_img,
                        ));
                    }
                }

                // if foce using thumb list
                if (in_array('OPT_THUMB_LIST_FORCE', $camp_opt) && in_array('OPT_THUMB_LIST', $camp_opt)) {

                    echo '<br>Force using image from set list';

                    $cg_thmb_list = $camp_general['cg_thmb_list'];
                    $cg_imgs = explode("\n", $cg_thmb_list);
                    $cg_imgs = array_filter($cg_imgs);
                    $cg_rand_img = wp_automatic_trim($cg_imgs[rand(0, count($cg_imgs) - 1)]);

                    // validate image
                    if (wp_automatic_trim($cg_rand_img) != '') {
                        $srcs = array(
                            $cg_rand_img,
                        );
                    }
                }

                // pixabay condition 1 force condition 2 no images found
                if ((in_array('OPT_PIXABAY', $camp_opt) && in_array('OPT_PIXABAY_FORCE', $camp_opt)) || (in_array('OPT_PIXABAY', $camp_opt) && count($srcs) == 0)) {

                    $possible_image = ''; // ini

                    if (in_array('OPT_PIXABAY_TITLE', $camp_opt)) {
                        // get keywords from post title
                        echo '<br>Getting keywords for PixaBay from post title ' . $post_title;

                        $validTitleWords = $this->wp_automatic_generate_tags($post_title);

                        foreach ($validTitleWords as $validTitleWord) {

                            echo '<br>Keyword from  the title : ' . $validTitleWord;
                            $possible_image = $this->get_pixabay_image($validTitleWord);

                            if (wp_automatic_trim($possible_image) != '') {
                                echo '<-- Found an image for this keyword';
                                break; // found for this keyword, nice
                            }
                        }
                    } else {
                        $cg_pixabay_keyword = $camp_general['cg_pixabay_keyword'];

                        // if cg_pixabay_keyword contains the [keyword] tag replace it with the keyword
                        if (stristr($cg_pixabay_keyword, '[keyword]') && !empty($img['keyword'])) {

                            // report the keyword to the user
                            echo '<br>PixaBay keyword: Replacing [keyword] tag with the keyword: ' . $img['keyword'];

                            $cg_pixabay_keyword = wp_automatic_str_replace('[keyword]', $img['keyword'], $cg_pixabay_keyword);
                        }

                        $possible_image = $this->get_pixabay_image($cg_pixabay_keyword);
                    }

                    if (stristr($possible_image, 'pixabay')) {
                        echo '<br>Final PixaBay image found to use:' . $possible_image;
                        $srcs = array(
                            $possible_image,
                        );
                    }
                }

                //dalle dalle3_image_generate condition 1 force condition 2 no images found
                if ((in_array('OPT_DALLE', $camp_opt) && in_array('OPT_DALLE_FORCE', $camp_opt)) || (in_array('OPT_DALLE', $camp_opt) && count($srcs) == 0)) {

                    $possible_image = ''; // ini

                    //cg_dalle3_prompt
                    $cg_dalle3_prompt = $camp_general['cg_dalle3_prompt'];

                    //if no prompt set, use the default prompt which is generate a featured image for a post that is titled:[post_title]
                    if (empty($cg_dalle3_prompt)) {
                        $cg_dalle3_prompt = 'generate a featured image for a post that is titled:[post_title]';
                    }

                    //replacing [keyword]
                    if (stristr($cg_dalle3_prompt, '[keyword]')) {

                        $keyword_to_replace = isset($img['keyword']) ? $img['keyword'] : '';

                        //replace
                        $cg_dalle3_prompt = wp_automatic_str_replace('[keyword]', $keyword_to_replace, $cg_dalle3_prompt);

                    }

                    //replacing [post_title]
                    if (stristr($cg_dalle3_prompt, '[post_title]')) {

                        $post_title_to_replace = isset($my_post['post_title']) ? $my_post['post_title'] : '';

                        //replace
                        $cg_dalle3_prompt = wp_automatic_str_replace('[post_title]', $post_title_to_replace, $cg_dalle3_prompt);

                    }

                    //size cg_openai_dalle_image_size defaults to 1024x1024
                    $cg_openai_dalle_image_size = $camp_general['cg_openai_dalle_image_size'];

                    //if empty set to 1024x1024
                    if (empty($cg_openai_dalle_image_size)) {
                        $cg_openai_dalle_image_size = '1024x1024';
                    }

                    //call dalle3_image_generate function in try catch
                    try {
                        $possible_image = $this->dalle3_image_generate($cg_dalle3_prompt, $cg_openai_dalle_image_size);
                    } catch (Exception $e) {
                        echo '<br>Exception: ' . $e->getMessage();
                    }

                    if (stristr($possible_image, 'dalle')) {
                        echo '<br>Final dalle image found to use:' . $possible_image;
                        $srcs = array(
                            $possible_image,
                        );
                    }
                }

                // check srcs size to skip small images
                if (count($srcs) > 0 && in_array('OPT_THUMB_WIDTH_CHECK', $camp_opt)) {

                    $cg_minimum_width = 0;
                    $cg_minimum_width = $camp_general['cg_minimum_width'];

                    if (!(is_numeric($cg_minimum_width) && $cg_minimum_width > 0)) {
                        $cg_minimum_width = 100;
                    }

                    $n = 0;
                    $upload_dir = wp_upload_dir();

                    foreach ($srcs as $current_img) {

                        echo '<br>Candidate featured image: ' . $current_img;

                        if (stristr($current_img, 'data:image') && stristr($current_img, 'base64,')) {

                            $ex = explode('base64,', $current_img);

                            $image_data = base64_decode($ex[1]);
                        } else {

                            // curl get
                            $x = 'error';
                            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($current_img));
                            $image_data = curl_exec($this->ch);
                            $x = curl_error($this->ch);
                        }

                        if (wp_automatic_trim($image_data) != '') {

                            // let's save the file
                            if (wp_mkdir_p($upload_dir['path'])) {
                                $file = $upload_dir['path'] . '/' . 'temp_wp_automatic';
                            } else {
                                $file = $upload_dir['basedir'] . '/' . 'temp_wp_automatic';
                            }

                            file_put_contents($file, $image_data);

                            $size = getimagesize($file);

                            if ($size != false) {

                                if ($size[0] > $cg_minimum_width) {
                                    echo '<-- Valid width is ' . $size[0] . ' larger than ' . $cg_minimum_width;
                                    break;
                                } else {
                                    echo '<-- width is too low ' . $size[0];
                                    unset($srcs[$n]);
                                    if (isset($srcs_alts[$n])) {
                                        unset($srcs_alts[$n]);
                                    }

                                }
                            } else {
                                echo '<--size verification failed';
                                unset($srcs[$n]);
                                if (isset($srcs_alts[$n])) {
                                    unset($srcs_alts[$n]);
                                }

                            }
                        } else {
                            echo '<--no content ';
                            unset($srcs[$n]);
                            if (isset($srcs_alts[$n])) {
                                unset($srcs_alts[$n]);
                            }

                        }

                        $n++;
                    }
                } // width check

                // Setting the thumb

                //filter the srcs array to remove empty values
                $srcs = array_filter($srcs);

                if (count($srcs) > 0) {

                    $src = reset($srcs);

                    $image_url = $src;

                    //if contains i2.wp.com, remove ?bla bla
                    if (stristr($image_url, 'i2.wp.com')) {
                        echo '<br>Image contains i2.wp.com, removing all parameters';
                        $image_url = preg_replace('{\?.*}', '', $image_url);
                    }

                    $this->log('Featured image', '<a href="' . $image_url . '">' . $image_url . '</a>');
                    echo '<br>Featured image src: ' . $image_url;

                    // set thumbnail
                    $upload_dir = wp_upload_dir();

                    // img host
                    $imghost = parse_url($image_url, PHP_URL_HOST);

                    if (stristr($imghost, 'http://')) {
                        $imgrefer = $imghost;
                    } else {
                        $imgrefer = 'http://' . $imghost;
                    }

                    // amazon fix https://m.media-amazon.com/images/I/41Vg3dKd4WL.jpg returns fastly error
                    if (strpos($image_url, 'amazon')) {
                        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                            'Host: ' . $imghost,
                        ));
                    }

                    // empty referal
                    if (!in_array('OPT_CACHE_REFER_NULL', $camp_opt)) {
                        curl_setopt($this->ch, CURLOPT_REFERER, $imgrefer);
                    } else {
                        curl_setopt($this->ch, CURLOPT_REFERER, '');
                    }

                    if (stristr($image_url, 'base64,')) {
                        $filename = time();
                    } else {
                        // Decode html entitiies
                        $image_url = html_entity_decode($image_url);

                        // file named 417t7%2BJs8wL.jpg turned to 417t7%252BJs8wL.jpg
                        //disabled because it causes issues with openai generated images from dalle3
                        if (stristr($image_url, '%')) {
                            //$image_url = urldecode ( $image_url );
                        }

                        // File name to store
                        $filename = basename($image_url);

                        //trim long filenames to the 100 first chars
                        if (strlen($filename) > 100) {

                            echo '<br>Filename is too long, trimming...';

                            //if function exists mb substr else normal substr
                            if (function_exists('mb_substr')) {
                                $filename = mb_substr($filename, 0, 100);
                            } else {
                                $filename = substr($filename, 0, 100);
                            }
                        }

                        // Empty spaces fix
                        if (stristr($filename, ' ')) {
                            $filename = wp_automatic_str_replace(' ', '-', $filename);
                        }

                        // ? parameters removal 98176282_1622303147922555_5452725500717826048_n.jpg?_nc_cat=100&_nc_sid=8024bb&_nc_ohc=GHJGt1-A1z4AX-HoLXS&_nc_ht=scontent.faly1-2.fna&oh=802a3fc69c0ace28cc4a936134acaa2d&oe=5F022286
                        if (stristr($filename, '?')) {
                            $without_params_filename = preg_replace('{\?.*}', '', $filename);

                            if (wp_automatic_trim($without_params_filename) != '') {
                                $filename = $without_params_filename;
                            }
                        }

                        // Youtube hqdefault.jpg may exists with different data, change the name
                        if (stristr($filename, 'default.jpg')) {
                            $filename = time() . '_' . $filename;
                        }

                        //if image url contains windows.net then use current time as filename then md5 it before using
                        if (stristr($image_url, 'windows.net')) {
                            $filename = time();
                        }

                        // sanizie to remove single quotes and fancey chars
                        $filename = wp_automatic_str_replace(array(
                            "'",
                            "’",
                        ), '', $filename);
                        $filename = sanitize_file_name($filename);

                        if (stristr($image_url, ' ')) {
                            $image_url = wp_automatic_str_replace(' ', '%20', $image_url);
                        }
                    }

                    // Clean thumb
                    if (in_array('OPT_THUMB_CLEAN', $camp_opt)) {

                        $clean_name = $this->file_name_from_title($post_title);

                        if (wp_automatic_trim($clean_name) != "") {

                            // get the image extension \.\w{3}
                            $ext = pathinfo($filename, PATHINFO_EXTENSION);

                            if ($camp_type == 'Instagram') {
                                $ext = 'jpg';
                            }

                            if (stristr($ext, '?')) {
                                $ext_parts = explode('?', $ext);
                                $ext = $ext_parts[0];
                            }

                            // clean parameters after filename
                            $filename = wp_automatic_trim($clean_name);

                            if (wp_automatic_trim($ext) != '') {
                                $filename = $filename . '.' . $ext;
                            }
                        }
                    }

                    if (!in_array('OPT_THUM_NELO', $camp_opt) || stristr($image_url, 'base64,')) {

                        if (stristr($image_url, 'base64,')) {
                            $ex = explode('base64,', $current_img);
                            $image_data = base64_decode($ex[1]);

                            // set fileName extention .png, .jpg etc
                            preg_match('{data:image/(.*?);}', $image_url, $ex_matches);
                            $image_ext = $ex_matches[1];

                            if (wp_automatic_trim($image_ext) != '') {
                                $filename = $filename . '.' . $image_ext;

                                echo '<br>Fname:' . $filename;
                            }
                        } else {

                            // get image content
                            $x = 'error';
                            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);

                            // case https://mtvnaija.com/wp-content/uploads/2019/10/Sjava-–-Decoder-Ft.-Ranks-ATM-Just-G-MP3.jpg
                            if (!stristr($image_url, '%') && class_exists('WpOrg\Requests\Iri')) {

                                try {
                                    $iri = new WpOrg\Requests\Iri($image_url);
                                    $iri->host = WpOrg\Requests\IdnaEncoder::encode($iri->ihost);
                                    $image_url = $iri->uri;
                                } catch (Exception $e) {
                                    echo '<br>Exception:' . $e->getMessage();
                                }

                            }

                            //report to load url wp_automatic_trim(  )
                            //echo '<br>Image URL:' . html_entity_decode ( $image_url );
     
                            
                            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim(html_entity_decode($image_url)));
                            //curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

                            if (isset($first_cached_image_link) && $first_cached_image_link == $image_url) {

                                echo '<-- previousely loaded...';
                                $image_data = $first_cached_image_data;
                                $first_cached_image_content_type = $contentType;
                                $contentType = $first_cached_image_type;
                            } else {

                                $image_data = $this->curl_exec_follow($this->ch);
                                $contentType = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
                            }

                            echo '<br>Content type:' . $contentType;
                            $x = curl_error($this->ch);
                            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                        }

                        // adding ext
                        $filename = $this->append_file_ext($filename, $contentType);

                        // do not validate content type option
                        if (in_array('OPT_THUM_NOTYPE', $camp_opt)) {
                            $contentType = 'image';
                        }

                        if (wp_automatic_trim($image_data) != '' && $http_code == 200 && !stristr($contentType, 'image')) {

                            // possibly correct image get it's size
                            $width = $this->get_image_width($image_data);

                            if ($width != 0) {
                                echo '<br>Regardless of this content type header, It still seems a valid image with width = ' . $width;
                                $contentType = 'image';
                            }
                        }

                        if (wp_automatic_trim($image_data) != '' && stristr($contentType, 'image')) {

                            // check if already saved

                            $image_data_md5 = md5($image_data);

                            $is_cached = $this->is_cached($image_url, $image_data_md5);
                            if ($is_cached != false) {
                                echo '<--already cached';
                                $file = $this->cached_file_path;
                                $guid = $is_cached;
                            } else {

                                if (stristr($filename, '?')) {
                                    $farr = explode('?', $filename);
                                    $filename = $farr[0];
                                }

                                // pagepeeker fix
                                if (stristr($image_url, 'pagepeeker') && !in_array('OPT_THUMB_CLEAN', $camp_opt)) {
                                    $filename = md5($filename) . '.jpg';
                                }

                                if (wp_mkdir_p($upload_dir['path'])) {
                                    $file = $upload_dir['path'] . '/' . $filename;
                                } else {
                                    $file = $upload_dir['basedir'] . '/' . $filename;
                                }

                                // check if same image name already exists
                                if (file_exists($file)) {

                                    // get the current saved one to check if identical
                                    $already_saved_image_link = $upload_dir['url'] . '/' . $filename;

                                    // curl get
                                    $x = 'error';
                                    $url = $already_saved_image_link;
                                    curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                                    curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));

                                    $exec = curl_exec($this->ch);

                                    if (wp_automatic_trim($exec) == wp_automatic_trim($image_data)) {
                                        $idential = true;
                                        echo '<br>Featured image already exists with same path.. using it';
                                    } else {
                                        echo '<br>Featured image exists with same path but not identical.. saving  ';

                                        $filename = time() . '_' . $filename;
                                    }
                                }

                                // saving image
                                if (!isset($idential)) {
                                    if (wp_mkdir_p($upload_dir['path'])) {
                                        $file = $upload_dir['path'] . '/' . $filename;
                                    } else {
                                        $file = $upload_dir['basedir'] . '/' . $filename;
                                    }

                                    $f = file_put_contents($file, $image_data);

                                    // echo '<br>File URL:'.$file;
                                }

                                $guid = $upload_dir['url'] . '/' . basename($filename);

                                $this->img_cached($image_url, $guid, $image_data_md5, $file);
                            } // not cached

                            // atttatchment check if exists or not
                            global $wpdb;

                            $query = "select * from $wpdb->posts where guid = '$guid'";
                            $already_saved_attachment = $wpdb->get_row($query);

                            if (isset($already_saved_attachment->ID)) {

                                $attach_id = $already_saved_attachment->ID;
                            } else {

                                $wp_filetype = wp_check_filetype($filename, null);

                                if ($wp_filetype['type'] == false) {
                                    $wp_filetype['type'] = 'image/jpeg';
                                }

                                // Title handling

                                $imgTitle = sanitize_file_name($filename);

                                if (in_array('OPT_THUMB_ALT', $camp_opt)) {
                                    $imgTitle = wp_trim_words($post_title, 10, '');
                                }

                                $attachment = array(
                                    'guid' => $guid,
                                    'post_mime_type' => $wp_filetype['type'],
                                    'post_title' => $imgTitle,
                                    'post_content' => '',
                                    'post_status' => 'inherit',
                                );

                                $attach_id = wp_insert_attachment($attachment, $file, $post_id);

                                require_once ABSPATH . 'wp-admin/includes/image.php';
                                $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                                wp_update_attachment_metadata($attach_id, $attach_data);


                                //alt text from post title OPT_THUMB_ALT_FROM_TITLE
                                if (in_array('OPT_THUMB_ALT_FROM_TITLE', $camp_opt)) {
                                    update_post_meta($attach_id, '_wp_attachment_image_alt', $post_title);
                                }

                                // alt text
                                if (in_array('OPT_THUMB_ALT2', $camp_opt)) {
                                    $img_alt = reset($srcs_alts);

                                    if (wp_automatic_trim($img_alt) != '') {
                                        update_post_meta($attach_id, '_wp_attachment_image_alt', $img_alt);
                                    }

                                    if (in_array('OPT_THUMB_ALT3', $camp_opt)) {

                                        if (wp_automatic_trim($img_alt) == '') {
                                            update_post_meta($attach_id, '_wp_attachment_image_alt', $title);
                                        }
                                    }
                                }

                                // if OPT_PIXABAY_ALT set the alt text from $cg_pixabay_keyword if not empty
                                if (in_array('OPT_PIXABAY_ALT', $camp_opt)) {
                                    if (!empty($cg_pixabay_keyword)) {
                                        echo '<br>Setting alt text from pixabay keyword: ' . $cg_pixabay_keyword;
                                        update_post_meta($attach_id, '_wp_attachment_image_alt', $cg_pixabay_keyword);
                                    }
                                } elseif ((in_array('OPT_PIXABAY_ALT_MAIN_KEY', $camp_opt))) {
                                    if (!empty($img['keyword']) && wp_automatic_trim($img['keyword']) != '') {
                                        echo '<br>Setting alt text from main keyword: ' . $img['keyword'];
                                        update_post_meta($attach_id, '_wp_attachment_image_alt', $img['keyword']);
                                    }
                                }

                            }

                            $img['featured_img_source'] = $image_url;
                            $img['featured_img_local_source'] = $guid;
                            $img['featured_img_id'] = $attach_id;

                            set_post_thumbnail($post_id, $attach_id);
                            echo ' <-- thumbnail set successfully attachement:' . $attach_id;

                            if (isset($attach_id) && is_numeric($attach_id)) {
                                $wp_automatic_thumb_success = $post_id;
                            }
                            // Flag for successfull thumbnail

                            // if hide first image set the custom field
                            if (in_array('OPT_THUMB_STRIP', $camp_opt)) {

                                if (in_array('OPT_THUMB_STRIP_FULL', $camp_opt)) {
                                    echo '<br>Deleting first image from the content...';

                                    $new_post = get_post($id);
                                    $new_content = preg_replace('/<img [^>]*src=["|\'][^"|\']+.*?>/i', '', $new_post->post_content, 1);

                                    $my_post = array(
                                        'ID' => $id,
                                        'post_content' => $new_content,
                                    );

                                    // Update the post into the database
                                    wp_update_post($my_post);
                                } else {
                                    update_post_meta($post_id, 'wp_automatic_remove_first_image', 'yes');
                                }
                            }
                        } else {
                            echo ' <-- can not get image content ' . $x;
                        }
                    } else { // nelo
                        // setting custom field for nelo image
                        echo '<br>Setting the featured image custom field for ';

                        //set featured_img_source
                        $img['featured_img_source'] = $image_url;

                        if (function_exists('fifu_update_fake_attach_id')) {

                            echo 'Featured image from URL plugin';

                            update_post_meta($id, 'fifu_image_url', $image_url);
                            fifu_update_fake_attach_id($id);

                            $wp_automatic_thumb_success = $post_id;
                        } else {

                            echo 'Nelio plugin';
                            update_post_meta($id, '_nelioefi_url', $image_url);
                        }

                        // if hide first image set the custom field
                        if (in_array('OPT_THUMB_STRIP', $camp_opt)) {

                            if (in_array('OPT_THUMB_STRIP_FULL', $camp_opt)) {
                                echo '<br>Deleting first image from the content...';

                                $new_post = get_post($id);
                                $new_content = preg_replace('/<img [^>]*src=["|\'][^"|\']+.*?>/i', '', $new_post->post_content, 1);

                                $my_post = array(
                                    'ID' => $id,
                                    'post_content' => $new_content,
                                );

                                // Update the post into the database

                                wp_update_post($my_post);
                            } else {
                                update_post_meta($post_id, 'wp_automatic_remove_first_image', 'yes');
                            }
                        }
                    }
                } else {

                    // currently no images in the content
                    $this->log('Featured image', 'No images found to set as featured');

                    //report
                    echo '<br>No images found to set as featured';

                }
            } // thumbnails

            // featured image set or set as pending?

            if (in_array('OPT_THUM_MUST', $camp_opt)) {
                if (isset($wp_automatic_thumb_success) && $wp_automatic_thumb_success == $post_id) {
                    // success
                } else {

                    echo '<br>Failed to set featured image, setting the post status to Pending';

                    // failed set as pending
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_status' => 'pending',
                    ));
                }
            }

            // tags
            if (in_array('OPT_TAG', $camp_opt)) {

                $targetKeywords = $keywords;

                if (in_array('OPT_TAG_KEYONLY', $camp_opt) && isset($this->used_keyword) && wp_automatic_trim($this->used_keyword) != '') {
                    $targetKeywords = $this->used_keyword;
                }

                if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                    wp_set_post_terms($id, $targetKeywords, wp_automatic_trim($camp_general['cg_tag_tax']), true);
                } else {
                    wp_set_post_tags($id, $targetKeywords, true);
                }
            }

            // youtube tags and comments
            if ($camp_type == 'Youtube') {

                // tags
                if (in_array('OPT_YT_TAG', $camp_opt)) {
                    if (wp_automatic_trim($this->used_tags) != '') {

                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                            wp_set_post_terms($id, $this->used_tags, wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $this->used_tags, true);
                        }
                    }
                }

                // comments
                if (in_array('OPT_YT_COMMENT', $camp_opt)) {
                    echo '<br>Trying to post comments';

                    // get id
                    $temp = explode('v=', $this->used_link);
                    $vid_id = $temp[1];

                    $wp_automatic_yt_tocken = wp_automatic_trim(wp_automatic_single_item('wp_automatic_yt_tocken'));

                    $maxResults = rand(20, 50);

                    $comments_link = "https://www.googleapis.com/youtube/v3/commentThreads?maxResults=$maxResults&part=snippet&videoId=" . $vid_id . "&key=$wp_automatic_yt_tocken";

                    echo '<br>Comments yt url:' . $comments_link;

                    // curl get
                    $x = 'error';
                    $url = $comments_link;
                    curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
                    $exec = curl_exec($this->ch);

                    $x = curl_error($this->ch);

                    if (wp_automatic_trim($x) != '') {
                        echo '<br>' . $x;
                    }

                    if (wp_automatic_trim($exec) != '') {

                        if (stristr($exec, 'items')) {

                            $exec = wp_automatic_str_replace('s28-', 's90-', $exec);

                            $comments_array = json_decode($exec);

                            $entry = $comments_array->items;

                            if (count($entry) == 0) {
                                echo '<br>No comments found';
                            } else {
                                echo '<br>Found ' . count($entry) . ' comment to post';

                                foreach ($entry as $comment) {

                                    $comment = $comment->snippet->topLevelComment->snippet;

                                    $commentText = $comment->textDisplay;
                                    $commentAuthor = $comment->authorDisplayName;
                                    $profileImage = $comment->authorProfileImageUrl;

                                    $commentUri = '';
                                    $comment_author_url = '';

                                    if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                        $comment_author_url = $profileImage . '|' . $comment->authorChannelId->value;
                                        $commentUri = $comment->authorChannelUrl;
                                    } else {
                                        $comment_author_url = $profileImage . '|';
                                    }

                                    if (in_array('OPT_YT_ORIGINAL_TIME', $camp_opt)) {
                                        $time = $comment->publishedAt;
                                        $time = get_date_from_gmt(gmdate('Y-m-d H:i:s', strtotime($time)));
                                    } else {
                                        $time = current_time('mysql');
                                    }

                                    if (wp_automatic_trim($commentText) != '') {

                                        // bb replies
                                        if ($camp->camp_post_type == 'topic' && function_exists('bbp_insert_reply')) {

                                            $post_parent = $post_topic_id = $id;
                                            $comment_author_url = "https://www.youtube.com/channel/" . $comment->authorChannelId->value;

                                            $reply_data = array(
                                                'post_parent' => $post_parent,
                                                'post_content' => $commentText,
                                                'post_author' => false,
                                                'post_date' => $time,
                                            );
                                            $reply_meta = array(
                                                'topic_id' => $post_topic_id,
                                                'anonymous_name' => $commentAuthor,
                                                'anonymous_email' => $profileImage,
                                                'anonymous_website' => $comment_author_url,
                                            );
                                            $ret = bbp_insert_reply($reply_data, $reply_meta);
                                        } else {

                                            $data = array(
                                                'comment_post_ID' => $id,
                                                'comment_author' => $commentAuthor,
                                                'comment_author_email' => '',
                                                'comment_author_url' => $comment_author_url,
                                                'comment_content' => $commentText,
                                                'comment_type' => '',
                                                'comment_parent' => 0,

                                                'comment_author_IP' => '127.0.0.1',
                                                'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
                                                'comment_date' => $time,
                                                'comment_approved' => 1,
                                            );

                                            $this->wp_automatic_insert_comment($data);
                                        }
                                    }
                                }
                            }
                        } else {
                            echo '<br>could not find comments';
                        }
                    } else {
                        echo '<br>No valid comments feed';
                    }
                }
            }

            // After single scraper
            if ($camp_type == 'Single') {

                // update last post
                update_post_meta($camp->camp_id, 'wp_automatic_previous_id', $id);
                update_post_meta($camp->camp_id, 'wp_automatic_previous_hash', $currentHash);
            }

            // AFTER POST SPECIFIC DAILYMOTION
            if ($camp_type == 'DailyMotion') {
                // tags
                if (in_array('OPT_DM_TAG', $camp_opt)) {
                    if (wp_automatic_trim($this->used_tags) != '') {

                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                            wp_set_post_terms($id, $this->used_tags, wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $this->used_tags, true);
                        }
                    }
                }
            }

            // Generic tags_to_set , if found set as tags
            if (isset($img['tags_to_set']) && wp_automatic_trim($img['tags_to_set']) != '') {

                echo '<br>Tags to be set: ' . $img['tags_to_set'];

                // tags

                if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                    wp_set_post_terms($id, $img['tags_to_set'], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                } else {
                    wp_set_post_tags($id, $img['tags_to_set'], true);
                }
            }

            // AFTER POST SPECIFIC
            if ($camp_type == 'Flicker') {
                if (in_array('OPT_FL_TAG', $camp_opt)) {

                    if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {

                        wp_set_post_terms($id, $img['img_tags'], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                    } else {
                        wp_set_post_tags($id, $img['img_tags'], true);
                    }
                }
            }

            // AFTER POST SPECIFIC SoundCloud
            if ($camp_type == 'SoundCloud') {

                // tags
                if (in_array('OPT_SC_TAG', $camp_opt)) {

                    $item_tags = $img['item_tags'];

                    // extract tags with multiple words
                    preg_match_all('{".*?"}', $item_tags, $multiple_tags_matches);

                    $multiple_tags_matches = $multiple_tags_matches[0];

                    $single_item_tags = $item_tags;

                    foreach ($multiple_tags_matches as $multiple_tag) {
                        $single_item_tags = wp_automatic_str_replace($multiple_tag, '', $single_item_tags);
                        $single_item_tags = wp_automatic_str_replace('  ', ' ', $single_item_tags);
                    }

                    // remove "
                    $multiple_tags_matches = wp_automatic_str_replace('"', '', $multiple_tags_matches);

                    // explode single tags
                    $single_item_tags = explode(' ', $single_item_tags);

                    $all_tags = array_merge($multiple_tags_matches, $single_item_tags);
                    $all_tags = array_filter($all_tags);
                    $all_tags_comma = implode(',', $all_tags);

                    if (wp_automatic_trim($all_tags_comma) != '') {
                        echo '<br>Tags:' . $all_tags_comma;

                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {

                            wp_set_post_terms($id, $all_tags_comma, wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $all_tags_comma, true);
                        }
                    }
                }

                // comments
                if (in_array('OPT_SC_COMMENT', $camp_opt)) {

                    $wp_automatic_sc_client = $this->get_soundcloud_key();

                    if (wp_automatic_trim($wp_automatic_sc_client) != '') {

                        // getting the comment

                        $item_id = $img['item_id'];

                        echo '<br>Fetching comments for tack:' . $item_id;

                        $commentsCount = rand(20, 30);

                        $api_url = "https://api-v2.soundcloud.com/tracks/$item_id/comments?client_id=$wp_automatic_sc_client&limit=$commentsCount&offset=0&linked_partitioning=1&app_version=1607422960&app_locale=en&threaded=1&filter_replies=0";

                        // curl get
                        $x = 'error';

                        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($api_url));
                        $exec = curl_exec($this->ch);
                        $x = curl_error($this->ch);

                        if (stristr($exec, '"comment"')) {

                            $comments_json = json_decode($exec);
                            $comments_json = $comments_json->collection;

                            echo '<br>Found ' . count($comments_json) . ' comments to post.';

                            $time = current_time('mysql');

                            foreach ($comments_json as $new_comment) {

                                if ($new_comment->kind == 'comment') {

                                    $commentUri = '';
                                    if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                        $commentUri = $new_comment->user->permalink_url;
                                    }

                                    // bb replies
                                    if ($camp->camp_post_type == 'topic' && function_exists('bbp_insert_reply')) {

                                        $post_parent = $post_topic_id = $id;

                                        $reply_data = array(
                                            'post_parent' => $post_parent,
                                            'post_content' => $new_comment->body,
                                            'post_author' => false,
                                            'post_date' => $time,
                                        );
                                        $reply_meta = array(
                                            'topic_id' => $post_topic_id,
                                            'anonymous_name' => $new_comment->user->username,
                                            'anonymous_website' => $commentUri,
                                        );
                                        $ret = bbp_insert_reply($reply_data, $reply_meta);
                                    } else {

                                        $data = array(
                                            'comment_post_ID' => $id,
                                            'comment_author' => $new_comment->user->username,
                                            'comment_author_email' => '',
                                            'comment_author_url' => $commentUri,
                                            'comment_content' => $new_comment->body,
                                            'comment_type' => '',
                                            'comment_parent' => 0,

                                            'comment_author_IP' => '127.0.0.1',
                                            'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
                                            'comment_date' => $time,
                                            'comment_approved' => 1,
                                        );

                                        $this->wp_automatic_insert_comment($data);
                                    }
                                }
                            }
                        } else {
                            echo '<br>No comments found';
                        }
                    }
                }
            }

            // After post facebook
            if ($camp_type == 'Facebook') {
                // tags
                if (in_array('OPT_FB_TAGS', $camp_opt)) {
                    if (wp_automatic_trim($img['item_tags']) != '') {
                        echo '<br>Setting tags:' . $img['item_tags'];
                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {

                            wp_set_post_terms($id, $img['item_tags'], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $img['item_tags'], true);
                        }
                    }
                }

                // comments
                if (in_array('OPT_FB_COMMENT', $camp_opt) && isset($img['comments'])) {

                    // trying to post FB comments
                    echo '<br>Posting FB comments as comments :' . $img['post_id'];

                    if (isset($img['comments'])) {

                        $comments = array_slice($img['comments'], 0, rand(25, 50));

                        $added = 0;
                        $time = current_time('mysql');

                        foreach ($comments as $comment) {

                            if (wp_automatic_trim($comment['text']) != '') {

                                $commentText = $comment['text'];

                                $commentAuthor = $comment['author_name'];
                                $commentAuthorID = $comment['author_id'];
                                $commentUri = '';

                                if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                    $commentUri = "https://facebook.com/" . $commentAuthorID;
                                }

                                if (in_array('OPT_ORIGINAL_FB_TIME', $camp_opt)) {

                                    $time = date('Y-m-d H:i:s', $comment['time']);
                                    $time = get_date_from_gmt($time);
                                }

                                if (wp_automatic_trim($commentText) != '') {

                                    $anonymous_email = '';
                                    if (!in_array('OPT_FB_COMMENT_IMG', $camp_opt)) {
                                        $anonymous_email = $commentAuthorID . '@fb.com';
                                    }

                                    // bb replies
                                    if ($camp->camp_post_type == 'topic' && function_exists('bbp_insert_reply')) {

                                        $post_parent = $post_topic_id = $id;

                                        $reply_data = array(
                                            'post_parent' => $post_parent,
                                            'post_content' => $commentText,
                                            'post_author' => false,
                                            'post_date' => $time,
                                        );
                                        $reply_meta = array(
                                            'topic_id' => $post_topic_id,
                                            'anonymous_name' => $commentAuthor,
                                            'anonymous_email' => $anonymous_email,
                                            'anonymous_website' => $commentUri,
                                        );
                                        $ret = bbp_insert_reply($reply_data, $reply_meta);
                                    } else {

                                        $data = array(
                                            'comment_post_ID' => $id,
                                            'comment_author' => $commentAuthor,
                                            'comment_author_email' => $anonymous_email,
                                            'comment_author_url' => $commentUri,
                                            'comment_content' => $commentText,
                                            'comment_type' => '',
                                            'comment_parent' => 0,

                                            'comment_author_IP' => '127.0.0.1',
                                            'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
                                            'comment_date' => $time,
                                            'comment_approved' => 1,
                                        );

                                        $this->wp_automatic_insert_comment($data);
                                    }
                                }

                                $added++;
                            }
                        }

                        echo '<br>' . $added . ' comments to post';
                    } else {
                        echo '<br>No comments found';
                    }
                }
            }

            // After post vimeo
            if ($camp_type == 'Vimeo') {

                if (in_array('OPT_VM_TAG', $camp_opt)) {

                    if (wp_automatic_trim($vid['vid_tags']) != '') {

                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {

                            wp_set_post_terms($id, $vid['vid_tags'], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $vid['vid_tags'], true);
                        }
                    }
                }
            }

            if ($camp_type == 'Envato') {

                if (in_array('OPT_EV_AUTO_TAGS', $camp_opt)) {

                    if (wp_automatic_trim($img['item_tags']) != '') {

                        echo '<br>Setting tags to:' . $img['item_tags'];

                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {

                            wp_set_post_terms($id, $img['item_tags'], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $img['item_tags'], true);
                        }
                    }
                }
            }

            if ($camp_type == 'Instagram') {

                if (in_array('OPT_IT_TAGS', $camp_opt)) {
                    if (wp_automatic_trim($img['item_tags']) != '') {
                        echo '<br>Setting tags:' . $img['item_tags'];
                        if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {
                            wp_set_post_terms($id, $img['item_tags'], wp_automatic_trim($camp_general['cg_tag_tax']), true);
                        } else {
                            wp_set_post_tags($id, $img['item_tags'], true);
                        }
                    }
                }

                // comments
                if (in_array('OPT_IT_COMMENT', $camp_opt)) {

                    echo '<br>Trying to post comments';

                    $time = current_time('mysql');

                    $comments = $img['item_comments'];

                    if (count($comments) > 0) {

                        echo '<br>Found ' . count($comments) . ' comment to post from';

                        // random count
                        $commentsCount = count($comments);
                        if ($commentsCount == 40) {
                            $commentsCount = rand(20, 40);
                            echo '...Posting ' . $commentsCount;
                        }

                        $i = 0;
                        foreach ($comments as $comment) {

                            $i++;

                            if ($i > $commentsCount) {
                                break;
                            }

                            if (isset($comment->node)) {
                                $comment = $comment->node;
                            }

                            $commentText = $comment->text;

                            if (isset($comment->owner) && isset($comment->created_at) && wp_automatic_trim($comment->created_at) != '') {

                                // new comments format
                                $commentAuthor = $comment->owner->username;
                                $commentAuthorID = $comment->owner->id;

                                $commentUri = '';
                                if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                    $commentUri = "https://instagram.com/" . $comment->owner->username;
                                }

                                if (in_array('OPT_IT_DATE', $camp_opt)) {
                                    $time = date('Y-m-d H:i:s', $comment->created_at);
                                }
                            } elseif (isset($comment->user)) {

                                // new comments format pk
                                $commentAuthor = $comment->user->full_name;
                                $commentAuthorID = $comment->user->pk;

                                $commentUri = '';
                                if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                    $commentUri = "https://instagram.com/" . $comment->user->username;
                                }

                                if (in_array('OPT_IT_DATE', $camp_opt)) {
                                    $time = date('Y-m-d H:i:s', $comment->created_at);
                                }
                            } else {

                                // old comments format
                                $commentAuthor = $comment->from->full_name;
                                if (wp_automatic_trim($commentAuthor) == '') {
                                    $commentAuthor = $comment->from->username;
                                }

                                $commentAuthorID = $comment->author[0]->uri->x;

                                $commentUri = '';
                                if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                    $commentUri = "https://instagram.com/" . $comment->from->username;
                                }

                                if (in_array('OPT_IT_DATE', $camp_opt)) {
                                    $time = date('Y-m-d H:i:s', $comment->created_time);
                                }
                            }

                            if (wp_automatic_trim($commentText) != '') {

                                // bb replies
                                if ($camp->camp_post_type == 'topic' && function_exists('bbp_insert_reply')) {

                                    $post_parent = $post_topic_id = $id;
                                    $reply_data = array(
                                        'post_parent' => $post_parent,
                                        'post_content' => $commentText,
                                        'post_author' => false,
                                        'post_date' => $time,
                                    );
                                    $reply_meta = array(
                                        'topic_id' => $post_topic_id,
                                        'anonymous_name' => $commentAuthor,
                                        'anonymous_website' => $commentUri,
                                    );
                                    $ret = bbp_insert_reply($reply_data, $reply_meta);
                                } else {

                                    $data = array(
                                        'comment_post_ID' => $id,
                                        'comment_author' => $commentAuthor,
                                        'comment_author_email' => '',
                                        'comment_author_url' => $commentUri,
                                        'comment_content' => $commentText,
                                        'comment_type' => '',
                                        'comment_parent' => 0,

                                        'comment_author_IP' => '127.0.0.1',
                                        'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
                                        'comment_date' => $time,
                                        'comment_approved' => 1,
                                    );

                                    $this->wp_automatic_insert_comment($data);
                                }
                            }
                        }
                    } else {
                        echo '<br>No comments found';
                    }
                }
            }

            // After TikTok
            if ($camp_type == 'TikTok') {
                if (in_array('OPT_TT_TAGS', $camp_opt)) {

                    if (isset($img['item_tags'])) {
                        echo '<br>Tags:' . $img['item_tags'];
                        wp_set_post_tags($id, $img['item_tags'], true);
                    }
                }
            }

            // After Twitter
            if ($camp_type == 'Twitter') {
                if (in_array('OPT_TW_TAG', $camp_opt)) {

                    if (isset($img['item_hashtags'])) {
                        echo '<br>Tags:' . $img['item_hashtags'];
                        wp_set_post_tags($id, $img['item_hashtags'], true);
                    }
                }
            }

            // After Reddit
            if ($camp_type == 'Reddit') {

                if (in_array('OPT_RD_COMMENT', $camp_opt)) {

                    // comments
                    echo '<br>Getting comments';
                    $comments_link = ($img['item_link'] . '.json');

                    // curl get
                    $x = 'error';

                    curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($comments_link));
                    $exec = curl_exec($this->ch);
                    $x = curl_error($this->ch);

                    if (stristr($exec, '[{')) {

                        $commentsJson = json_decode($exec);
                        $commentsJson = $commentsJson[1]->data->children;

                        $commentsJson = array_slice($commentsJson, 0, rand(25, 50));

                        echo '<br>Found ' . count($commentsJson) . ' comments ';
                        $time = current_time('mysql');

                        foreach ($commentsJson as $newComment) {

                            if (isset($newComment->data->stickied) && $newComment->data->stickied == 1) {
                                continue;
                            }

                            if (in_array('OPT_RD_TIME', $camp_opt)) {
                                $time = get_date_from_gmt(gmdate('Y-m-d H:i:s', ($newComment->data->created_utc)));
                            }

                            $commentUri = '';
                            if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {

                                try {
                                    $commentUri = 'https://www.reddit.com/u/' . $newComment->data->author;
                                } catch (Exception $e) {
                                }

                            }

                            if (isset($newComment->data->body) && wp_automatic_trim($newComment->data->body) != '') {

                                // bb replies
                                if ($camp->camp_post_type == 'topic' && function_exists('bbp_insert_reply')) {

                                    $post_parent = $post_topic_id = $id;

                                    $reply_data = array(
                                        'post_parent' => $post_parent,
                                        'post_content' => $newComment->data->body,
                                        'post_author' => false,
                                        'post_date' => $time,
                                    );
                                    $reply_meta = array(
                                        'topic_id' => $post_topic_id,
                                        'anonymous_name' => $newComment->data->author,
                                        'anonymous_website' => $commentUri,
                                    );
                                    $ret = bbp_insert_reply($reply_data, $reply_meta);
                                } else {

                                    $data = array(
                                        'comment_post_ID' => $id,
                                        'comment_author' => $newComment->data->author,
                                        'comment_author_email' => '',
                                        'comment_author_url' => $commentUri,
                                        'comment_content' => $newComment->data->body,
                                        'comment_type' => '',
                                        'comment_parent' => 0,

                                        'comment_author_IP' => '127.0.0.1',
                                        'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
                                        'comment_date' => $time,
                                        'comment_approved' => 1,
                                    );

                                    $this->wp_automatic_insert_comment($data);
                                }
                            }
                        }
                    } else {
                        echo '<br>Not valid reply from Reddit';
                    }
                }
            }

            //comments_to_post is an array of comments to be imported as comments
            //array(
            //'comment_author' => 'John Doe',
            //'comment_content' => 'Comment content',
            //'comment_date' => '2019-01-01 12:00:00',
            //'comment_rating' => '5',
            //'comment_author_url' => 'https://example.com',
            //'comment_author_image' => 'https://example.com/image.jpg',
            //)
            if (isset($img['comments_to_post']) && is_array($img['comments_to_post'])) {

                // comments
                echo '<br>Trying to post comments';

                $time = current_time('mysql');

                $comments = $img['comments_to_post'];

                if (count($comments) > 0) {

                    echo '<br>Found ' . count($comments) . ' comment to post from';

                    // random count
                    $commentsCount = count($comments);
                    if ($commentsCount > 40) {
                        $commentsCount = rand(20, 40);
                        echo '...Posting ' . $commentsCount;
                    }

                    $i = 0;
                    foreach ($comments as $comment) {

                        $i++;

                        if ($i > $commentsCount) {
                            break;
                        }

                        if (isset($comment['comment_author']) && isset($comment['comment_content']) && wp_automatic_trim($comment['comment_content']) != '') {

                            $commentText = $comment['comment_content'];

                            $commentAuthor = $comment['comment_author'];
                            $commentAuthorID = '';

                            //if isset $comment ['comment_author_id']
                            if (isset($comment['comment_author_id'])) {
                                $commentAuthorID = $comment['comment_author_id'];
                            }

                            $commentUri = '';

                            if (!in_array('OPT_NO_COMMENT_LINK', $camp_opt)) {
                                $commentUri = $comment['comment_author_url'];
                            }

                            if (in_array('OPT_IT_DATE', $camp_opt)) {
                                $time = $comment['comment_date'];
                            }

                            if (wp_automatic_trim($commentText) != '') {

                                $anonymous_email = '';

                                // set email to the image url comment_author_image
                                if (isset($comment['comment_author_image']) && wp_automatic_trim($comment['comment_author_image']) != '') {

                                    //add to the URI |image url
                                    $commentUri = $comment['comment_author_image'] . '|' . $commentUri;

                                }

                                // bb replies
                                if ($camp->camp_post_type == 'topic' && function_exists('bbp_insert_reply')) {

                                    $post_parent = $post_topic_id = $id;

                                    $reply_data = array(
                                        'post_parent' => $post_parent,
                                        'post_content' => $commentText,
                                        'post_author' => false,
                                        'post_date' => $time,
                                    );
                                    $reply_meta = array(
                                        'topic_id' => $post_topic_id,
                                        'anonymous_name' => $commentAuthor,
                                        'anonymous_email' => $anonymous_email,
                                        'anonymous_website' => $commentUri,
                                    );
                                    $ret = bbp_insert_reply($reply_data, $reply_meta);
                                } else {

                                    $data = array(
                                        'comment_post_ID' => $id,
                                        'comment_author' => $commentAuthor,
                                        'comment_author_email' => $anonymous_email,
                                        'comment_author_url' => $commentUri,
                                        'comment_content' => $commentText,
                                        'comment_type' => '',
                                        'comment_parent' => 0,

                                        'comment_author_IP' => '
											',
                                        'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:
											',
                                        'comment_date' => $time,
                                        'comment_approved' => 1,
                                    );

                                    if (isset($comment['comment_rating'])) {
                                        $data['comment_rating'] = $comment['comment_rating'];
                                    }

                                    if (isset($comment['comment_author_image'])) {
                                        $data['comment_author_image'] = $comment['comment_author_image'];
                                    }

                                    $this->wp_automatic_insert_comment($data);

                                }
                            }
                        }

                    }
                } else {
                    //echo '<br>No comments found';
                }
            }

            // After ebay
            if (in_array('OPT_EB_REDIRECT_END', $camp_opt)) {
                echo '<br>Setting expiry date: ' . $img['item_end_date'];

                $expiry_date = strtotime($img['item_end_date']);

                add_post_meta($id, 'wp_automatic_redirect_date', $expiry_date);
                add_post_meta($id, 'wp_automatic_redirect_link', $camp_general['cg_eb_redirect_end']);
            }

            if (in_array('OPT_EB_TRASH', $camp_opt)) {
                echo '<br>Setting trash date: ' . $img['item_end_date'];

                $expiry_date = strtotime($img['item_end_date']);

                add_post_meta($id, 'wp_automatic_trash_date', $expiry_date);
            }

            // setting post tags
            if (!isset($post_tags)) {
                $post_tags = array();
            }

            if (in_array('OPT_ADD_TAGS', $camp_opt)) {

                // replace fields
                $cg_post_tags = $camp_general['cg_post_tags'];
                if (stristr($camp_general['cg_post_tags'], '[')) {

                    foreach ($img as $key => $val) {
                        if (!is_array($val)) {
                            $cg_post_tags = wp_automatic_str_replace('[' . $key . ']', wp_automatic_trim($val), $cg_post_tags);
                        }
                    }
                }

                $post_tags = array_filter(explode("\n", $cg_post_tags));

                $max = $camp_general['cg_tags_limit'];
                if (!is_numeric($max)) {
                    $max = 100;
                }

                if (in_array('OPT_RANDOM_TAGS', $camp_opt) && count($post_tags) > $max) {

                    $rand_keys = array_rand($post_tags, $max);

                    if (is_array($rand_keys)) {

                        $temp_tags = array();
                        foreach ($rand_keys as $key) {
                            $temp_tags[] = $post_tags[$key];
                        }
                    } else {

                        // single value selected like 0

                        $temp_tags[] = $post_tags[$rand_keys];
                    }

                    $post_tags = $temp_tags;
                }
            }

            if (in_array('OPT_ORIGINAL_TAGS', $camp_opt) || in_array('OPT_ORIGINAL_META', $camp_opt)) {

                print_r($img['tags']);

                $new_tags = explode(',', $img['tags']);

                if (count($new_tags) > 0) {
                    $post_tags = array_merge($post_tags, $new_tags);
                }
            }

            // title tags
            if (in_array('OPT_TITLE_TAG', $camp_opt)) {

                $validTitleWords = $this->wp_automatic_generate_tags($post_title);

                $post_tags = array_merge($post_tags, $validTitleWords);
            }

            // Keyword to tag
            if (in_array('OPT_KEYWORD_TAG', $camp_opt) && wp_automatic_trim($camp_general['cg_keyword_tag']) != '') {
                echo '<br>Keyword to tag check started...';

                $content_to_check = in_array('OPT_KEYWORD_NO_CNT_TAG', $camp_opt) ? '' : $post_content;
                $content_to_check .= in_array('OPT_KEYWORD_TTL_TAG', $camp_opt) ? ' ' . $post_title : '';

                $cg_keyword_tag = $camp_general['cg_keyword_tag'];
                $cg_keyword_tag_rules = array_filter(explode("\n", $cg_keyword_tag));

                foreach ($cg_keyword_tag_rules as $cg_keyword_tag_rule) {
                    if (stristr($cg_keyword_tag_rule, '|')) {

                        $cg_keyword_tag_rule = wp_automatic_trim($cg_keyword_tag_rule);

                        $cg_keyword_tag_rule_parts = explode('|', $cg_keyword_tag_rule);

                        $cg_keyword_tag_rule_keyword = $cg_keyword_tag_rule_parts[0];
                        $cg_keyword_tag_rule_tag = $cg_keyword_tag_rule_parts[1];

                        $was_found = true; // ini

                        $keys_to_check = explode(',', $cg_keyword_tag_rule_keyword);

                        foreach ($keys_to_check as $keys_to_check_single) {
                            if (!preg_match('{\b' . preg_quote(wp_automatic_trim($keys_to_check_single)) . '\b}siu', $content_to_check)) {

                                $was_found = false;
                                break;
                            }
                        }

                        if ($was_found) {

                            echo '<br><- Key ' . $cg_keyword_tag_rule_keyword . ' exists adding tag:' . $cg_keyword_tag_rule_tag;

                            if (stristr($cg_keyword_tag_rule_tag, ',')) {

                                $post_tags = array_merge($post_tags, explode(',', $cg_keyword_tag_rule_tag));
                            } elseif (wp_automatic_trim($cg_keyword_tag_rule_tag)) {

                                $post_tags[] = $cg_keyword_tag_rule_tag;
                            }
                        }
                    }
                }
            }

            if (count(array_filter($post_tags)) > 0) {

                $post_tags = array_filter($post_tags);

                echo '<br>Setting ' . count($post_tags) . ' post tags as tags';

                if (in_array('OPT_TAXONOMY_TAG', $camp_opt)) {

                    wp_set_post_terms($id, implode(',', $post_tags), wp_automatic_trim($camp_general['cg_tag_tax']), true);
                } else {
                    wp_set_post_tags($id, implode(',', $post_tags), true);
                }
            }

            // now timestamp
            $now = time();

            //if posting from eBay, add custom field product_price_updated_ebay with the now timestamp
            //to be used for price updates
            if ($camp_type == 'eBay') {
                

                //merge product_price_updated_ebay, product_price, product_list_price with the rest of custom fields
                $camp_post_custom_k = array_merge(array(
                    
                    'product_price',
                    'product_list_price',
                    'item_api_id'
                ), $camp_post_custom_k);

                $camp_post_custom_v = array_merge(array(
                    
                    $img['item_price'],
                    $img['item_marketing_price'],
                    $img['item_api_id']
                ), $camp_post_custom_v);

                //if item_api_id is not empty, merge product_price_updated_ebay with value now
                if (isset($img['item_api_id']) && wp_automatic_trim($img['item_api_id']) != '') {
                    $camp_post_custom_k[] = 'product_price_updated_ebay';
                    $camp_post_custom_v[] = $now;
                }

            }

            // amazon woocommerce integration
            if ($camp_type == 'Amazon' && $camp->camp_post_type == 'product') {

                $camp_post_custom_k = array_merge(array(
                    'product_price_updated',
                    'product_asin',
                    'product_price',
                    'product_list_price',
                    '_regular_price',
                    '_price',
                    '_sale_price',
                    '_visibility',
                    '_product_url',
                    '_button_text',
                    '_product_type',
                ), $camp_post_custom_k);

                $wp_automatic_woo_buy = get_option('wp_automatic_woo_buy', 'Buy Now');
                if (wp_automatic_trim($wp_automatic_woo_buy) == '') {
                    $wp_automatic_woo_buy = 'Buy Now';
                }

                $camp_post_custom_v = array_merge(array(
                    $now,
                    '[product_asin]',
                    '[product_price]',
                    '[product_list_price]',
                    '[list_price_numeric]',
                    '[price_numeric]',
                    '[price_numeric]',
                    'visible',
                    '[product_link]',
                    $wp_automatic_woo_buy,
                    'external',
                ), $camp_post_custom_v);

                // product gallery
                if (isset($img['product_imgs']) && stristr($img['product_imgs'], ',') && in_array('OPT_AM_GALLERY', $camp_opt)) {

                    echo '<br>Multiple images found setting a gallery';
                    $attachmentsIDs = array();

                    $product_imgs_txt = $img['product_imgs'];
                    $product_imgs = explode(',', $product_imgs_txt);

                    // first image already attached
                    if (isset($attach_id)) {

                        // $attachmentsIDs[] = $attach_id;
                        unset($product_imgs[0]);
                    }

                    // set rest images as attachments
                    foreach ($product_imgs as $product_img) {
                        echo '<br>Attaching:' . $product_img;
                        $newAttach = $this->attach_image($product_img, $camp_opt, $post_id ,$post_title);

                        if (is_numeric($newAttach) && $newAttach > 0) {
                            $attachmentsIDs[] = $newAttach;
                        }
                    }

                    if (count($attachmentsIDs) > 0) {

                        $attachmentsIDsStr = implode(',', $attachmentsIDs);
                        add_post_meta($id, '_product_image_gallery', $attachmentsIDsStr);
                    }
                }
                
                
                //add product reviews if option OPT_AM_REVIEWS is enabled and product_reviews is set and is array and is not empty
                if (in_array('OPT_AM_REVIEWS', $camp_opt) && isset($img['product_reviews']) && is_array($img['product_reviews']) && count($img['product_reviews']) > 0) {

                    $reviews = $img['product_reviews'];

                    //report count of reviews to be imported
                    echo '<br>Found ' . count($reviews) . ' reviews to be imported';

                    //add reviews
                    foreach ($reviews as $review) {

                        $review_author = $review['reviewer_name'];
                        $review_content = $review['review_title'] . "\n" . $review['review_text'];
                        $review_rating = $review['review_rating'];

                        //correct review rating from 5.0 out of 5 stars to 5
                        if (stristr($review_rating, 'out of 5 stars')) {
                            $review_rating = str_replace('out of 5 stars', '', $review_rating);
                            $review_rating = floatval($review_rating);
                        }

                        $review_data = array(
                            'comment_post_ID' => $id,
                            'comment_author' => $review_author,
                            'comment_author_email' => '',
                            'comment_author_url' => '',
                            'comment_content' => $review_content,
                            'comment_type' => '',
                            'comment_parent' => 0,

                            'comment_author_IP' => '',
                        );

                        if (isset($review['rating'])) {
                            $review_data['comment_rating'] = $review['rating'];
                        }

                        if (isset($review['author_image'])) {
                            $review_data['comment_author_image'] = $review['author_image'];
                        }

                        if (isset($review['date'])) {
                            $review_data['comment_date'] = $review['date'];
                        }

                        $this->wp_automatic_insert_comment($review_data);

                    }
                }

                wp_set_object_terms($id, 'external', 'product_type');

            } elseif ($camp_type == 'eBay' && $camp->camp_post_type == 'product') {

                // product gallery
                if (isset($img['item_images']) && is_array($img['item_images']) && count($img['item_images']) > 1 && in_array('OPT_EB_GALLERY', $camp_opt)) {

                    echo '<br>Multiple images found setting a gallery for Woo';
                    $attachmentsIDs = array();

                    $product_imgs = $img['item_images'];

                    // first image already attached
                    if (isset($attach_id)) {

                        // $attachmentsIDs[] = $attach_id;
                        unset($product_imgs[0]);
                    }

                    // set rest images as attachments
                    foreach ($product_imgs as $product_img) {
                        echo '<br>Attaching:' . $product_img;
                        $newAttach = $this->attach_image($product_img, $camp_opt, $post_id,$post_title);

                        if (is_numeric($newAttach) && $newAttach > 0) {
                            $attachmentsIDs[] = $newAttach;
                        }
                    }

                    if (count($attachmentsIDs) > 0) {

                        $attachmentsIDsStr = implode(',', $attachmentsIDs);
                        add_post_meta($id, '_product_image_gallery', $attachmentsIDsStr);
                    }
                }

                $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                    '_regular_price',
                    '_price',
                    '_sale_price',
                    '_visibility',
                    '_product_url',
                    '_button_text',
                    '_product_type',
                ));
                $wp_automatic_woo_buy = get_option('wp_automatic_woo_buy2', 'Buy Now');
                if (wp_automatic_trim($wp_automatic_woo_buy) == '') {
                    $wp_automatic_woo_buy = 'Buy Now';
                }

                $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                    '[item_marketing_price]',
                    '[item_price_numeric]',
                    '[item_price_numeric]',
                    'visible',
                    '[item_link]',
                    $wp_automatic_woo_buy,
                    'external',
                ));

                wp_set_object_terms($id, 'external', 'product_type');
            } elseif ($camp_type == 'Craigslist' && $camp->camp_post_type == 'product') {

                // product gallery
                if (isset($img['item_images']) && is_array($img['item_images']) && count($img['item_images']) > 1 && in_array('OPT_CL_GALLERY', $camp_opt)) {

                    echo '<br>Multiple images found setting a gallery for Woo';
                    $attachmentsIDs = array();

                    $product_imgs = $img['item_images'];

                    // first image already attached
                    if (isset($attach_id)) {

                        // $attachmentsIDs[] = $attach_id;
                        unset($product_imgs[0]);
                    }

                    // set rest images as attachments
                    foreach ($product_imgs as $product_img) {
                        echo '<br>Attaching:' . $product_img;
                        $newAttach = $this->attach_image($product_img, $camp_opt, $post_id,$post_title);

                        if (is_numeric($newAttach) && $newAttach > 0) {
                            $attachmentsIDs[] = $newAttach;
                        }
                    }

                    if (count($attachmentsIDs) > 0) {

                        $attachmentsIDsStr = implode(',', $attachmentsIDs);
                        add_post_meta($id, '_product_image_gallery', $attachmentsIDsStr);
                    }
                }

                $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                    '_regular_price',
                    '_price',
                    '_visibility',
                    '_product_url',
                    '_button_text',
                    '_product_type',
                ));
                $wp_automatic_woo_buy = get_option('wp_automatic_woo_buy2', 'Buy Now');
                if (wp_automatic_trim($wp_automatic_woo_buy) == '') {
                    $wp_automatic_woo_buy = 'Buy Now';
                }

                $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                    '[item_price_numeric]',
                    '[item_price_numeric] ',
                    'visible',
                    '[item_link]',
                    $wp_automatic_woo_buy,
                    'external',
                ));

                wp_set_object_terms($id, 'external', 'product_type');
            } elseif ($camp_type == 'Aliexpress' && $camp->camp_post_type == 'product') {

                // imgs array

                $imgs_arr = explode(',', $img['item_images']);

                // product gallery
                if (isset($img['item_images']) && is_array($imgs_arr) && count($imgs_arr) > 1 && in_array('OPT_AE_GALLERY', $camp_opt)) {

                    echo '<br>Multiple images found setting a gallery for Woo';
                    $attachmentsIDs = array();

                    $product_imgs = $imgs_arr;

                    // first image already attached
                    if (isset($attach_id)) {

                        // $attachmentsIDs[] = $attach_id;
                        unset($product_imgs[0]);
                    }

                    // set rest images as attachments
                    foreach ($product_imgs as $product_img) {
                        echo '<br>Attaching:' . $product_img;
                        $newAttach = $this->attach_image($product_img, $camp_opt, $post_id,$post_title);

                        if (is_numeric($newAttach) && $newAttach > 0) {
                            $attachmentsIDs[] = $newAttach;
                        }
                    }

                    if (count($attachmentsIDs) > 0) {

                        $attachmentsIDsStr = implode(',', $attachmentsIDs);
                        add_post_meta($id, '_product_image_gallery', $attachmentsIDsStr);
                    }
                }

                $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                    '_regular_price',
                    '_sale_price',
                    '_price',
                    'product_list_price',
                    'product_price',
                    '_visibility',
                    '_product_url',
                    '_button_text',
                    '_product_type',
                ));
                $wp_automatic_woo_buy = get_option('wp_automatic_woo_buy2', 'Buy Now');
                if (wp_automatic_trim($wp_automatic_woo_buy) == '') {
                    $wp_automatic_woo_buy = 'Buy Now';
                }

                $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                    '[item_price_original_numeric]',
                    '[item_price_numeric] ',
                    '[item_price_numeric] ',
                    '[item_price_original_numeric]',
                    '[item_price_numeric] ',
                    'visible',
                    '[item_url]',
                    $wp_automatic_woo_buy,
                    'external',
                ));

                wp_set_object_terms($id, 'external', 'product_type');
            } elseif ($camp_type == 'Amazon' && $camp->camp_post_type != 'product') {

                $camp_post_custom_k = array_merge(array(
                    'product_price_updated',
                    'product_asin',
                    'product_price',
                    'product_list_price',
                ), $camp_post_custom_k);
                $camp_post_custom_v = array_merge(array(
                    $now,
                    '[product_asin]',
                    '[product_price]',
                    '[product_list_price]',
                ), $camp_post_custom_v);
            } elseif ($camp_type == 'Aliexpress' && $camp->camp_post_type != 'product') {

                $camp_post_custom_k = array_merge(array(
                    'product_price_updated',

                    'product_price',
                    'product_list_price',
                ), $camp_post_custom_k);
                $camp_post_custom_v = array_merge(array(
                    $now,

                    '[item_price_numeric]',
                    '[item_price_original_numeric]',
                ), $camp_post_custom_v);
            } elseif ($camp_type == 'Walmart' && $camp->camp_post_type != 'product') {

                $camp_post_custom_k = array_merge(array(
                    'product_price_updated',
                    'product_upc',
                    'product_price',
                    'product_list_price',
                ), $camp_post_custom_k);
                $camp_post_custom_v = array_merge(array(
                    $now,
                    '[item_upc]',
                    '$[item_price]',
                    '$[item_list_price]',
                ), $camp_post_custom_v);
            } elseif ($camp_type == 'Walmart' && $camp->camp_post_type == 'product') {

                // affiliate item_link
                /*
                 * if(stristr( $post_content , 'linksynergy')){
                 * $buyShortCode = '[product_affiliate_url]';
                 * }else{
                 * $buyShortCode = '[item_link]';
                 * }
                 */

                $buyShortCode = '[product_affiliate_url]';

                $camp_post_custom_k = array_merge(array(
                    'product_price_updated',
                    'product_upc',
                    'product_price',
                    'product_list_price',
                    '_regular_price',
                    '_price',
                    '_sale_price',
                    '_visibility',
                    '_product_url',
                    '_button_text',
                    '_product_type',
                ), $camp_post_custom_k);

                $wp_automatic_woo_buy = get_option('wp_automatic_woo_buy', 'Buy Now');
                if (wp_automatic_trim($wp_automatic_woo_buy) == '') {
                    $wp_automatic_woo_buy = 'Buy Now';
                }

                $camp_post_custom_v = array_merge(array(
                    $now,
                    '[item_upc]',
                    '$[item_price]',
                    '$[item_list_price]',
                    '[item_list_price]',
                    '[item_price]',
                    '[item_price]',
                    'visible',
                    $buyShortCode,
                    $wp_automatic_woo_buy,
                    'external',
                ), $camp_post_custom_v);

                // product gallery
                if (isset($img['item_imgs']) && stristr($img['item_imgs'], ',') && in_array('OPT_WM_GALLERY', $camp_opt)) {

                    echo '<br>Multiple images found setting a gallery';
                    $attachmentsIDs = array();

                    $product_imgs_txt = $img['item_imgs'];
                    $product_imgs = explode(',', $product_imgs_txt);

                    // first image already attached
                    if (isset($attach_id)) {

                        // $attachmentsIDs[] = $attach_id;
                        unset($product_imgs[0]);
                    }

                    // set rest images as attachments
                    foreach ($product_imgs as $product_img) {
                        echo '<br>Attaching:' . $product_img;
                        $newAttach = $this->attach_image($product_img, $camp_opt, $post_id,$post_title);

                        if (is_numeric($newAttach) && $newAttach > 0) {
                            $attachmentsIDs[] = $newAttach;
                        }
                    }

                    if (count($attachmentsIDs) > 0) {

                        $attachmentsIDsStr = implode(',', $attachmentsIDs);
                        add_post_meta($id, '_product_image_gallery', $attachmentsIDsStr);
                    }
                }

                wp_set_object_terms($id, 'external', 'product_type');
            } elseif ($camp_type == 'Envato' && $camp->camp_post_type == 'product') {

                $camp_post_custom_k = array_merge(array(
                    'product_price_updated',
                    'product_upc',
                    'product_price',
                    'product_list_price',
                    '_regular_price',
                    '_price',
                    '_sale_price',
                    '_visibility',
                    '_product_url',
                    '_button_text',
                    '_product_type',
                ), $camp_post_custom_k);

                $wp_automatic_woo_buy = get_option('wp_automatic_woo_buy', 'Buy Now');
                if (wp_automatic_trim($wp_automatic_woo_buy) == '') {
                    $wp_automatic_woo_buy = 'Buy Now';
                }

                $buyShortCode = '[item_link_affiliate]';

                $camp_post_custom_v = array_merge(array(
                    $now,
                    '[item_upc]',
                    '$[item_price]',
                    '$[item_price]',
                    '[item_price]',
                    '[item_price]',
                    '[item_price]',
                    'visible',
                    $buyShortCode,
                    $wp_automatic_woo_buy,
                    'external',
                ), $camp_post_custom_v);

                wp_set_object_terms($id, 'external', 'product_type');
            } elseif ($camp->camp_post_type == 'product') {

                $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                    '_visibility',
                ));
                $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                    'visible',
                ));

                wp_set_object_terms($id, 'external', 'product_type');
            }

            // Not external option
            if (in_array('OPT_SIMPLE', $camp_opt)) {

                wp_set_object_terms($id, 'simple', 'product_type');
            } elseif (in_array('OPT_PRODUCT_EXTERNAL', $camp_opt)) {

                wp_set_object_terms($id, 'external', 'product_type');

                // _product_url
                if (!in_array('_product_url', $camp_post_custom_k)) {
                    $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                        '_product_url',
                    ));
                    $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                        $source_link,
                    ));
                }

                //setting the post as external
                if (function_exists('wc_get_product')) {
                    echo '<br>Setting product as external';
                    $product = wc_get_product($id);
                    
                    if ( $product ) {
                        // Ensure the product is set as external
                        if ( ! is_a( $product, 'WC_Product_External' ) ) {

                            echo '<br>Changing product to external';

                            // Change the product to an external product
                            $product = new WC_Product_External( $id );
                        
                            

                             //setting the product url
                              $product->set_product_url($source_link);

                                //save
                            $product->save();
                        
                        }else{
                            echo '<br>Product is already external';
                        }
                    }
                    
                    
                    
                    
                   

                  

                }

            }

            // TrueMag integration
            if (($camp_type == 'Youtube' || $camp_type == 'Vimeo')) {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {

                    echo '<br>TrueMag/NewsTube theme exists adabting config..';

                    $duration_key_val = ($camp_type == 'Youtube') ? '[vid_duration]' : '[vid_duration_readable]';

                    $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                        'tm_video_url',
                        '_count-views_all',
                        'video_duration',
                    ));

                    $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                        '[source_link]',
                        '[vid_views]',
                        '[vid_duration]',
                    ));

                    // adding likes n dislikes
                    $vid_likes = isset($img['vid_likes']) ? $img['vid_likes'] : '';
                    $vid_dislikes = isset($img['vid_dislikes']) ? $img['vid_dislikes'] : '';

                    // adding likes
                    if ($vid_likes > 0) {

                        try {

                            $query = "INSERT INTO {$this->db->prefix}wti_like_post SET ";
                            $query .= "post_id = '" . $id . "', ";
                            $query .= "value = '$vid_likes', ";
                            $query .= "date_time = '" . date('Y-m-d H:i:s') . "', ";
                            $query .= "ip = ''";
                            @$this->db->query($query);
                        } catch (Exception $e) {
                        }
                    }

                    if ($vid_dislikes > 0 && $camp_type == 'Youtube') {

                        $query = "INSERT INTO {$this->db->prefix}wti_like_post SET ";
                        $query .= "post_id = '" . $id . "', ";
                        $query .= "value = '-$vid_dislikes', ";
                        $query .= "date_time = '" . date('Y-m-d H:i:s') . "', ";
                        $query .= "ip = ''";
                        @$this->db->query($query);
                    }
                }
            }

            // truemag dailymotion integration
            if ($camp_type == 'DailyMotion') {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {

                    echo '<br>TrueMag/NewsTube theme exists adabting config..';
                    $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                        'tm_video_url',
                        '_count-views_all',
                    ));
                    $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                        '[source_link]',
                        '[item_views]',
                    ));
                }
            }

            // trumag instagram integration
            if (($camp_type == 'Instagram') && stristr($abcont, '[embed]')) {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {

                    echo '<br>TrueMag/NewsTube theme exists adabting config..';

                    // extract video url
                    preg_match('{\[embed\](.*?)\[/embed\]}', $abcont, $embedMatchs);

                    $embedUrl = $embedMatchs[1];

                    $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                        'tm_video_file',
                    ));
                    $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                        $embedUrl,
                    ));

                    // adding likes n dislikes
                    $vid_likes = $img['item_likes_count'];

                    // adding likes
                    if ($vid_likes > 0) {

                        try {

                            $query = "INSERT INTO {$this->db->prefix}wti_like_post SET ";
                            $query .= "post_id = '" . $id . "', ";
                            $query .= "value = '$vid_likes', ";
                            $query .= "date_time = '" . date('Y-m-d H:i:s') . "', ";
                            $query .= "ip = ''";
                            @$this->db->query($query);
                        } catch (Exception $e) {
                        }
                    }
                }
            }

            // truemag facebook integration
            if ($camp_type == 'Facebook') {

                if (isset($img['vid_url'])) {

                    if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {

                        echo '<br>TrueMag setup and video exists...';

                        $camp_post_custom_k = array_merge($camp_post_custom_k, array(
                            'tm_video_url',
                        ));
                        $camp_post_custom_v = array_merge($camp_post_custom_v, array(
                            $img['vid_url'],
                        ));
                    }
                }
            }

            // replacing tags on custom fields values

            

            //add the post_title, post content to the img array
            if (isset($my_post_copy['post_title'])) {
                $img['post_title'] = $my_post_copy['post_title'];
            }

            if (isset($my_post_copy['post_content'])) {
                $img['post_content'] = $my_post_copy['post_content'];
            }

            //if camp_post_custom_v is an array and count is more than 0, echo 'correct'
            if (is_array($camp_post_custom_v) && count($camp_post_custom_v) > 0) {
                
            
    

                $camp_post_custom_v = implode('#****#', $camp_post_custom_v);
                foreach ($img as $key => $val) {
                    if (!is_array($val)) {
                        $camp_post_custom_v = wp_automatic_str_replace('[' . $key . ']', $val, $camp_post_custom_v);
                    }

                    // feed custom attributes
                    if ($camp_type == 'Feeds') {

                        $attributes = $img['attributes'];

                        foreach ($attributes as $attributeKey => $attributeValue) {

                            $camp_post_custom_v = wp_automatic_str_replace('[' . $attributeKey . ']', $attributeValue[0]['data'], $camp_post_custom_v);
                        }
                    }
                }

                $camp_post_custom_v = explode('#****#', $camp_post_custom_v);
          }
           

            // NewsPaper theme integration
            if (($camp_type == 'Youtube' || $camp_type == 'Vimeo') && function_exists('td_bbp_change_avatar_size') && !in_array('OPT_NO_NEWSPAPER', $wp_automatic_options)) {

                echo '<br>NewsPaper theme found integrating..';

                $td_video = array();
                $td_video['td_video'] = $img['vid_url'];
                $td_video['td_last_video'] = $img['vid_url'];

                $camp_post_custom_k[] = 'td_post_video';
                $camp_post_custom_v[] = $td_video;

                // format
                echo '<br>setting post format to Video';
                set_post_format($id, 'video');

                // custom field
            }

            // adding custom fields

            $in = 0;
            if (count($camp_post_custom_k) > 0) {

                foreach ($camp_post_custom_k as $key) {
                    if (wp_automatic_trim($key) != '') {
                        echo '<br>Setting custom field ' . $key;

                        // correcting serialized arrays if $camp_post_custom_v [$in] is a string and starts with a: and ends with }
                        if (!is_array($camp_post_custom_v[$in]) && preg_match('!^a:\d*:\{!', $camp_post_custom_v[$in])) {

                            preg_match_all('!s:(\d*):"(.*?)"!', $camp_post_custom_v[$in], $arry_pts);

                            $s = 0;

                            foreach ($arry_pts[0] as $single_prt) {
                                $camp_post_custom_v[$in] = wp_automatic_str_replace($single_prt, 's:' . strlen($arry_pts[2][$s]) . ':"' . $arry_pts[2][$s] . '"', $camp_post_custom_v[$in]);
                                $s++;
                            }

                            echo ' altered ' . $s . ' serialized array keys';
                        }

                        // serialized arrays
                        if (is_serialized($camp_post_custom_v[$in])) {
                            $camp_post_custom_v[$in] = unserialize($camp_post_custom_v[$in]);
                        }

                        $key_val = $camp_post_custom_v[$in];

                        

                        if (!is_array($key_val) && stristr($key_val, 'rand_')) {

                            $key_val_clean = wp_automatic_str_replace(array(
                                '[',
                                ']',
                            ), '', $key_val);
                            $val_parts = explode('_', $key_val_clean);

                            if (count($val_parts) == 3 && is_numeric($val_parts[1]) && is_numeric($val_parts[2])) {
                                $key_val = rand($val_parts[1], $val_parts[2]);
                            }
                        }

                        if (!is_array($key_val) && stristr($key_val, 'formated_date')) {
                            $key_val = do_shortcode($key_val);
                        }

                        // process gpt3 prompts if found in key_val if key_val is string
                        if (!is_array($key_val) && stristr($key_val, '[gpt')) {
                            $key_val = $this->openai_gpt3_tags_replacement($key_val);
                        }

                        //if key val is not an array and trim it is [inline_link], extract the inline link from the ab cont
                        if (!is_array($key_val) && wp_automatic_trim($key_val) == '[inline_link]') {

                            //extract the inline link from the ab cont
                            $inline_link = $this->extract_inline_link($abcont);

                            //if inline link is not empty
                            $key_val = ''; //reset
                            if (wp_automatic_trim($inline_link) != '') {
                                $key_val = $inline_link;
                            }
                        }

                        // linkjuicer plugin integration
                        // if key is ilj_linkdefinition, modify the value from keyword1,keyword2 to a serialzied array
                        if ($key == 'ilj_linkdefinition') {

                            echo '<br>Keyword before conversion: ' . $key_val;

                            //split the value by comma
                            $ilj_linkdefinition = explode(',', $key_val);

                            //report plugin integration detected converting the keywords to array
                            echo '<-- LinkJuicer plugin integration detected converting the keywords to array';

                            //update the value
                            $key_val = $ilj_linkdefinition;

                        }

                        if ($key == 'excerpt') {

                            $my_post = array(
                                'ID' => $id,
                                'post_excerpt' => $key_val,
                            );

                            wp_update_post($my_post);
                        } elseif (stristr($key, 'taxonomy_')) {

                            wp_set_post_terms($id, $key_val, wp_automatic_str_replace('taxonomy_', '', $key), true);

                        } elseif (stristr($key, 'attribute_')) {

                            $attribute_name = wp_automatic_str_replace('attribute_', '', $key);
                            $attribute_value = $key_val;

                            wp_automatic_add_product_attribute($id, $attribute_name, $attribute_value);

                            //report
                            echo '<-- Added attribute ' . $attribute_name . ' with value ' . $attribute_value;

                        } elseif (wp_automatic_trim($key) == 'woo_gallery' && $camp->camp_post_type == 'product') {

                            echo '<br>Setting gallery from set rule ' . $key;

                            //if contains <img, use the img tag to extract the images
                            if (stristr($key_val, '<img')) {
                                preg_match_all('{<img.*? src="(.*?)".*?}s', $key_val, $key_imgs_matches);
                            }else{
                                //find any image link 
                                preg_match_all('{(https?://[^"\' ]+?\.(?:jpg|jpeg|gif|png|bmp|webp))}', $key_val, $key_imgs_matches);
                            }

                            $key_imgs_matches = $key_imgs_matches[1];

                            if (count($key_imgs_matches) > 0) {

                                echo '<-- Found possible ' . count($key_imgs_matches) . ' images';

                                $attachmentsIDs = array();

                                $product_imgs = $key_imgs_matches;

                                // first image already attached
                                if (isset($attach_id)) {

                                    // $attachmentsIDs[] = $attach_id;
                                    unset($product_imgs[0]);
                                }

                                // set rest images as attachments
                                foreach ($product_imgs as $product_img) {
                                    echo '<br>Attaching:' . $product_img;
                                    $newAttach = $this->attach_image($product_img, $camp_opt, $post_id,$post_title);

                                    if (is_numeric($newAttach) && $newAttach > 0) {
                                        $attachmentsIDs[] = $newAttach;
                                    }
                                }

                                if (count($attachmentsIDs) > 0) {

                                    $attachmentsIDsStr = implode(',', $attachmentsIDs);
                                    add_post_meta($id, '_product_image_gallery', $attachmentsIDsStr);
                                }
                            } else {
                                echo '<-- did not find valid images';
                            }
                        } else {

                            if (($camp_type == 'Feeds' || $camp_type == 'Single' || $camp_type == 'Multi') && (wp_automatic_trim($key) == '_price' || wp_automatic_trim($key) == '_sale_price' || wp_automatic_trim($key) == '_regular_price')) {

                                preg_match('{[\d|\.|,]+}', $key_val, $price_matchs);

                                $possible_price = reset($price_matchs);
                                // $possible_price =wp_automatic_str_replace( ',', '', $possible_price );
                                if (wp_automatic_trim($possible_price) != '') {
                                    $key_val = $possible_price;
                                }

                            }

                            update_post_meta($id, $key, $key_val);

                            // if is _regular_price and _sale_price is empty set _price to _regular_price
                            if (wp_automatic_trim($key) == '_regular_price' && wp_automatic_trim($key_val) != '') {
                                $sale_price = get_post_meta($id, '_sale_price', true);
                                if (wp_automatic_trim($sale_price) == '') {
                                    echo '<br>setting _price to _regular_price';
                                     
                                    $this->set_woocommerce_product_price($id, $key_val);
                                }
                            }

                            // if is _sale_price and is not empty set the _price to _sale_price
                            //fix ticket:23898
                            if (wp_automatic_trim($key) == '_sale_price' && wp_automatic_trim($key_val) != '') {
                                echo '<br>setting _price to _sale_price';
 
                                /*
                                This code was added to fix on sale filter but it caused another issue which is
                                converting the external product to simple product
                                disabling it for now 
                                Restored on 4-June-2024 as tests showed that this issue no more happens, 
                                probably was fixed in the latest woocommerce updates
                                */
                                //if wc_get_product is available
                                $this->set_woocommerce_product_price($id, $key_val);

                            }

                        }
                    }

                    $in++;
                }
            }

            //if post type is product
            if ($camp->camp_post_type == 'product') {

                // get _sale_price and _regular_price and if they are equal, delete the _sale_price fix ticket:23246
                $regular_price = get_post_meta($id, '_regular_price', true);
                $sale_price = get_post_meta($id, '_sale_price', true);
                if (wp_automatic_trim($regular_price) != '' && wp_automatic_trim($sale_price) != '' && $regular_price == $sale_price) {
                    echo '<br>deleting _sale_price as it is equal to _regular_price';
                    delete_post_meta($id, '_sale_price');
                }

            }

            // setting post format OPT_FORMAT
            if (in_array('OPT_FORMAT', $camp_opt)) {
                echo '<br>setting post format to ' . $camp_general['cg_post_format'];
                set_post_format($id, stripslashes($camp_general['cg_post_format']));
            } elseif (($camp_type == 'Youtube' || $camp_type == 'Vimeo' || $camp_type == 'DailyMotion')) {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {
                    echo '<br>setting post format to Video';
                    set_post_format($id, 'video');
                }
            } elseif (($camp_type == 'Instagram') && stristr($abcont, '[embed]')) {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {
                    echo '<br>setting post format to Video';
                    set_post_format($id, 'video');
                }
            } elseif ($camp_type == 'Facebook' && isset($img['vid_url'])) {

                if ((defined('PARENT_THEME') && (PARENT_THEME == 'truemag' || PARENT_THEME == 'newstube')) || class_exists('Cactus_video')) {
                    echo '<br>setting post format to Video';
                    set_post_format($id, 'video');
                }
            }

            // if excluded from spin, add the wp_auto_spinner_checked custom field 
            if (in_array('OPT_EXCLUDE_SPIN', $camp_opt)) {
                echo '<br>Excluded from spin option enabled, excluding this post from spinning';
                update_post_meta($id, 'wp_auto_spinner_checked', '1');
            }

            // publishing the post
            if ( ( in_array('OPT_DRAFT_PUBLISH', $camp_opt) || in_array('OPT_EXCLUDE_SPIN', $camp_opt) )  && $camp->camp_post_status == 'publish') {

                echo '<br>Publishing the post now...';
                $newUpdatedPostArr['ID'] = $id;
                $newUpdatedPostArr['post_status'] = 'publish';

                wp_update_post($newUpdatedPostArr);
            }

            if (in_array('OPT_PREVIEW_EDIT', $wp_automatic_options)) {
                $plink = admin_url('post.php?post=' . $id . '&action=edit');
                if (wp_automatic_trim($plink) == '') {
                    $plink = get_permalink($id);
                }

            } else {
                $plink = get_permalink($id);
            }

            $plink = wp_automatic_str_replace('&amp;', '&', $plink);

            $display_title = get_the_title($id);

            if (wp_automatic_trim($display_title) == '') {
                $display_title = '(no title)';
            }

            $now = date('Y-m-d H:i:s');
            $now = get_date_from_gmt($now);

            echo '<br>New Post posted: <a target="_blank" class="new_post_link" time="' . $now . '" href="' . $plink . '">' . $display_title . '</a>';
            $this->log('Posted:' . $camp->camp_id, 'New post posted:<a href="' . $plink . '">' . $title_without_emoji . '</a>');

            // clean cached prompts if any delete_cached_prompt_results
            $this->delete_cached_prompt_results();

            // returning the security filter
            add_filter('content_save_pre', 'wp_filter_post_kses');

            // duplicate cache update
            if ($this->campDuplicateLinksUpdate == true && !in_array('OPT_LINK_NOCACHE', $camp_opt)) {

                $this->campNewDuplicateLinks[$id] = $source_link;
                update_post_meta($camp->camp_id, 'wp_automatic_duplicate_cache', $this->campNewDuplicateLinks);
            }

            exit();

            print_r($ret);
        } // if title
    } // end function

    /**
     * Checks if allowed to call the source or not: compares the call limit with actual previous calls
     */
    public function is_allowed_to_call()
    {
        if ($this->sourceCallLimit == $this->sourceCallTimes) {
            echo '<br> We have called the source ' . $this->sourceCallLimit . ' times already will die now and complete next time...';
            exit();
        }

        $this->sourceCallTimes++;
    }

    /**
     * Validate if the post contains the exact match keywords and does not contain the banned words
     *
     * @param String $cnt
     *            the content
     * @param String $ttl
     *            the title
     * @param String $opt
     *            campaign options
     * @param String $camp
     *            whole camp record
     * @param boolean $after
     *            true if after the template
     */
    public function validate_exacts(&$abcont, &$title, &$camp_opt, &$camp, $after = false, $camp_general = array())
    {

        // Valid
        $valid = true;

        $exact = $camp->camp_post_exact;
        $execr = '';
        $execr = @$camp_general['cg_camp_post_regex_exact'];
        $excludeRegex = @$camp_general['cg_camp_post_regex_exclude'];

        // Validate exacts
        if (in_array('OPT_EXACT', $camp_opt)) {

            // Exact keys

            // Validating Exact
            if (wp_automatic_trim($exact) != '' && in_array('OPT_EXACT', $camp_opt) && (!in_array('OPT_EXACT_AFTER', $camp_opt) && !$after || in_array('OPT_EXACT_AFTER', $camp_opt) && $after)) {

                $valid = false;

                $exactArr = explode("\n", wp_automatic_trim($exact));
                foreach ($exactArr as $wordexact) {
                    if (wp_automatic_trim($wordexact != '')) {

                        if (in_array('OPT_EXACT_STR', $camp_opt)) {

                            if (in_array('OPT_EXACT_TITLE_ONLY', $camp_opt) && stristr(html_entity_decode($title), wp_automatic_trim($wordexact))) {

                                echo '<br>Title contains the word : ' . $wordexact;
                                $valid = true;
                                if (!in_array('OPT_EXACT_ALL', $camp_opt)) {
                                    break;
                                }

                            } elseif (!in_array('OPT_EXACT_TITLE_ONLY', $camp_opt) && (stristr(html_entity_decode($abcont), wp_automatic_trim($wordexact)) || stristr(wp_automatic_trim($wordexact), html_entity_decode($title)))) {

                                echo '<br>Content contains the word : ' . $wordexact;
                                $valid = true;
                                if (!in_array('OPT_EXACT_ALL', $camp_opt)) {
                                    break;
                                }

                            } else {

                                echo '<br>Content does not contain the word : ' . $wordexact . ' try another ';
                                $valid = false;
                                if (in_array('OPT_EXACT_ALL', $camp_opt)) {
                                    break;
                                }

                            } // match
                        } else {

                            if (in_array('OPT_EXACT_TITLE_ONLY', $camp_opt) && preg_match('/\b' . wp_automatic_trim($wordexact) . '\b/iu', html_entity_decode($title))) {
                                echo '<br>Title contains the word : ' . $wordexact;
                                $valid = true;
                                if (!in_array('OPT_EXACT_ALL', $camp_opt)) {
                                    break;
                                }

                            } elseif (!in_array('OPT_EXACT_TITLE_ONLY', $camp_opt) && (preg_match('/\b' . wp_automatic_trim($wordexact) . '\b/iu', html_entity_decode($abcont)) || preg_match('/\b' . wp_automatic_trim($wordexact) . '\b/iu', html_entity_decode($title)))) {
                                echo '<br>Content contains the word : ' . $wordexact;
                                $valid = true;
                                if (!in_array('OPT_EXACT_ALL', $camp_opt)) {
                                    break;
                                }

                            } else {
                                echo '<br>Content does not contain the word : ' . $wordexact . ' try another ';
                                $valid = false;
                                if (in_array('OPT_EXACT_ALL', $camp_opt)) {
                                    break;
                                }

                            } // match
                        }
                    } // trim wordexact
                } // foreach exactword
            } // trim exact
        }

        // VALIDATING EXCLUDES
        if ($valid == true) {

            $execl = $camp->camp_post_execlude;

            if (wp_automatic_trim($execl) != '' && in_array('OPT_EXECLUDE', $camp_opt) && (!in_array('OPT_EXECLUDE_AFTER', $camp_opt) && !$after || in_array('OPT_EXECLUDE_AFTER', $camp_opt) && $after)) {

                // additional excl
                $execl .= "\n" . $this->generalBannedWords;

                $execlArr = explode("\n", wp_automatic_trim($execl));

                if (in_array('OPT_EXECLUDE_TITLE_ONLY', $camp_opt)) {
                    $the_text_to_check = html_entity_decode($title);
                } else {
                    $the_text_to_check = html_entity_decode($title) . ' ' . html_entity_decode($abcont);
                }

                foreach ($execlArr as $wordex) {
                    if (wp_automatic_trim($wordex) != '') {

                        $wordex = wp_automatic_trim($wordex);

                        if (in_array('OPT_EXCLUDE_EXACT_STR', $camp_opt)) {

                            if (stristr($the_text_to_check, $wordex)) {

                                echo '<br>Content contains the banned word :' . $wordex . ' getting another ';
                                $valid = false;
                                break;
                            }
                        } elseif (preg_match('/\b' . wp_automatic_trim($wordex) . '\b/iu', $the_text_to_check)) {
                            echo '<br>Content contains the banned word :' . $wordex . ' getting another ';
                            $valid = false;
                            break;
                        }
                    } // trim wordexec
                } // foreach wordex
            } // trim execl
        } // valid

        // Before only REGEX check
        if (!$after) {
            // validate REGEX
            if ($valid == true) {

                if (wp_automatic_trim($execr) != '' & in_array('OPT_EXACT_REGEX', $camp_opt)) {

                    $valid = false;
                    $exactArr = explode("\n", wp_automatic_trim($execr));

                    foreach ($exactArr as $wordexact) {

                        $wordexact = wp_automatic_trim($wordexact);

                        if (wp_automatic_trim($wordexact != '')) {
                            if (preg_match('{' . $wordexact . '}ius', html_entity_decode($abcont)) || preg_match('{' . wp_automatic_trim($wordexact) . '}ius', html_entity_decode($title))) {

                                echo '<br>REGEX Matched : ' . $wordexact;
                                $valid = true;
                                break;
                            } else {
                                echo '<br>REGEX did not match : ' . $wordexact . ' try another ';
                            } // match
                        } // trim wordexact
                    } // foreach exactword
                }
            }

            // exclude if match a specific REGEX
            if ($valid == true) {

                if (wp_automatic_trim($excludeRegex) != '' & in_array('OPT_EXCLUDE_REGEX', $camp_opt)) {

                    $excludeArr = explode("\n", wp_automatic_trim($excludeRegex));

                    foreach ($excludeArr as $wordexact) {
                        $wordexact = wp_automatic_trim($wordexact);
                        if (wp_automatic_trim($wordexact != '')) {
                            if (preg_match('{' . $wordexact . '}ius', html_entity_decode($abcont)) || preg_match('{' . wp_automatic_trim($wordexact) . '}ius', html_entity_decode($title))) {

                                echo '<br>Exclude REGEX matched : ' . $wordexact;
                                $valid = false;
                                break;
                            } else {
                                echo '<br>Exclude REGEX did not match : ' . $wordexact . ' try another ';
                            } // match
                        } // trim wordexact
                    } // foreach exactword
                }
            }
        }


        // validate length
        if ($valid == true && !$after && isset($camp_general['cg_min_length']) && (in_array('OPT_MIN_LENGTH', $camp_opt) || in_array('OPT_MAX_LENGTH', $camp_opt)) && $camp->camp_type != 'Feeds') {

            echo '<br>Validating length .....';

            $contentTextual = strip_tags($abcont);
            $contentTextual = wp_automatic_str_replace(' ', '', $contentTextual);

            if (function_exists('mb_strlen')) {
                $contentLength = mb_strlen($contentTextual);
            } else {
                $contentLength = strlen($contentTextual);
            }

            unset($contentTextual);

            echo ' Content length:' . $contentLength;

            if (in_array('OPT_MIN_LENGTH', $camp_opt)) {
                if ($contentLength < $camp_general['cg_min_length']) {
                    echo '<--Shorter than the minimum(' . $camp_general['cg_min_length'] . ')... Excluding';

                    $valid = false;
                } else {
                    echo '<-- Valid Min length i.e > (' . $camp_general['cg_min_length'] . ') ';
                }
            }

            if (in_array('OPT_MAX_LENGTH', $camp_opt)) {
                if ($contentLength > $camp_general['cg_max_length']) {
                    echo '<--Longer than the maximum( ' . $camp_general['cg_max_length'] . ' )... Excluding';

                    $valid = false;
                } else {
                    echo '<-- Valid Max length i.e < (' . $camp_general['cg_max_length'] . ') ';
                }
            }
        }

        return $valid;
    }

    /**
     * Function to validate if criterias applies to the fields or not
     * if option enabled OPT_CRITERIA_ALL and all criterias are true, return false
     * if option not enabled OPT_CRITERIA_ALL and any criteria is true, return false
     * if any criteria is true and, return false
     *
     */
    public function validate_criterias($img, $camp_opt, $camp, $camp_general)
    {

        // ini
        $valid = true; // true because false will mark the whole post as invalid, we want it invalid if any criteria applies only
        $passing_criteria = 0; // count of passing criteria

        // required field values
        $cg_criteria_skip_fields = @$camp_general['cg_criteria_skip_fields'];
        $cg_criteria_skip_criterias = @$camp_general['cg_criteria_skip_criterias'];
        $cg_criteria_skip_values = @$camp_general['cg_criteria_skip_values'];

        $i = 0;
        foreach ($cg_criteria_skip_fields as $cg_criteria_skip_field) {

            $cg_criteria_skip_field = wp_automatic_trim(wp_automatic_str_replace(array(
                '[',
                ']',
            ), '', $cg_criteria_skip_field));

            echo '<br>Checking Field:' . $cg_criteria_skip_field . ' if  ' . $cg_criteria_skip_criterias[$i] . ' ';

            if (isset($img[$cg_criteria_skip_field])) {

                // if $img [$cg_criteria_skip_field] is starting with [ and ending with ] then it is a field, chck if img array has that field and overwrite if yes
                if (substr($cg_criteria_skip_values[$i], 0, 1) == '[' && substr($cg_criteria_skip_values[$i], -1) == ']') {
                    $cg_criteria_skip_values[$i] = wp_automatic_trim(wp_automatic_str_replace(array(
                        '[',
                        ']',
                    ), '', $cg_criteria_skip_values[$i]));

                    if (isset($img[$cg_criteria_skip_values[$i]])) {
                        $cg_criteria_skip_values[$i] = $img[$cg_criteria_skip_values[$i]];
                    }
                }

                // validating the field
                $single_criteria_valid = $this->validate_criteria_single($cg_criteria_skip_criterias[$i], $cg_criteria_skip_values[$i], $img[$cg_criteria_skip_field]);

                if ($single_criteria_valid) {

                    if (!in_array('OPT_CRITERIA_ALL', $camp_opt)) {
                        echo '<-- Criteria applies, skipping the post ';
                        return false;
                    } else {
                        echo '<-- Criteria applies, but  all criteria must apply to skip, so continue';
                        $passing_criteria++;
                    }

                } else {

                    //we have a criteria that did not apply
                    echo '<-- Set exclusion criteria did not match';

                    if (!in_array('OPT_CRITERIA_ALL', $camp_opt)) {

                        //this criteria did not match

                    } else {

                        //this criteria did not match and we need all criteria to match to return false, this post is valid
                        echo '<-- post is valid';
                        return true;

                    }

                }
            } else {
                echo '<-- Field not found withen returned values...';
            }

            $i++;
        }

        //all criterias were checked now
        if (in_array('OPT_CRITERIA_ALL', $camp_opt) && $passing_criteria > 0) {
            //all criteria checked and all match, lets return false i.e skip the post
            echo '<-- All criteria match, skipping the post ';
            return false;
        }

        return $valid;

    }

    /**
     * Function to validate if criterias applies to the fields or not and must one criteria at least apply otherwise return false
     */
    public function validate_criterias_must($img, $camp_opt, $camp, $camp_general)
    {

        // ini
        $valid = true; // start with the post valid, if any criteria did not apply, it will be invaid
        $checked_criterias = 0; // count of passing criteria

        // required field values
        $cg_criteria_skip_fields = @$camp_general['cg_criteria_skip_fields_must'];
        $cg_criteria_skip_criterias = @$camp_general['cg_criteria_skip_criterias_must'];
        $cg_criteria_skip_values = @$camp_general['cg_criteria_skip_values_must'];

        $i = 0;
        foreach ($cg_criteria_skip_fields as $cg_criteria_skip_field) {

            $cg_criteria_skip_field = wp_automatic_trim(wp_automatic_str_replace(array(
                '[',
                ']',
            ), '', $cg_criteria_skip_field));

            echo '<br>Checking Field:' . $cg_criteria_skip_field . ' if  ' . $cg_criteria_skip_criterias[$i] . ' ';

            //compare a field with another field like list_price_numeric > price_numeric
            //compensate the value of the value to be compared with the field value
            //if $cg_criteria_skip_values [$i] starts with [ and ends with ] then its a field, check if img array contains this field and replace if yes
            if (substr($cg_criteria_skip_values[$i], 0, 1) == '[' && substr($cg_criteria_skip_values[$i], -1) == ']') {
                $cg_criteria_skip_values[$i] = wp_automatic_trim(wp_automatic_str_replace(array(
                    '[',
                    ']',
                ), '', $cg_criteria_skip_values[$i]));

                if (isset($img[$cg_criteria_skip_values[$i]])) {

                    $cg_criteria_skip_values[$i] = $img[$cg_criteria_skip_values[$i]];
                }
            }

            if (isset($img[$cg_criteria_skip_field])) {

                // validating the field
                $single_criteria_valid = $this->validate_criteria_single($cg_criteria_skip_criterias[$i], $cg_criteria_skip_values[$i], $img[$cg_criteria_skip_field]);

                if ($single_criteria_valid) {

                    //this criteria matched

                    //if OPT_CRITERIA_MUST_ANY is set, return true
                    if (in_array('OPT_CRITERIA_MUST_ANY', $camp_opt)) {
                        echo '<-- Criteria applies, nice approve this post';
                        return true;
                    } else {
                        echo '<-- Criteria applies, but  all criteria must apply to approve, so continue';

                    }

                } else {

                    //we have a criteria that did not apply

                    if (in_array('OPT_CRITERIA_MUST_ANY', $camp_opt)) {
                        //this criteria failed but we need to check all other criteria to see if any of them applies
                        echo '<-- Set must exist criteria did not match.. but we need to check all other criteria to see if any of them applies';
                        $checked_criterias++;
                    } else {
                        //this criteria did not match and we need all criteria to match to return true, this post is invalid
                        echo '<-- Set must exist criteria did not match.. skipping the post';
                        return false;
                    }

                }
            } else {
                echo '<-- Field not found withen returned values...';
            }

            $i++;
        }

        if (in_array('OPT_CRITERIA_MUST_ANY', $camp_opt)) {

            //if checked_criterias >0 return false as there are checked criterias and none has applied
            if ($checked_criterias > 0) {
                echo '<-- No criteria applied, skipping the post';
                return false;
            }

        } else {
            return $valid;
        }

    }

    /**
     * Checks a single field value for a specific criteia, return true if applies and false if not
     *
     * @param unknown $cg_criteria_skip_criteria
     * @param unknown $cg_criteria_skip_value
     * @param unknown $cnt
     */
    public function validate_criteria_single($cg_criteria_skip_criteria, $cg_criteria_skip_value, $cnt)
    {
        $criteria_applies = false; // ini
        $first_print = true;

        $cg_criteria_skip_value_parts = explode("\n", $cg_criteria_skip_value);
        $cg_criteria_skip_value_parts = array_filter($cg_criteria_skip_value_parts);

        //if empty array, reset it with an array containing an empty space, this means user wanted the field to be empty
        if (count($cg_criteria_skip_value_parts) == 0) {
            $cg_criteria_skip_value_parts = array(
                '',
            );
        }

        foreach ($cg_criteria_skip_value_parts as $cg_criteria_skip_value_part) {

            if (!$first_print) {

                echo ',';
            } else {
                $first_print = false;
            }

            echo ' ' . $cg_criteria_skip_value_part . ' ';

            if ($cg_criteria_skip_criteria == '==') {

                // equation
                if (wp_automatic_trim($cnt) == wp_automatic_trim($cg_criteria_skip_value_part)) {
                    $criteria_applies = true;
                }
            } elseif ($cg_criteria_skip_criteria == 'contains') {

                if (stristr($cnt, $cg_criteria_skip_value_part)) {
                    $criteria_applies = true;
                }
            } elseif ($cg_criteria_skip_criteria == 'greater') {

                if (is_numeric(wp_automatic_trim($cnt)) && is_numeric(wp_automatic_trim($cg_criteria_skip_value_part))) {

                    if (wp_automatic_trim($cnt) > wp_automatic_trim($cg_criteria_skip_value_part)) {
                        $criteria_applies = true;
                    } else {
                        echo '<-- not greater';
                    }
                } else {
                    echo ' (Not valid to compare, not numeric values)';
                }
            } elseif ($cg_criteria_skip_criteria == 'less') {

                if (is_numeric(wp_automatic_trim($cnt)) && is_numeric(wp_automatic_trim($cg_criteria_skip_value_part))) {

                    if (wp_automatic_trim($cnt) < wp_automatic_trim($cg_criteria_skip_value_part)) {
                        $criteria_applies = true;
                    } else {
                        echo '<-- not less';
                    }
                } else {
                    echo ' (Not valid to compare, not numeric values)';
                }
            } elseif ($cg_criteria_skip_criteria == 'length_greater') {

                // numeric check
                if (is_numeric(wp_automatic_trim($cg_criteria_skip_value_part))) {
                    $length = strlen($cnt);
                    if ($length > wp_automatic_trim($cg_criteria_skip_value_part)) {
                        $criteria_applies = true;
                    } else {
                        echo '<-- length(' . $length . ') is not greater';
                    }
                } else {
                    echo ' (Compare value is not a valid number )';
                }
            } elseif ($cg_criteria_skip_criteria == 'length_less') {

                // numeric check
                if (is_numeric(wp_automatic_trim($cg_criteria_skip_value_part))) {
                    $length = strlen($cnt);
                    if ($length < wp_automatic_trim($cg_criteria_skip_value_part)) {
                        $criteria_applies = true;
                    } else {
                        echo '<-- length(' . $length . ') is not less';
                    }
                } else {
                    echo ' (Compare value is not a valid number )';
                }
            }
        }

        return $criteria_applies;
    }
    public function fire_proxy()
    {
        echo '<br>Proxy Check Fired';

        $proxies = get_option('wp_automatic_proxy');
        if (stristr($proxies, ':')) {
            echo '<br>Proxy Found lets try';
            // listing all proxies

            $proxyarr = explode("\n", $proxies);

            foreach ($proxyarr as $proxy) {
                if (wp_automatic_trim($proxy) != '') {

                    $auth = '';
                    if (substr_count($proxy, ':') == 3) {
                        echo '<br>Private proxy found .. using authentication';
                        $proxy_parts = explode(':', $proxy);

                        $proxy = $proxy_parts[0] . ':' . $proxy_parts[1];
                        $auth = $proxy_parts[2] . ':' . $proxy_parts[3];

                        curl_setopt($this->ch, CURLOPT_PROXY, wp_automatic_trim($proxy));
                        curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, wp_automatic_trim($auth));
                    } else {
                        curl_setopt($this->ch, CURLOPT_PROXY, wp_automatic_trim($proxy));
                    }

                    echo "<br>Trying using proxy :$proxy";

                    curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, 1);

                    curl_setopt($this->ch, CURLOPT_URL, 'www.bing.com/search?count=50&intlF=1&mkt=En-us&first=0&q=test');
                    // curl_setopt($this->ch, CURLOPT_URL, 'http://whatismyipaddress.com/');
                    $exec = curl_exec($this->ch);
                    $x = curl_error($this->ch);

                    if (wp_automatic_trim($x) != '') {
                        echo '<br>Curl Proxy Error:' . curl_error($this->ch);
                    } else {

                        if (stristr($exec, 'It appears that you are using a Proxy') || stristr($exec, 'excessive amount of traffic')) {
                            echo '<br>Proxy working but captcha met let s skip it';
                        } elseif (stristr($exec, 'microsoft.com')) {

                            // succsfull connection here
                            // echo curl_exec($this->ch);
                            // reordering the proxy
                            $proxies = wp_automatic_str_replace(' ', '', $proxies);

                            if (wp_automatic_trim($auth) != '') {
                                $proxy = $proxy . ':' . $auth;
                            }

                            $proxies = wp_automatic_str_replace($proxy, '', $proxies);

                            $proxies = wp_automatic_str_replace("\n\n", "\n", $proxies);
                            $proxies = "$proxy\n$proxies";
                            // echo $proxies;
                            update_option('wp_automatic_proxy', $proxies);

                            echo '<br>Connected successfully using this proxy ';

                            $this->isProxified = true;

                            return true;
                        } else {

                            echo '<br>Proxy Reply:' . $exec;
                        }
                    }
                }
            }

            // all proxies not working let's call proxyfrog for new list

            // no proxyfrog list
            $this->unproxyify();

            // proxifing the connection
        } else {
            echo '..No proxies';
        }
    }

    /*
     * ---* Clear proxy function ---
     */
    public function unproxyify()
    {
        // clean the connection
        unset($this->ch);

        // curl ini
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_REFERER, 'http://www.google.com');
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
        // curl_setopt ( $this->ch, CURLOPT_COOKIEJAR, "cookie.txt" );
    }

    /**
     * Function to spin the content
     * @param string $html
     * @return string the spun content
     */
    public function spin($html){

        //key 
        $wp_automatic_spinrewriter_key = trim( get_option('wp_automatic_spinrewriter_key', ''));
        $wp_automatic_spinrewriter_email = trim( get_option('wp_automatic_spinrewriter_email', ''));

 
        // if no TBS account found, return the original content
        if(wp_automatic_trim($wp_automatic_spinrewriter_key) == '' || wp_automatic_trim($wp_automatic_spinrewriter_email) == ''){
            
            //orange message
            echo '<br><span style="color:orange">No SpinRewriter account found, please enter your SpinRewriter email and key in the settings page to enable spinning</span>';
            
            return $html;
        }

        return $this->spin_rapidapi_tbs($html,$wp_automatic_spinrewriter_key,$wp_automatic_spinrewriter_email);
 
    }

    /**
     * Function to spin the content using the rapidapi tbs method
     * @param string $html
     * @return string the spun content
     */
    function spin_rapidapi_tbs($html,$wp_automatic_spinrewriter_key,$wp_automatic_spinrewriter_email){
         
        
        //Protected terms
        $tbs_protected = get_option('wp_automatic_tbs_protected', '');

        if (wp_automatic_trim($tbs_protected) != '') {
            $tbs_protected = explode("\n", $tbs_protected);
            $tbs_protected = array_filter($tbs_protected);
            $tbs_protected = array_map('trim', $tbs_protected);

            $tbs_protected = array_filter($tbs_protected);

            $tbs_protected = implode(',', $tbs_protected);
        }

        // add , if not exists
        if (!stristr($tbs_protected, ',')) {
            $tbs_protected = $tbs_protected . ',';
        }

        // add ad_1, ad_2 , numbers
        $tbs_protected = $tbs_protected . ',ad_1,ad_2,0,1,2,3,4,5,6,7,8,9,';

        
         

            $this->log('SpinRewriter', "Using SpinRewriter method to rewrite the content...");
            echo '<br>Using SpinRewriter method';
 
            // instantiate original html
            $newhtml = $html;

            // replace nospins with astrics
            preg_match_all('{\[nospin.*?\/nospin\]}s', $html, $nospins);
            $nospins = $nospins[0];

            // shortcodes
            preg_match_all('{\[.*?]}s', $html, $shortcodes);
            $shortcodes = $shortcodes[0];

            // html
            preg_match_all("/<[^<>]+>/is", $html, $matches, PREG_PATTERN_ORDER);
            $htmlfounds = $matches[0];

            // js
            preg_match_all("/<script.*?<\/script>/is", $html, $matches3, PREG_PATTERN_ORDER);
            $js = $matches3[0];

            // numbers
            preg_match_all("{\d\d+}is", $html, $numMatches);
            $numFounds = ($numMatches[0]);
            $numFounds = array_filter(array_unique($numFounds));

            usort($numFounds, 'wp_automatic_sort');

            $nospins = array_merge( array('(99999)'),$nospins, $shortcodes, $htmlfounds, $js, $numFounds, array(
                9,
                8,
                7,
                6,
                5,
                4,
                3,
                2,
                1,
                
            ));

            // remove empty and duplicate
            $nospins = array_filter(array_unique($nospins));

            // replace nospin parts with astrics
            $i = 1;
            foreach ($nospins as $nospin) {
                $newhtml = wp_automatic_str_replace($nospin, '[' . str_repeat('*', $i) . ']', $newhtml);
                $i++;
            }

            
            //require spinrewriter
            require_once dirname(__FILE__) . '/inc/spinrewriter.php';

            $spinrewriter_api = new wp_auto_spinner_SpinRewriterAPI ( $wp_automatic_spinrewriter_email, $wp_automatic_spinrewriter_key );
			
			// Make the actual API request and save response as a native PHP array.
			$api_response = $spinrewriter_api->getQuota ();

            // check if response is a valid response i.e is array
			if (isset ( $api_response ['status'] )) {
				
				// check if reponse status is OK or Error
				if ($api_response ['status'] == 'OK') {
					
					// let's check if quote available
					if ($api_response ['api_requests_available'] > 0) {
						
						$this->log ( 'SpinRewriter', 'Quota ' . $api_response ['api_requests_available'] );

						$protected_terms = "John, Douglas Adams, then";
						//$spinrewriter_api->setProtectedTerms ( $wp_auto_spinner_execlude );
						
                        /*
						// (optional) Set whether the One-Click Rewrite process automatically protects Capitalized Words outside the article's title.
						if (in_array ( 'OPT_AUTO_SPIN_AutoProtectedTerms', $opt )) {
							$spinrewriter_api->setAutoProtectedTerms ( true );
						} else {
							$spinrewriter_api->setAutoProtectedTerms ( false );
						}
						
						// (optional) Set the confidence level of the One-Click Rewrite process.
						$spinrewriter_api->setConfidenceLevel ( $wp_auto_spinner_quality );
						
						// (optional) Set whether the One-Click Rewrite process uses nested spinning syntax (multi-level spinning) or not.
						if (in_array ( 'OPT_AUTO_SPIN_NestedSpintax', $opt )) {
							$spinrewriter_api->setNestedSpintax ( false );
						} else {
							$spinrewriter_api->setNestedSpintax ( false );
						}
						
						// (optional) Set whether Spin Rewriter rewrites complete sentences on its own.
						if (in_array ( 'OPT_AUTO_SPIN_AutoSentences', $opt )) {
							$spinrewriter_api->setAutoSentences ( true );
						} else {
							$spinrewriter_api->setAutoSentences ( false );
						}
						
						// (optional) Set whether Spin Rewriter rewrites entire paragraphs on its own.
						if (in_array ( 'OPT_AUTO_SPIN_AutoParagraphs', $opt )) {
							$spinrewriter_api->setAutoParagraphs ( false );
						} else {
							$spinrewriter_api->setAutoParagraphs ( false );
						}
						
						// (optional) Set whether Spin Rewriter writes additional paragraphs on its own.
						if (in_array ( 'OPT_AUTO_SPIN_AutoNewParagraphs', $opt )) {
							$spinrewriter_api->setAutoNewParagraphs ( false );
						} else {
							$spinrewriter_api->setAutoNewParagraphs ( false );
						}
						
						// (optional) Set whether Spin Rewriter changes the entire structure of phrases and sentences.
						if (in_array ( 'OPT_AUTO_SPIN_AutoSentenceTrees', $opt )) {
							$spinrewriter_api->setAutoSentenceTrees ( true );
						} else {
							$spinrewriter_api->setAutoSentenceTrees ( false );
						}

                        */
						
						// (optional) Set the desired spintax format to be used with the returned spun text.
						$spinrewriter_api->setSpintaxFormat ( "{|}" );
						
						// Make the actual API request and save response as a native PHP array.
						$text = "John will book a room. Then he will read a book by Douglas Adams.";
						
						$article = $newhtml;
						
						 
						// fixes
						$original_article = $article = str_replace ( ':(*', ': (*', $article );
						
						
						$api_response2 = $spinrewriter_api->getTextWithSpintax ( $article );

                        
						// validate reply with OK
						if (isset ( $api_response2 ['status'] )) {
							
							// status = OK
							if ($api_response2 ['status'] == 'OK') {
								
                                echo '<br>SpinRewriter success';
                                $this->log('SpinRewriter', "SpinRewriter success");
								 
								$article = $api_response2 ['response'];
								
								 
								// fix lrb
								$article = str_replace ( '-LRB-', '(', $article );
								
								// fix exclude
								$article = preg_replace ( '{\[\s(\**?\])\.}', '[$1', $article ); // ( *****).
								$article = preg_replace ( '{\[\s(\**?\])\s\[}', '[$1 [', $article ); // ( *****) (
								$article = preg_replace ( '{\s(\[\**?\])\.(\s)}', "$1$2", $article ); // (***********************).\s
								$article = str_replace ( '[ *', '[*', $article );
								$article = str_replace ( '& #', '&#', $article );

                                 // replace the astrics with nospin tags
                                if (count($nospins) > 0) {

                                    $i = 1;

                                    foreach ($nospins as $nospin) {

                                        $article = wp_automatic_str_replace('[' . str_repeat('*', $i) . ']', $nospin, $article);

                                        $i++;
                                    }
                                }

                                

                                echo '<br>SpinRewriter Successfully spinned the content';
                                
                                
                                $this->log('SpinRewriter', "SpinRewriter Successfully spinned the content");
                                return $article;
								
                                
 								 
							} else {
								wp_auto_spinner_log_new ( 'SpinRewriter says', $api_response2 ['response'] );
							}
						} else {
							echo "<br>SpinRewriter Error: Invalid response";
                            $this->log('error', "SpinRewriter Error: Invalid response");
						}
					} else {
						$this->log ( 'error', "SpinRewriter Error: No quota available" );
					}
				} else {
                    echo "<br>SpinRewriter Error: $api_response[response]";
					$this->log ( 'error', "SpinRewriter Error: $api_response[response]" );
				}
			} else {
				//log
                $this->log('error', "SpinRewriter Error: Invalid response");
                echo "<br>SpinRewriter Error: Invalid response";
			}

            //default return
            return $html;

    }


    /*
     * ---* Spin function that calls TBS ---
     */
    public function spin_tbs($html)
    {
        $url = 'http://thebestspinner.com/api.php';

        // $testmethod = 'identifySynonyms';
        $testmethod = 'replaceEveryonesFavorites';

        // Build the data array for authenticating.

        $data = array();
        $data['action'] = 'authenticate';
        $data['format'] = 'php'; // You can also specify 'xml' as the format.

        // The user credentials should change for each UAW user with a TBS account.
        $tbs_username = get_option('wp_automatic_tbs', ''); // "gigoftheday@gmail.com"; // Enter your The Best Spinner's Email ID
        $tbs_password = get_option('wp_automatic_tbs_p', ''); // "nd8da759a40a551b9aafdc87a1d902f3d"; // Enter your The Best Spinner's Password
        $tbs_protected = get_option('wp_automatic_tbs_protected', '');

        if (wp_automatic_trim($tbs_protected) != '') {
            $tbs_protected = explode("\n", $tbs_protected);
            $tbs_protected = array_filter($tbs_protected);
            $tbs_protected = array_map('trim', $tbs_protected);

            $tbs_protected = array_filter($tbs_protected);

            $tbs_protected = implode(',', $tbs_protected);
        }

        // add , if not exists
        if (!stristr($tbs_protected, ',')) {
            $tbs_protected = $tbs_protected . ',';
        }

        // add ad_1, ad_2 , numbers
        $tbs_protected = $tbs_protected . 'ad_1,ad_2,0,1,2,3,4,5,6,7,8,9,';

        if (wp_automatic_trim($tbs_username) == '' || wp_automatic_trim($tbs_password) == '') {
            // $this->log ( 'Info', 'No BTS account found , it is highly recommended ' );
            return $html;
        }

        $data['username'] = $tbs_username;
        $data['password'] = $tbs_password;

        // Authenticate and get back the session id.
        // You only need to authenticate once per session.
        // A session is good for 24 hours.
        $exec_login = $this->curl_post($url, $data, $info);

        $output = unserialize($exec_login);

        if ($output['success'] == 'true') {

            $this->log('TBS', "TBS Login success");
            echo '<br>TBS Login success';
            // Success.
            $session = $output['session'];

            // Build the data array for the example.
            $data = array();
            $data['protectedterms'] = $tbs_protected;
            $data['session'] = $session;
            $data['format'] = 'php'; // You can also specify 'xml' as the format.

            // instantiate original html
            $newhtml = $html;

            // replace nospins with astrics
            preg_match_all('{\[nospin.*?\/nospin\]}s', $html, $nospins);
            $nospins = $nospins[0];

            // shortcodes
            preg_match_all('{\[.*?]}s', $html, $shortcodes);
            $shortcodes = $shortcodes[0];

            // html
            preg_match_all("/<[^<>]+>/is", $html, $matches, PREG_PATTERN_ORDER);
            $htmlfounds = $matches[0];

            // js
            preg_match_all("/<script.*?<\/script>/is", $html, $matches3, PREG_PATTERN_ORDER);
            $js = $matches3[0];

            // numbers
            preg_match_all("{\d\d+}is", $html, $numMatches);
            $numFounds = ($numMatches[0]);
            $numFounds = array_filter(array_unique($numFounds));

            usort($numFounds, 'wp_automatic_sort');

            $nospins = array_merge($nospins, $shortcodes, $htmlfounds, $js, $numFounds, array(
                9,
                8,
                7,
                6,
                5,
                4,
                3,
                2,
                1,
            ));

            // remove empty and duplicate
            $nospins = array_filter(array_unique($nospins));

            // replace nospin parts with astrics
            $i = 1;
            foreach ($nospins as $nospin) {
                $newhtml = wp_automatic_str_replace($nospin, '[' . str_repeat('*', $i) . ']', $newhtml);
                $i++;
            }

            $data['text'] = (html_entity_decode($newhtml));

            // $data ['text'] = 'test <br> word <a href="http://onetow.com">http://onetow.com</a> ';

            $data['action'] = $testmethod;
            $data['maxsyns'] = '100'; // The number of synonyms per term.

            if ($testmethod == 'replaceEveryonesFavorites') {
                // Add a quality score for this method.
                $data['quality'] = '1';
            }

            // Post to API and get back results.
            $output = $this->curl_post($url, $data, $info);

            if (wp_automatic_trim($output) == '') {
                $this->log('TBS', "TBS Empty reply... we did not get a valid reply");
            }

            $output = unserialize($output);

            // Show results.
            // echo "<p><b>Method:</b><br>$testmethod</p>";
            // echo "<p><b>Text:</b><br>$data[text]</p>";

            if ($output['success'] == 'true') {
                $this->log('TBS', "TBS Successfully spinned the content");

                // replace the astrics with nospin tags
                if (count($nospins) > 0) {

                    $i = 1;

                    foreach ($nospins as $nospin) {

                        $output['output'] = wp_automatic_str_replace('[' . str_repeat('*', $i) . ']', $nospin, $output['output']);

                        $i++;
                    }
                }

                echo '<br>TBS Successfully spinned the content';
                return $output['output'];
            } else {

                $this->log('error', "TBS Returned an error:$output[error]");
                echo "TBS Returned an error:$output[error]";
                return $html;
            }
        } else {

            // There were errors.
            echo "<br>TBS login did not work returned an error : $output[error]";
            $this->log('error', "TBS login did not work returned an error : $output[error]");
            return $html;
        }
    } // end function



    /*
     * gtranslte function
     */
    public function gtranslate($title, $content, $from, $to, $translationMethod = 'microsoftTranslator')
    {
        if ($from == $to) {
            echo '<br>Translation to langauge can not be the same as translation from language. skipping this translation';
            return array(
                $title,
                $content,
            );
        }

        /*
         * $contains_bracket = stristr($content, '(' ) ? true : false ;
         * $content = wp_automatic_str_replace( '[' , '(' , $content);
         * $content = wp_automatic_str_replace( ']' , ')' , $content);
         */

        // Verify API data
        if ($translationMethod == 'microsoftTranslator') {

            // $wp_automatic_mt_secret = wp_automatic_trim(get_option('wp_automatic_mt_secret',''));
            $wp_automatic_mt_id = wp_automatic_trim(get_option('wp_automatic_mt_key', ''));
            $wp_automatic_mt_region = wp_automatic_trim(get_option('wp_automatic_mt_region', ''));

            if (wp_automatic_trim($wp_automatic_mt_id) == '') {
                echo '<br><span style="color:red">Microsoft translator settings required. Visit the plugin settings and set it.</span>';
                return array(
                    $title,
                    $content,
                );
            }

            $titleSeparator = '[19459000]';
        } elseif ($translationMethod == 'yandexTranslator') { // wp_automatic_yt_key

            $wp_automatic_yt_key = wp_automatic_trim(get_option('wp_automatic_yt_key', ''));

            if (wp_automatic_trim($wp_automatic_yt_key) == '') {
                echo '<br><span style="color:red">Yandex translator API key is required. Visit the plugin settings and set it.</span>';
                return array(
                    $title,
                    $content,
                );
            }

            $titleSeparator = '[19459000]';
        } elseif ($translationMethod == 'deeplTranslator') { // wp_automatic_dl_key

            $wp_automatic_dl_key = wp_automatic_trim(get_option('wp_automatic_dl_key', ''));

            if (wp_automatic_trim($wp_automatic_dl_key) == '') {
                echo '<br><span style="color:red">Deepl PRO translator API key is required. Visit the plugin settings and set it.</span>';
                return array(
                    $title,
                    $content,
                );
            }

            $titleSeparator = '[19459000]';
        } elseif ($translationMethod == 'googleTranslator' && !function_exists('mb_detect_encoding')) {

            echo '<br><span style="color:red">Translation using Gtranslate will not wrok, you must install PHP mbstring module.</span>';
            return array(
                $title,
                $content,
            );
        } else {

            $titleSeparator = '##########';
            $titleSeparator = "\n[19459000]";
        }

        // Fix Norwegian language Translation
        if ($from == 'nor') {
            $from = 'no';
        }

        if ($to == 'nor') {
            $to = 'no';
        }

        // Report Translate
        echo '<br>Translating from ' . $from . ' to ' . $to . ' using ' . $translationMethod;

        /*
         * $title = 'welcome to Egypt';
         * $content= 'it is a good place';
         */

        //Square bracket content rewrite
        if (in_array('OPT_TRANSLATE_SQUARE', $this->camp_opt)) {
            $title = wp_automatic_str_replace('[', '(', $title);
            $title = wp_automatic_str_replace(']', ')', $title);

            $content = wp_automatic_str_replace('[', '(', $content);
            $content = wp_automatic_str_replace(']', ')', $content);

            //protect embed shortcode by replacing (embed) by [embed] and (/embed) by [/embed]
            $content = wp_automatic_str_replace('(embed)', '[embed]', $content);
            $content = wp_automatic_str_replace('(/embed)', '[/embed]', $content);

        }

        // Concat title and content in one text
        $text = $title . $titleSeparator . $content;

        // decode html for chars like &euro; removed for images containing html encoded tags a-image-description="<p>How to Style 8 Stitch Fix Rom
        // $text = html_entity_decode ( $text );

        // $text = file_get_contents( dirname(__FILE__) . '/test.txt');
        // $text = 'welcome to egypt';

        if ($this->debug == true) {
            echo "\n\n--- Translation text-------\n" . $text;
        }

        // scripts
        preg_match_all('{<script.*?script>}s', $text, $script_matchs);
        $script_matchs = $script_matchs[0];

        // pre and code tags
        preg_match_all('{<pre.*?/pre>}s', $text, $pre_matchs);
        $pre_matchs = $pre_matchs[0];

        preg_match_all('{<code.*?/code>}s', $text, $code_matchs);
        $code_matchs = $code_matchs[0];

        // STRIP html and links
        preg_match_all("/<[^<>]+>/is", $text, $matches, PREG_PATTERN_ORDER);

        $htmlfounds = array_filter(array_unique($matches[0]));
        $htmlfounds = array_merge($script_matchs, $pre_matchs, $code_matchs, $htmlfounds);

        if ($this->debug == true) {
            echo "\n\n--- Html finds raw-------\n";
            print_r($htmlfounds);
        }

        $htmlfounds[] = '&quot;';

        // Fix alt tags
        $imgFoundsSeparated = array();
        $new_imgFoundsSeparated = array();
        $altSeparator = '';
        $colonSeparator = '';
        foreach ($htmlfounds as $key => $currentFound) {

            if (stristr($currentFound, '<img') && stristr($currentFound, 'alt') && !stristr($currentFound, 'alt=""')) {

                $altSeparator = '';
                $colonSeparator = '';
                if (stristr($currentFound, 'alt="')) {
                    $altSeparator = 'alt="';
                    $colonSeparator = '"';
                } elseif (stristr($currentFound, 'alt = "')) {
                    $altSeparator = 'alt = "';
                    $colonSeparator = '"';
                } elseif (stristr($currentFound, 'alt ="')) {
                    $altSeparator = 'alt ="';
                    $colonSeparator = '"';
                } elseif (stristr($currentFound, 'alt= "')) {
                    $altSeparator = 'alt= "';
                    $colonSeparator = '"';
                } elseif (stristr($currentFound, 'alt=\'')) {
                    $altSeparator = 'alt=\'';
                    $colonSeparator = '\'';
                } elseif (stristr($currentFound, 'alt = \'')) {
                    $altSeparator = 'alt = \'';
                    $colonSeparator = '\'';
                } elseif (stristr($currentFound, 'alt= \'')) {
                    $altSeparator = 'alt= \'';
                    $colonSeparator = '\'';
                } elseif (stristr($currentFound, 'alt =\'')) {
                    $altSeparator = 'alt =\'';
                    $colonSeparator = '\'';
                }

                if (wp_automatic_trim($altSeparator) != '') {

                    $currentFoundParts = explode($altSeparator, $currentFound);

                    // post alt
                    $preAlt = $currentFoundParts[1];
                    $preAltParts = explode($colonSeparator, $preAlt);
                    $altText = $preAltParts[0];

                    if (wp_automatic_trim($altText) != '') {

                        unset($preAltParts[0]);
                        $past_alt_text = implode($colonSeparator, $preAltParts);

                        // before alt text part
                        $imgFoundsSeparated[] = $currentFoundParts[0] . $altSeparator;

                        // after alt text
                        $imgFoundsSeparated[] = $colonSeparator . $past_alt_text; //wp_automatic_str_replace( $altText, '', $currentFoundParts [1] );

                        // $imgFoundsSeparated[] = $colonSeparator.implode($colonSeparator, $preAltParts);

                        /*
                         * echo ' ImageFound:'.$in.' '.$currentFound;
                         * print_r($currentFoundParts);
                         * print_r($imgFoundsSeparated);
                         */

                        $htmlfounds[$key] = '';
                    }
                }
            }
        }

        // title tag separation
        $title_separator = wp_automatic_str_replace('alt', 'title', $altSeparator);
        foreach ($imgFoundsSeparated as $img_part) {

            if (stristr($img_part, ' title')) {

                $img_part_parts = explode($title_separator, $img_part);

                // before title text
                $pre_title_part = $img_part_parts[0] . $title_separator;

                $post_title_parts = explode($colonSeparator, $img_part_parts[1]);
                $found_title = $post_title_parts[0];

                unset($post_title_parts[0]);
                $past_title_text = implode($colonSeparator, $post_title_parts);

                // after title text
                $post_title_part = $colonSeparator . $past_title_text; //wp_automatic_str_replace( $found_title, '', $img_part_parts [1] );

                $new_imgFoundsSeparated[] = $pre_title_part;
                $new_imgFoundsSeparated[] = $post_title_part;
            } else {
                $new_imgFoundsSeparated[] = $img_part;
            }
        }

        if (count($new_imgFoundsSeparated) != 0) {
            $htmlfounds = array_merge($htmlfounds, $new_imgFoundsSeparated);
        }

        // <!-- <br> -->
        preg_match_all("/<\!--.*?-->/is", $text, $matches2, PREG_PATTERN_ORDER);
        $newhtmlfounds = $matches2[0];

        // strip shortcodes
        preg_match_all("/\[.*?\]/is", $text, $matches3, PREG_PATTERN_ORDER);
        $shortcodesfounds = $matches3[0];

        // protected terms
        $wp_automatic_tra_stop = get_option('wp_automatic_tra_stop', '');

        $protected_terms = array();
        if (wp_automatic_trim($wp_automatic_tra_stop) != '') {
            $protected_terms_arr = explode("\n", wp_automatic_trim($wp_automatic_tra_stop));
            $protected_terms = array_filter($protected_terms_arr);
            $protected_terms = array_map('trim', $protected_terms);
        }

        $htmlfounds = array_merge($htmlfounds, $newhtmlfounds, $shortcodesfounds);

        // clean title separator & empties
        $in = 0;
        $cleanHtmlFounds = array();
        foreach ($htmlfounds as $htmlfound) {

            if ($htmlfound == '[19459000]') {
            } elseif (wp_automatic_trim($htmlfound) == '') {
            } else {
                $cleanHtmlFounds[] = $htmlfound;
            }
        }

        $htmlfounds = array_filter($cleanHtmlFounds);

        // sort
        usort($htmlfounds, 'wp_automatic_sort');

        // Replace founds by numbers
        $start = 19459001;
        foreach ($htmlfounds as $htmlfound) {
            $text = wp_automatic_str_replace($htmlfound, '[' . $start . ']', $text);
            $start++;
        }

        // protected
        foreach ($protected_terms as $exword) {

            if (wp_automatic_trim($exword) != '') {
                $text = preg_replace('/\b' . preg_quote(wp_automatic_trim($exword), '/') . '\b/u', '[' . $start . ']', $text);
                $start++;
            }
        }

        // .{ replace with . {
        $text = wp_automatic_str_replace('.{', '. {', $text);

        // group consequent matchs [19459003][19459003][19459004][19459003]
        preg_match_all('!(?:\[1945\d*\][\s]*){2,}!s', $text, $conseqMatchs);

        if ($this->debug == true) {
            echo "\n\n--- Html finds-------\n";
            print_r($htmlfounds);
            echo "\n\n----- Html before consequent replacements-----\n" . $text;

            echo "\n\n--- Consequent masks finds-------\n";

            print_r($conseqMatchs);
        }

        // replacing consequents
        $startConseq = 19659001;
        foreach ($conseqMatchs[0] as $conseqMatch) {
            $text = preg_replace('{' . preg_quote(wp_automatic_trim($conseqMatch)) . '}', '[' . $startConseq . ']', $text, 1);
            $startConseq++;
        }

        // copy of the sent masks
        preg_match_all('{\[.*?\]}', $text, $pre_tags_matches);
        $pre_tags_matches = ($pre_tags_matches[0]);

        // copy of sent masks with spaces before and after
        preg_match_all('{\s*\[.*?\]\s*}u', $text, $pre_tags_matches_s);
        $pre_tags_matches_s = ($pre_tags_matches_s[0]);

        if ($this->debug == true) {

            echo "\n\n----- Content to translate  without additional lins-----\n" . $text;
        }

        // each tag in a new line
        $text = wp_automatic_str_replace('[', "\n\n[", $text);
        $text = wp_automatic_str_replace(']', "]\n\n", $text);

        if ($this->debug == true) {

            echo "\n\n----- Content to translate  with  additional lins-----\n" . $text;
        }

        // Check Translation Method and use it

        if ($translationMethod == 'googleTranslator') {

            try {

                // Google Translator Class
                require_once 'inc/translator.Google.php';

                // curl ini
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
                curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");

                // Google Translator Object
                $GoogleTranslator = new GoogleTranslator($ch);

                // Translate Method
                $translated = $GoogleTranslator->translateText($text, $from, $to);

                // same language error The page you have attempted to translate is already
                if (stristr($translated, 'The page you have attempted to translate is already')) {
                    echo '<br>Google refused to translate and tells that the article is in the same laguage';
                    $translated = $text;
                }

                // fix html entities
                if (stristr($translated, ';')) {
                    $translated = wp_automatic_htmlspecialchars_decode($translated, ENT_QUOTES);
                }

                if ($this->debug == true) {
                    echo "\n\n\n\n--- Returned translation-------\n" . $translated . "\n\n\n";
                }
            } catch (Exception $e) {

                echo '<br>Translate Exception:' . $e->getMessage();

                $this->translationSuccess = false;

                return array(
                    $title,
                    $content,
                );
            }
        } elseif ($translationMethod == 'yandexTranslator') {

            try {

                // Yandex Translator Class
                require_once 'inc/translator.Yandex.php';

                // Yandex Translator Object
                $YandexTranslator = new YandexTranslator($this->ch, $wp_automatic_yt_key);

                // Translate Method
                $translated = $YandexTranslator->translateText($text, $from, $to);

                if ($this->debug == true) {
                    echo "\n\n\n\n--- Returned translation-------\n" . $translated . "\n\n\n";
                }
            } catch (Exception $e) {

                echo 'Exception:' . $e->getMessage();

                $this->translationSuccess = false;

                return array(
                    $title,
                    $content,
                );
            }
        } elseif ($translationMethod == 'deeplTranslator') {

            try {

                // Deepl Translator Class
                require_once 'inc/translator.Deepl.php';

                // Yandex Translator Object
                $DeeplTranslator = new DeeplTranslator($this->ch, $wp_automatic_dl_key);

                $wp_automatic_options = get_option('wp_automatic_options', array());

                // free or not
                if (in_array('OPT_DEEPL_FREE', $wp_automatic_options)) {
                    $DeeplTranslator->free = true;
                    echo '<br> Free Deepl Translator used...';
                }

                // formal or not
                // free or not
                if (in_array('OPT_DEEPL_FORMAL', $wp_automatic_options)) {

                    $DeeplTranslator->fomality = 'more';
                    echo 'Formality:more';
                } elseif (in_array('OPT_DEEPL_NFORMAL', $wp_automatic_options)) {

                    $DeeplTranslator->fomality = 'less';
                    echo 'Formality:less';
                }

                // Translate Method
                $translated = $DeeplTranslator->translateText($text, $from, $to);

                if ($this->debug == true) {
                    echo "\n\n\n\n--- Returned translation-------\n" . $translated . "\n\n\n";
                }
            } catch (Exception $e) {

                echo 'Exception:' . $e->getMessage();

                $this->translationSuccess = false;

                return array(
                    $title,
                    $content,
                );
            }
        } else {

            // Translating using Microsoft translator
            require_once 'inc/translator.Microsoft.php';

            $MicrosoftTranslator = new MicrosoftTranslator($this->ch);

            try {

                // Generate access token
                $accessToken = $MicrosoftTranslator->getToken($wp_automatic_mt_id, $wp_automatic_mt_region);

                echo '<br>Translated text chars: ' . $this->chars_count($text);

                $translated = $MicrosoftTranslator->translateWrap($text, $from, $to);
            } catch (Exception $e) {

                echo '<br>Translation error:' . $e->getMessage();

                $this->translationSuccess = false;

                return array(
                    $title,
                    $content,
                );
            }
        }

        // Fix broken ] 19459
        $translated = preg_replace('{]\s*?1945}', '][1945', $translated);

        // Fix broken Add Comment 19459012]
        $translated = preg_replace('{ 19459(\d*?)]}', ' [19459$1]', $translated);

        // Fix [[1945
        $translated = wp_automatic_str_replace('[ [1945', '[1945', $translated);

        // Fix ], [
        $translated = wp_automatic_str_replace('], ', ']', $translated);

        // file_put_contents( dirname(__FILE__) .'/test.txt' , $translated);

        // get all brackets
        preg_match_all('{\[.*?\]}', $translated, $bracket_matchs);
        $bracket_matchs = $bracket_matchs[0];

        foreach ($bracket_matchs as $single_bracket) {
            if (stristr($single_bracket, '1') && stristr($single_bracket, '9')) {
                $single_bracket_clean = wp_automatic_str_replace(array(
                    ',',
                    ' ',
                ), '', $single_bracket);
                $translated = wp_automatic_str_replace($single_bracket, $single_bracket_clean, $translated);
            }
        }

        // copy of the returned masks [numbers]
        preg_match_all('{\[\d*?\]}', $translated, $post_tags_matches);
        $post_tags_matches = ($post_tags_matches[0]);

        if ($this->debug == true) {
            echo "\n\n\n\n------ Pre translation and post tags-------";
            print_r($pre_tags_matches);
            print_r($pre_tags_matches_s);

            echo "\n\n\n\n------ Post translation and post tags-------";
            print_r($post_tags_matches);
        }

        // validate returned tags
        if (count($pre_tags_matches) == count($post_tags_matches)) {
            if ($pre_tags_matches !== $post_tags_matches) {

                $i = 0;
                foreach ($post_tags_matches as $post_tags_match) {
                    $translated = preg_replace('{' . preg_quote(wp_automatic_trim($post_tags_match)) . '}', '[' . $i . ']', $translated, 1);
                    $i++;
                }

                if ($this->debug == true) {
                    echo "\n\n\n\n-----Translated after replacing each tag with index-------";
                    echo $translated;
                }

                // replacing index tags with real pre translation tags
                $i = 0;
                foreach ($pre_tags_matches as $pre_tags_match) {
                    $translated = wp_automatic_str_replace('[' . $i . ']', $pre_tags_match, $translated);
                    $i++;
                }
            }
        }

        // each tag in a new line restoration
        $translated = wp_automatic_str_replace("\n\n[", '[', $translated);
        $translated = wp_automatic_str_replace("]\n\n", ']', $translated);

        // resotring spaces before and after tags
        $i = 0;
        foreach ($pre_tags_matches_s as $pre_tags_match) {

            $pre_tags_match_h = wp_automatic_htmlentities($pre_tags_match);
            if (stristr($pre_tags_match_h, '&nbsp;')) {
                $pre_tags_match = wp_automatic_str_replace('&nbsp;', ' ', $pre_tags_match_h);
            }

            $translated = preg_replace('{' . preg_quote(wp_automatic_trim($pre_tags_match)) . '}', "[$i]", $translated, 1);
            $i++;
        }

        // remove all spaces before and after current tags
        $translated = preg_replace('{\s*\[}u', '[', $translated);
        $translated = preg_replace('{\]\s*}u', ']', $translated);

        $i = 0;
        foreach ($pre_tags_matches_s as $pre_tags_match) {

            // fix &nbsp;
            $pre_tags_match_h = wp_automatic_htmlentities($pre_tags_match);
            if (stristr($pre_tags_match_h, '&nbsp;')) {
                $pre_tags_match = wp_automatic_str_replace('&nbsp;', ' ', $pre_tags_match_h);
            }

            $translated = preg_replace('{' . preg_quote("[$i]") . '}', $pre_tags_match, $translated, 1);

            $i++;
        }

        if ($this->debug == true) {
            echo "\n\n\n\n--- --- Fixed translation-------\n";
            print_r($translated);
        }
        // restore consquent masks
        $startConseq = 19659001;
        foreach ($conseqMatchs[0] as $conseqMatch) {
            $translated = wp_automatic_str_replace('[' . $startConseq . ']', $conseqMatch, $translated);
            $startConseq++;
        }

        // Grab all replacements with **
        preg_match_all('!\[.*?\]!', $translated, $brackets);

        $brackets = $brackets[0];
        $brackets = array_unique($brackets);

        foreach ($brackets as $bracket) {
            if (stristr($bracket, '19')) {

                $corrrect_bracket = wp_automatic_str_replace(' ', '', $bracket);
                $corrrect_bracket = wp_automatic_str_replace('.', '', $corrrect_bracket);
                $corrrect_bracket = wp_automatic_str_replace(',', '', $corrrect_bracket);

                $translated = wp_automatic_str_replace($bracket, $corrrect_bracket, $translated);
            }
        }

        if ($this->debug == true) {
            echo "\n\n\n\n--- --- Fixed translation consequests decoded-------\n";
            print_r($translated);
        }

        // check if successful translation contains ***
        if (stristr($translated, wp_automatic_trim($titleSeparator)) && count($pre_tags_matches) == count($post_tags_matches)) {

            $this->translationSuccess = true;

            // restore html tags
            $start = 19459001;
            foreach ($htmlfounds as $htmlfound) {
                $translated = wp_automatic_str_replace('[' . $start . ']', $htmlfound, $translated);
                $start++;
            }

            // restore excludes
            foreach ($protected_terms as $htmlfound) {
                $translated = wp_automatic_str_replace('[' . $start . ']', $htmlfound, $translated);
                $start++;
            }

            if ($this->debug == true) {
                echo "\n\n\n\n--- --- Final translation-------\n";
                print_r($translated);
            }

            $contents = explode(wp_automatic_trim($titleSeparator), $translated);
            $title = $contents[0];
            $content = $contents[1];
        } else {

            $this->translationSuccess = false;

            echo '<br>Translation failed ';

            if (!stristr($translated, wp_automatic_trim($titleSeparator))) {
                echo ' Separator we added between title and content went missing';
            }

            if (!stristr($translated, wp_automatic_trim($titleSeparator))) {
                echo ' Separator we added between title and content went missing';
            }

            if (count($pre_tags_matches) != count($post_tags_matches)) {
                echo ' Sent ' . count($pre_tags_matches) . ' tags and got ' . count($post_tags_matches);
            }
        }

        /*
         * if($contains_bracket == false){
         * $content = wp_automatic_str_replace('(','[',$content);
         * $content = wp_automatic_str_replace(')',']',$content);
         * }
         */

        return array(
            $title,
            $content,
        );
    }
    public function curl_post($url, $data, &$info)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->curl_postData($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        $html = wp_automatic_trim(curl_exec($ch));

        print_r(curl_error($ch));

        return $html;
    }
    public function curl_postData($data)
    {
        $fdata = "";
        foreach ($data as $key => $val) {
            $fdata .= "$key=" . urlencode($val) . "&";
        }

        return $fdata;
    }

    /*
     * ---* update cb categories ---
     */
    public function update_categories()
    {
        // Get
        $x = 'error';
        while (wp_automatic_trim($x) != '') {
            $url = 'http://www.clickbank.com/advancedMarketplaceSearch.htm';
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
            $exec = curl_exec($this->ch);
            echo $x = curl_error($this->ch);
        }

        if (stristr($exec, '<option value="">- All categories -</option>')) {
            echo '<br>categories found';
            preg_match_all("{>- All categories -</option>((.|\s)*?)</select>}", $exec, $matches, PREG_PATTERN_ORDER);

            $res = $matches[0];
            $cats = $res[0];

            // extracting single parent categories [<option value="1510">Betting Systems</option>]
            preg_match_all("{<option value=\"(.*?)\">(.*?)</option>}", $cats, $matches, PREG_PATTERN_ORDER);
            $paretcats_ids = $matches[1];
            $paretcats_names = $matches[2];

            // delete current records
            if (count($paretcats_names) > 0) {
                $query = "delete from {$this->wp_prefix}automatic_categories ";
                $this->db->query($query);
            }

            // adding parent categories
            $i = 0;
            foreach ($paretcats_ids as $parentcat_id) {

                $parentcat_name = $paretcats_names[$i];

                // inserting cats
                $query = "insert into {$this->wp_prefix}automatic_categories (cat_id , cat_name) values ('$parentcat_id','$parentcat_name')";
                $this->db->query($query);
                $i++;
            }

            echo '<br>Parent Categories added:' . $i;

            // extracting subcategories
            /*
             * <option value="1265" parent="1253" path="Arts & Entertainment &raquo; Architecture"> Architecture </option>
             */

            // echo $exec;
            // exit;
            preg_match_all("{<option value=\"(.*?)\"  parent=\"(.*?)\"(.|\s)*?>((.|\s)*?)</option>}", $exec, $matches, PREG_PATTERN_ORDER);
            $subcats_ids = $matches[1];
            $subcats_parents = $matches[2];
            $subcats_names = $matches[4];

            $i = 0;
            foreach ($subcats_ids as $subcats_id) {
                $subcats_names[$i] = wp_automatic_trim($subcats_names[$i]);
                $subcats_parents[$i] = wp_automatic_trim($subcats_parents[$i]);
                $query = "insert into {$this->wp_prefix}automatic_categories(cat_id,cat_parent,cat_name) values('$subcats_id','$subcats_parents[$i]','$subcats_names[$i]')";
                $this->db->query($query);
                $i++;
            }

            echo '<br>Sub Categories added ' . $i;

            // print_r($matches);
            exit();

            $res = $matches[2];
            $form = $res[0];

            preg_match_all("{<option value=\"(.*?)\"  parent=\"(.*?)\"}", $exec, $matches, PREG_PATTERN_ORDER);

            print_r($matches);

            // print_r($matches);
            exit();
            $res = $matches[0];
            $cats = $res[0];
        }
    }

    /*
     * ---* Proxy Frog Integration ---
     */
    public function alb_proxyfrog()
    {

        // get the current list
        $proxies = get_option('alb_proxy_list');

        // no proxies
        echo '<br>Need new valid proxies';

        if (function_exists('proxyfrogfunc')) {
            echo '<br>Getting New Proxy List from ProxyFrog.me';
            // Get
            $x = 'error';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); // Many login forms redirect at least once.
            // curl_setopt ( $ch, CURLOPT_COOKIEJAR, "cookie.txt" );

            // Get
            // license
            $paypal = get_option('pf_license');
            $paypal = urlencode($paypal);
            $url = "http://proxyfrog.me/proxyfrog/api.php?email=$paypal";
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_URL, wp_automatic_trim($url));
            $exec = curl_exec($ch);

            // echo $exec;

            if (stristr($exec, ':')) {
                update_option('be_proxy_list', $exec);
                update_option('alb_proxy_list', $exec);
                echo '<br>New Proxy List <b>Added successfully</b> ';
                $this->log('ProxyFrog', "New Proxy list added from ProxyFrog");
                return true;
            } else {
                $this->log('ProxyFrog', $exec);
            }
        } else {

            return false;
        }
    } // end fun

    /*
     * ---* Logging Function ---
     */
    public function log($type, $data)
    {
        // $now= date("F j, Y, g:i a");
        $now = date('Y-m-d H:i:s');
        $data = @addslashes($data);

        $query = "INSERT INTO {$this->wp_prefix}automatic_log (action,date,data) values('$type','$now','$data')";

        // echome$query;
        $this->db->query($query);

        $insert = $this->db->insert_id;

        $insert_below_100 = $insert - 100;

        if ($insert_below_100 > 0) {
            // delete
            $query = "delete from {$this->wp_prefix}automatic_log where id < $insert_below_100 and action not like '%Posted%'";
            $this->db->query($query);
        }
    }

    /**
     * Function that checks if the current link is already posted before from any campaign
     * @param string $link_url
     * @return false if not duplicate or the duplicate id if duplicate
     */
    public function is_duplicate($link_url)
    {

        //init
        $duplicate = false;

        // link suffix
        if ($this->isLinkSuffixed == true) {
            if (stristr($link_url, '?')) {
                $link_url = $link_url . '&rand=' . $this->currentCampID;
            } else {
                $link_url = $link_url . '?rand=' . $this->currentCampID;
            }
        }

        $md5 = md5($link_url);

        // Find items from the duplicate cache
        if (!$this->campOldDuplicateLinksFetched) {
            $this->campOldDuplicateLinks = get_post_meta($this->currentCampID, 'wp_automatic_duplicate_cache', 1);

            // array it
            if (!is_array($this->campOldDuplicateLinks)) {
                $this->campOldDuplicateLinks = array();
            }

            $this->campOldDuplicateLinksFetched = true;
        }

        $possibleID = array_search($link_url, $this->campOldDuplicateLinks);

        if ($possibleID != false) {

            $duplicate = true;
            $this->duplicate_id = $possibleID;
        }

        // Find items with meta = this url
        // Amazon product check by ASIN number but only if link sufix is not enabled
        if (!$duplicate) {

            // amazon link duplicate check
            if (stristr($link_url, '/dp/') && stristr($link_url, 'https://amazon.') && !$this->isLinkSuffixed) {

                $amazon_link_parts = explode('/dp/', $link_url);
                $amazon_asin = $amazon_link_parts[1];
                $query = "SELECT post_id FROM `{$this->wp_prefix}postmeta` WHERE meta_key= 'product_asin' and `meta_value` = '$amazon_asin' limit 1";
            } else {
                $query = "SELECT post_id from {$this->wp_prefix}postmeta where meta_key ='$md5' ";
            }

            $pres = $this->db->get_results($query);

            if (count($pres) == 0) {
                $duplicate = false;
            } else {

                $duplicate = true;

                foreach ($pres as $prow) {

                    $ppid = $prow->post_id;
                    $this->duplicate_id = $ppid;

                    $pstatus = get_post_status($ppid);

                    if ($pstatus != 'trash') {
                        break;
                    }
                }
            }
        }

        // Check if completely deleted
        if ($this->isLinkOnce) {
            if (!$duplicate) {

                $query = "SELECT link_url from {$this->wp_prefix}automatic_links where link_url='$md5' ";
                $pres = $this->db->get_results($query);

                if (count($pres) != 0) {
                    $duplicate = true;
                    $this->duplicate_id = 'Deleted';
                }
            }
        }

        // Update Duplicate cache
        if ($duplicate == true) {

            // duplicated url, add it to the duplicate cache array
            if (is_numeric($this->duplicate_id)) {
                $this->campNewDuplicateLinks[$this->duplicate_id] = $link_url;
                $this->campDuplicateLinksUpdate = true;
            }
        }

        return $duplicate;
    }

    /**
     * Function link exclude to execlude links
     *
     * @param unknown $camp_id
     * @param unknown $source_link
     */
    public function link_execlude($camp_id, $source_link)
    {

        if ($this->campExcludedLinksFetched == true) {
            $execluded_links = $this->campExcludedLinks;
        } else {

            $execluded_links = get_post_meta($camp_id, '_execluded_links', 1);
            $this->campExcludedLinks = $execluded_links;
            $this->campExcludedLinksFetched = true;
        }

        $newExecluded_links = $execluded_links . ',' . $source_link;

        $this->campExcludedLinks = $newExecluded_links;

        //skip updating excluded links not to remember them next time
        if (in_array('OPT_LINK_NOEXCLUDE', $this->camp_opt)) {
            return;
        }

        update_post_meta($camp_id, '_execluded_links', $newExecluded_links);

    }

    /**
     * Check if link is execluded or not i.e it didn't contain exact match keys or contins blocked keys
     *
     * @param unknown $camp_id
     * @param unknown $link
     */
    public function is_execluded($camp_id, $link)
    {
        if ($this->campExcludedLinksFetched == true) {
            $execluded_links = $this->campExcludedLinks;
        } else {
            $execluded_links = get_post_meta($camp_id, '_execluded_links', 1);
            $this->campExcludedLinks = $execluded_links;
            $this->campExcludedLinksFetched = true;

            //clean cache if size of excluded_links exceeded 100000 characters
            if (strlen($execluded_links) > 100000) {
                delete_post_meta($camp_id, '_execluded_links');
                echo '<br>Excluded links cache cleaned....';
            }

        }

        if (stristr(',' . $execluded_links, $link)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function cache_image
     * return local image src if found
     * return false if not cached
     */
    public function is_cached($remote_img, $data_md5)
    {

        // md5
        $md5 = md5($remote_img);

        // query database for this image

        $query = "SELECT * FROM {$this->db->prefix}automatic_cached where img_hash='$md5' and img_data_hash='$data_md5' limit 1";

        $rows = $this->db->get_results($query);

        if (count($rows) == 0) {
            return false;
        }

        $row = $rows[0];

        // hm we have cached image with previous same source let's compare
        $local_src = $row->img_internal;

        // make sure current image have same data md5 right now otherwise delete
        // curl get
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($local_src));
        $exec = curl_exec($this->ch);

        if (md5($exec) == $data_md5) {

            $this->cached_file_path = $row->img_path;

            return $local_src;
        } else {

            // now the local image no more giving the same md5 may be deleted or changed delete the record
            $query = "delete from {$this->db->prefix}automatic_cached where img_hash = '$md5' ";
            $this->db->query($query);

            return false;
        }
    }

    /**
     *
     * @param unknown $remote_img
     * @param unknown $local_img
     * @param number $thumb_id
     */
    public function img_cached($remote_img, $local_img, $image_data_md5, $file_path)
    {
        $md5 = md5($remote_img);
        $query = "insert into {$this->db->prefix}automatic_cached(img_external,img_internal,img_hash,img_data_hash,img_path) values ('$remote_img','$local_img','$md5','$image_data_md5','$file_path')";
        $this->db->query($query);
    }

    /**
     * deactivate keyword : set the reactivation time to one comig hour
     * Set $seconds to 0 to deactivate permanently
     *
     * @param integer $camp_id
     * @param string $keyword
     *
     */
    public function deactivate_key($camp_id, $keyword, $seconds = 3600)
    {
        $deactivatedUntill = time() + $seconds;

        if ($seconds == 0) {
            $deactivatedUntill = 0;
        }

        update_post_meta($camp_id, '_' . md5($keyword), $deactivatedUntill);
    }

    /**
     * is_deactivated: check if the current deactivated keyword is still deactivated or not
     * if yes it return false
     * if not deactivated return true
     *
     * @param integer $camp_id
     * @param string $key
     */
    public function is_deactivated($camp_id, $keyword)
    {

        // let's see if this keyword deactivated till date or not
        $keyword_key = '_' . md5($keyword);
        $deactivated_till = get_post_meta($camp_id, $keyword_key, 1);

        //nonce wp_automatic_reactivate_key
        $nonce = wp_create_nonce('wp_automatic_reactivate_key');

        if (wp_automatic_trim($deactivated_till) == '') {
            $deactivated_till = 1410020931;
        }

        if ($deactivated_till == 0) {

            // still deactivated
            echo '<br>Calling source for this keyword is <strong>deactivated</strong> permanently because last time we called the source for new items, There were no more results to get. You can still <a class="wp_automatic_key_reactivate" data-nonce="' . $nonce . '" data-id="' . $camp_id . '" data-key="' . $keyword_key . '" href="#"><u>Reactivate Now.</u></a><span class="spinner_' . $keyword_key . '  spinner"></span>';
            return false;
        }
        if (time() > $deactivated_till) {
            // time passed let's reactivate
            echo '<br>Keyword search reached end page lets sart from first page again ';
            return true;
        } else {

            // still deactivated
            echo '<br>Calling source for this keyword is <strong>deactivated</strong> temporarily because last time we called the source for new items, There were no more results to get. We will reactivate it after ' . number_format(($deactivated_till - time()) / 60, 2) . ' minutes. You can still <a class="wp_automatic_key_reactivate"  data-nonce="' . $nonce . '" data-id="' . $camp_id . '" data-key="' . $keyword_key . '" href="#"><u>Reactivate Now.</u></a><span class="spinner_' . $keyword_key . '  spinner"></span>';
            return false;
        }
    }

    /**
     * Function is_link_old check if the timestamp for the link is older than minimum
     *
     * @param unknown $camp_id
     * @param unknown $link_timestamp
     */
    public function is_link_old($camp_id, $link_timestamp)
    {
        if ($this->debug == true) {
            echo '<br>is_link_old Minimum:' . $this->minimum_post_timestamp . ' Current:' . $link_timestamp;
        }

        if ($this->minimum_post_timestamp_camp == $camp_id) {
            if ($link_timestamp < $this->minimum_post_timestamp) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * function is_title_duplicate
     *
     * @param unknown $title
     */
    public function is_title_duplicate($title, $post_type)
    {

        /*
         * echo ' title is:'.$title;
         *
         * var_dump(get_page_by_title( $title, 'OBJECT', $post_type ));
         *
         * exit;
         */
        if (wp_automatic_get_page_by_title($title, 'OBJECT', $post_type)) {

            return true;
        } else {

            // check if title contains spechial chars
            if (stristr($title, '&')) {

                $encoded_title = wp_automatic_htmlspecialchars_decode($title, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);

                if ($title != $encoded_title) {
                    // check again, this title may contain special chars

                    if (wp_automatic_get_page_by_title($encoded_title, 'OBJECT', $post_type)) {

                        return true;
                    } else {

                        // hmm, encoded title also was not found, sometimes apstrophe &#039; turns to &#x27 on the DB

                        if (stristr($title, '&#039;')) {
                            $title_hex_app = wp_automatic_str_replace('&#039;', '&#x27;', $title);
                            if (wp_automatic_get_page_by_title($title_hex_app, 'OBJECT', $post_type)) {
                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        }
    }
    public function do_tag_exists(&$camp, $tags)
    {
        $partToCheck = $camp->camp_post_custom_v . $camp->camp_post_content . $camp->camp_post_title;

        $partToCheck;

        foreach ($tags as $tag) {

            if (stristr($partToCheck, $tag)) {
                return true;
            }
        }

        return false;
    }

    /*
     * ---* Date Difference return days between two dates ---
     */
    public function dateDiff($dformat, $endDate, $beginDate)
    {
        $date_parts1 = explode($dformat, $beginDate);
        $date_parts2 = explode($dformat, $endDate);
        $start_date = gregoriantojd($date_parts1[0], $date_parts1[1], $date_parts1[2]);
        $end_date = gregoriantojd($date_parts2[0], $date_parts2[1], $date_parts2[2]);
        return $end_date - $start_date;
    }

    /*
     * function get_time_difference: get the time difference in minutes.
     * @start: time stamp
     * @end: time stamp
     */
    public function get_time_difference($start, $end)
    {
        $uts['start'] = $start;
        $uts['end'] = $end;

        if ($uts['start'] !== -1 && $uts['end'] !== -1) {
            if ($uts['end'] >= $uts['start']) {
                $diff = $uts['end'] - $uts['start'];

                return round($diff / 60, 0);
            }
        }
    }
    public function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    } // end function

    /**
     * function: curl with follocation that will get url if openbasedir is set or safe mode enabled
     *
     * @param unknown $ch
     * @return mixed
     */
    public function curl_exec_follow(&$ch)
    {
        $max_redir = 3;

        for ($i = 0; $i < $max_redir; $i++) {

            $exec = curl_exec($ch);

            $x = curl_error($ch);
            $info = curl_getinfo($ch);

            // meta refresh
            if (stristr($exec, 'http-equiv="refresh"') && $info['http_code'] == 200 && !stristr($exec, '_fb_noscript')) {

                // get the Redirect URL
                preg_match('{<meta.*?http-equiv="refresh".*?>}', $exec, $redirectMatch);
                if (isset($redirectMatch[0]) && wp_automatic_trim($redirectMatch[0]) != '') {

                    preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $redirectMatch[0], $urlMatchs);

                    if (isset($urlMatchs[0]) && stristr($urlMatchs[0], 'http')) {

                        //if does not contain $ or {
                        if (!stristr($urlMatchs[0], '$') && !stristr($urlMatchs[0], '{')) {
                            echo '<br><span style="color:orange">Alert:</span> HTTP redirection suspected... redirecting... enable the option below to don\'t try to guess redirections if you got wrong content';
                            echo '<br>Redirecting to: ' . $urlMatchs[0];

                            $info['http_code'] = 302;
                            $info['redirect_url'] = $urlMatchs[0];
                        }
 
                    
                    }
                }
            } elseif (stristr($exec, 'location.replace')) {

                preg_match('{location\.replace\((.*?)\)}', $exec, $loc_matches);

                $possible_redirect = isset($loc_matches[1]) ? $loc_matches[1] : '';

                if (stristr($possible_redirect, 'http')) {

                    echo '<br><span style="color:orange">Alert:</span> JavaScript redirection suspected... redirecting... enable the option below to don\'t try to guess redirections if you got wrong content';
                    echo '<br>Redirecting to:' . $possible_redirect;

                    $possible_redirect = wp_automatic_str_replace(array(
                        "'",
                        '"',
                    ), '', $possible_redirect);

                    $info['http_code'] = 302;
                    $info['redirect_url'] = wp_automatic_trim($possible_redirect);
                }
            }

            if ($info['http_code'] == 301 || $info['http_code'] == 302) {

                // if there is no reddirect_url
                if (wp_automatic_trim($info['redirect_url']) == '') {
                    $info['redirect_url'] = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

                    // if php is below 5.3.7 and there is no redirect_url option
                    if (wp_automatic_trim($info['redirect_url']) == '') {

                        if (stristr($exec, 'Location:')) {
                            preg_match('{Location:(.*)}', $exec, $loc_matches);
                            $redirect_url = wp_automatic_trim($loc_matches[1]);

                            if (wp_automatic_trim($redirect_url) != '') {
                                $info['redirect_url'] = $redirect_url;
                            }
                        } else {

                            // $info['redirect_url'] = $info['url'];
                        }
                    }
                }

                // fb %20 correction

                if (stristr($info['redirect_url'], 'mbasic.facebook')) {
                    $info['redirect_url'] = wp_automatic_str_replace('%20', '', $info['redirect_url']);
                }

                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_URL, wp_automatic_trim($info['redirect_url']));

                //echo '<br>Redirecting to: ' . $info ['redirect_url'];

                $exec = curl_exec($ch);
            } else {

                // no redirect just return
                break;
            }
        }

        return $exec;
    }

    /**
     * function curl_file_exists: check existence of a file
     *
     * @param unknown $url
     * @return boolean
     */
    public function curl_file_exists($url)
    {

        // curl get
        $x = 'error';
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
        curl_setopt($this->ch, CURLOPT_REFERER, $url);
        // curl_setopt($this->ch, CURLOPT_NOBODY, true);

        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // curl_setopt($this->ch, CURLOPT_NOBODY, false);

        if ($httpCode == '200' || $httpCode == '302') {
            return true;
        }

        return false;
    }

    // function to get user id and create it if not exists
    public function get_user_id_by_display_name($display_name)
    {

        // trim
        $display_name = wp_automatic_trim($display_name);

        // check user existence
        if (!$user = $this->db->get_row($this->db->prepare("SELECT `ID` FROM {$this->db->users} WHERE `display_name` = %s", $display_name))) {

            // replace spaces
            $login_name = wp_automatic_trim(wp_automatic_str_replace(' ', '_', $display_name));

            // no user with this name let's create it and return the id
            $userdata['display_name'] = $display_name;

            // check if URL Friendly without spaces or non latin chars
            if (preg_match('{^[\w|\d|_]*$}', $login_name)) {
                $userdata['user_login'] = $login_name;
            } else {
                $userdata['user_login'] = md5($display_name);
            }

            $userdata['role'] = 'contributor';

            $user_id = wp_insert_user($userdata);

            if (!is_wp_error($user_id)) {
                echo '<br>New user created:' . $display_name;
                return $user_id;
            } else {
                return false;
            }

            return false;
        }

        return $user->ID;
    }

    // remove emoji from instagram
    public function removeEmoji($text)
    {
        if (function_exists('wp_staticize_emoji')) {

            $text = wp_staticize_emoji($text);
            $text = preg_replace('{<img src="https://s.w.org.*?>}s', '', $text);
            return $text;
        } else {

            $clean_text = "";

            // Match Emoticons
            $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
            $clean_text = preg_replace($regexEmoticons, '', $text);

            // Match Miscellaneous Symbols and Pictographs
            $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
            $clean_text = preg_replace($regexSymbols, '', $clean_text);

            // Match Transport And Map Symbols
            $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
            $clean_text = preg_replace($regexTransport, '', $clean_text);

            // Match Miscellaneous Symbols
            $regexMisc = '/[\x{2600}-\x{26FF}]/u';
            $clean_text = preg_replace($regexMisc, '', $clean_text);

            // Match Dingbats
            $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
            $clean_text = preg_replace($regexDingbats, '', $clean_text);

            return $clean_text;
        }
    }

    // function for hyperlinking
    public function hyperlink_this($text)
    {
        return preg_replace(';(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-@]*(\?\S+)?[^\.\s])?)?);u', '<a href="$1" target="_blank">$0</a>', $text);
    }

    // function for stripping inline urls
    public function strip_urls($content)
    {
        return preg_replace('{http[s]?://[^\s]*}', '', $content);
    }

    // fix invalid uلtf chars
    public function fix_utf8($string)
    {

        // check if wrong utf8
        if (1 === @preg_match('/^./us', $string)) {

            return $string;
        } else {
            echo '<br>Fixing invalid utf8 text...';

            if (function_exists('iconv')) {
                return iconv('utf-8', 'utf-8//IGNORE', $string);
            } else {
                echo '<br>Iconv module is not installed, please install PHP iconv module';
                return $string;
            }
        }
    }
    public function cleanthetitle($title)
    {
        $title = wp_automatic_str_replace('nospin', '', $title);
        $title = wp_automatic_str_replace(' ', '-', $title); // Replaces all spaces with hyphens.
        $title = preg_replace('/[^A-Za-z0-9\-]/', '', $title); // Removes special chars.

        return preg_replace('/-+/', '-', $title); // Replaces multiple hyphens with single one.
    }

    // is_enlish: checks if the text is english requires mb_string module
    public function is_english($string)
    {
        if (!function_exists('mb_strlen')) {

            echo '<br>Will skip checking if english as mbstring module must be installed ';

            return true;
        }

        $string = wp_automatic_str_replace(array(
            '”',
            '“',
            '’',
            '‘',
            '’',
        ), '', $string);

        if (strlen($string) != mb_strlen($string, 'utf-8')) {
            return false;
        } else {
            return true;
        }
    }
    public function attach_image($image_url, $camp_opt, $post_id,$post_title = '')
    {

        // Upload dir
        $upload_dir = wp_upload_dir();

        // img host
        $imghost = parse_url($image_url, PHP_URL_HOST);

        if (stristr($imghost, 'http://')) {
            $imgrefer = $imghost;
        } else {
            $imgrefer = 'http://' . $imghost;
        }

        // empty referal
        if (!in_array('OPT_CACHE_REFER_NULL', $camp_opt)) {
            curl_setopt($this->ch, CURLOPT_REFERER, $imgrefer);
        } else {
            curl_setopt($this->ch, CURLOPT_REFERER, '');
        }

        if (stristr($image_url, 'base64,')) {

            $filename = time();
        } else {

            // decode html entitiies
            $image_url = html_entity_decode($image_url);

            if (stristr($image_url, '%')) {
                $image_url = urldecode($image_url);
            }

            // file name to store
            $filename = basename($image_url);

            if (stristr($image_url, ' ')) {
                $image_url = wp_automatic_str_replace(' ', '%20', $image_url);
            }
        }

        // Clean thumb
        if (in_array('OPT_THUMB_CLEAN', $camp_opt)) {

            $clean_name = '';
            $clean_name = sanitize_file_name($post_title);

            echo '<br>clean name' . $clean_name;

            if (wp_automatic_trim($clean_name) != "") {

                // get the image extension \.\w{3}
                $ext = pathinfo($filename, PATHINFO_EXTENSION);

                if (stristr($ext, '?')) {
                    $ext_parts = explode('?', $ext);
                    $ext = $ext_parts[0];
                }

                // clean parameters after filename
                $filename = wp_automatic_trim($clean_name);

                if (wp_automatic_trim($ext) != '') {
                    $filename = $filename . '.' . $ext;
                }
            }
        }

        if (stristr($image_url, 'base64,')) {
            $ex = explode('base64,', $current_img);
            $image_data = base64_decode($ex[1]);

            // set fileName extention .png, .jpg etc
            preg_match('{data:image/(.*?);}', $image_url, $ex_matches);
            $image_ext = $ex_matches[1];

            if (wp_automatic_trim($image_ext) != '') {
                $filename = $filename . '.' . $image_ext;
            }
        } else {

            // get image content
            $x = 'error';
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim(html_entity_decode($image_url)));
            $image_data = $this->curl_exec_follow($this->ch);

            $x = curl_error($this->ch);
        }

        if (wp_automatic_trim($image_data) != '') {

            // check if already saved

            $image_data_md5 = md5($image_data);

            $is_cached = $this->is_cached($image_url, $image_data_md5);

            if ($is_cached != false) {
                echo '<--already cached';
                $file = $this->cached_file_path;
                $guid = $is_cached;
            } else {

                if (stristr($filename, '?')) {
                    $farr = explode('?', $filename);
                    $filename = $farr[0];
                }

                // pagepeeker fix
                if (stristr($image_url, 'pagepeeker') && !in_array('OPT_THUMB_CLEAN', $camp_opt)) {
                    $filename = md5($filename) . '.jpg';
                }

                if (wp_mkdir_p($upload_dir['path'])) {
                    $file = $upload_dir['path'] . '/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                }

                // check if same image name already exists
                if (file_exists($file)) {

                    // get the current saved one to check if identical
                    $already_saved_image_link = $upload_dir['url'] . '/' . $filename;

                    // curl get
                    $x = 'error';
                    $url = $already_saved_image_link;
                    curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));

                    $exec = curl_exec($this->ch);

                    if (wp_automatic_trim($exec) == wp_automatic_trim($image_data)) {
                        $idential = true;
                        echo '<br>Featured image already exists with same path.. using it';
                    } else {
                        echo '<br>Featured image exists with same path but not identical.. saving  ';

                        $filename = time() . '_' . $filename;
                    }
                }

                // saving image
                if (!isset($idential)) {
                    if (wp_mkdir_p($upload_dir['path'])) {
                        $file = $upload_dir['path'] . '/' . $filename;
                    } else {
                        $file = $upload_dir['basedir'] . '/' . $filename;
                    }

                    $f = file_put_contents($file, $image_data);
                }

                $guid = $upload_dir['url'] . '/' . basename($filename);

                $this->img_cached($image_url, $guid, $image_data_md5, $file);
            } // not cached

            // atttatchment check if exists or not
            global $wpdb;

            $query = "select * from $wpdb->posts where guid = '$guid'";
            $already_saved_attachment = $wpdb->get_row($query);

            if (isset($already_saved_attachment->ID)) {

                $attach_id = $already_saved_attachment->ID;
            } else {

                $wp_filetype = wp_check_filetype($filename, null);

                if ($wp_filetype['type'] == false) {
                    $wp_filetype['type'] = 'image/jpeg';
                }

                // Alt handling
                $imgTitle = sanitize_file_name($filename);
                if (in_array('OPT_THUMB_ALT', $camp_opt)) {
                    // $imgTitle = $title;
                }

                $attachment = array(
                    'guid' => $guid,
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => $imgTitle,
                    'post_content' => '',
                    'post_status' => 'inherit',
                );

                $attach_id = wp_insert_attachment($attachment, $file, $post_id);
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                wp_update_attachment_metadata($attach_id, $attach_data);
            }

            if (is_numeric($attach_id) && $attach_id > 0) {
                return $attach_id;
            }
        } else {
            echo ' <-- can not get image content ' . $x;
            return false;
        }
    }

    /**
     * Count chars on text using mb_ module and if not exists it count it using strlen
     *
     * @param unknown $text
     */
    public function chars_count(&$text)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($text);
        } else {
            return strlen($text);
        }
    }
    public function randomUserAgent()
    {
        $os_type = rand(1, 3);

        $chrome_version = rand(100, 108) . '.0.' . rand(1, 4044) . '.' . rand(0, 138);

        // Chrome
        if ($os_type == 1) {

            $agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
        } elseif ($os_type == 2) {

            $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
            $mac_version = "10_" . rand(6, 15) . "_" . rand(1, 4);
            $agent = wp_automatic_str_replace('10_15_4', $mac_version, $agent);
        } elseif ($os_type == 3) {
            $agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
        }

        // main version 81
        $agent = wp_automatic_str_replace("81.0.4044.138", $chrome_version, $agent);

        return $agent;
    }
    /**
     * Function to download file and return the url of it at the server
     *
     * @param String $url
     *            @parm String optional $ext ex .xml
     *            return path of the file or false
     */
    public function download_file($url, $ext = '')
    {

        // curl get
        $x = 'error';

        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        if (wp_automatic_trim($x) == '' && wp_automatic_trim($exec) != '') {

            $upload_dir = wp_upload_dir();
            $filePath = $upload_dir['basedir'] . '/wp_automatic_temp' . $ext;
            $fileUrl = $upload_dir['baseurl'] . '/wp_automatic_temp' . $ext;
            file_put_contents($filePath, $exec);
            return $fileUrl;
        } else {
            echo '<br>Download failed with possible cURL error: ' . $x;
        }

        return false;
    }
    public function convert_single_quotes(&$original_cont)
    {
        $original_cont = preg_replace("{([alt|src|href])[\s]*=[\s]*'(.*?)'}s", "$1=\"$2\"", $original_cont);

        return $original_cont;
    }

    /**
     * Fix src links
     *
     * @param string $content
     *            to be fixed content
     * @param string $url
     * @return string fixed content
     */
    public function fix_relative_paths($content, $url)
    {

        // deprecated, please use wp_automatic_fix_relative_paths instead

        // fix images
        $pars = parse_url($url);

        $host = $pars['host'];
        $scheme = $pars['scheme'];
        if ($scheme != 'https') {
            $scheme = 'http';
        }

        // $url with last slash
        $path = $pars['path'];
        $path_parts = explode('/', $path);
        array_pop($path_parts);

        $url_with_last_slash = $scheme . '://' . $host . implode('/', $path_parts);

        // base url
        preg_match('{<base href="(.*?)"}', $content, $base_matches);
        $base_url = (isset($base_matches[1]) && wp_automatic_trim($base_matches[1]) != '') ? wp_automatic_trim($base_matches[1]) : $url_with_last_slash;

        /* preg_match_all('{<img.*?src[\s]*=[\s]*["|\'](.*?)["|\'].*?>}is', $res['cont'] , $matches); */

        $content = wp_automatic_str_replace('src="//', 'src="' . $scheme . '://', $content);
        $content = wp_automatic_str_replace('href="//', 'href="' . $scheme . '://', $content);

        preg_match_all('{(?:href|src)[\s]*=[\s]*["|\'](.*?)["|\'].*?>}is', $content, $matches);
        $img_srcs = ($matches[1]);
        $img_srcs = array_filter($img_srcs);

        foreach ($img_srcs as $img_src) {

            $original_src = $img_src;

            // ../ remove
            if (stristr($img_src, '../')) {
                $img_src = wp_automatic_str_replace('../', '', $img_src);
            }

            if (stristr($img_src, 'http:') || stristr($img_src, 'www.') || stristr($img_src, 'https:') || stristr($img_src, 'data:image') || stristr($img_src, '#')) {
                // valid image
            } else {
                // not valid image i.e relative path starting with a / or not or //
                $img_src = wp_automatic_trim($img_src);

                if (preg_match('{^//}', $img_src)) {
                    $img_src = $scheme . ':' . $img_src;
                } elseif (preg_match('{^/}', $img_src)) {
                    $img_src = $scheme . '://' . $host . $img_src;
                } else {
                    $img_src = $base_url . '/' . $img_src;
                }

                $reg_img = '{["|\'][\s]*' . preg_quote($original_src, '{') . '[\s]*["|\']}s';
                $content = preg_replace($reg_img, '"' . $img_src . '"', $content);
            }
        }

        // Fix Srcset
        preg_match_all('{srcset[\s]*=[\s]*["|\'](.*?)["|\']}s', $content, $srcset_matches);

        $srcset_matches_raw = $srcset_matches[0];
        $srcset_matches_inner = $srcset_matches[1];

        $i = 0;
        foreach ($srcset_matches_raw as $srcset) {

            if (stristr($srcset, 'http:') || stristr($srcset, 'https:') || stristr($srcset, 'data:image')) {
                // valid
            } else {

                // lets fix
                $correct_srcset = $srcset_inner = $srcset_matches_inner[$i];

                $srcset_inner_parts = explode(',', $srcset_inner);

                foreach ($srcset_inner_parts as $srcset_row) {

                    $srcset_row_parts = explode(' ', wp_automatic_trim($srcset_row));
                    $img_src_raw = $img_src = $srcset_row_parts[0];

                    if (preg_match('{^//}', $img_src)) {
                        $img_src = $scheme . ':' . $img_src;
                    } elseif (preg_match('{^/}', $img_src)) {
                        $img_src = $scheme . '://' . $host . $img_src;
                    } else {
                        $img_src = $scheme . '://' . $host . '/' . $img_src;
                    }

                    $srcset_row_correct = wp_automatic_str_replace($img_src_raw, $img_src, $srcset_row);
                    $correct_srcset = wp_automatic_str_replace($srcset_row, $srcset_row_correct, $correct_srcset);
                }

                $content = wp_automatic_str_replace($srcset_inner, $correct_srcset, $content);
            }

            $i++;
        }

        // Fix relative links
        $content = wp_automatic_str_replace('href="../', 'href="http://' . $host . '/', $content);
        $content = preg_replace('{href="/(\w)}', 'href="http://' . $host . '/$1', $content);
        $content = preg_replace('{href=/(\w)}', 'href=http://' . $host . '/$1', $content); // <a href=/story/sports/college/miss

        return $content;
    }

    /**
     * return width of an image
     */
    public function get_image_width($image_data)
    {
        $upload_dir = wp_upload_dir();

        // let's save the file
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . 'temp_wp_automatic';
        } else {
            $file = $upload_dir['basedir'] . '/' . 'temp_wp_automatic';
        }

        file_put_contents($file, $image_data);

        $size = getimagesize($file);

        if ($size != false) {

            return $size[0];
        } else {
            return 0;
        }
    }

    /**
     * Auto fix lazy loading
     *
     * @param
     *            $cont
     */
    public function lazy_loading_auto_fix($cont)
    {
        preg_match_all('{<img .*?>}s', $cont, $imgsMatchs);

        // if no images
        $imgs_count = count($imgsMatchs[0]);

        if ($imgs_count < 1) {
            return $cont;
        }

        $found_lazy_tag = '';

        if (stristr($cont, ' data-src=')) {
            $found_lazy_tag = 'data-src';
        } elseif (stristr($cont, ' data-lazy-src=')) {
            $found_lazy_tag = 'data-lazy-src';
        } else {

            // suspecting lazy
            $lazy_suspected = false;

            $images_plain = implode(' ', $imgsMatchs[0]);

            if (stristr($images_plain, 'lazy')) {

                if ($this->debug == true) {
                    echo '<br>Lazy word exists, now suspected';
                }

                $lazy_suspected = true;
            } else {

                if ($this->debug == true) {
                    echo '<br>Word Lazy does not exist, lets guess...';
                }

                // src values
                preg_match_all('{ src[\s]?=[\s]?["|\'](.*?)["|\']}', $images_plain, $srcs_matches);

                $found_srcs_count = count($srcs_matches[0]);
                $unique_srcs_count = count(array_unique($srcs_matches[1]));

                if ($this->debug == true) {
                    echo "<br>Post contains $found_srcs_count src attributes, and $unique_srcs_count unique";
                }

                if ($found_srcs_count != 0) {
                    $diff_percentage = (($found_srcs_count - $unique_srcs_count)) * 100 / $found_srcs_count;
                } else {
                    $diff_percentage = 0;
                }

                if ($this->debug == true) {
                    echo '<-- Percentage is ' . $diff_percentage;
                }

                if ($diff_percentage > 39) {
                    $lazy_suspected = true;

                    if ($this->debug == true) {
                        echo '<-- Lazy suspected';
                    }

                } else {
                    if ($this->debug == true) {
                        echo '<-- Lazy was not suspected';
                    }

                }
            }

            // finding suspected lazy attribute
            if ($lazy_suspected) {

                $images_plain_no_src = preg_replace('{ src[\s]?=[\s]?["|\'].*?["|\']}', ' ', $images_plain);

                $replace_known_attributes = array(
                    ' alt',
                    ' srcset',
                    ' data-srcset',
                    ' class',
                    ' id',
                    ' title',
                );

                $images_plain_no_src = wp_automatic_str_replace($replace_known_attributes, ' ', $images_plain_no_src);

                // remove attributes containing small data 1-9
                $images_plain_no_src = preg_replace('{ [\w|-]*?[\s]?=[\s]?["|\'].{1,9}?["|\']}s', ' ', $images_plain_no_src);

                // attributes with slashes
                preg_match_all('{( [\w|-]*?)[\s]?=[\s]?["|\'][^",]*?/[^",]*?["|\']}', $images_plain_no_src, $possible_src_matches);

                $unique_attr = (array_unique($possible_src_matches[1]));

                if (isset($unique_attr[0])) {
                    $found_lazy_tag = $unique_attr[0];
                }
            }
        }

        // found tag?

        // of course not src
        if (wp_automatic_trim($found_lazy_tag) == 'src') {
            return $cont;
        }

        if (wp_automatic_trim($found_lazy_tag) != '') {
            echo '<br>Lazy loading was automatically detected where lazy tag is: <strong>' . $found_lazy_tag . '</strong>...Fixing...';

            $cg_feed_lazy = wp_automatic_trim($found_lazy_tag);
        } else {
            return $cont;
        }

        if (!stristr($cont, $cg_feed_lazy)) {
            return $cont;
        }

        foreach ($imgsMatchs[0] as $imgMatch) {

            if (stristr($imgMatch, $cg_feed_lazy)) {

                $newImg = $imgMatch;
                $newImg = wp_automatic_str_replace(' src=', ' bad-src=', $newImg);
                $newImg = preg_replace('{ bad-src=[\'|"].*?[\'|"] }', ' ', $newImg);
                $newImg = wp_automatic_str_replace(' ' . $cg_feed_lazy, ' src', $newImg);

                $cont = wp_automatic_str_replace($imgMatch, $newImg, $cont);
            }
        }

        return $cont;
    }

    /**
     * function fix_noscript_lazy_loading : checks if <noscript><img exists and replaces it with only the image
     * @param $cont
     * @return mixed
     */
    public function fix_noscript_lazy_loading($cont)
    {

        // if no images
        if (!stristr($cont, '<noscript><img')) {
            return $cont;
        }

        // get all noscript images
        preg_match_all('{<noscript><img.*?>.*?noscript>}s', $cont, $noscript_imgs);

        $noscript_imgs = $noscript_imgs[0];

        $i = 0;
        foreach ($noscript_imgs as $noscript_img) {

            // get the image
            preg_match('{<img.*?>}s', $noscript_img, $img);

            $img = $img[0];

            // replace
            $cont = wp_automatic_str_replace($noscript_img, $img, $cont);

            $i++;
        }

        //report number of fixed images
        echo '<br>Fixed noscript lazy loading for ' . $i . ' images';

        //yellow warning message that explains that if you got duplicate images, please enable the option named "Disable noscript lazy loading fix"
        echo '<br><span style="color:orange">If you got duplicate images, please enable the option named "Disable noscript lazy loading fix"</span>';

        return $cont;

    }

    /**
     * Check if filename contains an ext and append it if not based on the mime
     *
     * @param string $filename
     *            file name
     * @param string $contentType
     *            content type
     */
    public function append_file_ext($filename, $contentType)
    {
        if (!preg_match('/\.(jpg|jpeg|jpe|png|gif|bmp|tiff|tif)$/i', $filename)) {
            if (stristr($contentType, 'image')) {
                $filename .= '.' . wp_automatic_trim(wp_automatic_str_replace('image/', '', $contentType));
                $filename = wp_automatic_str_replace('.php', '', $filename);
            }
        }

        return $filename;
    }

    // random text
    public function randomString($length = 10)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /**
     * Random cookie name
     *
     * @return string|unknown|mixed|boolean
     */
    public function cookieJarName()
    {
        $name = get_option('wp_automatic_cjn', '');

        if (wp_automatic_trim($name) == '') {

            $name = $this->randomString() . '_' . $this->randomString();
            update_option('wp_automatic_cjn', $name);
        }

        return $name;
    }

    /**
     */
    public function get_soundcloud_key()
    {

        // if we already got it before
        if (wp_automatic_trim($this->soundCloudAPIKey) != '') {
            return $this->soundCloudAPIKey;
        }

        // get from cache
        $wp_automatic_sc_client = get_option('wp_automatic_sc_client');

        // verify if key is valid
        if (wp_automatic_trim($wp_automatic_sc_client) != '') {

            // curl get
            $x = 'error';
            $url = "http://api.soundcloud.com/tracks/?q=love&client_id=$wp_automatic_sc_client&limit=1";
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
            $exec = curl_exec($this->ch);
            $x = curl_error($this->ch);

            // Valid key
            if (wp_automatic_trim($exec) != '' && !stristr($exec, 'Unauthorized')) {
                echo '<br>Cached key found to be ok.... using it.';

                $this->soundCloudAPIKey = $wp_automatic_sc_client;
                return $wp_automatic_sc_client;
            } else {
                echo '<br>Current SoundCloud key is no more valid:' . $wp_automatic_sc_client;
            }
        }

        // get new key
        echo '<br>Getting a new SoundCloud Key';

        // get https://soundcloud.com
        // curl get
        $x = 'error';
        $url = 'https://soundcloud.com';
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        preg_match_all('{src="(.*?)"></script>}', $exec, $scripts_matches);

        if (count($scripts_matches[1]) > 0) {
            $last_script = end($scripts_matches[1]);

            // curl get
            $x = 'error';
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($last_script));
            $exec = curl_exec($this->ch);
            $x = curl_error($this->ch);

            // extract key client_id:"Ia1FFtOCVzChwxvKk7dA6OsEQMwHVptP"
            preg_match('{client_id:"(.*?)"}', $exec, $found_client);

            // key found, save it
            if (wp_automatic_trim($found_client[1]) != '') {
                update_option('wp_automatic_sc_client', wp_automatic_trim($found_client[1]), false);
                echo '<br>Got latest key:' . $found_client[1];
                $this->soundCloudAPIKey = $found_client[1];
                return $found_client[1];
            }
        }

        return '';
    }

    // load a cookie
    public function load_cookie($cookie_name, $is_secret = false)
    {

        // already loaded?
        if ($this->was_cookie_loaded($cookie_name)) {
            return;
        }

        // cookie path
        $dir = $this->wp_automatic_upload_dir();

        // secret key
        $secret_key = '';
        if ($is_secret) {

            $secret_option_name = 'wp_automatic_cookie_' . $cookie_name . '_secret';
            $secret_key = get_option($secret_option_name, '');
        }

        $cookie_path = $dir . '/wp_automatic_' . $cookie_name . '_cookie';

        // load
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookie_path);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookie_path);

        // mark as loaded
        $this->is_cookie_loaded = true;
        $this->loaded_cookie_name = $cookie_name;
    }

    // loaded cookie or not
    public function was_cookie_loaded($cookie_name)
    {
        if ($this->is_cookie_loaded && $this->loaded_cookie_name == $cookie_name) {
            return true;
        } else {
            return false;
        }
    }

    // return the path of the cookie
    public function cookie_path($cookie_name)
    {
        $dir = $this->wp_automatic_upload_dir();
        return $dir . '/wp_automatic_' . $cookie_name . '_cookie';
    }

    // delete the cookie jar file
    public function cookie_delete($cookie_name)
    {
        unlink($this->cookie_path($cookie_name));
    }

    //
    public function cookie_content($cookie_name)
    {
        return file_get_contents($this->cookie_path($cookie_name));
    }

    // create uploads/wp_automatic
    public function wp_automatic_upload_dir()
    {
        $dir = wp_upload_dir();
        $baseurl = $dir['basedir'];
        $wp_automatic = $baseurl . '/wp-automatic';

        if (!file_exists($wp_automatic)) {
            mkdir($wp_automatic, 0777, true);
            $myfile = fopen($wp_automatic . "/index.php", "w");
            fclose($myfile);
        }

        return $wp_automatic;
    }

    // generate tags from title words
    public function wp_automatic_generate_tags($post_title)
    {
        $titleWords = explode(' ', $post_title);
        $validTitleWords = array();

        // get stop words
        $stopWordsRaw = file_get_contents(dirname(__FILE__) . '/stopwords.txt');

        $stopWords = array();
        $stopWords = explode(',', $stopWordsRaw);

        // additional stop words
        $additionalStopWordsRaw = get_option('wp_automatic_ttt_stop', '');

        if (wp_automatic_trim($additionalStopWordsRaw) != '') {
            $additionalStopWordsArr = explode("\n", $additionalStopWordsRaw);
            $additionalStopWordsArr = array_filter($additionalStopWordsArr);

            $stopWords = array_merge($stopWords, $additionalStopWordsArr);
        }

        $stopWords = array_map('trim', $stopWords);

        foreach ($titleWords as $titleWord) {

            $titleWord = preg_replace('#[^\p{L}\p{N}\._]+#u', '', $titleWord); // remove all chars expect a letter, number, dot or underscore
            $titleWord = preg_replace('#\.$#', '', wp_automatic_trim($titleWord)); // remove trailing dots only and keep other dots mo.salah

            if (!in_array(strtolower($titleWord), $stopWords)) {

                if (is_numeric(wp_automatic_trim($titleWord))) {
                    // numbers
                } elseif (strlen($titleWord) < 3) {
                    // too short
                } else {
                    $validTitleWords[] = $titleWord;
                }
            }
        }

        return $validTitleWords;
    } // end function

    /**
     * Generate a file name to save from a title
     *
     * @param string $title
     */
    public function file_name_from_title($title)
    {
        $clean_name = '';
        $clean_name = $this->removeEmoji($title);
        $clean_name = wp_automatic_str_replace(array(
            "'",
            "’",
            ".",
        ), '', $clean_name);
        $clean_name = remove_accents($clean_name);
        $clean_name = wp_trim_words($clean_name, 10, '');
        $clean_name = sanitize_file_name($clean_name);

        return $clean_name;
    }

    /**
     * Loads a URL from Google cache, bing cache or google translate proxy if the key was not found on any page
     *
     * @param unknown $url
     * @param unknown $match_key
     */
    public function wp_automatic_auto_proxy($url, $match_key)
    {
        $binglink = "http://webcache.googleusercontent.com/search?q=cache:" . urlencode($url);
        echo '<br>Cache link:' . $binglink;

        $headers = array();
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim(($binglink)));
        curl_setopt($this->ch, CURLOPT_REFERER, 'http://ezinearticles.com');
        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        if (strpos($exec, $match_key)) {
            echo '<-- Found using gcache';
            return $exec;
        } else {

            // Google translate
            echo '<br>Google cache failed Loading using GtranslateProxy...';

            require_once 'inc/proxy.GoogleTranslate.php';

            try {

                $GoogleTranslateProxy = new GoogleTranslateProxy($this->ch);
                $exec = $GoogleTranslateProxy->fetch($url);
                return $exec;
            } catch (Exception $e) {

                echo '<br>ProxyViaGoogleException:' . $e->getMessage();
            }
        }
    }

    /**
     * get the final slug from the source link
     */
    public function get_final_slug($link)
    {
        $link_parts = array_filter(explode('/', $link));
        $link_last_part = end($link_parts);
        return wp_automatic_trim($link_last_part);
    }

    /**
     * function to get an image from bixaBay cached image, if not fetch new and get image
     */
    public function get_pixabay_image($keyword)
    {
        $keyword = wp_automatic_trim($keyword);

        // call images from db
        $query_main = "select * from {$this->wp_prefix}automatic_general where item_type=  'pb_$keyword' and item_status = '0' limit 1";
        $res = $this->db->get_results($query_main);

        // if no images found, grab new images
        if (count($res) == 0) {

            // page index for the call ?
            $query = "select * from {$this->wp_prefix}automatic_keywords where keyword_name='{$keyword}' and keyword_camp=0 limit 1";
            $res = $this->db->get_results($query);

            // first time ?
            if (count($res) == 0) {
                $query = "INSERT INTO {$this->wp_prefix}automatic_keywords ( keyword_name , keyword_camp , keyword_start  ) values (   '{$keyword}' , '0' , '1' )   ";
                $kewyord_id = $q_result = $this->db->query($query);
                $page = 1;
                echo '<br>PixaBay keyword registered? <-- First time';
            } else {
                $page = $res[0]->keyword_start + 1;
                $kewyord_id = $res[0]->keyword_id;
                // update the start for the next call
                $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $page where keyword_id={$res[0]->keyword_id} ";
                $qres = $this->db->query($query);

                echo '<br>PixaBay keyword registered? <-- Yes';
            }

            $this->pixabay_image_fetch($keyword, $page);
            $res = $this->db->get_results($query_main); // repeat after getting the images
        }

        if (count($res) > 0) {

            // change status to 1 and return the image
            $item = $res[0];
            $query = "update {$this->wp_prefix}automatic_general set item_status = 1 where id={$item->id} ";
            $this->db->query($query);
        } else {
            // update the start for the next call

            //reset the start to 0 if returned_pixabay_images is below 20
            if ($this->returned_pixabay_images < 20) {
                echo ' <br> No images found for this keyword, resetting the start to 0';
                $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = 0 where keyword_id={$kewyord_id} ";
                $this->db->query($query);
            } else {
                echo ' <br> Images returned from PixaBay are ' . $this->returned_pixabay_images . ' but none was new, so we will try again next time ';
            }
        }

        $image_url = $item->item_data;
        $image_url = wp_automatic_str_replace('_150', '_960_720', $image_url);
        return $image_url;
    }

    /**
     * Function to fetch new items from PixaBay and cache them
     */
    public function pixabay_image_fetch($keyword, $page)
    {
        echo '<br>Calling PixaBay for new images for keyword:' . $keyword . '...page:' . $page;

        // api key
        $wp_automatic_pixabay_key = wp_automatic_trim(get_option('wp_automatic_pixabay_key', ''));

        if ($wp_automatic_pixabay_key == '') {
            echo '<br><span style="color:red">ERROR: PixaBay API key is required, please visit the plugin settings page and add it.</span>';
            return false;
        }

        // nice, we have a key, lets call
        require_once 'inc/class.pixabay.php';

        $pixabay = new pixabay($this->ch, $wp_automatic_pixabay_key);
        $items = $pixabay->get_images($keyword, $page);

        $success = 0;

        //reset number of returned pixaBay images to 0
        $this->returned_pixabay_images = 0;

        if (is_array($items) && count($items) != 0) {
            echo '<br>Found   ' . count($items) . ' PixaBay items to cache';

            //update number of returned pixaBay images
            $this->returned_pixabay_images = count($items);

            foreach ($items as $item) {

                // check duplicate
                $query = "SELECT * FROM {$this->wp_prefix}automatic_general WHERE item_id = '{$item->id}' limit 1 ";
                $res = $this->db->get_results($query);

                // insert
                if (count($res) == 0) {
                    $query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (    '{$item->id}', '0', '{$item->previewURL}' ,'pb_$keyword')   ";
                    $q_result = $this->db->query($query);
                    $success++;
                } else {
                    echo '<br> - ImageAlready cached ' . $item->previewURL;
                }
            }

            echo '<-- Saved ' . $success . ' unique... ';
        } else {
            echo '<-- Got nothing from PixaBay...';
        }
    }

    /**
     * Function to take a prompt and generate an image using Dall 3 API
     * It calls api call function
     */
    public function dalle3_image_generate($prompt, $size = '1024x1024')
    {

        //prompt to report limited to 100 words and without html
        $prompt_to_report = $prompt;

        //strip html tags
        $prompt_to_report = strip_tags($prompt_to_report);

        //if more than 100 chars, substr and add ...
        if (strlen($prompt_to_report) > 100) {
            $prompt_to_report = substr($prompt_to_report, 0, 100) . '...';
        }

        //report
        echo '<br>Generating image using Dalle 3 API: ' . $prompt_to_report;

        //prepare args array
        $args = array();

        $args['prompt'] = $prompt;

        //api key from settings
        $wp_automatic_openai_key = wp_automatic_single_item('wp_automatic_openai_key');

        //if no key, throw error
        if (wp_automatic_trim($wp_automatic_openai_key) == '') {
            throw new Exception('OpenAI API key is required, please visit the plugin settings page and add it.');
        }

        //add api key to args array apiKey
        $args['apiKey'] = $wp_automatic_openai_key;

        //add size to args array
        $args['size'] = $size;

        //call api /api/dalle to generate image
        try {

            //start timer to measure the time taken to generate the image
            $start_time = microtime(true);

            //log the api call
            wp_automatic_log_new('OpenAI dalle Call', $prompt_to_report);

            $api_call = $this->api_call('dalle', $args);

            //end timer
            $end_time = microtime(true);

            //calculate time taken
            $time_taken = $end_time - $start_time;

            //report time taken
            echo '<br>Time taken to generate the image: ' . $time_taken . ' seconds';

            //log the time taken
            wp_automatic_log_new('OpenAI dalle Time', $time_taken . ' seconds taken to generate the image');

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        //verify if api call is an array and first item is an array containing a key named url
        if (is_array($api_call) && is_array($api_call[0]) && isset($api_call[0]['url'])) {
            //return the url
            return $api_call[0]['url'];
        } else {
            //throw error
            throw new Exception('can not find the image url in the API response');
        }

    }

    /**
     * send an email notification if a required session/api key got invalid and need renewal
     *
     * @param unknown $expired_field
     * @param string $message
     */
    public function notify_the_admin($expired_field, $message = '')
    {

        // notification enabled for this field or not
        $wp_automatic_options = get_option('wp_automatic_options', array());
        if (!in_array('OPT_' . $expired_field, $wp_automatic_options)) {
            return;
        }

        // check if already mailed if so return
        $expired_value = get_option($expired_field, '');
        $latest_notification_expire_value = get_option('wp_automatic_field_expire_' . $expired_field, '');

        if ($latest_notification_expire_value == $expired_value) {
            // already notified, lets return
            return;
        } else {
            // update last notification expire value
            update_option('wp_automatic_field_expire_' . $expired_field, $expired_value);
        }

        // get mail
        $wp_automatic_fb_email = wp_automatic_trim(get_option('wp_automatic_fb_email', ''));

        // if not found email, return
        if (!stristr($wp_automatic_fb_email, '@')) {
            return;
        }

        // sending mail
        wp_mail($wp_automatic_fb_email, 'WP Automatic settings field expired, please update ', $message . ' on site ' . get_site_url());
        echo '<br>Notification email sent to update the value';
    }

    /**
     * convert time from hh:mm:ss to only seconds
     */
    public function time_to_seconds($time)
    {
        if (stristr($time, ':')) {
            $time_parts = explode(':', $time);
            $seconds = $time_parts[0] * 60 * 60 + $time_parts[1] * 60 + $time_parts[2];
            return $seconds;
        } else {
            return $time;
        }
    }

    /**
     * insert a comment but filters the data first if contains a banned word to skip
     *
     * @param unknown $data
     * @return boolean
     */
    public function wp_automatic_insert_comment($data)
    {
        if (in_array('OPT_FILTER_COMMENT', $this->camp_opt)) {

            $cg_comment_filter_keys = $this->camp_general['cg_comment_filter_keys'];
            $cg_comment_filter_keys_arr = explode("\n", $cg_comment_filter_keys);
            $cg_comment_filter_keys_arr = array_filter($cg_comment_filter_keys_arr);
            $pool = $data['comment_author'] . ' ' . $data['comment_content'];

            foreach ($cg_comment_filter_keys_arr as $cg_comment_filter_key) {

                if (stristr($pool, wp_automatic_trim($cg_comment_filter_key))) {
                    echo '<br><-- One comment (from ' . $data['comment_author'] . ' ) skipped, contains the banned keyword:' . $cg_comment_filter_key;
                    // skip this comment
                    return false;
                }
            }
        }

        $result = wp_insert_comment($data);

        //if failed, report
        if ($result == false) {
            echo '<br><-- Failed to insert a comment';

            print_r($data);
            exit;

        }

    }

    //function to find prompt between [gpt3] and [/gpt3] then use the api_call function to get the content from the plugin API and replace the prompt with the content
    public function openai_gpt3_tags_replacement($content)
    {

        //replace [gpt] with [gpt3] and [/gpt] with [/gpt3]
        $content = wp_automatic_str_replace('[gpt]', '[gpt3]', $content);
        $content = wp_automatic_str_replace('[/gpt]', '[/gpt3]', $content);

        // find all prompts
        preg_match_all('/\[gpt3\](.*?)\[\/gpt3\]/su', $content, $matches);

        // if found prompts replace them with the content from the plugin API
        if (count($matches[0]) > 0) {

            // loop through all prompts
            foreach ($matches[0] as $index => $prompt) {

                //report processing a prompt
                echo '<br><br>A new prompt found, processing...';

                // get the prompt text
                $prompt_text = $matches[1][$index];

                // take a copy of prompt text to substitute the result later
                $prompt_text_copy = $prompt_text;

                
                //mask embed codes with iframe 
                //convert [embed]https://www.youtube.com/watch?v=video_id[/embed] to <iframe src="https://www.youtube.com/watch?v=video_id"></iframe>
                $prompt_text = $this->embeds_to_iframes($prompt_text);

                
                
                //if prompt text contains a square bracket [ then do shortcodes using the function do_shortcode
                if (strpos($prompt_text, '[') !== false) {
                    echo '<br> - Prompt contains a shortcode, doing shortcodes...';
                    $prompt_text = do_shortcode($prompt_text);
                }
 
                // get the content from the plugin API
                //try catch to catch any errors
                try {

                    // take a copy of the prompt to report now
                    $prompt_text_to_report = $prompt_text;

                    // if prompt_text_to_report is longer than 100 words, strip html tags and trim the text to 100 words
                    if (str_word_count(($prompt_text_to_report)) > 100) {

                        $prompt_text_to_report = wp_trim_words(strip_tags($prompt_text_to_report), 100);

                        //convert html to entities
                        $prompt_text_to_report = htmlentities($prompt_text_to_report);

                        //add ... to the end of the text
                        $prompt_text_to_report = $prompt_text_to_report . '...';
                    }

                    // report the prompt text
                    echo '<br>Processing Found AI prompt: ' . $prompt_text_to_report;
                    

                    //model
                    $model = 'gpt-3.5-turbo';

                    if (in_array('OPT_OPENAI_CUSTOM', $this->camp_opt)) {

                        $model = isset($this->camp_general['cg_openai_model']) ? $this->camp_general['cg_openai_model'] : 'gpt-3.5-turbo';

                        //if not empty cg_openai_fine_tuned_model, use it instead
                        if (isset($this->camp_general['cg_openai_fine_tuned_model']) && wp_automatic_trim($this->camp_general['cg_openai_fine_tuned_model']) != '') {
                            $model = $this->camp_general['cg_openai_fine_tuned_model'];
                        }

                    }

                    // model gpt-3.5-turbo is limited to 4000 tokens, taking precaution to limit the prompt text to 1400 words
                    if ($model == 'gpt-3.5-turbo') {

                        // if prompt_text contains the word summarize, strip html tags
                        if (stristr($prompt_text, 'summarize')) {
                            echo '<br>- Prompt text contains the word summarize, stripping html tags';

                            $prompt_text = strip_tags($prompt_text);
                        }

                        // check if prompt_text word count is longer than 1500 and if yes, strip html tags and trim the text to 1500 words
                        $word_count = str_word_count(($prompt_text));

                        echo '<br>- Prompt text word count: ' . $word_count;

                        /*
                    Cancelled trim because new version of gpt turbo has a limit of 16k tokens
                    if( $word_count > 1400){
                    echo '<br>- Prompt text word count is longer than 1400, trimming the text to 1400 words, not to exceed the 4000 tokens limit for gpt-3.5-turbo';
                    //$prompt_text = wp_trim_words(strip_tags($prompt_text), 1400);
                    }*/

                    }

                    // get prompt result from cache and if not, call the api
                    $gpt3_content = $this->get_cached_prompt_result($prompt_text);

                    // if prompt result is not found in cache, call the api
                    if ($gpt3_content == false) {

                        // call the api
                        // get the content from the plugin API
                        $gpt3_content = $this->openai_gpt3($prompt_text);

                        //convert embeds iframes back to embed codes 
                        $gpt3_content = $this->iframes_to_embeds($gpt3_content);
                        
                        // save the api call result in the cache
                        $this->cache_prompt_result($prompt_text, $gpt3_content);

                    } else {
                        echo '<br>- Prompt result found in cache, skipping the api call';

                        //log reading the prompt from the cache with the prompt text
                        $prompt_text_to_report = wp_trim_words(strip_tags($prompt_text_to_report), 100);
                        wp_automatic_log_new('OpenAI prompt result found in cache: ', $prompt_text_to_report);

                    }

                    // report the content length
                    echo '<br>- AI returned content length: ' . strlen($gpt3_content);

                    


                } catch (Exception $e) {

                    // set openaiFailed to true
                    $this->openaiFailed = true;

                    // report the error
                    echo '<br>- AI prompt error: ' . $e->getMessage();

                    // if error found replace the prompt with the error message
                    $gpt3_content = $prompt_text;

                }

                // replace the prompt with the content from the plugin API
                $content = wp_automatic_str_replace('[gpt3]' . $prompt_text_copy . '[/gpt3]', $gpt3_content, $content);

            }

        }

        return $content;

    }

    /**
     * function to recieve a prompt then read the apikey from the plugin setttings 
     * and then use the method api_call to get the content from the plugin API
     * The only fuction to take a prompt and return the content from the plugin API
     * Used by openai_gpt3_tags_replacement and gpt3_get_post from core.gpt3.php
     */

    public function openai_gpt3($prompt, $key = null)
    {

        // get the apikey from the plugin settings
        if ($key == null) {
       
            
            
            //if openrouter is enabled, get the key from the plugin settings wp_automatic_openrouter_key
            if (in_array('OPT_USE_OPENROUTER', $this->camp_opt)) {
                $wp_automatic_openai_key = wp_automatic_single_item('wp_automatic_openrouter_key');
            }else{

                //openai key
                $wp_automatic_openai_key = wp_automatic_single_item('wp_automatic_openai_key');

            }
       
       
        } else {
            $wp_automatic_openai_key = $key;
        }
 
        // if the apikey is not found throw error
        if (wp_automatic_trim($wp_automatic_openai_key) == '') {

            throw new Exception('OpenAI API key not found, please add your OpenAI/OpenRouter API key in the plugin settings');

        }

        // prompt_to_log is a copy of the prompt to log in the plugin log but strip html and  limit to 100 words
        $prompt_to_log = wp_trim_words(strip_tags($prompt), 100);

        // key to log, replace first 10 characters with *
        $key_to_log = substr($wp_automatic_openai_key, 0, 10) . '**********';

        echo '<br>- Using OpenAI key: ' . $key_to_log;

        //add key to prompt to log
        $prompt_to_log = $prompt_to_log . ' (key: ' . $key_to_log . ')';

        //log the prompt and the content in the plugin log
        wp_automatic_log_new('OpenAI prompt: ', $prompt_to_log);

        //try catch to catch any errors
        try {

            // building args array
            $args = array('apiKey' => $wp_automatic_openai_key, 'prompt' => $prompt);


                //model
                $cg_openai_model = $this->camp_general['cg_openai_model'];

                // if cg_openai_fine_tuned_model is not empty, use it instead
                if (isset($this->camp_general['cg_openai_fine_tuned_model']) && wp_automatic_trim($this->camp_general['cg_openai_fine_tuned_model']) != '') {
                    $cg_openai_model = $this->camp_general['cg_openai_fine_tuned_model'];
                }
 

                // if OPT_USE_OPENROUTER is active, use the openai router model from cg_openrouter_model
                if (in_array('OPT_USE_OPENROUTER', $this->camp_opt)) {
                    
                    //if is set and is not empty 
                    if (isset($this->camp_general['cg_openrouter_model']) && wp_automatic_trim($this->camp_general['cg_openrouter_model']) != '') {
                        $cg_openai_model = wp_automatic_trim($this->camp_general['cg_openrouter_model']);
                    }else{
                        //if not set or empty, get default model from the plugin settings wp_automatic_openrouter_model
                        $cg_openai_model_from_settings = get_option('wp_automatic_openrouter_model');
                        if(wp_automatic_trim($cg_openai_model_from_settings) != ''){
                            $cg_openai_model = $cg_openai_model_from_settings;
                        }else{
                            
                            //default free model
                            $cg_openai_model = 'meta-llama/llama-3-8b-instruct:free';

                        }
 

                    }

                    //if model is still empty, throw error
                    if(wp_automatic_trim($cg_openai_model) == ''){
                        throw new Exception('OpenRouter model is required, please visit the plugin settings page and add it');
                    }

                    //set base on args array to openrouter domain
                    $args['base'] = 'openrouter.ai';


                }


                // if model is not empty , trim it and set it in the args array
                if (wp_automatic_trim($cg_openai_model) != '') {
                    $args['model'] = wp_automatic_trim($cg_openai_model);
                }

            // if option OPT_OPENAI_CUSTOM to add temprature and top_p is enabled, set them
            if (in_array('OPT_OPENAI_CUSTOM', $this->camp_opt)) {

                //temprature
                $cg_openai_temp = $this->camp_general['cg_openai_temp'];

                //top_p
                $cg_openai_top_p = $this->camp_general['cg_openai_top_p'];

                // if temprature is not empty , is a number between 0 and 2 trim it and set it in the args array
                if (wp_automatic_trim($cg_openai_temp) != '' && is_numeric($cg_openai_temp) && $cg_openai_temp >= 0 && $cg_openai_temp <= 2) {
                    $args['temperature'] = wp_automatic_trim($cg_openai_temp);

                    //parse the temprature to a float
                    $args['temperature'] = floatval($args['temperature']);

                }


                // if top_p is not empty , is a number between 0 and 1 trim it and set it in the args array
                if (wp_automatic_trim($cg_openai_top_p) != '' && is_numeric($cg_openai_top_p) && $cg_openai_top_p >= 0 && $cg_openai_top_p <= 1) {
                    $args['top_p'] = wp_automatic_trim($cg_openai_top_p);

                    //parse the top_p to a float
                    $args['top_p'] = floatval($args['top_p']);

                }

                //presence_penalty
                $cg_openai_presence_penalty = $this->camp_general['cg_openai_presence_penalty'];

                // if presence_penalty is not empty , is a number between -2 and 2 trim it and set it in the args array
                if (wp_automatic_trim($cg_openai_presence_penalty) != '' && is_numeric($cg_openai_presence_penalty) && $cg_openai_presence_penalty >= -2 && $cg_openai_presence_penalty <= 2) {
                    $args['presence_penalty'] = wp_automatic_trim($cg_openai_presence_penalty);

                    //parse the presence_penalty to a float
                    $args['presence_penalty'] = floatval($args['presence_penalty']);

                }

                //frequency_penalty
                $cg_openai_frequency_penalty = $this->camp_general['cg_openai_frequency_penalty'];

                // if frequency_penalty is not empty , is a number between -2 and 2 trim it and set it in the args array
                if (wp_automatic_trim($cg_openai_frequency_penalty) != '' && is_numeric($cg_openai_frequency_penalty) && $cg_openai_frequency_penalty >= -2 && $cg_openai_frequency_penalty <= 2) {
                    $args['frequency_penalty'] = wp_automatic_trim($cg_openai_frequency_penalty);

                    //parse the frequency_penalty to a float
                    $args['frequency_penalty'] = floatval($args['frequency_penalty']);

                }

            }

            //report model 
            echo '<br>- Using model: ' . $args['model'];
 

            // get the content from the plugin API
            $gpt3_content = $this->api_call('openaiComplete', $args);

           
            //log success in the plugin log with length of the content
            wp_automatic_log_new('OpenAI success: ', 'length: ' . strlen($gpt3_content));

        } catch (Exception $e) {

            //log the error in the plugin log
            wp_automatic_log_new('OpenAI error: ', $e->getMessage());

            // if error throw error
            throw new Exception($e->getMessage());

        }

        //markdown to html
        $gpt3_content = $this->markdown_to_html($gpt3_content);

         //Auto correct reply if <pre><code> found at the beginning of the content
         $gpt3_content = $this->correct_html_code($gpt3_content);
         
        return $gpt3_content;

    }

    /**
     * Corrects the HTML code of the content.
     *
     * This function takes the content as input and correct the reply if was wrapped in <pre><code> tags.
     * It ensures that the HTML code is well-formed and valid.
     *
     * @param string $content The content with HTML code to be corrected.
     * @return string The corrected content with valid HTML code.
     */
    function correct_html_code($content)
    {
        

        //if the reply contains <pre><code> at the beginning and </code></pre> at the end, remove them and decode the html entities
        if(preg_match('/^<pre><code/', $content) &&  stristr($content, '</code></pre>')){
           
            echo '<br>- Reply contained pre and code tags, auto-correcting...';

            $content = preg_replace('/^<pre.*?><code.*?>/', '', $content);
            $content = str_replace('</code></pre>', '', $content);
            $content = html_entity_decode($content);

        }

        //check if contains <html and </html>  and <body and </body>
        //if yes, remove them, we just want the insdie body content
        if(preg_match('/<html/', $content) && preg_match('/<\/html>/', $content) && preg_match('/<body/', $content) && preg_match('/<\/body>/', $content)){
           
            echo '<br>- Reply contained html and body tags, auto-correcting...';

            $content = preg_replace('/^.*?<body.*?>/s', '', $content);
            $content = preg_replace('/<\/body>.*$/s', '', $content);

        }

        return $content;
    }

    /**
     * post request to get the content from the plugin API
     */
    public function api_call($function, $args)
    {

        //limit call count to openAI to 5 on the demo version
        if (($function == 'openaiComplete' || $function == 'dalle') && WPAUTOMATIC_DEMO == true) {

            //get call count for openaiComplete from the options wp_automatic_openai_call_count
            $wp_automatic_openai_call_count = get_option('wp_automatic_openai_call_count', 0);

            // if call count is more than 5 throw error
            if ($wp_automatic_openai_call_count > 5) {
                throw new Exception('OpenAI API call limit reached, please upgrade to the premium version to remove this limit');
            } else {
                // add 1 to the call count
                $wp_automatic_openai_call_count++;
                // update the call count in the options wp_automatic_openai_call_count
                update_option('wp_automatic_openai_call_count', $wp_automatic_openai_call_count);
            }

        }

        // api url
        $api_url = 'http://api.valvepress.com/api/' . $function;

        // license check
        $wp_automatic_license_active = get_option('wp_automatic_license_active', '');

        // if not active throw error
        if (wp_automatic_trim($wp_automatic_license_active) == '') {

            // not active, throw error
            throw new Exception('License not active, please activate your license to use this feature');

        }

        // get the license key
        $wp_automatic_license_key = get_option('wp_automatic_license', '');
        $wp_automatic_license_key = wp_automatic_trim($wp_automatic_license_key);

        //add license to args array
        $args['license'] = $wp_automatic_license_key;

        //add domain name to args array
        $args['domain'] = (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : parse_url(get_home_url(), PHP_URL_HOST);

        //add the plugin version to args array
        $args['v'] = WPAUTOMATIC_VERSION;

        //json creating
        $json = json_encode($args);
 
        //init curl
        $ch = curl_init();

        //POST args to api url using curl and $this->ch as the handle
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_REFERER, isset($_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : parse_url(get_home_url(), PHP_URL_HOST) );
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        //post json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $server_output = curl_exec($ch);

        //check if curl error
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        //curl info
        $curl_info = curl_getinfo($ch);

        //close curl
        curl_close($ch);

        //wrap in try catch
        try {
            $server_output = json_decode($server_output, true);
        } catch (Exception $e) {
            throw new Exception('Error decoding server output');
        }

        //check if server output is json
        if (is_array($server_output) && isset($server_output['error'])) {
            throw new Exception($server_output['error']);
        }

        //check if server output is json
        if (is_array($server_output) && isset($server_output['result'])) {
            return $server_output['result'];
        }

        //check if server output is not json
        if (!is_array($server_output)) {

            throw new Exception('Server output is not json');
        }

        return $server_output['result'];

    }

    /**
     * Function to check the content and if found in markdown format convert it to html
     */
    public function markdown_to_html($content){
        

        //check if markdown is found in the content
        if($this->markdown_found($content)){

            echo '<br>- Markdown found, converting to html...';            

            //if class not exists require it
            if (!class_exists('Parsedown')) {
                require_once 'inc/parsedown.php';
            }

            //convert markdown to html
            $Parsedown = new Parsedown();
            $content = $Parsedown->text($content);
 

            return $content;

           
        }

        

        //no markdown found return the content as is
        return $content;

    }

    /**
     * function to check if markdown is found in the content
     */
    public function markdown_found($content){
         // Define a list of regular expressions for common Markdown syntax elements
    $patterns = [
        '/^#{1,6}\s.+/',             // Headers
        '/\*\*.*\*\*/',              // Bold text
        '/\*.*\*/',                  // Italic text
        '/\[(.*?)\]\((.*?)\)/',      // Links
        '/!\[(.*?)\]\((.*?)\)/',     // Images
        '/^>\s.+/',                  // Blockquotes
        '/^-\s.+/',                  // Unordered lists
        '/^\d+\.\s.+/',              // Ordered lists
        '/`{1,3}.*`{1,3}/',          // Inline code
        '/^```[\s\S]*```/',          // Code blocks
        '/^\s{4}.+/',                // Indented code blocks
    ];

    // Check each pattern against the content
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return true;
        }
    }

    return false;
    }

    /**
     * function to get the prompt result from cache instead of calling openai
     * it md5 the prompt text and check if a custom field connected with the campaign ID exists with the 'wp_automatic_cached_prompt_' md5 as a key
     * if found return the cached prompt result
     * if not found return false
     */
    public function get_cached_prompt_result($prompt)
    {

        // get the campaign ID
        $campaign_id = $this->currentCampID;

        // if campaign ID is not found return false
        if (wp_automatic_trim($campaign_id) == '') {
            return false;
        }

        // md5 the prompt text
        $prompt_md5 = md5($prompt);
        $source_link_md5 = md5($this->currentSourceLink);

        // get the cached prompt result from the custom field connected with the campaign ID
        $cached_prompt_result = get_post_meta($campaign_id, 'wp_automatic_cached_prompt_' . $source_link_md5 . '_' . $prompt_md5, true);

        // if cached prompt result is not found return false
        if (wp_automatic_trim($cached_prompt_result) == '') {
            return false;
        }

        // return the cached prompt result
        return $cached_prompt_result;

    }

    /**
     * function to cache the prompt result in a custom field connected with the campaign ID
     * it md5 the prompt text and save the cached prompt result in a custom field connected with the campaign ID with the 'wp_automatic_cached_prompt_' md5 as a key
     */
    public function cache_prompt_result($prompt, $result)
    {

        // get the campaign ID
        $campaign_id = $this->currentCampID;

        // if campaign ID is not found return false
        if (wp_automatic_trim($campaign_id) == '') {
            return false;
        }

        // md5 the prompt text
        $prompt_md5 = md5($prompt);
        $source_link_md5 = md5($this->currentSourceLink);

        // save the cached prompt result in a custom field connected with the campaign ID with the 'wp_automatic_cached_prompt_' md5 as a key
        update_post_meta($campaign_id, 'wp_automatic_cached_prompt_' . $source_link_md5 . '_' . $prompt_md5, $result);

        // return true
        return true;

    }

    /**
     * function to delete cached prompt results
     * it reads the current campaign id and deletes all custom fields who's key is suffixed with wp_automatic_cached_prompt_
     */
    public function delete_cached_prompt_results()
    {

        // get the campaign ID
        $campaign_id = $this->currentCampID;

        $source_link_md5 = md5($this->currentSourceLink);

        // if campaign ID is not found return false
        if (wp_automatic_trim($campaign_id) == '') {
            return false;
        }

        // get all custom fields connected with the campaign ID
        $custom_fields = get_post_custom($campaign_id);

        // if no custom fields found return false
        if (!is_array($custom_fields)) {
            return false;
        }

        // loop through all custom fields
        $deleted = 0;
        foreach ($custom_fields as $key => $value) {

            // if the key is suffixed with wp_automatic_cached_prompt_ delete it
            if (stristr($key, 'wp_automatic_cached_prompt_' . $source_link_md5)) {
                delete_post_meta($campaign_id, $key);
                $deleted++;
            }

        }

        if ($deleted > 0) {
            echo '<br>Deleted ' . $deleted . ' cached prompt results';
        }

        // return true
        return true;

    }

    /**
     * Reset user agent to the default one
     * Set the default user agent
     * @return void
     */

    public function reset_user_agent()
    {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->agent);
    }

    /**
     * Set user agent to the mobile one
     * Set the mobile user agent
     */
    public function set_mobile_user_agent()
    {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->agent_mobile);
    }

    /**
     * extract_inline_link function
     * it takes a string and removes all html tags then extract any link using REGEX
     * param $string
     * return $link
     */
    public function extract_inline_link($string)
    {

        //report
        echo '<br>Extracting inline link from: content';

        // remove all html tags
        $string = strip_tags($string);

        // extract any link using REGEX
        preg_match_all('/(https?:\/\/[^\s]+)/', $string, $matches);

        // if no links found return false
        if (count($matches[0]) == 0) {
            echo '<br> - No links found';
            return '';
        }

        // return the first link
        echo '<br> - Link found: ' . $matches[0][0];
        return $matches[0][0];

    }
 
    
    /**
     * Sets the price of a WooCommerce product.
     *
     * @param int $id The ID of the product.
     * @param array $key_val An associative array containing the price key-value pairs.
     * @return void
     */
    public function set_woocommerce_product_price($id, $key_val)
    {
        if (function_exists('wc_get_product')) {
            update_post_meta($id, '_price', $key_val);
            $product = wc_get_product( $id );
            $product->set_price( $key_val );
            $product->save();
        }else{
            update_post_meta ( $id, '_price', $key_val );
        }

        wc_delete_product_transients($id); // to reflect changes

    }

    //custom search API function
    //takes the search keyword and returns the search results
    //get the api key from the option wp_automatic_search_key
    function custom_search_api($keyword)
    {

        //get the api key from the plugin settings
        $wp_automatic_search_key = wp_automatic_single_item('wp_automatic_search_key');

        //if the api key is not found throw error
        if (wp_automatic_trim($wp_automatic_search_key) == '') {
            throw new Exception('Search API key not found, please add your search API key in the plugin settings');
        }

        //search engine id 013156076200156289477:e_o1j3uv0rs
        $cx = '013156076200156289477:aavh3lmtysa';

         //issue a call to google custom search API
         //    $url = 'https://www.googleapis.com/customsearch/v1?key=' . urlencode($apiKey) . '&cx=' . urlencode($cx) . '&q=' . urlencode($keyword);
        $url = 'https://www.googleapis.com/customsearch/v1?key=' . urlencode($wp_automatic_search_key) . '&cx=' . urlencode($cx) . '&q=' . urlencode($keyword);

        //curl
        curl_setopt($this->ch, CURLOPT_URL, $url);

        //execute
        $result = curl_exec($this->ch);

        //check if result is not empty
        if (empty($result)) {
            throw new Exception('Empty result from the search API');
        }

        //decode
        $result = json_decode($result, true);

        //check if error
        if (isset($result['error'])) {
            throw new Exception($result['error']['message']);
        }
 
        //return the result
        return $result;


    }

    //function embeds_to_iframes
    //replace [embed]https://www.youtube.com/watch?v=-i1uF3pa0oI[/embed] with <iframe src="https://www.youtube.com/watch?v=-i1uF3pa0oI"></iframe>
    //uses REGEX
    function embeds_to_iframes($prompt){

        if(stristr($prompt, '[embed]')){

            $prompt = preg_replace('/\[embed\](.*?)\[\/embed\]/su', '<iframe src="$1"></iframe>', $prompt, -1, $count);

            echo '<br>- Embeds found to mask before processing the prompt: ' . $count;

        }

        return $prompt;

    }

    //function iframes_to_embeds
    //replace <iframe src="https://www.youtube.com/watch?v=-i1uF3pa0oI"></iframe> with [embed]https://www.youtube.com/watch?v=-i1uF3pa0oI[/embed]
    //uses REGEX
    function iframes_to_embeds($prompt){

        if(stristr($prompt, '<iframe')){

            $prompt = preg_replace('/<iframe src="(.*?)"><\/iframe>/su', '[embed]$1[/embed]', $prompt, -1, $count);

            echo '<br>- Iframes found to convert back to embeds after processing the prompt: ' . $count;

        }

        return $prompt;

    }

} // End of the class
