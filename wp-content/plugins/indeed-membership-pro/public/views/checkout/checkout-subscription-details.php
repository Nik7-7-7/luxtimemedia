<div class="ihc-checkout-page-box-wrapper ihc-product-details-wrapper">
  <div class="ihc-product-details">
    <?php
    switch($data['levelData']['access_type']){

      case 'regular_period':
          if(isset($data['preparePaymentData']['first_amount']) && ($data['preparePaymentData']['first_amount'] != '' || $data['preparePaymentData']['first_amount'] === 0)){?>
            <!-- Recurring with Trial-->
            <table class="ihc-product-details-table">
              <tr class="ihc-product-details-table-name-row">
                <td>
                  <div class="ihc-product-name"><?php echo esc_html($data['levelData']['label']);?></div>
                  <div class="ihc-product-description"><?php echo esc_html($data['levelData']['short_description']);?></div>
                </td>
                <td></td>
              </tr>
              <?php if(isset($data['preparePaymentData']['prorate_from']) && $data['preparePaymentData']['prorate_from'] != '' && get_option('ihc_prorate_show_details_on_checkout', 0) == 1){
                include IHC_PATH . 'public/views/checkout/checkout-prorate-details.php';
              }?>
              <tr>
                <td>
                  <div class="ihc-product-trial-fee-label"><?php echo esc_html($data['messages']['ihc_checkout_price_initial']);?></div>
                  <div class="ihc-price-description"></div>
                </td>
                <td>
                  <div class="ihc-product-price" id="ihc-initial-payment-price">
                    <span id="ihc-initial-payment-price">
                      <?php if($data['preparePaymentData']['initial_first_amount'] > 0){
                        echo esc_html(ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['initial_first_amount']));
                      }else{
                        echo esc_html($data['messages']['ihc_checkout_price_free']);
                      }
                        ?>
                    </span>

                  <span class="ihc-product-price-detail"> <?php  echo \Indeed\Ihc\Checkout::getForPeriod( $data['messages']['ihc_checkout_price_for'], $data['preparePaymentData']['first_payment_interval_value'], $data['preparePaymentData']['first_payment_interval_type'] ); ?> </span>
                  </div>
                </td>
              </tr>

              <tr id="ihc-initial-payment-discount" <?php echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && count($data['couponData']['details']) > 0 && isset($data['preparePaymentData']['first_discount']) && $data['preparePaymentData']['first_discount'] > 0) ? '' : 'class="ihc-display-none"'; ?>>
                <td><div class="ihc-product-fee-label-extra"><?php echo esc_html($data['messages']['ihc_checkout_price_discount']);?> (<span id="ihc-main-payment-discount-value">
                   <?php  echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && isset($data['couponData']['details']['discount_display'])) ?  $data['couponData']['details']['discount_display'] : ''; ?></span>)</div></td>
                <td><div class="ihc-product-price-extra">-<?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['first_discount']);?></div></td>
              </tr>
              <?php if(isset($data['preparePaymentData']['first_amount_taxes_details']) && is_array($data['preparePaymentData']['first_amount_taxes_details']) && count($data['preparePaymentData']['first_amount_taxes_details']) > 0){ ?>
              <tr id="ihc-initial-payment-taxes">
                <td colspan="2">
                    <table class="ihc-product-extra-details-table">
                      <?php foreach ($data['preparePaymentData']['first_amount_taxes_details']['items'] as $item){ ?>
                      <tr>
                        <td><div class="ihc-product-fee-label-extra"><?php echo esc_ump_content($item['label']) . ' (' . esc_ump_content($item['percentage']) . '%)'; ?></div></td>
                        <td><div class="ihc-product-price-extra"><?php echo esc_ump_content($item['print_value']); ?></div></td>
                      </tr>
                    <?php } ?>
                    </table>
                </td>
              </tr>
            <?php } ?>


              <tr class="ihc-then-payment-wrapper">
                <td>
                  <div class="ihc-product-main-fee-label"><?php echo esc_ump_content($data['messages']['ihc_checkout_price_then']);?></div>
                  <div class="ihc-price-description"></div>
                </td>
                <td>
                  <div class="ihc-product-price">
                    <span id="ihc-product-price"><?php echo esc_ump_content(ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['dynamic_price']));?></span>
                    <span class="ihc-product-price-detail"><?php  echo \Indeed\Ihc\Checkout::getForPeriod( $data['messages']['ihc_checkout_price_every'], $data['preparePaymentData']['interval_value'], $data['preparePaymentData']['interval_type'], TRUE ); ?></span>
                  </div>
                </td>
              </tr>

               <tr id="ihc-main-payment-discount" <?php echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && count($data['couponData']['details']) > 0 && isset($data['preparePaymentData']['discount_value']) && $data['preparePaymentData']['discount_value'] > 0) ? '' : 'class="ihc-display-none"';?> >
                 <td><div class="ihc-product-fee-label-extra"><?php echo esc_ump_content($data['messages']['ihc_checkout_price_discount']);?> (<span id="ihc-main-payment-discount-value">
                   <?php  echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && isset($data['couponData']['details']['discount_display'])) ?  $data['couponData']['details']['discount_display'] : ''; ?></span>)</div></td>
                 <td><div class="ihc-product-price-extra">-<?php echo esc_ump_content(ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['discount_value']));?></div></td>
              </tr>


              <?php if(isset($data['preparePaymentData']['taxes_details']) && is_array($data['preparePaymentData']['taxes_details'])  && count($data['preparePaymentData']['taxes_details']) > 0){ ?>
              <tr id="ihc-main-payment-taxes">
                <td colspan="2">
                    <table class="ihc-product-extra-details-table">
                      <?php foreach ($data['preparePaymentData']['taxes_details']['items'] as $item){ ?>
                      <tr>
                        <td><div class="ihc-product-fee-label-extra"><?php echo esc_ump_content($item['label']).' ('.esc_html($item['percentage']).'%)'; ?></div></td>
                        <td><div class="ihc-product-price-extra"><?php echo esc_ump_content($item['print_value']); ?></div></td>
                      </tr>
                    <?php } ?>
                    </table>
                </td>
              </tr>
            <?php } ?>

            </table>
          <?php }else{ ?>
            <!-- Standard Recurring -->
            <table class="ihc-product-details-table">
              <tr class="ihc-product-details-table-name-row">
                <td>
                  <div class="ihc-product-name"><?php echo esc_ump_content($data['levelData']['label']);?></div>
                  <div class="ihc-product-description"><?php echo esc_ump_content($data['levelData']['short_description']);?></div>
                </td>
                <td>
                  <div class="ihc-product-price">
                      <span id="ihc-product-price"><?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['dynamic_price']);?></span>
                      <span class="ihc-product-price-detail"> <?php  echo \Indeed\Ihc\Checkout::getForPeriod( $data['messages']['ihc_checkout_price_every'], $data['preparePaymentData']['interval_value'], $data['preparePaymentData']['interval_type'], TRUE ); ?></span>
                  </div>
                </td>
              </tr>
              <?php if(isset($data['preparePaymentData']['prorate_from']) && $data['preparePaymentData']['prorate_from'] != '' && get_option('ihc_prorate_show_details_on_checkout', 0) == 1){
                include IHC_PATH . 'public/views/checkout/checkout-prorate-details.php';
              }?>
              <tr id="ihc-main-payment-discount" <?php echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && count($data['couponData']['details']) > 0  && isset($data['preparePaymentData']['discount_value']) && $data['preparePaymentData']['discount_value'] > 0) ? '' : 'class="ihc-display-none"' ?>>
                <td><div class="ihc-product-fee-label-extra"><?php echo esc_ump_content($data['messages']['ihc_checkout_price_discount']);?> (<span id="ihc-main-payment-discount-value">
                  <?php  echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && isset($data['couponData']['details']['discount_display'])) ?  $data['couponData']['details']['discount_display'] : ''; ?></span>)</div></td>
                <td><div class="ihc-product-price-extra">-<?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['discount_value']);?></div></td>
              </tr>
              <?php if(isset($data['preparePaymentData']['taxes_details']) && is_array($data['preparePaymentData']['taxes_details']) && count($data['preparePaymentData']['taxes_details']) > 0){ ?>
              <tr id="ihc-main-payment-taxes">
                <td colspan="2">
                    <table class="ihc-product-extra-details-table">
                      <?php foreach ($data['preparePaymentData']['taxes_details']['items'] as $item){ ?>
                      <tr>
                        <td><div class="ihc-product-fee-label-extra"><?php echo esc_ump_content($item['label']).' (' . esc_ump_content($item['percentage']) . '%)'; ?></div></td>
                        <td><div class="ihc-product-price-extra"><?php echo esc_ump_content($item['print_value']); ?></div></td>
                      </tr>
                    <?php } ?>
                    </table>
                </td>
              </tr>
            <?php } ?>

            </table>
         <?php }
      break;
      case 'limited':
      case 'date_interval':
      default:
      ?>
      <!-- One Time Membership -->
      <table class="ihc-product-details-table">
        <tr class="ihc-product-details-table-name-row">
          <td>
            <div class="ihc-product-name"><?php echo esc_ump_content($data['levelData']['label']);?></div>
            <div class="ihc-product-description"><?php echo esc_ump_content( $data['levelData']['short_description'] );?></div>
          </td>
          <td>
            <div class="ihc-product-price"><?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['dynamic_price']);?></div>
          </td>
        </tr>
        <?php if(isset($data['preparePaymentData']['prorate_from']) && $data['preparePaymentData']['prorate_from'] != '' && get_option('ihc_prorate_show_details_on_checkout', 0) == 1){
          include IHC_PATH . 'public/views/checkout/checkout-prorate-details.php';
        }?>
        <tr id="ihc-main-payment-discount" <?php echo (isset($data['couponData']['details']) && is_array($data['couponData']['details']) && count($data['couponData']['details']) > 0  && isset($data['preparePaymentData']['discount_value']) && $data['preparePaymentData']['discount_value'] > 0) ? '' : 'class="ihc-display-none"'; ?> >
          <td><div class="ihc-product-fee-label-extra"><?php echo isset( $data['messages']['ihc_checkout_price_discount'] ) ? $data['messages']['ihc_checkout_price_discount'] : $data['messages']['ihc_checkout_price_discount'];?> (<span id="ihc-main-payment-discount-value"><?php echo isset( $data['couponData']['details']['discount_display'] ) ? $data['couponData']['details']['discount_display'] : '';?></span>)</div></td>
          <td><div class="ihc-product-price-extra">-<?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['discount_value']);?></div></td>
        </tr>
        <?php if(isset($data['preparePaymentData']['taxes_details']) && is_array($data['preparePaymentData']['taxes_details']) && count($data['preparePaymentData']['taxes_details']) > 0){ ?>
        <tr id="ihc-main-payment-taxes">
          <td colspan="2">
              <table class="ihc-product-extra-details-table">
                <?php foreach ($data['preparePaymentData']['taxes_details']['items'] as $item){ ?>
                <tr>
                  <td><div class="ihc-product-fee-label-extra"><?php echo esc_ump_content($item['label']).' ('.esc_html($item['percentage']).'%)'; ?></div></td>
                  <td><div class="ihc-product-price-extra"><?php echo esc_ump_content($item['print_value']); ?></div></td>
                </tr>
              <?php } ?>
              </table>
          </td>
        </tr>
      <?php } ?>
      </table>

      <?php
      break;

    }

     ?>
  </div>
</div>
