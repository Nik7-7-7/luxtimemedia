<?php
/**
 * Convert array of objects into array of array
 * @param mixed (array or object)
 * @return array
 */
if (!function_exists('indeed_convert_to_array')):
function indeed_convert_to_array($input=null){
	$array = array();
	foreach ($input as $object){
		$array[] = (array)$object;
	}
	return $array;
}
endif;


/**
 * @param mixed
 * @return string
 */
if (!function_exists('indeed_debug_var')):
function indeed_debug_var($variable=null){
	 if (is_array($variable) || is_object($variable)){
		 echo esc_ulp_content('<pre>');
		 print_r($variable);
		 echo esc_ulp_content('</pre>');
	 } else {
	 	var_dump($variable);
	 }
}
endif;


/**
 * @param array
 * @param mixed
 * @return none
 */
if (!function_exists('array_set_pointer')):
function array_set_pointer(&$array, $key){
    reset($array);
    while ($val=key($array)){
        if ($val==$key){
					 break;
				}
        next($array);
    }
}
endif;


/**
 * @param string
 * @param array
 * @return string
 */
if (!function_exists('ulp_add_query_args')):
function ulp_add_query_args($base_url='', $args=array()){
	$permalink_type = get_option('permalink_structure');
	if ($permalink_type){
		foreach ($args as $k=>$v){
			$array[] = $k;
			$array[] = $v;
		}
		if (substr($base_url, -1)!='/'){
			$base_url .= '/';
		}
		$url = $base_url . implode("/", $array) . '/';
		return $url;
	} else {
		return add_query_arg($args, $base_url);
	}
}
endif;


/**
 * @param string
 * @return int
 */
if (!function_exists('ulp_get_post_id_from_url')):
function ulp_get_post_id_from_url($url=''){
	$post_id = url_to_postid($url);
	if (empty($post_id)){
		$all_post_types = DbUlp::getAllPostTypes();
		if (count($all_post_types)){
			foreach ($all_post_types as $cpt){
				if (!empty($_GET[$cpt])){
					$post_type = $cpt;
					$post_name = sanitize_textarea_field($_GET[$cpt]);
					break;
				}
			}
		}
		if (!empty($post_type) && $post_name){
			$post_id = DbUlp::getPostIdByTypeAndName($post_type, $post_name);
		}
	}
	return $post_id;
}
endif;

/**
 * @param none
 * @return array
 */
if (!function_exists('ulp_get_time_types')):
function ulp_get_time_types(){
	return array(
					'm' => esc_html__('Minutes', 'ulp'),
					'h' => esc_html__('Hours', 'ulp'),
					'd' => esc_html__('Days', 'ulp'),
					'w' => esc_html__('Weeks', 'ulp')
	);
}
endif;

/**
 * @param int (time value)
 * @param string (type of time minutes, hours, weeks)
 * @return int (time in seconds)
 */
if (!function_exists('ulp_get_seconds_by_time_value_and_type')):
function ulp_get_seconds_by_time_value_and_type($value=0, $type=''){
	if ( $value=='' ){
			return 0;
	}
	$multiply = 1;
	switch ($type){
		case 'm':
			$multiply = 60;
			break;
		case 'h':
			$multiply = 60 * 60;
			break;
		case 'd':
			$multiply = 60 * 60 * 24;
			break;
		case 'w':
			$multiply = 60 * 60 * 24 * 7;
			break;
	}
	return $value * $multiply;
}
endif;

/**
 * @param none
 * @return int
 */
if (!function_exists('ulp_get_current_user')):
function ulp_get_current_user(){
	global $current_user;
	return isset($current_user->ID) ? $current_user->ID : 0;
}
endif;

/**
 * @param
 * @return string
 */
if (!function_exists('ulpSetCookieViaJS')):
function ulpSetCookieViaJS( $name='', $value='', $time='' ){
	wp_enqueue_script( 'ulp-cookies', ULP_URL . 'assets/js/cookies.js', array('jquery'), 3.6, false );
	return "<span class='ulp-js-cookie-data' data-cookie_name='$name' data-cookie_value='$value' data-cookie_time='$time'></span>";
}
endif;

/**
 * put the value of each element as key
 * @param array
 * @return array
 */
if (!function_exists('ulp_array_value_become_key')):
function ulp_array_value_become_key($input=array()){
	if ($input){
		foreach ($input as $k=>$v){
			$output[$v] = $v;
		}
		return $output;
	}
	return $input;
}
endif;

/**
 * @param array
 * @param Int
 * @return none
 */
if (!function_exists('ulp_array_set_pointer')):
function ulp_array_set_pointer(&$array, $key){
    reset($array);
    while (($temp_key=key($array))>-1){
        if ($temp_key==$key){
					  break;
				}
				next($array);
    }
}
endif;

/**
 * @param array
 * @return array
 */
if (!function_exists('ulp_shuffle_assoc')):
function ulp_shuffle_assoc($list=array()) {
	  if (!is_array($list)){
			 return $list;
		}
	  $keys = array_keys($list);
	  shuffle($keys);
	  $random = array();
	  foreach ($keys as $key) {
	    $random[$key] = $list[$key];
	  }
	  return $random;
}
endif;

/**
 * @param array
 * @param string
 * @return array
 */
if (!function_exists('ulp_array_value_of_child_become_value')):
function ulp_array_value_of_child_become_value($array=array(), $child_key=''){
		$return = $array;
		if (is_array($array)){
				$return = array();
				foreach ($array as $key=>$value){
						$return[$key] = $value[$child_key];
				}
		}
		return $return;
}
endif;

/**
 * @param string
 * @return float
 */
if (!function_exists('indeed_get_plugin_version')):
function indeed_get_plugin_version($base_file_path=''){
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugin_data = get_plugin_data( $base_file_path, false, false);
		return $plugin_data['Version'];
}
endif;

if (!function_exists('ulp_print_date_like_wp')):
function ulp_print_date_like_wp($date='', $print_time=TRUE){
	/*
	 * @param string
	 * @return string
	 */
	if ($date && $date!='-' && is_string($date) && isset($date)){
		$date = strtotime($date);
		$format = get_option('date_format');
		$return_date = date_i18n($format, $date);
		$time = '';
		if ($print_time){
				$time_format = get_option('time_format');
				$time = date_i18n($time_format, $date);
				if ($time){
					$time = ' ' . $time;
				}
		}
		return $return_date . $time;
	}
	return $date;
}
endif;

/**
 * @param none
 * @return object
 */
if (!function_exists('indeed_get_all_pages')):
function indeed_get_all_pages(){
	global $wpdb;
	$q = "SELECT post_title, ID FROM {$wpdb->posts} WHERE post_status='publish' AND post_type='page' ORDER BY post_title ASC;";
	$data = $wpdb->get_results($q);
	return $data;
}
endif;


/**
 * @param string
 * @param array
 * @param string
 */
if (!function_exists('ulp_get_elem_from_array')):
function ulp_get_elem_from_array($direction='', $array=array(), $value=''){
		$key = array_search($value, $array);
		if ($key!==FALSE){
				if ($direction=='prev'){
						$next_key = $key - 1;
				} else {
						$next_key = $key + 1;
				}
				if (isset($array[$next_key])){
						return $array[$next_key];
				}
		}
		return FALSE;
}
endif;


if (!function_exists('ulp_replace_constants')):
function ulp_replace_constants( $string='', $uid=0, $course_id=0, $dynamic_data=array() ){
		/// first we replace the dynamic data passed as arg

		if (!empty($dynamic_data)){
			foreach ($dynamic_data as $k=>$v){
				$string = str_replace($k, $v, $string);
			}
		}
		if (!empty($dynamic_data['{user_id}'])){
				$uid = $dynamic_data['{user_id}'];
		}

		/// extract constants
		preg_match_all("/{([^}]*)}/", $string, $results);
		if (isset($results[1])){
					foreach ($results[1] as $constant){
						$replace = '';
						switch ($constant){
							case 'user_id':
							case 'uid':
								$replace = $uid;
								break;
							case 'course_id':
								$replace = $course_id;
								break;
							case 'username':
								$replace = DbUlp::getUsernameByUID($uid);
								break;
							case 'user_login':
							case 'user_email':
							case 'user_url':
							case 'user_nicename':
							case 'display_name':
								$replace = DbUlp::get_user_col_value($uid, $constant); /// uid, col_name
								break;

							case 'user_registered':
								$replace = DbUlp::get_user_col_value($uid, $constant); /// uid, col_name
								$replace = ulp_print_date_like_wp($replace, FALSE);
								break;
							case 'first_name':
								$replace = get_user_meta($uid, 'first_name', true);
								break;
							case 'last_name':
								$replace = get_user_meta($uid, 'last_name', true);
								break;
							case 'course_name':
								$replace = DbUlp::getPostTitleByPostId($course_id);
								break;
							case 'course_expire_date':

								break;
							case 'blogname':
								$replace = get_option("blogname");
								break;
							case 'blogurl':
							case 'site_url':
								$replace = get_option("home");
								break;
							case 'current_date':
								$replace = ulp_print_date_like_wp(date('Y-m-d H:i:s'));
								break;
							case 'currency':
								$replace = ulp_currency();
								break;
							default:
								if (strpos($constant, 'CUSTOM_FIELD_')!==FALSE){
									$search_key = str_replace("CUSTOM_FIELD_", "", $constant);
									$replace = get_user_meta($uid, $search_key, TRUE);
									if (is_array($replace)){
										$replace = implode(',', $replace);
									}
								} else {
									///search data into wp_usermeta
									$replace = get_user_meta($uid, $constant, TRUE);
									if (is_array($replace)){
										$replace = implode(',', $replace);
									}
								}
								break;
						} /// end of switch
						$string = str_replace("{" . $constant . "}", $replace, $string);
					} ///end of foreach
				}
			return $string;
}
endif;

/**
 * @param string
 * @return string
 */
if (!function_exists('indeed_format_str_like_wp')):
function indeed_format_str_like_wp( $str='' )
{
		$str = wpautop( $str );
		return $str;
}
endif;


if (!function_exists('ulp_currency')):
function ulp_currency(){
	$settings = DbUlp::getOptionMetaGroup('payment_settings');
	if (!empty($settings['ulp_custom_currency_code'])){
		 return $settings['ulp_custom_currency_code'];
	}
	return $settings['ulp_currency'];
}
endif;

/**
 * @param float
 * @param string
 * @return string
 */
if (!function_exists('ulp_format_price')):
function ulp_format_price($price_value=''){
		 $output = '';
		 $settings = DbUlp::getOptionMetaGroup('payment_settings');
		 $currency = $settings['ulp_currency'];
		 $price_value = number_format($price_value, $settings['ulp_num_of_decimals'], $settings['ulp_decimals_separator'], $settings['ulp_thousands_separator']);
		 if (!empty($settings['ulp_custom_currency_code'])){
		 		$currency = $settings['ulp_custom_currency_code'];
		 }
		 $rl = get_option('ulp_currency_position');
		 if ($rl=='left'){
		 	$output = $currency . $price_value;
		 } else {
		 	$output = $price_value . $currency;
		 }
		 return $output;
}
endif;

# dump and die
if (!function_exists('dd')):
function dd($variable){
		indeed_debug_var($variable);
		die;
}
endif;

if (!function_exists('ulp_convert_order_status_to_readable_str')):
function ulp_convert_order_status_to_readable_str($input=''){
		switch ($input){
				case 'ulp_fail':
					return esc_html__('Faild', 'ulp');
					break;
				case 'ulp_complete':
					return esc_html__('Completed', 'ulp');
					break;
				case 'ulp_pending':
					return esc_html__('Pending', 'ulp');
					break;
		}
		return esc_html__('Unknown', 'ulp');
}
endif;

if (!function_exists('indeed_explode_with_trim')):
function indeed_explode_with_trim($delimiter=',', $data = ''){
		$array = explode($delimiter, $data);
		if (is_array($array)){
				foreach ($array as $key=>$value){
						$array [$key] = trim($value);
				}
		}
		return $array;
}
endif;

if (!function_exists('indeed_time_elapsed_string')):
/*
If your target date is timestamp use @ in front of it (ex. @1367365500) or set the second parameter as TRUE.
You can also pass the date as string (ex. '2017-04-20 01:50:10')
*/
function indeed_time_elapsed_string($datetime, $isTimestamp=false, $full = false, $languageDomain='ulp') {
			if ($isTimestamp){
					$datetime = '@' . $datetime;
			}
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $weeks = floor($diff->d / 7);
	    $diff->d -= $weeks * 7;

	    $string = array(
	        'y' => esc_html__('year', $languageDomain),
	        'm' => esc_html__('month', $languageDomain),
	        'w' => esc_html__('week', $languageDomain),
	        'd' => esc_html__('day', $languageDomain),
	        'h' => esc_html__('hour', $languageDomain),
	        'i' => esc_html__('minute', $languageDomain),
	        's' => esc_html__('second', $languageDomain),
	    );
	    foreach ($string as $k => &$v) {
				if ( $k === 'w' ){
						$v = $weeks . ' ' . $v . ($weeks > 1 ? 's' : '');
				} else {
					if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
				}

	    }

	    if (!$full){
				 $string = array_slice($string, 0, 1);
			}
	    return $string ? implode(', ', $string) . esc_html__(' ago', $languageDomain) : esc_html__('just now', $languageDomain);
}
endif;

if (!function_exists('ulpFillInGetPossibleValues')):
function ulpGetPossibleValues($string='')
{
	preg_match_all(
			'~(?<={).+?(?=})~',
			$string,
			$matches
	);
	return $matches[0];
}
endif;

if (!function_exists('ulp_force_array_element_to_int')):
function ulp_force_array_element_to_int($array=[])
{
		if (empty($array)){
				return $array;
		}
		foreach ($array as $key=>$value){
				$array[$key] = (int)$value;
		}
}
endif;

if ( !function_exists('indeed_get_current_language_code') ):
function indeed_get_current_language_code()
{
		$languageCode = get_locale();
		if ( !$languageCode ){
				return false;
		}
		$language = explode( '_', $languageCode );
		if ( isset($language[0]) ){
				return $language[0];
		}
		return $languageCode;
}
endif;

if ( !function_exists('indeed_is_plugin_active') ):
function indeed_is_plugin_active( $pluginBaseFile='' )
{
		if (!function_exists('is_plugin_active')){
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (is_plugin_active($pluginBaseFile)){
				return true;
		}
		return false;
}
endif;

if ( !function_exists('indeed_correct_text') ):
function indeed_correct_text($str, $wp_editor_content=false)
{
	$str = stripcslashes(htmlspecialchars_decode($str));
	if ($wp_editor_content){
		return indeed_format_str_like_wp($str);
	}
	return $str;
}
endif;

if ( !function_exists('indeed_format_str_like_wp') ):
function indeed_format_str_like_wp( $str )
{
	$str = preg_replace("/\n\n+/", "\n\n", $str);
	$str_arr = preg_split('/\n\s*\n/', $str, -1, PREG_SPLIT_NO_EMPTY);
	$str = '';

	foreach ( $str_arr as $str_val ) {
		$str .= '<p>' . trim($str_val, "\n") . "</p>\n";
	}
	return $str;
}
endif;

/**
 * @param string
 * @return string
 */
if ( !function_exists( 'ulp_format_str_like_wp' ) ):
function ulp_format_str_like_wp( $string='' )
{
		$string = preg_replace("/\n\n+/", "\n\n", $string);
		$stringArr = preg_split('/\n\s*\n/', $string, -1, PREG_SPLIT_NO_EMPTY);
		$string = '';

		foreach ( $stringArr as $stringValue ) {
			$string .= '<p>' . trim($stringValue, "\n") . "</p>\n";
		}
		return $string;
}
endif;

/**
 * @param none
 * @return bool
 */
if ( !function_exists( 'indeedIsAdmin' ) ):
function indeedIsAdmin()
{
		global $current_user;
		if ( empty( $current_user->ID ) ){
				return false;
		}
		$userData = get_userdata( $current_user->ID );
		if ( !$userData || empty( $userData->roles ) ){
				return false;
		}
		if ( !in_array( 'administrator', $userData->roles ) ){
				return false;
		}
		return true;
}
endif;

/**
 * @param none
 * @return bool
 */
if ( !function_exists( 'ulpAdminVerifyNonce' ) ):
function ulpAdminVerifyNonce()
{
		$nonce = isset( $_SERVER['HTTP_X_CSRF_ULP_ADMIN_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_ULP_ADMIN_TOKEN']	: '';
		if ( wp_verify_nonce( $nonce, 'ulpAdminNonce' ) ) {
				return true;
		}
		return false;
}
endif;

/**
 * @param none
 * @return bool
 */
if ( !function_exists( 'ulpPublicVerifyNonce' ) ):
function ulpPublicVerifyNonce()
{
		$nonce = isset( $_SERVER['HTTP_X_CSRF_ULP_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_ULP_TOKEN']	: '';
		if ( wp_verify_nonce( $nonce, 'ulpPublicNonce' ) ) {
				return true;
		}
		return false;
}
endif;

if ( !function_exists( 'ulpPrintStringIntoField' ) ):
function ulpPrintStringIntoField( $string='' )
{
		$string = str_replace( '"', '&quot;', $string );
		$string = str_replace( "'", '&apos;', $string );
		return $string;
}
endif;


if ( !function_exists( 'ulpSendTestNotification' ) ):
	function ulpSendTestNotification($notificationId=0, $email="") {
		global $wpdb;

			if ( $notificationId === 0 || $email === '' ){
					return;
			}

		$query = $wpdb->prepare( "SELECT id,type,course_id,subject,message,pushover_message,pushover_status,status FROM {$wpdb->prefix}ulp_notifications
								WHERE 1=1
								AND id=%s;", $notificationId );
		$data = $wpdb->get_row( $query );

			if ( $data ){
				$subject = (isset($data->subject)) ? $data->subject : '';
				$message = (isset($data->message)) ? $data->message : '';
			}

		$message = stripslashes( htmlspecialchars_decode( uap_format_str_like_wp( $message ) ) );
		$message = "<html><head></head><body>" . $message . "</body></html>";

			if ( $subject == '' && $message == '' ){
					return false;
			}
			$fromName = get_option( 'ulp_notification_name' );
			if ( $fromName == '' ){
					$fromName = get_option( 'blogname' );
			}
			$fromEmail = get_option( 'ulp_notification_email_from' );
			if ( $fromEmail == '' ){
					$fromEmail = get_option( 'admin_email' );
			}

			if ( !empty( $fromEmail ) && !empty( $fromName ) ){
				$headers[] = "From: $fromName <$fromEmail>";
			}

		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$sent = wp_mail( $email, $subject, $message, $headers );

		return $sent;

	}
	endif;

	if ( !function_exists('esc_ulp_content') ):
	/*
	 * This function is used to filter the html output.
	 */
	function esc_ulp_content( $string='' )
	{
	    return $string;
	}
	endif;

	if ( !function_exists( 'ulp_sanitize_array' ) ):
	function ulp_sanitize_array( $input=[] )
	{
	    $output = [];
	    if ( !is_array( $input ) ){
	        return sanitize_text_field($input);
	    }
	    foreach ( $input as $key => $val ) {
	        if ( is_array( $val ) ){
	            $output[ $key ] = ulp_sanitize_array( $val );
	        } else {
	            $output[ $key ] = ( isset( $input[ $key ] ) ) ? sanitize_text_field( $val ) : false;
	        }
	    }
	    return $output;
	}
	endif;

	if ( !function_exists( 'ulp_sanitize_textarea_array' ) ):
	function ulp_sanitize_textarea_array( $input=[] )
	{
	    $output = [];
	    if ( !is_array( $input ) ){
	        return wp_kses_post($input);
	    }
	    foreach ( $input as $key => $val ) {
	        if ( is_array( $val ) ){
	            $output[ $key ] = ulp_sanitize_textarea_array( $val );
	        } else {
	            $output[ $key ] = ( isset( $input[ $key ] ) ) ? wp_kses_post( $val ) : false;
	        }
	    }
	    return $output;
	}
	endif;
