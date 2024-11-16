<?php

// Main Class
require_once 'core.php';

class WpAutomaticPlaces extends wp_automatic
{

    //private api key
    private $api_key = '';
    private $image_wdith = 700;

/**function places_get_post: return valid places pin to post
 * @param unknown $camp
 */
    public function places_get_post($camp)
    {

        //ini keywords
        $camp_opt = $this->camp_opt;
        $keywords = explode(',', $camp->camp_keywords);
        $camp_general = $this->camp_general;

        //get the api key from the settings page wp_automatic_google_places_key
        $api_key = get_option('wp_automatic_google_places_key', '');

        //set the api key
        $this->api_key = $api_key;

        //set the image width
        $this->image_wdith = is_numeric($camp_general['cg_pl_max_img_width']) ? $camp_general['cg_pl_max_img_width'] : 700;

        //if empty, display a red error message aksing the user to visit the plugin settings page and enter the api key
        if (trim($api_key) == '') {
            echo '<br><br><span style="color:red">Please visit the plugin settings page and enter your Google Places API key</span>';
            return false;
        }

        //looping keywords
        foreach ($keywords as $keyword) {

            $keyword = wp_automatic_trim($keyword);

            //report processing keyword keyword
            echo '<br>Processing keyword: ' . $keyword;

            //update last keyword
            update_post_meta($camp->camp_id, 'last_keyword', wp_automatic_trim($keyword));

            //when valid keyword
            if (wp_automatic_trim($keyword) != '') {

                //record current used keyword
                $this->used_keyword = $keyword;

                // getting links from the db for that keyword
                $query = "select * from {$this->wp_prefix}automatic_general where item_type=  'pl_{$camp->camp_id}_$keyword' ";
                $res = $this->db->get_results($query);

                // when no links lets get new links
                if (count($res) == 0) {

                    //clean any old cache for this keyword
                    $query_delete = "delete from {$this->wp_prefix}automatic_general where item_type='pl_{$camp->camp_id}_$keyword' ";
                    $this->db->query($query_delete);

                    //get new links
                    $this->places_fetch_items($keyword, $camp);

                    // getting links from the db for that keyword
                    $res = $this->db->get_results($query);
                }

                //check if already duplicated
                //deleting duplicated items
                $res_count = count($res);
                for ($i = 0; $i < $res_count; $i++) {

                    $t_row = $res[$i];

                    $t_data = unserialize(base64_decode($t_row->item_data));

                    $t_link_url = $t_data['item_url'];

                    if ($this->is_duplicate($t_link_url)) {

                        //duplicated item let's delete
                        unset($res[$i]);

                        echo '<br>Item (' . $t_data['item_name'] . ') found cached but duplicated <a href="' . get_permalink($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';

                        //delete the item
                        $query = "delete from {$this->wp_prefix}automatic_general where id= {$t_row->id} ";
                        $this->db->query($query);

                    } else {
                        break;
                    }

                }

                // check again if valid links found for that keyword otherwise skip it
                if (count($res) > 0) {

                    // lets process that link
                    $ret = $res[$i];

                    $temp = unserialize(base64_decode($ret->item_data));

                    //report link
                    echo '<br>Found Link:' . $temp['item_url'];

                    // update the link status to 1
                    $query = "delete from {$this->wp_prefix}automatic_general where id={$ret->id}";
                    $this->db->query($query);

                    // if cache not active let's delete the cached videos and reset indexes
                    if (!in_array('OPT_PL_CACHE', $camp_opt)) {
                        echo '<br>Cache disabled claring cache ...';
                        $query = "delete from {$this->wp_prefix}automatic_general where item_type='pl_{$camp->camp_id}_$keyword' ";
                        $this->db->query($query);

                        // reset index
                        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start =1 where keyword_camp={$camp->camp_id}";
                        $this->db->query($query);
                    }

                    //item map url $temp ['item_map'] = '<iframe src = "https://maps.google.com/maps?q=' . $temp ['item_location_latitude'] . ',' . $temp ['item_location_longitude'] . '&hl=es;z=14&amp;output=embed"></iframe>';
                    $temp['item_map_iframe'] = '<iframe src = "https://maps.google.com/maps?q=' . $temp['item_lat'] . ',' . $temp['item_lng'] . '&hl=es;z=14&amp;output=embed"></iframe>';

                    //item map url
                    $temp['item_map_url'] = 'https://maps.google.com/maps?q=' . $temp['item_lat'] . ',' . $temp['item_lng'] . '&hl=es;z=14&amp;output=embed';

                    //getting more item details by place id
                    echo '<br>Getting more details for this place ...';

                    //languagecode cg_pl_lang
                    $languageCode = isset($camp_general['cg_pl_lang']) ? $camp_general['cg_pl_lang'] : '';

                    $details = $this->place_get_details($temp['item_id'] , $languageCode);

                    //validating reply
                    if (is_array($details) && count($details) > 0) {

                        //image
                        if (isset($details['photos'][0]['photo_reference'])) {
                            $temp['item_image'] = $details['photos'][0]['photo_reference'];

                            //correct image url
                            $temp['item_image'] = $this->get_image_url($temp['item_image']);

                        }

                        //dine_in
                        $temp['item_dine_in'] = isset($details['dine_in']) ? $details['dine_in'] : '';

                        //formatted_phone_number
                        $temp['item_formatted_phone_number'] = isset($details['formatted_phone_number']) ? $details['formatted_phone_number'] : '';

                        //international_phone_number
                        $temp['item_international_phone_number'] = isset($details['international_phone_number']) ? $details['international_phone_number'] : '';

                        //serves_breakfast
                        $temp['item_serves_breakfast'] = isset($details['serves_breakfast']) ? $details['serves_breakfast'] : '';

                        //takeout
                        $temp['item_takeout'] = isset($details['takeout']) ? $details['takeout'] : '';

                        //website
                        $temp['item_website'] = isset($details['website']) ? $details['website'] : '';

                        //weekday_text
                        $temp['item_weekday_text'] = isset($details['current_opening_hours']['weekday_text']) ? $details['current_opening_hours']['weekday_text'] : '';

						//if is an array, implode with a break
						if(is_array($temp['item_weekday_text'])){
							$temp['item_weekday_text'] = implode('<br>',$temp['item_weekday_text']);
						}

                        //photos
                        if (isset($details['photos']) && count($details['photos']) > 0) {

                            //loop photos
                            $photos = array();
                            foreach ($details['photos'] as $photo) {

                                //convert to url
                                $photo['photo_reference'] = $this->get_image_url($photo['photo_reference']);

                                $photos[] = $photo['photo_reference'];
                                $photos_html[] = '<img src="' . $photo['photo_reference'] . '">';

                                //implode photos
                                $temp['item_photos'] = implode(',', $photos);
                                $temp['item_photos_html'] = implode('<br>', $photos_html);
                            }

                        } else {
                            $temp['item_photos'] = '';
                            $temp['item_photos_html'] = '';
                        }

                        //reviews array
                        if (isset($details['reviews']) && count($details['reviews']) > 0) {
                            $temp['item_reviews_arr'] = $details['reviews'];
                        }

                    }

                    //if OPT_PL_REVIEWS is set, set the comments_to_post to a comments array
                    //example comment array is in the format
                    //array(
                    //array(
                    //'comment_author' => 'John Doe',
                    //'comment_content' => 'Comment content',
                    //'comment_date' => '2019-01-01 12:00:00',
                    //'comment_rating' => '5',
                    //'comment_author_url' => 'https://example.com',
                    //'comment_author_image' => 'https://example.com/image.jpg',
                    //),

                    if (in_array('OPT_PL_REVIEWS', $camp_opt)) {

                        //comments array
                        $comments = array();

                        //loop reviews
                        foreach ($temp['item_reviews_arr'] as $review) {

                            //comment
                            $comment = array();

                            //comment author
                            $comment['comment_author'] = $review['author_name'];

                            //comment content
                            $comment['comment_content'] = $review['text'];

                            //comment date
                            $comment['comment_date'] = date('Y-m-d H:i:s', $review['time']);

                            //comment rating
                            $comment['comment_rating'] = $review['rating'];

                            //comment author url
                            $comment['comment_author_url'] = $review['author_url'];

                            //comment author image [does not load on sites except Google]
                            //$comment['comment_author_image'] = $review['profile_photo_url'];

                            //push comment
                            $comments[] = $comment;

                        }

                        //set comments to post
                        $temp['comments_to_post'] = $comments;

                    }

                    //build reviews text
                    $temp['item_reviews'] = '';

                    if (isset($temp['item_reviews_arr']) && count($temp['item_reviews_arr']) > 0) {

                        //loop reviews
                        foreach ($temp['item_reviews_arr'] as $review) {

                            //build review text
                            $temp['item_reviews'] .= '<div class="review">';
                            $temp['item_reviews'] .= '<div class="review_author">' . $review['author_name'] . '</div>';
                            $temp['item_reviews'] .= '<div class="review_text">' . $review['text'] . '</div>';
                            $temp['item_reviews'] .= '<div class="review_rating">' . $review['rating'] . '</div>';
                            $temp['item_reviews'] .= '</div>';

                        }

                    }
 
                    return $temp;

                } else {

                    echo '<br>No links found for this keyword';
                }
            } // if trim
        } // foreach keyword

    }

/**
 * function places_fetch_items: get new items from places for specific keyword
 * @param unknown $keyword
 * @param unknown $camp
 */
    public function places_fetch_items($keyword, $camp)
    {

        //report
        echo "<br>So I should now get some places from Google maps for keyword :" . $keyword;

        // ini options
        $camp_opt = $this->camp_opt;
        $camp_general = $this->camp_general;

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

            if (!in_array('OPT_PL_CACHE', $camp_opt)) {
                $start = 1;
                echo '<br>Cache disabled resetting index to 1';
            } else {

                //check if it is reactivated or still deactivated
                if ($this->is_deactivated($camp->camp_id, $keyword)) {
                    $start = 1;
                } else {
                    //still deactivated
                    return false;
                }

            }

        } elseif (!in_array('OPT_PL_CACHE', $camp_opt)) {
            $start = 1;
            echo '<br>Cache disabled resetting index to 1';

            //delete next page token
            delete_post_meta($camp->camp_id, 'wp_places_bookmark_' . md5($keyword));

        }

        echo ' index:' . $start;

        // update start index to start+1
        $nextstart = $start + 1;
        $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = $nextstart where keyword_id=$kid ";
        $this->db->query($query);

        //get bookmark
        $next_page_token = get_post_meta($camp->camp_id, 'wp_places_bookmark_' . md5($keyword), 1);

        //require class googleMaps.php
        require_once 'inc/class.googleMaps.php';

        //api key wp_automatic_google_places_key
        $api_key = get_option('wp_automatic_google_places_key', '');

        //language code cg_pl_lang
        $languageCode = isset($camp_general['cg_pl_lang']) ? $camp_general['cg_pl_lang'] : '';

        //init class
        $googleMaps = new GoogleMapsPlaces($api_key, $this->ch);

        //get places in try catch
        try {
            $places = $googleMaps->getPlacesList($keyword, $next_page_token, $languageCode);
        } catch (Exception $e) {
            echo '<br>Exception: ' . $e->getMessage();
            return false;
        }

        //validating reply
        if (is_array($places) && count($places) > 0) {
            //valid reply

            //set next page token from the class attribute next_page_token
            $new_bookmark = $googleMaps->next_page_token;

            echo '<ol>';

            //loop items
            $i = 0;
            foreach ($places as $item) {

                // print_r($item);

                //item_url from place id
                $itm['item_url'] = 'https://www.google.com/maps/place/?q=place_id:' . $item['place_id'];

                //formatted_address
                $itm['item_formatted_address'] = $item['formatted_address'];

                //icon
                $itm['item_icon'] = $item['icon'];

                //name
                $itm['item_title'] = $itm['item_name'] = $item['name'];

                //place_id
                $itm['item_id'] = $item['place_id'];

                //price_level
                $itm['item_price_level'] = isset($item['price_level']) ? $item['price_level'] : '';

                //rating
                $itm['item_rating'] = $item['rating'];

                //user_ratings_total
                $itm['item_user_ratings_total'] = $item['user_ratings_total'];

                //business_status
                $itm['item_business_status'] = $item['business_status'];

                //lat
                $itm['item_lat'] = $item['geometry']['location']['lat'];

                //lng
                $itm['item_lng'] = $item['geometry']['location']['lng'];

                //image
                if (isset($item['photos'][0]['photo_reference'])) {
                    $itm['item_image'] = $item['photos'][0]['photo_reference'];

                    //correct image url
                    $itm['item_image'] = $this->get_image_url($itm['item_image']);

                } else {
                    $itm['item_image'] = '';

                }

                //compound_code
                $itm['item_compound_code'] = isset($item['plus_code']['compound_code']) ? $item['plus_code']['compound_code'] : '';

                //global_code
                $itm['item_global_code'] = isset($item['plus_code']['global_code']) ? $item['plus_code']['global_code'] : '';

                //types
                $itm['item_types'] = implode(',', $item['types']);

                //echo item url
                echo '<li><a href="' . $itm['item_url'] . '" target="_blank">' . $itm['item_name'] . '</a>';

                //skip items here if needed

                $data = base64_encode(serialize($itm));

                if ($this->is_execluded($camp->camp_id, $itm['item_url'])) {
                    echo '<-- Execluded';
                    continue;
                }

                if (!$this->is_duplicate($itm['item_url'])) {
                    $query = "INSERT INTO {$this->wp_prefix}automatic_general ( item_id , item_status , item_data ,item_type) values (    '{$itm['item_id']}', '0', '$data' ,'pl_{$camp->camp_id}_$keyword')  ";
                    $this->db->query($query);
                } else {
                    echo ' <- duplicated <a href="' . get_edit_post_link($this->duplicate_id) . '">#' . $this->duplicate_id . '</a>';
                }

                echo '</li>';
                $i++;

            }

            echo '</ol>';

            echo '<br>Total ' . $i . ' items found & cached';

            //check if nothing found so deactivate
            if ($i == 0) {
                echo '<br>No new items found ';
                echo '<br>Keyword have no more items deactivating...';
                $query = "update {$this->wp_prefix}automatic_keywords set keyword_start = -1 where keyword_id=$kid ";
                $this->db->query($query);

                if (!in_array('OPT_NO_DEACTIVATE', $camp_opt)) {
                    $this->deactivate_key($camp->camp_id, $keyword);
                }

                //delete bookmark value
                delete_post_meta($camp->camp_id, 'wp_places_bookmark' . md5($keyword));
            } else {

                echo '<br>Updating bookmark:' . $new_bookmark;

                //save bookmark
                update_post_meta($camp->camp_id, 'wp_places_bookmark_' . md5($keyword), $new_bookmark);

            }

        } else {

            //no valid reply
            echo '<br>Invalid reply, no places found for this keyword';

        }

    }

  
    /**
     * function place_get_details: get details for place
     * @param unknown $place_id
     * @return string
     */
    public function place_get_details($place_id, $languageCode = '')
    {

        //require class googleMaps.php
        require_once 'inc/class.googleMaps.php';

        //api key wp_automatic_google_places_key
        $api_key = get_option('wp_automatic_google_places_key', '');

        //init class
        $googleMaps = new GoogleMapsPlaces($api_key, $this->ch);

        //get place details
        $details = $googleMaps->getPlaceDetails($place_id, $languageCode);

        //validating reply
        if (is_array($details) && count($details) > 0) {

            return $details;

        } else {
            return '';
        }
    }

    //function get image url by reference 
    //recieves the reference and built then return image URL 
    //url is on format 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=' . $temp['item_image'] . '&key=' . $api_key;
    public function get_image_url($reference)
    {

        //return image url
        return 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=' . $this->image_wdith . '&photoreference=' . $reference . '&key=' . $this->api_key;

    }

}