<?php
/*
 Plugin Name: WordPress Automatic Plugin
 Plugin URI: https://1.envato.market/rqbgD
 Description: WordPress Automatic posts quality articles, Amazon products, Clickbank products, Youtube videos, eBay items, Flicker images, RSS feeds posts on auto-pilot and much more.
 Version: 3.107.0
 Author: ValvePress
 Author URI: http://codecanyon.net/user/ValvePress/portfolio?ref=ValvePress
 */

/*  Copyright 2012-2024  Wordpress Automatic Plugin  (email : sweetheatmn@gmail.com) */

//constant for the plugin version
define('WPAUTOMATIC_VERSION', '1');

update_option( 'wp_automatic_license' , '4308eedb-1add-43a9-bbba-6f5d5aa6b8ee' );
update_option('wp_automatic_license_active','active');
update_option('wp_automatic_license_active_date',time());
update_option ( 'alb_license_active', '1' );
update_option ( 'alb_license_last', 01-01-2030);
update_option('wp_automatic_envato_token','4308eedb-1add-43a9-bbba-6f5d5aa6b8ee');

global $wpAutomaticTemp; //temp var used for displaying columns of campsigns 
global $wpAutomaticDemo;
$wpAutomaticDemo = false; 
  
//set demo mode
if (isset($_SERVER['HTTP_HOST']) && stristr($_SERVER['HTTP_HOST'], 'valvepress.com')){
	define('WPAUTOMATIC_DEMO', true);
	$wpAutomaticDemo = true;

}
else
{
	define('WPAUTOMATIC_DEMO', false);
}

//function wordpress automatic trim to trim a string, accepts a string or a null and if null, it returns an empty string and if a string, it trim it using trim function
//for PHP 8.2 compatibility
function wp_automatic_trim($str)
{
	if (is_null($str)) {
		return '';
	} else {
		return trim($str);
	}
}

//function wordpress automatic str replace that is a wrapper for str_replace function, accepts a string or null for the search and replace and if null it converts it to a string before passing it to str_replace
function wp_automatic_str_replace($search, $replace, $subject)
{
	if (is_null($search)) {
		$search = '';
	}

	if (is_null($replace)) {
		$replace = '';
	}

	if (is_null($subject)) {
		$subject = '';
	}

	return str_replace($search, $replace, $subject);
}

//function wordpress automatic wp_automatic_htmlentities that accepts null or string and if null, it returns an empty string and if a string, it converts it to html entities using wp_automatic_htmlentities function
function wp_automatic_htmlentities($str)
{
	if (is_null($str)) {
		return '';
	} else {
		return htmlentities($str);
	}
}

//function wordpress automatic htmlspecialchars that accepts null or string and if null, it returns an empty string and if a string, it converts it to html entities using htmlspecialchars function
function wp_automatic_htmlspecialchars($str)
{
	if (is_null($str)) {
		return '';
	} else {
		return htmlspecialchars($str);
	}
}

//function wordpress automatic htmlspecialchars_decode that accepts null or string and if null, it returns an empty string and if a string, it converts it to html entities using htmlspecialchars_decode function
function wp_automatic_htmlspecialchars_decode($str)
{
	if (is_null($str)) {
		return '';
	} else {
		return htmlspecialchars_decode($str);
	}
}

$licenseactive=get_option('wp_automatic_license_active','');
if(wp_automatic_trim($licenseactive) != ''){
	
 
	//fire checks
	require_once  plugin_dir_path(__FILE__) . 'plugin-updates/plugin-update-checker.php';
	$wp_automatic_UpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://deandev.com/upgrades/meta/wp-automatic.json',
			__FILE__,
			'wp-automatic'
			);
	
	//append keys to the download url
	$wp_automatic_UpdateChecker->addResultFilter('wp_automatic_addResultFilter');
	function wp_automatic_addResultFilter($info){
		
		$wp_automatic_license = get_option('wp_automatic_license','');
		
		if(isset($info->download_url)){
			$info->download_url = $info->download_url . '&key='.$wp_automatic_license;
		}
		return $info;
	}
}

// amazon
require_once ( dirname(__FILE__) . '/inc/amazon_api_class.php');

//require p_admin_notices.php to display notices, to add any notice to display on admin pages use add_settings_error function
require_once( dirname(__FILE__) .  '/p_admin_notices.php');

//bulk action Export add for exporting campaigns and handling the export action by reading selected campaigns, converting them to json and returning a json file for download
//added import button is added throw the file js/wp_automatic_script_campaigns_page.js enqueued by file p_scripts.php 
require_once ( dirname(__FILE__) . '/p_bulk_action_add_export.php');

//require p_upload_handler to handle import uploaded file once uploaded, it will process the file, get its content and import the campaigns to the database 
require_once( dirname(__FILE__) .  '/p_upload_handler.php');




/*  
 * Stylesheets & JS loading
 */
require_once 'p_scripts.php';

/*
 * Creating a Custom Post Type
 */
	require_once 'post_type.php';

/*
 * Creating the admin menu
 */
	require_once 'p_menu.php';

/*
 * Settings
 */	
	require_once 'p_options.php';

/*
 * Log
 */	
	require_once 'p_log.php';
	

/*
 * Plugin functions
 */

	require_once 'p_functions.php';
	
	/*
	 * ajax handling
	 */
	require_once 'pajax.php';
	
/*
 * ads adding
 */
require_once 'p_ads.php';

/*
 * Meta Box
 */
require_once('p_meta.php');
require_once('metabox_time.php');

/*
 * Cron Schedule
 */
require_once 'automatic_schedule.php';

/*
 * clear feed cache 
 */

add_filter( 'wp_feed_cache_transient_lifetime', 'wp_automatic_feed_lifetime');

function wp_automatic_feed_lifetime( $a  ){
	 return 0 ; 
}

if(! function_exists('do_not_cache_feeds')){
	function do_not_cache_feeds(&$feed) {
		$feed->enable_cache(false);
	}
}
add_action( 'wp_feed_options', 'do_not_cache_feeds' );

/*
 * Filter the content to remove first image if active
 */
require_once 'p_content_filter.php';

/*
 * tables
 */
global $wpdb;
$mysqlVersion = $wpdb->db_version();

if( version_compare($mysqlVersion, '5.5.3') > 0 ){
	register_activation_hook( __FILE__, 'create_table_all' );
	require_once 'p_tables.php';
}

//removes quick edit from custom post type list

/**
 * custom request for cron job
 */
function wp_automatic_parse_request($wp) {

	//secret word 
	$wp_automatic_secret = wp_automatic_trim(get_option('wp_automatic_cron_secret'));
	if(wp_automatic_trim($wp_automatic_secret) == '') $wp_automatic_secret = 'cron';
	
	// only process requests with "my-plugin=ajax-handler"
	if (array_key_exists('wp_automatic', $wp->query_vars)) {
			
		if($wp->query_vars['wp_automatic'] == $wp_automatic_secret){
			require_once(dirname(__FILE__) . '/cron.php');
			exit;

		}elseif ($wp->query_vars['wp_automatic'] == 'download'){
			require_once 'downloader.php';
			exit;
		}elseif ($wp->query_vars['wp_automatic'] == 'test'){
			
			//require test.php from the same directory
			require_once dirname(__FILE__) . '/test.php';
 			exit;
		}elseif($wp->query_vars['wp_automatic'] == 'show_ip'){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT,20);
			curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
			
			//curl get
			$x='error';
			$url='https://www.showmyip.com/';
			
			curl_setopt($ch, CURLOPT_HTTPGET, 0);
			curl_setopt($ch, CURLOPT_URL, wp_automatic_trim($url));
			
			$exec=curl_exec($ch);
			$x=curl_error($ch);
			
			//<h2 id="ipv4">41.176.183.83</h2>
			if(strpos($exec,'<h2 id="ipv4">')){
				preg_match('{<h2 id="ipv4">(.*?)</h2>}', $exec , $ip_matches);
				print_r($ip_matches[1]);
			}else{
			
				echo $exec.$x;
			}
			exit;
		 
		
		}
	}
}
add_action('parse_request', 'wp_automatic_parse_request');

//custom cron url for wordpress automatic based on wp-cron.php?doing_wp_cron=1&wp_automatic=secret
//this URL when called, only process the wp_automatic cron job and ignore all other cron jobs
function wp_automatic_filter_cron_jobs($cron_jobs) {

	 //if empty $_GET['wp_automatic'] then return null
	 if(! isset($_GET['wp_automatic'])) return $cron_jobs;

	 //get secret word and compare 
	 $wp_automatic_secret = wp_automatic_trim(get_option('wp_automatic_cron_secret'));

	 //compare 
	 if( wp_automatic_trim($_GET['wp_automatic']) != $wp_automatic_secret) return $cron_jobs;
	
	 //get all crons
	$crons    = _get_cron_array();
	 
	
	$gmt_time = microtime( true );
	echo '<br>GMT time: ' . $gmt_time;
	$results  = array();

	foreach ( $crons as $timestamp => $cronhooks ) {
		if ( $timestamp > $gmt_time ) {
			break;
		}

		$results[ $timestamp ] = $cronhooks;
	}

	//look results and check the value array for wp_automatic
	$new_results = array(); // contains only the wp_automatic cron job
	foreach($results as $timestamp => $cronhooks){
		
		foreach($cronhooks as $hook => $hook_data){
			
			if($hook == 'wp_automatic_hook'){
				
				 //loop cronhooks array and unset all other cron jobs
				 foreach($cronhooks as $hook => $hook_data){
				 	
				 	if($hook != 'wp_automatic_hook'){
				 		unset($cronhooks[$hook]);
				 	}
				 	
				 }
				
				$new_results [$timestamp] = $cronhooks;
			}
			
		}
		
	}

	return $new_results;

}

//skipped this filter to use the POST command instead
//add_filter('pre_get_ready_cron_jobs', 'wp_automatic_filter_cron_jobs');




function wp_automatic_query_vars($vars) {
	$vars[] = 'wp_automatic';
	return $vars;
}
add_filter('query_vars', 'wp_automatic_query_vars');

/*
 * support widget
 */

if( ! $wpAutomaticDemo)
require_once 'widget.php';


/*
 * rating notice
 */
require_once 'p_rating.php';
require_once 'p_license.php';

/*
 * update notice
 */
require_once 'updated.php';

/*
 * admin edit
 */
require_once 'wp-automatic-admin-edit.php';

/*
 *amazon product price update
 */
require_once 'wp-automatic-amazon-prices.php';

//sorting function by length
function wp_automatic_sort($a,$b){
	return strlen($b)-strlen($a);
}

//stripslashes with array support
function wp_automatic_stripslashes($toStrip){
	if(is_array($toStrip)){
		
		return array_map('wp_automatic_stripslashes',$toStrip);
		
	}else{
		return stripslashes($toStrip);
	}
}

//rotate and get a single item
function wp_automatic_single_item($option_name){
	
	$option_value = get_option($option_name);
	
	//empty value
	if(wp_automatic_trim($option_value) == '') return $option_value;
	
	$multiple_items = array_filter( explode("\n",  $option_value ) );
	
	 
	//single value
	if(count($multiple_items) == 1) return wp_automatic_trim($multiple_items[0]);
	
	//multiple items, return first and send it to the last 
	if(count($multiple_items) > 0 ){
		
		$first_item = $multiple_items[0];
		
		unset($multiple_items[0]);
		
		$multiple_items[] = $first_item;
		
		 update_option($option_name , implode("\n" ,$multiple_items ));
		
		 return wp_automatic_trim($first_item);
		
		
	}
	
	return wp_automatic_trim($option_value);
	
}

//fix relative link global
function wp_automatic_fix_relative_link ($found_link,$host,$http_prefix,$the_path,$base_url = ''){
	
	if (! stristr ( $found_link, 'http' )) {
		
		if (stristr ( $found_link, '//' )) {
			$found_link = $http_prefix . $found_link;
		} else {
			
			
			if( preg_match ( '{^/}' ,  $found_link ) ){
				
				$found_link = $host  . $found_link;
				
			}else{
				
				if(wp_automatic_trim($base_url) != ''){
					$found_link = $base_url   . $found_link;
				}else{
					
					if(wp_automatic_trim($the_path) != ''){
						$found_link = $host . $the_path .  '/' . $found_link;
					}else{
						$found_link = $host . '/' . $found_link;
					}
					
				}
			}
			
			
			
		}
	}
	
	return $found_link;
	
}

/**
 * Fix relative paths used in multi-page scraper before extracting links
 * @param string $content the content of the items list page containging the links
 * @param String $url the source URL of the items list page
 * @return mixed the content after fixingig realtive links
 */
function wp_automatic_fix_relative_paths($content, $url) {
 

	// URL params, http, domain and path 
	$pars = parse_url ( $url );
	$host = $pars ['host'];
	$scheme = $pars ['scheme'];

	if ($scheme != 'https')
		$scheme = 'http';
		
		// $url with last slash, remove ending slashes 
		$path = $pars ['path'];
		$path_parts = explode ( '/', $path );
		array_pop ( $path_parts );
		
		
		$url_with_last_slash = $scheme . '://' . $host . implode ( '/', $path_parts );
		
		//base url
		preg_match ( '{<base href="(.*?)"}', $content, $base_matches );
		
		//remove trailing slash from base URL if found
		if(isset ( $base_matches [1] ) && wp_automatic_trim( $base_matches [1] ) != '') $base_matches [1] = preg_replace('!/$!', '',$base_matches [1]);
		
		//real base url from the source 
		if(isset ( $base_matches [1] ) && wp_automatic_trim( $base_matches [1] ) != ''  ){
			$base_for_reltoabs_fn = $base_matches [1];
		}else{
			$base_for_reltoabs_fn = $url;
		}
		
		$base_url = (isset ( $base_matches [1] ) && wp_automatic_trim( $base_matches [1] ) != '') ? wp_automatic_trim( $base_matches [1] ) : $url_with_last_slash;
		 
		
		/* preg_match_all('{<img.*?src[\s]*=[\s]*["|\'](.*?)["|\'].*?>}is', $res['cont'] , $matches); */
		
		$content =wp_automatic_str_replace( 'src="//', 'src="' . $scheme . '://', $content );
		$content =wp_automatic_str_replace( 'href="//', 'href="' . $scheme . '://', $content );
		
		preg_match_all ( '{(?:href|src)[\s]*=[\s]*["|\'](.*?)["|\'].*?>}is', $content, $matches );
		$img_srcs = ($matches [1]);
		$img_srcs = array_filter ( $img_srcs );
		
		foreach ( $img_srcs as $img_src ) {
			
			$original_src = $img_src;
			 
			if (stristr ( $img_src, 'http:' ) || stristr ( $img_src, 'www.' ) || stristr ( $img_src, 'https:' ) || stristr ( $img_src, 'data:' ) || stristr ( $img_src, '#' ) ) {
				// valid image
			} else {
				// not valid image i.e relative path starting with a / or not or //
				$img_src = wp_automatic_trim( $img_src );
				
				if (preg_match ( '{^//}', $img_src )) {
					$img_src = $scheme . ':' . $img_src;
				} elseif (preg_match ( '{^/}', $img_src )) {
					$img_src = $scheme . '://' . $host . $img_src;
				} else {
					
					if( stristr($img_src , '../') ){
						$img_src = wp_automatic_rel2abs($img_src, $base_for_reltoabs_fn);
					}else{
						$img_src = $base_url . '/' . $img_src;
					}
				}
				
				
				$reg_img = '{["|\'][\s]*' . preg_quote ( $original_src, '{' ) . '[\s]*["|\']}s';
				$content = preg_replace ( $reg_img, '"' . $img_src . '"', $content );
			}
		}
		
	  
		// Fix Srcset
		preg_match_all ( '{srcset[\s]*=[\s]*["|\'](.*?)["|\']}s', $content, $srcset_matches );
		
		$srcset_matches_raw = $srcset_matches [0];
		$srcset_matches_inner = $srcset_matches [1];
		
		$i = 0;
		foreach ( $srcset_matches_raw as $srcset ) {
			
			if (stristr ( $srcset, 'http:' ) || stristr ( $srcset, 'https:' ) || stristr($srcset,  'data:image' ) ) {
				// valid
			} else {
				
				// lets fix
				$correct_srcset = $srcset_inner = $srcset_matches_inner [$i];
				
				$srcset_inner_parts = explode ( ',', $srcset_inner );
				
				foreach ( $srcset_inner_parts as $srcset_row ) {
					
					$srcset_row_parts = explode ( ' ', wp_automatic_trim( $srcset_row ) );
					$img_src_raw = $img_src = $srcset_row_parts [0];
					
					if (preg_match ( '{^//}', $img_src )) {
						$img_src = $scheme . ':' . $img_src;
					} elseif (preg_match ( '{^/}', $img_src )) {
						$img_src = $scheme . '://' . $host . $img_src;
					} else {
						$img_src = $scheme . '://' . $host . '/' . $img_src;
					}
					
					$srcset_row_correct =wp_automatic_str_replace( $img_src_raw, $img_src, $srcset_row );
					$correct_srcset =wp_automatic_str_replace( $srcset_row, $srcset_row_correct, $correct_srcset );
				}
				
				$content =wp_automatic_str_replace( $srcset_inner, $correct_srcset, $content );
			}
			
			$i ++;
		}
		
		
		
		// Fix relative links
		$content =wp_automatic_str_replace( 'href="../', 'href="http://' . $host . '/', $content );
		$content = preg_replace ( '{href="/(\w)}', 'href="http://' . $host . '/$1', $content );
		$content = preg_replace ( '{href=/(\w)}', 'href=http://' . $host . '/$1', $content ); // <a href=/story/sports/college/miss
		
		
		
		return $content;
}

//ebay backward compatiblility ebay site number to region
function wp_automatic_fix_category($category){
	
	if (is_numeric($category) && $category > 0){
		
		switch ($category) {
			case 1:
			return 'EBAY-US';
			break;

			case 2:
				return 'EBAY-IE';
				break;
				
			case 3:
				return 'EBAY-AT';
				break;
				
			case 4:
				return 'EBAY-AU';
				break;
				
				
			case 5:
				return 'EBAY-FRBE';
				break;
				
				
			case 7:
				return 'EBAY-ENCA';
				break;
				
			case 10:
				return 'EBAY-FR';
				break;
				
			case 11:
				return 'EBAY-DE';
				break;
				
			case 12:
				return 'EBAY-IT';
				break;
				
			case 13:
				return 'EBAY-ES';
				break;
				
			case 14:
				return 'EBAY-CH';
				break;
				
			case 15:
				return 'EBAY-GB';
				break;
				
			case 16:
				return 'EBAY-NL';
				break;
				 
			default:
				return 'EBAY-US';
			break;
		}
		
	}else{
		return $category;
	}
	
}

//check if the 'microsoft', 'xbox-scarlett'   fix json and remove slashes
function wp_automatic_fix_json_and_slashes($part){
	 
	if(preg_match ("!^'.*?'$!" , $part)){
		echo '<br>Fixing json part and removing slashes....';
		$part = wp_automatic_fix_json_part($part);
		 	
		if(wp_automatic_trim($part ) != ''){
			
			//remove slashes 
			$part_parts = explode(',', $part);
			 
			foreach($part_parts as $key => $part_part){
				
				$part_part = wp_automatic_trim($part_part);
				 
				if(preg_match ("!^'.*?'$!" , $part_part)){
					$part_part = preg_replace( "!^'(.*?)'$!" , "$1" ,  $part_part  );
					
					$part_parts[$key] = $part_part;
					
				}
				
			}
			
			if(is_array($part_parts) && count($part_parts) > 0){
				$part = implode(',' , $part_parts);
			}
			
		}
		
	}
	 
	return $part;
	
}

//convert extracted part from json to regular text 
function wp_automatic_fix_json_part($json_part){
  
	$json_string = '["' . $json_part . '"]';

	 $json = json_decode($json_string) ;
	 return $json[0];
	
}

//convert a relative url to a complete 
function wp_automatic_rel2abs($rel, $base)
{
	 
	/* return if already absolute URL */
	if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
	
	/* queries and anchors */
	if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;
	
	/* parse base URL and convert to local variables:
	 $scheme, $host, $path */
	extract(parse_url($base));
	
	/* remove non-directory element from path */
	$path = preg_replace('#/[^/]*$#', '', $path);
	
	/* destroy path if relative url points to root */
	if ($rel[0] == '/') $path = '';
	
	/* dirty absolute URL */
	$abs = "$host$path/$rel";
	
	/* replace '//' or '/./' or '/foo/../' with '/' */
	$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
	for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}
	
	/* absolute URL is ready! */
	return $scheme.'://'.$abs;
}

/**
 * a new log record from outside the core 
 * @param unknown $type
 * @param unknown $data
 */
function wp_automatic_log_new($type, $data){
	
	global $wpdb;
	$now = date ( 'Y-m-d H:i:s' );
	$data = @addslashes ( $data );
	
	$query = "INSERT INTO {$wpdb->prefix}automatic_log (action,date,data) values('$type','$now','$data')";
	$wpdb->query ( $query );
	
}

function remove_taxonomies_metaboxes() {
	remove_meta_box( 'td_post_theme_settings_metabox', 'wp_automatic', 'normal' );
}
add_action( 'add_meta_boxes_wp_automatic' , 'remove_taxonomies_metaboxes' ,50);

/**
 * Add a custom product attribute.
 * @param int $product_id
 * @param string $attribute_name
 * @param string $attribute_value
 */
function wp_automatic_add_product_attribute( $product_id, $attribute_name, $attribute_value ) {
    $product_attributes = get_post_meta( $product_id, '_product_attributes', true );
    if ( ! is_array( $product_attributes ) ) {
        $product_attributes = array();
    }

    $product_attributes[$attribute_name] = array(
        'name'         => $attribute_name,
        'value'        => $attribute_value,
        'is_visible'   => 1,
        'is_variation' => 1,
        'is_taxonomy'  => 0,
    );

    update_post_meta( $product_id, '_product_attributes', $product_attributes );
}

 

function wp_automatic_add_product_attribute_new($product_id, $attribute_name, $attribute_value) {
    // Get the product object
    $product = wc_get_product($product_id);

    // Get the attribute ID from the attribute name
    $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_name);
 
    // Set the attribute value
    if ($attribute_id) {
        // Set an existing attribute
        $product_attributes = $product->get_attributes();
        $attribute_slug = 'pa_' . $attribute_name;
        if (isset($product_attributes[$attribute_slug])) {
            $product_attributes[$attribute_slug]->set_options(array($attribute_value));
            $product->set_attributes($product_attributes);
        } else {
            // If the attribute doesn't exist, create a new one
            $product->set_attribute($attribute_name, $attribute_value);
        }
    } else {
        // Add a new custom attribute
        $product->update_meta_data($attribute_name, $attribute_value);
    }

    // Save the product object
    $product->save();
}


/**
 * Function to search posts with a specific title and post type
 * @param string $title
 * @param string $post_type
 * @return boolean true|false
 */
function wp_automatic_get_page_by_title ( $title, $output ='OBJECT', $post_type ='post' ){

	$query = new WP_Query(
		array(
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		)
	);
	 
	if ( ! empty( $query->post ) ) {
		return true;
	} else {
		return false;
	}

}

//stop wp_postmeta query on all posts on edit.php page when loading all campaigns
//causes a fatal memory error fix ticket:23361
function exclude_post_meta_cache($pre, $post_id) {
    // Check if we are on the edit.php page for the 'wp_automatic' post type
    if (is_admin() && isset($_GET['post_type']) && $_GET['post_type'] === 'wp_automatic') {
        return false; // Bypass the post meta cache update
    }
    return $pre; // Continue with the default behavior
}

add_filter('update_post_metadata_cache', 'exclude_post_meta_cache', 10, 4);

//custom mb_convert_encoding
//mb_convert_encoding($string, 'HTML-ENTITIES', 'utf-8');
//reference https://stackoverflow.com/questions/11974008/alternative-to-mb-convert-encoding-with-html-entities-charset
function wp_automatic_mb_convert_encoding($string) {
	
	return mb_encode_numericentity(
        htmlspecialchars_decode(
            htmlentities($string, ENT_NOQUOTES, 'UTF-8', false)
            ,ENT_NOQUOTES
        ), [0x80, 0x10FFFF, 0, ~0],
        'UTF-8'
    );
}


?>