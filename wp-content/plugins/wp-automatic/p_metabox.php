<?php

$dir = WP_PLUGIN_URL.'/wp-automatic/';
require_once dirname(__FILE__) . '/inc/amazon.searchindex.php';

//globals
global $post;
global  $wpdb;
global $camp_general;
global $post_id;
global $camp_options;
global $post_types;

// cats meta
global $camp_post_category;

// filters metabox
global $camp_post_exact;
global $camp_post_execlude;

// translate metabox
global $camp_translate_from;
global $camp_translate_to;
global $camp_translate_to_2;

// basic metabox
global $camp_post_title;
global  $camp_post_content;
global $camp_post_author ;
global  $camp_post_status;
global $camp_post_type;
global $camp_post_custom_k;
global $camp_post_custom_v;

// mods metabox
global $camp_replace_link;

$prefix=$wpdb->prefix;
$post_id=$post->ID;

$wp_automatic_options = get_option('wp_automatic_options',array() );
$wp_amazonpin_abk = get_option('wp_amazonpin_abk','');

//Allowed tags
$allowed_tags=array();
$ad1=  array('[ad_2]','will be replaced with the Second ad code added in the settings page');
$ad2=  array('[ad_1]','will be replaced with the first ad code added in the settings page');
$source=array( '[source_link]' , 'will be replaced with the original article source link') ;
$allowed_tags['Articles']=  array(array('[keyword]','Keyword used to get current item'), array('[matched_content]' ,' will be replaced with the original fetched article content'),array('[original_title]','article title'), array('[author_name]','author name') , array('[author_link]','author link')  ,$ad1,$ad2,$source );
$allowed_tags['gpt3'] = array(array('[keyword]','Keyword used to get current item'), array('[matched_content]' ,' will be replaced with the original fetched article content'),array('[original_title]','article title') ,$ad1,$ad2,$source );
$allowed_tags['ArticlesBase']=  array(array('[keyword]','Keyword used to get current item'), array('[matched_content]' ,' will be replaced with the original fetched article content'),array('[original_title]','article title'), array('[author_name]','author name') , array('[author_link]','author link')  ,$ad1,$ad2,$source );
$allowed_tags['Feeds']= $allowed_tags['Multi'] =  array( array('[matched_content]' ,' will be replaced with the original fetched article content') ,array('[original_title]','title of the post') , array('[author]','Author name if displayed at the feed or extracted using the extract original author option') ,array('[publish_date]','the date when the feed item published'),array('[feed_name]','Title of the feed extracted from the feed content') ,array('[enclosure_link]','Returns the enclosure link of the item in the feed, this could be an image/audio or video link'),array('[og_img]','Only if you used the option to set a featured image from og:img this tag will return the og:image src url'),array('[rule_{ruleNumber}]','Returns the content from the set rule, for example, if you have set two rules to extract the content using REGEX. [rule_1] returns content from the first rule and [rule_2] returns the second rule content and so on...'),array('[rule_{ruleNumber}_plain]','Returns the content from the set rule in plain text after removing all html tags, for example, if you have set two rules to extract the content using REGEX. [rule_1_plain] returns content from the first rule and [rule_2_plain] returns the second rule content and so on... all in plain text'),$ad1,$ad2,$source , array('[redirect_url]','If the source post URL redirects to another URL, this tag will contain the final redirection URL'), array('[source_domain]','Domain name of the source URL') , array('[source_url_encoded]','Encoded source URL to be added as a parameter on a link') , array('[source_url_decoded]','Decoded source URL, usefull for non latin links like arabic links') , array( '[cats]' , 'Comma spearated list of the post categories as shown on the RSS feed if exist' ) , array( '[tags]' , 'Comma spearated list of the post tags if the option to extract them was enabled' ) );
$allowed_tags['Amazon']=array(array('[keyword]','Keyword used to get current item'), array('[product_img]',' will be replaced with the product image src'), array('[product_imgs_html]','Html of all the product images'), array('[product_imgs]','Comma separated list of image srcs of the product'),array('[product_link]',' will be replaced with the product amazon page link'),array('[chart_url]','get replaced by the add to chart link of the product at amazon'),array('[product_list_price]','Product list price without discount') , array('[list_price_numeric]','Numeric value of the list price without currency') ,array('[price_with_discount]','If on sale, the original and new price are displayed.If not, the list price is displayed this tag is rendered on display to represent current price'),array('[price_with_discount_fixed]','If on sale, the original and new price are displayed.If not, the list price is displayed this tag value get generated when the product firstly added. It does not get updated with new price'),array('[product_price]',' Will be replaced with the product price like $32.10 with currency attached if possible Note  some products will not return a price'),array('[price_numeric]','price in a numeric format like 32.10 this price is suitable for woocommerce product price custom field named _price'), array('[price_currency]','The price currency i.e $ or € etc.'), array('[product_desc]',' will be replaced with the original fetched product descreption') , array('[item_details]','Technical product details/table ')  , array('[product_asin]','Product ASIN'), array('[product_upc]','Product Universal Product Code') , array('[product_brand]','Brand of the item if exists'), array('[product_author]','Author of the item if exists') , array('[product_summary]','AI generated text of the reviews summary. Customers say') , array('[product_reviews_text]','List of product reviews') ,array('[price_update_date]','Date and time when the price was updated by the plugin'),array('[item_rating]', 'Rating of the product out of 5') ,array('[item_cats]', 'Categories of the product') ,array('[item_manufacture_description]','Date and time when the price was updated by the plugin') ,$ad1,$ad2,$source);
$allowed_tags['Clickbank']=array( array('[keyword]','Keyword used to get current item'), array('[product_img]',' will be replaced with the product image html') , array('[product_img_src]',' will be replaced with the product image src') , array('[product_link]',' will be replaced with the product sales page with your affiliate link') , array('[product_original_link]',' will be replaced with product sales page link without affiliation')   ,  array('[product_desc]',' will be replaced with the original fetched product descreption'),$ad1,$ad2,$source);
$allowed_tags['Youtube']=array(array('[keyword]','Keyword used to get current item'), array('[vid_title]','Title of the video') ,array('[vid_player]',' will be replaced with the posted video player embed code') ,array('[vid_id]',' will be replaced with the youtube original vid id'), array('[source_link]',' will be replaced with the youtube original vid link') , array('[vid_img]',' replaced with video thumb img src') , array('[vid_views]',' will be replaced with video views count'),  array('[vid_desc]',' will be replaced with the original fetched video descreption') , array('[vid_time]','Video publish timestamp '), array('[vid_date]','Date when the video was published'), array('[vid_duration]','Video duration ') , array('[vid_duration_in_seconds]','Video duration calculated in seconds ') , array('[vid_author]','Video author channel id') , array('[vid_author_title]','Video author title') , array('[channel_country]','Country code of the channel origin'), array('[channel_country_name]', 'Channel country name') , array('[vid_likes]','Number of video likes'), array('[vid_tags]','Video tags comma separated only if the option to post YT tags as tags is active') ,array('[vid_download_url]' , 'Download URL of the video from y2mate.com'), array('[transcript]','Transcript text as paragraphs of the video if available') , array('[transcript_raw]','Transcript as copied from youtube time and sentences like 00:01 hello ') , $ad1,$ad2,$source );

$allowed_tags['Vimeo']=array(array('[keyword]','Keyword used to get current item'),array('[vid_title]','title of the video'),  array('[vid_description]',' will be replaced with the original fetched video descreption'), array('[vid_embed]',' will be replaced with the posted video player embed code') ,array('[vid_id]',' will be replaced with the original vid id'), array('[vid_url]',' will be replaced with the vimeo original vid link') , array('[vid_img]',' replaced with video thumb img src') , array('[vid_views]',' will be replaced with video views count') , array('[vid_likes]',' will be replaced with stars likes number of the video')  , array('[vid_duration]','Video duration in seconds'), array('[vid_duration_readable]','Video duration in  hh:mm:ss format'), array('[vid_width]', 'width of the video player'), array('[vid_height]', 'Height of the video player')  ,  array('[vid_created_time]','Video creation time '),array('[vid_modified_time]', 'last modification date') , array('[vid_author_name]','Video author name '),array('[vid_author_id]','video author id') , array('[vid_author_link]','video author link'), array('[vid_author_picutre]','video author picture src link'),array('[vid_tags]','Tags of the video comma separated')  , $ad1,$ad2 );
$allowed_tags['Flicker']=array(array('[keyword]','Keyword used to get current item'), array('[img_title]','Image Title'),array('[img_author]','Image author ID'),array('[img_src]','Image Src link'),array('[img_src_s]','small square 75x75') , array('[img_src_q]','large square 150x150') , array('[img_src_t]','thumbnail, 100 on longest side') , array('[img_src_m]','small, 240 on longest side') , array('[img_src_n]','small, 320 on longest side') , array('[img_src_z]','medium 640, 640 on longest side') , array('[img_src_c]','medium 800, 800 on longest side†') , array('[img_src_b]','large, 1024 on longest side ') , array('[img_src_h]','large, 1600 on longest side ') , array('[img_src_k]','large, 2048 on longest side ') , array('[img_src_o]','Original Image uploaded with original size') ,array('[img_link]','Image link on flicker'),array('[img_author_name]','Image author name'),array('[img_description]','Image description'),array('[img_date_posted]','Date when the image posted'),array('[img_date_taken]','Date when the image taken'),array('[img_viewed]','Num of views of the image'),array('[img_tags]','image tags on flicker'),$ad1,$ad2,$source );
$allowed_tags['eBay']=array(array('[keyword]','Keyword used to get current item'),array('[item_id]','added item id on ebay'),array('[item_title]','item title'),array('[item_desc]','item description'),array('[item_images]','html of all item images'),array('[item_img]','item image http source'),array('[item_link]','item link on ebay'),array('[item_bids]','Number of current bids if applicable'),array('[item_price]','current item price'),array('[item_marketing_price]','Price before discount defaults to price'), array('[item_price_with_discount]','Item price before discount deleted then current price. defaults to item price if no discount'),array('[price_with_discount]','Displays price and sale price side by side dynamically from latest values') ,array('[item_bin]','item buy it now price if applicable'),array('[item_end_date]','item when the listing ends'), array('[item_seller_username]' , 'Username of the seller') , array('[item_seller_url]' , 'URL of the seller'), array('[item_location]' , 'Location of the item'), array('[item_ships_to]' , 'Item shipping to location'), array('[item_condition]' , 'Condition of the item, New or Used') , array('[item_condition_desc]' , 'Description of the condition as added from the seller') , array('[item_return_policy]' , 'If seller accepts returns'), array('[item_listing_type]' , 'Type of the listing Auction or buy it now'), array('[item_shipping_start]' , 'If seller starts shipping after recieving the payment') , array('[item_subtitle]' , 'Subtitle of the item')  , array('[item_category]' , 'Name of item category') , array('[item_category]' , 'Name of item category') , array('[item_payment]' , 'Available payment method') , array('[item_postal]' , 'Postal code of the item location')  ,$ad1,$ad2,$source );
$allowed_tags['Pinterest'] = array(array('[pin_id]','Numerical id of the posted pin'),array('[pin_url]','Pin url at pinterest.com'),array('[pin_link]','original page link where the pin image exits *only for pins with link back to the source'),array('[pin_title]','Title of the pin'),array('[pin_description]','Description of the pin'),array('[pin_board_url]','URL of the matching board'),array('[pin_board_name]','Name of the board the pin belons to'),array('[pin_pinner_username]','Username of the pinner'),array('[pin_pinner_full_name]','Full name of the pinner'),array('[pin_pinner_id]','Numeric ID of the pin pinner'),array('[pin_domain]','Domain for the pin if posted from a url'),array('[repin_count]','Number of repins'),array('[pin_img]','Image src link for the pin image'),array('[pin_img_width]','Width of the pin image'),array('[pin_img_height]','Height of the pin image'),$ad1,$ad2);
$allowed_tags['Instagram'] = array( array('[item_title]','Instagram image title'), array('[item_embed]','The embed code of the item'), array('[item_vid_embed]','Item vido embed code') , array('[item_id]','The instagram pic id'),array('[item_url]','Instagram pic url'),array('[item_description]','Instagram pic description'),array('[item_img]','Instagram image src'),array('[item_images]','Item images HTML code'),array('[item_img_width]','Width of the instagram image'),array('[item_img_height]','Height of the image'),array('[item_user_id]','Instagram user id'),array('[item_user_username]','Username of the instagram image poster'),array('[item_user_name]','user name of the image poster'),array('[item_user_profile_pic]','Image src of the user pic'),array('[item_created_date]','date when the item created'),array('[item_likes_count]','Likes count of the item'),array('[item_comments_count]','Comments count of the item'),array('[item_tags]','Comma separated list of tags'),array('[item_location_name]','Location name'),array('[item_location_id]','Location ID'),array('[item_location_url]','Location explore link'), array('[video_view_count]','Views count only if the imported item is a video.') ,$ad1,$ad2,$source );
$allowed_tags['TikTok'] = array(array('[item_id]','Numeric ID of the video'),array('[item_url]','URL of the video'),array('[item_description]','Description of the video'),array('[item_img]','Cover image url of the video'),array('[item_img_width]','Width of the video cover pic'),array('[item_img_height]','height of the video cover pic'),array('[item_user_username]','username of the video uploader'),array('[item_user_name]','Name of the video uploader'),array('[item_tags]','hashtags of the video comma separated'),array('[item_images]',''),array('[item_title]','Title of the video'),array('[item_embed]','Embed code of the video') );
$allowed_tags['Facebook'] = $allowed_tags['Facebookposts'] = $allowed_tags['Facebookfeed'] = $allowed_tags['Facebooktagged']= array( array('[post_id]','Facebook post ID including page id'), array('[post_id_single]','Facebook post ID single numeric value') , array('[post_embed]','Embed code of the post') ,  array('[original_title]','Post title'), array('[matched_content]','Post content'),array('[from_name]','Username of the post author'),array('[from_id]','ID of the post author'),array('[from_url]','FB url of the post author'),array('[from_thumbnail]','Thumbnail url of the post author'),array('[shares_count]','Shares count'),array('[original_date]','Date of the post'),array('[original_date_timestamp]','Date of the post timestamp format'),array('[external_url]','External url of the shared post. only if the item is actually a shared link on the page'),array('[image_src]','source url of an image at the post if exists'),array('[likes_count]','Number of likes'),array('[vid_embed]','Embed code of the video if exists'),array('[vid_id]','ID of the video if exists'),array('[vid_url]','URL of the video if exists'), array('[item_tags]','Hashtags comma separated if exist.'),$ad1,$ad2,$source );
$allowed_tags['Facebookevents'] = array(array('[event_description]','Description of the event'),array('[post_id]','ID of the FB event'),array('[original_title]','Event title'),array('[original_date]','Date when the event was modified'),array('[image_src]','Event cover picture'), array('[interested_count]' , 'number of interested pepole') , array('[going_count]' , 'number of going pepole') , array('[place_name]','Place name where the event will happen'), array('[place_address]','Address of the event location'), array('[place_street]','Street name of the place'),array('[place_city]','Place city'),array('[place_latitude]','Latitude of the place location'),array('[place_longitude]','Longitude of the place'),array('[start_time]','When the event starts'),array('[start_time_timestamp]','Timestamp for the event start time e.g 1535052600'),array('[end_time]','When the event ends'),array('[end_time_timestamp]','Timestamp of when the event ends date e.g 1535052600'),array('[place_map]','Embed code of a map showing the place if exists'),$ad1,$ad2,$source );

$allowed_tags['Twitter'] = array(array('[item_id]','ID of the tweet'),array('[item_url]','Tweet url') , array('[item_original_link]','original url to the shared post if exists') ,array('[item_description]','Tweet textual content'),array('[item_embed]','embed code of the tweet') ,array('[item_retweet_count]','Rewteet Count'),array('[item_favorite_count]','Favorite count'),array('[item_author_id]','Numeric user id'),array('[item_author_screen_name]','Screen name of the user example: cnn'),array('[item_author_name]','User name'),array('[item_author_description]','User description'),array('[item_author_url]','user url'),array('[item_author_profile_image]','user profile image src'),array('[item_author_profile_background_image]','Background image src url used by the user'),array('[item_created_at]','Time of the tweet'),array('[item_title]','Title') , array('[item_video_url_direct]' , 'Direct video URL if available ') );
$allowed_tags['SoundCloud']= array( array('[item_id]','Numeric ID of the sound') ,array('[item_url]','Link of the sound at soundcloud'), array('[item_embed]','Embed code of the sound') , array('[item_video_url]','URL of the video if exists'), array('[item_video_embed]','embed code of the video if exists'),array('[item_likes_count]','Likes count'),array('[item_purchase_url]','Purchase link if exists'),array('[item_thumbnail]','Thumbnail url'),array('[item_comment_count]','Comments count'),array('[item_title]','Title of the sound'),array('[item_description]','Description of the sound'),array('[item_favoritings_count]','Favourites count'),array('[item_genre]','Genre of the post'),array('[item_playback_count]','Playback count'),array('[item_reposts_count]','Repost count'),array('[item_tags]','Item tags if exists'),array('[item_created_at]','Time where the sound was created'),array('[item_duration]','Duration of the post in minutes'),array('[item_user_id]','User ID of the soundCloud user'),array('[item_user_link]','Link of the author'),array('[item_user_thumbnail]','Thumbnail source of the sound'),array('[item_user_username]','username of the author'),array('[item_download_url]','Download url of the item if the item is downloadable '),$ad1,$ad2,$source );
$allowed_tags['Craigslist'] = array(array('[item_title]','Item title'),array('[item_description]','Item description'),array('[item_link]','Item url'),array('[item_date]','Item publish date'),array('[item_img]','Item image src link'),array('[item_img_html]','Item image html code'),array('[item_price]','Nmeric price of the item'),$ad1,$ad2,$source);
$allowed_tags['Itunesmusic'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_collectionId]','Numeric ID of the collection that the item belongs to.'),array('[item_collectionName]','Name of the collection that the item belongs to'),array('[item_collectionViewUrl]','Url of the collection that the item belongs to'),array('[item_previewUrl]','Media url of the item preview'),array('[item_img]','Main item image url'),array('[item_artistId]','Numeric ID of the item author/artist'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_artistViewUrl]','Url of the item author/artist'),array('[item_price]','Price of the item'),array('[item_collectionPrice]','Price of the item Collection'),array('[item_trackCount]','Count of items at the collection that the item belongs to'),array('[item_trackCount]','Numeric order of the item at the collection'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item'),array('[item_time]','Duration of the media in minutes') );
$allowed_tags['Itunesmovie'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_previewUrl]','Media url of the item preview'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item'),array('[item_time]','Duration of the media in minutes') );
$allowed_tags['Itunespodcast'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item') );
$allowed_tags['ItunesmusicVideo'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_previewUrl]','Media url of the item preview'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item'),array('[item_time]','Duration of the media in minutes'));
$allowed_tags['Itunesaudiobook'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item') );
$allowed_tags['ItunesshortFilm'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_previewUrl]','Media url of the item preview'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item'),array('[item_time]','Duration of the media in minutes') );
$allowed_tags['ItunestvShow'] = array(array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_previewUrl]','Media url of the item preview'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_releaseDate]','Release date of the item'),array('[item_time]','Duration of the media in minutes') );
$allowed_tags['Itunesebook'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_country]','Country of the item'),array('[item_currency]','Currency of the item'),array('[item_rating]','Rating of the item'),array('[item_rating_counts]','Number of ratings of the item'),array('[item_releaseDate]','Release date of the item') );
$allowed_tags['Itunessoftware'] = array( array('[item_link]','Link to the item at Itunes'),array('[item_id]','Numeric ID of the item at Itunes'),array('[item_description]','Textual description of the item at Itunes'),array('[item_title]','Title of the item'),array('[item_img]','Main item image url'),array('[item_artistName]','Name of the item author/artist'),array('[item_artistViewUrl]','Url of the item author/artist'),array('[item_supportedDevices]','Supported Devices for the app'),array('[item_fileSize]','Size of the item in MegaBytes'),array('[item_sellerUrl]','External website of the item seller'),array('[item_trackName]','Name of the item at Itunes'),array('[item_price]','Price of the item'),array('[item_currency]','Currency of the item'),array('[item_version]','Version number of the item'),array('[item_primaryGenreName]','Genre name of the item'),array('[item_rating]','Rating of the item'),array('[item_rating_counts]','Number of ratings of the item'),array('[item_screenshotUrls]','Urls of the screenshots comma separated'),array('[item_screenshot]','html of the screenshots'),array('[item_releaseDate]','Release date of the item') );

$envatoTags = array( array('[item_id]','Numeric ID of the item'),array('[item_title]','Title of the item'),array('[item_link]','URL of the item at Envato'),array('[item_link_affiliate]','Affiliate URL of the item using impact radius')  , array('[item_description]','Html description of the item'),array('[item_category]','Category of the item at Envato'),array('[item_category_url]','Url of the category at Envato'),array('[item_price]','Price of the item in $'),array('[item_author]','User name of the item author'),array('[item_author_url]','Url of the item author') ,array('[item_published_at]','Time when the item was published'),array('[item_updated_at]','Time when the item was updated'),array('[item_author_image]','url of the author image'),array('[item_rating]','Rating of the item out of 5'),array('[item_tags]','Tags of the item comma separated'),array('[item_sales]','Sales count of the item when posted'),array('[affiliate_id]','Affiliate ID set at the plugin settings page'));

$allowed_tags['Envatothemeforest'] = $allowed_tags['Envatocodecanyon'] = $allowed_tags['Envato3docean'] = array_merge($envatoTags,array( array('[preview_img]','Preview image url'),array('[preview_icon]','Url of the item icon'),array('[live_site]','url of the item live preview') ,array('[live_site_affiliate]','Affiliate URL of the item live preview')   ));
$allowed_tags['Envatographicriver'] = $allowed_tags['Envatophotodune'] = array_merge($envatoTags,array( array('[preview_icon]','Url of the preview icon'),array('[preview_img]','Url of the preview image') ));
$allowed_tags['Envatovideohive'] = array_merge($envatoTags,array( array('[preview_icon]','Url of the preview icon'),array('[preview_img]','Url of the preview image'),array('[preview_vid]','Url of the preview video') ));
$allowed_tags['Envatoaudiojungle'] = array_merge($envatoTags,array( array('[preview_icon]','Url of the preview icon'),array('[preview_mp3]','Url of the preview sound') ));
$allowed_tags['DailyMotion'] = array( array('[vid_player]','html embed code'),array('[item_id]','ID of the video at DM'),array('[item_title]','Title of the vide'),array('[item_image]','SRC url of the vid thumbnail'),array('[item_link]','URL of the video at DM'),array('[item_duration]','Length of the video'),array('[item_views]','Number of views'),array('[item_description]','Description of the video'),array('[item_channel]','Channel of the video'),array('[item_category_url]','URL of the video category'),array('[item_author]','Author name'),array('[item_author_id]','ID of the vid author'),array('[item_author_url]','URL of the vid author'),array('[item_author_image]','SRC url of the author image'),array('[item_published_at]','Timestamp when the video was published ex 1484080131'),array('[item_published_at_formated]','Date and time when the video was published ex 2017-01-13 22:30:26'),array('[item_likes]','Number of likes'));
$allowed_tags['Reddit'] = array(array('[item_title]','Title of the item'),array('[item_description]','Description of the item if exists'),array('[item_url]','URL of the shared content'),array('[item_link]','Reddit link for the post'),array('[item_date]','Timestamp of the post'),array('[item_img]','Image src url if exists'),array('[item_id]','ID of the item'),array('[item_domain]','Domain name of the shared post'),array('[item_score]','Score of the item at Reddit'),array('[item_author]','Author name'),array('[item_gif]','Gif image url if exists'),array('[item_mp4]','mp4 video link if exists'),array('[item_img_html]','Html markup of the item image'),array('[item_embed]','Embed code of a gif/video if exists'),array('[item_gif_embed]','Gif embed code if exists'),array('[item_mp4_embed]','mp4 video embed code if exists'),array('[item_author_link]',''),array('[item_date_formated]','Date and time when the post was published') , array('[item_nsfw]' , 'Returns yes if nsfw or no if not') , array('[item_flairs]', 'Item flaris like Video or Cat Pic') );
$allowed_tags['Walmart'] = array(array('[item_img]','Url of the item image'),array('[item_url]','link of the item at Walmart'),array('[product_affiliate_url]','If you have a publisher ID, use this tag for the url as it will has the affiliate tracking'),array('[item_cart_url]','Add to chart url'),array('[item_cart_affiliate_url]','Add to chart url with affiiliate tracking enabled'),array('[item_rating]','Rating of the item'),array('[item_rating_img]','rating image src'),array('[item_list_price]','Item list price'),array('[item_imgs]','Comma separated list of item images'),array('[item_img_html]','Html of the item image'),array('[item_imgs_html]','Html of all item images'),array('[item_link]','Link of the item at Walmart'),$ad1,$ad2);
$allowed_tags['Single'] = array( array('[matched_content]','Content extracted from all rules'),array('[matched_content_plain]','Content extracted from all rules in plain text after removing all html tags'),array('[source_link]','Original link of the source content'),array('[rule_{ruleNumber}]','Returns the content from the set rule, for example, if you have set two rules to extract the content using REGEX. [rule_1] returns content from the first rule and [rule_2] returns the second rule content and so on...'),array('[rule_{ruleNumber}_plain]','Returns the content from the set rule in plain text after removing all html tags, for example, if you have set two rules to extract the content using REGEX. [rule_1_plain] returns content from the first rule and [rule_2_plain] returns the second rule content and so on... all in plain text'),$ad1,$ad2);
$allowed_tags['Careerjet'] = array(array('[item_title]','Job title'),array('[item_description]','Job description'),array('[item_url]','URL of the job'),array('[item_date]','Date when the job was posted'),array('[item_date_timestamp]','Date when the job was posted'),array('[item_locations]','Location of the work'),array('[item_site]','Site of the job'),array('[item_company]','Company of the job'),array('[item_salary]','Salary'),array('[item_salary_min]','Minimum salary'),array('[item_salary_max]','Maximum salary'),array('[item_salary_type]','Salary type'),array('[item_salary_currency_code]','Currency of the sallary') , array( '[item_source_site]'  , 'Name of the source site displayed below the job description') , array('[item_logo_html]' , 'HTML of the logo image if available') , array('[item_logo]' , 'src link of the logo image if available') ) ;
$allowed_tags['Aliexpress'] = array( array('[item_id]','Numeric ID of the item'),    array('[item_url]','Link to the item'),    array('[item_title]','Title'),    array('[item_rating]','Numeric rating value out of 5'),    array('[item_orders]','Number of current orders'),    array('[item_price_current]','current displayed price'),    array('[item_price_original]','price before sale'),    array('[item_images]','Comma separated list of images URLs'),    array('[item_ship_from]','Location of the item'),    array('[item_delivery_time]','Time taken for delivery'),    array('[item_ship_cost]','Shipping costs'),    array('[item_wish_count]','whishes count'),    array('[item_description_url]','Link for the description iframe'),    array('[item_img]','Product featured image URL'),    array('[item_imgs_html]','HTML of the product images'),    array('[item_image_html]','Featured Image HTML'),    array('[item_description]','Description'), array('[item_price_numeric]','Numeric value for the price'),    array('[item_price_original_numeric]','Numeric value for the item price before sale'), array('[price_with_discount]', 'Price and sale price displayed side by side'), array('[item_affiliate_url]' , 'Affiliate link for the item') );
$allowed_tags['telegram'] = array(
    array('[item_img]', 'Src link of the image'),
    array('[item_type]', 'Type of the item'),
    array('[item_url]', 'URL of the item'),
    array('[item_link]', 'Link of the item'),
    array('[item_id]', 'ID of the item'),
    array('[item_description]', 'Description of the item'),
    array('[item_date]', 'Date of the item'),
    array('[item_views]', 'Number of views'),
    array('[item_video_link]', 'Link to the video'),
    array('[item_img_html]', 'HTML for the image'),
    array('[item_title]', 'Title of the item'),
    array('[item_embed]', 'Embed code'),
	array('[item_author]', 'Author name')
);

 //Places
 /*
 add

  [item_dine_in] => 1
    [item_formatted_phone_number] => 01 45 48 82 86
    [item_international_phone_number] => +33 1 45 48 82 86
    [item_serves_breakfast] => 1
    [item_takeout] => 1
    [item_website] => 
    [item_weekday_text] => Monday: 7:00 AM – 11:30 PM<br>Tuesday: 7:00 AM – 11:30 PM<br>Wednesday: 7:00 AM – 11:30 PM<br>Thursday: 7:00 AM – 11:30 PM<br>Friday: 7:00 AM – 11:30 PM<br>Saturday: 8:00 AM – 11:30 PM<br>Sunday: 9:00 AM – 11:30 PM
    [item_photos] => https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFh4Ry3qbeD2ZDYtc_e5OWzhtDj_BB2omd2xIlqwXdd-Y7GixojaK_OpdheYDziWkUDR0OCwmO7_mKL8FaCaggYQpWSBcZ3qer1FFW_KvbfwjYUzYMGXRjS5_lkce3lok_HOVKmc0l71XVpviSt-DjDFO6gL0X4PCrTPmjwT9BpFIhQ&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFhEnUjNpUifjMQ712-i701z_TD1TG7-RDWpgg50Zf5WzTWxHiPkCcWTJh1Slz7FIW1RfiW0f-5JCIAzsvN77PJ6ixi1x7647DgimYt_tSaWwTn_kGGNQdc_1Rny6uzIpq8W7C9v9zYoGhm8IwMkEwmOGcJONP9L0-UYs-xY_YFBl87L&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFh0kO9XCYybHCOJb0aduj35JtIsxIK3Jdvev1a0rqpDkKlymktA21BcLoC4X4_atDGQM-JqL-gk6h1qYGf4MxOWFyVkaRmxF9RTPbJx6auxhzHMFj8fDRxJuJiy_Z36vpEg3I4KCEfW2B2BZocDBVTIWconSPN0cCDmgTiQOcGSOT8&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFiV5foYQeCsrxCWd_jNXf2or7c5weV0HuwRXLNMmEexnl-2o_Bt0hNJc2TeUJqfqgIR1Ghw6jvlwOKva2df_z6nsQXOOMF-e8oxxBRWLA1D5cWkTT8SxZeH__Q2HR86KVfcp3kyy_jhOy5wX9MCDPKB5Ehcha264fQMi735YuPBWxOJ&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFjYSLv6UzYoVeGoBvomHCb6o_kkB0obR5He_RwOZePk7VZ09aUYVgopoDt-XK9J1zIUE3rXIlIJfn_n7rOu6pcOxtuIuvIJD_klh0Vw6iMVfW-aRMSicvX7_xfGX3d-Tujsd5lrXrodPSClhYw3kI09emuplBqtkkyjapO6eY-YRbhC&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFicC9qNukxjC77oOOhKsaXAxl3_Hh8osC9X15s9ZzdSqSG7DAOD5J409OAY41UAIDdsmQTACcrhLoQlMcRa8kE_mkh_zbyOuXj090YbOky_sGVdG4owLuHruXDHN7baMo0lUQbSw45b3sW3X87fUyJ_tHDGqBMpX_1KXEh1fa9cm4EH&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFhEb5JPhjwVZiCR3SY5ZGoj8cZMpt-tyMqtKkAug5vMLEyPMFxwyDL3eoFWIEya3iirimSGfxvr6JzHJe9SBNNv2_JCF0DHtGLDZrcKW_nSDxU_RqGH6os5jJszNt1yRfq7YaGAintNB1XHdwznbwIRzNvjumG4TFMB4kl-TVaGlsXg&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFiiMn0ToFQszMORjx9q4Ty-u5ZAPuPsNj6Rjm8ASgpAxc2x79qtYt_LYCoSYQUaO4HrDbGbaDEe1TXD1PnC4uZL8bo1abA1PD6H2HkFClnFdCs99tamYWBu5qgfE7nlKJckQgw2SDzpDJPy5_RMk04YwPQXq2z3Q8gxmm8XArnbaPXp&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFhi9cRHbE-MehIxN0aiXxj7_SufBdIxVZQH6TwcfIUZQ22_oOq2ZnFeVTduKB5okGVq0VLGQEv0bHFZj6RRMq1SRQZcOxBpA-QwC63aKsydtnpmNoY-iGhnOomnmusXcD_rFDfBGzpkAy8zqn-QrVIoDXMVhB3o96X9qnBTOh7mZTxB&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc,https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=AWU5eFiSWgATM8basg792WGLy1PMvmkQLdHG4hswcQLoWZi41MLplDB9ikEudTBRSgHDDMPUoSINHDrQAlOWn7b7nKA9aPWaDnHMnG0XRewM6I88ewE2qKCoXyOqaH8utTGkdx5uBxSw--GO3wbWP-XhrMbBfqSGwlNqOGxb8dTB6skdUJFn&key=AIzaSyCgAbY5aH4k-fRnhjcnSPLJr-qh1TXYPfc
    [item_photos_html] => 

 */
$allowed_tags ['Places'] = array (
		array (
				'[item_url]',
				'URL of the item at Google Places' 
		),
		array (
				'[item_formatted_address]',
				'Formatted address of the item' 
		),
		array (
				'[item_icon]',
				'Icon of the item' 
		),
		array (
				'[item_name]',
				'Name of the item' 
		),
		array (
				'[item_title]',
				'Title of the item' 
		),
		array (
				'[item_id]',
				'ID of the item' 
		),
		array (
				'[item_price_level]',
				'Price level of the item' 
		),
		array (
				'[item_rating]',
				'Rating of the item' 
		),
		array (
				'[item_user_ratings_total]',
				'Number of user ratings' 
		),
		array (
				'[item_business_status]',
				'Business status of the item' 
		),
		array (
				'[item_lat]',
				'Latitude of the item' 
		),
		array (
				'[item_lng]',
				'Longitude of the item' 
		),
		array (
				'[item_image]',
				'Image URL of the item' 
		),
		array (
				'[item_compound_code]',
				'Compound code of the item' 
		),
		array (
				'[item_global_code]',
				'Global code of the item' 
		),
		array (
				'[item_types]',
				'Types of the item' 
		),
		array (
				'[item_map_iframe]',
				'Embed code of the item map' 
		),
		array (
				'[item_map_url]',
				'URL of the item map' 
		),
		array ( '[item_dine_in]', '1 if available' ),
		array ( '[item_formatted_phone_number]', 'Formatted phone number' ),
		array ( '[item_international_phone_number]', 'International phone number' ),
		array ( '[item_serves_breakfast]', '1 if available' ),
		array ( '[item_takeout]', '1 if available' ),	
		array ( '[item_website]', 'Website of the item' ),
		array ( '[item_weekday_text]', 'Weekday working hours' ),
		array ( '[item_photos]', 'Comma separated list of photos' ),
		array ( '[item_photos_html]', 'HTML of the photos' ),
		array ( '[item_reviews]', 'List of reviews' )
);

$gpt_tag = array('[gpt]Prompt[/gpt]', 'Call OpenAI for a prompt and return the result for example [gpt]Write an article about [original_title] in French[/gpt]'); 

//merge gpt_tag array to every array in $allowed_tags values
foreach ($allowed_tags as $key => $value) {
	array_push($allowed_tags[$key], $gpt_tag);
}

$query="select * from {$prefix}automatic_camps where camp_id='$post_id'";
$res=$wpdb->get_results($query);
$raw_res = $res; // copy of the res var as we are going to change it
$camp_options_arr=array();

if (count($res) > 0 ){
	$res=$res[0];
	$camp_post_every = $res->camp_post_every;
	$camp_keywords = stripslashes ( $res->camp_keywords );
	$camp_cb_category = stripslashes ( $res->camp_cb_category );
	$camp_replace_link = stripslashes ( $res->camp_replace_link );
	$camp_add_star = stripslashes ( $res->camp_add_star );
	$camp_post_title = stripslashes ( $res->camp_post_title );
	$camp_post_content =   stripslashes( $res->camp_post_content );
	$camp_amazon_category = stripslashes ( $res->camp_amazon_cat );
	$camp_amazon_region = $res->camp_amazon_region;
	$camp_post_category = stripslashes ( $res->camp_post_category );
	$camp_post_status = stripslashes ( $res->camp_post_status );
	
	$post_name = '';
	if (isset ( $res->post_name ))
		$post_name = stripslashes ( $res->post_name );
		
		$camp_options = unserialize ( $res->camp_options );

		$camp_options_arr = $camp_options;
		
		$feeds = stripslashes ( $res->feeds );
		$camp_type = $res->camp_type;
		$camp_search_order = $res->camp_search_order;
		$camp_youtube_order = $res->camp_youtube_order;
		$camp_youtube_cat = $res->camp_youtube_cat;
		$camp_post_author = $res->camp_post_author;
		$camp_post_type = $res->camp_post_type;
		$camp_post_exact = $res->camp_post_exact;
		$camp_post_execlude = $res->camp_post_execlude;
		$camp_yt_user = $res->camp_yt_user;
		$camp_translate_from = $res->camp_translate_from;
		$camp_translate_to_2 = $res->camp_translate_to_2;
		
		$camp_translate_to= $res->camp_translate_to;
		
		$camp_post_custom_k=unserialize($res->camp_post_custom_k);
		$camp_post_custom_v=unserialize($res->camp_post_custom_v);
		
		
		
		$temp='';
		if(is_array($camp_options)){
			
			foreach($camp_options as $option){
				$temp=$temp.'|'.$option;
			}
			$camp_options=$temp;
			
		}
		
		
		if( stristr($res->camp_general, 'a:') ) $res->camp_general=base64_encode($res->camp_general);
		
		$camp_general=unserialize(base64_decode($res->camp_general));
		
		// striping slashes
		foreach ($camp_general as $campKey => $campVal){
			
			if( ! is_array($campVal) ){
				$camp_general[$campKey] = stripslashes($campVal);
			}else{
				$camp_general[$campKey] = array_map('wp_automatic_stripslashes',$campVal);
			}
			
		}
		
		if(! is_array($camp_general)) $camp_general=array();
		
		//cg_iu_attribute
		if(! isset($camp_general['cg_iu_attribute'])) $camp_general['cg_iu_attribute']='All';
		
		// Feed extraction method old format adaption
		
		
		//adabting to the new form
		if( in_array('OPT_SUMARRY_FEED', $camp_options_arr) ){
			$camp_general['cg_feed_extraction_method'] = 'summary';
		}elseif( in_array('OPT_FEED_CUSTOM', $camp_options_arr)   ){
			$camp_general['cg_feed_extraction_method'] = 'css';
			
			$camp_general['cg_custom_selector'] = array($camp_general['cg_custom_selector'] , $camp_general['cg_custom_selector2'] , $camp_general['cg_custom_selector3'] );
			$camp_general['cg_feed_custom_id'] = array($camp_general['cg_feed_custom_id'] , $camp_general['cg_feed_custom_id2'] , $camp_general['cg_feed_custom_id3'] );
			
			$cg_feed_css_size = array();
			$cg_feed_css_size[] = in_array('OPT_SELECTOR_SINGLE', $camp_options) ? 'single' : 'all' ;
			$cg_feed_css_size[] = in_array('OPT_SELECTOR_SINGLE2', $camp_options) ? 'single' : 'all' ;
			$cg_feed_css_size[] = in_array('OPT_SELECTOR_SINGLE3', $camp_options) ? 'single' : 'all' ;
			$camp_general['cg_feed_css_size'] = $cg_feed_css_size;
			
			$cg_feed_css_wrap = array();
			$cg_feed_css_wrap[] = in_array('OPT_SELECTOR_INNER',$camp_options) ? 'inner' : 'outer' ;
			$cg_feed_css_wrap[] = in_array('OPT_SELECTOR_INNER2',$camp_options) ? 'inner' : 'outer' ;
			$cg_feed_css_wrap[] = in_array('OPT_SELECTOR_INNER3',$camp_options) ? 'inner' : 'outer' ;
			$camp_general['cg_feed_css_wrap'] = $cg_feed_css_wrap;
			
		}elseif( in_array('OPT_FULL_FEED', $camp_options_arr)){
			$camp_general['cg_feed_extraction_method'] = 'auto';
		}elseif(in_array('OPT_FEED_CUSTOM_R', $camp_options_arr) ){
			$camp_general['cg_feed_extraction_method'] = 'regex';
			$camp_general['cg_feed_custom_regex'] = array($camp_general['cg_feed_custom_regex'],$camp_general['cg_feed_custom_regex2']);
		}
		
		if(  ! is_array($camp_general['cg_feed_custom_strip_id'])){
			
			$cg_feed_custom_strip_id[] = $camp_general['cg_feed_custom_strip_id'];
			$cg_feed_custom_strip_id[] = $camp_general['cg_feed_custom_strip_id2'];
			$cg_feed_custom_strip_id[] = $camp_general['cg_feed_custom_strip_id3'];
			
			$cg_custom_strip_selector[] = $camp_general['cg_custom_strip_selector'];
			$cg_custom_strip_selector[] = $camp_general['cg_custom_strip_selector2'];
			$cg_custom_strip_selector[] = $camp_general['cg_custom_strip_selector3'];
			
			$cg_feed_custom_strip_id = array_filter($cg_feed_custom_strip_id);
			$cg_custom_strip_selector = array_filter($cg_custom_strip_selector);
			
			if(count($cg_feed_custom_strip_id) == 0 ) $cg_feed_custom_strip_id = array("");
			if(count($cg_custom_strip_selector) == 0 ) $cg_custom_strip_selector = array("class");
			
			
			$camp_general['cg_feed_custom_strip_id'] = $cg_feed_custom_strip_id;
			$camp_general['cg_custom_strip_selector'] = $cg_custom_strip_selector;
			
		}
		
		//print_r($res);
		
}else{
	$camp_post_every=2000;
	$camp_keywords='';
	$camp_cb_category='All';
	$camp_replace_link='';
	$camp_add_star=5;
	$camp_post_title='[original_title]';
	$camp_post_content='[ad_1]
[matched_content]
[ad_2]';
	
	$camp_amazon_category='';
	$camp_amazon_region='';
	
	@$camp_post_category= '';
	$camp_post_status='publish';
	@$post_name='';
	$camp_type='gpt3';
	$camp_search_order='';
	$camp_youtube_order='';
	$camp_youtube_cat='';
	$camp_post_author =1;
	$camp_post_type = 'post';
	$camp_post_exact='';
	$camp_post_execlude='';
	
	//default checked checkboxes
	$camp_options = [
		'OPT_POST_STARS',
		'OPT_POST_CONTENT',
		'OPT_TBSS',
		'OPT_YT_CACHE',
		'OPT_DM_CACHE',
		'OPT_VM_CACHE',
		'OPT_FL_CACHE',
		'OPT_EV_CACHE',
		'OPT_CL_CACHE',
		'OPT_EB_CACHE',
		'OPT_PT_CACHE',
		'OPT_RD_CACHE',
		'OPT_RD_OFFICIAL_EMBED',
		'OPT_PT_AUTO_TITLE',
		'OPT_IT_CACHE',
		'OPT_IU_CACHE',
		'OPT_AE_CACHE',
		'OPT_IT_AUTO_TITLE',
		'OPT_SC_CACHE',
		'OPT_FB_CACHE',
		'OPT_GENERATE_FB_TITLE',
		'OPT_FEEDS_OG_IMG',
		'OPT_SUMARRY_FEED',
		'OPT_CB_DESCRIPTION',
		'OPT_TW_VID_EMBED',
		'OPT_AM_GALLERY',
		'OPT_THUMB',
		'OPT_FEED_SCRIPT',
		'OPT_WM_CACHE',
		'OPT_CJ_CACHE',
		'OPT_OPENAI_PENDING',
		'OPT_TW_RT',
		'OPT_TW_RE',
		'OPT_GP_REMOVE_H1',
		'OPT_PL_CACHE'
	];
	
	//add this checkbox OPT_USE_OPENROUTER if the option wp_automatic_openrouter_key holds a value 
	// and wp_automatic_openai_key is empty
	if( get_option('wp_automatic_openrouter_key') != '' && get_option('wp_automatic_openai_key') == '' ){
		$camp_options[] = 'OPT_USE_OPENROUTER';
	}
	
	$camp_yt_user='';
	$camp_translate_from='no';
	$camp_translate_to_2='no';
	$camp_translate_to='no';
	$camp_post_custom_k=array();
	$camp_post_custom_v=array();
	$camp_general=array();
	$camp_general['cg_post_format']='';
	$camp_general['cg_thmb_list']='';
	
	$camp_general['cg_eb_full_img_t']= $camp_general['cg_am_full_img_t'] =   '';
	$camp_general['cg_it_full_img_t']= $camp_general['cg_it_full_img_t'] =  "";
	
	
	
	$camp_general['cg_yt_dte_day'] = date('d');
	$camp_general['cg_yt_dte_month'] = date('m');
	$camp_general['cg_yt_dte_year'] = date('Y');
	$camp_general['cg_post_tags'] = '';
	$camp_general['cg_regex_replace'] = '';
	$camp_general['cg_minimum_width'] = 100;
	$camp_general['cg_feed_lazy'] = 'data-src';
	
	$keys_array_empty =  '  cg_ml_source   cg_ml_lnk_method   cg_ml_lnk_method   cg_ml_lnk_method   cg_ml_lnk_css   cg_ml_lnk_css_type   cg_ml_lnk_css_size   cg_ml_lnk_css_wrap   cg_ml_lnk_regex   cg_ml_lnk_visual   cg_multi_search_replace   cg_ml_append   cg_ml_cnt_method   cg_ml_cnt_method   cg_ml_cnt_method   cg_ml_cnt_method   cg_ml_cnt_method   cg_ml_cnt_method   cg_ml_cnt_css   cg_ml_cnt_css_type   cg_ml_cnt_css_size   cg_ml_cnt_css_wrap   cg_ml_cnt_regex   cg_ml_page_2_url   cg_ml_page_increment   cg_ml_page_base   cg_ml_infinite   cg_ml_cnt_visual   cg_multi_posts_list   cg_ml_cache   cg_ml_cache   cg_sn_source   cg_sn_ttl_method   cg_sn_ttl_method   cg_sn_ttl_method   cg_sn_ttl_method   cg_sn_css   cg_sn_css_type   cg_sn_css_size   cg_sn_css_wrap   cg_sn_regex   cg_sn_visual   cg_sn_cnt_method   cg_sn_cnt_method   cg_sn_cnt_method   cg_sn_cnt_method   cg_sn_cnt_css   cg_sn_cnt_css_type   cg_sn_cnt_css_size   cg_sn_cnt_css_wrap   cg_sn_cnt_regex   cg_sn_cnt_visual   cg_sn_cookie   cg_sn_after   cg_sn_after   cg_sn_after   cg_sn_after   cg_sn_after   cg_ml_ttl_method   cg_ml_ttl_method   cg_ml_ttl_method   cg_ml_ttl_method   cg_ml_css   cg_ml_css_type   cg_ml_css_size   cg_ml_css_wrap   cg_ml_regex   cg_ml_example   cg_ml_visual   cg_feed_extraction_method   cg_feed_extraction_method   cg_feed_extraction_method   cg_feed_extraction_method   cg_feed_extraction_method   cg_feed_css_size   cg_feed_css_wrap   cg_ml_example_2   cg_feed_visual   cg_allowed_tags   cg_custom_selector_cat   cg_custom_selector_cat   cg_custom_selector_cat   cg_feed_custom_id_cat   cg_parent_cat   cg_ml_cookie   cg_articles_date_last   cg_articles_date_last_val   cg_cj_locale   cg_cj_location   cg_cj_sort   cg_cj_sort   cg_cj_sort   cg_cj_contracttype   cg_cj_contracttype   cg_cj_contracttype   cg_cj_contracttype   cg_cj_contracttype   cg_cj_contracttype   cg_cj_contractperiod   cg_cj_contractperiod   cg_cj_contractperiod   cg_ae_full_img_t   cg_ae_custom_urls   cg_ae_custom_cur   cg_ae_custom_domain   cg_cl_full_img_t   cg_wm_cat   cg_wm_sort   cg_wm_sort   cg_wm_sort   cg_wm_sort   cg_wm_sort   cg_wm_sort   cg_wm_sort_dir   cg_wm_sort_dir   cg_wm_price_from   cg_wm_price_to   cg_rd_page   cg_fb_from   cg_fb_from   cg_fb_title_default   cg_am_condition   cg_am_session   cg_am_ubid   cg_tt_html   cg_tt_user   cg_dm_playlist   cg_dm_width   cg_dm_height   cg_dm_ctr   cg_dm_lang   cg_dm_channel   cg_yt_before   cg_yt_safe   cg_yt_safe   cg_yt_safe   cg_yt_plang   cg_eb_condition   cg_eb_condition   cg_eb_condition   cg_eb_seller_type   cg_eb_seller_type   cg_eb_seller_type   cg_eb_currency   cg_eb_min_bid   cg_eb_max_bid   cg_eb_currency_2   cg_eb_country   cg_eb_postal   cg_default_tags   cg_adjust_tags   cg_post_parent   cg_max_length   cg_poly_lang  cg_keywords_replace cg_am_custom_urls cg_rd_full_img_t cg_tw_type cg_pixabay_keyword cg_criteria_skip_fields cg_criteria_skip_criterias cg_criteria_skip_values cg_criteria_skip_fields_must cg_criteria_skip_criterias_must cg_criteria_skip_values_must';
	$keys_array_empty_arr = array_filter( explode(' ' , $keys_array_empty));
	
	foreach($keys_array_empty_arr as $keys_array_empty_key){
		$camp_general[$keys_array_empty_key] = '';
	}
	
	// undefined camp_general keys
	$camp_generalKeys = "cg_custom_selector,cg_feed_custom_id,cg_custom_selector2,cg_feed_custom_id2,cg_custom_selector3,cg_feed_custom_id3,cg_feed_custom_regex,cg_feed_custom_regex2,cg_part_to_field,cg_custom_strip_selector,cg_feed_custom_strip_id,cg_custom_strip_selector2,cg_feed_custom_strip_id2,cg_custom_strip_selector3,cg_feed_custom_strip_id3,cg_post_strip,cg_custom_selector_tag,cg_feed_custom_id_tag,cg_custom_selector_author,cg_feed_custom_id_author,cg_min_length,cg_feed_encoding,cg_cl_page,cg_iu_media,cg_iu_attribute,cg_iu_lang,cg_fb_source,cg_fb_page,cg_fb_page_id,cg_fb_title_count,cg_am_node,cg_am_order,cg_am_min,cg_am_max,cg_am_param_type,cg_am_param,cg_yt_dte_minutes,cg_vm_user_channel,cg_vm_user,cg_vm_order,cg_vm_order_dir,cg_vm_cc,cg_vm_width,cg_vm_height,cg_sc_user_playlist,cg_sc_user,cg_sc_from,cg_sc_to,cg_pt_user_channel,cg_pt_user,cg_pt_title_count,cg_it_user,cg_it_title_count,cg_tw_lang,cg_yt_playlist,cg_yt_license,cg_yt_type,cg_yt_duration,cg_yt_definition,cg_yt_width,cg_yt_height,cg_yt_ctr,cg_yt_lang,cg_eb_user,cg_eb_site,cg_eb_cat,cg_ebay_custom,cg_eb_listing,cg_eb_order,cg_eb_min,cg_eb_max,cg_eb_iframe_h,cg_eb_param,cg_eb_redirect_end,cg_fl_user,cg_fl_user_album,cg_fl_order,cg_cb_lang,cg_camp_tax,cg_keyword_cat,cg_tags_limit,cg_keyword_tag,cg_content_limit,cg_title_limit,cg_camp_post_regex_exact,cg_camp_post_regex_exclude,cg_wpml_lang,cg_tag_tax,cg_translate_method,cg_ev_filter,cg_ev_cat,cg_ev_tags,cg_ev_author,cg_ev_sort,cg_ev_sort_dir,cg_ev_api,cg_cat_replace,cg_tag_replace";
	$camp_generalKeys = explode(',', $camp_generalKeys);
	
	foreach ($camp_generalKeys as $camp_generalKey){
		$camp_general[$camp_generalKey] =  null;
	}
	
	if(is_array($camp_options)){
		
		$temp = '';
		foreach($camp_options as $option){
			@$temp=$temp.'|'.$option;
		}
		$camp_options=$temp;
		
	}
	
}




?>
 
 
<span id="wp_automatic_main_box_loading" class="spinner is-active" style="float:left;padding-left:25px">Loading...</span>
<div style= "clear:both"></div>
<div id="wp_automatic_main_box" class="TTWForm-container" dir="ltr" style="visibility:hidden;">
 
 <?php //nonce field 
wp_nonce_field('wp_automatic_nonce','wp_automatic_nonce_field'); ?>
    
     
<div class="TTWForm">
 

<div class="panes">
	

	<!--first tab-->
	<div class="contains">
	

	<input type="hidden" value="<?php   echo $post_id ?>" id="wp_automatic_post_id" />
      

		<div id="field-camp_type-container" class="field f_100" style="margin-top:10px" >
			<label for="field-camp_type">
				Campaign Type 
			</label>
			<select <?php if(count($raw_res) != 0)   echo 'disabled="disabled"' ?> name="camp_type" id="camp_type">
				
			    <option  value="gpt3"  <?php @wp_automatic_opt_selected('gpt3',$camp_type) ?> >AI Articles</option>
				<option  value="Feeds"  <?php @wp_automatic_opt_selected('Feeds',$camp_type) ?> >Feeds</option>
				<option  value="Multi"  <?php @wp_automatic_opt_selected('Multi',$camp_type) ?> >Multi-page scraper</option>
				<option  value="Single"  <?php @wp_automatic_opt_selected('Single',$camp_type) ?> >Single-page scraper</option>
				<option  value="Amazon"  <?php @wp_automatic_opt_selected('Amazon',$camp_type) ?> >Amazon</option>
				<option  value="Aliexpress"  <?php @wp_automatic_opt_selected('Aliexpress',$camp_type) ?> >AliExpress</option>
				<option  value="Clickbank"  <?php @wp_automatic_opt_selected('Clickbank',$camp_type) ?> >Clickbank</option>
				<option  value="Youtube"  <?php @wp_automatic_opt_selected('Youtube',$camp_type) ?> >Youtube</option>
				<option  value="Vimeo"  <?php @wp_automatic_opt_selected('Vimeo',$camp_type) ?> >Vimeo</option>
				<option  value="Flicker"  <?php @wp_automatic_opt_selected('Flicker',$camp_type) ?> >Flickr</option>
				<option  value="eBay"  <?php @wp_automatic_opt_selected('eBay',$camp_type) ?> >eBay</option>
				<option  value="Spintax"  <?php @wp_automatic_opt_selected('Spintax',$camp_type) ?> >Spintax</option>
				<option   data-sub-filter="#cg_fb_from"   value="Facebook"  <?php @wp_automatic_opt_selected('Facebook',$camp_type) ?> >Facebook</option>
				<option  value="Pinterest"  <?php @wp_automatic_opt_selected('Pinterest',$camp_type) ?> >Pinterest</option>
				<option  value="Instagram"  <?php @wp_automatic_opt_selected('Instagram',$camp_type) ?> >Instagram</option>
				<option  value="TikTok"  <?php @wp_automatic_opt_selected('TikTok',$camp_type) ?> >TikTok</option>
				<option  value="Twitter"  <?php @wp_automatic_opt_selected('Twitter',$camp_type) ?> >Twitter</option>
				<option  value="SoundCloud"  <?php @wp_automatic_opt_selected('SoundCloud',$camp_type) ?> >SoundCloud</option>
				<option  value="Craigslist"  <?php @wp_automatic_opt_selected('Craigslist',$camp_type) ?> >Craigslist</option>
				<option  data-sub-filter="#cg_iu_media" value="Itunes"  <?php @wp_automatic_opt_selected('Itunes',$camp_type) ?> >Itunes</option>
				<option  data-sub-filter="#cg_ev_filter" value="Envato"  <?php @wp_automatic_opt_selected('Envato',$camp_type) ?> >Envato</option>
				<option  value="DailyMotion"  <?php @wp_automatic_opt_selected('DailyMotion',$camp_type) ?> >DailyMotion</option>
				<option  value="Reddit"  <?php @wp_automatic_opt_selected('Reddit',$camp_type) ?> >Reddit</option>
				<option  value="Careerjet"  <?php @wp_automatic_opt_selected('Careerjet',$camp_type) ?> >Careerjet</option>
				<option  value="telegram"  <?php @wp_automatic_opt_selected('telegram',$camp_type) ?> >Telegram</option>
				<!--<option  value="Rumble"  <?php @wp_automatic_opt_selected('Rumble',$camp_type) ?> >Rumble</option>-->
				<option  value="Places"  <?php @wp_automatic_opt_selected('Places',$camp_type) ?> >Google Places</option>
				<option value="GoogleNews" <?php @wp_automatic_opt_selected('GoogleNews',$camp_type) ?> >Google News</option>
				<option value="BingNews" <?php @wp_automatic_opt_selected('BingNews',$camp_type) ?> >Bing News</option>
				
			</select>
			
			<?php if(count($raw_res) != 0)   echo 'Hint: you can not change type . campaign already published <input name="camp_type" type="hidden" value="'.$camp_type.'" />' ?> 
			
		</div>
          

          <div  id="field111-container" class="field typepart Articles gpt3 ArticlesBase Amazon Clickbank Youtube Vimeo Flicker  eBay f_100 Pinterest Instagram Twitter SoundCloud Itunes Envato DailyMotion Walmart Careerjet TikTok Aliexpress Places BingNews" style="display:none;" >
               <label for="field111">
                    Campaign keywords <i>(search for these keywords) (comma separated)</i>
               </label>
				<table style="width:100%">
					<tr>
						<td width="50%" style="position: absolute;">
					
					          <input autocomplete="off" id="search" type="text" onblur="if (this.value == '') {this.value = 'Search New Keyword...';}" onfocus="if (this.value == 'Search New Keyword...') {this.value = '';}" value="Search New Keyword..." style=" float: left;width: 90%;top:0"/> 
				        </td>
				        <td width="50%">
				               <textarea rows="5" cols="20" name="camp_keywords" id="field111" required="required"><?php   echo $camp_keywords  ?></textarea>	
				        </td>
			        </tr>
			          
		         </table>

				 
		         
		         <div class="typepart Twitter field f_100">
		         
		         
		         <table>
		         	<thead>
		         		<tr><td colspan="2"><strong><u>Twitter Keywords Examples..</u></strong></td></tr>
		         	</thead>
		         	<tbody>
				        <tr><td>from:alexiskold</td><td><i> Tweets from person “alexiskold”.</i></td></tr>
				        <tr><td>to:techcrunch</td><td><i>	sent to person “techcrunch”.</i></td></tr>
				        <tr><td>watching now</td><td><i>	containing both “watching” and “now”. This is the default operator.</i></td></tr>
				        <tr><td>“happy hour”</td><td><i>	containing the exact phrase “happy hour”.</i></td></tr>
						<tr><td>love OR hate</td><td><i>	containing either “love” or “hate” (or both).</i></td></tr>
						<tr><td>beer -root</td><td><i>	containing “beer” but not “root”.</i></td></tr>
						<tr><td>#haiku</td><td><i>	containing the hashtag “haiku”.</i></td></tr>
						<tr><td>@mashable</td><td><i>	referencing person “mashable”.</i></td></tr>
						<tr><td>movie -scary :)</td><td><i>	containing “movie”, but not “scary”, and with a positive attitude.</i></td></tr>
						<tr><td>flight :(</td><td><i>	containing “flight” and with a negative attitude.</i></td></tr>
						<tr><td>traffic ?</td><td><i>	containing “traffic” and asking a question.</i></td></tr>
						<tr><td>hilarious has:links</td><td><i>	containing “hilarious” and linking to URL.</i></td></tr>
						<tr><td>puppy has:media	</td><td><i>	containing “puppy” and an image or video.</i></td></tr>
						<tr><td>puppy has:images</td><td><i>	containing “puppy” and links identified as photos, including third parties such as Instagram.</i></td></tr>
						<tr><td>puppy has:videos	</td><td><i>	containing “puppy” and a video</i></td></tr>
					</tbody>
		        </table>
		         
		         </div>
		         
		         
		                  <div id="field1zzxzz-container" class="field f_100">
					              
					               
					               <div class="option clearfix">
                    
								        <input data-controls="TAG_c" name="camp_options[]" value="OPT_TAG" type="checkbox">
								        <span class="option-title">
												Set the keywords as post tags
								        </span>
								        <br>
								        
								        <div id="TAG_c" class="field f_100">
								        	
								        		<div class="option clearfix">
												<input name="camp_options[]" value="OPT_TAG_KEYONLY" type="checkbox"> 
												<span class="option-title">Only set the used keyword that was used to fetch the posted item</span>
											</div>
								         
								        </div>
								                    
								</div>
					               
					               <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_ROTATE" type="checkbox">
					                    <span class="option-title">
												Rotate keywords ( Tick to post for different keyword each time )
					                    </span>  
					                    <br>
					               </div>
					               
							 </div>
							  
		         
            </div>


          <div id="field5-container" class="field f_100 ">
               <label for="field5">
                    Maximum number of posts to post <i>(Campaign will stop after reaching)</i>
               </label>
               <input value="<?php   echo $camp_post_every   ?>" max="1000000" min="0" name="camp_post_every" id="field1" required="required" class="ttw-range range"
               type="range">
          </div>


		<!-- BingNews -->
		<div class="field typepart BingNews" style="display:none;">
			

			<!-- freshness -->
			<div class="field f_100">
				<label for="field_bing_freshness">
					Freshness
				</label>
				<select name="cg_bing_freshness" id="field_bing_freshness">

					<option value="Day" <?php @wp_automatic_opt_selected('Day',$camp_general['cg_bing_freshness']) ?>>Day</option>
					<option value="Week" <?php @wp_automatic_opt_selected('Week',$camp_general['cg_bing_freshness']) ?>>Week</option>
					<option value="Month" <?php @wp_automatic_opt_selected('Month',$camp_general['cg_bing_freshness']) ?>>Month</option>
				</select>
			</div>

			<!-- sortBy -->
			<div class="field f_100">
				<label for="field_bing_sortBy">
					Sort By
				</label>
				<select name="cg_bing_sortBy" id="field_bing_sortBy">

					<option value="Relevance" <?php @wp_automatic_opt_selected('Relevance',$camp_general['cg_bing_sortBy']) ?>>Relevance</option>
					<option value="Date" <?php @wp_automatic_opt_selected('Date',$camp_general['cg_bing_sortBy']) ?>>Date</option>
				</select>
			</div>

			<!-- cc input field with description -->
			<div class="field f_100">
				<label for="field_bing_cc">
					Country Code
			</label>
				<input type="text" name="cg_bing_cc" id="field_bing_cc" value="<?php echo  isset($camp_general['cg_bing_cc']) ?  $camp_general['cg_bing_cc'] : '' ?>">
				<div class="description">Country code to search in. for example: US check <a href="https://learn.microsoft.com/en-us/bing/search-apis/bing-news-search/reference/market-codes#country-codes">Codes List</a></div>
			</div>

			<!-- category input field with description -->
			<div class="field f_100">
				<label for="field_bing_cat">
					Category
				</label>
				<input type="text" name="cg_bing_cat" id="field_bing_cat" value="<?php echo  isset($camp_general['cg_bing_cat']) ?  $camp_general['cg_bing_cat'] : '' ?>">
				<div class="description">Category to search in. for example: Sports check <a href="https://learn.microsoft.com/en-us/bing/search-apis/bing-news-search/reference/query-parameters#news-categories-by-market">Codes List</a></div>
			</div>

			<!-- count input field with description -->
			<div class="field f_100">
				<label for="field_bing_count">
					Count
				</label>
				<input type="number" name="cg_bing_count" id="field_bing_count" value="<?php echo  isset($camp_general['cg_bing_count']) ?  $camp_general['cg_bing_count'] : '' ?>">
				<div class="description">Number of posts to fetch Maximum is 100</div>
			</div>

			<!-- setLang input field with description -->
			<div class="field f_100">
				<label for="field_bing_setLang">
					Set Language
				</label>
				<input type="text" name="cg_bing_setLang" id="field_bing_setLang" value="<?php echo  isset($camp_general['cg_bing_setLang']) ?  $camp_general['cg_bing_setLang'] : '' ?>">
				<div class="description">Language to search in. for example: en check <a href="https://learn.microsoft.com/en-us/bing/search-apis/bing-news-search/reference/market-codes#bing-supported-language-codes">Codes List</a></div>
			</div>
		
		</div>
          
          
         <!-- Multi-Page Scraper -->
		 
		 		<div  class="field  typepart Multi" style="display:none;" >

		 		<div id="wp_multi_extraction_part">
		 		
		 		<div class="field f_100">
			 		<label>Items list page URL</label> <input value="<?php  echo @$camp_general['cg_ml_source']   ?>" name="cg_ml_source"  data-visual-selector-Multi type="text">	
			 		<div class="description">URL of a webpage that contains the list of items to post from ( homepage for example )</div> 
				</div>

				 <div class="field f_100">		 		
			 		
			 		<label>Link to a single item extraction method</label>
					<select class="select_control_div" name="cg_ml_lnk_method">
					
						<option value="visual" <?php @wp_automatic_opt_selected('visual',$camp_general['cg_ml_lnk_method']) ?>>Visual Selector</option>
						<option value="css" <?php @wp_automatic_opt_selected('css',$camp_general['cg_ml_lnk_method']) ?>>CSS ID/Class  or xPath</option>
						<option value="regex" <?php @wp_automatic_opt_selected('regex',$camp_general['cg_ml_lnk_method']) ?>>Regular Expression</option>
						
					</select>


					<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_lnk_method cg_ml_lnk_method_css field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_lnk_css = @$camp_general['cg_ml_lnk_css'];
 		 		 			$cg_ml_lnk_css_type = @$camp_general['cg_ml_lnk_css_type'];
 		 		 			$cg_ml_lnk_css_size = @$camp_general['cg_ml_lnk_css_size'];
 		 		 			$cg_ml_lnk_css_wrap = @$camp_general['cg_ml_lnk_css_wrap'];
 		 		 			
 		 		 			if(!is_array($cg_ml_lnk_css)) $cg_ml_lnk_css = array('');
 		 		 			if(!is_array($cg_ml_lnk_css_type)) $cg_ml_lnk_css_type= array('id');
 		 		 			if(!is_array($cg_ml_lnk_css_size)) $cg_ml_lnk_css_size= array('all');
 		 		 			if(!is_array($cg_ml_lnk_css_wrap)) $cg_ml_lnk_css_wrap= array('outer');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_lnk_css as $cg_ml_lnk_css_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td   style="min-width:65px;padding-right:10px">
									<select class="no-unify"   name="cg_ml_lnk_css_type[]">
										 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_ml_lnk_css_type[$i]) ?>>ID</option>
										 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_ml_lnk_css_type[$i]) ?>>Class</option>
										 <option value="xpath" <?php @wp_automatic_opt_selected('xpath',$cg_ml_lnk_css_type[$i]) ?>>XPath</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:150px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo  wp_automatic_htmlentities($cg_ml_lnk_css_rule ,ENT_COMPAT, 'UTF-8' )  ?>" name="cg_ml_lnk_css[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td  style="padding-right:8px;" >
 		 		 					 <select class="no-unify"   name="cg_ml_lnk_css_size[]">
										 <option value="all" <?php @wp_automatic_opt_selected('all',$cg_ml_lnk_css_size[$i]) ?>>All</option>
										 <option value="single" <?php @wp_automatic_opt_selected('single', $cg_ml_lnk_css_size[$i]) ?>>Single</option>
									</select>
 		 		 				</td>

 		 		 				<td>
 		 		 					 <select class="no-unify"   name="cg_ml_lnk_css_wrap[]">
										 <option value="outer" <?php @wp_automatic_opt_selected('outer',$cg_ml_lnk_css_wrap[$i]) ?>>Outer</option>
										 <option value="inner" <?php @wp_automatic_opt_selected('inner', $cg_ml_lnk_css_wrap[$i]) ?>>Inner</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>
					
				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_lnk_method cg_ml_lnk_method_regex field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_lnk_regex = @$camp_general['cg_ml_lnk_regex'];
 		 		 			if(!is_array($cg_ml_lnk_regex)) $cg_ml_lnk_regex = array('');
 		 		 			 
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_lnk_regex as $cg_ml_lnk_regex_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlspecialchars(  $cg_ml_lnk_regex_rule )  ?>" name="cg_ml_lnk_regex[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Remove title"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	

				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_lnk_method cg_ml_lnk_method_visual field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_lnk_visual = @$camp_general['cg_ml_lnk_visual'];
 		 		 			if(!is_array($cg_ml_lnk_visual)) $cg_ml_lnk_visual = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_lnk_visual as $cg_ml_lnk_visual_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_ml_lnk_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_ml_lnk_visual[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Locate" data-popup-field-name  class="dashicons-before dashicons-color-picker  visual_selector" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	

	
			<div class="field f_100" >
			
				<div class="option clearfix">
	                    <input data-controls="apify_wait_for_div" name="camp_options[]"   value="OPT_FEED_APIFY2" type="checkbox">
	                    <span class="option-title">
								Load the items list page using APIFY.COM <br>(only if this site requires JavaScript)
	                    </span>
	                    <br>
						<div class="field f_100" id="apify_wait_for_div">
						
								<label for ="apify_wait_for">Wait for how many milliseconds after loading the site: [Optional]</label>
								<input name="cg_apify_wait_for" value="<?php  echo @$camp_general['cg_apify_wait_for']  ?>" placeholder="0" type="text">
								<div class="description">
									Wait for how many milliseconds after loading the site. This is useful if the site loads some content after a few seconds. for example: 15000 (waits for 15 seconds)
								</div>
					
						</div>
	             </div>
	             
			
				<div class="option  clearfix">
	                    
                    <input name="camp_options[]"  data-controls="wp_auatomatic_multi_url_search" value="OPT_MULTI_SEARCH_REPLACE" type="checkbox">
                    <span class="option-title">
							Search and replace on the extracted link
                    </span>
                    <br>
                    
		            <div id="wp_auatomatic_multi_url_search" class="field f_100">
		           		
		           		 <div class="description">
					    	
					    	 <label for="field6">
			                    Rules (search|replace) (one per line)
				             </label>
				               
				             <textarea name="cg_multi_search_replace" ><?php    echo wp_automatic_htmlentities(@$camp_general['cg_multi_search_replace'],ENT_COMPAT, 'UTF-8')   ?></textarea>
					    	
					    	 <i>
					    	example: "topics|articles" without quotes
					    	</i>
					    	 
					    	
				    	</div>
		           		 
				    </div>
				     
				</div>
				
				
				
				
				</div>
				
				<div class="field f_100" >
			
				<div class="option  clearfix">
	                    
                    <input name="camp_options[]"  data-controls="wp_auatomatic_multi_url_append" value="OPT_MULTI_APPEND" type="checkbox">
                    <span class="option-title">
							Append something to the extracted post URL
                    </span>
                    <br>
                    
		            <div id="wp_auatomatic_multi_url_append" class="field f_100">
		           		
		           		
					    	
					    	 <label>Text to append</label> <input value="<?php  echo @$camp_general['cg_ml_append']   ?>" name="cg_ml_append"   >
					    	 <div class="description">
					    	 <i>
					    	example: "?page=all" without quotes
					    	</i>
					    	 
					    	
				    	</div>
		           		 
				    </div>
				     
				</div>
				
				</div>
				

			</div>
			
			
			<div class="field f_100">		 		
			 		<label>Next page URL extraction method</label>
					<select class="select_control_div" name="cg_ml_cnt_method">
					
					 	<option value="auto" <?php @wp_automatic_opt_selected('auto',$camp_general['cg_ml_cnt_method']) ?>>Disabled (No pagination)</option>
						<option value="visual" <?php @wp_automatic_opt_selected('visual',$camp_general['cg_ml_cnt_method']) ?>>Visual Selector</option>
						<option value="css" <?php @wp_automatic_opt_selected('css',$camp_general['cg_ml_cnt_method']) ?>>CSS ID/Class  or xPath</option>
						<option value="regex" <?php @wp_automatic_opt_selected('regex',$camp_general['cg_ml_cnt_method']) ?>>Regular Expression</option>
						<option value="fixed" <?php @wp_automatic_opt_selected('fixed',$camp_general['cg_ml_cnt_method']) ?>>Increment page number (Fixed pattern)</option>
						<option value="infinite" <?php @wp_automatic_opt_selected('infinite',$camp_general['cg_ml_cnt_method']) ?>>Infinite scroll</option>
						
						
					</select>


					<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_cnt_method cg_ml_cnt_method_css field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_cnt_css = @$camp_general['cg_ml_cnt_css'];
 		 		 			$cg_ml_cnt_css_type = @$camp_general['cg_ml_cnt_css_type'];
 		 		 			$cg_ml_cnt_css_size = @$camp_general['cg_ml_cnt_css_size'];
 		 		 			$cg_ml_cnt_css_wrap = @$camp_general['cg_ml_cnt_css_wrap'];
 		 		 			
 		 		 			if(!is_array($cg_ml_cnt_css)) $cg_ml_cnt_css = array('');
 		 		 			if(!is_array($cg_ml_cnt_css_type)) $cg_ml_cnt_css_type= array('id');
 		 		 			if(!is_array($cg_ml_cnt_css_size)) $cg_ml_cnt_css_size= array('all');
 		 		 			if(!is_array($cg_ml_cnt_css_wrap)) $cg_ml_cnt_css_wrap= array('outer');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_cnt_css as $cg_ml_cnt_css_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td   style="min-width:65px;padding-right:10px">
									<select class="no-unify"   name="cg_ml_cnt_css_type[]">
										 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_ml_cnt_css_type[$i]) ?>>ID</option>
										 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_ml_cnt_css_type[$i]) ?>>Class</option>
										 <option value="xpath" <?php @wp_automatic_opt_selected('xpath',$cg_ml_cnt_css_type[$i]) ?>>XPath</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:150px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities(  $cg_ml_cnt_css_rule , ENT_COMPAT, 'UTF-8')  ?>" name="cg_ml_cnt_css[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td style="padding-right:8px;">
 		 		 					 <select class="no-unify"   name="cg_ml_cnt_css_size[]">
										 <option value="all" <?php @wp_automatic_opt_selected('all',$cg_ml_cnt_css_size[$i]) ?>>All</option>
										 <option value="single" <?php @wp_automatic_opt_selected('single', $cg_ml_cnt_css_size[$i]) ?>>Single</option>
									</select>
 		 		 				</td>

 		 		 				<td>
 		 		 					 <select class="no-unify"   name="cg_ml_cnt_css_wrap[]">
										 <option value="outer" <?php @wp_automatic_opt_selected('outer',$cg_ml_cnt_css_wrap[$i]) ?>>Outer</option>
										 <option value="inner" <?php @wp_automatic_opt_selected('inner', $cg_ml_cnt_css_wrap[$i]) ?>>Inner</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>
					
				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_cnt_method cg_ml_cnt_method_regex field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_cnt_regex = @$camp_general['cg_ml_cnt_regex'];
 		 		 			if(!is_array($cg_ml_cnt_regex)) $cg_ml_cnt_regex = array('');
 		 		 			 
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_cnt_regex as $cg_ml_cnt_regex_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo  wp_automatic_htmlentities( $cg_ml_cnt_regex_rule ,ENT_COMPAT, 'UTF-8')    ?>" name="cg_ml_cnt_regex[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Remove title"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	

				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_cnt_method cg_ml_cnt_method_fixed field f_100">
 		 		 		
 					<div class="field f_100">
			 			<label>Second page URL</label> <input   value="<?php  echo @$camp_general['cg_ml_page_2_url']   ?>" name="cg_ml_page_2_url" type="text">	 
						<div class="description">URL of the next page that contains the items of page number 2<br>e.g: http://example.com/page/2/<br><br>*If this URL contains any numbers than 2, Please replace the page number by [page_number] so it will be http://example123.com/page/[page_number]/</div>
					</div>
					
					<div class="field f_100">
			 			<label>Increment step: </label> <input   value="<?php  echo @$camp_general['cg_ml_page_increment']   ?>" name="cg_ml_page_increment" placeholder="1" type="text" >	 
						<div class="description">Number to add to the current page number to get the next page number</div>
					</div>
					
						<div class="field f_100">
			 			<label>Base number(First page number)    </label> <input   value="<?php  echo @$camp_general['cg_ml_page_base']   ?>" name="cg_ml_page_base" placeholder="1" type="text" >	 
						<div class="description">Used to calculate next pages where Base number  + Step = Second page number</div>
					</div>
 		 		 		
				</div>
				
				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_cnt_method cg_ml_cnt_method_infinite field f_100">
 		 		 		
 					<div class="field f_100">
			 			<label>Page HTML after scroll</label> <textarea name="cg_ml_infinite"><?php echo wp_automatic_htmlentities( @$camp_general['cg_ml_infinite'],ENT_COMPAT, 'UTF-8')     ?></textarea> 	 
						<div class="description">Click the scroll button to get as many posts as desired then paste the content of the page above. Check <a target="_blank" href="http://valvepress.com/how-to-import-from-sites-with-infinite-scroll-using-wordpress-automatic-plugin/">this tutorial</a> for more.</div>
					</div>
					
					 
 		 		 		
				</div>		

				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_cnt_method cg_ml_cnt_method_visual field f_100">
 		 		 		
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_cnt_visual = @$camp_general['cg_ml_cnt_visual'];
 		 		 			if(!is_array($cg_ml_cnt_visual)) $cg_ml_cnt_visual = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_cnt_visual as $cg_ml_cnt_visual_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_ml_cnt_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_ml_cnt_visual[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Locate"  data-popup-field-name  class="dashicons-before dashicons-color-picker  visual_selector" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	


 		 		</div>
			
			
			</div><!-- extraction_part -->
			
			
			<div class="field f_100">
				
				<div class="option  clearfix">
	                    
                    <input name="camp_options[]" data-controls-r="wp_multi_extraction_part"  data-controls="wp_auatomatic_multi_fixed_list" value="OPT_MULTI_FIXED_LIST" type="checkbox">
                    <span class="option-title">
							Post from a list of posts URLs Instead (Fixed list)
                    </span>
                    <br>
                    
		            <div id="wp_auatomatic_multi_fixed_list" class="field f_100">
		           		
		           		 <div class="description">
					    	
					    	 <label for="field6">
			                   List of posts URLs ( one post URL per line )
				             </label>
				               
				             <textarea name="cg_multi_posts_list" ><?php    echo wp_automatic_htmlentities(@$camp_general['cg_multi_posts_list'],ENT_COMPAT, 'UTF-8')   ?></textarea>
					    	
					    	 <i>
					    	example:<br> 
					    	https://example.com/post-1<br>
					    	https://example.com/post-2
					    	</i>
					    	 
					    	
				    	</div>
		           		 
				    </div>
				     
				</div>
			
			</div>
			
			<div class="field f_100">		 		
			 		<label>Caching</label>
					<select name="cg_ml_cache">
					
					 	<option value="enabled" <?php @wp_automatic_opt_selected('enabled',$camp_general['cg_ml_cache']) ?>>Enabled</option>
					 	<option value="disabled" <?php @wp_automatic_opt_selected('disabled',$camp_general['cg_ml_cache']) ?>>Disabled (Pagination will not work)</option>

					</select>
					
			</div>



 
		  </div>	
		 
		 <!-- / Multi-Page Scraper --> 
             
          		 <!--  Single Scraper -->
		  <div  class="field  typepart Single" style="display:none;" >

		 		<div class="field f_100">
			 		<label>Source URL</label> <input data-visual-selector-Single value="<?php  echo @$camp_general['cg_sn_source']   ?>" name="cg_sn_source" type="text">	 
				</div>

		 		<div class="field f_100">		 		
			 		<label>Title extraction method</label>
					<select class="select_control_div" name="cg_sn_ttl_method">
					
					 	<option value="auto" <?php @wp_automatic_opt_selected('auto',$camp_general['cg_sn_ttl_method']) ?>>Auto detect</option>
						<option value="visual" <?php @wp_automatic_opt_selected('visual',$camp_general['cg_sn_ttl_method']) ?>>Visual Selector</option>
						<option value="css" <?php @wp_automatic_opt_selected('css',$camp_general['cg_sn_ttl_method']) ?>>CSS ID/Class  or xPath</option>
						<option value="regex" <?php @wp_automatic_opt_selected('regex',$camp_general['cg_sn_ttl_method']) ?>>Regular Expression</option>
					</select>


					<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_sn_ttl_method cg_sn_ttl_method_css field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_sn_css = @$camp_general['cg_sn_css'];
 		 		  			$cg_sn_css_type = @$camp_general['cg_sn_css_type'];
 		 		 			$cg_sn_css_size = @$camp_general['cg_sn_css_size'];
 		 		 			$cg_sn_css_wrap = @$camp_general['cg_sn_css_wrap'];
 		 		 			
 		 		 			if(!is_array($cg_sn_css)) $cg_sn_css = array('');
 		 		 			if(!is_array($cg_sn_css_type)) $cg_sn_css_type= array('id');
 		 		 			if(!is_array($cg_sn_css_size)) $cg_sn_css_size= array('all');
 		 		 			if(!is_array($cg_sn_css_wrap)) $cg_sn_css_wrap= array('outer');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_sn_css as $cg_sn_css_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td   style="min-width:65px;padding-right:10px">
									<select class="no-unify"   name="cg_sn_css_type[]">
										 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_sn_css_type[$i]) ?>>ID</option>
										 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_sn_css_type[$i]) ?>>Class</option>
										 <option value="xpath" <?php @wp_automatic_opt_selected('xpath',$cg_sn_css_type[$i]) ?>>XPath</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:150px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo  wp_automatic_htmlentities($cg_sn_css_rule,ENT_COMPAT, 'UTF-8')   ?>" name="cg_sn_css[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td style="padding-right:8px;">
 		 		 					 <select class="no-unify"   name="cg_sn_css_size[]">
										 <option value="all" <?php @wp_automatic_opt_selected('all',$cg_sn_css_size[$i]) ?>>All</option>
										 <option value="single" <?php @wp_automatic_opt_selected('single', $cg_sn_css_size[$i]) ?>>Single</option>
									</select>
 		 		 				</td>

 		 		 				<td>
 		 		 					 <select class="no-unify"   name="cg_sn_css_wrap[]">
										 <option value="outer" <?php @wp_automatic_opt_selected('outer',$cg_sn_css_wrap[$i]) ?>>Outer</option>
										 <option value="inner" <?php @wp_automatic_opt_selected('inner', $cg_sn_css_wrap[$i]) ?>>Inner</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
 		 		 					 
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
 		 		 					 
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>
					
				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_sn_ttl_method cg_sn_ttl_method_regex field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_sn_regex = @$camp_general['cg_sn_regex'];
 		 		 			if(!is_array($cg_sn_regex)) $cg_sn_regex = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_sn_regex as $cg_sn_regex_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_sn_regex_rule ,ENT_COMPAT, 'UTF-8')  ?>" name="cg_sn_regex[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	

				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_sn_ttl_method cg_sn_ttl_method_visual field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_sn_visual = @$camp_general['cg_sn_visual'];
 		 		 			if(!is_array($cg_sn_visual)) $cg_sn_visual = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_sn_visual as $cg_sn_visual_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities($cg_sn_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_sn_visual[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Locate" data-popup-field-name class="dashicons-before dashicons-color-picker  visual_selector" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		</table>
				</div>	


 		 		</div>

				<div class="field f_100">		 		
			 		<label>Content extraction method</label>
					<select class="select_control_div" name="cg_sn_cnt_method">
					
					 	<option value="auto" <?php @wp_automatic_opt_selected('auto',$camp_general['cg_sn_cnt_method']) ?>>Auto detect</option>
						<option value="visual" <?php @wp_automatic_opt_selected('visual',$camp_general['cg_sn_cnt_method']) ?>>Visual Selector</option>
						<option value="css" <?php @wp_automatic_opt_selected('css',$camp_general['cg_sn_cnt_method']) ?>>CSS ID/Class  or xPath</option>
						<option value="regex" <?php @wp_automatic_opt_selected('regex',$camp_general['cg_sn_cnt_method']) ?>>Regular Expression</option>
						
					</select>


					<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_sn_cnt_method cg_sn_cnt_method_css field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_sn_cnt_css = @$camp_general['cg_sn_cnt_css'];
 		 		 			$cg_sn_cnt_css_type = @$camp_general['cg_sn_cnt_css_type'];
 		 		 			$cg_sn_cnt_css_size = @$camp_general['cg_sn_cnt_css_size'];
 		 		 			$cg_sn_cnt_css_wrap = @$camp_general['cg_sn_cnt_css_wrap'];
 		 		 			
 		 		 			if(!is_array($cg_sn_cnt_css)) $cg_sn_cnt_css = array('');
 		 		 			if(!is_array($cg_sn_cnt_css_type)) $cg_sn_cnt_css_type= array('id');
 		 		 			if(!is_array($cg_sn_cnt_css_size)) $cg_sn_cnt_css_size= array('all');
 		 		 			if(!is_array($cg_sn_cnt_css_wrap)) $cg_sn_cnt_css_wrap= array('outer');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_sn_cnt_css as $cg_sn_cnt_css_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td   style="min-width:65px;padding-right:10px">
									<select class="no-unify"   name="cg_sn_cnt_css_type[]">
										 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_sn_cnt_css_type[$i]) ?>>ID</option>
										 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_sn_cnt_css_type[$i]) ?>>Class</option>
										 <option value="xpath" <?php @wp_automatic_opt_selected('xpath',$cg_sn_cnt_css_type[$i]) ?>>XPath</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:150px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo   wp_automatic_htmlentities($cg_sn_cnt_css_rule,ENT_COMPAT, 'UTF-8')   ?>" name="cg_sn_cnt_css[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td style="padding-right:8px;">
 		 		 					 <select class="no-unify"   name="cg_sn_cnt_css_size[]">
										 <option value="all" <?php @wp_automatic_opt_selected('all',$cg_sn_cnt_css_size[$i]) ?>>All</option>
										 <option value="single" <?php @wp_automatic_opt_selected('single', $cg_sn_cnt_css_size[$i]) ?>>Single</option>
									</select>
 		 		 				</td>

 		 		 				<td>
 		 		 					 <select class="no-unify"   name="cg_sn_cnt_css_wrap[]">
										 <option value="outer" <?php @wp_automatic_opt_selected('outer',$cg_sn_cnt_css_wrap[$i]) ?>>Outer</option>
										 <option value="inner" <?php @wp_automatic_opt_selected('inner', $cg_sn_cnt_css_wrap[$i]) ?>>Inner</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>
					
				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_sn_cnt_method cg_sn_cnt_method_regex field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_sn_cnt_regex = @$camp_general['cg_sn_cnt_regex'];
 		 		 			if(!is_array($cg_sn_cnt_regex)) $cg_sn_cnt_regex = array('');
 		 		 			 
 		 		 			$i=0;
 		 		 			foreach ($cg_sn_cnt_regex as $cg_sn_cnt_regex_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_sn_cnt_regex_rule,ENT_COMPAT, 'UTF-8')    ?>" name="cg_sn_cnt_regex[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Remove title"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	

				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_sn_cnt_method cg_sn_cnt_method_visual field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_sn_cnt_visual = @$camp_general['cg_sn_cnt_visual'];
 		 		 			if(!is_array($cg_sn_cnt_visual)) $cg_sn_cnt_visual = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_sn_cnt_visual as $cg_sn_cnt_visual_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_sn_cnt_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_sn_cnt_visual[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Locate"  data-popup-field-name class="dashicons-before dashicons-color-picker  visual_selector" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	


 		 		</div>
 		 		
				<div  class="field f_100" >	
					<div class="option clearfix">
	                    
					        <input data-controls="COOKIE_c" name="camp_options[]" value="OPT_COOKIE" type="checkbox">
					        <span class="option-title">
									Set cookies
					        </span>
					        <br>
					        
					        <div id="COOKIE_c" class="field f_100">
					         	<label>Cookie</label> <input value="<?php  echo @$camp_general['cg_sn_cookie']   ?>" name="cg_sn_cookie" type="text">
					         	<div class="description">cookie1Name=cookie1Value;cookie2Name=cookie2value;cookie3Name=cookie3Value</div>
					       	</div>
	                    
					</div>
				</div>
				
				<div  class="field f_100" >	
				
					<label>After content extraction</label>
					<select name="cg_sn_after">
						
						<option value="update" <?php @wp_automatic_opt_selected('update',$camp_general['cg_sn_after']) ?>>Update previous posted post if the content got changed</option>
						<option value="new" <?php @wp_automatic_opt_selected('new',$camp_general['cg_sn_after']) ?>>Create a new post if content got changed and leave previous post</option>
						<option value="deletechange" <?php @wp_automatic_opt_selected('deletechange',$camp_general['cg_sn_after']) ?>>Delete previous post and create a new one if content got changed</option>
						<option value="justnew" <?php @wp_automatic_opt_selected('justnew',$camp_general['cg_sn_after']) ?>>Create a new post each time</option>
						<option value="delete" <?php @wp_automatic_opt_selected('delete',$camp_general['cg_sn_after']) ?>>Delete previous post and create a new one each time</option>
	
					</select>
				</div>
 
		  </div>
		 
		 <!-- /Single Scraper -->    
              
		<!-- Feed part -->
		<div  class="field f_100 typepart Feeds GoogleNews" style="display:none;" >			               
		               <label for="field11">
		                    Feeds to post from <i>(one feed link per line)</i>
		               </label>
		               <textarea data-visual-selector-Feeds rows="5" cols="20" name="feeds" id="field11"><?php if(isset($feeds))  echo $feeds ?></textarea>
               			              
               			          <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_ROTATE_FEEDS" type="checkbox">
					                    <span class="option-title">
												Rotate Feeds ( Tick to post for a different feed each time )
					                    </span>  
					                    <br>
					               </div> 
		</div>
               						
         <div id="feed_postcont" class="field f_100 typepart Feeds Multi BingNews GoogleNews" >
               
               	<div>		 		
			 		
			 		<label>Single item title extraction method</label>
					<select class="select_control_div" name="cg_ml_ttl_method">
					
					 	<option value="auto" <?php @wp_automatic_opt_selected('auto',$camp_general['cg_ml_ttl_method']) ?>>Auto detect</option>
						<option value="visual" <?php @wp_automatic_opt_selected('visual',$camp_general['cg_ml_ttl_method']) ?>>Visual Selector</option>
						<option value="css" <?php @wp_automatic_opt_selected('css',$camp_general['cg_ml_ttl_method']) ?>>CSS ID/Class  or xPath</option>
						<option value="regex" <?php @wp_automatic_opt_selected('regex',$camp_general['cg_ml_ttl_method']) ?>>Regular Expression</option>
					</select>


					<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_ttl_method cg_ml_ttl_method_css field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_css = @$camp_general['cg_ml_css'];
 		 		  			$cg_ml_css_type = @$camp_general['cg_ml_css_type'];
 		 		 			$cg_ml_css_size = @$camp_general['cg_ml_css_size'];
 		 		 			$cg_ml_css_wrap = @$camp_general['cg_ml_css_wrap'];
 		 		 			
 		 		 			if(!is_array($cg_ml_css)) $cg_ml_css = array('');
 		 		 			if(!is_array($cg_ml_css_type)) $cg_ml_css_type= array('id');
 		 		 			if(!is_array($cg_ml_css_size)) $cg_ml_css_size= array('all');
 		 		 			if(!is_array($cg_ml_css_wrap)) $cg_ml_css_wrap= array('outer');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_css as $cg_ml_css_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td style="min-width:65px;padding-right:10px">
									<select class="no-unify"   name="cg_ml_css_type[]">
										 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_ml_css_type[$i]) ?>>ID</option>
										 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_ml_css_type[$i]) ?>>Class</option>
										 <option value="xpath" <?php @wp_automatic_opt_selected('xpath',$cg_ml_css_type[$i]) ?>>XPath</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:150px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo  wp_automatic_htmlentities($cg_ml_css_rule,ENT_COMPAT, 'UTF-8')   ?>" name="cg_ml_css[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td style="padding-right:8px;">
 		 		 					 <select class="no-unify"   name="cg_ml_css_size[]">
										 <option value="all" <?php @wp_automatic_opt_selected('all',$cg_ml_css_size[$i]) ?>>All</option>
										 <option value="single" <?php @wp_automatic_opt_selected('single', $cg_ml_css_size[$i]) ?>>Single</option>
									</select>
 		 		 				</td>

 		 		 				<td>
 		 		 					 <select class="no-unify"   name="cg_ml_css_wrap[]">
										 <option value="outer" <?php @wp_automatic_opt_selected('outer',$cg_ml_css_wrap[$i]) ?>>Outer</option>
										 <option value="inner" <?php @wp_automatic_opt_selected('inner', $cg_ml_css_wrap[$i]) ?>>Inner</option>
									</select>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
 		 		 					 
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
 		 		 					 
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>
					
				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_ttl_method cg_ml_ttl_method_regex field f_100">
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_regex = @$camp_general['cg_ml_regex'];
 		 		 			if(!is_array($cg_ml_regex)) $cg_ml_regex = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_regex as $cg_ml_regex_rule){
 		 		 			
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_ml_regex_rule ,ENT_COMPAT, 'UTF-8')  ?>" name="cg_ml_regex[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		
 		 		 		</table>
 		 		 		
				</div>	

				<div style="margin-top:0;padding-top:0;padding-bottom:20px" class="select_control_div_div cg_ml_ttl_method cg_ml_ttl_method_visual field f_100">
 		 		 		
 		 		 		<div  class="field typepart Multi" >
					 		<label>URL of a single post</label> <input value="<?php  echo @$camp_general['cg_ml_example']   ?>" name="cg_ml_example" data-fill-this-field-after-picking-first-link  data-visual-selector-Multi-example type="text">	
					 		<div class="description">This URL will be used for the visual selector so add a URL for an example post</div> 
						</div>
		 		 		 		
 		 		 		
 		 		 		<table>
 		 		 			
 		 		 			<?php 
 		 		 				 
 		 		 			$cg_ml_visual = @$camp_general['cg_ml_visual'];
 		 		 			if(!is_array($cg_ml_visual)) $cg_ml_visual = array('');
 		 		 			
 		 		 			$i=0;
 		 		 			foreach ($cg_ml_visual as $cg_ml_visual_rule){
 		 		 			?>
 		 		 			
 		 		 			<tr>
 		 		 				
 		 		 				<td  style="padding-right:10px;width:300px" >
 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities($cg_ml_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_ml_visual[]" type="text">
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 					 <button  title="Locate" data-popup-field-name="cg_ml_example"  class="dashicons-before dashicons-color-picker  visual_selector" ></button>
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 				
 		 		 				<td    style="padding-left:15px;padding-top:8px;" >
 		 		 				</td>
 		 		 			
 		 		 			</tr>
 		 		 		
 		 		 		<?php  $i++ ; } ?>
 		 		 		
 		 		 		</table>
				</div>	


 		 		</div>
                
		          
			                        <div>
				
										<label>Content extraction method</label>
										 <select class="select_control_div" name="cg_feed_extraction_method" >
											
											<option value="auto" <?php @wp_automatic_opt_selected('auto',$camp_general['cg_feed_extraction_method']) ?>>Auto detect full content   </option>
										 	<option class="typepart Feeds" value="summary" <?php @wp_automatic_opt_selected('summary',$camp_general['cg_feed_extraction_method']) ?>>Post content from feed as-is   </option>
											<option value="visual" <?php @wp_automatic_opt_selected('visual',$camp_general['cg_feed_extraction_method']) ?>>Extract content using visual selector</option>
											<option value="css" <?php @wp_automatic_opt_selected('css',$camp_general['cg_feed_extraction_method']) ?>>Extract content by CSS selector (ID, Class or XPath)   </option>
											<option value="regex" <?php @wp_automatic_opt_selected('regex',$camp_general['cg_feed_extraction_method']) ?>>Extract content using REGEX</option>
													
										
										</select>
				
						               <div class="option clearfix">
						                    
								            <div id="feed_custom_c" class="select_control_div_div cg_feed_extraction_method cg_feed_extraction_method_css field f_100">
								            
								               <label for="field6">
								                    Division ID/Class or XPath (ADVANCED) 
								               </label>
								                
								                <table>
								                	
								                	 	<?php 
 		 		 				 
								                	 	$cg_feed_custom_id= @$camp_general['cg_feed_custom_id'];
						 		 		 			$cg_custom_selector= @$camp_general['cg_custom_selector'];
						 		 		 			$cg_feed_css_size = @$camp_general['cg_feed_css_size'];
						 		 		 			$cg_feed_css_wrap = @$camp_general['cg_feed_css_wrap'];
						 		 		 			
						 		 		 			if(!is_array($cg_feed_custom_id)) $cg_feed_custom_id= array('');
						 		 		 			if(!is_array($cg_custom_selector)) $cg_custom_selector= array('id');
						 		 		 			if(!is_array($cg_feed_css_size)) $cg_feed_css_size= array('all');
						 		 		 			if(!is_array($cg_feed_css_wrap)) $cg_feed_css_wrap= array('outer');
						 		 		 			
						 		 		 			$i=0;
						 		 		 			foreach ($cg_feed_custom_id as $cg_feed_custom_id_single){
						 		 		 				
						 		 		 				if(  true ) {
						 		 		 			?>
						 		 		 			
						 		 		 			<tr>
						 		 		 				
						 		 		 				<td   style="min-width:65px;padding-right:10px">
															<select class="no-unify"   name="cg_custom_selector[]">
																 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_custom_selector[$i]) ?>>ID</option>
																 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_custom_selector[$i]) ?>>Class</option>
																 <option value="xpath" <?php @wp_automatic_opt_selected('xpath',$cg_custom_selector[$i]) ?>>XPath</option>
															</select>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td  style="padding-right:10px;width:130px" >
						 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_feed_custom_id_single ,ENT_COMPAT, 'UTF-8') ?>" name="cg_feed_custom_id[]" type="text">
						 		 		 				</td>
						 		 		 				
						 		 		 				<td style="padding-right:8px;">
						 		 		 					 <select class="no-unify"   name="cg_feed_css_size[]">
																 <option value="all" <?php @wp_automatic_opt_selected('all',$cg_feed_css_size[$i]) ?>>All</option>
																 <option value="single" <?php @wp_automatic_opt_selected('single', $cg_feed_css_size[$i]) ?>>Single</option>
															</select>
						 		 		 				</td>
						
						 		 		 				<td>
						 		 		 					 <select class="no-unify"   name="cg_feed_css_wrap[]">
																 <option value="outer" <?php @wp_automatic_opt_selected('outer',$cg_feed_css_wrap[$i]) ?>>Outer</option>
																 <option value="inner" <?php @wp_automatic_opt_selected('inner', $cg_feed_css_wrap[$i]) ?>>Inner</option>
															</select>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:5x;padding-top:8px;" >
						 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
						 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
						 		 		 				</td>

														 <td    style="padding-left:5px;padding-top:8px;" >
											 		 		<button  title="Copy rule tag"  class="dashicons-before dashicons-tag copy_rule_tag" ></button>
											 		 	</td>
						 		 		 			
						 		 		 			</tr>
 		 		 		
 		 		 						<?php 
 		 		 						
 		 		 									$i++ ; 
						 		 		 			
						 		 		 				}	
						 		 		 } ?>
								                	<tr><td style="padding-top:20px" colspan="6">If using XPath and the part you picked is a link and you just want the URL, append this to the end of the XPath /@href so for example instead of "/html/body/div[1]/div[1]/div[3]/a", modify it to be "/html/body/div[1]/div[1]/div[3]/a/@href"</td></tr>
								                	<tr><td style="padding-top:20px" colspan="6">For each extraction rule, a new tag will be available for you to use on the <b>post template</b> or custom fields section below. It returns the extracted content. <br>For example, the first part you extract using the first rule will have tag named [rule_1] and the second part will have a tag named [rule_2]</td></tr>
								             
								                	
								                
								                </table>
								                
								                 
							                    
								            </div>
								            
						               </div>
							
										<div class="option clearfix">
						                    
						                       
								            <div id="feed_custom_r" class="select_control_div_div cg_feed_extraction_method cg_feed_extraction_method_regex field f_100">
								            
								               <label for="field6">
								                     Regex (HIGHLY ADVANCED) 
								               </label>
								                
								                <table>
								                
								                <?php 
 		 		 				 
								                		$cg_feed_custom_regex= @$camp_general['cg_feed_custom_regex'];
								                		if(!is_array($cg_feed_custom_regex)) $cg_feed_custom_regex= array('');
						 		 		 			 
						 		 		 			$i=0;
						 		 		 			foreach ($cg_feed_custom_regex as $cg_feed_custom_regex_rule){
						 		 		 			?>
						 		 		 			
						 		 		 			<tr>
						 		 		 				
						 		 		 				<td  style="padding-right:5px;width:300px" >
						 		 		 				 		<input   class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_feed_custom_regex_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_feed_custom_regex[]" type="text">
						 		 		 				</td>
						 		 		 				
														<td    style="padding-left:5px;padding-top:8px;" >
															<span class="option-title">
																			<abbr title="Tick if you want the plugin to extract the first match only not all matches">Single?</abbr>
															</span>   
															<input  class="no-unify"  title="Single match" name="camp_options[]"   value="OPT_FEED_REGEX_SINGLE_<?php echo $i ?>" type="checkbox">
														</td>

						 		 		 				<td    style="padding-left:5px;padding-top:8px;" >				 
						 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
						 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
						 		 		 				</td>

														<td    style="padding-left:5px;padding-top:8px;" >
											 		 		 <button  title="Copy rule tag"  class="dashicons-before dashicons-tag copy_rule_tag" ></button>
											 		 	</td>
						 		 		 			
						 		 		 			</tr>
						 		 		 		
						 		 		 		<?php  $i++ ; } ?>
								                	
								                	<tr><td style="padding-top:20px" colspan="5">If you want to extract content between two unique texts in the content use this  <strong>startTextHere(.*?)endTextHere</strong>  where startTextHere is the unique text that the plugin will extract starting after and endTextHere is the unique text that the plugin will stop once found. The extracted part must be wraped by brackets<br><br>Example: source content contains this html<br> &lt;span class=&quot;author&quot;&gt;Mo Atef&lt;/span&gt; <br>and we want to grab the author name "Mo Atef", we can use this Regex<br> &lt;span class=&quot;author&quot;&gt;(.*?)&lt;/span&gt;</td></tr>
								                <tr><td style="padding-top:20px" colspan="5">For each Regex rule, a new tag will be available for you to use on the <b>post template</b> or custom fields section below. It returns the extracted content. <br>For example, the first part you extract using the first rule will have tag named [rule_1] and the second part will have a tag named [rule_2]</td></tr>
								                <tr><td style="padding-top:20px" colspan="5">Tick the checkbox if you want the a single match only </td></tr>
								                </table>
								                
								                 
							                    
								            </div>
								            
						               </div>
						               
						               
						               <div class="option clearfix">
								            <div id="feed_visual" class="select_control_div_div cg_feed_extraction_method cg_feed_extraction_method_visual field f_100">
								            
								            
								            <div class="field typepart Multi">
										 		<label>URL of a single post</label> <input value="<?php  echo @$camp_general['cg_ml_example_2']   ?>" name="cg_ml_example_2" data-fill-this-field-after-picking-first-link  data-visual-selector-Multi-example type="text">	
										 		<div class="description">This URL will be used for the visual selector so add a URL for an example post</div> 
											</div>



											  <table>
											 		 		 			
											 		 		 			<?php 
											 		 		 				 
											 		 		 			$cg_feed_visual = @$camp_general['cg_feed_visual'];
											 		 		 			if(!is_array($cg_feed_visual)) $cg_feed_visual = array('');
											 		 		 			
											 		 		 			$i=0;
											 		 		 			foreach ($cg_feed_visual as $cg_feed_visual_rule){

																			//default cg_feed_visual_hint if not set
																			if(!isset($camp_general['cg_feed_visual_hint'][$i])) $camp_general['cg_feed_visual_hint'][$i]= '';

											 		 		 			?>
											 		 		 			
											 		 		 			<tr class="rule_row" >
											 		 		 				
											 		 		 				<td  style="padding-right:10px;width:300px" >
											 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities($cg_feed_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_feed_visual[]" type="text">
																			
																					<textarea placeholder="Optional hint for your reference to remember what this part represents..." rows="2" style="max-height:50px;" name="cg_feed_visual_hint[]"  ><?php echo wp_automatic_htmlentities(@$camp_general['cg_feed_visual_hint'][$i],ENT_COMPAT, 'UTF-8') ?></textarea>
																					
											 		 		 				</td>
											 		 		 				
											 		 		 				<td    style="padding-left:5px;padding-top:8px;" >

																			</td>
											 		 		 				
											 		 		 				<td    style="padding-left:5px;padding-top:8px;" > 
											 		 		 					 <button  title="Locate" data-popup-field-name="cg_ml_example_2"  data-popup-field-rss="yes" class="dashicons-before dashicons-color-picker visual_selector" ></button>
											 		 		 				</td>
											 		 		 				 
											 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
											 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
											 		 		 				</td>
											 		 		 				
											 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
											 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
											 		 		 				</td>

																			<td    style="padding-left:5px;padding-top:8px;" >
											 		 		 					 <button  title="Copy rule tag"  class="dashicons-before dashicons-tag copy_rule_tag" ></button>
											 		 		 				</td>
											 		 		 			
											 		 		 			</tr>
											 		 		 		
											 		 		 		<?php  $i++ ; } ?>
											 		 		 		<tr><td style="padding-top:20px" colspan="4">Click the (<span class="dashicons-before dashicons-color-picker"></span>) button and pick the desired part, the plugin will load a visual selector and when you click a specific part, the plugin will return an XPath and save it to the box<br><br>If the part you picked is a link and you just want the URL, append this to the end of the XPath /@href so for example instead of "/html/body/div[1]/div[1]/div[3]/a", modify it to be "/html/body/div[1]/div[1]/div[3]/a/@href"</td></tr>
											 		 		 		<tr><td style="padding-top:20px" colspan="4">For each part you pick a new tag will be available for you to use on the <b>post template</b> or custom fields section below. It returns the extracted content. <br>For example, the first part you pick will have tag named [rule_1] and the second part will have a tag named [rule_2]</td></tr>
											 		 		 		
											 		</table>								            			


								            </div>
								       </div>
						               
						               
						               
						               </div>
						               

									   <div  style="padding-top:12px">
						               		<div class="option clearfix">
	                    
							                    <input name="camp_options[]"  data-controls="wp_auatomatic_multi_paged_part"  value="OPT_FEED_MULTI_PAGE" type="checkbox">
							                    <span class="option-title">
														Load multi-paged content 
							                    </span>
							                    <br>
							                    
									            <div id="wp_auatomatic_multi_paged_part" class="field f_100">
									           		
									           		 <div class="description">
												    	
												    	 <label>
										                    Next page link format (Optional)
											             </label>
											
														 <input placeholder="[current_page]/[page_number]" value="<?php    echo wp_automatic_htmlentities(@$camp_general['cg_multi_paged_link'],ENT_COMPAT, 'UTF-8')   ?>" name="cg_multi_paged_link" type="text">
											             
												    	 <p>
												    	
												    	This feature loads next pages of the single post if the post content is multi-paged
												    	
												    	<br><br>Default format is [current_page]/[page_number] so if the first part of the post URL is http://www.example.com/2024/01/01/post-title/ then the next page URL will be http://www.example.com/2016/01/01/post-title/2/
												    	
												    	<br><br>Example: add [current_page]/page/[page_number] if the first part of the post URL is http://www.example.com/2024/01/01/post-title/ then the next page URL will be http://www.example.com/2016/01/01/post-title/page/2/
												    	 
														<br><br>Limit: the plugin will load a maximum of 10 pages, concat them together for extraction and applying the content extraction rules, best extraction method will be using CSS Class</p>	

											    	</div>
									           		 
											    </div>
											     
											</div>

										</div>

 						                 
						               <div  style="padding-top:12px">
						               		<div class="option clearfix">
	                    
							                    <input name="camp_options[]"  data-controls="wp_auatomatic_part_to_field" id="post_strip_css_opt" value="OPT_FEED_PTF" type="checkbox">
							                    <span class="option-title">
														Specific extraction to a custom field, excerpt, tags or custom taxonomy
							                    </span>
							                    <br>
							                    
									            <div id="wp_auatomatic_part_to_field" class="field f_100">
									           		
									           		 <div class="description">
												    	
												    	 <label for="field6">
										                    Rules (one per line)
											             </label>
											               
											             <textarea name="cg_part_to_field" ><?php    echo wp_automatic_htmlentities(@$camp_general['cg_part_to_field'],ENT_COMPAT, 'UTF-8')   ?></textarea>
												    	
												    	 <i>
												    	
												    	This feature extracts specific parts and set to specific custom fields
												    	
												    	<br><br>*one rule per line 
												    	
												    	<br><br>*rule fomat is  "extractionMethod|data|customFieldName"
												    	
												    	<br><br>*extractionMethod can be id,class,xpath or regex
												    	
												    	<br><br>Example #1 "class|tags|post_tags" will extract the part with class=tags and set it to a custom field named post_tags 
												    	<br><br>Example #2 "regex|liked(.*?)times|likes_count" will extract the content between "liked" and "times" and create a custom field with this value
												    	<br><br>If customFieldName is excerpt, then the extracted part will be set as an excerpt and if customFieldName is tags, the extracted part will be set as tags
												    	<br><br>If customFieldName is tags, then the extracted part will be set as tags
													<br><br>If customFieldName is categories, then the extracted part will be set as categories
												    	<br><br>Add |1 to the end of the rule if you want to get the first match only 
												    	<br><br>Prefix the customFieldName with  "taxonomy_" if you want to add the value as a custom taxonomy. ex: class|tags|taxonomy_product_tag will extract the part with class named tags and set to the taxonomy named product_tag
												    	</i>
												    	 
												    	
											    	</div>
									           		 
											    </div>
											     
											</div>

										</div>

						               	</div><!-- common first part feed/multi -->
						               		 
						               	<!-- common feed/multi/single -->	 
						               	<div id="feed_postcont" class="field f_100 typepart Feeds Multi Single BingNews GoogleNews" style="display:none;" >	 
						               		 
						               		 <div class="option clearfix">
								                    <input data-controls="apify_wait_for_div_single" name="camp_options[]"   value="OPT_FEED_APIFY" type="checkbox">
								                    <span class="option-title">
															Load the site single web pages using APIFY.COM (only if this site requires JavaScript)
								                    </span>
								                    <br>
													<div class="field f_100" id="apify_wait_for_div_single">
													
															<label for ="apify_wait_for_single">Wait for how many milliseconds after loading the site: [Optional]</label>
															<input name="cg_apify_wait_for_single" value="<?php  echo @$camp_general['cg_apify_wait_for_single']  ?>" placeholder="0" type="text">
															<div class="description">
																Wait for how many milliseconds after loading the site. This is useful if the site loads some content after a few seconds. for example: 15000 (waits for 15 seconds)
															</div>
												
													</div>
								             </div>
						               		 
						               		<div class="option clearfix">
	                    
							                    <input name="camp_options[]"  data-controls="post_strip_css_c" id="post_strip_css_opt" value="OPT_STRIP_CSS" type="checkbox">
							                    <span class="option-title">
														Strip parts after extracting content using ID or Class
							                    </span>
							                    <br>
							                    
									            <div id="post_strip_css_c" class="field f_100">
									                 
									              <table>
								                	
								                	 	<?php 
 		 		 				 
								                	 	$cg_custom_strip_selector =  ( isset( $camp_general['cg_custom_strip_selector'] ) ) ?  $camp_general['cg_custom_strip_selector'] : array('class') ;
								                	 	$cg_feed_custom_strip_id  = ( isset($camp_general['cg_feed_custom_strip_id']) ) ? $camp_general['cg_feed_custom_strip_id'] : array("");
						 		 		 			 
						 		 		 			$i=0;
						 		 		 			
						 		 		 		
						 		 		 			
						 		 		 			foreach ($cg_feed_custom_strip_id as $cg_feed_custom_strip_id_single){
						 		 		 				
						 		 		 				if(  true ) {
						 		 		 			?>
						 		 		 			
						 		 		 			<tr>
						 		 		 				
						 		 		 				<td   style="min-width:65px;padding-right:10px">
															<select class="no-unify"   name="cg_custom_strip_selector[]">
																 <option value="id" <?php @wp_automatic_opt_selected('id', $cg_custom_strip_selector[$i]) ?>>ID</option>
																 <option value="class" <?php @wp_automatic_opt_selected('class',$cg_custom_strip_selector[$i]) ?>>Class</option>
															</select>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td  style="padding-right:10px;width:130px" >
						 		 		 				 		<input  class="no-unify"  value="<?php  echo $cg_feed_custom_strip_id_single?>" name="cg_feed_custom_strip_id[]" type="text">
						 		 		 				</td>
						 		 		 				
						 		 		 				  		 		 				
						 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
						 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
						 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
						 		 		 				</td>
						 		 		 			
						 		 		 			</tr>
 		 		 		
 		 		 						<?php 
 		 		 						
 		 		 									$i++ ; 
						 		 		 			
						 		 		 				}	
						 		 		 } ?>
								                	
								                	
								                 
								                	<?php /*?>
								                	<tr>
								                		<td colspan="5">
									                		<input name="camp_options[]" value="OPT_FEED_CUSTOM_MULTI_PAGE" type="checkbox">
										                    <span class="option-title">
																	Try to fetch content from multi-pages posts. 
																	 
										                    </span>
										                    <br>
								                		</td>
								                	</tr>
								                	
								                	<?php */?>
								                	
								                	
								                
								                </table>
									                									                 
									            </div>
									            
							                </div>
						               
							                <div class="option clearfix">
	                    
							                    <input name="camp_options[]"  data-controls="post_strip_c" id="post_strip_opt" value="OPT_STRIP_R" type="checkbox">
							                    <span class="option-title">
														Strip parts after extracting content using REGEX (ADVANCED)
							                    </span>
							                    <br>
							                    
									            <div id="post_strip_c" class="field f_100">
									               <label for="field6">
									                    REGEX patterns to strip (one per line)
									               </label>
									               
									                <textarea name="cg_post_strip" ><?php    echo wp_automatic_htmlentities(@$camp_general['cg_post_strip'],ENT_COMPAT, 'UTF-8')   ?></textarea>
									               
									            </div>
									            
							               </div>

										   <div class="option clearfix">
													
													<input name="camp_options[]"  data-controls="post_strip_wrd"  value="OPT_STRIP_BY_WORD" type="checkbox">
													<span class="option-title">
															Strip paragraphs that contain a specific word (like Read more)
													</span>
													<br>
													
													<div id="post_strip_wrd" class="field f_100">
													<label for="field6">
															Words (one per line)
													</label>
													
														<textarea name="cg_post_strip_by_words" ><?php    echo  (@$camp_general['cg_post_strip_by_words'])   ?></textarea>
													
													</div>
													
											</div>

										   <div class="option clearfix">
	                    
							                    <input name="camp_options[]"  data-controls="post_strip_visual" value="OPT_STRIP_VISUAL" type="checkbox">
							                    <span class="option-title">
														Strip parts after extracting content using Visual selector/XPath (Newbie friendly)
							                    </span>
							                    <br>
							                    
									            <div id="post_strip_visual" class="field f_100">
									              
												   <div class="field typepart Multi">	
													   <label>Example URL of a single post</label> <input value="<?php  echo @$camp_general['cg_ml_example_strip']   ?>" name="cg_ml_example_strip" data-fill-this-field-after-picking-first-link  data-visual-selector-Multi-example type="text">	
														<div class="description">This URL will be used for the visual selector, so add a URL for an example post</div> 
													</div>
															
													
											 
											 		<table> 		 			
											 		 		 			<?php 
											 		 		 				 
											 		 		 			$cg_feed_visual_strip = @$camp_general['cg_feed_visual_strip'];
											 		 		 			if(!is_array($cg_feed_visual_strip)) $cg_feed_visual_strip = array('');
											 		 		 			
											 		 		 			$i=0;
											 		 		 			foreach ($cg_feed_visual_strip as $cg_feed_visual_rule){
											 		 		 			?>
											 		 		 			
											 		 		 			<tr>
											 		 		 				
											 		 		 				<td  style="padding-right:5px;width:300px" >
											 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities($cg_feed_visual_rule,ENT_COMPAT, 'UTF-8')  ?>" name="cg_feed_visual_strip[]" type="text">
											 		 		 				</td>
											 		 		 				
											 		 		 				<td    style="padding-left:15px;padding-top:8px;" > 
											 		 		 					 <button  title="Locate" data-popup-field-name="cg_ml_example_strip"  data-popup-field-rss="yes" class="dashicons-before dashicons-color-picker visual_selector" ></button>
											 		 		 				</td>
											 		 		 				 
											 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
											 		 		 					 <button  title="Duplicate rule"  class="dashicons-before dashicons-plus duplicator" ></button>
											 		 		 				</td>
											 		 		 				
											 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
											 		 		 					 <button  title="Remove rule"  class="dashicons-before dashicons-no cleaner" ></button>
											 		 		 				</td>
											 		 		 			
											 		 		 			</tr>
											 		 		 		
											 		 		 		<?php  $i++ ; } ?>
											 		 		 		
											 		 		 		<tr><td style="padding-top:20px" colspan="4">*Click on the dot icon and select parts to remove that exists on the part you want originally to extract as the post content<br><br>*Selected parts will be removed before extracting the actual content for the post so take care!<br><br>*You can add an XPath manually like:<br> <code>//p[contains(., "read more")]</code> which will remove the paragraph that contains the text "read more"</td></tr>
											 		 		 		
											 		</table>
									                 
									               
									            </div>
									            
							               </div>

										   <div class="option clearfix">
	                    
							                    <input name="camp_options[]" value="OPT_STRIP_IMGS" type="checkbox">
							                    <span class="option-title">
														Strip images from extracted post content
							                    </span>
 
							               </div>
							               
							               
											<div class="option clearfix">
	                    
							                    <input name="camp_options[]"  data-controls="post_strip_t"  value="OPT_STRIP_T" type="checkbox">
							                    <span class="option-title">
														Strip HTML tags
							                    </span>
							                    <br> 
							                    
									            <div id="post_strip_t" class="field f_100">
									               
									                <label>
									                    Allowed html tags  (tags not to strip)
									               </label>
									               
									                <input value="<?php    echo @$camp_general['cg_allowed_tags']   ?>"  name="cg_allowed_tags" type="text">
									               <div class="description" >example:&lt;br&gt;&lt;a&gt; </div>
									            </div>
									            
							               </div>
							               
							               
								               
										 </div><!-- end common part feeds/multi/single -->
										 
										 <!-- second common part feeds/multi -->
										 <div id="feed_postcont" class="field f_100 typepart Feeds Multi BingNews GoogleNews" style="display:none;" >
					              
					              		<div class="option clearfix">
							                    <input name="camp_options[]"   value="OPT_FEED_TOP" type="checkbox">
							                    <span class="option-title">
														Only check the top post (will ignore all older posts)
							                    </span>
							                    <br>
							               </div>
					               
							                 <div class="option clearfix">
							                    <input data-controls="original_time_dev" name="camp_options[]"   value="OPT_ORIGINAL_TIME" type="checkbox">
							                    <span class="option-title">
														Add posts with its original time   
							                    </span>
							                    <br>


												<div id="original_time_dev"  class="field f_100">
												
													<label>
									                    REGEX pattern to extract the original time (Optional)
									               </label>
									               
									                <input value="<?php    echo @$camp_general['cg_original_time_regex']   ?>"  name="cg_original_time_regex" type="text">
													<div class="description" >Example:<br><code>(January|February|March|April|May|June|July|August|September|October|November|December)\s\d{1,2},\s20\d{2}
</code><br><br> Example: <br><code>20\d{2}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d*)?.\d{2}:\d{2}</code> </div>
									               


									            </div>

							               </div>
					               
					               		   <div class="option clearfix">
							                    <input name="camp_options[]" data-controls="wp_automatic_cats_extract"   value="OPT_ORIGINAL_CATS" type="checkbox">
							                    <span class="option-title">
														Set original post categories as the imported post categories/tags   
							                    </span>
							                    <br>
							                    
							                     <div id="wp_automatic_cats_extract"  class="field f_100">
								                    	<label for="field6">
									                    Division ID or Class or XPath (ADVANCED) 
									               </label>
									                
									                <table>
									                	<tr>
									                		
									                	
									                		<td style="min-width:80px;padding-right:5px;">
									                		
									                		<select name="cg_custom_selector_cat" id="cg_custom_selector_cat" >
										                		<option  value="id"  <?php @wp_automatic_opt_selected('id', $camp_general['cg_custom_selector_cat'] ) ?> >ID</option> 
										                		<option  value="class"  <?php @wp_automatic_opt_selected('class', $camp_general['cg_custom_selector_cat'] ) ?> >Class</option>
										                		<option  value="xpath"  <?php @wp_automatic_opt_selected('xpath', $camp_general['cg_custom_selector_cat'] ) ?> >XPath</option>
										                	</select>
									                		
									                		</td>
									                		
									                		<td  style="vertical-align: top;width: 100%;" ><input value="<?php   echo wp_automatic_htmlspecialchars( @$camp_general['cg_feed_custom_id_cat'])   ?>" name="cg_feed_custom_id_cat"    type="text"></td>
									                		
									                		<td    style="padding-left:15px;padding-top:8px;padding-right:10px"   valign="top">
						 		 		 					 <button  title="Locate"  data-popup-field-name="cg_ml_example_2"  data-popup-field-rss="yes"  class="dashicons-before dashicons-color-picker visual_selector cat_locator" ></button>
						 		 		 				</td>
									                		
									                		
									                		<td style="min-width: 85px;" valign="top" >
									                			<span class="option-title">
																		<abbr title="Tick if you want the plugin to extract the first match only not all matches">Single?</abbr>
								                    			</span>
											                    <input  class="no-unify" name="camp_options[]"  value="OPT_SELECTOR_SINGLE_CAT" type="checkbox">
									                		</td>
									                		
									                		<td style="min-width: 85px;" valign="top" >
									                			<span class="option-title">
																		<abbr title ="Tick to extract the inner content not the outer content">Inner?</abbr>
								                    			</span>
											                    <input  class="no-unify" name="camp_options[]"  value="OPT_SELECTOR_INNER_CAT" type="checkbox">
									                		</td>
									                		
									                	 							                		
									                	</tr>
									                </table>
									                
									                
									                <div class="option clearfix">
	                    
									                    <input name="camp_options[]"  data-controls="post_cat_parent"  value="OPT_ORIGINAL_CATS_PARENT" type="checkbox">
									                    <span class="option-title">
																Set a parent category ID for new created categories 
									                    </span>
									                    <br> 
									                    
											            <div id="post_cat_parent" class="field f_100">
											               
											                <label>
											                    Parent category ID (get the numeric ID value from the categories box below)
											               </label>
											               
											                <input value="<?php    echo @$camp_general['cg_parent_cat']   ?>"  name="cg_parent_cat" type="text">
											               <div class="description" >example:1234 </div>
											            </div>
											            
									               </div>
									               
									               <div class="option clearfix">
	                    
									                    <input name="camp_options[]"  data-controls="post_cat_replace"  value="OPT_ORIGINAL_CATS_REPLACE" type="checkbox">
									                    <span class="option-title">
																Replace categories names  
									                    </span>
									                    <br> 
									                    
											            <div id="post_cat_replace" class="field f_100">
											               
											                <label>
											                    Search|Replace (one per line)
											               </label>
											               
											                <textarea name="cg_cat_replace"><?php echo (  $camp_general['cg_cat_replace'] )  ?></textarea>
											               <div class="description" >example: sports|Sport<br><br>This will replace the category named sports with Sport so this is the final category name on your site </div>
											            </div>
											            
									               </div>
									               
									               <div class="option clearfix">
	                    
									                    <input name="camp_options[]"    value="OPT_ORIGINAL_CATS_TAGS" type="checkbox">
									                    <span class="option-title">
																Set the values as tags instead of categories  
									                    </span>
									                    <br> 
													
													</div>
													
													<div class="description" >
														* This feature will by default import the categories from the RSS feed category tag If you leave the Class/ID field empty
													</div>
						
						
									                
							                    </div>
							                    
							               </div>
							               
							               <div class="option clearfix">
							                    <input name="camp_options[]"   value="OPT_ORIGINAL_META" type="checkbox">
							                    <span class="option-title">
														Set meta tags/Schema keywords as WordPress tags
							                    </span>
							                    <br>
							               </div>
											
											<div class="option clearfix">
							                    <input data-controls="wp_automatic_tags_extract" name="camp_options[]"   value="OPT_ORIGINAL_TAGS" type="checkbox">
							                    <span class="option-title">
														Extract original tags and set it as WordPress tags (using CSS ID/Class)    
							                    </span>
							                    
							                    <div id="wp_automatic_tags_extract" class="field f_100">
							                    	<label for="field6">
								                    Division ID or Class or XPath or click the dot button to pick using visual selector
								               			</label>
								                
								                <table>
								                	<tr>
								                		
								                	
								                		<td style="min-width:80px;padding-right:5px;">
								                		
								                		<select name="cg_custom_selector_tag" id="cg_custom_selector_tag" >
									                		<option  value="id"  <?php @wp_automatic_opt_selected('id', $camp_general['cg_custom_selector_tag'] ) ?> >ID</option> 
									                		<option  value="class"  <?php @wp_automatic_opt_selected('class', $camp_general['cg_custom_selector_tag'] ) ?> >Class</option>
									                		<option  value="xpath"  <?php @wp_automatic_opt_selected('xpath', $camp_general['cg_custom_selector_tag'] ) ?> >XPath</option>
									                	</select>
								                		
								                		</td>
								                		
								                		<td  style="vertical-align: top;width: 100%;" ><input value="<?php   echo wp_automatic_htmlspecialchars( @$camp_general['cg_feed_custom_id_tag'])   ?>" name="cg_feed_custom_id_tag"    type="text"></td>
								                		
								                		<td    style="padding-left:15px;padding-top:8px;padding-right:10px"   valign="top">
					 		 		 					 <button  title="Locate"  data-popup-field-name="cg_ml_example_2"  data-popup-field-rss="yes"  class="dashicons-before dashicons-color-picker visual_selector tag_locator" ></button>
					 		 		 				</td>
								                		
								                		
								                		<td style="min-width: 85px;" valign="top" >
								                			<span class="option-title">
																	<abbr title="Tick if you want the plugin to extract the first match only not all matches">Single?</abbr>
							                    			</span>
										                    <input  class="no-unify" name="camp_options[]"  value="OPT_SELECTOR_SINGLE_TAG" type="checkbox">
								                		</td>
								                		
								                		<td style="min-width: 85px;" valign="top" >
								                			<span class="option-title">
																	<abbr title ="Tick to extract the inner content not the outer content">Inner?</abbr>
							                    			</span>
										                    <input  class="no-unify" name="camp_options[]"  value="OPT_SELECTOR_INNER_TAG" type="checkbox">
								                		</td>
								                		
								                	 							                		
								                	</tr>
								                </table>

												<div class="option clearfix">
	                    
									                    <input name="camp_options[]"  data-controls="post_tag_replace"  value="OPT_ORIGINAL_TAGS_REPLACE" type="checkbox">
									                    <span class="option-title">
																Search and replace in extracted tags names  
									                    </span>
									                    <br> 
									                    
											            <div id="post_tag_replace" class="field f_100">
											               
											                <label>
											                    Search|Replace (one per line)
											               </label>
											               
											                <textarea name="cg_tag_replace"><?php  echo ( empty($camp_general['cg_tag_replace']) ?  '' :  $camp_general['cg_tag_replace'] )  ?></textarea>
											               <div class="description" >example: sports|Sport<br><br>This will replace the tag named sports with Sport so this is the final tag name on your site </div>
											            </div>
											            
									               </div>


							                    </div>
							                    
							                    <br>
							               </div>
							               
							               
							               <div class="option clearfix">
							                    <input data-controls="wp_automatic_author_extract" name="camp_options[]"   value="OPT_ORIGINAL_AUTHOR" type="checkbox">
							                    <span class="option-title">
														Extract original author and set it as the post author (using id/class/xpath)    
							                    </span>
							                    
							                    <div id="wp_automatic_author_extract"  class="field f_100">
							                    	<label for="field6">
								                    Division ID or Class or XPath (ADVANCED) 
								               </label>
								                
								                <table>
								                	<tr>
								                		
								                	
								                	
								                		<td style="min-width:80px;padding-right:5px;">
								                		
								                		<select id="cg_custom_selector_author" name="cg_custom_selector_author"  valign="top" >
									                		<option  value="id"  <?php @wp_automatic_opt_selected('id', $camp_general['cg_custom_selector_author'] ) ?> >ID</option> 
									                		<option  value="class"  <?php @wp_automatic_opt_selected('class', $camp_general['cg_custom_selector_author'] ) ?> >Class</option>
									                		<option  value="xpath"  <?php @wp_automatic_opt_selected('xpath', $camp_general['cg_custom_selector_author'] ) ?> >XPath</option>
									                	</select>
								                		
								                		</td>
								                		
								                		<td  style="vertical-align: top;width: 100%;" ><input value="<?php   echo wp_automatic_htmlspecialchars( @$camp_general['cg_feed_custom_id_author'])   ?>" name="cg_feed_custom_id_author"    type="text"></td>
								                		
								                		<td    style="padding-left:15px;padding-top:8px;padding-right:10px"   valign="top">
					 		 		 					 <button  title="Locate"  data-popup-field-name="cg_ml_example_2"  data-popup-field-rss="yes"  class="dashicons-before dashicons-color-picker visual_selector author_locator" ></button>
					 		 		 				</td>
								                		
								                		<td style="min-width:85px;" valign="top" >
								                			<span class="option-title">
																	<abbr title="Tick if you want the plugin to extract the first match only not all matches">Single?</abbr>
							                    			</span>
										                    <input  class="no-unify" name="camp_options[]"  value="OPT_SELECTOR_SINGLE_AUTHOR" type="checkbox">
								                		</td>
								                		
								                		<td style="min-width: 85px;" valign="top" >
								                			<span class="option-title">
																	<abbr title ="Tick to extract the inner content not the outer content">Inner?</abbr>
							                    			</span>
										                    <input  class="no-unify" name="camp_options[]"  value="OPT_SELECTOR_INNER_AUTHOR" type="checkbox">
								                		</td>
								                		
								                		
								                		
								                	 							                		
								                	</tr>
													 
													<tr>
														<td colspan="3">
															<div class="description" >
																			You can leave config empty, the plugin will try to auto detect it
														</div>
														</td>
													</tr>

																		

								                </table>
							                    </div>
							                    
							                    <br>
							               </div>

							                <div class="option clearfix">
							                    <input name="camp_options[]"   value="OPT_ORIGINAL_META_DESC" type="checkbox">
							                    <span class="option-title">
														Extract original meta description and set as Yoast/Rank Math meta description   
							                    </span>
							                    <br>
							                </div>
	
											<div class="option clearfix">
							                    <input name="camp_options[]"   value="OPT_MUST_CONTENT" type="checkbox">
							                    <span class="option-title">
														Skip posts with no content   
							                    </span>
							                    <br>
							                </div>
							                
							               		               
							                 <div class="option clearfix">
							                    <input name="camp_options[]"   value="OPT_MUST_IMAGE" type="checkbox">
							                    <span class="option-title">
														Skip posts without images   
							                    </span>
							                    <br>
							               </div>
							               
							                 
							                <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_REVERSE" type="checkbox">
								                    <span class="option-title">
															Process items from bottom to top instead    
								                    </span>
								                    <br>
								             </div>
								             
								              <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_ENTITIES" type="checkbox">
								                    <span class="option-title">
															Decode html entities
								                    </span>
								                    <br>
								             </div>
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_NOLENGTH" type="checkbox">
								                    <span class="option-title">
															Auto-fix transfer closed with bytes remaining to read (If you already getting this error displayed)
								                    </span>
								                    <br>
								             </div>
								             
								             <div class="option clearfix">
								                    <input data-controls="convert_encoding_div" name="camp_options[]"   value="OPT_FEED_CONVERT_ENC" type="checkbox">
								                    <span class="option-title">
															Convert encoding before posting (in case content is not utf-8 encoded)
								                    </span>
								                    
								                    <div id="convert_encoding_div" class="field f_100">
								                    	 
								                    	  <label>
										                    Source encoding (example: "CP1256" or "Windows-1252" etc) 
										                  </label>
										                  
										                  <input value="<?php   echo @$camp_general['cg_feed_encoding']  ?>" name="cg_feed_encoding"   type="text">
										             	
								                    </div>
								                    
								                    <br>
								             </div>
								             
								              <div class="option clearfix">
								                    <input name="camp_options[]"     value="OPT_FEED_ENCODING" type="checkbox">
								                    <span class="option-title">
															Clean cURL encoding value (becase at rare cases content get returned deformed.)
								                    </span>
								                    <br>
								             </div>
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_ENCLUSURE" type="checkbox">
								                    <span class="option-title">
															Don't extract enclosure image (By default it get appended to the post top if no image exists)
								                    </span>
								                    <br>
								             </div>
								             
								              
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_FORCE" type="checkbox">
								                    <span class="option-title">
															Don't try to find feed url, the added url is already a feed url (not recommended)
								                    </span>
								                    <br>
								             </div>
								             
								              <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_SCRIPT" type="checkbox">
								                    <span class="option-title">
															Don't strip script tags
								                    </span>
								                    <br>
								             </div>
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_NO_DECODE" type="checkbox">
								                    <span class="option-title">
															Don't strip tags from generated titles and do not html entity decode (by default the plugin removes html tags and decode html entities on titles)
								                    </span>
								                    <br>
								             </div>
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_NORED" type="checkbox">
								                    <span class="option-title">
															Don't try to guess redirections using JavaScript and http-equiv to refresh
								                    </span>
								                    <br>
								             </div>
								             
								             
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_OG_TTL" type="checkbox">
								                    <span class="option-title">
															Get og:title instead of the feed title. used for facebook share. 
								                    </span>
								                    <br>
								             </div>

											 <!-- checkbox: allow feed enties with empty title -->
											<div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_EMPTY_TITLE" type="checkbox">
								                    <span class="option-title">
															Allow feed items with empty title to be posted (by default the plugin skips them)
															</span>
								                    <br>
											</div>

								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_TITLE_NO" type="checkbox">
								                    <span class="option-title">
															Don't try to get original title from the original post and use what is in the feed.
								                    </span>
								                    <br>
								             </div>
								             
								             <div class="option clearfix">
								                    <input name="camp_options[]"   value="OPT_FEED_ORIGINAL_SLUG" type="checkbox">
								                    <span class="option-title">
															Set the post slug from the original post link slug
								                    </span>
								                    <br>
								             </div>
								               
								             <div class="option clearfix">
                    
										        <input data-controls="FEED_LAZY_c" name="camp_options[]" value="OPT_FEED_LAZY" type="checkbox">
										        <span class="option-title">
														Fix images lazy loading
										        </span>
										        <br>
										        
										        <div id="FEED_LAZY_c" class="field f_100">
										        	
										        		<label>Correct src attribute</label> <input value="<?php  echo @$camp_general['cg_feed_lazy']   ?>" name="cg_feed_lazy" type="text">
										        		<div class="description">The attribute containing the actual image URL ex: "data-src" or "data-original"</div>
										         
										        </div>
									                    
											</div>

											<div class="option clearfix">
                    
										        <input  name="camp_options[]" value="OPT_FEED_LAZY_NOSCRIPT_DISABLE" type="checkbox">
										        <span class="option-title">
														Disable noscript lazy loading fix (Use this option if you encounter duplicate images in the post)
										        </span>
										        
									                    
											</div>
											
											
											<div class="option clearfix">
							                    
											        <input data-controls="cookie_c" name="camp_options[]" value="OPT_COOKIE" type="checkbox">
											        <span class="option-title">
															Set cookies (To post protected content)
											        </span>
											        <br>
											        
											        <div id="cookie_c" class="field f_100">
											         	<label>Cookie</label> <input value="<?php echo  wp_automatic_htmlentities($camp_general['cg_ml_cookie']  ,ENT_COMPAT, 'UTF-8')     ?>" name="cg_ml_cookie" type="text">
											         	<div class="description">cookie1Name=cookie1Value;cookie2Name=cookie2value;cookie3Name=cookie3Value<br><br>Check this <a target="_blank" href="http://valvepress.com/how-to-import-protected-sites-content-to-wordpress-automatically/">tutorial</a> on how to get the cookies</div>
											       	</div>
							                    
											</div>
											
									 
		 
		 </div>
		 <!-- /feed part -->
		 
		 
		  <!-- Articles part -->
		 <div  class="field f_100 typepart Articles"  style="display:none">

				    
               <div class="option clearfix">
                    
                    <input name="camp_options[]"  data-controls="articles_date_c" value="OPT_ARTICLES_DATE" type="checkbox">
                    <span class="option-title">
							Limit search to articles posted on X last months/years  [Not Recommended: may not return results]
                    </span>
                    <br>
                    
		            <div id="articles_date_c" class="field f_100">
		                
		                <?php 
		                	$params=array('Years','Months');
		                ?>
		                
		                <div style="float:left;width:40%" >
			               	<select  name="cg_articles_date_last" >
			               		<?php 
				               			foreach ($params as $param){
								?>
	
											<option  value="<?php   echo $param ?>"  <?php @wp_automatic_opt_selected($param,$camp_general['cg_articles_date_last']) ?> ><?php   echo $param ?></option>
	
								<?php 
				               				
				               			}
			               		?>
			               		
			               		 
			               		 
			               	</select>
		                 </div>
		               
		                <div style="width:40%;float:left;margin-left:5px" >
		               		<input  placeholder="1" value="<?php   echo @$camp_general['cg_articles_date_last_val']  ?>" name="cg_articles_date_last_val"   type="text">
		                </div>
		               
		               <div style="clear:both" class="description">
			               <p>example: add 1 to post items from last year only and ignore older years</p>
		               </div>
		               
		            </div>
		            
               </div>
               
                  
				
		 </div>
		 
		 <!-- Careerjet part -->
		 <div  class="field f_100 typepart Careerjet"  style="display:none">
			
			<div class="option clearfix field f_100">
				<label>Localization</label><br>
				<select name="cg_cj_locale">
					<?php 
					$cj_localies= array( array('en_US','United States') , array('en_GB','United Kingdom') , array('cs_CZ','Czech Republic'),array('da_DK','Denmark'),array('de_AT','Austria'),array('de_CH','Switzerland'),array('de_DE','Germany'),array('en_AE','United Arab Emirates'),array('en_AU','Australia'),array('en_CA','Canada'),array('en_CN','China'),array('en_HK','Hong Kong'),array('en_IE','Ireland'),array('id_ID','Indonesia'),array('en_IN','India'),array('en_MY','Malaysia'),array('en_NZ','New Zealand'),array('en_OM','Oman'),array('en_PH','Philippines'),array('en_PK','Pakistan'),array('en_QA','Qatar'),array('en_SG','Singapore'),array('en_ZA','South Africa'),array('en_TW','Taiwan'),array('en_VN','Vietnam'),array('es_AR','Argentina'),array('es_BO','Bolivia'),array('es_CL','Chile'),array('es_CR','Costa Rica'),array('es_DO','Dominican Republic'),array('es_EC','Ecuador'),array('es_ES','Spain'),array('es_GT','Guatemala'),array('es_MX','Mexico'),array('es_PA','Panama'),array('es_PE','Peru'),array('es_PR','Puerto Rico'),array('es_PY','Paraguay'),array('es_UY','Uruguay'),array('es_VE','Venezuela'),array('fi_FI','Finland'),array('fr_CA','Canada'),array('fr_BE','Belgium'),array('fr_CH','Switzerland'),array('fr_FR','France'),array('fr_LU','Luxembourg'),array('fr_MA','Morocco'),array('hu_HU','Hungary'),array('it_IT','Italy'),array('ja_JP','Japan'),array('ko_KR','Korea'),array('nl_BE','Belgium'),array('nl_NL','Netherlands'),array('no_NO','Norway'),array('pl_PL','Poland'),array('pt_PT','Portugal'),array('pt_BR','Brazil'),array('ru_RU','Russia'),array('ru_UA','Ukraine'),array('sv_SE','Sweden'),array('sk_SK','Slovakia'),array('tr_TR','Turkey'),array('uk_UA','Ukraine'),array('vi_VN','Vietnam'),array('zh_CN','China') ,array('en_NG','Nigeria') , array('ar_SA' , 'Sauid Arabia') );
						
						foreach($cj_localies as $arrloc ){
							
							$locCode = $arrloc[0];
							$locCountry = $arrloc[1];
							
							?>
							
								<option value="<?php echo $locCode ?>" <?php @wp_automatic_opt_selected( $locCode ,$camp_general['cg_cj_locale']) ?>><?php echo $locCountry ?></option>		
							
							<?php 
							
						}
					
					?>
				</select>
			</div>

			<!-- custom locale code if your country is not listed -->
			<div class="option clearfix">
				<label>Custom locale code <i>(optional)</i></label> <input value="<?php  echo @$camp_general['cg_cj_locale_custom']   ?>" name="cg_cj_locale_custom" type="text">
				<div class="description" >if your country is not listed above, you can use the custom locale code here ex: fr_FR this will overwrite the above selected locale</div>
			</div>
			
			<div class="option clearfix">
	 			<label>Location <i>(optional)</i></label> <input value="<?php  echo @$camp_general['cg_cj_location']   ?>" name="cg_cj_location" type="text">
	 			<div class="description" >ex: London</div>
 			</div>
 			
 			<div class="option clearfix  field f_100">
	 			<label>Sorting order</label>
				<select name="cg_cj_sort">
				
				 	<option value="relevance" <?php @wp_automatic_opt_selected('relevance',$camp_general['cg_cj_sort']) ?>>Relevance</option>
				 	<option value="salary" <?php @wp_automatic_opt_selected('salary',$camp_general['cg_cj_sort']) ?>>Salary</option>
				 	<option value="date" <?php @wp_automatic_opt_selected('date',$camp_general['cg_cj_sort']) ?>>Date</option>
				
				</select>
			</div>
			
			<div class="option clearfix  field f_100">
				
					<label>Contract type</label>
					<select name="cg_cj_contracttype">
					
						<option value="all" <?php @wp_automatic_opt_selected('all',$camp_general['cg_cj_contracttype']) ?>>All</option>
					 	<option value="p" <?php @wp_automatic_opt_selected('p',$camp_general['cg_cj_contracttype']) ?>>Permanent job</option>
					 	<option value="c" <?php @wp_automatic_opt_selected('c',$camp_general['cg_cj_contracttype']) ?>>Contract</option>
					 	<option value="t" <?php @wp_automatic_opt_selected('t',$camp_general['cg_cj_contracttype']) ?>>Temporary</option>
					 	<option value="i" <?php @wp_automatic_opt_selected('i',$camp_general['cg_cj_contracttype']) ?>>Training</option>
					 	<option value="v" <?php @wp_automatic_opt_selected('v',$camp_general['cg_cj_contracttype']) ?>>Voluntary</option>
	
					</select>
			</div>
				
			<div class="option clearfix  field f_100">
				
					<label>Contract period</label>
					<select name="cg_cj_contractperiod">
					
						<option value="all" <?php @wp_automatic_opt_selected('all',$camp_general['cg_cj_contractperiod']) ?>>All</option>
						<option value="f" <?php @wp_automatic_opt_selected('f',$camp_general['cg_cj_contractperiod']) ?>>Full time</option>
						<option value="p" <?php @wp_automatic_opt_selected('p',$camp_general['cg_cj_contractperiod']) ?>>Part time</option>
					 	 
					</select>
								
			</div>
			
			<div class="option clearfix">
				<input name="camp_options[]" value="OPT_CJ_CACHE" type="checkbox"> 
				<span class="option-title">Cache items for faster posting and pagination</span>
			</div>

			<div class="option clearfix">
				<input name="camp_options[]" value="OPT_CJ_SALARY" type="checkbox"> 
				<span class="option-title">Skip jobs with no finite salary</span>
			</div>
			
			<div class="option clearfix">
				<input name="camp_options[]" value="OPT_CJ_FULL" type="checkbox"> 
				<span class="option-title">Try to load the full job page and capture the full description & logo image</span>
			</div>
							
		 </div>
		 
		 <!-- /Careerjet part -->
		 
		 <!-- Aliexpress part -->
		 <div  class="field f_100 typepart Aliexpress"  style="display:none">

					<!--Hint: add product numeric id like 1005005822085800 in keywords box to import specific products-->
					<p><strong>Hint:</strong> add product numeric id like 1005005822085800 for <a href="https://tr.aliexpress.com/i/1005005822085800.html" target="_blank">this product</a> in keywords box to import specific products</p>


		 	        <div class="option clearfix">
                    <input name="camp_options[]" data-controls="ae_full_img_t"  value="OPT_AE_FULL_IMG" type="checkbox">

                    <span class="option-title">
							Modify item images HTML code
                    </span>
                    
                    <br>
                    
		            <div id="ae_full_img_t" class="field f_100">
		               <label>
		                    Single image template  
		               </label>
		               
		                <input placeholder="<?php echo  wp_automatic_htmlentities('<img src="[img_src]" class="wp_automatic_gallery" />') ;?>"  value="<?php    echo wp_automatic_htmlentities(@$camp_general['cg_ae_full_img_t'],ENT_COMPAT, 'UTF-8')   ?>"  name="cg_ae_full_img_t" type="text">
		               	
		               	<div class="description">This is how the plugin will build images html for the [item_imgs_html] tag.<br><br>*Use [img_src] to replace the image src.<br><br> *Use class="wp_automatic_gallery" to display images as gallery<br><br>Default: <?php echo  wp_automatic_htmlentities('<img src="[img_src]" class="wp_automatic_gallery" />') ;?></div>
		               
		            </div>
               </div>
               
                
               <div class="option clearfix">
                    
                    <input name="camp_options[]"   data-controls="ali_custom_url" value="OPT_ALIEXPRESS_CUSTOM" type="checkbox">
                    <span class="option-title">
							Custom AliExpress search URL (AliExpress link containing a list of products ) 
                    </span>
                    <br>
                    
		            <div id="ali_custom_url" class="field f_100">
		               <label>
		                   AliExpress custom Search URL 
		               </label>
		               <input type="text" name="cg_ae_custom_urls" value="<?php   echo @$camp_general['cg_ae_custom_urls']  ?>">
		            	<div class="description">Use [keyword] inside the URL if needed<br>ex1 watches category only search:<br> https://www.aliexpress.com/af/casio.html?SearchText=[keyword]&catId=1511<br><br>ex2:only items with min price=100<br> https://www.aliexpress.com/af/casio.html?CatId=1511&SearchText=casio&minPrice=100<br><br>ex3: Jackets category without a keyword<br>https://www.aliexpress.com/af/category/200000664.html?CatId=200000664&catName=jackets</div>
		            	
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]"   value="OPT_AE_NO_KEYS" type="checkbox">
		                    <span class="option-title">
									Don't use keywords and add items from above URL directly 
		                    </span>
	                    </div>
		            
		       		 </div>
		        </div>
		        
		        <div class="option clearfix">
                    
                    <input name="camp_options[]"   data-controls="ali_custom_cur" value="OPT_ALIEXPRESS_CUR" type="checkbox">
                    <span class="option-title">
							Set a specific currency
                    </span>
                    <br>
                    
		            <div id="ali_custom_cur" class="field f_100">
		               <label>
		                  Currency code
		               </label>
		               <input type="text" placeholder="USD" name="cg_ae_custom_cur" value="<?php   echo @$camp_general['cg_ae_custom_cur']  ?>">
		            	<div class="description">ex: write USD for United states $ or EUR for euro</div>
		            	  
		       		 </div>
		        </div>

				<!-- specific language code ex: en_US -->
				 <div class="option clearfix">
					
					<input name="camp_options[]"   data-controls="ali_custom_lang" value="OPT_ALIEXPRESS_LANG" type="checkbox">
					<span class="option-title">
						Set a specific language for the title/description
					</span>
					<br>

					<div id="ali_custom_lang" class="field f_100">
						<label>
							Language code
						</label>
						<input type="text" placeholder="en_US" name="cg_ae_custom_lang" value="<?php   echo @$camp_general['cg_ae_custom_lang']  ?>">
						<div class="description">ex: write en_US for English</div>

					</div>

				</div>

				<!-- specific country code ex: US -->
				<div class="option clearfix">

					<input name="camp_options[]"   data-controls="ali_custom_country" value="OPT_ALIEXPRESS_COUNTRY" type="checkbox">
					<span class="option-title">
						Set a specific country for availability
					</span>
					<br>

					<div id="ali_custom_country" class="field f_100">
						<label>
							Country code
						</label>
						<input type="text" placeholder="US" name="cg_ae_custom_country" value="<?php   echo @$camp_general['cg_ae_custom_country']  ?>">
						<div class="description" >ex: write US for United States</div>
					</div>
				</div>

		         
		        <div class="option clearfix">
                    
                    <input name="camp_options[]"   data-controls="ali_custom_domain" value="OPT_ALIEXPRESS_DOMAIN" type="checkbox">
                    <span class="option-title">
							Set a specific domain for search
                    </span>
                    <br>
                    
		            <div id="ali_custom_domain" class="field f_100">
		               <label>
		                  AliExpress domain name
		               </label>
		               <input type="text" placeholder="https://fr.aliexpress.com/" name="cg_ae_custom_domain" value="<?php   echo @$camp_general['cg_ae_custom_domain']  ?>">
		            	<div class="description">ex: write https://fr.aliexpress.com/ for French</div>
		            	  
		       		 </div>
		        </div>               
               
               <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_AE_GALLERY" type="checkbox">
	                    <span class="option-title">
								Add item images as a woo-commerce product gallery

	                    </span>
	                    <br>
	          	</div>
	          	
	          	<div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_AE_CACHE" type="checkbox">
	                    <span class="option-title">
								Cache items for faster posting (Disable if you want to focus on latest products only) (pagination will not work if disabled)
	                    </span>
	                    <br>
	                     
	               </div>
		 
		 </div>
		 
		 <!-- Craigslist part -->
		 
		 <div  class="field f_100 typepart Craigslist"  style="display:none">
		 
		 	   <label>
                    Craigslist items page url
               </label>
               
			   <input value="<?php   echo @$camp_general['cg_cl_page']  ?>" name="cg_cl_page"   type="text">
			   
			   <div class="description">ex: https://newyork.craigslist.org/search/mnh/bia</div>

			    <div  class="field f_100">
			    
				    <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_CL_CACHE" type="checkbox">
	                    <span class="option-title">
								Cache items for faster posting  (uncheck to call Craigslist each post)
	                    </span>
	                    <br>
	          		</div>
	          		
	          		
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_CL_IMG" type="checkbox">
	                    <span class="option-title">
								Skip posts with no images
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_CL_TIME" type="checkbox">
	                    <span class="option-title">
								Post items with its original time
	                    </span>
	                    <br>
	          		</div>
	          		
	          
               <div class="option clearfix">
                    <input name="camp_options[]" data-controls="cl_full_img_t"  value="OPT_CL_FULL_IMG" type="checkbox">

                    <span class="option-title">
							Modify item images HTML code
                    </span>
                    
                    <br>
                    
		            <div id="cl_full_img_t" class="field f_100">
		               <label>
		                    Image template  
		               </label>
		               
		                <input placeholder="<?php echo  wp_automatic_htmlentities('<img src="[img_src]" class="wp_automatic_gallery" />') ;?>"  value="<?php    echo wp_automatic_htmlentities(@$camp_general['cg_cl_full_img_t'],ENT_COMPAT, 'UTF-8')   ?>"  name="cg_cl_full_img_t" type="text">
		               	
		               	<div class="description">This is how the plugin will build images html for the [item_imgs_html] tag.<br><br>*Use [img_src] to replace the image src.<br><br> *Use class="wp_automatic_gallery" to display images as gallery<br><br>Default: <?php echo  wp_automatic_htmlentities('<img src="[img_src]" class="wp_automatic_gallery" />') ;?></div>
		               
		            </div>
               </div>
               
               <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_CL_GALLERY" type="checkbox">
	                    <span class="option-title">
								Add item images as a woo-commerce product gallery

	                    </span>
	                    <br>
	          		</div>
	          		
	          	</div>	
		 
		 </div>
		 
		 <!--  /Craigslist -->
		 
		 <!-- Walmart part -->
		 <div  class="field f_100 typepart Walmart"  style="display:none">
		 
		 	<div class="option clearfix">
				<input name="camp_options[]" value="OPT_WM_CACHE" type="checkbox"> 
				<span class="option-title">Cache items for faster posting</span>
			</div>

		 
		 	<div class="option clearfix">
                    <input data-controls="wm_cat_c" name="camp_options[]" value="OPT_WM_CAT" type="checkbox">
                    <span class="option-title">
							Search a specific category
                    </span>
                    <br>
                    
                    <div id="wm_cat_c" class="field f_100">
		              	<label>
			              	Category ID
		              	</label>

		                <input   value="<?php   echo  @$camp_general['cg_wm_cat']   ?>" name="cg_wm_cat" type="text">
		                
		                <div class="description">Ex add "91083_1074767_4623199" without quotes for "Auto Paint" Category. Check list of IDs <a target="blank" href="http://pastebin.com/56JC4xrB">here</a></div>  
		            	
		              </div>
                    
               </div>
               
               
               <div class="option clearfix">
                    <input data-controls="wm_sort_c" name="camp_options[]" value="OPT_WM_ORDER" type="checkbox">
                    <span class="option-title">
							Set search order
                    </span>
                    <br>
                    
                    <div id="wm_sort_c" class="field f_100">
                    
                       <label>
		                    Sort order
		               </label>
		               <select name="cg_wm_sort">
		               
		                    <option value="relevance"  <?php @wp_automatic_opt_selected('relevance',$camp_general['cg_wm_sort']) ?> >
		                         Relevance
		                    </option>
		                    
		                    <option value="price" <?php @wp_automatic_opt_selected('price',$camp_general['cg_wm_sort']) ?>>Price</option>
		                    <option value="title" <?php @wp_automatic_opt_selected('title',$camp_general['cg_wm_sort']) ?>>Title</option>
		                    <option value="bestseller" <?php @wp_automatic_opt_selected('bestseller',$camp_general['cg_wm_sort']) ?>>Best Seller</option>
		                    <option value="customerRating" <?php @wp_automatic_opt_selected('customerRating',$camp_general['cg_wm_sort']) ?>>Customer Rating</option>
		                    <option value="New" <?php @wp_automatic_opt_selected('New',$camp_general['cg_wm_sort']) ?>>New</option>

		               </select>
		               
		               <label>Sorting Direction</label>
					   <select name="cg_wm_sort_dir">
						
							<option value="asc" <?php @wp_automatic_opt_selected('asc',$camp_general['cg_wm_sort_dir']) ?>>Asc</option>
							<option value="desc" <?php @wp_automatic_opt_selected('desc',$camp_general['cg_wm_sort_dir']) ?>>Desc</option>

					   </select>
					   
					   
						                    
		            </div>
                    
               </div>
               
               
               <div class="option clearfix">
                    
		        <input data-controls="WM_RANGE_c" name="camp_options[]" value="OPT_WM_RANGE" type="checkbox">
		        <span class="option-title">
						Set a price range
		        </span>
		        <br>
		        
		        <div id="WM_RANGE_c" class="field f_100">
		        	
		        	<label>From</label> <input  style="width:100px"   class="no-unify" value="<?php    echo @$camp_general['cg_wm_price_from']   ?>" name="cg_wm_price_from" type="text">
		        	<label>To</label> <input  style="width:100px"  class="no-unify" value="<?php    echo @$camp_general['cg_wm_price_to']   ?>" name="cg_wm_price_to" type="text">
		         
		       </div>
		       
		        <div class="option clearfix">
					<input name="camp_options[]" value="OPT_WM_GALLERY" type="checkbox"> 
					<span class="option-title">Set product images as woo-commerce gallery</span>
				</div>

                    
			</div>
               
		 	
		 
		 </div>
		 <!-- /Reddit part -->
		
		 <!--Rumble part -->
		 <div  class="field  typepart Rumble"  style="display:none">
			
			<div  class="field f_100">
				<label>
					Rumble Search URL/Channel 
				</label>
				
				<input value="<?php   echo @$camp_general['cg_rm_page']  ?>" name="cg_rm_page"   type="text">
				<div class="description">example1: https://rumble.com/search/all?q=crypto<br>example2: https://rumble.com/c/freshandfit<br>example3: https://rumble.com/search/video?q=crypto&sort=views&date=today</div>
				
			</div>

			<div  class="field f_100">
			
				<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RM_IMG" type="checkbox">
	                    <span class="option-title">
								Skip posts with no images
	                    </span>
	                    <br>
	          	</div>
			</div>
		</div>

		<!-- /Rumble part -->

		<!-- Google places part -->
		<div  class="field  typepart Places"  style="display:none">
				
				
				<div  class="field f_100">
				
					

					<!-- Maxmimum image width in pixels -->
					<div class="option clearfix">
							<label>
								Maximum image width in pixels (default 700)
							</label>
							<input placeholder="700" value="<?php   echo @$camp_general['cg_pl_max_img_width']  ?>" name="cg_pl_max_img_width"   type="text">
							<div class="description">ex: 800</div>
					</div>

					<!-- language code optionl eg. fr -->
					<div class="option clearfix">
							<label>
								Language code (optional)
							</label>
							<input placeholder="en" value="<?php   echo @$camp_general['cg_pl_lang']  ?>" name="cg_pl_lang"   type="text">
							<div class="description">ex: fr for French</div>
					</div>
				 
					<!-- cache option only -->
					<div class="option clearfix">
							<input name="camp_options[]" value="OPT_PL_CACHE" type="checkbox">
							<span class="option-title">
									Cache items for faster posting  (uncheck to call Google each post) (pagination will not work if disabled)
							</span>
							<br>
					</div>

					<!-- Import reviews as comments -->
					<div class="option clearfix">
							<input name="camp_options[]" value="OPT_PL_REVIEWS" type="checkbox">
							<span class="option-title">
									Import reviews as comments
							</span>
							<br>
					</div>
				
					<!-- Skip posts with no images -->
					<div class="option clearfix">
							<input name="camp_options[]" value="OPT_PL_IMG" type="checkbox">
							<span class="option-title">
									Skip posts with no images
							</span>
							<br>
					</div>
				</div>
	</div>


		 
		 <!-- Telegram part -->

		 <div  class="field  typepart telegram"  style="display:none">
			
			<div  class="field f_100">
				<label>
					Telegram Channel URL or ID
				</label>
				
				<input value="<?php   echo @$camp_general['cg_te_page']  ?>" name="cg_te_page"   type="text">
				<div class="description">example1: https://telegram.me/s/CryptoMemesHub<br>example2: CryptoMemesHub</div>
				
			</div>

			<div  class="field f_100">
			
				<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_TE_IMG" type="checkbox">
	                    <span class="option-title">
								Skip posts with no images
	                    </span>
	                    <br>
	          	</div>
				<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_TE_TIME" type="checkbox">
	                    <span class="option-title">
								Insert posts with its original source posting time
	                    </span>
	                    <br>
	          	</div>
				<div class="option clearfix">
					<input name="camp_options[]" value="OPT_TE_AUTHOR" type="checkbox">
					<span class="option-title">
							Set the post author from the original post author
					</span>
					<br>
				</div>
				<div class="option clearfix">
					<input name="camp_options[]" value="OPT_TE_TOP" type="checkbox">
					<span class="option-title">
							Only check the latest post (skip older posts)
					</span>
					<br>
				</div>
				<div class="option clearfix">
					<input name="camp_options[]" value="OPT_TE_REVERSE" type="checkbox">
					<span class="option-title">
							Reverse posting order (oldest first)
					</span>
					<br>
				</div>
				<div class="option clearfix">
	                    <input data-controls="wp_automatic_post_filter_te" name="camp_options[]"   value="OPT_TE_POST_FILTER" type="checkbox">
	                    <span class="option-title">
								Post a specific type of posts   
	                    </span>
	                    
	                    <br>
	                    
	                    <div id="wp_automatic_post_filter_te" class="field f_100">
	                		
	                		
	                		<div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_TE_POST_LINK" type="checkbox">
					                    <span class="option-title">
												link  
					                    </span>
					                    <br>
					        </div>
					        
					        <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_TE_POST_IMAGE" type="checkbox">
					                    <span class="option-title">
												Image  
					                    </span>
					                    <br>
					         </div>
	                		 
					         
					         <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_TE_POST_VIDEO" type="checkbox">
					                    <span class="option-title">
												Videos  
					                    </span>
					                    <br>
					         </div>
					         
					         <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_TE_POST_TEXT" type="checkbox">
					                    <span class="option-title">
												Text only posts  
					                    </span>
					                    <br>
					         </div> 					          
	                    	 
	                    </div>
	                    
	         	 </div>
				 
			</div>
						
		</div>	

		 <!-- /Telegram part -->



		  <!-- Reddit part -->
		 
		 <div  class="field f_100 typepart Reddit"  style="display:none">
		 
		 	   <label>
                    Reddits items page url (must display reddits)
               </label>
               
			   <input value="<?php   echo @$camp_general['cg_rd_page']  ?>" name="cg_rd_page"   type="text">
			   <div class="description">*Without any parameters<br><br>example: https://www.reddit.com/r/popular/<br>example: https://www.reddit.com/user/Attica-Attica/submitted/</div>
			   
			    <div  class="field f_100">
			    
				    <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_CACHE" type="checkbox">
	                    <span class="option-title">
								Cache items for faster posting  (uncheck to call Reddit each post)(pagination will not work if disabled)
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_IMG" type="checkbox">
	                    <span class="option-title">
								Skip posts with no images
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_TIME" type="checkbox">
	                    <span class="option-title">
								Post items with its original time
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_COMMENT" type="checkbox">
	                    <span class="option-title">
								Post original comments as wordpress comments
	                    </span>
	                    <br>
	          		</div>

	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_OFFICIAL_EMBED" type="checkbox">
	                    <span class="option-title">
								Use official reddit embed code for videos so sound can work
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
                    
				        <input data-controls="RD_SLIDER_c" name="camp_options[]" value="OPT_RD_SLIDER" type="checkbox">
				        <span class="option-title">
								If more than one image exists in the reddit, grab them all
				        </span>
				        <br>
				        
				        <div id="RD_SLIDER_c" class="field f_100">
				        	
				        	    <label>Single image html</label> 
				        	    
				        	     <input placeholder="<?php echo  wp_automatic_htmlentities( '<img src="[img_src]" />' ,ENT_COMPAT, 'UTF-8')   ?>" value="<?php    echo  wp_automatic_htmlentities($camp_general['cg_rd_full_img_t'],ENT_COMPAT, 'UTF-8')   ?>"  name="cg_rd_full_img_t" type="text">
				         	 <div class="description">Default: <?php echo  wp_automatic_htmlentities( '<img src="[img_src]" />' ,ENT_COMPAT, 'UTF-8')   ?></div>
				        </div>
				                    
				</div>	          		

	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_AUTO" type="checkbox">
	                    <span class="option-title">
								Autoplay embeds if possible
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_LOOP" type="checkbox">
	                    <span class="option-title">
								Loop embeds if possible
	                    </span>
	                    <br>
	          		</div>
	          		
	          		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_AUTHOR" type="checkbox">
	                    <span class="option-title">
								Set the post author from the reddit author
	                    </span>
	                    <br>
	          		</div>
	          		
	          		 <div class="option clearfix">
	                    <input data-controls="wp_automatic_post_filter_rd" name="camp_options[]"   value="OPT_RD_POST_FILTER" type="checkbox">
	                    <span class="option-title">
								Post a specific type of posts   
	                    </span>
	                    
	                    <br>
	                    
	                    <div id="wp_automatic_post_filter_rd" class="field f_100">
	                		
	                		
	                		<div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_RD_POST_LINK" type="checkbox">
					                    <span class="option-title">
												link  
					                    </span>
					                    <br>
					        </div>
					        
					        <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_RD_POST_IMAGE" type="checkbox">
					                    <span class="option-title">
												Image  
					                    </span>
					                    <br>
					         </div>
	                		
	                		<div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_RD_POST_VID" type="checkbox">
					                    <span class="option-title">
												Gifs and videos  
					                    </span>
					                    <br>
					         </div>
					         
					         <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_RD_POST_GIFS" type="checkbox">
					                    <span class="option-title">
												Gifs 
					                    </span>
					                    <br>
					         </div>
					         
					         <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_RD_POST_VIDS" type="checkbox">
					                    <span class="option-title">
												Videos  
					                    </span>
					                    <br>
					         </div>
					         
					         <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_RD_POST_TXT" type="checkbox">
					                    <span class="option-title">
												Text only posts  
					                    </span>
					                    <br>
					         </div> 					          
	                    	 
	                    </div>
	                    
	          </div>
	          
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_TAG" type="checkbox">
	                    <span class="option-title">
								Set reddit flairs as tags
	                    </span>
	                    <br>
	          	</div>
	          	
	          	<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_RD_CAT" type="checkbox">
	                    <span class="option-title">
								Set reddit flairs as categories
	                    </span>
	                    <br>
	          	</div>
	          		
	          	</div>	
		 
		 </div>
		 
		 <!--  /Craigslist -->
		 
		 <!-- Itunes part -->
		 
		 <div  class=" field f_100 typepart Itunes f_100"  style="display:none">
		 
		 	   
			    <div  class="field">

					 	<label>
					 		Media Type :
					 	</label><br>

					 	<select id="cg_iu_media" class="templateChanger" data-filters = "#cg_iu_attribute" name="cg_iu_media">

							<option  value="music"  <?php @wp_automatic_opt_selected('music',$camp_general['cg_iu_media']) ?> >Music</option>
							<option  value="movie"  <?php @wp_automatic_opt_selected('movie',$camp_general['cg_iu_media']) ?> >Movie</option>
							<option  value="podcast"  <?php @wp_automatic_opt_selected('podcast',$camp_general['cg_iu_media']) ?> >Podcast</option>
							<option  value="musicVideo"  <?php @wp_automatic_opt_selected('musicVideo',$camp_general['cg_iu_media']) ?> >Music Video</option>
							<option  value="audiobook"  <?php @wp_automatic_opt_selected('audiobook',$camp_general['cg_iu_media']) ?> >Audio Book</option>
							<option  value="shortFilm"  <?php @wp_automatic_opt_selected('shortFilm',$camp_general['cg_iu_media']) ?> >Short Film</option>
							<option  value="tvShow"  <?php @wp_automatic_opt_selected('tvShow',$camp_general['cg_iu_media']) ?> >Tv Show</option>
							<option  value="software"  <?php @wp_automatic_opt_selected('software',$camp_general['cg_iu_media']) ?> >Software</option>
							<option  value="ebook"  <?php @wp_automatic_opt_selected('ebook',$camp_general['cg_iu_media']) ?> >eBook</option>

					 	</select>
	          		
	          	</div>
	          	
	          	
	          	<div  class="field">

					 	<label>
					 		Search Attribute :
					 	</label>

					 	<select id="cg_iu_attribute" name="cg_iu_attribute" class="no-unify">
							 <?php 
							 	
							 	$medias = explode(',', "movie, podcast, music, musicVideo, audiobook, shortFilm, tvShow, software,ebook");

							 	$attributes['movie']   = "actorTerm, genreIndex, artistTerm, shortFilmTerm, producerTerm, ratingTerm, directorTerm, releaseYearTerm, featureFilmTerm, movieArtistTerm, movieTerm, ratingIndex, descriptionTerm";
							 	$attributes['podcast'] = "titleTerm, languageTerm, authorTerm, genreIndex, artistTerm, ratingIndex, keywordsTerm, descriptionTerm";
							 	$attributes['music']   = "mixTerm, genreIndex, artistTerm, composerTerm, albumTerm, ratingIndex, songTerm";
							 	$attributes['musicVideo']   = "genreIndex, artistTerm, albumTerm, ratingIndex, songTerm";
							 	$attributes['audiobook']   = "titleTerm, authorTerm, genreIndex, ratingIndex";
							 	$attributes['shortFilm']   = "genreIndex, artistTerm, shortFilmTerm, ratingIndex, descriptionTerm";
							 	$attributes['software']   = "softwareDeveloper";
							 	$attributes['tvShow']   = "genreIndex, tvEpisodeTerm, showTerm, tvSeasonTerm, ratingIndex, descriptionTerm";
							 	$attributes['ebook']   = "genreIndex, authorTerm";
							 	
							 	
							 	foreach($medias as $media){
							 		
							 		$media = wp_automatic_trim($media);
							 		
							 		$mediaAttributes = 'All,'.$attributes[$media];
							 		$mediaAttributes = explode(',', $mediaAttributes);
							 		 
							 		
							 		foreach ($mediaAttributes as $mediaAttribute){

							 			$mediaAttribute = wp_automatic_trim($mediaAttribute);

							 			wp_automatic_opt_display($mediaAttribute, $mediaAttribute, $camp_general['cg_iu_attribute'],$media);
							 		
							 		}
							 		 
							 	}
							 	
							 ?>
					 	</select>
	          		
	          	</div>
	          	
	          	
	          	<div class="field f_100">
		          		<div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_IU_CACHE" type="checkbox">
		                    <span class="option-title">
									Cache items for faster posting  (uncheck to call Itunes each post)
		                    </span>
		                    <br>
		          		</div>
		          		
		          		<div class="option clearfix">
		                    <input data-controls="wp_iu_country" name="camp_options[]"  value="OPT_IU_COUNTRY" type="checkbox">
		                    <span class="option-title">
									Specify a country 
		                    </span>
		                    <br>
		                    
		                    <div id="wp_iu_country" class="field f_100">
		                    	<label>Language code</label>
		                    	
		                    	<input value="<?php   echo @$camp_general['cg_iu_lang']  ?>"   name="cg_iu_lang"  type="text">
		                    	<div class="description"><i>Check list <a href="http://en.wikipedia.org/wiki/%20ISO_3166-1_alpha-2">here</a></i></div>
	                    	
	                 		</div>
		 	 	
		 	 			</div>
		          			
	          	</div>
		 
		 </div>
		 
		 <!--  /Itunes -->
		 
		 <div class= "field f_100 typepart Envato" style="display:none">
		 
		 		<select id="cg_ev_filter" class="templateChanger" name="cg_ev_filter" >
                    
                    <option  value="themeforest"  <?php @wp_automatic_opt_selected('themeforest',$camp_general['cg_ev_filter']) ?> >
                         themeforest
                    </option>
                    
                    <option  value="codecanyon"  <?php @wp_automatic_opt_selected('codecanyon',$camp_general['cg_ev_filter']) ?> >
                         codecanyon
                    </option>

                    <option  value="photodune"  <?php @wp_automatic_opt_selected('photodune',$camp_general['cg_ev_filter']) ?> >
                         photodune
                    </option>
                    
                    <option  value="videohive"  <?php @wp_automatic_opt_selected('videohive',$camp_general['cg_ev_filter']) ?> >
                         videohive
                    </option>
                    
                    <option  value="audiojungle"  <?php @wp_automatic_opt_selected('audiojungle',$camp_general['cg_ev_filter']) ?> >
                         audiojungle
                    </option>
                    
                    <option  value="graphicriver"  <?php @wp_automatic_opt_selected('graphicriver',$camp_general['cg_ev_filter']) ?> >
                         graphicriver
                    </option>
                    
                    <option  value="3docean"  <?php @wp_automatic_opt_selected('3docean',$camp_general['cg_ev_filter']) ?> >
                         3docean
                    </option>
                </select>
                
                
			   <div  class="field f_100">
			   
			   	   <div class="option clearfix">
	                    <input data-controls-r=''  name="camp_options[]"  value="OPT_EV_NOKEY" type="checkbox">
	                    <span class="option-title">
								Don't use keywords to filter items
	                    </span>
	               </div>
			   
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_EV_AUTO_TAGS" type="checkbox">
	                    <span class="option-title">
								Post original tags as wordpress tags
	                    </span>
	               </div>
	               
	               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EV_CACHE" type="checkbox">
                    <span class="option-title">
							Cache Items for faster posting  (uncheck to call Envato each post)
                    </span>
                    <br>
                    
             	  </div>
             	  
             	  <div class="option clearfix">
	                    
	                    <input name="camp_options[]"  data-controls="post_ev_categories" value="OPT_EV_CAT" type="checkbox">
	                    <span class="option-title">
								Post items from a specific category
	                    </span>
	                    <br>
	                    
			            <div id="post_ev_categories" class="field f_100">
			               <label for="field6">
			                    Category code
			               </label>
			               
			                <input value="<?php    echo @$camp_general['cg_ev_cat']   ?>"  name="cg_ev_cat" type="text">
			                <div class="description">For example add "wordpress" for this <a href="https://themeforest.net/category/wordpress">category</a></div>
			            </div>
			            
	               </div>
	               
	               <div class="option clearfix">
	                    
	                    <input name="camp_options[]"  data-controls="post_ev_tags" value="OPT_EV_TAGS" type="checkbox">
	                    <span class="option-title">
								Post items containing a specific tag
	                    </span>
	                    <br>
	                    
			            <div id="post_ev_tags" class="field f_100">
			               <label for="field6">
			                    tags list comma separated
			               </label>
			               
			                <input value="<?php    echo @$camp_general['cg_ev_tags']   ?>"  name="cg_ev_tags" type="text">
			               
			            </div>
			            
	               </div>
	               
	               <div class="option clearfix">
	                    
	                    <input name="camp_options[]"  data-controls="post_ev_author" value="OPT_EV_AUTHOR" type="checkbox">
	                    <span class="option-title">
								Post items from a specific author
	                    </span>
	                    <br>
	                    
			            <div id="post_ev_author" class="field f_100">
			               <label for="field6">
			                    Author username
			               </label>
			               
			                <input value="<?php    echo @$camp_general['cg_ev_author']   ?>"  name="cg_ev_author" type="text">
			                <div class="description">For example add "themefusion" for this <a href="https://themeforest.net/user/themefusion">Author</a></div>
			            </div>
			            
	               </div>
	               
			   </div> 
			    
			  
			 
			 <div class="field f_100">
               <label for="field1zz">
                    Sort by
               </label>
               <select name="cg_ev_sort">
               		
               		<option value="" ></option>
               		
                    <option value="following"  <?php @wp_automatic_opt_selected('following',$camp_general['cg_ev_sort']) ?> >
                         Following
                    </option>
                    <option value="relevance"  <?php @wp_automatic_opt_selected('relevance',$camp_general['cg_ev_sort']) ?>  >
                         Relevance
                    </option>
                    <option value="rating"  <?php @wp_automatic_opt_selected('rating',$camp_general['cg_ev_sort']) ?>  >
                         Rating
                    </option> 
                    <option value="sales"  <?php @wp_automatic_opt_selected('sales',$camp_general['cg_ev_sort']) ?>  >
                         Sales
                    </option>
                    <option value="rating"  <?php @wp_automatic_opt_selected('rating',$camp_general['cg_ev_sort']) ?>  >
                         Rating
                    </option>
                    <option value="price"  <?php @wp_automatic_opt_selected('price',$camp_general['cg_ev_sort']) ?>  >
                         Price
                    </option>
                    <option value="date"  <?php @wp_automatic_opt_selected('date',$camp_general['cg_ev_sort']) ?>  >
                         Date
                    </option>
                    <option value="updated"  <?php @wp_automatic_opt_selected('updated',$camp_general['cg_ev_sort']) ?>  >
                         Updated
                    </option>
                    <option value="category"  <?php @wp_automatic_opt_selected('category',$camp_general['cg_ev_sort']) ?>  >
                         Category
                    </option>
                    <option value="name"  <?php @wp_automatic_opt_selected('name',$camp_general['cg_ev_sort']) ?>  >
                         Name
                    </option>
                    <option value="trending"  <?php @wp_automatic_opt_selected('trending',$camp_general['cg_ev_sort']) ?>  >
                         Trending
                    </option>
                    
                    
                    
                     
                </select>
             </div>
			 
			 <div class="field f_100">
               <label>
                    Sort Direction
               </label>
               <select name="cg_ev_sort_dir">
               
               		<option value="" ></option>
               		
                    <option value="asc"  <?php @wp_automatic_opt_selected('asc',$camp_general['cg_ev_sort_dir']) ?> >
                         asc
                    </option>
                    <option value="desc"  <?php @wp_automatic_opt_selected('desc',$camp_general['cg_ev_sort_dir']) ?>  >
                         desc
                    </option>
                      
                </select>
             </div>
			 
			 <div  class="field f_100">
	               <div class="option clearfix">
	                    
	                    <input name="camp_options[]"  data-controls="post_ev_api" value="OPT_EV_API" type="checkbox">
	                    <span class="option-title">
								append other api parameters (Advanced)
	                    </span>
	                    <br>
	                    
			            <div id="post_ev_api" class="field f_100">
			               <label>
			                    Parameters
			               </label>
			               
			                <input value="<?php    echo @$camp_general['cg_ev_api']   ?>"  name="cg_ev_api" type="text">
			                <div class="description">example  "&length_min=1:00&length_max=5:00" This part will be appended to the request to Envato check allowed parameters <a href="https://build.envato.com/api/#search_GET_search_item_json">here</a></div>
			            </div>
			            
	               </div>
			 </div>
			 
                
		 
		 </div>
		 
		 <!--  Facebook part -->
		 
		 <div  class="field f_100 typepart Facebook"  style="display:none">
		 
			   <label>
                    Page, group or profile?
               </label>
               
               <select name="cg_fb_source" >
               
			   		  <option  value="profile"  <?php @wp_automatic_opt_selected('profile',$camp_general['cg_fb_source']) ?>  >
                         Profile or a page with the new page experience
                    </option>

                    <option  value="page"  <?php @wp_automatic_opt_selected('page',$camp_general['cg_fb_source']) ?> >
                         Page with a legacy like button (old page Layout)
                    </option>
                    
                   
                    
                   
  
                    <option  value="group"  <?php @wp_automatic_opt_selected('group',$camp_general['cg_fb_source']) ?>  >
                          Group
                    </option> 
                    
                     
                </select>

				<div class="description">*Leacy like button is the blue button as seen on Facebook CNN page here https://web.facebook.com/cnn, other gray like button/follow button is the new layout <br></div>
		 		
		 		
		 
			 <label>
			                    Facebook Page/Public Group url or Numeric ID 
			 </label>
			 
			 <input value="<?php   echo @$camp_general['cg_fb_page']  ?>" name="cg_fb_page"   type="text">
			 <input value="<?php   echo @$camp_general['cg_fb_page_id']  ?>" name="cg_fb_page_id"   type="hidden" >
			 <div class="description">Just paste the Page link or add the Numeric ID manually, <a href="http://valvepress.com/?p=565" target="_blank">How to get the id?</a></div>

 			            
 

		 
		 
		 <div  class="field">

		  <label>Posts from</label>
				
				<select id="cg_fb_from" name="cg_fb_from"  class="templateChanger"  >
				 	<option value="posts" <?php @wp_automatic_opt_selected('posts',$camp_general['cg_fb_from']) ?>>Page timeline posts</option>
					<option value="events" <?php @wp_automatic_opt_selected('events',$camp_general['cg_fb_from']) ?>>Page events</option>
				</select>
		 	 
			  <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_ORIGINAL_FB_TIME" type="checkbox">
	                    <span class="option-title">
								Add posts with its original time   
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input data-controls="wp_automatic_post_filter_div" name="camp_options[]"   value="OPT_FB_POST_FILTER" type="checkbox">
	                    <span class="option-title">
								Post a specific type of posts   
	                    </span>
	                    
	                    <br>
	                    
	                    <div id="wp_automatic_post_filter_div" class="field f_100">
	                		
	                		
	                		<div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_link" type="checkbox">
					                    <span class="option-title">
												link  
					                    </span>
					                    <br>
					          </div>
	                		
	                		<div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_photo" type="checkbox">
					                    <span class="option-title">
												Photo  
					                    </span>
					                    <br>
					          </div>
					          
					          <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_album" type="checkbox">
					                    <span class="option-title">
												Photo Album  
					                    </span>
					                    <br>
					          </div>
					                    
					          <div class="option clearfix">          
					                    <input name="camp_options[]"   value="OPT_FB_POST_video" type="checkbox">
					                    <span class="option-title">
												Video   
					                    </span>
					                    <br>
					           </div>
					           
					            <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_status" type="checkbox">
					                    <span class="option-title">
												Status  
					                    </span>
					                    <br>
					          </div>         
					                 
					          <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_event" type="checkbox">
					                    <span class="option-title">
												Event  
					                    </span>
					                    <br>
					          </div>
					          
					          <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_offer" type="checkbox">
					                    <span class="option-title">
												Offer  
					                    </span>
					                    <br>
					          </div>
					          
					          <div class="option clearfix">
					                    <input name="camp_options[]"   value="OPT_FB_POST_note" type="checkbox">
					                    <span class="option-title">
												Note  
					                    </span>
					                    <br>
					          </div>               
					          
	                    	 
	                    </div>
	                    
	          </div>
	          
	          
	          <div class="option clearfix">
	                    <input data-controls="wp_fb_title_count" name="camp_options[]"   value="OPT_GENERATE_FB_TITLE" type="checkbox">
	                    <span class="option-title">
								Auto generate title from content for posts with no title    
	                    </span>
	                    <br>
	                    
	                    <div id="wp_fb_title_count" class="field f_100">
                    	
                    	<label>Limit title to x chars (default is 80 )</label>
                    	<input value="<?php   echo @$camp_general['cg_fb_title_count']  ?>" placeholder="80" name="cg_fb_title_count"  type="text">
	                    
	                    <div class="option clearfix">
		                    <input  data-controls="wp_fb_strip_title"   name="camp_options[]"   value="OPT_GENERATE_FB_RETURN" type="checkbox">
		                    <span class="option-title">
									Stop at line breaks (i.e take the title before a new line)    
		                    </span>
	                    	
	                    		<br>
	                    		 <div id="wp_fb_strip_title" class="field f_100">
	                    		 		
	                    		 		 <input name="camp_options[]"   value="OPT_FB_STRIP_TITLE" type="checkbox">
				                    <span class="option-title">
											Strip the first line after setting it as the title    
				                    </span>
				                    <br>
	                    		 		
	                    		 </div>
	                    
	                    </div>
	                    
	                    <div class="option clearfix">
		                    <input  name="camp_options[]"   value="OPT_GENERATE_FB_DOT" type="checkbox">
		                    <span class="option-title">
									Don't add "..." after generated title    
		                    </span>
	                    </div>
	                    
	                    <div class="option clearfix">
		                    <input  name="camp_options[]"   value="OPT_GENERATE_NO_LINK" type="checkbox">
		                    <span class="option-title">
									Generate for links as well (by default link title is used)    
		                    </span>
	                    </div>
	                    
	                    <br>
	                    	
	                    </div>
	          </div>
	          
	          <div class="option clearfix">
					<input data-controls="wp_fb_title_default" name="camp_options[]" value="OPT_GENERATE_FB_TITLE_DEFAULT" type="checkbox"> <span class="option-title">Set a default title text if it was not possible to generate a title</span> <br>

					<div id="wp_fb_title_default" class="field f_100">

						<label>Default title text</label> <input value="<?php   echo @$camp_general['cg_fb_title_default']  ?>" name="cg_fb_title_default" type="text">
					</div><br>
				</div>

	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_TITLE_SKIP" type="checkbox">
	                    <span class="option-title">
								Skip posts with no title     
	                    </span>
	                    <br>
	          </div>

	          
	         <div class="option clearfix">
	                    <input data-controls="OPT_FB_COMMENT_IMG" name="camp_options[]"   value="OPT_FB_COMMENT" type="checkbox">
	                    <span class="option-title">
								Post FB comments as comments    
	                    </span>
	                    <br>
	                    
	                    <div id="OPT_FB_COMMENT_IMG" class="field f_100">
	                    		
	                    		<div class="option clearfix">
	                    			<input name="camp_options[]"   value="OPT_FB_COMMENT_IMG" type="checkbox">
			                    <span class="option-title">
										No commenter image
			                    </span>
			                 
	                    		</div>
	                    		   
	                    		 <div class="option clearfix">  
				                    <input name="camp_options[]"   value="OPT_FB_COMMENT_IMG_CNT" type="checkbox">
				                    <span class="option-title">
											Add the comment image if existing
				                    </span>
			                    </div>
	                    	
	                    </div>
	                    
	          </div> 
	          
	           <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_TAGS" type="checkbox">
	                    <span class="option-title">
								Post FB hashtags as WordPress tags
	                    </span>
	                    <br>
	          </div> 
	          
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_TXT_SKIP" type="checkbox">
	                    <span class="option-title">
								Strip textual content     
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_VID_SKIP" type="checkbox">
	                    <span class="option-title">
								Strip videos embed codes from the content
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_VID_TXT_SKIP" type="checkbox">
	                    <span class="option-title">
								Strip video description     
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_VID_AUTO" type="checkbox">
	                    <span class="option-title">
								Auto play embeded videos (Audio will mute)   
	                    </span>
	                    <br>
	          </div>
	          
 	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_VID_MUTE" type="checkbox">
	                    <span class="option-title">
								Mute embeded videos by default     
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_IMG_SKIP" type="checkbox">
	                    <span class="option-title">
								Skip posts with no Image     
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_VID_IMG_HIDE" type="checkbox">
	                    <span class="option-title">
								Hide video image from the post     
	                    </span>
	                    <br>
	          </div>
	          
	           <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_IMG_BTM" type="checkbox">
	                    <span class="option-title">
								Images at the bottom (By default on top)     
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_IMG_LNK_DISABLE" type="checkbox">
	                    <span class="option-title">
								Don't link images to its src     
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_FB_CACHE" type="checkbox">
	                    <span class="option-title">
								Cache items for faster posting ( Disabling this option posts from latest 5 posts only )   
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_FB_OLD" type="checkbox">
	                    <span class="option-title">
								Post old posts as well (By default it posts from the latest 5 )(cache option must be enabled for this option to function)
	                    </span>
	                    <br>
	          </div>
	          
	          <div class="option clearfix">
							<input name="camp_options[]" value="OPT_FB_NEW" type="checkbox"> <span class="option-title"> Skip posts that share another post (only get newly created posts) </span> <br>
			</div>
	          
	          <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_FB_OWNER" type="checkbox">
	                    <span class="option-title">
								When posting from a profile, only post profile owner posts (Exclude posts tagged by others)
	                    </span>
	                    <br>
	          </div>
		       
	          
          </div>
		 </div>
		 <!--  /Facebook part -->
		 
		 
		 <!-- amazon part -->
		 
		 
          <div id="field1zz-container" class="typepart Amazon"  style="display:none">
          
          
          		<?php 
		     	
		     	// supported  ext http://docs.aws.amazon.com/AWSECommerceService/latest/DG/Locales.html
		     	
		     	?>	
 		        <div class="field f_100">
 		        <label for="field1zzg">
                    Amazon site extention (region) :
               </label>
               <select data-filters="#field_amazon_cat" name="camp_amazon_region" id="field1zzg">
							 <option  value="com"  <?php @wp_automatic_opt_selected('com',$camp_amazon_region) ?> >amazon.com</option> 
							 <option  value="co.uk"  <?php @wp_automatic_opt_selected('co.uk',$camp_amazon_region) ?> >amazon.co.uk</option>
							 <option  value="ca"  <?php @wp_automatic_opt_selected('ca',$camp_amazon_region) ?> >amazon.ca</option>
							 <option  value="de"  <?php @wp_automatic_opt_selected('de',$camp_amazon_region) ?> >amazon.de</option>
							 <option  value="fr"  <?php @wp_automatic_opt_selected('fr',$camp_amazon_region) ?> >amazon.fr</option>
							 <option  value="it"  <?php @wp_automatic_opt_selected('it',$camp_amazon_region) ?> >amazon.it</option>
							 <option  value="es"  <?php @wp_automatic_opt_selected('es',$camp_amazon_region) ?> >amazon.es</option>
							 <option  value="cn"  <?php @wp_automatic_opt_selected('cn',$camp_amazon_region) ?> >amazon.cn</option>
							 <option  value="co.jp"  <?php @wp_automatic_opt_selected('co.jp',$camp_amazon_region) ?> >amazon.co.jp</option>
							 <option  value="in"  <?php @wp_automatic_opt_selected('in',$camp_amazon_region) ?> >amazon.in</option>
							 <option  value="com.br"  <?php @wp_automatic_opt_selected('com.br',$camp_amazon_region) ?> >amazon.com.br</option>
							  <option  value="com.mx"  <?php @wp_automatic_opt_selected('com.mx',$camp_amazon_region) ?> >amazon.com.mx</option>
							  <option  value="com.au"  <?php @wp_automatic_opt_selected('com.au',$camp_amazon_region) ?> >amazon.com.au</option>
							  <option  value="ae"  <?php @wp_automatic_opt_selected('ae',$camp_amazon_region) ?> >amazon.ae</option>
							  <option  value="com.tr"  <?php @wp_automatic_opt_selected('com.tr',$camp_amazon_region) ?> >amazon.com.tr</option>
							   <option  value="sg"  <?php @wp_automatic_opt_selected('sg',$camp_amazon_region) ?> >amazon.sg</option>
							   <option  value="nl"  <?php @wp_automatic_opt_selected('nl',$camp_amazon_region) ?> >amazon.nl</option>
							   <option  value="sa"  <?php @wp_automatic_opt_selected('sa',$camp_amazon_region) ?> >amazon.sa</option>
							   <option  value="eg"  <?php @wp_automatic_opt_selected('eg',$camp_amazon_region) ?> >amazon.eg</option>
							   <option  value="se"  <?php @wp_automatic_opt_selected('se',$camp_amazon_region) ?> >amazon.se</option>
							   <option  value="pl"  <?php @wp_automatic_opt_selected('pl',$camp_amazon_region) ?> >amazon.pl</option>
 		        </select>
 		        </div>
          
          	<?php if( ! in_array( 'OPT_AMAZON_NOAPI', $wp_automatic_options) && wp_automatic_trim( $wp_amazonpin_abk ) != ''  ) {?>
          
          	<div class="field f_100">
               <label for="field1zz">
                    Amazon category (also named Search Index) : 
               </label>
               <select  class="no-unify" name="camp_amazon_cat" id="field_amazon_cat" style="margin-bottom:30px">
              
              				<?php
              					
              					foreach ($searchIndex as $key => $mainIndex){
									$catNames = $mainIndex[0];
									$catVals  = $mainIndex[1];
									
									$i=0;
									foreach ($catNames as $catName){
										 
										if( wp_automatic_trim($catName) == '' ){
											$catName = $catVals[$i];
										}  
										
										  echo '<option data-filter-val="'.$key.'" value = "'.$catVals[$i].'" ';
										
										if($camp_amazon_region == $key){
											@wp_automatic_opt_selected( $catVals[$i] ,$camp_amazon_category);
										}
										
										  echo '>'.$catName . '</option>';
										$i++;
									}
              					}
              				?>
								
								
								              
							 
 		        </select>
 		        </div>
 		        
 		        <?php } ?>
 		        
 		      <div>
 		      
 		      
               <?php if( ! in_array( 'OPT_AMAZON_NOAPI', $wp_automatic_options) && wp_automatic_trim( $wp_amazonpin_abk ) != ''  ) {?>           
 		      
 		      <div class="field f_100">
               <div class="option clearfix">
                     
                    <input name="camp_options[]" id="amazon_node" data-controls="amazon_node_c" value="OPT_AMAZON_NODE" type="checkbox">
                    <span class="option-title">
							Specify a Browse Node (sub-category) 
                    </span>
                    <br>
                    
		            <div id="amazon_node_c" class="field f_100">
		               <label>
		                    Browse Node ID (click <a href="https://www.browsenodes.com/">here</a> to get browse node id's) (You must select the right category above matching this node)
		               </label>
		               <input value="<?php   echo @$camp_general['cg_am_node']  ?>" name="cg_am_node"   type="text">
		            
		            	
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]" id="amazon_full" value="OPT_AM_FULL" type="checkbox">
		                    <span class="option-title">
									Don't use keywords add items from browse node without filtering . 
		                    </span>
	                    </div>
		            
		            </div>
		            
               </div>
               </div>
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input id="am_order" data-controls="am_order_c" name="camp_options[]" value="OPT_AM_ORDER" type="checkbox">
                    <span class="option-title">
							Set items search order
                    </span>
                    <br>
                    
                    <div id="am_order_c" class="field f_100">
		              	<label>
		              	Order 
		              	</label>
		              	<br>
		                
		                <select  name="cg_am_order" style="min-width:140px" >
			               		<?php 
			               		$params = array( "Relevance	" , "AvgCustomerReviews" , "Featured" , "NewestArrivals" , "Price:HighToLow" , "Price:LowToHigh	");
				               			foreach ($params as $param){
								?>
	
											<option  value="<?php   echo $param ?>"  <?php @wp_automatic_opt_selected($param,$camp_general['cg_am_order']) ?> ><?php   echo $param ?></option>
	
								<?php 
				               				
				               			}
			               		?>
			               		
			               		 
			               		 
			               	</select>
		                
		              
		            	
		              </div>
                    
               </div>
               </div>
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input id="am_condition" data-controls="am_condition_c" name="camp_options[]" value="OPT_AM_CONDITION" type="checkbox">
                    <span class="option-title">
							Set items condition
                    </span>
                    <br>
                    
                    <div id="am_condition_c" class="field f_100">
		              	<label>
		              	Condition 
		              	</label>
		                <br>
		                <select  name="cg_am_condition" >
			               		<?php 
			               		$params = array( "Any" , "New" , "Used" , "Collectible" , "Refurbished" );
				               			foreach ($params as $param){
								?>
	
											<option  value="<?php   echo $param ?>"  <?php @wp_automatic_opt_selected($param,$camp_general['cg_am_condition']) ?> ><?php   echo $param ?></option>
	
								<?php 
				               				
				               			}
			               		?>
			               		
			               		 
			               		 
			               	</select>
		                
		              
		            	
		              </div>
                    
               </div>
               </div>
               
               <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" id="amazon_param" data-controls="amazon_param_c" value="OPT_AMAZON_PARAM" type="checkbox">
                    <span class="option-title">
							Set a search criteria  
                    </span>
                    <br>
                    
		            <div id="amazon_param_c" class="field f_100">
		                
		                <?php 
		                	$params=array('Actor','Artist','Author','Brand');
		                ?>
		                
		                <div >
			               	<select style="min-width:140px"  name="cg_am_param_type" >
			               		<?php 
				               			foreach ($params as $param){
								?>
	
											<option  value="<?php   echo $param ?>"  <?php @wp_automatic_opt_selected($param,$camp_general['cg_am_param_type']) ?> ><?php   echo $param ?></option>
	
								<?php 
				               				
				               			}
			               		?>
			               		
			               		 
			               		 
			               	</select>
		                 </div>
		                 <br>
		               
		                <div >
		               		<input value="<?php   echo @$camp_general['cg_am_param']  ?>" name="cg_am_param"   type="text">
		                </div>
		               
		               <div style="clear:both" class="description">
			               <p>e.g: Actor: Tom Cruise</p>
		               </div>
		               <input  data-controls-r='' name="camp_options[]"  value="cg_am_param_ex" type="checkbox">
		                    <span class="option-title">
									Don't use keywords just use this criteria . 
		                    </span>
		               
		            </div>
		            
               </div>
               </div>
                  
                <div class="field f_100">  
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_AMAZON_MERCHANT" type="checkbox">
                    <span class="option-title">
							Only post items sold by Amazon (no merchants) 
                    </span>
                    <br>
               </div>
               </div>
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_AMAZON_NOMAGIC" type="checkbox">
                    <span class="option-title">
							Do not use the plugin magic search trick to get unlimited number of products
                    </span>
                    
               	<div style="margin-left:25px" class="description"><br><i>(By default, search request returns maximum of 100 and using this trick, the plugin will search multiple times using different price points to multiply this number)</i></div>
               	<br>
               </div>
               
               </div>
               
               <?php }else{ ?>
               
               <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]"   data-controls="amazon_custom_url" value="OPT_AMAZON_CUSTOM" type="checkbox">
                    <span class="option-title">
							Custom amazon search URL/Best sellers/Whishlist (Amazon link containing a list of products ) 
                    </span>
                    <br>
                    
		            <div id="amazon_custom_url" class="field f_100">
		               <label>
		                    Amazon Search URL 
		               </label>
		               <input type="text" name="cg_am_custom_urls" value="<?php   echo @$camp_general['cg_am_custom_urls']  ?>">
		            	<div class="description">*Use [keyword] inside the URL if needed<br><br>Example 1 (Search page): https://www.amazon.com/s?k=[keyword]&s=price-desc-rank<br><br>Example 2 (Top sellers): https://www.amazon.com/Best-Sellers-Appliances/zgbs/appliances/<br><br>Example 3 (Whishlist): https://www.amazon.com/hz/wishlist/ls/1J5CXJUWGMDIO</div>
		            	
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]"   value="OPT_AM_NO_KEYS" type="checkbox">
		                    <span class="option-title">
									Don't use keywords and add items from above search URLs directly 
		                    </span>
	                    </div>
		            
		        </div>
		        </div>
		        </div>

               <div class="field f_100">
	               <div class="option clearfix">
	                    
	                    <input data-controls-r='' name="camp_options[]"   data-controls="amazon_custom_html" value="OPT_AMAZON_CUSTOM_HTML" type="checkbox">
	                    <span class="option-title">
								Extract products from added page HTML 
	                    </span>
	                    <br>
	                    
			            <div id="amazon_custom_html" class="field f_100">
			              
			               <label>
			                    HTML of the page that contains the product links 
			               </label>
			              	  <textarea  name="cg_am_html"><?php echo wp_automatic_htmlentities( @$camp_general['cg_am_html'],ENT_COMPAT, 'UTF-8')  ?></textarea>
				            	<div class="description">*Paste the whole page HTML that contains the products<br><br>*Suitable for infinte scroll pages like whichlists, check <a href="https://valvepress.com/how-to-import-all-products-from-amazon-wish-list-to-wordpress-using-wordpress-automatic-plugin/" target="_blank">this tutorial</a> on how to import using this option</div>
				            	
				             
			            
			       		 </div>
			        </div>
		        </div>
		        
		        <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_AMAZON_CATS" type="checkbox">
                    <span class="option-title">
							Set the post categories from the original product categories  
                    </span>
                    <br>
               </div>
               </div>
		        
		        <?php } ?>
		        
		        <div class="field f_100">
		        <div class="option clearfix">
                    <input id="am_price" data-controls="am_price_c" name="camp_options[]" value="OPT_AM_PRICE" type="checkbox">
                    <span class="option-title">
							Price range in pennies. For example, 3241 represents $32.41.
                    </span>
                    <br>
                    
                    <div id="am_price_c" class="field f_100">
		              
		                From <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_am_min']   ?>" name="cg_am_min"    type="text"> To <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_am_max']   ?>" name="cg_am_max"    type="text">
		            	
		              </div>
                    
               </div>
               </div>
               
               
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]" data-controls="am_full_img_t"  value="OPT_AM_FULL_IMG" type="checkbox">

                    <span class="option-title">
							Modify item images HTML code
                    </span>
                    
                    <br>
                    
		            <div id="am_full_img_t" class="field f_100">
		               <label for="field6">
		                    Image template  
		               </label>
		               
		                <input placeholder="<?php echo  wp_automatic_htmlentities('<img src="[img_src]" class="wp_automatic_gallery" />') ;?>"  value="<?php    echo wp_automatic_htmlentities(@$camp_general['cg_am_full_img_t'],ENT_COMPAT, 'UTF-8')   ?>"  name="cg_am_full_img_t" type="text">
		               	
		               	<div class="description">This is how the plugin will build images html for the [product_imgs_html] tag.<br><br>*Use [img_src] to replace the image src.<br><br> *Use class="wp_automatic_gallery" to display images as gallery<br><br>Default: <?php echo  wp_automatic_htmlentities('<img src="[img_src]" class="wp_automatic_gallery" />') ;?></div>
		               
		            </div>
               </div>
               </div>
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_AM_GALLERY" type="checkbox">
                    <span class="option-title">
							Add item images as a woo-commerce product gallery 
                    </span>
                    <br>
               </div>
               </div>

			   <?php //if not using the API 
			   		if( in_array( 'OPT_AMAZON_NOAPI', $wp_automatic_options) || wp_automatic_trim( $wp_amazonpin_abk ) == ''  ) {	
				?>  	
				
				<div class="field f_100">
				   <div class="option clearfix">
					   <input name="camp_options[]" value="OPT_AM_REVIEWS" type="checkbox">
					   <span class="option-title">
						   Import original reviews as WooCommerce product reviews
					   </span>
					   <br>
					</div>
				</div>
					
				<?php } ?>
					
			 
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_LINK_CHART" type="checkbox">
                    <span class="option-title">
							Make purchase link driectly to the amazon cart add page 
                    </span>
                    <br>
               </div>
               </div>
               
               <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_AMAZON_EXCERPT" type="checkbox">
                    <span class="option-title">
							Set the product description as excerpt  
                    </span>
                    <br>
               </div>
               </div>
 
               <div class="field f_100">
	               <div class="option clearfix">
	                    <input name="camp_options[]" data-controls="am_location"  value="OPT_AM_LOC" type="checkbox">
	
	                    <span class="option-title">
								Simulate a custom  location (If your server exists on a different country than current amazon one)
	                    </span>
	                    
	                    <br>
	                    
			            <div id="am_location" class="field f_100">
			               <label>
			                    session-id	 cookie value  
			               </label>
			               
			                <input   value="<?php    echo  (@$camp_general['cg_am_session'] )   ?>"  name="cg_am_session" type="text">
			               	
			               	<br>
			               	
			               	<label>
			                   ubid-	 cookie value (starts with ubid-)    
			               </label>
			               
			                <input   value="<?php    echo  (@$camp_general['cg_am_ubid'] )   ?>"  name="cg_am_ubid" type="text">
			               	 
			               	
			               	<div class="description">Example: ubid-main or ubid-acbuk<br><br>If you are posting from amazon.com but your server is not located in US, set these values to get same products that get displayed for US users. If you do not set them, you will get too less products<br><br>Check <a href="https://valvepress.com/how-to-import-amazon-products-from-a-specific-country-using-wordpress-automatic/" target="_blank">this tutorial</a> on how to get these values</div>
			               
			            </div>
	               </div>
               </div>
               
               
               
		 </div>
 		        
          </div>
		 <!-- / amazon part -->
		 
		 <!-- Min date part -->
		 
		 <div id="yt_date_div" class="typepart Youtube Feeds Multi BingNews GoogleNews Instagram Facebook Twitter SoundCloud DailyMotion field f_100">
               <div class="option clearfix">
                    
                    <input data-controls="yt_date_c" name="camp_options[]" id="yt_date" value="OPT_YT_DATE" type="checkbox">
                    <span class="option-title">
							Exclude Item if it is older than a specific date or if specific amount of time passed since the item was published
                    </span>
                    <br>
                    
		            <div id="yt_date_c" class="field f_100">
		               
		               <div id="yt_date_c_d" class="field f_100">
		                

					   <div id="specific_date_skip">

							Day:
							
							<select style="width:80px" class="no-unify" name="cg_yt_dte_day" >  
								<option value='01'  <?php @wp_automatic_opt_selected('01',$camp_general['cg_yt_dte_day']) ?> >01</option>
								<option value='02'  <?php @wp_automatic_opt_selected('02',$camp_general['cg_yt_dte_day']) ?> >02</option>
								<option value='03'  <?php @wp_automatic_opt_selected('03',$camp_general['cg_yt_dte_day']) ?> >03</option>
								<option value='04'  <?php @wp_automatic_opt_selected('04',$camp_general['cg_yt_dte_day']) ?> >04</option>
								<option value='05'  <?php @wp_automatic_opt_selected('05',$camp_general['cg_yt_dte_day']) ?> >05</option>
								<option value='06'  <?php @wp_automatic_opt_selected('06',$camp_general['cg_yt_dte_day']) ?> >06</option>
								<option value='07'  <?php @wp_automatic_opt_selected('07',$camp_general['cg_yt_dte_day']) ?> >07</option>
								<option value='08'  <?php @wp_automatic_opt_selected('08',$camp_general['cg_yt_dte_day']) ?> >08</option>
								<option value='09'  <?php @wp_automatic_opt_selected('09',$camp_general['cg_yt_dte_day']) ?> >09</option>
								<option value='10'  <?php @wp_automatic_opt_selected('10',$camp_general['cg_yt_dte_day']) ?> >10</option>
								<option value='11'  <?php @wp_automatic_opt_selected('11',$camp_general['cg_yt_dte_day']) ?> >11</option>
								<option value='12'  <?php @wp_automatic_opt_selected('12',$camp_general['cg_yt_dte_day']) ?> >12</option>
								<option value='13'  <?php @wp_automatic_opt_selected('13',$camp_general['cg_yt_dte_day']) ?> >13</option>
								<option value='14'  <?php @wp_automatic_opt_selected('14',$camp_general['cg_yt_dte_day']) ?> >14</option>
								<option value='15'  <?php @wp_automatic_opt_selected('15',$camp_general['cg_yt_dte_day']) ?> >15</option>
								<option value='16'  <?php @wp_automatic_opt_selected('16',$camp_general['cg_yt_dte_day']) ?> >16</option>
								<option value='17'  <?php @wp_automatic_opt_selected('17',$camp_general['cg_yt_dte_day']) ?> >17</option>
								<option value='18'  <?php @wp_automatic_opt_selected('18',$camp_general['cg_yt_dte_day']) ?> >18</option>
								<option value='19'  <?php @wp_automatic_opt_selected('19',$camp_general['cg_yt_dte_day']) ?> >19</option>
								<option value='20'  <?php @wp_automatic_opt_selected('20',$camp_general['cg_yt_dte_day']) ?> >20</option>
								<option value='21'  <?php @wp_automatic_opt_selected('21',$camp_general['cg_yt_dte_day']) ?> >21</option>
								<option value='22'  <?php @wp_automatic_opt_selected('22',$camp_general['cg_yt_dte_day']) ?> >22</option>
								<option value='23'  <?php @wp_automatic_opt_selected('23',$camp_general['cg_yt_dte_day']) ?> >23</option>
								<option value='24'  <?php @wp_automatic_opt_selected('24',$camp_general['cg_yt_dte_day']) ?> >24</option>
								<option value='25'  <?php @wp_automatic_opt_selected('25',$camp_general['cg_yt_dte_day']) ?> >25</option>
								<option value='26'  <?php @wp_automatic_opt_selected('26',$camp_general['cg_yt_dte_day']) ?> >26</option>
								<option value='27'  <?php @wp_automatic_opt_selected('27',$camp_general['cg_yt_dte_day']) ?> >27</option>
								<option value='28'  <?php @wp_automatic_opt_selected('28',$camp_general['cg_yt_dte_day']) ?> >28</option>
								<option value='29'  <?php @wp_automatic_opt_selected('29',$camp_general['cg_yt_dte_day']) ?> >29</option>
								<option value='30'  <?php @wp_automatic_opt_selected('30',$camp_general['cg_yt_dte_day']) ?> >30</option>
								<option value='31'  <?php @wp_automatic_opt_selected('31',$camp_general['cg_yt_dte_day']) ?> >31</option>
							</select>
							Month:
							<select  style="width:80px" class="no-unify"  name="cg_yt_dte_month" >
								<option value='01'  <?php @wp_automatic_opt_selected('01',$camp_general['cg_yt_dte_month']) ?> >January</option>
								<option value='02'  <?php @wp_automatic_opt_selected('02',$camp_general['cg_yt_dte_month']) ?> >February</option>
								<option value='03'  <?php @wp_automatic_opt_selected('03',$camp_general['cg_yt_dte_month']) ?> >March</option>
								<option value='04'  <?php @wp_automatic_opt_selected('04',$camp_general['cg_yt_dte_month']) ?> >April</option>
								<option value='05'  <?php @wp_automatic_opt_selected('05',$camp_general['cg_yt_dte_month']) ?> >May</option>
								<option value='06'  <?php @wp_automatic_opt_selected('06',$camp_general['cg_yt_dte_month']) ?> >June</option>
								<option value='07'  <?php @wp_automatic_opt_selected('07',$camp_general['cg_yt_dte_month']) ?> >July</option>
								<option value='08'  <?php @wp_automatic_opt_selected('08',$camp_general['cg_yt_dte_month']) ?> >August</option>
								<option value='09'  <?php @wp_automatic_opt_selected('09',$camp_general['cg_yt_dte_month']) ?> >September</option>
								<option value='10'  <?php @wp_automatic_opt_selected('10',$camp_general['cg_yt_dte_month']) ?> >October</option>
								<option value='11'  <?php @wp_automatic_opt_selected('11',$camp_general['cg_yt_dte_month']) ?> >November</option>
								<option value='12'  <?php @wp_automatic_opt_selected('12',$camp_general['cg_yt_dte_month']) ?> >December</option>
							</select>
							Year:<input style="width:70px" class="no-unify" value="<?php   echo $camp_general['cg_yt_dte_year']   ?>" name="cg_yt_dte_year"     type="text">
						
						 </div>



 		               </div>
 		            
 		            	<div class="option clearfix">
 		            	
		                     <span class="option-title">
									Exclude the Item if a specific amount of time passed instead of a fixed date.  
		                     </span>
	 		            	 
	 		            	 <input data-controls="yt_date_c_t" data-controls-r="specific_date_skip" name="camp_options[]"   value="OPT_YT_DATE_T" type="checkbox">
		                     <br>
		                     
		                     <div id="yt_date_c_t" class="field f_100">
		                     	 
		                     	  <label>
					                    Minutes passed to skip
					              </label>
					              
					              <input value="<?php   echo $camp_general['cg_yt_dte_minutes'] ?>" name="cg_yt_dte_minutes" type="text">
					              <div class="description">for example add "60" without quotes to skip posts posted more than 60 minutes ago. Above fixed date will be ignored.</div>
		                     	 
		                     </div>
	                     
	                     </div>
 		            	
 		            
 		            </div>
		            
               </div>
		 </div>
		 <!-- /date part -->
		 
 
		 <!-- /non english part -->
		 
		 
		 
		 <!--  Vimeo Part -->
		 
		 <div class="typepart Vimeo" style="display:none">
		 

		 
		 <div id="vm_user_div" class="field f_100">
               <div class="option clearfix">
                    
                    <input data-controls="vm_user_c" name="camp_options[]" id="vm_user" value="OPT_VM_USER" type="checkbox">
                    <span class="option-title">
							Post from specific vimeo user/channel/album 
                    </span>
                    <br>
                   
                    
		            <div id="vm_user_c" class="field f_100">
		               
		               	<label>
					 		User or Channel or Album ? :
					 	</label>
					 	 <br>
					 	<select id="cg_vm_user_channel" name="cg_vm_user_channel" >
					 		<option  value="users"  <?php @wp_automatic_opt_selected('users',$camp_general['cg_vm_user_channel']) ?> >User</option>
					 		<option  value="channels"  <?php @wp_automatic_opt_selected('channels',$camp_general['cg_vm_user_channel']) ?> >Channel</option>
					 		<option  value="albums"  <?php @wp_automatic_opt_selected('albums',$camp_general['cg_vm_user_channel']) ?> >Album</option>
					 		
					 	</select>
		               
		               <label for="field6">
		                    User ID or channel ID or album ID
		               </label>
		                
		                <input id="cg_vm_user" value="<?php   echo @$camp_general['cg_vm_user']   ?>" name="cg_vm_user"    type="text">
		                <div class="description">for example add "karimshaaban" for this <a href="https://vimeo.com/karimshaaban">user</a>. copy the value from the link or add "3270886" for this <a href="https://vimeo.com/album/3270886">album</a></div>
		                
		            	<br>
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]" id="vm_full" value="OPT_VM_FULL" type="checkbox">
		                    <span class="option-title">
									Don't use keywords add videos without filtering . 
		                    </span>
	                    </div>
	                    <br>
		            </div>
		            
               </div>
		 </div>
		 
		 
		 
		 
		 <div   class="field f_100" >
		 	<label>
		 		Video search order :
		 	</label>
		 	
		 	<select id="cg_vm_order" name="cg_vm_order" >
		 		<option  value="relevant"  <?php @wp_automatic_opt_selected('relevant',$camp_general['cg_vm_order']) ?> >Relevant</option>
		 		<option  value="date"  <?php @wp_automatic_opt_selected('date',$camp_general['cg_vm_order']) ?> >Date</option>
		 		<option  value="alphabetical"  <?php @wp_automatic_opt_selected('alphabetical',$camp_general['cg_vm_order']) ?> >Alphabetical</option>
		 		<option  value="plays"  <?php @wp_automatic_opt_selected('plays',$camp_general['cg_vm_order']) ?> >Plays</option>
		 		<option  value="likes"  <?php @wp_automatic_opt_selected('likes',$camp_general['cg_vm_order']) ?> >Likes</option>
		 		<option  value="comments"  <?php @wp_automatic_opt_selected('comments',$camp_general['cg_vm_order']) ?> >Comments</option>
		 		<option  value="duration"  <?php @wp_automatic_opt_selected('duration',$camp_general['cg_vm_order']) ?> >Duration</option>
		 	</select>
		 </div>


		 <div   class="field f_100" >
		 	<label>
		 		Sorting direction :
		 	</label>
		 	
		 	<select id="cg_vm_order_dir" name="cg_vm_order_dir" >

		 		<option  value="desc"  <?php @wp_automatic_opt_selected('desc',$camp_general['cg_vm_order_dir']) ?> >DESC</option>
		 		<option  value="asc"  <?php @wp_automatic_opt_selected('asc',$camp_general['cg_vm_order_dir']) ?> >ASC</option>
		 		 
		 	</select>
		 </div>
		 
		  <div   class="field f_100" >
		 	<label>
		 		CC Filter
		 	</label>
		 	
		 	<select id="cg_vm_cc" name="cg_vm_cc" >

		 		<option  value="none"  <?php @wp_automatic_opt_selected('none',$camp_general['cg_vm_cc']) ?> >None</option>
		 		<option  value="CC"  <?php @wp_automatic_opt_selected('CC',$camp_general['cg_vm_cc']) ?> >CC</option>
		 		<option  value="CC-BY"  <?php @wp_automatic_opt_selected('CC-BY',$camp_general['cg_vm_cc']) ?> >CC-BY</option>
		 		<option  value="CC-BY-SA"  <?php @wp_automatic_opt_selected('CC-BY-SA',$camp_general['cg_vm_cc']) ?> >CC-BY-SA</option>
		 		<option  value="CC-BY-ND"  <?php @wp_automatic_opt_selected('CC-BY-ND',$camp_general['cg_vm_cc']) ?> >CC-BY-ND</option>
		 		<option  value="CC-BY-NC"  <?php @wp_automatic_opt_selected('CC-BY-NC',$camp_general['cg_vm_cc']) ?> >CC-BY-NC</option>
		 		<option  value="CC-BY-NC-SA"  <?php @wp_automatic_opt_selected('CC-BY-NC-SA',$camp_general['cg_vm_cc']) ?> >CC-BY-NC-SA</option>
		 		<option  value="CC-BY-NC-ND"  <?php @wp_automatic_opt_selected('CC-BY-NC-ND',$camp_general['cg_vm_cc']) ?> >CC-BY-NC-ND</option>
		 		<option  value="in-progress"  <?php @wp_automatic_opt_selected('in-progress',$camp_general['cg_vm_cc']) ?> >in-progress</option>
		 		  
		 	</select>
		 </div>
		 
	
		
		 <div class="field f_100">
		              
		                Player Width <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_vm_width']   ?>" name="cg_vm_width"    type="text"> Height <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_vm_height']   ?>" name="cg_vm_height"    type="text">
		            	
		 </div>
				  
         <div  class="field f_100">
                 
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_VM_ORIGINAL_TIME" type="checkbox">
                    <span class="option-title">
							Add posts with its original time   
                    </span>
                    <br>
               </div>

               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_VM_REVERSE" type="checkbox">
                    <span class="option-title">
							Process videos from bottom to top instead    
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" id="OPT_VM_CACHE"  value="OPT_VM_CACHE" type="checkbox">
                    <span class="option-title">
							Cache videos for faster posting  (uncheck to call vimeo each video)
                    </span>
                    <br>
               </div>
                <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_VM_TAG" type="checkbox">
                    <span class="option-title">
							Post Vimeo Tags as Tags 
                    </span>
                    <br>
               </div>
                 
		 </div>
		 
          
		 </div>
		 
		 
		 <!-- / Vimeo Part -->
		 
		 <!-- gpt3 part -->
		 <div class="typepart gpt3" style="display:none">
		
		 		<div id="article_generation_prompt" class="field f_100">
			 		<label>Articles titles generation prompt</label> 
					<input value="<?php  echo @$camp_general['cg_gp_prompt_titles']   ?>" name="cg_gp_prompt_titles" type="text" placeholder="Suggest articles titles about [keyword]">
					<div class="description">The prompt will be used to generate the article titles. You can use [keyword] to substitute the keyword anywhere in the prompt.<br><br>
							*Example: Suggest articles titles about [keyword]<br>
							*Example2: Suggest a list of titles for a new horror story that include [keyword]<br>
							*Example3: Name a list of cities in [keyword]	<br>
							*Example4: Suggest 100 Articles titles about [keyword]	<br>
							 
					</div>
			
					<div class="field f_100">
						<div class="option clearfix">
							<input name="camp_options[]" id="OPT_GP_ONE_TITLES"  value="OPT_GP_ONE_TITLES" type="checkbox">
							<span class="option-title">
							Post one article only for every keyword
							</span>
							<br>

							<div class="description">By default, the plugin grab list of titles and post every new title as a new post, enable this option to only consider the first title.</div>

						</div>
					</div>
			
			</div>
		 
			<div class="field f_100">
			 		 

				<div class="option clearfix">
                    <input data-controls-r="article_generation_prompt" name="camp_options[]" id="OPT_GP_NO_TITLES"  value="OPT_GP_NO_TITLES" type="checkbox">
                    <span class="option-title">
					Disable using prompt for title generation and use the keyword from the title box directly as the [article_title]
                    </span>
                    <br>

					<div class="description">By default, the plugin will use the prompt to generate the article titles list. Check this to disable it and use every added keyword directly as the [article_title].</div>

               </div>


			</div>
 

			<div class="field f_100">
			 		
			
					<div id="article_generation_prompt_cnt" class="field  ">

							<label>Article content generation prompt</label> 
							
							<textarea name="cg_gp_prompt" placeholder="Write an article content about [article_title]" ><?php    echo wp_automatic_htmlentities(@$camp_general['cg_gp_prompt'],ENT_COMPAT, 'UTF-8')   ?></textarea>
							
							
							<div class="description">The prompt will be used to generate the article content. You can use [article_title] to insert the article title in the prompt.<br><br>
									*Example: Write an article about [article_title]<br>
									*Example2: Write a new horror story about [article_title]	<br>
									*Example3: Write an article about best attractions located in [article_title]<br>
									*Example4: Write an article about [article_title] and include HTML headings<br> 
									*Example4: Write 1000 words article about [article_title] and include FAQs section at the end<br> 
									*Example5: Write 1000 words article in HTML format about [article_title] and include conclusion section and FAQs section at the end. do not include the title<br> 
							</div>	 
					</div>

					<div class="">
			 		 

							<div class="option clearfix">
								<input data-controls-r="article_generation_prompt_cnt" name="camp_options[]" id="OPT_GP_NO_CONTENT"  value="OPT_GP_NO_CONTENT" type="checkbox">
								<span class="option-title">
									Disable using prompt for content generation (In case you have your own content)
								</span>
								<br>

								<div class="description">By default, the plugin will use the prompt to generate the article content and it can then be used using the [matched_content]</div>

							</div>

							<div class="option clearfix">
								<input  name="camp_options[]" id="OPT_GP_REMOVE_H1"  value="OPT_GP_REMOVE_H1" type="checkbox">
								<span class="option-title">
									Remove the first H1 heading from the generated content
								</span>
								<br>

								<div class="description">Enable this option if you want to remove the generated title inside the post content coming from OpenAI as part of the generated article.</div>

							</div>


					</div>

			</div>

			 
			



			<div class="field f_100">
			 		<label>How many times to call OpenAI GPT3 for titles for each keyword</label> 
					<input value="<?php  echo @$camp_general['cg_gp_prompt_count']   ?>" name="cg_gp_prompt_count" type="number" placeholder="1">
					<div class="description">By default, the plugin will call for new titles once, it will then disable the keyword. increase this if needed.</div>
			</div>
		
		
		</div>
		 <!-- / gpt3 part -->

		 <!--  Sound cloud part -->
		 
		 <div class="typepart SoundCloud" style="display:none">
		 
		 	<div id="sc_user_div" class="field f_100">
               
               <div class="option clearfix">
                    <input name="camp_options[]" id="OPT_SC_CACHE"  value="OPT_SC_TAG_SEARCH" type="checkbox">
                    <span class="option-title">
							Search by Tags instead of keywords (return sounds containing the tags)
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    
                    <input data-controls-r=""  data-controls="sc_user_c" name="camp_options[]"  value="OPT_SC_USER" type="checkbox">
                    <span class="option-title">
							Post from specific SoundCloud user/playlist 
                    </span>
                    <br>
                    
		            <div id="sc_user_c" class="field f_100">
		               
		               	<label>
					 		User or Playlist ? :
					 	</label>
					 	<br>
					 	
					 	<select style="min-width:150px" id="cg_sc_user_playlist" name="cg_sc_user_playlist" >
					 		<option  value="user"  <?php @wp_automatic_opt_selected('user',$camp_general['cg_sc_user_playlist']) ?> >User                 </option>
					 		<option  value="playlist"  <?php @wp_automatic_opt_selected('playlist',$camp_general['cg_sc_user_playlist']) ?> >Playlist              </option>
					 		
					 	</select>
					 	<br>
		               
		               <label for="field6">
		                    SoundCloud URL of the user or the playlist 
		               </label>
		                
		                <input id="cg_sc_user" value="<?php   echo @$camp_general['cg_sc_user']   ?>" name="cg_sc_user"    type="text">
		                <div class="description">*add "https://soundcloud.com/kkwi6642" to post from this <a href="https://soundcloud.com/kkwi6642">user</a><br>*add "https://soundcloud.com/martopsy/sets" to import from this <a href="https://soundcloud.com/martopsy/sets">playlist</a><br>*you can also add the numeric ID directly, check <a target="_blank" href="http://valvepress.com/how-to-get-a-soundcloud-userplaylist-id/">this tutorial</a> to know how to get the ID </div>
		                
		            	 
	                    <br>
		            </div>
		            
		            
		            
               </div>
               
                
               
                <div class="option clearfix">
                    <input name="camp_options[]" id="OPT_SC_CACHE"  value="OPT_SC_CACHE" type="checkbox">
                    <span class="option-title">
							Cache items for faster posting  (uncheck to call SoundCloud each pin)
                    </span>
                    <br>
               </div>
               
               
               
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_SC_REVERSE" type="checkbox">
                    <span class="option-title">
							Process items from bottom to top instead    
                    </span>
                    <br>
				</div>
				
				
				<div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_SC_DATE" type="checkbox">
	                    <span class="option-title">
								Post items with its original date
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_SC_TAG" type="checkbox">
	                    <span class="option-title">
								Post soundCloud tags as wordpress tags
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_SC_COMMENT" type="checkbox">
	                    <span class="option-title">
								Post soundCloud comments as wordpress comments
	                    </span>
	                    <br>
	                     
	               </div>
	               
		 </div>
		
		 
		 </div>
		 
		 <!--   /sound cloud part -->	 
		 
		 <!-- Pinterest Part -->
		 
		 
		 <div class="typepart Pinterest" style="display:none">
		 

		 
			 <div id="pt_user_div" class="field f_100">
	               <div class="option clearfix">
	                    
	                    <input data-controls="pt_user_c" name="camp_options[]" id="pt_user" value="OPT_PT_USER"  data-controls-r=''  type="checkbox">
	                    <span class="option-title">
								Post from specific pinterest user / board
	                    </span>
	                    <br>
	                    
			            <div id="pt_user_c" class="field f_100">
			               
			               <label>
					 		User or Board ? :
						 	</label><br>
						 	
						 	<select id="cg_pt_user_channel" name="cg_pt_user_channel" >
						 		<option  value="users"  <?php @wp_automatic_opt_selected('users',$camp_general['cg_pt_user_channel']) ?> >User</option>
						 		<option  value="boards"  <?php @wp_automatic_opt_selected('boards',$camp_general['cg_pt_user_channel']) ?> >Board</option>
						 		
						 	</select>
			               
			               	 <br>
			               <label for="field6">
			                    User id/board id 
			               </label><br>
			                
			                <input id="cg_pt_user" value="<?php   echo @$camp_general['cg_pt_user']   ?>" name="cg_pt_user"    type="text">
			                <div class="description">for example add "welkerpatrick" for this <a href="https://www.pinterest.com/welkerpatrick">user</a>. <br> or add "welkerpatrick/recipes" for this <a href="https://www.pinterest.com/welkerpatrick/recipes/">board</a><br>copy the value from the link after pinterest.com/ </div>
			                
			            	 
		                    <br>
			            </div>
			            
	               </div>
			 </div>
			 
			 
			 <div  class="field f_100">
                 
        
               
               <div class="option clearfix">
                    <input name="camp_options[]" id="OPT_PT_CACHE"  value="OPT_PT_CACHE" type="checkbox">
                    <span class="option-title">
							Cache pins for faster posting  (uncheck to call pinterest each pin)
                    </span>
                    <br>
               </div>
       
       			<div class="option clearfix">
                    <input data-controls="wp_pinterest_title_count" name="camp_options[]"  value="OPT_PT_AUTO_TITLE" type="checkbox">
                    <span class="option-title">
							Auto generate title from the description for the no title pins
                    </span>
                    <br>
                    
                    <div id="wp_pinterest_title_count" class="field f_100">
                    	<label>Limit title to xx chars (default is 80 )</label>
                    	<input value="<?php   echo @$camp_general['cg_pt_title_count']  ?>" placeholder="80" name="cg_pt_title_count"  type="text">
                    	
                    </div>
                    
               </div>
               
               <div class="option clearfix">
	                    <input name="camp_options[]"   value="OPT_PT_TITLE_SKIP" type="checkbox">
	                    <span class="option-title">
								Skip posts with no title     
	                    </span>
	                    <br>
	          </div>
       			
                 
		 </div>
			 
		 
		 </div>
		 
		 
		 <!--  / pinterest Part -->
		 
		 <!--  TikTok part -->
		  <div class="typepart TikTok" style="display:none">
		  
		  	<div  class="field f_100">
		 			Hint: keywords will be turned to TikTok hastags like "for you" the plugin will search "#foryou" tag on TikTok
			</div>
			
			<div  class="field f_100">
	               <div class="option clearfix">
	                    
	                    
	                    <input data-controls-r="" data-controls="wp_tt_infinte"  name="camp_options[]"  value="OPT_TT_INFINITE" type="checkbox">
	                    <span class="option-title">
								Extract videos from this HTML (to import older items or all items)
	                    </span>
	                    <br>
	                    
	                 <div id="wp_tt_infinte" class="field f_100">
	                    	<label>TikTok page HTML (Scroll the page then copy the HTML)</label>
	                    	<textarea name="cg_tt_html"><?php echo wp_automatic_htmlentities( @$camp_general['cg_tt_html'],ENT_COMPAT, 'UTF-8')  ?></textarea>
	                    	<div class="description">Scroll as many times as desired, then paste the page HTML. Check this  <a target="_blank" href="https://valvepress.com/how-to-post-older-tiktok-videos-to-wordpress-using-automatic-plugin/">tutorial</a></div>
                    </div>                    
                    
                    </div>
                    
                    <div class="option clearfix">
	                    
	                    <input data-controls-r="" data-controls="wp_tt_user"  name="camp_options[]"  value="OPT_TT_USER" type="checkbox">
	                    <span class="option-title">
								Post from a specific TikTok user 
	                    </span>
	                    <br>
	                    
	                 <div id="wp_tt_user" class="field f_100">
	                    	<label>TikTok user ID  </label>
	                    	<input value="<?php   echo @$camp_general['cg_tt_user']  ?>"  name="cg_tt_user"  type="text">
	                    	<div class="description">for example, add "kelly_bove" for for <a target="_blank" href="https://www.tiktok.com/@kelly_bove">this user</a></div>
                    </div>
                    
                    <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_TAGS" type="checkbox">
	                    <span class="option-title">
								Set TikTok hashtags as WordPress tags
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               
	                <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_REVERSE" type="checkbox">
	                    <span class="option-title">
								Reverse posting order ( post older items first)
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_NO_CNT_TAG" type="checkbox">
	                    <span class="option-title">
								Remove hashtags from generated content. 
	                    </span>
	                    <br>
	                     
	               </div>
				   <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_NO_CNT_REP" type="checkbox">
	                    <span class="option-title">
								Remove mentions from generated content (@user). 
	                    </span>
	                    <br>
	                     
	               </div>

				   <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_NO_CNT_MUSIC" type="checkbox">
	                    <span class="option-title">
								Remove the music part from the post content (original sound).
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_NO_TTL_TAG" type="checkbox">
	                    <span class="option-title">
								Remove hashtags from generated title if any exists. 
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TT_NO_TTL_MEN" type="checkbox">
	                    <span class="option-title">
								Remove mentions from generated title if any exists. 
	                    </span>
	                    <br>
	                     
	               </div>
                    
                   
                   </div> 
	                    
	        </div>
		  	
		  	
		  </div>
		 
		 
		 <!-- Instagram part -->
		 <div class="typepart Instagram" style="display:none">
		 		
		 		<div  class="field f_100">
		 			Hint: keywords will be turned to instagram hastags like "no filter" the plugin will search "#nofilter" tag at instagarm
		 		</div>
		 
		 		<div  class="field f_100">
	               <div class="option clearfix">
	                    <input data-controls-r="" data-controls="wp_it_user"  name="camp_options[]"  value="OPT_IT_USER" type="checkbox">
	                    <span class="option-title">
								Post from a specific Instagram user 
	                    </span>
	                    <br>
	                    
	                    <div id="wp_it_user" class="field f_100">
                    	<label>Instagram user ID (appear after instagram.com at the user page link)</label>
                    	<input value="<?php   echo @$camp_general['cg_it_user']  ?>"  name="cg_it_user"  type="text">
                    	<div class="description">for example add "cnn" for for <a target="_blank" href="https://instagram.com/cnn/">this user</a></div>
                    </div>
                    
	                    
	               </div>
                
                	
                	<div class="option clearfix">
                    
				        <input data-controls="IT_SLIDER_c" name="camp_options[]" value="OPT_IT_SLIDER" type="checkbox">
				        <span class="option-title">
								If more than one image exists in the single instagram item, display them all
				        </span>
				        <br>
				        
				        <div id="IT_SLIDER_c" class="field f_100">
				        	
				        	    <label>Single Image Html</label> 
				        	    
				        	     <input placeholder="<?php echo  wp_automatic_htmlentities( '<img src="[img_src]" />' ,ENT_COMPAT, 'UTF-8')   ?>" value="<?php    echo  wp_automatic_htmlentities($camp_general['cg_it_full_img_t'],ENT_COMPAT, 'UTF-8')   ?>"  name="cg_it_full_img_t" type="text">
				         	 <div class="description">Default: <?php echo  wp_automatic_htmlentities( '<img src="[img_src]" />' ,ENT_COMPAT, 'UTF-8')   ?></div>
				        </div>
				                    
				</div>
                	
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_POPULAR" type="checkbox">
	                    <span class="option-title">
								Only post popular posts (No most recent items)
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_TAGS" type="checkbox">
	                    <span class="option-title">
								Set Instagram tags as tags
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_COMMENT" type="checkbox">
	                    <span class="option-title">
								Post Instagram comments as comments
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_IMG" type="checkbox">
	                    <span class="option-title">
								Don't post images items 
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_VID" type="checkbox">
	                    <span class="option-title">
								Don't post video items 
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_VID_TOP" type="checkbox">
	                    <span class="option-title">
								Embed videos at the top of the post (by default at the bottom)
	                    </span>
	                    <br>
	                     
	               </div>
	               
	                <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_VID_EMBED" type="checkbox">
	                    <span class="option-title">
								Do not add the video embed code to [item_description] tag, I have another plan
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_REVERSE" type="checkbox">
	                    <span class="option-title">
								Reverse posting order ( post older items first)
	                    </span>
	                    <br>
	                     
	               </div>
	               
	                <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_VID_IMG_HIDE" type="checkbox">
	                    <span class="option-title">
								Don't hide video image (added hidden for featured image purpose) 
	                    </span>
	                    <br>
	                     
	               </div>
	               
	                <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_CNT_TAG" type="checkbox">
	                    <span class="option-title">
								Remove hashtags from generated content. 
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_TTL_TAG" type="checkbox">
	                    <span class="option-title">
								Remove hashtags from generated title if any exists. 
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_NO_TTL_MEN" type="checkbox">
	                    <span class="option-title">
								Remove mentions from generated title if any exists. 
	                    </span>
	                    <br>
	                     
	               </div>
	                
               </div>
        
		 		 
		 </div>
		 	
		 <!-- /Instagram Twitter part -->
		 <div class="typepart  Twitter Instagram TikTok telegram" style="display:none">
		 
		 <div  class="field f_100">
	               
	               <div class="option clearfix typepart  Twitter Instagram">
	                    <input name="camp_options[]"  value="OPT_IT_DATE" type="checkbox">
	                    <span class="option-title">
								Post items with its original date
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_IT_CACHE" type="checkbox">
	                    <span class="option-title">
								Cache items for faster posting (Disable if you want to focus on latest posts only)
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               
	               <div class="option clearfix">
	                    <input data-controls="wp_it_title_count" name="camp_options[]"  value="OPT_IT_AUTO_TITLE" type="checkbox">
	                    <span class="option-title">
								Auto generate title from the content 
	                    </span>
	                    <br>
	                    
	                    <div id="wp_it_title_count" class="field f_100">
                    	<label>Limit title to x chars (default is 80 )</label>
                    	<input value="<?php   echo @$camp_general['cg_it_title_count']  ?>" placeholder="80" name="cg_it_title_count"  type="text">
                    	
                    	<div class="option clearfix typepart Instagram">
	                    	<input   name="camp_options[]"  value="OPT_IT_STOP_LINE_BREAK" type="checkbox">
		                    <span class="option-title">
									Stop at line breaks (i.e take the title before a new line)  
		                    </span>
		                    <br>
                    	</div>
                    	
                    	<div class="option clearfix typepart Twitter">
	                    	<input   name="camp_options[]"  value="OPT_IT_TITLE_CLEAN" type="checkbox">
		                    <span class="option-title">
									Clean title from RT and @user  
		                    </span>
		                    <br>
                    	</div> 
                    	
                    	<div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TW_NO_TTL_TAG" type="checkbox">
	                    <span class="option-title">
								Remove hashtags from generated title if any exists. 
	                    </span>
	                    <br>
	                     
	               </div>
                    	
                    	<div class="option clearfix  typepart Twitter Instagram TikTok">
		                    <input  name="camp_options[]"   value="OPT_GENERATE_TW_DOT" type="checkbox">
		                    <span class="option-title">
									Don't add "..." after generated title    
		                    </span>
	                    </div>
                    	
                    </div>
                    
                     
	                    
	               </div>
               </div>
		 
		 </div>
		 
		 <!--  Twitter Part -->
		 <div  class="typepart Twitter"  > 
		 

		 	 
		 	 <div  class="field f_100">
		 	 	
		 	 	<div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TW_TAG" type="checkbox">
	                    <span class="option-title">
								Post hashtags as wordpress tags
	                    </span>
	                    <br>
	                     
	            </div>
	            
	            <div class="option clearfix">
	                    <input data-controls="tw_vid_embed_direct"  name="camp_options[]"  value="OPT_TW_VID_EMBED" type="checkbox">
	                    <span class="option-title">
								Automatically embed videos
	                    </span>
	                    <br>
	                    
	                    <div id="tw_vid_embed_direct" class="field f_100" >
	                    		
	                    		<div class="option clearfix">
				                    <input name="camp_options[]"  value="OPT_TW_VID_EMBED_DIRECT" type="checkbox">
				                    <span class="option-title">
											If available, embed direct video URL
				                    </span>
				                    <br>
				                     
				            </div>
	                    		
	                    </div>	
	                    
	                     
	            </div>
	            
	            <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TW_EXPAND" type="checkbox">
	                    <span class="option-title">
								Expand URLs on the post content (By default shortlinks)
	                    </span>
	                    <br>
	                     
	            </div>
		 	 	
		 	 	<div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TW_RT" type="checkbox">
	                    <span class="option-title">
								Skip Retweets
	                    </span>
	                    <br>
	                     
	               </div>
	               
               <div class="option clearfix">
                    <input name="camp_options[]"  value="OPT_TW_RE" type="checkbox">
                    <span class="option-title">
							Skip in-reply-to tweets
                    </span>
                    <br>
                     
               </div>
		 	 	
		 	 	<div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TW_CARDS" type="checkbox">
	                    <span class="option-title">
								Embed items as twitter cards (by default plain text)
	                    </span>
	                    <br>
	                     
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]"  value="OPT_TW_AUTHOR" type="checkbox">
	                    <span class="option-title">
								Set the WordPress author from the original tweet author
	                    </span>
	                    <br>
	                     
	               </div>
	             
		 	 	<div class="option clearfix">
	                    <input data-controls="wp_tw_lang" name="camp_options[]"  value="OPT_TW_LANG" type="checkbox">
	                    <span class="option-title">
								Limit search to a specific language 
	                    </span>
	                    <br>
	                    
	                    <div id="wp_tw_lang" class="field f_100">
	                    	<label>Language code 639-1</label>
	                    	
	                    	<input value="<?php   echo @$camp_general['cg_tw_lang']  ?>"   name="cg_tw_lang"  type="text">
	                    	<div class="description"><i>Example: en for English or fr for French. Check list <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">here</a></i></div>
                    	
                 		</div>
		 	 	
		 	 	</div>

  		 	 	
		 
		 	</div>
		 </div>
		 
		 <!-- DailyMotion part -->
<div class="typepart DailyMotion" style="display:none">
		 	
		 	
		 	<div class="field f_100">
		 	
		 	<div class="option clearfix"  >
		 	
		 		<input data-controls="dm_user_c" name="camp_options[]" id="dm_user" value="OPT_DM_USER" type="checkbox">
                    <span class="option-title">
							Post from a specific DailyMotion user 
                    </span>
                    <br>
                    
		            <div id="dm_user_c" class="field f_100">
		               <label>
		                    User ID  
		               </label>
		                
		                <input id="camp_dm_user" value="<?php  if ( isset($camp_general['cg_dm_user'] ) )  echo $camp_general['cg_dm_user']   ?>" name="cg_dm_user" type="text">
		                <div class="description"><br>Example:add Dakar for this user  <a href="http://www.dailymotion.com/Dakar">http://www.dailymotion.com/Dakar</a> </div>
		                
		                <br>
		            	<div class="field f_100">
		            	<input  data-controls="wp_automatic_dmplaylist_c"  name="camp_options[]" value="OPT_DM_PLAYLIST" id="wp_automatic_dmplaylist_opt" type="checkbox">
		                    <span class="option-title">
									Specify a playlist?   
		                    </span>
		                    <br>
		                    
		                    <div  id="wp_automatic_dmplaylist_c" class="field f_100">
		                    	 <select  style="width:220px" class="no-unify" name="cg_dm_playlist" id="cg_dm_playlist"  >
					                   
					                   
					                   
					                   <?php 
					                   
					                   $wp_automatic_dm_playlists = get_post_meta($post_id ,'wp_automatic_dm_playlists',1); 
					                   
					               
					                   if(! is_array($wp_automatic_dm_playlists)) $wp_automatic_dm_playlists = array();
					                   
					                   foreach ($wp_automatic_dm_playlists as $playlist){


					                   	?>
											<option  value="<?php   echo $playlist['id'] ?>"  <?php @wp_automatic_opt_selected($camp_general['cg_dm_playlist'],$playlist['id']) ?> >
					                         	<?php   echo $playlist['title']?>
					                    	</option>				

					                   	<?php

					                   	
					                   	
					                   }
  
					               
					                   ?>
					                     
					               </select>
					               
					               
					               <a href="#" id="dm_playlist_update">update playlists</a>
					               
					               <span class="spinner is-active spinner-dmplaylist" style="display:none"></span>
					        
					        		<br><br>
					        
								   <label>
					                    Chosen playlist id  
					               </label>
						        	
			                    	<input   id="cg_dm_playlist_txt" value="<?php   echo @$camp_general['cg_dm_playlist']   ?>" name="cg_dm_playlist"   type="text">
			                	    <div class="description">This field will be automatically loaded with the playlist id or simply paste a playlist id here for example add "x4shtt" for this <a href="http://www.dailymotion.com/playlist/x4shtt_Dakar_dakar-2017-touristic-postcard/">playlist</a></div> 
			                    
					               
		                    </div>
		                     
	                    </div>
	                    
	                    
		                
		            	<br>
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]" id="dm_full" value="OPT_DM_FULL" type="checkbox">
		                    <span class="option-title">
									Don't use keywords add videos without filtering . 
		                    </span>
	                    </div>
	                    <br>
		            </div>
		            
		            
               </div>
               
               </div>
               
               <div class="field f_100">
		                Player Width <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_dm_width']   ?>" name="cg_dm_width"    type="text"> Height <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_dm_height']   ?>" name="cg_dm_height"    type="text">
			   </div>
			   
			   <div class="field f_100">
			       <div class="option clearfix">
	                    <input name="camp_options[]" id="OPT_DM_CACHE"  value="OPT_DM_CACHE" type="checkbox">
	                    <span class="option-title">
								Cache videos for faster posting  (uncheck to call DailyMotion each video)
	                    </span>
	                    <br>
	               </div>
	               <div class="option clearfix">
	                    <input name="camp_options[]" id="OPT_DM_ORIGINAL_TIME"  value="OPT_DM_ORIGINAL_TIME" type="checkbox">
	                    <span class="option-title">
								Add posts with its original time 
	                    </span>
	                    <br>
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]" id="OPT_DM_REVERSE"  value="OPT_DM_REVERSE" type="checkbox">
	                    <span class="option-title">
								Process items from bottom to top instead 
	                    </span>
	                    <br>
	               </div>
	               
	               <div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_DM_TAG" type="checkbox">
	                    <span class="option-title">
								Post DailyMotion Tags as Tags 
	                    </span>
	                    <br>
               		</div>
               		
               		<div class="option clearfix">
	                    <input name="camp_options[]" value="OPT_DM_AUTO" type="checkbox">
	                    <span class="option-title">
								Auto play the video
	                    </span>
	                    <br>
               		</div>
               		
               		<div class="option clearfix">
	                    <input data-controls="wp_automatic_dm_sctry" name="camp_options[]" value="OPT_DM_LIMIT_CTRY" type="checkbox">
	                    <span class="option-title">
								Search results for a specific country  
	                    </span>
	                    <br>
	                    
	                    <div id="wp_automatic_dm_sctry" class="field f_100">
	                    	
	                       <label>
			                    Country Code
			               </label>
			               
			               <input value="<?php   echo @$camp_general['cg_dm_ctr']   ?>" name="cg_dm_ctr"   type="text">
			               
			               <div class="description">Example: "US" for united states. check other countries region codes <a target="_blank" href="https://www.iso.org/obp/ui/#search/code/">here</a></div>
	                    
	                    </div>
                    
               		</div>
               		
               		<div class="option clearfix">
	                    <input data-controls="wp_automatic_dm_slang" name="camp_options[]" value="OPT_DM_LIMIT_LANG" type="checkbox">
	                    <span class="option-title">
								Search results for a specific language  
	                    </span>
	                    <br>
	                    
	                    <div id="wp_automatic_dm_slang" class="field f_100">
	                    	
	                       <label>
			                    Language Code
			               </label>
			               
			               <input value="<?php   echo @$camp_general['cg_dm_lang']   ?>" name="cg_dm_lang"   type="text">
			               
			               <div class="description">Example: "en" for English. check other languages codes <a target="_blank" href="http://www.loc.gov/standards/iso639-2/php/code_list.php">here</a></div>
	                    
	                    </div>
                    
               		</div>
               		
               		<div class="option clearfix">
	                    <input data-controls="wp_automatic_dm_schannel" name="camp_options[]" value="OPT_DM_LIMIT_CHANNEL" type="checkbox">
	                    <span class="option-title">
								Search results from a specific channel 
	                    </span>
	                    <br>
	                    
	                    <div id="wp_automatic_dm_schannel" class="field f_100">
	                    	
	                       <label>
			                    Channel name
			               </label>
			               
			               <input value="<?php   echo @$camp_general['cg_dm_channel']   ?>" name="cg_dm_channel"   type="text">
			               
			               <div class="description">Example: news,sport,comedy and music</div>
	                    
	                    </div>
                    
               		</div>
               		
	               
               </div>
			   
               
               
		 </div>
		 	
		  		 
		 
		 
		 <!--  Youtube Part -->
		 <div class="typepart Youtube" style="display:none">
		 


		 
		 <div id="yt_user_div" class="field f_100">
		 
			 	<div class="option clearfix">
	                    
			        <input data-controls="YT_ID" name="camp_options[]" value="YT_ID" type="checkbox">
			        <span class="option-title">
							I want to post videos by IDs and have added videos IDs in the keyword box 
			        </span>
				</div>

			 	
		 
               <div class="option clearfix">
               
                    <input name="camp_options[]" id="yt_user" value="OPT_YT_USER" type="checkbox">
                    <span class="option-title">
							Post from a specific YouTube channel
                    </span>
                    <br>
                    
		            <div id="yt_user_c" class="field f_100">
		               <label for="field6">
		                    Channel ID or Channel URL
		               </label>
		                
		                <input id="camp_yt_user" value="<?php   echo $camp_yt_user   ?>" name="camp_yt_user" id="field6"   type="text">
		                <div class="description"><br>Example 1:  UCRrW0ddrbFnJCbyZqHHv4KQ<br>Example 2:  https://www.youtube.com/channel/UCRrW0ddrbFnJCbyZqHHv4KQ <br>Example 3:  https://www.youtube.com/c/HolyCulture</div>
		                 
		            	<div class="field f_100">
		            	
							<div class="option clearfix">
							<input  data-controls-r='' name="camp_options[]" id="yt_full" value="OPT_YT_FULL" type="checkbox">
								<span class="option-title">
										Don't use keywords add videos without filtering. 
								</span>
							</div>

							<div class="option clearfix">
								<input    name="camp_options[]"  value="OPT_YT_SHORT_SKIP" type="checkbox">
								<span class="option-title">
										Skip shorts
								</span>
							</div>

							<div class="option clearfix">
								<input    name="camp_options[]"  value="OPT_YT_SHORT_ONLY" type="checkbox">
								<span class="option-title">
										only import shorts
								</span>
							</div>
						</div>
	                    
	                    <div class="description">Tip: If you want to post from many channels, just add the channel ID and activate the option to post without keywords then leave the next 7 options below to thier default values. This will reduce the used quota per request from 100 to 3. Daily quota is 10000 and a single search request takes 100</div>
	                    <br>
	                    
		            </div>
		            
               </div>


			   <div class="option clearfix">
		            		<input data-controls-r="" data-controls="wp_automatic_playlist_c"  name="camp_options[]" value="OPT_YT_PLAYLIST" type="checkbox" id="wp_automatic_playlist_opt">
		                    <span class="option-title">
									Post from a specific Playlist
		                    </span>
		                    <br>
		                    
		                    <div  id="wp_automatic_playlist_c" class="field f_100">
		                    	 
					        
								   <label for="field6">
					                    Playlist ID  or Playlist URL
					               </label>
						        	
			                    	<input   id="cg_yt_playlist_txt" value="<?php   echo @$camp_general['cg_yt_playlist']   ?>" name="cg_yt_playlist"   type="text">
			                	    <div class="description">Example1 : PLFgquLnL59amB2HQvPIExssQsuNRPvOwk <br>Example2:  https://www.youtube.com/playlist?list=PLFgquLnL59amB2HQvPIExssQsuNRPvOwk </div> 
			                    
					               
		                    </div>
		                    
		                	
		                	
		                    
	           </div>

			   <!-- Post tredning videos with option to specify the region -->
			   <div class="option clearfix">
		            		<input data-controls-r="" data-controls="wp_automatic_trending_c"  name="camp_options[]" value="OPT_YT_TRENDING" type="checkbox" id="wp_automatic_trending_opt">
		                    <span class="option-title">
									Post trending videos (Most popular videos)
						   </span>
							<br>
							<div id="wp_automatic_trending_c" class="field f_100">
								<label for="field6">
									Region
								</label>
								<input id="cg_yt_trending_region" value="<?php   echo @$camp_general['cg_yt_trending_region']   ?>" name="cg_yt_trending_region" type="text">
								<div class="description">Example: US for United States, GB for Great Britain, DE for Germany, FR for France, CA for Canada...<br><br><strong>Tip:</strong> Disable the caching option below to call Youtube every run for the latest trending videos <br><br><strong>Tip 2:</strong> Choose a speific category below to get trending videos for that category, for example, choose "Music" to get trending music videos
								</div>
							</div>

				</div>


               
               <div class="option clearfix">
	                    
			        <input data-controls="YT_AFTER_c" name="camp_options[]" value="OPT_YT_BEFORE" type="checkbox">
			        <span class="option-title">
							Post videos that are published before a specific date
			        </span>
			        <br>
			        
			        <div id="YT_AFTER_c" class="field f_100">
			        	
			        	<label>Date</label> <input value="<?php    echo @$camp_general['cg_yt_before']   ?>" name="cg_yt_before" type="text">
			        	<div class="description">ex 2016-04-30</div>
			         
			        </div>
	                    
				</div>
               
		 </div>
		 
		 
		 
		 
		 <div id="field-camp_youtube_order-container" class="field f_100" >
		 	<label for="field-camp_youtube_order">
		 		Youtube search order :
		 	</label>
		 	<select id="camp_youtube_order" name="camp_youtube_order" id="field1zz">
		 		<option  value="relevance"  <?php @wp_automatic_opt_selected('relevance',$camp_youtube_order) ?> >Relevance</option>
		 		<option  value="date"  <?php @wp_automatic_opt_selected('date',$camp_youtube_order) ?> >Date</option>
		 		<option  value="title"  <?php @wp_automatic_opt_selected('title',$camp_youtube_order) ?> >Title</option>
		 		<option  value="viewCount"  <?php @wp_automatic_opt_selected('viewCount',$camp_youtube_order) ?> >View Count</option>
		 		<option  value="rating"  <?php @wp_automatic_opt_selected('rating',$camp_youtube_order) ?> >Rating</option>
		 		 
		 	</select>
		 </div>

		
		<div id="field-camp_youtube_cat-container" class="field f_100" >
			<label for="field-camp_youtube_cat">
				Youtube category:
			</label>
			<select name="camp_youtube_cat" id="field1zz">
					<option  value="All"  <?php @wp_automatic_opt_selected('All',$camp_youtube_cat) ?> >All</option>
					<option value="2" <?php @wp_automatic_opt_selected( '2',$camp_youtube_cat) ?> >Autos & Vehicles</option>
					<option value="10" <?php @wp_automatic_opt_selected( '10',$camp_youtube_cat) ?> >Music</option>
					<option value="15" <?php @wp_automatic_opt_selected( '15',$camp_youtube_cat) ?> >Pets & Animals</option>
					<option value="17" <?php @wp_automatic_opt_selected( '17',$camp_youtube_cat) ?> >Sports</option>
					<option value="18" <?php @wp_automatic_opt_selected( '18',$camp_youtube_cat) ?> >Short Movies</option>
					<option value="19" <?php @wp_automatic_opt_selected( '19',$camp_youtube_cat) ?> >Travel & Events</option>
					<option value="20" <?php @wp_automatic_opt_selected( '20',$camp_youtube_cat) ?> >Gaming</option>
					<option value="21" <?php @wp_automatic_opt_selected( '21',$camp_youtube_cat) ?> >Videoblogging</option>
					<option value="22" <?php @wp_automatic_opt_selected( '22',$camp_youtube_cat) ?> >People & Blogs</option>
					<option value="23" <?php @wp_automatic_opt_selected( '23',$camp_youtube_cat) ?> >Comedy</option>
					<option value="24" <?php @wp_automatic_opt_selected( '24',$camp_youtube_cat) ?> >Entertainment</option>
					<option value="25" <?php @wp_automatic_opt_selected( '25',$camp_youtube_cat) ?> >News & Politics</option>
					<option value="26" <?php @wp_automatic_opt_selected( '26',$camp_youtube_cat) ?> >Howto & Style</option>
					<option value="27" <?php @wp_automatic_opt_selected( '27',$camp_youtube_cat) ?> >Education</option>
					<option value="28" <?php @wp_automatic_opt_selected( '28',$camp_youtube_cat) ?> >Science & Technology</option>
					<option value="29" <?php @wp_automatic_opt_selected( '29',$camp_youtube_cat) ?> >Nonprofits & Activism</option>
					<option value="30" <?php @wp_automatic_opt_selected( '30',$camp_youtube_cat) ?> >Movies</option>
					<option value="31" <?php @wp_automatic_opt_selected( '31',$camp_youtube_cat) ?> >Anime/Animation</option>
					<option value="32" <?php @wp_automatic_opt_selected( '32',$camp_youtube_cat) ?> >Action/Adventure</option>
					<option value="33" <?php @wp_automatic_opt_selected( '33',$camp_youtube_cat) ?> >Classics</option>
					 
					<option value="35" <?php @wp_automatic_opt_selected( '35',$camp_youtube_cat) ?> >Documentary</option>
					<option value="36" <?php @wp_automatic_opt_selected( '36',$camp_youtube_cat) ?> >Drama</option>
					<option value="37" <?php @wp_automatic_opt_selected( '37',$camp_youtube_cat) ?> >Family</option>
					<option value="38" <?php @wp_automatic_opt_selected( '38',$camp_youtube_cat) ?> >Foreign</option>
					<option value="39" <?php @wp_automatic_opt_selected( '39',$camp_youtube_cat) ?> >Horror</option>
					<option value="40" <?php @wp_automatic_opt_selected( '40',$camp_youtube_cat) ?> >Sci-Fi/Fantasy</option>
					<option value="41" <?php @wp_automatic_opt_selected( '41',$camp_youtube_cat) ?> >Thriller</option>
					<option value="42" <?php @wp_automatic_opt_selected( '42',$camp_youtube_cat) ?> >Shorts</option>
					<option value="43" <?php @wp_automatic_opt_selected( '43',$camp_youtube_cat) ?> >Shows</option>
					<option value="44" <?php @wp_automatic_opt_selected( '44',$camp_youtube_cat) ?> >Trailers</option>		 
 
			</select>
		</div>
		
		
		<div   class="field f_100" >
			<label>
				Video License:
			</label>
			<select name="cg_yt_license" >
							
							<option  value="any"  <?php @wp_automatic_opt_selected('any',$camp_general['cg_yt_license']) ?> >Any</option>
							<option  value="creativeCommon"  <?php @wp_automatic_opt_selected('creativeCommon',$camp_general['cg_yt_license']) ?> >Creative Common</option>
							<option  value="youtube"  <?php @wp_automatic_opt_selected('youtube',$camp_general['cg_yt_license']) ?> >Standard</option>
 
			</select>
		</div>
		
		<div   class="field f_100" >
			<label>
				Video Type:
			</label>
			<select name="cg_yt_type" >
							
							<option  value="any"  <?php @wp_automatic_opt_selected('any',$camp_general['cg_yt_type']) ?> >Any</option>
							<option  value="episode"  <?php @wp_automatic_opt_selected('episode',$camp_general['cg_yt_type']) ?> >Episode</option>
							<option  value="movie"  <?php @wp_automatic_opt_selected('movie',$camp_general['cg_yt_type']) ?> >Movie</option>
 			</select>
		</div>
		
		<div   class="field f_100" >
			<label>
				Video Duration:
			</label>
			<select name="cg_yt_duration" >
							
							<option  value="any"  <?php @wp_automatic_opt_selected('any',$camp_general['cg_yt_duration']) ?> >Any</option>
							<option  value="long"  <?php @wp_automatic_opt_selected('long',$camp_general['cg_yt_duration']) ?> >Long (longer than 20 minutes)</option>
							<option  value="medium"  <?php @wp_automatic_opt_selected('medium',$camp_general['cg_yt_duration']) ?> >Medium (between four and 20 minutes)</option>
							<option  value="short"  <?php @wp_automatic_opt_selected('short',$camp_general['cg_yt_duration']) ?> >Short (less than four minutes)</option>
  			</select>
		</div>
		
		<div   class="field f_100" >
			<label>
				Video definition:
			</label>
			<select name="cg_yt_definition" >
							
							<option  value="any"  <?php @wp_automatic_opt_selected('any',$camp_general['cg_yt_definition']) ?> >Any</option>
							<option  value="high"  <?php @wp_automatic_opt_selected('high',$camp_general['cg_yt_definition']) ?> >High</option>
							<option  value="standard"  <?php @wp_automatic_opt_selected('standard',$camp_general['cg_yt_definition']) ?> >Standard</option>
							
							high
   			</select>
		</div>
		
		<div   class="field f_100" >
			<label>
				Safe Search:
			</label>
			<select name="cg_yt_safe" >
							
							<option  value="moderate"  <?php @wp_automatic_opt_selected('moderate',$camp_general['cg_yt_safe']) ?> >Moderate (default)</option>
							<option  value="none"  <?php @wp_automatic_opt_selected('none',$camp_general['cg_yt_safe']) ?> >None (will not filter)</option>
							<option  value="strict"  <?php @wp_automatic_opt_selected('strict',$camp_general['cg_yt_safe']) ?> >Strict (Exclude all restricted content)</option>
							
							high
   			</select>
		</div>
		
		 <div class="field f_100">
		              
		                Player Width <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_yt_width']   ?>" name="cg_yt_width"    type="text"> Height <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_yt_height']   ?>" name="cg_yt_height"    type="text">
		            	
		 </div>
				  
         <div  class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"  value="OPT_YT_FULL_CNT" type="checkbox">
                    <span class="option-title">
							Fetch Full video description from youtube
                    </span>
                    <br>
               </div>
               
             
               
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_YT_ORIGINAL_TIME" type="checkbox">
                    <span class="option-title">
							Add posts with its original time   
                    </span>
                    <br>
               </div>

               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_YT_REVERSE" type="checkbox">
                    <span class="option-title">
							Process videos from bottom to top instead    
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" id="OPT_YT_CACHE"  value="OPT_YT_CACHE" type="checkbox">
                    <span class="option-title">
							Cache videos for faster posting  (uncheck to call youtube each run & check latest 50 videos only) 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_LIVE_ONLY" type="checkbox">
                    <span class="option-title">
							Post live videos only 
                    </span>
                    <br>
               </div>
               
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_LIVE_SKIP" type="checkbox">
                    <span class="option-title">
							Skip live videos from returned results 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_CC" type="checkbox">
                    <span class="option-title">
							Search videos that contain closed captions only 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_TAG" type="checkbox">
                    <span class="option-title">
							Post Youtube Tags as Tags 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_AUTHOR" type="checkbox">
                    <span class="option-title">
							Set the channel author as the WordPress post author 
                    </span>
                    <br>
               </div>
               
                <div class="option clearfix">
	                <input name="camp_options[]" value="OPT_YT_HYPER" type="checkbox">
	                <span class="option-title">
							Auto hyperlink urls at the description
	                </span>
	                <br>
               	</div>
               
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_AUTO" type="checkbox">
                    <span class="option-title">
							Auto play the video
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_F_TITLE" type="checkbox">
                    <span class="option-title">
							Add title tag to the embed iframe
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_SUGGESTED" type="checkbox">
                    <span class="option-title">
							Limit suggested videos at the end of the embed to the video channel  
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_LOGO" type="checkbox">
                    <span class="option-title">
							disable Youtube logo 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_COMMENT" type="checkbox">
                    <span class="option-title">
							Post Youtube Comments as Comments 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_LIMIT_EMBED" type="checkbox">
                    <span class="option-title">
							Limit search to embeddable videos 
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input data-controls="wp_automatic_yt_sctry" name="camp_options[]" value="OPT_YT_LIMIT_CTRY" type="checkbox">
                    <span class="option-title">
							Search results for a specified country  
                    </span>
                    <br>
                    
                    <div id="wp_automatic_yt_sctry" class="field f_100">
                    	
                       <label>
		                    Region Code
		               </label>
		               
		               <input value="<?php   echo @$camp_general['cg_yt_ctr']   ?>" name="cg_yt_ctr"   type="text">
		               
		               <div class="description">Example: "US" for united states. check other countries region codes <a target="_blank" href="https://www.iso.org/obp/ui/#search/code/">here</a><br><br>!Single country only, no multiple values, no commas</div>
                    
                    </div>
                    
               </div>
               
               <div class="option clearfix">
                    <input data-controls="wp_automatic_yt_slang" name="camp_options[]" value="OPT_YT_LIMIT_LANG" type="checkbox">
                    <span class="option-title">
							Set relevance to a specific language  
                    </span>
                    <br>
                    
                    <div id="wp_automatic_yt_slang" class="field f_100">
                    	
                       <label>
		                    Language Code
		               </label>
		               
		               <input value="<?php   echo @$camp_general['cg_yt_lang']   ?>" name="cg_yt_lang"   type="text">
		               
		               <div class="description">Example: "en" for English. check other languages codes <a target="_blank" href="http://www.loc.gov/standards/iso639-2/php/code_list.php">here</a><br><br>Alert: Single language only, no multiple langauges, no commas</div>
                    
                    </div>
                    
               </div>

				<div class="option clearfix">
                    <input name="camp_options[]" value="OPT_YT_CAPTION" type="checkbox">
                    <span class="option-title">
							Enable caption
                    </span>
                    <br>
               </div>

			   <!-- set player subtitle language-->
               <div class="option clearfix">
                    <input data-controls="wp_automatic_yt_plang" name="camp_options[]" value="OPT_YT_PLAYER_LANG" type="checkbox">
                    <span class="option-title">
							Set player/subtitles language  
                    </span>
                    <br>
                    
                    <div id="wp_automatic_yt_plang" class="field f_100">
                    	
                       <label>
		                    Language Code
		               </label>
		               
		               <input value="<?php   echo @$camp_general['cg_yt_plang']   ?>" name="cg_yt_plang"   type="text">
		               
		               <div class="description">Example: "en" for English. check other languages codes <a target="_blank" href="http://www.loc.gov/standards/iso639-2/php/code_list.php">here</a></div>
                    
                    </div>
                    
               </div>

			   <!-- Truncate transcript to a specific length in chars -->
			   <div class="option clearfix">
				   <input data-controls="wp_automatic_yt_truncate" name="camp_options[]" value="OPT_YT_TRUNCATE" type="checkbox">
				   <span class="option-title">
							Truncate transcript to a specific length in chars
					</span>
				   <br>

				   <div id="wp_automatic_yt_truncate" class="field f_100">

					   <label>
						   Length
					   </label>

					   <input value="<?php   echo @$camp_general['cg_yt_truncate']   ?>" name="cg_yt_truncate"   type="text">

					   <div class="description">Example: 1000</div>

				   </div>
				</div>
               
                
               
		 </div>
		 
          
		 </div>
		 <!--  / Youtube Part -->
		
		<div class="typepart Spintax" style="display:none">
			
			 <div class="field f_100">
		 
				
				<div class="option clearfix">
					<input name="camp_options[]" value="OPT_SP_SIMILAR" type="checkbox"> 
					<span class="option-title">Replace all similar sets with the same value</span>
				</div>
	
			</div>
			
		</div>
		 
		 <!--  eBay Part -->
		 <div class="typepart eBay" style="display:none">

		 <div id="eb_user_div" class="field f_100">
               <div class="option clearfix">
                    
                    <input data-controls="eb_user_c" name="camp_options[]" id="eb_user" value="OPT_EB_USER" type="checkbox">
                    <span class="option-title">
							Post from a specific eBay seller  
                    </span>
                    <br>
                    
		            <div id="eb_user_c" class="field f_100">
		               <label>
		                    User id  
		               </label>
		                
		                <input value="<?php   echo  @$camp_general['cg_eb_user']   ?>" name="cg_eb_user"    type="text">
		            	<br>
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]" id="eb_full" value="OPT_EB_FULL" type="checkbox">
		                    <span class="option-title">
									Don't use keywords add all items without filtering from this seller. 
		                    </span>
	                    
	                    <div class="description">e.g add batteryshack as the user id  for this seller https://www.ebay.com/usr/batteryshack<br><br>Important:You must either set a specific category or keyword if you want to import from a specific seller</div>
	                    
	                    </div>
	                    <br>
		            </div>
		            
               </div>
		 </div>
		 
		 <div   class="field f_100" >
			<label>
				eBay Site :
			</label>
			<select name="cg_eb_site" >
				
				<?php  $camp_general['cg_eb_site'] =  wp_automatic_fix_category($camp_general['cg_eb_site']) ;?>
				
				<option  value="EBAY-US"  <?php @wp_automatic_opt_selected("EBAY-US",$camp_general['cg_eb_site']) ?> >eBay United States</option>
				<option  value="EBAY-AT"  <?php @wp_automatic_opt_selected("EBAY-AT",$camp_general['cg_eb_site']) ?> >eBay Austria</option>
				<option  value="EBAY-AU"  <?php @wp_automatic_opt_selected("EBAY-AU",$camp_general['cg_eb_site']) ?> >eBay Australia</option>
				<option  value="EBAY-CH"  <?php @wp_automatic_opt_selected("EBAY-CH",$camp_general['cg_eb_site']) ?> >eBay Switzerland</option>
				<option  value="EBAY-DE"  <?php @wp_automatic_opt_selected("EBAY-DE",$camp_general['cg_eb_site']) ?> >eBay Germany</option>
				<option  value="EBAY-ENCA"  <?php @wp_automatic_opt_selected("EBAY-ENCA",$camp_general['cg_eb_site']) ?> >eBay Canada (English)</option>
				<option  value="EBAY-ES"  <?php @wp_automatic_opt_selected("EBAY-ES",$camp_general['cg_eb_site']) ?> >eBay Spain</option>
				<option  value="EBAY-FR"  <?php @wp_automatic_opt_selected("EBAY-FR",$camp_general['cg_eb_site']) ?> >eBay France</option>
				<option  value="EBAY-FRBE"  <?php @wp_automatic_opt_selected("EBAY-FRBE",$camp_general['cg_eb_site']) ?> >eBay Belgium (French)</option>
				<option  value="EBAY-FRCA"  <?php @wp_automatic_opt_selected("EBAY-FRCA",$camp_general['cg_eb_site']) ?> >eBay Canada (French)</option>
				<option  value="EBAY-GB"  <?php @wp_automatic_opt_selected("EBAY-GB",$camp_general['cg_eb_site']) ?> >eBay UK</option>
				<option  value="EBAY-HK"  <?php @wp_automatic_opt_selected("EBAY-HK",$camp_general['cg_eb_site']) ?> >eBay Hong Kong</option>
				<option  value="EBAY-IE"  <?php @wp_automatic_opt_selected("EBAY-IE",$camp_general['cg_eb_site']) ?> >eBay Ireland</option>
				<option  value="EBAY-IN"  <?php @wp_automatic_opt_selected("EBAY-IN",$camp_general['cg_eb_site']) ?> >eBay India</option>
				<option  value="EBAY-IT"  <?php @wp_automatic_opt_selected("EBAY-IT",$camp_general['cg_eb_site']) ?> >eBay Italy</option>
				<option  value="EBAY-MOTOR"  <?php @wp_automatic_opt_selected("EBAY-MOTOR",$camp_general['cg_eb_site']) ?> >eBay Motors</option>
				<option  value="EBAY-MY"  <?php @wp_automatic_opt_selected("EBAY-MY",$camp_general['cg_eb_site']) ?> >eBay Malaysia</option>
				<option  value="EBAY-NL"  <?php @wp_automatic_opt_selected("EBAY-NL",$camp_general['cg_eb_site']) ?> >eBay Netherlands</option>
				<option  value="EBAY-NLBE"  <?php @wp_automatic_opt_selected("EBAY-NLBE",$camp_general['cg_eb_site']) ?> >eBay Belgium (Dutch)</option>
				<option  value="EBAY-PH"  <?php @wp_automatic_opt_selected("EBAY-PH",$camp_general['cg_eb_site']) ?> >eBay Philippines</option>
				<option  value="EBAY-PL"  <?php @wp_automatic_opt_selected("EBAY-PL",$camp_general['cg_eb_site']) ?> >eBay Poland</option>
				<option  value="EBAY-SG"  <?php @wp_automatic_opt_selected("EBAY-SG",$camp_general['cg_eb_site']) ?> >eBay Singapore</option>
				 
								
			 </select>
		</div>
		 
		 <div   class="field f_100" >
			<label>
				eBay category :
			</label>
			<select name="cg_eb_cat" >
							<option  value="0"  <?php @wp_automatic_opt_selected("0",$camp_general['cg_eb_cat']) ?> >All Categories</option>

							<option  value="20081"  <?php @wp_automatic_opt_selected("20081",$camp_general['cg_eb_cat']) ?> >Antiques</option>
							
							<option  value="550"  <?php @wp_automatic_opt_selected("550",$camp_general['cg_eb_cat']) ?> >Art</option>
							
							<option  value="2984"  <?php @wp_automatic_opt_selected("2984",$camp_general['cg_eb_cat']) ?> >Baby</option>
							
							<option  value="267"  <?php @wp_automatic_opt_selected("267",$camp_general['cg_eb_cat']) ?> >Books</option>
							
							<option  value="12576"  <?php @wp_automatic_opt_selected("12576",$camp_general['cg_eb_cat']) ?> >Business & Industrial</option>
							
							<option  value="625"  <?php @wp_automatic_opt_selected("625",$camp_general['cg_eb_cat']) ?> >Cameras & Photo</option>
							
							<option  value="15032"  <?php @wp_automatic_opt_selected("15032",$camp_general['cg_eb_cat']) ?> >Cell Phones & Accessories</option>
							
							<option  value="11450"  <?php @wp_automatic_opt_selected("11450",$camp_general['cg_eb_cat']) ?> >Clothing, Shoes & Accessories</option>
							
							<option  value="11116"  <?php @wp_automatic_opt_selected("11116",$camp_general['cg_eb_cat']) ?> >Coins & Paper Money</option>
							
							<option  value="1"  <?php @wp_automatic_opt_selected("1",$camp_general['cg_eb_cat']) ?> >Collectibles</option>
							
							<option  value="58058"  <?php @wp_automatic_opt_selected("58058",$camp_general['cg_eb_cat']) ?> >Computers/Tablets & Networking</option>
							
							<option  value="293"  <?php @wp_automatic_opt_selected("293",$camp_general['cg_eb_cat']) ?> >Consumer Electronics</option>
							
							<option  value="14339"  <?php @wp_automatic_opt_selected("14339",$camp_general['cg_eb_cat']) ?> >Crafts</option>
							
							<option  value="237"  <?php @wp_automatic_opt_selected("237",$camp_general['cg_eb_cat']) ?> >Dolls & Bears</option>
							
							<option  value="11232"  <?php @wp_automatic_opt_selected("11232",$camp_general['cg_eb_cat']) ?> >DVDs & Movies</option>
							
							<option  value="6000"  <?php @wp_automatic_opt_selected("6000",$camp_general['cg_eb_cat']) ?> >eBay Motors</option>
							
							<option  value="45100"  <?php @wp_automatic_opt_selected("45100",$camp_general['cg_eb_cat']) ?> >Entertainment Memorabilia</option>
							
							<option  value="172008"  <?php @wp_automatic_opt_selected("172008",$camp_general['cg_eb_cat']) ?> >Gift Cards & Coupons</option>
							
							<option  value="26395"  <?php @wp_automatic_opt_selected("26395",$camp_general['cg_eb_cat']) ?> >Health & Beauty</option>
							
							<option  value="11700"  <?php @wp_automatic_opt_selected("11700",$camp_general['cg_eb_cat']) ?> >Home & Garden</option>
							
							<option  value="281"  <?php @wp_automatic_opt_selected("281",$camp_general['cg_eb_cat']) ?> >Jewelry & Watches</option>
							
							<option  value="11233"  <?php @wp_automatic_opt_selected("11233",$camp_general['cg_eb_cat']) ?> >Music</option>
							
							<option  value="619"  <?php @wp_automatic_opt_selected("619",$camp_general['cg_eb_cat']) ?> >Musical Instruments & Gear</option>
							
							<option  value="1281"  <?php @wp_automatic_opt_selected("1281",$camp_general['cg_eb_cat']) ?> >Pet Supplies</option>
							
							<option  value="870"  <?php @wp_automatic_opt_selected("870",$camp_general['cg_eb_cat']) ?> >Pottery & Glass</option>
							
							<option  value="10542"  <?php @wp_automatic_opt_selected("10542",$camp_general['cg_eb_cat']) ?> >Real Estate</option>
							
							<option  value="316"  <?php @wp_automatic_opt_selected("316",$camp_general['cg_eb_cat']) ?> >Specialty Services</option>
							
							<option  value="888"  <?php @wp_automatic_opt_selected("888",$camp_general['cg_eb_cat']) ?> >Sporting Goods</option>
							
							<option  value="64482"  <?php @wp_automatic_opt_selected("64482",$camp_general['cg_eb_cat']) ?> >Sports Mem, Cards & Fan Shop</option>
							
							<option  value="260"  <?php @wp_automatic_opt_selected("260",$camp_general['cg_eb_cat']) ?> >Stamps</option>
							
							<option  value="1305"  <?php @wp_automatic_opt_selected("1305",$camp_general['cg_eb_cat']) ?> >Tickets & Experiences</option>
							
							<option  value="220"  <?php @wp_automatic_opt_selected("220",$camp_general['cg_eb_cat']) ?> >Toys & Hobbies</option>
							
							<option  value="3252"  <?php @wp_automatic_opt_selected("3252",$camp_general['cg_eb_cat']) ?> >Travel</option>
							
							<option  value="1249"  <?php @wp_automatic_opt_selected("1249",$camp_general['cg_eb_cat']) ?> >Video Games & Consoles</option>
							
							<option  value="99"  <?php @wp_automatic_opt_selected("99",$camp_general['cg_eb_cat']) ?> >Everything Else</option>		 
							 
			</select>
		</div>
		
		
		 <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]"  data-controls="ebay_custom_cat" id="ebay_custom_cat_c" value="OPT_EBAY_CUSTOM" type="checkbox">
                    <span class="option-title">
							Custom ebay category ID ?
                    </span>
                    <br>
                    
		            <div id="ebay_custom_cat" class="field f_100">
		               <label for="field6">
		                    Custom Category ID ( <a target="_blank" href="http://valvepress.com/post-ebay-subcategory-wordpress-using-wordpress-automatic/">how to get cat id</a>)
		               </label>
		               
		                <input value="<?php    echo @$camp_general['cg_ebay_custom']   ?>"  name="cg_ebay_custom" type="text">
		               
		               	<div class="description">example: 15032</div>
		               
		            </div>
		            
               </div>
		 </div>
		
		<div   class="field f_100" >
			<label>
				Listing Type :
			</label>
			<select name="cg_eb_listing" >
							<option  value="All"  <?php @wp_automatic_opt_selected("All",$camp_general['cg_eb_listing']) ?> >All Listings</option>
							<option  value="Auction"  <?php @wp_automatic_opt_selected("Auction",$camp_general['cg_eb_listing']) ?> >Auction Only</option>
							<option  value="BEST_OFFER"  <?php @wp_automatic_opt_selected("BEST_OFFER",$camp_general['cg_eb_listing']) ?> >Best offer</option>
							<option  value="FixedPrice"  <?php @wp_automatic_opt_selected("FixedPrice",$camp_general['cg_eb_listing']) ?> >Fixed Price</option>
							
							
 			</select>
		</div>
		 
		 <div   class="field f_100" >
		 	<label for="field-camp_youtube_order">
		 		eBay search order :
		 	</label>
		 	 
		 	<select name="cg_eb_order" >
		 		
		 		<option  value="BestMatch"  <?php @wp_automatic_opt_selected('BestMatch',$camp_general['cg_eb_order']) ?> >Best Match</option>
		 		<option  value="EndTimeSoonest"  <?php @wp_automatic_opt_selected('EndTimeSoonest',$camp_general['cg_eb_order']) ?> >Items Ending First</option>
		 		<option  value="StartTimeNewest"  <?php @wp_automatic_opt_selected('StartTimeNewest',$camp_general['cg_eb_order']) ?> >Newly-Listed Items First</option>
		 		<option  value="PricePlusShippingLowest"  <?php @wp_automatic_opt_selected('PricePlusShippingLowest',$camp_general['cg_eb_order']) ?> >Price Lowest First</option>
		 		<option  value="PricePlusShippingHighest"  <?php @wp_automatic_opt_selected('PricePlusShippingHighest',$camp_general['cg_eb_order']) ?> >Price Highst First</option>
		 		<option  value="distance"  <?php @wp_automatic_opt_selected('distance',$camp_general['cg_eb_order']) ?> >Distance (shortest distance first)</option>
		 		 <option  value="-distance"  <?php @wp_automatic_opt_selected('-distance',$camp_general['cg_eb_order']) ?> >Distance (shortest distance last)</option>
		 	</select>
		 </div>
		 
		 <div   class="field f_100" >
		 	<label>
		 		Item condition:
		 	</label>
		 	 
		 	<select name="cg_eb_condition" >
		 		
		 		<option  value="ANY"  <?php @wp_automatic_opt_selected('ANY',$camp_general['cg_eb_condition']) ?> >Any</option>
		 		<option  value="NEW"  <?php @wp_automatic_opt_selected('NEW',$camp_general['cg_eb_condition']) ?> >New</option>
		 		 <option  value="USED"  <?php @wp_automatic_opt_selected('USED',$camp_general['cg_eb_condition']) ?> >Used</option>

		 	</select>
		 </div>
		 
		 <div   class="field f_100" >
		 	<label>
		 		Seller type:
		 	</label>
		 	 
		 	<select name="cg_eb_seller_type" >
		 		
		 		<option  value="ANY"  <?php @wp_automatic_opt_selected('ANY',$camp_general['cg_eb_seller_type']) ?> >Any</option>
		 		<option  value="INDIVIDUAL"  <?php @wp_automatic_opt_selected('INDIVIDUAL',$camp_general['cg_eb_seller_type']) ?> >Individual</option>
		 		 <option  value="BUSINESS"  <?php @wp_automatic_opt_selected('BUSINESS',$camp_general['cg_eb_seller_type']) ?> >Business</option>

		 	</select>
		 </div>
		 

         <div   class="field f_100">
               <div class="option clearfix">
                    <input id="eb_price" data-controls="eb_price_c" name="camp_options[]" value="OPT_EB_PRICE" type="checkbox">
                    <span class="option-title">
							Price range
                    </span>
                    <br>
                    
                    <div id="eb_price_c" class="field f_100">
		              
		                From <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_min']   ?>" name="cg_eb_min"    type="text"> To <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_max']   ?>" name="cg_eb_max"    type="text">
		            	
		            		Currency 	<input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_currency']   ?>" name="cg_eb_currency"    type="text">
		            		<div class="description">example1: From 10 To 50 Currency USD returns products from $10 to $50<br>
		            		example2: From 10 To Empty Currency USD returns products starting from $10<br>
		            		example3: From Empty To 50 Currency USD returns products not higher than $50<br></div>
		            		
		              </div>
                    
               </div>
               
               <div class="option clearfix">
                    <input   data-controls="eb_bid_count_c" name="camp_options[]" value="OPT_EB_BID_COUNT" type="checkbox">
                    <span class="option-title">
							Bid count range
                    </span>
                    <br>
                    
                    <div id="eb_bid_count_c" class="field f_100">
		              
		                From <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_min_bid']   ?>" name="cg_eb_min_bid"    type="text"> To <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_max_bid']   ?>" name="cg_eb_max_bid"    type="text">
		            		
		            		<div class="description">example1: From 10 To 50   returns products with current bids between 10 and 50<br>
		            		example2: From 10 To Empty  returns products with at least 10 bids available<br>
		            		example3: From Empty To 50  returns products with no higher than 50 bids available<br></div>
		            		
		              </div>
                    
               </div>
               
               <div class="option clearfix">
                    <input   data-controls="eb_currency" name="camp_options[]" value="OPT_EB_CURRENCY" type="checkbox">
                    <span class="option-title">
							Specific currency
                    </span>
                    <br>
                    
                    <div id="eb_currency" class="field f_100">
		              
		                Currency <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_currency_2']   ?>" name="cg_eb_currency_2"    type="text"> 
		            		
		            		<div class="description">example: USD</div>
		            		
		              </div>
                    
               </div>
		 			 
               <div class="option clearfix">
                    <input   data-controls="eb_country" name="camp_options[]" value="OPT_EB_COUNTRY" type="checkbox">
                    <span class="option-title">
							Specific delivery country / postal code
                    </span>
                    <br>
                    
                    <div id="eb_country" class="field f_100">
		              
		                Country code <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_country']   ?>" name="cg_eb_country"    type="text"> 
		            		
		            		<br><br>Postal code (optional) <input style="width:100px" class="no-unify" value="<?php   echo  @$camp_general['cg_eb_postal']   ?>" name="cg_eb_postal"    type="text">
		            		
		            		<div class="description">Example country code: US<br>Example postal code:95125 </div>
		            		
		            		
		            		
		              </div>
                    
               </div>		 
          
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_TOP" type="checkbox">
                    <span class="option-title">
							Top Rated Sellers
                    </span>
                    <br>
               </div>
               
                <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_CHARITY" type="checkbox">
                    <span class="option-title">
							Charity only products
                    </span>
                    <br>
               </div>
               
                 <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_PRIORITY" type="checkbox">
                    <span class="option-title">
							Priority listing (Only items that are a part of a Promoted Listings campaign)
                    </span>
                    <br>
               </div>
               
		  			 
		 		<div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_RETURN" type="checkbox">
                    <span class="option-title">
							Returns accepted only products
                    </span>
                    <br>
               </div>
          
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_DESCRIPTION" type="checkbox">
                    <span class="option-title">
							Search description
                    </span>
                    <br>
               </div>
		  		
		 
		  
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_SHIP" type="checkbox">
                    <span class="option-title">
							Free Shipping
                    </span>
                    <br>
               </div>
		  		 
		 
          
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_CACHE" type="checkbox">
                    <span class="option-title">
							Cache Items for faster posting  (Uncheck to call eBay each post) (Pagination will not work if disabled)
                    </span>
                    <br>
               </div>
		  
		 
		 
		 
		  
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_EB_FULL_DESC_SPEC" type="checkbox">
                    <span class="option-title">
							Try to fetch detailed item info and item specifics 
                    </span>
                    <br>
               </div>
		  
		 
		  
               <div class="option clearfix">
                    <input data-controls="eb_iframe_height" name="camp_options[]" value="OPT_EB_FULL_DESC" type="checkbox">
                    <span class="option-title">
							Try to fetch item description iframe
                    </span>
                    
                    <br>
                    <div id="eb_iframe_height"  class="field f_100">
                    
                    	<label>
		                    Iframe height in pixels default 500
		               </label>
		               
		                <input value="<?php    echo   (@$camp_general['cg_eb_iframe_h'] )  ?>" placeholder="500"  name="cg_eb_iframe_h" type="text">
		               
                    
                    </div>
                    
                    <br>
               </div>
		  
		 
		    
               <div class="option clearfix">
                    <input name="camp_options[]" data-controls="eb_full_img_t"  value="OPT_EB_FULL_IMG" type="checkbox">
                    <span class="option-title">
							Try to fetch all images (by default single image)
                    </span>
                    <br>
                    
                   
                    
		            <div id="eb_full_img_t" class="field f_100">
		             
		             	 <div class="option clearfix">
		                    <input name="camp_options[]"   value="OPT_EB_GALLERY" type="checkbox">
		                    <span class="option-title">
									Add the item images as a Woo-Commerce product gallery.
		                    </span>
		                    <br>
	                    </div>
               		  
               		   <div class="option clearfix">
			               <label for="field6">
			                    Image template 
			               </label>
			                <input value="<?php     echo   (  wp_automatic_htmlentities($camp_general['cg_eb_full_img_t'],ENT_COMPAT, 'UTF-8' )  )  ?>"  name="cg_eb_full_img_t" type="text">
		              		<div class="description">(this is how the plugin will build images html) use [img_src] to replace the image src. use class="wp_automatic_gallery" to display images as gallery<br><br>
		              		Deafult:<?php echo  wp_automatic_htmlentities( '<img src="[img_src]"  class="wp_automatic_gallery" />' ,ENT_COMPAT, 'UTF-8')   ?></div>
		               </div>
		            </div>
               </div>
		 
		 
		  
               <div class="option clearfix">
                    <input name="camp_options[]" data-controls="eb_parm"  value="OPT_EB_PARAM" type="checkbox">
                    <span class="option-title">
							Append additional parameters to the REST request (Advanced)
                    </span>
                    <br>
                    
		            <div id="eb_parm" class="field f_100">
		               <label>
		                    url parameters 
		               </label>
		               
		                <input value="<?php    echo   (@$camp_general['cg_eb_param'] )  ?>"  name="cg_eb_param" type="text">
		                <div class="description">example:   &buyerPostalCode=b691sw </div>
		            </div>
               </div>
		  
		 
		  
               <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_EBAY_EXCERPT" type="checkbox">
                    <span class="option-title">
							Set the product description as excerpt  
                    </span>
                    <br>
               </div>
		  
		 
		  
               <div class="option clearfix">
                    <input name="camp_options[]" data-controls="eb_redirect_end"  value="OPT_EB_REDIRECT_END" type="checkbox">
                    <span class="option-title">
							Redirect to a specific page if item end date reached (& optionally trash)
                    </span>
                    <br>
                    
		            <div id="eb_redirect_end" class="field f_100">
		               <label >
		                    Page link to redirect to 
		               </label>
		               
		                <input value="<?php    echo   (@$camp_general['cg_eb_redirect_end'] )  ?>"  name="cg_eb_redirect_end" type="text">
		                
		                <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_EB_TRASH" type="checkbox">
		                    <span class="option-title">
									Trash posts also 
		                    </span>
		                    <br>
		               </div>
		                
		               
		            </div>
               </div>
		  

		 </div>
		 
		 </div>
		 <!--  / eBay Part -->
		 
		 <!--  Flicker Part -->
		 <div class="typepart Flicker" style="display:none">

		 <div   class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_FL_TAG" type="checkbox">
                    <span class="option-title">
							Add Flickr images tags as posts tags
                    </span>
                    <br>
               </div>
		 </div>		 
		 
		 <div id="fl_user_div" class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" id="fl_user" value="OPT_FL_USER" type="checkbox">
                    <span class="option-title">
							Post from specific flickr user  
                    </span>
                    <br>
                    
		            <div id="fl_user_c" class="field f_100">
		               <label for="field6">
		                    User id (click <a target="_blank" href="http://idgettr.com/">here</a> to get the id) (example id : 59164655@N00 ) 
		               </label>
		                
		                <input value="<?php   echo  @$camp_general['cg_fl_user']   ?>" name="cg_fl_user" id="field6"   type="text">
		                
		                <br><br>
		                
		                <label for="field6">
		                    <label>
		                    Album ID (Optional)
			               </label>
			                
		                </label>
		               
		                
		                <input value="<?php   echo  @$camp_general['cg_fl_user_album']   ?>" name="cg_fl_user_album"   type="text">
		                if you want to post from specific album, paste the album id here (last numbers at the album url) and the user id above. for example the id for this <a href="https://www.flickr.com/photos/tonydefilippo/albums/72157660698727425">album</a> is "72157660698727425"
		                
		            	
		            	<br>
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]" id="fl_full" value="OPT_FL_FULL" type="checkbox">
		                    <span class="option-title">
									Don't use keywords add images without filtering . 
		                    </span>
	                    </div>
	                    <br>
		            </div>
		            
               </div>
		 </div>

		 <!-- Specific Group -->
		 <div id="fl_group_div" class="field f_100">
			   <div class="option clearfix">
					
					<input name="camp_options[]" data-controls="fl_group_c" value="OPT_FL_GROUP" type="checkbox">
					<span class="option-title">
							Post from specific flickr group  
					</span>
					<br>
					
		            <div id="fl_group_c" class="field f_100">
		               <label for="field6">
		                    Group id (click <a target="_blank" href="http://idgettr.com/">here</a> to get the id) (example id : 3021664@N23 ) 
		               </label>
		                
		                <input value="<?php   echo  @$camp_general['cg_fl_group']   ?>" name="cg_fl_group" id="field6"   type="text">
		                
		                 
		            	<br>
		            	<div class="field f_100">
		            	<input  data-controls-r='' name="camp_options[]" id="fl_full" value="OPT_FL_FULL" type="checkbox">
		                    <span class="option-title">
									Don't use keywords add images without filtering . 
		                    </span>
	                    </div>
	                    <br>
		            </div>
		            
			   </div>
		</div>
		 
		 <div   class="field f_100" >
		 	<label for="field-camp_youtube_order">
		 		Flickr search order :
		 	</label>
		 	 
		 	<select name="cg_fl_order" id="field1zz">
		 		<option  value="relevance"  <?php @wp_automatic_opt_selected('relevance',$camp_general['cg_fl_order']) ?> >Relevance</option>
		 		<option  value="date-posted-asc"  <?php @wp_automatic_opt_selected('date-posted-asc',$camp_general['cg_fl_order']) ?> >Date Posted ASC</option>
		 		<option  value="date-posted-desc"  <?php @wp_automatic_opt_selected('date-posted-desc',$camp_general['cg_fl_order']) ?> >Date Posted DESC</option>
		 		<option  value="date-taken-asc"  <?php @wp_automatic_opt_selected('date-taken-asc',$camp_general['cg_fl_order']) ?> >Date Taken ASC</option>
		 		<option  value="date-taken-desc"  <?php @wp_automatic_opt_selected('date-taken-desc',$camp_general['cg_fl_order']) ?> >Date Taken DESC</option>
		 		<option  value="interestingness-desc"  <?php @wp_automatic_opt_selected('interestingness-desc',$camp_general['cg_fl_order']) ?> >Interestingness DESC</option>
		 		<option  value="interestingness-asc"  <?php @wp_automatic_opt_selected('interestingness-asc',$camp_general['cg_fl_order']) ?> >Interestingness ASC</option>
		 
		 	</select>
		 </div>

		
	 	
				  
         <div   class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_FL_CACHE" type="checkbox">
                    <span class="option-title">
							Cache Images for faster posting  (uncheck to call flickr each post)
                    </span>
                    <br>
               </div>
               
               <div class="option clearfix">
                    <input data-controls="wp_automatic_fl_licenses" name="camp_options[]" value="OPT_FL_LICENSE" type="checkbox">
                    <span class="option-title">
							Search for specific license
                    </span>
                    <br>
                    
                    
                    <div id="wp_automatic_fl_licenses" class="field f_100">
                    	
                    	<div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_0" type="checkbox">
		                    <span class="option-title">
									All Rights Reserved
		                    </span>
		                    <br>
		               </div>	
		               
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_1" type="checkbox">
		                    <span class="option-title">
									Attribution-NonCommercial-ShareAlike License
		                    </span>
		                    <br>
		               </div>
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_2" type="checkbox">
		                    <span class="option-title">
									Attribution-NonCommercial License
		                    </span>
		                    <br>
		               </div>
		               
		               
		               
                    	<div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_3" type="checkbox">
		                    <span class="option-title">
									Attribution-NonCommercial-NoDerivs License
		                    </span>
		                    <br>
		               </div>
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_4" type="checkbox">
		                    <span class="option-title">
									Attribution License
		                    </span>
		                    <br>
		               </div>
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_5" type="checkbox">
		                    <span class="option-title">
									Attribution-ShareAlike License
		                    </span>
		                    <br>
		               </div>
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_6" type="checkbox">
		                    <span class="option-title">
									Attribution-NoDerivs License
		                    </span>
		                    <br>
		               </div>
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_7" type="checkbox">
		                    <span class="option-title">
									No known copyright restrictions
		                    </span>
		                    <br>
		               </div>
		               
		               <div class="option clearfix">
		                    <input name="camp_options[]" value="OPT_FL_LICENSE_8" type="checkbox">
		                    <span class="option-title">
									United States Government Work
		                    </span>
		                    <br>
		               </div>
		                           
                    
                     
                    
               </div>
               
		 </div>
		 
		 </div>
		 
		 </div>
		 <!--  / flicker Part -->
		 
		 <!-- Click bank part -->
		 <div class="typepart Clickbank" style="display:none">
		   <div id="field1zz-container" class="field f_100 ">
               <label for="field1zz">
                    Add Products from this category :
               </label>
               <select name="camp_cb_category" id="field1zz">
                    <option id="field1-1" value="All">
                         All
                    </option>
 

					<?php
					
					$categories_main = '["Arts & Entertainment","As Seen on TV","Betting Systems","Business / Investing","Computers / Internet","Cooking, Food & Wine","E-Business & E-Marketing","Education","Employment & Jobs","Fiction","Games","Green Products","Health & Fitness","Home & Garden","Languages","Mobile","Parenting & Families","Politics / Current Events","Reference","Self-Help","Software & Services","Spirituality, New Age & Alternative Beliefs","Sports","Travel"]';
					$categories_sub   = '[["Architecture","Art","Body Art","Dance","Fashion","Film & Television","General","Humor","Magic Tricks","Music","Photography","Radio","Theater"],["Auto","Backyard Living","General","Health and Beauty","Kitchen Tools and Gadgets"],["Casino Table Games","Football","General","Horse Racing","Poker","Soccer"],["Careers, Industries & Professions","Commodities","Debt","Derivatives","Economics","Equities & Stocks","Foreign Exchange","General","International Business","Management & Leadership","Marketing & Sales","Outsourcing","Personal Finance","Real Estate","Small Biz / Entrepreneurship"],["Databases","Email Services","General","Graphics","Hardware","Networking","Operating Systems","Programming","Software","System Administration","System Analysis & Design","Web Hosting","Web Site Design"],["Baking","BBQ","Cooking","Drinks & Beverages","General","Recipes","Regional & Intl.","Special Diet","Special Occasions","Vegetables / Vegetarian","Wine Making"],["Affiliate Marketing","Article Marketing","Auctions","Banners","Blog Marketing","Classified Advertising","Consulting","Copywriting","Domains","E-commerce Operations","E-zine Strategies","Email Marketing","General","Market Research","Marketing","Niche Marketing","Paid Surveys","Pay Per Click Advertising","Promotion","SEM & SEO","Social Media Marketing","Submitters","Video Marketing"],["Admissions","Educational Materials","Higher Education","K-12","Student Loans","Test Prep & Study Guides"],["Cover Letter & Resume Guides","General","Job Listings","Job Search Guides","Job Skills / Training"],["General"],["Console Guides & Repairs","General","Strategy Guides"],["Alternative Energy","Conservation & Efficiency","General"],["Addiction","Beauty","Dental Health","Dietary Supplements","Diets & Weight Loss","Exercise & Fitness","General","Meditation","Men\'s Health","Mental Health","Nutrition","Remedies","Sleep and Dreams","Spiritual Health","Strength Training","Women\'s Health","Yoga"],["Animal Care & Pets","Crafts & Hobbies","Entertaining","Gardening & Horticulture","General","Homebuying","How-to & Home Improvements","Interior Design","Sewing","Weddings"],["Arabic","Chinese","English","French","German","Hebrew","Hindi","Italian","Japanese","Other","Russian","Sign Language","Spanish","Thai"],["Apps","Developer Tools","General","Ringtones","Security","Video"],["Divorce","Education","Genealogy","General","Marriage","Parenting","Pregnancy & Childbirth","Special Needs"],["General"],["Automotive","Catalogs & Directories","Consumer Guides","Education","Etiquette","Gay / Lesbian","General","Law & Legal Issues","The Sciences","Writing"],["Abuse","Dating Guides","Eating Disorders","General","Marriage & Relationships","Motivational / Transformational","Personal Finance","Public Speaking","Self Defense","Self-Esteem","Stress Management","Success","Survival","Time Management"],["Anti Adware / Spyware","Background Investigations","Communications","Developer Tools","Digital Photos","Drivers","Education","Email","Foreign Exchange Investing","General","Graphic Design","Hosting","Internet Tools","MP3 & Audio","Networking","Operating Systems","Other Investment Software","Personal Finance","Productivity","Registry Cleaners","Reverse Phone Lookup","Screensavers & Wallpaper","Security","System Optimization","Utilities","Video","Web Design","3D Printing"],["Astrology","General","Hypnosis","Magic","Numerology","Paranormal","Psychics","Religion","Tarot","Witchcraft"],["Automotive","Baseball","Basketball","Coaching","Cycling","Extreme Sports","Football","General","Golf","Hockey","Individual Sports","Martial Arts","Mountaineering","Other Team Sports","Outdoors & Nature","Racket Sports","Running","Soccer","Softball","Training","Volleyball","Water Sports","Winter Sports"],["Africa","Asia","Canada","Caribbean","Europe","General","Latin America","Middle East","Specialty Travel","United States"]]';
					
					$categories_main_json = json_decode($categories_main);
					$categories_sub_json   = json_decode($categories_sub);
					
	 					 $c = 0; 
						foreach($categories_main_json as $cat){
							 
							echo '<option   value="'.$cat.'"';
							wp_automatic_opt_selected($cat ,$camp_cb_category );
							echo '>'.$cat.'</option>';
						
							 
							foreach($categories_sub_json[$c] as $sub){
								 
								   echo '<option   value="' . $cat . ',' . $sub .'" ';
								 @wp_automatic_opt_selected($cat.','.$sub ,$camp_cb_category );
								   echo ' >-- ' . $sub . '</option>';
							}
						
						$c++;
						}
						
					 ?>



               </select>
          </div>
          
          <div class="field f_100">
               <div class="option clearfix">
                    <input name="camp_options[]"  value="OPT_CB_DESCRIPTION" type="checkbox">
                    <span class="option-title">
							Try to fetch product text from original site      
                    </span>
                    <br>
               </div>
		 </div>
          
          <div class="field f_100 ">
               <label>
                    Specific products language :
               </label>
               <select name="cg_cb_lang">
                    
                    <option value="ALL" <?php @wp_automatic_opt_selected('ALL',$camp_general['cg_cb_lang']) ?> >
                         ALL
                    </option>
                    
                    <option value="EN" <?php @wp_automatic_opt_selected('EN',$camp_general['cg_cb_lang']) ?> >
                         English
                    </option>
                    
                    <option value="DE" <?php @wp_automatic_opt_selected('DE',$camp_general['cg_cb_lang']) ?> >
                         German
                    </option>
                    
                    <option value="ES" <?php @wp_automatic_opt_selected('ES',$camp_general['cg_cb_lang']) ?> >
                         Spanish
                    </option>
                    
                    <option value="FR" <?php @wp_automatic_opt_selected('FR',$camp_general['cg_cb_lang']) ?> >
                         French
                    </option>
                    
                    <option value="IT" <?php @wp_automatic_opt_selected('IT',$camp_general['cg_cb_lang']) ?> >
                         Italian
                    </option>
                    
                    <option value="PT" <?php @wp_automatic_opt_selected('PT',$camp_general['cg_cb_lang']) ?> >
                         Portuguese
                    </option>
                    
               </select>
          </div>
          
          
          <div id="field1zz-container" class="field f_100">
               <label for="field1zz">
                    Search order:
               </label>
               <select name="camp_search_order" id="field1zz">
               
                    <option id="field1-1" value="" <?php @wp_automatic_opt_selected('',$camp_search_order) ?> >
                        Keyword Relevance
                    </option>
                    <option id="field1-1" value="GRAVITY" <?php @wp_automatic_opt_selected('GRAVITY',$camp_search_order) ?> >
                         Gravity
                    </option>
                    <option id="field1-2" value="POPULARITY"  <?php @wp_automatic_opt_selected('POPULARITY',$camp_search_order) ?>  >
                         Popularity
                    </option>
                   
                    <option id="field1-3" value="AVERAGE_EARNINGS_PER_SALE"  <?php @wp_automatic_opt_selected('AVERAGE_EARNINGS_PER_SALE',$camp_search_order) ?>  >
                         Average Earning / Sale
                    </option> 
                    
                    
                    <option id="field1-2" value="initial_earnings_per_sale"  <?php @wp_automatic_opt_selected('initial_earnings_per_sale',$camp_search_order) ?>  >
                         Initial earnings per sale
                    </option>
                      
                    <option id="field1-2" value="total_rebill_amt"  <?php @wp_automatic_opt_selected('total_rebill_amt',$camp_search_order) ?>  >
                         Total rebill amount
                    </option> 
                     
                   
               </select>
          </div>
          
          </div>
          
		 <!-- /clickbank part -->
		 		
          
                     

		 
	 
		 
		<!-- post templates -->
		<div id="postTemplates" style="display:none">
		
<div class="tempArticles">[ad_1]
[matched_content]
[ad_2]
<br><a href="[source_link]">Source</a> by <a href="[author_link]">[author_name]</a></div>		

<div class="tempgpt3">[ad_1]
[matched_content]
[ad_2]</div>	

<div class="tempArticlesBase">[ad_1]
[matched_content]
[ad_2]
<br><a href="[source_link]">Source</a> by <a href="[author_link]">[author_name]</a></div>		

<div class="tempWalmart">[item_imgs_html]
Price: <span style="color:#b12704">[price_with_discount]</span>
<br><a href="[product_affiliate_url]"><img data-src="https://i.imgur.com/SUv4PIl.png"></a> 
[ad_1]
[item_description]
<br>[ad_2]</div>		

<!-- FB template -->
<div class="tempFacebook tempFacebookposts tempFacebookfeed tempFacebooktagged">[ad_1]
[matched_content]
[ad_2]
<br><a href="[source_link]">Source</a>  </div>		

<div class="tempFacebookevents">[ad_1]
[matched_content]

Starts at: [start_time]
Ends at: [end_time]

Location:

[place_name]
[place_address]

[place_map]

[ad_2]
<br><a href="[source_link]">Source</a> </div>		


<div class="tempFeeds"> [ad_1]
<br>[matched_content]
<br>[ad_2]
<br><a href="[source_link]">Source link </a></div>

<div class="tempMulti"> [ad_1]
<br>[matched_content]
<br>[ad_2]
<br><a href="[source_link]">Source link </a></div>

<div class="tempBingNews"> [ad_1]
<br>[matched_content]
<br>[ad_2]
<br><a href="[source_link]">Source link </a></div>

<div class="tempGoogleNews"> [ad_1]
<br>[matched_content]
<br>[ad_2]
<br><a href="[source_link]">Source link </a></div>


			<!-- amazon template -->	
			<div class="tempAmazon">[product_imgs_html]
Price: <span style="color:#b12704">[price_with_discount]</span><br><i><small>(as of [price_update_date] - <span class="wp_automatic_amazon_disclaimer" title="Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on [relevant Amazon Site(s), as applicable] at the time of purchase will apply to the purchase of this product.">Details</span>)</small></i><br><br><a href="[product_link]"><img data-src="https://valvepress.s3.amazonaws.com/imgs/buy_now.png"></a> 
[ad_1]
[product_desc]
[product_summary]
<br>[ad_2]</div> 


		<!-- Clickbank template -->
		<div class="tempClickbank"><p style="text-align:center">[product_img]</p>

<p>
<strong>Product Name:</strong> [original_title]
</p>
[ad_1]
<p style="text-align: center; font-size: 150%;"><strong><a href="[product_link]">Click here to get [original_title] at discounted price while it's still available...</a></strong></p>

<p style="text-align: center; ">
<a href="[product_link]"><img style="display:inline" data-src="https://valvepress.s3.amazonaws.com/imgs/order_now.jpeg"></a></p>

<p style="text-align: center; ">
<em>All orders are protected by SSL encryption – the highest industry standard for online security from trusted vendors.<br>
<img data-src="https://valvepress.s3.amazonaws.com/imgs/money_back_gurantee.png"><br>
[original_title] is backed with a 60 Day No Questions Asked Money Back Guarantee. If within the first 60 days of receipt you are not satisfied with Wake Up Lean™, you can request a refund by sending an email to the address given inside the product and we will immediately refund your entire purchase price, with no questions asked.</em></p>

<!--more-->

<p>
<strong>Description:</strong> [product_desc]
</p>

[ad_2] 
 
<p style="text-align: center; font-size: 150%;"><strong><a href="[product_link]">Click here to get [original_title] at discounted price while it's still available...</a></strong></p>

<p style="text-align: center; ">
<a href="[product_link]"><img style="display:inline" data-src="https://valvepress.s3.amazonaws.com/imgs/order_now.jpeg"></a></p>

<p style="text-align: center; ">
<em>All orders are protected by SSL encryption – the highest industry standard for online security from trusted vendors.<br>
<img data-src="https://valvepress.s3.amazonaws.com/imgs/money_back_gurantee.png"><br>
[original_title] is backed with a 60 Day No Questions Asked Money Back Guarantee. If within the first 60 days of receipt you are not satisfied with Wake Up Lean™, you can request a refund by sending an email to the address given inside the product and we will immediately refund your entire purchase price, with no questions asked.</em></p>
</div>			
			
			<!-- Pinterest template -->
			<div class="tempPinterest">[ad_1]
<a href="[pin_url]"><img data-src="[pin_img]" title="[pin_title]" /></a>
<p>[pin_description]</p>
[ad_2]
<br><a href="[pin_url]">Source</a> by <a href="https://pinterest.com/[pin_pinner_username]">[pin_pinner_username]</a>
			
			</div>
			
			<!-- TikTok template -->
			<div class="tempTikTok">[ad_1]
[item_description]
[embed][item_url][/embed]
[ad_2]
<br><a href="[item_url]">Tiktok </a> by <a href="[item_user_link]">[item_user_name]</a>
			</div>
			
		<!-- Spintax template -->
		<div class="tempSpintax"></div>	
		
		<?php 

		$player= "[vid_player]
<br>";
		
		$vmplayer= "[vid_embed]
<br>";
		$dmplayer="[vid_player]
<br>";
		
		 if(  1   ){
			
			if( (defined('PARENT_THEME') &&  (PARENT_THEME =='truemag' || PARENT_THEME =='newstube'))  || class_exists('Cactus_video') ){
				$player ='';
				$vmplayer = '';	
				$dmplayer = '' ;
			}

		 	
		 } 
		 
		 //newspaper integration
		 if(function_exists('td_bbp_change_avatar_size')){
		 	
		 	
		 	
		 	if(! in_array( 'OPT_NO_NEWSPAPER' , $wp_automatic_options ) ){
			 	$player ='';
			 	$vmplayer = '';
		 	}
		 }
		
		?>
		
		<!-- youtube part -->
		<div class="tempYoutube"><?php   echo $player ?>[vid_desc]
<br><a href="[source_link]">source</a></div>

<!-- Rumble.com part -->
<div class="tempRumble"><?php   echo $player ?>[vid_desc]
<br><a href="[source_link]">source</a></div>

<!-- Places part -->
<div class="tempPlaces">[item_photos_html]<br>
<strong>[item_title]</strong><br>
[item_formatted_address]<br>
Rating: [item_rating]<br>
Rated count: [item_user_ratings_total]<br>
[item_map_iframe]<br><br>
<a href="[item_url]">Check on Google Maps</a></div>

<!-- Reddit part -->
<div class="tempReddit">[ad_1]
[item_img_html]
<p>[item_description]</p>
[item_embed]
[ad_2]
<br><a href="[item_link]">View Reddit</a> by [item_author_link] -  <a href="[item_url]">View Source</a></div>

<!-- Telegram part -->
<div class="temptelegram">[ad_1]
[item_img_html]
<p>[item_description]</p>
[item_embed]
[ad_2]
<a href="[item_url]">View Source</a></div>

<!-- Careerjet part -->
<div class="tempCareerjet">[item_logo_html]
<b>Job title:</b> [item_title]
<br><b>Company:</b> [item_company]
<br><b>Job description</b>: [item_description]
<br><b>Expected salary</b>: [item_salary]
<br><b>Location</b>: [item_locations]
<br><b>Job date</b>: [item_date]
<br><a href="[item_url]"><b>Apply for the job now!</b></a>

[ad_2]
</div>

		<!-- Instagram part -->
		<div class="tempInstagram">[ad_1]
<a href="[item_url]">[item_images]</a>
<p>[item_description]</p>
[ad_2]
<br><a href="[item_url]">Source</a></div>
	
	<!-- craigslist part -->
		<div class="tempCraigslist">[item_imgs_html]
<p>[item_price]</p>
<p>[item_hood]</p>
<p>[item_address]</p>
<p>[item_description]</p>
<p>[item_attributes]</p>
<p>[item_map]</p>
<a href="[item_link]">Check more...</a>
</div>

<!-- craigslist part -->
		<div class="tempAliexpress">[item_imgs_html]
Price: <span style="color:#b12704">[price_with_discount]</span>

<a href="[item_affiliate_url]"><img src="https://valvepress.s3.amazonaws.com/imgs/buy_now.png"></a> 

[item_description]</div>
	
	<!-- SoundCloud part -->
	<div class="tempSoundCloud" >[ad_1]
[item_embed]
<br>[item_description]
[ad_2]
<br><a href="[source_link]">Source</a> by <a href="[item_user_link]">[item_user_username]</a></div>

		<!-- vimeo part -->
		<div class="tempVimeo"><?php   echo $vmplayer ?>[vid_description]
<br>Likes: [vid_likes]
<br>Viewed: [vid_views]
<br><a href="[source_link]">source</a></div>


<!-- Twitter template -->
<div class="tempTwitter">[ad_1]
[item_description]
[ad_2]
<br><a href="[source_link]">Source</a> by <a href="[item_author_url]">[item_author_name]</a></div>


		<div class="tempFlicker"><img data-src="[img_src]" alt="[img_title]" />    
<p>[img_description] </p>
<p><a href="[img_link]">Posted</a> by <a href="http://flicker.com/[img_author] " >[img_author_name] </a> on [img_date_posted] </p>
  <p>  Tagged: [img_tags] </p></div>

  
  <div class="tempeBay">[item_images] 
<br> [item_desc]
<br> Price : [price_with_discount]
<br> Ends on : [readable_time][item_end_date][/readable_time]
<br> <a href="[item_link]">View on eBay </a></div>


<!-- Itunes template -->
		<div class="tempItunesmusic"><img data-src="[item_img]">

<p>[embed][item_previewUrl][/embed]</p>
<br>
<p>By <a href="[item_artistViewUrl]">[item_artistName]</a></p>
<br><a href="[item_link]&at=[affiliate_id]">Download now from Itunes</a></div>


<div class="tempItunesmovie"><img data-src="[item_img]">
<p>[video src="[item_previewUrl]"]</p>
<br>
<p>[item_description]</p>
<p>By [item_artistName]</p>
<br><a href="[item_link]&at=[affiliate_id]">Download movie from Itunes</a></div>

<div class="tempItunesshortFilm"><img data-src="[item_img]">
<p>[video src="[item_previewUrl]"]</p>
<br>
<p>[item_description]</p>

<p>By [item_artistName]</p>
<br><a href="[item_link]&at=[affiliate_id]">Download movie from Itunes</a></div>

<div class="tempItunestvShow"><img data-src="[item_img]">
<p>[video src="[item_previewUrl]"]</p>
<br>
<p>[item_description]</p>

<p>By [item_artistName]</p>
<br><a href="[item_link]&at=[affiliate_id]">Download from Itunes</a></div>


<div class="tempItunespodcast"><img data-src="[item_img]">

<p>[item_description]</p>

<p>By [item_artistName]</p>

<br><a href="[item_link]&at=[affiliate_id]">Download from Itunes</a></div>


<div class="tempItunesmusicVideo"><img data-src="[item_img]">

<p>[video src="[item_previewUrl]"]</p>
<br>
<p>[item_description]</p>

<p>By [item_artistName]</p>

<br><a href="[item_link]&at=[affiliate_id]">Download from Itunes</a></div>


<div class="tempItunesaudiobook"><img data-src="[item_img]">

<p>[item_description]</p>

<p>By [item_artistName]</p>

<br><a href="[item_link]&at=[affiliate_id]">Download from Itunes</a></div>

<div class="tempItunesebook">
<img data-src="[item_img]">

<p>[item_description]</p>

<p>By [item_artistName]</p>
<br><a href="[item_link]&at=[affiliate_id]">Download from Itunes</a></div>


<div class="tempItunessoftware">
<img data-src="[item_img]">

<p>[item_description]</p>

[item_screenshot]

<p>By <a href="[item_artistViewUrl]">[item_artistName]</a></p>
<br><a href="[item_link]&at=[affiliate_id]">Download from Itunes</a></div>

<!-- Envato template -->

<div class="tempEnvatophotodune tempEnvatocodecanyon tempEnvatothemeforest tempEnvato3docean tempEnvatophotodune tempEnvatographicriver">[ad_1]

<a class="wp_automatic_demo_btn" target="_blank" href="[live_site_affiliate]">LIVE PREVIEW</a><a class="wp_automatic_buy_btn" target="_blank" href="[item_link_affiliate]">BUY FOR $[item_price]</a>

<img data-src="[preview_img]">
[item_description]
[ad_2]
<a href="[item_link_affiliate]">Source</a></div>
<div class="tempEnvatoaudiojungle">[ad_1]
[embed][preview_mp3][/embed]
<p>
<a class="wp_automatic_buy_btn" target="_blank" href="[item_link_affiliate]">BUY FOR $[item_price]</a>
<img  class="alignleft" data-src="[preview_icon]">[item_description]</p>
[ad_2]
<a href="[item_link_affiliate]">Source</a></div>
<div class="tempEnvatovideohive">[ad_1]
<img data-src="[preview_img]">
[embed][preview_vid][/embed]
[item_description]
[ad_2]
<a href="[item_link_affiliate]">Source</a></div>

<!-- DailyMotion template -->

<div class="tempDailyMotion"><?php   echo $dmplayer ?>[item_description]
<br><a href="[source_link]">View at DailyMotion</a></div>

<div class="tempSingle">[matched_content]</div>


</div><!-- hidden part -->
 		<!-- Check Boxes -->
              <script type="text/javascript">

              		var pluginDir = '<?php   echo plugins_url('',__FILE__)?>';
					var supportedTags= <?php   echo json_encode($allowed_tags) ;?> ;
              		var $vals = '<?php   echo  $camp_options ?>';

              	
                    					


               </script>
               
               <div class="clear"></div>
               </div>             
             

          <!-- third tab -->
	 
          <input name="posted" type="hidden" value="<?php if( isset($posted))   echo @$posted ?>">
          
          <script type="text/javascript">
          	
          jQuery(document).ready(function(){
        	  jQuery(document).ready(function(){
        		  jQuery("#search").gcomplete({
				style: "default",
				effect: false,
				pan: '#field111'
				});
			});
 
		});
	</script>
	 
          <div class="clear"></div>
           
     </div>
</div>

</div><!-- panes -->