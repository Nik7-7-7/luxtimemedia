<tr class="ihc-product-details-table-name-row ihc-prorate-description-wrapper">
  <td  colspan="2">
    <div class="ihc-prorate-description">
    <?php
    if(isset($data['preparePaymentData']['prorate_from_label']) && $data['preparePaymentData']['prorate_from_label'] != ''){
      if(isset($data['preparePaymentData']['prorate_type']) && $data['preparePaymentData']['prorate_type'] != ''  && $data['preparePaymentData']['prorate_type'] == 0){
          echo esc_html__('Downgraded from ', 'ihc').'<span>'.$data['preparePaymentData']['prorate_from_label'].'</span>';
        }else{
           echo esc_html__('Upgraded from ', 'ihc').'<span>'.$data['preparePaymentData']['prorate_from_label'].'</span>';
         }
    }
       ?>
     <?php if(isset($data['preparePaymentData']['prorate_old_days_left']) && $data['preparePaymentData']['prorate_old_days_left'] != ''
              && $data['preparePaymentData']['prorate_old_days_left'] > 0 && $data['preparePaymentData']['prorate_old_days_left'] < 365){
       echo esc_html__(' with ', 'ihc').$data['preparePaymentData']['prorate_old_days_left'].esc_html__(' remaining days ', 'ihc');
     }?>
    </div>
  </td>
</tr>
<?php
  if(isset($data['preparePaymentData']['prorate_old_amount_left']) && $data['preparePaymentData']['prorate_old_amount_left'] != ''  && $data['preparePaymentData']['prorate_old_amount_left'] != 0 &&
     isset($data['preparePaymentData']['prorate_initial_amount']) && $data['preparePaymentData']['prorate_initial_amount'] != ''  && $data['preparePaymentData']['prorate_initial_amount'] != 0){
 ?>
<tr class="ihc-product-details-table-name-row ihc-prorate-credit-description-wrapper">
<td>
  <div class="ihc-prorate-credit-description">
    <?php esc_html_e('Credit adjustment', 'ihc'); ?>
    <?php if($data['levelData']['access_type'] == 'regular_period'){
        esc_html_e(' for Initial Payment', 'ihc');
    } ?>
  </div>
</td>
<td>
  <div class="ihc-product-price ihc-prorate-credit">
  <?php
  if($data['preparePaymentData']['prorate_initial_amount'] <= $data['preparePaymentData']['prorate_old_amount_left']){
    echo ihc_format_price_and_currency( $data['currency'],$data['preparePaymentData']['prorate_initial_amount']);
  }else{
    echo ihc_format_price_and_currency( $data['currency'],$data['preparePaymentData']['prorate_old_amount_left']);
    echo esc_ump_content('<span class="ihc-product-price-detail">'.esc_html__('of ', 'ihc').ihc_format_price_and_currency( $data['currency'],$data['preparePaymentData']['prorate_initial_amount']).'</span>');
  }
   ?>
 </div>
</td>
</tr>
<?php } ?>
