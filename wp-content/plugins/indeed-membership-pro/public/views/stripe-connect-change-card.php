<?php
global $current_user;
$fullName = \Ihc_Db::getUserFulltName( $current_user->ID );
?>
<div class="ihc-js-stripe-connect-wrapp ihc-stripe-connect-change-card" >

    <div class="ihc-stripe-connect-form-card-name-wrap" >
        <input type="text" name="ihc_stripe_connect_full_name" class="ihc-stripe-connect-input ihc-js-stripe-connect-full-name" value="<?php echo esc_attr($fullName);?>"  placeholder="<?php esc_html_e('Name on the Card *', 'ihc');?>"/>
    </div>

    <div class="ihc-stripe-connect-form-card-number-wrap" >
        <div id="ihc-js-stripe-connect-card-element" class="ihc-stripe-connect-form-card-number-input" data-client="<?php echo esc_attr($clientSecret);?>" ></div>
        <div id="ihc-js-stripe-connect-card-errors" class="ihc-stripe-connect-form-card-errors"  role="alert"></div>
    </div>

    <div class="ihc-stripe-connect-form-wallets-wrap" id="ihc-stripe-connect-payment-request-button-wrap">
        <div id="ihc-stripe-connect-payment-request-button"></div>
    </div>

    <div class="iump-subscription-table-button">
      <div class="ihc-js-stripe-connect-change-card-submit iump-renew-subscription-button"><?php esc_html_e('Change Card', 'ihc');?></div>
    </div>

</div>
