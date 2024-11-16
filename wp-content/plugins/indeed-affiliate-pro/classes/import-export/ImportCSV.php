<?php
namespace Indeed\Uap\ImportExport;

/*
How to use it:
// include the file, it has namespaces but its not added into autoload file.
require_once UAP_PATH . 'classes/import-export/ImportCSV.php';
$ImportCSV = new \Indeed\Uap\ImportExport\ImportCSV();
$response = $ImportCSV->setFile( $fileWithYourData )
                      ->setType( 'affiliates' or 'referrals' )
                      ->proceed();
// will return an array:
[
    'status'      => 0 or 1,
    'message'     => success message or error message,
]
*/
class ImportCSV
{

  	/**
  	 * @var string
  	 */
  	protected $file         = '';
    /**
     * @var string
     */
    protected $type         = '';
    /**
     * @var bool
     */
    protected $error        = false;
    /**
     * @var string
     */
    protected $errorMessage = '';


  	/**
  	 * @param none
  	 * @return none
  	 */
  	public function __construct(){}


  	/**
  	 * @param string
  	 * @return object
  	 */
  	public function setFile($filename='')
    {
    		if ( $filename === '' || !file_exists( $filename ) ){
    			   $this->error = true;
             $this->errorMessage = esc_html__( 'Please upload a CSV file.', 'uap' );
    		} else {
            $this->file = $filename;
        }
        return $this;
  	}

    /**
  	 * @param string
  	 * @return object
  	 */
    public function setType( $type='' )
    {
        if ( $type === '' ){
            $this->error = true;
            $this->errorMessage = esc_html__( 'Please upload a CSV file.', 'uap' );
        }
        $this->type = $type;
        return $this;
    }

    /**
  	 * @param none
  	 * @return array
  	 */
    public function proceed()
    {
        if ( $this->error ){
            return [
                      'status'        => 0,
                      'message'       => $this->errorMessage,
            ];
        }
        $response = [
                      'status'        => 0,
                      'message'       => 'Oops! Looks like there was an issue importing the CSV file. Please double-check the file format and try again.',
        ];
        switch ( $this->type ){
            case 'affiliates':
              $response = $this->importAffiliates();
              break;
            case 'referrals':
              $response = $this->importReferrals();
              break;
            default:
              $response = [
                'status'        => 0,
                'message'       => 'Unknown Import Request',
              ];
              break;
        }
        return $response;
    }

    /**
  	 * @param none
  	 * @return array
  	 */
    protected function importAffiliates()
    {
        global $indeed_db;
        $response = [
                      'status'          => 0,
                      'message'         => esc_html__( 'We encountered some issues with your data. Please check the details and try again.', 'uap' ),
        ];
        $handle = fopen( $this->file, 'r' );
        if ( $handle === false ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( 'Oops! Looks like there was an issue importing the CSV file. Please double-check the file format and try again.', 'uap' ),
            ];
            return $response;
        }

        $defaultRank = get_option('uap_register_new_user_rank');
        $errors = 0;
        $success = 0;
        $labels = fgetcsv( $handle );
        $userData = [];
        $ranks = [];
        $i = 0;
        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            // rank id
            $rankLabel = $data[4];
            if ( isset( $ranks[ $rankLabel ] ) ){
                $rankId = $ranks[ $rankLabel ];
            } else {
                $rankData = $indeed_db->getRankByLabel( $rankLabel );
                $rankId = isset( $rankData['id'] ) ? $rankData['id'] : 0;
                $ranks[$rankLabel] = $rankId;
            }
            // wp role
            $role = isset( $data[9] ) ? $data[9] : '';
            // email
            $email = isset( $data[1] ) ? $data[1] : false;
            // user login
            $userLogin = isset( $data[2] ) ? $data[2] : false;
            // affiliate id
            $affiliateId = isset( $data[0] ) ? $data[0] : false;

            if ( !$email || !$userLogin ){
                continue;
            }
            // name
            $name = $data[3];
            $firstName = '';
            $lastName = '';
            if ( $name !== '' ){
                $fullNameArr = explode( ' ', $name );
                if ( count($fullNameArr)>0 ){
                    $lastKey = count( $fullNameArr ) - 1;
                    $lastName = isset( $fullNameArr[$lastKey] ) ? $fullNameArr[$lastKey] : '';
                    $firstName = str_replace( $lastName, '', $name );
                }
            }
            // create time
            $createTime = isset( $data[10] ) ? strtotime( $data[10] ) : false;
            $createTime = date( 'Y-m-d H:i:s', $createTime );

            // insert user if its case
            $uid = $indeed_db->getUidByEmail($email);
            if ( $uid ){
                // already exists a user with this email in our db
                if ( $indeed_db->get_affiliate_id_by_wpuid( $uid ) ){
                    // already exists an affiliate for this email, so skip
                    continue;
                }
            } else {
                // insert user
                $password = wp_generate_password( 10 );
                $userdata = [
                              'user_login'        => $userLogin,
                              'user_email'        => $email,
                              'user_pass'         => $password,
                              'user_registered'   => $createTime,
                              'first_name'        => $firstName,
                              'last_name'         => $lastName,
                              'role'              => $role,
                ];

                $uid = wp_insert_user( $userdata );
                if ( $uid ){
                    /// send generated password to user
                    uap_send_user_notifications( $uid, 'register_lite_send_pass_to_user', false, [ '{NEW_PASSWORD}' => $password ] );
                }
            }
            $insertAffiliateData = [
                                      'create_date'             => $createTime,
                                      'rank_id'                 => $role,
                                      'affiliate_id'            => $affiliateId
            ];
            ////
            // check if affiliate id its already used
            if ( $indeed_db->get_wp_username_by_affiliate_id($affiliateId) !== '' ){
                unset( $insertAffiliateData['affiliate_id'] );
            }
            // save affiliate
            $jobResponse = $indeed_db->save_affiliate_with_params( $uid, $insertAffiliateData );

            // save rank
            if ( $rankId === 0 ){
                $rankId = $defaultRank;
            }
            $indeed_db->update_affiliate_rank_by_uid( $uid, $rankId );

            if ( $jobResponse === 0 ){
                $errors++;
            } else {
                $success++;
            }
        }

        if ( $errors > 0 && $success > 0 ){
            $response = [
                            'status'          => -1,
                            'message'         => esc_html__( 'There were some issues with your data import, only ', 'uap') . $success . esc_html__(' items were imported.', 'uap' )
            ];
        } else if ( $errors > 0 && $success === 0 ){
            $response = [
                            'status'          => 0,
                            'message'         => esc_html__( 'We encountered some issues with your data. Please check the details and try again.', 'uap' )
            ];
        }

        fclose( $handle ); // close file
        $response = [
                        'status'          => 1,
                        'message'         => esc_html__( "Your CSV file has been imported successfully. Your data is now updated in the system.", 'uap' )
        ];
        return $response; // all good
    }

    /**
  	 * @param none
  	 * @return array
  	 */
    protected function importReferrals()
    {
        global $indeed_db;
        $response = [
                      'status'          => 0,
                      'message'         => esc_html__( 'We encountered some issues with your data. Please check the details and try again', 'uap' ),
        ];
        $handle = fopen( $this->file, 'r' );
        if ( $handle === false ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( 'There seems to be a problem with your file. Please check it and try again.', 'uap' ),
            ];
            return $response;
        }
        $labels = fgetcsv( $handle );
        $userData = [];
        $i = 0;
        $referralStatusArray = [
                        esc_html__('Rejected', 'uap')           => 0,
                        esc_html__('Unverified', 'uap')         => 1,
                        esc_html__('Pending', 'uap')            => 1,
                        esc_html__('Verified', 'uap')           => 2,
                        esc_html__('Approved', 'uap')           => 2,
        ];
        $paymentStatusArray = [
                        esc_html__('Unpaid', 'uap')           => 0,
                        esc_html__('Pending', 'uap')          => 1,
                        esc_html__('Paid', 'uap')             => 2,
        ];

        $errors         = 0;
        $success        = 0;
        $alreadyExists  = 0;
        //
        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            // sort data
            $referralId = 0;
            $reference = '';
            $affiliateId = 0;
            $paymentStatusLabel = isset( $data[9] ) ? $data[9] : 0;
            $statusLabel = isset( $data[8] ) ? $data[8] : 0;
            $amount = $this->extractNumber( $data[6] );
            $currency = $this->extractNumber( $data[6] );


            $dataToInsert = [
                                'id'                      => $data[0],
                                'refferal_wp_uid'         => $indeed_db->get_uid_by_affiliate_id( $data[1] ),
                                'campaign'                => $data[10],
                                'affiliate_id'            => $data[1],
                                'visit_id'                => $data[11],
                                'description'             => $data[5],
                                'source'                  => uap_service_label_to_service_code($data[3]),// 8.6
                                'reference'               => $data[4],
                                'reference_details'       => $data[12],
                                'amount'                  => $amount,
                                'currency'                => $currency,
                                'date'                    => date( 'Y-m-d H:i:s', strtotime($data[7]) ),
                                'status'                  => isset( $referralStatusArray[ $statusLabel ] ) ? $referralStatusArray[ $statusLabel ] : 0,
                                'payment'                 => isset( $paymentStatusLabel[$paymentStatusArray] ) ? $paymentStatusLabel[$paymentStatusArray] : 0,
                                'parent_referral_id'      => $data[13],
                                'child_referral_id'       => $data[14],
            ];

            if ( $indeed_db->get_referral_id_by_reference_and_affiliate( $dataToInsert['reference'], $dataToInsert['affiliate_id'] ) !== false ){

                // referral already exists -> out
                $alreadyExists++;
                continue;
            }
            $temporaryData = $indeed_db->get_referral( $dataToInsert['id'] );
            if ( isset( $temporaryData['id'] ) && $temporaryData['id'] > 0 ){
                unset( $dataToInsert['id'] );
            }
            // insert referral
            $jobResponse = $indeed_db->insert_referral_via_import( $dataToInsert );

            if ( $jobResponse === 0 ){
                $errors++;
            } else {
                $success++;
            }
        }
        //
        if ( $errors > 0 && $success > 0 ){
            // some errors but some items was imported
            $response = [
                            'status'          => -1,
                            'message'         => esc_html__( 'There were some issues with your data import, so only ', 'uap') . $success . esc_html__(' items were imported.', 'uap' )
            ];
        } else if ( $errors > 0 && $success === 0 ){
            // only errors
            $response = [
                            'status'          => 0,
                            'message'         => esc_html__( 'We encountered some issues with your data. Please check the details and try again.', 'uap' )
            ];
        } else if ( $alreadyExists > 0 && $errors === 0 && $success === 0 ){
            // data already exists
            $response = [
                            'status'          => -1,
                            'message'         => esc_html__( 'We found existing data matching your import.', 'uap' )
            ];
        }

        fclose( $handle ); // close file
        $response = [
                        'status'          => 1,
                        'message'         => esc_html__( "All set! We've added your data.", 'uap' )
        ];
        return $response; // all good
    }

    /**
     * @param string
     * @return string
     */
    private function extractNumber( $string='' )
    {
          if ( $string === '' ){
              return '';
          }
          $string = str_replace(' ', '', $string);
          $remove = $this->extractChars( $string );
          $value = str_replace( $remove, '', $string );
          return (float)$value;
    }

    /**
     * @param string
     * @return string
     */
    private function extractChars( $string='' )
    {
        if ( $string === '' ){
            return '';
        }
        preg_match( '/[a-zA-Z]+/', $string, $matches );
        if ( isset( $matches[0] ) ){
            return $matches[0];
        }
    }


}
