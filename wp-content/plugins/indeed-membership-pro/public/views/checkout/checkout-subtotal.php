
<div class="ihc-checkout-page-box-wrapper ihc-subtotal-wrapper">
  <div class="ihc-checkout-page-box-title"><?php echo esc_html($data['messages']['ihc_checkout_subtotal_title']);?></div>
  <?php
  switch($data['levelData']['access_type']){
    case 'regular_period':
        if(isset($data['preparePaymentData']['first_amount']) && ($data['preparePaymentData']['first_amount'] != '' || $data['preparePaymentData']['first_amount'] === 0)){?>
          <!-- Recurring with Trial-->
          <table  class="ihc-product-details-table ihc-subtotal-table">
            <tr>
              <td><div class="ihc-product-trial-fee-label"><?php echo esc_html($data['messages']['ihc_checkout_price_initial']);?></div></td>
              <td>
                <div class="ihc-product-price">
                  <span id="ihc-subtotal-initial-payment-price">
                    <?php if($data['preparePaymentData']['first_amount'] > 0){
                      echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['first_amount']);
                    }else{
                       echo esc_ump_content($data['messages']['ihc_checkout_price_free']);
                    }
                      ?>
                  </span>
                  <span class="ihc-product-price-detail">
                    <?php  echo \Indeed\Ihc\Checkout::getForPeriod( $data['messages']['ihc_checkout_price_for'], $data['preparePaymentData']['first_payment_interval_value'], $data['preparePaymentData']['first_payment_interval_type'] ); ?>
                  </span>
                </div>
              </td>
            </tr>
            <tr>
              <td><div class="ihc-product-main-fee-label"><?php echo esc_ump_content($data['messages']['ihc_checkout_price_then']);?></div>
              </td>
              <td>
                <div class="ihc-product-price">
                  <span id="ihc-subtotal-product-price"><?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['amount']);?></span>
                  <span class="ihc-product-price-detail"> <?php  echo \Indeed\Ihc\Checkout::getForPeriod( $data['messages']['ihc_checkout_price_every'], $data['preparePaymentData']['interval_value'], $data['preparePaymentData']['interval_type'], TRUE ); ?></span>
                </div>
              </td>
            </tr>
          </table>
       <?php }else{ ?>
          <!-- Standard Recurring -->
          <table  class="ihc-product-details-table ihc-subtotal-table">
            <tr>
              <td><div class="ihc-product-main-fee-label"><?php echo esc_ump_content($data['messages']['ihc_checkout_price_fee']);?></div>
              </td>
              <td>
                <div class="ihc-product-price">
                  <span id="ihc-subtotal-product-price"><?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['amount']);?></span>
                  <span class="ihc-product-price-detail"> <?php  echo \Indeed\Ihc\Checkout::getForPeriod( $data['messages']['ihc_checkout_price_every'], $data['preparePaymentData']['interval_value'], $data['preparePaymentData']['interval_type'], TRUE ); ?></span>
                </div>
              </td>
            </tr>
          </table>
       <?php }
    break;
    case 'limited':
    case 'date_interval':
    default:
    ?>
    <table  class="ihc-product-details-table ihc-subtotal-table">
      <tr>
        <td><div class="ihc-product-main-fee-label"><?php echo esc_ump_content($data['messages']['ihc_checkout_price_fee']);?></div>
        </td>
        <td>
          <div class="ihc-product-price">
            <span id="ihc-subtotal-product-price"><?php echo ihc_format_price_and_currency( $data['currency'], $data['preparePaymentData']['amount']);?></span>
          </div>
        </td>
      </tr>
    </table>
    <?php
    break;
  }
   ?>

</div>

    <div class="ihc-js-checkout-session" data-value="<?php
      echo base64_encode(json_encode(
        [
                              'first_amount'      => isset( $data['preparePaymentData']['first_amount'] ) ? $data['preparePaymentData']['first_amount'] : false,
                              'amount'            => isset( $data['preparePaymentData']['amount'] ) ? $data['preparePaymentData']['amount'] : false,
                              'currency'          => isset( $data['preparePaymentData']['currency'] ) ? $data['preparePaymentData']['currency'] : false,
                              'uid'               => isset( $data['preparePaymentData']['uid'] ) ? $data['preparePaymentData']['uid'] : false,
                              'lid'               => isset( $data['preparePaymentData']['lid'] ) ? $data['preparePaymentData']['lid'] : false,
                              'level_label'       => isset( $data['preparePaymentData']['level_label'] ) ? $data['preparePaymentData']['level_label'] : false,
        ]
      ));?>" ></div>
