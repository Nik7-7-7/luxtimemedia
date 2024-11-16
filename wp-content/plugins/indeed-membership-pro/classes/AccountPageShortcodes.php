<?php
namespace Indeed\Ihc;
/*
 * @since version 9.5.2
 */

class AccountPageShortcodes
{

    /**
     * @var array
     */
    private $settings       = [];

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_shortcode( 'ihc-account-page-overview', [ $this, 'overviewPage' ] );
        add_shortcode( 'ihc-account-page-subscriptions-table', [ $this, 'subscriptionTable' ] );
        add_shortcode( 'ihc-account-page-pushover-form', [ $this, 'pushoverNotificationsForm' ] );
        add_shortcode( 'ihc-edit-profile-form', [ $this, 'editAccountPage' ] );
        add_shortcode( 'ihc-account-page-orders-table', [ $this, 'ordersTable' ] );

        add_shortcode( 'ihc-social-links-profile', [ $this, 'socialShareBttn'] );

        add_shortcode( 'ihc-user-sites-add-new-form', [ $this, 'userSitesAddNewForm' ] );
        add_shortcode( 'ihc-user-sites-table', [ $this, 'listUserSitesTable' ] );

        add_shortcode( 'ihc-user-banner', [ $this, 'userBanner' ] );

        // deprecated since version 11.3
        //add_shortcode( 'ihc-change-password-form', [ $this, 'ChangePasswordPage' ] );
    }

    /**
     * @param none
     * @return string
     */
    public function overviewPage()
    {
        global $current_user;
        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( !$uid ){
            return '';
        }
        $this->setSettings();
  			$data['content'] = '';
  			$data['title'] = '';
  			$post_overview = get_user_meta( $uid, 'ihc_overview_post', true);
  			if ($post_overview && $post_overview!=-1){
  				//print the post for user
  				$post = get_post($post_overview);
  				if (!empty($post) && !empty($post->post_content)){
  					$data['content'] = $post->post_content;
  				}
  			}
        if($data['content'] == ''){
  				//predifined message
  				$this->settings['ihc_ap_overview_msg'] = ihc_format_str_like_wp( $this->settings['ihc_ap_overview_msg'] );
  				$this->settings['ihc_ap_overview_msg'] = ihc_correct_text( $this->settings['ihc_ap_overview_msg'] );
  				$data['content'] = $this->settings['ihc_ap_overview_msg'];
  			}
  			$data['content'] = ihc_replace_constants($data['content'], $uid );
  			$data['title'] = (isset($this->settings['ihc_ap_overview_title'])) ? ihc_replace_constants( $this->settings['ihc_ap_overview_title'], $uid ) : '';
        $data['content'] = stripslashes($data['content']);
  			$data['title'] = stripslashes($data['title']);

  			$fullPath = IHC_PATH . 'public/views/account_page-overview.php';
  			$searchFilename = 'account_page-overview.php';
  			$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }

    /**
     * @param none
     * @return string
     */
    public function editAccountPage()
    {
        global $current_user, $ihc_error_register;
        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( !$uid ){
            return '';
        }
        $this->setSettings();

        $template = get_option('ihc_register_template');
        $custom_css = get_option('ihc_register_custom_css');
        if(get_option('ihc_profile_form_template')!==FALSE){
          $template = get_option('ihc_profile_form_template');
        }
        if(get_option('ihc_profile_form_custom_css')!==FALSE){
          $custom_css = get_option('ihc_profile_form_custom_css');
        }
        $data['template'] = $template;
  			$data['style'] = $custom_css;
  			$data['style'] = stripslashes($data['style']);
        $data['uid']  = $uid;

        /// create form

        // Profile Form - new implementation starting with 11.0
        $ProfileForm = new \Indeed\Ihc\ProfileForm();// ussing ProfileForm class since version 11.0
        $form = $ProfileForm->setUid()
                            ->setFields()
                            ->setUserData()
                            ->setTemplate()
                            ->form();
        $data['form'] = apply_filters('ihc_update_profile_form_html', $form );
        // end of Profile Form


        $template = IHC_PATH . 'public/views/edit-profile-form.php';
  			$searchFilename = 'edit-profile-form.php';
  			$template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        $output = $view->setTemplate( $template )
                       ->setContentData( $data )
                       ->getOutput();
        return apply_filters( 'ihc_filter_the_profile_form_output', $output );
    }

    /**
     * @param none
     * @return string
     */
    /*
    // deprecated since version 11.3
    public function ChangePasswordPage()
    {
        global $current_user, $ihc_error_register;
        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( !$uid ){
            return '';
        }
        $this->setSettings();

        $template = get_option('ihc_register_template');
        $custom_css = get_option('ihc_register_custom_css');
        if(get_option('ihc_profile_form_template')!==FALSE){
          $template = get_option('ihc_profile_form_template');
        }
        if(get_option('ihc_profile_form_custom_css')!==FALSE){
          $custom_css = get_option('ihc_profile_form_custom_css');
        }
        $data['template'] = $template;
  			$data['style'] = $custom_css;
  			$data['style'] = stripslashes($data['style']);
        $data['uid']  = $uid;

  			$template = IHC_PATH . 'public/views/change-password-form.php';
        $searchFilename = 'change-password-form.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }
    */

    /**
     * @param none
     * @return string
     */
    public function subscriptionTable()
    {
        global $current_user, $wpdb;
        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( !$uid ){
            return '';
        }
        $this->setSettings();

        $data['uid'] = $uid;

  			$data['show_table'] = 1;
  			if (isset($this->settings['ihc_ap_subscription_table_enable']) && $this->settings['ihc_ap_subscription_table_enable']==0){
  				$data['show_table'] = 0;
  			}

  			if ( isset($this->settings['ihc_ap_subscription_plan_enable']) && $this->settings['ihc_ap_subscription_plan_enable'] == 0 ){
  				$data['show_subscription_plan'] = 0;

  				if (isset($_GET['ihc_success_bt'])){
  					/// BT PAYMENT
  					add_filter('the_content', 'ihc_filter_print_bank_transfer_message', 79, 1);
  				}
  				/// subscription plan check stuff

  			}

  			$data['subscriptions'] = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid );
        $data['settings'] = ihc_return_meta_arr('manage_subscription_table');//getting metas


  			$fields = get_option('ihc_user_fields');
  			////PRINT SELECT PAYMENT
  			$key = ihc_array_value_exists($fields, 'payment_select', 'name');
  			$print_payment_select = (empty($fields[$key]['display_public_ap'])) ? FALSE : TRUE;
  			///INCLUDE STRIPE JS SCRIPT?
  			if (in_array('stripe', ihc_get_active_payments_services(TRUE)) && $print_payment_select){
  				$include_stripe = TRUE;
  			}

  			$template = IHC_PATH . 'public/views/subscription-table.php';
        $searchFilename = 'subscription-table.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }

    /**
     * @param none
     * @return string
     */
    public function pushoverNotificationsForm()
    {
        global $current_user;
  			$uid = empty($current_user->ID) ? 0 : $current_user->ID;

        if ( !$uid ){
            return '';
        }
        $this->setSettings();

  			if (!empty($_POST['ihc_pushover_token']) && !empty( $_POST['ihc_pushover_nonce'] ) && wp_verify_nonce( (isset($_POST['ihc_pushover_nonce'])) ? sanitize_text_field( $_POST['ihc_pushover_nonce'] ) : '', 'ihc_pushover_nonce' ) ){
    				update_user_meta( $uid, 'ihc_pushover_token', sanitize_text_field( $_POST['ihc_pushover_token'] ) );
  			}
  			$data['ihc_pushover_token'] = get_user_meta($uid, 'ihc_pushover_token', true );

  			$template = IHC_PATH . 'public/views/pushover-form.php';
        $searchFilename = 'pushover-form.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                  ->setContentData( $data )
                  ->getOutput();
    }

    /**
     * @param none
     * @return string
     */
    public function ordersTable()
    {
        global $current_user;
        $uid = empty($current_user->ID) ? 0 : $current_user->ID;

        if ( !$uid ){
            return '';
        }
        $this->setSettings();
        $baseUrl = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $data['total_items'] = \Ihc_Db::get_count_orders( $uid );
        if ($data['total_items']){
          $url = add_query_arg('ihc_ap_menu', 'orders', $baseUrl);
          $url = apply_filters( 'ihc_filter_public_account_page_tab_link', $url, $baseUrl, 'orders' );

          $limit = 25;
          $current_page = (empty($_GET['ihcp'])) ? 1 : sanitize_text_field($_GET['ihcp']);
          if ($current_page>1){
            $offset = ( $current_page - 1 ) * $limit;
          } else {
            $offset = 0;
          }
          if ($offset + $limit>$data['total_items']){
            $limit = $data['total_items'] - $offset;
          }
          include_once IHC_PATH . 'classes/Ihc_Pagination.class.php';
          $pagination = new \Ihc_Pagination(array(
                              'base_url'        => $baseUrl,
                              'param_name'      => 'ihcp',
                              'total_items'     => $data['total_items'],
                              'items_per_page'  => $limit,
                              'current_page'    => $current_page,
          ));
          $data['pagination'] = $pagination->output();
          $data['orders'] = \Ihc_Db::get_all_order($limit, $offset, $uid );
        }

        $accountPage = get_option( 'ihc_general_user_page' );
        $accountPage = get_permalink( $accountPage );
        if ( $accountPage == '' ){
            $accountPage = $url;
        }

        $data['show_invoices'] = (ihc_is_magic_feat_active('invoices')) ? TRUE : FALSE;
        $data['show_only_completed_invoices'] = get_option('ihc_invoices_only_completed_payments');
        $data['subscription_link'] =  add_query_arg('ihc_ap_menu', 'subscription', $accountPage );
        $data['subscription_link'] = apply_filters( 'ihc_filter_public_account_page_tab_link', $data['subscription_link'], $accountPage, 'subscription' );

        $data['payment_types'] = ihc_list_all_payments();

        $data['settings'] = ihc_return_meta_arr('manage_order_table');//getting metas

        $template = IHC_PATH . 'public/views/orders-table.php';
        $searchFilename = 'orders-table.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }

    /**
     * @param none
     * @return string
     */
    public function userSitesAddNewForm()
    {
      global $current_user;
      $uid = empty($current_user->ID) ? 0 : $current_user->ID;
      if ( !$uid ){
          return '';
      }
      $this->setSettings();

      $data = [
                'lid'               => isset( $_GET['lid'] ) ? sanitize_text_field($_GET['lid']) : '',
                'uid_levels'        => \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, false ),
                'levels_can_do'     => get_option( 'ihc_user_sites_levels' ),
      ];

      if (!empty($_POST['add_new_site']) && isset($_POST['lid'])
            && isset($_POST['ihc_multi_site_add_edit_nonce'])
            && wp_verify_nonce( sanitize_text_field($_POST['ihc_multi_site_add_edit_nonce']), 'ihc_multi_site_add_edit_nonce' ) ) {
        $lid = sanitize_text_field( $_POST['lid']);

        if (isset($data['uid_levels'][$lid]) && !empty($data['levels_can_do'][$lid])){
          if (\Ihc_Db::get_user_site_for_uid_lid($current_user->ID, $lid)==0){
            require_once IHC_PATH . 'classes/IhcUserSite.class.php';
            $IhcUserSite = new \IhcUserSite();
            $IhcUserSite->setUid($current_user->ID);
            $IhcUserSite->setLid($lid);
            if ( $IhcUserSite->save_site( indeed_sanitize_array($_POST) ) ){
              $IhcUserSite->saveUidLidRelation();
              $data['success'] = true;
            } else {
              $data['error'] = $IhcUserSite->get_error();
            }
          }
        }
      }

      $template = IHC_PATH . 'public/views/user-sites-add-new-form.php';
      $searchFilename = 'user-sites-add-new-form.php';
      $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

      $view = new \Indeed\Ihc\IndeedView();
      return $view->setTemplate( $template )
                  ->setContentData( $data )
                  ->getOutput();
    }

    /**
     * You can add the following attributes on shortcode: add_new_url ( url to add new site page )
     * @param array
     * @return string
     */
    public function listUserSitesTable( $attr=[] )
    {
        global $current_user;
        $uid = empty($current_user->ID) ? 0 : $current_user->ID;
        if ( !$uid ){
            return '';
        }
        $this->setSettings();

        if ( isset( $attr['add_new_url'] ) ){
            $data['add_new'] = $attr['add_new_url'];
        } else {
            $accountPage = get_option('ihc_general_user_page');
            $baseUrl = get_permalink( $accountPage );
            $data['add_new'] = add_query_arg( 'ihc_ap_menu', 'user_sites_add_new', $baseUrl );
            $data['add_new'] = apply_filters( 'ihc_filter_public_account_page_tab_link', $data['add_new'], $baseUrl, 'user_sites_add_new' );
        }

        $data['uid_levels'] = \Indeed\Ihc\UserSubscriptions::getAllForUser( $current_user->ID, false );
				$data['levels_can_do'] = get_option('ihc_user_sites_levels');

				if (!empty($data['uid_levels'])){
					if (!empty($data['levels_can_do'] )){
						foreach ($data['uid_levels'] as $lid=>$array){
							if (empty($data['levels_can_do'][$lid])){
								unset($data['uid_levels'][$lid]);
							}
						}
					}
				}

        $template = IHC_PATH . 'public/views/user-sites-table.php';
        $searchFilename = 'user-sites-table.php';
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
    public function userBanner( $attr=[] )
    {
        global $current_user;
        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( !$uid ){
            return '';
        }

        $data = [
                  'banner'    => get_user_meta( $uid, 'ihc_user_custom_banner_src', true ),
                  'width'     => isset( $attr['width'] ) ? $attr['width'] : '100%',
                  'height'    => isset( $attr['height'] ) ? $attr['height'] : '300px',
        ];

        if ( !$data['banner'] ){
            $data['banner'] = get_user_meta( $uid, 'ihc_ap_top_background_image', true );
        }

        if ( !$data['banner'] ){
            $data['banner'] = ihcDefaultBannerImage();
        }

        $template = IHC_PATH . 'public/views/user-banner.php';
        $searchFilename = 'user-banner.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }

    public function socialShareBttn()
    {
        global $current_user;
        $uid = isset( $current_user->ID ) ? $current_user->ID : 0;
        if ( !$uid ){
            return '';
        }
        $data = [];
        $data['users_sm'] = array();
        $socialTypes = array(
  					"fb" => "Facebook",
  					"tw" => "Twitter",
  					"in" => "LinkedIn",
  					"goo" => "Google",
  					"vk" => "Vkontakte",
  					"ig" => "Instagram",
  					"tbr" => "Tumblr"
  			);
  			foreach ($socialTypes as $k=>$v){
  				$social = get_user_meta( $uid, 'ihc_' . $k, true );
  				if (!empty($social)){
  					$data['users_sm'] = $k;
  				}
  			}

        $template = IHC_PATH . 'public/views/social-share.php';
        $searchFilename = 'social-share.php';
        $template = apply_filters('ihc_filter_on_load_template', $template, $searchFilename );

        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate( $template )
                    ->setContentData( $data )
                    ->getOutput();
    }

    /**
     * @param none
     * @return none
     */
    public function setSettings( $settings=[] )
    {
        if ( $settings ){
            $this->settings = $settings;
        } else {
            $this->settings = ihc_return_meta_arr('account_page');
        }
    }

}
