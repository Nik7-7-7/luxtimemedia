<?php
namespace Indeed\Ihc\Admin;

class MemberAddEdit
{
    /**
     * @var int
     */
    private $uid        = 0;
    /**
     * @var array
     */
    private $errors     = [];

    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
      * @param int
      * @return object
      */
    public function setUid( $uid=0 )
    {
        $this->uid = $uid;
        return $this;
    }

    /**
      * @param none
      * @return array
      */
    protected function getFormFields()
    {
        $fields = ihc_get_user_reg_fields();
        if ( !$fields ){
            return [];
        }
        ksort( $fields );
        foreach ( $fields as $key => $field ){
            if ( !$field['display_admin'] ){
                unset( $fields[$key] );
            }
        }
        $key = ihc_array_value_exists( $fields, 'pass2', 'name');
        if ( $key !== false ){
            unset( $fields[$key] );
        }


        return $fields;
    }

    /**
      * @param none
      * @return string
      */
    public function form()
    {
        $userMeta = get_userdata( $this->uid );
        $userRoles = isset( $userMeta->roles ) ? $userMeta->roles : [];
        if ( $userRoles === [] ){
            $userRoles = get_option( 'default_role' );
        }
        $fields = $this->getFormFields();
        $data = [
                  'uid'                     => $this->uid,
                  'fields'                  => $fields,
                  'userData'                => $this->getUserData(),
                  'role'                    => $userRoles,
                  'userSubscriptions'       => \Indeed\Ihc\UserSubscriptions::getAllForUserAsList( $this->uid, false ),
                  'subscriptions'           => \Indeed\Ihc\Db\Memberships::getAll(),
                  'ihc_overview_post'       => get_user_meta( $this->uid, 'ihc_overview_post', true ),
        ];
        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( IHC_PATH . 'admin/includes/tabs/member-form.php' )
                    ->setContentData( $data )
                    ->getOutput();
    }

    /**
      * @param none
      * @return array
      */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
      * @param int
      * @return object
      */
    public function save( $postData=[] )
    {
        if ( !$postData ){
            return [];
        }
        $formFields = $this->getFormFields();

        $this->checkUsername( $postData );
        $this->checkEmail( $postData );

        if ( $this->errors ){
            return 0;
        }

        foreach ( $formFields as $formField ){
            $name = isset( $formField['name'] ) ? $formField['name'] : '';
            if ( !isset( $postData[$name] ) ){
                $postData[$name] = '';
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

        if ( isset( $postData['pass1'] ) && $postData['pass1'] != '' ){
            $basicData['user_pass'] = $postData['pass1'];
        }

        $basicData['role'] = '';

        // overview page
        if ( !empty( $postData['ihc_overview_post'] ) && $postData['ihc_overview_post'] >= -1 ){
            $userMeta['ihc_overview_post'] = $postData['ihc_overview_post'];
        }

        if ( empty( $postData['ID'] ) ){
            // create
            $basicData = apply_filters( 'ump_before_register_new_user', $basicData );
            $basicData['ID'] = wp_insert_user( $basicData );
            if ( is_wp_error( $basicData['ID'] ) ) {
                $this->errors['general'] = $basicData['ID']->get_error_message();
                return 0;
            }
        } else {
            // Update
            $basicData['ID'] = $postData['ID'];
            $basicData = apply_filters( 'ump_before_update_user', $basicData );
            $updated = wp_update_user( $basicData );
            if ( is_wp_error( $updated ) ) {
                $this->errors['general'] = $updated->get_error_message();
                return 0;
            }
            do_action( 'ump_on_update_action', $basicData['ID'] );
        }


        //
        $oldBanner = get_user_meta( $basicData['ID'], 'ihc_user_custom_banner_src', true );
        if ( isset( $postData['ihc_user_custom_banner_src'] ) && $oldBanner != $postData['ihc_user_custom_banner_src'] ){
            if ( $oldBanner != '' ){
                // remove old banner
                $path = parse_url( $oldBanner, PHP_URL_PATH);
                $fileAbsolutePath = sanitize_text_field($_SERVER['DOCUMENT_ROOT']) . $path;
                unlink( $fileAbsolutePath );
            }
            update_user_meta( $basicData['ID'], 'ihc_user_custom_banner_src', sanitize_text_field( $postData['ihc_user_custom_banner_src'] ) );
        }

        // role
        if ( isset( $postData['role'] ) && $postData['role'] != '' ){
            $user = new \WP_User( $basicData['ID'] );
            foreach ( $postData['role'] as $role ){
                $user->add_role( $role );
            }
        }

        // user meta
        $this->saveUserMetas( $basicData['ID'], $userMeta );

        // delete levels if it's case
        if ( isset( $_POST['ihc_delete_levels'] ) ){
            foreach ( $_POST['ihc_delete_levels'] as $key => $lid ){
                \Indeed\Ihc\UserSubscriptions::deleteOne( $basicData['ID'], sanitize_text_field( $lid ) );
            }
        }

        // assign new levels
        \Indeed\Ihc\UserSubscriptions::assignSubscriptionToUserManually( $basicData['ID'], (isset($postData['ihc_assign_user_levels'])) ? $postData['ihc_assign_user_levels'] : '' );

        // update level expire
        $postData['uid'] = isset( $basicData['ID'] ) ? $basicData['ID'] : 0;
        \Indeed\Ihc\UserSubscriptions::updateUserSubscriptionExpireManually( $postData );

        // update subscription meta if it's case
        if ( isset( $postData['subscription_meta'] ) ){
            foreach ( $postData['subscription_meta'] as $subscriptionId => $subscriptionMetaArray ){
                foreach ( $subscriptionMetaArray as $metaKey => $metaValue ){
                    \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, $metaKey, $metaValue );
                }
            }
        }

        // update status if it's case
        if ( isset( $postData['subscription_status'] ) ){
            foreach ( $postData['subscription_status'] as $subscriptionId => $subscriptionStatus ){
                \Indeed\Ihc\UserSubscriptions::updateStatusBySubscriptionId( $subscriptionId, $subscriptionStatus );
            }
        }

        return $basicData['ID'];
    }

    /**
      * @param int
      * @param array
      * @return none
      */
    private function saveUserMetas( $uid=0, $userMeta=[] )
    {
        if ( empty( $uid ) ){
            return false;
        }
        if ( empty( $userMeta ) ){
            return false;
        }

        foreach ( $userMeta as $metaKey => $metaValue ){
            do_action( 'ihc_before_user_save_custom_field', $uid, $metaKey, $metaValue );
            update_user_meta( $uid, $metaKey, $metaValue );
            do_action( 'ihc_user_save_custom_field', $uid, $metaKey, $metaValue );
        }
    }

    /**
     * @param array
     * @return none
     */
    private function checkUsername( $postData=[] )
    {
        $username = (isset($postData['user_login'])) ? $postData['user_login'] : '';

        if ( !empty( $postData['ID'] ) ){
            return;
        }
        if ( !validate_username( $username ) ){
            $this->errors['user_login'] = get_option( 'ihc_register_error_username_msg' );
        }
        if ( username_exists( $username ) ){
            $this->errors['user_login'] = get_option( 'ihc_register_error_username_msg' );
        }
    }

    /**
     * @param array
     * @return none
     */
    protected function checkEmail( $postData=[] )
    {
        if ( !is_email( $postData['user_email'] ) ){
            $this->errors['user_email'] = get_option( 'ihc_register_invalid_email_msg' );
        }
        if ( isset( $postData['confirm_email'] ) ){
            if ( $postData['confirm_email'] != $postData['user_email'] ){
                $this->errors['user_email'] = get_option( 'ihc_register_emails_not_match_msg' );
            }
        }
        if ( email_exists( $postData['user_email'] ) ){
            if ( !empty( $postData['ID'] ) && email_exists( $postData['user_email'] ) != $postData['ID'] ){
                $this->errors['user_email'] = get_option( 'ihc_register_email_is_taken_msg' );
            }
        }
        $blacklist = get_option('ihc_email_blacklist');
        if ( $blacklist !== '' && $blacklist !== false ){
            $blacklist = explode( ',', preg_replace( '/\s+/', '', $blacklist ) );

            if ( count( $blacklist ) > 0 && in_array( $postData['user_email'], $blacklist ) ){
                $this->errors['user_email'] = get_option( 'ihc_register_email_is_taken_msg' );
            }
        }

        $errors = empty( $this->errors['user_email'] ) ? false : $this->errors['user_email'];
        $errors = apply_filters( 'ump_filter_public_check_email_message', $errors, $postData['user_email'] );
        if ( $errors !== false ){
            $this->errors['user_email'] = $errors;
        }
    }


    /**
      * @param none
      * @return string
      */
    public function getUserData()
    {
        global $wpdb;
        if ( !$this->uid ){
            return [];
        }
        $returnData = [];
        $data = get_userdata($this->uid);
				$user_fields = ihc_get_user_reg_fields();
				if ($data){
					foreach ($user_fields as $user_field){
						$name = $user_field['name'];
						if ($user_field['native_wp']==1){
							//native wp field, get value from get_userdata ( $data object )
							if (isset($data->$name) && $data->$name){
								$returnData[ $name ] = $data->$name;
							}
						} else {
							//custom field, get value from get_user_meta()
							$returnData[ $name ] = get_user_meta($this->uid, $name, true);
						}
					}
				}
        $returnData['user_registered'] = isset( $data->user_registered ) ? $data->user_registered : '';
        $returnData['ihc_user_custom_banner_src'] = get_user_meta( $this->uid, 'ihc_user_custom_banner_src', true );
        return $returnData;
    }

}
