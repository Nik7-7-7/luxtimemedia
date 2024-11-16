<div class="uap-wrapper">
	<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Referral Notifications', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/referral-notifications/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-10">
					<h2><?php esc_html_e('Activate/Hold Referral Notifications', 'uap');?></h2>
					<p><?php esc_html_e('If this module is activated, affiliates have the option to receive instant notifications when they get new referrals. The affiliate can decide from his Affiliate Portal which type of referrals he will be notified of via email.', 'uap');?></p>
					<label class="uap_bp_account_page_enable uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_referral_notifications_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_referral_notifications_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_referral_notifications_enable" value="<?php echo esc_attr($data['metas']['uap_referral_notifications_enable']);?>" id="uap_referral_notifications_enable" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-6">
					<h4><?php esc_html_e('Notification Subject', 'uap');?></h4>
					<div class="input-group">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Subject', 'uap');?></span>
					<input type="text" class="uap-field-text-with-padding form-control" name="uap_referral_notification_subject" value="<?php echo esc_attr($data['metas']['uap_referral_notification_subject']);?>" />
				</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12">
					<h4><?php esc_html_e('Notification Content', 'uap');?></h4>
					<div class="uap-wp_editor uap-wp-editor-box">
					<?php wp_editor(stripslashes($data['metas']['uap_referral_notification_content']), 'uap_referral_notification_content', array('textarea_name'=>'uap_referral_notification_content', 'editor_height'=>400));?>
					</div>
					<div class="uap-wp-editor-constants">
						<?php echo esc_uap_content("<h4>" . esc_html__('Referral details Tags', 'uap') . "</h4>"); ?>
						<?php foreach ($data['notification_constants'] as $key=>$value) : ?>
							<div ><?php echo esc_uap_content('<span><strong>'.$value . '</strong></span> : ' . $key);?></div>
						<?php endforeach; ?>
						<?php
						echo esc_uap_content("<h4>" . esc_html__('Native Fields Tags', 'uap') . "</h4>");
							$constants = array(	"{username}",
												"{first_name}",
												"{last_name}",
												"{user_id}",
												"{user_email}",
												"{account_page}",
												"{login_page}",
												"{blogname}",
												"{blogurl}",
												"{siteurl}",
												'{rank_id}',
												'{rank_name}',
							);
							$extra_constants = uap_get_custom_constant_fields();
							foreach ($constants as $v){
								?>
								<div><?php echo esc_html($v);?></div>
								<?php
							}
							echo esc_uap_content("<h4>" . esc_html__('Custom Fields Tags', 'uap') . "</h4>");
							foreach ($extra_constants as $k=>$v){
								?>
								<div><?php echo esc_html($k);?></div>
								<?php
							}
						?>
					</div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>
</div>
