<?php
/*
How to use it:
// include the file, it has namespaces but its not added into autoload file.
require_once IHC_PATH . 'classes/import-export/IumpImportCsv.php';
$IumpImportCsv = new \IumpImportCsv();
$response = $IumpImportCsv->setFile( $fileWithYourData )
                          ->setType( 'members' )
                          ->proceed();
// will return an array:
[
    'status'      => 0 or 1,
    'message'     => success message or error message,
]
*/

if ( class_exists('IumpImportCsv') ){
    return;
}

class IumpImportCsv
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
             $this->errorMessage = esc_html__( 'Please upload a CSV file.', 'ihc' );
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
            $this->errorMessage = esc_html__( 'Please upload a CSV file.', 'ihc' );
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
                      'message'       => esc_html__('Oops! Looks like there was an issue importing the CSV file. Please double-check the file format and try again.', 'ihc'),
        ];
        switch ( $this->type ){
            case 'members':
              $response = $this->importMembers();
              break;
        }
        return $response;
    }

    /**
     * @param none
     * @return array
     */
    public function importMembers()
    {
        $response = [
                      'status'          => 0,
                      'message'         => esc_html__( 'We encountered some issues with your data. Please check the details and try again.', 'ihc' ),
        ];
        $handle = fopen( $this->file, 'r' );
        if ( $handle === false ){
            $response = [
                          'status'          => 0,
                          'message'         => esc_html__( 'Oops! Looks like there was an issue importing the CSV file. Please double-check the file format and try again.', 'ihc' ),
            ];
            return $response;
        }
        $errors = 0;
        $success = 0;
        $labels = fgetcsv( $handle );
        $userData = [];
        $ranks = [];
        $i = 0;
        $defaultRole = get_option( 'ihc_automatically_new_role' );

        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            $user = [];
            $lid = '';

            // set membership if its case
            $membershipSlug = isset( $data[5] ) ? $data[5] : false;
            if ( $membershipSlug !== false ){
                // get membership id by label
                if ( isset( $memberships[$membershipSlug] ) ){
                    $lid = $memberships[$membershipSlug];
                } else {
                    $membershipTemporary = \Indeed\Ihc\Db\Memberships::getOneByName( $membershipSlug );
                    if ( isset( $membershipTemporary['id'] ) ){
                        $lid = $membershipTemporary['id'];
                        $memberships[$membershipSlug] = $membershipTemporary['id'];
                    }

                }
            }
            // user details
            $user['user_email'] = isset( $data[1] ) ? $data[1] : '';
            $user['user_login'] = isset( $data[2] ) ? $data[2] : '';
            $user['first_name'] = isset( $data[3] ) ? $data[3] : '';
            $user['last_name'] = isset( $data[4] ) ? $data[4] : '';
            $user['user_registered'] = isset( $data[10] ) ? strtotime( $data[10] ) : false;
            $user['user_registered'] = date( 'Y-m-d H:i:s', $user['user_registered'] );
            $user['role'] = '';

            // roles
            $roles = isset( $data[9] ) ? $data[9] : '';
            $roles = maybe_unserialize( $roles );
            $userRoles = [];
            if ( is_array( $roles ) && count( $roles ) > 0 ){
                foreach ( $roles as $keyRole => $vRole ){
                    $userRoles[] = $keyRole;
                }
            } else if ( $roles === '' ){
              $userRoles[] = $defaultRole;
            }

            // if the user does not exists
            $uid = null;
            if ( !\Ihc_Db::get_wpuid_by_email( $user['user_email'] ) ){
                // create password
                $user['user_pass'] = wp_generate_password( 10 );
                $uid = wp_insert_user( $user );

                if ( !is_wp_error( $uid ) ){
                    /// send generated password to user
                    do_action( 'ihc_register_lite_action', $uid, [ '{NEW_PASSWORD}' => $user['user_pass'] ] );
                    $success++;
                    // add roles
                    $this->addRolesToUser( $uid, $userRoles );

                } else {
                    $errors++;
                    continue;
                }
            } else {
                // user alredy exists
                $uid = \Ihc_Db::get_wpuid_by_email( $user['user_email'] );
                $success++;
            }

            // append membership to user if its case
            if ( $lid && $uid !== null ){
                \Indeed\Ihc\UserSubscriptions::assign( $uid, $lid, [
                            'start_time'      => isset( $data[7] ) ? $data[7] : '',
                            'update_time'     => '',
                            'expire_time'     => isset( $data[8] ) ? $data[8] : '',
                ] );
            }

        }

        if ( $errors > 0 && $success > 0 ){
            $response = [
                            'status'          => -1,
                            'message'         => esc_html__( 'There were some issues with your data import, only ', 'ihc') . $success . esc_html__(' items were imported.', 'ihc' )
            ];
        } else if ( $errors > 0 && $success === 0 ){
            $response = [
                            'status'          => 0,
                            'message'         => esc_html__( 'We encountered some issues with your data. Please check the details and try again.', 'ihc' )
            ];
        }

        fclose( $handle ); // close file
        // remove file
        unlink($this->file);

        $response = [
                        'status'          => 1,
                        'message'         => esc_html__( "Your CSV file has been imported successfully. Your data is now updated in the system.", 'ihc' )
        ];
        return $response; // all good

    }

    /**
     * @param int
     * @param array
     * @return none
     */
    private function addRolesToUser( $uid=0, $roles=[] )
    {
        if ( count( $roles ) === 0 ){
            return;
        }
        $user = new \WP_User( $uid );
        foreach ( $roles as $role ){
            $user->add_role( $role );
        }
    }
}
