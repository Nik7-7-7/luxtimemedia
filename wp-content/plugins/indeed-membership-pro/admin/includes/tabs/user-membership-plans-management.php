<?php
$level_select_options[-1] = '...';

if(isset($subscriptions) && is_array($subscriptions) && count($subscriptions) > 0){
  foreach ( $subscriptions as $k=>$v ){
      $level_select_options[$k] = $v['label'];
  }
}

wp_enqueue_script( 'ihc-user-membership-management', IHC_URL . 'admin/assets/js/user-memberships-management.js', ['jquery'], 10.1 );
?>

<a id="ihc_membeship_select_wrapper"></a>
<div class="ihc-admin-select-level-wrapper">
  <div class="ihc-manage-user-expire-wrapper">
    <h2><?php esc_html_e('Membership Plans management', 'ihc');?></h2>
    <p><?php esc_html_e('Assign new membership or manually change when membership starts and expires for current member ', 'ihc');?></p>
    <div>
        <h4 class="ihc-sub-title"><?php esc_html_e('Assign new Membership', 'ihc');?></h4>
        <p><?php esc_html_e('Manually assign a specific Membership which will become automatically Active (including paid memberships)', 'ihc');?></p>
        <select class="ihc-select-level" data-uid="<?php echo esc_attr($uid);?>">
            <?php foreach ( $level_select_options as $key => $value ):?>
                <option value="<?php echo esc_attr($key);?>"><?php echo esc_html($value);?></option>
            <?php endforeach;?>
        </select>
        <div class="ihc-js-add-new-membership-to-user-bttn indeed-add-new-like-wp">
          <i class="fa-ihc fa-add-ihc"></i>
          <?php esc_html_e( 'Assign New', 'ihc' );?>
        </div>
        <div class="clear"></div>
    </div>
</div>
</div>

<?php
  $userSubscriptions = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, false );
  $userSubscriptions = apply_filters( 'ihc_admin_filter_user_memberships', $userSubscriptions, $uid );
?>

  <div class="ihc-manage-user-expire-wrapper ihc-js-user-subscriptions-wrapper">

      <h2><?php esc_html_e( 'Membership Plans', 'ihc');?></h2>
      <p><?php esc_html_e( 'Assigned Memberships', 'ihc');?></p>


      <?php $extraTableClass = empty( $userSubscriptions ) ? 'ihc-display-none' : '';?>
      <table class="wp-list-table widefat fixed tags ihc-js-membership-table <?php echo esc_attr($extraTableClass);?>" >
          <thead>
              <tr>
                  <th class="ihc-js-membership-table-membership">
                      <?php esc_html_e( 'Membership', 'ihc');?>
                  </th>
                  <th class="ihc-js-membership-table-plan"><?php esc_html_e( 'Plan Details', 'ihc');?></th>
                  <th class="ihc-js-membership-table-amount"><?php esc_html_e( 'Amount', 'ihc');?></th>
                  <th class="ihc-js-membership-table-payment"><?php esc_html_e( 'Payment Service', 'ihc');?></th>
                  <th class="ihc-js-membership-table-trial"><?php esc_html_e( 'Trial Period', 'ihc');?></th>
                  <th class="ihc-js-membership-table-grace"><?php esc_html_e( 'Grace Period', 'ihc');?></th>
                  <th class="ihc-js-membership-table-paymentdue"><?php esc_html_e( 'Next Payment Due', 'ihc');?></th>
                  <th class="ihc-js-membership-table-starts"><?php esc_html_e( 'Starts On', 'ihc');?></th>
                  <th class="ihc-js-membership-table-expires"><?php esc_html_e( 'Expires On', 'ihc');?></th>
                  <th class="ihc-js-membership-table-status"><?php esc_html_e( 'Status', 'ihc');?></th>
                  <th class="ihc-js-membership-table-actions"><?php esc_html_e( 'Actions', 'ihc');?></th>
              </tr>
          </thead>
          <tbody>
            <?php if ( $userSubscriptions ):?>
              <?php
              $payment_gateways = ihc_list_all_payments();
              $payment_gateways['woocommerce'] = esc_html__( 'WooCommerce', 'ihc' );
              $i = 1;
              foreach ( $userSubscriptions as $subscription ):?>
                  <?php
                      $subscriptionMetas = \Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscription['id'] );
                      $orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $subscription['user_id'], $subscription['level_id'] );
                      $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                      $membershipData = \Indeed\Ihc\Db\Memberships::getOne( $subscription['level_id'] );
                      $accessType = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'access_type' );
                      $subscriptionStatus = \Indeed\Ihc\UserSubscriptions::getStatus( $subscription['user_id'], $subscription['level_id'], $subscription['id'] );

                  ?>
                  <tr class="<?php echo esc_attr('ihc-js-user-level-row-' . $subscription['level_id']);?> <?php if($i%2==0){
                    echo esc_attr('alternate');
                  } ?>">
                    <td class="ihc-levels-table-name">
                        <?php if ( isset( $subscription['label'] ) && !empty( $subscription['label'] ) ){
                            echo $subscription['label'];
                        } else {
                            echo \Indeed\Ihc\Db\Memberships::getMembershipLabel( $subscription['level_id'] );
                        }?>
                        <?php if (isset($subscription['is_mga']) && $subscription['is_mga'] == TRUE && isset($subscription['parent_name']) && !empty($subscription['parent_name'])){?>
                          <div class="ihc-levels-table-name-mga">
                            <?php echo esc_html__('Hold by ', 'ihc') . esc_html($subscription['parent_name']); ?>
                          </div>
                        <?php }?>
                        <input type="hidden" name="ihc_user_levels[]" class="ihc_user_levels_input" value="<?php echo esc_attr($subscription['level_id']);?>" />
                    </td>
                    <td><?php
                            echo \Indeed\Ihc\UserSubscriptions::getAccessTypeAsString( $subscription['user_id'], $subscription['level_id'], $subscription['id'] );
                    ?></td>
                    <td><?php echo ihcPaymentPlanDetailsAdmin( $uid, $subscription['level_id'], $subscription['id'] );?></td>
                    <td>
                        <?php if ( isset( $subscriptionMetas['payment_gateway'] ) && isset( $payment_gateways[$subscriptionMetas['payment_gateway']] ) ):?>
                            <?php echo  esc_html($payment_gateways[$subscriptionMetas['payment_gateway']]);?>
                        <?php else :
                            $paymentService = $orderMeta->get( $orderId, 'ihc_payment_type' );
                            echo isset( $payment_gateways[ $paymentService ] ) ? esc_html($payment_gateways[ $paymentService ]) : '-';
                        endif;?>
                    </td>
                    <td><?php
                        $isTrial = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'is_trial' );
                        $expireTrialTime = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'expire_trial_time' );
                        if ( $isTrial ){
                            esc_html_e( 'Yes', 'ihc' );
                            if ( isset( $isTrial ) && $isTrial && $expireTrialTime !==false && strtotime( $expireTrialTime )  < indeed_get_unixtimestamp_with_timezone() ){
                                echo esc_html__( ' - until ', 'ihc' ) . ihc_convert_date_time_to_us_format( $expireTrialTime );
                            }
                        } else {
                           esc_html_e( 'No', 'ihc' );
                        }
                    ?>
                    </td>
                    <td><?php if ( isset( $subscriptionMetas['grace_period'] ) && $subscriptionMetas['grace_period'] != ''): ?>
                        <?php echo esc_html__( 'Yes - ', 'ihc') . esc_html($subscriptionMetas['grace_period']) . esc_html(ihcGetTimeTypeByCode( 'D', $subscriptionMetas['grace_period'] )) . esc_html__(' after expires', 'ihc' );?>
                        <?php else:?>
                          <?php
                              $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $subscription['level_id'] );
                              if ( $gracePeriod ):?>
                            <?php echo esc_html__( 'Yes - ', 'ihc') . esc_html($gracePeriod) . esc_html(ihcGetTimeTypeByCode( 'D', $gracePeriod )) .  esc_html__(' after expires', 'ihc' );?>
                          <?php endif;?>
                    <?php endif;?>
                    </td>
                    <td><?php if ( isset( $subscriptionMetas['payment_due_time'] ) && $subscriptionMetas['payment_due_time'] !='' ){
                            echo esc_html(ihc_convert_date_time_to_us_format( $subscriptionMetas['payment_due_time'] ));
                    }?></td>
                    <td>
                      <div class="input-group">
                        <?php $disabled = empty( $subscription['is_mga'] ) ? '' : 'disabled';?>
                        <input type="text" <?php echo $disabled;?> name="<?php echo esc_attr('start_time_levels[' . $subscription['level_id'] . ']');?>" value="<?php echo esc_attr($subscription['start_time']);?>" placeholder="<?php echo esc_attr($subscription['start_time']);?>" class="start_input_text form-control" />
                        <div class="input-group-addon"><i class="fa-ihc ihc-icon-edit"></i></div>
                     </div>
                    </td>
                    <td>
                      <div class="input-group">
                        <input type="text" <?php echo $disabled;?> name="<?php echo esc_attr('expire_levels[' . $subscription['level_id'] . ']');?>" value="<?php echo esc_attr($subscription['expire_time']);?>" placeholder="<?php echo esc_attr($subscription['expire_time']);?>" class="expire_input_text  form-control" />
                        <div class="input-group-addon"><i class="fa-ihc ihc-icon-edit"></i></div>
                     </div>
                    </td>
                    <td class="ihc-levels-table-status ihc-js-subscription-status"><?php
                        echo isset( $subscriptionStatus['label'] ) ? esc_attr($subscriptionStatus['label']) : '';
                    ?></td>
                    <td>
                      <?php if ( $disabled === '' ):?>
                        <?php // Activate button ?>
                        <?php if ( $subscription['expire_time'] == '0000-00-00 00:00:00' ):?>
                            <?php // only for inactive levels ?>
                            <div class="ihc-js-activate-user-level ihc-pointer" data-lid="<?php echo esc_attr($subscription['level_id']);?>" ><?php esc_html_e( 'Activate', 'ihc' );?></div>
                        <?php endif;?>

                        <?php // Renew Button ?>
                        <?php if ( $subscriptionStatus['status'] != 4 && $subscription['expire_time'] != '0000-00-00 00:00:00' && strtotime( $subscription['expire_time'] ) < indeed_get_unixtimestamp_with_timezone() && empty( $remainTime ) ):?>
                            <?php // only for expired subscriptions ?>
                            <div class='ihc-js-renew-user-level ihc-pointer' data-lid='<?php echo esc_attr($subscription['level_id']);?>' ><?php esc_html_e( 'Renew', 'ihc' );?></div>
                        <?php endif;?>

                        <?php if ( $membershipData['access_type'] === 'regular_period' && $subscription['status'] == 1 ):?>
                            <div title="<?php esc_html_e( 'First charging plan should be Canceled from Payment Service Platform', 'ihc' );?>" class="ihc-js-set-at-canceled-user-level ihc-pointer" data-lid="<?php echo esc_attr($subscription['level_id']);?>"
                                data-subscription_id="<?php echo esc_attr($subscription['id']);?>"
                               data-new_label="<?php esc_html_e('Canceled', 'ihc');?>" ><?php esc_html_e( 'Set as Canceled', 'ihc' );?></div>
                        <?php endif;?>


                        <?php if ( $subscription['expire_time'] != '0000-00-00 00:00:00' ):?>
                            <?php $StripeSubscription = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscription[ 'id' ], 'ihc_stripe_subscription_id' ); ?>
                            <?php // Pause Button ?>
                            <?php if ( ( $accessType == 'unlimited' || $accessType == 'limited' || $accessType == 'date_interval' || isset($StripeSubscription) ) && strtotime( $subscription['expire_time'] ) > indeed_get_unixtimestamp_with_timezone() ):?>
                                <div class='ihc-js-pause-user-level ihc-pointer' data-lid='<?php echo esc_attr($subscription['level_id']);?>' data-subscription_id="<?php echo esc_attr($subscription[ 'id' ]);?>" ><?php esc_html_e( 'Pause', 'ihc' );?></div>
                            <?php endif;?>

                            <?php // Resume Button ?>
                            <?php if ( ( $accessType == 'unlimited' || $accessType == 'limited' || $accessType == 'date_interval' || isset($StripeSubscription) ) && $subscriptionStatus['status'] == 4 ):?>
                                  <div class='ihc-js-reactivate-user-level ihc-pointer' data-lid='<?php echo esc_attr($subscription['level_id']);?>' data-subscription_id="<?php echo esc_attr($subscription[ 'id' ]);?>" ><?php esc_html_e( 'Resume', 'ihc' );?></div>
                            <?php endif;?>

                        <?php endif;?>

                            <div class='ihc-js-delete-user-level ihc-pointer' data-lid='<?php echo esc_attr($subscription['level_id']);?>'><?php esc_html_e( 'Remove', 'ihc' );?></div>
                      <?php endif;?>
                    </td>
                  </tr>
              <?php
              $i++;
              endforeach;?>
            <?php endif;?>
            </tbody>
      </table>
  </div>
