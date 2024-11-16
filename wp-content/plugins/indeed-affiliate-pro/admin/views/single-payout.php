<?php
\Indeed\Uap\Admin\Datatable::Scripts( '', 'payout_payments' );
?>
<div class="uap-payout-setup-step-2-content">
  <div class="uap-new-payout-inside">
        <?php if ( is_array( $data['payout_data'] ) && count( $data['payout_data'] ) > 0 ):?>
          <form method="post" action="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=manage_payouts' );?>" >

        <div class="uap-payout-setup-step-2-content">
            <h3 class="uap-new-payout-wrapper-content-title"><?php esc_html_e( 'Payout Summary', 'uap');?></h3>
            <div class="uap-new-payout-wrapper-content-title-description"><?php esc_html_e( 'Overview of Payout Details: Breakdown of Payments to Affiliates', 'uap');?></div>
            <table class="uap-payout-summary-table">
              <tr>
                <th><?php esc_html_e( 'Payout Method', 'uap' );?></th>
                <td><?php echo $data['payout_methods'][$data['payout_data']['method']];?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Date Range', 'uap' );?></th>
                <td><?php echo uap_convert_date_to_us_format($data['payout_data']['start_time'])
                              . ' - ' . uap_convert_date_to_us_format($data['payout_data']['end_time']);?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Affiliates', 'uap' );?></th>
                <td><?php if (!empty( $data['payments'] ) ){
                  echo count( $data['payments'] );
                }?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Referrals', 'uap' );?></th>
                <td><?php echo $data['referrals_count'];?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Payments', 'uap' );?></th>
                <td><?php if (!empty( $data['payments'] ) ){
                  echo count( $data['payments'] );
                }?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Created time', 'uap' );?></th>
                <td><?php if (!empty( $data['payments'] ) ){
                  echo uap_convert_date_to_us_format( $data['payout_data']['created_time'] );
                }?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Status', 'uap' );?></th>
                <td>
                    <?php if ( isset( $data['status'] ) && (int)$data['status'] === 2 ):?>
                        <?php echo esc_html_e('Completed', 'uap');?>
                    <?php else :?>
                        <?php echo esc_html_e('Processing', 'uap');?>
                    <?php endif;?>
                </td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Total Amount to Pay', 'uap' );?></th>
                <td><?php echo uap_format_price_and_currency( uapCurrency($data['payout_data']['currency']), $data['payout_data']['amount']);?></td>
              </tr>
            </table>

        </div>

        <div class="uap-payout-setup-2-next-page">
            <?php if ( isset( $data['status'] ) && (int)$data['status'] === 2 ):?>
                <div class="uap-first-button button uap-js-payout-change-status-all-payments" data-id="<?php echo $data['payout_data']['id'];?>" data-status="1" ><?php esc_html_e( 'Mark as Processing', 'uap');?></div>
            <?php else :?>
                <div class="uap-first-button button uap-js-payout-change-status-all-payments" data-id="<?php echo $data['payout_data']['id'];?>" data-status="2"><?php esc_html_e( 'Mark as Completed', 'uap');?></div>
            <?php endif;?>
            <div class="uap-first-button button uap-js-single-payout-generate-csv" data-id="<?php echo $data['payout_data']['id'];?>" data-status="2"><?php esc_html_e( 'Generate CSV', 'uap');?></div>
            <a href="" class="uap-first-button uap-js-payouts-csv-file uap-display-none uap-js-payouts-csv-file-for-payout-<?php echo $data['payout_data']['id'];?>"><?php esc_html_e('Download File', 'uap');?></a>
        </div>

        </br></br>

        <div class="uap-payout-setup-2-next-page">

        </div>

        <h3 class="uap-new-payout-wrapper-content-title uap-new-payout-wrapper-table-title"><?php esc_html_e( 'Payments to Affiliates', 'uap');?></h3>
        <div class="uap-new-payout-wrapper-content-title-description"><?php esc_html_e( 'List of Payments made by current Payout', 'uap');?></div>
              <div class="uap-js-messages-for-datatable" ></div>


              <table class="uap-js-list-payments-for-payouts uap-display-none" id="uap-dashboard-table">
                  <thead class="" >
                    <tr class="" >
                        <th class="uap-max-width-250"><?php esc_html_e('Affiliate Email');?></th>
                        <th class="uap-max-width-150"><?php esc_html_e('Affiliate Name');?></th>
                        <th class="uap-max-width-150"><?php esc_html_e('Amount');?></th>
                        <th class="uap-max-width-250"><?php esc_html_e('Payout Method');?></th>
                        <th class="uap-max-width-100"><?php esc_html_e('Referrals');?></th>
                        <th class="uap-max-width-250"><?php esc_html_e('Payment Details');?></th>
                        <th class="uap-max-width-150"><?php esc_html_e('Status');?></th>
                        <th class="uap-max-width-150"><?php esc_html_e('Actions');?></th>
                    </tr>
                  </thead>
                  <tbody class="" >
                      <?php foreach ( $data['payments'] as $paymentData ):?>
                          <tr class="">
                              <td class=""><a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=user_profile&affiliate_id=' . $paymentData['affiliate_id']);?>" target="_blank"><?php echo $paymentData['email'];?></a></td>
                              <td class=""><?php echo $paymentData['name'];?></td>
                              <td class=""><?php echo isset( $paymentData['amount'] ) ? uap_format_price_and_currency( uapCurrency($paymentData['currency']), $paymentData['amount'] ) : uap_format_price_and_currency( uapCurrency($paymentData['currency']), 0);?></td>
                              <td class=""><?php
                                  if ( $paymentData['payment_type'] === 'stripe_v3' ){
                                      $paymentData['payment_type'] = 'stripe';
                                  }
                                  echo isset( $data['payout_methods'][$paymentData['payment_type']] ) ? $data['payout_methods'][$paymentData['payment_type']] : $paymentData['payment_type'];
                              ?></td>
                              <td class=""><?php echo count($paymentData['referrals']);?></td>
                              <td class=""><?php if ( $paymentData['payment_details'] ){
                                    $paymentDetails = json_decode( $paymentData['payment_details'], true );// since version 9.1
                                    if ( isset( $paymentDetails['uap_affiliate_paypal_email'] )
                                    && !empty( $paymentDetails['uap_affiliate_paypal_email']['label'] )
                                    && !empty( $paymentDetails['uap_affiliate_paypal_email']['value'] ) ){
                                        echo $paymentDetails['uap_affiliate_paypal_email']['label'] . ' : ' . $paymentDetails['uap_affiliate_paypal_email']['value'];
                                    } else if ( isset( $paymentDetails['uap_affiliate_bank_transfer_data'] )
                                    && !empty( $paymentDetails['uap_affiliate_bank_transfer_data']['label'] )
                                    && !empty( $paymentDetails['uap_affiliate_bank_transfer_data']['value'] ) ){
                                        echo $paymentDetails['uap_affiliate_bank_transfer_data']['label'] . ' : ' . $paymentDetails['uap_affiliate_bank_transfer_data']['value'];
                                    }
                              }?></td>
                              <td><?php
                              switch ($paymentData['status']){
                                case 0:
                                    ?>
                                    <div class="referral-status-refuse uap-status uap-status-failed"><?php
                                        esc_html_e('Failed', 'uap');
                                    ?></div>
                                  <?php
                                  break;
                                case 1:
                                    ?>
                                    <div class="referral-status-unverified uap-status uap-status-inactive"><?php
                                        esc_html_e('Processing', 'uap');
                                    ?>
                                    </div>
                                  <?php
                                  break;
                                case 2:
                                    ?>
                                    <div class="referral-status-verified uap-status uap-status-active"><?php
                                        esc_html_e('Paid', 'uap');
                                    ?></div>
                                  <?php
                                  break;
                              }
                              ?>
                            </td>
                            <td>
                              <?php
                                if ($paymentData['status']==2){
                                  ?>
                                  <div class="refferal-chang-status uap-js-payout-change-status-for-payment"
                                    data-id="<?php echo esc_attr($paymentData['id']);?>"
                                    data-status="1"><?php esc_html_e('Mark as Processing', 'uap');?>
                                  </div>
                                  <?php
                                } else if ($paymentData['status']==1){
                                  ?>
                                  <div class="refferal-chang-status uap-js-payout-change-status-for-payment"
                                    data-id="<?php echo esc_attr($paymentData['id']);?>"
                                    data-status="2"><?php esc_html_e('Mark as Paid', 'uap');?>
                                  </div>
                                  <?php
                                }
                              ?>
                              <div class="refferal-chang-status" ><a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=view_payment&id=' . $paymentData['id'] ); ?>" class="" target"_blank"><?php  echo esc_html__( 'View', 'uap' ); ?></a></div>
                               <div class="refferal-chang-status uap-js-payout-delete-payment"
                                  data-id="<?php echo esc_attr($paymentData['id']);?>"><?php esc_html_e('Remove', 'uap');?>
                                </div>

                            </td>
                          </tr>
                      <?php endforeach;?>
                  </tbody>
              </table>

              <div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>



          </form>
        <?php else :?>
            <div class=""><?php echo esc_html__('No entries for selected Filters.', 'uap');?></div>
        <?php endif;?>
    </div>

</div>
