<div class="ulp-student-profile-tab-the-title"><?php echo esc_ulp_content($data ['title']);?></div>
<div class="ulp-student-profile-tab-the-content"><?php echo esc_ulp_content($data ['content']);?></div>
<?php if(isset( $data ['error_mess'])):?>
	<div class="ulp-profile-error"><?php echo esc_html($data ['error_mess']);?></div>
<?php elseif(isset( $data ['confirm_mess'])):?>
	<div class="ulp-profile-confirm"><?php echo esc_html($data ['confirm_mess']);?></div>
<?php endif; ?>
    <form  method="post">
				<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />
	<div class="ulp-profile-basic-section">
    <div class="ulp-profile-tab-section"><?php esc_html_e( 'Basic Information', 'ulp' ); ?></div>
     	 <div class="ulp-profile-row">
          <label><?php esc_html_e('E-mail', 'ulp');?></label>
          <input type="text" name="user_email" value="<?php echo esc_attr($data ['user_email']);?>" />
      	</div>
        <div class="ulp-profile-row">
            <label><?php esc_html_e('First Name', 'ulp');?></label>
            <input type="text" name="first_name" value="<?php echo esc_attr($data ['first_name']);?>" />
        </div>
        <div class="ulp-profile-row">
            <label><?php esc_html_e('Last Name', 'ulp');?></label>
            <input type="text" name="last_name" value="<?php echo esc_attr($data ['last_name']);?>" />
        </div>
        <div class="ulp-profile-row">
            <input type="submit" name="update_user_data" value="<?php esc_html_e('Update Info', 'ulp');?>" />
        </div>
		</div>


		<div class="ulp-profile-password-section">
    	<div class="ulp-profile-tab-section"><?php esc_html_e( 'Reset Password', 'ulp' ); ?></div>
        <div class="ulp-profile-row">
			<label><?php esc_html_e( 'Old password', 'ulp' ); ?></label>
			<div>
				<input type="password" id="old_pass" name="old_pass" autocomplete="off" />
			</div>
		</div>
		<div class="ulp-profile-row">
			<label><?php esc_html_e( 'New password', 'ulp' ); ?></label>
			<div>
				<input type="password" name="pass1" id="pass1" value="" />
			</div>
		</div>
		<div class="ulp-profile-row">
			<label><?php esc_html_e( 'Confirmation password', 'ulp' ); ?></label>
			<div>
				<input name="pass2" type="password" id="pass2" value="" />
			</div>
		</div>

        <div class="ulp-profile-row">
            <input type="submit" name="update_user_password" value="<?php esc_html_e('Change Password', 'ulp');?>" />
        </div>
		</div>



			<?php if ( $data['avatar_field'] ):?>
					<div class="ulp-profile-avatar-section">
			    	<div class="ulp-profile-tab-section"><?php esc_html_e( 'User Avatar', 'ulp' ); ?></div>
			      <div class="ulp-profile-row">
								<?php echo esc_ulp_content($data['avatar_field']);?>
				        <div class="ulp-profile-row">
				            <input type="submit" name="update_user_avatar" value="<?php esc_html_e('Change Avatar', 'ulp');?>" />
				        </div>
						</div>
			    </div>
			<?php endif;?>

    </form>
