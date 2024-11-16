<?php
namespace Indeed\Ihc\Admin;
/*
Since version 12.1
Initiate : $iumpWizard = new \Indeed\Ihc\Admin\Wizard();
*/
class Wizard
{

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'ump_print_admin_page', [ $this, 'content'], 1, 1 );

        // ajax calls
        add_action( 'wp_ajax_ihc_ajax_wizard_save_page', [ $this, 'savePage' ] );

        // skip wizard ajax
        add_action( 'wp_ajax_ihc_ajax_wizard_do_skip', [ $this, 'doSkip' ] );

        // force redirect if its ump dashboard
        add_action( 'admin_init', [ $this, 'maybeRedirect' ] );
        // css and js
        add_action( 'admin_enqueue_scripts', [ $this, 'styleAndScripts' ] );
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
        if ( $pagenow && $pagenow === 'admin.php' && $page && $page === 'ihc_manage' && $tab !== 'wizard' && (int)get_option( 'iump_wizard_complete', -1 ) === 0 ){
            // redirect to wizard
            wp_safe_redirect( admin_url( 'admin.php?page=ihc_manage&tab=wizard') );
            die;
        }
        return;
    }

    /**
     * @param none
     * @return none
     */
    public function styleAndScripts()
    {
          if ( !isset( $_GET['page'] ) || $_GET['page'] !== 'ihc_manage' || !isset( $_GET['tab'] ) || $_GET['tab'] !== 'wizard' ){
              return;
          }
          // add style and scripts
          wp_enqueue_style( 'ihc_select2_style' );
          wp_enqueue_script( 'ihc-select2' );
          wp_enqueue_script( 'iump-wizard', IHC_URL . 'admin/assets/js/wizard.js', [ 'jquery' ], '12.8' );
          wp_enqueue_style( 'iump-wizard-style', IHC_URL . 'admin/assets/css/wizard.css', [], '12.8' );
    }

    /**
     * @param string
     * @return string
     */
    public function bodyClass( $classes='' )
    {
        if ( !isset( $_GET['page'] ) || $_GET['page'] !== 'ihc_manage' || !isset( $_GET['tab'] ) || $_GET['tab'] !== 'wizard' ){
            return $classes;
        }
        return $classes . ' iump-body-wizard ';
    }

    /**
     * @param string
     * @return none ( print string )
     */
    public function content( $tab='' )
    {
        if ( $tab !== 'wizard' ){
            return ;
        }
        ////
        if ( (int)get_option( 'iump_wizard_complete', 0 ) === 1 ){
            // wizard its completed
            return;
        }
        ////
        update_option( 'iump_wizard_complete', -2 );

        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $h = $ol_dL_ogs->GCP();
        if ( $h === true ){
            $h = '';
        }
        // ------------------------------------------- license ------------------------------------
        $responseNumber = isset($_GET['response']) ? sanitize_text_field($_GET['response']) : false;
        if ( !empty($_GET['token'] ) && $responseNumber == 1 ){
        		$ElCheck = new \Indeed\Ihc\Services\ElCheck();
        		$responseNumber = $ElCheck->responseFromGet();
        }
        if ( $responseNumber !== false ){
        		$ElCheck = new \Indeed\Ihc\Services\ElCheck();
        		$licenseMessage = $ElCheck->responseCodeToMessage( $responseNumber, 'ihc-danger-box', 'ihc-success-box', 'ihc' );
        }
        // ------------------------------------------- end of license ------------------------------------

        // ------------------------------------------- stripe connect -----------------------------
        if ( isset( $_GET['access_token'] ) && $_GET['access_token'] !== ''	&& isset( $_GET['stripe_publishable_key'] )
          && $_GET['stripe_publishable_key'] !== '' && isset( $_GET['stripe_user_id'] ) && $_GET['stripe_user_id'] !== ''
          && isset( $_GET['code'] ) && $_GET['code'] !== '' && wp_verify_nonce( sanitize_text_field($_GET['code']), 'ihc_stripe_connect_auth' ) ){
              // save the credentials
              if ( $_GET['sandbox'] ){
                  // sandbox
                  update_option( 'ihc_stripe_connect_test_client_secret', sanitize_text_field( $_GET['access_token'] ) );
                  update_option( 'ihc_stripe_connect_test_publishable_key', sanitize_text_field( $_GET['stripe_publishable_key'] ) );
                  update_option( 'ihc_stripe_connect_test_account_id', sanitize_text_field( $_GET['stripe_user_id'] ) );
                  //update_option( 'ihc_stripe_connect_live_mode', 0 );
                  $stripeSandbox = 1;
              } else {
                  // live
                  update_option( 'ihc_stripe_connect_client_secret', sanitize_text_field( $_GET['access_token'] ) );
                  update_option( 'ihc_stripe_connect_publishable_key', sanitize_text_field( $_GET['stripe_publishable_key'] ) );
                  update_option( 'ihc_stripe_connect_account_id', sanitize_text_field( $_GET['stripe_user_id'] ) );
                  //update_option( 'ihc_stripe_connect_live_mode', 1 );
              }

              if ( get_option( 'ihc_stripe_connect_activation_time' ) === false ){
                  update_option( 'ihc_stripe_connect_activation_time', time() );
              }
        }
        // ------------------------------------------- end of stripe connect -----------------------------

        $siteUrl = site_url();
  			$siteUrl = trailingslashit($siteUrl);

        $data = [
                  'page'                                      => 1,
                  'h'                                         => $h,
                  'license_message'                           => isset( $licenseMessage ) ? $licenseMessage : '',
                  'site_url'                                  => $siteUrl,
                  'countries'                                 => ihc_get_countries(),
                  'locale'                                    => iumpGetLocaleList(),
                  'pages'                                     => ihc_get_all_pages(),
                  'currency_arr'                              => ihc_get_currencies_list( 'all' ),
                  'custom_currencies'                         => ihc_get_currencies_list( 'custom' ),
                  // membership
                  'price_types'                               => [
                                                                  'free'          => esc_html__('Free', 'ihc'),
                                                                  'payment'       => esc_html__('Payment', 'ihc')
                  ],
                  'membership'                                => [
                                                                  'label'                     => esc_html__( 'Default Membership', 'ihc' ),
                                                                  'payment_type'              => 'free',
                                                                  'access_type'               =>'unlimited',
                                                                  'access_interval_start'     => date( 'd-m-Y', indeed_get_unixtimestamp_with_timezone() ),
                                                                  'access_interval_end'       => date( 'd-m-Y', strtotime( "+1 month", indeed_get_unixtimestamp_with_timezone() ) ),
                                                                  'access_regular_time_value' => 1,
                                                                  'access_regular_time_type'  => 'M',
                                                                  'access_limited_time_value' => 1,
                                                                  'access_limited_time_type'  => 'M',
                                                                  'price'                     => '',
                                                                  'level_id'                  => '',
                  ],
                  'time_types'                                 => [
                                                                    'D'     =>'Days',
                                                                    'W'     =>'Weeks',
                                                                    'M'     =>'Months',
                                                                    'Y'     =>'Years'
                  ],
                  'access_types'                               => [
                                                                    'unlimited'       => esc_html__( 'LifeTime', 'ihc' ),
                                                                    'limited'         => esc_html__( 'Limited Time', 'ihc' ),
                                                                    'regular_period'  => esc_html__( 'Recurring Subscription', 'ihc' ),
                                                                    'date_interval'   => esc_html__( 'Date Range', 'ihc' ),
                  ],
                  'roles'                                      => ihc_get_wp_roles_list(),
                  'currency_position_arr'                      => [
                                                                    'left'  => esc_html__('Left', 'ihc'),
                                                                    'right' => esc_html__('Right', 'ihc')
                  ],
                  'register_page'                              => '',
                  'profile_page'                               => '',
                  'login_page'                                 => '',
                  'edit_register_page'                         => '',
                  'edit_profile_page'                          => '',
                  'edit_login_page'                            => '',
        ];

        // current page via option. for some special cases
        $wizardCurrentPage = (int)get_option( 'ihc_wizard_current_page', 1 );
        if ( $wizardCurrentPage > 1 ){
            $data['page'] = $wizardCurrentPage;
        }
        // current page via get its the most important
        if ( isset( $_GET['step'] ) ){
            $data['page'] = (int)sanitize_text_field( $_GET['step'] );
        }

        // default pages
        $register = get_option( 'ihc_general_register_default_page', false );
        if ( $register !== false ){
            $data['register_page'] = get_permalink( $register );
            $data['register_page_title'] = get_the_title( $register );
            $data['edit_register_page'] = get_edit_post_link( $register );
        }
        $profile = get_option( 'ihc_general_user_page', false );
        if ( $profile !== false ){
            $data['profile_page'] = get_permalink( $profile );
            $data['profile_page_title'] = get_the_title( $profile );
            $data['edit_profile_page'] = get_edit_post_link( $profile );
        }
        $login = get_option( 'ihc_general_login_default_page', false );
        if ( $login !== false ){
            $data['login_page'] = get_permalink( $login );
            $data['login_page_title'] = get_the_title( $login );
            $data['edit_login_page'] = get_edit_post_link( $login );
        }

        $lidAlreadyExists = get_option( 'iump_wizard_lid', false );
        if ( $lidAlreadyExists ){
            $temporaryLidData = \Indeed\Ihc\Db\Memberships::getOne( $lidAlreadyExists );
            if ( $temporaryLidData !== false && is_array($temporaryLidData) && count( $temporaryLidData ) > 0 && isset( $temporaryLidData['id'] ) ){
                $data['membership'] = $temporaryLidData;
                $data['membership']['level_id'] = $temporaryLidData['id'];
            }
        }
        $data = array_merge( $data, $this->getGroupOptions( 2 ) );
        $data = array_merge( $data, $this->getGroupOptions( 3 ) );
        $data = array_merge( $data, $this->getGroupOptions( 5 ) );

        // workarround for stripe connect
        if ( !empty( $stripeSandbox ) ){
            $data['ihc_stripe_connect_live_mode'] = 0;
        }

        $view = new \Indeed\Ihc\IndeedView();
    		$output = $view->setTemplate( IHC_PATH . 'admin/includes/tabs/wizard.php' )
    							     ->setContentData( $data )
    							     ->getOutput();
        echo esc_ump_content( $output );
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

        switch ( $number ){
            case 2:
                $response = [
                  'ihc_register_new_user_role'                => get_option( 'ihc_register_new_user_role', 'subscriber'),
                  'ihc_default_country'                       => get_option( 'ihc_default_country', 'us' ),
                  'ihc_currency'                              => get_option( 'ihc_currency', 'USD' ),
                  'ihc_currency_position'                     => get_option( 'ihc_currency_position', 'right' ),
                  'ihc_register_auto_login'                   => get_option( 'ihc_register_auto_login', 0 ),
                  'ihc_security_allow_search_engines'         => get_option( 'ihc_security_allow_search_engines', 0 ),
                  'ihc_allow_tracking'                        => 1,//get_option( 'ihc_allow_tracking', 1 ),
                ];
                break;
            case 3:
              $response = [
                // stripe connect
                'ihc_stripe_connect_status'									=> 1,
                'ihc_stripe_connect_payment_request'				=> get_option( 'ihc_stripe_connect_payment_request', 0 ),
                'ihc_stripe_connect_live_mode'							=> 1,//get_option( 'ihc_stripe_connect_live_mode', 1 ),
                'ihc_stripe_connect_publishable_key'				=> get_option( 'ihc_stripe_connect_publishable_key', '' ),
                'ihc_stripe_connect_client_secret'					=> get_option( 'ihc_stripe_connect_client_secret', '' ),
                'ihc_stripe_connect_account_id'							=> get_option( 'ihc_stripe_connect_account_id', '' ),
                'ihc_stripe_connect_test_publishable_key'		=> get_option( 'ihc_stripe_connect_test_publishable_key', '' ),
                'ihc_stripe_connect_test_client_secret'			=> get_option( 'ihc_stripe_connect_test_client_secret', '' ),
                'ihc_stripe_connect_test_account_id'				=> get_option( 'ihc_stripe_connect_test_account_id', '' ),
                'ihc_stripe_connect_label'                  => get_option( 'ihc_stripe_connect_label', 'Credit Card' ),
                // paypal
                'ihc_paypal_status'                         => 0,
                'ihc_paypal_sandbox'                        => get_option( 'ihc_paypal_sandbox', 0 ),
                'ihc_paypal_merchant_account_id'            => get_option( 'ihc_paypal_merchant_account_id', '' ),
                'ihc_paypal_email'                          => get_option( 'ihc_paypal_email', '' ),
                'ihc_paypal_label'                          => get_option( 'ihc_paypal_label', 'PayPal' ),
                // bank transfer
                'ihc_bank_transfer_status'                  => 0,
                'ihc_bank_transfer_message'                 => get_option( 'ihc_bank_transfer_message', '<p>Please proceed the bank transfer payment for: {currency}{amount}</p>

                <p><strong>Payment Details:</strong> Subscription {level_name} for {username} with Identification: {user_id}_{level_id}</p>

                <br/>

                <strong>Bank Details:</strong><br/>

                IBAN:xxxxxxxxxxxxxxxxxxxx<br/>

                Bank NAME<br/>' ),
                'ihc_bank_transfer_label'                   => get_option( 'ihc_bank_transfer_label', 'Bank Transfer' ),
                //
                'ihc_payment_selected'                      => get_option( 'ihc_payment_selected', 'stripe_connect' ),
              ];
              break;
            case 4:
              $response = []; // at step 4 we create the membership
              break;
            case 5:
              $response = [
                'ihc_notification_email_from'         => get_option( 'ihc_notification_email_from', get_option('admin_email') ),
                'ihc_notification_name'               => get_option('ihc_notification_name'),
                'notifications'                       => [
                      'ihc_order_placed_notification-user'          => [
                                                                      'label'  => esc_html__('A new Payment has been created', 'ihc'),
                                                                      'status' => 1,
                      ],
                      'payment'                                     => [
                                                                      'label'   => esc_html__('Payment confirmation has been received', 'ihc'),
                                                                      'status'  => 1,
                      ],
                      'ihc_new_subscription_assign_notification'    => [
                                                                      'label'   => esc_html__('A new subscription has been assigned to the customer', 'ihc'),
                                                                      'status'  => 1,
                      ],
                      'expire'                                      => [
                                                                      'label'   => esc_html__('Notify the customer that his subscription has expired', 'ihc'),
                                                                      'status'  => 1,
                      ],
                      'approve_account'                             => [
                                                                      'label'   => esc_html__('Admin has given his approval to the account', 'ihc'),
                                                                      'status'  => 1,
                      ],
                      'delete_account'                              => [
                                                                      'label'   => esc_html__('The account has been deactivated', 'ihc'),
                                                                      'status'  => 1,
                      ],
                ]
              ];
              if ( $response['ihc_notification_email_from'] === null || $response['ihc_notification_email_from'] === '' || $response['ihc_notification_email_from'] === false ){
                  $response['ihc_notification_email_from'] = get_option( 'admin_email' );
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

    /**
      * @param none
      * @return none
      */
    public function savePage()
    {
        if ( !ihcIsAdmin() ){
            die;
        }
        if ( !ihcAdminVerifyNonce() ){
            die;
        }

        $response = [
                      'message'     => esc_html__( 'Success', 'ihc'),
                      'status'      => 1,
        ];
        $_POST['form_values'] = json_decode( stripslashes( $_POST['form_values'] ), true );
        $formValues = indeed_sanitize_array( $_POST['form_values'] );
        $page = isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1;

        switch ( $page ){
            case 1:
              // Step 1 - License
              if ( !isset( $formValues['pv2'] ) || $formValues['pv2'] === '' ){
                  $response = [
                                'status'            => 0,
                                'field_message'     => esc_html__( "Please complete 'Purchase Code' field and activate Ultimate Membership pro.", 'ihc' ),
                                'target_field'      => "pv2",
                  ];
                  break;
              }
              $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
              $ol_dL_ogs = new $class();
              if ( $ol_dL_ogs->FGCS() === true || (int)$ol_dL_ogs->FGCS() > 0 || $ol_dL_ogs->GCP() === true ){
                  $response = [
                                'status'            => 0,
                                'general_message'   => esc_html__( "Please activate Ultimate Membership pro.", 'ihc' ),
                  ];
                  break;
              }
              break;
            case 2:
              // Step 2 - General Settings
              $this->saveGroupOptions( 2, $formValues );
              break;
            case 3:
                // Step 3 - Payment Gateways

                // ------------ conditions
                // at least one payment gateway must be checked
                if ( ( !isset( $formValues['ihc_stripe_connect_status'] ) || $formValues['ihc_stripe_connect_status'] === '0' ) &&
                     ( !isset( $formValues['ihc_paypal_status'] ) || $formValues['ihc_paypal_status'] === '0' ) &&
                     ( !isset( $formValues['ihc_bank_transfer_status'] ) || $formValues['ihc_bank_transfer_status'] === '0' ) ){
                       $response = [
                                     'general_message'     => esc_html__( 'Please select at least one Payment Service.', 'ihc' ),
                                     'status'              => 0,
                       ];
                       break;
                }

                // stripe connect conditions
                if ( $formValues['ihc_stripe_connect_status'] === '1' ){
                    if ( $formValues['ihc_stripe_connect_live_mode'] === '1' ){
                        // live conditions
                        if ( !isset( $formValues['ihc_stripe_connect_publishable_key'] ) || $formValues['ihc_stripe_connect_publishable_key'] === ''
                             || !isset( $formValues['ihc_stripe_connect_client_secret'] ) || $formValues['ihc_stripe_connect_client_secret'] === ''
                             || !isset( $formValues['ihc_stripe_connect_account_id'] ) || $formValues['ihc_stripe_connect_account_id'] === ''
                        ){
                          $response = [
                                        'field_message'       => esc_html__( "Please complete Setup and connect your Stripe Account.", 'ihc' ),
                                        'status'              => 0,
                                        'target_field'        => 'ihc_stripe_connect_publishable_key',
                          ];
                          break;
                        }
                    } else {
                        // sandbox conditions
                        if ( !isset( $formValues['ihc_stripe_connect_test_publishable_key'] ) || $formValues['ihc_stripe_connect_test_publishable_key'] === ''
                             || !isset( $formValues['ihc_stripe_connect_test_client_secret'] ) || $formValues['ihc_stripe_connect_test_client_secret'] === ''
                             || !isset( $formValues['ihc_stripe_connect_test_account_id'] ) || $formValues['ihc_stripe_connect_test_account_id'] === ''
                        ){
                          $response = [
                                        'field_message'       => esc_html__( "Please complete Setup and connect your Stripe Account.", 'ihc' ),
                                        'status'              => 0,
                                        'target_field'        => 'ihc_stripe_connect_test_publishable_key',
                          ];
                          break;
                        }
                    }
                }
                // paypal conditions
                if ( $formValues['ihc_paypal_status'] === '1' ){
                    if ( !isset( $formValues['ihc_paypal_email'] ) || $formValues['ihc_paypal_email'] === '' ){
                        // no email provided
                        $response = [
                                      'status'              => 0,
                                      'field_message'       => esc_html__( "Please Enter a valid Merchant Email", 'ihc' ),
                                      'target_field'        => 'ihc_paypal_email',
                        ];
                        break;
                    }
                    if ( !isset( $formValues['ihc_paypal_merchant_account_id'] ) || $formValues['ihc_paypal_merchant_account_id'] === '' ){
                        // no merchant id provided
                        $response = [
                                      'status'            => 0,
                                      'field_message'     => esc_html__( "Please Enter a valid Merchant account ID", 'ihc' ),
                                      'target_field'        => 'ihc_paypal_merchant_account_id',
                        ];
                        break;
                    }
                }
                // bank transfer conditions
                if ( $formValues['ihc_bank_transfer_status'] === '1' && ( !isset( $formValues['ihc_bank_transfer_message'] ) || $formValues['ihc_bank_transfer_message'] === '' ) ){
                    $response = [
                                  'status'              => 0,
                                  'field_message'       => esc_html__( 'Please complete Bank Transfer details.', 'ihc' ),
                                  'target_field'        => 'ihc_bank_transfer_message',
                    ];
                    break;
                }
                $this->saveGroupOptions( 3, $formValues );
              break;
            case 4:
              // Step 4 - Membership Plan
              $postData = [
                              'level_id'                      => $formValues['level_id'],
                              'name'                          => strtolower( $formValues['label'] ),
                              'label'                         => $formValues['label'],
                              'access_type'                   => $formValues['access_type'],
                              'price'                         => $formValues['price'],
                              'price_text'                    => '',
                              'description'                   => '',
                              'access_regular_time_type'      => $formValues['access_regular_time_type'],
                              'access_regular_time_value'     => $formValues['access_regular_time_value'],
                              'access_interval_start'         => $formValues['access_interval_start'],
                              'access_interval_end'           => $formValues['access_interval_end'],
                              'access_limited_time_type'      => $formValues['access_limited_time_type'],
                              'access_limited_time_value'     => $formValues['access_limited_time_value'],
              ];

              if ( $postData['label'] === '' ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please Enter a valid Membership Name", 'ihc' ),
                                'target_field'        => "label",
                  ];
                  break;
              }

              // special conditions for each membership access type
              if ( $postData['access_type'] === 'regular_period' && ( !isset( $postData['access_regular_time_value'] ) || $postData['access_regular_time_value'] === '' || $postData['access_regular_time_value'] === '0' ) ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please fill out the Membership Billing Cycle", 'ihc' ),
                                'target_field'        => "access_regular_time_value",
                  ];
                  break;
              } else if ( $postData['access_type'] === 'regular_period' && ( !isset( $postData['price'] ) || $postData['price'] === '' || $postData['price'] === '0' || $postData['price'] === '0.00' ) ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please complete the price of the Membership", 'ihc' ),
                                'target_field'        => "price",
                  ];
                  break;
              } else if ( $postData['access_type'] === 'limited' && ( !isset( $postData['access_limited_time_value'] ) || $postData['access_limited_time_value'] === '' ) ){
                  $response = [
                                'status'              => 0,
                                'field_message'       => esc_html__( "Please specify the Duration of your Membership", 'ihc' ),
                                'target_field'        => "access_limited_time_value",
                  ];
                  break;
              } else if ( $postData['access_type'] === 'date_interval' ){
                      if ( !isset( $postData['access_interval_start'] ) || $postData['access_interval_start'] === ''){
                            $response = [
                                          'status'              => 0,
                                          'field_message'       => esc_html__( "Please specify the Membership's Starting Date", 'ihc' ),
                                          'target_field'        => "access_interval_start",
                            ];
                            break;
                      } else if ( !isset( $postData['access_interval_end'] ) || $postData['access_interval_end'] === '' ){
                            $response = [
                                          'status'              => 0,
                                          'field_message'       => esc_html__( "Please specify the Membership's Expiration Date", 'ihc' ),
                                          'target_field'        => "access_interval_end",
                            ];
                      }
              }
              // end of special conditions for each membership access type

              if ( !isset( $postData['price'] ) || $postData['price'] === '' || $postData['price'] === '0' ){
                  $postData['payment_type'] = 'free';
              } else {
                  $postData['payment_type'] = 'payment';
              }

              $saveResponse = \Indeed\Ihc\Db\Memberships::save( $postData, false );
              if ( isset($saveResponse['success']) && $saveResponse['success'] === true && isset( $saveResponse['id'] ) ){
                  $response['level_id'] = $saveResponse['id'];
                  // save level id into options, in order to prevent multiple creations on memberships on install process in case something went wrong.
                  update_option( 'iump_wizard_lid', $response['level_id'] );
              } else {
                  $response = [
                                'status'              => 0,
                                'field_message'       => isset( $saveResponse['reason'] ) ? $saveResponse['reason'] : esc_html__( 'Something went wrong, try again.', 'ihc' ),
                                'target_field'        => "label",
                  ];
              }
              break;
            case 5:
              // step 5 - notifications
              if ( !isset( $formValues['ihc_notification_email_from'] ) || $formValues['ihc_notification_email_from'] === '' ){
                  $response = [
                                'status'            => 0,
                                'field_message'     => esc_html__( "Please complete 'E-mail Address' field.", 'ihc' ),
                                'target_field'      => "ihc_notification_email_from",
                  ];
                  break;
              }
              $notificationName = isset( $formValues['ihc_notification_name'] ) ? $formValues['ihc_notification_name'] : '';
              update_option( 'ihc_notification_email_from', $formValues['ihc_notification_email_from'] );
              update_option( 'ihc_notification_name', $notificationName );

              // create notifications
              $groupStandardValues = $this->getGroupOptions( 5 );
              $keys = $groupStandardValues['notifications'];
              global $wpdb;
              $table = $wpdb->prefix . "ihc_notifications";
              $notificationObject = new \Indeed\Ihc\Notifications();
              foreach ($keys as $key => $nData ){
                  $selectQuery = $wpdb->prepare("SELECT id FROM $table WHERE notification_type=%s;", $key);
                  $exists = $wpdb->get_row( $selectQuery );
                  if ( $exists ){
                      // already exists in db
                      continue;
                  }
                  if ( !isset( $formValues[ $key ] ) || (int)$formValues[ $key ] === 0 ){
                      // disabled by user
                      continue;
                  }
                  $newNotification = $notificationObject->getNotificationTemplate( $key );
                  $newNotification['message'] = (isset($newNotification['content'])) ? $newNotification['content'] : '';
                  $newNotification['notification_type'] = $key;
                  $newNotification['level_id'] = -1;
                  $newNotification['pushover_message'] = '';
                  $newNotification['pushover_status'] = '';
                  $notificationObject->save( $newNotification );
                  unset( $newNotification );
              }
              break;
        }

        if ( (int)$page === 5 && $response['status'] === 1 ){
            // make wizard completed if its submit from page 5 and everything its fine
            update_option( 'iump_wizard_complete', 1 );
        }

        // update current page value in db
        if ( $response['status'] === 1 ){
            $page++;
            update_option( 'ihc_wizard_current_page', $page );
        }
        echo json_encode( $response );
        die;
    }

    /**
      * @param none
      * @return none
      */
    public function doSkip()
    {
        if ( !ihcIsAdmin() ){
            die;
        }
        if ( !ihcAdminVerifyNonce() ){
            die;
        }
        \Ihc_Db::create_notifications();
        \Ihc_Db::create_demo_levels();
        $response = [
                      'message'     => 'success',
                      'status'      => 1,
        ];
        update_option( 'iump_wizard_complete', -1 );// wizard uncomplete
        echo json_encode( $response );
        die;
    }

}
