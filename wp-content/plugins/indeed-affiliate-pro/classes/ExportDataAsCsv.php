<?php
namespace Indeed\Uap;

class ExportDataAsCsv
{
    private $typeOfData             = '';
    private $linkToDownload         = '';
    private $filters                = [];

    public function __construct()
    {

    }

    public function setTypeOfData( $typeOfData='' )
    {
        $this->typeOfData = $typeOfData;
        return $this;
    }

    public function setFilters( $filters=[] )
    {
        $this->filters = $filters;
        return $this;
    }

    public function run()
    {
        switch ( $this->typeOfData ){
            case 'affiliates':
              $this->affiliates();
              break;
            case 'visits':
              $this->visits();
              break;
            case 'referrals':
              $this->referrals();
              break;
            case 'payouts':
              $this->payouts();
              break;
        }
        return $this;
    }

    private function affiliates()
    {
        global $indeed_db;

        //
        $params = maybe_unserialize( $this->filters );
        if ( isset( $params['limit'] ) ){
            $params['limit'] = false;
        }
        if ( isset( $params['offset'] ) ){
            $params['offset'] = false;
        }

        $params['order_by'] = 'a.id';
        $params['asc_or_desc'] = 'DESC';

        $manyAffiliates = $indeed_db->getAffiliatesWithFilters( $params );
        if ( empty( $manyAffiliates ) ){
            return;
        }

        $this->removeOldFiles();
        $hash = bin2hex( random_bytes( 5 ) );
        $plugin_name = "uap";
        $export_type = "affiliates";
        $filename = $plugin_name.'-'.$export_type.'-'.$hash .'-'.date("m-d-Y").'.csv';
        $targetFile = UAP_PATH . 'temporary/' . $filename;
        $fileResource = fopen( $targetFile, 'w' );

        $data = [
                    esc_html__( 'Affiliate ID', 'uap' ),
                    esc_html__( 'Email', 'uap' ),
                    esc_html__( 'Username', 'uap' ),
                    esc_html__( 'Name', 'uap' ),
                    esc_html__( 'Rank', 'uap' ),
                    esc_html__( 'Clicks', 'uap' ),
                    esc_html__( 'Referrals', 'uap' ),
                    esc_html__( 'Paid Earnings', 'uap' ),
                    esc_html__( 'Unpaid Earnings', 'uap' ),
                    esc_html__( 'WP Role', 'uap' ),
                    esc_html__( 'Affiliate Since', 'uap' ),
        ];

        /// top of CSV file
        fputcsv( $fileResource, $data, ',' );
        unset( $data );

        $currency = get_option( 'uap_currency' );
        $ranksList = uap_get_wp_roles_list();
        foreach ( $manyAffiliates as $affiliateObject ){
            // full name
            $name = $indeed_db->get_full_name_of_user( $affiliateObject->id );

            // rank
            if ( !isset( $rankLabel[ $affiliateObject->rank_id ] ) ){
                $rank_data = $indeed_db->get_rank($affiliateObject->rank_id);
                $rankLabel[ $affiliateObject->rank_id ] = isset( $rank_data['label'] ) ? $rank_data['label'] : '';
                $rankColor[] = isset( $rank_data['rank_color'] ) ? $rank_data['rank_color'] : '';
                $rankStyle[ $affiliateObject->rank_id ] = isset( $rank_data['rank_color'] ) ? 'uap-box-background-' . $rank_data['rank_color'] : 'uap-box-background-c9c9c9;';
            }
            $rank_label = $rankLabel[ $affiliateObject->rank_id ];

            // stats
            $stats = $indeed_db->get_stats_for_payments( $affiliateObject->id );

            // wp role
            $role = $indeed_db->get_user_first_role($affiliateObject->uid);

            $data = [
                        $affiliateObject->id,
                        $affiliateObject->user_email,
                        $affiliateObject->user_login,
                        $name,
                        $rank_label,
                        $stats['visits'],
                        $stats['referrals'],
                        uap_format_price_and_currency( $currency, $stats['paid_payments_value'] ),
                        uap_format_price_and_currency( $currency, $stats['unpaid_payments_value'] ),
                        $role,
                        uap_convert_date_to_us_format( $affiliateObject->user_registered ),
            ];
            fputcsv( $fileResource, $data, "," );
            unset( $data );
        }

        fclose( $fileResource );
        $this->linkToDownload = UAP_URL . 'temporary/' . $filename;
    }

    private function visits()
    {
        global $indeed_db;
        $params = maybe_unserialize( $this->filters );
        if ( isset( $params['limit'] ) ){
            $params['limit'] = false;
        }
        if ( isset( $params['offset'] ) ){
            $params['offset'] = false;
        }

        $params['order_by'] = 'v.id';
        $params['asc_or_desc'] = 'DESC';

        $visits = $indeed_db->getVisitsWithFilters( $params );

        if ( empty( $visits ) ){
            return;
        }

        $this->removeOldFiles();
        $hash = bin2hex( random_bytes( 5 ) );
        $plugin_name = "uap";
        $export_type = "clicks";
        $filename = $plugin_name.'-'.$export_type.'-'.$hash .'-'.date("m-d-Y").'.csv';
        $targetFile = UAP_PATH . 'temporary/' . $filename;
        $fileResource = fopen( $targetFile, 'w' );
        $data = [
                  esc_html__( 'ID', 'uap' ),
                  esc_html__( 'Affiliate ID', 'uap' ),
                  esc_html__( 'Affiliate Email', 'uap' ),
                  esc_html__( 'Referral ID', 'uap' ),
                  esc_html__( 'URL', 'uap' ),
                  esc_html__( 'From Page', 'uap' ),
                  esc_html__( 'IP Address', 'uap' ),
                  esc_html__( 'Browser', 'uap' ),
                  esc_html__( 'Device', 'uap' ),
                  esc_html__( 'Date', 'uap' ),
                  esc_html__( 'Status', 'uap' ),
        ];

        /// top of CSV file
        fputcsv( $fileResource, $data, ',' );
        unset( $data );

        foreach ( $visits as $visit ){
            $data = [
                      $visit->id,
                      empty( $visit->affiliate_id ) ? esc_html__( '-', 'uap' ) : $visit->affiliate_id,
                      empty( $visit->user_email ) ? esc_html__( '-', 'uap' ) : $visit->user_email,
                      $visit->referral_id,
                      $visit->url,
                      empty( $visit->ref_url ) ? esc_html__( '-', 'uap' ) : $visit->ref_url,
                      $visit->ip,
                      $visit->browser,
                      $visit->device,
                      uap_convert_date_to_us_format( $visit->visit_date ),
                      empty( $visit->referral_id ) ? esc_html__('Just Visit', 'uap') : esc_html__('Converted', 'uap'),
            ];
            fputcsv( $fileResource, $data, "," );
            unset( $data );
        }

        fclose( $fileResource );
        $this->linkToDownload = UAP_URL . 'temporary/' . $filename;

    }

    private function referrals()
    {
        global $indeed_db;
        $params = maybe_unserialize( $this->filters );
        if ( isset( $params['limit'] ) ){
            $params['limit'] = false;
        }
        if ( isset( $params['offset'] ) ){
            $params['offset'] = false;
        }

        $params['order_by'] = 'r.id';
        $params['asc_or_desc'] = 'DESC';

        $referrals = $indeed_db->getReferralsWithFilters( $params );

        if ( empty( $referrals ) ){
            return;
        }

        $this->removeOldFiles();
        $hash = bin2hex( random_bytes( 5 ) );
        $plugin_name = "uap";
        $export_type = "referrals";
        $filename = $plugin_name.'-'.$export_type.'-'.$hash .'-'.date("m-d-Y").'.csv';
        $targetFile = UAP_PATH . 'temporary/' . $filename;
        $fileResource = fopen( $targetFile, 'w' );

        $data = [
                    esc_html__( 'ID', 'uap' ),
                    esc_html__( 'Affiliate ID', 'uap' ),
                    esc_html__( 'Affiliate Email', 'uap' ),
                    esc_html__( 'From', 'uap' ),
                    esc_html__( 'Reference', 'uap' ),
                    esc_html__( 'Description', 'uap' ),
                    esc_html__( 'Amount', 'uap' ),
                    esc_html__( 'Date', 'uap' ),
                    esc_html__( 'Status', 'uap' ),
                    esc_html__( 'Payout Status', 'uap' ),
                    //
                    esc_html__('Campaign', 'uap'),
                    esc_html__('Visit Id', 'uap'),
                    esc_html__('Reference Details', 'uap'),
                    esc_html__('Parent Referral Id', 'uap'),
                    esc_html__('Child Referral Id', 'uap'),
        ];

        /// top of CSV file
        fputcsv( $fileResource, $data, ',' );
        unset( $data );
        $currency = get_option( 'uap_currency' );

        foreach ( $referrals as $referral ){
            $status = esc_html__( 'Rejected', 'uap' );
            if ( $referral->status == 1 ){
                $status = esc_html__( 'Pending', 'uap' );
            } else if ( $referral->status == 2 ){
                $status = esc_html__( 'Approved', 'uap' );
            }
            $payment_status = esc_html__( 'Unpaid', 'uap' );
            if ( $referral->payment == 1 ){
                $payment_status = esc_html__( 'Pending', 'uap' );
            } else if ( $referral->payment == 2 ){
                $payment_status = esc_html__( 'Paid', 'uap' );
            }
            $data = [
                      $referral->id,
                      $referral->affiliate_id,
                      empty( $referral->user_email ) ? esc_html__( '-', 'uap' ) : $referral->user_email,
                      uap_service_type_code_to_title( $referral->source ),
                      $referral->reference,
                      $referral->description,
                      uap_format_price_and_currency( $referral->currency, $referral->amount ),
                      uap_convert_date_to_us_format( $referral->date ),
                      $status,
                      $payment_status,
                      //
                      $referral->campaign,
                      $referral->visit_id,
                      $referral->reference_details,
                      $referral->parent_referral_id,
                      $referral->child_referral_id
            ];
            fputcsv( $fileResource, $data, "," );
            unset( $data );
        }

        fclose( $fileResource );
        $this->linkToDownload = UAP_URL . 'temporary/' . $filename;
    }

    private function payouts()
    {
        global $indeed_db;
        $params = maybe_unserialize( $this->filters );
        if ( isset( $params['limit'] ) ){
            $params['limit'] = false;
        }
        if ( isset( $params['offset'] ) ){
            $params['offset'] = false;
        }

        $params['order_by'] = 'p.id';
        $params['asc_or_desc'] = 'DESC';

        $payouts = $indeed_db->getPayoutsWithFilters( $params );

        if ( empty( $payouts ) ){
            return;
        }

        $this->removeOldFiles();
        $hash = bin2hex( random_bytes( 5 ) );
        $plugin_name = "uap";
        $export_type = "payouts";
        $filename = $plugin_name.'-'.$export_type.'-'.$hash .'-'.date("m-d-Y").'.csv';
        $targetFile = UAP_PATH . 'temporary/' . $filename;
        $fileResource = fopen( $targetFile, 'w' );

        $data = [
                    esc_html__( 'ID', 'uap' ),
                    esc_html__( 'Affiliate ID', 'uap' ),
                    esc_html__( 'Affiliate Email', 'uap' ),
                    esc_html__( 'Amount', 'uap' ),
                    esc_html__( 'Payout Method', 'uap' ),
                    esc_html__( 'Transaction ID', 'uap' ),
                    esc_html__( 'Payment Details', 'uap' ),
                    esc_html__( 'Date', 'uap' ),
                    esc_html__( 'Status', 'uap' ),
        ];

        /// top of CSV file
        fputcsv( $fileResource, $data, ',' );
        unset( $data );
        $currency = get_option( 'uap_currency' );

        foreach ( $payouts as $payout ){
            $status = esc_html__( 'Failed', 'uap' );
            if ( $payout->status == 1 ){
                $status = esc_html__( 'Pending', 'uap' );
            } else if ( $payout->status == 2 ){
                $status = esc_html__( 'Completed', 'uap' );
            }
            $paymentDetails = $payout->payment_details;
            $paymentDetailsArray = maybe_unserialize( $payout->payment_details );
            if ( $paymentDetailsArray && isset( $paymentDetailsArray['uap_affiliate_bank_transfer_data'] ) && isset( $paymentDetailsArray['uap_affiliate_bank_transfer_data']['label'] )
            && isset( $paymentDetailsArray['uap_affiliate_bank_transfer_data']['value'] ) ){
               $paymentDetails = $paymentDetailsArray['uap_affiliate_bank_transfer_data']['label'] . ' : ' . $paymentDetailsArray['uap_affiliate_bank_transfer_data']['value'];
            } else if ( $paymentDetailsArray && isset( $paymentDetailsArray['uap_affiliate_paypal_email']) && isset( $paymentDetailsArray['uap_affiliate_paypal_email']['label'] )
            && isset( $paymentDetailsArray['uap_affiliate_paypal_email']['value'] ) ){
                $paymentDetails = $paymentDetailsArray['uap_affiliate_paypal_email']['label'] . ' : ' . $paymentDetailsArray['uap_affiliate_paypal_email']['value'];
            }
            $data = [
                      $payout->id,
                      $payout->affiliate_id,
                      empty( $payout->user_email ) ? esc_html__( '-', 'uap' ) : $payout->user_email,
                      uap_format_price_and_currency( $payout->currency, $payout->amount ),
                      $payout->payment_type,
                      $payout->transaction_id,
                      $paymentDetails,
                      uap_convert_date_to_us_format( $payout->create_date ),
                      $status
            ];
            fputcsv( $fileResource, $data, "," );
            unset( $data );
        }

        fclose( $fileResource );
        $this->linkToDownload = UAP_URL . 'temporary/' . $filename;
    }

    public function getDownloadLink()
    {
        return $this->linkToDownload;
    }

    private function removeOldFiles()
    {
        $directory = UAP_PATH . 'temporary/';
        $files = scandir( $directory );
        foreach ( $files as $file ){
            $fileFullPath = $directory . $file;
            if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
                $extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
                if ( $extension == 'csv' ){
                    unlink( $fileFullPath );
                }
            }
        }
    }

}
