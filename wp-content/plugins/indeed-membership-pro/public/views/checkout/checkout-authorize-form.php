<?php wp_register_script( 'ihc-authorize', IHC_URL . 'assets/js/authorize.js', [], '12.7' );?>
<?php
global $wp_version;
if ( empty( $data['isRegistered'] ) ){
  // user is not registered
  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
      wp_add_inline_script( 'ihc-authorize', "window.ihcCheckoutIsRegister='1';" );
  } else {
      wp_localize_script( 'ihc-authorize', 'window.ihcCheckoutIsRegister', '1' );
  }
} else {
  if ( version_compare ( $wp_version , '5.7', '>=' ) ){
      wp_add_inline_script( 'ihc-authorize', "window.ihcCheckoutIsRegister='0';" );
  } else {
      wp_localize_script( 'ihc-authorize', 'window.ihcCheckoutIsRegister', '0' );
  }
}?>
<?php wp_enqueue_script( 'ihc-authorize' );?>
<div class="ihc-authorize-form-wrapper">
  <?php if($data['sandbox']){?>
    <div class="ihc-authorize-form-sandbox-block-wrapper">
      <div class="ihc-authorize-form-sandbox-block"><?php esc_html_e('Sandbox Mode ', 'ihc'); ?></div>
    </div>
  <?php }?>
 <div class="ihc-authorize-form-card-name-wrap">
   <input type="text" class="ihc-authorize-input" value="<?php echo esc_attr($data['fields']['ihcpay_cardholderName']['value']);?>" name="ihcpay_cardholderName" placeholder="<?php esc_html_e('Name on the Card *', 'ihc');?>"/>
 </div>

 <div class="ihc-authorize-form-details-wrap">
   <div class="ihc-authorize-form-details-card-number-wrap">
     <div class="ihc-authorize-form-card-number-label"><?php esc_html_e('Credit Card Number', 'ihc');?> <span class="ihc-required-sign">*</span></div>
     <input type="number" class="ihc-authorize-input" value="<?php echo esc_attr($data['fields']['ihcpay_card_number']['value']);?>" name="ihcpay_card_number" placeholder="1234 1234 1234 1234"/>

   </div>
   <div class="ihc-authorize-form-details-exp-wrap">
     <div class="ihc-authorize-form-details-exp-label"><?php esc_html_e('Expiration', 'ihc');?> <span class="ihc-required-sign">*</span></div>
     <input type="number" class="ihc-authorize-input" value="<?php echo esc_attr($data['fields']['ihcpay_card_expire']['value']);?>" name="ihcpay_card_expire" placeholder="MMYY"/>
   </div>
 </div>
 <div class="ihc-authorize-form-errors">
 </div>
</div>
