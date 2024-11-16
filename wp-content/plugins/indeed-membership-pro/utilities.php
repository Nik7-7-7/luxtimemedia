<?php
function ihc_post_metas($post_id=null, $return_name=FALSE){
	/*
	 * @param int, bool
	 * @return array
	 */
	$arr = array(
					'ihc_mb_type' 										=> 'show',
					'ihc_mb_who' 											=> '',
					'ihc_mb_block_type' 							=> 'redirect',
					'ihc_mb_redirect_to' 							=> -1,
					'ihc_replace_content' 						=> '',
					//DRIP CONTENT
					'ihc_drip_content' 								=> 0,
					'ihc_drip_start_type' 						=> 1,
					'ihc_drip_end_type' 							=> 1,
					'ihc_drip_start_numeric_type' 		=> 'days',
					'ihc_drip_start_numeric_value' 		=> '',
					'ihc_drip_end_numeric_type' 			=> 'days',
					'ihc_drip_end_numeric_value' 			=> '',
					'ihc_drip_start_certain_date' 		=> '',
					'ihc_drip_end_certain_date' 			=> '',
	);

	$arr = apply_filters( 'ihc_filter_post_meta', $arr );

	if($return_name==TRUE){
		return $arr;
	}
	foreach($arr as $k=>$v){
		$data = get_post_meta($post_id, $k, true);
		if( $data!==FALSE && $data!='' ){
			$arr[$k] = $data;
		}
	}
	return $arr;
}

function ihc_get_all_pages(){
	/*
	 * @param none
	 * @return array
	 */
	$arr = array();
	$args = array(
			'sort_order' => 'ASC',
			'sort_column' => 'post_title',
			'hierarchical' => 1,
			'child_of' => 0,
			'parent' => -1,
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
	);
	$pages = get_pages($args);
	if (isset($pages) && count($pages)>0){
		foreach ($pages as $page){
			if ($page->post_title==''){
				$page->post_title = '(no title)';
			}
			$arr[$page->ID] = $page->post_title;
		}
	}
	return $arr;
}


function ihc_locker_meta_keys(){
	/*
	 * @param none
	 * @return array
	 */
	//meta keys for ihc_lockers
	$arr = array(
					'ihc_locker_name' => 'Untitled Locker',
					'ihc_locker_custom_content' => '<h2>This content is locked</h2>
													Login To Unlock The Content!',
					'ihc_locker_custom_css' => '',
					'ihc_locker_template' => '',
					'ihc_locker_login_template' => '',
					'ihc_locker_login_form' => 1,
					'ihc_locker_additional_links' => 1,
					'ihc_locker_display_sm' => 0,
				 );
	return $arr;
}

/**
 * @param string, string|bool
 * @return mixed
 */
function ihc_return_meta($name=null, $id=false){
	$data = get_option($name);
	if ($data!==FALSE){
		if($data && isset($data[$id])){
			return $data[$id];
		}
		return $data;
	}
	else return FALSE;
}

function ihc_return_meta_arr($type=null, $only_name=false, $return_default=false){
	/*
	 * @param string, bool, bool
	 * @return array
	 */
	//all metas
	if (!defined('IHC_URL')){
		define('IHC_URL', plugin_dir_url(__FILE__));
	}
	switch ($type){
		case 'payment':
			$arr = array(
							'ihc_currency' 								=> 'USD',
							'ihc_currency_position' 			=> 'right',
							'ihc_num_of_decimals'					=> 2,
							'ihc_decimals_separator'			=> '.',
							'ihc_thousands_separator'			=> ',',
							'ihc_custom_currency_code' 		=> '',
							'ihc_payment_set' 						=> 'predefined',
							'ihc_payment_selected' 				=> 'bank_transfer',
							'ihc_payment_logs_on' 				=> 0,
							'ihc_payment_workflow'				=> 'new',
							'ihc_payment_merchant_business_name'			=> '',
							'ihc_payment_merchant_business_address_1'	=> '',
							'ihc_payment_merchant_business_address_2' => '',
							'ihc_payment_merchant_business_country'		=> '',
							'ihc_payment_merchant_business_city'			=> '',
							'ihc_payment_merchant_business_state'			=> '',
							'ihc_payment_merchant_business_postcode'	=> '',
							'ihc_payment_merchant_business_vat'				=> '',
							'ihc_payment_taxes_included_in_amount'		=> 0,
						);
		break;
		case 'payment_paypal':
			$arr = array(
							'ihc_paypal_email' => '',
							'ihc_paypal_sandbox' => 0,
							'ihc_paypal_return_page' => -1,
							'ihc_paypal_return_page_on_cancel'	=> -1,
							'ihc_paypal_status' => 0,
							'ihc_paypal_label' => 'PayPal',
							'ihc_paypal_select_order' => 1,
							/*developer */
							'ihc_paypal_short_description' => '',
							/*end developer*/
							'ihc_paypal_merchant_account_id' => '',
							'ihc_paypapl_locale_code' => 'en_US',
						);
		break;
		case 'payment_authorize':
			$arr = array(
							'ihc_authorize_login_id' => '',
							'ihc_authorize_transaction_key' => '',
							'ihc_authorize_sandbox' => 0,
							'ihc_authorize_status' => 0,
							'ihc_authorize_label' => 'Authorize',
							'ihc_authorize_select_order' => 3,
							'ihc_authorize_short_description' => ''
			);
		break;
		case 'payment_twocheckout':
			$arr = array(
							'ihc_twocheckout_status' => 0,
							'ihc_twocheckout_sandbox' => 0,
							'ihc_twocheckout_api_user' => '',
							'ihc_twocheckout_api_pass' => '',
							'ihc_twocheckout_private_key' => '',
							'ihc_twocheckout_account_number' => '',
							'ihc_twocheckout_secret_word' => '',
							'ihc_twocheckout_return_url'	=> -1,
							'ihc_twocheckout_label' => '2Checkout',
							'ihc_twocheckout_select_order' => 4,
							'ihc_twocheckout_short_description' => ''
			);
		break;
		case 'payment_bank_transfer':
			$arr = array(
					'ihc_bank_transfer_status' => 1,
					'ihc_bank_transfer_message' => '
<p>Please proceed the bank transfer payment for: {currency}{amount}</p>

<p><strong>Payment Details:</strong> Subscription {level_name} for {username} with Identification: {user_id}_{level_id}</p>

<br/>

<strong>Bank Details:</strong><br/>

IBAN:xxxxxxxxxxxxxxxxxxxx<br/>

Bank NAME<br/>',
					'ihc_bank_transfer_label' => 'Bank Transfer',
					'ihc_bank_transfer_select_order' => 5,
					'ihc_bank_transfer_short_description' => ''
			);
		break;
		case 'payment_braintree':
			$arr = array(
					'ihc_braintree_status' => 0,
					'ihc_braintree_sandbox' => 0,
					'ihc_braintree_merchant_id' => '',
					'ihc_braintree_public_key' => '',
					'ihc_braintree_private_key' => '',
					'ihc_braintree_label' => 'Braintree',
					'ihc_braintree_select_order' => 6,
					'ihc_braintree_short_description' => ''
			);
			break;
		case 'payment_mollie':
			$arr = array(
					'ihc_mollie_status' 					=> 0,
					'ihc_mollie_api_key' 					=> '',
					'ihc_mollie_label' 						=> 'Mollie',
					'ihc_mollie_select_order' 		=> 8,
					'ihc_mollie_short_description' => '',
					'ihc_mollie_return_page'			=> -1,
			);
			break;
		case 'payment_paypal_express_checkout':
			$arr = array(
						'ihc_paypal_express_checkout_status' 						=> 0,
						'ihc_paypal_express_checkout_label' 						=> 'PayPal Express Checkout',
						'ihc_paypal_express_checkout_signature'					=> '',
						'ihc_paypal_express_checkout_user'							=> '',
						'ihc_paypal_express_checkout_password'					=> '',
						'ihc_paypal_express_checkout_sandbox' 					=> 0,
						'ihc_paypal_express_checkout_select_order' 			=> 9,
						'ihc_paypal_express_short_description'					=> '',
						'ihc_paypal_express_return_page'								=> -1,
						'ihc_paypal_express_return_page_on_cancel'			=> -1,
			);
			break;
		case 'payment_pagseguro':
			$arr = array(
						'ihc_pagseguro_status' 						=> 0,
						'ihc_pagseguro_label' 						=> 'Pagseguro',
						'ihc_pagseguro_email'							=> '',
						'ihc_pagseguro_token'							=> '',
						'ihc_pagseguro_select_order' 			=> 9,
						'ihc_pagseguro_short_description' => '',
						'ihc_pagseguro_sandbox' 					=> 0,
			);
			break;
		case 'payment_stripe_checkout_v2':
			$arr = array(
						'ihc_stripe_checkout_v2_secret_key'					=> '',
						'ihc_stripe_checkout_v2_publishable_key'		=> '',
						'ihc_stripe_checkout_v2_status'							=> 0,
						'ihc_stripe_checkout_v2_select_order' 			=> 11,
						'ihc_stripe_checkout_v2_short_description' => '',
						'ihc_stripe_checkout_v2_locale_code' 				=> 'en',
						'ihc_stripe_checkout_v2_label' 							=> 'Stripe Checkout',
						'ihc_stripe_checkout_v2_success_page'				=> -1,
						'ihc_stripe_checkout_v2_cancel_page'				=> -1,
						'ihc_stripe_checkout_v2_use_user_email'			=> 0,
			);
			break;
		case 'payment_stripe_connect':
			$arr = array(
							'ihc_stripe_connect_status'									=> 0,
							'ihc_stripe_connect_payment_request'				=> 0,
							'ihc_stripe_connect_saved_cards'						=> 0,
							'ihc_stripe_connect_live_mode'							=> 0,
							'ihc_stripe_connect_publishable_key'				=> '',
							'ihc_stripe_connect_client_secret'					=> '',
							'ihc_stripe_connect_account_id'							=> '',
							'ihc_stripe_connect_test_publishable_key'		=> '',
							'ihc_stripe_connect_test_client_secret'			=> '',
							'ihc_stripe_connect_test_account_id'				=> '',
							'ihc_stripe_connect_select_order' 					=> 1,
							'ihc_stripe_connect_short_description' 			=> '',
							'ihc_stripe_connect_descriptor'							=> '',
							'ihc_stripe_connect_locale_code' 						=> 'auto',
							'ihc_stripe_connect_label' 									=> 'Credit Card',
			);
			break;
		case 'login':
			$arr = array(
						   'ihc_login_remember_me' => 1,
						   'ihc_login_register' => 1,
						   'ihc_login_pass_lost' => 1,
						   'ihc_login_template' => 'ihc-login-template-11',
						   'ihc_login_custom_css' => '',
						   'ihc_login_show_sm' => 0,
						   'ihc_login_show_recaptcha' => 0,
						);
		break;
		case 'login-messages':
			$arr = array(
							'ihc_login_succes' => 'Welcome to our Website!',
							'ihc_login_pending' => 'Your account has not been approved. Please retry later',
							'ihc_social_login_failed' => 'You are not registered with this social network. Please register first!',
							'ihc_login_error' => 'Invalid email address or password entered',
							'ihc_reset_msg_pass_err' => 'Invalid email address or username entered',
							'ihc_reset_msg_pass_ok' => 'A new password has been sent to your email address',
							'ihc_login_error_email_pending' => 'The email address has not been confirmed yet',
							'ihc_login_error_on_captcha' => 'Error with Captcha',
							'ihc_login_error_ajax' => 'Please fill out all mandatory fields',
						);
		break;
		case 'general-defaults':
			$arr = array(
							//default pages
							'ihc_general_login_default_page' => '',
							'ihc_general_register_default_page'=>'',
							'ihc_general_lost_pass_page' => '',
							'ihc_general_logout_page' => '',
							'ihc_general_user_page' => '',
							'ihc_general_tos_page' => '',
							'ihc_subscription_plan_page' => '',
							'ihc_checkout_page' => '',
							'ihc_thank_you_page' => '',
							'ihc_general_register_view_user' => '',
							//redirects
							'ihc_general_redirect_default_page' => '',
							'ihc_general_logout_redirect' => '',
							'ihc_general_register_redirect' => '',
							'ihc_general_login_redirect' => '',
							'ihc_general_password_redirect' => '',
							/// prevent listing hidden post, pages
							'ihc_listing_show_hidden_post_pages' => 0,
						);
		break;
		case 'general-captcha':
			//recapcha
			$arr = array(
							'ihc_recaptcha_version'							=> 'v2',
							'ihc_recaptcha_public' 							=> '',
							'ihc_recaptcha_private' 						=> '',
							'ihc_recaptcha_public_v3'						=> '',
							'ihc_recaptcha_private_v3'					=> '',
			);
		break;
		case 'general-subscription':
			$arr = array(
							'ihc_level_template' => 'ihc_level_template_5',
							'ihc_select_level_custom_css' => '',
						);
		break;
		case 'general-msg':
			$arr = array(
							'ihc_general_update_msg' => 'Successfully Updated!',
						);
		break;
		case 'register':
			$arr = array(
							'ihc_register_template' => 'ihc-register-9',
							'ihc_register_admin_notify' => 1,
							'ihc_register_pass_min_length' => 6,
							'ihc_register_pass_options' => 1,
							'ihc_register_new_user_level' => -1,//'none'
							'ihc_register_new_user_role' => 'subscriber',
							'ihc_register_custom_css' => '',
							'ihc_register_terms_c' => 'Accept our Terms&Conditions',
							'ihc_subscription_type' => 'predifined_level',
							'ihc_register_opt-in' => 0,
							'ihc_register_opt-in-type' => 'email_list',
							'ihc_register_show_level_price' => 1,
							'ihc_register_auto_login' => 0,
							'ihc_register_double_email_verification' => 0,
							'ihc_automatically_switch_role' => 0,
							'ihc_automatically_new_role' => 'subscriber',
							'ihc_register_button_label' => '',
						);
		break;
		case 'register-msg':
			$arr = array(
							//messages
							'ihc_register_username_taken_msg' => 'Username has already been taken',
							'ihc_register_error_username_msg' => 'Invalid Username',
							'ihc_register_email_is_taken_msg' => 'Email address has already been taken',
							'ihc_register_invalid_email_msg' => 'You must have a valid email address',
							'ihc_register_emails_not_match_msg' => 'Email Addresses do not match',
							'ihc_register_pass_not_match_msg' => 'New passwords do not match',
							'ihc_register_pass_letter_digits_msg' => 'Password must contain both letters and numbers',
							'ihc_register_pass_let_dig_up_let_msg' => 'Password must include characters, numbers, and at least one uppercase letter',
							'ihc_register_pass_min_char_msg' => 'Password must include minimum {X} characters',
							'ihc_register_pending_user_msg' => 'Your Account has not been approved yet. Please retry later',
							'ihc_register_err_req_fields' => 'Please fill out all mandatory fields',
							'ihc_register_err_recaptcha' => 'Error with Captcha',
							'ihc_register_err_tos' => 'Please agree to our Terms & Conditions',
							'ihc_register_success_meg' => '<h4>Successfully Register!</h4>
<br/>',
							'ihc_register_update_msg' => 'Successfully Updated!',
							'ihc_register_unique_value_exists' => 'This value is already exist.',
						);
		break;
		case 'register-custom-fields':
			$arr = array(
							'ihc_user_fields' => ihc_native_user_field(),
						);
		break;
		case 'opt_in':
			$arr = array(
							//aweber
							'ihc_aweber_auth_code' => '',
							'ihc_aweber_list' => '',
							'ihc_aweber_consumer_key' => '',
							'ihc_aweber_consumer_secret' => '',
							'ihc_aweber_acces_key' => '',
							'ihc_aweber_acces_secret' => '',
							//mailchimp
							'ihc_mailchimp_api' => '',
							'ihc_mailchimp_id_list' => '',
							//get response
							'ihc_getResponse_api_key' => '',
							'ihc_getResponse_token' => '',
							//campaign monitor
							'ihc_cm_api_key' => '',
							'ihc_cm_list_id' => '',
							//icontact
							'ihc_icontact_user' => '',
							'ihc_icontact_appid' => '',
							'ihc_icontact_pass' => '',
							'ihc_icontact_list_id' => '',
							//constant contact
							'ihc_cc_user' => '',
							'ihc_cc_pass' => '',
							'ihc_cc_list' => '',

							// version 12.5
							'ihc_cc_email_service_refresh_token'	=> '',
							'ihc_cc_email_service_client_id'			=> '',
							'ihc_cc_email_service_client_secret'	=> '',
							'ihc_cc_email_service_access_token'		=> '',
							'ihc_cc_email_service_refresh_token'	=> '',
							'ihc_cc_email_service_default_list'		=> '',
							// end of version 12.5

							//Wysija Contact
							'ihc_wysija_list_id' => '',
							//MyMail
							'ihc_mymail_list_id' => '',
							//Mad Mimi
							'ihc_madmimi_username' => '',
							'ihc_madmimi_apikey' => '',
							'ihc_madmimi_listname' => '',
							//indeed email list
							'ihc_email_list' => '',
							// active campaign
							'ihc_active_campaign_apiurl' => '',
							'ihc_active_campaign_apikey' => '',
							'ihc_active_campaign_listId' => '',
						);
		break;
		case 'notifications':
			$arr = array(
							'ihc_notification_email_from' => '',
							'ihc_notification_before_time' => 5,
							'ihc_notification_before_time_second' => 3,
							'ihc_notification_before_time_third' => 1,
							'ihc_notification_payment_due_time_interval'	=> 1,
							'ihc_notification_card_expiry_time_interval'	=> 1,
							'ihc_notification_name' => '',
							'ihc_notification_email_addresses' => '',
						);
		break;
		case 'extra_settings':
			$arr = array(
							'ihc_grace_period' => '',
							'ihc_debug_payments_db' => '',
							'ihc_upload_extensions' => 'txt,doc,pdf,jpg,jpeg,png,gif,mp3,zip',
							'ihc_upload_max_size' => 5,
							'ihc_avatar_max_size' => 1,
						);
			break;
		case 'account_page':
			$arr = array(	'ihc_ap_theme' => 'ihc-ap-theme-3',
							'ihc_ap_edit_show_avatar' => 1,
							'ihc_ap_edit_show_level' => 1,
							'ihc_ap_tabs' => 'overview,profile,subscription,logout,help,transactions,orders,social',
							'ihc_ap_welcome_msg' => '<span class="iump-user-page-mess-special">Hello</span> <span class="iump-user-page-mess-special"> {last_name} {first_name}</span>,
														<span class="iump-user-page-mess">You are logged as</span><span class="iump-user-page-mess-special"> {username}</span>
														<div class="iump-user-page-mess"><span>{flag}</span>Member since {user_registered}</div>
														',
							'ihc_account_page_custom_css' => '',
							'ihc_ap_social_plus_message' => '',

							'ihc_ap_overview_menu_label' => 'Dashboard',
							'ihc_ap_overview_title' => 'Dashboard',
							'ihc_ap_overview_msg' => '<p>Welcome to our Membership platform. Check for valuable content and sign to our Subscriptions.</p><p>From Membership dashboard you may manage <strong>your Subscriptions</strong>, check <strong>recent payments</strong> or edit your <strong>account details</strong>.</p>',
							'ihc_ap_overview_icon_class' => '',
							'ihc_ap_overview_icon_code' => 'f015',
							'ihc_ap_profile_menu_label' => 'Profile Details',
							'ihc_ap_profile_title' => 'Edit your Account',
							'ihc_ap_profile_msg' => '',
							'ihc_ap_profile_icon_class' => '',
							'ihc_ap_profile_icon_code' => 'f007',
							'ihc_ap_subscription_menu_label' => 'Subscriptions',
							'ihc_ap_subscription_title' => '',
							'ihc_ap_subscription_msg' => '',
							'ihc_ap_subscription_icon_class' => '',
							'ihc_ap_subscription_icon_code' => 'f0a1',
							'ihc_ap_subscription_table_enable' => 1,
							'ihc_ap_subscription_plan_enable' => 1,
							'ihc_ap_social_menu_label' => 'Social Connection',
							'ihc_ap_social_title' => 'Social Connection',
							'ihc_ap_social_icon_class' => '',
							'ihc_ap_social_icon_code' => 'f0e6',
							'ihc_ap_social_msg' => '',
							'ihc_ap_transactions_menu_label' => 'Transactions',
							'ihc_ap_transactions_title' => 'Transactions',
							'ihc_ap_transactions_msg' => '',
							'ihc_ap_transactions_icon_class' => '',
							'ihc_ap_transactions_icon_code' => 'f155',
							'ihc_ap_orders_menu_label' => 'Payments',
							'ihc_ap_orders_title' => 'Payments',
							'ihc_ap_orders_msg' => '',
							'ihc_ap_orders_icon_class' => '',
							'ihc_ap_orders_icon_code' => 'f0d6',
							'ihc_ap_membeship_gifts_menu_label' => 'Membership Gifts',
							'ihc_ap_membeship_gifts_title' => 'Membership Gifts',
							'ihc_ap_membeship_gifts_msg' => '[ihc-list-gifts]',
							'ihc_ap_membeship_gifts_icon_class' => '',
							'ihc_ap_membeship_gifts_icon_code' => 'f06b',
							'ihc_ap_membership_cards_menu_label' => 'Membership Cards',
							'ihc_ap_membership_cards_title' => 'Membership Cards',
							'ihc_ap_membership_cards_msg' => '[ihc-membership-card]',
							'ihc_ap_membership_cards_icon_class' => '',
							'ihc_ap_membership_cards_icon_code' => 'f022',
							'ihc_ap_help_menu_label' => 'Help',
							'ihc_ap_help_title' => 'Help',
							'ihc_ap_help_msg' => 'If you have any questions or need help, please do not hesitate to contact us.',
							'ihc_ap_help_icon_class' => '',
							'ihc_ap_help_icon_code' => 'f059',
							'ihc_ap_pushover_notifications_menu_label' => 'Pushover Notifications',
							'ihc_ap_pushover_notifications_title' => 'Pushover Notifications',
							'ihc_ap_pushover_notifications_msg' => '',
							'ihc_ap_pushover_notifications_icon_class' => '',
							'ihc_ap_pushover_notifications_icon_code' => 'f0f3',
							'ihc_ap_logout_menu_label' => 'LogOut',
							'ihc_ap_logout_icon_class' => '',
							'ihc_ap_logout_icon_code' => 'f08b',
							'ihc_ap_affiliate_icon_class' => '',
							'ihc_ap_affiliate_icon_code' => 'f0e8',

							'ihc_ap_user_sites_label' => 'Your Sites',
							'ihc_ap_user_sites_title' => 'Your Sites',
							'ihc_ap_user_sites_icon_code' => 'f0e8',
							'ihc_ap_user_sites_icon_class' => '',
							'ihc_ap_user_sites_msg' => '',

							'ihc_ap_footer_msg' => '',
							'ihc_ap_top_background_image' => '',
							'ihc_ap_edit_background' => 1,
							'ihc_ap_top_template' => 'ihc-ap-top-theme-4',
							'ihc_ap_edit_show_level' => 1,

							'ihc_account_page_orders_show_table'					=> 1,
							'ihc_account_page_pushover_show_form'					=> 1,
							'ihc_account_page_user_sites_show_table'			=> 1,
							'ihc_account_page_social_plus_show_buttons'		=> 1,
							'ihc_account_page_profile_show_form'					=> 1,
					);
			break;
		case 'fb':
			$arr = array(
							'ihc_fb_app_id' => '',
							'ihc_fb_app_secret' => '',
							'ihc_fb_status' => 0,
						);
			break;
		case 'tw':
			$arr = array(
							'ihc_tw_app_key' => '',
							'ihc_tw_app_secret' => '',
							'ihc_tw_status' => 0,
			);
			break;
		case 'in':
			$arr = array(
							'ihc_in_app_key' => '',
							'ihc_in_app_secret' => '',
							'ihc_in_status' => 0,
			);
			break;
		case 'tbr':
			$arr = array(
							'ihc_tbr_app_key' => '',
							'ihc_tbr_app_secret' => '',
							'ihc_tbr_status' => 0,
			);
			break;
		case 'ig':
				$arr = array(
					'ihc_ig_app_id' => '',
					'ihc_ig_app_secret' => '',
					'ihc_ig_status' => 0,
				);
			break;
		case 'vk':
				$arr = array(
					'ihc_vk_app_id' => '',
					'ihc_vk_app_secret' => '',
					'ihc_vk_status' => 0,
				);
			break;
		case 'goo':
				$arr = array(
					'ihc_goo_app_id' => '',
					'ihc_goo_app_secret' => '',
					'ihc_goo_status' => 0,
				);
			break;
		case 'social_media':
			$arr = array(
							"ihc_sm_template" => "ihc-sm-template-1",
							"ihc_sm_custom_css" => "",
							"ihc_sm_show_label" => 1,
							'ihc_sm_top_content' => '<div class="ihc-top-social-login"> - OR - </div>',
							'ihc_sm_bottom_content' => '',
						);
			break;
		case 'double_email_verification':
			$arr = array(
							'ihc_double_email_expire_time' => -1,
							'ihc_double_email_redirect_success' => '',
							'ihc_double_email_redirect_error' => '',
							'ihc_double_email_delete_user_not_verified' => -1,
						);
			break;
		case 'listing_users':
			$arr = array(
							'ihc_listing_users_custom_css' => '',
							'ihc_listing_users_responsive_small' => 1,
							'ihc_listing_users_responsive_medium' => 2,
							'ihc_listing_users_responsive_large' => 0,
							'ihc_listing_users_target_blank' => 0,
						);
			break;
		case 'listing_users_inside_page':
			$arr = array(
							'ihc_listing_users_inside_page_content' => '<div class="iump-user-page-avatar">
<img src="{AVATAR_HREF}" />
</div>
<div class="ihc-account-page-top-mess">
<p><span class="iump-user-page-name"> {first_name} {last_name}</span>,</p>
<p><span class="iump-user-page-mess">Username:</span><span class="iump-user-page-mess-special"> {username}</span>
</p>
<p><span class="iump-user-page-mess">and his/her awesome e-mail address is : <strong>{user_email}</strong></span></p>
{IHC_SOCIAL_MEDIA_LINKS}
</div>
<div class="iump-clear"></div>',
							'ihc_listing_users_inside_page_custom_css' => '',
							'ihc_listing_users_inside_page_type' => 'basic',
							'ihc_listing_users_inside_page_show_avatar' => 1,
							'ihc_listing_users_inside_page_show_level' => 1,
							'ihc_listing_users_inside_page_show_banner' => 1,
							'ihc_listing_users_inside_page_show_since' => 1,
							'ihc_listing_users_inside_page_show_name' => 1,
							'ihc_listing_users_inside_page_show_username' => 1,
							'ihc_listing_users_inside_page_show_email' => 1,
							'ihc_listing_users_inside_page_show_website' => 1,
							'ihc_listing_users_inside_page_show_flag' => 1,
							'ihc_listing_users_inside_page_show_custom_fields' => '',
							'ihc_listing_users_inside_page_extra_custom_content' => '',
							'ihc_listing_users_inside_page_color_scheme' => '',
							'ihc_listing_users_inside_page_template' => 'template-1',
							'ihc_listing_users_inside_page_banner_href' => '',
			);
			break;
		case 'affiliate_options':
			$arr = array(
							'ihc_ap_show_aff_tab' => 0,
							'ihc_ap_aff_msg' => '[uap-user-become-affiliate]',
			);
			break;
		case 'ihc_taxes_settings':
			$arr = array(
							'ihc_enable_taxes' => 0,
							'ihc_show_taxes' => 0,
							'ihc_default_tax_label' => '',
							'ihc_default_tax_value' => 0,
			);
			break;
		case 'admin_workflow':
			$arr = array(
							'ihc_admin_workflow_dashboard_notifications'  => 1,
							'ihc_debug_payments_db' 											=> '',
							'ihc_order_prefix_code' 											=> 'IUMP',
							'ihc_keep_data_after_delete'									=> 0,
							'ihc_wp_login_custom_css'											=> 1,
							'ihc_wp_login_logo_image'											=> '',
			);
			break;
		case 'public_workflow':
			$arr = array(
							'ihc_listing_show_hidden_post_pages' 	=> 0,
							'ihc_grace_period' 										=> '',
							'ihc_use_gravatar' 										=> 1,
							'ihc_use_buddypress_avatar' 					=> 0,
							'ihc_email_blacklist' 								=> '',
							'ihc_default_country'									=> 'US',
							'ihc_pretty_links'										=> 0,
			);
			break;
	  case 'security':
				$arr = array(
								'ihc_security_allow_search_engines' 			=> 0,
								'ihc_security_username' 									=> '',
								'ihc_email_blacklist'											=> '',
								'ihc_security_block_username_message' 		=> '',
								'ihc_security_rename_wpadmin'							=> 0,
								'ihc_security_rename_wpadmin_name'				=> '',
								'ihc_security_restrict_everything'				=> 0,
								'ihc_security_restrict_everything_except' => '',
				);
				break;
		  case 'debug':
					$arr = array(
									'ihc_prevent_use_of_do_shortcode_on_content' 			=> 0,
									'ihc_allow_tracking'															=> 0,
					);
					break;
		case 'ihc_woo':
			$arr = array(
							'ihc_woo_account_page_enable' => 0,
							'ihc_woo_account_page_name' => '',
							'ihc_woo_account_page_menu_position' => 5,
			);
			break;
		case 'prorate_subscription':
			$arr = array(
							'ihc_prorate_subscription_enabled' 										=> 0,
							'ihc_prorate_subscription_reset_billing_period'				=> 1,
							'ihc_prorate_show_details_on_checkout'								=> 0,
							'ihc_prorate_button_label'														=> 'Change Plan',
			);
			break;
		case 'ihc_bp':
			$arr = array(
							'ihc_bp_account_page_enable' => 0,
							'ihc_bp_account_page_name' => '',
							'ihc_bp_account_page_position' => 5,
			);
			break;
		case 'ihc_membership_card':
			$arr = array(
							'ihc_membership_card_enable' => 0,
							'ihc_membership_card_background_color' => '',
							'ihc-membership-card-settings-image-type' => 1,
							'ihc_membership_card_image' => IHC_URL . 'assets/images/default-logo.png',
							'ihc_membership_card_size' => 'ihc-membership-card-medium',
							'ihc_membership_card_template' => 'ihc-membership-card-2',
							'ihc_membership_member_since_enable' => 1,
							'ihc_membership_member_since_label' => esc_html__('Member Since: ', 'ihc'),
							'ihc_membership_member_level_label' => esc_html__('Membership: ', 'ihc'),
							'ihc_membership_member_level_expire' => 1,
							'ihc_membership_member_level_expire_label' => esc_html__('Expires On: ', 'ihc'),
							'ihc_membership_member_show_uid' => 0,
							'ihc_membership_member_show_extrafields' => '',
							'ihc_membership_member_uid_label' => esc_html__('Member ID:', 'ihc'),
							'ihc_membership_card_custom_css' => '',
							'ihc_membership_card_exclude_levels' => '',
			);
			break;
		case 'ihc_cheat_off':
			$arr = array(
							'ihc_cheat_off_enable' => 0,
							'ihc_cheat_off_cookie_time' => 365,
							'ihc_cheat_off_redirect' => '',
			);
			break;
		case 'ihc_invitation_code':
			$arr = array(
							'ihc_invitation_code_enable' => 0,
							'ihc_invitation_code_err_msg' => esc_html__('Your Invitation Code is wrong.', 'ihc'),
			);
			break;
		case 'download_monitor_integration':
			$arr = array(
							'ihc_download_monitor_enabled' => 0,
							'ihc_download_monitor_limit_type' => 'files',
							'ihc_download_monitor_values' => '',
			);
			break;
		case 'register_lite':
			$arr = array(
							'ihc_register_lite_enabled' => 0,
							'ihc_register_lite_template' => 'ihc-register-3',
							'ihc_register_lite_custom_css' => '',
							'ihc_register_lite_opt_in' => 0,
							'ihc_register_lite_opt_in_type' => '',
							'ihc_register_lite_double_email_verification' => '',
							'ihc_register_lite_user_role' => 'subscriber',
							'ihc_register_lite_auto_login' => 1,
							'ihc_register_lite_redirect' => '',
			);
			break;
		case 'individual_page':
			$arr = array(
							'ihc_individual_page_enabled' => 0,
							'ihc_individual_page_parent' => -1,
							'ihc_individual_page_default_content' => '',
							'ihc_individual_page_title' => 'IUMP Individual Page: {username}',
							'ihc_individual_page_slug_prefix' => 'iump_individual_page_',
			);
			break;
		case 'level_restrict_payment':
			$arr = array(
							'ihc_level_restrict_payment_enabled' => 0,
							'ihc_levels_default_payments' => '',
							'ihc_level_restrict_payment_values' => '',
			);
			break;
		case 'level_subscription_plan_settings':
			$arr = array(
							'ihc_level_subscription_plan_settings_enabled' => 0,
							'ihc_level_subscription_plan_settings_restr_levels' => '',
							'ihc_level_subscription_plan_settings_condt' => '',
			);
			break;
		case 'gifts':
			$arr = array(
							'ihc_gifts_enabled' => 0,
							'ihc_gifts_user_get_multiple_on_recurring' => 0,
			);
			break;
		case 'login_level_redirect':
			$arr = array(
							'ihc_login_level_redirect_on' => 0,
							'ihc_login_level_redirect_rules' => '',
							'ihc_login_level_redirect_priority' => '',
			);
			break;
		case 'register_redirects_by_level':
			$arr = array(
							'ihc_register_redirects_by_level_enable' => 0,
							'ihc_register_redirects_by_level_rules' => '',
			);
			break;
		case 'wp_social_login':
			$arr = array(
							'ihc_wp_social_login_on' => 0,
							'ihc_wp_social_login_redirect_page' => '',
							'ihc_wp_social_login_default_role' => '',
							'ihc_wp_social_login_default_level' => '',
			);
			break;
		case 'list_access_posts':
			$arr = array(
							'ihc_list_access_posts_on' => 0,
							'ihc_list_access_posts_title' => '',
							'ihc_list_access_posts_item_details' => 'post_title',
							'ihc_list_access_posts_custom_css' => '',
							'ihc_list_access_posts_order_by' => 'post_date',
							'ihc_list_access_posts_order_type' => 'DESC',
							'ihc_list_access_posts_template' => '',
							'ihc_list_access_posts_order_limit' => '',
							'ihc_list_access_posts_per_page_value' => 25,
							'ihc_list_access_posts_order_post_type' => 'post,page',
							'ihc_list_access_posts_order_exclude_levels' => '',
			);
			break;
		case 'invoices':
			$arr = array(
							'ihc_invoices_on' => 0,
							'ihc_invoices_only_completed_payments' => 0,
							'ihc_invoices_company_field' => '<div><b>Your CompanyName LLC</b></div>
<div>Unique Code: #99991239</div>
<div>Company Address: Your Email Address</div>',
							'ihc_invoices_bill_to' => '<div><b>Bill to</b></div>
<div><b>Name: </b>{first_name} {last_name} </div>
<div><b>E-mail: </b>{user_email} </div>
<div><b>Address: </b>{CUSTOM_FIELD_addr1}</div>',
							'ihc_invoices_title' => 'Your Order Invoice',
							'ihc_invoices_template' => '',
							'ihc_invoices_logo' => IHC_URL . 'assets/images/default-logo1.png',
							'ihc_invoices_custom_css' => '',
							'ihc_invoices_footer' => 'If you have any questions about this Invoice, please contact us!',
			);
			break;
		case 'woo_payment':
			$arr = array(
							'ihc_woo_payment_on' => 0,
			);
			break;
		case 'badges':
			$arr = array(
							'ihc_badges_on' => 0,
							'ihc_badge_custom_css' => '',
			);
			break;
		case 'login_security':
			$arr = array(
							'ihc_login_security_on' => 0,
							'ihc_login_security_allowed_retries' => 3,
							'ihc_login_security_lockout_time' => 15,
							'ihc_login_security_max_lockouts' => 3,
							'ihc_login_security_extended_lockout_time' => 24,
							'ihc_login_security_reset_retries' => 24,
							'ihc_login_security_notify_admin' => 2,
							'ihc_login_security_black_list' => '',
							'ihc_login_security_lockout_attempt_message' => esc_html__('You have {number} login attempts remain.', 'ihc'),
							'ihc_login_security_lockout_message' => esc_html__('Login Form is locked for 15 minutes.', 'ihc'),
							'ihc_login_security_extended_lockout_message' => esc_html__('You have made too many failed login attempts. Login Form will be locked for 24 hours.', 'ihc'),
			);
			break;
		case 'workflow_restrictions':
			$arr = array(
							'ihc_workflow_restrictions_on' => 0,
							'ihc_workflow_restrictions_timelimit' => 30,
							'ihc_workflow_restrictions_post_views' => array(),
							'ihc_workflow_restrictions_posts_created' => array(),
							'ihc_workflow_restrictions_comments_created' => array(),
			);
			break;
		case 'subscription_delay':
			$arr = array(
							'ihc_subscription_delay_on' => 0,
							'ihc_subscription_delay_time' => array(),
							'ihc_subscription_delay_type' => array(),
			);
			break;
		case 'level_dynamic_price':
			$arr = array(
							'ihc_level_dynamic_price_on' => 0,
							'ihc_level_dynamic_price_step' => 0.01,
							'ihc_level_dynamic_price_levels_on' => 0,
							'ihc_level_dynamic_price_levels_min' => array(),
							'ihc_level_dynamic_price_levels_max' => array(),
			);
			break;
		case 'user_reports':
			$arr = array('ihc_user_reports_enabled'=>0);
			break;
		case 'pushover':
			$arr = array(
							'ihc_pushover_enabled' => 0,
							'ihc_pushover_app_token' => '',
							'ihc_pushover_admin_token' => '',
							'ihc_pushover_url' => '',
							'ihc_pushover_url_title' => '',
							'ihc_pushover_sound' => 'bike',
			);
			break;
		case 'account_page_menu':
			$arr = array(
							'ihc_account_page_menu_enabled' => 0,
							'ihc_account_page_menu_order' => array(),
			);
			break;
		case 'mycred':
			$arr = array(
							'ihc_mycred_enabled' => 0,
			);
			break;
		case 'api':
			$arr = array(
							'ihc_api_enabled' => 0,
							'ihc_api_hash' => '',
							'ihc_api_actions' => array(
														'verify_user_level' => 1,
														'user_approve' => 1,
														'user_add_level' => 1,
														'user_get_details' => 1,
														'user_activate_level' => 1,
														'get_user_field_value' => 1,
														'get_user_levels' => 1,
														'get_user_level_details' => 1,
														'get_user_posts' => 1,
														'search_users' => 1,
														'list_levels' => 1,
														'get_level_users' => 1,
														'get_level_details' => 1,
														'orders_listing' => 1,
														'order_get_status' => 1,
														'order_get_data' => 1,
							),
			);
			break;
		case 'woo_product_custom_prices':
			$arr = array(
							'ihc_woo_product_custom_prices_enabled' => 1,
							'ihc_woo_product_custom_prices_tiebreaker' => 'biggest',
							'ihc_woo_product_custom_prices_like_discount' => 0,
			);
			break;
		case 'drip_content_notifications':
			$arr = array(
							'ihc_drip_content_notifications_enabled' => 0,
							'ihc_drip_content_notifications_logs_enabled' => 0,
							'ihc_drip_content_notifications_sleep' => 0,
			);
			break;
		case 'user_sites':
			$arr = array(
							'ihc_user_sites_enabled' => 0,
							'ihc_user_sites_levels' => array(),
			);
			break;
		case 'zapier':
			$arr = array(
					'ihc_zapier_enabled'									=> 0,
					'ihc_zapier_new_user_webhook'					=> '',
					'ihc_zapier_new_user_enabled'					=> 0,
					'ihc_zapier_new_order_webhook'				=> '',
					'ihc_zapier_new_order_enabled'				=> 0,
					'ihc_zapier_order_completed_webhook'  => '',
					'ihc_zapier_order_completed_enabled'  => 0,
			);
			break;
		case 'infusionSoft':
			$arr = array(
					'ihc_infusionSoft_enabled'									=> 0,
					'ihc_infusionSoft_id'												=> '',
					'ihc_infusionSoft_api_key'									=> '',
					'ihc_infusionSoft_keap_api_key'							=> '',
					'ihc_infusionSoft_levels_groups'						=> array(),
			);
			break;
		case 'kissmetrics':
			$arr = array(
					'ihc_kissmetrics_enabled'															=> 0,
					'ihc_kissmetrics_apikey'															=> '',
					'ihc_kissmetrics_events_user_register'								=> 0,
					'ihc_kissmetrics_events_user_register_label'					=> esc_html__( 'Registered!', 'ihc' ),
					'ihc_kissmetrics_events_user_get_level'								=> 0,
					'ihc_kissmetrics_events_user_get_level_label'					=> esc_html__( 'User get Membership ', 'ihc' ) . '%level%',
					'ihc_kissmetrics_events_user_finish_payment'					=> 0,
					'ihc_kissmetrics_events_user_finish_payment_label'		=> esc_html__( 'User has finish the payment for Membership ', 'ihc' ) . '%level%',
					'ihc_kissmetrics_events_user_login'										=> 0,
					'ihc_kissmetrics_events_user_login_label'							=> esc_html__( 'User has login. ', 'ihc' ),
					'ihc_kissmetrics_events_remove_user_level'						=> 0,
					'ihc_kissmetrics_events_remove_user_level_label'			=> esc_html__( 'Membership ', 'ihc') . '%level%' . esc_html__( ' has been removed from this user.', 'ihc'),
			);
			break;
		case 'direct_login':
			$arr = array(
					'ihc_direct_login_enabled'					=> 0,
			);
			break;
		case 'reason_for_cancel':
			$arr = array(
					'ihc_reason_for_cancel_enabled'				=> 0,
					'ihc_reason_for_cancel_resons'				=> "I have no time,
The content of your website don't satisfy me"
			);
			break;
		case 'weekly_summary_email':
			$arr = array(
					'ihc_reason_for_weekly_email_enabled'			=> 0
			);
			break;
			case 'manage_subscription_table':
			$arr = [
				'ihc_show_renew_link' 						=> 1,
				'ihc_show_delete_link' 						=> 1,
				'ihc_show_cancel_link'						=> 1,
				'ihc_show_pause_resume_link'			=> 0,
				'ihc_show_finish_payment'					=> 1,

				'ihc_show_plan_details_column'		=> 1,
				'ihc_show_amount_column'					=> 1,
				'ihc_show_payment_service_column' => 1,
				'ihc_show_trial_period_column'		=> 1,
				'ihc_show_grace_period_column'		=> 1,
				'ihc_show_starts_on_column'				=> 1,
				'ihc_show_expires_on_column'			=> 1,
				'ihc_show_status_column'					=> 1,
				'ihc_subscription_table_finish_payment_after'		=> 12,
			];
			break;
			case 'manage_order_table':
			$arr = [
				'ihc_show_order_memberships_column'			=> 1,
				'ihc_show_total_amount_column'					=> 1,
				'ihc_show_payment_method_column'				=> 1,
				'ihc_show_date_column'									=> 1,
				'ihc_show_coupon_column'								=> 1,
				'ihc_show_transaction_column'						=> 1,
				'ihc_show_invoice_column'								=> 1,
				'ihc_show_order_status_column'					=> 1,
				'ihc_show_taxes_column'									=> 1
			];
			break;
		case 'checkout-settings':
			$arr = [
					'ihc_checkout_enable'  										=> 1,
					'ihc_checkout_inital'  										=> 0,
					'ihc_checkout_template'										=> 'ihc_checkout_template_1',
					'ihc_checkout_column_structure'						=> 1,
					'ihc_checkout_customer_information'				=> 0,
					'ihc_checkout_customer_fields'						=> '',
					'ihc_checkout_payment_section'  					=> 1,
					'ihc_checkout_payment_theme'  						=> 'ihc-select-payment-theme-2',
					'ihc_checkout_membership_price_details'		=> 1,
					'ihc_checkout_dynamic_price'							=> 0,
					'ihc_checkout_coupon'											=> 1,
					'ihc_checkout_taxes_display_section'  		=> 0,
					'ihc_checkout_privacy_policy_option'			=> 0,
					'ihc_checkout_privacy_policy_message'			=> 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes',
					'ihc_checkout_avoid_free_membership'			=> 0,
					'ihc_checkout_payment_method_position'		=> 0,
					'ihc_checkout_custom_css'									=> '',
					'ihc_payment_selected'										=> 'bank_transfer',
				];
				break;
			case 'checkout-messages':
				$arr = [
					'ihc_checkout_customer_title' 								=> 'Customer Information',
					'ihc_checkout_payment_title'									=> 'Payment Method',
					'ihc_checkout_taxes_title'										=> 'Taxes',
					'ihc_checkout_coupon_field_message'						=> 'If you have a coupon code, please apply it below',
					'ihc_checkout_coupon_field_used'							=> 'Coupon',
					'ihc_checkout_apply_button'  									=> 'Apply',
					'ihc_checkout_price_initial'									=> 'Initial Payment',
					'ihc_checkout_price_then'											=> 'Then',
					'ihc_checkout_price_fee'											=> 'Fee',
					'ihc_checkout_price_free'											=> 'Free',
					'ihc_checkout_price_discount'									=> 'Discount',
					'ihc_checkout_price_every'							  		=> 'every',
					'ihc_checkout_price_for'							  			=> 'for',
					'ihc_checkout_dynamic_field_message'					=> 'Choose how much you wish to pay for it.',
					'ihc_checkout_dynamic_price-set'			  			=> 'Chosen Price',
					'ihc_checkout_dynamic_field_button'			  		=> 'Apply',
					'ihc_checkout_subtotal_title'									=> 'Subtotal',
					'ihc_checkout_purchase_button'								=> 'Complete Purchase',
					'ihc_checkout_free_button'										=> 'Free Access',
					'ihc_checkout_remove'													=> '[Remove]'
					];
					break;
			case 'profile-form-settings':
				$arr = [
					'ihc_profile_form_template'			=> 'ihc-register-9',
					'ihc_profile_form_custom_css'		=> ''
				];
				break;
			case 'thank-you-page-settings':
				$arr = [
					'ihc_thank_you_message'			    => '<h5 class="primary">Thank you for your purchase!</h5>
<p>&nbsp;</p><div><p><strong>Hello {customer_name},</strong></p> <p>This is a confirmation that we have just received your secure online payment ({amount}{currency}) through {order_payment_method} for <strong>{membership_name}</strong>.</p>
<p>Your Order <strong>#{order_code}</strong> have been placed. Check your Inbox on <strong>{customer_email}</strong> for further details.</p>
<p>Thank you for your trust.</p></div>',
					'ihc_thank_you_error_message'		=> 'Sorry! Not enough information available.',
					'ihc_thank_you_custom_css'		  => ''
				];
				break;
		default:
			// used for add-ons
			$arr = array();
			break;
	}
	$arr = apply_filters( 'ihc_default_options_group_filter', $arr, $type );
	// @description Settings group. @param list of settings (array), type of settings group (array)

	if ($return_default){
		//return default values
		return $arr;
	}

	if (isset($arr)){
		if ($only_name){
			return $arr;
		}
		foreach ($arr as $k=>$v){
			$data = get_option($k);
			if ($data!==FALSE){
				$arr[$k] = $data;
			} else {
				add_option($k, $v);
			}
		}
		return $arr;
	}
	return FALSE;
}

function ihc_native_user_field(){
	/*
	 * @param none
	 * @return array
	 */
	//arr[] = array('display_public_reg'=>'', 'display_public_ap'=>'', 'display_admin'=>'', 'name'=>'', 'label'=>'', 'type'=>'', 'native_wp' => '', 'req' => '' );
	//order will be each key . ex: array( n=>array())
	//arr[]['display'] 0 not show, 1 show, 2 show always cannot be removed from register form
	//arr['req'] 0 not, 1 require, 2 if is selected it will be automatically require
	$arr = array(
			array( 'display_admin'=>2, 'display_public_reg'=>2, 'display_public_ap'=>2, 'display_on_modal'=> 2, 'name'=>'user_login', 'label'=>'Username', 'type'=>'text', 'native_wp' => 1, 'req' => 1, 'sublevel' => '' ),
			array( 'display_admin'=>2, 'display_public_reg'=>2, 'display_public_ap'=>2, 'display_on_modal'=> 2, 'name'=>'user_email', 'label'=>'Email', 'type'=>'text', 'native_wp' => 1, 'req' => 1, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'confirm_email', 'label'=>'Confirm Email', 'type'=>'text', 'native_wp' => 0, 'req' => 2, 'sublevel' => '' ),
			array( 'display_admin'=>1, 'display_public_reg'=>1, 'display_public_ap'=>1, 'display_on_modal'=> 0, 'name'=>'first_name', 'label'=>'First Name', 'type'=>'text', 'native_wp' => 1, 'req' => 1, 'sublevel' => '' ),
			array( 'display_admin'=>1, 'display_public_reg'=>1, 'display_public_ap'=>1, 'display_on_modal'=> 0, 'name'=>'last_name', 'label'=>'Last Name', 'type'=>'text', 'native_wp' => 1, 'req' => 1, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'user_url', 'label'=>'Website', 'type'=>'text', 'native_wp' => 1, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>1, 'display_public_reg'=>2, 'display_public_ap'=>1, 'display_on_modal'=> 1, 'name'=>'pass1', 'label'=>'Password', 'type'=>'password', 'native_wp' => 1, 'req' => 1, 'sublevel' => '' ),
			array( 'display_admin'=>1, 'display_public_reg'=>1, 'display_public_ap'=>1, 'display_on_modal'=> 0, 'name'=>'pass2', 'label'=>'Confirm Password', 'type'=>'password', 'native_wp' => 1, 'req' => 2, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'description', 'label'=>'Biographical Info', 'type'=>'textarea', 'native_wp' => 1, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'phone', 'label'=>'Phone', 'type'=>'number', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'addr1', 'label'=>'Address 1', 'type'=>'textarea', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'addr2', 'label'=>'Address 2', 'type'=>'textarea', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'zip', 'label'=>'Zip', 'type'=>'text', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'city', 'label'=>'City', 'type'=>'text', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'thestate', 'label'=>'State', 'type'=>'text', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'country', 'label'=>'Country', 'type'=>'text', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>1, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_country', 'label'=>'Country', 'type'=>'ihc_country', 'native_wp' => 0, 'req' => 2, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_state', 'label'=>'State', 'type'=>'ihc_state', 'native_wp' => 0, 'req' => 2, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>1, 'display_public_ap'=>1, 'display_on_modal'=> 0, 'name'=>'ihc_avatar', 'label'=>'Avatar', 'type'=>'upload_image', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>1, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'tos', 'label'=>'Accept', 'type'=>'checkbox', 'native_wp' => 0, 'req' => 2, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_optin_accept', 'label' => esc_html__( 'I would like to subscribe to newsletter list ', 'ihc' ), 'type'=>'single_checkbox', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_memberlist_accept', 'label' => esc_html__( 'Show my profile on public Members Directory', 'ihc' ), 'type'=>'single_checkbox', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'recaptcha', 'label'=>'Capcha', 'type'=>'capcha', 'native_wp' => 0, 'req' => 2, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>1, 'display_on_modal'=> 0, 'name'=>'ihc_social_media', 'label'=>'-', 'type'=>'social_media', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_invitation_code_field', 'label'=>'Invitation Code', 'type'=>'ihc_invitation_code_field', 'native_wp' => 0, 'req' => 2, 'sublevel' => '' ),
			//array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_dynamic_price', 'label'=>'Price', 'type'=>'ihc_dynamic_price', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			//array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'ihc_coupon', 'label'=>'Coupon', 'type'=>'text', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
			//array( 'display_admin'=>0, 'display_public_reg'=>0, 'display_public_ap'=>0, 'display_on_modal'=> 0, 'name'=>'payment_select', 'label'=>'Select Payment', 'type'=>'payment_select', 'native_wp' => 0, 'req' => 0, 'sublevel' => '' ),
	);

	return $arr;
}

function ihc_get_user_reg_fields(){
	/*
	 * @param none
	 * @return array
	 */
	$option_name = 'ihc_user_fields';
	$data = get_option($option_name);
	if ($data!==FALSE){
		return $data;
	} else {
		$data = ihc_native_user_field();
		add_option($option_name, $data);
		return $data;
	}
}

/*
 * Deprecated since version 11.7
 * @param attr
 * @return string with form for lost password
 */
 /*
function ihc_print_form_password($meta_arr=null){

	$str = '';

	if(!empty($meta_arr['ihc_login_custom_css'])){
		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', $meta_arr['ihc_login_custom_css'] );
	}

	$nonce = wp_create_nonce( 'ihc_lost_password_nonce' );
	$str .= '<div class="ihc-pass-form-wrap '.$meta_arr['ihc_login_template'].'">';
	$str .= '<form method="post" >'
					. '<input name="ihcaction" type="hidden" value="reset_pass">'
					. '<input type="hidden" name="ihc_lost_password_nonce" value="' . $nonce . '" />';

	switch($meta_arr['ihc_login_template']){

	case 'ihc-login-template-3':
		$str .=  '<div class="impu-form-line-fr">'
						. '<input type="text" value="" name="email_or_userlogin" placeholder="' . esc_html__('Username or E-mail', 'ihc') . '" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-4':
		$str .=  '<div class="impu-form-line-fr">'
						. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" name="email_or_userlogin" placeholder="'. esc_html__('Username or E-mail', 'ihc').'" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-8':
		$str .=  '<div class="impu-form-line-fr">'
						. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" name="email_or_userlogin" placeholder="'. esc_html__('Username or E-mail', 'ihc').'" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-9':
		$str .=  '<div class="impu-form-line-fr">'
						. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" name="email_or_userlogin" placeholder="'. esc_html__('Username or E-mail', 'ihc').'" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-10':
		$str .=  '<div class="impu-form-line-fr">'
						. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" name="email_or_userlogin" placeholder="'. esc_html__('Username or E-mail', 'ihc').'" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-11':
		$str .=  '<div class="impu-form-line-fr">'
						. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" name="email_or_userlogin" placeholder="'. esc_html__('Username or E-mail', 'ihc').'" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-12':
		$str .=  '<div class="impu-form-line-fr">'
						. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" name="email_or_userlogin" placeholder="'. esc_html__('Username or E-mail', 'ihc').'" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	case 'ihc-login-template-13':
		$str .=  	'<div class="impu-form-pass-additional-content">'
					. esc_html__('To reset your password, please enter your email address or username below', 'ihc')
					. '</div>'
					.'<div class="impu-form-line-fr">'
						. '<input type="text" value="" name="email_or_userlogin" placeholder="' . esc_html__('Enter your username or email', 'ihc') . '" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Reset my password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;

	default:
		$str .=  '<div class="impu-form-line-fr">'
					. '<span class="impu-form-label-fr impu-form-label-username">' . esc_html__('Username or E-mail', 'ihc') . ': </span>'
						. '<input type="text" value="" name="email_or_userlogin" />'
					. '</div>'
					. '<div class="impu-form-submit">'
						. '<input type="submit" value="' . esc_html__('Get New Password', 'ihc') . '" name="Submit" class="button button-primary button-large">'
					. '</div>';
	break;
	}
	$str .=   '</form>';
	$str .= '</div>';
	return $str;
}
*/

/*
function ihc_print_form_login($meta_arr=null){
	$str = '';
	if(!empty($meta_arr['ihc_login_custom_css'])){

		wp_register_style( 'dummy-handle', false );
 	 	wp_enqueue_style( 'dummy-handle' );
 	 	wp_add_inline_style( 'dummy-handle', $meta_arr['ihc_login_custom_css'] );
	}
	wp_enqueue_style( 'dashicons' );
	$sm_string = (!empty($meta_arr['ihc_login_show_sm'])) ? ihc_print_social_media_icons('login', array(), (isset($meta_arr['is_locker'])) ? $meta_arr['is_locker'] : FALSE) : '';

	$nonce = wp_create_nonce( 'ihc_login_nonce' );

	$str .= '<div class="ihc-login-form-wrap '.$meta_arr['ihc_login_template'].'">'
			.'<form method="post" id="ihc_login_form">'
			. '<input type="hidden" name="ihcaction" value="login" />'
			. '<input type="hidden" name="ihc_login_nonce" value="' . $nonce . '" />';

	if (!empty($meta_arr['is_locker'])){
		$str .= '<input type="hidden" name="locker" value="1" />';
	}

	$captcha = '';
	if (!empty($meta_arr['ihc_login_show_recaptcha'])){
			$captchaType = get_option( 'ihc_recaptcha_version' );
			if ( $captchaType !== false && $captchaType == 'v3' ){
					$captchaKey = get_option('ihc_recaptcha_public_v3');
			} else {
					$captchaKey = get_option('ihc_recaptcha_public');
			}

			if ( !empty( $captchaKey ) ){
					$view = new \Indeed\Ihc\IndeedView();
					$captchaData = array(
							'class' 		=> '',
							'key'				=> $captchaKey,
							'langCode'	=> indeed_get_current_language_code(),
							'type'			=> $captchaType,
					);
					$captcha = $view->setTemplate( IHC_PATH . 'public/views/login-captcha.php' )->setContentData( $captchaData, true)->getOutput();
			}
	}

	$user_field_id = 'iump_login_username';
	$password_field_id = 'iump_login_password';

	switch($meta_arr['ihc_login_template']){

	case 'ihc-login-template-2':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-username">'. esc_html__('Username', 'ihc').':</span>'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '" />'
				. '</div>'
				. '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-pass">'. esc_html__('Password', 'ihc').':</span>'
				. '<input type="password" value="" name="pwd" id="' . $password_field_id . '" />'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-form-line-fr impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-fr impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>

		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div class="impu-form-line-fr impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						$lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
	break;

	case 'ihc-login-template-3':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}

		$str .= $captcha;

		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.'/>'
				 . '</div>';

		$str .= $sm_string;
		$str .= '<div class="impu-temp3-bottom">';
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>

		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						$register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}

			$str .= '</div>';
		}
		//>>>>
		$str .= '<div class="iump-clear"></div>';
		$str .= '</div>';

		break;

	case 'ihc-login-template-4':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-pass-ihc"></i><input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' />'
				 . '</div>';

		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}

		$str .= '</div>';
		}
		//>>>>

		break;
	case 'ihc-login-template-5':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-username">'. esc_html__('Username', 'ihc').':</span>'
				. '<input type="text" value="" id="' . $user_field_id . '" name="log" />'
				. '</div>'
				. '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-pass">'. esc_html__('Password', 'ihc').':</span>'
				. '<input type="password" value="" id="' . $password_field_id . '" name="pwd" />'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		$str .=    '<div class="impu-temp5-row">';
		$str .=    '<div class="impu-temp5-row-left">';
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-fr impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-line-fr impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .= '</div>';

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
		$str .= '<div class="iump-clear"></div>';


		$str .= $sm_string;

		$str .= '</div>';

		break;
		case 'ihc-login-template-6':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-username">'. esc_html__('Username', 'ihc').':</span>'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '" />'
				. '</div>'
				. '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-pass">'. esc_html__('Password', 'ihc').':</span>'
				. '<input type="password" value="" id="' . $password_field_id . '" name="pwd" />'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .=    '<div class="impu-temp6-row">';
		$str .=    '<div class="impu-temp6-row-left">';
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-fr impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>

		$str .= '</div>';

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
		$str .= '<div class="iump-clear"></div>';
		$str .= '</div>';

		break;

		case 'ihc-login-template-7':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-username">'. esc_html__('Username', 'ihc').':</span>'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-pass">'. esc_html__('Password', 'ihc').':</span>'
				. '<input type="password" value="" id="' . $password_field_id . '" name="pwd" />'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		$str .=    '<div class="impu-temp5-row">';
		$str .=    '<div class="impu-temp5-row-left">';
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-fr impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .= '</div>';

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
		$str .= '<div class="iump-clear"></div>';
		$str .= '</div>';

		break;

	case 'ihc-login-template-8':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-pass-ihc"></i><input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' />'
				 . '</div>';

		$str .= $sm_string;

		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}

		$str .= '</div>';
		}
		//>>>>

		break;
	case 'ihc-login-template-9':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-pass-ihc"></i><input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>
		if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '<div class="ihc-clear"></div>';
		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' />'
				 . '</div>';

		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg">'. esc_html__("Don't have an account?", 'ihc').'<a href="'.$register_page.'">'. esc_html__('Sign Up', 'ihc').'</a></div>';
				}
			}


		$str .= '</div>';
		}
		//>>>>

		break;
	case 'ihc-login-template-10':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-pass-ihc"></i><input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>
		if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '<div class="ihc-clear"></div>';
		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' />'
				 . '</div>';

		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg">'. esc_html__("Don't have an account?", 'ihc').'<a href="'.$register_page.'">'. esc_html__('Sign Up', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		break;
	case 'ihc-login-template-11':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-pass-ihc"></i><input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>
		if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '<div class="ihc-clear"></div>';
		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' />'
				 . '</div>';

		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg">'. esc_html__("Don't have an account?", 'ihc').'<a href="'.$register_page.'">'. esc_html__('Sign Up', 'ihc').'</a></div>';
				}
			}


		$str .= '</div>';
		}
		//>>>>

		break;

	case 'ihc-login-template-12':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-username-ihc"></i><input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'. esc_html__('Username', 'ihc').'"/>'
				. '</div>'
				. '<div class="impu-form-line-fr">'
				. '<i class="fa-ihc fa-pass-ihc"></i><input type="password" value="" id="' . $password_field_id . '" name="pwd" placeholder="'. esc_html__('Password', 'ihc').'"/>'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-remember">'. esc_html__('Remember Me', 'ihc').'</span> </div>';
		}
		//>>>>
		if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '<div class="ihc-clear"></div>';
		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' />'
				 . '</div>';

		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg">'. esc_html__("Don't have an account?", 'ihc').'<a href="'.$register_page.'">'. esc_html__('Sign Up', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		break;

	case 'ihc-login-template-13':
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-username">'. esc_html__('Username or Email', 'ihc').'</span>'
				. '<input type="text" value="" id="' . $user_field_id . '" name="log" />'
				. '</div>'
				. '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-pass">'. esc_html__('Password', 'ihc').'</span>'
				. '<input type="password" value="" id="' . $password_field_id . '" name="pwd" />'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>

		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-temp5-row">';
			$str .= '<div class="impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-fr impu-form-label-remember">'. esc_html__('Keep me signed in', 'ihc').'</span> </div>';
			$str .= '</div>';
		}
		//>>>>

		$str .= '<div class="impu-temp5-row">';
		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .= '<div class="impu-temp5-row-left">';
		$str .=    '<div class="impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		$str .= '</div>';
		//>>>>
		if($meta_arr['ihc_login_register']){
		$str .= '<div class="impu-temp5-row-right">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="ihc-register-link"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}

		$str .= '<div class="iump-clear"></div>';

		$str .= '</div>';
		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_pass_lost']){
			$str .= '<div class="impu-temp5-row">';
			$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			$str .= '</div>';
		}

		//>>>>

		$str .= $captcha;
		$str .= $sm_string;

		break;

	default:
		//<<<< FIELDS
		$str .= '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-username">'. esc_html__('Username', 'ihc').':</span>'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '" />'
				. '</div>'
				. '<div class="impu-form-line-fr">' . '<span class="impu-form-label-fr impu-form-label-pass">'. esc_html__('Password', 'ihc').':</span>'
				. '<input type="password" value="" name="pwd" id="' . $password_field_id . '" />'
				. '<span type="button" class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< REMEMBER ME
		if($meta_arr['ihc_login_remember_me']){
			$str .= '<div class="impu-form-line-fr impu-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" /><span class="impu-form-label-fr impu-form-label-remember">'. esc_html__('Remember Me').'</span> </div>';
		}
		//>>>>

		//<<<< ADDITIONAL LINKS
		if($meta_arr['ihc_login_register'] || $meta_arr['ihc_login_pass_lost']){
		$str .= '<div  class="impu-form-line-fr impu-form-links">';
			if($meta_arr['ihc_login_register']){
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}
			}
			if($meta_arr['ihc_login_pass_lost']){
				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}

		$str .= $captcha;

		$str .=    '<div class="impu-form-line-fr impu-form-submit">'
					. '<input type="submit" value="'. esc_html__('Log In', 'ihc').'" name="Submit" '.$disabled.' class="button button-primary button-large"/>'
				 . '</div>';
		//>>>>
		break;
	}


	$str .=   '</form>';

	/// ERROR MESSAGE
	 if (!empty($_GET['ihc_pending_email'])){
		// PENDING EMAIL
		$login_faild = get_option('ihc_login_error_email_pending', true);
		if (empty($login_faild)){
			$arr = ihc_return_meta_arr('login-messages', false, true);
			//print_r($arr);
			if (isset($arr['ihc_login_error_email_pending']) && $arr['ihc_login_error_email_pending']){
				$login_faild = $arr['ihc_login_error_email_pending'];
			} else {
				$login_faild = esc_html__('Error', 'ihc');
			}
		}
		$str .= '<div class="ihc-login-error-wrapper"><div class="ihc-login-error">' . ihc_correct_text($login_faild) . '</div></div>';
	} else if (!empty($_GET['ihc_login_fail'])){
		// FAIL
		$login_faild = ihc_correct_text( get_option('ihc_login_error', true) );
		if (empty($login_faild)){
			$arr = ihc_return_meta_arr('login-messages', false, true);
			if (isset($arr['ihc_login_error']) && $arr['ihc_login_error']){
				$login_faild = $arr['ihc_login_error'];
			} else {
				$login_faild = esc_html__('Error', 'ihc');
			}
		}
		$str .= '<div class="ihc-login-error-wrapper"><div class="ihc-login-error">' . ihc_correct_text($login_faild) . '</div></div>';
	} else if (!empty($_GET['ihc_login_pending'])){
		// PENDING
		$str .= '<div class="ihc-login-pending">' . ihc_correct_text(get_option('ihc_login_pending', true)) . '</div>';
	} else if (!empty($_GET['ihc_social_login_failed'])){
		// Social Login - Error
		$errMessage = get_option('ihc_social_login_failed', true );
		if ( $errMessage == '' ){
				$errMessage = esc_html__( 'You are not registered with this social network. Please register first!', 'ihc' );
		}
		$errMessage = ihc_correct_text( $errMessage );
		$str .= '<div class="ihc-login-error-wrapper">' . $errMessage . '</div>';
	} else if (!empty($_GET['ihc_fail_captcha'])){
		$login_faild = ihc_correct_text(get_option('ihc_login_error_on_captcha'));
		if (!$login_faild){
			$login_faild = esc_html__('Error with Captcha', 'ihc');
		}
		$str .= '<div class="ihc-login-error-wrapper"><div class="ihc-login-error">' . $login_faild . '</div></div>';
	}
	if (!empty($_GET['ihc_login_block'])){
		require_once IHC_PATH . 'classes/Ihc_Security_Login.class.php';
		$security_object = new Ihc_Security_Login();
		$message = $security_object->get_error_attempt_message();
		if ($message){
			$str .= '<div class="ihc-login-error-wrapper"><div class="ihc-login-error">' . $message . '</div></div>';
		}
	}
	/// ERROR MESSAGE

	$str .= '</div>';

	$err_msg = esc_html__('Please complete all require fields!', 'ihc');
	$custom_err_msg = get_option('ihc_login_error_ajax');
	if ($custom_err_msg){
		$err_msg = $custom_err_msg;
	}

	$str .= "<span class='ihc-js-login-data'
								data-user_field='#$user_field_id'
								data-password_field='#$password_field_id'
								data-error_message='$err_msg' ></span>";

	return $str;
}
*/


function ihc_print_social_media_icons($type='login', $already_registered_sm=array(), $is_locker=FALSE){
	/*
	 * @param string (login, register, update), array, bool
	 * @return string
	 */

	$current_url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$metas = ihc_return_meta_arr('social_media');

	$arr = array(
			"fb" => "Facebook",
			"tw" => "Twitter",
			"goo" => "Google",
			"in" => "LinkedIn",
			"vk" => "Vkontakte",
			"ig" => "Instagram",
			"tbr" => "Tumblr",
	);

	$str = '';
	foreach ($arr as $k=>$v){
		$data = ihc_check_social_status($k);
		$label = (empty($metas['ihc_sm_show_label'])) ? "" : '<span class="ihc-sm-item-label">'.$v.'</span>';

		if ($data['settings']=='Completed' && $data['active']){
			$extra_class = 'ihc-' . $k;
			$icon = '<i class="fa-ihc-sm fa-ihc-' . $k . '"></i>';
			if ($type=='login'){
				$href = IHC_URL . 'public/social_handler.php?sm_login=' . $k . '&ihc_current_url=' . urlencode($current_url);
				if (!empty($is_locker)){
					$href .= '&is_locker=1';
				}
				$str .= '<div class="ihc-sm-item ' . $extra_class . '"><a href="' . $href . '">' . $icon . $label . '</a></div>';
			} else if ($type=='register'){
				$str .= '<div onClick="ihcRunSocialReg(\''.$k.'\');" class="ihc-sm-item ' . $extra_class . '">' . $icon . $label . '<div class="iump-clear"></div></div>';
			} else if ($type=='update'){
				$already_class = '';
				if ($already_registered_sm && is_array( $already_registered_sm ) && in_array($k, $already_registered_sm)){
					$already_class = ' ihc-sm-already-reg';
					$str .= '<div class="ihc-sm-item ' . $extra_class . ' ' . $already_class . '"><a href="javascript:void(0)" onClick="ihcRemoveSocial(\'' . $k . '\');">' . $icon . $label . '<div class="iump-clear"></div></a></div>';
				} else {
					$href = IHC_URL . 'public/social_handler.php?reg_ext_usr=' . $k . '&ihc_current_url=' . urlencode($current_url);
					$str .= '<div class="ihc-sm-item ' . $extra_class . '"><a href="' . $href . '">' . $icon . $label . '<div class="iump-clear"></div></a></div>';
				}
			}
		}
	}
	if ($str){
		if ($type=='login'){
			$str = '<div>' . ihc_correct_text($metas['ihc_sm_top_content']) . '</div>' . $str . '<div>' . ihc_correct_text($metas['ihc_sm_bottom_content']) . '</div>';
		}
		$str = '<div class="ihc-sm-wrapp-fe ' . ((isset($metas['ihc_sm_template'])) ? $metas['ihc_sm_template'] : '') . '">' . $str . '</div>';

		if (!empty($metas['ihc_sm_custom_css'])){
			wp_register_style( 'dummy-handle', false );
	 	 	wp_enqueue_style( 'dummy-handle' );
	 	 	wp_add_inline_style( 'dummy-handle', $metas['ihc_sm_custom_css'] );
		}
	}
	return $str;
}

function ihc_print_links_login(){
	/*
	 * @param none
	 * @return string
	 */
	$str ='';
	$str .= '<div  class="impu-form-line-fr impu-form-links">';
				$pag_id = get_option('ihc_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-reg"><a href="'.$register_page.'">'. esc_html__('Register', 'ihc').'</a></div>';
				}


				$pag_id = get_option('ihc_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="impu-form-links-pass"><a href="'.$lost_pass_page.'">'. esc_html__('Lost your password?', 'ihc').'</a></div>';
				}

		$str .= '</div>';
	return $str;
}

function ihc_get_level_by_id($id=null){
	/*
	 * @param int
	 * @return array|bool
	 */
	return \Indeed\Ihc\Db\Memberships::getOne( $id );
}

function ihc_format_str_like_wp( $str ){
	/*
	 * @param string
	 * @return string
	 */
	$str = wpautop( $str );
	return $str;
}

function ihc_array_value_exists($haystack=[], $needle='', $key=''){
	/*
	 * @param array, string, string
	 * @return string|int, bool
	 */
	foreach ($haystack as $k=>$v){
		if ( isset( $v[$key] ) && $v[$key]==$needle ){
			return $k;
		}
	}
	return FALSE;
}

function ihc_is_array_value_multi_exists($haystack=array(), $needle='', $key=''){
	/*
	 * @param array, string, string
	 * @return int
	 */
	$c = 0;
	foreach ($haystack as $k=>$v){
		if ($v[$key]==$needle){
			$c++;
		}
	}
	return $c;
}

function ihc_array_key_recursive($arr=[], $key=null){
	/*
	 * @param array, string|int
	 * @return string|int, bool
	 */
	foreach ($arr as $k=>$v){
		if (array_key_exists($key, $v)){
			 return $k;
		}
	}
	return FALSE;
}


function ihc_correct_text( $str='', $wp_editor_content=false, $escAttr=false )
{
	/*
	 * @param string, bool
	 * @return string
	 */
	$str = stripcslashes( htmlspecialchars_decode( $str ) );
	if ( $escAttr ){
			$str = esc_attr( $str );
	}
	if ($wp_editor_content){
			return ihc_format_str_like_wp($str);
	}
	return $str;
}


function ihc_from_simple_array_to_k_v($arr=[]){
	/*
	 * @param array
	 * @return array
	 */
	$return_arr = array();
	foreach ($arr as $v){
		$return_arr[$v] = $v;
	}
	return $return_arr;
}

function ihc_reorder_arr($arr=[]){
	/*
	 * @param array
	 * @return array
	 */
	if (isset($arr) && is_array($arr) && count($arr)>0 && $arr !== false){
		$new_arr = [];
		foreach ($arr as $k=>$v){
			$order = isset( $v['the_order'] ) ? $v['the_order'] : false;
			if ( $order === false ){
					/// deprecated
					$order = isset( $v['order'] ) ? $v['order'] : false;
			}
			while (!empty($new_arr[$order])){
				$order++;
			}
			$new_arr[$order][$k] = $v;
		}
		if ($new_arr && count($new_arr)){
			ksort($new_arr);
			foreach ($new_arr as $k=>$v){
				$return_arr[key($v)] = $v[key($v)];
			}
			return $return_arr;
		}
	}
	return $arr;
}

function ihc_check_show($arr=array()){
	/*
	 * @param array
	 * @return array
	 */
	if ($arr!==FALSE && count($arr)>0){
		$new_arr = array();
		foreach ($arr as $k=>$v){
			if (isset($v['show_on'])){
				if($v['show_on'] == 1){
					$new_arr[$k] = $v;
				}
			} else {
				$new_arr[$k] = $v;
			}
		}
		return $new_arr;
	}
	return $arr;
}

function ihc_check_level_restricted_conditions($levels=array()){
	/*
	 * @param array
	 * @return array
	 */
	 $metas = ihc_return_meta_arr('level_subscription_plan_settings');
	 if (!empty($metas['ihc_level_subscription_plan_settings_enabled']) && $levels){
	 	 global $current_user;
		 $uid = (empty($current_user->ID)) ? 0 : $current_user->ID;
		 if (empty($uid)){
		 	 /// will check only for unreg
		 	 foreach ($levels as $id=>$level){
		 	 	if (empty($metas['ihc_level_subscription_plan_settings_restr_levels']) || empty($metas['ihc_level_subscription_plan_settings_restr_levels'][$id])){
		 	 		continue;
		 	 	} else {
		 	 		/// CHECK IF MUST BLOCK THIS LEVEL
		 	 		if ($metas['ihc_level_subscription_plan_settings_condt'] && !empty($metas['ihc_level_subscription_plan_settings_condt'][$id])){
		 	 			$array_check = explode(',', $metas['ihc_level_subscription_plan_settings_condt'][$id]);
						if (in_array('unreg', $array_check)){
							unset($levels[$id]);
						}
		 	 		}
		 	 	}
		 	 }
		 } else {
			 $user_bought_something = Ihc_Db::does_this_user_bought_something($uid);
			 $user_levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid );

			 	 foreach ($levels as $id=>$level){
			 	 	if (empty($metas['ihc_level_subscription_plan_settings_restr_levels']) || empty($metas['ihc_level_subscription_plan_settings_restr_levels'][$id])){
			 	 		continue;
			 	 	} else {
			 	 		/// CHECK IF MUST BLOCK THIS LEVEL
			 	 		if ($metas['ihc_level_subscription_plan_settings_condt'] && !empty($metas['ihc_level_subscription_plan_settings_condt'][$id])){
			 	 			$array_check = explode(',', $metas['ihc_level_subscription_plan_settings_condt'][$id]);
							if (!$user_bought_something && in_array('no_pay', $array_check)){
								unset($levels[$id]);
							}
							foreach ($user_levels as $current_level=>$current_level_data){
								if (in_array($current_level, $array_check)){
									unset($levels[$id]);
								}
							}
			 	 		}
			 	 	}
			 	 }

		 }
	 }
	 return $levels;
}

function ihc_return_cc_list($ips_cc_user='', $ips_cc_pass=''){
	/*
	 * @param string, string
	 * @return array
	 */
	if (!class_exists('cc')){
		include_once IHC_PATH .'classes/services/email_services/constantcontact/class.cc.php';
	}
	$list = array();
	$cc = new cc($ips_cc_user, $ips_cc_pass);
	$lists = $cc->get_lists('lists');
	if ($lists){
		foreach ((array) $lists as $v){
			$list[$v['id']] = array('name' => $v['Name']);
		}
	}
	return $list;
}


function ihc_get_all_post_types(){
	/*
	 * use this in front-end, returns all the custom post type available in db
	 * @param none
	 * @return array
	 */
	global $wpdb;
	$arr = array();
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $customQuery = 'SELECT DISTINCT post_type FROM ' . $wpdb->prefix . 'posts WHERE post_status="publish";';
	$data = $wpdb->get_results( $customQuery );
	if ($data && count($data)){
		foreach ($data as $obj){
			$arr[] = $obj->post_type;
		}
		$exclude = array('bp-email', 'edd_log', 'nav_menu_item', 'bp-email');
		foreach ($exclude as $e){
			if ($k=array_search($e, $arr)){
				unset($arr[$k]);
				unset($k);
			}
		}
	}
	return $arr;
}

function ihc_get_post_types_be(){
	/*
	 * @param none
	 * @return all custom post type that are registered
	 * use this for back-end actions
	 */
	$args = array('public'=>true, '_builtin'=>false);
	$data = get_post_types($args);
	if (!function_exists('is_plugin_active')){
	 	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if (is_plugin_active('download-monitor/download-monitor.php')){
		$data[] = 'dlm_download';
	}
	return $data;
}


function ihc_get_post_id_by_cpt_name($custom_post_type='', $post_name=''){
	/*
	 * @param string, string
	 * @return int (id of post, >0 )
	 */
	global $wpdb;
	$table = $wpdb->prefix . 'posts';
	$q = $wpdb->prepare("SELECT ID FROM $table WHERE post_type=%s AND post_name=%s ", $custom_post_type, $post_name);
	$data = $wpdb->get_row($q);
	if (!empty($data->ID)){
		return $data->ID;
	}
	return FALSE;
}

function ihc_get_wp_roles_list(){
	/*
	 * @param none
	 * @return array with all wp roles available without administrator
	 */
	global $wp_roles;
	$roles = $wp_roles->get_names();
    if (!empty($roles)){
    	unset($roles['administrator']);// remove admin role from our list
    	return $roles;
    }
	return FALSE;
}

function ihc_get_multiply_time_value($time_type=''){
	/*
	 * @param string D,W,M,Y
	 * @return time in seconds
	 */
	$multiply = FALSE;
	switch ($time_type){
		case 'D':
			$multiply = 60*60*24;
		break;
		case 'W':
			$multiply = 60*60*24*7;
		break;
		case 'M':
			$multiply = 60*60*24*31;
		break;
		case 'Y':
			$multiply = 60*60*24*365;
		break;
	}
	return $multiply;
}

function ihc_insert_update_transaction($u_id=null, $txn_id=null, $post_data=null, $dont_save_order=FALSE){
	/*
	 * @param user id, trascation id, post data from paypal
	 * @return none
	 */
	//remove quotes from post data

	foreach ($post_data as $k=>$v){
		if (is_string($post_data[$k])){
			if (strpos($post_data[$k], "'")!==FALSE){
				$post_data[$k] = stripslashes($post_data[$k]);
				$post_data[$k] = str_replace("'", "", $post_data[$k]);
			} else if (strpos($post_data[$k], "\'")!==FALSE){
				$post_data[$k] = stripslashes($post_data[$k]);
				$post_data[$k] = str_replace("\'", "", $post_data[$k]);
			}
		}
	}

	global $wpdb;
	$table = $wpdb->prefix . 'indeed_members_payments';
	$q = $wpdb->prepare("SELECT id,txn_id,u_id,payment_data,history,orders,paydate FROM $table WHERE txn_id=%s;", $txn_id);
	$exists = $wpdb->get_row($q);
	if ($exists){
		/************** UPDATE ***************/
		$history = '';
		$q = $wpdb->prepare("SELECT history FROM $table WHERE txn_id=%s ;", $txn_id);
		$history_data = $wpdb->get_row($q);
		if ($history_data && isset($history_data->history)){

			$history = (isset($history_data->history)) ? maybe_unserialize($history_data->history) : '';
		} else {
			$q = $wpdb->prepare("SELECT payment_data FROM $table WHERE txn_id=%s;", $txn_id);
			$history_data = $wpdb->get_row($q);
			if (isset($history_data->payment_data)){
				$temp = (array)json_decode($history_data->payment_data);
				if (isset($temp['custom'])){
					unset($temp['custom']);
				}
				if (isset($temp['transaction_subject'])){
					 unset($temp['transaction_subject']);
				}
				$history[] = $temp;
			}
		}
		//remove custom from history
		$post_data_history = $post_data;
		if (isset($post_data_history['custom'])){
			 unset($post_data_history['custom']);
		}
		if (isset($post_data_history['transaction_subject'])){
			 unset($post_data_history['transaction_subject']);
		}
		$history[indeed_get_unixtimestamp_with_timezone()] = $post_data_history;
		$history_string = serialize($history);

		$q = $wpdb->prepare("UPDATE $table SET history=%s WHERE txn_id=%s ", $history_string, $txn_id);
		$wpdb->query($q);

		//////////update payment_data (last $_REQUEST )
		$post_data = json_encode($post_data);
		$q = $wpdb->prepare("UPDATE $table SET payment_data=%s WHERE txn_id=%s ", $post_data, $txn_id);
		$wpdb->query($q);

	} else {
		/************* insert ************/

		/////the history
		$post_data_history = $post_data;
		if (isset($post_data_history['custom'])){
			 unset($post_data_history['custom']);
		}
		if (isset($post_data_history['transaction_subject'])){
			 unset($post_data_history['transaction_subject']);
		}
		$history[ indeed_get_unixtimestamp_with_timezone() ] = $post_data_history;
		$history_str = serialize($history);

		////the payment data
		$post_data = json_encode($post_data);

		/// since version 8.6, before we used NOW() function in mysql
		$currentDate = indeed_get_current_time_with_timezone();

		$q = $wpdb->prepare("INSERT INTO $table VALUES (null, %s, %d, %s, %s, null, %s );", $txn_id, $u_id, $post_data, $history_str, $currentDate );
		$wpdb->query($q);
	}

	if ($dont_save_order){
		return;
	}
	/// ORDER
	require_once IHC_PATH . 'classes/Orders.class.php';
	$object = new Ump\Orders();
	$object->do_insert_update($txn_id);
}

function ihc_insert_update_order($uid=0, $lid=0, $amount_value=0, $status='pending', $payment_gateway='', $extra_fields=array(), $amount_type=''){
	/*
	 * @param int, int, float, string
	 * @return int
	 */
	if (!empty($uid) && isset($lid) && isset($amount_value)){
		require_once IHC_PATH . 'classes/Orders.class.php';
		$object = new Ump\Orders();
		if (empty($amount_type)){
				$amount_type = get_option('ihc_currency');
		}
		$order_id = $object->do_insert(array(
									'uid' 							=> $uid,
									'lid' 							=> $lid,
									'amount_type' 			=> $amount_type,
									'amount' 						=> $amount_value,
									'status' 						=> $status,
									'ihc_payment_type'  => $payment_gateway,
									'extra_fields' 			=> $extra_fields,
		));
		return $order_id;
	}
}


function ihc_insert_debug_payment_log($source, $data){
	/*
	 * insert into ihc_debug_payments
	 * @param source = type of payment service (paypall)
	 * data = the request from payment service
	 * @return none
	 */
	global $wpdb;
	$table = $wpdb->prefix . "ihc_debug_payments";
	$time = indeed_get_current_time_with_timezone();

	$data = serialize($data);
	$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %s, %s, %s);", $source, $data, $time );
	$wpdb->query($q);
}

/*
 * Used in addons
 * main function for notification module
 * send e-mail to user
 * @param:
 * user id ($u_id) - int,
 * notification type ($notification_type) - string
 * optional level id ($l_id) - int, -1 means all levels
 * dynamic_data - array
 * subject - string
 * message - string
 * @return TRUE if mail was sent, FALSE otherwise
 */
function ihc_send_user_notifications($u_id=FALSE, $notification_type='', $l_id=FALSE, $dynamic_data=array(), $subject='', $message=''){

	global $wpdb;
	$sent = FALSE;
	if ($u_id && $notification_type){
		$admin_case = array(
							'admin_user_register',
							'admin_before_user_expire_level',
							'admin_second_before_user_expire_level',
							'admin_third_before_user_expire_level',
							'admin_user_expire_level',
							'admin_user_payment',
							'admin_user_profile_update',
							'ihc_cancel_subscription_notification-admin',
							'ihc_delete_subscription_notification-admin',
							'ihc_order_placed_notification-admin',
							'ihc_new_subscription_assign_notification-admin',
		);

		if (empty($subject) || empty($message)){ /// SEARCH INTO DB FOR NOTIFICATION TEMPLATE
			if ($l_id!==FALSE && $l_id>-1){
				$q = $wpdb->prepare("SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM " . $wpdb->prefix . "ihc_notifications
										WHERE 1=1
										AND notification_type=%s
										AND level_id=%d
										ORDER BY id DESC LIMIT 1;", $notification_type, $l_id);
				$data = $wpdb->get_row($q);
				if ($data){
						$subject = (isset($data->subject)) ? $data->subject : '';
						$message = (isset($data->message)) ? $data->message : '';

						$domain = 'ihc';
						$languageCode = get_user_meta( $u_id, 'ihc_locale_code', true );
						$wmplName = $notification_type . '_title_' . $l_id;
						$subject = apply_filters( 'wpml_translate_single_string', $subject, $domain, $wmplName, $languageCode );
						$wmplName = $notification_type . '_message_' . $l_id;
						$message = apply_filters( 'wpml_translate_single_string', $message, $domain, $wmplName, $languageCode );
				}
			}
			if ($l_id===FALSE || $l_id==-1 || empty($data)){
				$q = $wpdb->prepare("SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM " . $wpdb->prefix . "ihc_notifications
										WHERE 1=1
										AND notification_type=%s
										AND level_id='-1'
										ORDER BY id DESC LIMIT 1;", $notification_type);
				$data = $wpdb->get_row($q);
				if ($data){
						$subject = (isset($data->subject)) ? $data->subject : '';
						$message = (isset($data->message)) ? $data->message : '';

						$domain = 'ihc';
						$languageCode = get_user_meta( $u_id, 'ihc_locale_code', true );
						$wmplName = $notification_type . '_title_-1';
						$subject = apply_filters( 'wpml_translate_single_string', $subject, $domain, $wmplName, $languageCode );
						$wmplName = $notification_type . '_message_-1';
						$message = apply_filters( 'wpml_translate_single_string', $message, $domain, $wmplName, $languageCode );
				}
			}
		}

		if (!empty($message)){
			$from_name = get_option('ihc_notification_name');
			if (!$from_name){
				$from_name = get_option("blogname");
			}
			//user levels
			$level_list_data = \Indeed\Ihc\UserSubscriptions::getAllForUserAsList( $u_id );
			if (isset($level_list_data)){
				$level_list_data = explode(',', $level_list_data);
				foreach ($level_list_data as $id){
					$temp_level_data = ihc_get_level_by_id($id);
					if ( isset( $temp_level_data['label'] ) ){
							$level_list_arr[] = $temp_level_data['label'];
					}
				}
				if ( !empty( $level_list_arr ) ){
					$level_list = implode(',', $level_list_arr);
				}
			}
			//user data
			$u_data = get_userdata($u_id);
			$user_email = '';
			if ($u_data && !empty($u_data->data) && !empty($u_data->data->user_email)){
				$user_email = $u_data->data->user_email;
			}
			//from email
			$from_email = get_option('ihc_notification_email_from');
			if (!$from_email){
				$from_email = get_option('admin_email');
			}
			$message = ihc_replace_constants($message, $u_id, $l_id, $l_id, $dynamic_data);
			$subject = ihc_replace_constants($subject, $u_id, $l_id, $l_id, $dynamic_data);
			$message = stripslashes(htmlspecialchars_decode(ihc_format_str_like_wp($message)));
			$message = apply_filters('ihc_send_notification_filter_message', $message, $u_id, $l_id, $notification_type);
			// @description Filter for notification message. @param the message (text), user id (integer), level id (integer), notification type (string)

			$message = "<html><head></head><body>" . $message . "</body></html>";
			if ($subject && $message && $user_email){
				if (in_array($notification_type, $admin_case)){
					/// SEND NOTIFICATION TO ADMIN, (we change the destination)
					$admin_email = get_option('ihc_notification_email_addresses');
					if (empty($admin_email)){
						$user_email = get_option('admin_email');
					} else {
						$user_email = $admin_email;
					}
				}
				if (!empty($from_email) && !empty($from_name)){
					$headers[] = "From: $from_name <$from_email>";
				}
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$sent = wp_mail($user_email, $subject, $message, $headers);
			}
		}
		/// PUSHOVER
		if (ihc_is_magic_feat_active('pushover')){
			$send_to_admin = in_array($notification_type, $admin_case) ? TRUE : FALSE;
			require_once IHC_PATH . 'classes/services/Ihc_Pushover.class.php';
			$pushover_object = new Ihc_Pushover();
			$pushover_object->send_notification($u_id, $l_id, $notification_type, $send_to_admin);
		}
		/// PUSHOVER
	}
	return $sent;
}


function ihc_print_bank_transfer_order($u_id, $l_id){
	/*
	 * print the bank transfer message
	 * @param int, int, string, int
	 * @return string
	 */
	$msg = get_option('ihc_bank_transfer_message');
	if (!empty($_GET['cp'])){
		$discount_type = 'percentage';
		$discount_value = sanitize_text_field( $_GET['cp'] );
	} else if (!empty($_GET['cc'])) {
		$discount_type = 'flat';
		$discount_value = sanitize_text_field( $_GET['cc'] );
	}
	//get amount
	$level_data = ihc_get_level_by_id($l_id);
	$orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $u_id, $l_id );
	$orderAmount = \Ihc_Db::getOrderAmount( $orderId );
	$amount = isset( $orderAmount ) ? $orderAmount : '';

	$currency = get_option( 'ihc_currency' );
	$amount = ihc_format_price_and_currency( $currency, $amount );
	$msg = str_replace('{amount}', $amount, $msg);
	$msg = str_replace('{currency}', '', $msg);

	$msg = ihc_replace_constants($msg, $u_id, $l_id, $l_id);

	return '<div class="ihc-bank-transfer-msg" id="ihc_bt_success_msg">' . ihc_correct_text( $msg, true ) . '</div>';
}


/**
 * generate csv file with all users
 * @param none
 * @return string, link to csv file or empty string
 */
if ( !function_exists( 'ihc_make_csv_user_list' ) ):
function ihc_make_csv_user_list( $attributes=[] )
{
		global $wpdb;
		$levelDetails = \Ihc_Db::getLevelsDetails();
		$possibles = [
			'search_phrase',
			'user_roles',
			'memberships',
			'order',
			'extra_conditions',
			'advancedOrder',
			'memberships_status',
			'extra_conditions',
		];
		$applyFilters = false;
		foreach ( $possibles as $possible ){
				if ( isset( $attributes[$possible] ) ){
					$applyFilters = true;
				}
		}

		$searchUsers = new \Indeed\Ihc\Db\SearchUsers();
		$searchUsers->setLimit( 0 )
								->setOffset( 0 )
								->setLid( -1 );
		if ( $applyFilters ){

				/*
				$limit = (isset($attributes['limit'])) ? $attributes['limit'] : 25;
				$start = isset($attributes['offset'])) ? $attributes['offset'] : 0;
				if(isset($attributes['ihcdu_page'])){
					$pg = $attributes['ihcdu_page'] - 1;
					$start = (int)$pg * $limit;
				}
				*/
				$search_query = isset($attributes['search_phrase']) ? $attributes['search_phrase'] : '';
				$filter_role = isset($attributes['user_roles']) ? implode( ',', $attributes['user_roles'] ) : '';
				$search_level = isset($attributes['memberships']) ? ( implode( ',', $attributes['memberships'] ) ) : -1;
				$order = isset($attributes['order']) ? $attributes['order'] : 'user_registered_desc'; // user_registered_desc
				$advancedOrder = isset( $attributes['advancedOrder'] ) ? $attributes['advancedOrder'] : '';
				$levelStatus = isset( $attributes['memberships_status'] ) ? $attributes['memberships_status'] : '';

				$searchUsers = new \Indeed\Ihc\Db\SearchUsers();
				$searchUsers->setOrder( $order )
										->setLid( $search_level )
										->setSearchWord( $search_query )
										->setRole( $filter_role )
										->setAdvancedOrder( $advancedOrder )
										->setLevelStatus( $levelStatus );

				if ( isset( $attributes['extra_conditions'] ) && $attributes['extra_conditions'] !== '' ){
						if ( in_array( 1, $attributes['extra_conditions'] ) ){
								// approvel request
							  $searchUsers->setApprovelRequest( true );
						}
						if ( in_array( 2, $attributes['extra_conditions'] ) ){
								// pending email verifications
								$searchUsers->setOnlyDoubleEmailVerification( true );
						}
				}
		}
		$users = $searchUsers->getResults();

		if ( !$users ){
				return '';
		}

			$hash = bin2hex( random_bytes( 20 ) );
			$hash = substr( $hash, 0, 12 );
			$file_path = IHC_PATH . 'temporary/' . $hash . '.csv';
			$file_link = IHC_URL . 'temporary/' . $hash . '.csv';

			// remove old files
			if (file_exists($file_path)){
					unlink($file_path);
			}
			$directory = IHC_PATH . 'temporary/';
			$files = scandir( $directory );
			foreach ( $files as $file ){
					$fileFullPath = $directory . $file;
					if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
							$extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
							if ( $extension == 'csv' ){
									unlink( $fileFullPath );
							}
					}
			}

			// create file
			$file_resource = fopen($file_path, 'w');

			$data[] = esc_html__('User ID', 'ihc');

			$exclude = ['pass1', 'pass2', 'tos', 'recaptcha', 'ihc_dynamic_price', 'ihc_social_media'];// 'confirm_email', 'ihc_optin_accept', 'ihc_memberlist_accept',  // since version 12.01
			$register_fields = ihc_get_user_reg_fields();
			/*
			User ID | Username | Email | First Name | Last Name | Membership Slug | Started Time | Expired Time | Membership Label | WP Role | Addresss | Custom Field 1 | Custom Field 2
			*/
			$columnNames = [
								'ID' 											=> esc_html__('User ID', 'ihc'),
								'user_email' 							=> esc_html__('Email', 'ihc'),
								'user_login' 							=> esc_html__('Username', 'ihc'),
								'first_name' 							=> esc_html__('First Name', 'ihc'),
								'last_name' 							=> esc_html__('Last Name', 'ihc'),
								'#membership_name' 				=> esc_html__('Membership Slug', 'ihc'),
								'#membership_label' 			=> esc_html__('Membership Label', 'ihc'),
								'#membership_start_time' 	=> esc_html__('Started Time', 'ihc'),
								'#membership_expire_time' => esc_html__('Expired Time', 'ihc'),
								'roles'										=> esc_html__('WP Role', 'ihc'),
								'user_registered' 			  => esc_html__('Join Date', 'ihc')
			];
			foreach ($register_fields as $k=>$v){
				if ( in_array( $v['name'], $exclude ) ){
						unset($register_fields[$k]);
				} else if( isset( $columnNames[ $v['name'] ] ) ){
						continue;
				} else {
					if (isset($v['native_wp']) && $v['native_wp']){
							$columnNames[ $v['name'] ] = esc_html__($v['label'], 'ihc');
					} else {
							$columnNames[ $v['name'] ] = $v['label'];
					}
				}
			}
			/*
			$data[] = esc_html__('Membership ID', 'ihc');
			$data[] = esc_html__('Membership', 'ihc');
			$data[] = esc_html__('Start time', 'ihc');
			$data[] = esc_html__('Expire time', 'ihc');
			$data[] = esc_html__('WP User Roles', 'ihc');
			$data[] = esc_html__('Join Date', 'ihc');
			*/

			/// top of CSV file
			fputcsv($file_resource, $columnNames, ",");
			//unset($data);

			global $wpdb;
			$query = "SELECT user_id ";

			foreach ( $columnNames as $columnName => $columnLabel ){
			    if ( strpos( $columnName, '#' ) === 0 ){
			        continue;
			    }
			    $query .= " ,max(case when meta_key = '{$columnName}' then meta_value end) `{$columnName}` ";
			}
			$query .= " FROM {$wpdb->usermeta} ";

			///
			if ( isset( $attributes['level_strict_mode'] ) && $attributes['level_strict_mode'] === true ){
					$strictLevels = true;
			} else {
					$strictLevels = false;
			}
			///

			// loop users
			foreach ($users as $user){

					// select user metas
					$userQuery = $query . $wpdb->prepare( " WHERE user_id=%d", $user->ID );
					$userMetaArray = $wpdb->get_row( $userQuery, ARRAY_A );

					// get memberships for this user
					$memberships = \Indeed\Ihc\UserSubscriptions::getAllForUser( $user->ID, false );
					if ( $memberships === false || ( is_array( $memberships ) && count ( $memberships ) === 0 ) ){
							$memberships[-1] = false;
					}

					// only for selected levels - import-export section
					if ( $strictLevels ){
							$temporaryLevels = [];
							if ( isset( $user->levels ) && $user->levels && stripos( $user->levels, ',' ) !== false ){
								$temporaryLevels = explode( ',', $user->levels );
							} else {
								$temporaryLevels[] = $user->levels;
							}
							if ( empty( $temporaryLevels )  ){
									 continue;
							}
							foreach ( $temporaryLevels as $levelTemporaryData ){
									if ( strpos( $levelTemporaryData, '|' ) !== false ){
											 $levelDataArray = explode( '|', $levelTemporaryData );
									} else {
											 $levelDataArray = array();
									}
									$userLevelsFromFirstQuery[] = isset( $levelDataArray[0] ) ? $levelDataArray[0] : '';;
							}
					}
					// end of selected levels - import-export section

					// create a row for each user-membership relation
					foreach ( $memberships as $membershipData ){

							// only for selected levels - import-export section
							if ( $strictLevels && is_array( $userLevelsFromFirstQuery ) && count( $userLevelsFromFirstQuery )
									&& !in_array( $membershipData['level_id'], $userLevelsFromFirstQuery ) ){
									continue;
							}
							// end of selected levels - import-export section

							$row = [];
							$membershipId = isset( $membershipData['id'] ) ? $membershipData['id'] : -1;
							$userMembershipDates = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription( $user->ID, ( $membershipId === -1 ) ? false : $membershipId );
							foreach ( $columnNames as $columnName => $columnLabel ){
									if ( isset( $user->$columnName ) ){
											// information in the user main object
											$row[] = $user->$columnName;
									}	else if ( isset( $userMetaArray[ $columnName ] ) && $userMetaArray[ $columnName ] !== false ){
											// information in the user meta
											if (is_array($userMetaArray[ $columnName ])){
													$row[] = implode(",", $userMetaArray[ $columnName ]);
											} else {
													$row[] = $userMetaArray[ $columnName ];
											}
									} else if ( strpos( $columnName, '#' ) === 0 ) {
											// membership information
											if ( $membershipId === -1 ){
													$row[] = '-';
											} else {
													switch ( $columnName ){
															case '#membership_name':
																$row[] = isset( $membershipData['level_slug'] ) ? $membershipData['level_slug'] : '-';
																break;
															case '#membership_label':
																$row[] = isset( $membershipData['label'] ) ? $membershipData['label'] : '-';
																break;
															case '#membership_start_time':
																$row[] = isset( $membershipData['start_time'] ) ? $membershipData['start_time'] : '-';
																break;
															case '#membership_expire_time':
																$row[] = isset( $membershipData['expire_time'] ) ? $membershipData['expire_time'] : '-';
																break;
													}
											}
									} else {
											// nothing
											$row[] = '-';
									}
							}
							fputcsv($file_resource, $row, ",");
							unset( $row );
					}
			} /// end of foreach  users

			fclose($file_resource);
			return $file_link;
}
endif;

function ihc_get_attachment_details($id, $return_type='name'){
	/*
	 * @param attachment id, what to return: name or extension
	 * @return string :
	 */
	$attachment_data = wp_get_attachment_url($id);
	if (isset($attachment_data)){
		$attachment_arr = explode('/', $attachment_data);
		if (isset($attachment_arr)){
			end($attachment_arr);
			$attachment_name = $attachment_arr[key($attachment_arr)];
			if ($return_type=='name'){
				return $attachment_name;
			}
			$attachment_type = explode('.', $attachment_name);
			if (isset($attachment_type)){
				end($attachment_type);
				if (isset($attachment_type[key($attachment_type)])){
					return $attachment_type[key($attachment_type)];
				}
			}
		}
	}
	return 'Unknown';
}


function ihc_replace_constants( $string='', $uid=0, $current_lid=-1, $lid=-1, $dynamic_data=array() ){
	if ( $uid && $string !== '' ){ // since version 12.5 adding && $string !== ''
		/// first we replace the dynamic data passed as arg
		if (!empty($dynamic_data)){
			foreach ($dynamic_data as $k=>$v){
					if ( strpos( $k, '{' ) === false && strpos( $k, '}' ) === false ){
							$k = '{' . $k . '}';
					}
					if ( strpos( $string, $k ) !== false ){
							$string = str_replace( $k, $v, $string);
					}
			}
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
					case 'level_id':
					case 'lid':
						$replace = $lid;
						break;
					case 'username':
						$replace = Ihc_Db::get_user_col_value($uid, 'user_login'); /// uid, col_name
						break;
					case 'CUSTOM_FIELD_user_url':
						$replace = Ihc_Db::get_user_col_value($uid, 'user_url'); /// uid, col_name
						break;
					case 'user_login':
					case 'user_email':
					case 'user_url':
					case 'user_nicename':
					case 'user_registered':
					case 'display_name':
						$replace = Ihc_Db::get_user_col_value($uid, $constant); /// uid, col_name
						if ($constant=='user_registered'){
							$replace = ihc_convert_date_to_us_format($replace);
						}
						break;
					case 'first_name':
						$replace = get_user_meta($uid, 'first_name', true);
						break;
					case 'last_name':
						$replace = get_user_meta($uid, 'last_name', true);
						break;
					case 'current_level':
						if ( $current_lid !='' && $current_lid>-1){
							$current_level_data = ihc_get_level_by_id($current_lid);
							$replace = $current_level_data['label'];
						}
						break;
					case 'level_expire_time':
						if ($lid>-1){
							$time = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription($uid, $lid);
							$replace = ihc_convert_date_to_us_format($time['expire_time']);
						} else if ($current_lid>-1){
							$time = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription($uid, $current_lid);
							$replace = ihc_convert_date_to_us_format($time['expire_time']);
						}
						break;
					case 'current_level_expire_date':
						if ($lid>-1){
								$time = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription($uid, $current_lid);
								// 12.5.2
								if ( isset( $time['expire_time'] ) && $time['expire_time'] === '0000-00-00 00:00:00' ){
										$replace = '-';
								} else if ( isset( $time['expire_time'] ) ){
										$replace = ihc_convert_date_to_us_format($time['expire_time']);
								} else {
										$replace = '-';
								}
								// 12.5.2
						}
						break;
					case 'level_list':
						$level_list = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid );
						if (!empty($level_list)){
							foreach ($level_list as $id=>$t_arr){
								$level_list_arr[] = $t_arr['label'];
							}
							if ($level_list_arr){
								$replace = implode(',', $level_list_arr);
							}
						}
						break;
					case 'account_page':
						$account_page = get_option("ihc_general_user_page");
						if ($account_page){
							$replace = get_permalink($account_page);
						}
						break;
					case 'login_page':
						$login_page = get_option("ihc_general_login_default_page");
						if ($login_page){
							$replace = get_permalink($login_page);
						}
						break;
					case 'blogname':
						$replace = get_option("blogname");
						break;
					case 'blogurl':
					case 'site_url':
						$replace = get_option("siteurl");
						break;
					case 'level_name':
						if ($lid>-1){
							$level_data = ihc_get_level_by_id($lid);
							$replace = isset( $level_data['label'] ) ? $level_data['label'] : '';
						}
						break;
					case 'amount':
						if (isset($dynamic_data['order_id'])){
							$replace = Ihc_Db::getOrderAmount($dynamic_data['order_id']);
						} else if ($lid>-1){
							$level_data = ihc_get_level_by_id($lid);
							$replace = $level_data['price'];
							$state = get_user_meta($uid, 'ihc_state', TRUE);
							$country = get_user_meta($uid, 'ihc_country', TRUE);
							$taxes_data = ihc_get_taxes_for_amount_by_country($country, $state, $replace);
							if (isset($taxes_data['total'])){
								$replace = $replace + $taxes_data['total'];
							}
						}
						$replace = ihc_format_price_and_currency( '', $replace );
						break;
					case 'currency':
						$replace = get_option('ihc_currency');
						$currency_custom_code = get_option('ihc_custom_currency_code');
	                    if (!empty($currency_custom_code)){
	                         $replace = $currency_custom_code;
	                    }
						break;
					case 'current_date':
						$replace = ihc_convert_date_to_us_format(date('Y-m-d H:i:s'));
						break;
					case 'ihc_avatar':
						$avatar = ihc_get_avatar_for_uid($uid);
						if (!empty($avatar)){
							$replace = '<img src="' . $avatar . '"/>';
						}
						break;
					case 'flag':
						$replace = ihc_user_get_flag($uid);
						break;
					case 'CUSTOM_FIELD_ihc_country':
							$search_key = str_replace("CUSTOM_FIELD_", "", $constant);
							$country = get_user_meta($uid, $search_key, TRUE);
							$countries = ihc_get_countries();
							$replace = isset( $countries[$country] ) ? $countries[$country] : '';
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

				if ( $replace !== null ){
						$string = str_replace("{" . $constant . "}", $replace, $string);
				}

			} ///end of foreach
		}
	}
	return $string;
}


function ihc_user_get_flag($uid=0, $class='ihc-public-flag'){
	/*
	 * @param int (user id), string (class of image)
	 * @return string (image)
	 */
	$flag = get_user_meta($uid, 'ihc_country', true);
	if (empty($flag)){
		return '';
	} else {
		$countries = ihc_get_countries();
		$key = $flag;
		$flag = strtolower($flag);
		$country = $countries[strtoupper($key)];
		$title = (empty($country)) ? '' : $country;
		return '<img src="' . esc_url(IHC_URL  . 'assets/flags/' . $flag . '.svg' ) .'" class="' . esc_attr($class) . '" title="' . esc_attr($title) . '" />';
	}
}

function ihc_random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
	/*
	 * @param length - int, keyspace - string
	 * @return string
	 */
	$str = '';
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$str .= $keyspace[rand(0, $max)];
	}
	return $str;
}

function ihc_generate_alias_name($length=6, $check=array()){
	/*
	 * @param length, array
	 * @return string
	 */
	$keyspace = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$str = '';
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$str .= $keyspace[rand(0, $max)];
	}
	while (in_array($str, $check)){
		ihc_generate_alias_name($length, $check);
	}
	return $str;
}

function ihc_check_social_status($type){
	/*
	 * @param string name of social media
	 * @return array
	 */
	$return = array();
	$return['active'] = '';
	$return['status'] = 0;
	$return['settings'] = 'Uncompleted';
	switch ($type){
		case 'fb':
			$arr = ihc_return_meta_arr('fb');
			if (!empty($arr['ihc_fb_app_id']) && !empty($arr['ihc_fb_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_fb_status'])){
				$return['status'] = 1;
				$return['active'] = 'fb-active';
			}
			break;
		case 'tw':
			$arr = ihc_return_meta_arr('tw');
			if (!empty($arr['ihc_tw_app_key']) && !empty($arr['ihc_tw_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_tw_status'])){
				$return['status'] = 1;
				$return['active'] = 'tw-active';
			}
			break;
		case 'in':
			$arr = ihc_return_meta_arr('in');
			if (!empty($arr['ihc_in_app_key']) && !empty($arr['ihc_in_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_in_status'])){
				$return['status'] = 1;
				$return['active'] = 'in-active';
			}
			break;
		case 'tbr':
			$arr = ihc_return_meta_arr('tbr');
			if (!empty($arr['ihc_tbr_app_key']) && !empty($arr['ihc_tbr_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_tbr_status'])){
				$return['status'] = 1;
				$return['active'] = 'tbr-active';
			}
			break;
		case 'ig':
			$arr = ihc_return_meta_arr('ig');
			if (!empty($arr['ihc_ig_app_id']) && !empty($arr['ihc_ig_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_ig_status'])){
				$return['status'] = 1;
				$return['active'] = 'ig-active';
			}
			break;
		case 'vk':
			$arr = ihc_return_meta_arr('vk');
			if (!empty($arr['ihc_vk_app_id']) && !empty($arr['ihc_vk_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_vk_status'])){
				$return['status'] = 1;
				$return['active'] = 'vk-active';
			}
			break;
		case 'goo':
			$arr = ihc_return_meta_arr('goo');
			if (!empty($arr['ihc_goo_app_id']) && !empty($arr['ihc_goo_app_secret'])){
				$return['settings'] = 'Completed';
			}
			if (!empty($arr['ihc_goo_status'])){
				$return['status'] = 1;
				$return['active'] = 'goo-active';
			}
			break;
	}
	return $return;
}

function ihc_generate_color_hex(){
	/*
	 * @param none
	 * @return string
	 */
	$colors =  array('#0a9fd8', '#38cbcb', '#27bebe', '#0bb586', '#94c523', '#6a3da3', '#f1505b', '#ee3733', '#f36510', '#f8ba01');
	return $colors[rand(0, (count($colors)-1) )];
}

//=================== COUPONS
function ihc_create_coupon($post_data=array()){
	/*
	 * @param post_data (array)
	 * @return boolean
	 */
	 if ( isset( $post_data['ihc_bttn'] ) ){
		 		unset( $post_data['ihc_bttn'] );
	 }
	 if ( isset( $post_data['ihc_admin_coupons_nonce'] ) ){
				unset( $post_data['ihc_admin_coupons_nonce'] );
	 }
	if ($post_data){
		global $wpdb;
		if (!empty($post_data['how_many_codes'])){
			// ============== MULTIPLE COUPONS ===============//
			$settings = serialize($post_data);
			$prefix = $post_data['code_prefix'];
			$prefix_length = strlen($post_data['code_prefix']);

			if( $post_data['code_length'] <= $prefix_length) {
				$length = 10;
			} else {
				$length = $post_data['code_length'] - $prefix_length;
			}

			$limit = $post_data['how_many_codes'];
			unset($post_data['how_many_codes']);
			unset($post_data['code_prefix']);
			unset($post_data['code_length']);
			if (empty($post_data['discount_value'])){
				return;
			}
			while ($limit){
				$code = ihc_random_str($length);
				$code = $prefix . $code;
				$code = str_replace(' ', '', $code);
				$code = ihc_make_string_simple($code);
				$query = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE code=%s ;", $code );

				$data = $wpdb->get_row( $query );
				unset( $query );
				if ($data){
					continue;
				}
				$query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_coupons VALUES( '', %s, %s, 0, 1);", $code, $settings );
				$wpdb->query( $query );
				unset( $query );
				$limit--;
			}
		} else {
			//============== SINGLE COUPON ==================//
			if (empty($post_data['code']) || empty($post_data['discount_value'])){
				return FALSE;
			}
			//check if this code already exists
			$query = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE code=%s ;", $post_data['code'] );
			$data = $wpdb->get_row( $query );
			if ($data){
				return FALSE;
			}
			$code = str_replace(' ', '', $post_data['code']);
			$code = ihc_make_string_simple($code);
			unset($post_data['code']);
			if (isset($post_data['special_status'])){
				$status = $post_data['special_status'];
				unset($post_data['special_status']);
			} else {
				$status = 1;
			}
			$settings = serialize($post_data);
			$query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_coupons VALUES( '', %s, %s, 0, %s );", $code, $settings, $status );
			$wpdb->query( $query );
			return TRUE;
		}
	}
}

function ihc_update_coupon($post_data=array()){
	/*
	 * @param post_data (array)
	 * @return none
	 */
	if ($post_data){
		if (empty($post_data['code']) || empty($post_data['discount_value'])){
			return FALSE;
		}
		global $wpdb;
		$id = sanitize_text_field($post_data['id']);
		unset($post_data['id']);
		$query = $wpdb->prepare( "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE id=%d;", $id );
		$data = $wpdb->get_row( $query );
		if ($data){
			$code = str_replace(' ', '', $post_data['code']);
			$code = ihc_make_string_simple($post_data['code']);
			unset($post_data['code']);
			unset($post_data['id']);
			$settings = serialize($post_data);
			$query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_coupons
																		SET code=%s, settings=%s
																		WHERE id=%d;
			", $code, $settings, $id );
			$wpdb->query( $query );
		}
	}
}

function ihc_delete_coupon($id){
	/*
	 * @param id (int)
	 * @return none
	 */
	global $wpdb;
	$q = $wpdb->prepare("SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE id=%d;", $id);
	$exists = $wpdb->get_row($q);
	if ($exists){
		$q = $wpdb->prepare("DELETE FROM {$wpdb->prefix}ihc_coupons WHERE id=%d;", $id);
		$wpdb->query($q);
	}
}

// deprecated since version 11.7
function ihc_submit_coupon($code='', $uid=0, $lid=0){

	global $wpdb;
	//check if this code already exists
	$code = str_replace(' ', '', $code);
	if (defined('IHC_COUPON_SUBMITED')){
			return; /// preventing from accidently submit the same coupon twice
	} else {
			define('IHC_COUPON_SUBMITED', 1);
	}
	$q = $wpdb->prepare("SELECT submited_coupons_count FROM {$wpdb->prefix}ihc_coupons WHERE code=%s ;", $code);
	$data = $wpdb->get_row($q);
	if (isset($data->submited_coupons_count)){
		$submited_coupons_count = (int)$data->submited_coupons_count;
		$submited_coupons_count++;
		$table = $wpdb->prefix ."ihc_coupons";
		$q = $wpdb->prepare("UPDATE $table
								SET submited_coupons_count=%d
								WHERE code=%s;", $submited_coupons_count, $code );
		$wpdb->query($q);

		do_action('ump_coupon_code_submited', $code,  $uid, $lid);
		// @description Run after coupon code was submited. @param coupon code, user id, level id.

		return TRUE;
	}
	return FALSE;
}

function ihc_get_coupon_by_code($code=''){
	/*
	 * @param string
	 * @return array
	 */
	$return_data = array();
	if ($code){
		global $wpdb;
		$code = str_replace(' ', '', $code);
		$q = $wpdb->prepare("SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons	WHERE code=%s ;", $code);
		$data = $wpdb->get_row($q);
		if ($data){
			$return_data = maybe_unserialize($data->settings);
			$return_data['code'] = $data->code;
			$return_data['submited_coupons_count'] = $data->submited_coupons_count;
		}
	}
	return $return_data;
}

function ihc_get_all_coupons(){
	/*
	 * @param none
	 * @return array
	 */
	$return_data = array();
	global $wpdb;
	//No query parameters required, Safe query. prepare() method without parameters can not be called
	$query = "SELECT id,code,settings,submited_coupons_count,status FROM {$wpdb->prefix}ihc_coupons WHERE status=1;";
	$data = $wpdb->get_results( $query );
	if ($data){
		foreach ($data as $obj){
			$return_data[$obj->id]['code'] = $obj->code;
			$return_data[$obj->id]['settings'] = maybe_unserialize($obj->settings);
			$return_data[$obj->id]['submited_coupons_count'] = $obj->submited_coupons_count;
		}
	}
	return $return_data;
}

function ihc_get_coupon_by_id($id=0){
	/*
	 * @param string
	 * @return array
	 */
	$arr = array();
	if ($id){
		global $wpdb;
		$q = $wpdb->prepare("SELECT id,code,settings,submited_coupons_count,status FROM " . $wpdb->prefix . "ihc_coupons	WHERE id=%d ", $id);
		$data = $wpdb->get_row($q);
		if ($data && isset($data->code) && isset($data->settings)){
			$arr = maybe_unserialize($data->settings);
			$arr['code'] = $data->code;
		}
	} else {
		$arr = array(
						"code" => "",
						"discount_type" => "percentage",
						"discount_value" => '10',
						"period_type" => "unlimited",
						"repeat" => "10",
						"target_level" => "",
						"reccuring" => "1",
						"start_time" => '',
						"end_time" => '',
						"box_color" => ihc_generate_color_hex(),
						"description" => "",
					);
	}
	return $arr;
}


function ihc_check_coupon($coupon='', $level_id=-1)
{
	$empty = array();
	if (!$coupon || $level_id==-1){
		return $empty;
	}
	$coupon_data = ihc_get_coupon_by_code($coupon);
	if ($coupon_data){

		if (!empty($coupon_data['repeat']) && ($coupon_data['repeat']<=$coupon_data['submited_coupons_count'])){
			//out of repeat number
			return $empty;
		}

		if ($coupon_data['period_type']=='date_range' && !empty($coupon_data['start_time']) && !empty($coupon_data['end_time'])){
			//we must check the time
			$start_time = strtotime($coupon_data['start_time']);
			$end_time = strtotime($coupon_data['end_time']);
			$current_time = indeed_get_unixtimestamp_with_timezone();
			if ($start_time>$current_time){
				//not begin coupon time
				return $empty;
			}
			if ($current_time>$end_time){
				//out of date
				return $empty;
			}
		}
		if ( $coupon_data['target_level'] > -1 ){
				if ( strpos( $coupon_data['target_level'], ',') !== false ){
						// multiple
						$coupon_data['target_level'] = explode( ',', $coupon_data['target_level'] );
						if ( !in_array( $level_id, $coupon_data['target_level'] ) ){
								return $empty;
						}
				} else {
						if ( $coupon_data['target_level'] != $level_id ){
								//it's not the target level
								return $empty;
						}
				}
		}
		return [
			"discount_type" 		=> $coupon_data['discount_type'],
			"discount_value" 		=> $coupon_data['discount_value'],
			"reccuring" 				=> $coupon_data['reccuring'],
			"code" 							=> $coupon,
		];
	}
	return $empty;
}


function ihc_coupon_return_price_after_decrease($price=0, $coupon_data=array(), $update_coupon_count=TRUE, $uid=0, $lid=0){
	/*
	 * @param price int, coupon data array, update coupon count bool
	 * @return price int
	 */
	if ($price && $coupon_data){
		if ($coupon_data['discount_type']=='percentage'){
			$price = $price - ($price*$coupon_data['discount_value']/100);
		} else {
			$price = $price - $coupon_data['discount_value'];
		}
		$price = round($price, 2);

		if ($price<0){
			$price = 0; //// price cannot be negative
		}

		if ($update_coupon_count){
			//lets update the coupon count in db
			ihc_submit_coupon($coupon_data['code'], $uid, $lid);
		}
	}
	return $price;
}

function ihc_get_discount_value($price=0, $coupon_data=array()){
	/*
	 * @param int, int
	 * @return none
	 */
	if ($price && $coupon_data){
		if ($coupon_data['discount_type']=='percentage'){
			return ($price*$coupon_data['discount_value']/100);
		} else {
			return $coupon_data['discount_value'];
		}
	}
}

function ihc_get_redirect_link_by_label($name='', $uid=0){
	/*
	 * @param string, int (USER ID used for login first redirect, when current_user is not available)
	 * @return string
	 */
	if ($name=='#individual_page#'){
		if (empty($uid)){
			global $current_user;
			if (!empty($current_user->ID)){
				$uid = $current_user->ID;
			}
		}
		if (!empty($uid)){
			$individual_page = get_user_meta($uid, 'ihc_individual_page', TRUE);
			if ($individual_page){
				$redirect_to = get_permalink($individual_page);
				if ($redirect_to){
					return $redirect_to;
				}
			}
		}
	} else {
		$data = get_option("ihc_custom_redirect_links_array");
		if (isset($data[$name])){
			return $data[$name];
		}
	}
	return '';
}

function ihc_run_opt_in($email='', $target_opt_in=''){
	/*
	 * @param string
	 * @return none
	 */
	if (!$target_opt_in){
		$target_opt_in = get_option('ihc_register_opt-in-type');
	}
	do_action('ihc_run_opt_in_action', $email, $target_opt_in);
	// @description Run on opt in. @param email (string), type of service for opt-in (string)

	if ($target_opt_in && $email){
		if (!class_exists('IhcMailServices')){
			require_once IHC_PATH . 'classes/IhcMailServices.class.php';
		}
		$uid = \Ihc_Db::get_wpuid_by_email( $email );
		if ( isset( $_POST['first_name'] ) ){
				$firstName = sanitize_text_field( $_POST['first_name'] );
		} else {
				$firstName = get_user_meta( $uid, 'first_name', true );
		}
		if ( !$firstName ){
				$firstName = '';
		}
		if ( isset( $_POST['last_name'] ) ){
				$lastName = sanitize_text_field( $_POST['last_name'] );
		} else {
				$lastName = get_user_meta( $uid, 'last_name', true );
		}
		if ( !$lastName ){
				$lastName = '';
		}

		$indeed_mail = new IhcMailServices();
		$indeed_mail->dir_path = IHC_PATH . 'classes';
		switch ($target_opt_in){
			case 'aweber':
				$awListOption = get_option('ihc_aweber_list');
				if ($awListOption){
					$aw_list = str_replace('awlist', '', $awListOption);
					$consumer_key = get_option( 'ihc_aweber_consumer_key' );
					$consumer_secret = get_option( 'ihc_aweber_consumer_secret' );
					$access_key = get_option( 'ihc_aweber_acces_key' );
					$access_secret = get_option( 'ihc_aweber_acces_secret' );
					if ($consumer_key && $consumer_secret && $access_key && $access_secret){
						$return = $indeed_mail->indeed_aWebberSubscribe( $consumer_key, $consumer_secret, $access_key, $access_secret, $aw_list, $email, $firstName . ' ' . $lastName );
					}
				}
				break;

			case 'email_list':
				$email_list = get_option('ihc_email_list');
				$email_list .= $email . ',';
				update_option('ihc_email_list', $email_list);
				break;

			case 'mailchimp':
				$mailchimp_api = get_option( 'ihc_mailchimp_api', false );
				$mailchimp_id_list = get_option( 'ihc_mailchimp_id_list', false );
				if ( $mailchimp_api !== false && $mailchimp_api !== ''
					&& $mailchimp_id_list !== false && $mailchimp_id_list !== '' ){
					$indeed_mail->indeed_mailChimp( $mailchimp_api, $mailchimp_id_list, $email, $firstName, $lastName );
				}
				break;

			case 'get_response':
				$api_key = get_option('ihc_getResponse_api_key');
				$token = get_option('ihc_getResponse_token');
				if ( $api_key === '' || $token === '' ){
						return false;
				}
				$addcontacturl = 'https://api.getresponse.com/v3/contacts/';
				$getcontacturl = 'https://api.getresponse.com/v3/contacts?query[email]='.$email;
				$fullName = $firstName . ' ' . $lastName;
				$data = array (
				'name' 				=> $fullName,
				'email' 			=> $email,
				'dayOfCycle' 	=> 0,
				'campaign' 		=> array( 'campaignId' => $token ),
				'ipAddress'		=>  $_SERVER['REMOTE_ADDR'],
				);

				$dataString = json_encode($data);

				$ch = curl_init($addcontacturl);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						    'Content-Type: application/json',
						    'X-Auth-Token: api-key '.$api_key,
						)
				);

				$result = curl_exec($ch);
				break;

			case 'campaign_monitor':
				$listId = get_option('ihc_cm_list_id');
				$apiID = get_option('ihc_cm_api_key');
				if ($listId && $apiID){
					$indeed_mail->indeed_campaignMonitor( $listId, $apiID, $email, $firstName . ' ' . $lastName );
				}
				break;

			case 'icontact':
				$appId = get_option('ihc_icontact_appid');
				$apiPass = get_option('ihc_icontact_pass');
				$apiUser = get_option('ihc_icontact_user');
				$listId = get_option('ihc_icontact_list_id');
				if ($appId && $apiPass && $apiUser && $listId){
					$indeed_mail->indeed_iContact( $apiUser, $appId, $apiPass, $listId, $email, $firstName, $lastName );
				}
				break;

			case 'constant_contact':
				/*
				$apiUser = get_option('ihc_cc_user');
				$apiPass = get_option('ihc_cc_pass');
				$listId = get_option('ihc_cc_list');
				if ($apiUser && $apiPass && $listId){
					$indeed_mail->indeed_constantContact($apiUser, $apiPass, $listId, $email, $firstName, $lastName );
				}
				*/
				$accessToken = get_option( '' );
				$cc = new \Indeed\Ihc\Services\ConstantContact();
				if ( $cc->getAccessToken() ){
						$cc->storeContact( $email, $firstName, $lastName );
				}
				break;

			case 'wysija':
				$listID = get_option('ihc_wysija_list_id');
				if ($listID){
					$indeed_mail->indeed_wysija_subscribe( $listID, $email, $firstName, $lastName );
				}
				break;

			case 'mymail':
				$listID = get_option('ihc_mymail_list_id');
				if ($listID){
					$indeed_mail->indeed_myMailSubscribe( $listID, $email, $firstName, $lastName );
				}
				break;

			case 'madmimi':
				$username = get_option('ihc_madmimi_username');
				$api_key =  get_option('ihc_madmimi_apikey');
				$listName = get_option('ihc_madmimi_listname');
				if ($username && $api_key && $listName){
					$indeed_mail->indeed_madMimi( $username, $api_key, $listName, $email, $firstName, $lastName );
				}
				break;
			case 'active_campaign':
				$api_url = get_option('ihc_active_campaign_apiurl');
				$api_key =  get_option('ihc_active_campaign_apikey');
				if ($api_url && $api_key){
					$indeed_mail->add_contanct_to_active_campaign( $api_url, $api_key, $email, $firstName, $lastName );
				}
				break;
			default:
				do_action( 'ump_public_action_optin_custom_service', $target_opt_in, $email, $firstName, $lastName );
				break;
		}
	}
}

function ihc_get_custom_constant_fields(){
	/*
	 * @param none
	 * @return array
	 */
	$data = get_option('ihc_user_fields');
	foreach ($data as $arr){
		$fields["{CUSTOM_FIELD_" . $arr['name'] ."}"] = $arr['name'];
	}
	$diff = array('ihc_social_media', 'ihc_coupon', 'recaptcha', 'tos', 'pass2', 'pass1', 'user_login', 'user_email', 'confirm_email', 'first_name', 'last_name', 'ihc_avatar');
	$fields = array_diff($fields, $diff);
	return $fields;
}


function ihc_get_active_payments_services($only_keys=FALSE){
	/*
	 * @param none
	 * @return array
	 */
	$arr = array();
	if (!function_exists('ihc_check_payment_status')){
		require_once IHC_PATH . 'admin/includes/functions.php';
	}
	$gateways = ihc_list_all_payments();

	$gateways_without_labels = array();
	foreach ($gateways as $key=>$value){
		$order = get_option('ihc_' . $key . '_select_order');
		if ($order===FALSE || $order === '' ){
			$order = array_search($key, array_keys($gateways));
		}
		while (!empty($gateways_without_labels[$order])){
			$order = $order+1;
		}
		$gateways_without_labels[$order] = $key;
	}
	ksort($gateways_without_labels);

	foreach ($gateways_without_labels as $k){
		$data = ihc_check_payment_status($k);
		if ($data['status'] && $data['settings']=='Completed'){
			if ($only_keys){
				$arr[] = $k;
			} else {
				$arr[$k] = $gateways[$k];
			}
		}
	}
	return $arr;
}

function ihc_get_active_payment_services(){
	/*
	 * @param none
	 * @return array
	 */
	 $array = array();
	 $gateways = ihc_list_all_payments();
	 foreach ($gateways as $k=>$v){
		$data = ihc_check_payment_status($k);
		if ($data['status'] && $data['settings']=='Completed'){
			$array[$k] = $gateways[$k];
		}
	 }
	 return $array;

}

function ihc_is_level_reccuring($lid=-1){
	/*
	 * @param int
	 * @return bool
	 */
	if ($lid>-1){
		$level_data = ihc_get_level_by_id($lid);
		if (!empty($level_data['access_type']) && $level_data['access_type']=='regular_period'){
			return TRUE;
		}
	}
	return FALSE;
}

function ihc_check_payment_available($type=''){
	/*
	 * check if a payment service it's enabled and has the required keys set
	 * @param string - type of payment
	 * @return bool
	 */
	$status = false;
	if ($type){
		$payment_metas = ihc_return_meta_arr('payment_' . $type);
		switch ($type){
			case 'paypal':
				if (!empty($payment_metas['ihc_paypal_email']) && !empty($payment_metas['ihc_paypal_status'])){
					$status = true;
				}
				break;
			case 'authorize':
				if (!empty($payment_metas['ihc_authorize_login_id']) && !empty($payment_metas['ihc_authorize_transaction_key']) && !empty($payment_metas['ihc_authorize_status'])){
					$status = true;
				}
				break;
			case 'twocheckout':
				if (!empty($payment_metas['ihc_twocheckout_status']) && !empty($payment_metas['ihc_twocheckout_api_user'])
						&& !empty($payment_metas['ihc_twocheckout_api_pass']) && !empty($payment_metas['ihc_twocheckout_private_key'])
						&& !empty($payment_metas['ihc_twocheckout_account_number']) && !empty($payment_metas['ihc_twocheckout_secret_word'])){
					$status = true;
				}
				break;
			case 'bank_transfer':
				if (!empty($payment_metas['ihc_bank_transfer_status']) && !empty($payment_metas['ihc_bank_transfer_message'])){
					$status = true;
				}
				break;
			case 'braintree':
				if ($payment_metas['ihc_braintree_status'] == 1 && !empty($payment_metas['ihc_braintree_merchant_id']) && !empty($payment_metas['ihc_braintree_public_key']) && !empty($payment_metas['ihc_braintree_private_key'])){
					$status = true;
				}
				break;
			case 'mollie':
				if (!empty($payment_metas['ihc_mollie_status']) && !empty($payment_metas['ihc_mollie_api_key'])){
					$status = true;
				}
				break;
			case 'pagseguro':
				if (!empty($payment_metas['ihc_pagseguro_status']) && !empty($payment_metas['ihc_pagseguro_email']) && !empty($payment_metas['ihc_pagseguro_token'])){
					$status = true;
				}
				break;
			case 'paypal_express_checkout':
				if (!empty($payment_metas['ihc_paypal_express_checkout_signature']) && !empty($payment_metas['ihc_paypal_express_checkout_user'])
					&& !empty($payment_metas['ihc_paypal_express_checkout_password']) && !empty($payment_metas['ihc_paypal_express_checkout_status'])){
						$status = true;
				}
				break;
			case 'stripe_checkout_v2':
				if (!empty($payment_metas['ihc_stripe_checkout_v2_secret_key']) && !empty($payment_metas['ihc_stripe_checkout_v2_publishable_key']) && !empty($payment_metas['ihc_stripe_checkout_v2_status'])){
						$status = true;
				}
				break;
			case 'stripe_connect':
				if (
					( (!empty($payment_metas['ihc_stripe_connect_publishable_key']) && !empty($payment_metas['ihc_stripe_connect_client_secret']) && !empty($payment_metas['ihc_stripe_connect_account_id'])) ||
						(!empty($payment_metas['ihc_stripe_connect_test_publishable_key']) && !empty($payment_metas['ihc_stripe_connect_test_client_secret']) && !empty($payment_metas['ihc_stripe_connect_test_account_id'])) )
						&& !empty($payment_metas['ihc_stripe_connect_status'])){
						$status = true;
				}
				break;
		}
	}
	$status = apply_filters( 'ihc_payment_gateway_status', $status, $type );
	// @description Run on check if payment gateway is available. @param bool ( true if available )

	return $status;
}

function ihc_switch_role_for_user($uid=0){
	/*
	 * Switch User Role when Complete a Payment.
	 * @param int
	 * @return none
	 */
	$levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, true );
	if ( count( $levels ) > 1 ){
			return; // only for the first level
	}

	$do_switch = get_option('ihc_automatically_switch_role');
	if ($do_switch && $uid){
		$data = get_userdata($uid);
		if ($data && isset($data->roles) && isset($data->roles[0])){
			$role = get_option('ihc_automatically_new_role');
			if (empty($role)){
				$role = 'subscriber';
			}
			$arr['role'] = $role;
			$arr['ID'] = $uid;
			$userObject = new \WP_User( $uid );
			$userObject->set_role( $role );
		}
	}
}

function ihc_get_currencies_list($return='all'){
	/*
	 * @param string : all, basic, custom
	 * @return array
	 */
	$basic = array(
			'AUD' => 'Australian Dollar (A $)',
			'CAD' => 'Canadian Dollar (C $)',
			'EUR' => 'Euro (&#8364;)',
			'GBP' => 'British Pound (&#163;)',
			'JPY' => 'Japanese Yen (&#165;)',
			'USD' => 'U.S. Dollar ($)',
			'NZD' => 'New Zealand Dollar ($)',
			'CHF' => 'Swiss Franc',
			'HKD' => 'Hong Kong Dollar ($)',
			'SGD' => 'Singapore Dollar ($)',
			'SEK' => 'Swedish Krona',
			'DKK' => 'Danish Krone',
			'PLN' => 'Polish Zloty',
			'NOK' => 'Norwegian Krone',
			'HUF' => 'Hungarian Forint',
			'CZK' => 'Czech Koruna',
			'ILS' => 'Israeli New Shekel',
			'MXN' => 'Mexican Peso',
			'BRL' => 'Brazilian Real (only for Brazilian members)',
			'MYR' => 'Malaysian Ringgit (only for Malaysian members)',
			'PHP' => 'Philippine Peso',
			'TWD' => 'New Taiwan Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira (only for Turkish members)',
			'RUB' => 'Russian Ruble',
			'ZAR' => 'South African rand',
			'GHS' => 'Ghanaian cedi',
			'NGN' => 'Nigerian naira',
			'INR'	=> 'Indian Rupee (&#8377;)',
	);
	$data = get_option('ihc_currencies_list');
	if ($return=='all'){
		if ($data!==FALSE && is_array($data)){
			return $basic+$data;
		}
		return $basic;
	} else if ($return=='basic'){
		return $basic;
	} else {
		return $data;
	}
}

function ihc_get_user_type(){
	/*
	 * @param none
	 * @return string
	 */
	$type = 'unreg';
	if (function_exists('is_user_logged_in') && is_user_logged_in()){
		if (current_user_can('manage_options')){
			 return 'admin';
		}
		//pending user
		global $current_user;
		if ($current_user){
			if (isset($current_user->roles[0]) && $current_user->roles[0]=='pending_user'){
				$type = 'pending';
			}else{
				$type = 'reg';
				$current_user = wp_get_current_user();
				$levels = \Indeed\Ihc\UserSubscriptions::getAllForUserAsList( $current_user->ID, true );
				$levels = apply_filters( 'ihc_public_get_user_levels', $levels, $current_user->ID );

				if ($levels!==FALSE && $levels!=''){
						$type = $levels;
				}
			}
		}
	}
	return $type;
}

function ihc_required_conditional_field_test($name='', $match_string=''){
	/*
	 * @param string, string
	 * @return string with error if it's case, empty string if it's ok
	 */
	$fields_meta = ihc_get_user_reg_fields();
	$key = ihc_array_value_exists($fields_meta, $name, 'name');
	if ($key!==FALSE && isset($fields_meta[$key]) && isset($fields_meta[$key]['type'])
		&& $fields_meta[$key]['type']=='conditional_text' && !empty($fields_meta[$key]['conditional_text'])){
		if ($fields_meta[$key]['conditional_text']!=$match_string){
			return ihc_correct_text($fields_meta[$key]['error_message']);
		}
	}
	return '';
}

function ihc_get_public_register_fields($exclude_field=''){
	/*
	 * used only in register.php admin section,
	 * @param string
	 * @return array
	 */
	$return = array();
	$fields_meta = ihc_get_user_reg_fields();
	foreach ($fields_meta as $arr){
		if ($arr['display_public_reg']>0 && !in_array($arr['type'], array('payment_select', 'social_media', 'upload_image', 'plain_text', 'file', 'capcha')) && $arr['name']!='tos'){
			if ($exclude_field && $exclude_field==$arr['name']){
				continue;
			}
			$return[$arr['name']] = $arr['name'];
		}
	}
	return $return;
}


function ihc_check_license(){
	/*
	 * @param none
	 * @return bool
	 */
	$check = get_option('ihc_license_set');
	if ($check!==FALSE){
		if ($check==1)
			return TRUE;
		return FALSE;
	}
	return TRUE;
}

function ihc_inside_dashboard_error_license($global=FALSE){
	/*
	 * @param none
	 * @return string
	 */
	$url = get_admin_url() . 'admin.php?page=ihc_manage&tab=help';
	$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
	$ol_dL_ogs = new $class();

	if ( $ol_dL_ogs->FGCS() === true || (int)$ol_dL_ogs->FGCS() > 0 || $ol_dL_ogs->GCP() === true ){
		$hide = get_option( 'ihc_hide_admin_license_notice' );
		$currentPage = isset($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $currentPage != 'ihc_manage' && $hide ){
				return '';
		}
		if ($global){
			 $class = 'error ihc-license-warning';
		}
		else{
			 $class = 'ihc-error-global-dashboard-message';
		}
		return "<div class='$class'>
							<div class='ihc-close-notice ihc-js-close-admin-dashboard-notice'>x</div>
							<p>This is a Trial Version of <strong>Ultimate Membership Pro</strong> plugin. Please add your purchase code into Licence section to enable the Full Ultimate Membership Pro Version. Check your <a href='" . $url . "'>licence section</a>.</p></div>";
	}

	if ( $ol_dL_ogs->GTCO() === '1' ){
			$hide = get_option( 'ihc_hide_admin_license_registration_notice' );
			if ( $hide ){
					return '';
			}

			if ( $global ){
				 $class = 'error ihc-license-warning';
			} else {
				 $class = 'ihc-error-global-dashboard-message';
			}
			return "<div class='$class'>
								<div class='ihc-close-notice ihc-js-close-admin-dashboard-registration-notice'>x</div><p>Your <strong>Ultimate Membership Pro</strong> plugin license is not activated and registered. Please add your purchase code into Licence section. Check your <a href='" . $url . "'>licence section</a>.</p></div>";
	}
	return '';
}

function ihc_public_notify_trial_version(){
	/*
	 * @param none
	 * @return string
	 */
	$str = '';
	$str .= '<div class="ihc-public-trial-version">';
	$str .= esc_html__("This is a Trial Version of ", 'ihc').'<a href="https://ultimatemembershippro.com" target="_blank" rel="dofollow">Ultimate Membership Pro</a>'.esc_html__(" plugin. Please add your purchase code into Licence section to enable the Full ", 'ihc').'<a href="https://ultimatemembershippro.com" target="_blank"  rel="dofollow">Ultimate Membership Pro</a>'.esc_html__("  Version.", 'ihc');
	$str .= '</div>';
	return $str;
}

function ihc_make_string_simple($str=''){
	/*
	 * @param string
	 * @return string
	 */
	if (!empty($str)){
		$str = trim($str);
		$str = str_replace(' ', '_', $str);
		$str = preg_replace("/[^A-Za-z0-9_]/", '', $str);//remove all non-alphanumeric chars
	}
	return $str;
}

function ihc_return_transaction_amount_for_user_level($payment_history='', $payment_data=''){
	/*
	 * @param string, string
	 * @return float
	 */
	$count = 0;
	if (!empty($payment_history)){
		$history_data = (isset($payment_history)) ? maybe_unserialize($payment_history) : '';
		if ($history_data && is_array($history_data)){
			// calculating with recurring payments from entire history
			foreach ($history_data as $arr){
				$amount = 0;
				if (isset($arr['amount'])){
					if (isset($arr['ihc_payment_type']) && !empty($arr['ihc_payment_type']) && $arr['ihc_payment_type']=='stripe' && ((empty($arr['type']) || $arr['type']!='charge.succeeded')) ){
						$amount = 0;//stripe first row entry
					} else if ( !empty($arr['ihc_payment_type']) && $arr['ihc_payment_type']=='mollie' && isset( $arr['message'] ) && $arr['message'] == 'pending' ) {
						continue;
					} else {
						$amount = (float)$arr['amount'];
					}
				} else if (isset($arr['mc_gross'])){
					$amount = (float)$arr['mc_gross'];
				} else if (isset($arr['x_amount'])){
					$amount = (float)$arr['x_amount'];
				}
				$count += $amount;
			}
		} else {
			$history_not_available = TRUE;
		}
	} else {
		$history_not_available = FALSE;
	}
	if (!empty($history_not_available)){
		$amount = 0;
		if (isset($obj->payment_data)){
			$arr = json_decode($payment_data, TRUE);
			if (isset($arr['amount'])){
				$amount = (float)$arr['amount'];
			} else if (isset($arr['mc_gross'])){
				$amount = (float)$arr['mc_gross'];
			} else if (isset($arr['x_amount'])){
				$amount = (float)$arr['x_amount'];
			}
		}
		$count = $count + $amount;
	}
	return $count;
}

function ihc_get_user_id_by_user_login($u_login=''){
	/*
	 * @param string
	 * @return int
	 */
	if (!empty($u_login)){
		global $wpdb;
		$q = $wpdb->prepare("SELECT ID FROM " . $wpdb->base_prefix . "users WHERE user_login=%s ;", $u_login);
		$data = $wpdb->get_row($q);
		if (!empty($data->ID)){
			return $data->ID;
		}
	}
	return 0;
}

function ihc_get_avatar_for_uid($uid){
	/*
	 * @param int
	 * @return string
	 */
	$avatar_url = IHC_URL . 'assets/images/no-avatar.png';
	if (!empty($uid)){
		$avatar = get_user_meta( $uid, 'ihc_avatar', true );
		if (!empty($avatar)){
			if (strpos($avatar, "http")===0){
				$avatar_url = $avatar;
			} else {
				$avatar_url = \Ihc_Db::getMediaBaseImage( $avatar );
				if ( $avatar_url && strpos($avatar_url, "http")===0 ){
						return $avatar_url;
				}
				$avatar_data = wp_get_attachment_image_src($avatar, 'full');
				if (!empty($avatar_data[0])){
					$avatar_url = $avatar_data[0];
				}
			}
		} else {
			$temp_metas = ihc_return_meta_arr('public_workflow');
			if ($temp_metas['ihc_use_gravatar']){
				/// GRAVATAR
				if (function_exists('get_avatar_url')){
					$avatar = get_avatar_url($uid);
				} else if (function_exists('get_avatar')){
					/// < wp 4.2
    				$avatar = get_avatar($uid);
    				preg_match("/src='(.*?)'/i", $avatar, $matches);
    				$avatar = $matches[1];
				}

			} else if ($temp_metas['ihc_use_buddypress_avatar'] && function_exists('bp_core_fetch_avatar')){
				/// BUDDYPRESS
				$avatar = bp_core_fetch_avatar(array('item_id' => $uid, 'html' => FALSE, 'type' => 'full'));
			}
			if (!empty($avatar)){
				$avatar_url = $avatar;
			}
		}
	}
	return $avatar_url;
}

function ihc_get_admin_ids_list(){
	/*
	 * @param none
	 * @return array
	 */
	$ids = array();
	$data = get_users(array('role' => 'administrator'));
	if ($data && is_array($data)){
		foreach ($data as $user) {
			$ids[] = $user->ID;
		}
	}
	return $ids;
}

function ihc_return_user_sm_profile_visit($uid=0){
	/*
	 * @param int
	 * @return string
	 */
	$str = '';
	if ($uid){
		$sm_base = array(
									'ihc_fb' => 'https://www.facebook.com/',/// profile.php?id=
									'ihc_tw' => 'https://twitter.com/intent/user?user_id=',
									'ihc_in' => 'https://www.linkedin.com/profile/view?id=',
									'ihc_tbr' => 'https://www.tumblr.com/blog/',
									'ihc_ig' => 'http://instagram.com/_u/',
									'ihc_vk' => 'http://vk.com/id',
									'ihc_goo' => 'https://plus.google.com/',
		);
		foreach ($sm_base as $k=>$v){
			$data = get_user_meta($uid, $k, TRUE);
			if (!empty($data)){
				$class = str_replace('_', '-', $k);
				$str .= "<div class='ihc-account-page-sm-icon ihc-display-inline" . $class . "'>";
				$str .= "<a href='" . $v . $data . "'>";
				$str .= "<i class='fa-ihc-sm fa-" . $class . "'></i>";
				$str .= '</a>';
				$str .= "</div>";
			}
		}
	}
	if ($str){
		$str = "<div class='ihc-ap-sm-top-icons-wrap'>" . $str . "</div>";
	}
	return $str;
}

function ihc_save_rewrite_rule_for_register_view_page($page_id=0){
	/*
	 * @param int
	 * @return none
	 */
	if ($page_id){
		$post_name = get_post_field('post_name', $page_id);
		if (!empty($post_name)){
			add_rewrite_rule("$post_name/([^/]+)/?", 'index.php?pagename=' . $post_name . '&ihc_name=$matches[1]', 'top');
			add_rewrite_rule("$post_name/([^/]+)/?",'index.php?page_id=' . $page_id . '&ihc_name=$matches[1]', 'top');
			flush_rewrite_rules();
		}
	}
}

function ihc_is_uap_active(){
	/*
	 * @param none
	 * @return boolean
	 */
	 if (!function_exists('is_plugin_active')){
	 	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	 }
	 if (file_exists(WP_CONTENT_DIR . '/plugins/indeed-affiliate-pro/indeed-affiliate-pro.php') && is_plugin_active('indeed-affiliate-pro/indeed-affiliate-pro.php')){
		//if (get_option('uap_license_set')==1){
			return TRUE;
		//}
	}
	return FALSE;
}

function ihc_meta_value_exists($meta_key='', $meta_value=''){
	/*
	 * @param string
	 * @return boolean
	 */
	if ($meta_key && $meta_value){
		global $wpdb;
		$table = $wpdb->base_prefix . 'usermeta';
		$q = $wpdb->prepare("SELECT umeta_id,user_id,meta_key,meta_value FROM $table WHERE meta_value=%s AND meta_key=%s ;", $meta_value, $meta_key);
		$data = $wpdb->get_results($q);
		if (!empty($data)){
			return TRUE;
		}
	}
	return FALSE;
}

function ihc_save_metas_group($group='', $post_data=array()){
	/*
	 * @param string, array
	 * @return none
	 */
	$data = ihc_return_meta_arr($group, true);
	foreach ($data as $k=>$v){
		if (isset($post_data[$k])){
			$data_db = get_option($k);
			if ($data_db!==FALSE){
				update_option($k, $post_data[$k]);
			} else {
				add_option($k, $post_data[$k]);
			}
		}
	}
}

function ihc_get_taxes_for_amount_by_country($country='', $state='', $amount=0){
	/*
	 * @param string, float || int
	 * @return array
	 */
	 $array = array();
	 if (!get_option('ihc_enable_taxes')){
	 	return $array;
	 }
	 $currency = get_option('ihc_currency');
	 if (!empty($country)){
		 $data = Ihc_Db::get_taxes_by_country($country, $state);
		 if ($data){
			$array['total'] = 0;
			$array['currency'] = get_option("ihc_currency");
			foreach ($data as $tax){
				$temp['label'] = $tax['label'];
				$temp['value'] = $tax['amount_value'] * $amount / 100;
				$temp['value'] = round($temp['value'], 2);
				$temp['print_value'] = ihc_format_price_and_currency($currency, $temp['value']);
				$array['items'][] = $temp;
				$array['total'] += $temp['value'];
			}
			$array['print_total'] = ihc_format_price_and_currency($currency, $array['total']);
			return $array;
		 }
	 }
	//use the defaults
	$taxes_settings = ihc_return_meta_arr('ihc_taxes_settings');
	if (!empty($taxes_settings['ihc_default_tax_label']) && !empty($taxes_settings['ihc_default_tax_value'])){
		$array['currency'] = get_option("ihc_currency");
		$item['label'] = $taxes_settings['ihc_default_tax_label'];
		$item['value'] = $taxes_settings['ihc_default_tax_value'] * $amount / 100;
		$item['value'] = round($item['value'], 2);
		$item['print_value'] = ihc_format_price_and_currency($currency, $item['value']);
		$array['items'][] = $item;
		$array['total'] = $item['value'];
		$array['print_total'] = ihc_format_price_and_currency($currency, $array['total']);
	}
	return $array;
}

function ihc_convert_date_to_us_format($date=''){
	/*
	 * @param string
	 * @return string
	 */
	if ($date && $date!='-' && is_string($date)){
		$date = (isset($date)) ? strtotime($date) : '';

		$format = get_option('date_format');
		$return_date = date_i18n($format, $date);
		return $return_date;
	}
	return $date;
}
function ihc_convert_date_time_to_us_format($date=''){
	/*
	 * @param string
	 * @return string
	 */
	if ($date && $date!='-' && is_string($date)){
		$date = (isset($date)) ? strtotime($date) : '';

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$return_date = date_i18n($date_format . ' '. $time_format, $date);
		return esc_html($return_date);
	}
	return $date;
}
function ihc_get_user_orders_count($user_id=''){
	global $wpdb;

	$count = 0;
	$table = $wpdb->prefix . 'ihc_orders';
		$q = $wpdb->prepare("SELECT COUNT(id) AS count FROM $table WHERE uid=%d ", $user_id);
		$data = $wpdb->get_results($q);
		if (!empty($data)){
			$count = $data[0]->count;
		}
	return $count;

}

function ihc_is_magic_feat_active($type=''){
	/*
	 * @param string
	 * @return boolean
	 */
	 $active = false;
	 if ($type){
	 	switch ($type){
			case 'taxes':
				$active = get_option('ihc_enable_taxes');
				break;
			case 'bp_account_page':
				$active = get_option('ihc_bp_account_page_enable');
				break;
			case 'woo_account_page':
				$active = get_option('ihc_woo_account_page_enable');
				break;
			case 'membership_card':
				$active = get_option('ihc_membership_card_enable');
				break;
			case 'cheat_off':
				$active = get_option('ihc_cheat_off_enable');
				break;
			case 'invitation_code':
				$active = get_option('ihc_invitation_code_enable');
				break;
			case 'download_monitor_integration':
				$active = get_option('ihc_download_monitor_enabled');
				break;
			case 'register_lite':
				$active = get_option('ihc_register_lite_enabled');
				break;
			case 'individual_page':
				$active = get_option('ihc_individual_page_enabled');
				break;
			case 'level_restrict_payment':
				$active = get_option('ihc_level_restrict_payment_enabled');
				break;
			case 'level_subscription_plan_settings':
				$active = get_option('ihc_level_subscription_plan_settings_enabled');
				break;
			case 'gifts':
				$active = get_option('ihc_gifts_enabled');
				break;
			case 'login_level_redirect':
				$active = get_option('ihc_login_level_redirect_on');
				break;
			case 'register_redirects_by_level':
				$active = get_option('ihc_register_redirects_by_level_enable');
				break;
			case 'wp_social_login':
				$active = get_option('ihc_wp_social_login_on');
				break;
			case 'list_access_posts':
				$active = get_option('ihc_list_access_posts_on');
				break;
			case 'invoices':
				$active = get_option('ihc_invoices_on');
				break;
			case 'woo_payment':
				$active = get_option('ihc_woo_payment_on');
				break;
			case 'badges':
				$active = get_option('ihc_badges_on');
				break;
			case 'login_security':
				$active = get_option('ihc_login_security_on');
				break;
			case 'workflow_restrictions':
				$active = get_option('ihc_workflow_restrictions_on');
				break;
			case 'subscription_delay':
				$active = get_option('ihc_subscription_delay_on');
				break;
			case 'level_dynamic_price':
				$active = get_option('ihc_level_dynamic_price_on');
				break;
			case 'user_reports':
				$active = get_option('ihc_user_reports_enabled');
				break;
			case 'pushover':
				$active = get_option('ihc_pushover_enabled');
				break;
			case 'account_page_menu':
				$active = get_option('ihc_account_page_menu_enabled');
				break;
			case 'mycred':
				$active = get_option('ihc_mycred_enabled');
				break;
			case 'api':
				$active = get_option('ihc_api_enabled');
				break;
			case 'woo_product_custom_prices':
				$active = get_option('ihc_woo_product_custom_prices_enabled');
				break;
			case 'drip_content_notifications':
				$active = get_option('ihc_drip_content_notifications_enabled');
				break;
			case 'user_sites':
				$active = get_option('ihc_user_sites_enabled');
				break;
			case 'zapier':
				$active = get_option('ihc_zapier_enabled');
				break;
			case 'infusionSoft':
				$active = get_option( 'ihc_infusionSoft_enabled' );
				break;
			case 'kissmetrics':
				$active = get_option( 'ihc_kissmetrics_enabled' );
				break;
			case 'direct_login':
				$active = get_option( 'ihc_direct_login_enabled' );
				break;
			case 'reason_for_cancel':
				$active = get_option( 'ihc_reason_for_cancel_enabled' );
				break;
			case 'weekly_summary_email':
				$active = get_option('ihc_reason_for_weekly_email_enabled');
				break;
			case 'prorate_subscription':
				$active = get_option('ihc_prorate_subscription_enabled');
				break;
	 	}
	 }
	 $active = apply_filters( 'ihc_is_magic_feat_active_filter', $active, $type );
	 // @description Filter if a magic feature is active. @param is active (boolean), type of magic feature (string)

	 return $active;
}

function get_terms_for_post_id($post_id=0){
	/*
	 * @param int
	 * @return array
	 */
	 $array = array();
	 if ($post_id){
	 	 global $wpdb;
	 	 $table = $wpdb->prefix . 'term_relationships';
		 $q = $wpdb->prepare("SELECT term_taxonomy_id FROM $table WHERE object_id=%d ", $post_id);
		 $data = $wpdb->get_results($q);
		 if (!empty($data)){
		 	foreach ($data as $object){
		 		$array[] = $object->term_taxonomy_id;
		 	}
		 }
	 }
	 return $array;
}

function ihc_get_all_terms_with_names(){
	/*
	 * @param none
	 * @retunr array
	 */
	 $array = array();
	 global $wpdb;
	 $table = $wpdb->prefix . 'terms';
	 $table_2 = $wpdb->prefix . 'term_relationships';
	 //No query parameters required, Safe query. prepare() method without parameters can not be called
	 $query = "SELECT term_id, name FROM $table t1 INNER JOIN $table_2 t2 ON t2.term_taxonomy_id=t1.term_id;";
	 $data = $wpdb->get_results( $query );
	 if (!empty($data)){
	 	foreach ($data as $object){
	 		$array[$object->term_id] = $object->name;
	 	}
		$exclude = array('settings-verify-email-change', 'groups-membership-request-accepted', 'groups-membership-request-rejected', 'friends-request',
		'core-user-registration', 'core-user-registration-with-blog',
		);
		foreach ($exclude as $e){
			if ($k=array_search($e, $array)){
				unset($array[$k]);
				unset($k);
			}
		}
	 }
	 return $array;
}


function ihc_do_write_into_htaccess($extensions='mp3|mp4|avi|pdf|zip|rar|doc|gz|tar|docx|xls|xlsx|PDF'){
	/*
	 * @param none
	 * @return none
	 */
	 $file = ABSPATH . '.htaccess';
	 if (file_exists($file) && is_writable($file)){
	 	/// READ FROM HTACCESS
		$data = file_get_contents($file);
		$resource = fopen($file, 'r');
		$data = fread($resource, filesize($file));
		fclose($resource);
		unset($resource);
		$path_to_check_file = WP_CONTENT_DIR . '/plugins/indeed-membership-pro/public/check-file-permissions.php';
		$string_to_write = '#BEGIN Ultimate Membership Pro Rules
	<IfModule mod_rewrite.c>
		RewriteCond %{REQUEST_URI} !^/(wp-content/themes|wp-content/plugins|wp-admin|wp-includes)
		RewriteCond %{REQUEST_URI} \.(' . $extensions . ')
		RewriteRule . ' . $path_to_check_file . ' [L]
	</IfModule>
#END Ultimate Membership Pro Rules';
		if (strpos($data, $string_to_write)===FALSE){
			$string_to_write = '#BEGIN Ultimate Membership Pro Rules
		<IfModule mod_rewrite.c>
			RewriteCond %{REQUEST_URI} !^/(wp-content/themes|wp-content/plugins|wp-admin|wp-includes)
			RewriteCond %{REQUEST_URI} \.(' . $extensions . ')
			RewriteRule . /index.php?ihc_action=check-file-permissions [L]
		</IfModule>
	#END Ultimate Membership Pro Rules';
			if (strpos($data, $string_to_write)===FALSE){
					$data = $data . $string_to_write;
					$resource = fopen($file, 'w+');
					fwrite($resource, $data);/// WRITE THE NEW CONTENT
					fclose($resource);
			}
		} else {
				// conditions already exists, lets update them
				$newCondition = '#BEGIN Ultimate Membership Pro Rules
			<IfModule mod_rewrite.c>
				RewriteCond %{REQUEST_URI} !^/(wp-content/themes|wp-content/plugins|wp-admin|wp-includes)
				RewriteCond %{REQUEST_URI} \.(' . $extensions . ')
				RewriteRule . /index.php?ihc_action=check-file-permissions [L]
			</IfModule>
		#END Ultimate Membership Pro Rules';
				$data = str_replace( $string_to_write, $newCondition, $data );
				$resource = fopen($file, 'w+');
				fwrite($resource, $data);/// WRITE THE NEW CONTENT
				fclose($resource);
		}
	 }
}

if ( !function_exists( 'ihc_format_price_and_currency' ) ):
/**
 * @param string
 * @param string
 * @return string
 */
function ihc_format_price_and_currency( $currency='', $price_value='' )
{
	 $output = '';
	 $settings = ihc_return_meta_arr('payment');

	 if ( !empty( $settings['ihc_custom_currency_code'] ) ){
	 		$currency = $settings['ihc_custom_currency_code'];
	 }
	 if ($price_value !== null && $price_value !== '' && isset( $settings['ihc_num_of_decimals'] ) && $settings['ihc_num_of_decimals'] >= 0 && isset( $settings['ihc_decimals_separator'] ) && isset( $settings['ihc_thousands_separator'] ) ){
	 		$price_value = number_format( $price_value, $settings['ihc_num_of_decimals'], $settings['ihc_decimals_separator'], $settings['ihc_thousands_separator'] );
	 }
	 if ( $settings['ihc_currency_position'] == 'left' ){
	 		$output = $currency . $price_value;
	 } else {
	 		$output = $price_value . $currency;
	 }
	 return $output;
}
endif;

if ( !function_exists( 'ihc_format_price_and_currency_with_price_wrapp' ) ):
/**
 * @param string
 * @param string
 * @param string
 * @return string
 */
function ihc_format_price_and_currency_with_price_wrapp( $currency='', $price_value='', $priceHtmlAttr='' )
{
	 $output = '';
	 $settings = ihc_return_meta_arr('payment');
	 if ( !empty( $settings['ihc_custom_currency_code'] ) ){
	 		$currency = $settings['ihc_custom_currency_code'];
	 }
	 if ( isset( $settings['ihc_num_of_decimals'] )  && $settings['ihc_num_of_decimals'] >= 0  && isset( $settings['ihc_decimals_separator'] ) && isset( $settings['ihc_thousands_separator'] ) ){
	 		$price_value = number_format( $price_value, $settings['ihc_num_of_decimals'], $settings['ihc_decimals_separator'], $settings['ihc_thousands_separator'] );
	 }

	 if ( $settings['ihc_currency_position'] == 'left' ){
	 		$output = $currency . "<span $priceHtmlAttr>" . $price_value . '</span>';
	 } else {
	 		$output = "<span $priceHtmlAttr>" . $price_value . '</span>' . $currency;
	 }
	 return $output;
}
endif;

function ihc_get_levels_with_payment(){
	/*
	 * @param none
	 * @return array
	 */
	 $data = \Indeed\Ihc\Db\Memberships::getAll();
	 if ($data){
	 	foreach ($data as $key=>$array){
	 		if ($array['payment_type']=='free'){
	 			unset($data[$key]);
	 		}
	 	}
		return $data;
	 }
	 return array();
}

function ihc_get_state_field_str($country='', $attr=[]){
	/*
	 * @param string
	 * @return string
	 */
	$str = '';
	$extraClass = isset( $attr['class'] ) ? $attr['class'] : '';
	$id = isset( $attr['id'] ) ? $attr['id'] : 'ihc_state_field_' . rand( 1, 10000 );
	$value = isset( $attr['value'] ) ? ihc_correct_text( $attr['value'] ) : '';
	$placeholder = isset( $attr['placeholder'] ) ? $attr['placeholder'] : '';
	$otherArgs = isset( $attr['other_args'] ) ? $attr['other_args'] : '';
	$disabled = isset( $attr['disabled'] ) ? $attr['disabled'] : '';
	$name = isset( $attr['name'] ) ? $attr['name'] : 'ihc_state';
	switch ($country){
		case 'US':
			include IHC_PATH . 'public/static-data.php';
			$states = indeedUsCaStates();
			if ( empty( $attr['avoid_reload_cart'] ) ){
					$onChange = '';//'ihcUpdateCart();';// deprecated since 11.8
			} else {
					$onChange = '';
			}
			$str .= "<select class='iump-form-select $extraClass' id='$id' placeholder='$placeholder' $disabled $otherArgs
			 				name='$name' onChange='$onChange'>";
			foreach ($states['US'] as $prefix => $label){
				$selected = $prefix === $value ? 'selected' : '';
				$str .= "<option value='$prefix' $selected >$label</option>";
			}
			$str .= "</select>";
			break;
		case 'CA':
			include IHC_PATH . 'public/static-data.php';
			$states = indeedUsCaStates();
			if ( empty( $attr['avoid_reload_cart'] ) ){
					$onChange = '';//'ihcUpdateCart();';// deprecated since 11.8
			} else {
					$onChange = '';
			}
			$str .= "<select class='iump-form-select $extraClass' id='$id' placeholder='$placeholder' $disabled $otherArgs
			 				name='$name' onChange='$onChange'>";
			foreach ($states['CA'] as $prefix => $label){
				$selected = $prefix === $value ? 'selected' : '';
				$str .= "<option value='$prefix' $selected >$label</option>";
			}
			$str .= "</select>";
			break;
		default:
			if ( empty( $attr['avoid_reload_cart'] ) ){
					$onBlur = '';//'ihcUpdateCart();';// deprecated since 11.8
			} else {
					$onBlur = '';
			}

			$str .= "<input type='text'
			onBlur='$onBlur'
			name='$name'
			id='$id'
			class='$extraClass'
			value='$value'
			placeholder='$placeholder'
			$disabled
			$otherArgs />";
			break;
	}
	return $str;
}

function ihc_do_show_hide_admin_bar_on_public(){
	/*
	 * @param none
	 * @return none
	 */
	 if (!current_user_can('manage_options')){
		if (function_exists('is_user_logged_in') && is_user_logged_in()){
			/// ONLY REGISTERED USERS
			$uid = get_current_user_id();
			$user = new WP_User($uid);

			// show for super admin
			if ( is_super_admin( $uid ) ){
					return show_admin_bar( true );
			}

			if ($user && !empty($user->roles) && !empty($user->roles[0]) && !in_array( 'administrator', $user->roles ) ){
				$allowed_roles = get_option('ihc_dashboard_allowed_roles');
				$allowed_roles = apply_filters( 'ihc_filter_allowed_roles_in_dashboard', $allowed_roles );

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
}

if (!function_exists('indeed_debug_var')):
function indeed_debug_var($variable){
	/*
	 * print the array into '<pre>' tags
	 * @param array, string, int ... anything
	 * @return none (echo)
	 */
	 if (is_array($variable) || is_object($variable)){
		 echo esc_ump_content('<pre>');
		 print_r($variable);
		 echo esc_ump_content('</pre>');
	 } else {
	 	var_dump($variable);
	 }
}
endif;

if (!function_exists('ihc_get_custom_field_label')):
function ihc_get_custom_field_label($slug=''){
	/*
	 * Return Label of custom register field by slug
	 * @param string
	 * @return string
	 */
	 $data = get_option('ihc_user_fields');
	 if ($data){
	 	 $key = ihc_array_value_exists($data, $slug, 'name');
		 if (isset($data[$key]) && isset($data[$key]['label'])){
		 	return $data[$key]['label'];
		 }
	 }
	 return '';
}
endif;

if (!function_exists('ihc_listing_user_get_filter_fields')):
function ihc_listing_user_get_filter_fields(){
	/*
	 * @param none
	 * @return array
	 */
  	 $return = array();
	 $data = get_option('ihc_user_fields');
	 $allow = array('select', 'multi_select', 'checkbox', 'radio', 'date', 'number', 'ihc_country');
	 $not_allow_names = array('tos');
	 if ($data){
	 	foreach ($data as $k=>$array){
	 		if (in_array($array['type'], $allow) && !in_array($array['name'], $not_allow_names)){
	 			$return[$array['name']] = $array['label'];
	 		}
	 	}
	 }
	return $return;
}
endif;

if (!function_exists('ihc_register_field_get_type_by_slug')):
function ihc_register_field_get_type_by_slug($slug=''){
	/*
	 * @param string
	 * @return string
	 */
	 if ($slug){
	 	 $data = get_option('ihc_user_fields');
		 $key = ihc_array_value_exists($data, $slug, 'name');
		 if ($key!==FALSE && isset($data[$key])){
		 	return $data[$key]['type'];
		 }
	 }
}
endif;

if (!function_exists('ihc_make_level_expire_for_user')):
function ihc_make_level_expire_for_user($uid=0, $lid=0){
	/*
	 * @param int, int
	 * 2return none
	 */
	 if ($uid && $lid!==FALSE){
	 	 global $wpdb;
		 $table = $wpdb->prefix . 'ihc_user_levels';
		 $q = $wpdb->prepare("UPDATE $table SET expire_time='0000-00-00 00:00:00', notification=0 WHERE user_id=%d AND level_id=%d ", $uid, $lid);
		 $wpdb->query($q);
	 }
}
endif;

if (!function_exists('ihc_suspend_account')):
function ihc_suspend_account($uid=0){
	/*
	 * @param int
	 * @return boolean
	 */
	 if ($uid){
	 	 /// CANCEL & DELETE * THE LEVELS
	 	 $levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid );
		 if ($levels){
		 	 foreach ($levels as $lid=>$array){
				 \Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
				 $cancel = new \Indeed\Ihc\Payments\CancelSubscription();
				 $cancel->setUid( $uid )
								->setLid( sanitize_text_field( $lid ) )
								->proceed();
		 	 }
		 }

		 /// MAKE ROLE SUSPEND
		 wp_update_user(array('ID'=>$uid, 'role'=>'suspended'));
		 return TRUE;
	 }
	 return FALSE;
}
endif;

if (!function_exists('ihc_get_register_form_fields_order')):
function ihc_get_register_form_fields_order(){
	/*
	 * @param none
	 * @return array
	 */
	$array_return = array();
	$data = get_option('ihc_user_fields');
	ksort($data);
	$array_return = array();
	foreach ($data as $key=>$array){
		$array_return[$array['name']] = $key;
	}
	return $array_return;
}
endif;


if (!function_exists('ihc_register_form_get_order_values')):
function ihc_register_form_get_order_values($name=''){
	/*
	 * @param string
	 * @eturn array
	 */
	 if ($name){
		$data = get_option('ihc_user_fields');
		$key = ihc_array_value_exists($data, $name, 'name');
		if (isset($data[$key]) && isset($data[$key]['values']) && $name != 'ihc_country'){
			return $data[$key]['values'];
		}
	 }
}
endif;

if (!function_exists('ihc_check_dynamic_price_from_user')):
function ihc_check_dynamic_price_from_user($lid=0, $amount=0){
	/*
	 * @param int($lid), float($amount)
	 * @return boolean (TRUE if ok)
	 */
	if (!empty($lid)){
		$temp_settings = ihc_return_meta_arr('level_dynamic_price');
		if (!empty($temp_settings['ihc_level_dynamic_price_levels_on'][$lid])){
			$min = isset($temp_settings['ihc_level_dynamic_price_levels_min'][$lid]) ? $temp_settings['ihc_level_dynamic_price_levels_min'][$lid] : 0;
			if ($min<=$amount){
				return TRUE;
			}
		}
	}
	return FALSE;
}
endif;

if (!function_exists('ihc_reorder_menu_items')):
function ihc_reorder_menu_items($order=array(), $array=array()){
	/*
	 * @param array, array
	 * @return array
	 */
	 if (!empty($order) && is_array($order)){
		 $return_array = array();
		 foreach ($order as $key=>$value){
		 	 if (isset($array[$key])){
		 	 	 $return_array[$key] = $array[$key];
				 unset($array[$key]);
		 	 }
		 }
		 if (!empty($array)){
		 	$return_array = array_merge($return_array, $array);
		 }
		 return $return_array;
	 }
	 return $array;
}
endif;

if (!function_exists('ihc_do_user_approve')):
function ihc_do_user_approve($uid=0){
	/*
	 * Approve User and send a nice notification.
	 * @param int
	 * @return bool
	 */
	if ($uid){
		$data = get_userdata($uid);
		if ($data && isset($data->roles) && isset($data->roles[0]) && $data->roles[0]=='pending_user'){
			$default_role = get_option('default_role');
			$user_id = wp_update_user(array( 'ID' => $uid, 'role' => $default_role));
			if ($user_id==$uid){
				do_action( 'ihc_action_approve_user_account', $user_id );
				return TRUE;
			}
		}
	}
	return FALSE;
}
endif;

if (!function_exists('ihc_generate_random_string')):
function ihc_generate_random_string($length=10){
	/*
	 * @param int
	 * @return string
	 */
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $output = '';
    for ($i = 0; $i < $length; $i++){
        $output .= $chars[rand(0, strlen($chars))];
    }
    return $output;
}
endif;

/**
 * Convert array of objects into array of array
 * @param mixed (array or object)
 * @return array
 */
if (!function_exists('indeed_convert_to_array')):
function indeed_convert_to_array($input=null){
	foreach ($input as $object){
		$array[] = (array)$object;
	}
	return $array;
}
endif;

if (!function_exists('indeed_preg_match_callback')):
function indeed_preg_match_callback($matches){
	return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
}
endif;

/**
 * @param array
 * @return int
 */
if (!function_exists('ihc_get_biggest_key_from_array')):
function ihc_get_biggest_key_from_array($input=array()){
		$max = 0;
		foreach ($input as $key=>$value){
  		if ($key>$max){
				 $max = $key;
			}
		}
		return $max;
}
endif;


function ihc_get_user_pending_trial_order($user_id='', $level_id='', $level_data=null){
	global $wpdb;

	$count = 0;
	$table = $wpdb->prefix . 'ihc_orders';
		$q = $wpdb->prepare("SELECT COUNT(id) AS count FROM $table
											WHERE
											uid=%d
											AND lid=%d
											AND amount_value=%s
											AND status='pending'
											ORDER BY create_date DESC
											LIMIT 1
				", $user_id, $level_id, 0);
		$data = $wpdb->get_results($q);
		if (!empty($data)){
			$count = $data[0]->count;
		}
		if($count == 0){
			$q = $wpdb->prepare("SELECT COUNT(id) AS count FROM $table
											WHERE
											uid=%d
											AND lid=%d
											AND amount_value=%s
											AND status='pending'
											ORDER BY create_date DESC
											LIMIT 1
				", $user_id, $level_id, $level_data['access_trial_price']);
			$datas = $wpdb->get_results($q);
			if (!empty($datas) && $datas[0]->count > 0){
				$count = $level_data['access_trial_price'];
			}
		}
	return $count;

}

# dump and die
if (!function_exists('dd')):
function dd($variable){
		indeed_debug_var($variable);
		die;
}
endif;

if (!function_exists('ihcGetTransactionDetails')):
function ihcGetTransactionDetails($txnId='')
{
		global $wpdb;
		if (empty($txnId)){
				return false;
		}
		$data = $wpdb->get_row($wpdb->prepare("SELECT payment_data, orders, u_id FROM {$wpdb->prefix}indeed_members_payments WHERE txn_id=%s; ", $txnId));
		if (empty($data)){
				return false;
		}
		$paymentData = json_decode($data->payment_data, TRUE);
		if (!empty($paymentData['lid'])){
				$lid = $paymentData['lid'];
		} else if (!empty($paymentData['level'])){
				$lid = $paymentData['level'];
		}

		$array = array(
				'uid'								=> $data->u_id,
				'lid'								=> $lid,
				'amount'						=> $paymentData['amount'],
				'orders'						=> maybe_unserialize($data->orders),
				'ihc_payment_type'  => $paymentData['ihc_payment_type'],
		);

		return $array;
}
endif;

if (!function_exists('ihcActAsIpn')):
function ihcActAsIpn($uid=0, $lid=0, $transactionId='', $paymentData=array())
{
    $levelData = ihc_get_level_by_id($paymentData['lid']);//getting details about current level
		\Indeed\Ihc\UserSubscriptions::makeComplete( $paymentData['uid'], $paymentData['lid'] );
    $paymentData['message'] = 'success';
    $paymentData['status'] = 'Completed';
    ihc_insert_update_transaction($paymentData['uid'], $transactionId, $paymentData);
}
endif;

if (!function_exists('ihc_get_current_user')):
function ihc_get_current_user(){
	global $current_user;
	return isset($current_user->ID) ? $current_user->ID : 0;
}
endif;

if ( !function_exists( 'ihc_list_all_payments' ) ):
function ihc_list_all_payments()
{
		$paymentGateways = array(
								'stripe_connect'						=> 'Stripe',
								'paypal' 										=> 'PayPal Standard',
								'paypal_express_checkout'		=> 'PayPal Express Checkout',
								'stripe_checkout_v2'				=> 'Stripe Checkout',
							  'twocheckout' 							=> '2Checkout',
								'mollie'										=> 'Mollie',
							 	'bank_transfer' 						=> 'Bank Transfer',
								'pagseguro'									=> 'Pagseguro',
								'braintree' 								=> 'Braintree',
							  'authorize' 								=> 'Authorize',
		);
		$paymentGateways = apply_filters( 'ihc_payment_gateways_list', $paymentGateways );

		// @description List of payment gateways. @param list of payment gateways ( array )
		foreach ( $paymentGateways as $paymentSlug => $paymentLabel ){
			$label = get_option('ihc_' . $paymentSlug . '_label');

			if (!empty($label)){
				$paymentGateways[$paymentSlug] = $label;
			}
		}

		return $paymentGateways;

}
endif;

if (!function_exists('indeed_get_plugin_version')):
function indeed_get_plugin_version( $base_file_path='' ){
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugin_data = get_plugin_data( $base_file_path, false, false);
		return $plugin_data['Version'];
}
endif;

if ( !function_exists('indeed_is_plugin_active') ):
function indeed_is_plugin_active( $pluginBaseFile='' )
{
		if ( !$pluginBaseFile ){
				return false;
		}
		if (!function_exists('is_plugin_active')){
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (is_plugin_active($pluginBaseFile)){
				return true;
		}
		return false;
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

if ( !function_exists('ihc_order_like_register') ):
function ihc_order_like_register( $items=array() )
{
		$registerFields = ihc_get_user_reg_fields();
		ksort( $registerFields );
		foreach ( $registerFields as $registerField ){
				if ( in_array( $registerField['name'], $items ) ){
						$returnArray[] = $registerField['name'];
						unset( $items[ $registerField['name'] ] );
				}
		}
		if ( !empty( $items ) ){
				$returnArray = $returnArray + $items;
		}
		return $returnArray;
}
endif;

if ( !function_exists('ihc_level_time_left') ):
function ihc_level_time_left($expire_time)
{
    $cur_time   = indeed_get_unixtimestamp_with_timezone();
    $time_elapsed   = $expire_time - $cur_time;
    $seconds    = $time_elapsed ;
    $minutes    = round($time_elapsed / 60 );
    $hours      = round($time_elapsed / 3600);
    $days       = round($time_elapsed / 86400 );
    $weeks      = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640 );
    $years      = round($time_elapsed / 31207680 );
    // Seconds
    if($seconds <= 60){
        return "just now";
    }
    //Minutes
    else if($minutes <=60){
        if($minutes==1){
            return  esc_html__('1 minute left','ihc');
        }
        else{
            return "$minutes". esc_html__(' minutes left','ihc');
        }
    }
    //Hours
    else if($hours <=24){
        if($hours==1){
            return  esc_html__('1 hour left','ihc');
        }else{
            return "$hours". esc_html__(' hours left','ihc');
        }
    }
    //Days
    else if($days <= 7){
        if($days==1){
            return  esc_html__('1 day left','ihc');
        }else{
            return "$days". esc_html__(' days left','ihc');
        }
    }
    //Weeks
    else if($weeks <= 4.3){
        if($weeks==1){
            return  esc_html__('1 week left','ihc');
        }else{
            return "$weeks". esc_html__(' weeks left','ihc');
        }
    }
    //Months
    else if($months <=12){
        if($months==1){
            return  esc_html__('1 month left','ihc');
        }else{
            return "$months". esc_html__(' months left','ihc');
        }
    }
    //Years
    else{
        if($years==1){
            return  esc_html__('1 year left','ihc');
        }else{
            return "$years". esc_html__(' years left','ihc');
        }
    }
}
endif;

if ( !function_exists('ihc_prepare_level_show_format') ):
function ihc_prepare_level_show_format( $item=array() )
{

	$current_time = indeed_get_unixtimestamp_with_timezone();
	$format = get_option('date_format');
	$grace_period = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $item['level_id'] );
	if ($grace_period===FALSE || $grace_period == ''){
		$grace_period = 0;
	}
	$item = array_merge($item, array(
					'level_status' => '',
					'start_time_format' => date_i18n('M j'),
					'expire_time_format' => FALSE,
					'time_class' => '',
					'bar_width' => 0,
					'bar_class' => '',
					'tooltip_class' => '',
					'tooltip_message' => '',
					'extra_message' => ''
					)
			);
	$level_data = ihc_get_level_by_id($item['level_id']);

	$start_time = strtotime($item['start_time']);
	$expire_time = strtotime($item['expire_time']) + ((int)$grace_period * 24 * 60 *60);
	$expire_time_nograce = strtotime($item['expire_time']);
	$grace_period_status = FALSE;

	if(date('Y') != date('Y', $start_time)){
			$item ['start_time_format'] = date_i18n('M j, y', $start_time);
	}else{
			$item ['start_time_format'] = date_i18n('M j', $start_time);
		}
	if ($expire_time > 0){
		if ($current_time>$expire_time){
			$item ['level_status'] = 'expired';
		}
		else{
			$item ['level_status'] = 'active';

			$item ['tooltip_message'] = ihc_level_time_left($expire_time);
			$item ['bar_width'] = ($expire_time - $current_time)*100/($expire_time - $start_time);
			if($item ['bar_width'] > 100){
				$item ['bar_width'] = 100;
			}
			//check if is in grace period
			if($current_time > $expire_time_nograce){
				$grace_period_status = TRUE;
			}
		}
		if(date('Y') != date('Y', $expire_time)){
			$item ['expire_time_format'] = date_i18n('M j, y', $expire_time);
		}else{
			$item ['expire_time_format'] = date_i18n('M j', $expire_time);
		}
	}else{
		$item ['level_status'] = 'hold';
	}
	switch($item ['level_status']){
		case 'active':
			 if(isset($level_data['access_type']) && $level_data['access_type'] == 'unlimited'){
			 	$item ['bar_width'] = '100';
				$item ['time_class'] = 'ihc-level-skin-hide';
			 	$item ['tooltip_message'] = esc_html__('LifeTime','ihc');
			}elseif(isset($grace_period) && $grace_period > 0){
 			 $item ['extra_message'] = esc_html__('Subscription has ','ihc').$grace_period. esc_html__(' days Grace Period included','ihc');;
			}
			 if($grace_period_status === TRUE){
			 $item ['bar_class'] = 'ihc-level-skin-bar-grace-period';
			 $item ['extra_message'] = esc_html__('Subscription is on Grace Period','ihc');
			 }
			 if($item ['bar_width'] < 10){
				 if($grace_period_status === TRUE){
					 $item ['bar_class'] = 'ihc-level-skin-bar-grace-period-expiresoon';
					 $item ['extra_message'] = esc_html__('Subscription is on Grace Period and will expire soon','ihc');
				 }else{
						$item ['bar_class'] = 'ihc-level-skin-bar-expiresoon';
						$item ['extra_message'] = esc_html__('Subscription will expire soon','ihc');
				 }
			 }
			 break;
		case 'expired':
			$item ['bar_width'] = '100';
			$item ['bar_class'] = 'ihc-level-skin-bar-expired';
			$item ['tooltip_class'] = 'ihc-level-skin-single-expired';
			$item ['tooltip_message'] = esc_html__('Expired','ihc');
			$item ['extra_message'] = esc_html__('Subscription period has expired','ihc');
			break;
		case 'hold':
			$item ['bar_width'] = '100';
			$item ['bar_class'] = 'ihc-level-skin-bar-hold';
			$item ['tooltip_class'] = 'ihc-level-skin-single-hold';
			$item ['tooltip_message'] = esc_html__('On hold','ihc');
			$item ['time_class'] = 'ihc-level-skin-hide';
			$item ['extra_message'] = esc_html__('No payment confirmation received','ihc');
			break;
	}

   if($item ['bar_width'] == 100){
	  $item ['bar_class'] .= ' ihc-level-skin-bar-full';
   }


	return $item;
}
endif;

if ( !function_exists('ihc_return_individual_page_link') ):
function ihc_return_individual_page_link($user_id = 0){
	 $output = '';
	 if ($user_id != 0){
	 	 $individual_page = get_user_meta($user_id, 'ihc_individual_page', TRUE);
		 if ($individual_page){
		 	 $permalink = get_permalink($individual_page);
			 if ($permalink){
			 	$output = $permalink;
			 }
		 }
	 }
	 return $output;
}
endif;

if ( !function_exists('ihcIsRegisterPage') ):
function ihcIsRegisterPage( $url )
{
		$registerPage = get_option('ihc_general_register_default_page');
		if ( !$registerPage || $registerPage==-1 ){
				return false;
		}
		$permalink = get_permalink($registerPage);
		if ( strpos( $url, $permalink) !== false ){
				return true;
		}
		return false;
}
endif;

if (!function_exists('indeed_get_uid')):
function indeed_get_uid(){
		global $current_user;
		if (isset($current_user->ID) && $current_user->ID > 0 ){
				return $current_user->ID;
		}
		return 0;
}
endif;

if ( !function_exists( 'ihcGetListOfMagicFeatures' ) ):
function ihcGetListOfMagicFeatures()
{
	$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
	$ol_dL_ogs = new $class();
	$list = array(

									'prorate_subscription'		=> [
														'label' => esc_html__('Prorating Subscriptions', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true ) ? admin_url('admin.php?page=ihc_manage&tab=prorate_subscription') : '',
														'icon' => 'fa-prorate_subscription_settings-ihc',
														'extra_class' => 'ihc-module-pro ihc-module-pro-color',
														'pro' => TRUE,
														'description' => esc_html__('Changes to a Subscription such as upgrading or downgrading with prorated amounts', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('prorate_subscription'),
									],

									'taxes' => array(
														'label' => esc_html__('Taxes', 'ihc'),
														'description' => esc_html__('Add additional tax charges which can be based on the user location by using the Country field', 'ihc'),
														'icon' => 'fa-taxes-ihc',
														'extra_class' => '',
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true ) ? admin_url('admin.php?page=ihc_manage&tab=taxes') : '',
														'enabled' => ihc_is_magic_feat_active('taxes'),
									),
									'opt_in' => array(
														'label' => esc_html__('Opt-In Integrations', 'ihc'),
														'description' => esc_html__('Store your subscribers email address in a well known email marketing platform', 'ihc'),
														'icon' => 'fa-opt_in-ihc',
														'extra_class' => '',
														'link' => admin_url('admin.php?page=ihc_manage&tab=opt_in'),
														'enabled' => TRUE,
									),
									'woo_payment' => array(
														'label' => esc_html__('WooCommerce Payment Integration', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true ) ? admin_url('admin.php?page=ihc_manage&tab=woo_payment') : '',
														'icon' => 'fa-woo-ihc',
														'extra_class' => 'ihc-module-pro iump-woo-payment-special-color',
														'description' => esc_html__('Assign Ultimate Membership Pro with WooCommerce products and sell memberships as products', 'ihc'),
														'pro' => TRUE,
														'enabled' => ihc_is_magic_feat_active('woo_payment'),
									),
									'gifts' => array(
														'label' => esc_html__('Membership Gifts', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true ) ? admin_url('admin.php?page=ihc_manage&tab=gifts') : '',
														'icon' => 'fa-gifts-ihc',
														'extra_class' => '',
														'description' => esc_html__('Give your customers the option of purchasing Memberships as gifts', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('gifts'),
									),
									'badges' => array(
														'label' => esc_html__('Membership Badges', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true ) ? admin_url('admin.php?page=ihc_manage&tab=badges') : '',
														'icon' => 'fa-badges-ihc',
														'extra_class' => '',
														'description' => esc_html__('Add a custom badge for each Membership for a better approach', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('badges'),
									),
									'individual_page' => array(
														'label' => esc_html__('Individual Page', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true ) ? admin_url('admin.php?page=ihc_manage&tab=individual_page') : '',
														'icon' => 'fa-individual_page-ihc',
														'extra_class' => '',
														'description' => esc_html__('Each Member will have an individual page accessible only by him and Administrator', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('individual_page'),
									),
									'reason_for_cancel'			=> array(
														'label'						=> esc_html__('Reason for Cancelling', 'ihc'),
														'link' 						=> ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=reason_for_cancel') : '',
														'icon'						=> 'fa-reason_for_cancel-ihc',
														'extra_class' 		=> '',
														'description'			=> esc_html__('Track the reasons why Members wish to cancel/delete their Subscriptions', 'ihc'),
														'enabled'					=> ihc_is_magic_feat_active('reason_for_cancel'),
									),
									'woo_product_custom_prices' => array(
														'label' => esc_html__('WooCommerce Products Discount', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices') : '',
														'icon' => 'fa-woo-ihc',
														'extra_class' => 'iump-woo-discounts-special-color',
														'description' => esc_html__('Set price discounts for WooCommerce products based on memberships', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('woo_product_custom_prices'),
									),
									'level_dynamic_price' => array(
														'label' => esc_html__('Membership Dynamic Price', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=level_dynamic_price') : '',
														'icon' => 'fa-level_dynamic_price-ihc',
														'extra_class' => '',
														'description' => esc_html__('Mimic Donations by letting the client decide how much to pay for Memberships', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('level_dynamic_price'),
									),
									'level_restrict_payment' => array(
														'label' => esc_html__('Memberships vs Payments', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=level_restrict_payment') : '',
														'icon' => 'fa-level_restrict_payment-ihc',
														'extra_class' => '',
														'description' => esc_html__('Restrict each Membership to be paid only through a specific Payment Service', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('level_restrict_payment'),
									),
									'level_subscription_plan_settings' => array(
														'label' => esc_html__('Memberships Plus', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=level_subscription_plan_settings') : '',
														'icon' => 'fa-level_subscription_paln_settings-ihc',
														'extra_class' => '',
														'description' => esc_html__('Decide which Memberships should be available, based on the user current assigned Membership', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('level_subscription_plan_settings'),
									),
									'user_sites' => array(
														'label' => esc_html__('WP MultiSite Subscriptions', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=user_sites') : '',
														'icon' => 'fa-user_sites-ihc',
														'extra_class' => '',
														'description' => esc_html__('Provides Single Sites based on purchased subscriptions. You can sell Single Sites via memberships.', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('user_sites'),
									),
									'drip_content_notifications' => array(
														'label' => esc_html__('Drip Content Notifications', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=drip_content_notifications') : '',
														'icon' => 'fa-drip_content_notifications-ihc',
														'extra_class' => 'iump-dripcontentnotifications-special-color',
														'description' => esc_html__('Alert Members when a new post is released by “Drip Content” strategy.', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('drip_content_notifications'),
									),
									'membership_card' => array(
														'label' => esc_html__('Membership Card', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=membership_card') : '',
														'icon' => 'fa-membership_card-ihc',
														'extra_class' => '',
														'description' => esc_html__('Members will find their Membership Cards into Member Portal page and may Print them for further usage', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('membership_card'),
									),
									'cheat_off' => array(
														'label' => esc_html__('Cheat Off', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=cheat_off') : '',
														'icon' => 'fa-cheat_off-ihc',
														'extra_class' => '',
														'description' => esc_html__('Prevent your customers from sharing their login credentials by keeping only one user logged in at a time', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('cheat_off'),
									),
									'invitation_code' => array(
														'label' => esc_html__('Invitation Codes', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=invitation_code') : '',
														'icon' => 'fa-invitation_code-ihc',
														'extra_class' => '',
														'description' => esc_html__(' Restrict register process to only allow invited persons who have a valid Invitation code', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('invitation_code'),
									),
									'import_users' => array(
														'label' => esc_html__('Import Users&Memberships', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=import_users') : '',
														'icon' => 'fa-import_users-ihc',
														'extra_class' => '',
														'description' => esc_html__('Allows to import new Members, update current Members main data or to assign/change Members Memberships', 'ihc'),
														'enabled' => TRUE,
									),
									'login_level_redirect' => array(
														'label' => esc_html__('Login Redirects+', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=login_level_redirect') : '',
														'icon' => 'fa-sign-in-ihc',
														'extra_class' => '',
														'description' => esc_html__('Replace the default redirect after login with a custom one based on the member assigned membership', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('login_level_redirect'),
									),
									'wp_social_login' => array(
														'label' => esc_html__('Wp Social Login Integration', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=wp_social_login') : '',
														'icon' => 'fa-wp_social_login-ihc',
														'extra_class' => '',
														'description' => esc_html__('Integrated with WP Social Login free Plugin for a lite register/login with social accounts', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('wp_social_login'),
									),
									'list_access_posts' => array(
														'label' => esc_html__('List Access Posts', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=list_access_posts') : '',
														'icon' => 'fa-list_access_posts-ihc',
														'extra_class' => '',
														'description' => esc_html__('Display all the posts that a user can see based on his subscriptions', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('list_access_posts'),
									),
									'invoices' => array(
														'label' => esc_html__('Invoice', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=invoices') : '',
														'icon' => 'fa-invoices-ihc',
														'extra_class' => '',
														'description' => esc_html__('Provides printable invoices for each order in the account page or system dashboard', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('invoices'),
									),
									'custom_currencies' => array(
														'label' => esc_html__('Custom Currencies', 'ihc'),
														'description' => esc_html__('Add new currencies (with custom symbols) alongside the predefined list', 'ihc'),
														'icon' => 'fa-currencies-ihc',
														'extra_class' => '',
														'link' => admin_url('admin.php?page=ihc_manage&tab=custom_currencies'),
														'enabled' => TRUE,
									),
									'login_security' => array(
														'label' => esc_html__('Security Login', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=login_security') : '',
														'icon' => 'fa-login_security-ihc',
														'extra_class' => '',
														'description' => esc_html__('Fight against brute-force attacks by blocking login for the IP after it reaches the maximum allowed retries', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('login_security'),
									),
									'workflow_restrictions' => array(
														'label' => esc_html__('WP Workflow Restrictions', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=workflow_restrictions') : '',
														'icon' => 'fa-workflow_restrictions-ihc',
														'extra_class' => '',
														'description' => esc_html__('You can restrict how many posts can be viewed, released and how many comments can be submitted for each Membership', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('workflow_restrictions'),
									),
									'subscription_delay' => array(
														'label' => esc_html__('Subscription Delay', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=subscription_delay') : '',
														'icon' => 'fa-subscription_delay-ihc',
														'extra_class' => '',
														'description' => esc_html__('Each Membership will become active after a custom delay time instead of when it was assigned', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('subscription_delay'),
									),
									'user_reports' => array(
														'label' => esc_html__('Members Reports', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=user_reports') : '',
														'icon' => 'fa-user_reports-ihc',
														'extra_class' => '',
														'description' => esc_html__('Follow the most important actions and activities done by members such Membership Assignation, Payments Placed, etc', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('user_reports'),
									),
									'pushover' => array(
														'label' => esc_html__('Pushover Notifications', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=pushover') : '',
														'icon' => 'fa-pushover-ihc',
														'extra_class' => '',
														'description' => esc_html__('Members may receives notifications on their Mobile Devices via Pushover App', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('pushover'),
									),
									'weekly_summary_email'			=> array(
														'label'						=> esc_html__('Weekly Summary Email', 'ihc'),
														'link' 						=> ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=weekly_summary_email') : '',
														'icon'						=> 'fa-weekly_summary_email-ihc',
														'extra_class' 		=> '',
														'description'			=> esc_html__('A brief report on a one-week period will be sent to Administrator about number of Payments, Total Revenue and not only', 'ihc'),
														'enabled'					=> ihc_is_magic_feat_active('weekly_summary_email'),
									),
									'mycred' => array(
														'label' => esc_html__('MyCred Points', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=mycred') : '',
														'icon' => 'fa-mycred-ihc',
														'extra_class' => '',
														'description' => esc_html__('Reward with MyCred points when a Membership is purchased', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('mycred'),
									),
									'zapier'	=> array(
														'label'						=> esc_html__('Zapier Integration', 'ihc'),
														'link' 						=> ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=zapier') : '',
														'icon'						=> 'fa-zapier-ihc',
														'extra_class' 		=> '',
														'description'			=> esc_html__('Connect Ultimate Membership Pro with other apps via Zapier platform with multiple triggers available', 'ihc'),
														'enabled'					=> ihc_is_magic_feat_active('zapier'),
									),
									'redirect_links' => array(
														'label' => esc_html__('Redirect Links', 'ihc'),
														'description' => esc_html__('Set custom links from inside or outside of your website that can be used for redirects inside the system', 'ihc'),
														'icon' => 'fa-links-ihc',
														'extra_class' => '',
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=redirect_links') : '',
														'enabled' => TRUE,
									),
									'register_lite' => array(
														'label' => esc_html__('Register Lite', 'ihc'),
														'link' => admin_url('admin.php?page=ihc_manage&tab=register_lite'),
														'icon' => 'fa-register_lite-ihc',
														'extra_class' => '',
														'description' => esc_html__('Let your Members register by using only their Email Address. A Generated Password for their account will be sent out via Email', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('register_lite'),
									),
									'account_page_menu' => array(
														'label' => esc_html__('Member Potal Tabs', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=account_page_menu') : '',
														'icon' => 'fa-account_page_menu-ihc',
														'extra_class' => '',
														'description' => esc_html__('Customize members\' account page menu items based on their choices and needs', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('account_page_menu'),
									),
									'api' => array(
														'label' => esc_html__('API Gate', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=api') : '',
														'icon' => 'fa-api-ihc',
														'extra_class' => '',
														'description' => esc_html__('Manage your membership system and access data from it through an API with access based on URL calls', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('api'),
									),
									'register_redirects_by_level' => array(
														'label' => esc_html__('Register Redirects+', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=register_redirects_by_level') : '',
														'icon' => 'fa-register_redirects_by_level-ihc',
														'extra_class' => '',
														'description' => esc_html__('Choose a custom redirect after register based on the member assigned Membership', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('register_redirects_by_level'),
									),
									'infusionSoft'	=> array(
														'label'						=> esc_html__('Infusion Soft', 'ihc'),
														'link' 						=> ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=infusionSoft') : '',
														'icon'						=> 'fa-infusionSoft-ihc',
														'extra_class' 		=> '',
														'description'			=> esc_html__('Synchronize your InfusionSoft contacts based on Tags. For each user status or Membership a Tag is associated', 'ihc'),
														'enabled'					=> ihc_is_magic_feat_active('infusionSoft'),
									),
									'kissmetrics'		=> array(
														'label'						=> esc_html__('Kissmetrics Integration', 'ihc'),
														'link' 						=> ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=kissmetrics') : '',
														'icon'						=> 'fa-kissmetrics-ihc',
														'extra_class' 		=> '',
														'description'			=> esc_html__('Track multiple Membership events and Member actions with Kissmetrics service', 'ihc'),
														'enabled'					=> ihc_is_magic_feat_active('kissmetrics'),
									),
									'direct_login'		=> array(
														'label'						=> esc_html__('Direct Login', 'ihc'),
														'link' 						=> ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=direct_login') : '',
														'icon'						=> 'fa-direct_login-ihc',
														'extra_class' 		=> '',
														'description'			=> esc_html__('Members can login without standard credentials but with a special temporary link available', 'ihc'),
														'enabled'					=> ihc_is_magic_feat_active('direct_login'),
									),
									'download_monitor_integration' => array(
														'label' => esc_html__('Download Monitor Integration', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=download_monitor_integration') : '',
														'icon' => 'fa-download_monitor_integration-ihc',
														'extra_class' => '',
														'description' => esc_html__('For each membership, limit the number of downloads per file or per user', 'ihc'),
														'enabled' => ihc_is_magic_feat_active('download_monitor_integration'),
									),
									'bp_account_page' => array(
														'label' => esc_html__('BuddyPress Account Page Integration', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=bp_account_page') : '',
														'icon' => 'fa-bp-ihc',
														'extra_class' => '',
														'description' => esc_html__('Integrate Ultimate Membership Pro in BuddyPress Account page',''),
														'enabled' => ihc_is_magic_feat_active('bp_account_page'),
									),
									'woo_account_page' => array(
														'label' => esc_html__('WooCommerce Account Page Integration', 'ihc'),
														'link' => ($ol_dL_ogs->FGCS() === '0' && $ol_dL_ogs->GCP() !== true) ? admin_url('admin.php?page=ihc_manage&tab=woo_account_page') : '',
														'icon' => 'fa-woo-ihc',
														'extra_class' => '',
														'description' => esc_html__('Integrate Ultimate Membership Pro with the WooCommerce customer account page','ihc'),
														'enabled' => ihc_is_magic_feat_active('woo_account_page'),
									),


	);
	$list = apply_filters( 'ihc_magic_feature_list', $list );
	// @description Magic feature list. @param list of magic features ( array )

	$list[ 'new_extension' ] = array(
                'label'						=> esc_html__( 'Add new Extensions', 'ihc' ),
                'link' 						=> 'https://ultimatemembershippro.com/pro-addons/',
                'icon'						=> 'fa-new-extension-ihc',
                'extra_class' 		=> 'ihc-new-extension-box',
                'description'			=> '',
                'enabled'					=> 1,
        );

	return $list;
}
endif;

if ( !function_exists( 'ihcNotificationConstants' ) ):
/**
 * @param string
 * @return string
 */
function ihcNotificationConstants( $type='' )
{
		$constants = array(
							'{username}'										=> '',
							'{user_id}'											=> '',
							'{user_email}'									=> '',
							'{first_name}'									=> '',
							'{last_name}'										=> '',
							'{account_page}'								=> '',
							'{login_page}'									=> '',
							'{current_level}'								=> '',
							'{current_level_expire_date}'		=> '',
							'{level_list}'									=> '',
							'{blogname}'										=> '',
							'{blogurl}'											=> '',
							'{currency}'										=> '',
							'{amount}'											=>'',
							'{level_name}'									=> '',
							'{current_date}' 								=> '',
		);
		// remove some constants
		switch ( $type ){
				case 'admin_user_register':
				case 'ihc_order_placed_notification-user':
				case 'ihc_new_subscription_assign_notification-admin':
				case 'register':
				case 'register_lite_send_pass_to_user':
				case 'ihc_new_subscription_assign_notification':
					unset( $constants['{current_level_expire_date}'] );
					break;
				case 'admin_user_payment':
				case 'payment':
				case 'bank_transfer':
				case 'expire':
				case 'ihc_order_placed_notification-admin':
				case 'ihc_cancel_subscription_notification-admin':
				case 'bank_transfer':
				case 'ihc_subscription_activated_notification':
				case 'ihc_delete_subscription_notification-user':
				case 'ihc_cancel_subscription_notification-user':

					break;
				case 'admin_before_user_expire_level':
				case 'admin_second_before_user_expire_level':
				case 'admin_third_before_user_expire_level':
				case 'admin_user_expire_level':
				case 'admin_user_profile_update':
				case 'before_expire':
				case 'second_before_expire':
				case 'third_before_expire':
				case 'user_update':
				case 'ihc_delete_subscription_notification-admin':
					unset( $constants['{amount}'] );
					unset( $constants['{currency}'] );
					break;
				case 'reset_password_process':
			  case 'reset_password':
				case 'email_check':
				case 'email_check_success':
				case 'change_password':
				case 'approve_account':
				case 'delete_account':
				case 'drip_content-user':
				case 'review_request':
				case 'register_lite_send_pass_to_user':
					unset( $constants['{amount}'] );
					unset( $constants['{currency}'] );
					unset( $constants['{current_level_expire_date}'] );
					unset( $constants['{level_list}'] );
					unset( $constants['{level_name}'] );
					unset( $constants['{current_level}'] );
					break;
		}
		// adding some
		switch ( $type ){
				case 'reset_password':
					$constants['{NEW_PASSWORD}'] = '';
					break;
				case 'reset_password_process':
					$constants['{password_reset_link}'] = '';
					break;
				case 'drip_content-user':
					$constants['{POST_LINK}'] = '';
					break;
				case 'email_check':
					$constants['{verify_email_address_link}'] = '';
					break;
		}
		$constants = apply_filters( 'ihc_filter_constants_for_notifications', $constants, $type );
		return $constants;
}
endif;

if ( !function_exists('indeed_get_unixtimestamp_with_timezone') ):
/**
 * Return unixtimestamp with the timezone set in Wp Admin dashboard.
 * @param int ( timestamp )
 * @return int
 */
function indeed_get_unixtimestamp_with_timezone( $time='' )
{
		if ( '' == $time ){
				$time = time();
		}
		$date = new DateTime();
		$date->setTimestamp( $time );
		$date->setTimezone( new DateTimeZone('UTC') );
		$time = $date->format('Y-m-d H:i:s');
		$time = get_date_from_gmt( $time );
		return strtotime( $time );
}
endif;

if ( !function_exists('indeed_get_current_time_with_timezone') ):
/**
 * Return date with the timezone set in Wp Admin dashboard.
 * @param int ( timestamp )
 * @return string
 */
function indeed_get_current_time_with_timezone( $time='' )
{
		if ( '' == $time ){
				$time = time();
		}
		$date = new DateTime();
		$date->setTimestamp( $time );
		$date->setTimezone( new DateTimeZone('UTC') );
		$time = $date->format('Y-m-d H:i:s');
		return get_date_from_gmt( $time, 'Y-m-d H:i:s' );
}
endif;

if ( !function_exists( 'indeed_timestamp_to_date_without_timezone' ) ):
/**
 * Convert a timestamp to 'Y-m-d H:i:s' format
 * @param int
 * @return string
 */
function indeed_timestamp_to_date_without_timezone( $timestamp='', $format='Y-m-d H:i:s' )
{
		if ( '' == $timestamp ){
				$timestamp = time();
		}
		$date = new DateTime();
		$date->setTimestamp( $timestamp );
		$date->setTimezone( new DateTimeZone('UTC') );
		return $date->format( $format );
}
endif;

if ( !function_exists( 'indeedObjectToArray' ) ):
function indeedObjectToArray( $object=null )
{
    if ( is_object( $object ) || is_array( $object ) ){
        $return = (array)$object;
        foreach ($return as &$item) {
            $item = indeedObjectToArray($item);
        }
        return $return;
    } else {
        return $object;
    }
}
endif;

if ( !function_exists( 'ihcIsAdmin' ) ):
function ihcIsAdmin()
{
		global $current_user;
		if ( empty( $current_user->ID ) ){
				return false;
		}
		if ( is_super_admin( $current_user->ID ) ){
				return true;
		}
		$userData = get_userdata( $current_user->ID );
		if ( !$userData || empty( $userData->roles ) ){
				return false;
		}
		$isAdmin = in_array( 'administrator', $userData->roles );
		$isAdmin = apply_filters( 'ihc_filter_admin_is_admin_check', $isAdmin, $userData->roles );
		if ( !$isAdmin ){
				return false;
		}
		return true;
}
endif;

if ( !function_exists( 'ihcAdminVerifyNonce' ) ):
function ihcAdminVerifyNonce()
{
		$nonce = isset( $_SERVER['HTTP_X_CSRF_UMP_ADMIN_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_UMP_ADMIN_TOKEN']	: '';
		if ( wp_verify_nonce( $nonce, 'umpAdminNonce' ) ) {
				return true;
		}
		return false;
}
endif;

if ( !function_exists( 'ihcPublicVerifyNonce' ) ):
function ihcPublicVerifyNonce()
{
		$nonce = isset( $_SERVER['HTTP_X_CSRF_UMP_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_UMP_TOKEN']	: '';
		if ( wp_verify_nonce( $nonce, 'umpPublicNonce' ) ) {
				return true;
		}
		return false;
}
endif;

if ( !function_exists( 'ihcStripeMultiplyForCurrency') ):
function ihcStripeMultiplyForCurrency( $currency='' )
{
		$zeroDecimal = [
											'BIF',
											'CLP',
											'DJF',
											'GNF',
											'JPY',
											'KMF',
											'KRW',
											'MGA',
											'PYG',
											'RWF',
											'UGX',
											'VND',
											'VUV',
											'XAF',
											'XOF',
											'XPF',
		];
		$currency = strtoupper( $currency );
		if ( in_array( $currency, $zeroDecimal ) ){
				return 1;
		}
		return 100;
}
endif;

if ( !function_exists( 'ihcGetDefaultCountry' ) ):
function ihcGetDefaultCountry()
{
		$country = get_option( 'ihc_default_country' );
		if ( !is_string( $country ) ){
				$locale = get_locale();
				if ( strpos( $locale, '_' ) !== false ){
						$localeData = explode( '_', $locale );
						$country = isset( $localeData[1] ) ? $localeData[1] : '';
				}
		}
		return apply_filters( 'ihc_filter_the_default_country', $country );
}
endif;

/**
 * @param array
 * @return array
 */
if ( !function_exists( 'indeedFilterVarArrayElements' ) ):
function indeedFilterVarArrayElements( $data=[] )
{
		if ( !is_array( $data ) || count( $data ) == 0 ){
				return $data;
		}
		foreach ( $data as $key => $value ){
				$data[$key] = sanitize_textarea_field( $value );//filter_var( $value, FILTER_SANITIZE_STRING );
		}
		return $data;
}
endif;

if ( !function_exists( 'ihc_payment_workflow' ) ):
/**
 * @param none
 * @return string
 */
function ihc_payment_workflow()
{
		$paymentWorkflow = get_option( 'ihc_payment_workflow' );
		if ( $paymentWorkflow == '' || $paymentWorkflow === false ){
				$paymentWorkflow = 'new';
		}
		//Starting from v.9.9 only new payment integration will be used.
		$paymentWorkflow = 'new';

		$paymentWorkflow = apply_filters( 'ihc_filter_payment_workflow', $paymentWorkflow );
		return $paymentWorkflow;
}
endif;

if ( !function_exists( 'ihc_print_array_in_depth') ):
function ihc_print_array_in_depth( $array=[] )
{
	foreach ( $array as $key => $value ){
			if ( is_array( $value ) ){
					ihc_print_array_in_depth( $value );
			} else {
					echo esc_html($key) . ': ' . esc_html($value) . '<br/>';
			}
	}
}
endif;

/**
 *  DEPRACATED
 */
function ihc_do_complete_level_assign_from_ap($uid=0, $lid=0, $start_time=0, $end_time=0)
{
	\Indeed\Ihc\UserSubscriptions::assign( $uid, $lid );
	$succees = \Indeed\Ihc\UserSubscriptions::makeComplete( $uid, $lid );
	if ($succees){
		return TRUE;
	}
	return FALSE;
}

if ( !function_exists( 'ihcSanitizeValue' ) ):
function ihcSanitizeValue( $value=null, $type='' )
{
		switch ( $type ){
				case 'email':
					$value = sanitize_email( $value );
					break;
				case 'textarea':
					$value = sanitize_textarea_field( $value );
					break;
				case 'text':
				default:
					if ( is_array( $value )){
							foreach ( $value as $val ){
									$val = sanitize_text_field( $val );
							}
					} else {
							$value = sanitize_text_field( $value );
					}
					break;
		}
		return $value;
}
endif;

if ( !function_exists( 'ihcPaymentPlanDetailsAdmin') ):
function ihcPaymentPlanDetailsAdmin( $uid=0, $lid=0, $subscriptionId=0 )
{
		$data = [ 'uid' 								=> $uid,
							'lid' 								=> $lid,
							'subscriptionMetas' 	=> \Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscriptionId ),
						  'membershipData' 		  => \Indeed\Ihc\Db\Memberships::getOne( $lid ),
							'currency'						=> get_option( 'ihc_currency' ),
		];
		$view = new \Indeed\Ihc\IndeedView();
		return $view->setTemplate( IHC_PATH . 'admin/includes/tabs/payment-plan-details.php' )
							  ->setContentData( $data, true )
							  ->getOutput();
}
endif;

if ( !function_exists( 'ihcPaymentPlanDetailsPublic') ):
function ihcPaymentPlanDetailsPublic( $uid=0, $lid=0, $subscriptionId=0 )
{
	$data = [ 'uid' 								=> $uid,
						'lid' 								=> $lid,
						'subscriptionMetas' 	=> \Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscriptionId ),
						'membershipData' 		  => \Indeed\Ihc\Db\Memberships::getOne( $lid ),
						'currency'						=> get_option( 'ihc_currency' ),
	];
	$view = new \Indeed\Ihc\IndeedView();
	return $view->setTemplate( IHC_PATH . 'public/views/payment-plan-details.php' )
						  ->setContentData( $data, true )
						  ->getOutput();
}
endif;

if ( !function_exists( 'ihcGetTimeTypeByCode' ) ):
function ihcGetTimeTypeByCode( $timeType='D', $timeValue=0 )
{
	switch ( $timeType ){
			case 'D':
					if ( $timeValue > 1 ){
						 $timeType = esc_html__( ' days', 'ihc' );
					} else {
							$timeType = esc_html__( ' day', 'ihc' );
					}
				break;
			case 'W':
					if ( $timeValue > 1 ){
						 $timeType = esc_html__( ' weeks', 'ihc' );
					} else {
							$timeType = esc_html__( ' week', 'ihc' );
					}
				break;
			case 'M':
					if ( $timeValue > 1 ){
						 $timeType = esc_html__( ' months', 'ihc' );
					} else {
							$timeType = esc_html__( ' month', 'ihc' );
					}
				break;
			case 'Y':
					if ( $timeValue > 1 ){
						 $timeType = esc_html__( ' years', 'ihc' );
					} else {
						$timeType = esc_html__( ' year', 'ihc' );
					}
				break;
	}
	return $timeType;
}
endif;

if ( !function_exists( 'ihcGetValueFromTwoPossibleArrays' ) ):
function ihcGetValueFromTwoPossibleArrays( $arrayOne=[], $arrayTwo=[], $key='' )
{
		if ( isset( $arrayOne[$key] ) ){
				return $arrayOne[$key];
		} else if ( isset( $arrayTwo[$key] ) ){
				return $arrayTwo[$key];
		}
		return false;
}
endif;

if ( !function_exists( 'ihcAdminUserDetailsPage' ) ):
function ihcAdminUserDetailsPage( $uid=0 )
{
		if ( $uid === false || $uid == 0 ){
				return '';
		}
		return admin_url( 'admin.php?page=ihc_manage&tab=user-details&uid=' . $uid );
}
endif;

if ( !function_exists( 'ihcCheckCheckoutPage' ) ):
function ihcCheckCheckoutPage( )
{
	$value = get_option('ihc_checkout_page');
	$shortcode = '[ihc-checkout-page]';
	//if page does not exists
	if($value!=-1 && (!get_post_status($value) || get_post_status($value)=='trash') ){
		$value = -1;
	}
	if($value==FALSE || $value==-1){
			return FALSE;
	}else{
			$post = get_post_field('post_content', $value);
			if(strpos( $post,  $shortcode) === false) {
          return FALSE;
      }
		}
		return TRUE;
}
endif;

if ( !function_exists( 'ihcCheckCheckoutSetup' ) ):
function ihcCheckCheckoutSetup( )
{
	$checkPage = ihcCheckCheckoutPage();

	if ( $checkPage === FALSE ){
		// checkout page is not set
		return FALSE;
	}

	return TRUE;

}
endif;

if ( !function_exists( 'ihcCheckThankYouPage' ) ):
function ihcCheckThankYouPage( )
{
	$value = get_option('ihc_thank_you_page');
	$shortcode = '[ihc-thank-you-page]';
	//if page does not exists
	if($value!=-1 && (!get_post_status($value) || get_post_status($value)=='trash') ){
		$value = -1;
	}
	if($value==FALSE || $value==-1){
			return FALSE;
	}else{
			$post = get_post_field('post_content', $value);
			if(strpos( $post,  $shortcode) === false) {
          return FALSE;
      }
		}
		return TRUE;
}
endif;

if ( !function_exists( 'indeedCheckIfStripeLibIsAlreadyLoaded' ) ):
function indeedCheckIfStripeLibIsAlreadyLoaded()
{
    if ( !class_exists( '\Stripe\StripeClient' ) ){
        return false;
    }
    if ( !class_exists( '\ReflectionClass' ) ){
        return false;
    }
    $file = new \ReflectionClass( '\Stripe\StripeClient' );
    try {
        $baseNameFile = plugin_basename( $file->getFileName() );
        $baseName = dirname( $baseNameFile );
    } catch ( \Exception $e ){
        return false;
    }

    if ( strpos( $baseName, '/' )){
        $baseNameArr = explode( '/', $baseNameFile );
        if ( isset( $baseNameArr[0] ) ){
            $dir = $baseNameArr[0];
        }
    }
    if ( !isset( $dir ) ){
        return false;
    }
    $plugins = get_plugins();
    if ( !$plugins ){
        return false;
    }
    foreach ( $plugins as $pluginName => $pluginData ){
        if ( strpos( $pluginName, $dir ) !== false ){
            return $pluginData['Name'];
        }
    }
    return false;
}
endif;

/**
 * @param string
 * @return bool
 */
if ( !function_exists( 'ihcRegisterIsFieldRequired' ) ):
function ihcRegisterIsFieldRequired( $fieldName='' )
{
		if ( $fieldName === '' ){
				return false;
		}
		$registerFields = ihc_get_user_reg_fields();
		if ( !$registerFields ){
				return false;
		}
		$key = ihc_array_value_exists( $registerFields, $fieldName, 'name' );
		if ( $key === false ){
				return false;
		}
		if ( !isset( $registerFields[$key] ) ){
				return false;
		}
		if ( isset( $registerFields[$key]['req'] ) && (int)$registerFields[$key]['req'] === 1 ){
				return true;
		}
		return false;
}
endif;

if ( !function_exists( 'ihcSearchForDiviWithNotice' ) ):
/**
 * @param none
 * @return array
 */
function ihcSearchForDiviAndExtension()
{
    // search for divi theme
    if ( !class_exists( 'ET_Theme_Builder_Request' ) ){
        // divi is not installed
        return;
    }
    // search for our add-on
    $exists = file_exists( WP_CONTENT_DIR . '/plugins/ump-divi/ump-divi.php');
		$disabled = get_option( 'ihc_disable_divi_mk_message', false );
    if ( $exists === false && ( $disabled === false || (int)$disabled + ( 15 * 24 * 60 * 60 ) < time() ) ){
				$view = new \Indeed\Ihc\IndeedView();
				$html = $view->setTemplate( IHC_PATH . 'admin/includes/elements/divi-message.php' )
										 ->setContentData( [], true)
										 ->getOutput();
				return [
									'status'      => 1,
									'message'     => $html,
				];
    }
    return [
              'status'        => 0,
              'message'       => ''
    ];
}
endif;

if ( !function_exists( 'ihcSearchForElementorAndExtension' ) ):
function ihcSearchForElementorAndExtension()
{
		// search for elementor theme
		if ( !is_plugin_active( 'elementor/elementor.php' ) ){
				// elementor is not installed
				return;
		}
		// search for our add-on
		$exists = file_exists( WP_CONTENT_DIR . '/plugins/ump-elementor-widget-lock/ump-elementor-widget-lock.php' );
		$disabled = get_option( 'ihc_disable_elementor_mk_message', false );
		if ( $exists === false && ( $disabled === false || (int)$disabled + ( 15 * 24 * 60 * 60 ) < time() ) ){
				$view = new \Indeed\Ihc\IndeedView();
				$html = $view->setTemplate( IHC_PATH . 'admin/includes/elements/elementor-message.php' )
										 ->setContentData( [], true )
										 ->getOutput();
				return [
									'status'      => 1,
									'message'     => $html,
				];
		}
		return [
							'status'        => 0,
							'message'       => ''
		];
}
endif;

if ( !function_exists( 'ihcWriteGeneralLog' ) ):
function ihcWriteGeneralLog( $message='', $append=false )
{
		if ( $append ){
				file_put_contents( IHC_PATH . 'log.log', $message, FILE_APPEND );
		} else {
				file_put_contents( IHC_PATH . 'log.log', $message );
		}
}
endif;

if ( !function_exists( 'ihc_user_has_level' ) ):
	/**
	 * @param int
	 * @param int
	 * @return bool
	 */
function ihc_user_has_level($u_id, $l_id)
{
  $hasLevel = \Indeed\Ihc\UserSubscriptions::userHasSubscription( $u_id, $l_id );
	if ( !$hasLevel ){
			return false;
	}
	$isActive = \Indeed\Ihc\UserSubscriptions::isActive( $u_id, $l_id );
	if ( $isActive ){
			return true;
	}
	return false;
}
endif;

if ( !function_exists('ihcLoginLink') ):
function ihcLoginLink()
{
		$loginPage = '';
		$login = get_option('ihc_general_login_default_page');
		if ( $login !== false ){
				$loginPage = get_permalink( $login );
		}
		if ( !$loginPage ){
			 	$loginPage = get_home_url();
		}
		return $loginPage;
}
endif;

if ( !function_exists( 'indeed_sanitize_array' ) ):
function indeed_sanitize_array( $input=[] )
{
    $output = [];
    if ( !is_array( $input ) ){
        return sanitize_text_field($input);
    }
    foreach ( $input as $key => $val ) {
        if ( is_array( $val ) ){
            $output[ $key ] = indeed_sanitize_array( $val );
        } else {
            $output[ $key ] = ( isset( $input[ $key ] ) ) ? sanitize_text_field( $val ) : false;
        }
    }
    return $output;
}
endif;

if ( !function_exists( 'indeed_sanitize_textarea_array' ) ):
function indeed_sanitize_textarea_array( $input=[] )
{
    $output = [];
    if ( !is_array( $input ) ){
        return wp_kses_post($input);
    }
    foreach ( $input as $key => $val ) {
        if ( is_array( $val ) ){
            $output[ $key ] = indeed_sanitize_textarea_array( $val );
        } else {
            $output[ $key ] = ( isset( $input[ $key ] ) ) ? wp_kses_post( $val ) : false;
        }
    }
    return $output;
}
endif;

if ( !function_exists('esc_ump_content') ):
/*
 * This function is used to filter the html output.
 */
function esc_ump_content( $string='' )
{
    return $string;
}
endif;

if ( !function_exists( 'ihcDefaultBannerImage' ) ):
function ihcDefaultBannerImage()
{
    $image = get_option( 'ihc_ap_top_background_image' );
    if ( $image === false || $image === '' ){
        $image = IHC_URL . 'assets/images/top_ump_bk_4.png'; /// default
    }
    return $image;
}
endif;

if ( !function_exists( 'ihcRedirectToCheckout' ) ):
function ihcRedirectToCheckout( $args=[] )
{
		$checkoutPageId = get_option( 'ihc_checkout_page' );
		if ( $checkoutPageId === '' || $checkoutPageId === false || (int)$checkoutPageId === -1 ){
				return;
		}
		$checkoutPageURL = get_permalink( $checkoutPageId );
		if ( $checkoutPageURL === '' || $checkoutPageURL === false ){
				return;
		}
		if ( $args ){
				$checkoutPageURL = add_query_arg( $args, $checkoutPageURL );
		}
		wp_safe_redirect( $checkoutPageURL );
		exit;
}
endif;

if ( !function_exists( 'iumpEscapeArrayForQuery' ) ):
function iumpEscapeArrayForQuery( $array=[] ){
    global $wpdb;
    $prepered = [];
    foreach ( $array as $key => $value ){
        if ( is_numeric( $value ) ){
            	$prepered[] = $wpdb->prepare( '%d', $value );
        } else {
							$prepered[] = $wpdb->prepare( '%s', $value );
				}
    }
    return implode(',', $prepered );
}
endif;

if ( !function_exists( 'indeed_sanitize_array_with_html' ) ):
function indeed_sanitize_array_with_html( $input=[] )
{
    $output = [];
    if ( !is_array( $input ) ){
        return wp_kses_post( $input );
    }
    foreach ( $input as $key => $val ) {
        if ( is_array( $val ) ){
            $output[ $key ] = indeed_sanitize_array_with_html( $val );
        } else {
            $output[ $key ] = ( isset( $input[ $key ] ) ) ? wp_kses_post( $val ) : false;
        }
    }
    return $output;
}
endif;

if ( !function_exists( 'iumpGetLocaleList' ) ):
function iumpGetLocaleList()
{
		return [
						'en_US' => 'English - US',
						'ar_EG' => 'Arabic - Egipt',
						'fr_XC' => 'France - Algeria',
						'en_AU' => 'English - Australia',
						'de_DE' => 'German - Germany',
						'nl_NL' => 'Dutch - Netherlands',
						'fr_FR' => 'French - France',
						'pt_BR' => 'Portuguese - Brazil',
						'fr_CA' => 'French - Canada',
						'zh_CN' => 'Chinese - China',
						'da_DK' => 'Danish - Denmark',
						'ru_RU' => 'Russian - Russia',
						'en_GB' => 'English - Grand Britain',
						'id_ID' => 'Indonesian - Indonesia',
						'he_IL' => 'Hebrew - Israel',
						'it_IT' => 'Italian - Italy',
						'ja_JP' => 'Japanese - Japan',
						'no_NO' => 'Norwegian - Norway',
						'pl_PL' => 'Polish - Poland',
						'pt_PT' => 'Portuguese - Portugal',
						'sv_SE' => 'Swedish - Sweden',
						'zh_TW' => 'Chinese - Taiwan',
						'th_TH' => 'Thai - Thailand',
						'es_ES' => 'Spanish - Spain',
		];
}
endif;

if ( !function_exists( 'iumpRestrictedLocalesForStripeConnect' ) ):
function iumpRestrictedLocalesForStripeConnect()
{
		return [
							'bn_IN',
							'pa_IN',
							'pt_BR',
							'es_MX',
							'ms_MY',
		];
}
endif;

if ( !function_exists( 'iump_is_wizard_uncompleted_but_not_skiped' ) ):
function iump_is_wizard_uncompleted_but_not_skiped( $global=false )
{
		if ( (int)get_option( 'iump_wizard_complete', 0 ) === -2 ){
				if ( $global ){
					 $class = 'error ihc-license-warning';
				} else{
					 $class = 'ihc-error-global-dashboard-message';
				}
				$responseMessage = "<div class='" . $class . "'>";
				$responseMessage .= "<p>";
				$responseMessage .= "<strong>" . esc_html( 'Warning:', 'ihc')  ."</strong>" . esc_html__( ' It appears that the ', 'ihc') . '<a href="' . esc_url( admin_url( 'admin.php?page=ihc_manage&tab=wizard' ) ) . '"> ' . esc_html__( 'Wizard Setup',  'ihc' ) . '</a>' . esc_html__( ' was not completed. Please complete the ', 'ihc') . '<a href="' . esc_url( admin_url( 'admin.php?page=ihc_manage&tab=wizard' ) ) .'">' . esc_html__('setup', 'ihc') . '</a>' . esc_html__(' to ensure proper functionality.', 'ihc' );
				$responseMessage .= "</p></div>";
				return $responseMessage;
		}
		return '';
}
endif;

if ( !function_exists( 'umpSiteDomain' ) ):
function umpSiteDomain()
{
		$domain = get_option( 'siteurl' );
		$domain = str_replace('http://', '', $domain);
		$domain = str_replace('https://', '', $domain);
		$domain = str_replace('www.', '', $domain);
		return $domain;
}
endif;

if ( !function_exists( 'iumpExtractDateFormat' ) ):
function iumpExtractDateFormat( $time='' )
{
	$patterns = [
		'/\b\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\b/'    => 'Y-m-d h:i:s',
		'/\b\d{4}-(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])\b/'    => 'Y-d-m h:i:s',
		'/\b(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-\d{4}\b/'    => 'd-m-Y h:i:s',
		'/\b(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])-\d{4}\b/'    => 'm-d-Y h:i:s',
		'/\b\d{4}\/(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\b/'  => 'Y/d/m h:i:s',
		'/\b\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\b/'  => 'Y/m/d h:i:s',
		'/\b(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/\d{4}\b/'  => 'd/m/Y h:i:s',
		'/\b(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/\d{4}\b/'  => 'm/d/Y h:i:s',
		'/\b\d{4}\.(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])\b/'  => 'Y.m.d h:i:s',
		'/\b\d{4}\.(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\b/'  => 'Y.d.m h:i:s',
		'/\b(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.\d{4}\b/'  => 'd.m.Y h:i:s',
		'/\b(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])\.\d{4}\b/'  => 'm.d.Y h:i:s',
		'/\b[0-9]\/[1-3][1-9]\/\d{4}\b/'  													=> 'm/d/Y h:i',
	];
	foreach ( $patterns as $pattern => $format ){
			if ( preg_match( $pattern, $time ) === 1 ){
					return $format;
			}
	}
	return '';
}
endif;
