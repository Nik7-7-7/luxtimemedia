<?php
namespace Indeed\Ihc;

class ResetPassword
{
		/**
		 * @var int
		 */
		private $expire_interval 					= 3600;//one hour
		/**
		 * @var bool
		 */
		private static $emailSent 				= 0;

		/**
		 * @param none
		 * @return none
		 */
		public function __construct(){}

		/**
			* @param none
			* @return string
			*/
		public function form()
		{
				$output = '';

				$oldLogs = new \Indeed\Ihc\OldLogs();
				$s = $oldLogs->FGCS();
				$logg = $oldLogs->GCP();
				if ( $s === true || (int)$s > 0 || $logg === true ){
						$output .= ihc_public_notify_trial_version();
				}

				// set the template
				$templateName = get_option( 'ihc_login_template' );
				if ( empty( $templateName ) ){
						$templateName = get_option( 'ihc_login_template', 'ihc-login-template-1' );
				}
				$templateFile = IHC_PATH . 'public/views/login-templates/' . $templateName . '.php';
				$searchFilename = $templateName . '.php';
	  		$templateFile = apply_filters('ihc_filter_on_load_template', $templateFile, $searchFilename );

				// error message
				$errorMessage = get_option('ihc_reset_msg_pass_err');
				$errorMessage = stripslashes( $errorMessage );

				// success message
				$successMessage = get_option( 'ihc_reset_msg_pass_ok' );
				$successMessage = stripslashes( $successMessage );

				$data = [
									'usernameField'                 => false,
									'passwordField'                 => false,
									'emailLostPasswordField'        => true,
									'formId'                        => '',
									'ihcAction'                     => 'reset_pass',
	                'submitValue'                   => esc_html__('Get New Password', 'ihc'),
									'template'                      => $templateName,
									'wrappClass'										=> 'ihc-pass-form-wrap',
									'isLocker'                      => 0,
									'captcha'                       => [ 'show_captcha' => 0, 'html' => ''],
									'hideForm'                      => '',
									'hideFormMessage'               => '',
									'nonce'                         => wp_create_nonce( 'ihc_lost_password_nonce' ),
	                'nonceName'                     => 'ihc_lost_password_nonce',
									'settings'                      => [],
									'disabledSubmit'                => '',
									'registerPageUrl'               => '',
									'lostPassUrl'                   => '',
									'successMessage'                => $successMessage,
									'success'			                  => self::$emailSent === 1 ? true : false,
									'userType'                      => ihc_get_user_type(),
									'pendingUserMessage'            => '',
									'social'                        => '',
									'ajaxErrorMessage'              => '',
									'errorCode'                     => self::$emailSent === -1 ? 1 : 0,
									'errorMessage'                  => $errorMessage,
				];

				if ( $data['template'] === 'ihc-login-template-13' ){
						$data['submitValue'] = esc_html__('Reset my password', 'ihc');
				}

				// html output
				$view = new \Indeed\Ihc\IndeedView();
				$output .= $view->setTemplate( $templateFile )
												->setContentData( $data, true )
												->getOutput();
				return $output;
		}

		/**
		 * @param string
		 * @return none
		 */
		public function send_mail_with_link($username_or_email='')
		{
			self::$emailSent = -1;
			$user = get_user_by('email', $username_or_email);
			if ($user){
				$uid = $user->data->ID;
				$email_addr = $username_or_email;
			} else {
				//get user by user_login
				global $wpdb;
				$username_or_email = sanitize_text_field($username_or_email);
				$query = $wpdb->prepare( "SELECT ID, user_email FROM {$wpdb->users} WHERE `user_login`=%s;", $username_or_email );
				$data = $wpdb->get_row( $query );
				if (isset($data->ID) && isset($data->user_email)){
					$uid = $data->ID;
					$email_addr = $data->user_email;
				}
			}

			if (!empty($email_addr) && !empty($uid)){
				$hash = ihc_random_str(10);
				$time = indeed_get_unixtimestamp_with_timezone();
				update_user_meta($uid, 'ihc_reset_password_temp_data', array('code' => $hash, 'time' => $time ));
				$link = site_url();
				$link = add_query_arg('ihc_action', 'arrive', $link);
				$link = add_query_arg('do_reset_pass', 'true', $link);
				$link = add_query_arg('c', $hash, $link);
				$link = add_query_arg('uid', $uid, $link);
				$link = apply_filters( 'ump_public_filter_reset_password_link', $link, $uid, $hash );

				$sent = apply_filters( 'ihc_filter_reset_password_process', false, $uid, array('{password_reset_link}' => $link) );
				if (!$sent){
					$subject = esc_html__('Password reset on ', 'ihc') . get_option('blogname');
					$msg = '<p>' . esc_html__('You or someone else has requested to change password for your account', 'ihc'). '</p><br><p>' . esc_html__('To change Your Password click on this URL:', 'ihc') . ' </p>' . $link;
					wp_mail($email_addr, $subject, $msg);
				}
				self::$emailSent = 1;
			}
		}

		/**
		 * It will redirect.
		 * @param none
		 * @return none
		 */
		public function arrive()
		{
				if (!empty($_GET['do_reset_pass']) && !empty($_GET['uid']) && !empty($_GET['c'])){
					/// DO RESET PASSWORD
					$this->proceed( sanitize_text_field($_GET['uid']), sanitize_text_field($_GET['c']) );

					$redirect = get_option('ihc_general_password_redirect'); /// PASSWORD REDIRECT
				}

				/// AND OUT
				if (empty($redirect)){
					$redirect = get_option('ihc_general_redirect_default_page'); /// STANDARD REDIRECT
				}
				if (!empty($redirect) && $redirect!=-1){
					$redirect_url = get_permalink($redirect);
				} else {
					$redirect_url = get_home_url();	/// HOME
				}

				wp_safe_redirect( $redirect_url );
				exit;
		}

		/**
		 * @param int
		 * @param string
		 * @return none
		 */
		public function proceed($uid=0, $code='')
		{
			 if ($uid && $code){
			 	$time = indeed_get_unixtimestamp_with_timezone();
				$data = get_user_meta($uid, 'ihc_reset_password_temp_data', TRUE);
				if ($data){
					if ($data['code']==$code && $data['time']+$this->expire_interval>$time){
						$sucess = $this->do_reset_password($uid);
						if ($sucess){
							delete_user_meta($uid, 'ihc_reset_password_temp_data');
						}
					}
				}
			 }
		}

		/**
		 * @param int
		 * @return boolean
		 */
		private function do_reset_password($uid=0)
		{
				if ( $uid === 0 ){
						return;
				}
			 	add_filter( 'send_password_change_email', '__return_false', 1);
				$password = wp_generate_password(10, TRUE);
				$result = wp_set_password( $password, $uid );
				$sent = apply_filters( 'ihc_filter_reset_password', false, $uid, [ '{NEW_PASSWORD}' => $password ] );
				if (!$sent){
					$email_addr = $this->get_mail_by_uid( $uid );
					if ($email_addr){
						$subject = esc_html__('Password reset on ', 'ihc') . get_option('blogname');
						$msg = esc_html__('Your new password it\'s: ', 'ihc') . $password;
						$sent = wp_mail( $email_addr, $subject, $msg );
					}
				}
				do_action( 'ihc_action_password_has_been_reset', $uid );
		}

		/**
		 * @param int
		 * @return string
		 */
		private function get_mail_by_uid($uid=0)
		{
			 if ($uid){
			 	$data = get_userdata($uid);
				return (!empty($data) && !empty($data->user_email)) ? $data->user_email : '';
			 }
			 return '';
		}

}
