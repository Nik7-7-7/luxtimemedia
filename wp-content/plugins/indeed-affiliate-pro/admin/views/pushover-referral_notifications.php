<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('PushOver Referrals Notifications', 'uap');?></h3>
		<div class="inside">

			<div class="uap-form-line">
				<h2><?php esc_html_e('Activate/Hold PushOver Referrals Notifications', 'uap');?></h2>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_pushover_referral_notifications_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_pushover_referral_notifications_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_pushover_referral_notifications_enabled" value="<?php echo esc_attr($data['metas']['uap_pushover_referral_notifications_enabled']);?>" id="uap_pushover_referral_notifications_enabled" />
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>


</form>

</div>
