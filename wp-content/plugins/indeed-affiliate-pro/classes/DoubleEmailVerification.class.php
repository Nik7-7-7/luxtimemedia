<?php
if ( class_exists( 'DoubleEmailVerification' ) ){
		return;
}

class DoubleEmailVerification
{

		/**
		 * @param int, string
		 * @return none. Redirect
		 */
		public function __construct($uid=0, $input_code='')
		{
				if (!empty($uid) && !empty($input_code)){
						$time_before_expire = get_option('uap_double_email_expire_time');
						$user_data = get_userdata($uid);
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
							$current_time = time();
							if ($current_time>$expire_time){
								$error = TRUE;
							}
						}

						//activate if it's case
						if (!$error){
							$hash = get_user_meta($uid, 'uap_activation_code', TRUE);
							if ($input_code==$hash){
								//success
								delete_user_option($uid, 'uap_activation_code');//remove code
								update_user_meta($uid, 'uap_verification_status', 1);
								//opt in
								if (!empty($user_data->data->user_email)){
										$this->doOptIn( $uid, $user_data->data->user_email );
								}
								//send notification

								uap_send_user_notifications($uid, 'email_check_success');
							} else {
								$error = TRUE;
							}
						}

						//redirect
						if ($error){
							//error redirect
							$redirect = get_option('uap_double_email_redirect_error');
						} else {
							//success redirect
							$redirect = get_option('uap_double_email_redirect_success');
						}
				}

				if (!empty($redirect) || $redirect!=-1){
					$redirect_url = get_permalink($redirect);
				}

				if (empty($redirect_url)){
					//go home
					$redirect_url = get_home_url();
				}

				wp_redirect($redirect_url);
				exit();
		}

		/**
		 * @param int
		 * @param string
		 * @return bool
		 */
		private function doOptIn( $uid=0, $emailAddr='' )
		{
				global $indeed_db;
				$doOptin = 1;
				$registerFields = $indeed_db->register_get_custom_fields( true );
				if ( isset( $registerFields['uap_optin_accept'] ) ){
						$doOptin = get_user_meta( $uid, 'uap_optin_accept', true );
				}
				if ( !$doOptin ){
						return ;
				}
				return uap_do_opt_in( $emailAddr );
		}

}
