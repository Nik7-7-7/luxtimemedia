<?php
//Stripe Checkout, New Payment integration

require_once '../../../../../../../wp-load.php';
include IHC_PATH . 'classes/gateways/libraries/stripe-sdk/vendor/autoload.php';
// deprecated since version >=12.1 include IHC_PATH . 'classes/gateways/libraries/stripe-checkout/vendor/autoload.php';

if ( empty( $_GET['sessionId'] ) ){
    die;
}

$key = get_option( 'ihc_stripe_checkout_v2_publishable_key' );
$secretKey = get_option( 'ihc_stripe_checkout_v2_secret_key' );
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
<span id="ihc_js_stripe_v2_settings"
    data-key='<?php echo $key;?>'
    data-session_id='<?php echo $_GET['sessionId'];?>'
></span>
<script src="<?php echo IHC_URL . 'assets/js/stripe_v2.js';?>"></script>
