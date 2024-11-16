<?phpupdate_option( substr( md5('uap-deco') . md5('ultimate-affiliate-pro'), 3, 12 ), '********-****-****-****-************' );
update_option( substr( md5('uap-seop') . md5('ultimate-affiliate-pro'), 3, 12 ), '0' );
update_option( substr( md5('uap-nil') . md5('ultimate-affiliate-pro'), 3, 12 ), '0' );
/*
Plugin Name: Indeed Ultimate Affiliate Pro
Plugin URI: https://ultimateaffiliate.pro/
Description: The most complete and easy to use Affiliate system Plugin that provides you a complete solution for your affiliates.
Version: 9.3
Author: WPIndeed Development
Author URI: https://www.wpindeed.com
Text Domain: uap
Domain Path: /languages

@package        Indeed Ultimate Affiliate Pro
@author           WPIndeed Development
*/


class UAP_Main{
	private static $instance = FALSE;

	public function __construct(){}		if (get_option('ihc_license_set') != 1) {
		update_option('uap_license_set', 1);
		update_option('uap_envato_code', "123456-123456-123456-123456");
		}
		}

	/**
	* @param none
	* @return none
	*/
	public static function run()
	{
		if (self::$instance==TRUE){
			return;
		}
		self::$instance = TRUE;
		/// PATHS
		if (!defined('UAP_PATH')){
			define('UAP_PATH', plugin_dir_path(__FILE__));
		}
		if (!defined('UAP_URL')){
			define('UAP_URL', plugin_dir_url(__FILE__));
		}
		if (!defined('UAP_MAIN_FILE_NAME')){
			$uapMainFileName = str_replace( UAP_PATH, '', __FILE__ );
			define('UAP_MAIN_FILE_NAME', $uapMainFileName );
		}
		if (!defined('UAP_PROTOCOL')){
			if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
				define('UAP_PROTOCOL', 'https://');
			} else {
				define('UAP_PROTOCOL', 'http://');
			}
		}

		if (!defined('UAP_PLUGIN_VER')){
			define('UAP_PLUGIN_VER', self::get_plugin_ver() );//used for updates
		}

		if ( !defined( 'UAP_VEND' ) ){
				define( 'UAP_VEND', [
																		'evt'			=> 'Envato Marketplace'
				]);
		}
		if (!defined('UAP_DEV')){
			define('UAP_DEV', "WPIndeed");
		}

		/// LANGUAGES
		add_action('init', array('UAP_Main', 'uap_load_language'));
		add_filter('send_password_change_email', array('UAP_Main', 'uap_update_passowrd_filter'), 99, 2);
		add_filter('wp_authenticate_user', array('UAP_Main', 'uap_authenticate_filter'), 9999, 3);

		require_once UAP_PATH . 'autoload.php';
		require_once UAP_PATH . 'utilities.php';
		require_once UAP_PATH . 'classes/UapDb.class.php';

		register_activation_hook( __FILE__, array( 'UAP_Main', 'onActivation') );

		add_filter( 'et_grab_image_setting', [ 'UAP_Main', 'diviGrabImage' ], 999, 1 );

		global $indeed_db;
		$indeed_db = new UapDb();
		$UapGDPR = new Indeed\Uap\UapGDPR();
		$modals = new \Indeed\Uap\Admin\Modals();


		require_once UAP_PATH . 'classes/UapAjax.class.php';
		$uap_ajax = new UapAjax();

		//since version 8.5
		// tracking
		$uapTracking = new \Indeed\Uap\Tracking();
		// wizard
		$UapWizard = new \Indeed\Uap\Admin\Wizard();
		$UapWizardElCheck = new \Indeed\Uap\Admin\WizardElCheck();

		// deprecated shortcodes
		$deprecatedShortcodes = new \Indeed\Uap\DeprecatedShortcodes();
		$bannersLinksAndNames = new \Indeed\Uap\BannerLinksAndNames();
		// end version 8.5

		add_action( 'init', [ 'UAP_Main', 'createBaseObject'] );

		/// CRON
		require_once UAP_PATH . 'classes/UapCronJobs.class.php';
		$uap_cron_object = new UapCronJobs();

		/// ADMIN MENU && NOTIFICATIONS
		add_action('admin_bar_menu', array('UAP_Main', 'uap_add_custom_top_menu_dashboard'), 995);
		add_action('admin_bar_menu', array('UAP_Main', 'add_custom_admin_bar_item'), 996);
		add_filter('query_vars', array('UAP_Main', 'edit_query_vars'), 991, 1);
		add_action('init', array('UAP_Main', 'do_add_rewrite_endpoint_uap'), 30);
		add_action('init', array('UAP_Main', 'uap_gate'), 92);
		add_action( 'init', [ 'UAP_Main', 'check_user_privilege' ], 1 );

		///other modules
		require_once UAP_PATH . 'classes/WPSocialLoginIntegration.class.php';
		WPSocialLoginIntegration::run();

		$RewriteDefaultWpAvatar = new \Indeed\Uap\RewriteDefaultWpAvatar();
		$LoadTemplates = new \Indeed\Uap\LoadTemplates();
		if ( class_exists( 'WP_REST_Controller' ) ){
				$uapRestAPI = new \Indeed\Uap\RestAPI();
		}
		$wpmlActions = new \Indeed\Uap\WPMLActions();
		$PayToBecomeAffiliate = new \Indeed\Uap\PayToBecomeAffiliate();

		/// elementor
		$elementorIntegration = new \Indeed\Uap\RegisterElementorWidgets();
		$gutenbergIntegration = new \Indeed\Uap\GutenbergEditorIntegration();
		$UploadFilesSecurity = new \Indeed\Uap\UploadFilesSecurity();

		$uapGeneralActions = new \Indeed\Uap\GeneralActions();

		$ElCheck = new \Indeed\Uap\ElCheck();
		$WooSpecificReferralRates = new \Indeed\Uap\WooSpecificReferralRates();
		$WooCategoryReferralRates = new \Indeed\Uap\WooCategoryReferralRates();

		/// BP CUSTOM MENU ITEM
		$temp = $indeed_db->return_settings_from_wp_option('bp_account_page');
		if (!empty($temp['uap_bp_account_page_enable']) && is_plugin_active('buddypress/bp-loader.php')){
			require UAP_PATH . 'classes/BuddyPressCustomMenuItem.class.php';
			$bp_menu = new BuddyPressCustomMenuItem();
		}


		// dataTables
		$uapDataTable = new \Indeed\Uap\Admin\Datatable();

		// nonce
		add_action( 'admin_head', 'UAP_Main::adminNonce' );
		add_action( 'admin_head', 'UAP_Main::uapStyleForTopNotifications' );
		add_action( 'wp_head', 'UAP_Main::publicNonce' );

	}

	public static function createBaseObject()
	{
			//if ( defined('DOING_AJAX') && DOING_AJAX ){
					//return;
			//}
			if ( is_admin() && current_user_can( 'manage_options' ) && !defined('DOING_AJAX')){
				/// ADMIN
				require_once UAP_PATH . 'admin/UapMainAdmin.class.php';
				$uap_main_object = new UapMainAdmin();
			} else{
				/// PUBLIC
				require_once UAP_PATH . 'public/UapMainPublic.class.php';
				$uap_main_object = new UapMainPublic();
			}

	}

	public static function onActivation()
	{
			global $indeed_db;
			include UAP_PATH . 'classes/UapCronJobs.class.php';
			$crons = new \UapCronJobs();
			$crons->registerCrons();
			$installTime = get_option( 'uap_install_time', false );
			if ( $installTime === false ){
					update_option( 'uap_install_time', time() );
			}
			// since 8.5 - wizard
			if ( $indeed_db->affiliateTableExists() ){
					// table exists so we'll not run the wizard
					update_option( 'uap_wizard_complete', -1 );
			} else {
					// fresh install. run the wizard
					update_option( 'uap_wizard_complete', 0 );
			}
			// end of since 8.5 - wizard
	}

	public static function uap_gate(){
		/*
		 * @param none
		 * @return none
		 */
		 if (!empty($_GET['uap_act'])){
			$action = sanitize_text_field($_GET['uap_act']);
		 } else {
		 	global $wp_query;
			if (!empty($wp_query)){
				 $action = get_query_var('uap_act');
			}
		 }
		 if (!empty($action)){
		 	$no_load = TRUE;
		 	switch ($action){
				case 'stripe_payout':
					require_once UAP_PATH . 'public/stripe-webhook.php';
					break;
				case 'password_reset':
					require_once UAP_PATH . 'public/arrive.php';
					break;
				case 'email_verification':
					require_once UAP_PATH . 'public/arrive.php';
					break;
				case 'migrate':
					$params = array(
								'serviceType'   => isset($_POST['service_type']) ? uap_sanitize_array($_POST['service_type']) : false,
								'entityType'    => isset($_POST['entity_type']) ? uap_sanitize_array($_POST['entity_type']) : false,
								'offset'        => isset($_POST['offset']) ? uap_sanitize_array($_POST['offset']) : 0,
								'assignRank'    => isset($_POST['assignRank']) ? uap_sanitize_array($_POST['assignRank']) : false
					);
					$object = new \Indeed\Uap\Migration\BaseMigration();
					$object->run($params);
					break;
				case 'tracking':
						$type = isset($_GET['type']) ? $_GET['type'] : '';
						if (empty($type)){
								return;
						}
						switch ($type){
								case 'cpm':
									$object = new \Indeed\Uap\CPM($_GET['affiliate']);
									break;
						}
						break;
				case 'stripe_v3_auth':
					// authentificate user in stripe. processing response.
					$stripe = new \Indeed\Uap\PayoutStripeV3();
					$stripe->authAffiliate();
					$accountPageId = get_option( 'uap_general_user_page' );
					if ( $accountPageId ){
							$redirect = get_permalink( $accountPageId );
					}
					if ( !empty( $redirect ) ){
							// redirect to account page - payment settings
							$redirect = add_query_arg( 'uap_aff_subtab', 'payments_settings', $redirect );
					} else {
							// redirect home. no account page set properly
							$redirect = get_home_url();
					}
					wp_safe_redirect( $redirect );
					exit;
					break;
				case 'stripe_v3_webhook':
					$stripe = new \Indeed\Uap\PayoutStripeV3();
					$stripe->webhook();
					break;
				default:
					$home = get_home_url();
					wp_safe_redirect($home);
					exit;
		 	}
		 }
	}

	public static function do_add_rewrite_endpoint_uap(){
		add_rewrite_endpoint('uap', EP_ROOT | EP_PAGES );
	}

	public static function uap_load_language(){
		/*
		 * @param none
		 * @return none
		 */
		load_plugin_textdomain( 'uap', false, dirname(plugin_basename(__FILE__)) . '/languages/' );
	}

	public static function uap_update_passowrd_filter($return, $user_data){
		/*
		 * @param return - boolean, $user_data - array
		 * @return boolean
		 */
		if (isset($user_data['ID']) && $return){
			$sent_mail = uap_send_user_notifications($user_data['ID'], 'change_password');
			if ($sent_mail){
				return FALSE;
			}
		}
		return $return;
	}

	public static function edit_query_vars($vars){
		$vars[] = "uap";
		return $vars;
	}

	public static function uap_add_custom_top_menu_dashboard(){
		/*
		 * =============== DASHBOARD TOP MENU =================
		 * @param none
		 * @return none
		 */

		global $wp_admin_bar;
		if (!is_admin() || !is_admin_bar_showing()){
			return;
		}

		/// PARENT
		$wp_admin_bar->add_menu(array(
					'id'    => 'uap_dashboard_menu',
					'title' => 'Ultimate Affiliate Pro',
					'href'  => admin_url( 'admin.php?page=ultimate_affiliates_pro' ),
					'meta'  => array(),
		));

		///ITEMS
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_pages', 'title'=>esc_html__('Affiliate Pages', 'uap'), 'href'=>'#', 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_showcases', 'title'=>esc_html__('Showcases', 'uap'), 'href'=>'#', 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_magic_feat', 'title'=>esc_html__('Extensions', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_ranks', 'title'=>esc_html__('Ranks', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=ranks'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_offers', 'title'=>esc_html__('Product Rates', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=offers'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_visits', 'title'=>esc_html__('Clicks', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=visits'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_notifications', 'title'=>esc_html__('Email Notifications', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=notifications'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_shortcodes', 'title'=>esc_html__('Shortcodes', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=shortcodes'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap_dashboard_menu_general', 'title'=>esc_html__('General Settings', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap-pro-addons-link', 'title'=>'Pro AddOns', 'href'=>'https://ultimateaffiliate.pro/pro-addons/', 'meta'=>array('target' => '_blank')));
		if (!uap_is_ihc_active()){
			$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu', 'id'=>'uap-ihc-link', 'title'=>'Ultimate Membership Pro', 'href'=>'https://ultimatemembershippro.com', 'meta'=>array('target' => '_blank')));
		}

		/// SHOWCASES
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu_showcases', 'id'=>'uap_dashboard_menu_showcases_rf', 'title'=>esc_html__('Registration Form', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=register'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu_showcases', 'id'=>'uap_dashboard_menu_showcases_lf', 'title'=>esc_html__('Login Form', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=login'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu_showcases', 'id'=>'uap_dashboard_menu_showcases_ta', 'title'=>esc_html__('Top Affiliates', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=top_affiliates'), 'meta'=>array()));
		$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu_showcases', 'id'=>'uap_dashboard_menu_showcases_ap', 'title'=>esc_html__('Affiliate Portal', 'uap'), 'href'=>admin_url('admin.php?page=ultimate_affiliates_pro&tab=account_page'), 'meta'=>array()));

		/// DEFAULT PAGES
		$array = array(
							'uap_general_login_default_page' => esc_html__('Affiliate Login', 'uap'),
							'uap_general_register_default_page'=> esc_html__('Affiliate Registration', 'uap'),
							'uap_general_lost_pass_page' => esc_html__('Lost Password', 'uap'),
							'uap_general_logout_page' => esc_html__('LogOut', 'uap'),
							'uap_general_user_page' => esc_html__('Affiliate Portal', 'uap'),
							'uap_general_tos_page' => esc_html__('TOS', 'uap'),
		);
		foreach ($array as $k=>$v){
			$page = get_option($k);
			$permalink = get_permalink($page);
			if ($permalink){
				$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu_pages', 'id'=>'uap_dashboard_menu_pages_' . $k, 'title'=>$v, 'href'=>$permalink, 'meta'=>array('target'=>'_blank')));
			}
		}

		//. MAGIC FEATURES
		global $indeed_db;
		$array = $indeed_db->get_magic_feat_item_list();
		if ($array){
			foreach ($array as $key=>$item){
				$wp_admin_bar->add_menu(array('parent'=>'uap_dashboard_menu_magic_feat', 'id'=>'uap_dashboard_menu_magic_feat_' . $key, 'title'=>$item['label'], 'href'=>$item['link'], 'meta'=>array()));
			}
		}
	}

	public static function add_custom_admin_bar_item(){
			/*
			 * @param none
			 * @return none
			 */
			 if ( (int)get_option( 'uap_wizard_complete', -1 ) === 0 ){
				 		return;
			 }
		global $wp_admin_bar;
		if (!is_admin() || !is_admin_bar_showing()){
			return;
		}
		global $wpdb, $indeed_db;
			if (!empty($_GET['page']) && $_GET['page']=='ultimate_affiliates_pro' && !empty($_GET['tab'])){
				switch ($_GET['tab']){
					case 'affiliates':
						$indeed_db->reset_dashboard_notification('affiliates');
						break;
					case 'referrals':
						$indeed_db->reset_dashboard_notification('referrals');
						break;
				}
			}



			$admin_workflow = $indeed_db->return_settings_from_wp_option('general-admin_workflow');

			if (!$admin_workflow['uap_admin_workflow_dashboard_notifications']){
				return;
			}

			$new_affiliates = $indeed_db->get_dashboard_notification_value('affiliates');
			$new_referrals = $indeed_db->get_dashboard_notification_value('referrals');

			if (!is_admin() || ! is_admin_bar_showing()){
				return;
			}

			$wp_admin_bar->add_menu( array(
				'id'    => 'uap_affiliates',
				'title' => '<span class="uap-top-bar-count">' . $new_affiliates . '</span>New Affiliates',
				'href'  => admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates'),
				'meta'  => array ( 'class' => 'uap-top-notf-admin-menu-bar' )
			));

			$wp_admin_bar->add_menu( array(
				'id'    => 'uap_referrals',
				'title' => '<span class="uap-top-bar-count">' . $new_referrals . '</span>New Referrals',
				'href'  => admin_url('admin.php?page=ultimate_affiliates_pro&tab=referrals'),
				'meta'  => array ( 'class' => 'uap-top-notf-admin-menu-bar' )
			));

	}

	public static function uapStyleForTopNotifications(){
		$custom_css = '
		.uap-top-bar-count{
				display: inline-block !important;
				vertical-align: top !important;
			padding: 2px 7px !important;
				background-color: #d54e21 !important;
				color: #fff !important;
				font-size: 9px !important;
				line-height: 17px !important;
				font-weight: 600 !important;
				margin: 5px !important;
				vertical-align: top !important;
				-webkit-border-radius: 10px !important;
				border-radius: 10px !important;
				z-index: 26 !important;
		}
		li#wp-admin-bar-uap-ihc-link{
	    display: block;
	     width: 100%;
	     background-color: #ed5a4c !important;
	     color: #fff !important;
	     padding: 5px 0 5px 4px;
	  }
		li#wp-admin-bar-uap-ihc-link a{
			color:#fff !important;
		}
		li.uap-addons-link-wrapp, li#wp-admin-bar-uap-pro-addons-link{
			display: block;
	     width: 100%;
	     background-color: #eee !important;
	     color: #fff !important;
	     padding: 5px 0 5px 4px;
			 background-color: #284051 !important;
	     padding: 4px 0 4px 0px !important;
		}
		li.uap-addons-link-wrapp a, li#wp-admin-bar-uap-pro-addons-link a{
			color:#333 !important;
			color: #53E2F3 !important;
		}';

		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', stripslashes($custom_css) );
	}


	/**
	 * @param none
	 * @return float
	 */
	public static function get_plugin_ver(){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_data = get_plugin_data( UAP_PATH . 'indeed-affiliate-pro.php', false, false);
		return $plugin_data['Version'];
	}


	public static function uap_authenticate_filter($user_data=null, $username='', $password=''){
			if ($user_data==null){
				 return $user_data;
			}
			if (is_object($user_data) && !empty($user_data->roles) && in_array('pending_user', $user_data->roles)){
				$errors = new WP_Error();
        		$errors->add('title_error', 'Pending User');
        		return $errors;
			}
			return $user_data;
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function adminNonce()
	{
			$nonce = wp_create_nonce( 'uapAdminNonce' );
			echo esc_uap_content("<meta name='uap-admin-token' content='$nonce'>");
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function publicNonce()
	{
			$nonce = wp_create_nonce( 'uapPublicNonce' );
	    echo esc_uap_content("<meta name='uap-token' content='$nonce'>");
	}

	public static function check_user_privilege()
	{
		if ( !is_admin() ){
				return;
		}
		if ( defined('DOING_AJAX') && DOING_AJAX ){
				return;
		}
		$uid = get_current_user_id();
		$role = '';
		$user = new WP_User( $uid );
		$public_home = home_url();
		if ( !is_super_admin( $uid ) && $user && !empty($user->roles) && !empty($user->roles[0]) && !in_array( 'administrator', $user->roles )){
		 $allowed_roles = get_option('uap_dashboard_allowed_roles');
		 if ($allowed_roles){
			 $roles = explode(',', $allowed_roles);
			 $show = false;
			 foreach ( $roles as $role ){
					 if ( !empty( $role ) && !empty( $user->roles ) && in_array( $role, $user->roles ) ){
						 $show = true;
					 }
			 }

			 if ( !$show ){
				 wp_redirect(home_url());
				 exit();
			 }

		 } else {
			 wp_redirect($public_home);
			 exit();
		 }
	 }
	}

	public static function diviGrabImage( $bool=true )
	{
			return false;
	}

}

UAP_Main::run();
