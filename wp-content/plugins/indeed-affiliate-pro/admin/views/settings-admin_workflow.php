<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Admin Workflow Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-12">
						<h2><?php esc_html_e('Update Frequency', 'uap');?></h2>
						<p><?php esc_html_e('Affiliate performances are regularly verified, and their Ranks are automatically updated through scheduled cron processes. Payouts are also periodically reviewed.', 'uap');?></p>
					</div>
				</div>
		</div>
		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
							<span class="uap-labels-special"><?php esc_html_e('Update Affiliates Rank:', 'uap');?></span>
							<select name="uap_update_ranks_interval" class="form-control m-bot15"><?php
								$values = array(
													'hourly' => esc_html__('Hourly', 'uap'),
													'twicedaily' => esc_html__('At every 12hours', 'uap'),
													'daily' => esc_html__('Daily', 'uap'),
								);
								foreach ($values as $k=>$v){
									$selected = ($data['metas']['uap_update_ranks_interval']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
									<?php
								}
							?></select>
						</div>
					</div>
			</div>
			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-6">
							<span class="uap-labels-special"><?php esc_html_e('Update Payments Status:', 'uap');?></span>
							<select name="uap_update_payments_status" class="form-control m-bot15"><?php
								$values = array(
													'hourly' => esc_html__('Hourly', 'uap'),
													'twicedaily' => esc_html__('At every 12hours', 'uap'),
													'daily' => esc_html__('Daily', 'uap'),
								);
								foreach ($values as $k=>$v){
									$selected = ($data['metas']['uap_update_payments_status']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
									<?php
								}
							?></select>
						</div>
					</div>
			</div>


			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e('Keep Referral Status as Pending', 'uap');?></label>
					<span class="uap-form-setting-item">
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_workflow_referral_status_dont_automatically_change']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_workflow_referral_status_dont_automatically_change');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_workflow_referral_status_dont_automatically_change" value="<?php echo esc_attr($data['metas']['uap_workflow_referral_status_dont_automatically_change']);?>" id="uap_workflow_referral_status_dont_automatically_change" />
					</span>
					<p class="uap-form-setting-description"><?php esc_html_e("Don't change the Referral Status to Approved", 'uap');?></p>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e('Disable IP Address Logging', 'uap');?></label>
					<span class="uap-form-setting-item">
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_workflow_disable_ip_address']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_workflow_disable_ip_address');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_workflow_disable_ip_address" value="<?php echo esc_attr($data['metas']['uap_workflow_disable_ip_address']);?>" id="uap_workflow_disable_ip_address" />
					</span>
					<p class="uap-form-setting-description"><?php esc_html_e("Disable logging of the customer IP address  for improved GDPR compliance", 'uap');?></p>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e('Show Dashboard Notifications', 'uap');?></label>
					<span class="uap-form-setting-item">
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_admin_workflow_dashboard_notifications']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_admin_workflow_dashboard_notifications');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_admin_workflow_dashboard_notifications" value="<?php echo esc_attr($data['metas']['uap_admin_workflow_dashboard_notifications']);?>" id="uap_admin_workflow_dashboard_notifications" />
					</span>
					<p class="uap-form-setting-description"><?php esc_html_e("New Affiliates & Referrals", 'uap');?></p>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e('Data on Uninstall', 'uap');?></label>
					<span class="uap-form-setting-item">
						<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_keep_data_after_delete']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_keep_data_after_delete');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
						</label>
							<input type="hidden" name="uap_keep_data_after_delete" value="<?php echo esc_attr($data['metas']['uap_keep_data_after_delete']);?>" id="uap_keep_data_after_delete" />
						</span>
					<p class="uap-form-setting-description"><?php esc_html_e("Keep all saved data for Ultimate Affiliate Pro when the plugin is deleted.", 'uap');?></p>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e('Enable Tracking', 'uap');?></label>
					<span class="uap-form-setting-item">
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_allow_tracking']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_allow_tracking');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_allow_tracking" value="<?php echo esc_attr($data['metas']['uap_allow_tracking']);?>" id="uap_allow_tracking" />
					</span>
					<p class="uap-form-setting-description"><?php esc_html_e('By choosing this option, you give us permission to gather some technical information about your website in order to improve the plugin. A full list of the data to be collected can be found ', 'uap');?>
					<a href="https://ultimateaffiliate.pro/usage-tracking/" target="_blank"><?php esc_html_e( 'here', 'uap' );?></a></p>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
		</div>
</form>
</div>

<?php
