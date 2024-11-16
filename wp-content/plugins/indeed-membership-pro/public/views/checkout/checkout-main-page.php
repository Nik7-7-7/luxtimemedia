<?php
  wp_enqueue_style( 'ihc_templates_style', IHC_URL . 'assets/css/templates.min.css', [], '12.7' );

  if($data['custom_css']){
    wp_register_style( 'dummy-handle', false );
    wp_enqueue_style( 'dummy-handle' );
    wp_add_inline_style( 'dummy-handle', stripslashes($data['custom_css']) );
  }
 ?>
<div class="ihc-checkout-page-wrapp" id="ihc_checkout_page_wrapp">
  <div class="ihc-js-discount-success-message" data-value="<?php echo esc_html__( 'Discount successfully applied.', 'ihc' );?>" ></div>
  <div class="ihc-js-discount-error-message" data-value="<?php echo esc_html__( 'This Discount Code is invalid.', 'ihc' );?>" ></div>
  <div class="ihc-js-dynamic-price-error-message" data-value="<?php echo esc_html__('Price is invalid.', 'ihc');?>"></div>
  <div class="ihc-js-dynamic-price-success-message" data-value="<?php echo esc_html__('Price successfully applied.', 'ihc');?>"></div>

  <?php if($data['isRegistered']){?>
   <form method="post" name="checkout" id="checkout" class="ihc-checkout-page-wrapp" enctype="multipart/form-data">
     <input type="hidden" name="uid" value="<?php echo esc_attr($data['uid']);?>" />
  <?php }?>
  <div class="ihc-checkout-page-left-side ihc-checkout-page-one-column">

   <!-- Customer Form -->
   <div id="ihc-checout-page-purchase-customer-form-section">
        <?php include IHC_PATH . 'public/views/checkout/checkout-customer-form.php';?>
  </div>

  <?php if ( isset($data['settings']['ihc_checkout_payment_method_position']) && $data['settings']['ihc_checkout_payment_method_position'] == 1){
        //do nothing
     }else{
     ?>
      <!-- Payment Method -->
      <div id="ihc-checout-page-purchase-payment-method-section">
          <?php include IHC_PATH . 'public/views/checkout/checkout-payment-method.php';?>
      </div>
   <?php } ?>

  </div>

  <div class="ihc-checkout-page-right-side ihc-checkout-page-one-column">

    <!-- SUBSCRIPTION DETAILS -->
    <div id="ihc-checout-page-purchase-subscription-details-section">
        <?php include IHC_PATH . 'public/views/checkout/checkout-subscription-details.php';?>
    </div>

    <?php if ( isset($data['dynamicData']['show']) || isset($data['couponData']['show']) ): ?>
    <div class="ihc-checkout-page-box-extra-options">

      <!-- DYNAMIC PRICE -->
      <?php if ( isset($data['dynamicData']['show']) ): ?>
          <div class="ihc-checkout-page-box-wrapper ihc-dynamic-price-wrapper">
            <div class="ihc-checkout-page-additional-info"><?php echo esc_html($data['messages']['ihc_checkout_dynamic_field_message']);?></div>
            <div class="ihc-checkout-page-input-left">
              <input class="ihc-checkout-page-input" type="number" min='<?php echo esc_attr($data['dynamicData']['min']); ?>' max='<?php echo esc_attr($data['dynamicData']['max']); ?>' step="<?php echo esc_attr($data['dynamicData']['step']); ?>" value="" name="ihc-dynamic-price" id="ihc-dynamic-price"/>
            </div>
            <div class="ihc-checkout-page-apply-right">
              <button type="submit" class="ihc-checkout-page-apply" id="ihc-apply-dynamic-price" name="ihc-apply-dynamic-price" value="<?php echo esc_attr($data['messages']['ihc_checkout_dynamic_field_button']);?>"><?php echo esc_html($data['messages']['ihc_checkout_dynamic_field_button']);?></button>
            </div>
            <div class="ihc-clear"></div>
            <div class="ihc-checkout-page-used">
                <?php include IHC_PATH . 'public/views/checkout/checkout-dynamic-price-set.php';?>
            </div>
            <div id="ihc-dynamic-price-error-wrap" class="ihc-checkout-alert ihc-display-none"></div>
          </div>
      <?php endif;?>

      <!-- COUPON -->
      <?php if ( isset($data['couponData']['show']) ): ?>
          <div class="ihc-checkout-page-box-wrapper ihc-discount-wrapper">
            <div class="ihc-checkout-page-additional-info"><?php echo esc_html($data['messages']['ihc_checkout_coupon_field_message']);?></div>
            <div class="ihc-checkout-page-input-left">
              <input class="ihc-checkout-page-input" id="ihc-discount" type="text" value="" name="ihc-discount"/>
            </div>
            <div class="ihc-checkout-page-apply-right">
              <button type="submit" class="ihc-checkout-page-apply" name="ihc-apply-discount" id="ihc-apply-discount" value="<?php echo esc_attr($data['messages']['ihc_checkout_apply_button']);?>"><?php echo esc_html($data['messages']['ihc_checkout_apply_button']);?></button>
            </div>

            <div class="ihc-clear"></div>

            <div class="ihc-checkout-page-used">
                <?php include IHC_PATH . 'public/views/checkout/checkout-coupon-used.php';?>
            </div>

            <div id="ihc-discount-error-wrap" class="ihc-checkout-alert ihc-display-none"></div>

          </div>
      <?php endif;?>

    </div>
  <?php endif;?>

    <!-- TAXES -->
    <div id="ihc-checout-page-taxes-section">
        <?php include IHC_PATH . 'public/views/checkout/checkout-taxes.php';?>
    </div>

    <?php if ( isset($data['settings']['ihc_checkout_payment_method_position']) && $data['settings']['ihc_checkout_payment_method_position'] == 1){?>
        <!-- Payment Method -->
        <div id="ihc-checout-page-purchase-payment-method-section">
            <?php include IHC_PATH . 'public/views/checkout/checkout-payment-method.php';?>
        </div>
     <?php } ?>

    <!-- SUBTOTAL -->
    <div id="ihc-checout-page-subtotal-section">
        <?php include IHC_PATH . 'public/views/checkout/checkout-subtotal.php';?>
    </div>

    <!-- PRIVACY POLICY -->
    <?php if ( $data['privacyData'] ):?>
        <div class="ihc-checkout-page-box-wrapper ihc-terms-wrapper">
          <p><?php echo esc_ump_content($data['privacyData']);?></p>
        </div>
    <?php endif;?>

    <!-- PURCHASE BUTTON -->
    <div id="ihc-checout-page-purchase-button-section">
        <?php include IHC_PATH . 'public/views/checkout/checkout-purchase-button.php';?>
    </div>

  </div>

  <?php if($data['isRegistered']){?>

      <?php if ( isset( $data['order_id'] ) && $data['order_id'] !== '' && isset( $data['ihc-finish-payment'] ) && $data['ihc-finish-payment'] === 1 ):?>
          <input type="hidden" value="<?php echo esc_ump_content($data['order_id']);?>" name="order_id" />
          <input type="hidden" value="1" name="ihc-finish-payment" />
      <?php endif;?>
      <input type="hidden" value="<?php echo wp_create_nonce( 'ihc_payment_from_account_page' );?>" name="ihcfinishpaymentcd" />

  </form>
  <?php } ?>

</div>
