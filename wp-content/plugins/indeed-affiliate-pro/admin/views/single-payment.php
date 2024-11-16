<div class="uap-payout-setup-step-2-content">
  <div class="uap-new-payout-inside">

        <div class="uap-payout-setup-step-2-content">
            <h3 class="uap-new-payout-wrapper-content-title"><?php esc_html_e( 'Payment Summary', 'uap');?></h3>
            <div class="uap-new-payout-wrapper-content-title-description"><?php esc_html_e( 'Overview of Payment Details: Breakdown of Referrals to Payment', 'uap');?></div>
            <table class="uap-payout-summary-table">
              <tr>
                <th><?php esc_html_e( 'Affiliate', 'uap' );?></th>
                <td><?php echo $data['payment_details']['email'];?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Amount', 'uap' );?></th>
                <td><?php echo uap_format_price_and_currency( uapCurrency( $data['payment_details']['currency'] ), $data['payment_details']['amount']);?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Payout Method', 'uap' );?></th>
                <td><?php
                    if ( $data['payment_details']['payment_type'] === 'stripe_v3' ){
                        $data['payment_details']['payment_type'] = 'stripe';
                    }
                    echo isset( $data['payout_methods'][$data['payment_details']['payment_type']] ) ? $data['payout_methods'][$data['payment_details']['payment_type']] : $data['payment_details']['payment_type'];
                ?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Status', 'uap' );?></th>
                <td><?php
                  switch ($data['payment_details']['status']){
                    case 0:
                            esc_html_e('Failed', 'uap');
                      break;
                    case 1:
                            esc_html_e('Processing', 'uap');
                      break;
                    case 2:
                            esc_html_e('Paid', 'uap');
                      break;
                  }
                  ?></td>
              </tr>
              <tr>
                <th><?php esc_html_e( 'Description', 'uap' );?></th>
                <td><?php if ( $data['payment_details']['payment_details'] ){
                        $paymentDetails = json_decode( $data['payment_details']['payment_details'], true );// since version 9.1
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
              </tr>
              <tr>
                <th><?php esc_html_e( 'Created time', 'uap' );?></th>
                <td><?php echo uap_convert_date_to_us_format($data['payment_details']['create_date']);?></td>
              </tr>
            </table>

        </div>

        <div class="uap-payout-setup-2-next-page">
            <select class="uap-js-single-payment-new-status uap-form-select uap-form-element uap-form-element-select uap-form-select"><?php foreach ( $data['status_types'] as $type => $label ):?>
                <?php if ( (int)$data['payment_details']['status'] === (int)$type ){
                    continue;
                }?>
                <option value="<?php echo $type;?>" ><?php echo $label;?></option>
            <?php endforeach;?></select>
            <div class="uap-first-button button-small uap-js-payment-change-status" data-id="<?php echo $data['payment_details']['id'];?>" ><?php esc_html_e('Update Status', 'uap');?></div>
        </div>

        <?php if ( !empty($data['payment_method_details'])):?>

        <h3 class="uap-new-payout-wrapper-content-title uap-new-payout-wrapper-table-title"><?php echo esc_html__( 'Affiliate Payment details', 'uap' );?></h3>
        <div class="uap-new-payout-wrapper-content-title-description"><?php echo esc_html__( 'Payment details submitted by affiliates for payout processing', 'uap' );?></div>
        <div class="uap-new-payout-payment-details"><?php echo $data['payment_method_details'];?></div>

        <?php endif;?>

        <h3 class="uap-new-payout-wrapper-content-title uap-new-payout-wrapper-table-title"><?php esc_html_e( 'Referrals', 'uap');?></h3>
        <div class="uap-new-payout-wrapper-content-title-description"><?php esc_html_e( 'List of Referrals for current Payment', 'uap');?></div>
        <div class="uap-js-messages-for-datatable" ></div>

        <?php
        \Indeed\Uap\Admin\Datatable::Scripts( '', 'referrals_for_payment' );

        // ready for reference links
        $available_systems = uap_get_active_services();
        if (!empty($available_systems['woo'])){
          $woo_order_base_link = admin_url('post.php?post=');// must add &action=edit after id
        }
        if (!empty($available_systems['ulp'])){
          $ulp_order_base_link = admin_url('post.php?post=');// must add &action=edit after id
        }
        if (!empty($available_systems['edd'])){
          $edd_order_base_link = admin_url('edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=');
        }
        if (!empty($available_systems['ump'])){
          $ump_order_base_link = admin_url('admin.php?page=ihc_manage&tab=order-edit&order_id=');
        }
        $mlm_order_base_link = admin_url('admin.php?page=ultimate_affiliates_pro&tab=referral_list_details&id=');
        $user_sign_up_link = admin_url('user-edit.php?user_id=');
        $urlAddEdit = admin_url('admin.php?page=ultimate_affiliates_pro&tab=referrals&subtab=add_edit');


        ?>
        <table class="uap-js-list-payments-for-payouts uap-display-none" id="uap-dashboard-table">
            <thead class="" >
              <tr class="" >
                  <th class=""><?php esc_html_e('Referral ID', 'uap');?></th>
                  <th class=""><?php esc_html_e('Affiliate ID', 'uap');?></th>
                  <th class=""><?php esc_html_e('Affiliate', 'uap');?></th>
                  <th class=""><?php esc_html_e('Source', 'uap');?></th>
                  <th class=""><?php esc_html_e('Reference', 'uap');?></th>
                  <th class=""><?php esc_html_e('Description', 'uap');?></th>
                  <th class=""><?php esc_html_e('Amount', 'uap');?></th>
                  <th class=""><?php esc_html_e('Created Time', 'uap');?></th>
                  <th class=""><?php esc_html_e('Status', 'uap');?></th>
              </tr>
            </thead>
            <tbody class="" >
                <?php global $indeed_db;?>
                <?php foreach ( $data['payment_details']['referrals'] as $referralData ):?>
                    <tr class="" onmouseover="uapDhSelector('#referral_<?php echo esc_attr($referralData['id']);?>', 1);" onmouseout="uapDhSelector('#referral_<?php echo esc_attr($referralData['id']);?>', 0);" >
                        <td class=""><?php echo $referralData['id'];
                        ?><div id="referral_<?php echo esc_attr($referralData['id']);?>" class="uap-visibility-hidden">
<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=referrals&subtab=add_edit&id=' . $referralData['id'] ) );?>"><?php echo esc_html__('Edit', 'uap');?></a>
                        </div></td>
                        <td class=""><a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=user_profile&affiliate_id=' . $referralData['affiliate_id'] );?>" target="_blank"><?php echo $referralData['affiliate_id'];?></a></td>
                        <td class=""><a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=user_profile&affiliate_id=' . $referralData['affiliate_id'] );?>" target="_blank"><?php echo $referralData['affiliate_email'];?></a><div><?php
                            echo $indeed_db->get_full_name_of_user( $referralData['affiliate_id'] );
                        ?></div></td>
                        <td class=""><?php echo $data['sources'][$referralData['source']];?></td>
                        <td class=""><?php
                        //////////////////// Reference
                        $reference = '';
                        if (!empty($referralData['reference'])){
                          switch ($referralData['source']){
                            case 'woo':
                              if (!empty($woo_order_base_link)){
                                $link = $woo_order_base_link . $referralData['reference'] . '&action=edit';
                              }
                              $reference = esc_html__('Sale', 'uap').' '.esc_uap_content('<a href="' . $link . '" target="_blank">#' . $referralData['reference'] . '</a>');
                              break;
                            case 'ulp':
                              if (!empty($ulp_order_base_link)){
                                $link = $ulp_order_base_link . $dataObject->reference . '&action=edit';
                              }
                              $reference = esc_html__('Sale', 'uap').' '.esc_uap_content('<a href="' . $link . '" target="_blank">#' . $referralData['reference'] . '</a>');
                              break;
                            case 'edd':
                              if (!empty($edd_order_base_link)){
                                $link = $edd_order_base_link . $referralData['reference'];
                              }
                              $reference = esc_html__('Sale', 'uap').' '.esc_uap_content('<a href="' . $link . '" target="_blank">#' . $referralData['reference'] . '</a>');
                              break;
                            case 'ump':
                              $link = $ump_order_base_link . $referralData['reference'];
                              $reference = esc_html__('Sale', 'uap').' '.esc_uap_content('<a href="' . $link . '" target="_blank">#' . $referralData['reference'] . '</a>');
                              break;
                            case 'mlm':
                              $the_ref = $referralData['reference'];
                              $the_ref = str_replace('mlm_', '', $the_ref);
                              $link = $mlm_order_base_link . $the_ref;
                              $reference = esc_html__('Referral', 'uap').' '.esc_uap_content('<a href="' . $link . '" target="_blank">#' . $the_ref . '</a>');
                              break;
                            case 'User SignUp':
                              if (!empty($referralData['reference']) && strpos($referralData['reference'], 'user_id_')!==FALSE){
                                $uid_sign_up = str_replace('user_id_', '', $referralData['reference'] );
                                $link = $user_sign_up_link . $uid_sign_up;
                                $reference = esc_html__('User ID', 'uap').' '.esc_uap_content('<a href="' . $link . '" target="_blank">#' . $uid_sign_up . '</a>');
                              }
                              break;
                            default:
                              $link = apply_filters( 'uap_admin_dashboard_custom_referrence_link', '', $referralData );
                              if(!empty($link)){
                                $reference = esc_uap_content('<a href="' . $link . '" target="_blank">' . $referralData['reference'] . '</a>');
                              }

                              break;
                          }
                        }
                        echo $reference;
                        //////////////////// End of Reference
                        ?></td>
                        <td class=""><?php echo $referralData['description'];?></td>
                        <td class=""><?php echo uap_format_price_and_currency( uapCurrency( $referralData['currency'] ), $referralData['amount']);?></td>
                        <td class=""><?php echo uap_convert_date_to_us_format($referralData['date']);?></td>
                        <td class=""><?php
                          switch ( $referralData['payment'] ){
                              case 0:
                                ?>
                                    <div class="referral-status-refuse uap-status uap-status-failed"><?php echo $data['status_types'][$referralData['payment']];?></div>
                                <?php
                                break;
                              case 1:
                                ?>
                                    <div class="referral-status-unverified uap-status uap-status-inactive"><?php echo $data['status_types'][$referralData['payment']];?></div>
                                <?php
                                break;
                              case 2:
                                ?>
                                    <div class="referral-status-verified uap-status uap-status-active"><?php echo $data['status_types'][$referralData['payment']];?></div>
                                <?php
                                break;
                          }
                        ?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>

        <div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>


    </div>

</div>
