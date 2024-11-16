<?php
global $wp_version;
$settings = ihc_return_meta_arr( 'payment_stripe_connect' );
if ( $settings['ihc_stripe_connect_live_mode'] ){
    $accountNumber = $settings['ihc_stripe_connect_account_id'];
    $publicKey = $settings['ihc_stripe_connect_publishable_key'];
} else {
    $accountNumber = $settings['ihc_stripe_connect_test_account_id'];
    $publicKey = $settings['ihc_stripe_connect_test_publishable_key'];
}

$isRecurring = \Indeed\Ihc\Db\Memberships::isRecurring( $data['lid'] ) ? 1 : 0;
$lang = get_option( 'ihc_stripe_connect_locale_code' );
if ( $lang === false || $lang === null || $lang === '' ){
    $lang = 'en';
}

wp_enqueue_script( 'ihc-stripe-v3', 'https://js.stripe.com/v3/', [], false );
wp_register_script( 'ihc-stripe-connect', IHC_URL . 'assets/js/stripe-connect.js', [ 'jquery' ], '12.7' );
if ( version_compare ( $wp_version , '5.7', '>=' ) ){
    wp_add_inline_script( 'ihc-stripe-connect', "window.ihcStripeConnectAcctNumber='" . $accountNumber . "';" );
    wp_add_inline_script( 'ihc-stripe-connect', "window.ihcStripeConnectPublicKey='" . $publicKey . "';" );
    wp_add_inline_script( 'ihc-stripe-connect', "window.ihcStripeConnectRecurringSubscription='" . $isRecurring . "';" );
    wp_add_inline_script( 'ihc-stripe-connect', "window.ihcStripeConnectLang='" . $lang . "';" );
} else {
    wp_localize_script( 'ihc-stripe-connect', 'window.ihcStripeConnectAcctNumber', $accountNumber );
    wp_localize_script( 'ihc-stripe-connect', "window.ihcStripeConnectPublicKey", $publicKey );
    wp_localize_script( 'ihc-stripe-connect', "window.ihcStripeConnectRecurringSubscription", $isRecurring );
    wp_localize_script( 'ihc-stripe-connect', "window.ihcStripeConnectLang", $lang );
}

if ( empty( $data['isRegistered'] ) ){
  // user is not registered
  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
      wp_add_inline_script( 'ihc-stripe-connect', "window.ihcCheckoutIsRegister='1';" );
  } else {
      wp_localize_script( 'ihc-stripe-connect', 'window.ihcCheckoutIsRegister', '1' );
  }
} else {
  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
      wp_add_inline_script( 'ihc-stripe-connect', "window.ihcCheckoutIsRegister='0';" );
  } else {
      wp_localize_script( 'ihc-stripe-connect', 'window.ihcCheckoutIsRegister', '0' );
  }
}
wp_enqueue_script( 'ihc-stripe-connect' );

if ( isset($data['preparePaymentData']['first_amount']) && $data['preparePaymentData']['first_amount'] !== false && (int)$data['preparePaymentData']['first_amount'] === 0 ){
    // setup intent
    $doSetupIntent = true;
} else {
    // payment intent
    $doPaymentIntent = true;
}

if ( !empty( $data['uid'] ) ){
    $fullName = \Ihc_Db::getUserFulltName( $data['uid'] );
    $stripeObject = new \Indeed\Ihc\Gateways\StripeConnect();
    $paymentMethods = $stripeObject->getPaymentMethodsForUser( $data['uid'] );
} else {
    $fullName = '';
}

if (  ( !isset($data['preparePaymentData']['first_amount']) || $data['preparePaymentData']['first_amount'] == 0) && $data['preparePaymentData']['amount'] == 0 ){
    unset( $doSetupIntent );
    unset( $doPaymentIntent );
}
?>

<?php if ( !empty( $doPaymentIntent ) || !empty( $doSetupIntent ) ) :?>
<div class="ihc-stripe-connect-form-wrapper">
      <?php if ( !$settings['ihc_stripe_connect_live_mode'] ){ ?>
      <div class="ihc-stripe-connect-form-sandbox-block-wrapper">
        <div class="ihc-stripe-connect-form-sandbox-block"><?php esc_html_e('Sandbox Mode ', 'ihc'); ?></div>
      </div>
    <?php } ?>

    <?php
          $extraClassWrapp = '';
    ?>
    <?php if ( get_option('ihc_stripe_connect_saved_cards') && isset( $paymentMethods ) && is_array( $paymentMethods ) && count( $paymentMethods ) ):?>
      <?php
      $paymentMethodsCount = count( $paymentMethods ) - 1;
      $extraClassWrapp = 'ihc-display-none';
      ?>
      <div class="ihc-stripe-connect-saved-cards">
            <?php $i = 0;?>
            <?php foreach ( $paymentMethods as $paymentMethod ): ?>
                <?php
                      if ( $i === $paymentMethodsCount ){
                          $extraClass = 'ihc-stripe-connect-saved-card-wrapper-selected';
                      } else {
                          $extraClass = '';
                      }
                ?>
                <div class="ihc-stripe-connect-saved-card-wrapper <?php echo esc_attr($extraClass);?>">
                    <input type="radio" checked name="ihc_stripe_connect_payment_methods" class="ihc-js-stripe-connect-paymnt-methods" value="<?php echo esc_attr($paymentMethod['payment_method']);?>" />
                    <div class="ihc-stripe-connect-saved-card-details">
                      <span class="ihc-stripe-connect-saved-card-brand"><?php echo esc_html($paymentMethod['brand']);?></span>
                      <span class="ihc-stripe-connect-saved-card-number"><?php echo'**** ' . esc_html($paymentMethod['last4']);?></span>
                      <span class="ihc-stripe-connect-saved-card-exp"><?php echo esc_html($paymentMethod['exp_month']) . '/' . esc_html($paymentMethod['exp_year']);?></span>
                    </div>
                </div>
            <?php endforeach;?>

        <div class="ihc-stripe-connect-saved-card-wrapper">
            <input type="radio" name="ihc_stripe_connect_payment_methods" class="ihc-js-stripe-connect-paymnt-methods" value="new"  />
            <div class="ihc-stripe-connect-saved-card-details">
              <span class="ihc-stripe-connect-saved-card-new"><?php echo esc_html__('Add new Card', 'ihc');?></span>
            </div>
        </div>
      </div>
    <?php endif;?>

    <div class="ihc-js-stripe-connect-wrapp <?php echo esc_attr($extraClassWrapp);?>" >
        <div class="ihc-stripe-connect-form-card-name-wrap" >
            <input type="text" name="ihc_stripe_connect_full_name" class="ihc-stripe-connect-input ihc-js-stripe-connect-full-name" value="<?php echo esc_attr($fullName);?>"  placeholder="<?php esc_html_e('Name on the Card *', 'ihc');?>"/>
        </div>

        <div class="ihc-stripe-connect-form-card-number-wrap" >
            <div id="ihc-js-stripe-connect-card-element" class="ihc-stripe-connect-form-card-number-input" ></div>
            <div id="ihc-js-stripe-connect-card-errors" class="ihc-stripe-connect-form-card-errors"  role="alert"></div>
        </div>
        <div class="ihc-stripe-connect-form-wallets-wrap" id="ihc-stripe-connect-payment-request-button-wrap">
            <div id="ihc-stripe-connect-payment-request-button"></div>
        </div>

    </div>

    <?php if ( !empty( $doPaymentIntent ) ): ?>
        <div class="ihc-js-connect-do-payment-intent" ></div>
        <input type="hidden" name="stripe_payment_intent" value="" />
    <?php elseif ( !empty( $doSetupIntent ) ): ?>
        <div class="ihc-js-connect-do-setup-intent" ></div>
        <input type="hidden" name="stripe_setup_intent" value="" />
    <?php endif;?>


</div>

<?php endif;?>
