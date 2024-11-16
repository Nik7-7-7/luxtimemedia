<?php
//Stripe Checkout, New Payment integration
require_once '../../../../../../wp-load.php';
require_once ULP_PATH . 'classes/services/Stripe/vendor/autoload.php';


if ( empty( $_GET['sessionId'] ) ){
    die;
}

$key = get_option( 'ulp_stripe_publishable_key' );
$secretKey = get_option( 'ulp_stripe_secret_key' );
if ( !$secretKey || !$key ){
    die;
}
\Stripe\Stripe::setApiKey( $secretKey );
$session = \Stripe\Checkout\Session::retrieve( $_GET['sessionId'] );
if ( !$session ){
    die;
}
?>
<script src="https://js.stripe.com/v3"></script>
<span id="uap_js_stripe_settings"
    data-key='<?php echo $key;?>'
    data-session_id='<?php echo $_GET['sessionId'];?>'
></span>
<script src="<?php echo ULP_URL . 'assets/js/stripe_checkout.js';?>"></script>
