<?php
if (!class_exists('UapMainPublic')){
	class UapMainPublic{
		private $current_url = '';
		private $affiliate_id = 0; // from uap_affiliate table
		private $user_id = 0; // from wp_users table
		private $is_admin = FALSE;
		private $user_role = '';

		public function __construct(){
			/*
			 * @param none
			 * @return none
			 */
			$this->current_url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			$this->referral_action();

			add_action('init', array($this, 'do_tracking'), 20);
			add_action('init', array($this, 'set_user'), 21);
			add_action('init', array($this, 'check_for_form_actions'), 22);
		  //add_action('init', array($this, 'check_page'), 23);
			add_action('wp', array($this, 'check_page'), 23);

			add_action('init', array($this, 'hide_admin_bar'), 24);
			add_action('user_register', array($this, 'all_new_users_become_affiliates'), 150, 1);
			add_filter('send_email_change_email', array($this, 'uap_affiliate_email_was_changed_filter'), 999, 3);

			/// SHORTCODES
			add_shortcode( 'uap-account-page', array($this, 'affiliate_print_account_page') );
			add_shortcode('uap-register', array($this, 'affiliate_print_register_form'));
			add_shortcode('uap-login-form', array($this, 'affiliate_print_login_form'));
			add_shortcode('uap-logout', array($this, 'affiliate_print_logout'));
			add_shortcode('uap-reset-password', array($this, 'affiliate_print_reset_password'));
			add_shortcode('uap-affiliate', array($this, 'affiliate_print_field'));
			add_shortcode('uap-user-become-affiliate', array($this, 'affiliate_print_become_affiliate_bttn'));
			add_shortcode('uap-public-affiliate-info', array($this, 'public_print_affiliate_info'));
			add_shortcode('uap-landing-commission', array($this, 'do_landing_commisions'));
			add_shortcode('uap-listing-affiliates', array($this, 'do_listing_affiliates'));
			add_shortcode('if_affiliate', array($this, 'uap_shortcode_if_affliate'));
			add_shortcode('if_not_affiliate', array($this, 'uap_shortcode_if_not_affliate'));
			add_shortcode('visitor_referred', [$this, 'uap_shortcode_visitor_is_referred'] );
			add_shortcode('visitor_not_referred', [$this, 'uap_shortcode_visitor_is_not_referred'] );
			add_shortcode( 'uap-product-links', [ $this, 'product_links' ] );

			/// FILTERS
			add_filter('the_content', array($this, 'uap_print_message'), 65);
			/// STYLE & SCRIPTS
			add_action('wp_enqueue_scripts', array($this, 'add_style_and_scripts'));

			if (!function_exists('is_plugin_active')){
	 			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	 		}
			// WOO CUSTOM MENU ITEM
			global $indeed_db;
			$temp = $indeed_db->return_settings_from_wp_option('woo_account_page');
			if (!empty($temp['uap_woo_account_page_enable']) && is_plugin_active('woocommerce/woocommerce.php')){
				require UAP_PATH . 'classes/WooCustomEndpoint.class.php';
				$woo_menu = new WooCustomEndpoint();
			}


			// mlm notification
			add_action( 'uap_action_new_mlm_relation', [ $this, 'mlm_notification_new_relation' ], 999, 2 );
		}

		public function add_style_and_scripts(){
			/*
			 * @param none
			 * @return none
			 */
			global $wp_version;

			//wp_enqueue_style( 'uap_public_style', UAP_URL . 'assets/css/main_public.css', [], '9.3' );
			wp_enqueue_style( 'uap_public_style', UAP_URL . 'assets/css/main_public.min.css', [], '9.3' );

			//wp_enqueue_style( 'uap_templates', UAP_URL . 'assets/css/templates.css', [], '9.3' );
			wp_enqueue_style( 'uap_templates', UAP_URL . 'assets/css/templates.min.css', [], '9.3' );

			wp_enqueue_script( 'jquery' );

			//wp_register_script( 'uap-public-functions', UAP_URL . 'assets/js/public-functions.js', ['jquery'], '9.3' );
			wp_register_script( 'uap-public-functions', UAP_URL . 'assets/js/public-functions.min.js', ['jquery'], '9.3' );

			if ( version_compare ( $wp_version , '5.7', '>=' ) ){
					wp_add_inline_script( 'uap-public-functions', "var ajax_url='" . admin_url('admin-ajax.php') . "';" );
			} else {
					wp_localize_script( 'uap-public-functions', 'ajax_url', admin_url( 'admin-ajax.php' ) );
			}
			wp_enqueue_script( 'uap-public-functions' );

			wp_register_style( 'uap_select2_style', UAP_URL . 'assets/css/select2.min.css' );
			wp_register_script( 'uap-select2', UAP_URL . 'assets/js/select2.min.js', [], '9.3' );
			wp_register_script( 'uap-jquery_form_module', UAP_URL . 'assets/js/jquery.form.js', ['jquery'], '9.3' );
			wp_register_script( 'uap-jquery.uploadfile', UAP_URL . 'assets/js/jquery.uploadfile.min.js', ['jquery'], '9.3' );
			//wp_register_script( 'uap-public-dynamic', UAP_URL . 'assets/js/public.js', ['jquery'], '9.3' );
		}

		public function do_tracking(){
			/*
			 * TRACKING
			 * @param none
			 * @return none
			 */
			require UAP_PATH . 'public/AffiliateTracking.class.php';
			$tracking_object = new AffiliateTracking();
		}

		public function affiliate_print_account_page($args=array()){
			/*
			 * @param array
			 * @return string
			 */
			global $indeed_db;

			$output = '';

			// since v.8.6 - preview affiliate profile
			$previewPortal = false;
			if( isset( $_GET['aid'] ) && $_GET['aid'] !== 0 && current_user_can( 'administrator' )  ){
					$affiliate_id = sanitize_text_field($_GET['aid']);
					$previewPortal = true;
			}
			// end of v.8.6

			if ($this->is_admin && !$this->affiliate_id && !isset($affiliate_id)){
				$output = $this->return_admin_info_message('account_page');
			} else if ($this->is_admin && isset($affiliate_id)){
				$user_id = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
				/// ONLY FOR AFFILIATES
				require UAP_PATH . 'public/AffiliateAccountPage.class.php';
				$obj = new AffiliateAccountPage($user_id, $affiliate_id);
				$obj->setPreview( $previewPortal );
				$output = $obj->output();

			}else if ($this->affiliate_id){
				/// ONLY FOR AFFILIATES
				require UAP_PATH . 'public/AffiliateAccountPage.class.php';
				$obj = new AffiliateAccountPage($this->user_id, $this->affiliate_id);
				$obj->setPreview( $previewPortal );
				$output = $obj->output();
			}
			return $output;
		}


		public function affiliate_print_register_form($attr=array()){
			/*
			 * @param array
			 * @return string
			 */

			$output = '';
			if ($this->is_admin){
				$output = $this->return_admin_info_message('register');
			} else if (!$this->affiliate_id){

				$showForm = true;
				$showForm = apply_filters( 'uap_show_register_form', $showForm );
				if ( !$showForm ){
						return '';
				}

				$shortcodes_attr = array();

				require UAP_PATH . 'classes/AffiliateAddEdit.class.php';
				$register_template = '';
				if(!empty($attr['template'])){
					$register_template = $attr['template'];
				 }
				$args = array(
						'user_id' => false,
						'type' => 'create',
						'tos' => true,
						'captcha' => true,
						'action' => '',
						'is_public' => true,
						'register_template' => $register_template,
						'shortcodes_attr' => $shortcodes_attr,
				);
				//8.6
				if (is_user_logged_in()){
					$args = array(
								'user_id' => $this->user_id
					);
				}
				$obj = new AffiliateAddEdit($args);
				$data = $obj->form();
				/// TEMPLATE
				$data['template'] = empty($attr['template']) ? get_option('uap_register_template') : $attr['template'];
				$data['css'] = get_option('uap_register_custom_css');

				$fullPath = UAP_PATH . 'public/views/register.php';
				$searchFilename = 'register.php';
				$template = apply_filters('uap_filter_on_load_template', $fullPath, $searchFilename );

				ob_start();
				$data['action'] = '';
				require $template;
				$output = ob_get_contents();
				ob_end_clean();
				$cl = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
				$w = new $cl();
				$clA = 'gd' . 'cp';
				$clS = 'G'.'LD';
				if ( $w->$clA() === true || $w->$clA() === '' || $w->$clS() === '1' ){
						$output .= $this->paragraph();
				}
			}
			return $output;
		}

		public function affiliate_print_login_form($args=array()){
			/*
			 * @param array
			 * @return string
			 */
			require UAP_PATH . 'public/AffiliateLogin.class.php';
			if ($this->is_admin){
				return $this->return_admin_info_message('login');
			} else {
				$object = new AffiliateLogin();
				$y = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
				$r = new $y();
				$state = 'g'.'dc'.'p';
				$cntr = 'GL'.'D';

				if ( $r->$state() === true || $r->$state() === '' || $r->$cntr() === '1' ){
					echo esc_uap_content($this->paragraph());
				}
				return $object->print_login_form($args, $this->user_role, $this->affiliate_id);
			}
			return '';
		}

		private function paragraph(){
			$name = 'tr'.'ial'.'_'.'ver'.'sion'.'_'.'me'.'ssage' . '.php';
		  $fullPath = UAP_PATH . 'public/views/' . $name;
			$template = apply_filters('uap_filter_on_load_template', $fullPath, $name );
			ob_start();
			require $template;
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}

		public function check_for_form_actions(){
			/*
			 * @param none
			 * @return none
			 */
			if (!empty($_REQUEST['uapaction'])){
				switch ($_REQUEST['uapaction']){
					case 'login':
						require UAP_PATH . 'public/AffiliateLogin.class.php';
						$object = new AffiliateLogin();
						$current_url_check = explode("?", $this->current_url);
						$this->current_url = $current_url_check[0];

						$object->do_login($this->current_url);
						break;

					case 'logout':
						$this->do_logout();
						break;

					case 'register':
						/// REGISTER
						require UAP_PATH . 'classes/AffiliateAddEdit.class.php';
						$args = array(
										'user_id' => FALSE,
										'type' => 'create',
										'tos' => TRUE,
										'captcha' => TRUE,
										'action' => '',
										'is_public' => TRUE,
										'register_template' => '',
										'url' => $this->current_url
						);
						//8.6
						if (is_user_logged_in()){
							$args = array(
										'user_id' => $this->user_id
							);
						}
						$obj = new AffiliateAddEdit($args);
						$obj->save_update_user();
						break;

					case 'update':
						/////////////////////// UPDATE
						if (is_user_logged_in()){

							require UAP_PATH . 'classes/AffiliateAddEdit.class.php';
							$args = array(
										'user_id' => $this->user_id,
										'type' => 'edit',
										'tos' => TRUE,
										'captcha' => TRUE,
										'action' => '',
										'is_public' => TRUE,
										'register_template' => '',
										'url' => $this->current_url
							);
							$obj = new AffiliateAddEdit($args);
							$obj->save_update_user();
						}
						break;
					case 'reset_pass':
						if ( empty($_POST['uap_reset_password_nonce']) || !wp_verify_nonce( $_POST['uap_reset_password_nonce'], 'uap_reset_password_nonce' ) ){
								return;
						}
						require UAP_PATH . 'public/AffiliateResetPassword.class.php';
						$object = new AffiliateResetPassword();
						$object->do_reset();
						break;
				}
			}
		}

		public function set_user(){
			/*
			 * @param none
			 * @return none
			 */
			global $current_user;
			global $indeed_db;
			$this->user_role = 'unreg';
			$this->is_admin = (current_user_can('manage_options')) ? TRUE : FALSE;
			if (!empty($current_user->ID)){
				$this->user_id = $current_user->ID;
				$this->affiliate_id = $indeed_db->affiliate_get_id_by_uid($this->user_id);

				if ($this->is_admin){
					$this->user_role = 'admin';
 				} else {
 					if (isset($current_user->roles[0]) && $current_user->roles[0]=='pending_user'){
 						$this->user_role = 'pending';
 					} else {
 						$this->user_role = 'reg';
 					}
 				}
			}
			return FALSE;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_print_message($content='')
		{
				if ( !empty( $_REQUEST['uap_register'] ) ){
				 		$str = '';
						switch ($_REQUEST['uap_register'] ){
							case 'create_message':
								$str .= '<div class="uap-reg-success-msg">' . uap_correct_text(get_option('uap_register_success_meg')) . '</div>';
								break;
							case 'update_message':
								$str .= '<div class="uap-reg-update-msg">' . uap_correct_text(get_option('uap_general_update_msg')) . '</div>';
								break;
						}
						return do_shortcode($content) . $str;
				}
				return $content;
		}

		public function affiliate_print_logout($attr=array()){
			/*
			 * @param array
			 * @return none
			 */
			$output = '';
			if ($this->user_id){ // && $this->affiliate_id
				if (isset($attr['uap_login_template'])){
					$data['metas']['uap_login_template'] = $attr['uap_login_template'];
				} else {
					$data['metas']['uap_login_template'] = get_option('uap_login_template');
				}
				$data['logout_link'] = add_query_arg( 'uapaction', 'logout', $this->current_url );
				$data['logout_label'] = esc_html__('Log Out', 'uap');

				$fullPath = UAP_PATH . 'public/views/logout.php';
				$searchFilename = 'logout.php';
				$template = apply_filters('uap_filter_on_load_template', $fullPath, $searchFilename );

				ob_start();
				require $template;
				$output = ob_get_contents();
				ob_end_clean();
			}
			return $output;
		}

		public function affiliate_print_reset_password($args=array()){
			/*
			 * @param none
			 * @return string
			 */
			 if ($this->is_admin){
			 	return $this->return_admin_info_message('reset_password');
			 } else {
				require UAP_PATH . 'public/AffiliateResetPassword.class.php';
				$object = new AffiliateResetPassword();
				return $object->form($args);
			 }
		}

		private function do_logout(){
			/*
			 * @param none
			 * @return none
			 */

			$url = get_option('uap_general_logout_redirect');
			if ($url && $url!=-1){
				$link = get_permalink($url);
				if (!$link){
					$link = $this->current_url;
				}
			} else {
				//redirect to same page
				global $wp;
				$link = remove_query_arg('uapaction', $this->current_url);
			}
			wp_clear_auth_cookie();
			do_action('wp_logout');
			nocache_headers();
			wp_redirect( $link );
			exit();
		}

		public function referral_action(){
			/*
			 * @param
			 * @return
			 */

			/// main referral
			require UAP_PATH . 'public/Referral_Main.class.php';
			$object = new Referral_Main($this->user_id, $this->affiliate_id);

			/************** services ****************/
			/// WOO
			require UAP_PATH . 'public/services/Uap_Woo.class.php';
			$woo = new Uap_Woo();

			/// UMP
			require UAP_PATH . 'public/services/Uap_UMP.class.php';
			$ump = new Uap_UMP();

			/// EDD
			require UAP_PATH . 'public/services/Uap_Easy_Digital_Download.class.php';
			$edd = new Uap_Easy_Digital_Download();

			/// ULP
			require UAP_PATH . 'public/services/Uap_Ulp.php';
			$ulp = new Uap_Ulp();

			/// UAP
			$uap = new \Indeed\Uap\Services\Uap_Uap();

			$InfoAffiliateBar = new \Indeed\Uap\InfoAffiliateBar();

			/// Landing pages
			$landingPagesObject = new \Indeed\Uap\AffiliateLandingPages();
		}

		public function affiliate_print_field($attr=array()){
			/*
			 * @param array
			 * @return string
			 */
			 $str = '';
			 if (!empty($attr['field']) && !empty($this->user_id)){
				$search = "{" . $attr['field'] . "}";
				$return = uap_replace_constants($search, $this->user_id);
				if ($search!=$return){
					$str = $return;
				}
			}
			return $str;
		}

		public function return_admin_info_message($type=''){
			/*
			 * @param string
			 * @return string
			 */
			 $data['content'] = '';
			 switch ($type){
			 	case 'login':
					$data['content'] = esc_html__('Loggin Form is not showing up when You\'re logged. Open the page into incognito window instead.', 'uap');
					break;
				case 'register':
					$data['content'] = esc_html__('Register Form is not showing up when You\'re logged. Open the page into incognito window instead.', 'uap');
					break;
				case 'account_page':
					$data['content'] = esc_html__('Affiliate Portal', 'uap');
					break;
				case 'reset_password':
					$data['content'] = esc_html__('Affiliate Lost Password Page', 'uap');
					break;
			 }
			 $fullPath = UAP_PATH . 'public/views/message_for_admin.php';
			 $searchFilename = 'message_for_admin.php';
			 $template = apply_filters('uap_filter_on_load_template', $fullPath, $searchFilename );

			 ob_start();
			 require $template;
			 $output = ob_get_contents();
			 ob_end_clean();
			 return $output;
		}

		public function affiliate_print_become_affiliate_bttn(){
			/*
			 * @param none
			 * @return string
			 */
			 global $indeed_db;
			 if ($this->user_id && !$indeed_db->is_user_affiliate_by_uid($this->user_id)){
				 $fullPath = UAP_PATH . 'public/views/become_affiliate_bttn.php';
				 $searchFilename = 'become_affiliate_bttn.php';
				 $template = apply_filters('uap_filter_on_load_template', $fullPath, $searchFilename );
				 $data['show_button'] = apply_filters('uap_become_affiliate_bttn', true);
				 $data['warning_message'] = apply_filters('uap_become_affiliate_warning_message', '');

				ob_start();
			 	require $template;
			 	$output = ob_get_contents();
			 	ob_end_clean();
			 	return $output;
			 }
		}

		public function check_page(){
			/*
			 * Do Redirect if it's case
			 * @param none
			 * @return none
			 */
			global $post, $indeed_db;
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}
			if (isset($_REQUEST['uxb_iframe']) && isset($_REQUEST['post_id'])){
				return;
			}
			if ( current_user_can( 'manage_options' ) ){
					return;
			}
			$url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			/// GETTING CURRENT POST ID
			//$post_id = url_to_postid($url);
			$post_id = isset( $post->ID ) ? $post->ID : 0;

			if ($post_id==0){
				$cpt_arr = $indeed_db->get_all_post_types();
				$the_cpt = FALSE;
				$post_name = FALSE;
				if (count($cpt_arr)){
					foreach ($cpt_arr as $cpt){
						if (!empty($_GET[$cpt])){
							$the_cpt = $cpt;
							$post_name = sanitize_text_field($_GET[$cpt]);
							break;
						}
					}
				}
				if ($the_cpt && $post_name){
					$cpt_id = $indeed_db->get_post_id_by_cpt_name($the_cpt, $post_name);
					if ($cpt_id){
						$postid = $cpt_id;
					}
				} else {
					//test if its homepage
					$homepage = get_option('page_on_front');
					if ($url==get_permalink($homepage)){
						$postid = $homepage;
					}
				}
			}

			/// CHECK IF WE MUST DO REDIRECT
			$default_pages = $indeed_db->return_settings_from_wp_option('general-default_pages');
			$default_redirects = $indeed_db->return_settings_from_wp_option('general-redirects');
			if ($default_pages && $default_redirects && $post_id){
				switch ($post_id){
					case ($default_pages['uap_general_login_default_page']==$post_id):
						if ($this->affiliate_id){
							/// DO REDIRECT
							$pid = $default_redirects['uap_general_login_page_logged_users_redirect'];
						}
						break;
					case ($default_pages['uap_general_register_default_page']==$post_id):
						if ($this->affiliate_id){
							/// DO REDIRECT
							$pid = $default_redirects['uap_general_register_page_logged_users_redirect'];
						}
						break;
					case ($default_pages['uap_general_lost_pass_page']==$post_id):
						if ($this->affiliate_id){
							/// DO REDIRECT
							$pid = $default_redirects['uap_general_lost_pass_page_logged_users_redirect'];
						}
						break;
					case ($default_pages['uap_general_logout_page']==$post_id):
						if (!$this->affiliate_id){
							/// DO REDIRECT
							$pid = $default_redirects['uap_general_logout_page_non_logged_users_redirect'];
						}
						break;
					case ($default_pages['uap_general_user_page']==$post_id):
						if (!$this->affiliate_id){
							/// DO REDIRECT
							$pid = $default_redirects['uap_general_account_page_no_logged_redirect'];
						}
						break;
				}
				if (isset($pid) && $pid > 0){
					$target = get_permalink($pid);
					wp_redirect($target);
					exit;
				}
			}
		}

		public function public_print_affiliate_info($attr=array()){
			/*
			 * @param array
			 * @return string
			 */
			$str = '';

			$affiliate_id = $this->check_and_return_affiliate_id(); /// get affiliate from cookie
			if (!empty($attr['field']) && !empty($affiliate_id)){
				$search = "{" . $attr['field'] . "}";
				global $indeed_db;
				$affiliate_wp_uid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
				if ($affiliate_wp_uid){
					$return = uap_replace_constants($search, $affiliate_wp_uid);
					if ($search!=$return){
						$str = $return;
					}
				}
			}
			return $str;
		}

		public function check_and_return_affiliate_id(){
			/*
			 * @param none
			 * @return int
			 */
			if (empty($_COOKIE['uap_referral'])){ /// SEARCH INTO DB
				global $indeed_db;
				$lifetime = get_option('uap_lifetime_commissions_enable');
				if ($lifetime && $this->user_id){ /// here was self::$user_id
					return $indeed_db->search_affiliate_id_for_current_user($this->user_id);
				}
			} else { /// SEARCH INTO COOKIE
				$cookie_data = json_decode(stripslashes($_COOKIE['uap_referral']), true );// since version 9.1
				if (!empty($cookie_data['affiliate_id'])){
					return $cookie_data['affiliate_id'];
				}
			}
			return 0;
		}

		public function do_landing_commisions($arr=array()){
			/*
			 * @param array
			 * @return none
			 */
			 if (!empty($arr['slug'])){
				if (!class_exists('ReferralLandingCommissions')){
					require UAP_PATH . 'public/ReferralLandingCommissions.class.php';
				}
				$object = new ReferralLandingCommissions($arr['slug'], $this->user_id);
			 }
		}

		public function all_new_users_become_affiliates($uid=0){
			/*
			 * @param int
			 * @return none
			 */
			 if (get_option('uap_all_new_users_become_affiliates') && $uid && !defined('UAP_USER_REGISTER_PROCESS')){
				 global $indeed_db;
				 $affiliate_id = $indeed_db->save_affiliate($uid);
				 if (!empty($affiliate_id)){
				 	/// assign default rank
				 	$settings = $indeed_db->return_settings_from_wp_option('register');
					if (!empty($settings['uap_register_new_user_rank'])){
				 		$indeed_db->update_affiliate_rank_by_uid($uid, $settings['uap_register_new_user_rank']);
					}

					/// SET MLM RELATION
					$indeed_db->set_mlm_relation_on_new_affiliate($affiliate_id);
				 }
			 }
		}

		public function do_listing_affiliates($params=array()){
			/*
			 * @param array
			 * @return string
			 */
			 if ( defined( 'REST_REQUEST' ) && REST_REQUEST ){
					 return;
			 }
			$params['current_page'] = (empty($_REQUEST['uapUserList_p'])) ? 1 : $_REQUEST['uapUserList_p'];
			if (!class_exists('TopAffiliatesList')){
				require UAP_PATH . 'classes/TopAffiliatesList.class.php';
			}
			$object = new TopAffiliatesList($params);
			$output = $object->run();
			return $output;
		}

		public function uap_shortcode_if_affliate($attr=array(), $content=''){
				global $indeed_db;
				$uid = indeed_get_uid();
				if (empty($uid)){
					 return '';
				}
				$is_affiliate = $indeed_db->is_user_an_active_affiliate($uid);
				if (empty($is_affiliate)){
					 return '';
				}
				return $content;
		}

		public function uap_shortcode_if_not_affliate($attr=array(), $content=''){
				global $indeed_db;
				$uid = indeed_get_uid();
				if (empty($uid)){
					 return $content;
				}
				$is_affiliate = $indeed_db->is_user_an_active_affiliate($uid);
				if (empty($is_affiliate)){
					 return $content;
				}
		}

		public function uap_affiliate_email_was_changed_filter($sent, $user, $user_new_data){
			/*
			 * USE THIS TO UPDATE EMAIL ON uap_reports TABLE
			 * @param boolean, array, array
			 * @return boolean
			 */
			 global $indeed_db;
			 $uid = $user['ID'];
			 $affiliate_id = $indeed_db->get_affiliate_id_by_wpuid($uid);
			 if ($affiliate_id && isset($user_new_data['user_email'])){
			 	$indeed_db->update_affiliate_reports_email_addr($affiliate_id, $user_new_data['user_email']);
			 }
			 return $sent;
		}

		public function hide_admin_bar(){
			/*
			 * Hide the admin bar if user has no privilege
			 * @param none
			 * @return none
			 */
			global $current_user;
			$uid = (isset($current_user->ID)) ? $current_user->ID : 0;
			if ($uid){
				$user = new WP_User($uid);

				// show for super admin
				if ( is_super_admin( $uid ) ){
						return show_admin_bar( true );
				}

				if ($user && !empty($user->roles) && !empty($user->roles[0]) && !in_array( 'administrator', $user->roles ) ){
					$allowed_roles = get_option('uap_dashboard_allowed_roles');
					if ($allowed_roles){
						$roles = explode(',', $allowed_roles);
						$show = FALSE;
						foreach ( $roles as $role ){
								if ( !empty( $role ) && !empty( $user->roles ) && in_array( $role, $user->roles ) ){
									$show = TRUE;
								}
						}
					} else {
						$show = FALSE;
					}
					show_admin_bar($show);
				}
			}
		}

		/// [visitor_referred]
		public function uap_shortcode_visitor_is_referred($attr=[], $content='')
		{
				$onlyFor = empty($attr['affiliate_id']) ? false : $attr['affiliate_id'];

				$cookieName = 'uap_referral';
				if (empty($_COOKIE[$cookieName])){
						return '';
				}
				$cookieData = json_decode(stripslashes($_COOKIE[$cookieName]), true );// since version 9.1
				if (empty($cookieData)){
						return '';
				}
				$onlyForAffiliates = explode(',', $onlyFor);

				if (in_array($cookieData['affiliate_id'], $onlyForAffiliates)){
						return $content;
				}
				return $content;
		}

		/// [visitor_not_referred]
		public function uap_shortcode_visitor_is_not_referred($attr=[], $content='')
		{
				$cookieName = 'uap_referral';
				if (empty($_COOKIE[$cookieName])){
						return $content;
				}
				$cookieData = json_decode(stripslashes($_COOKIE[$cookieName]), true );// since version 9.1
				if (empty($cookieData)){
						return $content;
				}
				return '';
		}

		public function product_links( $attr=[] )
		{
				$object = new \Indeed\Uap\ProductLinks();
				return $object->getOutput();
		}

		public function mlm_notification_new_relation( $parentAffiliateId=0, $childrenAffiliateId=0 )
		{
				global $indeed_db;
				if ( $parentAffiliateId === 0 || $childrenAffiliateId === 0 ){
						return;
				}
				$uid = $indeed_db->get_uid_by_affiliate_id( $parentAffiliateId );
				$sent_mail = uap_send_user_notifications( $uid, 'mlm_new_assignation' );
		}


	}//end of class
}//end if
