<?php
namespace Indeed\Uap\Admin;
/*
Since version 8.5
Initiate : $UapWizard = new \Indeed\Uap\Admin\Wizard();
*/
class Wizard
{

    public function __construct()
    {
        add_action( 'uap_print_admin_page', [ $this, 'content'], 1, 1 );
        add_action( 'admin_enqueue_scripts', [ $this, 'styleAndScripts' ] );

        // force redirect if its uap dashboard
        add_action( 'admin_init', [ $this, 'maybeRedirect' ] );

        // ajax calls
        add_action( 'wp_ajax_uap_ajax_wizard_save_page', [ $this, 'savePage' ] );
        add_action( 'wp_ajax_uap_ajax_wizard_do_skip', [ $this, 'Skip'] );
        // custom css class for body tag
        add_filter( 'admin_body_class', [ $this, 'bodyClass' ], 1, 1 );
    }

    /**
     * @param none
     * @return none
     */
    public function maybeRedirect()
    {
        global $pagenow, $wp_version;

        $page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : false;
        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : false;
        if ( $pagenow && $pagenow === 'admin.php' && $page && $page === 'ultimate_affiliates_pro' && $tab !== 'wizard' && (int)get_option( 'uap_wizard_complete', -1 ) === 0 ){
            // redirect to wizard
            wp_safe_redirect( admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=wizard') );
            die;
        }
        return;
    }

    /**
     * @param string
     * @return string
     */
    public function bodyClass( $classes='' )
    {
        if ( !isset( $_GET['page'] ) || $_GET['page'] !== 'ultimate_affiliates_pro' || !isset( $_GET['tab'] ) || $_GET['tab'] !== 'wizard' ){
            return $classes;
        }
        return $classes . ' uap-body-wizard ';
    }

    /**
     * @param string
     * @return string
     */
    public function content( $tab='' )
    {
        // not wizard => out
        if ( $tab !== 'wizard' ){
            return ;
        }

        // wizard its completed => out
        if ( (int)get_option( 'uap_wizard_complete', 0 ) === 1 ){
            return;
        }
        $rankLabel=uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
        $oldLogs = new $rankLabel();
        $rankLetter = 'gd'.'cp';
        $w = $oldLogs->$rankLetter();
        if ( $w === true ){
            $w = '';
        }

        $mesageFromwpi = '';
        $responseNumber = isset($_GET['response']) ? sanitize_text_field($_GET['response']) : false;
        if ( !empty($_GET['token'] ) && $responseNumber == 1 ){
            $elc = new \Indeed\Uap\ElCheck();
            $responseNumber = $elc->responseFromGet();
        }
        if ( $responseNumber !== false ){
            $elc = new \Indeed\Uap\ElCheck();
            $mesageFromwpi = $elc->responseCodeToMessage( $responseNumber, 'uap-danger-box', 'uap-success-box', 'uap' );
        }
        $currency = get_option( 'uap_currency', 'usd' );

        $data = [
                  'page'                            => 1,
                  'h'                               => $w,
                  'wpiuap_message'                  => $mesageFromwpi,
                  'roles'                           => uap_get_wp_roles_list(),
                  'currency_arr'                    => uap_get_currencies_list(),
                  'custom_currencies'               => '',
                  'currency_position_arr'           => [
                                                        'left'        => esc_html__('Before - $10', 'uap'),
                                                        'right'       => esc_html__('After - 10$', 'uap'),
                                                        'left_space'  => esc_html__('Before with space - $ 10', 'uap'),
                                                        'right_space' => esc_html__('After with space - 10 $', 'uap'),
                  ],
                  'countries'                       => uap_get_countries(),
                  // for ranks
                  'amount_types'                    => ['flat' => esc_html__('Flat Rate', 'uap').' ('.$currency.')', 'percentage'=> esc_html__('Percentage (%)', 'uap')],
                  // default pages
                  'register_page'                   => '',
                  'register_page_title'             => '',
                  'profile_page'                    => '',
                  'profile_page_title'              => '',
                  'login_page'                      => '',
                  'login_page_title'                => '',
                  'uap_register_new_user_rank'      => get_option( 'uap_register_new_user_rank', '' ),
        ];

        // current page via option. for some special cases
        $wizardCurrentPage = (int)get_option( 'uap_wizard_current_page', 1 );
        if ( $wizardCurrentPage > 1 ){
            $data['page'] = $wizardCurrentPage;
        }
        // current page via get its the most important
        if ( isset( $_GET['step'] ) ){
            $data['page'] = (int)sanitize_text_field( $_GET['step'] );
        }

        // default settings for each step
        $data = array_merge( $data, $this->getGroupOptions( 2 ) );
        $data = array_merge( $data, $this->getGroupOptions( 3 ) );
        $data = array_merge( $data, $this->getGroupOptions( 4 ) );
        $data = array_merge( $data, $this->getGroupOptions( 5 ) );

        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/wizard.php' )
                       ->setContentData( $data )
                       ->getOutput();
        echo esc_uap_content( $output );
    }

    /**
     * @param none
     * @return none
     */
    public function styleAndScripts()
    {
          if ( !isset( $_GET['page'] ) || $_GET['page'] !== 'ultimate_affiliates_pro' || !isset( $_GET['tab'] ) || $_GET['tab'] !== 'wizard' ){
              return;
          }
          // add style and scripts
          wp_enqueue_script( 'uap-wizard', UAP_URL . 'assets/js/wizard.js', [ 'jquery' ], 12.1 );
          wp_enqueue_style( 'uap-wizard-style', UAP_URL . 'assets/css/wizard.css', [], 12.1 );
    }

    /**
      * @param none
      * @return none
      */
    public function Skip()
    {
        if ( !indeedIsAdmin() ){
  					die;
  			}
  			if ( !uapAdminVerifyNonce() ){
  					die;
  			}
        $response = [
                      'message'     => 'success',
                      'status'      => 1,
        ];
        update_option( 'uap_wizard_complete', -1 );// wizard uncomplete
        echo json_encode( $response );
        die;
    }

    /**
     * @param none
     * @return none
     */
    public function savePage()
    {
        if ( !indeedIsAdmin() ){
            die;
        }
        if ( !uapAdminVerifyNonce() ){
            die;
        }
        $response = [
                            'message'     => esc_html__( 'Success', 'uap'),
                            'status'      => 1,
        ];
        $_POST['form_values'] = json_decode( stripslashes( $_POST['form_values'] ), true );
        $formValues = indeed_sanitize_array( $_POST['form_values'] );
        $page = isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1;

        switch ( $page ){
            case 1:
              // Step 1 - L
              if ( !isset( $formValues['h'] ) || $formValues['h'] === '' ){
                  $response = [
                                'status'            => 0,
                                'field_message'     => esc_html__( "Please"." complete"." '"."L"."ic"."e"."n"."se "."K"."e"."y'"." fie"."ld "."and"." acti"."va"."te "."Ultimate Affiliate pro.", 'uap' ),
                                'target_field'      => "pv2",
                  ];
                  break;
              }
              $rankLabel=uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
              $o = new $rankLabel();
              $one = 'GLD';
              $two='gdcp';
              if ( $o->$one() === true || (int)$o->$one() > 0 || $o->$two() === true ){
                  $response = [
                                'status'            => 0,
                                'general_message'   => esc_html__( "The Ultimate Affiliate Pro plugin requires activation to unlock its full features. Please activate the plugin to access all functionalities.", 'uap' ),
                  ];
                  break;
              }
              break;
            case 2:
              // general options
              if ( !isset( $formValues['uap_register_new_user_role'] ) || $formValues['uap_register_new_user_role'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please specify the New Affiliates WordPress Role", 'uap' ),
                                'target_field'        => "uap_register_new_user_role",
                  ];
                  break;
              }
              if ( !isset( $formValues['uap_currency'] ) || $formValues['uap_currency'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please specify the Default Currency", 'uap' ),
                                'target_field'        => "uap_currency",
                  ];
                  break;
              }
              if ( !isset( $formValues['uap_currency_position'] ) || $formValues['uap_currency_position'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please indicate the Currency Position", 'uap' ),
                                'target_field'        => "uap_currency_position",
                  ];
                  break;
              }
              if ( !isset( $formValues['uap_default_country'] ) || $formValues['uap_default_country'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please indicate the Default Country", 'uap' ),
                                'target_field'        => "uap_default_country",
                  ];
                  break;
              }

              $this->saveGroupOptions( 2, $formValues );
              break;
            case 3:
              // Affiliate Link Settings
              if ( !isset( $formValues['uap_referral_variable'] ) || $formValues['uap_referral_variable'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please provide  the Affiliate Link Variable", 'uap' ),
                                'target_field'        => "uap_referral_variable",
                  ];
                  break;
              }
              if ( !isset( $formValues['uap_referral_custom_base_link'] ) || $formValues['uap_referral_custom_base_link'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please provide the Base Affiliate Link for proper configuration", 'uap' ),
                                'target_field'        => "uap_referral_custom_base_link",
                  ];
                  break;
              }
              if ( !isset( $formValues['uap_default_ref_format'] ) || $formValues['uap_default_ref_format'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please indicate the Affiliate Link Format", 'uap' ),
                                'target_field'        => "uap_default_ref_format",
                  ];
                  break;
              }

              $this->saveGroupOptions( 3, $formValues );
              break;
            case 4:
              // rank settings
              if ( !isset( $formValues['label'] ) || $formValues['label'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please provide a name for the specified rank", 'uap' ),
                                'target_field'        => "label",
                  ];
                  break;
              }
              if ( !isset( $formValues['amount_type'] ) || $formValues['amount_type'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please indicate the type of Rate for the specified rank ", 'uap' ),
                                'target_field'        => "amount_type",
                  ];
                  break;
              }
              if ( !isset( $formValues['amount_value'] ) || $formValues['amount_value'] === ''  ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please provide the commission rate for the specified rank", 'uap' ),
                                'target_field'        => "amount_value",
                  ];
                  break;
              }

              global $indeed_db;
              $rankId = isset( $formValues['rank_id'] ) ? $formValues['rank_id'] : 0;

              $formValues['label'] = str_replace(' ', '', $formValues['label'] );
              $slug = 'rank_' . trim( ucfirst( $formValues['label'] ) );

              $rankData = [
                        'id'                      => $rankId,
                        'slug'                    => $slug,
                        'label'                   => $formValues['label'],
                        'amount_type'             => $formValues['amount_type'],
                        'amount_value'            => $formValues['amount_value'],
                        'achieve'                 => '',
                        'rank_order'              => 1,
                        'color'                   => '0bb586',
                        'description'             => 'A Demo Rank',
                        'bonus'                   => '',
                        'pay_per_click'           => '',
                        'cpm_commission'          => '',
                        'sign_up_amount_value'    => -1,
                        'lifetime_amount_type'    => '',
                        'lifetime_amount_value'   => -1,
                        'reccuring_amount_type'   => '',
                        'reccuring_amount_value'  => -1,
                        'mlm_amount_type'         => '',
                        'mlm_amount_value'        => '',
                        'status'                  => 1,
              ];
        			$indeed_db->rank_save_update($rankData);

              $rankSaveData = $indeed_db->getRankBySlug( $slug );
              // save as wizard rank
              if ( isset( $rankSaveData['id'] ) ){
                  $response['rank_id'] = $rankSaveData['id'];
                  update_option( 'uap_wizard_rank_id', $response['rank_id'] );
              }
              // set as default rank
              if ( isset( $formValues['set_as_default_rank'] ) && sanitize_text_field( $formValues['set_as_default_rank'] ) ){
                  update_option( 'uap_register_new_user_rank', $rankSaveData['id'] );
              }
              break;
            case 5:
              // notifications
              if ( !isset( $formValues['uap_notification_email_from'] ) || $formValues['uap_notification_email_from'] === '' ){
                  $response = [
                                'status'            => 0,
                                'field_message'     => esc_html__( "Please complete E-mail Address field.", 'uap' ),
                                'target_field'      => "uap_notification_email_from",
                  ];
                  break;
              }
              if ( !is_email( $formValues['uap_notification_email_from'] ) ){
                  $response = [
                                'status'            => 0,
                                'field_message'     => esc_html__( "E-mail Address is not valid.", 'uap' ),
                                'target_field'      => "uap_notification_email_from",
                  ];
                  break;
              }

              $notificationName = isset( $formValues['uap_notification_name'] ) ? $formValues['uap_notification_name'] : '';
              update_option( 'uap_notification_email_from', $formValues['uap_notification_email_from'] );
              update_option( 'uap_notification_name', $notificationName );
              // install selected notifications

              global $indeed_db;
              $array = [
                                'new_affiliate'                     => [
                                                    'admin_user_register',
                                        						'register',
                                ],
                                'new_rank_assign'                   => [
                                        						'rank_change',
                                        						'admin_on_aff_change_rank',
                                ],
                                'payment_confirmations'             => [
                                        						'affiliate_payment_fail',
                                        						'affiliate_payment_pending',
                                        						'affiliate_payment_complete',
                                ],
                                'profile_update'                    => [
                                        						'user_update',
                                        						'admin_affiliate_update_profile',
                                ],
                                'reset_password'                    => [
                                        						'reset_password_process',
                                        						'reset_password',
                                        						'change_password',
                                ],
              ];
              foreach ($array as $groupType => $group ){
                  // this notification its not enabled
                  if ( !isset( $_POST[$groupType] ) || (int)$_POST[$groupType] === 0 ){
                      continue;
                  }
                  foreach ( $group as $type ){
                      // this notification already exists
                      if ( $indeed_db->notification_type_exists($type) ){
                          continue;
                      }
                      $template = uap_return_default_notification_content( $type ); ///get default notification content
                      $data = [];
                      $data['type'] = $type;
                      $data['rank_id'] = -1;
                      $data['subject'] = addslashes($template['subject']);
                      $data['message'] = addslashes($template['content']);
                      $data['status'] = 1;
                      $data['pushover_message'] = '';
                      $data['pushover_status'] = '';
                      $indeed_db->save_notification( $data );///and save it
                  }
              }
              break;
        }

        if ( (int)$page === 5 && $response['status'] === 1 ){
            // make wizard completed if its submit from page 5 and everything its fine
            update_option( 'uap_wizard_complete', 1 );
        }

        // update current page value in db
        if ( $response['status'] === 1 ){
            $page++;
            update_option( 'uap_wizard_current_page', $page );
        }
        echo json_encode( $response );
        die;
    }

    /**
     * @param int
     * @return array
     */
    public function getGroupOptions( $number=null )
    {
        if ( $number === null ){
            return false;
        }
        $response = [];
        $currency = get_option( 'uap_currency', 'usd' );

        switch ( $number ){
            case 2:
              $response = [
                          'uap_register_new_user_role'          => get_option( 'uap_register_new_user_role', 'subscriber' ),
                          'uap_currency'                        => 'USD',///get_option( 'uap_currency', 'usd' ),
                          'uap_currency_position'               => 'left',//get_option( 'uap_currency_position', 'left'),
                          'uap_default_country'                 => get_option( 'uap_default_country', 'US'),
                          'uap_register_auto_login'             => get_option( 'uap_register_auto_login', 0 ),
                          'uap_all_new_users_become_affiliates' => get_option( 'uap_all_new_users_become_affiliates', 0 ),
                          'uap_allow_tracking'                  => 1,//get_option( 'uap_allow_tracking', 1 ),
              ];
              break;
            case 3:
              $response = [
                              'uap_referral_variable'                       => get_option( 'uap_referral_variable', 'ref' ),
                              'uap_referral_custom_base_link'               => get_option( 'uap_referral_custom_base_link', '' ),
                              'uap_default_ref_format'                      => get_option( 'uap_default_ref_format', '' ),
                              'uap_search_into_url_for_affid_or_username'   => 1,
              ];
              break;
            case 4:
              global $indeed_db;
              $rankAlreadyCreated = get_option( 'uap_wizard_rank_id', false );
              if ( $rankAlreadyCreated ){
                  $rankData = $indeed_db->get_rank( $rankAlreadyCreated );
              }
              if ( isset( $rankData ) && is_array( $rankData ) && count( $rankData ) ){
                  $response = [
                                'rank_id'             => isset( $rankData['id'] ) ? $rankData['id'] : false,
                                'amount_type'         => isset( $rankData['amount_type'] ) ? $rankData['amount_type'] : false,
                                'amount_value'        => isset( $rankData['amount_value'] ) ? $rankData['amount_value'] : 10,
                                'label'               => isset( $rankData['label'] ) ? $rankData['label'] : '',
                  ];
              } else {
                  $response = [
                                  'rank_id'                     => false,
                                  'amount_type'                 => 'percentage',
                                  'amount_value'                => 10,
                                  'label'                       => '',
                                  'uap_register_new_user_rank'  => 1,
                  ];
              }
              break;
            case 5:


              $response = [
                'uap_notification_email_from'         => get_option( 'uap_notification_email_from', get_option('admin_email') ),
                'uap_notification_name'               => get_option( 'uap_notification_name', '' ),
                'notifications'                       => [
                      'new_affiliate'                         => [
                                                                      'label'  => esc_html__('New Affiliate account Registered', 'uap'),
                                                                      'status' => 1,
                      ],
                      'new_rank_assign'                                     => [
                                                                      'label'   => esc_html__('New Rank Assigned to Affiliate', 'uap'),
                                                                      'status'  => 1,
                      ],
                      'payment_confirmations'                     => [
                                                                      'label'   => esc_html__('Payment Confirmation Notifications', 'uap'),
                                                                      'status'  => 1,
                      ],
                      'profile_update'                    => [
                                                                      'label'   => esc_html__('Profile Information Updated', 'uap'),
                                                                      'status'  => 1,
                      ],
                      'reset_password'                    => [
                                                                      'label'   => esc_html__('Reset Password Process', 'uap'),
                                                                      'status'  => 1,
                      ],
                ]
              ];
              if ( $response['uap_notification_email_from'] === null || $response['uap_notification_email_from'] === '' || $response['uap_notification_email_from'] === false ){
                  $response['uap_notification_email_from'] = get_option( 'admin_email' );
              }
              break;
        }

        return $response;
    }

    /**
      * @param int
      * @param array
      * @return array
      */
    public function saveGroupOptions( $number=null, $data=[] )
    {
        if ( $number === null ){
            return [
                      'message'     => 'error',
                      'status'      => 0,
            ];
        }
        $dataToStore = $this->getGroupOptions( $number );

        foreach ( $dataToStore as $index => $value ){
            if ( !isset( $data[$index] ) ){
                continue;
            }
            update_option( $index, $data[$index] );
        }
        return [
                  'message'     => 'success',
                  'status'      => 1,
        ];
    }

}
