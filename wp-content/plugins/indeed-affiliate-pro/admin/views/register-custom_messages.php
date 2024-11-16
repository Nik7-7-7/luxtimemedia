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
										<h2><?php esc_html_e('Error Messages', 'uap');?></h2>
									</div>
									<div class="col-xs-10">
										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Username already exists', 'uap');?></div>
											<div class="uap-input-text">
												<input type="text" name="uap_register_username_taken_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_username_taken_msg']);?>">
												<div class="uap-field-details"><?php esc_html_e('This message means that the username have been chosen is already being used by another user.', 'uap');?></div>
											</div>
										</div>


										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Username is invalid', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_error_username_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_error_username_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Email Address already exists', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_email_is_taken_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_email_is_taken_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Email Address is invalid', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_invalid_email_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_invalid_email_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Email Addresses did not match', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_emails_not_match_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_emails_not_match_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Passwords did not match', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_pass_not_match_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_pass_not_match_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Password Only Characters and Digits', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_pass_letter_digits_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_pass_letter_digits_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Password Min Length', 'uap');?></div>
											<div class="uap-input-text">
												<input type="text" name="uap_register_pass_min_char_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_pass_min_char_msg']);?>">
													<div id="uap_msg_alert" class="uap-field-details"><?php esc_html_e('Where {X} will be the minimum length of password.', 'uap');?></div>
											</div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Password Characters, Digits and minimum one uppercase letter', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_pass_let_dig_up_let_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_pass_let_dig_up_let_msg']);?>"></div>
										</div>
										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Account is not approved yet', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_pending_user_msg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_pending_user_msg']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Required fields are missing', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_err_req_fields" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_err_req_fields']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Required ReCaptcha', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_err_recaptcha" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_err_recaptcha']);?>"></div>
										</div>

										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Terms of Services checkbox', 'uap');?></div>
											<div class="uap-input-text"><input type="text" name="uap_register_err_tos" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_err_tos']);?>"></div>
										</div>
									</div>
									<div class="col-xs-10">
										<h2><?php esc_html_e('Success Message', 'uap');?></h2>
									</div>
									<div class="col-xs-10">
										<div class="uap-labels-special"><div class="uap-label-text"><?php esc_html_e('Registration process has Completed', 'uap');?></div>
											<div class="uap-input-text">
												<input type="text" name="uap_register_success_meg" class="" value="<?php echo uap_correct_text($data['metas']['uap_register_success_meg']);?>">
													<div class="uap-field-details"><?php esc_html_e('This message will show up after the user has finish the registration step.', 'uap');?></div>
											</div>

										</div>

									</div>
									</div>
									</div>
							</div>
							<div id="uap_save_changes" class="uap-submit-form">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" onClick="" class="button button-primary button-large" />
							</div>
						</div>
					</div>
			</form>
		</div>
