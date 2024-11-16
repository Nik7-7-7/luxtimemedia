<?php
namespace Indeed\Uap;

class PayoutStripeV3
{
    /**
     * @var string
     */
    private $errorMessage         = '';

    /**
     * @param none
     * @return bool
     */
    public function authAffiliate()
    {
        global $current_user;
        if ( empty( $current_user->ID ) ){
            return false;
        }
        require_once UAP_PATH  . 'classes/services/stripe-php-master/vendor/autoload.php';
        $sandbox = get_option( 'uap_stripe_v3_sandbox' );
        if ( $sandbox ){
            $secret = get_option( 'uap_stripe_v3_sandbox_secret_key' );
        } else {
            $secret = get_option( 'uap_stripe_v3_secret_key' );
        }
        if ( $secret == '' ){
            return false;
        }

				$accountId = get_user_meta( $current_user->ID, 'uap_stripe_v3_user_account_id-pending', true );
        if ( $accountId === false || $accountId === null || $accountId === '' ){
            return false;
        }
        $stripe = new \Stripe\StripeClient($secret);
        $accountData = $stripe->accounts->retrieve( $accountId, [] );
        if ( !isset( $accountData->details_submitted ) || (int)$accountData->details_submitted !== 1 ){
            // save user account id
            return false;
        }
        update_user_meta( $current_user->ID, 'uap_stripe_v3_user_account_id', $accountId );
        update_user_meta( $current_user->ID, 'uap_affiliate_payment_type', 'stripe_v3' );
        delete_user_meta( $current_user->ID, 'uap_stripe_v3_user_account_id-pending' );

        /*
        // deprecated since version 8.4
        $code = isset( $_GET['code'] ) ? $_GET['code'] : '';
        if ( $code == '' ){
            return false;
        }
        $state = isset( $_GET['state'] ) ? $_GET['state'] : '';
        if ( $state == '' || !wp_verify_nonce( $state, 'uap_stripe_v3_auth_user' ) ){
            return false;
        }
        \Stripe\Stripe::setApiKey( $secret );
        $response = \Stripe\OAuth::token([
          'grant_type'      => 'authorization_code',
          'code'            => $code,
        ]);
        $userAccountId = isset( $response->stripe_user_id ) ? $response->stripe_user_id : '';
        if ( $userAccountId == '' ){
            return false;
        }
        return update_user_meta( $current_user->ID, 'uap_stripe_v3_user_account_id', $userAccountId );
        */
    }

    /**
     * @param int
     * @return string
     */
    public function generateAuthLink( $uid=0 )
    {
        global $indeed_db;
        if ( !$uid ){
            return '';
        }
        $firstName = get_user_meta( $uid, 'first_name', true );
				$lastName = get_user_meta( $uid, 'last_name', true );
        $email = $indeed_db->get_email_by_uid( $uid );
        $sandbox = get_option( 'uap_stripe_v3_sandbox' );
        if ( $sandbox ){
            $clientId = get_option( 'uap_stripe_v3_sandbox_client_id' );
        } else {
            $clientId = get_option( 'uap_stripe_v3_client_id' );
        }
        if ( $clientId == '' ){
            return false;
        }

        $nonce = wp_create_nonce( 'uap_stripe_v3_auth_user' );
        // old version :
        $link = 'https://connect.stripe.com/express/oauth/authorize?client_id=' . $clientId . '&state=' . $nonce . '&stripe_user[email]=' . $email;

        return $link;
    }

    /**
     * @param int
     * @param number
     * @param string
     * @return string
     */
    public function do_payout( $uid=0, $affiliateId=0, $amount=0, $currency='usd' )
    {
        global $indeed_db;
        if ( $amount == 0 ){
            return ;
        }
        if ( empty( $uid ) ){
            $uid = $indeed_db->get_uid_by_affiliate_id( $affiliateId );
        }
        if ( empty( $uid ) ){
            return ;
        }
        $accountId = get_user_meta( $uid, 'uap_stripe_v3_user_account_id', true );

        if ( $accountId == '' || $accountId === false ){
            return ;
        }
        require_once UAP_PATH  . 'classes/services/stripe-php-master/vendor/autoload.php';

        $sandbox = get_option( 'uap_stripe_v3_sandbox' );
        if ( $sandbox ){
            $secret = get_option( 'uap_stripe_v3_sandbox_secret_key' );
        } else {
            $secret = get_option( 'uap_stripe_v3_secret_key' );
        }
        if ( $secret == '' ){
            return ;
        }
        $siteName = get_option( 'blogname' );
        $username = $indeed_db->get_username_by_wpuid( $uid );
        $amount = $amount * 100;

        \Stripe\Stripe::setApiKey( $secret );
        try {
          $transfer_details = \Stripe\Transfer::create(array(
              	"amount" 			=> $amount,
              	"currency"		=> $currency,
              	"destination" => $accountId,
                "source_type" => get_option( 'uap_stripe_v3_source_type', 'card' ),//'bank_account',
              	"description" => esc_html__("From ", 'uap') . $siteName . esc_html__(" to ", 'uap') . $username . '.',
                'metadata'    => []
          ));
        } catch ( \Exception $e ){
            // since version 8.4
            $requestBodyError = $e->getHttpBody();
            $requestBodyError = json_decode( $requestBodyError, true );
            $this->errorMessage = isset( $requestBodyError['error']['message'] ) ? $requestBodyError['error']['message'] : esc_html__( 'Error', 'uap' );
            // end of since version 8.4
            return '';
        }

        if ( isset( $transfer_details->id ) ){
          return $transfer_details->id;
        } else {
            $this->errorMessage = '';
        }
    		return '';
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param none
     * @return bool
     */
    public function webhook()
    {
        global $indeed_db;
        $body = @file_get_contents('php://input');
        if(isset($body)){
          $data = json_decode( $body, true );
        }
        if ( empty($data) || !$data ){
            exit;
        }

        if ( !isset( $data['type'] ) ){
            exit;
        }

        $accountId = isset( $data['account'] ) ? $data['account'] : '';
        if ( $accountId == ''){
            $accountId = isset( $data['data']['object']['destination'] ) ? $data['data']['object']['destination'] : '';
        }
        $amount = isset( $data['data']['object']['amount'] ) ? $data['data']['object']['amount'] : '';
        if ( $accountId == '' || $amount == '' ){
            exit;
        }
        $amount = $amount / 100;

        $uid = $indeed_db->getUidByStripeV3AcctId( $accountId );

        if ( !$uid ){
            exit;
        }

        $affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $uid );

        $transactionId = $indeed_db->uapPaymentsGetTransactionIdByAffiliateIdAndAmount( $affiliateId, $amount );

        if ( $transactionId == '' ){
            exit;
        }
        $indeed_db->update_transaction_stripe_status( $transactionId, 'paid' );
        http_response_code(200);
        exit;
    }
}
