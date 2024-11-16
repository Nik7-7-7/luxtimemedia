<?php
namespace Indeed\Ulp\PaymentService;
if (!defined('ABSPATH')){
   exit();
}
if (!interface_exists('Ulp_Payment_Service_Interface')){
   require_once ULP_PATH . 'classes/Payment_Services/Ulp_Payment_Service_Interface.php';
}

class TwoCheckout implements \Ulp_Payment_Service_Interface
{
  private $_metas = [];
  private $_transaction_details = [];

  public function __construct()
  {
      $this->_metas = \DbUlp::getOptionMetaGroup('2checkout');
  }

  public function setTransactionDetails($transaction_details=array())
  {
      $this->_transaction_details = $transaction_details;
      return $this;
  }

  public function pay()
  {
    if ( !isset( $this->_transaction_details['uid'] ) ){
        return;
    }
    $secretKey = get_option( 'ulp_2checkout_api_private_key' ); //
    $sellerId = get_option( 'ulp_2checkout_account_number' ); //
    $secretWord = get_option( 'ulp_2checkout_secret_word' ); //
    $return_url = get_home_url();
    $userObject = get_userdata( $this->_transaction_details['uid'] );

    if ( !isset( $userObject->user_email ) ){
        return;
    }

    $params = [
        //Billing information
        'email'                 => $userObject->user_email,
        'name'                  => \DbUlp::get_full_name( $this->_transaction_details['uid'] ),
        'country' 							=> get_user_meta( $this->_transaction_details['uid'], 'country', true ),
        'state' 						  	=> get_user_meta( $this->_transaction_details['uid'], 'thestate', true ),
        'city' 									=> get_user_meta( $this->_transaction_details['uid'], 'city', true ),
        'address' 							=> get_user_meta( $this->_transaction_details['uid'], 'addr1', true ),
        'phone' 								=> get_user_meta( $this->_transaction_details['uid'], 'phone', true ),
        'zip' 								  => get_user_meta( $this->_transaction_details['uid'], 'zip', true ),
        //Product information
        'dynamic'								=> 1,
        'expiration'						=> time() + 300, // 5minutes
        'item-ext-ref'				  => $this->_transaction_details['course_id'],
        'customer-ext-ref'			=> $this->_transaction_details['uid'],
        'order-ext-ref'					=> $this->_transaction_details['order_id'],
        'prod'									=> $this->_transaction_details['course_label'],
        'description'           => $this->_transaction_details['course_label'],
        'price'									=> $this->_transaction_details['amount'],
        'currency'							=> $this->_transaction_details['currency'],
        'qty'										=> 1,
        'type'									=> 'PRODUCT',
        'tangible'							=> 0,
        //Cart behavior
        'return-url'						=> $return_url,
        'return-type'						=> 'redirect',
        'tpl'										=> 'one-column', // default/one-column
        'language'							=> 'en',
    ];

    $sandbox = get_option('ulp_2checkout_sandbox_on');
    if ( !empty( $sandbox ) ){
        $params['test'] = 1;
    }
    $params['signature'] = $this->getSignature( $sellerId, $secretWord, $params );

    $redirectUrl = 'https://secure.2checkout.com/checkout/buy/?merchant=' . $sellerId;
    foreach ( $params as $key => $value ){
        $redirectUrl .= '&' . "$key=$value";
    }
  	wp_redirect($redirectUrl);
  	exit;
  }

  private function getSignature( $sellerId='', $secretWord='', $payload=[] )
  {
    $payload['merchant'] = $sellerId;
    $payload = json_encode( $payload );
    $merchantToken = $this->generateJwtToken(
      $sellerId,
      time(),
      time() + 360,
      $secretWord
    );
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://secure.2checkout.com/checkout/api/encrypt/generate/signature",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'content-type: application/json',
            'cache-control: no-cache',
            'merchant-token: ' . $merchantToken,
        ],
    ]);
    $response = curl_exec($curl);
    $err      = curl_error($curl);
    curl_close($curl);

    if ( $err ) {
        $signature = false;
    } else {
        $responseObject = json_decode( $response );
        $signature = isset( $responseObject->signature ) ? $responseObject->signature : false;
    }
    return $signature;
  }

  function generateJwtToken( $sub, $iat, $exp, $buy_link_secret_word )
  {
    $header    = $this->encode( json_encode( [ 'alg' => 'HS512', 'typ' => 'JWT' ] ) );
    $payload   = $this->encode( json_encode( [ 'sub' => $sub, 'iat' => $iat, 'exp' => $exp ] ) );
    $signature = $this->encode(
      hash_hmac( 'sha512', "$header.$payload", $buy_link_secret_word, true )
    );

    return implode( '.', [
      $header,
      $payload,
      $signature
    ] );
  }

  private function encode( $data )
  {
     return str_replace( '=', '', strtr( base64_encode( $data ), '+/', '-_' ) );
  }

  public function ipn()
  {
    if ( !isset( $_POST['REFNOEXT'] ) || !isset( $_POST['ORDERSTATUS'] ) ){
        echo esc_ulp_content('============= Ultimate Learning Pro - 2Checkout IPN ============= ');
        echo esc_ulp_content('<br/><br/>No Payments details sent. Come later');
        http_response_code(200);
        exit;
    }
    $orderId = sanitize_text_field( $_POST['REFNOEXT'] );

    $UlpOrder = new \UlpOrder();
    $unique = $UlpOrder->getUniqueByOrderId( $orderId );
    if ( $unique === false || $unique === null ){
        exit;
    }

    switch ( sanitize_text_field($_POST['ORDERSTATUS']) ) {
      case 'COMPLETE':
        $UlpOrder->modify_status( $unique, 'ulp_complete' );
        break;
      case 'PENDING':

        break;
      case 'REFUND':
        $UlpOrder->modify_status($unique, 'ulp_fail');
        break;
      case 'INVALID':
      case 'REVERSED':
        $UlpOrder->modify_status($unique, 'ulp_fail');
        break;
      case 'CANCELED':
        $UlpOrder->modify_status($unique, 'ulp_fail');
        break;
      default:

        break;
    }
    http_response_code(200);
    exit;

  }

}
