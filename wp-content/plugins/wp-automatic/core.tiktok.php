<?php

// Main Class
require_once 'core.php';
class WpAutomatictiktok extends wp_automatic
{

    //user agent 
    public $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";

    public function tiktok_get_post($camp)
    {

        //random user agent
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randomUserAgent());

        // ini keywords
        $camp_opt = unserialize($camp->camp_options);
        $keywords = explode(',', $camp->camp_keywords);
        $camp_general = unserialize(base64_decode($camp->camp_general));

        // looping keywords
        foreach ($keywords as $keyword) {

            $keyword = wp_automatic_trim($keyword);

            // update last keyword
            update_post_meta($camp->camp_id, 'last_keyword', wp_automatic_trim($keyword));

            // when valid keyword
            if (wp_automatic_trim($keyword) != '') {

                // record current used keyword
                $this->used_keyword = $keyword;

                echo '<br>Let\'s post a TikTok Video for the key:' . $keyword;

                // getting links from the db for that keyword
                $query = "select * from {$this->wp_prefix}automatic_general where item_type=  'tt_{$camp->camp_id}_$keyword' ";
                $res = $this->db->get_results($query);

                // when no links lets get new links
                if (count($res) == 0) {

                    // clean any old cache for this keyword
                    $query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='tt_{$camp->camp_id}_$keyword' ";
                    $this->db->query($query_delete);

                    // get new links
                    $this->tiktok_fetch_items($keyword, $camp);

                    // getting links from the db for that keyword
                    $res = $this->db->get_results($query);
                }

                // check if already duplicated
                // deleting duplicated items

                $item_count = count($res);

                for ($i = 0; $i < $item_count; $i++) {

                    $t_row = $res[$i];

                    $t_data = unserialize(base64_decode($t_row->item_data));

                    $t_link_url = $t_data['item_url'];

                    echo '<br>Link:' . $t_link_url;

                    // check if link is duplicated
                    if ($this->is_duplicate($t_link_url)) {

                        // duplicated item let's delete
                        unset($res[$i]);

                        echo '<br>tiktok pic (' . $t_data['item_title'] . ') found cached but duplicated <a href="' . get_permalink($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';

                        // delete the item
                        $query = "delete from {$this->wp_prefix}automatic_general where id={$t_row->id}";
                        $this->db->query($query);
                    } else {

                        break;
                    }
                } // end for

                // check again if valid links found for that keyword otherwise skip it
                if (count($res) > 0) {

                    // lets process that link
                    $ret = $res[$i];

                    $temp = unserialize(base64_decode($ret->item_data));

                    //get the item info for this video
                    $current_vid_url = $temp['item_url'];

                    //embed url
                    $oembed_url = "https://www.tiktok.com/oembed?url=" . $current_vid_url;

                    echo '<br>Embed URL:' . $oembed_url;

                    //curl get
                    $x = 'error';

                    curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($oembed_url));
                    $exec = curl_exec($this->ch);
                    $x = curl_error($this->ch);

                    //if reply is {"message":"Something went wrong","code":400}
                    //exclude the link and continue
                    if (stristr($exec, 'code":400')) {
                        echo '<br>Something went wrong with this link, excluding it...';

                        //link_exclude
                        $this->link_execlude( $camp->camp_id, $current_vid_url);

                        $query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
                        $this->db->query($query);
                        continue;
                    }

                    //validating reply, i.e condition: contains {"version
                    if (!stristr($exec, '{"version')) {
                        echo '<br><-- Could not get a valid reply ' . $exec;
                        return false;
                    }

                    //json decode
                    $reply_json = json_decode($exec);

                    //build item details
                    $temp['item_title'] = $reply_json->title;
                    $temp['item_user_username'] = $temp['item_user_name'] = $reply_json->author_name;
                    $temp['item_user_link'] = $reply_json->author_url;
                    $temp['item_img'] = $reply_json->thumbnail_url;
                    $temp['item_img_width'] = $reply_json->thumbnail_width;
                    $temp['item_img_height'] = $reply_json->thumbnail_height;
                    $temp['item_description'] = $this->get_description_from_embed($reply_json->html);

                    // generating title
                    if (true || @wp_automatic_trim($temp['item_title']) == '') {

                        if (in_array('OPT_IT_AUTO_TITLE', $camp_opt)) {

                            echo '<br>No title generating...';

                            $cg_it_title_count = $camp_general['cg_it_title_count'];
                            if (!is_numeric($cg_it_title_count)) {
                                $cg_it_title_count = 80;
                            }

                            // Clean content from tags , emoji and more
                            $contentClean = $this->removeEmoji(strip_tags(strip_shortcodes(($temp['item_description']))));

                            //remove original sound using regex starting from the music char  ♬ original sound - Collins Key
                            $contentClean = preg_replace('{♬.*}', '', $contentClean);

                            // remove hashtags
                            if (in_array('OPT_TT_NO_TTL_TAG', $camp_opt)) {
                                $contentClean = preg_replace('{#\S*}', '', $contentClean);
                            }

                            // remove mentions
                            if (in_array('OPT_TT_NO_TTL_MEN', $camp_opt)) {

                                //remove Replying to text using str replace
                                $contentClean = wp_automatic_str_replace('Replying to ', '', $contentClean);

                                $contentClean = preg_replace('{@\S*}', '', $contentClean);
                            }

                            if (function_exists('mb_substr')) {
                                $newTitle = (mb_substr($contentClean, 0, $cg_it_title_count));
                            } else {
                                $newTitle = (substr($contentClean, 0, $cg_it_title_count));
                            }

                            $temp['item_title'] = in_array('OPT_GENERATE_TW_DOT', $camp_opt) ? ($newTitle) : ($newTitle) . '...';
                        } else {

                            $temp['item_title'] = '(notitle)';
                        }
                    }

                    // report link
                    echo '<br>Found Link:' . $temp['item_url'];

                    // update the link status to 1
                    $query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
                    $this->db->query($query);

                    // if cache not active let's delete the cached videos and reset indexes
                    if (!in_array('OPT_IT_CACHE', $camp_opt)) {
                        echo '<br>Cache disabled claring cache ...';
                        $query = "delete from {$this->wp_prefix}automatic_general where item_type='tt_{$camp->camp_id}_$keyword' ";
                        $this->db->query($query);

                        // reset index
                        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
                        $this->db->query($query);

                        delete_post_meta($camp->camp_id, 'wp_tiktok_next_max_id' . md5($keyword));
                    }

                    $temp['item_embed'] = '<blockquote class="tiktok-embed" cite="' . $temp['item_url'] . '" data-video-id="' . $temp['item_id'] . '" style="max-width: 605px;min-width: 325px;" ><section> </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>';

                    //item_tags extract hashtags as tags
                    $temp['item_tags'] = $this->get_hash_tags($temp['item_description']);

                    // remove hashtags <a title="foryoupage" target="_blank" href="https://www.tiktok.com/tag/foryoupage?refer=embed">#foryoupage</a>
                    if (in_array('OPT_TT_NO_CNT_TAG', $camp_opt)) {

                        echo '<br>Removing hashtags from the description';
                        $temp['item_description'] = preg_replace('{<a[^<]*?href="https://www.tiktok.com/tag.*?</a>}', '', $temp['item_description'], -1, $count);

                        //report number of hashtags removed
                        echo '<br>Removed ' . $count . ' hashtags from the description';

                    }

                    //remove mentions from the description including the link <a target="_blank" title="@collinskey" href="https://www.tiktok.com/@collinskey?refer=embed">@collinskey</a>
                    if (in_array('OPT_TT_NO_CNT_REP', $camp_opt)) {
                        echo '<br>Removing mentions from the description';
                        $temp['item_description'] = preg_replace('{<a target="_blank" title="@.*?</a>}', '', $temp['item_description']);

                        //remove Replying to text using str replace
                        $temp['item_description'] = wp_automatic_str_replace('Replying to ', '', $temp['item_description']);

                        //remove @username from the description
                        $temp['item_description'] = preg_replace('{@.*? }', '', $temp['item_description']);

                    }

                    // remove music from description OPT_TT_NO_CNT_MUSIC <a target="_blank" title="♬ original sound .... a>
                    if (in_array('OPT_TT_NO_CNT_MUSIC', $camp_opt)) {

                        echo '<br>Removing music from the description';
                        $temp['item_description'] = preg_replace('{<a target="_blank" title="♬ original sound.*?a>}', '', $temp['item_description']);
                    }

                    // item images ini
                    $temp['item_images'] = '<img src="' . $temp['item_img'] . '" />';

                    return $temp;
                } else {
                    echo '<br>No links found for this keyword';
                }
            } // if trim
        } // foreach keyword
    }
    public function tiktok_fetch_items($keyword, $camp)
    {

        // report
        echo "<br>So I should now get some items from tiktok for keyword :" . $keyword;

        //random user agent
        $random_agent = $this->randomUserAgent();
        curl_setopt($this->ch, CURLOPT_USERAGENT, $random_agent);

        // ini options
        $camp_opt = unserialize($camp->camp_options);
        if (stristr($camp->camp_general, 'a:')) {
            $camp->camp_general = base64_encode($camp->camp_general);
        }

        $camp_general = unserialize(base64_decode($camp->camp_general));
        $camp_general = array_map('wp_automatic_stripslashes', $camp_general);

        // get start-index for this keyword
        $query = "select keyword_start ,keyword_id from {$this->wp_prefix}automatic_keywords where keyword_name='$keyword' and keyword_camp={$camp->camp_id}";
        $rows = $this->db->get_results($query);
        $row = $rows[0];
        $kid = $row->keyword_id;
        $start = $row->keyword_start;

        if ($start == 0) {
            $start = 1;
        }

        if ($start == -1) {

            echo '<- exhausted keyword';

            if (!in_array('OPT_IT_CACHE', $camp_opt)) {
                $start = 1;
                echo '<br>Cache disabled resetting index to 1';
            } else {

                // check if it is reactivated or still deactivated
                if ($this->is_deactivated($camp->camp_id, $keyword)) {
                    $start = 1;
                } else {
                    // still deactivated
                    return false;
                }
            }
        } else {

            if (!in_array('OPT_IT_CACHE', $camp_opt)) {
                $start = 1;
                echo '<br>Cache disabled resetting index to 1';
            }
        }

        echo ' index:' . $start;

        // update start index to start+1
        $nextstart = $start + 1;
        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
        $this->db->query($query);

        // pagination
        if ($start == 1) {

            // use first base query
            $wp_tiktok_next_max_id = 0;
            echo ' Posting from the first page...';
        } else {

            // not first page get the bookmark
            $wp_tiktok_next_max_id = get_post_meta($camp->camp_id, 'wp_tiktok_next_max_id' . md5($keyword), 1);

            if (wp_automatic_trim($wp_tiktok_next_max_id) == '') {
                echo '<br>No new page max id';
                $wp_tiktok_next_max_id = 0;
            } else {
                if (in_array('OPT_IT_CACHE', $camp_opt)) {
                    echo '<br>max_id:' . $wp_tiktok_next_max_id;
                } else {
                    $start = 1;
                    echo '<br>Cache disabled resetting index to 1';
                    $wp_tiktok_next_max_id = 0;
                }
            }
        }

        // if specific user posting
        if (in_array('OPT_TT_USER', $camp_opt)) {

            $cg_tt_user = wp_automatic_trim($camp_general['cg_tt_user']);
            echo '<br>Specific user:' . $cg_tt_user;

            //try catch and get secUid
            try {
                $secUid = $this->get_tiktok_secUid($cg_tt_user);
            } catch (Exception $e) {

                //echo error in red color
                echo '<br><span style="color:red">' . $e->getMessage() . '</span>';
                return false;
            }

            //build the url for the user without the x-bogus and _signature
            //https://www.tiktok.com/api/user/detail/?WebIdLastTime=1696678658&aid=1988&app_language=en&app_name=tiktok_web&browser_language=en-US&browser_name=Mozilla&browser_online=true&browser_platform=MacIntel&browser_version=5.0%20%28Macintosh%3B%20Intel%20Mac%20OS%20X%2010_15_7%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F120.0.0.0%20Safari%2F537.36&channel=tiktok_web&cookie_enabled=true&device_id=7287179289455281670&device_platform=web_pc&focus_state=true&from_page=user&history_len=2&is_fullscreen=false&is_page_visible=true&language=en&os=mac&priority_region=&referer=&region=EG&screen_height=900&screen_width=1440&secUid=MS4wLjABAAAAw5jmjV4MzJLbcx0_NQ4DlCUUdKMgJQeQNBzMs7W4mZtHR0zLSCioCEnyF3tZM9s2&tz_name=Africa%2FCairo&uniqueId=kelly_bove&webcast_language=en&msToken=82_DwKylufie81PPb0bU_ZY94vDqiDeMoU5oiwsx-ojAoVj45jJC_Q2Cy5ygQc83Kt279mBaeSq6WFRnBu0YAJ6b1rMRsuaBilaeV4wdhW0j1KtaLAG-S5QsPk0egNsd5CXSC6c=&X-Bogus=DFSzswVOvGhANnfOtNGC8t9WcBJh&_signature=_02B4Z6wo00001uK6wfQAAIDC4rrB9JcmPG7iusVAAN0x0c
            $tiktok_url = 'https://www.tiktok.com/api/post/item_list/?WebIdLastTime=1696678658&aid=1988&app_language=en&app_name=tiktok_web&browser_language=en-US&browser_name=Mozilla&browser_online=true&browser_platform=MacIntel&browser_version=5.0%20%28Macintosh%3B%20Intel%20Mac%20OS%20X%2010_15_7%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F120.0.0.0%20Safari%2F537.36&channel=tiktok_web&cookie_enabled=true&count=35&coverFormat=2&cursor=0&device_id=7287179289455281670&device_platform=web_pc&focus_state=true&from_page=user&history_len=7&is_fullscreen=false&is_page_visible=true&language=en&os=mac&priority_region=&referer=&region=EG&screen_height=900&screen_width=1440&secUid='.$secUid.'&tz_name=Africa%2FCairo&webcast_language=en&msToken=7xr8HuNRAXEsahPJHG80kNB9OFlQIanxN3r5a51qlMKEf9U9yqa6AQTH8EZazzP1KskPLkjRwQ5dfm-IK9RPWDX-F7Ey2QmVt3MqGPUayA2CuOReVuuoFnOEAIOYSBUiTdD7ISqKMMIohR7Bmw==';
            
            //generate the signature
            //$signature = $this->generate_signature($tiktok_url, $user_agent);

            //add the signature to the url
            //$tiktok_url .= '&_signature=' . $signature;


        } else {

            // prepare keyword
            $qkeyword = wp_automatic_trim(wp_automatic_str_replace(' ', '', $keyword));
            $qkeyword = wp_automatic_str_replace('#', '', $qkeyword);

            //get the Tioktok challenge id for this tag
            try {
                $tiktok_challenge_id = $this->get_tiktok_challenge_id($qkeyword);
            } catch (Exception $e) {

                //echo error in red color
                echo '<br><span style="color:red">' . $e->getMessage() . '</span>';
                return false;
            }

            //$tiktok_url = 'https://www.tiktok.com/tag/' . urlencode($qkeyword); //retired

            //https://www.tiktok.com/api/challenge/item_list/?WebIdLastTime=1696678658&aid=1988&app_language=en&app_name=tiktok_web&browser_language=en-US&browser_name=Mozilla&browser_online=true&browser_platform=MacIntel&browser_version=5.0%20%28Macintosh%3B%20Intel%20Mac%20OS%20X%2010_15_7%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F120.0.0.0%20Safari%2F537.36&challengeID=6134815&channel=tiktok_web&cookie_enabled=true&count=30&coverFormat=2&cursor=0&device_id=7287179289455281670&device_platform=web_pc&focus_state=true&from_page=hashtag&history_len=2&is_fullscreen=false&is_page_visible=true&language=en&os=mac&priority_region=&referer=&region=EG&screen_height=900&screen_width=1440&tz_name=Africa%2FCairo&webcast_language=en&msToken=gfuGvwLLf9LVyiTYx2N8IjiaSgmdd81OstDAD8Pg_fZW7tg_5BN0-KRT5ToBDIRchGYkuyE97LfgP2BGhwgST4bM8hTf4U9TolbkopB3NTw4tDuL89GLiSiI20vubJjvEoWhyI4=&X-Bogus=DFSzswSLGzhANcpTtNkE6z9WcBnU
            //ignored &_signature=_02B4Z6wo00001hWpY9QAAIDCFalj1bgbK0YVqWdAAOD8ba

            //build the url for the challenge without the x-bogus and _signature
            $tiktok_url = 'https://www.tiktok.com/api/challenge/item_list/?WebIdLastTime=1696678658&aid=1988&app_language=en&app_name=tiktok_web&browser_language=en-US&browser_name=Mozilla&browser_online=true&browser_platform=MacIntel&browser_version=5.0%20%28Macintosh%3B%20Intel%20Mac%20OS%20X%2010_15_7%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F120.0.0.0%20Safari%2F537.36&challengeID=' . $tiktok_challenge_id . '&channel=tiktok_web&cookie_enabled=true&count=30&coverFormat=2&cursor=0&device_id=7287179289455281670&device_platform=web_pc&focus_state=true&from_page=hashtag&history_len=2&is_fullscreen=false&is_page_visible=true&language=en&os=mac&priority_region=&referer=&region=EG&screen_height=900&screen_width=1440&tz_name=Africa%2FCairo&webcast_language=en';

        }

        //infite or load directly
        if (in_array('OPT_TT_INFINITE', $camp_opt)) {

            echo '<br>Loading the videos from the added HTML...';
            $exec = $camp_general['cg_tt_html'];

        } else {

            //user agent
            $user_agent = $this->userAgent;

            //generate an x-bogus value
            try {
                $x_bogus = $this->generate_x_bogus($tiktok_url, $user_agent);
            } catch (Exception $e) {

                //echo error in red color
                echo '<br><span style="color:red">' . $e->getMessage() . '</span>';
                return false;
            }

            //add the x-bogus value to the url
            $tiktok_url .= '&X-Bogus=' . $x_bogus;

 
            //set the user agent
            curl_setopt($this->ch, CURLOPT_USERAGENT, $user_agent);
 
            
            echo '<br>Loading:' . $tiktok_url;
            $x = 'error';
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($tiktok_url));
            $exec = curl_exec($this->ch);
            $x = curl_error($this->ch);
            $info = curl_getinfo($this->ch);

            //verify if the reply is valid and contains itemList
            if (!stristr($exec, 'itemList')) {
                echo '<br><-- Could not get a valid reply, does not contain itemList :'.$exec  ;
                return false;
            }

            //items list exist, parse the json
            $json_arr = json_decode($exec);

            //check if the json is valid
            if (!is_object($json_arr)) {
                echo '<br><-- Could not get a valid reply, json is not valid '  ;
                return false;
            }

            //get the items list
            $items_list = $json_arr->itemList;

            //building the items and items ids arrays
            $items = array();
            $items_ids = array();

            //loop the items list
            foreach ($items_list as $item) {

                //build the item url
                $item_url = 'https://www.tiktok.com/@' . $item->author->uniqueId . '/video/';

                //build the item id
                $item_id = $item->id;

                //add the id to the item url
                $item_url .= $item_id;

                //add the item url to the items array
                $items[] = $item_url;

                //add the item id to the items ids array
                $items_ids[] = $item_id;
            }

        }

         

        if ($info['http_code'] == 403 || stristr($info['url'], 'login')) {
            echo '<br>Tried to load the items page and TikTok returned 403 error, tring auto-proxy ';

            $binglink = "http://webcache.googleusercontent.com/search?q=cache:" . urlencode($tiktok_url);
            echo '<br>Cache link:' . $binglink;

            $headers = array();
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim(($binglink)));
            curl_setopt($this->ch, CURLOPT_REFERER, 'http://ezinearticles.com');
            $exec = curl_exec($this->ch);

        }

        //specific user posts "user-post":{"list":["7255771189174422827","7

        if ( (isset($items) && count($items) >0 ) ||  strpos($exec, '/video/') || strpos($exec, 'user-post":{"list":["')) {


            if( isset($items) && count($items) >0 ){

                //correctly extracted the ids using the API

            //if OPT_TT_USER is enabled and posting from a specific user, get the videos ids list
            }elseif (in_array('OPT_TT_USER', $camp_opt)) {

                //specific user posts "user-post":{"list":["7255771189174422827","7
                preg_match_all('!user-post":{"list":\[(.*?)]!s', $exec, $found_vids_matches);

                $items_ids = $found_vids_matches[1][0]; //array of video ids
                $items_ids = explode(',', $items_ids);

                //remove quotes from the ids
                $items_ids = wp_automatic_str_replace('"', '', $items_ids);

                //build items links array
                $items = array();
                foreach ($items_ids as $item_id) {
                    $items[] = 'https://www.tiktok.com/@' . wp_automatic_trim($cg_tt_user) . '/video/' . $item_id;
                }

               

            } else {

                //extract video links
                preg_match_all('{https://www.tiktok.com/@[\w\d_\.]*?/video/(\d*)}s', $exec, $found_vids_matches);

                $items = $found_vids_matches[0]; //array of video links
                $items_ids = $found_vids_matches[1]; //array of video ids
            }

            
            // reverse
            if (in_array('OPT_TT_REVERSE', $camp_opt)) {
                echo '<br>Reversing order';
                $items = array_reverse($items);
                $items_ids = array_reverse($items_ids);
            }

            echo '<ol>';

            // loop items
            $i = 0;
            foreach ($items as $item) {

                // clean itm
                unset($itm);

                // build item
                $itm['item_id'] = $items_ids[$i];
                $itm['item_url'] = $item;

                $data = base64_encode(serialize($itm));

                $i++;

                echo '<li>' . $itm['item_url'] . '</li>';

                //check if excluded
                if ($this->is_execluded($camp->camp_id, $itm['item_url'])) {
                    echo '<-- Execluded';
					continue;
                }

                if (!$this->is_duplicate($itm['item_url'])) {
                    $query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (    '{$itm['item_id']}', '0', '$data' ,'tt_{$camp->camp_id}_$keyword')  ";
                    $this->db->query($query);
                } else {
                    echo ' <- duplicated <a href="' . get_edit_post_link($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';
                }

                echo '</li>';
            }

            echo '</ol>';

            echo '<br>Total ' . $i . ' pics found & cached';

            // check if nothing found so deactivate
            if ($i == 0) {
                echo '<br>No new items got found ';
                echo '<br>Keyword have no more items deactivating...';
                $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
                $this->db->query($query);

                if (!in_array('OPT_NO_DEACTIVATE', $camp_opt)) {
                    $this->deactivate_key($camp->camp_id, $keyword);
                }

                // delete bookmark value
                delete_post_meta($camp->camp_id, 'wp_tiktok_next_max_id' . md5($keyword));

            } else {

                // get max id
                if (isset($json_arr->hasMore) && $json_arr->hasMore == 1) {

                    echo '<br>Updating max_id:' . $json_arr->cursor;
                    update_post_meta($camp->camp_id, 'wp_tiktok_next_max_id' . md5($keyword), $json_arr->cursor);
                } else {
                    echo '<br>No pagination found deleting next page index';
                    delete_post_meta($camp->camp_id, 'wp_tiktok_next_max_id' . md5($keyword));

                    // disable queries for an hour if cache disabled
                    if (in_array('OPT_IT_CACHE', $camp_opt)) {

                        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
                        $this->db->query($query);

                        if (!in_array('OPT_NO_DEACTIVATE', $camp_opt)) {
                            $this->deactivate_key($camp->camp_id, $keyword);
                        }

                        // delete bookmark value
                        delete_post_meta($camp->camp_id, 'wp_tiktok_next_max_id' . md5($keyword));
                    }
                }
            }
        } else {

            // no valid reply
            echo '<br>No Valid reply for tiktok search <br>' . $exec;
        }
    }

    public function get_description_from_embed($ebmed_code)
    {
        $description = preg_replace('!<blockquote.*?>(.*?)</blockquote>.*!', "$1", $ebmed_code);
        $description = wp_automatic_str_replace(array('<section>', '</section>'), '', $description);
        return wp_automatic_trim($description);
    }

    public function get_hash_tags($text)
    {

        //href="https://www.tiktok.com/tag/fruit">#fruit</a>
        preg_match_all('{>(#.*?)</a>\s}', $text, $hashtags_matches);

        $hashtags_founds = $hashtags_matches[1];
        $hashtags_founds = wp_automatic_str_replace('#', '', $hashtags_founds);
        $hashtags_founds = implode(',', $hashtags_founds);
        return $hashtags_founds;
    }

    /**
     * This function take a tag name and return its challenge id
     * It calls https://m.tiktok.com/api/challenge/detail/?challengeName={keyword}&language=en
     * @param string $keyword
     * @return string
     * it throws an exception if the challenge id is not found or was not able to get it
     */
    public function get_tiktok_challenge_id($keyword)
    {

        //get the challenge id from the cache if exists
        //chaced value are saved in the campaign post meta field with the name is the md5 of the keyword
        $cached_challenge_id = get_post_meta($this->currentCampID, md5($keyword), true);

        //if cached value is valid return it
        if (is_numeric($cached_challenge_id)) {
            echo '<br>Challenge id for tag ' . $keyword . ' is ' . $cached_challenge_id . ' (cached)';
            return $cached_challenge_id;
        }

        //report
        echo '<br>Getting challenge id for tag ' . $keyword;

        //random user agent
        $random_agent = $this->randomUserAgent();
        curl_setopt($this->ch, CURLOPT_USERAGENT, $random_agent);

        //get the challenge id
        $challenge_url = 'https://m.tiktok.com/api/challenge/detail/?challengeName=' . $keyword . '&language=en';

        //curl get
        $x = 'error';
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($challenge_url));
        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        //validating reply, i.e condition: contains {"challengeInfo
        if (!stristr($exec, '{"challengeInfo')) {
            echo '<br><-- Could not get a valid reply ' . $exec;

            //throw an exception
            throw new Exception('Could not get the challenge id for the tag ' . $keyword);
        }

        //json decode
        $reply_json = json_decode($exec);

        //get the challenge id
        $challenge_id = $reply_json->challengeInfo->challenge->id;

        //check if challenge id is valid
        if (!is_numeric($challenge_id)) {
            throw new Exception('Could not get the challenge id for the tag ' . $keyword);
        }

        //report
        echo '<br>Challenge id for tag ' . $keyword . ' is ' . $challenge_id;

        //save the challenge id in the cache
        update_post_meta($this->currentCampID, md5($keyword), $challenge_id);

        return $challenge_id;

    }

    /**
     * This function take a username and return its secUid
     * It calls https://www.tiktok.com/api/user/detail/?WebIdLastTime=1696678658&aid=1988&app_language=en&app_name=tiktok_web&browser_language=en-US&browser_name=Mozilla&browser_online=true&browser_platform=MacIntel&browser_version=5.0%20%28Macintosh%3B%20Intel%20Mac%20OS%20X%2010_15_7%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F120.0.0.0%20Safari%2F537.36&channel=tiktok_web&cookie_enabled=true&device_id=7287179289455281670&device_platform=web_pc&focus_state=true&from_page=user&history_len=2&is_fullscreen=false&is_page_visible=true&language=en&os=mac&priority_region=&referer=&region=EG&screen_height=900&screen_width=1440&secUid=MS4wLjABAAAAw5jmjV4MzJLbcx0_NQ4DlCUUdKMgJQeQNBzMs7W4mZtHR0zLSCioCEnyF3tZM9s2&tz_name=Africa%2FCairo&uniqueId=kelly_bove&webcast_language=en&msToken=82_DwKylufie81PPb0bU_ZY94vDqiDeMoU5oiwsx-ojAoVj45jJC_Q2Cy5ygQc83Kt279mBaeSq6WFRnBu0YAJ6b1rMRsuaBilaeV4wdhW0j1KtaLAG-S5QsPk0egNsd5CXSC6c=&X-Bogus=DFSzswVOvGhANnfOtNGC8t9WcBJh
     * It builds the URL and generate an x-bogus value before calling the api
     * @param string $username
     * @return string
     * it throws an exception if the secUid is not found or was not able to get it
     */
    public function get_tiktok_secUid($username){

            //trim 
            $username = wp_automatic_trim($username);
            
            //get the secUid from the cache if exists
            //chaced value are saved in the campaign post meta field with the name is the md5 of the username
            $cached_secUid = get_post_meta($this->currentCampID, md5($username), true);
    
            //if cached value is valid return it
            if (is_string($cached_secUid) && strlen($cached_secUid) > 10) {
                echo '<br>secUid for user ' . $username . ' is ' . $cached_secUid . ' (cached)';
                return $cached_secUid;
            }
    
            //report
            echo '<br>Getting secUid for user ' . $username;
    
            //user agent
            $random_agent = $this->userAgent;
            curl_setopt($this->ch, CURLOPT_USERAGENT, $random_agent);
    
            //get the secUid
            $secUid_url = 'https://www.tiktok.com/api/user/detail/?WebIdLastTime=1696678658&aid=1988&app_language=en&app_name=tiktok_web&browser_language=en-US&browser_name=Mozilla&browser_online=true&browser_platform=MacIntel&browser_version=5.0%20%28Macintosh%3B%20Intel%20Mac%20OS%20X%2010_15_7%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F120.0.0.0%20Safari%2F537.36&channel=tiktok_web&cookie_enabled=true&device_id=7287179289455281670&device_platform=web_pc&focus_state=true&from_page=user&history_len=2&is_fullscreen=false&is_page_visible=true&language=en&os=mac&priority_region=&referer=&region=EG&screen_height=900&screen_width=1440&secUid=&tz_name=Africa%2FCairo&uniqueId='. $username .'&webcast_language=en&msToken=82_DwKylufie81PPb0bU_ZY94vDqiDeMoU5oiwsx-ojAoVj45jJC_Q2Cy5ygQc83Kt279mBaeSq6WFRnBu0YAJ6b1rMRsuaBilaeV4wdhW0j1KtaLAG-S5QsPk0egNsd5CXSC6c=';
    
            //generate an x-bogus value

            try {
                $x_bogus = $this->generate_x_bogus($secUid_url, $random_agent);
            } catch (Exception $e) {
    
                 //throw an exception
                throw new Exception('Could not generate x-bogus value:' . $e->getMessage());
            }

            //add the x-bogus value to the url
            $secUid_url .= '&X-Bogus=' . $x_bogus;

            //set the user agent
            curl_setopt($this->ch, CURLOPT_USERAGENT, $random_agent);

            //curl get
            $x = 'error';
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($secUid_url));
            $exec = curl_exec($this->ch);
            $x = curl_error($this->ch);

            //example reply {"extra":{"fatal_item_ids":[],"logid":"20231226120119B34A17C8D276DA1A8AFA","now":1703592081000},"log_pb":{"impr_id":"20231226120119B34A17C8D276DA1A8AFA"},"shareMeta":{"desc":"@kelly_bove 5.8m Followers, 69 Following, 74.1m Likes - Watch awesome short videos created by kelly_bove","title":"kelly_bove on TikTok"},"statusCode":0,"status_code":0,"userInfo":{"stats":{"diggCount":0,"followerCount":5800000,"followingCount":69,"friendCount":28,"heart":74100000,"heartCount":74100000,"videoCount":349},"user":{"avatarLarger":"https://p16-sign-va.tiktokcdn.com/musically-maliva-obj/1646105741211654~c5_1080x1080.jpeg?lk3s=a5d48078\u0026x-expires=1703764800\u0026x-signature=r3Q3a7m3iQjKGi62cgKWvVuXnkM%3D","avatarMedium":"https://p16-sign-va.tiktokcdn.com/musically-maliva-obj/1646105741211654~c5_720x720.jpeg?lk3s=a5d48078\u0026x-expires=1703764800\u0026x-signature=y1A1EGEeYS2znNWUjmreW2uhdWg%3D","avatarThumb":"https://p16-sign-va.tiktokcdn.com/musically-maliva-obj/1646105741211654~c5_100x100.jpeg?lk3s=a5d48078\u0026x-expires=1703764800\u0026x-signature=lbfuEoXXshahZOIFY%2FYZkinKh8k%3D","canExpPlaylist":true,"commentSetting":0,"commerceUserInfo":{"commerceUser":false},"duetSetting":0,"followingVisibility":1,"ftc":false,"id":"6740271601105978374","isADVirtual":false,"isEmbedBanned":false,"nickname":"kelly_bove","openFavorite":false,"privateAccount":false,"profileEmbedPermission":1,"profileTab":{"showPlayListTab":false,"showQuestionTab":true},"relation":0,"secUid":"MS4wLjABAAAAw5jmjV4MzJLbcx0_NQ4DlCUUdKMgJQeQNBzMs7W4mZtHR0zLSCioCEnyF3tZM9s2","secret":false,"signature":"Kelly | Zain \u0026 Trek","stitchSetting":0,"ttSeller":false,"uniqueId":"kelly_bove","verified":false}}}

            //validating reply, i.e condition: contains "secUid"
            if (!stristr($exec, '"secUid"')) {
                echo '<br><-- Could not get a valid reply ' . $exec;

                //throw an exception
                throw new Exception('Could not get the secUid for the user ' . $username);
            }

            //json decode
            $reply_json = json_decode($exec);

            //get the secUid
            $secUid = $reply_json->userInfo->user->secUid;

            //check if secUid is valid
            if (!is_string($secUid) || strlen($secUid) < 10) {
                throw new Exception('Could not get the secUid for the user ' . $username. ' secUid is not valid');
            }

            //report
            echo '<br>secUid for user ' . $username . ' is ' . $secUid;

            //save the secUid in the cache
            update_post_meta($this->currentCampID, md5($username), $secUid);

            return $secUid;
     }

     /**
      * This function generate a random signature like _02B4Z6wo00001uK6wfQAAIDC4rrB9JcmPG7iusVAAN0x0c
      */
    public function generate_signature(){
            
            //signature chars
            $signature_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    
            //signature length
            $signature_length = 16;
    
            //signature
            $signature = '';
    
            //loop the signature length
            for ($i = 0; $i < $signature_length; $i++) {
    
                //get a random char from the signature chars
                $signature .= $signature_chars[rand(0, strlen($signature_chars) - 1)];
            }
    
            //report
            echo '<br>Generated signature is ' . $signature;
    
            return $signature;
    }

    /**
     * This function generate a an x-bogus value
     * It takes the url and user agent then make a an api request to xbogusGenerator
     * it sends the tiktokLink and userAgent as parameters
     * It uses the api_call function from core.php file
     * @param string $tiktokLink
     * @param string $userAgent
     * @return string
     * It threw an exception if the x-bogus value is not found or was not able to get it
     */
    public function generate_x_bogus($tiktokLink = '', $userAgent = '')
    {

        //prepare the args array
        $args = array(
            'tiktokLink' => $tiktokLink,
            'userAgent' => $userAgent,
        );

        //call the api in try catch
        try {
            $x_bogus = $this->api_call('xbogusGenerator', $args);
        } catch (Exception $e) {
            throw new Exception('Could not generate x-bogus value:' . $e->getMessage());
        }

        //check if the x-bogus value is valid
        if (!is_string($x_bogus) || strlen($x_bogus) < 10) {
            throw new Exception('Could not generate x-bogus value');
        }

        //report
        echo '<br>x-bogus value is ' . $x_bogus;

        return $x_bogus;

    }

}
