<?php

// Main Class
require_once 'core.php';
class WpAutomaticRumble extends wp_automatic
{

    /*
     * ---* Fetch a new list of items
     */
    public function rumble_fetch_items($keyword, $camp)
    {
        echo "<br>So I should now get some items from Rumble ...";

        // ini options
        $camp_opt = $this->camp_opt;
        $camp_general = $this->camp_general;

        // items url
        $cg_rm_page = wp_automatic_trim($camp_general['cg_rm_page']);
        $cg_rm_page_md = md5($cg_rm_page);

        //if empty page return false and ask the user to add a correct rumble page URL on the format https://rumble.com/search/all?q=crypto
        if (wp_automatic_trim($cg_rm_page) == '') {
            echo '<br>Rumble page URL is empty please visit rumble.com and get a correct one ';
            return false;
        }

        
        //verify if page contains rumble and if not, ask the user to add a correct page URL
        if (!(stristr($cg_rm_page, 'rumble.')  )) {
            echo '<br>Rumble page URL is not correct please visit rumble.com and get a correct one on the format https://rumble.com/search/all?q=crypto';
            return false;
        }

        // get start-index for this keyword
        $query = "select keyword_start ,keyword_id from {$this->wp_prefix}automatic_keywords where keyword_name='$keyword' and keyword_camp={$camp->camp_id}";
        $rows = $this->db->get_results($query);
        @$row = $rows[0];

        // If no rows add a keyword record
        if (count($rows) == 0) {
            $query = "insert into {$this->wp_prefix}automatic_keywords(keyword_name,keyword_camp,keyword_start) values ('$keyword','{$camp->camp_id}',1)";
            $this->db->query($query);
            $kid = $this->db->insert_id;
            $start = 0;
        } else {
            $kid = $row->keyword_id;
            $start = $row->keyword_start;
        }

        if ($start == -1) {
            echo '<- exhausted link';

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
        }

        // page tag
        if (in_array('OPT_IT_CACHE', $camp_opt)) {

            $after_tag = get_post_meta($camp->camp_id, 'after_tag', 1);
            $after_md = get_post_meta($camp->camp_id, 'after_md', 1);

            echo '<br>Before pagination tag:' . $after_tag;

            if (wp_automatic_trim($after_tag) != '') {

                if ($after_md == $cg_rm_page_md) {

                    $cg_rm_page .= stristr($cg_rm_page, '?') ? '&' : '?';
                    $cg_rm_page .= 'before=' . $after_tag;
                }
            }
        }

        echo '<br>Rumble items url:' . $cg_rm_page;

        echo ' index:' . $start;

        // update start index to start+1
        $nextstart = $start + 1;

        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
        $this->db->query($query);

        // get items
        // curl get
        $x = 'error';
        $url = $cg_rm_page;
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        // error check
        if (wp_automatic_trim($x) != '') {
            echo '<br>Curl error:' . $x;
            return false;
        }

        //echo length of returned data
        echo '<br>Returned data length:' . strlen($exec) . ' chars';

        
        //extract elements with css class video-listing-entry
        $dom = new DOMDocument();
        @$dom->loadHTML($exec);
        $xpath = new DOMXPath($dom);
        $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'video-listing-entry')]");
        $allItms = array();
        foreach ($elements as $element) {

            //save HTML of every element
            $allItms[] = $dom->saveHTML($element);

        }

    
        // Check returned items count
        if (count($allItms) > 0) {

            

            echo '<br>Valid reply returned with ' . count($allItms) . ' item';
 

            //if option OPT_TE_REVERSE is enabled, reverse the array
            if (!in_array('OPT_TE_REVERSE', $camp_opt)) {
                echo '<br>Reversing posts order...';
                $allItms = array_reverse($allItms);
            }

            //if option OPT_TE_TOP is enabled, get only the first item
            if (in_array('OPT_TE_TOP', $camp_opt)) {
                echo '<br>Getting only the first item...';
                $allItms = array_slice($allItms, 0, 1);
            }

        } else {

            echo '<br>No items found';
            delete_post_meta($camp->camp_id, 'after_tag');

            echo '<br>Keyword have no more images deactivating...';
            $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
            $this->db->query($query);

            if (!in_array('OPT_NO_DEACTIVATE', $camp_opt)) {
                $this->deactivate_key($camp->camp_id, $keyword);
            }

        }

        echo '<ol>';

        foreach ($allItms as $itemTxt) {

            //echo $itemTxt;

            $item = array(); //ini item array
            $item['item_img'] = ''; //ini image
             

            //extract the href of the first link <a class="video-item--a" href="/v3g1b2q-crypto-mindset-returns.html"
            //dom
            $dom = new DOMDocument();

            //wrap item txt with HTML <html><head><meta charset="utf-8"> ...<body>
            $itemTxt = '<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"></head><body>' . $itemTxt . '</body></html>';

            @$dom->loadHTML($itemTxt);
            $xpath = new DOMXPath($dom);
           
            //extract href of the first link
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'video-item--a')]");

            //if founds > 0 set href to the href of the first link
            if($elements->length > 0)
            $href = $elements->item(0)->getAttribute('href');

             
           

            //grab the id from the href example href /v3g1b2q-crypto-mindset-returns.html, example id v3g1b2q
            $id = wp_automatic_str_replace('.html', '', wp_automatic_str_replace('/', '', $href));
            
            //set the id to the first part before the dash in v3a8olp-cryptos-top-10-for-the-past-10-years
            $id = explode('-', $id)[0];

            //append https://rumble.com to the href
            $href = 'https://rumble.com' . $href;

            $item['item_url'] = $href;
            $item['item_link'] = $href;           
            $item['item_id'] = $id;

            //get item image <img class="video-item--img" src="https://sp.rmbl.ws/s8/1/s/P/U/A/sPUAm.oq1b.2-small-Crypto-Mindset-Returns.jpg" alt="Crypto Mindset Returns!">
            //get the image which has the class video-item--img exactly and not contains 
            $elements = $xpath->query("//*[@class='video-item--img']");
             
            //if founds > 0 set item_img to the src of the first image
            if($elements->length > 0)
            $item['item_img'] = $elements->item(0)->getAttribute('src');

            //item image alt 
            $item['item_img_alt'] = $elements->item(0)->getAttribute('alt');

            //item duration <span class="video-item--duration" data-value="1:30:13">
            $elements = $xpath->query("//*[@class='video-item--duration']");
            $item['item_duration'] = $elements->item(0)->getAttribute('data-value');

            //video item time <time class="video-item--meta video-item--time" datetime="2023-09-08T14:22:51-04:00"
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'video-item--time')]");
            $item['item_time'] = $elements->item(0)->getAttribute('datetime');

            //get item title <h3 class="video-item--title">Crypto Mindset Returns!</h3>
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'video-item--title')]");
            $item['item_title'] = $elements->item(0)->nodeValue;

            //get author name <a rel="author" class="video-item--by-a video-item--by-a--c2100694" href="/c/freshandfit"><div class="ellipsis-1">FreshandFit
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'video-item--by-a')]");
            $item['item_author'] = $elements->item(0)->nodeValue;

            //get author link <a rel="author" class="video-item--by-a video-item--by-a--c2100694" href="/c/freshandfit"><div class="ellipsis-1">FreshandFit
            $item['item_author_link'] = $elements->item(0)->getAttribute('href');

            //append https://rumble.com to the item author link
            $item['item_author_link'] = 'https://rumble.com' . $item['item_author_link'];



            
            print_r($item);
            exit;

            //extract inner  HTML of the element with class tgme_widget_message_text and set as the item description
             
            $item['item_description'] = ''; //initialize item_description to empty string
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'tgme_widget_message_text')]");
            
            
            //if founds > 0 set item_description to the inner HTML of the element with class tgme_widget_message_text
            if($elements->length > 0)
            $item['item_description'] = $dom->saveHTML($elements->item(0));

            //extract the date <time datetime="2023-05-14T05:48:26+00:00" class="time">05:48</time>
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'time')]");
            $item['item_date'] = $elements->item(0)->getAttribute('datetime');

            //extract views <span class="tgme_widget_message_views">535</span>
            $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'tgme_widget_message_views')]");
            $item['item_views'] = $elements->item(0)->nodeValue;

            //extract video src URL using REGEX <video src="https://cdn4.rumble-cdn.org/file/c796994d28.mp4?token=LjWzVqucLqYGLdeMZkF5R_mQFyZ_AzOPBeXWr_l8SHoLGGSLfutDvkXUbAGID87HZfxdGPebm77iAy4XDFKMpQwmEhXkBZ8Edm5H_8uX7fTUrexECNpnocEVexxFtM3GMZElm9ATXaYhkJ-S4L7BJ_VufDXbYfMpDH7um_UZtgN4mVFocSewqcZSoGn2oMN4nsWU7eWMdXIiAOz2B-HAxqp0029h73iyPaYQvtMVyGIXSon88op1a8VIhikPocCch6HY9L1m8Iafz_IgWbCEyOAqaTBosQhiNbLCeNc2vhNlOFJEas1FGj_-YUgtRaxJ69eCg7JZ43j7ya5HdLxkyw" class="tgme_widget_message_video js-message_video" width="100%" height="100%"></video>
            $item['item_video_link'] = ''; //initialize item_video_link to empty string

            //Youtube video <a class="tgme_widget_message_link_preview" href="https://youtu.be/McLH8oicvgY">
            if (stristr($itemTxt, '<a class="tgme_widget_message_link_preview" href="https://youtu')) {

                //match the video src URL using REGEX
                preg_match_all('/<a class="tgme_widget_message_link_preview" href="([^"]+)"/', $itemTxt, $matches);
                $item['item_video_link'] = $matches[1][0];

                //set item type to video
                $item['item_type'] = 'video';

                //get video ID
                $video_id = wp_automatic_str_replace('https://youtu.be/', '', $item['item_video_link']);

                //remove extra parameters from video ID
                $video_id = explode('?', $video_id)[0];

                //if video ID is not empty, set item_img to youtube maxresdefault image
                if ($video_id != '') {
                    $item['item_img'] = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
                }

            }

            //item author <span class="tgme_widget_message_from_author" dir="auto">Vlad</span>
            $item['item_author'] = ''; //initialize item_author to empty string

            if (stristr($itemTxt, '<span class="tgme_widget_message_from_author"')) {
                $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'tgme_widget_message_from_author')]");
                $item['item_author'] = $elements->item(0)->nodeValue;
            }

            //if itemTxt contains <video
            if (stristr($itemTxt, 'tgme_widget_message_video_thumb')) {
                //match the video src URL using REGEX
                $item['item_video_link'] = '';
                preg_match_all('/<video .*?src="([^"]+)"/', $itemTxt, $matches);
                
                if(!empty($matches[1][0]))
                $item['item_video_link'] = $matches[1][0];

                //get background image URL and set as the item_img <i class="tgme_widget_message_video_thumb" style="background-image:url('https://cdn4.rumble-cdn.org/file/vW-jZL63IoKKBgOwog_7IDfaLXrxoQwhRiW4cF5mNKuOEKPs_5ikdISWwzvg8N1W8hoOcBdcbTfgzH9j7dUeEyo1gh1KsGDg8oM6z76pPf8C3ssg0Admg6EJBN85tPo-RNoKZgLNXdBwuKgvPCb-O0lWtdDAFQeJRUG7jYCXtTa95F-96wTQNVCaaH1vXt0uH7zkD7ML-MNxSZKuDCudyZ1GpBjvyqJGU9rYAQOCV2p6om7MmWh-5-fppAD15uJe1bcxXwPa8YPAXqaH8-QRb_ntsg8NUDiiVYwBYFQVOMPjm75OgmrlHWvErhS1T09pWOEF5VIuvnmI8m5J5A-Lag')"></i>
                preg_match_all('/<i class="tgme_widget_message_video_thumb" style="background-image:url\(\'([^"]+)\'\)"/', $itemTxt, $matches);
                $item['item_img'] = $matches[1][0];

                //set item type to video
                $item['item_type'] = 'video';

            }

            //when image grab the image URL from the background-image of the element with class tgme_widget_message_photo_wrap
            //<a class="tgme_widget_message_photo_wrap blured 5411199334994791329 1259893024_456248225" href="https://t.me/on2club/228" style="width:566px;background-image:url('https://cdn4.rumble-cdn.org/file/QAv_5uliHqzzPyEj90lmUoRcNOhR0WQzcOgY128IO8SyqGLmTh4E-sCku9WdRr8rD8AWT5B9e5FzyFYt_2dag6-zO525ufawDg5Z1Cbw9ez0OA1XcyDBZv2Wrl3TEppjs-21nl188XyvpMmH4OyStCI_x0p7Bo6qGcEUMTz8WJhX8AJD3PlM3fZOGTRwMeWNOjelMNDKRKoABfbzp2nEbSjBmnfmqE2y8Ug4vUOMz2KT4NW1qaJ7QzGSMl7aeHaT69WN1c3-opoVpJoSfrFKoBkugYChF59m76OEJ438dYFVbeVKjXPub5MM-lis0t08u9OUG6_qpN2HFHOgp8uj4A.jpg')"> <div class="tgme_widget_message_photo" style="width:94.333333333333%;padding-top:133.33333333333%"></div>

            if (stristr($itemTxt, 'tgme_widget_message_photo_wrap')) {

                //match the image URL using REGEX
                preg_match_all('/tgme_widget_message_photo_wrap .*?background-image:url\(\'([^"]+)\'\)/', $itemTxt, $matches);
                $item['item_img'] = $matches[1][0];

                //set item type to photo
                $item['item_type'] = 'photo';

            }

            //if a link <div class="link_preview_title" dir="auto">DRINK &amp; DANCE</div>
            if (stristr($itemTxt, 'link_preview_title')) {

                //match the link URL using REGEX
                preg_match_all('/<a class="tgme_widget_message_link_preview" href="([^"]+)"/', $itemTxt, $matches);
                $item['item_link_url'] = $matches[1][0];

                //match the link title using REGEX
                preg_match_all('/<div class="link_preview_title" dir="auto">([^"]+)<\/div>/', $itemTxt, $matches);
                $item['item_link_title'] = $matches[1][0];

                //if item link title is not empty, wrap it in <p> tags and append it to the item description
                if ($item['item_link_title'] != '') {

                    $link_to_add_to_description = '<strong>' . $item['item_link_title'] . '</strong>';

                    //wrap with <a tag that its href is set to the link URL
                    $link_to_add_to_description = '<a href="' . $item['item_link_url'] . '">' . $link_to_add_to_description . '</a>';

                    $item['item_description'] .= $link_to_add_to_description;
                }

                //match the link description using REGEX
                preg_match_all('/<div class="link_preview_description".*?>(.*?)<\/div>/', $itemTxt, $matches);

                //if not empty, set item link description to the extracted link description
				if(! empty($matches[1][0]))				
				$item['item_link_description'] = $matches[1][0];


                //if item link description is not empty, wrap it in <p> tags and append it to the item description
                if ( ! empty($item['item_link_description'])  ) {
                    $item['item_description'] .= '<p>' . $item['item_link_description'] . '</p>';
                }

                //match the link image using REGEX
                preg_match_all('/<div class="link_preview_image" style="background-image:url\(\'([^"]+)\'\)"/', $itemTxt, $matches);

                //if item img is empty and $matches[1][0] is not empty, set item_img to $matches[1][0]
                if (  empty($item['item_img'] ) && !empty( $matches[1][0] )) {
                    $item['item_img'] = $matches[1][0];
                }

                //if shared video, grab video thumb      <i class="link_preview_video_thumb" style="background-image:url('https://cdn4.rumble-cdn.org/file/Z
                if (stristr($itemTxt, 'link_preview_video_thumb')) {
                    preg_match_all('/<i class="link_preview_video_thumb" style="background-image:url\(\'([^"]+)\'\)"/', $itemTxt, $matches);

                    //if item img is empty and $matches[1][0] is not empty, set item_img to $matches[1][0]
                    if ($item['item_img'] == '' && $matches[1][0] != '') {
                        $item['item_img'] = $matches[1][0];
                    }

                }

                //if item txt contains <video and contains t.me, set the item embed url to the link url
                if (stristr($itemTxt, '<video') && stristr($itemTxt, 't.me')) {
                    $item['item_embed_url'] = $item['item_link_url'];
                }

                //set item type to link
                $item['item_type'] = 'link';

            }

            //print item
            //print_r($item);
            //exit;

            $data = (base64_encode(serialize($item)));

            echo '<li> Link:' . $item_link;

            // No image skip
            if (wp_automatic_trim($item['item_img']) == '' && in_array('OPT_TE_IMG', $camp_opt)) {
                echo '<- No image skip';
                continue;
            }

            // Filter type
            if (in_array('OPT_TE_POST_FILTER', $camp_opt)) {

                $item_type = $item['item_type'];

                //uppercase item_type and append OPT_TE_POST_ then set this as search for opt
                $opt = 'OPT_TE_POST_' . strtoupper($item_type);

                //if opt is not in camp_opt skip
                if (!in_array($opt, $camp_opt)) {

                    //echo skipped and show current item type which is not selected
                    echo '<- Skipped (' . $item_type . ')';

                    continue;
                }

            }

            if ($this->is_execluded($camp->camp_id, $item_link)) {
                echo '<-- Execluded';
                continue;
            }

            if (!$this->is_duplicate($item_link)) {
                $query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (  '$id', '0', '$data' ,'te_{$camp->camp_id}_$keyword')  ";
                $this->db->query($query);
            } else {
                echo ' <- duplicated <a href="' . get_edit_post_link($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';
            }
        }

        echo '</ol>';
    }

    /*
     * ---* rumble fetch post ---
     */
    public function rumble_get_post($camp)
    {

        // Campaign options and general fields from db
        $camp_opt = $this->camp_opt;
        $camp_general = $this->camp_general;

        //mocking a keyword
        $keywords = array(
            '*',
        );

        foreach ($keywords as $keyword) {

            $keyword = wp_automatic_trim($keyword);

            // update last keyword
            update_post_meta($camp->camp_id, 'last_keyword', wp_automatic_trim($keyword));

            if (wp_automatic_trim($keyword) != '') {

                // report posting for cg_te_page
                $cg_te_page = wp_automatic_trim($camp_general['cg_te_page']);
                echo '<br>Posting from Rumble page:' . $cg_te_page;

                // getting links from the db for that keyword
                $query = "select * from {$this->wp_prefix}automatic_general where item_type=  'te_{$camp->camp_id}_$keyword' ";
                $this->used_keyword = $keyword;
                $res = $this->db->get_results($query);

                // when no links lets get new links
                if (count($res) == 0) {

                    // clean any old cache for this keyword
                    $query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='te_{$camp->camp_id}_$keyword' ";
                    $this->db->query($query_delete);

                    // get new fresh items
                    $this->rumble_fetch_items($keyword, $camp);

                    // getting links from the db for that keyword
                    $res = $this->db->get_results($query);
                }

                // check if already duplicated
                // deleting duplicated items
                $res_count = count($res);
                for ($i = 0; $i < $res_count; $i++) {

                    $t_row = $res[$i];

                    $t_data = unserialize(base64_decode($t_row->item_data));

                    $t_link_url = $t_data['item_url'];

                    if ($this->is_duplicate($t_link_url)) {

                        // duplicated item let's delete
                        unset($res[$i]);

                        echo '<br>Rumble item (' . $t_data['item_title'] . ') found cached but duplicated <a href="' . get_permalink($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';

                        // delete the item
                        $query = "delete from {$this->wp_prefix}automatic_general where id='{$t_row->id}' ";
                        $this->db->query($query);
                    } else {
                        break;
                    }
                }

                // check again if valid links found for that keyword otherwise skip it
                if (count($res) > 0) {

                    // lets process that link
                    $ret = $res[$i];

                    $data = unserialize(base64_decode($ret->item_data));

                    $temp = $data;

                    echo '<br>Found Link:' . $temp['item_link'];

                    // Item img html
                    if (isset($temp['item_gallery_imgs']) && in_array('OPT_RD_SLIDER', $camp_opt)) {
                        echo '<br>Slider images exist...';

                        // template for img html
                        $cg_rm_full_img_t = $camp_general['cg_rm_full_img_t'];
                        if (wp_automatic_trim($cg_rm_full_img_t) == '') {
                            $cg_rm_full_img_t = '<img src="[img_src]" />';
                        }

                        // build the html
                        $item_gallery_imgs = explode(',', $temp['item_gallery_imgs']);

                        $gallery_html = '';
                        foreach ($item_gallery_imgs as $single_img_src) {
                            $gallery_html .= wp_automatic_str_replace('[img_src]', $single_img_src, $cg_rm_full_img_t);
                        }

                        $temp['item_img_html'] = $gallery_html;
                    } elseif (wp_automatic_trim($temp['item_img']) != '') {
                        $temp['item_img_html'] = '<img src="' . $temp['item_img'] . '" />';
                    } else {
                        $temp['item_img_html'] = '';
                    }

                    //item_title auto generation
                    if (in_array('OPT_IT_AUTO_TITLE', $camp_opt)) {

                        echo '<br>No title generating...';

                        $cg_it_title_count = $camp_general['cg_it_title_count'];
                        if (!is_numeric($cg_it_title_count)) {
                            $cg_it_title_count = 80;
                        }

                        // Clean content from tags , emoji and more
                        $contentClean = $this->removeEmoji(strip_tags(strip_shortcodes(($temp['item_description']))));

                        // remove hashtags
                        if (in_array('OPT_TT_NO_TTL_TAG', $camp_opt)) {
                            $contentClean = preg_replace('{#\S*}', '', $contentClean);
                        }

                        // remove mentions
                        if (in_array('OPT_TT_NO_TTL_MEN', $camp_opt)) {
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

                    // Yt embed
                    if (!isset($temp['item_embed'])) {
                        $temp['item_embed'] = '';
                    }

                    if (stristr($temp['item_url'], 'youtu.be') || stristr($temp['item_url'], 'youtube.com')) {
                        $temp['item_embed'] = '[embed]' . $temp['item_url'] . '[/embed]';
                    }

                    // Gif embed
                    /*
                    $temp ['item_gif_embed'] = '';
                    if (wp_automatic_trim( $temp ['item_gif'] ) != '') {
                    $temp ['item_embed'] = $temp ['item_gif_embed'] = '<img src="' . $temp ['item_gif'] . '"/>';
                    }
                     */

                    //if item type is video or the item_embed_url is set and
                    if (wp_automatic_trim($temp['item_type']) == 'video' || isset($temp['item_embed_url'])) {

                        //if item_embed_url is set, set the target video url to item_embed_url, otherwise set it to item_link
                        $target_video_url = isset($temp['item_embed_url']) ? $temp['item_embed_url'] : $temp['item_link'];

                        //remove https://t.me/ from the beginning of the item_link as a short_link
                        $short_link = wp_automatic_str_replace('https://t.me/', '', $target_video_url);

                        $temp['item_embed'] = '<script async src="https://rumble.org/js/rumble-widget.js?22" data-rumble-post="' . $short_link . '" data-width="100%"></script>';

                        //if item_video_link contains youtube or youtu.be, set item_embed to youtube embed
                        if (stristr($temp['item_video_link'], 'youtube') || stristr($temp['item_video_link'], 'youtu.be')) {
                            $temp['item_embed'] = '[embed]' . $temp['item_video_link'] . '[/embed]';
                        }

                        // official embed
                        /*
                    if (in_array ( 'OPT_RD_OFFICIAL_EMBED', $camp_opt )) {
                    $temp ['item_embed'] = '[embed]https://reddit.com' . $temp ['item_link'] . '[/embed]';
                    }
                     */
                    }

                    // update the link status to 1
                    $query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
                    $this->db->query($query);

                    // if cache not active let's delete the cached videos and reset indexes
                    if (!in_array('OPT_IT_CACHE', $camp_opt)) {
                        echo '<br>Cache disabled claring cache ...';
                        $query = "delete from {$this->wp_prefix}automatic_general where item_type='te_{$camp->camp_id}_$keyword' ";
                        $this->db->query($query);

                        // reset index
                        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
                        $this->db->query($query);
                    }

                    return $temp;
                } else {

                    echo '<br>No links found for this keyword';
                }
            } // if trim
        } // foreach keyword
    }
}
