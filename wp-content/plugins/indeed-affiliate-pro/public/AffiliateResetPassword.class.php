<?php
if (!class_exists('AffiliateResetPassword')) :

class AffiliateResetPassword{
	private static $reset_success = 0;
	public function __construct(){}

	public function form($args=array()){
		/*
		 * @param none
		 * @return string
		 */
		$output = '';
		if (!is_user_logged_in()){
			global $indeed_db;
			$meta_arr = $indeed_db->return_settings_from_wp_option('login');
			foreach ($meta_arr as $key=>$value){
				if (isset($args[$key])){
					$meta_arr[$key] = $args[$key];
				}
			}
			if (!empty(self::$reset_success)){
				if (self::$reset_success==2){
					$data['success_message'] = get_option('uap_reset_msg_pass_ok');
				} else if (self::$reset_success==1) {
					$data['error_message'] = get_option('uap_reset_msg_pass_err');
				}
			}
			ob_start();
			require UAP_PATH . 'public/views/reset_password.php';
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	public function do_reset(){
		/*
		 * @param none
		 * @return none
		 */
		self::$reset_success = 1;
		require_once UAP_PATH . 'classes/ResetPassword.class.php';
		$reset_password = new UAP\ResetPassword();
		if ($reset_password->send_mail_with_link($_REQUEST['email_or_userlogin'])){
			self::$reset_success = 2;
		}
	}

}

endif;
