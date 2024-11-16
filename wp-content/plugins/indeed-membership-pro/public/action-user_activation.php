<?php
if (empty($no_load)){
	require_once '../../../wp-load.php';
}

if (!empty($_GET['uid']) && !empty($_GET['ihc_code'])){
	require_once IHC_PATH . 'utilities.php';
	$time_before_expire = get_option('ihc_double_email_expire_time');
	$user_data = get_userdata(sanitize_text_field($_GET['uid']));
	$error = FALSE;

	//checking expire time if it's case
	if (!empty($user_data)){
		if ($time_before_expire!=-1){
			$expire_time = strtotime($user_data->data->user_registered) + floatval($time_before_expire);
		}
	} else {
		$error = TRUE;
	}
	if (!$error && $time_before_expire!=-1){
		$current_time = current_time( 'timestamp' );
		if ($current_time>$expire_time){
			$error = TRUE;
		}
	}

	//activate if it's case
	if (!$error){
		$hash = get_user_meta( sanitize_text_field($_GET['uid']), 'ihc_activation_code', TRUE);
		if ($_GET['ihc_code']==$hash){
			//success
			if ( is_multisite() ){
					delete_user_option(sanitize_text_field($_GET['uid']), 'ihc_activation_code');//remove code
			}
			delete_user_meta( sanitize_text_field($_GET['uid']), 'ihc_activation_code');//remove code
			update_user_meta( sanitize_text_field($_GET['uid']), 'ihc_verification_status', 1);
			/// user log
			Ihc_User_Logs::set_user_id( sanitize_text_field($_GET['uid']) );
			$username = Ihc_Db::get_username_by_wpuid( sanitize_text_field($_GET['uid']) );
			Ihc_User_Logs::write_log(esc_html__('E-mail address has become active for ', 'ihc') . $username, 'user_logs');
			//opt in
			if (!empty($user_data->data->user_email)){

				$doOptIn = get_option('ihc_register_opt-in');
				$registerField = ihc_get_user_reg_fields();
				if ( $registerField ){
						foreach ( $registerField as $registerArray ){
								if ( $registerArray['name'] == 'ihc_optin_accept' && $registerArray['display_public_reg'] == 1 ){
										// check for user meta ihc_optin_accept
										$doOptIn = get_user_meta( sanitize_text_field( $_GET['uid'] ), 'ihc_optin_accept', true );
								}
						}
				}
				if ( $doOptIn ){
						ihc_run_opt_in($user_data->data->user_email);
				}
			}
			do_action( 'ihc_action_user_activation_email_check_success', sanitize_text_field( $_GET['uid'] ) );
		} else {
			$error = TRUE;
		}
	}

	//redirect
	if ($error){
		//error redirect
		$redirect = get_option('ihc_double_email_redirect_error');
		do_action('ihc_double_email_verification_fail', (isset($_GET['uid'])) ? sanitize_text_field($_GET['uid']) : '');
		// @description run if double email verification process has fail. @param user id (integer)
	} else {
		//success redirect
		$redirect = get_option('ihc_double_email_redirect_success');
		do_action('ihc_double_email_verification_success', (isset($_GET['uid'])) ? sanitize_text_field($_GET['uid']) : '');
		// @description run if double email verification process is Successfully. @param user id (integer)
	}
}

if (!empty($redirect) || $redirect!=-1){
	$redirect_url = get_permalink($redirect);
	if (!$redirect_url){
		// maybe custom redirect url
		$redirect_url = ihc_get_redirect_link_by_label($redirect);
	}
}

if (empty($redirect_url)){
	//go home
	$redirect_url = get_home_url();
}

wp_safe_redirect($redirect_url);
exit();
