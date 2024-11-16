<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Stripe - Payouts', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<div class="row">
						<div class="col-xs-7">
							<h2><?php esc_html_e('Activate/Hold Stripe Gateway', 'uap');?></h2>
							<p><?php esc_html_e('Once activated you can process payments to your affiliate users via Stripe directly from the affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_stripe_v3_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_stripe_v3_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_stripe_v3_enable" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_enable']);?>" id="uap_stripe_v3_enable" />
						</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="uap-form-line">
							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Live Secret Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_secret_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_secret_key']);?>" class="form-control" />
							</div>

							<div class="uap-form-line"></div>

							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Live Publishable Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_publishable_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_publishable_key']);?>" class="form-control" />
							</div>

							<div class="uap-form-line"></div>

							<div class="input-group">
             		<label class="input-group-addon"><?php esc_html_e('Live mode client ID', 'uap');?></label>
              		<input type="text" name="uap_stripe_v3_client_id" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_client_id']);?>" class="form-control" />
              </div>
						</div>

          <div class="row">
					<div class="col-xs-4">
						<h2><?php esc_html_e('Sandbox', 'uap');?></h2>
						<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_stripe_v3_sandbox']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_stripe_v3_sandbox');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_stripe_v3_sandbox" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_sandbox']);?>" id="uap_stripe_v3_sandbox" />
					</div>
				</div>
					<div class="row">
            <div class="uap-form-line">

									<div class="input-group">
											<label class="input-group-addon"><?php esc_html_e('Sandbox Secret Key', 'uap');?></label>
											<input type="text" name="uap_stripe_v3_sandbox_secret_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_sandbox_secret_key']);?>" class="form-control" />
									</div>

									<div class="uap-form-line"></div>

									<div class="input-group">
										<label class="input-group-addon"><?php esc_html_e('Sandbox Publishable Key', 'uap');?></label>
										<input type="text" name="uap_stripe_v3_sandbox_publishable_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_sandbox_publishable_key']);?>"  class="form-control"/>
									</div>

									<div class="uap-form-line"></div>
									<div class="input-group">
											<label class="input-group-addon"><?php esc_html_e('Test mode client ID', 'uap');?></label>
											<input type="text" name="uap_stripe_v3_sandbox_client_id" value="<?php echo esc_attr($data['metas']['uap_stripe_v3_sandbox_client_id']);?>" class="form-control" />
									</div>
							</div>
							<div class="uap-form-line"><h4><?php esc_html_e('Setup Intructions', 'uap');?></h4></div>


					<ul class="uap-info-list">
						<?php
							$notification_url = site_url();
							$notification_url = trailingslashit( $notification_url );
							$oauthRedirectBackPage = $notification_url . '?uap_act=stripe_v3_auth';
							$notification_url .= '?uap_act=stripe_v3_webhook';

							//$notification_url = add_query_arg( 'uap_act', 'stripe_v3_webhook', site_url() );
							//$oauthRedirectBackPage = add_query_arg( 'uap_act', 'stripe_v3_auth', site_url() );
						?>

						<li><?php echo esc_html__('1. Go to ', 'uap') . "<a href='https://stripe.com'>https://stripe.com</a>" . esc_html__( ' and login with username and password ', 'uap');?> </li>
						<li><?php echo esc_html__('2. Create a new Account application by clicking on New Account top left menu button into dashboard.', 'uap');?> </li>
						<li><?php echo esc_html__('3. In order to process payments to your affiliates go to Connect tab menu and get started financial services and multi-party payments with Connect.', 'uap');?> </li>
						<li><?php echo esc_html__('4. After that You\'ll find the section "Developers" at the top, click it.', 'uap');?> </li>
						<li><?php echo esc_html__('5. In "Developers" section click on "Webhooks" and add a new Endpoint.', 'uap');?> </li>
						<li><?php echo esc_html__('6. Set Your "Endpoint URL" at: ', 'uap') . "<b>" . $notification_url . "</b>"  . esc_html__(', at "Listen to" select "Events to your account", add "Transfers" on Events. and save it.', 'uap');?></li>
						<li><?php echo esc_html__('7. Go to "API keys", here you will find the "Secret Key" and "Publishable Key, copy and paste them into Ultimate Affiliate Pro - Stripe Settings page.', 'uap');?></li>
						<li><?php echo esc_html__('8. In the top right You’ll find the settings button, click it. You will arrive at: ', 'uap') . 'https://dashboard.stripe.com/settings';?></li>
						<li><?php echo esc_html__('9. In the "Connect" section You\'ll find "Settings", click it.', 'uap');?></li>
						<li><?php echo esc_html__('10. In the "Account types" be sure that "Standard" and "Express" options are enabled.', 'uap');?></li>
						<li><?php echo esc_html__('11. On "Capabilities" section the "Transfers" also must be enabled.', 'uap');?></li>
						<li><?php echo esc_html__('12. Complete the "Branding" section with Your personal Logo and Informations.', 'uap');?></li>
						<li><?php echo esc_html__('13. In "Integration" You\'ll find the client ID, copy and paste into Ultimate Affiliate Pro - Stripe Settings page. ', 'uap');?></li>
						<li><?php echo esc_html__('14. In "OAuth settings" section enable the "OAuth for Standard accounts".', 'uap');?></li>
						<li><?php echo esc_html__('15. On "Redirects" add the following URL : ', 'uap') .  esc_uap_content("<b>" . $oauthRedirectBackPage . "</b>");?></li>
						<li><?php echo esc_html__('16. Right up beside "Integration" You\'ll find a button ("Test OAuth…"), in order to check if everything is configured correctly. Run a test in order to check everything in set up correctly.', 'uap');?></li>
						<li><?php echo esc_html__('17. Run some tests in Sandbox Mode before switching to Live Transactions.', 'uap' );?></li>
				</ul>
				</div>

				<div class="row">
						<div class="col-xs-6">
							<div class="uap-form-line">
								<?php
									if ( !isset( $data['metas']['uap_stripe_v3_source_type'] ) ){
											$data['metas']['uap_stripe_v3_source_type'] = 'card';
									}
								?>
								<div class="input-group">
								<label class="input-group-addon"><?php esc_html_e('Balance Source', 'uap');?></label>
								<select name="uap_stripe_v3_source_type">
										<option value="bank_account" <?php if ( $data['metas']['uap_stripe_v3_source_type'] === 'bank_account' ) echo esc_attr('selected');?> ><?php esc_html_e('Bank Account', 'uap');?></option>
										<option value="card" <?php if ( $data['metas']['uap_stripe_v3_source_type'] === 'card' ) echo esc_attr('selected');?> ><?php esc_html_e('Card', 'uap');?></option>
										<option value="fpx" <?php if ( $data['metas']['uap_stripe_v3_source_type'] === 'fpx' ) echo esc_attr('selected');?> ><?php esc_html_e('FPX', 'uap');?></option>
								</select>
								</div>
								<p><?php esc_html_e('The source balance to use for transfers.','uap'); ?></p>
							</div>
						</div>
				</div>

				<div class="inside">
					<h5 class="uap-h5"><?php esc_html_e('Important:', 'uap');?></h5>
					<p><?php esc_html_e('1. When Affiliate connects his Stripe Account with your merchant Stripe Account, Stripe will create a Sub Account for him where payout will be transferred.','uap'); ?></p>
					<p><?php esc_html_e('2. Affiliates may see all their Sub Accounts on Profile page from Stripe Dashboard.', 'uap');?></p>
					<p><?php esc_html_e('3. In order to run some tests before switching live, make sure that in Stripe balance you have the required amount to be in the same currency as the currency saved in UAP.', 'uap');?></p>
					<p><?php esc_html_e('4. In "Settings" > "Connect settings" make sure that you have configured ', 'uap');?> <b> <?php esc_html_e('Accounts Types', 'uap');?></b> <?php esc_html_e(' and ', 'uap');?> <b> <?php esc_html_e('Capabilities.', 'uap');?></b></p>
				</div>

				<div id="uap_save_changes" class="uap-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</form>
</div>
