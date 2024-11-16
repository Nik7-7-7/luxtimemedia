<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Stripe v2 - Payouts', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<div class="row">
						<div class="col-xs-7">
							<h2><?php esc_html_e('Activate/Hold Stripe Gateway', 'uap');?></h2>
							<p><?php esc_html_e('Once activated you can process payments to your affiliate users via Stripe directly from the affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_stripe_v2_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_stripe_v2_enable');" <?php echo esc_attr($checked);?> disabled />
								<div class="switch uap-display-inline uap-opacity-disabled"></div>
							</label>
							<input type="hidden" name="uap_stripe_v2_enable" value="<?php echo esc_attr($data['metas']['uap_stripe_v2_enable']);?>" id="uap_stripe_v2_enable" />
							<p><strong><?php esc_html_e('This Stripe API is deprecated. Please use Stripe V3 module instead.', 'uap');?></strong></p>
						</div>
				</div>

				<div class="row">
					<div class="col-xs-4">
						<h2><?php esc_html_e('Sandbox', 'uap');?></h2>
						<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_stripe_v2_sandbox']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_stripe_v2_sandbox');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_stripe_v2_sandbox" value="<?php echo esc_attr($data['metas']['uap_stripe_v2_sandbox']);?>" id="uap_stripe_v2_sandbox" />
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="uap-form-line">

							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Sandbox Secret Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v2_sandbox_secret_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v2_sandbox_secret_key']);?>" / class="form-control">
						 </div>
						 <div class="uap-form-line"></div>
						 <div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Sandbox Publishable Key', 'uap');?></label>
							<input type="text" name="uap_stripe_v2_sandbox_publishable_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v2_sandbox_publishable_key']);?>" class="form-control" />
							</div>
							<div class="uap-form-line"></div>
							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Live Secret Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v2_secret_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v2_secret_key']);?>" class="form-control" />
							</div>
							<div class="uap-form-line"></div>
							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Live Publishable Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v2_publishable_key" value="<?php echo esc_attr($data['metas']['uap_stripe_v2_publishable_key']);?>" class="form-control" />
							</div>

						</div>
					</div>
				</div>

				<div>
					<ul class="uap-info-list">
						<?php

							$notification_url = site_url();
							$notification_url = trailingslashit($notification_url);
							$notification_url = add_query_arg('uap_act', 'stripe_payout', $notification_url);
						?>
						<li><?php esc_html_e('1. Go to', 'uap');?> <a href="http://stripe.com" target="_blank">http://stripe.com</a> <?php esc_html_e(' and login with username and password.', 'uap');?></li>
						<li><?php esc_html_e('2. Click on "Dashboard".', 'uap');?></li>
						<li><?php esc_html_e('3. In left you will find a menu that contains "Developers" section. Enter into this section and you will find the API Keys("Secret Key" and "Publishable Key").', 'uap');?></li>
						<li><?php echo esc_html__('4. Do not forget to set Your Webhook at: ', 'uap') . '<b>' . $notification_url . '</b>';?></li>
						<li><?php esc_html_e('5. Be sure you use the same currency in the Stripe account and in the "Ultimate Affiliate Pro" settings.', 'uap');?></li>
						<li><?php echo esc_html__('For testing purposes, you can find card credentials here: ', 'uap') . '<a href="https://stripe.com/docs/testing#test-debit-card-numbers" target="_blank">https://stripe.com/docs/testing#test-debit-card-numbers</a>';?></li>
					</ul>
				</div>

				<div id="uap_save_changes" class="uap-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	</div>
</form>

<div class="uap-stuffbox">
	<h3 class="uap-h3"><?php esc_html_e('Additional informations', 'uap');?></h3>
	<div class="inside">
			<div class="row">
					<div class="col-xs-7">
						<div><?php esc_html_e('Accepted countries:', 'uap');?></div>
						<div>- United States (US)</div>
						<div>- Great Britain (GB)</div>
						<div>- Denmark (DK)</div>
						<div>- Germany (DE)</div>
						<div>- Belgium (BE)</div>
						<div>- Italy (IT)</div>
						<div>- Switzerland (CH)</div>
						<div>- Austria (AT)</div>
						<div>- Finland (FI)</div>
						<div>- Netherlands (NL)</div>
						<div>- Norway (NO)</div>
						<div>- Sweden (SE)</div>
						<div>- Spain (ES)</div>
						<div>- Republic of Ireland (IE)</div>
						<div>- Luxembourg (LU)</div>
						<div>- Portugal (PT)</div>
					</div>
					<?php echo esc_html__('You can find more details here: ', 'uap') . '<a href="https://stripe.com/docs/connect/testing" target="_blank">https://stripe.com/docs/connect/testing</a>';?>
				</div>
		</div>
</div>

<div class="uap-stuffbox">
	<h3 class="uap-h3"><?php esc_html_e('Testing payout data', 'uap');?></h3>
	<div class="inside">
			<div class="row">
					<div class="col-xs-7">
							<div><?php echo esc_html__('Country: ', 'uap') . ' US';?></div>
							<div><?php echo esc_html__('Bank number. Account: ', 'uap') . ' 000123456789 . ' . esc_html__('Rounting: ', 'uap') . ' 110000000 .';?></div>
							<div><?php echo esc_html__('Personal ID numbers: ', 'uap') . ' 000000000 .';?></div>
							<div><?php echo esc_html__('Business tax ID: ', 'uap') . ' 000000000 .';?></div>
					</div>
				</div>
		</div>
</div>
