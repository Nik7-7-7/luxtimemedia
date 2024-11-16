<?php wp_enqueue_style( 'uap-croppic_css', UAP_URL . 'assets/css/croppic.css' );?>
<?php wp_enqueue_script( 'uap-jquery_mousewheel', UAP_URL . 'assets/js/jquery.mousewheel.min.js', array('jquery'), null );?>
<?php wp_enqueue_script( 'uap-croppic', UAP_URL . 'assets/js/croppic.js', array('jquery'), null );?>
<?php wp_enqueue_script( 'uap-image_croppic', UAP_URL . 'assets/js/image_croppic.js', array('jquery'), null );?>
<?php
wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
 ?>
<div class="uap-page-title"><span class="second-text"><?php esc_html_e('Registration Form', 'uap');?></span>
</div>
<div class="uap-wrapper">
<div class="uap-stuffbox">
	<div class="uap-shortcode-display">
		[uap-register]
	</div>
</div>

<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Registration Form Display', 'uap');?></h3>
		<div class="inside">
			<div class="uap-register-select-template">
				<?php
					$templates = array(
										'uap-register-9'=>'(#9) '.esc_html__('Radius Theme', 'uap'),
										'uap-register-14'=>'(#14) '.esc_html__('Ultimate Member', 'uap'),
										'uap-register-10'=>'(#10) '.esc_html__('BootStrap Theme', 'uap'),
										'uap-register-8'=>'(#8) '.esc_html__('Simple Border Theme', 'uap'),
										'uap-register-13'=>'(#13) '.esc_html__('Double BootStrap Theme', 'uap'),
										'uap-register-11'=>'(#11) '.esc_html__('Double Simple Border Theme', 'uap'),
										'uap-register-12'=>'(#12) '.esc_html__('Dobule Radius Theme', 'uap'),
										'uap-register-7'=>'(#7) '.esc_html__('BackBox Theme', 'uap'),
										'uap-register-6'=>'(#6) '.esc_html__('Double Strong Theme', 'uap'),
										'uap-register-5'=>'(#5) '.esc_html__('Strong Theme', 'uap'),
										'uap-register-4'=>'(#4) '.esc_html__('PlaceHolder Theme', 'uap'),
										'uap-register-3'=>'(#3) '.esc_html__('Blue Box Theme', 'uap'),
										'uap-register-2'=>'(#2) '.esc_html__('Basic Theme', 'uap'),
										'uap-register-1'=>'(#1) '.esc_html__('Standard Theme', 'uap')
					);
				?>
				<?php esc_html_e('Registration Form Template', 'uap');?>
				<select name="uap_register_template" id="uap_register_template" onChange="uapRegisterPreview();" >
					<?php
						foreach ($templates as $k=>$v){
						?>
							<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['uap_register_template']) ? 'selected' : '';?> >
								<?php echo esc_html($v);?>
							</option>
						<?php
						}
						?>
				</select>
			</div>

			<div>
				<div id="register_preview"></div>
			</div>

			<div class="uap-clear"></div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Additional Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h2><?php esc_html_e('Rank Settings', 'uap');?></h2>
				<div id="rank_assign_to_user">
					<h4><?php esc_html_e('Set the default Rank for Affiliates', 'uap');?></h4>
					<p><?php esc_html_e('This ensures that new affiliates are automatically assigned to this default Rank upon registration, streamlining the onboarding process.', 'uap');?></p>
					<select name="uap_register_new_user_rank">
						<option value="0" <?php echo ($data['metas']['uap_register_new_user_rank']==0) ? 'selected' : '';?> ><?php esc_html_e('None', 'uap');?></option>
					<?php
					$ranks = $indeed_db->get_rank_list();
					if (!empty($ranks) && is_array($ranks)){
						foreach ($ranks as $id=>$v){
						?>
							<option value="<?php echo esc_attr($id);?>" <?php echo ($data['metas']['uap_register_new_user_rank']==$id) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
						<?php
						}
					}
					?>
					</select>
				</div>
		</div>
		<div class="uap-form-line">
			<h2><?php esc_html_e('New Affiliates WordPress Role', 'uap');?></h2>
			<div><?php esc_html_e('Assign a specific WordPress role to new affiliates upon registration for streamlined user management.', 'uap');?></div>
				<select name="uap_register_new_user_role">
				<?php
					$roles = uap_get_wp_roles_list();
					if ($roles){
						foreach ($roles as $k=>$v){
							$selected = ($data['metas']['uap_register_new_user_role']==$k) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php
						}
					}
				?>
				</select>
			<p><?php esc_html_e('If the "Pending" Role is set the user cannot login until the Admin manually approves the user.', 'uap');?></p>
		</div>

		<div class="uap-form-line">
			<div><h4><?php esc_html_e('After Approval', 'uap');?></h4></div>
				<p><?php esc_html_e('After the Administrator has approved the Affiliate account, assign a specific WordPress role', 'uap');?></p>
				<select name="uap_after_approve_role">
				<?php
					$roles = uap_get_wp_roles_list();
					if ($roles){
						foreach ($roles as $k=>$v){
							$selected = ($data['metas']['uap_after_approve_role']==$k) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php
						}
					}
				?>
				</select>
		</div>

		<div class="uap-form-line">

		<h2><?php esc_html_e('Password Requirements', 'uap');?></h2>

		<div class="row">
			<div class="col-xs-4">
			<div class="input-group">
				<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Minimum Length', 'uap');?></span>
				<input type="number" value="<?php echo esc_attr($data['metas']['uap_register_pass_min_length']);?>" name="uap_register_pass_min_length" min="4" class="form-control" />
			</div>
		</div>
	</div>


	<div class="row">
		<div class="col-xs-4">
				<h4><?php esc_html_e('Password Strength Options', 'uap');?></h4>
					<select name="uap_register_pass_options"  class="form-control m-bot15">
						<option value="1" <?php echo ($data['metas']['uap_register_pass_options']==1) ? 'selected' : '';?> ><?php esc_html_e('Standard', 'uap');?></option>
						<option value="2" <?php echo ($data['metas']['uap_register_pass_options']==2) ? 'selected' : '';?> ><?php esc_html_e('Characters and digits', 'uap');?></option>
						<option value="3" <?php echo ($data['metas']['uap_register_pass_options']==3) ? 'selected' : '';?> ><?php esc_html_e('Characters, digits, minimum one uppercase letter', 'uap');?></option>
					</select>
				</div>
			</div>
			</div>
			<div class="uap-form-line">
				<h2><?php esc_html_e('Administrator Notification', 'uap');?></h2>
				<p><?php esc_html_e('Once a new user registers, administrators will receive immediate notifications at the default Email Administrator address, ensuring swift awareness of new registrations on your WordPress site. Stay informed and manage user accounts effectively.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_register_admin_notify']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_register_admin_notify');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_register_admin_notify" value="<?php echo esc_attr($data['metas']['uap_register_admin_notify']);?>" id="uap_register_admin_notify" />
			</div>

			<div class="uap-form-line">
				<div>
					<h2><?php esc_html_e('Automatic Login after Registration', 'uap');?></h2>
					<p><?php esc_html_e('Automatically log in newly registered affiliates after completing the registration process for a seamless and user-friendly experience', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_register_auto_login']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_register_auto_login');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
						<input type="hidden" name="uap_register_auto_login" value="<?php echo esc_attr($data['metas']['uap_register_auto_login']);?>" id="uap_register_auto_login" />

				</div>
			</div>
			<div class="uap-form-line">
				<h2><?php esc_html_e('Terms & Conditions Label', 'uap');?></h2>
				<p><?php esc_html_e('This label serves as a quick reference for affiliates to understand the legal obligations and guidelines associated with their account', 'uap');?></p>
				<div class="row">
					<div class="col-xs-4">
						<input type="text" name="uap_register_terms_c" class="form-control m-bot15" value="<?php echo uap_correct_text($data['metas']['uap_register_terms_c']);?>" />
					</div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

				<div class="uap-stuffbox uap-custom-css-box-wrapper">
					<h3 class="uap-h3"><?php esc_html_e('Custom CSS', 'uap');?></h3>
						<div class="uap-form-line">
							<textarea name="uap_register_custom_css" id="uap_register_custom_css" class="uap-dashboard-textarea" onBlur="uapRegisterLockerPreview();"><?php
							echo stripslashes($data['metas']['uap_register_custom_css']);
							?></textarea>
						<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

			</form>
		</div>
