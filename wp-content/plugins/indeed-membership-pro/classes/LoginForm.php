<?php
namespace Indeed\Ihc;
/*
Added since version 10.7
How to use it:
$ihcLoginForm = new \Indeed\Ihc\LoginForm();
before the init action.
This class use the following shortcode: [ihc-login-form].
For the processing part it use the action ihc_action_public_post.
For processing social login it use action ihc_action_do_login_with_social.
Success request its made with ihc_success_login get param.
Error Message codes:
1 - pending user
2 - email pending
3 - login failed ( username or password wrong )
4 - social error
5 - captcha is wrong ( general error )
6 - nonce error ( general error )
7 - login block ( security module )
8 - general error
*/
class LoginForm
{
    /**
     * @var int
     */
    private static $error                    = false;
    /**
     * @var string
     */
    private static $errorCode                 = 0;
    /**
     * @var array
     */
    private $settings                         = '';

	/**
	 * @param none
	 * @return none
	 */
	public function __construct()
	{}

	/**
	 * @param array
	 * @return string
   * This function its used for [ihc-login-form] shortcode . Attributes available:  template , remember , register , lost_pass , social , captcha .
	 */
	public function html( $shortcodeAttr=[] )
	{
      $output = '';
      $this->settings = $this->setSettings( $shortcodeAttr );

      $this->buildCss();
      $this->buildJs();

      $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
      $ol_dL_ogs = new $class();
      $s = $ol_dL_ogs->FGCS();
      $logg = $ol_dL_ogs->GCP();
      if ( $s === true || (int)$s > 0 || $logg === true ){
          $output .= ihc_public_notify_trial_version();
      }

      // set the template
      if ( isset( $this->settings['ihc_login_template'] ) && $this->settings['ihc_login_template'] !== '' ){
          $templateName = $this->settings['ihc_login_template'];
      } else {
          $templateName = get_option( 'ihc_login_template', 'ihc-login-template-1' );
      }

      $templateFile = IHC_PATH . 'public/views/login-templates/' . $templateName . '.php';
      $searchFilename = $templateName . '.php';
  		$templateFile = apply_filters('ihc_filter_on_load_template', $templateFile, $searchFilename );

      $securityCheck = $this->loginSecurityModule();

      // errors
      $errorMessage = '';
      if ( isset( $_GET['ump-login-error'] ) && $_GET['ump-login-error'] !== '' ){
          self::$errorCode = sanitize_text_field( $_GET['ump-login-error'] ) ;
          $errorMessage = $this->getErrorByCode( self::$errorCode );
      } else if ( self::$error && self::$errorCode > 0 ){
          $errorMessage = $this->getErrorByCode( self::$errorCode );
      }

      $data = [
                'usernameField'                 => true,
                'passwordField'                 => true,
                'emailLostPasswordField'        => false,
                'formId'                        => 'ihc_login_form',
                'ihcAction'                     => 'login',
                'submitValue'                   => esc_html__('Log In', 'ihc'),
                'template'                      => $templateName,
                'wrappClass'                    => 'ihc-login-form-wrap',
                'isLocker'                      => empty( $this->settings['is_locker'] ) ? 0 : 1,
                'captcha'                       => $this->captcha(),
                'hideForm'                      => $securityCheck['hide_form'],
                'hideFormMessage'               => $securityCheck['hide_message'],
                'nonce'                         => wp_create_nonce( 'ihc_login_nonce' ),
                'nonceName'                     => 'ihc_login_nonce',
                'settings'                      => $this->settings,
                'disabledSubmit'                => empty( $this->settings['preview'] ) ? '' : 'disabled',
                'registerPageUrl'               => $this->getRegisterPageUrl( $this->settings ),
                'lostPassUrl'                   => $this->getLostPassUrl( $this->settings ),
                'successMessage'                => ihc_correct_text(get_option( 'ihc_login_succes', esc_html__('Welcome to our Website!', 'ihc') )),
                'success'                       => isset($_GET['ihc_success_login']) && $_GET['ihc_success_login'] ? 1 : 0,
                'userType'                      => isset( $this->settings['preview'] ) ? 'unreg' : ihc_get_user_type(),
                'pendingUserMessage'            => ihc_correct_text(get_option('ihc_register_pending_user_msg', true)),
                'social'                        => (!empty($this->settings['ihc_login_show_sm'])) ? ihc_print_social_media_icons('login', [], isset($this->settings['is_locker']) ? $this->settings['is_locker'] : false) : '',
                'ajaxErrorMessage'              => get_option( 'ihc_login_error_ajax' , esc_html__('Please complete all require fields!', 'ihc') ),
                'errorCode'                     => self::$errorCode,
                'errorMessage'                  => $errorMessage,
      ];

      // html output
      $view = new \Indeed\Ihc\IndeedView();
      $output .= $view->setTemplate( $templateFile )
                      ->setContentData( $data, true )
                      ->getOutput();

      return apply_filters( 'ihc_filter_login_form_html', $output );
	}

  /**
   * @param none
   * @return none
   */
  private function buildCss()
  {
      wp_enqueue_style( 'dashicons' );

      $customCss = get_option( 'ihc_login_custom_css' );
      if ( !empty( $customCss ) ){
      		wp_register_style( 'dummy-handle', false );
       	 	wp_enqueue_style( 'dummy-handle' );
       	 	wp_add_inline_style( 'dummy-handle', $customCss );
    	}

  }

  /**
   * @param none
   * @return none
   */
  private function buildJs()
  {
        wp_enqueue_script( 'ihc-public-login', IHC_URL . 'assets/js/IhcLoginForm.js', ['jquery'], '12.8' );
  }

  /**
   * @param int
   * @return string
   */
  private function getErrorByCode( $errorCode=0 )
  {
      if ( $errorCode === 0 ){
          return '';
      }
      $message = '';
      switch ( $errorCode ){
          case 1:
            // pending user
            $message = ihc_correct_text( get_option( 'ihc_login_pending', true ) );
            if ( $message === '' || $message === false || $message === null ){
                $message = esc_html__( 'Your account has not been approved. Please retry later.', 'ihc' );
            }
            break;
          case 2:
            // pending email
            $message = get_option( 'ihc_login_error_email_pending', true );
            if ( $message === '' || $message === false || $message === null ){
                $arr = ihc_return_meta_arr('login-messages', false, true);
                $message = isset( $arr['ihc_login_error_email_pending'] ) && $arr['ihc_login_error_email_pending'] ? $arr['ihc_login_error_email_pending'] : '';
            }
            if ( $message === '' || $message === false || $message === null ){
                $message = esc_html__('Error', 'ihc' );
            }
            break;
          case 3:
            // login failed ( username of password is wrong, or they are missing )
            $message = get_option('ihc_login_error', true);
            if ( $message === '' || $message === false || $message === null ){
        			   $arr = ihc_return_meta_arr('login-messages', false, true);
        			   $message = (isset($arr['ihc_login_error']) && $arr['ihc_login_error'] ) ? $arr['ihc_login_error'] : '';
        		}
            if ( $message === '' || $message === false || $message === null ){
                $message = esc_html__('Error', 'ihc');
            }
            break;
          case 4:
            // social login
            $message = get_option( 'ihc_social_login_failed', true );
        		if ( $message === '' || $message === false || $message === null ){
        				$message = esc_html__( 'You are not registered with this social network. Please register first!', 'ihc' );
        		}
            break;
          case 5:
            $message = get_option('ihc_login_error_on_captcha');
        		if (!$message){
        			   $message = esc_html__('Error on Captcha', 'ihc');
        		}
            break;
          case 6:
            // nonce error
            $message = esc_html__('Something went wrong, please try again later!', 'ihc');
            break;
          case 7:
            // security block
            require_once IHC_PATH . 'classes/Ihc_Security_Login.class.php';
        		$security_object = new \Ihc_Security_Login();
        		$message = $security_object->get_error_attempt_message();
            break;
          default:
            $message = esc_html__('Something went wrong, please try again later!', 'ihc');
            break;
      }
      return ihc_correct_text( $message );
  }

  /**
   * @param array
   * @return array
   */
  private function setSettings( $shortcodeAttr=[] )
  {
      $settings = ihc_return_meta_arr('login');
      if (!empty($shortcodeAttr['template'])){
        $settings['ihc_login_template'] = $shortcodeAttr['template'];
      }
      if (isset($shortcodeAttr['remember'])){
        $settings['ihc_login_remember_me'] = $shortcodeAttr['remember'];
      }
      if (isset($shortcodeAttr['register'])){
        $settings['ihc_login_register'] = $shortcodeAttr['register'];
      }
      if (isset($shortcodeAttr['lost_pass'])){
        $settings['ihc_login_pass_lost'] = $shortcodeAttr['lost_pass'];
      }
      if (isset($shortcodeAttr['social'])){
        $settings['ihc_login_show_sm'] = $shortcodeAttr['social'];
      }
      if (isset($shortcodeAttr['captcha'])){
        $settings['ihc_login_show_recaptcha'] = $shortcodeAttr['captcha'];
      }
      if (isset($shortcodeAttr['preview'])){
        // for admin preview purpose
        $settings['preview'] = $shortcodeAttr['preview'];
      }
      return $settings;
  }

  /**
   * @param array
   * @return string
   */
  private function getRegisterPageUrl( $settings=[] )
  {
      if ( empty($settings['ihc_login_register']) ){
          return '';
      }
      $pag_id = get_option('ihc_general_register_default_page');
      if ( !$pag_id || (int)$pag_id === -1 ){
          return '';
      }
      $register_page = get_permalink( $pag_id );
      if (!$register_page){
         $register_page = get_home_url();
      }
      return $register_page;
  }

  /**
   * @param array
   * @return string
   */
  private function getLostPassUrl( $settings=[] )
  {
      if ( empty($settings['ihc_login_pass_lost']) ){
          return '';
      }
      $pag_id = get_option( 'ihc_general_lost_pass_page' );
      if( !$pag_id || (int)$pag_id === -1 ){
          return '';
      }
      $lost_pass_page = get_permalink( $pag_id );
      if ( !$lost_pass_page || (int)$lost_pass_page === -1 ){
         $lost_pass_page = get_home_url();
      }
      return $lost_pass_page;
  }

  /**
   * @param none
   * @return array
   */
  private function captcha()
  {
      global $current_user;
      if ( isset( $current_user->ID ) && $current_user->ID != 0 ){
          // user is already login
          return [
                    'show_captcha'    => 0,
                    'html'            => '',
          ];
      }
      if ( !get_option( 'ihc_login_show_recaptcha', 0 ) ){
          return [
                    'show_captcha'    => 0,
                    'html'            => '',
          ];
      }
      $captchaType = get_option( 'ihc_recaptcha_version' );
      if ( $captchaType !== false && $captchaType == 'v3' ){
          $captchaKey = get_option('ihc_recaptcha_public_v3');
      } else {
          $captchaKey = get_option('ihc_recaptcha_public');
      }

      if ( empty( $captchaKey ) ){
          return [
                    'show_captcha'    => 0,
                    'html'            => '',
          ];
      }
      $view = new \Indeed\Ihc\IndeedView();
      $captchaData = [
          'class' 		=> '',
          'key'				=> $captchaKey,
          'langCode'	=> indeed_get_current_language_code(),
          'type'			=> $captchaType,
      ];

      $filename = 'login-captcha.php';
      $template = IHC_PATH . 'public/views/login-captcha.php';
      $template = apply_filters( 'ihc_filter_on_load_template', $template, $filename );

      $captcha = $view->setTemplate( $template )->setContentData( $captchaData, true)->getOutput();
      return [
                'show_captcha'    => 1,
                'html'            => $captcha,
      ];
  }

  /**
   * @param none
   * @return array
   */
  private function loginSecurityModule()
  {
      if ( !ihc_is_magic_feat_active('login_security') ){
          return [
                    'hide_form'       => 0,
                    'hide_message'    => '',
          ];
      }
      require_once IHC_PATH . 'classes/Ihc_Security_Login.class.php';
      $security_object = new \Ihc_Security_Login();
      if ($security_object->is_ip_on_black_list()){
          return [
                    'hide_form'       => 1,
                    'hide_message'    => esc_html__('You are not allowed to see this Page.', 'ihc'),
          ];
      } else {
          $show_form = $security_object->show_login_form();
          if (!$show_form){
              return [
                        'hide_form'       => 1,
                        'hide_message'    => $security_object->get_locked_message(),
              ];
          }
      }
      return [
                'hide_form'       => 0,
                'hide_message'    => '',
      ];
  }

	/**
	 * @param string
	 * @param array
	 * @return string
	 */
	public function doLogin( $actionValue='', $postData=[] )
  {
      if ( $actionValue !== 'login' || !isset( $postData ) ){
          return;
      }

  		$stop = apply_filters( 'ihc_login_filter_stop_process', false, $postData );
  		if ( $stop ){
  			return;
  		}

  		// no username provided - out
  		if ( !isset($postData['log']) || $postData['log'] === '' ){
        self::$errorCode = 3;
        self::$error = true;
  			return;
  		}
  		// no password provided - out
  		if ( !isset($postData['pwd']) || $postData['pwd'] === '' ){
        self::$errorCode = 3;
        self::$error = true;
  			return;
  		}

  		// set the url of current page
  		$url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  		// set the login data
  		$loginData['user_login'] = sanitize_user( $postData['log'] );
  		$loginData['user_password'] = sanitize_text_field( $postData['pwd'] );
  		$loginData['remember'] = ( isset( $postData['rememberme'] ) == 'forever' ) ? true : false;

  		/// CHECK RECAPTCHA
  		if ( !$this->checkRecaptcha( $postData ) ){
        self::$errorCode = 5;
        self::$error = true;
  			return;
  		}

  		// check nonce - if something is wrong redirect to same page with error message
  		if ( empty( $postData['ihc_login_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $postData['ihc_login_nonce'] ), 'ihc_login_nonce' ) ){
        self::$errorCode = 6;
        self::$error = true;
  			return;
  		}

  		// login security - magic feature
  		if ( ihc_is_magic_feat_active( 'login_security' ) ){
    			require_once IHC_PATH . 'classes/Ihc_Security_Login.class.php';
    			$security_object = new \Ihc_Security_Login( $loginData['user_login'], $loginData['user_password'] );
    			if ( !$security_object->login() && !$security_object->is_error_on_login() ){
              self::$errorCode = 7;
              self::$error = true;
        			return;
    			}
  		}

  		$user = wp_signon( $loginData, true );

  		// Pending user
  		if ( is_wp_error( $user ) && $user->get_error_message() == 'Pending User' ){
  			$this->clearCookiesAndLogout();
  			return $this->doRedirect( $url, [ 'ump-login-error' => 1 ] );// this will exit and redirect
  		}

  		// Login failed
  		if ( !isset( $user->ID ) ){
        self::$errorCode = 3;
        self::$error = true;
  			return;
  		}

  		// email status
  		if ( $this->isEmailVerified( $user->ID ) === false ){
  			$this->clearCookiesAndLogout();
  			if (!$url){
  				$url = home_url();
  			}
  			return $this->doRedirect( $url, [ 'ump-login-error' => 2 ] );// this will exit and redirect
  		}

  		// set the role
  		$role = isset( $user->roles[0] ) ? $user->roles[0] : '';

  		// pending role
  		if ( $role === 'pending_user' ){
  			$this->clearCookiesAndLogout();
  			return $this->doRedirect( $url, [ 'ump-login-error' => 1 ] );// this will exit and redirect
  		}

  		// suspended role
  		if ( $role === 'suspended' ){
  			$this->clearCookiesAndLogout();
  			return $this->doRedirect( $url, [ 'ump-login-error' => 8 ] );// this will exit and redirect
  		}

  		// login success
  		do_action('ihc_do_action_on_login', $user->ID );
  		$url = add_query_arg( [ 'ihc_success_login' => 'true' ], $url );

  		//LOCKER REDIRECT
  		if (!empty($postData['locker'])){
  			   return $this->doRedirect( $url );// this will exit and redirect
  		}
  		//LOCKER REDIRECT

  		$redirect = $this->loginRegirectUrl( $user->ID );
  		if ( $redirect !== '' ){
  			   return $this->doRedirect( $redirect );// this will exit and redirect
  		}

  		return $this->doRedirect( $url );// this will exit and redirect
	}

	/**
	 * @param array
     * @return none
	 */
	public function doLoginWithSocial( $loginData=[] )
	{
      global $wpdb;

      // set the url of current page
      $url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  		$meta_key = "ihc_" . $loginData['sm_type'];
  		$query = $wpdb->prepare("SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta}
  														WHERE meta_key=%s
  														AND meta_value=%s ", $meta_key, $loginData['sm_uid']
  		);
  		$data = $wpdb->get_row( $query );
  		$uid = isset( $data->user_id ) ? $data->user_id : 0;
      if ( !isset( $loginData['url'] ) ){
          $loginData['url'] = $url;
      }
  		if ( empty( $uid ) ){
          // out
  				$this->doRedirect( $loginData['url'], [ 'ump-login-error' => 4 ] );
  		}

			$user = get_userdata($uid);

      // set the role
  		$role = isset( $user->roles[0] ) ? $user->roles[0] : '';

  		// pending role
  		if ( $role === 'pending_user' ){
  			$this->clearCookiesAndLogout();
  			return $this->doRedirect( $url, [ 'ump-login-error' => 1 ] );// this will exit and redirect
  		}

  		// suspended role
  		if ( $role === 'suspended' ){
  			$this->clearCookiesAndLogout();
  			return $this->doRedirect( $url, [ 'ump-login-error' => 8 ] );// this will exit and redirect
  		}

			//======================== LOGIN SUCCESS
			wp_set_auth_cookie( $uid );//we set the user


			/********** REDIRECT ************/

			//LOCKER REDIRECT
			if (!empty($login_data['is_locker']) && !empty($login_data['url'])){
          return $this->doRedirect( $loginData['url'], [ 'ump-login-error' => 3 ] );// this will exit and redirect
			}
			//LOCKER REDIRECT
      $redirect = $this->loginRegirectUrl( $uid );
  		if ( $redirect !== '' ){
  			$this->doRedirect( $redirect );// this will exit and redirect
  		}
      do_action('ihc_do_action_on_login', $uid );
      // @description On user login. @param user id (integer)
      return $this->doRedirect( $loginData['url'], [ 'ihc_success_login' => 'true' ] );// this will exit and redirect
	}

	/**
	 * @param int ( user id )
	 * @return
	 */
	private function isEmailVerified( $uid=0 )
	{
		$email_verification = get_user_meta( $uid, 'ihc_verification_status', true );
		if ( (int)$email_verification === -1 ){
			return false;
		}
		return true;
	}

	/**
	 * @param array
	 * @return none
	 */
	private function checkRecaptcha( $postData=[] )
	{
		if ( !get_option( 'ihc_login_show_recaptcha', false ) ){
			return true;
		}
		$type = get_option( 'ihc_recaptcha_version' );
		if ( $type !== false && $type == 'v3'){
			 $secret = get_option('ihc_recaptcha_private_v3');
		} else {
			 $secret = get_option('ihc_recaptcha_private');
		}
		if ( $secret === false || $secret === '' || $secret === null ){
			 return true;
		}
		if (isset($postData['g-recaptcha-response'])){
			require_once IHC_PATH . 'classes/services/ReCaptcha/autoload.php';
			$recaptcha = new \ReCaptcha\ReCaptcha( $secret, new \ReCaptcha\RequestMethod\CurlPost() );
			$response = $recaptcha->verify( sanitize_text_field( $postData['g-recaptcha-response']), $_SERVER['REMOTE_ADDR'] );
			if ( !$response->isSuccess() ){
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * @param int
	 * @return string
	 */
	private function loginRegirectUrl($uid=0)
	{
		 $redirectUrl = '';
		 $id = get_option('ihc_general_login_redirect');
		 if (ihc_is_magic_feat_active('login_level_redirect')){
			 $user_levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, true );
		 	 $custom = get_option('ihc_login_level_redirect_rules');
			 if ($custom && $user_levels){
			 	$priority = get_option('ihc_login_level_redirect_priority');
			 	foreach ($priority as $lid){
			 		if (isset($user_levels[$lid]) && isset($custom[$lid])){
			 			$id = $custom[$lid];
						break;
			 		}
			 	}
			 }
		 }
		 $id = apply_filters( 'ump_public_filter_post_id_on_redirect_after_login', $id );
		 if ( $id && (int)$id !== -1){
			$redirectUrl = get_permalink($id);
			if ( !$redirectUrl && $uid ){
				$redirectUrl = ihc_get_redirect_link_by_label( $id, $uid );
			}
		 }
		 return $redirectUrl;
	}

	/**
	 * @param none
	 * @return none
	 */
	private function clearCookiesAndLogout()
	{
		wp_clear_auth_cookie();//logout
		do_action( 'wp_logout' );
		nocache_headers();
	}

	/**
	 * @param string
	 * @param arrray
	 * @return none
	 */
	private function doRedirect( $url='', $params=[] )
	{
		if ( is_array( $params ) && count( $params ) > 0 ){
			$url = add_query_arg( $params, $url );
		}
		wp_redirect( $url );
		exit;
	}

}
