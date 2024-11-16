			<form action="<?php echo esc_url($data['form_action_url']);?>" method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

<div class="uap-wrapper">
		<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Add new Email Notification', 'uap');?></h3>
				<div class="inside">
					<div class="uap-form-line">
						<h2><?php esc_html_e('Email Notification Action', 'uap');?></h2>
						<select name="type" id="notf_type" onChange="uapReturnNotification();" class="uap-form-select uap-form-element uap-form-element-select uap-form-select">
						<?php foreach ($data['actions_available'] as $k=>$v):?>
							<?php
								switch ($k){
									case 'admin_user_register':
										echo esc_uap_content(' <optgroup label="' . esc_html__('-----Affiliate Manager Notifications-----', 'uap') . '">');
										break;
									case 'register':
										echo esc_uap_content(' </optgroup><optgroup label="' . esc_html__('-----Affiliate Notifications-----', 'uap') . '"></optgroup>');
										echo esc_uap_content(' <optgroup label="' . esc_html__('Registration Process', 'uap') . '">');
										break;

									case 'user_update':
										echo esc_uap_content(' </optgroup><optgroup label="' . esc_html__('Affiliate Account', 'uap') . '">');
										break;

									case 'reset_password_process':
										echo esc_uap_content(' </optgroup><optgroup label="' . esc_html__('Reset Password Process', 'uap') . '">');
										break;
										case 'affiliate_payment_fail':
											echo esc_uap_content(' </optgroup><optgroup label="' . esc_html__('Payouts Process', 'uap') . '">');
											break;
									case 'email_check':
										echo esc_uap_content(' </optgroup><optgroup label="' . esc_html__('Double Email Verification', 'uap') . '">');
										break;

								}
							?>
							<?php $selected = ($k==$data['type']) ? 'selected' : '';?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
							<?php
								/*switch ($k){
									case 'register_lite_send_pass_to_user':
									case 'affiliate_payment_complete':
									case 'change_password':
									case 'rank_change':
									case 'admin_affiliate_update_profile':
									case 'email_check_success':
										//echo esc_uap_content('</optgroup>');
										break;
								}*/
							?>
						<?php endforeach;
						echo esc_uap_content('</optgroup>');
						?>
						</select>
						<div class="uap-notification-description-wrap">
							<div id="" class="uap-js-notification-description uap-notification-description"><?php if ( isset( $data['notification_description'] ) ){ echo $data['notification_description'];}?></div>
					 	</div>
					</div>
					<div class="uap-special-line">
						<h2><?php esc_html_e('Choose the Target Rank', 'uap')?></h2>
						<select name="rank_id" class="uap-form-select uap-form-element uap-form-element-select uap-form-select">
						<?php foreach ($data['ranks_available'] as $k=>$v):?>
							<?php $selected = ($k==$data['rank_id']) ? 'selected' : '';?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
						<?php endforeach;?>
						</select>
					</div>
					<div class="uap-form-line">
						<h2><?php esc_html_e('Email Subject', 'uap');?></h2>
						<div class="row">
							<div class="col-xs-8">
								<div class="form-group">
									<input type="text" class="form-control" value="<?php echo esc_attr($data['subject']);?>" name="subject" id="notf_subject" />
								</div>
								<p><?php esc_html_e('Enter the subject line for current notification type. Support template tags.', 'uap');?></p>
							</div>
						</div>
					</div>
					<div class="uap-form-line">
						<h2><?php esc_html_e('Message to be Sent', 'uap');?></h2>
						<div class="uap-notification-edit-editor">
							<?php wp_editor( $data['message'], 'notf_message', array('textarea_name'=>'message', 'quicktags'=>TRUE) );?>
						</div>
						<div class="uap-notification-edit-constants">
							<h4><?php esc_html_e('Template Tags', 'uap');?></h4>
						<?php
							$constants = array(	"{username}",
												"{first_name}",
												"{last_name}",
												"{user_id}",
												'{affiliate_id}',
												"{user_email}",
												"{account_page}",
												"{login_page}",
												"{blogname}",
												"{blogurl}",
												"{siteurl}",
												'{rank_id}',
												'{rank_name}',
												'{NEW_PASSWORD}',
												'{password_reset_link}',
							);
							$extra_constants = uap_get_custom_constant_fields();
							foreach ($constants as $v){
								?>
								<div class="uap-tag-wrap"><span class="uap-tag-code"><?php echo esc_html($v);?></span></div>
								<?php
							}
							?>
						</div>
						<div class="uap-notification-edit-constants">
						<?php
							echo esc_uap_content("<h4>" . esc_html__('Custom Fields Tags', 'uap') . "</h4>");
							foreach ($extra_constants as $k=>$v){
								?>
								<div class="uap-tag-wrap"><span class="uap-tag-code"><?php echo esc_html($k);?></span></div>
								<?php
							}
						?>
						</div>

						<div class="uap-clear"></div>

					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
					</div>
				</div>
			</div>
		</div>
						<!-- PUSHOVER -->
						<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
							<div class="uap-stuffbox">
							<h3 class="uap-h3"><?php esc_html_e('Pushover Notification', 'uap');?></h3>
								<div class="inside">
									<div class="iump-form-line">
										<span class="uap-labels-special"><?php esc_html_e('Pushover Notification', 'uap');?></span>
										<label class="uap_label_shiwtch uap-switch-button-margin">
											<?php $checked = (empty($data['pushover_status'])) ? '' : 'checked';?>
											<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#pushover_status');" <?php echo esc_attr($checked);?> />
											<div class="switch uap-display-inline"></div>
										</label>
										<input type="hidden" name="pushover_status" value="<?php echo isset($data['pushover_status']) ? $data['pushover_status'] : '';?>" id="pushover_status" />
									</div>

									<div class="uap-form-line">
										<label class="uap-labels-special"><?php esc_html_e('Pushover Message:', 'uap');?></label>
										<textarea name="pushover_message" class="uap-pushover_message" onBlur="uapCheckFieldLimit(1024, this);"><?php echo isset($data['pushover_message']) ? stripslashes($data['pushover_message']) : '';?></textarea>
										<div><i><?php esc_html_e('Only Plain Text and up to ', 'uap');?><strong>1024</strong><?php esc_html_e(' characters are available!', 'uap');?></i></div>
									</div>
									<div id="uap_save_changes" class="uap-submit-form">
										<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
									</div>
								</div>
							</div>
						<?php else :?>
							<input type="hidden" name="pushover_message" value=""/>
							<input type="hidden" name="pushover_status" value=""/>
						<?php endif;?>
						<!-- PUSHOVER -->

				<input type="hidden" name="status" value="1" />
				<input type="hidden" name="id" value="<?php echo esc_attr($data['id']);?>" class="uap-js-add-edit-notification-id" />
</div>
	</form>
