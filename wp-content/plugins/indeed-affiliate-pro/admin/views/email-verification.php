<div class="uap-wrapper">
	<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Email Verification', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/e-mail-verification/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">

			<div class="uap-form-line">
				<h2><?php esc_html_e('Activate/Hold Email Verification', 'uap');?></h2>
				<p><?php esc_html_e('Requires the email address for new Affiliates to be verified before they will be able to login. If the email address is not confirmed, the user account may be automatically deleted after a certain time.', 'uap');?></p>
				<div>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_register_double_email_verification']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_register_double_email_verification');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_register_double_email_verification" value="<?php echo esc_attr($data['metas']['uap_register_double_email_verification']);?>" id="uap_register_double_email_verification" />
					<p>
						<?php esc_html_e('Be sure that your notifications for','uap');?> "<strong>Double Email Verification</strong>" <?php esc_html_e(' are properly set.','uap');?>
					</p>
				</div>
			</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-5">

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Activation Link Expire Time', 'uap');?></span>
							<select name="uap_double_email_expire_time" class="form-control m-bot15">
								<?php
									$arr = array(
															'-1' => 'Never',
															'900' => '15 Minutes',
															'3600' => '1 Hour',
															'43200' => '12 Hours',
															'86400' => '1 Day',
															);
									foreach ($arr as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k)?>" <?php echo ($k==$data['metas']['uap_double_email_expire_time']) ? 'selected' : '';?> >
											<?php echo esc_html($v);?>
										</option>
										<?php
									}
								?>
							</select>
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Success Redirect', 'uap');?></span>
							<select name="uap_double_email_redirect_success" class="form-control m-bot15">
								<option value="-1" <?php echo ($data['metas']['uap_double_email_redirect_success']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($data['pages']){
										foreach ($data['pages'] as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_double_email_redirect_success']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Error Redirect', 'uap');?></span>
							<select name="uap_double_email_redirect_error" class="form-control m-bot15">
								<option value="-1" <?php echo ($data['metas']['uap_double_email_redirect_error']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($data['pages']){
										foreach ($data['pages'] as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['uap_double_email_redirect_error']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Delete User if is not verified', 'uap');?></span>
							<select name="uap_double_email_delete_user_not_verified" class="form-control m-bot15">
								<?php
									$arr = array(
															'-1' => 'Never',
															'1' => 'After 1 day',
															'7' => 'After 7 days',
															'14' => 'After 14 days',
															'30' => 'After 30 days',
															);
									foreach ($arr as $k=>$v){
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['uap_double_email_delete_user_not_verified']) ? 'selected' : '';?> >
											<?php echo esc_html($v);?>
										</option>
										<?php
									}
								?>
							</select>
					</div>

				</div>
			</div>
		</div>

		<div id="uap_save_changes" class="uap-submit-form">
			<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
		</div>

		</div>
	</div>
</form>
</div>
