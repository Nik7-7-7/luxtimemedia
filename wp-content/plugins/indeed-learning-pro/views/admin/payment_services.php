<div class="ulp-page-title">
    <?php esc_html_e('Payments Gateways', 'ulp');?>
</div>

<div class="ulp-payment-list-wrapper">
  <div class="ulp-payment-box-wrap">
    <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_paypal');?>">
    <div class="ulp-payment-box <?php echo esc_attr($data['paypal']['active']); ?>">
      <div class="ulp-payment-box-title">PayPal</div>
      <div class="ulp-payment-box-bottom">Settings: <span><?php echo esc_html($data['paypal']['settings']); ?></span></div>
    </div>
   </a>
  </div>
  <div class="ulp-payment-box-wrap">
    <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_bt');?>">
    <div class="ulp-payment-box <?php echo esc_attr($data ['bt']['active']);?>">
      <div class="ulp-payment-box-title">Bank Transfer</div>
      <div class="ulp-payment-box-bottom">Settings: <span><?php echo esc_html($data['bt']['settings']); ?></span></div>
    </div>
   </a>
  </div>
  <div class="ulp-payment-box-wrap">
    <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_stripe');?>">
    <div class="ulp-payment-box <?php echo esc_attr($data ['stripe']['active']);?>">
      <div class="ulp-payment-box-title">Stripe</div>
      <div class="ulp-payment-box-bottom">Settings: <span><?php echo esc_html($data['stripe']['settings']); ?></span></div>
    </div>
   </a>
  </div>
  <div class="ulp-payment-box-wrap">
    <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_2checkout');?>">
    <div class="ulp-payment-box <?php echo esc_attr($data ['2checkout']['active']);?>">
      <div class="ulp-payment-box-title">2Checkout</div>
      <div class="ulp-payment-box-bottom">Settings: <span><?php echo esc_html($data['2checkout']['settings']); ?></span></div>
    </div>
   </a>
  </div>



</div>
