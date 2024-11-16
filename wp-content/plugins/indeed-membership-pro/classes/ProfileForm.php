<?php
namespace Indeed\Ihc;
/*
Getting the form:
$ProfileForm = new \Indeed\Ihc\ProfileForm();
$form = $ProfileForm->setUid( $uid )
                    ->setFields()
                    ->setUserData()
                    ->setTemplate()
                    ->form();
// and then print the $form variable

Update the user infos:
$ProfileForm = new \Indeed\Ihc\ProfileForm();
$result = $ProfileForm->setUid( $uid )
                      ->setFields()
                      ->doUpdate( $postData );
if ( $result === 0 ){
    $errors = $ProfileForm->getErrors();
}

*/
class ProfileForm
{
    /**
     * @var string
     */
    private $template                   = 'ihc-register-1';
    /**
     * @var int
     */
    private $uid                        = 0;
    /**
     * @var array
     */
    private $userData                   = [];
    /**
     * @var array
     */
    private $fields                     = [];
    /**
     * @var string
     */
    private $requiredFields             = [];
    /**
     * @var string
     */
    private $globalCss                  = null;
    /**
     * @var array
     */
    private static $errors              = [];
    /**
     * @var array
     */
    private $conditionalLogicFields     = [];
    /**
     * @var array
     */
    private $conditionalTextFields      = [];
    /**
     * @var array
     */
    private $uniqueFields               = [];
    /**
     * @var array
     */
    private $exceptionFields            = [];
    /**
     * @var bool
     */
    private $addPasswordForm            = false;

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
        $this->conditionalLogic();
        $this->buildCSS();
        $this->buildJS();

        $data = [
                    'fields'            => $this->fields,
                    'user_data'         => $this->userData,
                    'form_type'         => 'edit',
                    'uid'               => $this->uid,
                    'errors'            => self::$errors,
                    'form_class'        => 'ihc-form-create-edit',
                    'form_name'         => 'edituser',
                    'form_id'           => 'edituser',
                    'extra_fields'      => [
                                              [
                                                  'type'        => 'hidden',
                                                  'name'        => 'ihcFormType',
                                                  'value'       => 'edit',
                                              ],
                                              [
                                                  'type'        => 'hidden',
                                                  'name'        => 'ihcaction',
                                                  'value'       => 'update',
                                              ],
                                              [
                                                  'type'        => 'hidden',
                                                  'name'        => 'ihc_user_add_edit_nonce',
                                                  'value'       => wp_create_nonce( 'ihc_user_add_edit_nonce' ),
                                              ],
                    ],
                    'submit_bttn_label'   => esc_html__('Save Changes', 'ihc'),
                    'submit_bttn_name'    => 'Update',
                    'submit_bttn_id'      => 'ihc_submit_bttn',
        ];

        // add exceptions. fields that are conditional logic and required in the same time.
        if ( $this->exceptionFields !== null && count( $this->exceptionFields ) > 0 ){
            $data['extra_fields'][] = [
              'type'        => 'hidden',
              'name'        => 'ihc_exceptionsfields',
              'value'       => implode(',', $this->exceptionFields ),
            ];
        }

        // form template
        $templateParts = explode( '-', $this->template );
        $templateNo = isset( $templateParts[2] ) ? (int)$templateParts[2] : 1;
        if ( $templateNo < 1 ){
            $templateNo = 1;
        }
        $filename = 'form-template-' . $templateNo . '.php';
        $template = IHC_PATH . 'public/views/form-templates/' . $filename;
        $template = apply_filters('ihc_filter_on_load_template', $template, $filename );

        if ( $this->addPasswordForm === true ){
            add_filter( 'ihc_filter_the_profile_form_output', [ $this, 'addChangePasswordShortcode'] );
        }
        // html

        $view = new \Indeed\Ihc\IndeedView();
        $output = $view->setTemplate( $template )
                       ->setContentData( $data, true )
                       ->getOutput();
        return $output;
    }

    /**
     * @param none
     * @return none
     */
    public function buildCSS()
    {
        $globalCss = get_option( 'ihc_profile_form_custom_css', '' ); //add custom css to global css
        $globalCss .= $this->globalCss;
        if ( $globalCss === '' ){
            return;
        }
        wp_register_style( 'dummy-handle', false );
        wp_enqueue_style( 'dummy-handle' );
        wp_add_inline_style( 'dummy-handle', stripslashes( $globalCss ) );
    }

    /**
     * @param none
     * @return none
     */
    public function buildJS()
    {
        global $wp_version;
  			if ( !isset( $GLOBALS['wp_scripts']->registered['ihc-public-dynamic'] ) ){
  					wp_register_script( 'ihc-public-dynamic', IHC_URL . 'assets/js/public.js', ['jquery'], '12.8' );
  			}
        if ( !isset( $GLOBALS['wp_scripts']->registered['ihc-public-profile-form'] ) ){
  					wp_register_script( 'ihc-public-profile-form', IHC_URL . 'assets/js/IhcProfileForm.js', ['jquery'], '12.8' );
  			}

        if ( version_compare ( $wp_version , '5.7', '>=' ) ){
            if ( count( $this->requiredFields ) > 0 ){
                wp_add_inline_script( 'ihc-public-profile-form', "var ihc_edit_required_fields='" . json_encode( $this->requiredFields ) . "';" );
            }
            if ( count( $this->conditionalLogicFields ) > 0 ){
                wp_add_inline_script( 'ihc-public-profile-form', "var ihc_edit_conditional_logic='" . json_encode( $this->conditionalLogicFields ) . "';" );
            }
            if ( count( $this->conditionalTextFields ) > 0 ){
                wp_add_inline_script( 'ihc-public-profile-form', "var ihc_edit_conditional_text='" . json_encode( $this->conditionalTextFields ) . "';" );
            }
            if ( count( $this->uniqueFields ) > 0 ){
                wp_add_inline_script( 'ihc-public-profile-form', "var ihc_edit_unique_fields='" . json_encode( $this->uniqueFields ) . "';" );
            }
      	} else {
            if ( count( $this->requiredFields ) > 0 ){
                wp_localize_script( 'ihc-public-profile-form', 'ihc_edit_required_fields', json_encode( $this->requiredFields ) );
            }
      			if ( count( $this->conditionalLogicFields ) > 0 ){
                wp_localize_script( 'ihc-public-profile-form', 'ihc_edit_conditional_logic', json_encode( $this->conditionalLogicFields ) );
            }
            if ( count( $this->conditionalTextFields ) > 0 ){
                wp_add_inline_script( 'ihc-public-profile-form', 'ihc_edit_conditional_text', json_encode( $this->conditionalTextFields ) );
            }
            if ( count( $this->uniqueFields ) > 0 ){
                wp_add_inline_script( 'ihc-public-profile-form', 'ihc_edit_unique_fields', json_encode( $this->uniqueFields ) );
            }
      	}

        wp_enqueue_script( 'ihc-public-profile-form' );
    }

    /**
     * @param string
     * @return object
     */
    public function setTemplate( $template='' )
    {
        if ( $template === '' ){
            $this->template = get_option( 'ihc_profile_form_template', 'ihc-register-1' );
        } else {
            $this->template = $template;
        }
        return $this;
    }

    /**
     * @param string
     * @return object
     */
    public function setUid( $input=0 )
    {
        global $current_user;
        if ( empty( $input ) ){
            $input = isset( $current_user->ID ) ? $current_user->ID : 0;
        }
        $this->uid = $input;
        return $this;
    }

    /**
     * @param array
     * @return object
     */
    public function setFields( $fields=[] )
    {
        // getting fields from db
        $this->fields = ihc_get_user_reg_fields();
        // sort the fields
        ksort( $this->fields );

        // remove some fields that are not usefull on the edit
        $removeFields = [
                            'ihc_coupon',
                            'ihc_dynamic_price',
                            'payment_select',
                            'user_login',
                            'ihc_invitation_code_field',
                            'ihc_social_media',
                            'recaptcha',
                            'tos',
        ];
        foreach ( $removeFields as $field ){
            $key = ihc_array_value_exists( $this->fields, $field, 'name' );
            if ( $key !== false ){
                unset( $this->fields[$key] );
            }
        }

        // remove password - since version 11.3
        $key = ihc_array_value_exists( $this->fields, 'pass1', 'name' );
        if ( $key !== false && isset( $this->fields[$key]['display_public_ap'] ) && (int)$this->fields[$key]['display_public_ap'] === 1 ){
            $this->addPasswordForm = true;
            unset( $this->fields[$key] );
            $key = ihc_array_value_exists( $this->fields, 'pass2', 'name' );
            if ( $key !== false ){
                unset( $this->fields[$key] );
            }
        }
        // end of remove password - since version 11.3

        // show only the fields that are selected on backend
        foreach ( $this->fields as $fieldKey => $fieldArray ){
            if ( (int)$fieldArray['display_public_ap'] === 0 ){
                unset( $this->fields[$fieldKey] );
            } else {
                // Targeting Memberships
                if ( isset( $fieldArray['target_levels'] ) && $fieldArray['target_levels'] !== '' ){
                    $targetMemberships = explode( ',', $fieldArray['target_levels'] );
                    if ( count( $targetMemberships ) > 0 ){
                        $showField = false;
                        foreach ( $targetMemberships as $targetMembership ){
                            if ( \Indeed\Ihc\UserSubscriptions::userHasSubscription( $this->uid, $targetMembership )
                            && \Indeed\Ihc\UserSubscriptions::isActive( $this->uid, $targetMembership ) ){
                                $showField = true;
                            }
                        }
                        if ( !$showField ){
                            unset( $this->fields[$fieldKey] );
                            continue;
                        }
                    }
                }
                // end of Targeting Memberships

                // set the field parent id & class, required, inside label, multiple values
                $this->fields[$fieldKey]['parent_field_class']    = 'iump-form-' . $fieldArray['name'];
                $this->fields[$fieldKey]['parent_field_id']       = 'ihc_reg_' . $fieldArray['name'] . '_' . rand(1,10000);
                $this->fields[$fieldKey]['multiple_values']       = isset( $fieldArray['values'] ) && $fieldArray['values'] ? ihc_from_simple_array_to_k_v( $fieldArray['values'] ) : false;
                $this->fields[$fieldKey]['label_inside']          = isset( $fieldArray['native_wp'] ) && $fieldArray['native_wp'] ? esc_html__( $fieldArray['label'], 'ihc') : ihc_correct_text( $fieldArray['label'] );
                $this->fields[$fieldKey]['required_field']        = isset( $fieldArray['req'] ) && $fieldArray['req'] ? $fieldArray['req'] : false;
                //disable the user_login field
                $this->fields[$fieldKey]['disabled_field']        = $fieldArray['name'] === 'user_login' ? 'disabled' : '';

                // is this field required, this array will go into js. we exclude the pass1 and pass2
                if ( $this->fields[$fieldKey]['required_field'] !== false
                && ( $this->fields[$fieldKey]['name'] !== 'pass1' && $this->fields[$fieldKey]['name'] !== 'pass2' ) ){
                    $this->requiredFields[] = $fieldArray['name'];
                }

                // conditional text, unique value text
                if ( $this->fields[$fieldKey]['type'] === 'conditional_text' ){
                    $this->conditionalTextFields[] = $fieldArray['name'];
                } else if ( $this->fields[$fieldKey]['type'] === 'unique_value_text' ){
                    $this->uniqueFields[] = $fieldArray['name'];
                } else if ( $this->fields[$fieldKey]['name'] === 'ihc_memberlist_accept' || $this->fields[$fieldKey]['name'] === 'ihc_optin_accept' ){
                    $this->fields[$fieldKey]['hide_outside_label'] = true;
                }

            }
        }

        // since version 12.5 - switch the type of state field if its available
        $key = ihc_array_value_exists( $this->fields, 'ihc_state', 'name' );
        if ( $key !== false ){
            // switch the type of text field from checkbox to 'ihc_state'
            $this->fields[$key]['type'] = 'ihc_state';
        }
        // end of since version 12.5

        return $this;

    }

    /**
     * @param none
     * @return object
     */
    public function setUserData()
    {
        if ( count( $this->fields ) === 0 ){
            // no fields for edit section
            return $this;
        }
        $userDataObject = get_userdata( $this->uid );
        if ( !$userDataObject ){
            // no data for this user
            return $this;
        }

        foreach ( $this->fields as $key => $userField ){
            $name = $userField['name'];
            if ( $userField['native_wp'] == 1 ){
              //native wp field, get value from get_userdata ( $data object )
              if ( isset( $userDataObject->$name ) ){
                  $this->userData[ $name ] = $userDataObject->$name;
              }
            } else {
              //custom field, get value from get_user_meta()
              $this->userData[ $name ] = get_user_meta( $this->uid, $name, true );
            }

            // setting the value
            $value = isset( $this->userData[ $name ] ) ? $this->userData[ $name ] : '';
            // maybe it's plain text
            if ( empty( $value ) && $userField['type'] === 'plain_text' ){
                $value = $userField['plain_text_value'];
            }
            // put the value into fields array
            $this->fields[$key]['value_to_print'] = $value;

        }
        return $this;
    }

    /**
     * @param array
     * @return int
     */
    public function doUpdate( $postData=[] )
    {
        $this->fields = apply_filters( 'ump_before_update_user', $this->fields );

        // first of all lets check the nonce
        if ( !$this->checkNonce( $postData ) ){
            self::$errors['general'] = esc_html__( 'Something went wrong.', 'ihc' );
            return;
        }

        // check if we update the password, if the password is not completed we remove the field from list
        $key = ihc_array_value_exists( $this->fields, 'pass1', 'name' );
        if ( $key !== false && isset( $this->fields[$key] ) && isset( $postData['pass1'] ) && $postData['pass1'] === '' ){
            unset( $postData['pass1'] );
        	  unset( $this->fields[$key] );
            //remove pass2
            $key = ihc_array_value_exists( $this->fields, 'pass2', 'name' );
            if ( $key !== false && isset($this->fields[$key] ) ){
                unset( $postData['pass2'] );
        	      unset( $this->fields[$key] );
            }
        }

        $basicData  = [];
        $userMeta   = [];
        $validator  = new \Indeed\Ihc\ValidateForm();

        // exceptions ?
        $exceptions = [];
        if ( isset( $postData['ihc_exceptionsfields'] ) && $postData['ihc_exceptionsfields'] !== '' ){
            $exceptions = explode( ',', $postData['ihc_exceptionsfields'] );
        }

        foreach ( $this->fields as $formField ){
            $name = isset( $formField['name'] ) ? $formField['name'] : '';
            if ( !isset( $postData[$name] ) ){
                $postData[$name] = '';
            }
            // verify field
            $validator->resetInputProperties()
                                 ->setUid( $this->uid )
                                 ->setFieldName( $name )
                                 ->setCurrentValue( $postData[$name] );
            if ( $name === 'confirm_email' && isset( $postData['user_email'] ) ){
                $validator->setCompareValue( $postData['user_email'] );
            } else if ( $name === 'pass2' && isset( $postData['pass1'] ) ){
                $validator->setCompareValue( $postData['pass1'] );
            }

            if ( isset( $formField['req'] ) && $formField['req'] && !in_array( $name, $exceptions ) ){
                $validator->setIsRequired( true );
            } else {
                $validator->setIsRequired( false );
            }

            $isValid = $validator->isValid();
            if ( $isValid['status'] === 0 ){
                self::$errors[$name] = $isValid['message'];
            }

            if ( $formField['native_wp'] ){
                $basicData[$name] = $postData[$name];
            } else {
                if ( is_array( $postData[$name] ) ){
                    $userMeta[$name] = indeedFilterVarArrayElements( $postData[$name] );
                } else {
                    $userMeta[$name] = sanitize_textarea_field( $postData[$name] );//filter_var( $postData[$name], FILTER_SANITIZE_STRING );
                }
            }
        }

        if ( count( self::$errors ) ){
            return 0; // something went wrong
        }


        //remove pass2
        $key = ihc_array_value_exists( $this->fields, 'pass2', 'name' );
        if ( $key!== false && isset( $this->fields[$key] ) ){
             unset( $basicData['pass2'] );
        }
        //remove repeat email
        $key = ihc_array_value_exists( $this->fields, 'confirm_email', 'name' );
        if ( $key !== false && isset( $this->fields[$key] ) ){
             unset( $basicData['confirm_email'] );
        }

        // set the uid
        $basicData['ID'] = $this->uid;

        // password
        if ( isset( $basicData['pass1'] ) && $basicData['pass1'] !== '' ){
            $basicData['user_pass'] = $basicData['pass1'];
            unset( $basicData['pass1'] );
        }

        $basicData = $this->setWpNativeFields( $basicData, $postData, $this->fields, $this->uid );

        wp_update_user( $basicData );
        do_action( 'ump_on_update_action', $this->uid );

        // update the custom fields
        if ( $userMeta ){
            $userMeta = $this->setCustomFields( $userMeta, $postData, $this->fields, $this->uid );

    				foreach ( $userMeta as $metaKey => $metaValue ){
    						do_action( 'ihc_before_user_save_custom_field', $this->uid, $metaKey, $metaValue );
    						// @description run before save user custom information (user meta). @param user id(integer), custom information name (string), custom information (mixed)
    						update_user_meta( $this->uid, $metaKey, $metaValue );
    						do_action( 'ihc_user_save_custom_field', $this->uid, $metaKey, $metaValue );
    						// @description run after save user custom information (user meta). @param user id(integer), custom information name (string), custom information (mixed)
    				}
  			}

        do_action( 'ihc_action_update_user', $this->uid );
        return 1;// means ok
    }

    /**
     * @param none
     * @return array
     */
    public function getErrors()
    {
        return self::$errors;
    }

    /**
     * @param none
     * @return none
     */
    private function conditionalLogic()
    {
        if ( count( $this->fields ) === 0 ){
            return '';
        }

        foreach ( $this->fields as $fieldKey => $field ){
            if ( empty( $field['conditional_logic_corresp_field'] ) || $field['conditional_logic_corresp_field'] === -1 ){
                continue;
            }
            // Js action
            $key = ihc_array_value_exists( $this->fields, $field['conditional_logic_corresp_field'], 'name' );

            if ( $key === false || empty( $field['type'] ) ){
                continue;
            }
            $fieldToCheckType = isset( $this->fields[$key]['type'] ) ? $this->fields[$key]['type'] : 0;

            $value = get_user_meta( $this->uid, $field['conditional_logic_corresp_field'], true );

            $checkConditionalLogic = 0;

            if ( $field['conditional_logic_cond_type'] === 'has' ){
                // has value
                if ( $field['conditional_logic_corresp_field_value'] === $value ){
                    $checkConditionalLogic = 1;
                }
            } else {
                // contain value
                if ( isset($value) && isset($field['conditional_logic_corresp_field_value']) && !empty($field['conditional_logic_corresp_field_value'])
                && is_string( $value ) && is_string( $field['conditional_logic_corresp_field_value'] ) ){
                  if(strpos( $value, $field['conditional_logic_corresp_field_value'] ) !== false){
                    $checkConditionalLogic = 1;
                  }
                }
            }

            $show = ( $field['conditional_logic_show'] === 'yes' ) ? 1 : 0;

            if ( $show ){
                // 'yes'
                $no_on_edit = $checkConditionalLogic;
            } else {
                // 'no'
                $no_on_edit = !$checkConditionalLogic;
            }

            $this->conditionalLogicFields[] = [
                'type'                => $field['type'],
                'field_to_check'      => $field['conditional_logic_corresp_field'],
                'field_to_check_type' => $fieldToCheckType,
                'target_parent_id'    => $field['parent_field_id'],
                'target_field'        => $field['name'],
                'show'                => $show,
            ];

            if ( !empty( $field['req'] ) && empty( $no_on_edit ) ){
                $this->exceptionFields[] = $field['name'];
            }

            if ( empty( $no_on_edit ) ){
                // hide the conditional logic only for public create, we must hide this field and show only when correlated field it's completed with desired value
                $this->globalCss .= "#{$field['parent_field_id']}{display: none;}";

            }
        }
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
              $wpNativeFields[$name] = sanitize_textarea_field($postData[$name]);//filter_var ( $postData[$name], FILTER_SANITIZE_STRING );
            }
        }
        return $wpNativeFields;
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
     * @return none
     */
    public function checkNonce( $postData=[] )
    {
        if ( empty( $postData['ihc_user_add_edit_nonce'] ) || !wp_verify_nonce( $postData['ihc_user_add_edit_nonce'], 'ihc_user_add_edit_nonce' ) ){
            return false;
        }
        return true;
    }

    /**
     * @param none
     * @return string
     */
    public function addChangePasswordShortcode( $content='' )
    {
        return $content . do_shortcode( '[ihc-change-password-form]' );
    }

}
