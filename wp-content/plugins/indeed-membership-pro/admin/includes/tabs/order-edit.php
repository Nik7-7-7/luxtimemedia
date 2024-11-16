<?php
$orderId = isset( $_GET['order_id'] ) ? sanitize_text_field($_GET['order_id']) : 0;
$orderObject = new \Indeed\Ihc\Db\Orders();
$orderData = $orderObject->setId( $orderId )
                         ->fetch()
                         ->get();
$orderMetaObject = new \Indeed\Ihc\Db\OrderMeta();
$orderMeta = $orderMetaObject->getAllByOrderId( $orderId );
$uid = isset( $orderData->uid ) ? $orderData->uid : 0;
$lid = isset( $orderData->lid ) ? $orderData->lid : 0;

if ( !$orderId || !$orderData ):?>
    <h5><?php esc_html_e( "No order details available!", 'ihc' );?></h5>
<?php else :?>

<form action="<?php echo admin_url('admin.php?page=ihc_manage&tab=orders');?>" method="post">

	<input type="hidden" name="ihc_admin_edit_order_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_edit_order_nonce' );?>" />

  <input type="hidden" name="id" value="<?php echo esc_attr($orderId);?>" />

	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Edit Order', 'ihc');?></h3>
		<div class="inside">
      <div class="ihc-order-edit-leftside">
      <div class="iump-form-line">
      <div class="row">
      		<div class="col-xs-5">
      		    <div class="input-group">
          				<span class="input-group-addon ihc-order-edit-label" id="basic-addon1" ><?php esc_html_e('Username:', 'ihc');?></span>
                  <input type="text" name="username" value="<?php echo esc_attr(\Ihc_Db::get_username_by_wpuid( $orderData->uid ));?>"  />
      				</div>
      		</div>
      </div>
      </div>
      <div class="iump-form-line">

            <div class="row">
            		<div class="col-xs-5">
            		    <div class="input-group">
                				<span class="input-group-addon ihc-order-edit-label" id="basic-addon1" ><?php esc_html_e('Amount:', 'ihc');?></span>
                        <input type="number" min=0 name="amount_value" step="0.01" value="<?php echo esc_attr($orderData->amount_value);?>" />
            				</div>
            		</div>
            </div>
      </div>
      <div class="iump-form-line">
            <div class="row">
      				<div class="col-xs-5">
      					<div class="input-group">
          					<span class="input-group-addon ihc-order-edit-label" id="basic-addon1" ><?php esc_html_e('Currency:', 'ihc');?></span>
                    <select name="amount_type">
                      <?php
        								$currency_arr = ihc_get_currencies_list('all');
        								$custom_currencies = ihc_get_currencies_list('custom');
                        $ihc_currency = get_option('ihc_currency');
        								foreach ($currency_arr as $k=>$v){
        									?>;?>
        									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$orderData->amount_type){
                             echo esc_attr('selected');
                          }
                          ?>
                           >
        										<?php echo esc_html($v);?>
        										<?php if (is_array($custom_currencies) && in_array($v, $custom_currencies)){
                                esc_html_e(" (Custom Currency)");
                            }
                            ?>
        									</option>
        									<?php
        								}
        							?>
                    </select>
      					</div>
      				</div>
      			</div>
        </div>
        <div class="iump-form-line">
            <div class="row">
      				<div class="col-xs-5">
      					<div class="input-group">
          					<span class="input-group-addon ihc-order-edit-label" id="basic-addon1" ><?php esc_html_e('Created Date:', 'ihc');?></span>
                    <input type="text" id="created_date_ihc" name="create_date" value="<?php echo esc_attr($orderData->create_date);?>" />
      					</div>
      				</div>
      			</div>
       </div>
       <div class="iump-form-line">
      <div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon ihc-order-edit-label" id="basic-addon1" ><?php esc_html_e('Payment Status:', 'ihc');?></span>
          <select name="status">
            <?php
                $status = [
                            'pending'   => esc_html__( 'Pending', 'ihc' ),
                            'Completed' => esc_html__( 'Completed', 'ihc' ),
                            'error'     => esc_html__( 'Error', 'ihc' ),
                            'refund'    => esc_html__( 'Refund', 'ihc' ),
                            'fail'      => esc_html__( 'Failed', 'ihc' ),
                ];
  							foreach ($status as $k=>$v):
                  $selected = ( $k == $orderData->status ) ? 'selected' : '';
  								?>
  								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
  								<?php
  							endforeach;
  					?>
          </select>
					</div>
				</div>
			</div>
    </div>

			<div class="ihc-wrapp-submit-bttn">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="save_edit_order" class="button button-primary button-large" />
			</div>
    </div>
    <div class="ihc-order-edit-rightside">
      <div class="ihc-order-edit-box">
      <h4><?php esc_html_e('Customer', 'ihc');?></h4>
        <div class="ihc-order-edit-box-title"><?php
          $fullName = \Ihc_Db::getUserFulltName( $uid );
          if ( $fullName != '' ){
              echo esc_html($fullName);
          } else {
              echo esc_html('-');
          }
          ?></div>
        <div><?php echo esc_html( \Ihc_Db::user_get_email( $uid ) );?></div>
      </div>
      <div class="ihc-order-edit-box">
      <h4><?php esc_html_e('Membership Plan', 'ihc');?></h4>
        <div class="ihc-order-edit-box-title"><?php echo \Indeed\Ihc\Db\Memberships::getMembershipLabel( $lid );?>
          <?php if ( empty( $orderMeta['is_recurring'] ) ) :?>
              <?php esc_html_e( '(One Time)', 'ihc' );?>
          <?php else :?>
              <?php esc_html_e( '(Recurring)', 'ihc' );?>
          <?php endif;?>
        </div>
        <div><?php echo \Indeed\Ihc\Db\Memberships::getMembershipShortDescription( $lid );?></div>
      </div>
      <div class="ihc-order-edit-box">
      <h4><?php esc_html_e('Charging details', 'ihc');?></h4>
        <div>Payment via <span><strong>
          <?php
          $payment_gateway = "";
          $payment_gateways = ihc_list_all_payments();
          $payment_gateways['woocommerce'] = esc_html__( 'WooCommerce', 'ihc' );
          if ( empty( $orderMeta['ihc_payment_type'] ) ):
  					  echo esc_html('-');
  				else:
  						echo isset( $payment_gateways[$orderMeta['ihc_payment_type']] ) ? esc_html($payment_gateways[$orderMeta['ihc_payment_type']]) : '-';
              $payment_gateway =  $payment_gateways[$orderMeta['ihc_payment_type']];
  				endif;
          ?>
          <?php
              if ( isset( $orderMeta['transaction_id'] ) && $orderMeta['transaction_id'] != '' ){
                  $transactionId = $orderMeta['transaction_id'];
                  switch ( $orderMeta['ihc_payment_type'] ){
                          case 'paypal':
                            if ( get_option( 'ihc_paypal_sandbox' ) ){
                              $transactionLink = 'https://www.sandbox.paypal.com/activity/payment/' . esc_attr($transactionId);
                            } else {
                              $transactionLink = 'https://www.paypal.com/activity/payment/' . esc_attr($transactionId);
                            }
                            break;
                          case 'paypal_express_checkout':
                              if ( get_option( 'ihc_paypal_express_checkout_sandbox' ) ){
                                $transactionLink = 'https://www.sandbox.paypal.com/activity/payment/' . esc_attr($transactionId);
                              } else {
                                $transactionLink = 'https://www.paypal.com/activity/payment/' . esc_attr($transactionId);
                              }
                              break;
                          case 'stripe':

                            break;
                          case 'stripe_checkout_v2':
                            $key = get_option( 'ihc_stripe_checkout_v2_publishable_key' );
                            if ( strpos( $key, 'pk_test' ) !== false ){
                              $transactionLink = 'https://dashboard.stripe.com/test/payments/' . esc_attr($transactionId);
                            } else {
                              $transactionLink = 'https://dashboard.stripe.com/payments/' . esc_attr($transactionId);
                            }
                            break;
                          case 'stripe_connect':
                          $key = get_option( 'ihc_stripe_connect_live_mode' );
                          if ( $key == false ){
                              $transactionLink = 'https://dashboard.stripe.com/test/payments/' . esc_attr($transactionId);
                            } else {
                              $transactionLink = 'https://dashboard.stripe.com/payments/' . esc_attr($transactionId);
                            }
                            break;
                          case 'mollie':
                            $transactionLink = 'https://www.mollie.com/dashboard/payments/' . esc_attr($transactionId);
                            break;
                          case 'twocheckout':
                            if ( strpos( $orderMeta['transaction_id'], '_' ) !== false ){
                                $temporaryTransactionId = explode( '_', $transactionId );
                                $transactionId = isset( $temporaryTransactionId[1] ) ? $temporaryTransactionId[1] : $transactionId;
                            }
                            $transactionLink = 'https://secure.2checkout.com/cpanel/order_info.php?refno=' . esc_attr($transactionId);
                            break;
                          case 'braintree':
      											if(get_option( 'ihc_braintree_merchant_id' )){
      												$merchantID = get_option( 'ihc_braintree_merchant_id' );
      												if ( get_option( 'ihc_braintree_sandbox' )){
      													$transactionLink = 'https://sandbox.braintreegateway.com/merchants/' . esc_attr( $merchantID ) . '/transactions/' . esc_attr($transactionId);
      												}else{
      													$transactionLink = 'https://braintreegateway.com/merchants/' . esc_attr($merchantID) . '/transactions/' . esc_attr($transactionId);
      												}
      											}

      											break;
                        default:
                            	$transactionLink = '#';
                              break;
                    }
                    ?>
                    (<a target="_blank" title="<?php esc_attr_e( 'Check Transaction on ' . $payment_gateway, 'ihc'); ?>" href="<?php echo esc_url($transactionLink);?>"><?php echo esc_html($transactionId);?></a>)
                    <?php
                }
          ?></strong></span></div>
        <div>
          <?php if ( empty( $orderMeta['is_recurring'] ) ) :?>
              <?php esc_html_e( 'Single Charge', 'ihc' );?>
          <?php else :?>
              <?php if ( empty( $orderMeta['is_trial'] ) ):?>
                  <?php esc_html_e( 'Recurrent', 'ihc' );?>
              <?php else :?>
                  <?php esc_html_e( 'Trial/Intial Payment', 'ihc' );?>
              <?php endif;?>
          <?php endif;?>
        </div>
        <?php if ( isset( $orderMeta['taxes_amount'] ) && $orderMeta['taxes_amount'] != '' && isset( $orderMeta['currency'] ) ):?>
            <div><?php esc_html_e( 'Taxes Included:', 'ihc' );?> <?php echo esc_html( $orderMeta['taxes_amount'] ) . esc_html( $orderMeta['currency'] );?></div>
        <?php endif;?>
            <?php if ( isset( $orderMeta['coupon_used'] ) && $orderMeta['coupon_used'] != '' ) :?>
                <div>
                    <?php esc_html_e( 'Coupon applied:', 'ihc' );?>
                    <?php echo esc_html($orderMeta['coupon_used']);?>
                </div>
            <?php endif;?>
        <div class="ihc-order-edit-box-links">
          <?php if ( isset( $orderMeta['ihc_payment_type'] )
                && in_array( $orderMeta['ihc_payment_type'], [ 'stripe', 'paypal', 'paypal_express_checkout', 'stripe_checkout_v2', 'stripe_connect', 'mollie', 'twocheckout' ] ) ) :?>
            <?php
            $chargingPlan = '';
            $refundLink = '';
            $transactionId = isset( $orderMeta['transaction_id'] ) ? $orderMeta['transaction_id'] : '';
            switch ( $orderMeta['ihc_payment_type'] ){
                case 'paypal':
                  if ( get_option( 'ihc_paypal_sandbox' ) ){
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://www.sandbox.paypal.com/billing/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://www.sandbox.paypal.com/activity/actions/refund/edit/' . esc_attr($transactionId);
                  } else {
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://www.paypal.com/billing/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://www.paypal.com/activity/actions/refund/edit/' . esc_attr($transactionId);
                  }
                  break;
                case 'paypal_express_checkout':
                  if ( get_option( 'ihc_paypal_express_checkout_sandbox' ) ){
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://www.sandbox.paypal.com/billing/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://www.sandbox.paypal.com/activity/actions/refund/edit/' . esc_attr($transactionId);
                  } else {
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://www.paypal.com/billing/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://www.paypal.com/activity/actions/refund/edit/' . esc_attr($transactionId);
                  }
                  break;
                case 'stripe':

                  break;
                case 'stripe_checkout_v2':
                  $key = get_option( 'ihc_stripe_checkout_v2_publishable_key' );
                  if ( strpos( $key, 'pk_test' ) !== false ){
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://dashboard.stripe.com/test/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://dashboard.stripe.com/test/payments/' . esc_attr($transactionId);
                  } else {
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://dashboard.stripe.com/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://dashboard.stripe.com/payments/' . esc_attr($transactionId);
                  }
                  break;
                case 'stripe_connect':
                $key = get_option( 'ihc_stripe_connect_live_mode' );
                if ( $key == false ){
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://dashboard.stripe.com/test/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://dashboard.stripe.com/test/payments/' . esc_attr($transactionId);
                  } else {
                    if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                        $chargingPlan = 'https://dashboard.stripe.com/subscriptions/' . esc_attr($orderMeta['subscription_id']);
                    }
                    $refundLink = 'https://dashboard.stripe.com/payments/' . esc_attr($transactionId);
                  }
                  break;
                case 'mollie':
                  if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['customer_id'] ) && $orderMeta['customer_id'] != '' ){
                      $chargingPlan = 'https://www.mollie.com/dashboard/customers/' . $orderMeta['customer_id'];
                  }
                  $refundLink = 'https://www.mollie.com/dashboard/payments/' . esc_attr($transactionId);
                  break;
                case 'twocheckout':
                  if ( !empty( $orderMeta['is_recurring'] ) && isset( $orderMeta['subscription_id'] ) && $orderMeta['subscription_id'] != '' ){
                      $chargingPlan = 'https://secure.2checkout.com/cpanel/license_info.php?refno=' . esc_attr($orderMeta['subscription_id']);
                  }
                  break;
            }
            if ( $refundLink != '' ):?>
              <span>
                  <a title="<?php esc_html_e( 'Refund', 'ihc' );?>" href="<?php echo esc_url( $refundLink );?>" target="_blank" ><?php esc_html_e('Refund', 'ihc');?></a>
              </span>
            <?php endif;?>

            <?php if ( $chargingPlan != '' ):?>
              <span>
                  <a title="<?php esc_attr_e( 'Check charging plan on ' . $payment_gateway, 'ihc' );?>" href="<?php echo esc_url($chargingPlan);?>" target="_blank" ><?php esc_html_e('Check Charging Plan', 'ihc');?></a>
              </span>
            <?php endif;?>

          <?php endif;?>
              <span>
                  <a target="_blank" href="<?php echo admin_url( '/admin.php?page=ihc_manage&tab=order-details&order_id=' . esc_attr( $orderId ) );?>"><?php esc_html_e('Order Details', 'ihc');?></a>
              </span>
        </div>
      </div>
    </div>
    <div class="ihc-clear"></div>
		</div>

	</div>
</form>

<?php endif;?>
