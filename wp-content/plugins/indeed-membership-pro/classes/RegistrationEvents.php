<?php
namespace Indeed\Ihc;

class RegistrationEvents
{
    public function __construct()
    {
        // check for errors
        add_action( 'ihc_filter_register_process_check_errors', [ $this, 'checkFieldsForErrors' ], 1, 4 );
        // check for errors in register lite
        add_action( 'ihc_filter_register_lite_process_check_errors', [ $this, 'checkFieldsForErrorsRegisterLite' ], 1, 4 );

        // set the role
        add_filter( 'ihc_filter_register_role', [ $this, 'setRole'], 9, 5 );

        // filter the form fields after we check the values
        add_filter( 'ihc_filter_register_process_form_fields', [ $this, 'filterFormFields' ], 1, 3 );

        // custom fields - user meta
        add_filter( 'ihc_filter_custom_fields_values', [ $this, 'setCustomFields' ], 1, 4 );

        // native worpdress user fields
        add_action( 'ihc_filter_wp_fields_values', [ $this, 'setWpNativeFields' ], 1, 4 );

        // after insert
        add_action( 'ihc_register_action_after_insert', [ $this, 'afterUserRegister' ], 1, 5 );
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkFieldsForErrors( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        // nonce
        $errorMessages = $this->checkNonce( $errorMessages, $postData, $registerFields, $uid );

        // username
        $errorMessages = $this->checkUsername( $errorMessages, $postData, $registerFields, $uid );
        $errorMessages = $this->securityCheckUsername( $errorMessages, $postData, $registerFields, $uid );

        // email
        $errorMessages = $this->checkEmail( $errorMessages, $postData, $registerFields, $uid );
        $errorMessages = $this->checkEmailBlackList( $errorMessages, $postData, $registerFields, $uid );

        // TOS
        $errorMessages = $this->checkTos( $errorMessages, $postData, $registerFields, $uid );

        // Captcha
        $errorMessages = $this->checkCaptcha( $errorMessages, $postData, $registerFields, $uid );

        // password
        $errorMessages = $this->checkPassword( $errorMessages, $postData, $registerFields, $uid );

        // invitation code
        $errorMessages = $this->checkInvitationCode( $errorMessages, $postData, $registerFields, $uid );

        // unique value
        $errorMessages = $this->checkUniqueValue( $errorMessages, $postData, $registerFields, $uid );

        return $errorMessages;
    }

    /**
      * @param array
      * @param array
      * @param array
      * @param int
      * @return array
      */
      public function checkFieldsForErrorsRegisterLite( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
      {
          // nonce
          $errorMessages = $this->checkNonce( $errorMessages, $postData, $registerFields, $uid );

          // email
          $errorMessages = $this->checkEmail( $errorMessages, $postData, $registerFields, $uid );
          $errorMessages = $this->checkEmailBlackList( $errorMessages, $postData, $registerFields, $uid );

          return $errorMessages;
      }

    /**
     * @param int
     * @param int
     * @param array
     * @param array
     * @param array
     * @return array
     */
    public function afterUserRegister( $uid=0, $postData=[], $registerFields=[], $shortcodesAttr=[], $registerType=''  )
    {
         // opt in
         $this->doOptIn( $uid, $postData, $registerFields, $shortcodesAttr, $registerType );

         //double e-mail verification
         $this->doubleEmailVerification( $uid, $postData, $shortcodesAttr, $registerType );

         // individual page
         $this->doIndividualPage( $uid );
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkNonce( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( empty( $postData['ihc_user_add_edit_nonce'] ) || !wp_verify_nonce( $postData['ihc_user_add_edit_nonce'], 'ihc_user_add_edit_nonce' ) ){
            $errorMessages['nonce'] = '';
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkEmail( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        //Change Password
        if(isset($postData['ChangePass'])){
          return $errorMessages;
        }

        if ( !is_email( $postData['user_email'] ) ) {
          $errorMessages['user_email'] = get_option( 'ihc_register_invalid_email_msg' );
        }
        if ( isset( $postData['confirm_email'] ) && $postData['user_email'] != $postData['confirm_email'] ){
          $errorMessages['user_email'] = get_option( 'ihc_register_emails_not_match_msg' );
        }
        if ( email_exists( $postData['user_email'] ) ){
          if ( $uid == 0 || ( $uid != 0 && email_exists( $postData['user_email'] ) != $uid ) ){
            $errorMessages['user_email'] = get_option( 'ihc_register_email_is_taken_msg' );
          }
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkEmailBlackList( $errorMessages=[], $postData=[], $registerFields=[], $uid=0  )
    {
        $blacklist = get_option('ihc_email_blacklist');
        if ( $blacklist === '' || $blacklist === false ){
            return $errorMessages;
        }
        $blacklist = explode( ',', preg_replace('/\s+/', '', $blacklist) );

        if( count($blacklist) > 0 && in_array( $postData['user_email'], $blacklist ) ){
            $errorMessages['user_email'] = get_option( 'ihc_register_email_is_taken_msg' );
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkUsername( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( ihc_is_magic_feat_active('register_lite') && !isset($postData['user_login']) ){ // ||
            return $errorMessages;
        }
        // is not required
        if ( !ihcRegisterIsFieldRequired( 'user_login' ) && $postData['user_login'] === '' ){
            return $errorMessages;
        }

        if ( !$uid && isset( $postData['user_login'] ) ){
            if ( !validate_username($postData['user_login'])) {
              $errorMessages['user_login'] = get_option( 'ihc_register_error_username_msg' );
            }
            if ( username_exists( $postData['user_login'] ) ){
              $errorMessages['user_login'] = get_option( 'ihc_register_username_taken_msg' );
            }
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function securityCheckUsername( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( $uid ){
            return $errorMessages;
        }
        if ( !isset($postData['user_login']) ){
            return $errorMessages;
        }

        // is not required
        if ( !ihcRegisterIsFieldRequired( 'user_login' ) && $postData['user_login'] === '' ){
            return $errorMessages;
        }

        $blacklist = get_option('ihc_security_username');
        if ( $blacklist == '' ){
            return $errorMessages;
        }
        $blacklist = explode( ',', preg_replace('/\s+/', '', $blacklist) );
        $theMessage = get_option( 'ihc_security_block_username_message' );
        $theMessage = stripslashes( $theMessage );

        if ( count($blacklist) > 0 && in_array( $postData['user_login'], $blacklist ) ){
            $errorMessages['user_login'] = $theMessage;
            return $errorMessages;
        }

        foreach ( $blacklist as $name ){
            if ( isset($name) && $name !='' && strpos( $postData['user_login'], $name ) !== false ){
                $errorMessages['user_login'] = $theMessage;
                return $errorMessages;
            }
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkCaptcha( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( $uid ){
            return $errorMessages;
        }

        // if it's register lite - no need for checking captcha
        if ( isset($postData['ihcaction']) && $postData['ihcaction'] == 'register_lite' && ihc_is_magic_feat_active('register_lite') && !isset( $postData['g-recaptcha-response'] ) ){
            return $errorMessages;
        }

        $lid = isset( $postData['lid'] ) ? $postData['lid'] : '';
				$key = ihc_array_value_exists($registerFields, 'recaptcha', 'name');
				if ($key!==FALSE && isset($registerFields[$key]) && isset($registerFields[$key]['target_levels'])
              && $registerFields[$key]['target_levels']!=''){
					///not available for current level
					$target_levels = explode(',', $registerFields[$key]['target_levels']);
					if (!in_array( $lid, $target_levels)){
						return $errorMessages;
					}
				}

				//check if capcha key is set
				$captcha_key = get_option('ihc_recaptcha_public');
				if (!$captcha_key){
					return $errorMessages;
				}

				$captcha = ihc_array_value_exists( $registerFields, 'recaptcha', 'name');
				$displayType = 'display_public_reg';
				if ( isset( $postData['ihcFormType'] ) ){
					 switch ( $postData['ihcFormType'] ){
							 case 'modal':
								 $displayType = 'display_on_modal';
								 break;
							 case 'create':
							 default:
								 $displayType = 'display_public_reg';
								 break;
					 }
				}
				if ($captcha!==FALSE && (int)($registerFields[$captcha][$displayType ]) > 0 ){
					$captchaError = get_option('ihc_register_err_recaptcha');
					if (isset($postData['g-recaptcha-response'])){
						$type = get_option( 'ihc_recaptcha_version' );
						if ( $type !== false && $type == 'v3'){
								$secret = get_option('ihc_recaptcha_private_v3');
						} else {
								$secret = get_option('ihc_recaptcha_private');
						}

						if ($secret){
							require_once IHC_PATH . 'classes/services/ReCaptcha/autoload.php';
							$recaptcha = new \ReCaptcha\ReCaptcha( $secret, new \ReCaptcha\RequestMethod\CurlPost() );
							$resp = $recaptcha->verify($postData['g-recaptcha-response'], sanitize_text_field($_SERVER['REMOTE_ADDR']) );
							if (!$resp->isSuccess()){
								$errorMessages['captcha'] = $captchaError;
							}
						} else {
							$errorMessages['captcha'] = $captchaError;
						}
					} else {
						$errorMessages['captcha'] = $captchaError;
					}
				}
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkTos( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( $uid ){
            return $errorMessages;
        }
        //check if tos was printed
        $tos_page_id = get_option('ihc_general_tos_page');
        $tos_msg = get_option('ihc_register_terms_c');//getting tos message
        if ( !$tos_page_id || !$tos_msg ){
          return $errorMessages;
        }

        $displayType = 'display_public_reg';
        if ( isset( $postData['ihcFormType'] ) ){
           switch ( $postData['ihcFormType'] ){
               case 'modal':
                 $displayType = 'display_on_modal';
                 break;
               case 'create':
               default:
                 $displayType = 'display_public_reg';
                 break;
           }
        }
        $tos = ihc_array_value_exists( $registerFields, 'tos', 'name' );
        if ( $tos!==FALSE && (int)($registerFields[$tos][ $displayType ]) > 0 ){
            if (!isset($postData['tos']) || $postData['tos']!=1){
              $errorMessages['tos'] = get_option('ihc_register_err_tos');
            }
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkPassword( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        // is not required
        if ( !ihcRegisterIsFieldRequired( 'pass1' ) && $postData['pass1'] === '' ){
            return $errorMessages;
        }

        if ( ($uid && empty( $postData['pass1'] )) || $uid ) {
            // edit profile.
            //Change Password
            if(!isset($postData['ChangePass'])){
              return $errorMessages;
            }
        }

        if ( !isset( $postData['pass1'] ) ){
            return $errorMessages;
        }

        //check the strength
        $passwordType = get_option( 'ihc_register_pass_options' );
        if ( $passwordType == 2 ){
              //characters and digits
              if (!preg_match('/[a-z]/', $postData['pass1'])){
                $errorMessages['pass1'] = get_option( 'ihc_register_pass_letter_digits_msg' );
              }
              if (!preg_match('/[0-9]/', $postData['pass1'])){
                $errorMessages['pass1'] = get_option( 'ihc_register_pass_letter_digits_msg' );
              }
        } elseif ( $passwordType == 3 ){
              //characters, digits and one Uppercase letter
              if (!preg_match('/[a-z]/', $postData['pass1'])){
                $errorMessages['pass1'] = get_option( 'ihc_register_pass_let_dig_up_let_msg' );
              }
              if (!preg_match('/[0-9]/', $postData['pass1'])){
                $errorMessages['pass1'] = get_option( 'ihc_register_pass_let_dig_up_let_msg' );
              }
              if (!preg_match('/[A-Z]/', $postData['pass1'])){
                $errorMessages['pass1'] = get_option( 'ihc_register_pass_let_dig_up_let_msg' );
              }
        }

        //check the length of password
        $passwordLength = get_option( 'ihc_register_pass_min_length' );
        if( $passwordLength != 0 ){
              if ( strlen($postData['pass1']) < $passwordLength ){
                $errorMessage = get_option( 'ihc_register_pass_min_char_msg' );
                $errorMessages['pass1'] = str_replace( '{X}', $passwordLength, $errorMessage );
              }
        }
        if ( isset( $postData['pass2'] ) ){
            if ( $postData['pass1'] != $postData['pass2'] ){
                $errorMessages['pass2'] = get_option( 'ihc_register_pass_not_match_msg' );
            }
        }

        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkInvitationCode( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        $fieldKey = ihc_array_value_exists( $registerFields, 'ihc_invitation_code_field', 'name' );
        $checkInviteCode = FALSE;
        $displayType = 'display_public_reg';
        if ( isset( $postData['ihcFormType'] ) ){
           switch ( $postData['ihcFormType'] ){
               case 'modal':
                 $displayType = 'display_on_modal';
                 break;
               case 'create':
               default:
                 $displayType = 'display_public_reg';
                 break;
           }
        }
        $lid = isset( $postData['lid'] ) ? $postData['lid'] : -1;
        if ($fieldKey!==false && !empty($registerFields[$fieldKey]) && !empty($registerFields[$fieldKey][ $displayType ] ) ){
           $checkInviteCode = true;
           if (isset($registerFields[$fieldKey]['target_levels']) && $registerFields[$fieldKey]['target_levels']!=''){
               ///not available for current level
               $targetLevels = explode(',', $registerFields[$fieldKey]['target_levels']);
               if (count($targetLevels)>0 && !in_array($lid, $targetLevels)){
                 $checkInviteCode = false;
               }
           }
        }

        if ( empty( $postData['uid'] ) && $checkInviteCode && get_option('ihc_invitation_code_enable')){
           if ( empty( $postData['ihc_invitation_code_field'] ) || !\Ihc_Db::invitation_code_check( $postData['ihc_invitation_code_field'] ) ){
               $errMsg = get_option('ihc_invitation_code_err_msg');
               if (!$errMsg){
                 $errorMessages['ihc_invitation_code_field'] = esc_html__('Your Invitation Code is wrong.', 'ihc');
               } else {
                 $errorMessages['ihc_invitation_code_field'] = $errMsg;
               }
           } else {
               \Ihc_Db::invitation_code_increment_submited_value( $postData['ihc_invitation_code_field'] );
           }
        }
        return $errorMessages;
    }

    /**
     * @param int
     * @return none
     */
    public function doOptIn( $uid=0, $postData=[], $registerFields=[], $shortcodesAttr=[], $registerType='' )
    {
        if ( isset( $shortcodesAttr['double_email'] ) && $shortcodesAttr['double_email'] !== false ){
              $doubleEmailVerfication = $shortcodesAttr['double_email'];
        } else if ( $registerType == 'register_lite' ){
              $doubleEmailVerfication = get_option( 'ihc_register_lite_double_email_verification' );
        } else {
              $doubleEmailVerfication = get_option('ihc_register_double_email_verification');
        }
        if ( $registerType == 'register_lite' ){
            $doOptIn = get_option( 'ihc_register_lite_opt_in' );
        } else {
            $doOptIn = get_option( 'ihc_register_opt-in' );
        }
        if ( $doOptIn && empty( $doubleEmailVerfication ) ){
            //If Opt In it's enable, put the email address somewhere
            // Not available when double email verification it's enabled

            if ( !isset( $registerFields['ihc_user_fields'] ) ){
                return;
            }
            // check if user accept to be in opt-in list
            $optinAccept = ihc_array_value_exists( $registerFields['ihc_user_fields'], 'ihc_optin_accept', 'name' );

            if ( $optinAccept === false || empty( $registerFields['ihc_user_fields'][ $optinAccept ][ 'display_public_reg' ] ) ){
                // opt in accept is not on register form
                return ihc_run_opt_in($postData['user_email']);
            }

            // opt-in accept field is on register form
            if ( !isset( $postData['ihc_optin_accept']) || $postData['ihc_optin_accept'] === 0  ){
                return;
            }
            return ihc_run_opt_in( $postData['user_email'] );
        }
    }

    /**
     * @param int
     * @return none
     */
    public function doubleEmailVerification( $uid=0, $postData=[], $shortcodesAttr=[], $registerType='' )
    {
        if (isset($shortcodesAttr['double_email']) && $shortcodesAttr['double_email']!==FALSE){
  				$doubleEmailVerfication = $shortcodesAttr['double_email'];
  			} else if ( $registerType == 'register_lite'){
              $doubleEmailVerfication = get_option( 'ihc_register_lite_double_email_verification' );
        }  else {
  				$doubleEmailVerfication = get_option('ihc_register_double_email_verification');
  			}

  			if ( empty( $doubleEmailVerfication ) ){
            return;
  			}
        $hash = ihc_random_str( 10 );
        //put the hash into user option
        update_user_meta( $uid, 'ihc_activation_code', $hash );
        //set ihc_verification_status @ -1
        update_user_meta( $uid, 'ihc_verification_status', -1 );

        $activationUrl = site_url();
        $activationUrl = add_query_arg( 'ihc_action', 'user_activation', $activationUrl );
        $activationUrl = add_query_arg( 'uid', $uid, $activationUrl );
        $activationUrl = add_query_arg( 'ihc_code', $hash, $activationUrl );

        $lid = isset( $postData['lid'] ) ? $postData['lid'] : '';
        do_action( 'ihc_action_double_email_verification', $uid, $lid, [ '{verify_email_address_link}' => $activationUrl ] );
    }


    /**
     * @param int
     * @return none
     */
    public function doIndividualPage( $uid=0 )
    {
        if ( !ihc_is_magic_feat_active( 'individual_page' ) ){
            return;
        }
        if ( !class_exists( 'IndividualPage' ) ){
            include_once IHC_PATH . 'classes/IndividualPage.class.php';
        }
        $object = new \IndividualPage();
        $object->generate_page_for_user( $uid );
    }


    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function checkUniqueValue( $errorMessages=[], $postData=[], $registerFields=[], $uid=0 )
    {
        foreach ( $registerFields as $registerField ) {
            $name = isset( $registerField['name'] ) ? $registerField['name'] : '';
            if ( isset( $registerField['type'] ) && $registerField['type'] == 'unique_value_text' && isset( $postData[$name] ) && \Ihc_Db::doesUserMetaValueExists( $name, $postData[$name], $uid ) ){
                $errorMessages[$name] = esc_html__('Error', 'ihc'); // unique value
            }
        }
        return $errorMessages;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function setCustomFields( $customMetaUser=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( empty( $uid ) ){
  				$customMetaUser['indeed_user'] = 1;
  			}

  			foreach ( $registerFields as $registerField ){
  					$name = $registerField['name'];

  					if ( $name == 'ihc_payment_gateway' ){
  							continue;
  					}
  					if ( isset( $registerField['type'] ) && $registerField['type']=='checkbox' && empty( $postData[$name] ) ){
  							/// empty checkbox
  							if ( $registerField['display_public_reg'] == 1 && empty( $uid ) ){
  								$customMetaUser[$name] = '';
  							} else if ( $registerField['display_public_ap'] == 1 && !empty( $uid ) ){
  								$customMetaUser[$name] = '';
  							}
  					} else if ( isset( $registerField['type'] ) && $registerField['type']=='single_checkbox' && empty( $postData[$name] )) {
  							if ( $name == 'ihc_memberlist_accept' || $name == 'ihc_optin_accept' ){
  								$customMetaUser[$name] = 0;
  							}
  					} else if ( isset( $postData[$name] ) ){
  							/// sanitize
                if ( empty( $registerField['type'] ) ){
  							         $postData[$name] = ihcSanitizeValue( $postData[$name], '' );
                       }else{
                         $postData[$name] = ihcSanitizeValue( $postData[$name], $registerField['type'] );
                       }
  							if ( empty( $registerField['native_wp'] ) ){
  							 	//custom field
  								if ( is_array( $postData[$name] ) ){
  									$customMetaUser[$name] = indeedFilterVarArrayElements( $postData[$name] );
  								} else {
  									$customMetaUser[$name] = sanitize_textarea_field( $postData[$name] );//filter_var( $postData[$name], FILTER_SANITIZE_STRING);
  								}
  							}
  					}
  			}

  			/// just for safe (in some older versions the ihc_country waa mark as wp native and don't save the value)
  			if ( !isset( $customMetaUser['ihc_country'] ) && isset( $postData['ihc_country'] ) ){
  				$customMetaUser['ihc_country'] = $postData['ihc_country'];
  			}
  			/// ihc_state - in older version ihc_state is wp_native
  			if ( isset( $postData['ihc_state'] ) ){
  				$customMetaUser['ihc_state'] = $postData['ihc_state'];
  			}

        return $customMetaUser;
    }

    /**
     * @param array
     * @param array
     * @param array
     * @param int
     * @return array
     */
    public function setWpNativeFields( $wpNativeFields=[], $postData=[], $registerFields=[], $uid=0 )
    {
        if ( isset( $postData['pass1'] ) ){
            $wpNativeFields['user_pass'] = $postData['pass1'];
        }
        foreach ( $registerFields as $registerField ){
            $name = $registerField['name'];
            if ( !isset( $postData[$name] ) ){
                continue;
            }
            if ( !empty( $registerField['native_wp'] ) ){
              $wpNativeFields[$name] = sanitize_textarea_field( $postData[$name] );//filter_var ( $postData[$name], FILTER_SANITIZE_STRING );
            }
        }
        return $wpNativeFields;
    }

        /**
         * @param string
         * @param array
         * @param int
         * @param array
         * @param string
         * @return string
         */
        public function setRole( $role='', $postData=[], $shortcodesAttr=[], $registerType='' )
        {
            if ( $registerType == 'register_lite' ){
                return $this->setRoleForRegisterLite( $role, $postData , $shortcodesAttr );
            } else {
                return $this->setRoleForRegister( $role, $postData , $shortcodesAttr );
            }
        }

        /**
         * @param string
         * @param array
         * @param array
         * @return string
         */
      public function setRoleForRegisterLite( $role='', $postData=[], $shortcodesAttr=[] )
      {
          $registerLiteRole = get_option( 'ihc_register_lite_user_role' );
          if ( $registerLiteRole != null && $registerLiteRole != '' ){
    				  $role = $registerLiteRole;
    			} else {
    				  $role = 'subscriber';
    			}

          //Removed >v.12.7
          /*
    			if ( isset( $shortcodesAttr['role'] ) && $shortcodesAttr['role'] !== false ){
    				  $role = $shortcodesAttr['role'];
    			}
          */

          return $role;
      }

      /**
       * @param string
       * @param array
       * @param array
       * @return string
       */
      public function setRoleForRegister( $role='', $postData=[], $shortcodesAttr=[] )
      {
          // special role for this level?
          //Removed >v.12.7
          /*
          if ( isset( $postData['lid'] ) ){
              $levelData = ihc_get_level_by_id( $postData['lid'] );
              if ( isset( $levelData['custom_role_level'] ) && $levelData['custom_role_level']!=-1 && $levelData['custom_role_level']){
                  return $levelData['custom_role_level'];
              }
          }

          /// CUSTOM ROLE FROM SHORTCODE
          if ( isset( $shortcodesAttr['role'] ) && $shortcodesAttr['role'] !== false ){
              return $shortcodesAttr['role'];
          }
          */

          $role = get_option( 'ihc_register_new_user_role' );
          if ( $role !== false && $role != '' ){
              return $role;
          }
          $role = get_option( 'default_role' );
          if ( $role !== false && $role != '' ){
              return $role;
          }
          return 'subscriber';
      }

      /**
        * @param array
        * @param array
        * @param array
        * @return array
        */
      public function filterFormFields( $registerFields=[], $postData=[], $uid=0 )
      {
          /// captcha
          $captcha = ihc_array_value_exists( $registerFields, 'recaptcha', 'name' );
          if ( $captcha && isset( $registerFields[$captcha] ) ){
              unset( $registerFields[$captcha] );
          }
          /// invation code
          $invitationCode = ihc_array_value_exists( $registerFields, 'ihc_invitation_code_field', 'name');
          if ( $invitationCode !== false && isset( $registerFields[$invitationCode] ) ){
              unset( $registerFields[$invitationCode] );
          }
          /// tos
          $tos = ihc_array_value_exists( $registerFields, 'tos', 'name');
          if ( $tos !== false && isset( $registerFields[$tos] ) ){
              unset( $registerFields[$tos] );
          }
          // pass1
          $pass1 = ihc_array_value_exists( $registerFields, 'pass1', 'name');
          if ( $pass1 !== false && isset( $registerFields[$pass1] ) ){
              unset( $registerFields[$pass1] );
          }
          // pass2
          $pass2 = ihc_array_value_exists( $registerFields, 'pass2', 'name');
          if ( $pass2 !== false && isset( $registerFields[$pass2] ) ){
              unset( $registerFields[$pass2] );
          }
          return $registerFields;
      }

}
