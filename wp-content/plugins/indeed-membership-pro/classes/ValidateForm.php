<?php
namespace Indeed\Ihc;
/*
How to use it:
$validator = new \Indeed\Ihc\ValidateForm();

required fields, email, passwords
$isValid = $validator->resetInputProperties()
                     ->setUid( $uid=0 ) // int or null if the user is not registered yet
                     ->setFieldName( 'user_login' )
                     ->setCurrentValue( $currentValue='' )
                     ->isValid();

// conditional logic
$isValid = $ValidateForm->resetInputProperties()
                                  ->setFieldName( sanitize_text_field( $_POST['field'] ) )
                                  ->setUid( $uid )
                                  ->checkConditionalLogic( sanitize_text_field( $_POST['value'] ) );
*/
class ValidateForm
{
    /**
     * @var int
     */
    private $uid                        = null;
    /**
     * @var bool
     */
    private $isRequired                 = null;
    /**
     * @var string
     */
    private $fieldName                  = null;
    /**
     * @var string
     */
    private $fieldType                  = null;
    /**
     * @var array
     */
    private $fieldData                  = null;
    /**
     * @var string
     */
    private $currentValue               = null;
    /**
     * @var string
     */
    private $compareValue               = null;
    /**
     * @var string
     */
    private $conditionalLogicType       = null;
    /**
     * @var array
     */
    private $registerMessages           = null;
    /**
     * @var array
     */
    private $registerMetas              = null;

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        $this->registerMessages = ihc_return_meta_arr( 'register-msg' );
        $this->registerMetas = ihc_return_meta_arr('register');
    }

    /**
     * @param int
     * @return object
     */
    public function setUid( $input=0 )
    {
        $this->uid = $input;
        return $this;
    }

    /**
     * @param bool
     * @return object
     */
    public function setIsRequired( $input=null )
    {
        if ( $input === null ){
            $fieldData = $this->getFieldData();
            $this->isRequired = isset( $fieldData['req'] ) && $fieldData['req'] ? $fieldData['req'] : false;
        } else {
            $this->isRequired = $input;
        }

        return $this;
    }

    /**
     * @param array
     * @return object
     */
    public function setFieldData( $input=null )
    {
        if ( $input === null ){
            $allFields = ihc_get_user_reg_fields();
            if ( $allFields ){
                $key = ihc_array_value_exists( $allFields, $this->fieldName, 'name' );
                $this->fieldData = isset( $allFields[ $key ] ) ? $allFields[ $key ] : [];
            }
        } else {
            $this->fieldData = $input;
        }
        return $this;
    }

    /**
     * @param none
     * @return array
     */
    public function getFieldData()
    {
        if ( $this->fieldData === null ){
            $this->setFieldData();
        }
        return $this->fieldData;
    }

    /**
     * @param string
     * @return object
     */
    public function setFieldName( $input='' )
    {
        $this->fieldName = $input;
        return $this;
    }

    /**
     * @param string ( optional, if not provided, it will select the field type based on field name )
     * @return object
     */
    public function setFieldType( $input='' )
    {
        if ( $input === '' ){
            $fieldData = $this->getFieldData();
            $this->fieldType = isset( $fieldData[ 'type' ] ) ? $fieldData[ 'type' ] : '';
            return $this;
        }
        $this->fieldType = $input;
        return $this;
    }

    /**
     * @param string
     * @return object
     */
    public function setCurrentValue( $input='' )
    {
        $this->currentValue = $input;
        return $this;
    }

    /**
     * @param string
     * @return object
     */
    public function setCompareValue( $input='' )
    {
        $this->compareValue = $input;
        return $this;
    }


    /**
     * @param none
     * @return none
     */
    public function resetInputProperties()
    {
        foreach ( get_class_vars( get_class( $this ) ) as $variableName => $value ){
            if ( $variableName === 'registerMessages' || $variableName === 'registerMetas' ){
                continue;
            }
            $this->$variableName = null;
        }
        return $this;
    }

    /**
     * @param none
     * @return array
     */
    public function isValid()
    {
        if ( $this->currentValue === null ){
            return [
                      'message'     => 'Value is not set.',
                      'status'      => 0,
            ];
        }
        if ( $this->fieldName === null ){
            return [
                      'message'     => 'Field Name is not set.',
                      'status'      => 0,
            ];
        }
        if ( $this->fieldType === null || $this->fieldType === '' ){
            $this->setFieldType();
        }
        if ( $this->isRequired === null ){
            $this->setIsRequired();
        }
        switch ( $this->fieldName ){
            case 'user_login':
              return $this->user_login();
              break;
            case 'user_email':
              return $this->user_email();
              break;
            case 'confirm_email':
              return $this->confirm_email();
              break;
            case 'pass1':
              return $this->pass1();
              break;
            case 'pass2':
              return $this->pass2();
              break;
            case 'ihc_country':
              return $this->ihc_country();
              break;
            case 'ihc_state':
              return $this->ihc_state();
              break;
            case 'ihc_optin_accept':
              return $this->ihc_optin_accept();
              break;
            case 'ihc_memberlist_accept':
              return $this->ihc_memberlist_accept();
              break;
            case 'recaptcha':
              return $this->recaptcha();
              break;
            case 'ihc_social_media':
              return $this->ihc_social_media();
              break;
            case 'ihc_invitation_code_field':
              return $this->ihc_invitation_code_field();
              break;
            case 'tos':
              return $this->tos();
              break;
            default:
              switch ( $this->fieldType ){
                  case 'text':
                    return $this->text();
                    break;
                  case 'textarea':
                    return $this->textarea();
                    break;
                  case 'number':
                    return $this->number();
                    break;
                  case 'hidden':
                    return $this->hidden();
                    break;
                  case 'select':
                    return $this->select();
                    break;
                  case 'radio':
                    return $this->radio();
                    break;
                  case 'checkbox':
                    return $this->checkbox();
                    break;
                  case 'upload_image':
                    return $this->upload_image();
                    break;
                  case 'multi_select':
                    return $this->multi_select();
                    break;
                  case 'file':
                    return $this->file();
                    break;
                  case 'conditional_text':
                    return $this->conditional_text();
                    break;
                  case 'unique_value_text':
                    return $this->unique_value_text();
                    break;
                  case 'date':
                    return $this->date();
                    break;
                  case 'password':
                    return $this->password();
                    break;
                  case 'plain_text':
                    return [
                              'message'     => esc_html__( 'Success', 'ihc' ),
                              'status'      => 1,
                    ];
                    break;
              }
              break;
        }
        return [
                  'message'     => 'Unknown field type or field name.',
                  'status'      => 0,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function user_login()
    {
        global $wpdb;
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages['ihc_register_err_req_fields'] ),
                      'status'            => 0,
            ];
        } else if ( !$this->isRequired && $this->currentValue === '' ){
            // field is not required and has no value
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        }

        if ( $this->uid === null || $this->uid === 0 ){
            if ( !validate_username( $this->currentValue ) ){
                return [
                          'message'           => stripslashes( $this->registerMessages[ 'ihc_register_error_username_msg' ] ),
                          'status'            => 0,
                ];
            }
            if ( username_exists( $this->currentValue ) ) {
                return [
                          'message'           => stripslashes( $this->registerMessages[ 'ihc_register_username_taken_msg' ] ),
                          'status'            => 0,
                ];
            }
        } else {
            // compore with existent
            $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_login=%s;", $this->currentValue ) );
            if ( $id !== null && $id !== false && $id !== $this->uid ){
                return [
                          'message'           => stripslashes( $this->registerMessages[ 'ihc_register_username_taken_msg' ] ),
                          'status'            => 0,
                ];
            }
        }

        // ========= security check username ========== //
        $blacklist = get_option('ihc_security_username');
        if ( $blacklist == '' ){
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        } else {
            $blacklist = explode( ',', preg_replace('/\s+/', '', $blacklist) );
            $theMessage = get_option( 'ihc_security_block_username_message' );
            $theMessage = stripslashes( $theMessage );

            if ( count($blacklist) > 0 && in_array( $this->currentValue, $blacklist ) ){
                return [
                          'message'           => $theMessage,
                          'status'            => 0,
                ];
            }

            foreach ( $blacklist as $name ){
                if ( isset( $name ) && $name != '' && strpos( $this->currentValue, $name ) !== false ){
                    return [
                              'message'           => $theMessage,
                              'status'            => 0,
                    ];
                }
            }
        }
        // ========= end of security check username ========== //

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function user_email()
    {
        global $wpdb;
        $response = [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
        ];
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            $response = [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        if ( !is_email( $this->currentValue ) ) {
          $response = [
                    'message'           => stripslashes( $this->registerMessages[ 'ihc_register_invalid_email_msg' ] ),
                    'status'            => 0,
          ];
        }

        // check if email address exists
        if ( $this->uid ){
          $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_email=%s;", $this->currentValue ) );

          if ( $id !== null && $id !== false && (int)$id !== (int)$this->uid ){
              $response = [
                        'message'           => stripslashes( $this->registerMessages[ 'ihc_register_email_is_taken_msg' ] ),
                        'status'            => 0,
              ];
          }
        } else {
            // new user ?
            if ( email_exists( $this->currentValue ) ){
                $return = stripslashes( $this->registerMessages['ihc_register_email_is_taken_msg'] );//ihc_correct_text(  )
                $response = [
                          'message'           => $return,
                          'status'            => 0,
                ];;
            }
        }

        // blacklist
        $blacklist = get_option('ihc_email_blacklist');
        if( $blacklist && $blacklist !== '' && $this->currentValue !== '' ){
            $blacklist = explode( ',', preg_replace( '/\s+/', '', $blacklist ) );
            if ( count( $blacklist ) > 0 && in_array( $this->currentValue, $blacklist ) ){
                $response = [
                          'message'           => stripslashes( $this->registerMessages['ihc_register_email_is_taken_msg'] ),
                          'status'            => 0,
                ];
            }
        }
        //$response['message'] = apply_filters( 'ump_filter_public_check_email_message', $response['message'], $this->currentValue );
        $response = apply_filters( 'ump_filter_public_check_email_message', $response, $this->currentValue );
        return $response;
    }

    /**
     * @param none
     * @return array
     */
    public function confirm_email()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        if ( $this->currentValue !== $this->compareValue ){
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_emails_not_match_msg' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function pass1()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages['ihc_register_err_req_fields'] ),
                      'status'            => 0,
            ];
        }

        if ( !$this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        }


				if ( (int)$this->registerMetas['ihc_register_pass_options'] === 2 ){
  					//characters and digits
  					if (!preg_match('/[a-z]/', $this->currentValue)){
                return [
                          'message'           => stripslashes( $this->registerMessages['ihc_register_pass_letter_digits_msg'] ),
                          'status'            => 0,
                ];
  					}
  					if (!preg_match('/[0-9]/', $this->currentValue)){
                return [
                          'message'           => stripslashes( $this->registerMessages['ihc_register_pass_letter_digits_msg'] ),
                          'status'            => 0,
                ];
  					}
				} else if ( (int)$this->registerMetas['ihc_register_pass_options'] === 3 ){
  					//characters, digits and one Uppercase letter
  					if ( !preg_match( '/[a-z]/', $this->currentValue ) ){
                return [
                          'message'           => stripslashes( $this->registerMessages['ihc_register_pass_let_dig_up_let_msg'] ),
                          'status'            => 0,
                ];
  					}
  					if ( !preg_match('/[0-9]/', $this->currentValue ) ){
                return [
                          'message'           => stripslashes( $this->registerMessages['ihc_register_pass_let_dig_up_let_msg'] ),
                          'status'            => 0,
                ];
  					}
  					if ( !preg_match('/[A-Z]/', $this->currentValue ) ){
                return [
                          'message'           => stripslashes( $this->registerMessages['ihc_register_pass_let_dig_up_let_msg'] ),
                          'status'            => 0,
                ];
  					}
				}
				//check the length of password
				if ( (int)$this->registerMetas['ihc_register_pass_min_length'] !== 0 ){
  					if ( strlen( $this->currentValue ) < $this->registerMetas['ihc_register_pass_min_length'] ){
  						  $message = str_replace( '{X}', $this->registerMetas['ihc_register_pass_min_length'], $this->registerMessages['ihc_register_pass_min_char_msg'] );
  						  $message = stripslashes( $message );
                return [
                          'message'           => $message,
                          'status'            => 0,
                ];
  					}
				}

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function pass2()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages['ihc_register_err_req_fields'] ),
                      'status'            => 0,
            ];
        }
        if ( $this->currentValue !== $this->compareValue ){
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_pass_not_match_msg' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function password()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages['ihc_register_err_req_fields'] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function ihc_country()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        /*
        if ( $this->compareValue !== null && !$this->checkConditionalLogic() ){
            // Conditional Logic
            return [
                      'message'           => esc_html__( 'Error', 'ihc' ),
                      'status'            => 0,
            ];
        }
         */

        return [
                'message'           => esc_html__( 'Success', 'ihc' ),
                'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function ihc_state()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        /*
        if ( $this->compareValue !== null && !$this->checkConditionalLogic() ){
            // Conditional Logic
            return [
                      'message'           => esc_html__( 'Error', 'ihc' ),
                      'status'            => 0,
            ];
        }

         */

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function ihc_optin_accept()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        return [
                'message'           => esc_html__( 'Success', 'ihc' ),
                'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function ihc_memberlist_accept()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function recaptcha()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        return [
                'message'           => esc_html__( 'Success', 'ihc' ),
                'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function ihc_social_media()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function ihc_invitation_code_field()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        if ( !$this->isRequired && $this->currentValue === '' ){
            // field is required only for a target membership and has no value
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        }
      	if ( $this->currentValue === '' || !\Ihc_Db::invitation_code_check( sanitize_text_field( $this->currentValue ) ) ){
      		$errorMessage = get_option('ihc_invitation_code_err_msg');
      		if ( $errorMessage ){
              return [
                        'message'           => stripslashes( $errorMessage ),
                        'status'            => 0,
              ];
      		} else {
              return [
                        'message'           => esc_html__('Your Invitation Code is wrong.', 'ihc'),
                        'status'            => 0,
              ];
      		}
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }


    /**
     * @param none
     * @return array
     */
    public function text()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function number()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }


    /**
     * @param none
     * @return array
     */
    public function textarea()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function hidden()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function select()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function radio()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                'message'           => esc_html__( 'Success', 'ihc' ),
                'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function checkbox()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function upload_image()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function multi_select()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function file()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }


    /**
     * @param none
     * @return array
     */
    public function tos()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        if ( (int)$this->currentValue === 0 ){
            $errorMessage = get_option( 'ihc_register_err_tos', esc_html__( 'Error On Terms & Conditions ', 'ihc' ) );
            return [
                      'message'           => $errorMessage,
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function date()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }
        if ( $this->currentValue !== '' && strtotime( $this->currentValue ) === false ){
            return [
                      'message'           => esc_html__( 'Date is not valid', 'ihc' ),
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param none
     * @return array
     */
    public function unique_value_text()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        if ( $this->currentValue === '' ){
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        }

        // check if the value is unique in the usermeta table
        $uidForPair = \Ihc_Db::getUserIdForMetaAndValue( $this->fieldName, $this->currentValue );
        if ( $uidForPair && (int)$uidForPair !== (int)$this->uid ){
            if ( $this->registerMessages['ihc_register_unique_value_exists'] === false || $this->registerMessages['ihc_register_unique_value_exists'] === '' ){
                return [
                          'message'           => esc_html__('This value already exists.', 'ihc'),
                          'status'            => 0,
                ];
            }
            return [
                      'message'           => stripslashes( $this->registerMessages['ihc_register_unique_value_exists'] ),
                      'status'            => 0,
            ];
        }

        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

    /**
     * @param string
     * @return bool
     */
    public function conditional_text()
    {
        if ( $this->isRequired && $this->currentValue === '' ){
            // field is required but has no value
            return [
                      'message'           => stripslashes( $this->registerMessages[ 'ihc_register_err_req_fields' ] ),
                      'status'            => 0,
            ];
        }

        if ( !isset( $this->fieldData['conditional_text'] ) ){
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        }
        if ( ( ( string )$this->currentValue) === ( ( string )$this->fieldData['conditional_text'] ) ){
            return [
                      'message'           => esc_html__( 'Success', 'ihc' ),
                      'status'            => 1,
            ];
        }
        if ( !empty( $this->fieldData['error_message'] ) ){
            return [
                      'message'           => stripslashes( $this->fieldData[ 'error_message' ] ),
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Error', 'ihc' ),
                  'status'            => 0,
        ];
    }

    /**
     * @param string
     * @return bool
     */
    public function checkConditionalLogic( $valueToCheck='' )
    {
        $this->setFieldData();
        if ( !isset( $this->fieldData['conditional_logic_corresp_field_value'] ) ){
            return true;
        }
        if ( $this->fieldData['conditional_logic_cond_type'] === 'has' ){
            //has value
            if ( ( is_string($valueToCheck) || is_numeric($valueToCheck) ) && $this->fieldData['conditional_logic_corresp_field_value'] == $valueToCheck ){
                return true;
            } else if ( is_array( $valueToCheck ) && count($valueToCheck) === 1 && isset( $valueToCheck[0] ) && $this->fieldData['conditional_logic_corresp_field_value'] == $valueToCheck[0] ){
                return true;
            }
        } else {
            //contain value
            if ( ( is_string($valueToCheck) || is_numeric($valueToCheck) ) && strpos( $valueToCheck, $this->fieldData['conditional_logic_corresp_field_value'] ) !== false ){
                return true;
            } else if ( is_array( $valueToCheck ) && count($valueToCheck) > 0 && in_array($this->fieldData['conditional_logic_corresp_field_value'], $valueToCheck)  ){
                return true;
            }
        }
        return false;
    }

    /**
     * @param array
     * @return bool
     */
    public function checkRecaptcha( $postData=[] )
    {
        $captchaError = get_option('ihc_register_err_recaptcha');
        $captchaError = stripslashes( $captchaError );
        if (isset($postData['g-recaptcha-response'])){
          $type = get_option( 'ihc_recaptcha_version' );
          if ( $type !== false && $type == 'v3'){
              $secret = get_option('ihc_recaptcha_private_v3');
          } else {
              $secret = get_option('ihc_recaptcha_private');
          }

          if ( $secret ){
              require_once IHC_PATH . 'classes/services/ReCaptcha/autoload.php';
              $recaptcha = new \ReCaptcha\ReCaptcha( $secret, new \ReCaptcha\RequestMethod\CurlPost() );
              $response = $recaptcha->verify($postData['g-recaptcha-response'], sanitize_text_field($_SERVER['REMOTE_ADDR']) );
              if ( !$response->isSuccess() ){
                  return [
                            'message'           => $captchaError,
                            'status'            => 0,
                  ];
              }
          } else {
              return [
                        'message'           => $captchaError,
                        'status'            => 0,
              ];
          }
        } else {
            return [
                      'message'           => $captchaError,
                      'status'            => 0,
            ];
        }
        return [
                  'message'           => esc_html__( 'Success', 'ihc' ),
                  'status'            => 1,
        ];
    }

}
