<?php
if (class_exists('ihcAccountPage') ){
	 return;
}

class ihcAccountPage{
		private $url = '';
		private $current_user = array();
		private $settings = array();
		private $tab = '';
		private $users_sm = array();
		private $show_tabs = array();
		private $is_affiliate_on = FALSE;
		private $base_url = '';

		/**
		 * @param array
		 * @return none
		 */
		public function __construct( $args=[] )
		{
				$account_page = get_option('ihc_general_user_page');
				if ($account_page!==FALSE && $account_page>-1 && empty($args['is_buddypress']) && empty($args['is_woocommerce'])){
					$this->url = get_permalink($account_page);
					$this->base_url = $this->url;
				} else {
					$this->url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					$this->base_url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				}

				$remove_get_attr = array(
											'ihcdologout',
											'ihc_ap_menu',
											'ihc_list_item',
											'ihc_braintree_fields',
											'lid',
											'ihcbt',
											'ihc_lid',
											'ihc_uid',
											'ihc_success_bt',
											'ihc_bt_success_msg',
											'ihc_state',
											'ihc_country',
											'ihc_register',
											'ihcUserList_p',
											'ihc_name',
				);
				foreach ($remove_get_attr as $key){
					if (!empty($_GET[$key])){
						$this->base_url = remove_query_arg($key, $this->base_url);
					}
				}

				$this->current_user = wp_get_current_user();
				$this->settings = ihc_return_meta_arr('account_page');
				$temp = Ihc_Db::account_page_get_tabs_details();
				if ($temp){
					$this->settings = array_merge($this->settings, $temp);
				}
				$this->is_affiliate_on = ihc_is_uap_active();
		}

		/**
		 * @param string
		 * @return string
		 */
		public function print_page($tab)
		{
				$this->tab = $tab;
				$str = '';
				$str .= $this->print_header();
				$str .= $this->print_tabs();
				/// CONTENT
				if (empty($this->tab)){
					$str .= $this->overview_page();
				} else {
					switch ($this->tab){
						case 'profile':
							$str .= $this->account_details_page();
							break;
						case 'transactions':
							// transactions is deprecated since version 9.4
							// for safety reason if somehow the user got here we redirect to the orders page
							$str .= $this->print_orders();
							break;
						case 'subscription':
							$str .= $this->subscription_page();
							break;
						case 'social':
						case 'social_plus':
							$str .= $this->social_page();
							break;
						case 'affiliate':
							$str .= $this->affiliate_page();
							break;
						case 'help';
							$str .= $this->print_help();
							break;
						case 'orders';
							$str .= $this->print_orders();
							break;
						case 'membeship_gifts';
							$str .= $this->print_membeship_gifts();
							break;
						case 'membership_cards';
							$str .= $this->print_membership_cards();
							break;
						case 'pushover_notifications':
							$str .= $this->print_pushover_notifications();
							break;
						case 'overview':
							$str .= $this->overview_page();
							break;
						case 'user_sites':
							$str .= $this->print_user_sites();
							break;
						case 'user_sites_add_new':
							$str .= $this->print_user_sites_add_new();
							break;
						default:
							$customTabContent = '';
							$customTabContent = apply_filters( 'ihc_account_page_custom_tab_content', $customTabContent, $this->tab );
							// @description run on account page, print the content for custom tab. @paramn custom tab content (string), slug of the tab
							if ( $customTabContent ){
									$str .= $customTabContent;
							} else {
									$str .= $this->print_custom_tab();
							}
							break;
					}
				}

				/// CONTENT
				$str .= $this->print_footer();
				return $str;
		}

		/**
		 * print the top section with photo and welcome message
		 * @param none
		 * @return string
		 */
		private function print_header()
		{
				$output = '';
				$data['custom_css'] = '';
				if (!empty($this->settings['ihc_account_page_custom_css'])){
						$data['custom_css'] = stripslashes($this->settings['ihc_account_page_custom_css']);
				}
				$show_avatar = $this->settings['ihc_ap_edit_show_avatar'];
				if ($show_avatar){
						$avatar_url = ihc_get_avatar_for_uid($this->current_user->ID);
						if ($avatar_url){
								$data['avatar'] = $avatar_url;
						}
				}
				$data ['top_banner'] = get_user_meta($this->current_user->ID, 'ihc_user_custom_banner_src', true);
                                if ( !$data['top_banner'] ){
                                    $data['top_banner'] = ihcDefaultBannerImage();
                                }


				$first_name = get_user_meta($this->current_user->ID, 'first_name', true);
				$last_name = get_user_meta($this->current_user->ID, 'last_name', true);

				if (!empty($this->settings['ihc_ap_welcome_msg'])){
						$this->settings['ihc_ap_welcome_msg'] = ihc_format_str_like_wp($this->settings['ihc_ap_welcome_msg']);
						$this->settings['ihc_ap_welcome_msg'] = htmlspecialchars_decode($this->settings['ihc_ap_welcome_msg']);
						$this->settings['ihc_ap_welcome_msg'] = stripslashes($this->settings['ihc_ap_welcome_msg']);
						$data['welcome_message'] = ihc_replace_constants($this->settings['ihc_ap_welcome_msg'], $this->current_user->ID);
				}
				$data['sm'] = $this->print_sm_icons_for_current_user();

				if (!empty($this->settings['ihc_ap_edit_show_level'])){
					$data['levels'] = array();
					$level_list_data = \Indeed\Ihc\UserSubscriptions::getAllForUserAsList( $this->current_user->ID );
					$level_list_data = apply_filters( 'ihc_public_get_user_levels', $level_list_data, $this->current_user->ID );
					// @description

					if (isset($level_list_data) && $level_list_data!=''){
						$level_list_data = explode(',', $level_list_data);
						if ($level_list_data){
							foreach ($level_list_data as $id){
								$data['levels'][$id] = ihc_get_level_by_id($id);
							}
						}
					}
				}

				$data['badges_metas'] = ihc_return_meta_arr('badges');
				if (!empty($data['badges_metas']) && !empty($data['badges_metas']['ihc_badges_on']) && !empty($data['badges_metas']['ihc_badge_custom_css'])){
					$data['custom_css'] .= stripslashes($data['badges_metas']['ihc_badge_custom_css']);
				}

				$fullPath = IHC_PATH . 'public/views/account_page-header.php';
				$searchFilename = 'account_page-header.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename);


				ob_start();
				require $template;
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_footer()
		{
		 	$output = '';
			$data['content'] = (isset($this->settings['ihc_ap_footer_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_footer_msg'], $this->current_user->ID) : '';
			$data['content'] = $this->clean_text($data['content']);

			$fullPath = IHC_PATH . 'public/views/account_page-footer.php';
			$searchFilename = 'account_page-footer.php';
			$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename);

			ob_start();
			require $template;
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}

		/**
		 * print the top menu with available tabs
		 * @param none
		 * @return string
		 */
		private function print_tabs()
		{
				global $indeed_db;
				$output = '';
				$available_tabs = Ihc_Db::account_page_get_menu();

				if (!ihc_is_magic_feat_active('gifts')){
					unset($available_tabs['membeship_gifts']);
				}

				if (!ihc_is_magic_feat_active('membership_card')){
					unset($available_tabs['membership_cards']);
				}

				if (!ihc_is_magic_feat_active('pushover')){
					unset($available_tabs['pushover_notifications']);
				}

				if (!ihc_is_magic_feat_active('user_sites')){
					unset($available_tabs['user_sites']);
				}

				// transactions is deprecated since version 9.4
				if ( isset( $available_tabs['transactions'] ) ){
						unset( $available_tabs['transactions'] );
				}

				$this->show_tabs = explode(',', $this->settings['ihc_ap_tabs']);

				if (!in_array('logout', $this->show_tabs)){
					unset($available_tabs['logout']);
				}
				if (empty($this->is_affiliate_on) || get_option('ihc_ap_show_aff_tab')==FALSE){
					unset($available_tabs['affiliate']);
				}

				$data['menu'] = [];
				foreach ($available_tabs as $k=>$v){
					if (in_array($k, $this->show_tabs)){
						if (!empty($this->settings['ihc_ap_' . $k . '_menu_label'])){
							$data['menu'][$k]['title'] = $this->settings['ihc_ap_' . $k . '_menu_label'];
						} else if (isset($v['label'])) {
							$data['menu'][$k]['title'] = $v['label'];
						} else {
							$data['menu'][$k]['title'] = $k;
						}
						$data['menu'][$k]['class'] = 'ihc-ap-menu-item';
						$data['menu'][$k]['class'] .= ($k==$this->tab) ? ' ihc-ap-menu-item-selected' : '';
						if ( !empty( $v['url'] ) ){
								$data['menu'][$k]['url'] = $v['url'];
						} else {
								$data['menu'][$k]['url'] = add_query_arg( 'ihc_ap_menu', $k, $this->base_url );
								$data['menu'][$k]['url'] = apply_filters( 'ihc_filter_public_account_page_tab_link', $data['menu'][$k]['url'], $this->base_url, $k );
						}

					}
				}

				if ($this->is_affiliate_on && get_option('ihc_ap_show_aff_tab') && in_array('affiliate', $this->show_tabs)){

					$data['menu']['affiliate']['class'] = 'ihc-account-affiliate-link ihc-ap-menu-item';
					$data['menu']['affiliate']['class'] .= ('affiliate'==$this->tab) ? ' ihc-ap-menu-item-selected' : '';
					if (empty($indeed_db) && defined('UAP_PATH')){
						include UAP_PATH . 'classes/Uap_Db.class.php';
						$indeed_db = new Uap_Db;
					}
					$is_affiliate = $indeed_db->is_user_affiliate_by_uid($this->current_user->ID);
					if ($is_affiliate){
						$pid = get_option('uap_general_user_page');
						$data['menu']['affiliate']['url'] = get_permalink($pid);
					} else {
						$data['menu']['affiliate']['url'] = add_query_arg( 'ihc_ap_menu', 'affiliate', $this->base_url );
						$data['menu']['affiliate']['url'] = apply_filters( 'ihc_filter_public_account_page_tab_link', $data['menu']['affiliate']['url'] , $this->base_url, 'affiliate' );
					}
				}
				if (in_array('logout', $this->show_tabs)){
					if (!empty($this->settings['ihc_ap_logout_menu_label'])){
						$data['menu']['logout']['title'] = $this->settings['ihc_ap_logout_menu_label'];
					} else {
						$data['menu']['logout']['title'] = 'LogOut';
					}
					$data['menu']['logout']['class'] = 'ihc-ap-menu-item';
					$data['menu']['logout']['url'] = $this->base_url;
					$data['menu']['logout']['url'] = add_query_arg('ihcdologout', 1, $data['menu']['logout']['url']);
				}

				$custom_tabs = Ihc_Db::account_page_menu_get_custom_items();

				$custom_tabs = apply_filters( 'ihc_public_account_page_menu_custom_tabs', $custom_tabs );
				// @description used to filter the custom menu tabs in public account page. @param list of custom tabs ( array )

				$data['menu'] = apply_filters( 'ihc_public_account_page_menu_standard_tabs', $data['menu'] );
				// @description used to filter the standard menu tabs in public account page. @param list of standard tabs ( array )

				$fullPath = IHC_PATH . 'public/views/account_page-tabs.php';
				$searchFilename = 'account_page-tabs.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename);

				ob_start();
				require $template;
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
		}

		/**
		 * @param none
		 * @return string
		 */
		private function overview_page()
		{
				return do_shortcode( '[ihc-account-page-overview]' );
		}

		/**
		 * @param none
		 * @return string
		 */
		private function affiliate_page()
		{
				$key = $this->tab;
				$data['content'] = get_option('ihc_ap_aff_msg');
				$data['content'] = ihc_format_str_like_wp($data['content']);
				$data['content'] = htmlspecialchars_decode($data['content']);
				$data['content'] = ihc_replace_constants($data['content'] , $this->current_user->ID);
				$data['title'] = get_option('ihc_ap_affiliate_title');
				$data['title'] = ihc_replace_constants($data['title'], $this->current_user->ID);
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				$fullPath = IHC_PATH . 'public/views/account_page-custom_tab.php';
				$searchFilename = 'account_page-custom_tab.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				ob_start();
				require $template;
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
		}

		/**
		 * @param none
		 * @return string
		 */
		private function account_details_page()
		{
				$data['template'] = get_option('ihc_register_template');
				$data['style'] = get_option('ihc_register_custom_css');
				$data['style'] = stripslashes($data['style']);
				$data['show_form'] = isset( $this->settings['ihc_account_page_profile_show_form'] ) ? $this->settings['ihc_account_page_profile_show_form'] : 1;
				$current_user = wp_get_current_user();

				$data['content'] = (isset($this->settings['ihc_ap_profile_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_profile_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_profile_title'])) ? ihc_replace_constants($this->settings['ihc_ap_profile_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				$fullPath = IHC_PATH . 'public/views/account_page-account_details_page.php';
				$searchFilename = 'account_page-account_details_page.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();

		}

		/**
		 * @param none
		 * @return string
		 */
		private function subscription_page()
		{
				$data['content'] = (isset($this->settings['ihc_ap_subscription_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_subscription_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_subscription_title'])) ? ihc_replace_constants($this->settings['ihc_ap_subscription_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				$data['show_table'] = 1;
				if (isset($this->settings['ihc_ap_subscription_table_enable']) && $this->settings['ihc_ap_subscription_table_enable']==0){
					$data['show_table'] = 0;
				}
				$data['show_subscription_plan'] = 1;

				if ( isset($this->settings['ihc_ap_subscription_plan_enable']) && $this->settings['ihc_ap_subscription_plan_enable'] == 0 ){
					$data['show_subscription_plan'] = 0;

					if (isset($_GET['ihc_success_bt'])){
						/// BT PAYMENT
						add_filter('the_content', 'ihc_filter_print_bank_transfer_message', 79, 1);
					}
					/// subscription plan check stuff

				}

				$data['levels_str'] = \Indeed\Ihc\UserSubscriptions::getAllForUserAsList( $this->current_user->ID );
				$data['uid'] = $this->current_user->ID;
				$fields = get_option('ihc_user_fields');
				////PRINT SELECT PAYMENT
				$key = ihc_array_value_exists($fields, 'payment_select', 'name');
				$print_payment_select = (empty($fields[$key]['display_public_ap'])) ? FALSE : TRUE;

				///INCLUDE STRIPE JS SCRIPT?
				if (in_array('stripe', ihc_get_active_payments_services(TRUE)) && $print_payment_select){
					$include_stripe = TRUE;
				}

				global $wpdb;

				$fullPath = IHC_PATH . 'public/views/account_page-subscription_page.php';
				$searchFilename = 'account_page-subscription_page.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

		/**
		 * @param none
		 * @return string
		 */
		private function social_page()
		{
				$output = '';
				if (!empty($this->settings['ihc_ap_social_plus_message'])){
					/// old var
					$this->settings['ihc_ap_social_msg'] = $this->settings['ihc_ap_social_plus_message'];
				}
				$data['content'] = ihc_replace_constants((isset($this->settings['ihc_ap_social_msg'])) ? $this->settings['ihc_ap_social_msg'] : '', $this->current_user->ID);
				$data['title'] = (isset($this->settings['ihc_ap_social_title'])) ? $this->settings['ihc_ap_social_title'] : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);
				$data['show_buttons'] = isset( $this->settings['ihc_account_page_social_plus_show_buttons'] ) ? $this->settings['ihc_account_page_social_plus_show_buttons'] : 1;

				$fullPath = IHC_PATH . 'public/views/account_page-social_plus.php';
				$searchFilename = 'account_page-social_plus.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				ob_start();
				require $template;
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_sm_icons_for_current_user()
		{
			$arr = array(
					"fb" => "Facebook",
					"tw" => "Twitter",
					"in" => "LinkedIn",
					"goo" => "Google",
					"vk" => "Vkontakte",
					"ig" => "Instagram",
					"tbr" => "Tumblr"
			);
			$str = '';
			foreach ($arr as $k=>$v){
				$data = get_user_meta($this->current_user->ID, 'ihc_' . $k, true);
				if (!empty($data)){
					$this->users_sm[] = $k;
					$str .= '<span class="ihc-account-page-sm-icon ihc-'.esc_attr($k).'"><i class="fa-ihc-sm fa-ihc-'.esc_attr($k).'"></i></span>';
				}
			}
			if ($str){
				$str = '<div class="ihc-ap-sm-top-icons-wrap">' . $str . '</div>';
			}
			return $str;
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_help()
		{
				$data['content'] = (isset($this->settings['ihc_ap_help_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_help_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_help_title'])) ? ihc_replace_constants($this->settings['ihc_ap_help_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				$fullPath = IHC_PATH . 'public/views/account_page-help.php';
				$searchFilename = 'account_page-help.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_pushover_notifications()
		{
				$data['content'] = (isset($this->settings['ihc_ap_pushover_notifications_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_pushover_notifications_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_pushover_notifications_title'])) ? ihc_replace_constants($this->settings['ihc_ap_pushover_notifications_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);
				$data['show_form'] = isset( $this->settings['ihc_account_page_pushover_show_form'] ) ? $this->settings['ihc_account_page_pushover_show_form'] : 1;

				$fullPath = IHC_PATH . 'public/views/account_page-pushover_notifications.php';
				$searchFilename = 'account_page-pushover_notifications.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_orders()
		{
				if ( !($this->current_user) || !isset($this->current_user->ID)){
						return '';
				}
				$data['content'] = (isset($this->settings['ihc_ap_orders_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_orders_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_orders_title'])) ? ihc_replace_constants($this->settings['ihc_ap_orders_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);
				$data['show_table'] = isset( $this->settings['ihc_account_page_orders_show_table'] ) ? $this->settings['ihc_account_page_orders_show_table'] : 1;

				$fullPath = IHC_PATH . 'public/views/account_page-orders.php';
				$searchFilename = 'account_page-orders.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
				return $view->setTemplate( $template )
										->setContentData( $data )
										->getOutput();
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_membeship_gifts()
		{
				$data['content'] = (isset($this->settings['ihc_ap_membeship_gifts_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_membeship_gifts_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_membeship_gifts_title'])) ? ihc_replace_constants($this->settings['ihc_ap_membeship_gifts_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				$fullPath = IHC_PATH . 'public/views/account_page-help.php';
				$searchFilename = 'account_page-help.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_membership_cards()
		{
				$data['content'] = (isset($this->settings['ihc_ap_membership_cards_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_membership_cards_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_membership_cards_title'])) ? ihc_replace_constants($this->settings['ihc_ap_membership_cards_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				ob_start();
				require IHC_PATH . 'public/views/account_page-help.php';
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_user_sites_add_new()
		{
				$data['save_link'] = add_query_arg('ihc_ap_menu', 'user_sites', $this->base_url);
				$data['save_link'] = apply_filters( 'ihc_filter_public_account_page_tab_link', $data['save_link'], $this->base_url, 'user_sites' );
				$data['lid'] = (isset($_GET['lid'])) ? $_GET['lid'] : 0;
				$data['content'] = (isset($this->settings['ihc_ap_user_sites_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_user_sites_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_user_sites_title'])) ? ihc_replace_constants($this->settings['ihc_ap_user_sites_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);
				$data['show_table'] = isset( $this->settings['ihc_account_page_user_sites_show_table'] ) ? $this->settings['ihc_account_page_user_sites_show_table'] : 1;

				$fullPath = IHC_PATH . 'public/views/account_page-user_sites_add_new.php';
				$searchFilename = 'account_page-user_sites_add_new.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_user_sites()
		{
				global $current_user;

				$data['current_url'] = add_query_arg( 'ihc_ap_menu', 'user_sites', $this->base_url );
 				$data['current_url'] = apply_filters( 'ihc_filter_public_account_page_tab_link', $data['current_url'], $this->base_url, 'user_sites' );
				$data['content'] = (isset($this->settings['ihc_ap_user_sites_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_user_sites_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_user_sites_title'])) ? ihc_replace_constants($this->settings['ihc_ap_user_sites_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);
				$data['show_table'] = isset( $this->settings['ihc_account_page_user_sites_show_table'] ) ? $this->settings['ihc_account_page_user_sites_show_table'] : 1;

				$fullPath = IHC_PATH . 'public/views/account_page-user_sites.php';
				$searchFilename = 'account_page-user_sites.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

		/**
		 * @param string
		 * @return string
		 */
		private function clean_text($string='')
		{
			 //return stripslashes($string);
			 return ihc_correct_text( $string, true );
		}

		/**
		 * @param none
		 * @return string
		 */
		private function print_custom_tab()
		{
				$key = $this->tab;
				$data['content'] = (isset($this->settings['ihc_ap_' . $key . '_msg'])) ? ihc_replace_constants($this->settings['ihc_ap_' . $key . '_msg'], $this->current_user->ID) : '';
				$data['title'] = (isset($this->settings['ihc_ap_' . $key . '_title'])) ? ihc_replace_constants($this->settings['ihc_ap_' . $key . '_title'], $this->current_user->ID) : '';
				$data['content'] = $this->clean_text($data['content']);
				$data['title'] = $this->clean_text($data['title']);

				$fullPath = IHC_PATH . 'public/views/account_page-custom_tab.php';
				$searchFilename = 'account_page-custom_tab.php';
				$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

				$view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
		}

}
