<?php
namespace Indeed\Ihc\Services;
/*
Since Ultimate Membership Pro v. 12.5
ConstantContact use OAuth2 Authentification.
Ajax for getting redirect link to auth: ihc_admin_get_cc_auth_uri
Ajax for reset auth: ihc_admin_reset_cc

Send contact to cc:
$cc = new \Indeed\Ihc\Services\ConstantContact();
$cc->storeContact( $email='', $firstName='', $lastName='' );

Get the lists from cc:
$cc = new \Indeed\Ihc\Services\ConstantContact();
$lists = $cc->getLists();

*/
class ConstantContact
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'wp_ajax_ihc_admin_get_cc_auth_uri', [ $this, 'createAuthURI' ] );
        add_action( 'wp_ajax_ihc_admin_reset_cc', [ $this, 'resetCC' ] );
    }

    /**
     * Ajax call to obtain authentication uri.
     * @param none
     * @return none
     */
    public function createAuthURI()
    {
        if ( !isset( $_POST['client_id'] ) || $_POST['client_id'] === '' ){
            echo json_encode([
                                'status'      => 0,
                                'message'     => esc_html__('No Client Id provided.', 'ihc'),
            ]);
            die;
        }

        if ( !isset( $_POST['client_secret'] ) || $_POST['client_secret'] === '' ){
            echo json_encode([
                                'status'      => 0,
                                'message'     => esc_html__('No Client Secret provided.', 'ihc'),
            ]);
            die;
        }
        $clientId = sanitize_text_field( $_POST['client_id'] );
        $clientSecret = sanitize_text_field( $_POST['client_secret'] );
        $nonce = wp_create_nonce( 'ihc_admin_cc_nonce' );
        $scope = 'contact_data';
        $responseURL = $this->responseEndpointURL();

        $link = $this->authLink( $clientId, $responseURL, $scope, $nonce );
        if ( $link === false || $link === '' ){
            echo json_encode([
                                'status'      => 0,
                                'message'     => esc_html__('Something went wrong, please try again.', 'ihc'),
            ]);
            die;
        }
        // save the client id and client secret
        $this->setClientId( $clientId );
        $this->setClientSecret( $clientSecret );
        echo json_encode([
                            'status'      => 1,
                            'message'     => esc_html__('Success', 'ihc'),
                            'redirect_uri'=> $link,
        ]);
        die;
    }

    /**
     * Ajax call to reset CC.
     * @param none
     * @return none
     */
    public function resetCC()
    {
        if ( !ihcIsAdmin() ){
            die;
        }
        if ( !ihcAdminVerifyNonce() ){
            die;
        }
        $this->setClientId( '' );
        $this->setClientSecret( '' );
        $this->setRefreshToken( '' );
        $this->setAccessToken( '' );
        $this->setExpireTime( '' );
        $this->setDefaultList( '' );
    }

    /**
     * @param none
     * @return none
     */
    public function adminEndpoint()
    {
        if ( !isset( $_GET['code'] ) || $_GET['code'] === '' ){
            return [
                      'status'      => 0,
                      'message'     => esc_html__( 'Authentication failed.', 'ihc')
            ];
        }
        if ( !isset($_GET['state'] ) || $_GET['state'] === '' || !wp_verify_nonce( sanitize_text_field( $_GET['state'] ), 'ihc_admin_cc_nonce') ){
            return [
                      'status'      => 0,
                      'message'     => esc_html__( 'Authentication failed.', 'ihc')
            ];
        }

        //
        $clientId = $this->getClientId();
        $clientSecret = $this->getClientSecret();
        $code = sanitize_text_field( $_GET['code'] );
        $responseURL = $this->responseEndpointURL();

        $response = $this->requestAccessToken($responseURL, $clientId, $clientSecret, $code );

        if ( !isset( $response['expires_in'] ) ){
            return [
                      'status'      => 0,
                      'message'     => esc_html__( 'Authentication failed.', 'ihc')
            ];
        }
        if ( !isset( $response['access_token'] ) ){
            return [
                      'status'      => 0,
                      'message'     => esc_html__( 'Authentication failed.', 'ihc')
            ];
        }
        if ( !isset( $response['refresh_token'] ) ){
            return [
                      'status'      => 0,
                      'message'     => esc_html__( 'Authentication failed.', 'ihc')
            ];
        }
        // store the access token and refresh token
        $this->storeAllTokens( $response );
        return [
                  'status'      => 1,
                  'message'     => esc_html__( 'Completed Authentication.', 'ihc')
        ];
    }

    /**
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function authLink( $clientId='', $redirectURI='', $scope='', $state='' )
    {
        $baseURL = "https://authz.constantcontact.com/oauth2/default/v1/authorize";
        $authURL = $baseURL . "?client_id=" . $clientId . "&scope=" . $scope . "+offline_access&response_type=code&state=" . $state . "&redirect_uri=" . $redirectURI;
        return $authURL;
    }

    /**
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function requestAccessToken($redirectURI='', $clientId='', $clientSecret='', $code='')
    {
       // Use cURL to get access token and refresh token
       $ch = curl_init();

       // Define base URL
       $base = 'https://authz.constantcontact.com/oauth2/default/v1/token';

       // Create full request URL
       $url = $base . '?code=' . $code . '&redirect_uri=' . $redirectURI . '&grant_type=authorization_code';
       curl_setopt($ch, CURLOPT_URL, $url);

       // Set authorization header
       // Make string of "API_KEY:SECRET"
       $auth = $clientId . ':' . $clientSecret;
       // Base64 encode it
       $credentials = base64_encode($auth);
       // Create and set the Authorization header to use the encoded credentials, and set the Content-Type header
       $authorization = 'Authorization: Basic ' . $credentials;
       curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization, 'Content-Type: application/x-www-form-urlencoded'));

       // Set method and to expect response
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

       // Make the call
       $result = curl_exec($ch);
       curl_close($ch);
       if ( $result === false || $result === '' || $result === null ){
          return false;
       }
       return json_decode( $result, true );
    }

    /**
     * @param none
     * @return array
     */
    public function getLists()
    {
        $accessToken = $this->getAccessToken();
        if ( $accessToken === false ){
            return;
        }
        if ( $this->isExpired() ){
            $refreshToken = $this->getRefreshToken();
            $clientId     = $this->getClientId();
            $clientSecret = $this->getClientSecret();
            $response     = $this->refreshToken( $refreshToken, $clientId, $clientSecret );
            $this->storeAllTokens( $response );
            $accessToken  = $this->getAccessToken();
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.cc.email/v3/contact_lists?include_count=true&status=active&include_membership_count=all',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept: */*',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $listArray = json_decode($response, true );
        if ( !$listArray ){
            return false;
        }
        foreach ( $listArray['lists'] as $list ){
            $data[$list['list_id']] = $list['name'];
        }
        return $data;
    }

    /**
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function storeContact( $email='', $firstName='', $lastName='' )
    {
        $accessToken = $this->getAccessToken();
        if ( $accessToken === false ){
            return;
        }
        if ( $this->isExpired() ){
            $refreshToken = $this->getRefreshToken();
            $clientId     = $this->getClientId();
            $clientSecret = $this->getClientSecret();
            $response     = $this->refreshToken( $refreshToken, $clientId, $clientSecret );
            $this->storeAllTokens( $response );
            $accessToken  = $this->getAccessToken();
        }

        $body = [
        	'email_address' => [
        			'address' 				     => $email,
        			'permission_to_send' 	 => 'implicit',
        	],
        	'first_name'	  => $firstName,
        	'last_name'		  => $lastName,
          "create_source" => "Account",
        ];

        $list = $this->getDefaultList();
        if ( $list !== false && $list !== null && $list !== '' ){
            $body["list_memberships"] = [ $list ];
        }

        $body = wp_json_encode( $body );

        $targetUrl = 'https://api.cc.email/v3/contacts';
        $options = [
        	'body'        => $body,
        	'headers'     => [
        	      'cache-control'  => 'no-cache',
        	      'authorization'  => 'Bearer ' . $accessToken,
        		    'Content-Type' 	 => 'application/json',
          	  	'accept' 		     => 'application/json'
        	],
        ];
        $response = wp_remote_post( $targetUrl, $options );
        return $response;
    }

    /**
     * @param string
     * @param string
     * @param string
     * @return array
     */
    public function refreshToken( $refreshToken='', $clientId='', $clientSecret='' )
    {
        // Use cURL to get a new access token and refresh token
        $ch = curl_init();

        // Define base URL
        $base = 'https://authz.constantcontact.com/oauth2/default/v1/token';

        // Create full request URL
        $url = $base . '?refresh_token=' . $refreshToken . '&grant_type=refresh_token';
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set authorization header
        // Make string of "API_KEY:SECRET"
        $auth = $clientId . ':' . $clientSecret;
        // Base64 encode it
        $credentials = base64_encode($auth);
        // Create and set the Authorization header to use the encoded credentials, and set the Content-Type header
        $authorization = 'Authorization: Basic ' . $credentials;
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization, 'Content-Type: application/x-www-form-urlencoded'));

        // Set method and to expect response
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Make the call
        $result = curl_exec($ch);
        curl_close($ch);
        if ( $result === false || $result === '' || $result === null ){
           return false;
        }
        return json_decode( $result, true );
    }

    /**
     * @param array
     * @return none
     */
    public function storeAllTokens( $response=[] )
    {
        if ( isset( $response['expires_in'] ) ){
            $this->setExpireTime( time() + (int)$response['expires_in' ] );
        }
        if ( isset( $response['access_token'] ) ){
            $this->setAccessToken( $response['access_token'] );
        }
        if ( isset( $response['refresh_token'] ) ){
            $this->setRefreshToken( $response['refresh_token'] );
        }
    }


    /**
     * @param none
     * @return bool
     */
    public function isExpired()
    {
        $expiredTime = $this->getExpireTime();
        if ( $expiredTime === false || $expiredTime === null ){
            return false;
        }
        if ( $expiredTime < time() ){
            return true;
        }
        return false;
    }

    /**
     * @param none
     * @return string
     */
    public function getRefreshToken()
    {
        return get_option( 'ihc_cc_email_service_refresh_token' );
    }

    /**
     * @param none
     * @return string
     */
    public function getClientId()
    {
        return get_option( 'ihc_cc_email_service_client_id' );
    }

    /**
     * @param none
     * @return string
     */
    public function getClientSecret()
    {
        return get_option( 'ihc_cc_email_service_client_secret' );
    }

    /**
     * @param none
     * @return string
     */
    public function getAccessToken()
    {
        return get_option( 'ihc_cc_email_service_access_token' );
    }

    /**
     * @param none
     * @return string
     */
    public function getExpireTime()
    {
        return get_option( 'ihc_cc_email_service_expire_time' );
    }

    /**
     * @param none
     * @return string
     */
    public function getDefaultList()
    {
        return get_option( 'ihc_cc_email_service_default_list' );
    }

    /**
     * @param string
     * @return none
     */
    public function setRefreshToken( $input='' )
    {
        return update_option( 'ihc_cc_email_service_refresh_token', $input );
    }


    /**
     * @param string
     * @return none
     */
    public function setClientId( $input='' )
    {
        return update_option( 'ihc_cc_email_service_client_id', $input );
    }

    /**
     * @param string
     * @return none
     */
    public function setClientSecret( $input='' )
    {
        return update_option( 'ihc_cc_email_service_client_secret', $input );
    }

    /**
     * @param string
     * @return none
     */
    public function setAccessToken( $input='' )
    {
        return update_option( 'ihc_cc_email_service_access_token', $input );
    }

    /**
     * @param string
     * @return none
     */
    public function setExpireTime( $input='' )
    {
        return update_option( 'ihc_cc_email_service_expire_time', $input );
    }

    /**
     * @param string
     * @return none
     */
    public function setDefaultList( $input='' )
    {
        return update_option( 'ihc_cc_email_service_default_list', $input );
    }

    /**
     * @param none
     * @return string
     */
    public function responseEndpointURL()
    {
        $siteUrl = site_url();
        $siteUrl = trailingslashit($siteUrl);
        $siteUrl = $siteUrl . '?ihc_action=cc-auth';
        return $siteUrl;
    }
}
