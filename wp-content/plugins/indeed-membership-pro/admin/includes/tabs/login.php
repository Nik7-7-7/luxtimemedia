<?php $subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'design';?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='design') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=design' );?>"><?php esc_html_e('Login Form Showcase', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab =='msg') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=msg' );?>"><?php esc_html_e('Custom Messages', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
	echo ihc_inside_dashboard_error_license();
	echo iump_is_wizard_uncompleted_but_not_skiped();
	//set default pages message
	echo ihc_check_default_pages_set();
	echo ihc_check_payment_gateways();
	echo ihc_is_curl_enable();
	do_action( "ihc_admin_dashboard_after_top_menu" );
	$login_templates = array(
							  13 => '(#13) '.esc_html__('Ultimate Member', 'ihc'),
							  12 => '(#12) '.esc_html__('MegaBox', 'ihc'),
							  11 => '(#11) '.esc_html__('Flat New Style', 'ihc'),
							  10 => '(#10) '.esc_html__('Simple BootStrap Theme', 'ihc'),
							  9 => '(#9) '.esc_html__('Radius Gradient Theme', 'ihc'),
							  8 => '(#8) '.esc_html__('Border Pink Theme', 'ihc'),
							  7 => '(#7) '.esc_html__('Double Long Theme', 'ihc'),
							  6 => '(#6) '.esc_html__('Premium Theme', 'ihc'),
							  5 => '(#5) '.esc_html__('Labels Theme', 'ihc'),
							  4 =>  '(#4) '.esc_html__('Simple Green Theme', 'ihc'),
							  3 => '(#3) '.esc_html__('BlueBox Theme', 'ihc'),
							  2 =>'(#2) '.esc_html__('Basic Theme', 'ihc'),
							  1 => '(#1) '.esc_html__('Standard Theme', 'ihc')
							  );


	if ($subtab=='design'){
		if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_login_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_login_settings_nonce']), 'ihc_admin_login_settings_nonce' ) ){
				ihc_save_update_metas('login');
		}

		wp_enqueue_script( 'wp-theme-plugin-editor' );
		wp_enqueue_style( 'wp-codemirror' );
		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );

		$meta_arr = ihc_return_meta_arr('login');
		?>
		<div class="iump-page-headline"><?php esc_html_e('Login Form Showcase', 'ihc');?></div>
			<div class="impu-shortcode-display-wrapper">
				<div class="impu-shortcode-display">
					[ihc-login-form]
				</div>
			</div>
			<form  method="post" >
				<input type="hidden" name="ihc_admin_login_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_login_settings_nonce' );?>" />
				<div class="ihc-login-showcase-sectionone1">
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Login Form Display', 'ihc');?></h3>
						<div class="inside">
						  <div class="iump-register-select-template">
						  <?php esc_html_e('Login Form Template', 'ihc');?>
							<select name="ihc_login_template" id="ihc_login_template" onChange="ihcLoginPreview();" class="ihc_profile_form_template-st">
							<?php
								foreach ($login_templates as $k=>$value){
									echo esc_ump_content('<option value="ihc-login-template-'.$k.'"'. ($meta_arr['ihc_login_template']=='ihc-login-template-'.$k ? 'selected': '') .'>'.$value.'</option>');
								}
							?>
							</select>
						 </div>
						 <div>
							<div id="ihc-preview-login"></div>
						</div>
							<div class="ihc-wrapp-submit-bttn">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
							</div>
						</div>
					</div>


				</div>
			   <div class="ihc-login-showcase-sectiontwo2">
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Additional Options', 'ihc');?></h3>
					<div class="inside">
							<div class="iump-form-line iump-no-border check-wrapper">
									<input type="checkbox" onClick="checkAndH(this, '#ihc_login_remember_me');ihcLoginPreview();" <?php if($meta_arr['ihc_login_remember_me']==1){
										 echo esc_attr('checked');
									}
									?>
									/>
									<input type="hidden" name="ihc_login_remember_me" value="<?php echo esc_attr($meta_arr['ihc_login_remember_me']);?>" id="ihc_login_remember_me"/>
									<span><?php esc_html_e('Display Remember Me Link', 'ihc');?></span>
							</div>
							<div class="iump-form-line iump-no-border check-wrapper">
									<input type="checkbox" onClick="checkAndH(this, '#ihc_login_register');ihcLoginPreview();" <?php if($meta_arr['ihc_login_register']==1){
										 echo esc_attr('checked');
									}
									?>
									/>
									<input type="hidden" name="ihc_login_register" value="<?php echo esc_attr($meta_arr['ihc_login_register']);?>" id="ihc_login_register"/>
									<span><?php esc_html_e('Display Register Link', 'ihc');?></span>
							</div>
							<div class="iump-form-line iump-no-border check-wrapper">
									<input type="checkbox" onClick="checkAndH(this, '#ihc_login_pass_lost');ihcLoginPreview();" <?php if($meta_arr['ihc_login_pass_lost']==1){
										 echo esc_attr('checked');
									}
									?>
									/>
									<span><?php esc_html_e('Display Lost your password Link', 'ihc');?></span>
									<input type="hidden" name="ihc_login_pass_lost" value="<?php echo esc_attr($meta_arr['ihc_login_pass_lost']);?>" id="ihc_login_pass_lost"/>
							</div>
							<div class="iump-form-line iump-no-border check-wrapper">
									<input type="checkbox" onClick="checkAndH(this, '#ihc_login_show_sm');ihcLoginPreview();" <?php if ($meta_arr['ihc_login_show_sm']==1){
										 echo esc_attr('checked');
									}
									?>
									/>
									<span><?php esc_html_e('Display Social Media Login Buttons', 'ihc');?></span>
									<input type="hidden" name="ihc_login_show_sm" value="<?php echo esc_attr($meta_arr['ihc_login_show_sm']);?>" id="ihc_login_show_sm"/>
							</div>
							<div class="iump-form-line iump-no-border check-wrapper">
									<input type="checkbox" onClick="checkAndH(this, '#ihc_login_show_recaptcha');ihcLoginPreview();" <?php if ($meta_arr['ihc_login_show_recaptcha']==1){
										 echo esc_attr('checked');
									}
									?>
									/>
									<span><?php esc_html_e('Display ReCaptcha Verification box', 'ihc');?></span>
									<input type="hidden" name="ihc_login_show_recaptcha" value="<?php echo esc_attr($meta_arr['ihc_login_show_recaptcha']);?>" id="ihc_login_show_recaptcha"/>
							</div>
							<div class="ihc-wrapp-submit-bttn">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
							</div>
						</div>
				  </div>
				  <div class="ihc-stuffbox iump-custom-css-box-wrapper">
						<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
						<div class="inside">
							<div  class="iump-form-line">
								<textarea id="ihc_login_custom_css" name="ihc_login_custom_css" onBlur="ihcLoginPreview();" class="ihc-dashboard-textarea-full"><?php echo stripslashes($meta_arr['ihc_login_custom_css']);?></textarea>
							</div>
							<div class="ihc-wrapp-submit-bttn">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
							</div>
						</div>
					</div>
				</div>
			</form>
		<?php
	} else {
		if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_login_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_login_settings_nonce']), 'ihc_admin_login_settings_nonce' ) ){
				ihc_save_update_metas('login-messages');
		}
		$meta_arr = ihc_return_meta_arr('login-messages');
		?>
			<form  method="post" >
				<input type="hidden" name="ihc_admin_login_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_login_settings_nonce' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Custom Messages', 'ihc');?></h3>
					<div class="inside ump-custom-messages">
						<div class="iump-form-line">
						<div class="row">
							<div class="col-xs-10">
								<h2><?php esc_html_e('Login Messages', 'ihc');?></h2>
								<div class="ump-space"></div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Successfully Message', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_login_succes" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_login_succes']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Default Message for pending members', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_login_pending" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_login_pending']);?>">
										<div class="ump-field-details"><?php esc_html_e('This notice will appear during login process if the administrator has not approved the member account.', 'ihc');?></div>
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Default message for error on social login', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_social_login_failed" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_social_login_failed']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('General Error Message', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_login_error" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_login_error']);?>">
										<div class="ump-field-details"><?php esc_html_e('This notice will appear during login process if the email address or password were typed wrongly.', 'ihc');?></div>
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Email Address not Approved', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_login_error_email_pending" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_login_error_email_pending']);?>">
										<div class="ump-field-details"><?php esc_html_e('This notice will appear during the login procedure if the member\'s email address has not been approved and he attempts to log in', 'ihc');?></div>
									</div>
								</div>

								<h2><?php esc_html_e('Reset Password Messages', 'ihc');?></h2>
								<div class="ump-space"></div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Successfully Message', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_reset_msg_pass_ok" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_reset_msg_pass_ok']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('General Error Message', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_reset_msg_pass_err" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_reset_msg_pass_err']);?>">
										<div class="ump-field-details"><?php esc_html_e('This notice will appear during login process if the email address is typed wrongly.', 'ihc');?></div>
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('ReCaptcha Error Message', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_login_error_on_captcha" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_login_error_on_captcha']);?>">
										<div class="ump-field-details"><?php esc_html_e('This notice will appear during the reset password procedure if the member does not correctly fill out the form.', 'ihc');?></div>
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Ajax Error Message', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_login_error_ajax" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_login_error_ajax']);?>">
									</div>
								</div>

							</div>
						</div>
						</div>


						<div class="ihc-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
						</div>
					</div>
				</div>
			</form>
		<?php
	}
?>
