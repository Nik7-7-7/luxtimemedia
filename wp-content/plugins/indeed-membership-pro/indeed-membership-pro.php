<?php
/*
Plugin Name: Indeed Ultimate Membership Pro
Plugin URI: https://ultimatemembershippro.com/
Description: The most complete and easy to use Membership Plugin, ready to allow or restrict your content, Page for certain Users.
Version: 12.8
Author: WPIndeed Development
Author URI: https://www.wpindeed.com
Text Domain: ihc
Domain Path: /languages

@package        Indeed Ultimate Membership Pro
@author         WPIndeed Development
*/
update_option( 'ihc_license_set', 1 );
update_option( 'ihc_envato_code', 'activated' );
update_option( md5('ihclsm'), 0 );
update_option( substr( md5('umpsl'), 0, 10 ), 0 );
///setting the paths
if (!defined('IHC_PATH')){
	define('IHC_PATH', plugin_dir_path(__FILE__));
}
if (!defined('IHC_URL')){
	define('IHC_URL', plugin_dir_url(__FILE__));
}
if (!defined('IHC_PROTOCOL')){
	if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
		define('IHC_PROTOCOL', 'https://');
	} else {
		define('IHC_PROTOCOL', 'http://');
	}
}
if ( !defined( 'IUMP_VEND' ) ){
		define( 'IUMP_VEND', [
																'evt'			=> 'Envato Marketplace'
		]);
}
if (!defined('IHC_DEV')){
	define('IHC_DEV', "WPIndeed");
}
//LANGUAGES
add_action('init', 'ihc_load_language');
function ihc_load_language(){
	load_plugin_textdomain( 'ihc', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}

require_once IHC_PATH . 'utilities.php';
require_once IHC_PATH  . 'autoload.php';
require_once IHC_PATH . 'classes/Ihc_Db.class.php';


if ( is_admin()  ){
			$ihcDatatable = new \Indeed\Ihc\Admin\Datatable();
			//go to admin
			require_once IHC_PATH . 'admin/Main.php';

			// let's make ump ready for elementor edit page
			if (!function_exists('is_plugin_active')){
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( is_plugin_active( 'elementor/elementor.php' ) && isset( $_GET['action'] ) && $_GET['action'] === 'elementor' ){
				require_once IHC_PATH . 'public/shortcodes.php';
			}
			// end of elementor compatibility

} else {
			//go to public
			require_once IHC_PATH . 'public/Main.php';
}

require_once IHC_PATH  . 'public/static-data.php';

/************************ MODULES *************************/

/// WooCommerce
require_once IHC_PATH . 'classes/Ihc_Custom_Woo_Endpoint.class.php';
$ihc_woo_object = new Ihc_Custom_Woo_Endpoint();

/// BuddyPress
require_once IHC_PATH . 'classes/Ihc_Custom_BP_Endpoint.class.php';
$ihc_bp_object = new Ihc_Custom_BP_Endpoint();

/// Woo payment integration
require_once IHC_PATH . 'classes/IhcPaymentViaWoo.class.php';
$IhcPaymentViaWoo = new IhcPaymentViaWoo();

require_once IHC_PATH . 'classes/Ihc_Workflow_Restrictions.class.php';
$Ihc_Workflow_Restrictions = new Ihc_Workflow_Restrictions();

require_once IHC_PATH . 'classes/Ihc_User_Logs.class.php';
$Ihc_User_Logs = new Ihc_User_Logs();

require_once IHC_PATH . 'classes/IhcWooProductCustomPrices.class.php';
$IhcWooProductCustomPrices = new IhcWooProductCustomPrices();

require_once IHC_PATH . 'classes/IhcUserSitesActions.class.php';
$IhcUserSitesActions = new IhcUserSitesActions();

$ihcOldLevels = new \Indeed\Ihc\Levels();



/// register custom redirect
if (get_option('ihc_register_redirects_by_level_enable')){
		require_once IHC_PATH . 'classes/Ihc_Register_Redirects.class.php';
		$Ihc_Register_Redirects = new Ihc_Register_Redirects();
}

require_once IHC_PATH . 'classes/Ihc_Actions_On_Events.php';
$Ihc_Actions_On_Events = new Ihc_Actions_On_Events();

/// GDPR
$Ihc_GDPR = new \Indeed\Ihc\Ihc_GDPR();

/// Zapier
$ihcZapier = new \Indeed\Ihc\Services\ZapierSendData();

/// infusionSoft - modified since version 12.5
$iSViaRESTAPI = get_option('ihc_infusionSoft_keap_api_key', false );
if ( $iSViaRESTAPI === false || $iSViaRESTAPI === null || $iSViaRESTAPI === '' ){
		$ihcInfusionSoft = new \Indeed\Ihc\Services\InfusionSoft();
} else {
		$InfusionSoftREST = new \Indeed\Ihc\Services\InfusionSoftREST();
}


/// kissmetrics
$ihcKissmetrics = new \Indeed\Ihc\Services\Kissmetrics();

$ihcFilters = new \Indeed\Ihc\Filters();

$WPMLActions = new \Indeed\Ihc\Services\WPMLActions();

require_once IHC_PATH . 'classes/RegisterElementorWidgets.php';

$ihcGutenbergEditorIntegration = new \Indeed\Ihc\Services\GutenbergEditorIntegration();

$ihcGeneralActions = new \Indeed\Ihc\GeneralActions();

$ihcWpLoginCustomCss = new \Indeed\Ihc\WpLogin();

$ihcElCheck = new \Indeed\Ihc\Services\ElCheck();

$UserSubscriptionsEvents = new \Indeed\Ihc\UserSubscriptionsEvents();

$ihcCheckout = new \Indeed\Ihc\Checkout();

$NotificationTriggers = new \Indeed\Ihc\NotificationTriggers();

$ihcCrons = new \Indeed\Ihc\Crons();

$ihcAccountPageShortcodes = new \Indeed\Ihc\AccountPageShortcodes();

$prorate = new \Indeed\Ihc\ProrateMembership();

// change password - since version 11.1
$ihcChangePassword = new \Indeed\Ihc\ChangePassword();

// deprecated since version 11.3
//$ihcRegistrationEvents = new \Indeed\Ihc\RegistrationEvents();

// since version 11.3
$ihcRegisterForm = new \Indeed\Ihc\RegisterForm();

// since version 11.7
$ihcLoginForm = new \Indeed\Ihc\LoginForm();

// pretty links
$iumpPrettyLinks = new \Indeed\Ihc\PrettyLinks();

// tracking - since version 12.0
$iumpTracking = new \Indeed\Ihc\Tracking();

/******************** END MODULES **************************/

//on activating the plugin
function ihc_initiate_plugin(){
	/*
	 * @param none
	 * @return none
	 */
	/// IF PHP >5.3 don't activate plugin
	if (defined('PHP_VERSION') && version_compare(PHP_VERSION, 5.6, '<')){
		deactivate_plugins(plugin_basename( __FILE__ ));
		die('Ultimate Membership Pro requires PHP version greater than 5.6, Your current PHP is v.' . PHP_VERSION . ' . Update Your PHP and try again!');
	}

	// since 12.1 - wizard
	if ( \Ihc_Db::membershipTableExists() ){
			// table exists so we'll not run the wizard
	    update_option( 'iump_wizard_complete', -1 );
	} else {
			// fresh install. run the wizard
			update_option( 'iump_wizard_complete', 0 );
	}
	// end of since 12.1 - wizard

	if ( get_option('iump_install_time') === false ){
			update_option( 'iump_install_time', time() );
	}

	require_once IHC_PATH . 'classes/Ihc_Db.class.php';
	Ihc_Db::add_new_role();
	Ihc_Db::save_settings_into_db();
	Ihc_Db::create_tables();
	//Ihc_Db::create_notifications(); // deprecated since version 12.1
	Ihc_Db::create_default_pages();
	Ihc_Db::create_default_redirects();
	Ihc_Db::create_extra_redirects();
	Ihc_Db::create_default_lockers();
	//Ihc_Db::create_demo_levels(); // deprecated since version 12.1

	$WPMLActions = new \Indeed\Ihc\Services\WPMLActions();
	$WPMLActions->registerNotifications();
	$WPMLActions->registerTaxes();

	// register crons
	$crons = new \Indeed\Ihc\Crons();
	$crons->registerCrons();
}
register_activation_hook( __FILE__, 'ihc_initiate_plugin' );


// Deprecated since version 10.10
add_action('init', 'ihc_check_plugin_version');
function ihc_check_plugin_version(){
	define('IHCACTIVATEDMODE', true);
}



function ihc_admin_global_notice(){
	if (current_user_can('manage_options')){
		echo ihc_inside_dashboard_error_license(TRUE);
	}
}
add_action('admin_notices', 'ihc_admin_global_notice');


//delete attachment ajax
add_action('wp_ajax_nopriv_ihc_delete_attachment_ajax_action', 'ihc_delete_attachment_ajax_action');
add_action('wp_ajax_ihc_delete_attachment_ajax_action', 'ihc_delete_attachment_ajax_action');
function ihc_delete_attachment_ajax_action()
{
		if ( current_user_can('manage_options') && is_admin() ){
			if ( !ihcAdminVerifyNonce() ){
					die;
			}
		} else {
			if ( !ihcPublicVerifyNonce() ){
					die;
			}
		}

		$uid = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : 0;
		$field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
		$attachment_id = isset($_POST['attachemnt_id']) ? sanitize_text_field($_POST['attachemnt_id']) : 0;
		if (function_exists('is_user_logged_in') && is_user_logged_in()){
				$current_user = wp_get_current_user();
			  if ( !empty($uid) && $uid == $current_user->ID ){
						/// registered users
						if (!empty($attachment_id)){
								$verify_attachment_id  = get_user_meta($uid, $field_name, TRUE);
								if ($verify_attachment_id==$attachment_id){
										wp_delete_attachment($attachment_id, TRUE);
										update_user_meta($uid, $field_name, '');
										echo 0;
										die();
								}
						}
			  } else if (current_user_can('manage_options')){
					 /// ADMIN, no extra checks
					 wp_delete_attachment($attachment_id, TRUE);
					 update_user_meta($uid, $field_name, '');
				}
		} else if ($uid==-1){
				/// unregistered user
				$hash_from_user = isset($_POST['h']) ? sanitize_text_field($_POST['h']) : '';
				$attachment_url = wp_get_attachment_url($attachment_id);
				$attachment_hash = md5($attachment_url);
				if (empty($hash_from_user) || empty($attachment_hash) || $hash_from_user!==$attachment_hash){
						echo 1;die;
				} else {
						wp_delete_attachment($attachment_id, TRUE);
						echo 0;die;
				}
		}
		echo 1;
		die();
}


add_action("wp_ajax_nopriv_ihc_ap_reset_custom_banner", "ihc_ap_reset_custom_banner");
add_action('wp_ajax_ihc_ap_reset_custom_banner', 'ihc_ap_reset_custom_banner');
function ihc_ap_reset_custom_banner()
{
		if ( !ihcPublicVerifyNonce() ){
				die;
		}
		global $current_user;
		$uid = isset($current_user->ID) ? $current_user->ID : 0;
		if (empty($uid)){
				die;
		}
		$banner = isset($_POST['oldBanner']) ? sanitize_text_field($_POST['oldBanner']) : '';
		if (empty($banner)){
				die;
		}
		update_user_meta($uid, 'ihc_user_custom_banner_src', $banner);
		die;
}



add_action("wp_ajax_nopriv_ihc_check_unique_value_field_register", "ihc_check_unique_value_field_register");
add_action('wp_ajax_ihc_check_unique_value_field_register', 'ihc_check_unique_value_field_register');
function ihc_check_unique_value_field_register()
{
	/*
	 * @param none
	 * @return none
	 */
	 if ( !ihcPublicVerifyNonce() ){
  			echo 0;
  			die;
  	}
	$meta_key = (empty($_REQUEST['meta_key'])) ? '' : sanitize_text_field($_REQUEST['meta_key']);
	$meta_value = (empty($_REQUEST['meta_value'])) ? '' : sanitize_text_field($_REQUEST['meta_value']);
	if (ihc_meta_value_exists($meta_key, $meta_value)){
		$unique_msg = get_option('ihc_register_unique_value_exists');
		if (empty($unique_msg)){
			echo esc_html__('This value already exists.', 'ihc');
			die;
		}
		echo esc_html($unique_msg);
		die;
	}
	echo 1;
	die();
}

add_action("wp_ajax_nopriv_ihc_get_cart_via_ajax", "ihc_get_cart_via_ajax");
add_action('wp_ajax_ihc_get_cart_via_ajax', 'ihc_get_cart_via_ajax');
function ihc_get_cart_via_ajax()
{
	/*
	 * deprecated
	 * @param none
	 * @return none
	 */
	if ( !ihcPublicVerifyNonce() ){
  		die;
  }
	if ( ihcCheckCheckoutSetup() ){
			die;
	}
	$currency = get_option("ihc_currency");
 	$data['template'] = '';
	$lid = sanitize_text_field((isset($_REQUEST['lid'])) ? $_REQUEST['lid'] : 0);
	$country = empty($_REQUEST['country']) ? '' : sanitize_text_field($_REQUEST['country']);
	$state = (isset($_REQUEST['state'])) ? sanitize_text_field($_REQUEST['state']) : '';
	$paymentGateway = isset($_POST['payment_type']) ? sanitize_text_field($_POST['payment_type']) : '';

	$trialObject = new Indeed\Ihc\Db\TrialData();
	$trialObject->setVariable('lid', $lid)
							->setVariable('currency', $currency)
							->setVariable('country', $country)
							->setVariable('state', $state)
							->run();

 	$level_data = ihc_get_level_by_id($lid);
	$data['level_label'] = ihc_correct_text($level_data['label']);
	$data['final_price'] = (isset($level_data['price'])) ? $level_data['price'] : '';

	/*************************** DYNAMIC PRICE ***************************/
	if (isset($_REQUEST['a']) && $_REQUEST['a']!=-1 && ihc_is_magic_feat_active('level_dynamic_price')){
		if (ihc_check_dynamic_price_from_user($lid, sanitize_text_field($_REQUEST['a']) )) {
			$data['final_price'] = sanitize_text_field($_REQUEST['a']);
		}
	}
	/*************************** DYNAMIC PRICE ***************************/

	/// LEVEL PRICE
	if (is_array($level_data) && isset($level_data['payment_type']) && $level_data['payment_type']=='payment'){
		$data['level_price'] = ihc_format_price_and_currency($currency, $data['final_price']);
	} else {
		$data['level_price'] = esc_html__("Free", "ihc");
		$data['final_price'] = 0;
	}

	/// COUPON
	if (!empty($_REQUEST['coupon'])){
		$coupon_data = ihc_check_coupon(sanitize_text_field($_REQUEST['coupon']), $lid);
		if ($coupon_data){
			if ($coupon_data['reccuring']==0 && $paymentGateway=='stripe'){
				$finalPrice = ihc_coupon_return_price_after_decrease($data['final_price'], $coupon_data, FALSE);
				$data['discount_value'] = ihc_get_discount_value($data['final_price'], $coupon_data);
				$data['discount_value']	 = '-' . ihc_format_price_and_currency($currency, $data['discount_value']);
				if ( (int)$finalPrice==0  && ihc_is_level_reccuring( $lid )){

				} else if ( !ihc_is_level_reccuring( $lid ) ){
						$data['final_price'] = $finalPrice;
				}
			}elseif ($coupon_data['reccuring']==0 && $trialObject->isTrialActive()){
					//recurring with trial
					$initialTrialPrice = $trialObject->getInitialTrialPrice(true);
					if(isset($initialTrialPrice) && $initialTrialPrice > 0){
						$finalTrialPrice = ihc_coupon_return_price_after_decrease($initialTrialPrice , $coupon_data, FALSE);

						$data['trial_price'] = $finalTrialPrice;
						$trialObject->setVariable('trialPrice',$finalTrialPrice);
						$trialObject->setTaxesAfterDiscount();

						$data['trial_discount_value'] = ihc_get_discount_value($initialTrialPrice, $coupon_data);
						$data['trial_discount_value']	 = '-' . ihc_format_price_and_currency($currency, $data['trial_discount_value']);
					}

			}else {
					if($trialObject->isTrialActive()){
						$initialTrialPrice = $trialObject->getInitialTrialPrice(true);
						if(isset($initialTrialPrice) && $initialTrialPrice > 0){
							$finalTrialPrice = ihc_coupon_return_price_after_decrease($initialTrialPrice , $coupon_data, FALSE);

							$data['trial_price'] = $finalTrialPrice;
							$trialObject->setVariable('trialPrice',$finalTrialPrice);
							$trialObject->setTaxesAfterDiscount();

							$data['trial_discount_value'] = ihc_get_discount_value($initialTrialPrice, $coupon_data);
							$data['trial_discount_value']	 = '-' . ihc_format_price_and_currency($currency, $data['trial_discount_value']);
						}
					}
					$data['discount_value'] = ihc_get_discount_value($data['final_price'], $coupon_data);
					$data['final_price'] = ihc_coupon_return_price_after_decrease($data['final_price'], $coupon_data, FALSE);
					$data['discount_value']	 = '-' . ihc_format_price_and_currency($currency, $data['discount_value']);
			}
		}
	}

	/// TAXES
	if (!empty($data['final_price']) && ihc_is_magic_feat_active('taxes')){
		$taxes = ihc_get_taxes_for_amount_by_country($country, $state, $data['final_price']);
		/// view tax value
		$total_taxes = $taxes['total'];
		$data['total_taxes'] = $taxes['print_total'];
		$data['taxes_details'] = $taxes['items'];
		$data['print_taxes'] = get_option('ihc_show_taxes');
	}

	/// FINAL PRICE
	if (isset($total_taxes)){
		$data['final_price'] += $total_taxes;
	}
	if (isset($data['final_price'])){
		$data['price_number'] = $data['final_price'];
		$data['final_price'] = ihc_format_price_and_currency($currency, $data['final_price']);
	}

	$data['show_full_cart'] = get_option("ihc_register_show_level_price");

	$fullPath = IHC_PATH . 'public/views/cart.php';
	$searchFilename = 'cart.php';
	$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

	ob_start();
	require $template;
	$str = ob_get_contents();
	ob_end_clean();
	echo esc_ump_content($str);
	die();
}

add_action('admin_bar_menu', 'ihc_add_custom_admin_bar_item', 998);
function ihc_add_custom_admin_bar_item(){
	/*
	 * @param none
	 * @return none
	 */
	global $wp_admin_bar, $wpdb;
	if (!is_admin() || !is_admin_bar_showing()){
		return;
	}
	if (!empty($_GET['page']) && $_GET['page']=='ihc_manage' && !empty($_GET['tab'])){
		switch ($_GET['tab']){
			case 'users':
				Ihc_Db::reset_dashboard_notification('users');
				break;
			case 'orders':
				Ihc_Db::reset_dashboard_notification('orders');
				break;
		}
	}

	if (!is_admin() || !is_admin_bar_showing() || get_option('ihc_admin_workflow_dashboard_notifications')==0){
		return;
	}
	$new_users = Ihc_Db::get_dashboard_notification_value('users');
	$new_orders = Ihc_Db::get_dashboard_notification_value('orders');

	$wp_admin_bar->add_menu(array(
				'id'    => 'ihc_users',
				'title' => '<span class="ihc-top-bar-count">' . $new_users . '</span>' . esc_html__('New Members', 'ihc'),
				'href'  => admin_url('admin.php?page=ihc_manage&tab=users'),
				'meta'  => array('class' => 'ihc-top-notf-admin-menu-bar'),
	));
	$wp_admin_bar->add_menu(array(
				'id'    => 'ihc_orders',
				'title' => '<span class="ihc-top-bar-count">' . $new_orders . '</span>' . esc_html__('New Orders', 'ihc'),
				'href'  => admin_url('admin.php?page=ihc_manage&tab=orders'),
				'meta'  => array('class' => 'ihc-top-notf-admin-menu-bar'),
	));
}

add_action('admin_bar_menu', 'ihc_add_custom_top_menu_dashboard', 997);
function ihc_add_custom_top_menu_dashboard(){
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
				'id'    => 'ihc_dashboard_menu',
				'title' => 'Ultimate Membership Pro',
				'href'  => admin_url('admin.php?page=ihc_manage'),
				'meta'  => array(),
	));
	///ITEMS
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc_dashboard_menu_pages', 'title'=>esc_html__('Membership Pages', 'ihc'), 'href'=>'#', 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc_dashboard_menu_showcases', 'title'=>esc_html__('Showcases', 'ihc'), 'href'=>'#', 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc_dashboard_menu_payment_gateways', 'title'=>esc_html__('Payment Gateways', 'ihc'), 'href'=>'#', 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'magic_feat', 'title'=>esc_html__('Extensions', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=magic_feat'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc_dashboard_menu_levels', 'title'=>esc_html__('Memberships', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=levels'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc_dashboard_menu_notifications', 'title'=>esc_html__('Email Notifications', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=notifications'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc_dashboard_menu_shortcodes', 'title'=>esc_html__('Shortcodes', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=user_shortcodes'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'block_url', 'title'=>esc_html__('Content Access Rules', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=block_url'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'general', 'title'=>esc_html__('General Settings', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=general'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc-pro-addons-link', 'title'=>'Pro AddOns', 'href'=>'https://ultimatemembershippro.com/pro-addons/', 'meta'=>array('target' => '_blank')));
	if(!ihc_is_uap_active()){
		$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu', 'id'=>'ihc-uap-link', 'title'=>'Ultimate Affiliate Pro', 'href'=>'https://wpindeed.com/ultimate-affiliate-pro', 'meta'=>array('target' => '_blank')));
	}

	/// SHOWCASES
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_rf', 'title'=>esc_html__('Registration Form', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=register'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_lf', 'title'=>esc_html__('Login Form', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=login'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_sp', 'title'=>esc_html__('Subscriptions Plan', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=subscription_plan'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_cp', 'title'=>esc_html__('Checkout Page', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=checkout'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_ap', 'title'=>esc_html__('Member Portal', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=account_page'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_pf', 'title'=>esc_html__('Profile Form', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=profile-form'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_st', 'title'=>esc_html__('Subscriptions Table', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=manage_subscription_table'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_ot', 'title'=>esc_html__('Orders Table', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=manage_order_table'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_ty', 'title'=>esc_html__('Thank You Page', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=thank-you-page'), 'meta'=>array()));
	$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_showcases', 'id'=>'ihc_dashboard_menu_showcases_lu', 'title'=>esc_html__('Members Directory', 'ihc'), 'href'=>admin_url('admin.php?page=ihc_manage&tab=listing_users'), 'meta'=>array()));

	/// PAYMENT GATEWAYS
	$gateways = ihc_get_active_payments_services();
	foreach ($gateways as $k=>$v){
		$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_payment_gateways', 'id'=>'ihc_dashboard_menu_gateway_' . $k, 'title'=>$v, 'href'=>admin_url('admin.php?page=ihc_manage&tab=payment_settings&subtab=' . $k), 'meta'=>array()));
	}


	$magicFeatures = ihcGetListOfMagicFeatures();
	foreach ( $magicFeatures as $k => $magicFeature ){
			$wp_admin_bar->add_menu(array('parent'=>'magic_feat', 'id'=>'magic_feat_' . $k, 'title'=> $magicFeature['label'], 'href'=> $magicFeature['link'], 'meta'=>array()));
	}


	/// DEFAULT PAGES
	$array = array(
					'ihc_general_login_default_page' 				=> esc_html__('Login Page', 'ihc'),
					'ihc_general_register_default_page' 		=> esc_html__('SignUp Page', 'ihc'),
					'ihc_subscription_plan_page' 						=> esc_html__('Subscription Plan', 'ihc'),
					'ihc_checkout_page' 										=> esc_html__('Checkout Page', 'ihc'),
					'ihc_general_user_page' 								=> esc_html__('Member Portal', 'ihc'),
					'ihc_general_lost_pass_page' 						=> esc_html__('Lost Password', 'ihc'),
					'ihc_thank_you_page' 										=> esc_html__('Thank You Page', 'ihc'),
					'ihc_general_logout_page' 							=> esc_html__('LogOut', 'ihc'),
					'ihc_general_tos_page' 									=> esc_html__('TOS', 'ihc'),
	);
	foreach ($array as $k=>$v){
		$page = get_option($k);
		$permalink = get_permalink($page);
		if ($permalink){
			$wp_admin_bar->add_menu(array('parent'=>'ihc_dashboard_menu_pages', 'id'=>'ihc_dashboard_menu_pages_' . $k, 'title'=>$v, 'href'=>$permalink, 'meta'=>array('target'=>'_blank')));
		}
	}
}

//// ACTIONS

add_action('wsl_hook_process_login_before_wp_safe_redirect', 'ihc_wp_social_login_do_redirect', 99, 0);
function ihc_wp_social_login_do_redirect(){
	/*
	 * @param none
	 * @return none, will do redirect if it's case
	 */
	if (ihc_is_magic_feat_active('wp_social_login')){
		$redirect = get_option('ihc_wp_social_login_redirect_page');
		if ($redirect && $redirect!=-1){
			$url = get_permalink($redirect);
			if (!empty($url)){
				wp_safe_redirect($url);
				die();
			}
		}
	}
}

add_action('wsl_hook_process_login_after_wp_insert_user', 'ihc_wp_social_login_after_register_action', 99, 3);
function ihc_wp_social_login_after_register_action($user_id=0, $provider='', $hybridauth_user_profile=''){
 	/*
	 * @param none
	 * @return none
	 */
	 if ($user_id){
	 	if (ihc_is_magic_feat_active('wp_social_login')){
	 		/// STORE AVATAR
	 		if (!empty($hybridauth_user_profile) && !empty($hybridauth_user_profile->photoURL)){
	 			update_user_meta($user_id, 'ihc_avatar', $hybridauth_user_profile->photoURL);
	 		}

			///ROLE
			$role = get_option('ihc_wp_social_login_default_role');
			if ($role){
				$u = new WP_User($user_id);
				$u->set_role($role);
			}

			/// LEVEL
			$lid = get_option('ihc_wp_social_login_default_level');
			if ($lid!='' && $lid!=-1){
				$level_data = \Indeed\Ihc\Db\Memberships::getOne( $lid );
				\Indeed\Ihc\UserSubscriptions::assign( $user_id, $lid );
				\Indeed\Ihc\UserSubscriptions::makeComplete( $user_id, $lid );
			}
		}
	 }
}

/// PAYMENT GATE
function ihc_gate_add_query_vars_filter($vars=array()){
	/*
	 * @param array
	 * @return array
	 */
	$vars[] = "ihc_action";
	$vars[] = "ihc_name";
	$vars[] = "ihc";
	return $vars;
}
add_filter('query_vars', 'ihc_gate_add_query_vars_filter', 99);


add_action('pre_get_posts', 'ihc_payment_gate_check', 999, 1 );
/**
 * @param object
 * @return none
 */
function ihc_payment_gate_check( $query ){
	 // since version 12.6.2 if its not the main query out
	 if ( !empty( $query ) && !$query->is_main_query() ){
		 	return;
	 }
	 // end of version 12.6.2

	if (!empty($_GET['ihc_action'])){
		$ihc_action = sanitize_text_field($_GET['ihc_action']);
	} else {
		global $wp_query;
		if (!empty($wp_query)) {
			$ihc_action = get_query_var('ihc_action');
		}
	}
	 if (!empty($ihc_action)){
	 	$no_load = TRUE;
	 	switch ($ihc_action){
			case 'stripe_checkout':
				// Stripe Checkout - Webhook
				$enabled = get_option('ihc_stripe_checkout_v2_status');
				if ( empty( $enabled ) ){
						return;
				}
				$object = new \Indeed\Ihc\Gateways\StripeCheckout();
				$object->webhookPayment();
				break;
			case 'paypal':
				// PayPal Standard - Webhook
				$object = new \Indeed\Ihc\Gateways\PayPalStandard();
				$object->webhookPayment();
				break;
			case 'paypal_express_complete_payment':
				// PayPal Express - Complete Recurring Payment
				$object = new \Indeed\Ihc\Gateways\Libraries\PayPalExpress\PayPalExpressCheckoutNVP();
				$object->confirmAuthorization()
								 ->getExpressCheckoutDetails()
								 ->createRecurringProfile()
								 ->redirectToSuccessPage();
				break;
			case 'paypal_express_single_payment_complete_payment':
				// PayPal Express - Complete Single Payment
				$object = new \Indeed\Ihc\Gateways\Libraries\PayPalExpress\PayPalExpressCheckoutNVP();
				$object->completeSinglePayment()
								 ->redirectToSuccessPage();
				break;
			case 'paypal_express_checkout_ipn':
				// PayPal Express - Webhook
				$object = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
				$object->webhookPayment();
				break;
			case 'paypal_express_cancel_payment':
				// PayPal Express - Cancel Payment
				$object = new \Indeed\Ihc\Gateways\Libraries\PayPalExpress\PayPalExpressCheckoutNVP();
				$object->redirectHome();
				break;
			case 'twocheckout':
				// TwoCheckout - Webhook
				$object = new \Indeed\Ihc\Gateways\TwoCheckout();
				$object->webhookPayment();
				break;
			case 'mollie':
				// Mollie - Webhook
				$object = new \Indeed\Ihc\Gateways\Mollie();
				$object->webhookPayment();
				break;
			case 'pagseguro':
				// Pagseguro - Webhook
					$object = new \Indeed\Ihc\Gateways\Pagseguro();
					$object->webhookPayment();
				break;
			case 'authorize':
				// Authorize - Webhook
				$object = new \Indeed\Ihc\Gateways\Authorize();
				$object->webhookPayment();
				break;
			case 'braintree':
				// Braintree - Webhook
				$object = new \Indeed\Ihc\Gateways\Braintree();
				$object->webhookPayment();
				break;
			case 'arrive':
				//require_once IHC_PATH . 'public/action-reset_password.php';
				$resetPassword = new \Indeed\Ihc\ResetPassword();
				$resetPassword->arrive();
				break;
			case 'user_activation':
				require_once IHC_PATH . 'public/action-user_activation.php';
				break;
			case 'dl':
				$token = isset($_GET['token']) ? $_GET['token'] : '';
				$directLogin = new \Indeed\Ihc\Services\DirectLogin();
				$directLogin->handleRequest($token);
				break;
			case 'social_login':
				$ihcLoadWp = true;
				require IHC_PATH . 'public/social_handler.php';
				break;
			case 'stripe_connect':
				// Stripe Checkout - Webhook
				$enabled = get_option('ihc_stripe_connect_status');
				if ( empty( $enabled ) ){
						return;
				}
				$object = new \Indeed\Ihc\Gateways\StripeConnect();
				$object->webhookPayment();
				break;
			case 'check-file-permissions':
				$ihcNoWpLoad = true;
				require_once IHC_PATH . 'public/check-file-permissions.php';
				break;
			case 'api-gate':
				$no_load = true;
				require_once IHC_PATH . 'apigate.php';
				break;
			case 'cc-auth':
				// 12.5 - constant contact
				$cc = new \Indeed\Ihc\Services\ConstantContact();
				$response = $cc->adminEndpoint();
				if ( isset( $response['status'] ) && (int)$response['status'] === 1 ){
						$url = admin_url( 'admin.php?page=ihc_manage&tab=opt_in&cc_auth=1' );
				} else {
						$url = admin_url( 'admin.php?page=ihc_manage&tab=opt_in&cc_auth=0' );
				}
				wp_safe_redirect( $url );
				exit;
				break;
			default:
				$paymentObject = apply_filters( 'ihc_payment_gateway_create_payment_object', false, $ihc_action );
				// @description
				if ( $paymentObject ){
						$paymentObject->webhookPayment();
				} else {
						$home = get_home_url();
						wp_safe_redirect($home);
				}
				exit;
				break;
	 	}
	 }
}


add_action("wp_ajax_nopriv_ihc_check_coupon_status_via_ajax", "ihc_check_coupon_status_via_ajax");
add_action('wp_ajax_ihc_check_coupon_status_via_ajax', 'ihc_check_coupon_status_via_ajax');
function ihc_check_coupon_status_via_ajax()
{
	/*
	 * @param none
	 * @return none
	 */
	if ( !ihcPublicVerifyNonce() ){
			die;
	}
	$data['is_active'] = 0;
	$data['success_msg'] = esc_html__('Coupon applied successfully.', 'ihc');
	$data['err_msg'] = esc_html__('Coupon code is not valid.', 'ihc');
	if (!empty($_REQUEST['c']) && isset($_REQUEST['l'])){
		$check = ihc_check_coupon(sanitize_text_field($_REQUEST['c']), sanitize_text_field($_REQUEST['l']));
		if (empty($check)){
			$data['is_active'] = 0;
		} else {
			$data['is_active'] = 1;
		}
	}
	echo json_encode($data);
	die();
}


add_action("wp_ajax_nopriv_ihc_get_ihc_state_field", "ihc_get_ihc_state_field");
add_action('wp_ajax_ihc_get_ihc_state_field', 'ihc_get_ihc_state_field');
function ihc_get_ihc_state_field()
{
	/*
	 * @param none
	 * @return string
	 */
	if ( !ihcPublicVerifyNonce() ){
  		die;
  }
	if (isset($_REQUEST['country'])){
		echo ihc_get_state_field_str(sanitize_text_field($_REQUEST['country']));
	}
	die;
}

add_action("wp_ajax_nopriv_ihc_remove_sm_from_user", "ihc_remove_sm_from_user");
add_action('wp_ajax_ihc_remove_sm_from_user', 'ihc_remove_sm_from_user');
function ihc_remove_sm_from_user()
{
	/*
	 * @param none
	 * @return string
	 */
	if ( !ihcPublicVerifyNonce() ){
  		die;
  }
	if (isset($_REQUEST['type'])){
		global $current_user;
		if (isset($current_user->ID)){
			delete_user_meta($current_user->ID, 'ihc_' . sanitize_text_field($_REQUEST['type']) );
		}
	}
	die;
}

add_action("wp_ajax_nopriv_ihc_generate_invoice", "ihc_generate_invoice");
add_action('wp_ajax_ihc_generate_invoice', 'ihc_generate_invoice');
function ihc_generate_invoice()
{
	/*
	 * @param none
	 * @return string
	 */
	if (isset($_REQUEST['order_id'])){
		$order_id = sanitize_text_field($_REQUEST['order_id']);
		$order_id = (int)$order_id;
		if (current_user_can('manage_options')){
			/// is secure so get the uid from order table
			if ( !ihcAdminVerifyNonce() ){
					die;
			}
			$uid = Ihc_Db::get_uid_by_order_id($order_id);
			$check = TRUE;
		} else {
			if ( !ihcPublicVerifyNonce() ){
					 die;
			 }
			global $current_user;
			$uid = (isset($current_user->ID)) ? $current_user->ID : 0;
			$check = Ihc_Db::is_order_id_for_uid($uid, $order_id);	/// Security check
		}

		if ($check && $uid){
			require_once IHC_PATH . 'classes/Ihc_Invoice.class.php';
			$object = new Ihc_Invoice($uid, $order_id);
			echo esc_ump_content($object->output(TRUE));
		}
	}
	die;
}

add_action('user_register', 'ihc_increment_dashboard_user_notification', 1, 1);
function ihc_increment_dashboard_user_notification($uid=0){
	/*
	 * @param int
	 * @return none
	 */
	 Ihc_Db::increment_dashboard_notification('users');
}

add_action('ihc_action_after_subscription_activated', 'increment_user_limit_count', 1, 2);
function increment_user_limit_count($uid=0, $lid=0){
	if (get_option('ihc_download_monitor_enabled')){
		Ihc_Db::ihc_download_monitor_update_user_limit($uid, $lid);
	}
}


//===================== MyCred Integration Module ============================
if (ihc_is_magic_feat_active('mycred')):
add_filter('mycred_setup_hooks', 'ihc_my_cred_hook');
function ihc_my_cred_hook($installed){
	$installed['ihc_mycred'] = array(
		'title'       => esc_html__('Ultimate Membership Pro', 'ihc'),
		'description' => esc_html__('Ultimate Membership Pro - buy level hook.', 'ihc'),
		'callback'    => array('Ihc_My_Cred')
	);
	return $installed;
}
add_action('mycred_load_hooks', 'mycredpro_load_custom_hook');
function mycredpro_load_custom_hook(){
	require_once IHC_PATH . 'classes/services/Ihc_My_Cred.class.php';
}
endif;
//===================== END  MyCred Integration Module ============================


$ihcAjaxObject = new \Indeed\Ihc\Ajax();
$ihcRewriteAvatar = new \Indeed\Ihc\RewriteDefaultWpAvatar();
$ihcLoadTemplate = new \Indeed\Ihc\LoadTemplates();

// nonce
// nonce to admin pages
add_action( 'admin_head', 'ihcAdminNonce' );
function ihcAdminNonce()
{
    $nonce = wp_create_nonce( 'umpAdminNonce' );
    echo esc_ump_content("<meta name='ump-admin-token' content='$nonce'>");
}
// Nonce to front-end pages
add_action( 'wp_head', 'ihcPublicNonce' );
function ihcPublicNonce()
{
    $nonce = wp_create_nonce( 'umpPublicNonce' );
    echo esc_ump_content("<meta name='ump-token' content='$nonce'>");
}
add_action( 'admin_head', 'ihcStyleForTopNotifications' );
if ( !function_exists( 'ihcStyleForTopNotifications' ) ):
function ihcStyleForTopNotifications()
{
	$custom_css = '
	.ihc-top-bar-count{
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
	li.ihc-uap-link-wrapp, li#wp-admin-bar-ihc-uap-link{
    display: block;
     width: 100%;
     background-color: #ed5a4c !important;
     color: #fff !important;
     padding: 4px 0 4px 0px !important;
  }
	li.ihc-uap-link-wrapp a,  li#wp-admin-bar-ihc-uap-link a{
		color:#fff !important;
	}
	li.ihc-addons-link-wrapp, li#wp-admin-bar-ihc-pro-addons-link{
		display: block;
     width: 100%;
     background-color: #eee !important;
     color: #fff !important;
		 background-color: #284051 !important;
     padding: 4px 0 4px 0px !important;
	}
	li.ihc-addons-link-wrapp a, li#wp-admin-bar-ihc-pro-addons-link a{
		color:#333 !important;
		color: #53E2F3 !important;
	}
	'
	;

	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes($custom_css) );

}
endif;

add_filter( 'et_grab_image_setting', 'ihcDiviGrabImage', 999, 1 );
if ( !function_exists( 'ihcDiviGrabImage' ) ):
function ihcDiviGrabImage( $bool=true )
{
		return false;
}
endif;

if ( !function_exists( 'ihcAdminGlobalNoticeMk' ) ):
function ihcAdminGlobalNoticeMk()
{
		if ( !current_user_can('manage_options') ){
			return;
		}

		// 12.6
		if ( isset($_GET['page'] ) && ($_GET['page'] === 'ihc_manage'
				|| $_GET['page'] === 'ultimate_affiliates_pro'
				|| $_GET['page'] === 'ultimate_learning_pro' ) ){
				return;
		}
		// end of 12.6

		$divi = ihcSearchForDiviAndExtension();
		if ( isset( $divi['status'] ) && $divi['status'] === 1 ){
				// print the message
				echo esc_ump_content($divi['message']);
		}

		$elementor = ihcSearchForElementorAndExtension();
		if ( isset( $elementor['status'] ) && $elementor['status'] === 1 ){
				// print the message
				echo esc_ump_content($elementor['message']);
		}
}
endif;
add_action( 'admin_notices', 'ihcAdminGlobalNoticeMk' );
