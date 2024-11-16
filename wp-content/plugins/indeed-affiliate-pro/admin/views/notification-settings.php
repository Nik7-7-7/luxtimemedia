<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Email Notifications Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h4><?php esc_html_e('Sender Details', 'uap');?></h4>
			</div>
			<div class="uap-form-line">
			 <div class="row">
					<div class="col-xs-8">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e("Email Address", 'uap');?></span>
							<input type="text" class="form-control" name="uap_notification_email_from" value="<?php echo esc_attr($data['metas']['uap_notification_email_from']);?>"/>
						</div>
						<p><?php esc_html_e('Set the email address which emails wil be sent from. This will act as the "from" and "reply-to" address.', 'uap');?>
					</div>
				</div>
			</div>
			<div class="uap-form-line">
			 <div class="row">
					<div class="col-xs-8">
						<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e("From Name", 'uap');?></span>
									<input type="text" class="form-control" name="uap_notification_name" value="<?php echo esc_attr($data['metas']['uap_notification_name']);?>"/>
						</div>
						<p><?php esc_html_e('Customze the email from Name. The standard is to use your Site Name.', 'uap');?>
					</div>
				</div>
			</div>

			<div class="uap-form-line">
				<h4><?php esc_html_e('Affiliate Manager Notifications Email Address(es)', 'uap');?></h4>
				<p><?php esc_html_e('The email address(es) to receive Affiliate Manager notifications. Separate multiple email addresses with a comma (,).', 'uap');?>
			</div>
			<div class="uap-form-line">
			 <div class="row">
					<div class="col-xs-8">
						<div class="input-group">
									<span class="input-group-addon"><?php esc_html_e("Affiliate Manager Email Address", 'uap');?></span>
									<input type="text" class="form-control" name="uap_admin_notification_address" value="<?php echo esc_attr($data['metas']['uap_admin_notification_address']);?>"/>
						</div>
					</div>
				</div>
			</div>

			<div class="uap-form-line">
				<h2><?php esc_html_e('Notification Logs', 'uap');?></h2>
				<p><?php esc_html_e('Clean Up the Email Notifcations logs longer than a certain period', 'uap');?>
			</div>
			<div class="uap-form-line">
				<?php $we_have_logs = \Indeed\Uap\Db\NotificationLogs::countAll();?>
				<?php if ($we_have_logs):?>
						 <div class="row">
								<div class="col-xs-8">
							<select id="uap_older_then_select" class="form-control">
									<option value="">...</option>
									<option value="1"><?php esc_html_e('older than One Day', 'uap'); ?></option>
									<option value="7"><?php esc_html_e('older than One Week', 'uap'); ?></option>
									<option value="30"><?php esc_html_e('older than One Month', 'uap'); ?></option>
							</select>
							</div>
						</div>
						<div class="button button-primary button-large uap-first-button uap-text-center" onclick="uapDoCleanUpLogs('');">Clean Up</div>
					<?php else :?>
						<div class="iump-form-line">
						       <?php esc_html_e('No notification logs yet', 'uap');?>
	          </div>
					<?php endif;?>
				</div>



			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" onClick="" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>
</div>
