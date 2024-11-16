<!-- Coupon Used -->
<?php if ( isset($data['couponData']['details']['code']) && $data['couponData']['details']['code'] != ''): ?>
  <span class="ihc-checkout-page-used-label"><?php echo esc_html($data['messages']['ihc_checkout_coupon_field_used']);?>:</span>
  <span class="ihc-checkout-page-used-code"><?php echo esc_html($data['couponData']['details']['code']);?></span>
   <span class="ihc-checkout-page-used-remove ihc-js-checkout-page-do-remove-coupon" data-coupon="<?php echo esc_attr($data['couponData']['details']['code']);?>"><?php echo esc_html($data['messages']['ihc_checkout_remove']);?></span>
<?php endif;?>
