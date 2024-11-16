<?php

// Main Class
require_once 'core.php';
class WpAutomaticaliexpress extends wp_automatic
{

    public function aliexpress_get_post($camp)
    {

        // ini keywords
        $camp_opt = unserialize($camp->camp_options);
        $keywords = explode(',', $camp->camp_keywords);
        $camp_general = unserialize(base64_decode($camp->camp_general));

        if (in_array('OPT_ALIEXPRESS_CUR', $camp_opt)) {

            $cg_ae_custom_cur = wp_automatic_trim($camp_general['cg_ae_custom_cur']);
            echo '<br>Custom currency is requested...' . $cg_ae_custom_cur;

            //price cookie aep_usuc_f=site=ara&c_tp=CAD
            $cookie_value = $this->cookie_content('aliexpress');

            if (!stristr($cookie_value, 'c_tp=' . $cg_ae_custom_cur)) {
                echo ' Found not set, let us set it ';
                $this->cookie_delete('aliexpress');

                $aep_usuc_f = 'aep_usuc_f=c_tp=' . $cg_ae_custom_cur;

                //global domain name site=glo
                if (!in_array('OPT_ALIEXPRESS_DOMAIN', $camp_opt)) {
                    $aep_usuc_f .= '&site=glo&b_locale=en_US';
                }

                curl_setopt($this->ch, CURLOPT_COOKIE, $aep_usuc_f);

            } else {
                echo ' Currency is already set to ' . $cg_ae_custom_cur;
            }

        } else {

            if (!in_array('OPT_ALIEXPRESS_DOMAIN', $camp_opt)) {

                //no currency is required but lets make sure that the domain is set to glo
                //price cookie aep_usuc_f=site=ara&c_tp=CAD
                $cookie_value = $this->cookie_content('aliexpress');

                if (!stristr($cookie_value, 'site=glo')) {
                    echo '<br>Found site glo not set, let us set it ';
                    $this->cookie_delete('aliexpress');

                    $aep_usuc_f = 'aep_usuc_f=site=glo&c_tp=USD&isb=y&b_locale=en_US';

                    curl_setopt($this->ch, CURLOPT_COOKIE, $aep_usuc_f);

                } else {
                    echo '<br>Site found set to Glo';
                }

            }

        }

        //  cookie load
        $this->load_cookie('aliexpress');

        // looping keywords
        foreach ($keywords as $keyword) {

            $keyword = wp_automatic_trim($keyword);

            // update last keyword
            update_post_meta($camp->camp_id, 'last_keyword', wp_automatic_trim($keyword));

            // when valid keyword
            if (wp_automatic_trim($keyword) != '') {

                // record current used keyword
                $this->used_keyword = $keyword;

                echo '<br>Let\'s post AliExpress product for the key:' . $keyword;

                // getting links from the db for that keyword
                $query = "select * from {$this->wp_prefix}automatic_general where item_type=  'ae_{$camp->camp_id}_$keyword' ";
                $res = $this->db->get_results($query);

                // when no links lets get new links
                if (count($res) == 0) {

                    // clean any old cache for this keyword
                    $query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='ae_{$camp->camp_id}_$keyword' ";
                    $this->db->query($query_delete);

                    // get new links
                    $this->aliexpress_fetch_items($keyword, $camp);

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

                        echo '<br>AliExpress product (' . $t_data['item_url'] . ') found cached but duplicated <a href="' . get_permalink($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';

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
                    $current_item_url = $temp['item_url'];

                    // update the link status to 1
                    $query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
                    $this->db->query($query);

                    //get_m_h5_tk
                    echo '<br>Getting _m_h5_tk...';
                    try {
                        $_m_h5_tk_arr = $this->get_m_h5_tk();
                    } catch (Exception $e) {
                        echo 'ERROR: ', $e->getMessage(), "\n";
                        return false;
                    }

                    $token = $_m_h5_tk = $_m_h5_tk_arr[0];
                    $token_enc = $_m_h5_tk_enc = $_m_h5_tk_arr[1];
                    $cna  = $_m_h5_tk_arr[2];

                    echo '<br>Found _m_h5_tk: ' . $_m_h5_tk;
                    echo '<br>Found _m_h5_tk_enc: ' . $_m_h5_tk_enc;
                    echo '<br>Found cna token: ' . $cna;

                    //nice we now have the tokens
                    $productId = $ret->item_id;

                    $appKey = '12574478';
                    $time = time();
                    $data_enc = '%7B%22productId%22%3A%22' . $productId . '%22%2C%22_lang%22%3A%22en_SA%22%2C%22_currency%22%3A%22SAR%22%2C%22country%22%3A%22SA%22%2C%22province%22%3A%22918500040000000000%22%2C%22city%22%3A%22918500040008000000%22%2C%22channel%22%3A%22%22%2C%22pdp_ext_f%22%3A%22%22%2C%22pdpNPI%22%3A%22%22%2C%22sourceType%22%3A%22%22%2C%22clientType%22%3A%22pc%22%2C%22ext%22%3A%22%7B%5C%22foreverRandomToken%5C%22%3A%5C%222fdbd100bd92439bbd66159c59dbaf97%5C%22%2C%5C%22site%5C%22%3A%5C%22glo%5C%22%2C%5C%22webAffiParameters%5C%22%3A%5C%22%7B%5C%5C%5C%22aeuCID%5C%5C%5C%22%3A%5C%5C%5C%22e2a5dcde75104f81aa98b4c7fe55e07a-1717592179938-07798-_ePNSNV%5C%5C%5C%22%2C%5C%5C%5C%22af%5C%5C%5C%22%3A%5C%5C%5C%222263676%5C%5C%5C%22%2C%5C%5C%5C%22affiliateKey%5C%5C%5C%22%3A%5C%5C%5C%22_ePNSNV%5C%5C%5C%22%2C%5C%5C%5C%22channel%5C%5C%5C%22%3A%5C%5C%5C%22AFFILIATE%5C%5C%5C%22%2C%5C%5C%5C%22cv%5C%5C%5C%22%3A%5C%5C%5C%221%5C%5C%5C%22%2C%5C%5C%5C%22isCookieCache%5C%5C%5C%22%3A%5C%5C%5C%22N%5C%5C%5C%22%2C%5C%5C%5C%22ms%5C%5C%5C%22%3A%5C%5C%5C%220%5C%5C%5C%22%2C%5C%5C%5C%22pid%5C%5C%5C%22%3A%5C%5C%5C%22177275576%5C%5C%5C%22%2C%5C%5C%5C%22tagtime%5C%5C%5C%22%3A1717592179938%7D%5C%22%2C%5C%22crawler%5C%22%3Afalse%2C%5C%22x-m-biz-bx-region%5C%22%3A%5C%22%5C%22%2C%5C%22signedIn%5C%22%3Afalse%2C%5C%22host%5C%22%3A%5C%22www.aliexpress.com%5C%22%7D%22%7D';
                    $data = urldecode($data_enc);

//json decode data
                    $data = json_decode($data);

//set productId
                    $data->productId = $productId;

//set currency to usd
$cg_ae_custom_cur = wp_automatic_trim($camp_general['cg_ae_custom_cur']);

//if empty set to USD
                    if ($cg_ae_custom_cur == '') {
                        $cg_ae_custom_cur = 'USD';
                    }

$data->_currency = $cg_ae_custom_cur;

//set country to US
$cg_ae_custom_country = isset($camp_general['cg_ae_custom_country']) ? wp_automatic_trim($camp_general['cg_ae_custom_country']) : '';

//if empty set to US
                    if ($cg_ae_custom_country == '') {
                        $cg_ae_custom_country = 'US';
                    }
                    $data->country = $cg_ae_custom_country;

//set language to english
//cg_ae_custom_lang
$cg_ae_custom_lang = wp_automatic_trim($camp_general['cg_ae_custom_lang']);

//if empty set to en_US
                    if ($cg_ae_custom_lang == '') {
                        $cg_ae_custom_lang = 'en_US';
                    }
                    $data->_lang = $cg_ae_custom_lang;

//encode data
                    $data_enc = urlencode(json_encode($data));

//data to string of json
                    $data = json_encode($data);

//explode token
                    //api request sign
                    try
                    {
                        $args = array(
                            'token' => $token,
                            'time' => $time,
                            'appKey' => $appKey,
                            'data' => $data,
                        );
                        $sign = $this->api_call('aliSign', $args);
                    }
                    catch (Exception $e)
                    {
                        //error in red
                        echo '<br><span style="color:red;">ERROR: ' . $e->getMessage() . '</span>';
                        return false;
                    }
                    

                    curl_setopt_array($this->ch, array(
                        CURLOPT_URL => 'https://acs.aliexpress.com/h5/mtop.aliexpress.pdp.pc.query/1.0/?jsv=2.5.1&appKey=12574478&t=' . $time . '&sign=' . $sign . '&api=mtop.aliexpress.pdp.pc.query&type=originaljsonp&v=1.0&timeout=15000&dataType=originaljsonp&callback=mtopjsonp2&data=' . $data_enc,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'accept: */*',
                            'accept-language: en-US,en;q=0.9,ar;q=0.8',
                            'cookie:  _m_h5_tk=' . $token . '; _m_h5_tk_enc=' . $token_enc . '; cna='. $cna . '; ',
                            'referer: https://www.aliexpress.com/',
                            'sec-ch-ua: "Not/A)Brand";v="8", "Chromium";v="126", "Google Chrome";v="126"',
                            'sec-ch-ua-mobile: ?0',
                            'sec-ch-ua-platform: "macOS"',
                            'sec-fetch-dest: script',
                            'sec-fetch-mode: no-cors',
                            'sec-fetch-site: same-site',
                            'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                        ),
                    ));

 

                    echo '<br>NEW Sign:' . $sign . '<br>';

                    $response = curl_exec($this->ch);

					//response code
					$response_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

					echo '<br>Response code: '.$response_code;

					//validating reply, check if mtopjsonp2 is in the reply
					if (!stristr($response, 'mtopjsonp2')) {
						echo '<br>Could not get a valid reply ' . $response;
						return false;
					}

                    //if continas punish?recaptcha=1 then ask for using proxies 
                    if(stristr($response , 'punish?recaptcha=1')){
                        echo '<br><span style="color:red">Recaptcha detected, please use private proxies on the plugin settings page and enable using them on the campaign</span>';
                        return false;
                    }

					//validating reply, i.e e,"itemId"
					if (!stristr($response, 'PRODUCT_TITLE')) {
						echo '<br>Could not get a positive reply ' . $response;
						return false;
					}

					//nice now we have a valid reply mtopjsonp2( JSON )
					//extract the json
					//remove mtopjsonp2( from the start
					$response = str_replace('mtopjsonp2(', '', $response);

					//remove the last char )
					$response = substr($response, 0, -1);

					//decode the json
					$response = json_decode($response);

					//validating the json
					if (!is_object($response)) {
						echo '<br>Could not decode the json ' . $response;
						return false;
					}

                    //title
                    $title = $response->data->result->PRODUCT_TITLE->text;
                    $temp['item_title'] = $title;

                    //rating PC_RATING->rating
                    $temp['item_rating'] = isset($response->data->result->PC_RATING->rating) ? $response->data->result->PC_RATING->rating : '';

                    //TradeCount PC_RATING->otherText
                    $temp['item_orders'] = isset($response->data->result->PC_RATING->otherText) ? $response->data->result->PC_RATING->otherText : '';

                    //SKU->selectedSkuId
                    $temp['item_sku'] = $response->data->result->SKU->selectedSkuId;

                    //item_price_current
                    $temp['item_price_current'] = $response->data->result->PRICE->skuIdStrPriceInfoMap->{$temp['item_sku']}->salePriceString;


                    //item_price_original default to current price
                    $temp['item_price_original'] = $temp['item_price_current'];

                    //if set $response->data->result->PRICE->skuIdStrPriceInfoMap->{$temp['item_sku']}->originalPrice;
                    if (isset($response->data->result->PRICE->skuIdStrPriceInfoMap->{$temp['item_sku']}->originalPrice)) {
                        $temp['item_price_original'] = $response->data->result->PRICE->skuIdStrPriceInfoMap->{$temp['item_sku']}->originalPrice->formatedAmount;
                    }


                     
                    //images list HEADER_IMAGE_PC->imagePathList 
                    $temp['item_images'] = implode (',', $response->data->result->HEADER_IMAGE_PC->imagePathList);

                    //ship from SHIPPING->deliveryLayoutInfo[0]->bizData->shipFrom
                    $temp['item_ship_from'] = $response->data->result->SHIPPING->deliveryLayoutInfo[0]->bizData->shipFrom;

                    //deliveryDayMax
                    $temp['item_delivery_time'] = $response->data->result->SHIPPING->deliveryLayoutInfo[0]->bizData->deliveryDayMax;

                    // shippingFee
                    $temp['item_ship_cost'] = $response->data->result->SHIPPING->deliveryLayoutInfo[0]->bizData->shippingFee;

                    // WISHLIST->wishItemCount
                    $temp['item_wish_count'] = $response->data->result->WISHLIST->wishItemCount;

                    //descriptionUrl DESC->pcDescUrl
                    $temp['item_description_url'] = $response->data->result->DESC->pcDescUrl;

                    // report link
                    echo '<br>Found Link:' . $temp['item_url'];

                    // if cache not active let's delete the cached videos and reset indexes
                    if (!in_array('OPT_AE_CACHE', $camp_opt)) {
                        echo '<br>Cache disabled claring cache ...';
                        $query = "delete from {$this->wp_prefix}automatic_general where item_type='ae_{$camp->camp_id}_$keyword' ";
                        $this->db->query($query);

                        // reset index
                        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
                        $this->db->query($query);

                    }

                    // imgs html
                    if (in_array('OPT_AM_FULL_IMG', $this->camp_opt)) {
                        $cg_am_full_img_t = stripslashes(@$camp_general['cg_ae_full_img_t']);
                    } else {
                        $cg_am_full_img_t = '';
                    }

                    if (wp_automatic_trim($cg_am_full_img_t) == '') {
                        $cg_am_full_img_t = '<img src="[img_src]" class="wp_automatic_gallery" />';
                    }

                    $product_imgs_html = '';

                    $allImages = explode(',', $temp['item_images']);
                    $allImages_html = '';

                    foreach ($allImages as $singleImage) {

                        //first image
                        if (!isset($temp['item_img'])) {
                            $temp['item_img'] = $singleImage;
                        }

                        $singleImageHtml = $cg_am_full_img_t;
                        $singleImageHtml = wp_automatic_str_replace('[img_src]', $singleImage, $singleImageHtml);
                        $allImages_html .= $singleImageHtml;
                    }

                    $temp['item_imgs_html'] = $allImages_html;

                    // item images ini
                    $temp['item_image_html'] = '<img src="' . $temp['item_img'] . '" />';

                    //get description content from descriptionUrl
                    if (wp_automatic_trim($temp['item_description_url']) != '') {
                        echo '<br>Finding item description from description URL:' . $temp['item_description_url'];

                        //curl get
                        $x = 'error';
                        $url = $temp['item_description_url'];
                        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
                        $exec = curl_exec($this->ch);
                        $x = curl_error($this->ch);

                        $temp['item_description'] = $exec;

                    }

                    $temp['item_price_numeric'] = $this->get_numberic_price($temp['item_price_current']);
                    $temp['item_price_original_numeric'] = $this->get_numberic_price($temp['item_price_original']);

                    //generating affiliate link
                    echo '<br>Generating affiliate link... ';
                    $temp['item_affiliate_url'] = $temp['item_url']; // ini

                    //admitad affilite link wp_automatic_ali_admitad
                    $wp_automatic_ali_admitad = trim(get_option('wp_automatic_ali_admitad', ''));

                    //if empty
                    if ($wp_automatic_ali_admitad == '') {

                        //Orange warning
                        echo '<br><br><span style="color:red;">WARNING! Admitad affiliate link is not set, please set it from the settings page and add it to make affiliate commissions.</span><br>';

                    } else {

                        //if admitad link does not end with / add it
                        if (!preg_match('!/$!', $wp_automatic_ali_admitad)) {
                            $wp_automatic_ali_admitad = $wp_automatic_ali_admitad . '/';
                        }

                        //build affiliate link, example https://wextap.com/g/1e8d11449444df579b1316525dc3e8/?ulp=https%3A%2F%2Fwww.aliexpress.com%2Fitem%2F1005006161296694.html
                        $affilite_link = $wp_automatic_ali_admitad . '?ulp=' . urlencode($temp['item_url']);

                        //set
                        $temp['item_affiliate_url'] = $affilite_link;

                        echo '<br>Affiliate link generated: ' . $temp['item_affiliate_url'];

                    }

                    /*
                    echo '<pre>';
                    print_r($temp);

                     */

                    return $temp;
                } else {
                    echo '<br>No links found for this keyword';
                }
            } // if trim
        } // foreach keyword
    }
    public function aliexpress_fetch_items($keyword, $camp)
    {

        // report
        echo "<br>So I should now get some items from AliExpress for keyword :" . $keyword;

        // Amazon cookie
        $this->load_cookie('aliexpress');

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

            if (!in_array('OPT_AE_CACHE', $camp_opt)) {
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

            if (!in_array('OPT_AE_CACHE', $camp_opt)) {
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

        $aliexpress_encoded_keyword = urlencode(wp_automatic_trim($keyword));

        if (in_array('OPT_ALIEXPRESS_CUSTOM', $camp_opt)) {

            //custom search link
            $cg_ae_custom_urls = $camp_general['cg_ae_custom_urls'];

            $aliexpress_url = wp_automatic_str_replace( '[keyword]' , urlencode(wp_automatic_trim($keyword)) , $cg_ae_custom_urls );

            $aliexpress_url_parts = explode('aliexpress.com', $aliexpress_url);
            $aliexpress_domain = wp_automatic_trim($aliexpress_url_parts[0]) . 'aliexpress.com';

        } else {
            // prepare keyword https://www.aliexpress.com/af/search.html?SearchText=red+duck
            //$aliexpress_url = 'https://www.aliexpress.com/af/search.html?SearchText=' . urlencode(wp_automatic_trim($keyword)) ;

            // new search URL https://www.aliexpress.com/w/wholesale-pizza-box.html?catId=0&initiative_id=SB_20230718094010&SearchText=pizza+box&spm=a2g0o.productlist.1000002.0
            // short fom https://www.aliexpress.com/w/wholesale-pizza-box.html?SearchText=pizza+box
            $aliexpress_domain = 'https://www.aliexpress.com';

            //create an initiative_id like SB_20230718094010
            $initiative_id = 'SB_' . date('YmdHis');

            //$aliexpress_url = 'https://www.aliexpress.com/w/wholesale-' . $aliexpress_encoded_keyword . '.html?SearchText=' . $aliexpress_encoded_keyword;
            $aliexpress_url = 'https://www.aliexpress.com/w/wholesale-' . $aliexpress_encoded_keyword . '.html?SearchText=' . $aliexpress_encoded_keyword . '&catId=0&initiative_id=' . $initiative_id . '&spm=a2g0o.productlist.1000002.0&trafficChannel=main&g=y';

        }

        //custom country domain name
        if (in_array('OPT_ALIEXPRESS_DOMAIN', $camp_opt)) {
            $cg_ae_custom_domain = wp_automatic_trim($camp_general['cg_ae_custom_domain']);

            if (stristr($cg_ae_custom_domain, 'aliexpress.com')) {
                echo '<br>Custom country/domain is requested: ' . $cg_ae_custom_domain;
                $cg_ae_custom_domain = preg_replace('!/$!', '', $cg_ae_custom_domain);
                $aliexpress_url = wp_automatic_str_replace('https://www.aliexpress.com', $cg_ae_custom_domain, $aliexpress_url);
                $aliexpress_domain = $cg_ae_custom_domain;
            }

        } else {

            //set to US by default
            $cookie_value = $this->cookie_content('aliexpress');

            if (!stristr($cookie_value, 'site=glo')) {
                echo ' Found global site not set, let us set it ';
                $this->cookie_delete('aliexpress');

                curl_setopt($this->ch, CURLOPT_COOKIE, 'aep_usuc_f=site=glo');

            } else {
                echo ' Site is already set to Glo';
            }

        }

        //pagination
        if ($start != 1) {
            $aliexpress_url .= '&page=' . $start;
        }

        //if the keyword is a product id on the form 1005006869409001
        //{"productId":"1005006869409001"}

        if (preg_match('!^\d+$!', $keyword)) {
            echo '<br>Keyword is a product id, we will use the product ID in the URL...';

            //exec {"productId":"1005006869409001"}
            $exec = '{"productId":"' . trim($keyword) . '"}';

            //deactivate permanently
            $this->deactivate_key($camp->camp_id, $keyword, 0);

        } elseif (in_array('OPT_TT_INFINITE', $camp_opt)) {

            echo '<br>Loading the items from the added HTML...';
            $exec = $camp_general['cg_tt_html'];

        } else {

            echo '<br>Loading:' . $aliexpress_url;

            $x = 'error';
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($aliexpress_url));
            $exec = curl_exec($this->ch);
            $x = curl_error($this->ch);

            curl_setopt_array($this->ch, array(
                CURLOPT_URL => $aliexpress_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'authority: www.aliexpress.com',
                    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'accept-language: en-US,en;q=0.9,ar;q=0.8',
                    'referer: ' . $aliexpress_url,
                    'sec-fetch-dest: document',
                    'sec-fetch-mode: navigate',
                    'sec-fetch-site: same-origin',
                    'sec-fetch-user: ?1',
                    'upgrade-insecure-requests: 1',
                    'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
                ),
            ));

            $exec = curl_exec($this->ch);

        }

        $items = array();

        // "productId":"1005003141253710"
        if (strpos($exec, '"productId":"')) {

            //extract video links
            preg_match_all('{"productId":"(\d*)"}s', $exec, $found_items_matches);

            $items_ids = $found_items_matches[1];

            // reverse
            if (in_array('OPT_TT_REVERSE', $camp_opt)) {
                echo '<br>Reversing order';

                $items_ids = array_reverse($items_id);
            }

            echo '<ol>';

            // loop items
            $i = 0;
            foreach ($items_ids as $item) {

                // clean itm
                unset($itm);

                // build item
                $itm['item_id'] = $item;
                $itm['item_url'] = "{$aliexpress_domain}/item/" . $item . ".html";

                $data = base64_encode(serialize($itm));

                $i++;

                echo '<li>' . $itm['item_url'] . '</li>';

                if (!$this->is_duplicate($itm['item_url'])) {
                    $query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (    '{$itm['item_id']}', '0', '$data' ,'ae_{$camp->camp_id}_$keyword')  ";
                    $this->db->query($query);
                } else {
                    echo ' <- duplicated <a href="' . get_edit_post_link($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';
                }

                echo '</li>';
            }

            echo '</ol>';

            echo '<br>Total ' . $i . ' products found & cached';

            // check if nothing found so deactivate
            if ($i == 0) {
                echo '<br>No new items got found ';
                echo '<br>Keyword have no more items deactivating...';
                $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
                $this->db->query($query);

                if (!in_array('OPT_NO_DEACTIVATE', $camp_opt)) {
                    $this->deactivate_key($camp->camp_id, $keyword);
                }

            } else {

                //we got products

            }

        } else {

            // no valid reply
            echo '<br>No Valid reply for AliExpress search ';

            echo '<br>Reply: ' . $exec;

            echo '<br>No new items got found ';
            echo '<br>Keyword have no more items deactivating...';
            $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
            $this->db->query($query);

            if (!in_array('OPT_NO_DEACTIVATE', $camp_opt)) {
                $this->deactivate_key($camp->camp_id, $keyword);
            }

        }
    }

    public function get_numberic_price($text_price)
    {

        $item_price_current = $text_price;
        $item_price_current_pts = explode('-', $item_price_current);
        $item_price_current = $item_price_current_pts[0];

        preg_match('![\d\.\,]+!', $item_price_current, $price_matchs);
        if (isset($price_matchs[0])) {
            return $price_matchs[0];
        } else {
            return '';
        }

    }

    /**
     * Get _m_h5_tk, _m_h5_tk_enc from cookie
     */
    public function get_m_h5_tk()
    {

        //curl request to https://acs.youku.com/h5/mtop.com.youku.aplatform.weakget/1.0/?jsv=2.5.1&appKey=24679788

        //curl ini
        $x = 'error';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://acs.youku.com/h5/mtop.com.youku.aplatform.weakget/1.0/?jsv=2.5.1&appKey=12574478');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //return header too
        curl_setopt($ch, CURLOPT_HEADER, 1);

        //execute
        $exec = curl_exec($ch);


        //error
        $x = curl_error($ch);

        //close
        curl_close($ch);

        //extract _m_h5_tk, _m_h5_tk_enc
        preg_match('!_m_h5_tk=(.*?);!', $exec, $m_h5_tk_matches);

        preg_match('!_m_h5_tk_enc=(.*?);!', $exec, $m_h5_tk_enc_matches);

        //if not found, throw error
        if (!isset($m_h5_tk_matches[1]) || trim($m_h5_tk_matches[1]) == '' || !isset($m_h5_tk_enc_matches[1]) || trim($m_h5_tk_enc_matches[1]) == '') {
            throw new Exception('Could not get _m_h5_tk, _m_h5_tk_enc');
        }

        //get cna cookie by request to https://log.mmstat.com/eg.js
        //set url to https://log.mmstat.com/eg.js
        $url = 'https://log.mmstat.com/eg.js';
        curl_setopt($ch, CURLOPT_URL, $url);

        //execute
        $exec = curl_exec($ch);


        //extract cna cna=eCJOH40wN2QCAZzNrh/9Eyt1
        preg_match('!cna=(.*?);!', $exec, $cna_matches);

        //if not found, throw error
        if (!isset($cna_matches[1]) || trim($cna_matches[1]) == '') {
            throw new Exception('Could not get cna');
        }

        
        //return
        return array($m_h5_tk_matches[1], $m_h5_tk_enc_matches[1] , $cna_matches[1]);

    }

}
