<?php
if ( !empty( $data['settings']['ihc_show_pause_resume_link'] ) || !empty( $data['settings']['ihc_show_finish_payment'] ) ){
}

wp_enqueue_script( 'ihc-user-membership-management', IHC_URL . 'assets/js/user-membership-management.js', ['jquery'], '12.7', false );

wp_enqueue_style( 'ihc_iziModal' );
wp_enqueue_script( 'ihc_iziModal_js' );
$needReasons = ihc_is_magic_feat_active( 'reason_for_cancel' );
$paymentGateways = ihc_list_all_payments();
$paymentGateways['woocommerce'] = esc_html__( 'WooCommerce', 'ihc' );
$orderMeta = new \Indeed\Ihc\Db\OrderMeta();
$checkoutPage = get_option( 'ihc_checkout_page' );
if ( $checkoutPage > -1 ){
    $checkoutPage = get_permalink( $checkoutPage );
}
$payments_available = ihc_get_active_payments_services();
    if ( $data['subscriptions'] !='' && $data['subscriptions'] ){
      ?>
      <table class="ihc-account-subscr-list">
        <thead>
          <tr>
            <td class="ihc-subscription-table-level"><?php esc_html_e("Membership", 'ihc');?></td>

            <?php if ( !empty( $data['settings']['ihc_show_plan_details_column'] ) ):?>
                <td class="ihc-remove-onmobile"><?php esc_html_e("Plan Details", 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_amount_column'] ) ):?>
                <td class="ihc-remove-onmobile ihc-content-right"><?php esc_html_e("Amount", 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_payment_service_column'] ) ):?>
                <td class="ihc-remove-onmobile"><?php esc_html_e("Payment Service", 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_trial_period_column'] ) ):?>
                <td class="ihc-remove-onmobile"><?php esc_html_e( 'Trial Period', 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_grace_period_column'] ) ):?>
                <td class="ihc-remove-onmobile"><?php esc_html_e( 'Grace Period', 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_starts_on_column'] ) ):?>
                <td class="ihc-remove-onmobile"><?php esc_html_e("Starts On", 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_expires_on_column'] ) ):?>
                <td><?php esc_html_e("Expires On", 'ihc');?></td>
            <?php endif;?>

            <?php if ( !empty( $data['settings']['ihc_show_status_column'] ) ):?>
                <td><?php esc_html_e("Status", 'ihc');?></td>
            <?php endif;?>

            <td class="ihc-subscription-table-actions ihc-content-right"><?php esc_html_e("Actions", 'ihc');?></td>
          </tr>
        </thead>
      <?php
      $i = 0;
      $show_meta_links = ihc_return_meta_arr('level_subscription_plan_settings');

      foreach ( $data['subscriptions'] as $subscriptionData ){

        $paymentType = get_option('ihc_payment_selected');

        $levelData = \Indeed\Ihc\Db\Memberships::getOne( $subscriptionData['level_id'] );//ihc_get_level_by_id($level_id);
        if ( empty( $levelData ) ){
          continue;
        }

        $hidden_div = 'ihc_ap_subscription_l_' . $i;
        $status = \Indeed\Ihc\UserSubscriptions::getStatusAsString( $subscriptionData['user_id'], $subscriptionData['level_id'] );

        // first we search into order meta for payment type
        $orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $subscriptionData['user_id'], $subscriptionData['level_id']  );
        $orderMetaObject = new \Indeed\Ihc\Db\OrderMeta();
        $subscriptionMetas = \Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscriptionData['id'] );
        $membershipData = \Indeed\Ihc\Db\Memberships::getOne( $subscriptionData['level_id'] );
        $subscriptionStatus = \Indeed\Ihc\UserSubscriptions::getStatus( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] );

        ?>
        <tr>
          <!--MEMBERSHIP Column-->
          <td  class="ihc-level-name-wrapp ihc-content-left"><span class="ihc-level-name">
            <span class="ihc-level-status-set-<?php echo esc_attr($subscriptionStatus['status_as_string']);?>"><?php echo esc_attr($membershipData['label']);?></span></span>
          </td>

          <!--PLAN DETAILS Column-->
          <?php if ( !empty( $data['settings']['ihc_show_plan_details_column'] ) ):?>
              <td class="ihc-level-type-wrapp ihc-remove-onmobile"><?php
                      echo \Indeed\Ihc\UserSubscriptions::getAccessTypeAsString( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] );
              ?></td>
          <?php endif;?>

          <!--AMOUNT Column-->
          <?php if ( !empty( $data['settings']['ihc_show_amount_column'] ) ):?>
              <td class="ihc-level-price-wrapp ihc-remove-onmobile ihc-subscription-table-price"><?php
                  echo ihcPaymentPlanDetailsPublic( $subscriptionData['user_id'], $subscriptionData['level_id'] );
              ?></td>
          <?php endif;?>

          <!--Payment Type Column-->
          <?php if ( !empty( $data['settings']['ihc_show_payment_service_column'] ) ):?>
              <td class="ihc-level-payment-wrapp ihc-remove-onmobile">
                  <?php
                      if ( isset( $subscriptionMetas['payment_gateway'] ) && isset( $paymentGateways[$subscriptionMetas['payment_gateway']] ) ){
                          $paymentTypeForThisLevel = $paymentGateways[$subscriptionMetas['payment_gateway']];
                      } else {
                          $paymentService = $orderMeta->get( $orderId, 'ihc_payment_type' );
                          $paymentTypeForThisLevel = isset( $paymentGateways[ $paymentService ] ) ? $paymentGateways[ $paymentService ] : false;
                      }
                  ?>
                  <?php echo ( $paymentTypeForThisLevel === false ) ? '-' : $paymentTypeForThisLevel;?>
              </td>
          <?php endif;?>

          <!-- Trial Period Column -->
          <?php if ( !empty( $data['settings']['ihc_show_trial_period_column'] ) ):?>
              <td class="ihc-level-trial-wrapp ihc-remove-onmobile"><?php
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
              ?></td>
          <?php endif;?>

          <!-- Grace Period Column -->
          <?php if ( !empty( $data['settings']['ihc_show_grace_period_column'] ) ):?>
              <td class="ihc-level-grace-wrapp ihc-remove-onmobile">
                <?php if ( isset( $subscriptionMetas['grace_period'] ) && $subscriptionMetas['grace_period'] != ''): ?>
                    <?php echo esc_html__( 'Yes - ', 'ihc') . $subscriptionMetas['grace_period'] . ihcGetTimeTypeByCode( 'D', $subscriptionMetas['grace_period'] ) . esc_html__(' after expires', 'ihc' );?>
                <?php else:?>
                    <?php $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $subscriptionData['level_id'] );?>
                    <?php if ( $gracePeriod ):?>
                        <?php echo esc_html__( 'Yes - ', 'ihc') . $gracePeriod . ihcGetTimeTypeByCode( 'D', $gracePeriod ) .  esc_html__(' after expires', 'ihc' );?>
                    <?php endif;?>
                <?php endif;?>
              </td>
          <?php endif;?>

          <!--START TIME Column-->
          <?php if ( !empty( $data['settings']['ihc_show_starts_on_column'] ) ):?>
              <td class="ihc-level-start-time-wrapp ihc-remove-onmobile"><?php
                  echo ihc_convert_date_to_us_format( $subscriptionData['start_time'] );
              ?></td>
          <?php endif;?>

          <!--EXPIRE TIME Column-->
          <?php if ( !empty( $data['settings']['ihc_show_expires_on_column'] ) ):?>
              <td class="ihc-level-end-time-wrapp">
              <?php if ( $subscriptionData['expire_time'] === '0000-00-00 00:00:00' ):?>
                  -
              <?php elseif ( $subscriptionData['expire_time'] && $subscriptionData['expire_time']!='--' ):?>
                  <?php echo ihc_convert_date_to_us_format($subscriptionData['expire_time']);?>
              <?php else :?>
                  <?php echo esc_html($subscriptionData['expire_time']);?>
              <?php endif;?>
              </td>
          <?php endif;?>

          <!--STATUS Column-->
          <?php if ( !empty( $data['settings']['ihc_show_status_column'] ) ):?>
              <td class="ihc_account_level_status">
                 <span class="ihc-level-status-set-<?php echo esc_attr($subscriptionStatus['status_as_string']);?>" ><?php echo esc_html($subscriptionStatus['label']);?></span>
              </td>
          <?php endif;?>

       <!--ACTIONS Column-->
       <td>
          <div class="ihc-subscription-table-actions ihc-content-right" id="<?php echo esc_attr($hidden_div);?>">

            <!-- Finish Payment Button -->
                <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showFinishPayment( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ):?>
                    <div class="iump-subscription-table-button">
                        <?php
                            $paymentService = $orderMeta->get( $orderId, 'ihc_payment_type' );
                            if ( $checkoutPage !== '' && $checkoutPage !== false && $checkoutPage !== '-1'
                                  && $paymentService !== false && $paymentService === 'stripe_connect'
                                  && ihc_check_payment_available( 'stripe_connect' ) ){
                                /////// STRIPE CONNECT - Finish Payment
                                $urlParams = [
                                    'lid' => $subscriptionData['level_id'],
                                ];
                                if (count($payments_available) > 1 ){
                                    $urlParams['py'] = $paymentService;
                                }
                                $finishPaymentLink = add_query_arg( $urlParams, $checkoutPage );
                                ?>
                                <span class="iump-renew-subscription-button"><a href="<?php echo esc_url($finishPaymentLink);?>" ><?php
                                    esc_html_e( 'Finish Payment', 'ihc');
                                ?></a></span>
                            <?php
                          } else {
                        ?>
                          <span class="ihc-js-finish-payment-bttn" data-lid="<?php echo esc_attr($subscriptionData['level_id']);?>"
                                data-level_name="<?php echo esc_attr($membershipData['label']);?>"
                                data-level_amount="<?php echo esc_attr($membershipData['price']);?>"
                                data-oid="<?php echo esc_attr($orderId);?>"
                                 ><?php
                              esc_html_e( 'Finish Payment', 'ihc' );
                          ?></span>
                        <?php }?>
                    </div>
                <?php endif;?>

            <!-- Renew Button -->
                <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showRenew( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] )
                            && isset( $checkoutPage ) && $checkoutPage !== ''
                ):?>
                    <div class="iump-subscription-table-button">
                    <?php
                    $urlParams = [
                        'lid' => $subscriptionData['level_id'],
                    ];
                    $paymentService = $orderMeta->get( $orderId, 'ihc_payment_type' );

                    if ( $paymentService !== '' && $paymentService !== false
                        && count($payments_available) > 1 && ihc_check_payment_available( $paymentService ) ){
                        $urlParams['py']   = $paymentService;
                    }
                    $renewLink = add_query_arg( $urlParams, $checkoutPage );?>

                        <span class="iump-renew-subscription-button iump-renew-<?php echo esc_attr($subscriptionData['level_id']);?>-subscription"
                        ><a href="<?php echo esc_url($renewLink);?>" ><?php esc_html_e( 'Renew', 'ihc');?></a></span>
                    </div>
                <?php endif;?>

            <!-- Pause Button -->
                <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showPause( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ):?>

                    <div class="iump-subscription-table-button">
                        <span class="ihc-js-pause-subscription-bttn iump-pause-<?php echo esc_attr($subscriptionData['level_id']);?>-subscription" data-subscription_id="<?php echo esc_attr($subscriptionData['id']);?>" data-lid="<?php echo esc_attr($subscriptionData['level_id']);?>"><?php
                            esc_html_e( 'Pause', 'ihc' );
                        ?></span>
                    </div>

                <?php endif;?>

            <!-- Resume Button -->
            <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showResume( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ):?>

                <div class="iump-subscription-table-button">
                    <span class="ihc-js-resume-subscription-bttn iump-resume-<?php echo esc_attr($subscriptionData['level_id']);?>-subscription" data-subscription_id="<?php echo esc_attr($subscriptionData['id']);?>" data-lid="<?php echo esc_attr($subscriptionData['level_id']);?>"><?php
                        esc_html_e( 'Resume', 'ihc' );
                    ?></span>
                </div>

            <?php endif;?>

              <!-- Cancel Button -->
              <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showCancel( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ):?>
                  <div class="iump-subscription-table-button">
                      <span class="iump-cancel-subscription-button iump-cancel-<?php echo esc_attr($subscriptionData['level_id']);?>-subscription" data-lid="<?php
                          echo esc_attr($subscriptionData['level_id']);?>"><?php esc_html_e( 'Cancel', 'ihc' );
                      ?></span>
                  </div>
              <?php endif;?>

              <!-- Remove Button -->
                  <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showRemove( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ):?>
                      <div class="iump-subscription-table-button">
                          <span class="iump-delete-subscription-button iump-delete-<?php echo esc_attr($subscriptionData['level_id']);?>-subscription" data-lid="<?php echo esc_attr($subscriptionData['level_id']);?>"><?php
                            esc_html_e( 'Remove', 'ihc' );
                          ?></span>
                      </div>
                  <?php endif;?>

              <!-- Update Stripe Card -->
              <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showStripeChangeCard( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ):?>
                  <div class="iump-subscription-table-button">
                      <span class="ihc-js-stripe-connect-change-card iump-change-card-<?php echo esc_attr($subscriptionData['level_id']);?>-subscription" data-subscription_id="<?php echo esc_attr($subscriptionData['id']);?>" data-uid="<?php echo esc_attr($subscriptionData['user_id']);?>"><?php
                        esc_html_e( 'Change Card', 'ihc' );
                      ?></span>
                  </div>
                  <?php $printStripeConnect = true;?>
              <?php endif;?>

              <?php if ( \Indeed\Ihc\SubscriptionActionsButtons::showChangePlanBttn( $subscriptionData['user_id'], $subscriptionData['level_id'], $subscriptionData['id'] ) ) :?>
                <div class="iump-subscription-table-button">
                    <?php
                        $subscriptionPage = get_option( 'ihc_subscription_plan_page', 0 );
                        $subscriptionPage = get_permalink( $subscriptionPage );
                        $subscriptionPage = add_query_arg( 'membership', $subscriptionData['level_id'], $subscriptionPage );
                    ?>
                    <a href="<?php echo esc_url($subscriptionPage);?>" class="" ><?php
                      echo get_option('ihc_prorate_button_label', 'Change Plan');
                    ?></a>
                </div>
              <?php endif;?>

          </div>

        </td>

        </tr><?php
        $i++;
      }
      $defaultPayment = get_option('ihc_payment_selected');
      ?>
    <?php do_action( 'ump_public_account_page_subscription_table-row', $subscriptionData['user_id'] );?>
    </table>
        <form id="ihc_form_ap_subscription_page" name="ihc_ap_subscription_page" method="post" data-modal="<?php echo esc_attr($needReasons);?>" >
          <input type="hidden" name="ihc_delete_level" value="" id="ihc_delete_level" />
          <input type="hidden" name="ihc_cancel_level" value="" id="ihc_cancel_level" />
          <input type="hidden" name="ihc_renew_level" value="" id="ihc_renew_level" />
          <input type="hidden" name="ihc_finish_payment_level" value="" id="ihc_finish_payment_level" />
          <input type="hidden" name="ihcaction" value="renew_cancel_delete_level_ap" />
      <?php
      $the_payment_type = ( ihc_check_payment_available($defaultPayment) ) ? $defaultPayment : '';
      if (!defined('IHC_HIDDEN_PAYMENT_PRINT')){
         define('IHC_HIDDEN_PAYMENT_PRINT', TRUE);
      }
        ?><input type="hidden" value="<?php echo esc_attr($the_payment_type);?>" name="ihc_payment_gateway" />
      </form><?php


    }
?>

<!-- Stripe Connect -->
<?php
global $wp_version, $current_user;
$settings = ihc_return_meta_arr( 'payment_stripe_connect' );
if ( $settings['ihc_stripe_connect_live_mode'] ){
    $accountNumber = $settings['ihc_stripe_connect_account_id'];
    $publicKey = $settings['ihc_stripe_connect_publishable_key'];
} else {
    $accountNumber = $settings['ihc_stripe_connect_test_account_id'];
    $publicKey = $settings['ihc_stripe_connect_test_publishable_key'];
}

$lang = get_option( 'ihc_stripe_connect_locale_code' );
if ( $lang === false || $lang === null || $lang === '' ){
    $lang = 'en';
}

wp_enqueue_script( 'ihc-stripe-v3', 'https://js.stripe.com/v3/', [], false );
wp_register_script( 'ihc-stripe-connect-change-card', IHC_URL . 'assets/js/stripe-connect-change-card.js', [ 'jquery' ], '12.7' );
if ( version_compare ( $wp_version , '5.7', '>=' ) ){
    wp_add_inline_script( 'ihc-stripe-connect-change-card', "window.ihcStripeConnectAcctNumber='" . $accountNumber . "';" );
    wp_add_inline_script( 'ihc-stripe-connect-change-card', "window.ihcStripeConnectPublicKey='" . $publicKey . "';" );
    wp_add_inline_script( 'ihc-stripe-connect-change-card', "window.ihcStripeConnectLang='" . $lang . "';" );
} else {
    wp_localize_script( 'ihc-stripe-connect-change-card', 'window.ihcStripeConnectAcctNumber', $accountNumber );
    wp_localize_script( 'ihc-stripe-connect-change-card', "window.ihcStripeConnectPublicKey", $publicKey );
    wp_localize_script( 'ihc-stripe-connect-change-card', "window.ihcStripeConnectLang", $lang );
}
wp_enqueue_script( 'ihc-stripe-connect-change-card' );
?>
<!-- Stripe Connect Change Card -->


<?php if ( $needReasons && !empty( $data['subscriptions'] ) ): ?>
	<?php $prefedinedReasons = stripslashes(get_option('ihc_reason_for_cancel_resons'));?>
	<div id="ihc_reasons_modal" class="ihc-display-none">
			<label><?php esc_html_e( 'Reason', 'ihc' );?></label>
			<?php if ( $prefedinedReasons ):?>
				<?php $reasons = explode( ',', $prefedinedReasons );?>
				<div>
						<select id="ihc_reason_predefined_type" class="ihc_reason_predefined_type" >
								<option value=0>...</option>
								<?php foreach ( $reasons as $reason ):?>
										<?php $reason = trim($reason);?>
										<option value="<?php echo esc_attr($reason);?>" ><?php echo esc_attr($reason);?></option>
								<?php endforeach;?>
						</select>
				</div>
		  <?php endif;?>
			<textarea class="ihc_the_reason_textarea" id="ihc_the_reason_textarea" placeholder="<?php esc_html_e('Other', 'ihc');?>"></textarea>
			<input type="hidden" value="" id="ihc_reason_type" />
			<div class="ihc-standard-cancel-bttn" id="ihc_close_modal_bttn"><?php esc_html_e('Cancel', 'ihc');?></div>
			<div class="ihc-standard-bttn" id="ihc_submit_subscription_form"><?php esc_html_e('Submit', 'ihc');?></div>
	</div>
<?php endif;?>


<!-- Trigger to open Modal -->
