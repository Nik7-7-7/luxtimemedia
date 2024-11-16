<?php
namespace Indeed\Ihc;

class ChangePassword
{
    /**
     * @var array
     */
    private static $errors                           = [];
    /**
     * @var bool
     */
    private static $status                           = null;

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_shortcode( 'ihc-change-password-form', [ $this, 'shortcode' ] );
        add_action( 'ihc_action_public_post', [ $this, 'processing' ], 999, 2 );
    }

    /**
     * @param none
     * @return string
     */
    public function shortcode()
    {
        $template = get_option( 'ihc_profile_form_template', false );
        if ( $template === false ){
            $template = get_option('ihc_register_template');
        }
        $data['template'] = $template;
        $data['form'] = $this->form();

        $template = IHC_PATH . 'public/views/change-password-form.php';
        $searchFilename = 'change-password-form.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }

    /**
     * @param array
     * @return string
     */
    public function form( $attr=[] )
    {
        global $current_user, $wp_version;

        wp_register_script( 'ihc_passwordStrength', IHC_URL . 'assets/js/passwordStrength.js', ['jquery'], '12.8' );
        wp_enqueue_style( 'dashicons' );
        if ( version_compare ( $wp_version , '5.7', '>=' ) ){
            wp_add_inline_script( 'ihc_passwordStrength', "ihcPasswordStrengthLabels='" . json_encode( [esc_html__('Very Weak', 'ihc'), esc_html__('Weak', 'ihc'), esc_html__('Good', 'ihc'), esc_html__('Strong', 'ihc')] ) . "';" );
        } else {
            wp_add_inline_script( 'ihc_passwordStrength', 'ihcPasswordStrengthLabels', json_encode( [esc_html__('Very Weak', 'ihc'), esc_html__('Weak', 'ihc'), esc_html__('Good', 'ihc'), esc_html__('Strong', 'ihc') ] ) );
        }
        wp_enqueue_script('ihc_passwordStrength');

        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( $uid === 0 ){
            return '';
        }

        if ( self::$status === true ){
            add_action( 'ihc_action_template_form_file_before_submit_button', [ $this, 'printSuccess' ], 999, 2 );
        }

        $template = get_option( 'ihc_profile_form_template', 'ihc-register-1' );

        $data = [
                    'fields'            => [
                                              [
                                                  'name'                => 'current_pass',
                                                  'type'                => 'password',
                                                  'parent_field_id'     => '',
                                                  'parent_field_class'  => '',
                                                  'required_field'      => '',
                                                  'label_inside'        => '',
                                                  'disabled_field'      => '',
                                                  'label'               => esc_html__("Current password", 'ihc'),
                                                  'label_inside'        => esc_html__("Current password", 'ihc'),
                                                  'multiple_values'     => false,
                                              ],
                                              [
                                                  'name'                => 'new_pass1',
                                                  'type'                => 'password',
                                                  'parent_field_id'     => '',
                                                  'parent_field_class'  => '',
                                                  'required_field'      => '',
                                                  'label_inside'        => '',
                                                  'disabled_field'      => '',
                                                  'label'               => esc_html__("New password", 'ihc'),
                                                  'label_inside'        => esc_html__("New password", 'ihc'),
                                                  'multiple_values'     => false,
                                              ],
                                              [
                                                  'name'                => 'new_pass2',
                                                  'type'                => 'password',
                                                  'parent_field_id'     => '',
                                                  'parent_field_class'  => '',
                                                  'required_field'      => '',
                                                  'label_inside'        => '',
                                                  'disabled_field'      => '',
                                                  'label'               => esc_html__("Confirm new password", 'ihc'),
                                                  'label_inside'        => esc_html__("Confirm new password", 'ihc'),
                                                  'multiple_values'     => false,
                                              ]
                    ],
                    'extra_fields'      => [
                                              [
                                                  'name'                => 'ihcaction',
                                                  'value'               => 'do_change_pass',
                                                  'type'                => 'hidden',
                                                  'parent_field_id'     => '',
                                                  'parent_field_class'  => '',
                                                  'required_field'      => '',
                                                  'label_inside'        => '',
                                                  'disabled_field'      => '',
                                                  'multiple_values'     => false,
                                              ],
                                              [
                                                  'type'        => 'hidden',
                                                  'name'        => 'ihcchangepnonce',
                                                  'value'       => wp_create_nonce( 'ihc_user_do_change_p_nonce' ),
                                              ],
                    ],
                    'submit_bttn_label'   => esc_html__('Save changes', 'ihc'),
                    'submit_bttn_name'    => 'Submit',
                    'submit_bttn_id'      => 'ihc_submit_change_pass_bttn',
                    'form_type'           => 'edit',
                    'uid'                 => $uid,
                    'errors'              => self::$errors,
                    'form_class'          => 'ihc-form-change-password',
                    'form_name'           => 'changepassword',
                    'form_id'             => 'changepassword',
        ];
        $templateParts = explode( '-', $template );
        $templateNo = isset( $templateParts[2] ) ? (int)$templateParts[2] : 1;
        if ( $templateNo < 1 ){
            $templateNo = 1;
        }
        $template = 'form-template-' . $templateNo . '.php';

        $filename = 'form-template-' . $templateNo . '.php';
        $templateFile = IHC_PATH . 'public/views/form-templates/' . $filename;
        $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

        $view = new \Indeed\Ihc\IndeedView();
        $output = $view->setTemplate( $templateFile )
                       ->setContentData( $data, true )
                       ->getOutput();
        return $output;
    }

    /**
     * @param string
     * @param array
     * @return bool
     */
    public function processing( $action='', $postData=[] )
    {
        global $current_user;

        if ( $action !== 'do_change_pass' ){
            return false;
        }

        if ( empty( $postData['ihcchangepnonce'] ) || !wp_verify_nonce( $postData['ihcchangepnonce'], 'ihc_user_do_change_p_nonce' ) ){
            self::$errors[] = esc_html__("Something went wrong", 'ihc');
            self::$status = false;
            return false;
        }

        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( $uid === 0 ){
            self::$errors = esc_html__("User is not logged in", 'ihc');
            self::$status = false;
            return false;
        }

        // check the old pass
        $user = get_user_by( 'ID', $uid );
        if ( !$user || !wp_check_password( sanitize_text_field( $_POST['current_pass'] ), $user->data->user_pass, $user->ID ) ){
            self::$errors['current_pass'] = esc_html__( "Your current password is incorrect", 'ihc');
            self::$status = false;
            return false;
        }

        if ( !isset( $postData['new_pass1'] ) || $postData['new_pass1'] === '' ){
            self::$errors['new_pass1'] = esc_html__("Please complete all fields", 'ihc');
            self::$status = false;
            return false;
        }
        if ( !isset( $postData['new_pass2'] ) || $postData['new_pass2'] === '' ){
            self::$errors['new_pass2'] = esc_html__("Please complete all fields", 'ihc');
            self::$status = false;
            return false;
        }

        $validator = new \Indeed\Ihc\ValidateForm();
        $newPass1Response = $validator->resetInputProperties()
                                      ->setUid( $uid )
                                      ->setFieldName( 'pass1' ) // validate as a pass1 ( same name used on register process )
                                      ->setCurrentValue( sanitize_text_field( $postData['new_pass1'] ) )
                                      ->isValid();
        if ( $newPass1Response['status'] === 0 ){
            self::$errors['new_pass1'] = $newPass1Response['message'];
            self::$status = false;
            return false;
        }
        $newPass2Response = $validator->resetInputProperties()
                                      ->setUid( $uid )
                                      ->setFieldName( 'pass2' ) // validate as a pass2 ( same name used on register process )
                                      ->setCurrentValue( sanitize_text_field( $postData['new_pass2'] ) )
                                      ->setCompareValue( sanitize_text_field( $postData['new_pass1'] ) )
                                      ->isValid();
        if ( $newPass2Response['status'] === 0 ){
            self::$errors['new_pass2'] = $newPass2Response['message'];
            self::$status = false;
            return false;
        }

        // update pass
        $userData = [
                'ID'          => $uid,
                'user_pass'   => sanitize_text_field( $postData['new_pass1'] ),
        ];
        // update the user wp native fields
        wp_update_user( $userData );
        // user update his profile
        do_action( 'ihc_action_update_user', $uid );
        self::$status = true;
    }

    /**
     * @param int
     * @param array
     * @return none . it prints an error message
     */
    public function printSuccess( $uid=0, $fields=[] )
    {
        echo esc_ump_content('<div class="ihc-succes-message" >') . esc_html__("Your password has been successfully changed!", 'ihc') . esc_ump_content('</div>');
    }


}
