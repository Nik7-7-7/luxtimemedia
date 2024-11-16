<?php
/********************************* ADMIN SECTION **************************/
// update object @since 7.4
$updateObject = new \Indeed\Ihc\Updates();
$updatePlugin = new \Indeed\Ihc\Admin\UpdatePlugin();
$ihcModals = new \Indeed\Ihc\Admin\Modals();
$iumpWizard = new \Indeed\Ihc\Admin\Wizard();// 12.1
$iumpWizardElCheck = new \Indeed\Ihc\Admin\WizardElCheck();// 12.1
$IUMPConstantContact = new \Indeed\Ihc\Services\ConstantContact();

add_action('init', 'ihc_add_bttn_func');
function ihc_add_bttn_func(){
	/*
	 * add the locker and shortcodes buttons for wp editor
	 * prevent indeed users to view them
	 * @param none
	 * @return none
	 */
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return;
	}
	if (is_user_logged_in()){
		$uid = get_current_user_id();
		$role = '';
		$user = new WP_User( $uid );

		if ( !is_super_admin( $uid ) && $user && !empty($user->roles) && !empty($user->roles[0]) && !in_array( 'administrator', $user->roles ) ){
			$allowed_roles = get_option('ihc_dashboard_allowed_roles');
			$allowed_roles = apply_filters( 'ihc_filter_allowed_roles_in_dashboard', $allowed_roles );

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
					wp_redirect(home_url());
					exit();
			}

		}

	    if (!current_user_can('edit_posts') || !current_user_can('edit_pages')){
	    	return;
	    }
	    if (get_user_option('rich_editing') == 'true') {
	    	/// add the buttons
	    	add_filter( 'mce_buttons', 'ihc_register_button' );
	    	add_filter( "mce_external_plugins", "ihc_js_bttns_return" );
	    }
	}
}

function ihc_register_button( $arr ) {
	array_push( $arr, 'ihc_button_locker' );
	array_push( $arr, 'ihc_button_forms' );
	return $arr;
}

function ihc_js_bttns_return( $arr ) {
	$arr['ihc_button_forms'] =  IHC_URL . 'admin/assets/js/ihc_buttons.js';
	$arr['ihc_button_locker'] =  IHC_URL . 'admin/assets/js/ihc_buttons.js';
	return $arr;
}

/////////////// SETTINGS META BOX
add_action( 'add_meta_boxes', 'ihc_meta_boxes_settings');
function ihc_meta_boxes_settings(){
	include_once IHC_PATH . 'admin/includes/functions.php';
	$arr = ihc_get_post_types_be();
	$arr[] = 'post';
	$arr[] = 'page';
	foreach($arr as $v){
		add_meta_box(   'ihc_show_for',//id
						'Ultimate Membership Pro - Locker',
						'ihc_meta_box_settings_html',
						$v,
						'side',
						'high'
					);
	}
}

////REPLACE CONTENT METABOX
add_action( 'add_meta_boxes', 'ihc_replace_content_meta_box' );
function ihc_replace_content_meta_box(){
	$arr = ihc_get_post_types_be();
	$arr[] = 'post';
	$arr[] = 'page';
	foreach($arr as $v){
		add_meta_box(   'ihc_replace_content',//id
						'Ultimate Membership Pro - Replace Content',
						'ihc_meta_box_replace_content_html',
						$v,
						'normal',
						'high'
					);
	}
}

////SET DEFAULT PAGES META BOX
add_action( 'add_meta_boxes', 'ihc_set_default_pages_meta_box' );
function ihc_set_default_pages_meta_box(){
	global $post;
	$set_arr = ihc_get_default_pages_il(true);

		add_meta_box(
				'ihc_default_pages_content',//id
				'Membership Pro - Page Type',
				'ihc_meta_box_default_pages_html',
				'page',
				'side',
				'high'
		);

}

////DRIP CONTENT SETTINGS
add_action( 'add_meta_boxes', 'ihc_drip_content_meta_box' );
function ihc_drip_content_meta_box(){
	$arr = ihc_get_post_types_be();
	$arr[] = 'post';
	$arr[] = 'page';
	foreach ($arr as $v){
		add_meta_box(   'ihc_drip_content',//id
				'Membership Pro - Drip Content',
				'ihc_drip_content_return_meta_box',
				$v,
				'side',
				'high'
		);
	}
}

/////save/update custom metabox values
add_action('save_post', 'ihc_save_post_meta', 10, 1 );//save ihc_meta_box_settings_html values
function ihc_save_post_meta($post_id){
	$meta_arr = ihc_post_metas($post_id, true);
	foreach($meta_arr as $k=>$v){
		if(isset($_REQUEST[$k])){
			update_post_meta($post_id, $k, indeed_sanitize_textarea_array($_REQUEST[$k]) );
		}
	}

	//default pages
        $meta_name = isset($_REQUEST['ihc_set_page_as_default_something']) ? sanitize_text_field( $_REQUEST['ihc_set_page_as_default_something'] ) : false;
        $postId = isset($_REQUEST['ihc_post_id']) ? sanitize_text_field( $_REQUEST['ihc_post_id'] ) : false;
	if( $meta_name && $meta_name!=-1 && $postId ){

		//EXTRA CHECK - REWRITE RULE FOR Visitor Inside User Page
		if ($meta_name=='ihc_general_register_view_user'){
			ihc_save_rewrite_rule_for_register_view_page( $postId );
		}

		if(get_option($meta_name)!==FALSE){
			update_option($meta_name, $postId );
		}else{
			add_option( $meta_name, $postId );
		}
	}
}

///dashboard menu
add_action ( 'admin_menu', 'ihc_menu', 81 );
function ihc_menu()
{
		$access = current_user_can( 'manage_options' );
		$access = apply_filters( 'ihc_filter_admin_show_the_dashboard_menu', $access );
		if ( $access ){
				$capability = 'manage_options';
				$capability = apply_filters( 'ihc_filter_admin_capability_for_dashboard_menu', $capability );
				add_menu_page( 'Ultimate Membership Pro', 'Ultimate Membership Pro', $capability,	'ihc_manage', 'ihc_manage', 'dashicons-universal-access-alt' );
		}
}

$ext_menu = 'ihc_manage';

function ihc_manage(){
		$access = current_user_can( 'manage_options' );
		$access = apply_filters( 'ihc_filter_admin_show_the_dashboard_menu', $access );

		$show_announcement = iumpannouncementProcessing();

		if ( $access ){
			include_once IHC_PATH . 'admin/includes/functions.php';
			require_once IHC_PATH . 'admin/includes/manage-page.php';
		}
}


add_action( 'admin_menu' , 'ihc_manage_submenu' );
function ihc_manage_submenu(){
	global $submenu;
	$capability = 'manage_options';
	$capability = apply_filters( 'ihc_filter_admin_capability_for_dashboard_menu', $capability );

	$submenu['ihc_manage'][300] = [ esc_html__( 'Dashboard', 'ihc' ),	$capability, 'admin.php?page=ihc_manage&tab=dashboard', esc_html__( 'Dashboard', 'ihc' ), 'ump-admin-menu-dashboard-item', '', '' ];
	$submenu['ihc_manage'][301] = [ esc_html__( 'Members', 'ihc' ),	$capability, 'admin.php?page=ihc_manage&tab=users', esc_html__( 'Members', 'ihc' ), '', '', '' ];
	$submenu['ihc_manage'][302] = [ esc_html__( 'Memberships', 'ihc' ), $capability, 'admin.php?page=ihc_manage&tab=levels', esc_html__( 'Memberships', 'ihc' ), '' , '','' ];
	$submenu['ihc_manage'][303] = [ esc_html__( 'Payment Gateways', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=payment_settings', esc_html__( 'Payment Gateways', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][304] = [ esc_html__( 'Showcases', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=showcases', esc_html__( 'Showcases', 'ihc' ), '', '' ];

	$submenu['ihc_manage'][341] = [ esc_html__( 'Registration Form', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=register', esc_html__( 'Registration Form', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][342] = [ esc_html__( 'Login Form', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=login', esc_html__( 'Login Form', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][343] = [ esc_html__( 'Subscriptions Plan', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=subscription_plan', esc_html__( 'Subscriptions Plan', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][344] = [ esc_html__( 'Checkout Page', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=checkout', esc_html__( 'Checkout Page', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][345] = [ esc_html__( 'Member Portal', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=account_page', esc_html__( 'Member Portal', 'ihc' ), '', '' ];

	$submenu['ihc_manage'][404] = [ esc_html__( 'Coupons', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=coupons', esc_html__( 'Coupons', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][305] = [ esc_html__( 'Content Access Rules', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=block_url', esc_html__( 'Content Access Rules', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][306] = [ esc_html__( 'Payment History', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=orders', esc_html__( 'Payment History', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][307] = [ esc_html__( 'Email Notifications', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=notifications', esc_html__( 'E-mail Notifications', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][308] = [ esc_html__( 'Extensions', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=magic_feat', esc_html__( 'Extensions', 'ihc' ), 'ihc-admin-menu-extension-item', '' ];
	$submenu['ihc_manage'][309] = [ esc_html__( 'General Settings', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=general', esc_html__( 'General Settings', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][400] = [ esc_html__( 'Pro Addons', 'ihc' ), $capability , 'https://ultimatemembershippro.com/pro-addons/', esc_html__( 'Pro Addons', 'ihc' ), 'ihc-addons-link-wrapp', '_blank' ];
	$submenu['ihc_manage'][401] = [ esc_html__( 'Shortcodes', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=user_shortcodes', esc_html__( 'Shortcodes', 'ihc' ), '', '' ];
	$submenu['ihc_manage'][402] = [ esc_html__( 'Licensing', 'ihc' ), $capability , 'admin.php?page=ihc_manage&tab=help', esc_html__( 'Licensing', 'ihc' ), 'ihc-admin-menu-licensing-item', ''];
	$submenu['ihc_manage'][403] = [ esc_html__( 'Documentation', 'ihc' ), $capability , 'https://ultimatemembershippro.com/documentation/', esc_html__( 'Documentation', 'ihc' ), 'ihc-admin-menu-documentation-item', '_blank' ];
	if (!ihc_is_uap_active()){
		$submenu['ihc_manage'][500] = [ 'Ultimate Affiliate Pro',$capability, 'https://ultimateaffiliate.pro', 'Ultimate Affiliate Pro', 'ihc-uap-link-wrapp', '_blank' ];
	}
	if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'ihc_manage' && isset( $_GET['tab'] ) ){
			switch ( sanitize_text_field( $_GET['tab'] ) ){
					case 'users':
						$submenu['ihc_manage'][301][4] = 'current';
						break;
					case 'levels':
						$submenu['ihc_manage'][302][4] = 'current';
						break;
					case 'payment_settings':
						$submenu['ihc_manage'][303][4] = 'current';
						break;
					case 'showcases':
						$submenu['ihc_manage'][304][4] = 'current';
						break;
					case 'block_url':
						$submenu['ihc_manage'][305][4] = 'current';
						break;
					case 'orders':
						$submenu['ihc_manage'][306][4] = 'current';
						break;
					case 'notifications':
						$submenu['ihc_manage'][307][4] = 'current';
						break;
					case 'magic_feat':
						$submenu['ihc_manage'][308][4] = 'current';
						break;
					case 'general':
						$submenu['ihc_manage'][309][4] = 'current';
						break;
					case 'user_shortcodes':
						$submenu['ihc_manage'][401][4] = 'current';
						break;
					case 'help':
						$submenu['ihc_manage'][402][4] = 'current';
						break;
			}
	}

}

add_action("admin_enqueue_scripts", 'ihc_head');
function ihc_head(){
	global $pagenow, $wp_version;
	wp_enqueue_style( 'ihc_admin_style', IHC_URL . 'admin/assets/css/style.css', array(), '12.8' );
	wp_enqueue_style( 'ihc_public_style', IHC_URL . 'assets/css/style.min.css', array(), '12.8' );
	wp_enqueue_style( 'ihc-font-awesome', IHC_URL . 'assets/css/font-awesome.css', array(), '12.8' );
	wp_enqueue_style( 'indeed_sweetalert_css', IHC_URL . 'assets/css/sweetalert.css', array(), '12.8' );
	wp_enqueue_media();
	wp_register_script( 'ihc-back_end', IHC_URL . 'admin/assets/js/back_end.min.js', [ 'jquery' ], '12.8' );
	//wp_register_script( 'ihc-back_end', IHC_URL . 'admin/assets/js/back_end.js', [ 'jquery' ], '12.8' );

	if ( version_compare ( $wp_version , '5.7', '>=' ) ){
			wp_add_inline_script( 'ihc-back_end', "var ihc_site_url='" . get_site_url() . "';" );
			wp_add_inline_script( 'ihc-back_end', "var ihc_plugin_url='" . IHC_URL . "';" );
			wp_add_inline_script( 'ihc-back_end', "var ihcAdminAjaxNonce='" . wp_create_nonce( 'ihcAdminAjaxNonce' ) . "';" );
	} else {
			wp_localize_script( 'ihc-back_end', 'ihc_site_url', get_site_url() );
			wp_localize_script( 'ihc-back_end', 'ihc_plugin_url', IHC_URL );
			wp_localize_script( 'ihc-back_end', 'ihcAdminAjaxNonce', wp_create_nonce( 'ihcAdminAjaxNonce' ) );
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
        $page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : false;
        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : false;

	if ( $page && $page === 'ihc_manage' ){
		wp_enqueue_style( 'ihc_jquery-ui.min.css', IHC_URL . 'admin/assets/css/jquery-ui.min.css', array(), '12.8' );
		wp_enqueue_style( 'ihc_bootstrap-slider', IHC_URL . 'admin/assets/css/bootstrap-slider.css', array(), '12.8' );
		wp_enqueue_script( 'ihc-bootstrap-slider', IHC_URL . 'admin/assets/js/bootstrap-slider.js', [ 'jquery' ], '12.8' );

		if ( $tab && $tab !== 'orders'){
			wp_enqueue_style( 'ihc_bootstrap', IHC_URL . 'admin/assets/css/bootstrap.css', array(), '12.8' );
		}
		wp_enqueue_style( 'ihc_bootstrap-res', IHC_URL . 'admin/assets/css/bootstrap-responsive.min.css', array(), '12.8' );

		wp_enqueue_style( 'ihc_templates_style', IHC_URL . 'assets/css/templates.min.css', array(), '12.8' );
		wp_enqueue_style( 'ihc_select2_style', IHC_URL . 'assets/css/select2.min.css', array(), '12.8' );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'ihc-flot', IHC_URL . 'admin/assets/js/jquery.flot.js', [ 'jquery' ], '12.8' );
		wp_enqueue_script( 'indeed_sweetalert_js', IHC_URL . 'assets/js/sweetalert.js', [ 'jquery' ], '12.8' );

		wp_enqueue_script( 'ihc-front_end_js', IHC_URL . 'assets/js/functions.js', [ 'jquery' ], '12.8' );
	}
	if ( $pagenow == 'plugins.php' ){
			if ( version_compare ( $wp_version , '5.7', '>=' ) ){
					wp_add_inline_script( 'ihc-back_end', "var ihcKeepData=" . get_option('ihc_keep_data_after_delete') . ";" );
			} else {
					wp_localize_script( 'ihc-back_end', 'ihcKeepData', get_option('ihc_keep_data_after_delete') );
			}
			wp_enqueue_script( 'indeed_sweetalert_js', IHC_URL . 'assets/js/sweetalert.js', [ 'jquery' ], '12.8' );
			wp_enqueue_style( 'indeed_sweetalert_css', IHC_URL . 'assets/css/sweetalert.css', array(), '12.8' );
	}
	if ( $pagenow == 'post.php' ){
			wp_enqueue_style( 'ihc_templates_style', IHC_URL . 'assets/css/templates.min.css', array(), '12.8' );
	}
	wp_enqueue_script( 'ihc-back_end' );
	wp_register_style( 'ihc_select2_style', IHC_URL . 'assets/css/select2.min.css', [], '12.8' );
	wp_register_script( 'ihc-select2', IHC_URL . 'assets/js/select2.min.js', [ 'jquery' ], '12.8' );
	wp_register_script( 'ihc-jquery_upload_file', IHC_URL . 'assets/js/jquery.uploadfile.min.js', [ 'jquery' ], '12.8' );
	wp_register_script( 'ihc-jquery_form_module', IHC_URL . 'assets/js/jquery.form.js', [ 'jquery' ], '12.8' );
	wp_register_script( 'ihc-print-this', IHC_URL . 'assets/js/printThis.js', [ 'jquery' ], '12.8' );
}

///CUSTOM NAV MENU
require_once IHC_PATH . 'admin/includes/custom-nav-menu.php';

//AJAX CALL FOR POPUP
add_action( 'wp_ajax_ihc_ajax_admin_popup', 'ihc_ajax_admin_popup' );
function ihc_ajax_admin_popup()
{
	if ( !ihcIsAdmin() ){
			echo 0;
			die;
	}
	if ( !ihcAdminVerifyNonce() ){
			echo 0;
			die;
	}
	include_once IHC_PATH . 'admin/includes/popup-locker.php';
	die;
}

/**
 * @param none
 * @return none
 */
add_action('wp_ajax_ihc_get_font_awesome_popup', 'ihc_get_font_awesome_popup');
function ihc_get_font_awesome_popup()
{
	if ( !ihcIsAdmin() ){
		  echo 0;
			die;
	}
	if ( !ihcAdminVerifyNonce() ){
		 echo 0;
		 die;
 	}
	require_once IHC_PATH . 'admin/includes/font_awesome_popup.php';
	$output = ob_get_contents();
	ob_end_clean();
	echo esc_ump_content($output);
	die;
}

//AJAX CALL FOR DELETE USER
add_action( 'wp_ajax_ihc_delete_user_via_ajax', 'ihc_delete_user_via_ajax' );
function ihc_delete_user_via_ajax()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
			 echo 0;
			 die;
		}
                $id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : false;

		if ($id){
			require_once IHC_PATH . 'admin/includes/functions.php';
			ihc_delete_users( $id );
		}
		die;
}


//ajax call for popup forms
add_action( 'wp_ajax_ihc_ajax_admin_popup_the_forms', 'ihc_ajax_admin_popup_the_forms');
function ihc_ajax_admin_popup_the_forms()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
			 echo 0;
			 die;
		}
		include_once IHC_PATH . 'admin/includes/popup-forms.php';
		die;
}

add_action( 'wp_ajax_ihc_ajax_notification_send_test_email', 'ihc_ajax_notification_send_test_email');
function ihc_ajax_notification_send_test_email()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
			 echo 0;
			 die;
		}
		include_once IHC_PATH . 'admin/includes/notification-send-email-test.php';
		die;
}

add_action( 'wp_ajax_ihc_ajax_do_send_notification_test', 'ihc_ajax_do_send_notification_test' );
function ihc_ajax_do_send_notification_test()
{
	if ( !ihcIsAdmin() ){
			echo 0;
			die;
	}
	if ( !ihcAdminVerifyNonce() ){
		 echo 0;
		 die;
	}
	$notificationId = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : 0;
	$email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
	$notification = new \Indeed\Ihc\Notifications();
	$notification->sendTestNotification( $notificationId, $email );
	echo 1;
	die;
}

//AJAX CALL PREVIEW TEMPLATE IN POPUP
add_action( 'wp_ajax_ihc_ajax_template_popup_preview', 'ihc_ajax_template_popup_preview' );
function ihc_ajax_template_popup_preview()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
			 echo 0;
			 die;
		}
                $template = isset( $_REQUEST['template'] ) ? sanitize_text_field( $_REQUEST['template'] ) : false;
		if ( $template ){
			//get id
			$arr = explode('_', $template );
			if(isset($arr[1]) && $arr[1]!=''){
				include IHC_PATH . 'public/layouts-locker.php';
				echo ihc_print_locker_template($arr[1]);
			}
		}
		die;
}

//AJAX CALL PREVIEW LOGIN FORM
add_action( 'wp_ajax_ihc_login_form_preview', 'ihc_login_form_preview' );
function ihc_login_form_preview()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
		$meta_arr['ihc_login_remember_me'] = isset( $_POST['remember'] ) ? sanitize_text_field( $_POST['remember'] ) : '';
		$meta_arr['ihc_login_register'] = isset( $_POST['register'] ) ? sanitize_text_field( $_POST['register'] ) : '';
		$meta_arr['ihc_login_pass_lost'] = isset( $_POST['pass_lost'] ) ? sanitize_text_field( $_POST['pass_lost'] ) : '';
		$meta_arr['template'] = isset( $_POST['template'] ) ? sanitize_text_field( $_POST['template'] ) : '';
		$meta_arr['ihc_login_custom_css'] = isset( $_POST['css'] ) ? sanitize_text_field( stripslashes( $_POST['css'] ) ) : '';
		$meta_arr['ihc_login_show_sm'] = isset( $_POST['ihc_login_show_sm'] ) ? sanitize_text_field( $_POST['ihc_login_show_sm'] ) : '';
		$meta_arr['ihc_login_show_recaptcha'] = isset( $_POST['ihc_login_show_recaptcha'] ) ? sanitize_text_field( $_POST['ihc_login_show_recaptcha'] ) : '';
		$captchaType = get_option( 'ihc_recaptcha_version' );
		if ( $captchaType !== false && $captchaType == 'v3' ){
				$meta_arr['ihc_login_show_recaptcha'] = 0;
		}
		$meta_arr['preview'] = 1;
		$loginForm = new \Indeed\Ihc\LoginForm();
		$output = $loginForm->html( $meta_arr );
		echo esc_ump_content($output);
	  die;
}

//ajax preview locker
add_action( 'wp_ajax_ihc_locker_preview_ajax', 'ihc_locker_preview_ajax' );
function ihc_locker_preview_ajax()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
                $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : false;
		if ( !ihcAdminVerifyNonce() && ( !$nonce || !wp_verify_nonce( $nonce, 'umpAdminNonce' )  ) ){
			  echo 0;
			  die;
		}

		include IHC_PATH . 'public/layouts-locker.php';
                $locker_id = isset( $_REQUEST['locker_id'] ) ? sanitize_text_field( $_REQUEST['locker_id'] ) : false;
		if ( $locker_id ){
			//ihc_print_locker_template(template id, meta array, preview)
                        $popup_display = isset( $_REQUEST['popup_display'] ) ? sanitize_text_field( $_REQUEST['popup_display'] ) : false;
			if ( $popup_display ){
				//preview in a popup
				$str = '
						<div class="ihc-popup-wrapp" id="popup_box">
							<div class="ihc-the-popup">
							<div class="ihc-popup-top">
								<div class="title">Preview Locker</div>
								<div class="close-bttn" onclick="ihcClosePopup();"></div>
								<div class="clear"></div>
							</div>
								<div class="ihc-popup-content ihc-text-aling-center">
									<div>
										'.ihc_print_locker_template( $locker_id, false, true).'
									</div>
								</div>
							</div>
						</div>
				';
			} else {
				// html
				$str = ihc_print_locker_template( $locker_id, false, true);
			}

			echo esc_ump_content($str);

		} else {
			$meta_arr = indeed_sanitize_array( $_REQUEST );
			echo ihc_print_locker_template(false, $meta_arr, true);
		}

		die;
}

//ajax preview locker
add_action( 'wp_ajax_ihc_register_preview_ajax', 'ihc_register_preview_ajax' );
function ihc_register_preview_ajax()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}

		// new register implementation
		$template = isset( $_POST['template'] ) ? sanitize_text_field( $_POST['template'] ) : '';
		$registerForm = new \Indeed\Ihc\RegisterForm();
		$str = $registerForm->form( [ 'template' => $template, 'is_preview' => 1 ] );
		// end of new register implementation

		echo esc_ump_content($str);
		die;
}

//ajax approve user
/**
 * @param none
 * @return none
 */
add_action( 'wp_ajax_ihc_approve_new_user', 'ihc_approve_new_user' );
function ihc_approve_new_user()
{
		if ( !ihcIsAdmin() ){
		  	echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
                $uid = isset($_REQUEST['uid']) ? sanitize_text_field($_REQUEST['uid']) : false;
		if ( $uid ){
			$success = ihc_do_user_approve($uid);
			if ($success){
				 echo get_option('default_role');
			}
		}
		die;
}

//ajax approve email address
add_action( 'wp_ajax_ihc_approve_user_email', 'ihc_approve_user_email' );
function ihc_approve_user_email()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
                $uid = isset($_REQUEST['uid']) ? sanitize_text_field($_REQUEST['uid']) : false;
		if ( $uid ){
			/// user log
			Ihc_User_Logs::set_user_id( $uid );
			$username = Ihc_Db::get_username_by_wpuid( $uid );
			Ihc_User_Logs::write_log(esc_html__('E-mail address has become active for ', 'ihc') . $username, 'user_logs');

			update_user_meta( $uid, 'ihc_verification_status', 1);
			do_action( 'ihc_action_approve_user_email', $uid );
			echo 1;
		}
		die;
}

//ajax reorder levels
add_action( 'wp_ajax_ihc_preview_select_level', 'ihc_preview_select_level' );
function ihc_preview_select_level()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
		include IHC_PATH . 'public/shortcodes.php';
		$attr = array(
						'template' 							=> sanitize_text_field($_REQUEST['template']),
						'css' 									=> stripslashes_deep( sanitize_text_field( $_REQUEST['custom_css'] ) ),
						'is_admin_preview'			=> 1,
		);
		echo ihc_user_select_level($attr);
		die;
}

//////////////aweber
add_action( 'wp_ajax_ihc_update_aweber', 'ihc_update_aweber' );
function ihc_update_aweber()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
		include_once IHC_PATH .'classes/services/email_services/aweber/aweber_api.php';
		list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID( sanitize_text_field($_REQUEST['auth_code']) );
		update_option( 'ihc_aweber_consumer_key', $consumer_key );
		update_option( 'ihc_aweber_consumer_secret', $consumer_secret );
		update_option( 'ihc_aweber_acces_key', $access_key );
		update_option( 'ihc_aweber_acces_secret', $access_secret );
		echo 1;
		die;
}

add_action('wp_ajax_ihc_get_cc_list', 'ihc_get_cc_list');
add_action('wp_ajax_nopriv_ihc_get_cc_list', 'ihc_get_cc_list');
function ihc_get_cc_list()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
		echo json_encode(ihc_return_cc_list( sanitize_text_field( $_REQUEST['ihc_cc_user'] ), sanitize_text_field( $_REQUEST['ihc_cc_pass'] ) ) );
		die;
}

///////VC SECTION
add_action( 'init', 'ihc_check_vc' );

function ihc_check_vc(){
	if (function_exists('vc_map')){
		require_once IHC_PATH . 'admin/includes/vc_map.php';
	}
}

//ajax call for popup forms
add_action( 'wp_ajax_ihc_return_csv_link', 'ihc_return_csv_link');
function ihc_return_csv_link()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}

		if ( isset($_POST['filters']) ){
				$attributes = json_decode( stripslashes( sanitize_text_field( $_POST['filters'] ) ), true );
		} else {
				$attributes = [];
		}
		echo ihc_make_csv_user_list( $attributes );
		die;
}

add_action( 'wp_ajax_ihc_return_csv_link_with_json_params', 'ihc_return_csv_link_with_json_params');
function ihc_return_csv_link_with_json_params()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}

		if ( isset($_POST['filters']) ){
				$attributes = json_decode( stripslashes( sanitize_text_field( $_POST['filters'] ) ), true );
		} else {
				$attributes = [];
		}
		$attributes['level_strict_mode'] = true;
		$link = ihc_make_csv_user_list( $attributes );
		if ( !$link ){
				echo json_encode([
														'status'							=> 0,
														'reason'							=> 'No data available',
														'errorMessageAsHtml'	=> '<div class="iump-js-notice-for-export ihc-warning-box" >' . esc_html__('No entries available for selected options.', 'ihc') . '</div>',
				]);
				die;
		}
		echo json_encode([
												'status'							=> 1,
												'file'								=> $link,
												'htmlSuccessMessage'	=> '<span class="iump-download-csv-link"><p>'.esc_html__('If your download did not start automatically, click the link to manually initiate the download process:', 'ihc').' <a href="' . $link . '">' . esc_html__( 'download CSV file', 'ihc' ) . '</a></p></span>',
		]);
		die;
}

//ajax notification templates
/**
 * @param [string]
 * @return array
 */
add_action( 'wp_ajax_ihc_notification_templates_ajax', 'ihc_notification_templates_ajax');
function ihc_notification_templates_ajax()
{
		if ( !ihcIsAdmin() ){
				 echo 0;
				 die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
                $type = isset( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : false;
		if ( $type ){
			$notificationObject = new \Indeed\Ihc\Notifications();
			$template = $notificationObject->getNotificationTemplate( $type );
			echo json_encode( $template );
		}
		die;
}


/////////////////////////// DASHBOARD LIST POST/PAGES/CUSTOM POST TYPE ULTIMATE MEMBERSHIP PRO COLUMN WIHT DEFAULT PAGES/RESTRINCTED AND DRIP CONTENT

/**
 * @param string
 * @return none, print a string if its case
 */
add_filter( 'display_post_states', 'ihc_custom_column_dashboard_print', 999, 2 );
function ihc_custom_column_dashboard_print($states, $post)
{
	if (isset($post->ID) ){
			$str = '';
			//////////// DEFAULT PAGES
			if (get_post_type($post->ID)=='page'){
				$register_page = get_option('ihc_general_register_default_page');
				$lost_pass = get_option('ihc_general_lost_pass_page');
				$login_page = get_option('ihc_general_login_default_page');
				$redirect = get_option('ihc_general_redirect_default_page');
				$logout = get_option('ihc_general_logout_page');
				$user_page = get_option('ihc_general_user_page');
				$tos = get_option('ihc_general_tos_page');
				$subscription_plan = get_option('ihc_subscription_plan_page');
				$checkout_page = get_option('ihc_checkout_page');
				$thank_you_page = get_option('ihc_thank_you_page');
				$view_user_page = get_option('ihc_general_register_view_user');

				switch($post->ID){
					case $register_page:
						$print = esc_html__('Register Page', 'ihc');
						break;
					case $lost_pass:
						$print = esc_html__('Lost Password Page', 'ihc');
						break;
					case $login_page:
						$print = esc_html__('Login Page', 'ihc');
						break;
					case $redirect:
						$print = esc_html__('Redirect Page', 'ihc');
						break;
					case $logout:
						$print = esc_html__('Logout Page', 'ihc');
						break;
					case $user_page:
						$print = esc_html__('User Page', 'ihc');
						break;
					case $tos:
						$print = esc_html__('TOS Page', 'ihc');
						break;
					case $subscription_plan:
						$print = esc_html__('Subscription Plan Page', 'ihc');
						break;
					case $checkout_page:
							$print = esc_html__('Checkout Page', 'ihc');
							break;
					case $thank_you_page:
									$print = esc_html__('Thank You Page', 'ihc');
									break;
					case $view_user_page:
						$print = esc_html__('Visitor Inside User Page', 'ihc');
						break;
				}
				if (!empty($print)){
					$str .= '<div class="ihc-dashboard-list-posts-col-default-pages">' . $print . '</div>';
				}
			}

			$post_meta = ihc_post_metas($post->ID);
			////////// RESTRICTIONS
			if (!empty($post_meta['ihc_mb_who'])){
				$str .= '<div class="ihc-dashboard-list-posts-col-restricted-posts">' . esc_html__(" Restricted", 'ihc') . '</div>';
			}

			//////////// DRIP CONTENT
			if (!empty($post_meta['ihc_drip_content']) && $post_meta['ihc_mb_type']=='show' && !empty($post_meta['ihc_mb_who'])){
				$str .= '<div class="ihc-dashboard-list-posts-col-drip-content">' . esc_html__(" Drip Content", 'ihc') . '</div>';
			}
			if (!empty($str))
			$states[] = $str;
	}
	return $states;
}

add_action('wp_ajax_ihc_delete_currency_code_ajax', 'ihc_delete_currency_code_ajax');
add_action('wp_ajax_nopriv_ihc_delete_currency_code_ajax', 'ihc_delete_currency_code_ajax');
function ihc_delete_currency_code_ajax()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
                $code = isset( $_REQUEST['code'] ) ? sanitize_text_field( $_REQUEST['code'] ) : false;
		if ( $code ){
				$data = get_option('ihc_currencies_list');
				if (!empty($data[$code])){
						unset($data[$code]);
						echo 1;
				}
				update_option('ihc_currencies_list', $data);
		}
		die;
}

add_action('wp_ajax_ihc_preview_user_listing', 'ihc_preview_user_listing');
add_action('wp_ajax_nopriv_ihc_preview_user_listing', 'ihc_preview_user_listing');
function ihc_preview_user_listing()
{
		if ( !ihcIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
                $shortcode = isset( $_REQUEST['shortcode'] ) ? sanitize_text_field( $_REQUEST['shortcode'] ) : false;
		if ( $shortcode ){
				define('IS_PREVIEW', TRUE);
				$shortcode = stripslashes( $shortcode );
				require_once IHC_PATH . 'public/shortcodes.php';
				echo do_shortcode($shortcode);
		}
		die;
}

/**
 * @param string, string
 * @return none
 */
add_action( 'update_option_permalink_structure' , 'ihc_update_permalink_structure_action', 99, 2 );
function ihc_update_permalink_structure_action( $old_value, $new_value )
{
		update_option('indeed_do_rewrite_update', TRUE);
}

/**
 * @param none
 * @return none
 */
add_action('init', 'ihc_do_rewrite_update', 1);
function ihc_do_rewrite_update()
{
	if (get_option('indeed_do_rewrite_update')){
		$page_id = get_option('ihc_general_register_view_user');
		ihc_save_rewrite_rule_for_register_view_page($page_id);
		update_option('indeed_do_rewrite_update', FALSE);
	}
}

/**
 * @param none
 * @return none
 */
add_action('wp_ajax_ihc_delete_user_level_relationship', 'ihc_delete_user_level_relationship');
function ihc_delete_user_level_relationship()
{
		if ( !ihcIsAdmin() ){
				 echo 0;
				 die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
                $lid = isset( $_REQUEST['lid'] ) ? sanitize_text_field( $_REQUEST['lid'] ) : false;
                $uid = isset( $_REQUEST['uid'] ) ? sanitize_text_field( $_REQUEST['uid'] ) : false;
		if ( $lid && $uid ){
				\Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
			echo 1;
		}
		die;
}




add_action('wp_ajax_ihc_check_mail_server', 'ihc_check_mail_server');
/**
 * @param none
 * @return int
 */
function ihc_check_mail_server()
{
	 if ( !ihcIsAdmin() ){
			 echo 0;
			 die;
	 }
	 if ( !ihcAdminVerifyNonce() ){
			 echo 0;
			 die;
	 }
	 $from_email = '';
	 $from_name = '';
	 $from_email = get_option('ihc_notification_email_from');
	 if (!$from_email){
		$from_email = get_option('admin_email');
	 }
	 $from_name = get_option('ihc_notification_name');
	 if (empty($from_name)){
		$from_name = get_option("blogname");
	 }
	 $headers[] = "From: $from_name <$from_email>";
	 $headers[] = 'Content-Type: text/html; charset=UTF-8';

	 $to = get_option('admin_email');
	 $subject = get_option('blogname') . ': ' . esc_html__('Testing Your E-mail Server', 'ihc');
	 $content = esc_html__('Just a simple message to test if Your E-mail Server is working', 'ihc');
	 wp_mail($to, $subject, $content, $headers);
	 echo 1;
	 die();
}


//add_action('init', 'ihc_do_rewrite_rule', 10, 0);
/**
 * @param none
 * @return none
 */
function ihc_do_rewrite_rule()
{
	$inside_page = get_option('ihc_general_register_view_user');
	if ($inside_page && !defined('DOING_AJAX')){
		$page_slug = Ihc_Db::get_page_slug($inside_page);
		add_rewrite_rule($page_slug . "/([^/]+)/?",'index.php?pagename=' . $page_slug . '&ihc_name=$matches[1]', 'top');
		flush_rewrite_rules();
	}
}


add_action('wp_ajax_ihc_do_generate_individual_pages', 'ihc_do_generate_individual_pages');
/**
 * @param none
 * @return none
 */
function ihc_do_generate_individual_pages()
{
		if ( !ihcIsAdmin() ){
				echo 0;
		    die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
		$users = Ihc_Db::get_users_with_no_individual_page();
		if ($users){
			if (!class_exists('IndividualPage')){
				include_once IHC_PATH . 'classes/IndividualPage.class.php';
			}
			$object = new IndividualPage();
			$object->generate_pages_for_users($users);
		}
		die;
}


add_action('wp_ajax_ihc_preview_invoice_via_ajax', 'ihc_preview_invoice_via_ajax');
/**
 * @param none
 * @return none
 */
function ihc_preview_invoice_via_ajax()
{
		if ( !ihcIsAdmin() ){
			  echo 0;
			  die;
		}
		if ( !ihcAdminVerifyNonce() ){
				echo 0;
				die;
		}
		$temp = isset($_REQUEST['m'])  ? indeed_sanitize_array( $_REQUEST['m'] ) : [];
		foreach ($temp as $k=>$array){
			$metas[$array['name']] = $array['value'];
		}
		require IHC_PATH . 'classes/Ihc_Invoice.class.php';
		$object = new Ihc_Invoice(1, 0, $metas);
		echo esc_ump_content($object->output());
		die;
}

add_action('wp_ajax_ihc_make_export_file', 'ihc_make_export_file');
/**
 * @param none
 * @return none
 */
function ihc_make_export_file()
{
	////////////////// EXPORT
	global $wpdb;

	if ( !ihcIsAdmin() ){
			echo 0;
			die;
	}
	if ( !ihcAdminVerifyNonce() ){
			echo 0;
			die;
	}

	require_once IHC_PATH . 'classes/import-export/IndeedExport.class.php';
	$export = new IndeedExport();
	$hash = bin2hex( random_bytes( 20 ) );
	$filename = $hash . '.xml';
	$export->setFile( IHC_PATH . 'temporary/' . $filename );
        $importUsers = isset( $_POST['import_users'] ) ? sanitize_text_field( $_POST['import_users'] ) : false;
	if ( $importUsers ){
		////////// USERS
		$export->setEntity( array('full_table_name' => $wpdb->users, 'table_name' => 'users') );
		$export->setEntity( array('full_table_name' => $wpdb->usermeta, 'table_name' => 'usermeta') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_orders', 'table_name' => 'ihc_orders') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_orders_meta', 'table_name' => 'ihc_orders_meta') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_security_login', 'table_name' => 'ihc_security_login') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_user_levels', 'table_name' => 'ihc_user_levels') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_user_logs', 'table_name' => 'ihc_user_logs') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'indeed_members_payments', 'table_name' => 'indeed_members_payments') );
	}
        $importSettings = isset( $_POST['import_settings'] ) ? sanitize_text_field( $_POST['import_settings'] ) : false;
	if ( $importSettings ){
		///////// SETTINGS
		$values = Ihc_Db::get_all_ump_wp_options();
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'options', 'table_name' => 'options', 'values' => $values) );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_notifications', 'table_name' => 'ihc_notifications') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_invitation_codes', 'table_name' => 'ihc_invitation_codes') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_coupons', 'table_name' => 'ihc_coupons') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_debug_payments', 'table_name' => 'ihc_debug_payments') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_gift_templates', 'table_name' => 'ihc_gift_templates') );
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ihc_taxes', 'table_name' => 'ihc_taxes') );
	}
        $importPostmeta = isset( $_POST['import_postmeta'] ) ? sanitize_text_field( $_POST['import_postmeta'] ) : false;
	if ( $importPostmeta ){
		//////// POST META
		$post_meta_keys = Ihc_Db::get_post_meta_keys_used_in_ump();
		$export->setEntity( array('full_table_name' => $wpdb->prefix . 'postmeta', 'table_name' => 'postmeta', 'keys_to_select' => $post_meta_keys) );
	}
	if ($export->run()){
		/// print link to file
		echo IHC_URL . 'temporary/' . $filename;
	} else {
		/// no entity
		echo 0;
	}
	die;
}

add_action('wp_ajax_ihc_do_delete_woo_ihc_relation', 'ihc_do_delete_woo_ihc_relation');
function ihc_do_delete_woo_ihc_relation()
{
	if ( !ihcIsAdmin() ){
			echo 0;
			die;
	}
	if ( !ihcAdminVerifyNonce() ){
			echo 0;
			die;
	}
        $id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : false;
	if ( $id ){
		Ihc_Db::ihc_woo_product_custom_price_delete_item($id);
		Ihc_Db::ihc_woo_product_custom_price_lid_product_delete($id);
		echo 1;
	}
	die;
}

add_action('wp_ajax_ihc_run_custom_process', 'ihc_run_custom_process');
function ihc_run_custom_process()
{
	if ( !ihcIsAdmin() ){
			echo 0;
			die;
	}
	if ( !ihcAdminVerifyNonce() ){
			echo 0;
			die;
	}

	/// for now used only for sending drip content notifications
	require_once IHC_PATH . 'classes/DripContentNotifications.class.php';
	$object = new DripContentNotifications();
	$object->setStartBy('admin');
	die;
}

add_action( 'admin_head-nav-menus.php', 'ihc_nav_menu_hook', 99 );
function ihc_nav_menu_hook()
{
		add_meta_box( 'ihc_nav_menu_custom', esc_html__( 'Ultimate Membership Pro', 'ihc' ), 'ihc_print_custom_nav_menu', 'nav-menus', 'side', 'default' );
}
function ihc_print_custom_nav_menu()
{
		require_once IHC_PATH . 'admin/includes/tabs/custom_nav_menu_box.php';
}

add_action( 'ihc_admin_dashboard_after_top_menu', 'ihcCheckDeprecatedStripe' );
function ihcCheckDeprecatedStripe( )
{
	$checkModule = get_option('ihc_stripe_status');

	if(isset($checkModule) && $checkModule == 1){
			echo esc_ump_content('<div class="ihc-warning-box">' . esc_html__('Stripe Standard Payment Service have been shut down. Turn off this payment service and switch to latest Stripe Payment Service.', 'ihc') . '</div>');
	}

	//return TRUE;
}

add_action( 'ihc_admin_dashboard_after_top_menu', 'ihc_check_allow_fopen' );
function ihc_check_allow_fopen()
{
		$allow = ini_get( 'allow_url_fopen' );
		if (!$allow ){
				echo esc_ump_content('<div class="ihc-not-set"><strong>' . esc_html__("'allow_url_fopen' directive is disabled. In order for Ultimate Membership Pro to work properly this directive has to be set 'on'. Contact your hosting provider for more details.", 'ihc') . ' </strong></div>');
		}

				// crons
				$wp_cron = ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) ? FALSE : TRUE;
				if (!$wp_cron ){
						echo esc_ump_content('<div class="ihc-not-set">' . esc_html__('Crons are disabled on your WordPress Website. Some functionality and processes may not work properly.', 'ihc') . '</div>');
				}

				// crop image
				$cropFunctions = [
													'getimagesize',
													'imagecreatefrompng',
													'imagecreatefromjpeg',
													'imagecreatefromgif',
													'imagecreatetruecolor',
													'imagecopyresampled',
													'imagerotate',
													'imagesx',
													'imagesy',
													'imagecolortransparent',
													'imagecolorallocate',
													'imagejpeg',
				];
				foreach ( $cropFunctions as $cropFunction ){
						if ( !function_exists( $cropFunction ) ){
								$functionsErrors[] = $cropFunction .'()';
						}
				}
				if ( !empty($functionsErrors) ){
						echo esc_ump_content('<div class="ihc-not-set">' . esc_html__('Following functions: ', 'ihc') . implode( ', ', $functionsErrors )
						. esc_html__( ' are disabled on your Website environment. Avatar feature may not work properly. Please contract your Hosting provider.', 'ihc')
						. '</div>');
				}

}

add_action( 'ump_admin_after_top_menu_add_ons', 'ihc_after_header_for_addons' );
function ihc_after_header_for_addons()
{
		echo ihc_check_default_pages_set();//set default pages message
		echo ihc_check_payment_gateways();
		echo ihc_is_curl_enable();

		do_action( "ihc_admin_dashboard_after_top_menu" );
}

add_action( 'ump_print_admin_page', 'ihc_listen_hooks_on_admin', 1, 1 );
function ihc_listen_hooks_on_admin( $tab='' )
{
		if ( $tab != 'hooks' ){
				return;
		}
		$object = new \Indeed\Ihc\SearchFiltersAndHooks();
		$object->setPluginName( 'indeed-membership-pro' )->setNameShouldContain( [ 'ihc', 'ump' ] )->SearchFiles( IHC_PATH );
		$data = $object->getResults();
		$view = new \Indeed\Ihc\IndeedView();
		$output = $view->setTemplate( IHC_PATH . 'admin/includes/tabs/hooks.php' )
							->setContentData( $data )
							->getOutput();
                echo esc_ump_content( $output );
}

add_action('wp_ajax_ihc_admin_delete_register_field', 'ihc_admin_delete_register_field');
function ihc_admin_delete_register_field()
{
		if ( !ihcIsAdmin() ){
				die;
		}
		if ( !ihcAdminVerifyNonce() ){
				die;
		}
                $id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : false;
		if ( !$id ){
				die;
		}
		require_once IHC_PATH . 'admin/includes/functions/register.php';
		ihc_delete_user_field( $id );//delete user custom fields
		die;
}



/**
 * Plugin row meta links
 *
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function ump_plugin_row_meta( $input, $file ) {

	if ( $file != 'indeed-membership-pro/indeed-membership-pro.php' ) {
		return $input;
	}

	$links = [
		'<a href="https://ultimatemembershippro.com/documentation/" target="_blank">' . esc_html__( 'Documentation', 'ihc' ) . '</a>',
		'<a href="https://ultimatemembershippro.com/pro-addons/" target="_blank">' . esc_html__( 'Pro AddOns', 'ihc' ) . '</a>',
		'<a href="https://ultimatemembershippro.com/changelog/" target="_blank">' . esc_html__( 'ChangeLog', 'ihc' ) . '</a>'
	];

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'ump_plugin_row_meta', 10, 2 );

// on user delete - delete his media files
$ihcHandleDeleteMedia = new \Indeed\Ihc\Admin\HandleDeleteMedia();
$ihcEvents = new \Indeed\Ihc\Admin\Events();
function ump_admin_count_users_for()
{
		$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
		$baners = new $class();
		if ( (int)$baners->FGCS() === 2){
				echo '<div' .  ' class="ihc'. '-error'. '-global' .'-dashboard'.'-message'.'">'. 'Yo'. 'ur '.'li'.'cen'.'se'.' i'. 's'. ' no'.'t'. 'act'. 'iv'.'e</div>';
		}
		die;
}
