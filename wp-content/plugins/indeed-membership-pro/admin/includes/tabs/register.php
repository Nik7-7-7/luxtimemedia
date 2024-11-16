<?php
include_once IHC_PATH . 'admin/includes/functions/register.php';
wp_enqueue_style( 'ihc_select2_style' );
wp_enqueue_script( 'ihc-select2' );
?>
<?php wp_enqueue_style( 'ihc-croppic_css', IHC_URL . 'assets/css/croppic.css', array(), 9.7 );?>
<?php wp_enqueue_script( 'ihc-jquery_mousewheel', IHC_URL . 'assets/js/jquery.mousewheel.min.js', ['jquery'], 10.1 );?>
<?php wp_enqueue_script( 'ihc-croppic', IHC_URL . 'assets/js/croppic.js', ['jquery'], 10.1 );?>
<?php wp_enqueue_script( 'ihc-image_croppic', IHC_URL . 'assets/js/image_croppic.js', ['jquery'], 10.1 );?>
<?php $subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'settings';?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='settings' ) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url . '&tab=' . $tab .'&subtab=settings');?>"><?php esc_html_e('Register Form Showcase', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='msg') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=msg' );?>"><?php esc_html_e('Custom Messages', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='custom_fields') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=custom_fields' );?>"><?php esc_html_e('Custom Fields', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

if (isset($_GET['subtab'])) {
	$subtab = sanitize_text_field($_GET['subtab']);
} else {
	$subtab = 'settings';
}

switch ($subtab){
	case 'settings':
		if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_register_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_register_settings_nonce']), 'ihc_admin_register_settings_nonce' ) ){
				ihc_save_update_metas('register');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('register');//getting metas
		wp_enqueue_script( 'wp-theme-plugin-editor' );
    wp_enqueue_style( 'wp-codemirror' );
    wp_enqueue_script( 'code-editor' );
    wp_enqueue_style( 'code-editor' );
		?>
		<div class="iump-page-headline"><?php esc_html_e('Register Form Showcase', 'ihc');?></div>
			<div class="impu-shortcode-display-wrapper">
				<div class="impu-shortcode-display">
					[ihc-register]
				</div>
			</div>
			<form  method="post">
				<input type="hidden" name="ihc_admin_register_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_register_settings_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Register Form Display', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-register-select-template">
							<?php
								$templates = array(

								'ihc-register-14'=>'(#14) '.esc_html__('Ultimate Member', 'ihc'),
								'ihc-register-10'=>'(#10) '.esc_html__('BootStrap Theme', 'ihc'),
								'ihc-register-9'=>'(#9) '.esc_html__('Radius Theme', 'ihc'),
								'ihc-register-8'=>'(#8) '.esc_html__('Simple Border Theme', 'ihc'),
								'ihc-register-13'=>'(#13) '.esc_html__('Double BootStrap Theme', 'ihc'),
								'ihc-register-12'=>'(#12) '.esc_html__('Dobule Radius Theme', 'ihc'),
								'ihc-register-11'=>'(#11) '.esc_html__('Double Simple Border Theme', 'ihc'),
								'ihc-register-7'=>'(#7) '.esc_html__('BackBox Theme', 'ihc'),
								'ihc-register-6'=>'(#6) '.esc_html__('Double Strong Theme', 'ihc'),
								'ihc-register-5'=>'(#5) '.esc_html__('Strong Theme', 'ihc'),
								'ihc-register-4'=>'(#4) '.esc_html__('PlaceHolder Theme', 'ihc'),
								'ihc-register-3'=>'(#3) '.esc_html__('Blue Box Theme', 'ihc'),
								'ihc-register-2'=>'(#2) '.esc_html__('Basic Theme', 'ihc'),
								'ihc-register-1'=>'(#1) '.esc_html__('Standard Theme', 'ihc')
								);
							?>
							<?php esc_html_e('Register Form Template', 'ihc');?>
							<select name="ihc_register_template" id="ihc_register_template" onChange="ihcRegisterLockerPreview();" class="ihc-admin-register-register-template">
							<?php
								foreach ($templates as $k=>$v){
								?>
									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_register_template']){echo esc_attr('selected');}?> >
										<?php echo esc_attr($v);?>
									</option>
								<?php
								}
							?>
							</select>
						</div>

						<div class="ihc-admin-register-preview-wrapper">
							<div id="register_preview"></div>
						</div>

						<div class="ihc-wrapp-submit-bttn">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
						</div>

					</div>
				</div>

				<div class="ihc-stuffbox ump-register-settings">
					<h3><?php esc_html_e('Additional Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">

							<h2><?php esc_html_e('Subscription Settings', 'ihc');?></h2>
							<div class="ump-space"></div>
							<h4><?php esc_html_e('Choose Subscription Type', 'ihc');?></h4>
							<p><?php esc_html_e('If Subscription Plan is selected, the user is redirected to the Subscription Plan Page to choose a Subscription. Be sure that the subscription plan page is properly set up.', 'ihc');?></p>
							<div class="row">
									<div class="col-xs-4">
										<select name="ihc_subscription_type" onChange="ihcSelectShDiv(this, '#level_assign_to_user', 'predifined_level');" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
											<option value="predifined_level" <?php if ('predifined_level'==$meta_arr['ihc_subscription_type']){ echo esc_attr('selected');}?> ><?php esc_html_e('Predifined Subscription', 'ihc');?></option>
											<option value="subscription_plan" <?php if ('subscription_plan'==$meta_arr['ihc_subscription_type']){ echo esc_attr('selected');}?> ><?php esc_html_e('Subscription Plan', 'ihc');?></option>
										</select>
									</div>
								</div>
						</div>

						<div  class="iump-form-line  ihc-admin-register-predifined-level <?php if($meta_arr['ihc_subscription_type']=='predifined_level'){ echo esc_attr('ihc-display-block');} else{ echo esc_attr('ihc-display-none');}?>" id="level_assign_to_user" >
							<h4><?php esc_html_e('Subscription assigned to new Member', 'ihc');?></h4>
							<div class="row">
									<div class="col-xs-4">
										<select name="ihc_register_new_user_level" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
											<option value="-1" <?php if($meta_arr['ihc_register_new_user_level']==-1){echo esc_attr('selected');}?> ><?php esc_html_e('None', 'ihc');?></option>
											<?php
												$levels = \Indeed\Ihc\Db\Memberships::getAll();
												if ($levels && count($levels)){
													foreach ($levels as $id=>$v){
														?>
															<option value="<?php echo esc_attr($id);?>" <?php if ($meta_arr['ihc_register_new_user_level']==$id){ echo esc_attr('selected');}?> ><?php echo esc_html($v['name']);?></option>
														<?php
													}
												}
											?>
										</select>
									</div>
								</div>
						</div>
						<div  class="iump-form-line">
								<h2><?php esc_html_e('WordPress User Role', 'ihc');?></h2>
								<p><?php esc_html_e('Predefined Wordpress Role Assign to new Registered Users.', 'ihc');?> <?php esc_html_e('If the "Pending" role is set, the user will not able to login until the admin manually approves it.', 'ihc');?></p>
								<div class="row">
										<div class="col-xs-4">
										<select name="ihc_register_new_user_role" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
											<?php
												$roles = ihc_get_wp_roles_list();
												if ($roles){
													foreach ($roles as $k=>$v){
														$selected = ($meta_arr['ihc_register_new_user_role']==$k) ? 'selected' : '';
														?>
															<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
														<?php
													}
												}
											?>
										</select>
									</div>
								</div>
							</div>
							<div  class="iump-form-line">
							<h4><?php esc_html_e('After Payment Completed', 'ihc');?></h4>
							<p><?php esc_html_e("Automatically Switch Role when the first Payment is confirmed", 'ihc');?></p>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_automatically_switch_role']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_automatically_switch_role');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>

							<input type="hidden" name="ihc_automatically_switch_role" value="<?php echo esc_attr($meta_arr['ihc_automatically_switch_role']);?>" id="ihc_automatically_switch_role" />
							<p><?php esc_html_e("Choose New WordPress Role", 'ihc');?></p>
							<div class="row">
									<div class="col-xs-4">
										<select name="ihc_automatically_new_role" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
											<?php
												if ($roles){
													unset($roles['pending_user']);
													foreach ($roles as $k=>$v){
														$selected = ($meta_arr['ihc_automatically_new_role']==$k) ? 'selected' : '';
														?>
															<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
														<?php
													}
												}
											?>
										</select>
									</div>
								</div>
						</div>

						<div class="iump-form-line">

								<h2><?php esc_html_e('Password Requirements', 'ihc');?></h2>
								<div class="row">
										<div class="col-xs-6">
										<div class="input-group">
											<span class="input-group-addon"><?php esc_html_e('Minimum Length', 'ihc');?></span>
											<input type="number" value="<?php echo esc_attr($meta_arr['ihc_register_pass_min_length']);?>" name="ihc_register_pass_min_length" min="4" class="form-control">
										</div>
									</div>
								</div>

							<div class="ihc-admin-register-password-format-wrapper">
								<div class="row">
										<div class="col-xs-6">
											<h4><?php esc_html_e('Password Strength Options', 'ihc');?></h4>
												<select name="ihc_register_pass_options" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ">
													<option value="1" <?php if ($meta_arr['ihc_register_pass_options']==1){echo esc_attr('selected');}?> ><?php esc_html_e('Standard', 'ihc');?></option>
													<option value="2" <?php if ($meta_arr['ihc_register_pass_options']==2){echo esc_attr('selected');}?> ><?php esc_html_e('Characters and digits', 'ihc');?></option>
													<option value="3" <?php if ($meta_arr['ihc_register_pass_options']==3){echo esc_attr('selected');}?> ><?php esc_html_e('Characters, digits, minimum one uppercase letter', 'ihc');?></option>
												</select>
											</div>
							</div>
							</div>
						</div>
						<div class="iump-form-line">
						<h2><?php esc_html_e('Administrator Notification', 'ihc');?></h2>
						<p><?php esc_html_e('When a new user registers, the Website Administrator is notified using the default admin email address set in the current WordPress instance. You can setup a custom Admin Email address ', 'ihc');?><a href="admin.php?page=ihc_manage&tab=general&subtab=notifications" target="_blank"><?php esc_html_e(' here', 'ihc');?></a></p>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_register_admin_notify']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_admin_notify');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="ihc_register_admin_notify" value="<?php echo esc_attr($meta_arr['ihc_register_admin_notify']);?>" id="ihc_register_admin_notify" />


						</div>

						<div class="iump-form-line">
							<h2><?php esc_html_e('Opt-In Subscription', 'ihc');?></h2>
								<p><?php esc_html_e('The new registered user Email Address is sent further to your OptIn destination. You can manage your OptIn Services ', 'ihc');?><a href="admin.php?page=ihc_manage&tab=opt_in" target="_blank"><?php esc_html_e(' here', 'ihc');?></a></p>
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_register_opt-in']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_opt-in');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" name="ihc_register_opt-in" value="<?php echo esc_attr($meta_arr['ihc_register_opt-in']);?>" id="ihc_register_opt-in" />

								<div class="ihc-admin-register-optin-wrapper">
								<h4><?php esc_html_e('Choose Opt-In Service', 'ihc');?></h4>
								<div class="row">
										<div class="col-xs-6">
																<select name="ihc_register_opt-in-type" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
                                    <?php
                                        $subscribe_types = array(
                                                                    'active_campaign' => 'Active Campaign',
                                                                    'aweber' => 'AWeber',
                                                                    'campaign_monitor' => 'CampaignMonitor',
                                                                    'constant_contact' => 'Constant Contact',
                                                                    'email_list' => esc_html__('E-mail List', 'ihc'),
                                                                    'get_response' => 'GetResponse',
                                                                    'icontact' => 'IContact',
                                                                    'madmimi' => 'Mad Mimi',
                                                                    'mailchimp' => 'MailChimp',
                                                                    'mymail' => 'Mailster (MyMail)',
                                                                    'wysija' => 'Wysija',
                                                                 );
																					$subscribe_types = apply_filters( 'ump_filter_optin_types', $subscribe_types );
                                        foreach ($subscribe_types as $k=>$v){
                                            $selected = ($meta_arr['ihc_register_opt-in-type']==$k) ? 'selected' : '';
                                            ?>
                                                <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php
                                                	echo esc_html($v);
                                                ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
															</div>
														</div>
							</div>

						</div>
						<div class="iump-form-line">
							<h2><?php esc_html_e('Double Email Verification', 'ihc');?></h2>
							<p><?php esc_html_e('Be sure that your notifications for ', 'ihc');?> <strong>Double Email Verification</strong> <?php esc_html_e(' are properly set up. Make sure to check the settings from the General Options tab. ', 'ihc');?> <a href="admin.php?page=ihc_manage&tab=general&subtab=double_email_verification" target="_blank"><?php esc_html_e(' here', 'ihc');?></a></p>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_register_double_email_verification']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_double_email_verification');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" name="ihc_register_double_email_verification" value="<?php echo esc_attr($meta_arr['ihc_register_double_email_verification']);?>" id="ihc_register_double_email_verification" />

						</div>
						<div class="iump-form-line">


							<?php if ( !ihcCheckCheckoutSetup() ):?>
								<h2><?php esc_html_e('Checkout Settings', 'ihc');?></h2>
								<p><?php esc_html_e('Show Subscription Price & Data On Register Form', 'ihc');?></p>
									<div class="ihc-admin-register-margin-bottom-space">
										<label class="iump_label_shiwtch ihc-switch-button-margin">
											<?php $checked = ($meta_arr['ihc_register_show_level_price']) ? 'checked' : '';?>
											<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_show_level_price');" <?php echo esc_attr($checked);?> />
											<div class="switch ihc-display-inline"></div>
										</label>
										<input type="hidden" name="ihc_register_show_level_price" value="<?php echo esc_attr($meta_arr['ihc_register_show_level_price']);?>" id="ihc_register_show_level_price" />
									</div>

							<?php endif;?>
							<h2><?php esc_html_e('Automatic Login after Registration', 'ihc');?></h2>
							<p>	<?php esc_html_e('Auto Logged In Users after Registration. If you choose to have an Email Verification request keep this option Off.', 'ihc');?></p>
							<div class="ihc-admin-register-margin-bottom-space">
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_register_auto_login']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_auto_login');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" name="ihc_register_auto_login" value="<?php echo esc_attr($meta_arr['ihc_register_auto_login']);?>" id="ihc_register_auto_login" />

							</div>

						</div>
						<div class="iump-form-line">
							<h2><?php esc_html_e('Sign-Up Button Label', 'ihc');?></h2>
							<div class="row">
								<div class="col-xs-6">
									<input type="text" name="ihc_register_button_label" value="<?php echo esc_attr(ihc_correct_text($meta_arr['ihc_register_button_label']));?>" class=" form-control m-bot15"/>
								</div>
							</div>
						</div>
						<div class="ihc-wrapp-submit-bttn ihc-submit-button-wrapper">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
						</div>
					</div>
				</div>

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Terms & Conditions (TOS) Label', 'ihc');?></h3>
					<div class="inside">
					  <div  class="iump-form-line">
							<p><?php esc_html_e('This label serves as a quick reference for members to understand the legal obligations and guidelines associated with their account', 'ihc');?></p>
							<div class="row">
								<div class="col-xs-6">
									<input type="text" name="ihc_register_terms_c" value="<?php echo esc_attr(ihc_correct_text($meta_arr['ihc_register_terms_c']));?>" class="ihc-admin-register-tos-input form-control m-bot15"/>
								</div>
							</div>
						</div>
						<div class="ihc-wrapp-submit-bttn ihc-submit-button-wrapper">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" onClick="" class="button button-primary button-large ihc_submit_bttn" />
						</div>
					</div>
				</div>

				<div class="ihc-stuffbox iump-custom-css-box-wrapper">
					<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
					<div class="inside">
						<div  class="iump-form-line">
							<textarea name="ihc_register_custom_css" id="ihc_register_custom_css" class="ihc-dashboard-textarea-full" onBlur="ihcRegisterLockerPreview();"><?php
							echo stripslashes($meta_arr['ihc_register_custom_css']);
							?></textarea>
						</div>
						<div class="ihc-wrapp-submit-bttn ihc-submit-button-wrapper">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
						</div>
					</div>
				</div>

			</form>
		<?php
	break;
	case 'msg':
		if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_register_messages_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_register_messages_nonce']), 'ihc_admin_register_messages_nonce' ) ){
				ihc_save_update_metas('register-msg');//save update metas
		}

		$meta_arr = ihc_return_meta_arr('register-msg');//getting metas
		?>
			<form method="post" >
				<input type="hidden" name="ihc_admin_register_messages_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_register_messages_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Custom Messages', 'ihc');?></h3>
					<div class="inside ump-custom-messages">
						<div class="iump-form-line">
						<div class="row">
							<div class="col-xs-10">
								<h2><?php esc_html_e('Error Messages', 'ihc');?></h2>
								<div class="ump-space"></div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('The Username is taken', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_username_taken_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_username_taken_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Username is invalid', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_error_username_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_error_username_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Email Address is taken', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_email_is_taken_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_email_is_taken_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Invalid Email Address', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_invalid_email_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_invalid_email_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Email Addresses did not match', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_emails_not_match_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_emails_not_match_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Member not Approved yet', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_pending_user_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_pending_user_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Empty Required Fields', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_err_req_fields" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_err_req_fields']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Required ReCaptcha', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_err_recaptcha" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_err_recaptcha']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Terms of Services checkbox', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_err_tos" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_err_tos']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Unique Field - Value already exists', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_unique_value_exists" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_unique_value_exists']);?>">
									</div>
								</div>




								<h2><?php esc_html_e('Password Field Messages', 'ihc');?></h2>
								<div class="ump-space"></div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Passwords did not match', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_pass_not_match_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_pass_not_match_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Password Only Characters and Digits', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_pass_letter_digits_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_pass_letter_digits_msg']);?>">
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Password Min Length', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_pass_min_char_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_pass_min_char_msg']);?>">
										<div class="ump-field-details"><?php esc_html_e('Where {X} will be the minimum length of password.', 'ihc');?></div>
									</div>
								</div>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Password Characters, Digits and minimum one uppercase letter', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_pass_let_dig_up_let_msg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_pass_let_dig_up_let_msg']);?>">
									</div>
								</div>

								<h2><?php esc_html_e('Success Message', 'ihc');?></h2>
								<div class="ump-labels-special">
									<div class="ump-label-text"><?php esc_html_e('Registration process has Completed', 'ihc');?></div>
									<div class="ump-input-text">
										<input type="text" name="ihc_register_success_meg" class="" value="<?php echo ihc_correct_text($meta_arr['ihc_register_success_meg']);?>">
											<div class="ump-field-details"><?php esc_html_e('This message will show up after the user has finish the registration step and no payment process has been included.', 'ihc');?></div>
									</div>
								</div>


							</div>
						</div>
					</div>

						<div class="ihc-wrapp-submit-bttn ihc-submit-button-wrapper">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" onClick="" class="button button-primary button-large ihc_submit_bttn" />
						</div>
					</div>
				</div>
			</form>
		<?php
	break;
	case 'custom_fields':
		//SAVE/UPDATE

		if ( isset( $_POST['ihc_add_edit_cf'] ) && !empty($_POST['ihc_admin_register_form_fields_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_register_form_fields_nonce']), 'ihc_admin_register_form_fields_nonce' ) ){
      ihc_save_user_field(indeed_sanitize_array_with_html($_POST));//save update user custom fields
		}
		if (isset($_POST['ihc_save_custom_field']) && !empty($_POST['ihc_admin_register_form_fields_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_register_form_fields_nonce']), 'ihc_admin_register_form_fields_nonce' ) ){
			ihc_update_reg_fields(indeed_sanitize_array_with_html($_POST));//update register fields
		}
		if (isset($_POST['ihc_update_register_fields']) && isset($_POST['id']) && !empty($_POST['ihc_admin_register_form_fields_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_register_form_fields_nonce']), 'ihc_admin_register_form_fields_nonce' ) ){
			ihc_update_register_fields(indeed_sanitize_array_with_html($_POST));//update the name, labels
		}

		//GETTING METAS
		$reg_fields = ihc_get_user_reg_fields();
		ksort($reg_fields);


		if(ihcCheckCheckoutSetup()){
			// Deprecated - remove ihc_coupon, ihc_dynamic_price, payment_select from register FORM
			$fieldKey = ihc_array_value_exists($reg_fields, 'ihc_coupon', 'name');
			if ( $fieldKey !== false ){
				unset($reg_fields[$fieldKey]);
			}
			$fieldKey = ihc_array_value_exists($reg_fields, 'ihc_dynamic_price', 'name');
			if ( $fieldKey !== false ){
				unset($reg_fields[$fieldKey]);
			}
			$fieldKey = ihc_array_value_exists($reg_fields, 'payment_select', 'name');
			if ( $fieldKey !== false ){
				unset($reg_fields[$fieldKey]);
			}
		}

		$the_levels = \Indeed\Ihc\Db\Memberships::getAll();
		if ($the_levels){
			foreach ($the_levels as $k=>$v){
				$levels_arr[$k] = $v['name'];
			}
			unset($the_levels);
			unset($k);
			unset($v);
		}

		?>
			<div class="clear"></div>
			<?php
				$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
				$ol_dL_ogs = new $class();
				if ( $ol_dL_ogs->FGCS() === '0' || $ol_dL_ogs->GCP() === true ){
					$add_new_lnk = $url . '&tab=register&subtab=add_edit_cf';
					$disabled_lnk = '';
					$global_disabled = '';
				} else {
					$add_new_lnk = '#';
					$disabled_lnk = 'ihc-register-fields-disabled';
					$global_disabled = 'disabled';
				}
			?>
			<a href="<?php echo esc_url($add_new_lnk);?>" class="indeed-add-new-like-wp ihc-admin-register-add-new-field-wrapper <?php echo esc_attr($disabled_lnk);?>"><i class="fa-ihc fa-add-ihc"></i><?php
				esc_html_e('Add New Custom Form Field', 'ihc');
			?></a>
			<div class="clear"></div>
			<form  method="post">
				<input type="hidden" name="ihc_admin_register_form_fields_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_register_form_fields_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Custom Fields', 'ihc');?></h3>
					<div class="inside">
						<div class="ihc-admin-register-margin-bottom-space">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_custom_field" class="btn bg-transparent ihc-js-notifications-fire-notification-test ihc-remove-group-button" />
						</div>
								<div class="ihc-sortable-table-wrapp">

									<table class="wp-list-table widefat fixed tags ihc-custom-fields-wrapper" id='ihc-register-fields-table'>
										    <thead>
												<tr>
													<th class="manage-column"><?php esc_html_e('Slug', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('Label', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('Field Type', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Admin', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Registration Form', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Profile Form', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Modal', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e("Targeting Subscriptions", 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('Required', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('WP Native', 'ihc');?></th>
													<th class="manage-column ihc-admin-register-fields-table-small-col"><?php esc_html_e('Edit', 'ihc');?></th>
													<th class="manage-column ihc-admin-register-fields-table-small-col"><?php esc_html_e('Remove', 'ihc');?></th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="manage-column"><?php esc_html_e('Slug', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('Label', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('Field Type', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Admin', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Registration Form', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Profile Form', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('On Modal', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e("Targeting Subscriptions", 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('Required', 'ihc');?></th>
													<th class="manage-column"><?php esc_html_e('WP Native', 'ihc');?></th>
													<th class="manage-column ihc-admin-register-fields-table-small-col"><?php esc_html_e('Edit', 'ihc');?></th>
													<th class="manage-column ihc-admin-register-fields-table-small-col"><?php esc_html_e('Remove', 'ihc');?></th>
												</tr>
											</tfoot>
											<tbody>
									<?php
									foreach ($reg_fields as $k=>$v){

										switch ($v['name']){
											case 'ihc_social_media':
												$tr_extra_class = "ihc-social-media-tr-dashboard";
												break;
											case 'ihc_coupon':
												$tr_extra_class = "ihc-coupon-tr-dashboard";
												break;
											case 'ihc_dynamic_price':
												$tr_extra_class = "ihc-dynamic-price-tr-dashboard";
												break;
											case 'ihc_avatar':
												$tr_extra_class = "ihc-avatar-tr-dashboard";
												break;
											case 'payment_select':
												$tr_extra_class = "ihc-payment-tr-dashboard";
												break;
											case 'ihc_invitation_code_field':
												$tr_extra_class = "ihc-invitation-code-tr-dashboard";
												break;
											default:
												$tr_extra_class = '';
												break;
										}
										?>
										<tr class="ihc-custom-fields-row <?php echo esc_attr($tr_extra_class);?>" id="tr_<?php echo esc_attr($k);?>">
											<td>
												<?php echo esc_html($v['name']);?>
												<input type="hidden" value="<?php echo esc_attr($k);?>" name="ihc-order-<?php echo esc_attr($k);?>" class="ihc-order" />
											</td>
											<td class="ihc-custom-fields-label"><?php
													if ($v['native_wp']){
														esc_html_e($v['label'], 'ihc');
													} else {
														echo stripslashes($v['label']);
													}
												?>
											</td>
											<td><?php echo esc_attr($v['type']);?></td>
											<td>
												<?php

													$notAvailableForAdminSection = [
																														'ihc_social_media',
																														'payment_select',
																														'ihc_invitation_code_field',
																														'ihc_dynamic_price',
																														'confirm_email',
																														'ihc_coupon',
																														'recaptcha',
																														'tos',
																														'pass2'
													];
													if( in_array( $v['name'], $notAvailableForAdminSection ) ){
														echo esc_html('-');
													} else if ($v['display_admin']==2){
														esc_html_e('Always', 'ihc');
													} else {
														?>
														<span class="check-wrapper">
															<input type="checkbox" onClick="iumpCheckAndH(this, '#ihc-field-display-admin<?php echo esc_attr($k);?>');ihcReq(this, <?php echo esc_attr($k);?>);" <?php if($v['display_admin']){ echo esc_attr('checked');}?> <?php echo esc_attr($global_disabled);?> />
														</span>
														<input type="hidden" value="<?php echo esc_attr($v['display_admin']);?>" name="ihc-field-display-admin<?php echo esc_attr($k);?>" id="ihc-field-display-admin<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
											</td>
											<td>
												<?php
													$can_be_editable = FALSE;
													if (ihc_is_magic_feat_active('register_lite') && ($v['name']=='pass1')){
														$can_be_editable = TRUE;
													}
													if ( ihc_is_magic_feat_active('register_lite') && $v['name']=='user_login' ){
														$can_be_editable = TRUE;
													}
													if ($v['display_public_reg']==2 && !$can_be_editable){
														esc_html_e('Always', 'ihc');
													} else {
														?>
														<span class="check-wrapper">
															<input type="checkbox" onClick="iumpCheckAndH(this, '#ihc-field-display-public-reg<?php echo esc_attr($k);?>');ihcReq(this, <?php echo esc_attr($k);?>);" <?php if($v['display_public_reg']){ echo esc_attr('checked');}?> <?php echo esc_attr($global_disabled);?> />
														</span>
														<input type="hidden" value="<?php echo esc_attr($v['display_public_reg']);?>" name="ihc-field-display-public-reg<?php echo esc_attr($k);?>" id="ihc-field-display-public-reg<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
											</td>
											<td>
												<?php
													if ($v['display_public_ap']==2){
														esc_html_e('Always', 'ihc');
													} else if($v['name']=='ihc_social_media' || $v['name']=='ihc_invitation_code_field' || $v['name']=='ihc_dynamic_price'){
														echo esc_html('-');
													} else {
														?>
														<span class="check-wrapper">
															<input type="checkbox" onClick="iumpCheckAndH(this, '#ihc-field-display-public-ap<?php echo esc_attr($k);?>');ihcReq(this, <?php echo esc_attr($k);?>);" <?php if($v['display_public_ap']){ echo esc_attr('checked');}?> <?php echo esc_attr($global_disabled);?> />
														</span>
														<input type="hidden" value="<?php echo esc_attr($v['display_public_ap']);?>" name="ihc-field-display-public-ap<?php echo esc_attr($k);?>" id="ihc-field-display-public-ap<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
											</td>
											<td>
												<?php
														if ( !isset($v['display_on_modal']) ){
																$v['display_on_modal'] = 0;
														}
														if ($v['display_on_modal']==2){
																esc_html_e('Always', 'ihc');
														} else if($v['name']=='ihc_avatar' || $v['name']=='ihc_dynamic_price'){
																echo esc_html('-');
														} else {
														?>
														<span class="check-wrapper">
																<input type="checkbox" onClick="iumpCheckAndH(this, '#<?php echo esc_attr('ihc-field-display-on-modal' . $k);?>');ihcReq(this, <?php echo esc_attr($k);?>);" <?php if($v['display_on_modal']){ echo esc_attr('checked');}?> <?php echo esc_attr($global_disabled);?> />
															</span>
																<input type="hidden" value="<?php echo esc_attr($v['display_on_modal']);?>" name="<?php echo esc_attr('ihc-field-display-on-modal' . $k);?>" id="<?php echo esc_attr('ihc-field-display-on-modal' . $k);?>" />
														<?php
														}
														?>
											</td>
											<td><?php
												if (isset($v['target_levels']) && $v['target_levels']!=''){
													$target_levels = explode(',', $v['target_levels']);
													foreach ($target_levels as $target_value){
														if ($target_value==-1){
															echo esc_ump_content('<div class="ump-custom-fields-list subcr-type-list">' . esc_html__('No Membership selected', 'ihc') . '</div>');
														} else if (isset($levels_arr[$target_value])){
															echo esc_ump_content('<div class="ump-custom-fields-list subcr-type-list">' . esc_attr($levels_arr[$target_value]) . '</div>');
														} else {
															$deleted_level = TRUE;
														}
													}
													if (!empty($deleted_level)){
														esc_html_e("Removed Membership", 'ihc');
														unset($deleted_level);
													}
													unset($target_levels);
												} else {
													echo esc_ump_content('<div class="ump-custom-fields-list subcr-type-list">' . esc_html__('All', 'ihc') . '</div>');
												}
											?></td>
											<td>
												<?php
													if ($v['display_public_reg']==2){
														esc_html_e('Always', 'ihc');
													} else if ($v['req']==2){
														esc_html_e('Required When Selected', 'ihc');
													} else if ($v['name']=='ihc_social_media' || $v['type']=='plain_text'){
														echo esc_html('-');
													} else {
														?>
														<span class="check-wrapper">
															<input type="checkbox" onClick="iumpCheckAndH(this, '#ihc-require-<?php echo esc_attr($k);?>');" <?php if ($v['req']){ echo esc_attr('checked');}?> id="req-check-<?php echo esc_attr($k);?>" <?php echo esc_attr($global_disabled);?>/>
														</span>
														<input type="hidden" value="<?php echo esc_attr($v['req']);?>" name="ihc-require-<?php echo esc_attr($k);?>" id="ihc-require-<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
											</td>
											<td>
												<?php
													if ($v['native_wp']){
														esc_html_e('Yes', 'ihc');
													} else {
														esc_html_e('No', 'ihc');
													}
												?>
											</td>
											<td class="ihc-custom-fields-edit">
												<?php
													$no_edit = array('ihc_social_media');
													if($v['native_wp'] || in_array($v['name'], $no_edit) ){
														echo esc_html('-');
													} else {
														?>
														<a href="<?php echo esc_url( $url . '&tab=register&subtab=add_edit_cf&id=' . $k );?>">
															<i class="fa-ihc ihc-icon-edit-e"></i>
														</a>
														<?php
													}
												?>
											</td>
											<td class="ihc-custom-fields-remove">
												<?php
													$no_delete_fields = array(
																											'ihc_avatar',
																											'recaptcha',
																											'ihc_coupon',
																											'tos',
																											'ihc_social_media',
																											'confirm_email',
																											'payment_select',
																											'ihc_invitation_code_field',
																											'ihc_state',
																											'ihc_country',
																											'ihc_dynamic_price',
																											'ihc_optin_accept',
																											'ihc_memberlist_accept',
													);
													if ($v['native_wp'] || in_array($v['name'], $no_delete_fields)){
														echo esc_html('-');
													} else {
														?>
														<div class="ihc-js-delete-register-field ihc-display-inline" data-id="<?php echo esc_attr($k);?>" ><i class="fa-ihc ihc-icon-remove-e"></i></div>
														<?php
													}
												?>
											</td>
										</tr>
										<?php
										}
									?>
										</tbody>
									</table>
								</div>
						<div class="ihc-submit-button-wrapper">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_custom_field" class="btn bg-transparent ihc-js-notifications-fire-notification-test ihc-remove-group-button" />
						</div>
					</div>
				</div>
			</form>

		<?php
	break;
	case 'add_edit_cf':
		$meta = get_option('ihc_user_fields');
		if (isset($_REQUEST['id']) && isset($meta[$_REQUEST['id']]) && count($meta[$_REQUEST['id']])){
			$meta_arr = $meta[ sanitize_text_field($_REQUEST['id'] ) ];
			$bttn = 'ihc_update_register_fields';
		} else {
			$meta_arr = array(  'name' => '',
							    'label' => '',
							    'type' => 'text',
								'values' => '',
								'sublabel' => '',
								'class' => '',
					);
			$bttn = 'ihc_add_edit_cf';
		}
		$disabled = '';
		$disabledTypes = array(
				'confirm_email',
				'tos',
				'recaptcha',
				'ihc_avatar',
				'ihc_coupon',
				'payment_select',
				'ihc_country',
				'ihc_invitation_code_field',
				'ihc_dynamic_price',
				'ihc_optin_accept',
				'ihc_memberlist_accept'
		);
		foreach ( $disabledTypes as $disabledType ){
				if ( $meta_arr['name'] == $disabledType ){
						$disabled = 'disabled';
						break;
				}
		}
		/*
		if ($meta_arr['name']=='confirm_email' || $meta_arr['name']=='tos' || $meta_arr['name']=='recaptcha'
			|| $meta_arr['name']=='ihc_avatar' || $meta_arr['name']=='ihc_coupon' || $meta_arr['name']=='payment_select' || $meta_arr['name']=='ihc_country' || $meta_arr['name']=='ihc_invitation_code_field' || $meta_arr['name']=='ihc_dynamic_price'){
			$disabled = 'disabled';
		}
		*/
		?>
			<form method="post" action="<?php echo esc_url($url . '&tab=register&subtab=custom_fields' );?>">
				<input type="hidden" name="ihc_admin_register_form_fields_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_register_form_fields_nonce' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Custom Field Settings', 'ihc');?></h3>
					<div class="inside">
						<?php
							if (isset($_REQUEST['id'])){
								?>
								<input type="hidden" name="id" value="<?php echo sanitize_text_field($_REQUEST['id']);?>" />
								<?php
							}
						?>
						<div class="iump-form-line">
							<label class="iump-labels"><?php esc_html_e('Field Unique Slug:', 'ihc');?> </label>
							<input type="text" name="name" value="<?php echo esc_attr($meta_arr['name']);?>" <?php echo esc_attr($disabled);?> class="ihc-custom-field-slug"/>
							<p class="ihc-slug-notification"><i><?php esc_html_e("must be unique and based on lowercase characters only without special symbols or empty spaces", 'ihc');?></i></p>
                            <?php
							if($disabled != ''){ ?>
									<input type="hidden" name="name" value="<?php echo esc_attr($meta_arr['name']);?>"/>
							<?php }
							?>
						</div>
						<div class="iump-form-line  iump-no-border">
							<label class="iump-labels"><strong><?php esc_html_e('Field Type:', 'ihc');?></strong></label>
							<select id="ihc_new_field-type" <?php if ($disabled){ echo esc_attr('disabled'); } else{ echo esc_ump_content('name="type"');}?> onChange="ihcRegisterFields(this.value);">
								<?php
									$field_types = array('text'=>'Text',
															'textarea'=>'Textarea',
															'date'=>'Date Picker',
															'number'=>'Number',
															'select'=>'Select',
															'multi_select' => 'Multiselect Box',
															'checkbox'=>'Checkbox',
															'radio'=>'Radio',
															'file' => 'File Upload',
															'plain_text' => 'HTML Field',
															'conditional_text' => 'Verification Code',
															'unique_value_text' => 'Text - Unique Value',
									);
									foreach ($field_types as $k=>$v){
										$selected = ($meta_arr['type']==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
										<?php
									}
								?>
							</select>
						</div>
						<?php
							$display = 'ihc-display-none';
							if ($meta_arr['type']=='select' || $meta_arr['type']=='checkbox' || $meta_arr['type']=='radio' || $meta_arr['type']=='multi_select'){
								if ($meta_arr['name']!='tos')
								$display = 'ihc-display-block';
							}
						?>
						<div class="iump-form-line <?php echo esc_attr($display);?>" id="ihc-register-field-values">
							<label class="iump-labels ihc-vertical-align-top"><?php esc_html_e('Values:', 'ihc');?></label>
							<div class="ihc-register-the-values ihc-display-inline">
								<?php
									if (isset($meta_arr['values']) && $meta_arr['values']){
										foreach ($meta_arr['values'] as $value){
											?>
											<div class="ihc-custom-field-item-wrapp ihc-display-block">
												<input type="text" name="values[]" value="<?php echo ihc_correct_text($value);?>"/>
												<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-register-delete-parent" ></i>
												<i class="fa-ihc fa-arrows-ihc"></i>
											</div>
											<?php
										}
									} else {
										?>
										<div class="ihc-custom-field-item-wrapp ihc-display-block">
											<input type="text" name="values[]" value=""/>
											<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-register-delete-parent" ></i>
											<i class="fa-ihc fa-arrows-ihc"></i>
										</div>
										<?php
									}
								?>
							</div>
							<div class="ihc-clear"></div>
							<div class="ihc-admin-register-add-new-value" onclick="ihcAddNewRegisterFieldValue();">
							<?php esc_html_e('Add New Value', 'ihc');?>
							</div>
						</div>

						<div id="ihc-register-field-conditional-text" class="<?php if ($meta_arr['type']=='conditional_text'){ echo esc_attr('ihc-display-block'); }else{ echo esc_attr('ihc-display-none');}?>">
							<div class="iump-form-line">
								<label class="iump-labels ihc-vertical-align-top"><?php esc_html_e('Right Answer:', 'ihc');?> </label>
								<input type="text" value="<?php echo ihc_correct_text((isset($meta_arr['conditional_text'])) ? $meta_arr['conditional_text'] : '');?>" name="conditional_text" />
							</div>
							<div class="iump-form-line">
								<label class="iump-labels ihc-vertical-align-top"><?php esc_html_e('Error Message:', 'ihc');?> </label>
								<textarea name="error_message" class="ihc-admin-register-min-width"><?php echo ihc_correct_text((isset($meta_arr['error_message'])) ? $meta_arr['error_message'] : '');?></textarea>
							</div>
						</div>

						<div id="ihc-register-field-plain-text" class="iump-no-border <?php if ($meta_arr['type']=='plain_text'){ echo esc_attr('ihc-display-block');} else{ echo esc_attr('ihc-display-none');}?>">
							<label class="iump-labels ihc-vertical-align-top"><?php esc_html_e('Content:', 'ihc');?> </label>
							<div class="ihc-admin-register-editor-wrapper">
							<?php
							$settings = array(
									'media_buttons' => true,
									'textarea_name'=>'plain_text_value',
									'textarea_rows' => 5,
									'tinymce' => true,
									'quicktags' => true,
									'teeny' => true,
							);
							wp_editor(ihc_correct_text((isset($meta_arr['plain_text_value'])) ? $meta_arr['plain_text_value'] : ''), 'plain_text_value', $settings);
							?>
							</div>
						</div>

						<div class="iump-special-line">
							<?php
								$posible_values[-1] = esc_html__('No Membership selected', 'ihc');
								$levels = \Indeed\Ihc\Db\Memberships::getAll();
								if ($levels){
									foreach ($levels as $id=>$level){
										$posible_values[$id] = $level['name'];
									}
								}
								if (!isset($meta_arr['target_levels'])){
									$meta_arr['target_levels'] = '';
								}
							?>
							<h2><?php esc_html_e('Targeting Memberships', 'ihc');?></h2>
							<p><?php esc_html_e("Choose to show this field inside Register Form only when a certain Membership have been selected before the Register form is displayed", 'ihc');?></p>
							<label class="iump-labels"><?php esc_html_e('to Show Up Only for Membership:', 'ihc');?></label>
							<select name="" class="iump-form-select ihc-admin-register-min-width" onchange="ihcWriteTagValueCfl(this, '#indeed-target-levels-cf', '#ihc_select_levels_cf_view', 'ihc-level-select-v-');">
								<option value="-2" selected="">...</option>
								<?php
								foreach ($posible_values as $k=>$v){
									?>
									<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
									<?php
								}
								?>
							</select>
							<input type="hidden" name="target_levels" id="indeed-target-levels-cf" value="<?php echo esc_attr($meta_arr['target_levels']);?>" />
							<div id="ihc_select_levels_cf_view">
								<?php
									if ($meta_arr['target_levels']!=''){
										$target_levels = explode(',', $meta_arr['target_levels']);
										$str = '';
										foreach ($target_levels as $v){
											$v = (int)$v;
											$temp_class = 'ihc-tag-item';
											if ($v>-1){
												if (Ihc_Db::does_level_exists($v)){
													$temp_data = ihc_get_level_by_id($v);
												} else {
													$temp_data['name'] = esc_html__('Removed Membership', 'ihc');
													$temp_class .= ' ihc-expired-level';
												}
											} else {
												$temp_data['name'] = esc_html__('No Membership selected', 'ihc');
											}
											if ($temp_data){
												$str .= '<div id="ihc-level-select-v-'.esc_attr($v).'" class="'.esc_attr($temp_class).'">'.esc_html($temp_data['name'])
												. '<div class="ihc-remove-tag" onclick="ihcremoveTag('.esc_attr($v).', \'#ihc-level-select-v-\', \'#indeed-target-levels-cf\');" title="'.esc_attr('Removing tag', 'ihc').'">'
												. 'x</div>'
												. '</div>';
											}
										}
										echo esc_ump_content($str);
									}
								?>
							</div>
						</div>

						<div class="iump-form-line iump-no-border">
						<h2><?php esc_html_e("Field Labels", 'ihc');?></h2>
						<p><?php esc_html_e("Change the field's main label or the sub-text displayed below the field on certain Templates. Add specific CSS class for further customization and custom style", 'ihc');?></p>
					</div>
					<div class="iump-form-line">
							<label class="iump-labels"><?php esc_html_e('Field Label:', 'ihc');?> </label>
							<input type="text" name="label" value="<?php echo ihc_correct_text($meta_arr['label']);?>"/>
						</div>
						<div class="iump-form-line">
							<label class="iump-labels"><?php esc_html_e('Sub-Text:', 'ihc');?></label>
							<input type="text" value="<?php echo ihc_correct_text((isset($meta_arr['sublabel'])) ? $meta_arr['sublabel'] : '');?>" name="sublabel" class="ihc-admin-register-sublabel-input" />
						</div>
						<?php if (empty($meta_arr['class'])) $meta_arr['class'] = '';?>
						<div class="iump-form-line iump-no-border">
							<label class="iump-labels"><?php esc_html_e('Box Class:', 'ihc');?> </label> <input type="text" name="class" value="<?php echo ihc_correct_text($meta_arr['class']);?>"/>
						</div>
						<?php
							if ($meta_arr['name']=='payment_select'){
								?>
								<div class="iump-form-line iump-no-border">
									<h2><?php esc_html_e("Template", 'ihc');?></h2>
									<p>Payment selection showcase</p>
									<select name="theme"><?php
										if (empty($meta_arr['theme'])) $meta_arr['theme'] = 'ihc-select-payment-theme-1';
										foreach (array('ihc-select-payment-theme-1' => 'RadioBox', 'ihc-select-payment-theme-2' => 'Logos', 'ihc-select-payment-theme-3' => 'DropDown') as $k=>$v){
											?>
											<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['theme']) echo esc_attr('selected');?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?></select>
								</div>
								<?php
							}
						?>

						<?php
							if (!in_array($meta_arr['name'], array('payment_select', 'ihc_social_media', 'tos', 'ihc_avatar', 'recaptcha', 'ihc_optin_accept', 'ihc_memberlist_accept'))){
						?>
						<div class="iump-special-line">
							<h2><?php esc_html_e("Conditional Logic", 'ihc');?></h2>
							<p><?php esc_html_e("Choose if you wish to Show Up or to Hide this field only on certain conditions, such when other field value contains a specific value", 'ihc');?></p>
							<div class="iump-form-line">
								<label class="iump-labels"><?php esc_html_e('Show:', 'ihc');?></label>
								<select name="conditional_logic_show">
									<option <?php if (isset($meta_arr['conditional_logic_show']) && $meta_arr['conditional_logic_show']=='yes'){ echo esc_attr('selected');}?> value="yes"><?php esc_html_e("Yes", 'ihc');?></option>
									<option <?php if (isset($meta_arr['conditional_logic_show']) && $meta_arr['conditional_logic_show']=='no'){ echo esc_attr('selected');}?> value="no"><?php esc_html_e("No", 'ihc');?></option>
								</select>
							</div>
							<div class="iump-form-line">
								<div class="ihc-display-inline">
									<label class="iump-labels"><?php esc_html_e('If Field:', 'ihc');?></label>
									<select name="conditional_logic_corresp_field">
									<?php
										if (empty($meta_arr['conditional_logic_corresp_field'])){
											$meta_arr['conditional_logic_corresp_field'] = -1;
										}
										$register_fields = array('-1'=>'...') + ihc_get_public_register_fields($meta_arr['name']);
										foreach ($register_fields as $k => $v){
											$selected = ($meta_arr['conditional_logic_corresp_field']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
											<?php
										}
									?>
									</select>
								</div>
								<div class="ihc-admin-register-condition-field-sect1">
									<select name="conditional_logic_cond_type">
										<option <?php if (isset($meta_arr['conditional_logic_cond_type']) && $meta_arr['conditional_logic_cond_type']=='has'){ echo esc_attr('selected');}?> value="has"><?php esc_html_e("Is", 'ihc');?></option>
										<option <?php if (isset($meta_arr['conditional_logic_cond_type']) && $meta_arr['conditional_logic_cond_type']=='contain'){ echo esc_attr('selected');}?> value="contain"><?php esc_html_e("Contains", 'ihc');?></option>
									</select>
								</div>
								<div class="ihc-admin-register-condition-field-sect2">
									<label class="ihc-admin-register-condition-field-sept"> : </label>
									<input type="text" name="conditional_logic_corresp_field_value" value="<?php echo ihc_correct_text((isset($meta_arr['conditional_logic_corresp_field_value'])) ? $meta_arr['conditional_logic_corresp_field_value'] : '');?>" class="ihc-admin-register-condition-field-input" />
								</div>
							</div>
						</div>
						<?php } ?>

						<?php if ( $meta_arr['name'] === 'ihc_optin_accept' ){ ?>
							<div class="iump-special-line">
								<h2><?php esc_html_e("Input Checkbox checked by default", 'ihc');?></h2>
								<?php $checked = ($meta_arr['ihc_optin_accept_checked']) ? 'checked' : '';?>
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_optin_accept_checked');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" name="ihc_optin_accept_checked" id="ihc_optin_accept_checked" value="<?php echo esc_attr($meta_arr['ihc_optin_accept_checked']);?>" />
							</div>
						<?php }?>

						<?php if ( $meta_arr['name'] === 'ihc_memberlist_accept' ){ ?>
							<div class="iump-special-line">
								<h2><?php esc_html_e("Input Checkbox checked by default", 'ihc');?></h2>
								<?php $checked = ($meta_arr['ihc_memberlist_accept_checked']) ? 'checked' : '';?>
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_memberlist_accept_checked');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" name="ihc_memberlist_accept_checked" id="ihc_memberlist_accept_checked" value="<?php echo esc_attr($meta_arr['ihc_memberlist_accept_checked']);?>" />
							</div>
						<?php }?>

						<div class="ihc-wrapp-submit-bttn ihc-submit-button-wrapper">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="<?php echo esc_attr($bttn);?>" class="button button-primary button-large ihc_submit_bttn" />
						</div>

				</div>
			</form>

		<?php
	break;
}
?>

<?php esc_html__( 'Username', 'ihc');?>
<?php esc_html__( 'Email', 'ihc');?>
<?php esc_html__( 'Confirm Email', 'ihc');?>
<?php esc_html__( 'First Name', 'ihc');?>
<?php esc_html__( 'Last Name', 'ihc');?>
<?php esc_html__( 'Website', 'ihc');?>
<?php esc_html__( 'Password', 'ihc');?>
<?php esc_html__( 'Confirm Password', 'ihc');?>
<?php esc_html__( 'Biographical Info', 'ihc');?>
<?php esc_html__( 'Phone', 'ihc');?>
<?php esc_html__( 'Address 1', 'ihc');?>
<?php esc_html__( 'Address 2', 'ihc');?>
<?php esc_html__( 'Zip', 'ihc');?>
<?php esc_html__( 'City', 'ihc');?>
<?php esc_html__( 'State', 'ihc');?>
<?php esc_html__( 'Country', 'ihc');?>
<?php esc_html__( 'Avatar', 'ihc');?>
<?php esc_html__( 'Accept', 'ihc');?>
<?php esc_html__( 'Invitation Code', 'ihc');?>
