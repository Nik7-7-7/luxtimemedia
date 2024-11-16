<div class="uap-wrapper">
<form method="post" >
	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Custom Messages', 'uap');?></h3>

					<div class="inside uap-custom-messages">
						<div class="uap-form-line">
							<div class="uap-inside-item">
							<div class="row">
								<div class="col-xs-10">
						<h2><?php esc_html_e('Login Messages', 'uap');?></h2>

							<div class="uap-space"></div>

								<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Successfully Message', 'uap');?></div>
								<div class="uap-input-text"><input type="text" name="uap_login_succes" class="" value="<?php echo uap_correct_text($data['metas']['uap_login_succes']);?>"></div>
								</div>

								<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Default message for pending users', 'uap');?>
								</div>
									<div class="uap-input-text">
										<input type="text" name="uap_login_pending" class="" value="<?php echo uap_correct_text($data['metas']['uap_login_pending']);?>">
										<div class="uap-field-details"><?php esc_html_e('This notice will appear during login process if the administrator has not approved the affiliate account.', 'uap');?></div>
									</div>

								</div>

								<div class="uap-labels-special">
									<div class="uap-label-text"><?php esc_html_e('Error Message', 'uap');?></div>
								<div class="uap-input-text">
									<input type="text" name="uap_login_error" class="" value="<?php echo uap_correct_text($data['metas']['uap_login_error']);?>">
									<div class="uap-field-details"><?php esc_html_e('This notice will appear during login process if the email address or password were typed wrongly.', 'uap');?></div>
								</div>
								</div>

								<div class="uap-labels-special">
									<div class="uap-label-text"><?php esc_html_e('Email Pending', 'uap');?></div>
								<div class="uap-input-text">
									<input type="text" name="uap_login_error_email_pending" class="" value="<?php echo uap_correct_text($data['metas']['uap_login_error_email_pending']);?>">
									<div class="uap-field-details"><?php esc_html_e('This notice will appear during the login procedure if the affiliate\'s email address has not been approved and he attempts to log in.', 'uap');?></div>
								</div>
								</div>

								<div class="inside">
								<div class="uap-space"></div>
								<div class="uap-line-break"></div>
								<div class="uap-space"></div>
							</div>

							<h2><?php esc_html_e('Reset Password Messages', 'uap');?></h2>
								<div class="uap-space"></div>
								<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Successfully Message', 'uap');?></div>
								<div class="uap-input-text"><input type="text" name="uap_reset_msg_pass_ok" class="" value="<?php echo uap_correct_text($data['metas']['uap_reset_msg_pass_ok']);?>"></div>
								</div>


								<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Error Message', 'uap');?>	</div>
								<div class="uap-input-text">
									<input type="text" name="uap_reset_msg_pass_err" class="" value="<?php echo uap_correct_text($data['metas']['uap_reset_msg_pass_err']);?>">
									<div class="uap-field-details"><?php esc_html_e('This notice will appear during the reset password procedure if the affiliate\'s email address or current password is incorrectly typed.', 'uap');?></div>
								</div>
								</div>


								<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('reCaptcha Error Message', 'uap');?></div>
								<div class="uap-input-text">
									<input type="text" name="uap_login_error_on_captcha" class="" value="<?php echo uap_correct_text($data['metas']['uap_login_error_on_captcha']);?>">
										<div class="uap-field-details"><?php esc_html_e('This notice will appear during the reset password procedure if the affiliate does not correctly fill out the form.', 'uap');?></div>
								</div>
								</div>
							</div>
							</div>
					</div>
				</div>
					<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
			</div>
			</div>
</form>
</div>
