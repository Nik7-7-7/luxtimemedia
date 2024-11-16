<?php
if (!class_exists('AffiliateTracking')){
	class AffiliateTracking{
		protected $cookie_name = 'uap_referral';
		protected $settings = array();
		private static $single = FALSE;

		public function __construct(){
			/*
			 * @param string, string
			 * @return none
			 */
			if (self::$single){
				return;
			} else {
				self::$single = TRUE;
			}

			/// SET SETTINGS
			global $indeed_db;
			$this->settings = $indeed_db->return_settings_from_wp_option('general-settings');
			$current_url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];


			/// NO Referral Variable
			if (empty($this->settings['uap_referral_variable'])){
				return;
			}

			if(isset($_SERVER['HTTP_REFERER'])) {
				$url_referer = $_SERVER['HTTP_REFERER'];

				$block_referer_urls = preg_split("/(\r\n|\n|\r)/", $this->settings['uap_blocked_referers']);
				$search_block = in_array ($url_referer, $block_referer_urls);
				if ($search_block){
					return;
				}

			}

			$get_value = '';
			$campaign = '';
			$affiliateIdFromLandingPage = apply_filters('uap_init_affiliate_id_value', '', $current_url);

			/// SET THE REFERRAL & CAMPAIGN
			if (!empty($_GET[$this->settings['uap_referral_variable']])){
				/// REFERRAL
				$get_value = sanitize_text_field($_GET[$this->settings['uap_referral_variable']]);
				$current_url = remove_query_arg( $this->settings['uap_referral_variable'], $current_url);
				/// CAMPAIGN
				if ($this->settings['uap_campaign_variable'] && !empty($_GET[$this->settings['uap_campaign_variable']])){
					$campaign = sanitize_text_field($_GET[$this->settings['uap_campaign_variable']]);
					$current_url = remove_query_arg( $this->settings['uap_campaign_variable'], $current_url);// remove param from url
				}
			} else if (strpos($current_url, '/' . $this->settings['uap_referral_variable'] . '/')!==FALSE){
				$temp_get = explode('/', $current_url);
				if (is_array($temp_get) && count($temp_get)){
					/// REFERRAL
					$search_key = array_search($this->settings['uap_referral_variable'], $temp_get);
					if ($search_key){
						$key = $search_key + 1;
						if (isset($temp_get[$key])){
							$get_value = $temp_get[$key];
							$current_url = str_replace('/' . $this->settings['uap_referral_variable'] . '/' . $get_value, '', $current_url);
						}
					}
					/// CAMPAIGN
					if (strpos($current_url, '/' . $this->settings['uap_campaign_variable'] . '/')!==FALSE){
						$search_key = array_search($this->settings['uap_campaign_variable'], $temp_get);
						if ($search_key){
							$key = $search_key + 1;
							if (isset($temp_get[$key])){
								$campaign = $temp_get[$key];
								$current_url = str_replace('/' . $this->settings['uap_campaign_variable'] . '/' . $campaign, '', $current_url);
							}
						}
					}
					$force_redirect = TRUE;
				}
			} else if ($indeed_db->is_magic_feat_enable('simple_links')){
				/////////////// CUSTOM LINKS
				$http_ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

				if ($http_ref){
					$get_value = $indeed_db->simple_links_get_uid_by_link($http_ref);
				}
			}

			if (empty($get_value) && empty($affiliateIdFromLandingPage)){
					return; /// OUT
			}

			/************************* GETTING AFFILIATE ID ***************************/
			$affiliate_id = $this->getAffiliateId( $get_value );

			/// landing pages
			if (empty($affiliate_id) && !empty($affiliateIdFromLandingPage)){
					$affiliate_id = $affiliateIdFromLandingPage;
			}

			// change the cookie name. Used in Split Commission AddOn
			$this->cookie_name = apply_filters( 'uap_public_filter_change_cookie_name', $this->cookie_name, $affiliate_id );
			///

			$rewrite_referrals_enable = get_option('uap_rewrite_referrals_enable');
			if (!empty($_COOKIE[$this->cookie_name]) && empty($rewrite_referrals_enable)){

					if (!empty($this->settings['uap_redirect_without_param']) || !empty($force_redirect)){
							$this->do_redirect($current_url);
					}
					return;
			}

			if (empty($_COOKIE[$this->cookie_name])){
				$referral_hash = md5($_SERVER['REMOTE_ADDR'] . time());
			} else {
				$cookie_data = json_decode( stripslashes( $_COOKIE[$this->cookie_name] ), true );// since version 9.1 json_decode
				if (!empty($cookie_data['referral_hash'])){
					$referral_hash = $cookie_data['referral_hash'];
				}
			}

			$browser = $this->get_browser();
			$device = $this->get_device_type();
			$ip = '-';
			if( get_option('uap_workflow_disable_ip_address','0') === '0'){
				$ip = (empty($_SERVER['REMOTE_ADDR'])) ? '-' : $_SERVER['REMOTE_ADDR'];
			}


			if ($indeed_db->is_affiliate_active($affiliate_id)){
				/// STORE DATA IN DB
				$ref_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
				$visit_id = $indeed_db->track_the_visit($referral_hash, 0, $affiliate_id, $current_url, $ip, $browser, $device, $campaign, $ref_url);

				/// SET COOKIE
				$this->set_cookie($affiliate_id, $campaign, $referral_hash, $visit_id);

				if (!empty($affiliateIdFromLandingPage) && $affiliateIdFromLandingPage==$affiliate_id){
						return;
				}

				/// REDIRECT
				if (!empty($this->settings['uap_redirect_without_param'])){
						$this->do_redirect($current_url);
				}
			}

			/// FORCE REDIRECT - FOR FRIENDLY LINKS
			if (!empty($force_redirect)){
					$this->do_redirect($current_url);
			}
		}

		/**
		 * @param string
		 * @return int
		 */
		protected function getAffiliateId( $getValue='' )
		{
				global $indeed_db;
				$affiliateId = 0;
				if ( $this->settings['uap_default_ref_format'] == 'username' ){
						$getValue = urldecode( $getValue );
						$affiliateId = $indeed_db->get_affiliate_id_by_custom_slug( $getValue );
						/// Search affiliate id by username
						if ( empty( $affiliateId ) ){
							$affiliateId = $indeed_db->get_affiliate_id_by_username( $getValue );
						}
				} else {
						if ( is_numeric( $getValue ) ){
								$affiliateId = $getValue;
						}
				}

				if ( !empty( $affiliateId ) ){
						return $affiliateId;
				}

				// check if the value from $getValue is actually an affiliate id
				if ( $getValue != '' && is_numeric( $getValue ) && $indeed_db->is_affiliate_active( $getValue ) ){
						return $getValue;
				}

				if ( empty( $this->settings['uap_search_into_url_for_affid_or_username'] ) ){
						return $affiliateId;
				}
				/// we search again
				if ( $this->settings['uap_default_ref_format'] == 'username' ){
						if ( is_numeric( $getValue ) ){
								$affiliateId = $getValue;
						}
				} else {
						$getValue = urldecode( $getValue );
						$affiliateId = $indeed_db->get_affiliate_id_by_custom_slug( $getValue );
						if ( empty( $affiliateId ) ){
							$affiliateId = $indeed_db->get_affiliate_id_by_username( $getValue );
						}
				}
				return $affiliateId;
		}

		protected function do_redirect($target_url=''){
				// check if original url is the with target url in order to prevent infinite loop
				$originalUrl = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				if ( $target_url === $originalUrl ){
						return;
				}

				wp_redirect($target_url);
				exit();
		}

		protected function set_cookie($affiliate_id=0, $campaign='', $referral_hash='', $visit_id=0){
			/*
			 * @param string
			 * @return int
 		 	 */

			/// For PPC
			if (empty($_COOKIE[$this->cookie_name])){
					do_action('uap_insert_into_cookie_new_affiliate', $affiliate_id, $visit_id);
			} else {
					$cookieData = json_decode( stripslashes( $_COOKIE[$this->cookie_name] ), true );// since version 9.1 json_decode
					if ($cookieData['affiliate_id']!=$affiliate_id){
							do_action('uap_insert_into_cookie_new_affiliate', $affiliate_id, $visit_id);
					}
			}

			$data['affiliate_id'] = $affiliate_id;
			$data['campaign'] = $campaign;
			$data['referral_hash'] = $referral_hash;
			$data['visit_id'] = $visit_id;
			$data['timestamp'] = time();
			$data['site_referer'] = (empty($_SERVER['HTTP_REFERER'])) ? '' : $_SERVER['HTTP_REFERER'];
			$cookie_time = $this->settings['uap_cookie_expire'];
			if (empty($cookie_time)){
				$cookie_time = $data['timestamp'] + 360 * 24 * 60 * 60;//one year
			} else {
				$cookie_time = $data['timestamp'] + $cookie_time * 24 * 60 * 60;
			}
			$path = '/';
			if ( is_multisite() && isset( $this->settings['uap_cookie_sharing'] ) && $this->settings['uap_cookie_sharing'] == '0'){
				 $path = $_SERVER['HTTP_HOST'];
			}

			// since version 9.1 json_encode
			setcookie($this->cookie_name, json_encode( $data ), $cookie_time, $path); /// name, value, expire, path

			return $data['referral_hash'];
		}

		protected function get_browser(){
			/*
			 * @param none
			 * @return string
			 */
			if (!empty($_SERVER['HTTP_USER_AGENT'])){
				if (preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/i', $_SERVER['HTTP_USER_AGENT'])){
					return 'Internet Explorer';
				} else if (preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])){
					return 'Firefox';
				} else if (preg_match('/Chrome/i', $_SERVER['HTTP_USER_AGENT'])) {
					return 'Chrome';
				} else if (preg_match('/Safari/i', $_SERVER['HTTP_USER_AGENT'])){
					return 'Safari';
				} else if (preg_match('/Opera/i', $_SERVER['HTTP_USER_AGENT'])){
					return 'Opera';
				} else {
					return 'Other';
				}
			}
		}

		protected function get_device_type(){
			/*
			 * @param none
			 * @return string
			 */
			if (!class_exists('MobileDetect')){
				require UAP_PATH . 'classes/MobileDetect.php';
			}
			$detect = new MobileDetect();
			if (($detect->isMobile()) || ($detect->isTablet())){
				 return 'mobile';
			}
			return 'web';
		}

	}
}
