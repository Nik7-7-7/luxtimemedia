<?php if (!empty($meta_arr['uap_login_custom_css'])):
					wp_register_style( 'dummy-handle', false );
					wp_enqueue_style( 'dummy-handle' );
					wp_add_inline_style( 'dummy-handle', stripslashes($meta_arr['uap_login_custom_css']) );
	?>
<?php endif;?>
<div class="uap-pass-form-wrap <?php echo esc_attr($meta_arr['uap_login_template']);?>">
	<?php
	if (!empty($data['success_message'])){
		echo esc_uap_content("<div class='uap-reset-pass-success-msg'>" . $data['success_message'] . '</div>');
	} else if (!empty($data['error_message'])){
		echo esc_uap_content("<div class='uap-wrapp-the-errors'>" . $data['error_message'] . '</div>');
	}
	?>
	<form method="post" >
		<input type="hidden" name="uap_reset_password_nonce" value="<?php echo wp_create_nonce( 'uap_reset_password_nonce' );?>" />
		<input name="uapaction" type="hidden" value="reset_pass">

	<?php switch ($meta_arr['uap_login_template']){
		 case 'uap-login-template-2': ?>
			<div class="uap-form-line-fr">
					<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address');?>" />
			</div>
			<div class="uap-form-line-fr uap-form-submit"><input type="submit" value="<?php esc_html_e('Reset Password', 'ulp');?>" name="Submit"></div>
	<?php break;?>

	<?php case 'uap-login-template-3': ?>
		<div >
			<div class="uap-form-line-fr">
				<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address');?>" />
			</div>
			<div class="uap-form-line-fr uap-form-submit">
				<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large uap-reset-password-button" />
			</div>
			<div class="uap-clear"></div>
		</div>
	<?php break;?>

		<?php case 'uap-login-template-4': ?>
			<div class="uap-form-line-fr">
				<i class="fa-uap fa-username-uap"></i><input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address', 'uap');?>" />
			</div>
			<div class="uap-form-line-fr uap-form-submit">
				<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="uap-reset-password-button" />
			</div>
		<?php break;?>

		<?php case 'uap-login-template-5': ?>
			<div class="uap-form-line-fr">
					<span class="uap-form-label-fr uap-form-label-username"><?php esc_html_e('Username or Email Address', 'uap');?></span>
				  <input type="text" value="" name="email_or_userlogin" class="uap-email-field" placeholder="" />
			</div>
			<div class="uap-form-line-fr uap-form-submit">
				<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="uap-reset-password-button" />
			</div>
		<?php break;?>

				<?php case 'uap-login-template-6': ?>
					<div class="uap-form-line-fr">
							<span class="uap-form-label-fr uap-form-label-username"><b><?php esc_html_e('Username or Email Address', 'uap');?></b></span>
						  <input type="text" value="" name="email_or_userlogin" class="uap-email-field" placeholder="" />
					</div>
					<div class="uap-temp6-row-right">
							<div class="uap-form-line-fr uap-form-submit">
								<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="uap-reset-password-button" />
							</div>
					</div>
				<?php break;?>

				<?php case 'uap-login-template-8':?>
					<div class="uap-form-line-fr">
						<i class="fa-uap fa-username-uap"></i>
						<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address', 'uap');?>" />
					</div>
					<div class="uap-form-line-fr uap-form-submit">
						<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large uap-reset-password-button" />
					</div>
				<?php break;?>

				<?php case 'uap-login-template-9':?>
					<div class="uap-form-line-fr">
						<i class="fa-uap fa-username-uap"></i>
						<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address', 'uap');?>" />
					</div>
					<div class="uap-form-line-fr uap-form-submit">
						<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large uap-reset-password-button" />
					</div>
				<?php break;?>

				<?php case 'uap-login-template-10':?>
					<div class="uap-form-line-fr">
						<i class="fa-uap fa-username-uap"></i>
						<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address', 'uap');?>" />
					</div>
					<div class="uap-form-line-fr uap-form-submit">
						<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large  uap-reset-password-button" />
					</div>
				<?php break;?>
                <?php case 'uap-login-template-11':?>
					<div class="uap-form-line-fr">
						<i class="fa-uap fa-username-uap"></i>
						<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address', 'uap');?>" />
					</div>
					<div class="uap-form-line-fr uap-form-submit">
						<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large  uap-reset-password-button" />
					</div>
				<?php break;?>
                <?php case 'uap-login-template-12':?>
					<div class="uap-form-line-fr">
						<i class="fa-uap fa-username-uap"></i>
						<input type="text" value="" name="email_or_userlogin" placeholder="<?php esc_html_e('Username or Email Address', 'uap');?>" />
					</div>
					<div class="uap-form-line-fr uap-form-submit">
						<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large uap-reset-password-button" />
					</div>
				<?php break;?>
                <?php case 'uap-login-template-13': ?>
                	<div class="uap-form-pass-additional-content">
					<?php esc_html_e('To reset your password, please enter your email address or username below', 'uap');?>
					</div>
			<div class="uap-form-line-fr">
				  <input type="text" value="" name="email_or_userlogin" class="uap-email-field" placeholder="<?php esc_html_e('Enter your Username or Email Address', 'uap');?>" />
			</div>
			<div class="uap-form-line-fr uap-form-submit">
				<input type="submit" value="<?php esc_html_e('Reset My Password', 'uap');?>" name="Submit" class="uap-reset-password-button" />
			</div>
		<?php break;?>

				<?php default:?>
					<div class="uap-form-line-fr">
						<span class="uap-form-label-fr uap-form-label-username"><?php esc_html_e('Username or Email Address', 'uap');?></span>
						<input type="text" value="" name="email_or_userlogin" />
					</div>
					<div class="uap-form-line-fr uap-form-submit">
						<input type="submit" value="<?php esc_html_e('Reset Password', 'uap');?>" name="Submit" class="button button-primary button-large" />
					</div>
				<?php break;?>

	<?php }?>

	</form>

</div>
