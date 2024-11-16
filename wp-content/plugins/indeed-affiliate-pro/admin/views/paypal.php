<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php  esc_html_e('PayPal - Payouts', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/paypal/" target="_blank"><?php  esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<?php if ((float)$phpversion>=5.4):?>
				<div class="row">
						<div class="col-xs-7">
							<h2><?php  esc_html_e('Activate/Hold PayPal Gateway', 'uap');?></h2>
							<p><?php  esc_html_e('Once activated you can process payments to your affiliate users via PayPal directly from the affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_paypal_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_paypal_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_paypal_enable" value="<?php echo esc_attr($data['metas']['uap_paypal_enable']);?>" id="uap_paypal_enable" />
						</div>
						</div>

				<div class="row">
					<div class="col-xs-4">
						<h2><?php  esc_html_e('Sandbox Mode', 'uap');?></h2>
						<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_paypal_sandbox']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch uap-js-paypal-sandbox-on-off" onClick="uapCheckAndH(this, '#uap_paypal_sandbox');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_paypal_sandbox" value="<?php echo esc_attr($data['metas']['uap_paypal_sandbox']);?>" id="uap_paypal_sandbox" />
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="uap-js-paypal-sandbox-credentials  <?php echo ( !$data['metas']['uap_paypal_sandbox'] ) ? "uap-display-none" : '';?> ">
								<div class="uap-form-line">
									<div class="input-group">
							         <span class="input-group-addon"><?php esc_html_e('Sandbox Client ID', 'uap');?></span>
									<input type="text" name="uap_paypal_sandbox_client_id" value="<?php echo esc_attr($data['metas']['uap_paypal_sandbox_client_id']);?>" class="form-control"/>
								</div>

								<div class="uap-form-line"></div>

								<div class="input-group">
									<span class="input-group-addon"><?php  esc_html_e('Sandbox Client Secret', 'uap');?></span>
									<input type="text" name="uap_paypal_sandbox_client_secret" value="<?php echo esc_attr($data['metas']['uap_paypal_sandbox_client_secret']);?>" class="form-control"/>
								</div>
							</div>
						</div>

						<div class="uap-js-paypal-live-credentials <?php echo ( $data['metas']['uap_paypal_sandbox'] ) ? "uap-display-none" : '';?>"  >
								<div class="uap-form-line">
									<div class="input-group">
									<span class="input-group-addon"><?php  esc_html_e('Client ID', 'uap');?></span>
									<input type="text" name="uap_paypal_client_id" value="<?php echo esc_attr($data['metas']['uap_paypal_client_id']);?>" class="form-control" />
								</div>

								<div class="uap-form-line"></div>

								<div class="input-group">
									<span class="input-group-addon"><?php  esc_html_e('Client Secret', 'uap');?></span>
									<input type="text" name="uap_paypal_client_secret" value="<?php echo esc_attr($data['metas']['uap_paypal_client_secret']);?>" class="form-control" />
								</div>
							</div>
						</div>

				</div>
				</div>

				<div>
					<ul class="uap-info-list">
						<li><?php  esc_html_e('1. Go to ', 'uap');?><a href="https://developer.paypal.com/" target="_blank">https://developer.paypal.com/</a> <?php  esc_html_e('and login with your PayPal email and password.', 'uap');?></li>
						<li><?php  esc_html_e('2. After you have successfully logged in go to: ', 'uap');?> <a target="_blank" href="https://developer.paypal.com/developer/applications/">https://developer.paypal.com/developer/applications/</a> <?php  esc_html_e(' and create a new REST API application.', 'uap');?></li>
						<li><?php  esc_html_e('3. After you have created a new application, the "Client ID" and "Client Secret" will be available.', 'uap');?></li>
						<li><?php  esc_html_e( '4. Be sure your PayPal account has permissions for Payouts.', 'uap' );?> <a href='https://developer.paypal.com/developer/accountStatus/' target="_blank">https://developer.paypal.com/developer/accountStatus/</a></li>
					</ul>
				</div>

				<div id="uap_save_changes" class="uap-submit-form">
					<input type="submit" value="<?php  esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
				</div>
			<?php else : ?>
				<div class="uap-color-red">
					<?php echo esc_html__("Your current version of PHP is ", 'uap') . $phpversion . esc_html__('. To use this feature You need >= PHP 5.4.', 'uap');?>
				</div>
			<?php endif;?>
		</div>
		</div>
	</div>
</form>
</div>
