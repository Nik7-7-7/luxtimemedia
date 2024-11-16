<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	ihc_save_update_metas('prorate_subscription');//save update metas
}
$data['metas'] = ihc_return_meta_arr('prorate_subscription');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
$stripeConnectStatus = ihc_check_payment_status('stripe_connect');
// save/update group
if ( isset( $_POST['ihc_save_prorate'] ) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		$memberships = isset( $_POST['memberships'] ) ? indeed_sanitize_array($_POST['memberships']) : [];

		if ( isset( $_POST['id'] ) ){
				// update
				foreach ( $memberships as $key => $levelId ){
						if ( $levelId === '' ){
								unset( $memberships[$key] );
						}
						$groupId = \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $levelId );
						if ( $groupId !== false && (int)$groupId !== (int)sanitize_text_field($_POST['id']) ){
								unset( $memberships[$key] );
						}
				}
				\Indeed\Ihc\Db\ProrateMembershipGroups::update( [
								'memberships'       => $memberships,
								'name'              => isset( $_POST['name'] ) ? sanitize_text_field($_POST['name']) : '',
								'id'		    => isset( $_POST['id'] ) ? sanitize_text_field($_POST['id']) : '',
					] );
		} else {
				// create
				foreach ( $memberships as $key => $levelId ){
						if ( $levelId === '' ){
								unset( $memberships[$key] );
						}
						if ( \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $levelId ) !== false ){
								unset( $memberships[$key] );
						}
				}
				\Indeed\Ihc\Db\ProrateMembershipGroups::create( [
								'memberships'       => $memberships,
								'name'              => isset( $_POST['name'] ) ? sanitize_text_field($_POST['name']) : '',
				] );
		}
}

// get all groups
$groups = \Indeed\Ihc\Db\ProrateMembershipGroups::getAll();
?>
<form  method="post">
	<div class="ihc-stuffbox">

		<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

		<h3 class="ihc-h3"><?php esc_html_e('Prorating Subscriptions', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
					<h2><?php esc_html_e('Activate/Hold Prorating Subscriptions', 'ihc');?></h2>
					<p><?php esc_html_e('In subscription billing, Proration is a system that allows customers to make changes in their subscription plans, including plan upgrade, downgrade of the subscribed Memberships', 'ihc');?></p>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_prorate_subscription_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_prorate_subscription_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_prorate_subscription_enabled" value="<?php echo esc_attr($data['metas']['ihc_prorate_subscription_enabled']);?>" id="ihc_prorate_subscription_enabled" />
			</div>
			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-12">
						<p><strong><?php esc_html_e('Important: Prorating Process is cover only by Stripe Connect Payment service. You can setup and activate this Payment Service ', 'ihc');?><a target="_blank" href="<?php echo esc_url( $url .'&tab=payment_settings&subtab=stripe_connect' );?>"><?php esc_html_e('here', 'ihc');?></a></strong></p>
					</div>
				</div>
			</div>
			<?php if(isset($stripeConnectStatus) && is_array($stripeConnectStatus) && isset($stripeConnectStatus['status']) && $stripeConnectStatus['status'] == 0){ ?>
			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-12">
						<div class="ihc-alert-warning">
							<?php esc_html_e('Stripe Connect Payment Service is not setup on your Website. In order to use Prorating Subscriptions module first setup Stripe Connect supported payment service.', 'ihc');?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
			<div class="iump-form-line">
				<div class="row">
					<div class="col-xs-6">
						<h4><?php esc_html_e('How it Works?', 'ihc');?></h4>
					<p><?php esc_html_e('For instance, a customer uses a monthly subscription plan A costs $20 and in the middle of the cycle, he or she chooses to switch their monthly subscription plan from A to B which costs $30, then proration adjusts the changes in the subscription charges in the billing as per the usage of any of the plans in a day. In this scenario, a customer is charged per Plan B for a further 15 days that is $15 instead of full price that is $30.', 'ihc');?></p>

					</div>
				</div>
				</div>
			<div class="iump-form-line">
				<h2><?php esc_html_e('Additional Settings', 'ihc');?></h2>
			</div>
      <div class="iump-form-line">
					<h4><?php esc_html_e('Reset Billing Period', 'ihc');?></h4>
					<p><?php esc_html_e('The billing period will be reset when a recurring Subscription is Upgraded.', 'ihc');?></p>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_prorate_subscription_reset_billing_period']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_prorate_subscription_reset_billing_period');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_prorate_subscription_reset_billing_period" value="<?php echo esc_attr($data['metas']['ihc_prorate_subscription_reset_billing_period']);?>" id="ihc_prorate_subscription_reset_billing_period" />
			</div>

			<div class="iump-form-line">
				<h4><?php esc_html_e('Change Plan button', 'ihc');?></h4>
				<div class="row">
							<div class="col-xs-4">
												 <div class="input-group">
														<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
														<input name="ihc_prorate_button_label" class="form-control" type="text" value="<?php echo ihc_correct_text($data['metas']['ihc_prorate_button_label']);?>"/>
												</div>
								</div>
				</div>
				<p><?php esc_html_e('Button will show up into Member Portal -> Subscriptions section where Members may see and manage their Memberships. This button will be available only if certain conditions are covered:', 'ihc');?></p>
				<ol>
					<li><?php esc_html_e('Proration Subscription module have been turned On.', 'ihc');?></li>
					<li><?php esc_html_e('One of supported Payment Services (Stripe Connect) are enabled and setup.', 'ihc');?></li>
					<li><?php esc_html_e('Subscription Plan page have been setup.', 'ihc');?></li>
					<li><?php esc_html_e('Existent Membership have been paid with supported Payment Services (Stripe Connect).', 'ihc');?></li>
					<li><?php esc_html_e('Existent Membership is part any of Prorate Groups.', 'ihc');?></li>
				</ol>
			</div>
			<div class="iump-form-line">
					<h4><?php esc_html_e('Show Prorating details on Checkout page', 'ihc');?></h4>
					<p><?php esc_html_e('You may inform members when they upgrade/downgrade their memberships with the adjusted credit calculated by the Proration Algorithm.', 'ihc');?></p>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_prorate_show_details_on_checkout']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_prorate_show_details_on_checkout');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_prorate_show_details_on_checkout" value="<?php echo esc_attr($data['metas']['ihc_prorate_show_details_on_checkout']);?>" id="ihc_prorate_show_details_on_checkout" />
			</div>


			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>
<div class="ihc-prorate-groups-wrapper">
	<div class="iump-page-title">Prorate Groups
		</div>
		<p><?php esc_html_e('Choose which Memberships will be part of Proration process. If no Group is created by default all Memberships will be taken in consideration.', 'ihc');?></p>
		<div class="ihc-prorate-groups-add-new-option">
				<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=prorate_add_edit' );?>" class="indeed-add-new-like-wp"><i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Group', 'ihc');?></a>
				<span class="ihc-top-message">...<?php esc_html_e('set Prorate Groups rules!', 'ihc');?></span>
	  </div>

		<div class="ihc-prorate-groups-list">

				<?php if ( $groups ):?>
						<?php foreach ( $groups as $id => $groupData ):?>
								<div class="ihc-coupon-admin-box-wrap">
									<div class="ihc-coupon-box-wrap ihc-box-background-f1505b">
										<div class="ihc-coupon-box-main">
											<div class="ihc-coupon-box-title"><?php echo esc_html($groupData['name']);?></div>
											<div class="ihc-coupon-box-content">
												<div class="ihc-coupon-box-levels"><?php esc_html_e("Target Memberships: ", "ihc"); ?>
															<?php if ( $groupData ):?>
																	<?php foreach ( $groupData['memberships'] as $membershipId ):?>
																			<div><?php echo \Indeed\Ihc\Db\Memberships::getMembershipLabel( $membershipId );?></div>
																	<?php endforeach;?>
															<?php endif;?>
												</div>
											</div>
										</div>
										<div class="ihc-coupon-box-bottom">
											<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=prorate_add_edit&id=' . $id );?>" class="ihc-coupon-box-link"><?php esc_html_e("Edit", "ihc"); ?></a>
											<div class="ihc-coupon-box-link ihc-js-prorate-delete-group" data-id="<?php echo esc_attr($id);?>" ><?php esc_html_e("Remove", "ihc"); ?></div>
										</div>
									</div>
								</div>
						<?php endforeach;?>
				<?php endif;?>

			<div class="ihc-clear"></div>
			</div>

		</div>
<?php
