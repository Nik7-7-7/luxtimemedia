<?php wp_register_script( 'ihc-braintree', IHC_URL . 'assets/js/braintree.js', [], '12.7' );?>
<?php
global $wp_version;
if ( empty( $data['isRegistered'] ) ){
  // user is not registered
  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
      wp_add_inline_script( 'ihc-braintree', "window.ihcCheckoutIsRegister='1';" );
  } else {
      wp_localize_script( 'ihc-braintree', 'window.ihcCheckoutIsRegister', '1' );
  }
} else {
  //
  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
      wp_add_inline_script( 'ihc-braintree', "window.ihcCheckoutIsRegister='0';" );
  } else {
      wp_localize_script( 'ihc-braintree', 'window.ihcCheckoutIsRegister', '0' );
  }
}
?>
<?php wp_enqueue_script( 'ihc-braintree' );?>
<div class="ihc-braintree-form-wrapper">
  <?php if($data['metaData']['ihc_braintree_sandbox']){?>
    <div class="ihc-braintree-form-sandbox-block-wrapper">
      <div class="ihc-braintree-form-sandbox-block"><?php esc_html_e('Sandbox Mode ', 'ihc'); ?></div>
    </div>
  <?php }?>
 <div class="ihc-braintree-form-card-name-wrap">
   <input type="text" class="ihc-braintree-input" value="<?php echo esc_attr($data['fields']['ihc_braintree_cardholderName']['value']);?>" name="ihc_braintree_cardholderName" placeholder="<?php echo esc_attr('Name on the Card *', 'ihc');?>"/>
 </div>
 <div class="ihc-braintree-form-card-number-wrap">
   <div class="ihc-braintree-form-card-number-label"><?php esc_html_e('Credit Card Number', 'ihc');?> <span class="ihc-required-sign">*</span></div>
   <div class="ihc-braintree-form-card-element">
     <input type="number" class="ihc-braintree-input" value="<?php echo esc_attr($data['fields']['ihc_braintree_card_number']['value']);?>" name="ihc_braintree_card_number" placeholder="1234 1234 1234 1234"/>
   </div>
 </div>
 <div class="ihc-braintree-form-details-wrap">
   <div class="ihc-braintree-form-details-exp-wrap">
     <div class="ihc-braintree-form-details-exp-label"><?php echo esc_html('Expiration', 'ihc');?> <span class="ihc-required-sign">*</span></div>
     <div class="ihc-braintree-form-details-elements">
       <input type="number" class="ihc-braintree-input" value="<?php echo esc_attr($data['fields']['ihc_braintree_card_expire_month']['value']);?>" name="ihc_braintree_card_expire_month" placeholder="MM" min="1" max="12"/>
       <span class="ihc-braintree-form-details-exp-split">/</span>
       <input type="number" class="ihc-braintree-input" value="<?php echo esc_attr($data['fields']['ihc_braintree_card_expire_year']['value']);?>" name="ihc_braintree_card_expire_year" placeholder="YY" min="<?php echo esc_attr($data['fields']['ihc_braintree_card_expire_year']['min']);?>"
     max="<?php echo esc_attr($data['fields']['ihc_braintree_card_expire_year']['max']);?>"/>
   </div>
   </div>
   <div class="ihc-braintree-form-details-cvv-wrap">
     <div class="ihc-braintree-form-details-cvv-label"><?php esc_html_e('CVC', 'ihc');?> <span class="ihc-required-sign">*</span></div>
     <input type="number" class="ihc-braintree-input" value="<?php echo esc_attr($data['fields']['ihc_braintree_cvv']['value']);?>" name="ihc_braintree_cvv" placeholder="123"/>
   </div>
 </div>
 <div class="ihc-braintree-form-errors">
 </div>
</div>
