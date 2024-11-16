<?php
    $uid = isset( $_GET['uid'] ) ? sanitize_text_field($_GET['uid']) : 0;
    $MemberAddEdit = new \Indeed\Ihc\Admin\MemberAddEdit();
    $data = $MemberAddEdit->setUid( $uid )->getUserData();
    $data['avatar'] = ihc_get_avatar_for_uid( $uid );
    $data['order'] = \Ihc_Db::get_all_order( 100, 0, $uid);
    $data['full_name'] = \Ihc_Db::getUserFulltName( $uid );
    $data['verified_email'] =  get_user_meta( $uid, 'ihc_verification_status', true );
    $data['allow_show_public_profile'] = get_user_meta( $uid, 'ihc_membership_accept', true );
    $data['user_roles'] = \Ihc_Db::getUserRole( $uid );
    $availableRoles = ihc_get_wp_roles_list();
    $registerFields = ihc_get_user_reg_fields();
    $key = ihc_array_value_exists( $registerFields, 'ihc_memberlist_accept', 'name' );
    $showMemberlist = false;
    if ( !empty( $registerFields[$key]['display_public_reg'] ) ){
    	$showMemberlist = true;
    }
    $data['gifts'] = \Ihc_Db::get_gifts_by_uid( $uid );
    $reasonDbObject = new \Indeed\Ihc\Db\ReasonsForCancelDeleteLevels();
    $data['reasons_for_cancel'] = $reasonDbObject->getForUser( $uid );
    $data['notification_logs'] = \Indeed\Ihc\Db\NotificationLogs::getMany( $uid, 5, 0, true  );
    $data['orders'] = \Ihc_Db::get_all_order( 10, 0, $uid);
    $payment_gateways = ihc_list_all_payments();
    $payment_gateways['woocommerce'] = esc_html__( 'WooCommerce', 'ihc' );

    $data['subscriptions'] = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, false );
    $data['subscriptions'] = apply_filters( 'ihc_admin_filter_user_memberships', $data['subscriptions'], $uid );

    $taxesOn = ihc_is_magic_feat_active( 'taxes' );
    $userFields = ihc_get_user_reg_fields();

wp_enqueue_script( 'ihc-print-this' );
?>

<?php if ( isset( $data['ihc_user_custom_banner_src'] ) && $data['ihc_user_custom_banner_src'] != '' ) :
  $custom_css = '';
	$custom_css .= "
  .ihc-admin-user-profile-wrapper .ihc-admin-user-page-top-ap-background {
      background-image: url( '" . esc_url( $data['ihc_user_custom_banner_src'] ) . "' );
  }
	";
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', $custom_css );

  ?>
<?php endif;?>

<div class="ihc-stuffbox">
    <h3><?php esc_html_e( 'Member Details', 'ihc');?></h3>
    <div class="inside">
      <div class="ihc-js-print-page ihc-print-buttton-wrapper"><i class="fa-ihc fa-print-ihc"></i> <?php esc_html_e('Print', 'ihc');?></div>
      <div class="ihc-admin-user-profile-wrapper" id="ihc_js_print_this" >
        <div class="ihc-admin-user-page-top-ap-background "></div>
        <div class="ihc-admin-user-details-wrapper">
          <div class="ihc-admin-left-side">
            <?php
                if ( isset( $data['avatar'] ) ){
                    $avatar = $data['avatar'];
                } else {
                    $avatar = 'https://secure.gravatar.com/avatar/1cc31b08528740e0d8519581e6bf1b04?s=96&amp;d=mm&amp;r=g';
                }
            ?>
            <img src="<?php echo esc_url($avatar);?>" />
            <div class="ihc-admin-user-edit-profile">
              <a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users&ihc-edit-user=' . esc_attr($uid) );?>" target="_blank" class="button button-primary button-large">
                <?php esc_html_e( 'Edit Member Profile', 'ihc' );?>
              </a>
            </div>
            <div class="ihc-admin-user-profile-status">
              <span><?php esc_html_e( 'Member since:', 'ihc');?></span> <?php echo esc_html( ihc_convert_date_to_us_format( $data['user_registered'] ) );?>
            </div>
            <div class="ihc-admin-user-profile-status">
              <!-- Pending WP role with extra style  -->
              <span><?php esc_html_e( 'WordPress Role:', 'ihc');?></span>
              <?php if ( !empty( $data['user_roles'] ) ):?>
                  <?php foreach ( $data['user_roles'] as $roleKey => $roleIndex ):?>
                      <?php $class = $roleKey == 'pending_user' ? 'ihc-userdetails-pending' : '';?>
                      <div class="<?php echo esc_attr($class);?>"><?php echo esc_html($availableRoles[$roleKey]);?></div>
                  <?php endforeach;?>
              <?php endif;?>
            </div>
            <?php if ( get_option( 'ihc_register_double_email_verification' ) && ($data['verified_email'] == 1 || $data['verified_email'] == -1) ):?>
                <div class="ihc-admin-user-profile-status">
                  <!-- Show only if Double Email Verification is different than -  -->
                      <span><?php esc_html_e( 'Email Verification:', 'ihc');?></span>
                      <?php if ( $data['verified_email'] == 1 ):?>
                          <?php esc_html_e( 'Approved', 'ihc' );?>
                      <?php elseif ( $data['verified_email'] == -1 ) :?>
                          <?php esc_html_e( 'Unapproved', 'ihc' );?>
                      <?php endif;?>
                </div>
            <?php endif;?>
            <?php if ( get_option( 'ihc_register_opt-in' ) ):?>
                <div class="ihc-admin-user-profile-status">
                  <!-- Show only if ihc_optin_accept is activated: Accepted/Denied  -->
                      <span><?php esc_html_e( 'Subscribe to Newsletter list:', 'ihc');?></span>
                      <?php if ( empty( $data['ihc_optin_accept'] ) ):?>
                          <?php esc_html_e( 'Denied', 'ihc' );?>
                      <?php else :?>
                          <?php esc_html_e( 'Accepted', 'ihc' );?>
                      <?php endif;?>
                </div>
            <?php endif;?>
              <?php if ( $showMemberlist ):?>
                  <div class="ihc-admin-user-profile-status">
                    <!-- Show only if ihc_membership_accept is activated: Accepted/Denied  -->
                    <span><?php esc_html_e( 'Show Profile on Public:', 'ihc');?></span>
                    <?php if ( empty( $data['allow_show_public_profile'] ) ):?>
                        <?php esc_html_e( 'Denied', 'ihc' );?>
                    <?php else:?>
                        <?php esc_html_e( 'Accepted', 'ihc' );?>
                    <?php endif;?>
                  </div>
              <?php endif;?>
              <?php if ( get_option( 'ihc_individual_page_enabled' ) && !empty( $data['ihc_individual_page'] ) ) :?>
                  <div class="ihc-admin-user-profile-status">
                    <!-- Show only if Individual Page module is enabled and user has this page created  -->
                    <?php $url = admin_url( 'post.php?post=' . $data['ihc_individual_page'] . '&action=edit'  );?>
                    <div class="level-type-list ihc_small_yellow_button level-type-list-st"> <a href="<?php echo esc_url($url);?>" target="_blank"><?php esc_html_e( 'Individual Page', 'ihc');?></a></div>
                  </div>
              <?php endif;?>
          </div>

          <div class="ihc-admin-middle-side">
            <div class="ihc-admin-user-main-name">
                <?php echo esc_html($data['full_name']);?>
            </div>
            <div class="ihc-admin-user-data">
              <table>
                <tbody>
              <tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'Username:', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><?php
                    if ( isset( $data['user_login'] ) ){
                        echo esc_html($data['user_login']);
                    } else {
                        echo esc_html('-');
                    }
                ?></td>
							</tr>
							<tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'Email:', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><a href="mailto:<?php echo esc_url($data['user_email']);?>"><?php
                if ( isset( $data['user_email'] ) ){
                    echo esc_html($data['user_email']);
                } else {
                    echo esc_html('-');
                }
                ?></a></td>
							</tr>
							<tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'First Name:', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><?php
                    if ( isset( $data['first_name'] ) ){
                        echo esc_html($data['first_name']);
                    } else {
                        echo esc_html('-');
                    }
                ;?></td>
							</tr>
							<tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'Last Name:', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><?php
                if ( isset( $data['last_name'] ) ){
                    echo esc_html($data['last_name']);
                } else {
                    echo esc_html('-');
                }?></td>
							</tr>
							<tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'Biography:', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><?php  if ( isset( $data['description'] ) ){
                    echo esc_html($data['description']);
                } else {
                    echo esc_html('-');
                };?></td>
							</tr>
							<tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'Gender:', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><?php echo isset( $data['gender'] ) ? esc_html($data['gender']) : '-';?></td>
							</tr>
							<tr>
								<th class="ihc-admin-user-data-label"><?php esc_html_e( 'Website (URL):', 'ihc' );?></th>
								<td class="ihc-admin-user-data-content"><?php echo isset( $data['user_url'] ) ? esc_html($data['user_url']) : '-';?></td>
							</tr>

						</tbody>
              </table>

              <!-- SHOW MORE USER DETAILS -->
              <?php $showMore = false;?>
                  <table class="ihc-js-user-extra-fields ihc-display-none">
                          <tbody>
                          <?php if ( $userFields ):?>
                              <?php $exclude = [ 'tos',
                                                 'confirm_email',
                                                 'ihc_optin_accept',
                                                 'ihc_memberlist_accept',
                                                 'recaptcha',
                                                 'ihc_invitation_code_field',
                                                 'ihc_dynamic_price',
                                                 'ihc_coupon',
                                                 'payment_select',
                                                 'ihc_avatar',
                              ];?>
                              <?php foreach ( $userFields as $userField ):?>
                                  <?php


                                    $slug = $userField['name'];
                                    if ( $userField['native_wp'] || !isset( $data[ $slug ] ) || $data[ $slug ] == '' || in_array( $slug, $exclude) ){
                                       continue;
                                    }
                                    $showMore = true;
                                  ?>
                                  <tr>
                                      <th class="ihc-admin-user-data-label"><?php echo esc_html($userField['label']) . ':';?></th>
                                      <td class="ihc-admin-user-data-content">
                                      <?php
                                        switch ( $userField['type'] ){
                                            case 'ihc_country':
                                              $countries = ihc_get_countries();
                                              echo isset( $countries[ $data[ $slug ] ] ) ? $countries[ $data[ $slug ] ] : $data[ $slug ];
                                              break;
                                            case 'ihc_state':
                                              echo esc_html($data[ $slug ]);
                                              break;
                                            case 'select':
                                              if (in_array($data[ $slug ], $userField['values']) ){
                                                  echo esc_html($data[ $slug ]);
                                              }
                                              break;
                                            default:
                                              if ( is_array( $data[ $slug ] ) ){
                                                  echo esc_html(implode( ',', $data[$slug] ));
                                              } else {
                                                  echo esc_html($data[ $slug ]);
                                              }
                                              break;
                                        }
                                      ?>
                                      </td>
                                  </tr>
                              <?php endforeach;?>
                          <?php endif;?>
                        </tbody>
                      </table>
                <?php if ( $showMore ):?>
                    <div class="ihc-pointer ihc-js-show-more ihc-user-details-showmore-fields iump-second-button"><?php esc_html_e( 'Show More', 'ihc' );?></div>
                <?php endif;?>
              <!-- END OF SHOW MORE USER DETAILS -->

            </div>
          </div>
          <div class="ihc-clear"></div>

          <?php if ( !empty( $data['subscriptions'] ) ):?>
              <div class="ihc-admin-user-data-list ihc-admin-user-data-list-st">
                <!-- Show only if User has any Membership assigned  -->
                <h2><?php esc_html_e( 'Membership History', 'ihc');?></h2>
                <p><?php esc_html_e( 'Member-assigned membership plans', 'ihc');?></p>
                <div class="ihc-manage-user-expire-wrapper-edit">
                <a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users&ihc-edit-user=' . esc_attr($uid) .'#ihc_membeship_select_wrapper' );?>" target="_blank">
                  <i class="fa-ihc ihc-icon-edit-e"></i>
                </a>
              </div>
                <table class="wp-list-table widefat fixed tags" >
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Membership', 'ihc');?></th>
                            <th><?php esc_html_e( 'Plan Details', 'ihc');?></th>
                            <th><?php esc_html_e( 'Amount', 'ihc');?></th>
                            <th><?php esc_html_e( 'Payment Service', 'ihc');?></th>
                            <th><?php esc_html_e( 'Trial Period', 'ihc');?></th>
                            <th><?php esc_html_e( 'Grace Period', 'ihc');?></th>
                            <th><?php esc_html_e( 'Next Payment Due', 'ihc');?></th>
                            <th><?php esc_html_e( 'Starts On', 'ihc');?></th>
                            <th><?php esc_html_e( 'Expires On', 'ihc');?></th>
                            <th class="ihc-small-status-col"><?php esc_html_e( 'Status', 'ihc');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $data['subscriptions'] as $subscription ):?>
                            <?php
                                $subscriptionMetas = \Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscription['id'] );
                                $membershipData = \Indeed\Ihc\Db\Memberships::getOne( $subscription['level_id'] );
                                $accessType = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'access_type' );

                                $orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $subscription['user_id'], $subscription['level_id'] );
                                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                            ?>
                            <tr>
                              <td class="ihc-levels-table-name ihc-levels-table-name-st">
                                <?php
                                  if ( isset( $subscription['label'] ) && !empty( $subscription['label'] ) ){
                                      echo esc_ump_content( $subscription['label'] );
                                  } else {
                                      echo esc_ump_content(\Indeed\Ihc\Db\Memberships::getMembershipLabel( $subscription['level_id'] ));
                                  }?>
                                  <?php if (isset($subscription['is_mga']) && $subscription['is_mga'] == TRUE && isset($subscription['parent_name']) && !empty($subscription['parent_name'])){?>
                                    <div class="ihc-levels-table-name-mga">
                                      <?php echo esc_html__('Hold by ', 'ihc') . esc_html($subscription['parent_name']); ?>
                                    </div>
                                  <?php }?>
                                </td>
                              <td><?php
                                  switch ( $accessType ){
                                      case 'regular_period':
                                        $accessRegularTimeType = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'access_regular_time_type' );
                                        esc_html_e( 'Subscription - ', 'ihc' );
                                        switch ( $accessRegularTimeType ){
                                            case 'D':
                                              esc_html_e( 'Daily', 'ihc' );
                                              break;
                                            case 'W':
                                              esc_html_e( 'Weekly', 'ihc' );
                                              break;
                                            case 'M':
                                              esc_html_e( 'Monthly', 'ihc' );
                                              break;
                                            case 'Y':
                                              esc_html_e( 'Yearly', 'ihc' );
                                              break;
                                        }
                                        break;
                                      case 'unlimited':
                                        esc_html_e( 'LifeTime', 'ihc' );
                                        break;
                                      case 'limited':
                                        esc_html_e( 'Limited Time', 'ihc' );
                                        break;
                                      case 'date_interval':
                                        esc_html_e( 'Date Range', 'ihc' );
                                        break;
                                  }
                              ?></td>
                              <td><?php echo esc_ump_content(ihcPaymentPlanDetailsAdmin( $uid, $subscription['level_id'], $subscription['id'] ));?></td>
                              <td>
                                  <?php if ( isset( $subscriptionMetas['payment_gateway'] ) && isset( $payment_gateways[$subscriptionMetas['payment_gateway']] ) ):?>
                                      <?php echo  esc_ump_content($payment_gateways[$subscriptionMetas['payment_gateway']]);?>
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
                                  <?php echo esc_html__( 'Yes - ', 'ihc') . esc_html($subscriptionMetas['grace_period']) . esc_html(ihcGetTimeTypeByCode( 'D', $subscriptionMetas['grace_period'] ) ). esc_html__(' after expires', 'ihc' );?>
                                  <?php else:?>
                                    <?php
                                        $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $subscription['level_id'] );
                                        if ( $gracePeriod ):?>
                                      <?php echo esc_html__( 'Yes - ', 'ihc') . $gracePeriod . ihcGetTimeTypeByCode( 'D', $gracePeriod ) .  esc_html__(' after expires', 'ihc' );?>
                                    <?php endif;?>
                              <?php endif;?>
                              </td>
                              <td><?php if ( isset( $subscriptionMetas['payment_due_time'] ) && $subscriptionMetas['payment_due_time'] !='' ){
                                      echo ihc_convert_date_time_to_us_format( $subscriptionMetas['payment_due_time'] );
                              }?></td>
                              <td><?php echo ihc_convert_date_time_to_us_format( $subscription['start_time'] );?></td>
                              <td><?php
                              if($subscription['expire_time'] != '0000-00-00 00:00:00'){
                                echo ihc_convert_date_time_to_us_format( $subscription['expire_time'] );
                              }
                              ?></td>
                              <td><?php
                                  $subscriptionStatus = \Indeed\Ihc\UserSubscriptions::getStatus( $subscription['user_id'], $subscription['level_id'], $subscription['id'] );
                                  echo isset( $subscriptionStatus['label'] ) ? $subscriptionStatus['label'] : '';
                              ?></td>
                            </tr>
                        <?php endforeach;?>
                      </tbody>
                </table>
              </div>
          <?php endif;?>

          <?php if ( !empty( $data['orders'] ) ):?>
            <?php
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                require_once IHC_PATH . 'classes/Orders.class.php';
                $Orders = new Ump\Orders();
            ?>
              <div class="ihc-admin-user-data-list">
                <!-- Show only if User has any Order created. Taxes and Net Amount shows only when Taxes module is enabled  -->
                <h2><?php esc_html_e( 'Payment History', 'ihc');?></h2>
                <p><?php esc_html_e( 'All payments registered for current Member until now', 'ihc');?></p>
                <table class="wp-list-table widefat fixed tags ">
                  <thead>
                      <tr>
                          <th><?php esc_html_e( 'Membership', 'ihc');?></th>
                          <th><?php esc_html_e( 'Code', 'ihc');?></th>
                          <?php if ( $taxesOn ):?>
                              <th><?php esc_html_e( 'Net Amount', 'ihc');?></th>
                              <th><?php esc_html_e( 'Taxes', 'ihc');?></th>
                          <?php endif;?>
                          <th><?php esc_html_e( 'Total Amount', 'ihc');?></th>
                          <th><?php esc_html_e( 'Payment Method', 'ihc');?></th>
                          <th><?php esc_html_e( 'Charging Type', 'ihc');?></th>
                          <th><?php esc_html_e( 'Coupon', 'ihc');?></th>
                          <th><?php esc_html_e( 'Transaction', 'ihc');?></th>
                          <th><?php esc_html_e( 'Date', 'ihc');?></th>
                          <th class="ihc-small-status-col"><?php esc_html_e( 'Status', 'ihc');?></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ( $data['orders'] as $orderData ):?>
                          <?php
                          $taxes = $orderMeta->get( $orderData['id'], 'taxes_amount' );
                          ?>
                          <tr>
                            <td><?php echo esc_html($orderData['level']);?></td>
                            <td><?php echo isset( $orderData['metas']['code'] ) ? $orderData['metas']['code'] : '-';?></td>
                            <?php if ( $taxesOn ):?>
                                <td>
                                  <?php $value = $orderMeta->get( $orderData['id'], 'base_price' );?>
                          				<?php if ( $value !== null ):?>
                          						<?php echo esc_html($value . ' ' . $orderData['amount_type']);?>
                          				<?php elseif ( $taxes != false ):?>
                          						<?php $netAmount = $orderData['amount_value'] - $taxes;?>
                          						<?php echo esc_html($netAmount . ' ' . $orderData['amount_type']);?>
                          				<?php else :?>
                          						<?php echo esc_html($orderData['amount_value'] . ' ' . $orderData['amount_type']);?>
                          				<?php endif;?>
                                </td>
                                <td>
                            				<?php
                                        $value = $orderMeta->get( $orderData['id'], 'base_price' );
                                    ?>
                            				<?php if ( $value !== null ):?>
                            						<?php echo esc_html($value . ' ' . $orderData['amount_type']);?>
                            				<?php elseif ( $taxes != false ):?>
                            						<?php $netAmount = $orderData['amount_value'] - $taxes;?>
                            						<?php echo esc_html($netAmount . ' ' . $orderData['amount_type']);?>
                            				<?php else :?>
                            						<?php echo esc_html($orderData['amount_value'] . ' ' . $orderData['amount_type']);?>
                            				<?php endif;?>
                                </td>
                            <?php endif;?>
                            <td><?php echo esc_html($orderData['amount_value'] . ' ' . $orderData['amount_type']);?></td>
                            <td><?php
                                if (empty($orderData['metas']['ihc_payment_type'])){
                        					echo esc_html('-');
                                } else {
                        					if (!empty($orderData['metas']['ihc_payment_type'])){
                        						$gateway_key = $orderData['metas']['ihc_payment_type'];
                        						echo isset( $payment_gateways[$gateway_key] ) ? $payment_gateways[$gateway_key] : '-';
                        						 $payment_gateway = $payment_gateways[$gateway_key];
                        					}
                                }
                            ?></td>
                            <td><?php
                              $isRecurring = $orderMeta->get( $orderData['id'], 'is_recurring' );;
                              if ( $isRecurring ):?>
                                  <?php esc_html_e( 'Recurrent', 'ihc' );?>
                              <?php else :?>
                                  <?php esc_html_e( 'Single payment', 'ihc' );?>
                              <?php endif;?>
                            </td>
                            <td><?php
                      					$coupon = $Orders->get_meta_by_order_and_name( $orderData['id'], 'coupon_used' );
                      					if ($coupon){
                                   echo esc_html($coupon);
                                }else{
                                   echo esc_html('-');
                                }
                      			?></td>
                            <td><?php
                              $transactionId = $orderMeta->get( $orderData['id'], 'transaction_id' );
                              echo !empty( $transactionId ) ? $transactionId : '-';
                            ?></td>
                            <td><?php echo ihc_convert_date_time_to_us_format($orderData['create_date']);?></td>
                            <td><?php
                                switch ($orderData['status']){
                      						case 'Completed':
                      							esc_html_e('Completed', 'ihc');
                      							break;
                      						case 'pending':
                      							echo esc_ump_content('<div>' . esc_html__('Pending', 'ihc') . '</div>');

                      							break;
                      						case 'fail':
                      						case 'failed':
                      							esc_html_e('Fail', 'ihc');
                      							break;
                      						case 'error':
                      							esc_html_e('Error', 'ihc');
                      							break;
                      						default:
                      							echo esc_html($orderData['status']);
                      							break;
                      					}
                            ?></td>
                          </tr>
                      <?php endforeach;?>
                  </tbody>
                </table>
                <a class="ihc-user-details-showmore-link" href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=orders&uid=' . $uid );?>" target="_blank"><?php esc_html_e( 'Show more', 'ihc');?></a>
              </div>
          <?php endif;?>

          <?php if ( get_option( 'ihc_gifts_enabled' ) && !empty( $data['gifts'] ) && is_array( $data['gifts'] ) ):?>
              <div class="ihc-admin-user-data-list">
                <!-- Show only if Membership Gifts module is enabled and user received any Gift  -->
                <h2><?php esc_html_e( 'Membership Gifts', 'ihc');?></h2>
                <p><?php esc_html_e( 'Gift codes received by current Member', 'ihc');?></p>
                <table class="wp-list-table widefat fixed tags ">
                  <thead>
                      <tr>
                          <th><?php esc_html_e( 'Gift Code', 'ihc');?></th>
                          <th><?php esc_html_e( 'Discount Value', 'ihc');?></th>
                          <th><?php esc_html_e( 'Discount for Membership', 'ihc');?></th>
                          <th class="ihc-small-status-col"><?php esc_html_e( 'Status', 'ihc');?></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ( $data['gifts'] as $gift ):
                        if(!isset($gift['code'])) continue;
                        ?>
                          <tr>
                            <td><?php echo esc_html($gift['code']);?></td>
                            <td><?php
                    				if ($gift['discount_type']=='price'){
                    					echo ihc_format_price_and_currency( get_option( 'ihc_currency' ) , $gift['discount_value']);
                    				} else {
                    					echo esc_html($gift['discount_value']) . '%';
                    				}
                    			?></td>
                            <td><?php echo \Indeed\Ihc\Db\Memberships::getMembershipLabel( $gift['target_level'] );?></td>
                            <td><?php
                                if ($gift['is_active']):
					                          esc_html_e('Unused', 'ihc');
                        				else :
                        					  esc_html_e('Used', 'ihc');
                        				endif;
                            ?></td>
                          </tr>
                      <?php endforeach;?>
                  </tbody>
                </table>
              </div>
          <?php endif;?>

          <?php if ( !empty( $data['notification_logs'] ) ):?>
              <?php
              $notifications = new \Indeed\Ihc\Notifications();
              $notification_arr = $notifications->getAllNotificationNames();
              $adminNotifications = $notifications->getAdminCases();
              ?>
              <div class="ihc-admin-user-data-list">
                <!-- Show only if User has any Notification stored in Logs  -->
                <h2><?php esc_html_e( 'Notifications History', 'ihc');?></h2>
                <p><?php esc_html_e( 'All Notifications sent to current Member by now', 'ihc');?></p>
                <table class="wp-list-table widefat fixed tags">
                  <thead>
                      <tr>
                          <th class="ihc-userdetails-not-mess"><?php esc_html_e( 'Notification Type', 'ihc');?></th>
                          <th><?php esc_html_e( 'Email', 'ihc');?></th>
                          <th class="ihc-userdetails-not-send"><?php esc_html_e( 'Sent On', 'ihc');?></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ( $data['notification_logs'] as $notification ):?>
                        <tr>
                          <td><?php echo isset( $notification_arr[$notification->notification_type] ) ? $notification_arr[$notification->notification_type] : $notification->notification_type;?></td>
                          <td>
                              <div class="ihc-userdetails-not-sub"><?php echo esc_ump_content($notification->subject);?></div>
                            <div class="ihc-notification-logs-message"><p></p>
                            <?php echo esc_ump_content($notification->message);?>
                          </div>
                          </td>
                          <td><?php echo ihc_convert_date_time_to_us_format( esc_ump_content($notification->create_date) );?></td>
                        </tr>
                      <?php endforeach;?>
                  </tbody>
                </table>
                <a class="ihc-user-details-showmore-link" href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=notification-logs&uid=' . $uid );?>" target="_blank"><?php esc_html_e( 'Show more', 'ihc' );?></a>
              </div>
          <?php endif;?>

          <?php if ( get_option( 'ihc_reason_for_cancel_enabled' ) && !empty( $data['reasons_for_cancel'] ) ):?>
              <div class="ihc-admin-user-data-list">
                <!-- Show only if Reason for Cancelling module is enabled and user has a reason submitted  -->
                <h2><?php esc_html_e( 'Reason for Cancelling', 'ihc');?></h2>
                <p><?php esc_html_e( 'Reasons submitted by Member during cancelling process', 'ihc');?></p>
                <table class="wp-list-table widefat fixed tags ">
                  <thead>
                      <tr>
                        <th class="ihc-userdetails-mem-mess"><?php esc_html_e( 'Membership', 'ihc');?></th>
                        <th><?php esc_html_e( 'Reason', 'ihc');?></th>
                        <th class="ihc-userdetails-mem-date"><?php esc_html_e( 'Date', 'ihc');?></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ( $data['reasons_for_cancel'] as $reason ):?>
                          <tr>
                            <td><?php echo \Ihc_Db::get_level_name_by_lid( $reason->lid );?></td>
                            <td><?php echo stripslashes($reason->reason);?></td>
                            <td><?php echo date( 'Y-m-d h:i:s', $reason->action_date );?></td>
                          </tr>
                      <?php endforeach;?>
                  </tbody>
                </table>
              </div>
          <?php endif;?>

        </div>
      </div>

    </div>
</div>
