<?php
namespace Indeed\Uap\Admin;

class Payouts
{
    /**
    * @param none
    * @return none
    */
    public function __construct()
    {
        add_action( 'uap_admin_dashboard_payment_tab_custom_subtab', [$this, 'addNewPage' ], 999, 1 );
        add_action( 'uap_admin_dashboard_payment_tab_custom_subtab', [$this, 'managePayouts' ], 999, 1 );
        add_action( 'uap_admin_dashboard_payment_tab_custom_subtab', [$this, 'managePayments' ], 999, 1 );
        add_action( 'uap_admin_dashboard_payment_tab_custom_subtab', [$this, 'singlePayout' ], 999, 1 );
        add_action( 'uap_admin_dashboard_payment_tab_custom_subtab', [$this, 'singlePayment' ], 999, 1 );
    }


    /**
     * @param string
     * @return string
     */
    public function managePayouts( $subtab='' )
    {
        global $indeed_db;
        if ( $subtab === '' || $subtab !== 'manage_payouts' ){
            return;
        }

        if ( isset( $_POST['uap_payout_setup_submit'] ) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['uap_admin_forms_nonce']), 'uap_admin_forms_nonce' )  ){
            // submit from step 2 => proceed to payout
            $response = $this->submit( uap_sanitize_textarea_array( $_POST ) );
        }

        // view
        $data = [
                  'response'    => isset( $response ) ? $response : false,
        ];
        wp_enqueue_script( 'uap-payout-setup', UAP_URL . 'assets/js/UapPayoutSetup.js', ['jquery'], 8.9 );
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/manage-payouts.php' )
                       ->setContentData( $data )
                       ->getOutput();
        echo esc_uap_content( $output );
    }


    /**
      * @param none
      * @return string
      */
    public function managePayments( $subtab='' )
    {
        global $indeed_db;
        if ( $subtab === '' || $subtab !== 'manage_payments' ){
            return;
        }

        $data = [];
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/manage-payments.php' )
                       ->setContentData( $data )
                       ->getOutput();
        echo esc_uap_content( $output );
    }

    /**
     * @param string
     * @return string
     */
    public function singlePayout( $subtab='')
    {
        global $indeed_db;
        if ( $subtab === '' || $subtab !== 'view_payout' ){
            return;
        }
        $id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
        if ( $id === false ){
            return;
        }

        $payoutModel = new \Indeed\Uap\Db\Payouts();
        $data = [
                  'payout_data'                 => $payoutModel->getOne( $id ),
                  'payout_methods'              => [
                                                      'inherited'     => esc_html__( 'Inherited Payout Method', 'uap'),
                                                      'bt'            => esc_html__( 'Direct Deposit', 'uap' ),
                                                      'bank_transfer' => esc_html__( 'Direct Deposit', 'uap' ),
                                                      'paypal'        => esc_html__( 'PayPal Bulk Payout', 'uap' ),
                                                      'stripe'        => esc_html__( 'Stripe Payout', 'uap' ),
                                                      'stripe_v3'     => esc_html__( 'Stripe Payout', 'uap' ),
                  ],
                  'status_types' => [
                      2 => esc_html__('Paid', 'uap'),
                      1 => esc_html__('Processing', 'uap'),
                      0 => esc_html__('Failed', 'uap'),
                  ],
                  'referrals_count'             => 0,
                  'complete_percentage'         => $payoutModel->getCompletedPercentage( $id ),
        ];

        if ( (int)$data['complete_percentage'] === 100 ){
            $data['status'] = 2;
        } else {
            $data['status'] = 1;
        }
        if ( !empty($data['payout_data']['id']) ){
            $paymentMetaModel = new \Indeed\Uap\Db\PaymentMeta();
            $payments = $paymentMetaModel->getAllPaymentIdsForMetaNameMetaValue( 'payout_id', $data['payout_data']['id'] );
        }
        if ( !empty( $payments ) ){
            foreach ( $payments as $paymentId ){
                $data['payments'][$paymentId] = $indeed_db->getOnePaymentWithReferrals( $paymentId );
                if ( isset( $data['payments'][$paymentId]['referrals'] ) && is_array( $data['payments'][$paymentId]['referrals'] ) ){
                    $data['referrals_count'] = $data['referrals_count'] + count( $data['payments'][$paymentId]['referrals'] );
                }
            }
        }
        wp_enqueue_script( 'uap-payout-setup', UAP_URL . 'assets/js/UapPayoutSetup.js', ['jquery'], 8.9 );
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/single-payout.php' )
                       ->setContentData( $data )
                       ->getOutput();
        echo esc_uap_content( $output );
    }

    /**
     * @param string
     * @return string
     */
    public function singlePayment( $subtab='' )
    {
        global $indeed_db;
        if ( $subtab === '' || $subtab !== 'view_payment' ){
            return;
        }

        $id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
        if ( $id === false ){
            return;
        }
        $availableSystems = $indeed_db->getPossibleSources();
        $sources = [];
        if ( $availableSystems ){
            foreach ($availableSystems as $k=>$v){
                $label = uap_service_type_code_to_title($v['source']);
                if ( $label === '' ){
                    continue;
                }
                $sources[$v['source']] = $label;
            }
        }

        $data = [
                  'payment_details'       => $indeed_db->getOnePaymentWithReferrals( $id ),
                  'payout_methods'        => [
                                                      'inherited'     => esc_html__( 'Inherited Payout Method', 'uap'),
                                                      'bt'            => esc_html__( 'Direct Deposit', 'uap' ),
                                                      'bank_transfer' => esc_html__( 'Direct Deposit', 'uap' ),
                                                      'paypal'        => esc_html__( 'PayPal Bulk Payout', 'uap' ),
                                                      'stripe'        => esc_html__( 'Stripe Payout', 'uap' ),
                                                      'stripe_v3'     => esc_html__( 'Stripe Payout', 'uap' ),
                  ],
                  'status_types'          => [
                      2 => esc_html__('Paid', 'uap'),
                      1 => esc_html__('Processing', 'uap'),
                      0 => esc_html__('Failed', 'uap'),
                  ],
                  'sources'                     => $sources,
                  'payment_method_details'  => false,
        ];


        if ( isset( $data['payment_details']['payment_type'] ) && isset( $data['payment_details']['affiliate_id'] ) ){
            if ( $data['payment_details']['payment_type'] === 'bank_transfer' ){
                  $uid = $indeed_db->get_uid_by_affiliate_id( $data['payment_details']['affiliate_id'] );
                  $data['payment_method_details'] = get_user_meta( $uid, 'uap_affiliate_bank_transfer_data', true );
            } else if ( $data['payment_details']['payment_type'] === 'paypal' ){
                  $data['payment_method_details'] = $indeed_db->get_paypal_email_addr( $data['payment_details']['affiliate_id'] );
            }
        }

        wp_enqueue_script( 'uap-payout-setup', UAP_URL . 'assets/js/UapPayoutSetup.js', ['jquery'], 8.9 );
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/single-payment.php' )
                       ->setContentData( $data )
                       ->getOutput();
        echo esc_uap_content( $output );
    }

    /**
    * @param string
    * @return string
    */
    public function addNewPage( $subtab='' )
    {
        global $indeed_db;
        if ( $subtab === '' || $subtab !== 'new_payout' ){
            return;
        }

        $olderThanInterval = get_option( 'uap_payments_grace_period', 30 );
        $olderThanIntervalLabel = $olderThanInterval .' '.esc_html__( 'days', 'uap' );

        $firstUnpaidDate = $indeed_db->getDateOfFirstUnpaidReferral();
        $endTime = strtotime('-' . $olderThanInterval . ' day', time() );
        $endTime = date( 'Y-m-d h:i:s', $endTime );

        $minimumAmount = get_option( 'uap_payments_minimum_amount', 0 );

        $data = [
            'minimum_amount'              => $minimumAmount, // step 1
            'reports_data_all_time'       => $indeed_db->getDataForPayoutSetupTablePreview( -1, '', '', $minimumAmount ),//$indeed_db->get_stats_for_reports(),
            'reports_data_custom_time'    => $indeed_db->getDataForPayoutSetupTablePreview( -1, strtotime($firstUnpaidDate), strtotime($endTime), $minimumAmount ),//$indeed_db->get_stats_for_reports( '', 0, $firstUnpaidDate, $endTime ),
            'older_than_label'            => $olderThanIntervalLabel,
            'older_than'                  => $olderThanInterval,
            'custom_range_values'         => [
                                              'yesterday'     => esc_html__('Yesterday', 'uap'),
                                              'this_week'     => esc_html__('This Week', 'uap'),
                                              'last_week'     => esc_html__('Last Week', 'uap'),
                                              'this_month'    => esc_html__('This Month', 'uap'),
                                              'last_month'    => esc_html__('Last Month', 'uap'),
                                              'this_quarter'  => esc_html__('This Quarter', 'uap'),
                                              'last_quarter'  => esc_html__('Last Quarter', 'uap'),
                                              'this_year'     => esc_html__('This Year', 'uap'),
                                              'last_year'     => esc_html__('Last Year', 'uap'),
                                              'custom'        => esc_html__('Custom', 'uap'),
            ],
            'default_payout_method'       => 'inherited',
            'table_values'                => [],// step 2
            'step'                        => empty( $_GET['step'] ) ? 1 : (int)(sanitize_text_field( $_GET['step'] )),
            'currency_label'              => uapCurrency(),
            'currency'                    => get_option( 'uap_currency' ),
            'payout_methods'              => [
                                                'inherited' => [
                                                                  'label'     => esc_html__( 'Inherited Payout Method', 'uap'),
                                                                  'details'   => esc_html__( 'Inherit the Payout method configured by the Affiliate', 'uap'),
                                                                  'is_active' => 1,
                                                ],
                                                'bt'        => [
                                                                  'label'     => esc_html__( 'Direct Deposit', 'uap' ),
                                                                  'details'   => esc_html__( 'Process Payouts manually through a Wire Transfer', 'uap'),
                                                                  'is_active' => (int)(get_option( 'uap_disable_bt_payment_system', 0)) === 1 ? false : true,
                                                ],
                                                'paypal'    => [
                                                                  'label'     => esc_html__( 'PayPal Bulk Payout', 'uap' ),
                                                                  'details'   => esc_html__( 'Process bulk Payouts to affiliates through PayPal', 'uap'),
                                                                  'is_active' => $indeed_db->isPayPalActiveAndCompleted(),
                                                ],
                                                'stripe'    => [
                                                                  'label'     => esc_html__( 'Stripe Payout', 'uap' ),
                                                                  'details'   => esc_html__( "Manage bulk Payouts using Stripe payment gateway", 'uap'),
                                                                  'is_active' => $indeed_db->isStripeActiveAndCompleted(),
                                                ],
            ],
        ];

        if ( isset( $_POST['uap_payout_setup_next'] ) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['uap_admin_forms_nonce']), 'uap_admin_forms_nonce' )  ){
            // submit from step 1 => proceed to step 2

            //minimum amount
            $minimumAmount = isset( $_POST['minimum_amount'] ) ? sanitize_text_field( $_POST['minimum_amount'] ) : 0;


            // target date
            $select_referrals_time = isset( $_POST['select_referrals'] ) ? sanitize_text_field( $_POST['select_referrals'] ) : 'all';
            switch ( $select_referrals_time ){
                case 'all':
                  $startTime = $indeed_db->getDateOfFirstUnpaidReferral( $minimumAmount ); // first referral
                  $startTime = strtotime( $startTime );
                  $endTime = indeed_get_unixtimestamp_with_timezone();// now
                  $dateRange = esc_html__( 'All time', 'uap' );
                  //$startTime = false;
                  //$endTime = false;
                  break;
                case 'older_than':
                  $startTime = $indeed_db->getDateOfFirstUnpaidReferral( $minimumAmount );
                  $olderThan = isset( $_POST['older_than'] ) ? sanitize_text_field( $_POST['older_than'] ) : 30;
                  $endTime = strtotime('-' . $olderThan . ' day', time() );
                  $endTime = date( 'Y-m-d h:i:s', $endTime );
                  $dateRange = $startTime . ' - ' . $endTime;

                  $startTime = strtotime( $startTime );
                  $endTime = strtotime( $endTime );
                  break;
                case 'custom_range':
                  $customRange = sanitize_text_field( $_POST['custom_range_value'] );
                  if ( $customRange === 'custom' ){
                      // via date picker
                      $startTime = isset( $_POST['referrals_start_time'] ) ? sanitize_text_field( $_POST['referrals_start_time'] ) : $indeed_db->getDateOfFirstUnpaidReferral( $minimumAmount );
                      $endTime = isset( $_POST['referrals_end_time'] ) ? sanitize_text_field( $_POST['referrals_end_time'] ) : false;
                      $dateRange = $startTime . ' - ' . $endTime;
                      $startTime = strtotime( $startTime );
                      $endTime = strtotime( $endTime );
                  } else {
                      $customRange = $this->getStartTimeEndTimeFromCustomRange( $customRange );
                      $startTime = isset( $customRange['start_time'] ) ? $customRange['start_time'] : '';
                      $endTime = isset( $customRange['end_time'] ) ? $customRange['end_time'] : '';
                      $select_referrals_time = 'custom_range-custom_dates';
                  }
                  break;
            }
            // end of - target date

            // target affiliates
            $targetAffiliates = isset( $_POST['select_affiliates'] ) ? sanitize_text_field( $_POST['select_affiliates'] ) : '';
            if ( $targetAffiliates === 'all' ){
                // all affiliates
                $targetAffiliatsLabel = esc_html__( 'All', 'uap' );
                $affiliates_list = -1;
            } else {
                // some affiliates
                $affiliates_list = isset( $_POST['affiliates_list'] ) ? sanitize_text_field( $_POST['affiliates_list'] ) : -1;
                if ( (int)$affiliates_list === -1 ){
                    // all affiliates
                    $targetAffiliatsLabel = esc_html__( 'All', 'uap' );
                    $affiliates_list = -1;
                } else {
                    // some affiliates
                    $affiliates_list_id = explode( ',', $affiliates_list );
                    if ( $affiliates_list_id ){
                        $affiliateListEmail = [];
                        foreach ( $affiliates_list_id as $affiliateId ){
                            $affiliateListEmail[] = $indeed_db->get_email_by_affiliate_id( $affiliateId );
                        }
                        if ( count( $affiliateListEmail ) > 0 ){
                            $targetAffiliatsLabel = implode( ', ', $affiliateListEmail );
                        }
                    }
                }
            }
            // end of - target affiliates

            // payout method
            $data['selected_payout_method'] = isset( $_POST['payout_method'] ) ? sanitize_text_field( $_POST['payout_method'] ) : get_option( 'uap_default_payment_system', 'bt' );
            $data['selected_payout_method_label'] = isset( $data['payout_methods'][ $data['selected_payout_method'] ]['label'] ) ? $data['payout_methods'][ $data['selected_payout_method'] ]['label'] : '';


            // data for payout
            $data['payout_data'] = $indeed_db->getDataForPayoutSetupTablePreview( $affiliates_list, $startTime, $endTime, $minimumAmount, $data['selected_payout_method'] );

            if ( $startTime && $endTime ){
                $data['date_range'] = uap_convert_date_to_us_format( date('Y-m-d H:i:s', $startTime) ) . ' - ' . uap_convert_date_to_us_format( date( 'Y-m-d H:i:s', $endTime ) );
            } else {
                // all time
                $data['date_range'] = esc_html__( 'All time', 'uap');
            }
            $data['start_time'] = $startTime;
            $data['end_time'] = $endTime;
            $data['select_referrals_time'] = $select_referrals_time;

        }

        // view
        wp_enqueue_script( 'uap-payout-setup', UAP_URL . 'assets/js/UapPayoutSetup.js', ['jquery'], 8.9 );
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/new-payout.php' )
                       ->setContentData( $data )
                       ->getOutput();
        echo esc_uap_content( $output );

    }

    /**
     * @param array
     * @return array
     */
    private function submit( $postData=[] )
    {
        // do something with post data
        $response = [
                      'status'          => 0,
                      'message'         => esc_html__( 'Error', 'uap' ),
        ];

        // no referrals
        if ( !isset( $postData['payout_data'] ) || $postData['payout_data'] ==='' ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( "Unfortunately, we couldn't find any referrals based on your selection.", 'uap' ),
            ];
            return $response;
        }
        // no payout method
        if ( !isset( $postData['payout_method'] ) || $postData['payout_method'] === '' ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( "No payout method is set up.", 'uap' ),
            ];
            return $response;
        }

        $postData['payout_data'] = stripslashes( $postData['payout_data'] );
        $payoutData = json_decode( $postData['payout_data'], true );// since version 9.1

        // no affiliate-referrals data provided
        if ( empty( $payoutData ) || !is_array( $payoutData ) || count ( $payoutData ) < 1 ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( "Unfortunately, we couldn't find any referrals based on your selection.", 'uap' ),
            ];
            return $response;
        }

        $affiliateReferralsArray = isset( $payoutData['data_per_affiliate'] ) ? $payoutData['data_per_affiliate'] : false;

        if ( $affiliateReferralsArray === false || !is_array( $affiliateReferralsArray ) || count( $affiliateReferralsArray ) < 1 ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( "Unfortunately, we couldn't find any referrals based on your selection.", 'uap' ),
            ];
            return $response;
        }

        $currency = isset( $postData['currency'] ) ? sanitize_text_field( $postData['currency'] ) : get_option( 'uap_currency' );

        // loop throught referrals
        $errors = [];
        foreach ( $affiliateReferralsArray as $affiliateReferrals ){

            $payoutMethodForUser = isset( $affiliateReferrals['payout_method'] ) ? $affiliateReferrals['payout_method'] : false;
            $affiliateId = isset( $affiliateReferrals['affiliate_id'] ) ? $affiliateReferrals['affiliate_id'] : false;
            $referrals = '';
            $responseForAffiliate = false;

            switch ( $payoutMethodForUser ){
                case 'bt':
                  $responseForAffiliate = $this->doPayoutViaBT( $affiliateId, $affiliateReferrals['sum'], $currency, indeedImplodeKeys( $affiliateReferrals['referrals'] ) );
                  break;
                case 'paypal':
                  $responseForAffiliate = $this->doPayoutViaPaypal( $affiliateId, $affiliateReferrals['sum'], $currency, indeedImplodeKeys( $affiliateReferrals['referrals'] ) );
                  break;
                case 'stripe':
                  $responseForAffiliate = $this->doPayoutViaStripe( $affiliateId, $affiliateReferrals['sum'], $currency, indeedImplodeKeys( $affiliateReferrals['referrals'] ) );
                  break;
            }
            if ( $responseForAffiliate === false ){
                $errors[] = [
                              'affiliate_id'      => $affiliateId,
                              'referrals'         => indeedImplodeKeys( $affiliateReferrals['referrals'] ),
                              'reason'            => '',
                ];
            }
            if ( isset( $responseForAffiliate['payment_id'] ) && $responseForAffiliate['payment_id'] > 0 ){
                $payments[ $affiliateId ] = $responseForAffiliate['payment_id'];
            } else {
                $errors[] = [
                              'affiliate_id'      => $affiliateId,
                              'referrals'         => indeedImplodeKeys( $affiliateReferrals['referrals'] ),
                              'reason'            => '',
                ];
            }
        }


        if ( empty( $payments ) || count( $payments ) < 1 ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( 'No payments was made', 'uap' ),
                          'errors'          => isset( $errors ) ? $errors : '',
            ];
            return $response;
        }
        // save payout
        $payoutObject = new \Indeed\Uap\Db\Payouts();

        $payoutId = $payoutObject->save([
          'method'            => $postData['payout_method'],
          'amount'            => $payoutData['total_payment'],
          'currency'          => $postData['currency'],
          'date_range_type'   => $postData['select_referrals_time'],
          'start_time'        => date( 'Y-m-d h:i:s', $postData['start_time']),
          'end_time'          => date( 'Y-m-d h:i:s', $postData['end_time']),
          'details'           => '',
          'status'            => 1
        ]);

        // save payout id into payment_meta
        $PaymentMeta = new \Indeed\Uap\Db\PaymentMeta();
        foreach ( $payments as $paymentId ){
            $PaymentMeta->save( $paymentId, 'payout_id', $payoutId );
        }

        if ( count( $errors ) ){
            return [
                      'status'        => -1, // partially completed
                      'message'       => esc_html__( 'Some of the payments was not completed.', 'uap' ),
            ];
        }
        $response = [
                      'status'          => 1,
                      'message'         => esc_html__( 'Success', 'uap' ),
        ];
        return $response;
    }

    /**
     * @param int
     * @param float
     * @param string
     * @param string
     * @return array
     */
    protected function doPayoutViaBT( $affiliateId=0, $sum=0, $currency='', $referrals='' )
    {
        global $indeed_db;

        // update referral status, set payment as pending ( 1 )
        $indeed_db->change_referrals_status( explode( ',', $referrals ), 1 );

        // save the payment
        $paymentId = $this->savePayment( '-', $referrals, $affiliateId, $sum, $currency, 'bank_transfer' );

        if ( $paymentId === false || $paymentId === 0 || $paymentId === null ){
            return [
                      'status'            => 0,
                      'message'           => 'error',
                      'payment_id'        => $paymentId,
            ];
        }
        return [
                  'status'            => 1,
                  'message'           => 'success',
                  'payment_id'        => $paymentId,
        ];
    }

    /**
     * @param int
     * @param float
     * @param string
     * @param string
     * @return array
     */
    protected function doPayoutViaPaypal( $affiliateId=0, $sum=0, $currency='', $referrals='' )
    {
        global $indeed_db;
        $email = $indeed_db->get_paypal_email_addr( $affiliateId );
        // validate amount
        if ( !is_numeric($sum) || $sum <= 0 || strlen(intval($sum)) > 7){
            return [
                'status'		=> 0,
                'message'		=> esc_html__( 'The amount must be non-negative number, may optionally contain exactly 2 decimal places separated by point, limited to 7 digits before the decimal point', 'uap' ),
            ];
        }

        if ( strlen($currency) != 3){
          return [
              'status'				=> 0,
              'message'		    => esc_html__( 'Currency code must be 3-character ISO 4217 value (upper case)', 'uap' ),
          ];
          return $return;
        }

        // validate email
        if ( !is_email( $email ) ){
            return [
                'status'				=> 0,
                'message'		    => esc_html__( 'The Paypal Email address is Invalid', 'uap' ),
            ];
            return $return;
        }

        require_once UAP_PATH . 'classes/PayoutPayPal.class.php';
        $object = new \PayoutPayPal();
        if ( $object->isAvailableAndActive() === false ){
            // no api keys, out
            return [
                'message'		      => $object->getErrorDetails(),
                'status'          => 0,
            ];
        }
        $object->add_payment( $email, $sum, $currency );
        $transactionId = $object->do_payout();
        if ( !$transactionId){
            return [
                'message'		      => $object->getErrorDetails(),
                'status'          => 0,
            ];
        }

        // update referral status, set payment as pending ( 1 )
        $indeed_db->change_referrals_status( explode( ',', $referrals ), 1 );
        $paymentId = $this->savePayment( $transactionId, $referrals, $affiliateId, $sum, $currency, 'paypal' );

        if ( $paymentId === false || $paymentId === 0 || $paymentId === null ){
            return [
                      'status'            => 0,
                      'message'           => 'error',
                      'payment_id'        => $paymentId,
            ];
        }
        return [
                  'status'            => 1,
                  'message'           => 'success',
                  'payment_id'        => $paymentId,
        ];

    }

    /**
     * @param int
     * @param float
     * @param string
     * @param string
     * @return array
     */
    protected function doPayoutViaStripe( $affiliateId=0, $sum=0, $currency='', $referrals='' )
    {
        global $indeed_db;
        $object = new \Indeed\Uap\PayoutStripeV3();
        // make payment
        $transactionId = $object->do_payout( 0, $affiliateId, $sum, $currency );
        $errorMessage = $object->getErrorMessage();
        if ( $errorMessage ){
            // error
            return [
                'message'		      => 'something went wrong',
                'status'          => 0,
            ];
        } else if ( $transactionId === null || $transactionId === false || $transactionId === '' ){
              // error
              return [
                  'message'		      => 'something went wrong',
                  'status'          => 0,
              ];
        }

        // update referral status, set payment as pending ( 1 )
        $indeed_db->change_referrals_status( explode( ',', $referrals ), 1 );

        // save the payment
        $paymentId = $this->savePayment( $transactionId, $referrals, $affiliateId, $sum, $currency, 'stripe_v3' );

        if ( $paymentId === false || $paymentId === 0 || $paymentId === null ){
            return [
                      'status'            => 0,
                      'message'           => 'error',
                      'payment_id'        => $paymentId,
            ];
        }
        return [
                  'status'            => 1,
                  'message'           => 'success',
                  'payment_id'        => $paymentId,
        ];
    }

    /**
     * @param string
     * @param string
     * @param int
     * @param string
     * @param string
     * @param string
     * @return int
     */
    protected function savePayment( $transactionId='', $referrals='', $affiliateId=0, $sum='', $currency='', $paymentType='' )
    {
        global $indeed_db;
        $now = current_time( 'Y-m-d H:i:s' );
        $paymentId = $indeed_db->add_payment( [
                'payment_type' 				=> $paymentType,
                'transaction_id' 			=> $transactionId,
                'referral_ids' 				=> $referrals,
                'affiliate_id' 				=> $affiliateId,
                'amount' 							=> $sum,
                'currency' 						=> $currency,
                'create_date' 				=> $now,
                'update_date' 				=> $now,
                'status' 							=> 1,// pending
        ] );
        return $paymentId;
    }

    /**
     * @param string
     * @return array
     */
    private function getStartTimeEndTimeFromCustomRange( $customRange='' )
    {
        $today = strtotime('00:00:00');
        $now = indeed_get_unixtimestamp_with_timezone();
        $response = [];
        switch ( $customRange ){
          case 'yesterday':
            $response['start_time'] = strtotime('-1 day', $today );
            $response['start_time'] = date('Y-m-d H:i:s', $response['start_time']);
            $response['start_time'] = strtotime($response['start_time']);
            $response['end_time'] = date('Y-m-d H:i:s', $today);
            $response['end_time'] = strtotime($response['end_time']);
            break;
          case 'last_week':
            $response['start_time'] = strtotime('Monday last week');
            $response['end_time'] = strtotime('last Monday');
            break;
          case 'this_week':
            $response['start_time'] = strtotime('last Monday');
            $response['end_time'] = $now;
          break;
          case 'this_month':
            $response['start_time'] = strtotime('first day of this month midnight');
            $response['end_time'] = $now;
            break;
          case 'last_month':
            $response['start_time'] = strtotime('first day of last month midnight');
            $response['end_time'] =  strtotime('first day of this month midnight');
            break;
          case 'this_quarter':
            $response['start_time'] = strtotime((new \DateTime('first day of -' . (((date('n') - 1) % 3) + 0) . ' month'))->format('Y-m-d'));
            $response['end_time'] = $now;
          break;
          case 'last_quarter':
            $response['start_time'] = strtotime((new \DateTime('first day of -' . (((date('n') - 1) % 3) + 3) . ' month'))->format('Y-m-d'));
            $response['end_time'] =  strtotime((new \DateTime('first day of -' . (((date('n') - 1) % 3) + 0) . ' month'))->format('Y-m-d'));
            break;
          case 'this_year':
            $response['start_time'] = strtotime('first day of january this year midnight');
            $response['end_time'] = $now;
            break;
          case 'last_year':
            $response['start_time'] = strtotime('first day of january last year midnight');
            $response['end_time'] = strtotime('first day of january this year midnight');
            break;
        }
        return $response;
    }

}
