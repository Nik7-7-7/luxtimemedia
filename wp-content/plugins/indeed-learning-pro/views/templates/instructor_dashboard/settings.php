<h2 class="ulp-instructor-edit-top-title ulp-instructor-edit-top-settings"><?php esc_html_e('Settings', 'ulp');?></h2>
<div class="ulp-instructor-edit ulp-instructor-edit-settings">
<div class="ulp-instructor-settings-left-menu">
<ul>
    <?php foreach ($data['tabs'] as $slug => $label):?>
        <li data-target="<?php echo esc_attr($slug);?>" class="js-ulp-instructor-settings-menu-item <?php echo ($slug == 'basic_info' ? 'ulp-menu-tab-active' : '');?>"><?php echo esc_attr($label);?></li>
    <?php endforeach;?>
    <li class="js-ulp-instructor-settings-menu-item"><a href="<?php echo \Ulp_Permalinks::getForInstructor($data['user_id']);?>" target="_blank" class="js-ulp-instructor-settings-menu-item"><?php esc_html_e('Check Public Profile', 'ulp');?></a></li>
</ul>
</div>
<div class="ulp-instructor-settings-right-content">
<?php wp_enqueue_script('ulp_instructorSettingsMenu', ULP_URL . 'assets/js/instructorSettingsMenu.js', ['jquery'], '3.7' );?>

<?php if(isset( $data ['error_mess'])):?>
	<div class="ulp-profile-error"><?php echo esc_html($data ['error_mess']);?></div>
<?php elseif(isset( $data ['confirm_mess'])):?>
	<div class="ulp-profile-confirm"><?php echo esc_html($data ['confirm_mess']);?></div>
<?php endif; ?>

<form  method="post">
  <input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

	<div class="ulp-profile-basic-section">
    <div class="ulp-profile-tab-section"><?php esc_html_e( 'Basic Information', 'ulp' ); ?></div>
    <div class="ulp-instructor-edit-row">
    <div class="ulp-inst-col-8">

     	 <div class="ulp-form-section">
          <h4><?php esc_html_e('E-mail', 'ulp');?></h4>
          <input type="text" class="ulp-form-control" name="user_email" value="<?php echo esc_attr($data ['user_email']);?>" />
      	</div>
        <div class="ulp-form-section">
            <h4><?php esc_html_e('First Name', 'ulp');?></h4>
            <input type="text" class="ulp-form-control" name="first_name" value="<?php echo esc_attr($data ['first_name']);?>" />
        </div>
        <div class="ulp-form-section">
            <h4><?php esc_html_e('Last Name', 'ulp');?></h4>
            <input type="text" class="ulp-form-control" name="last_name" value="<?php echo esc_attr($data ['last_name']);?>" />
        </div>
        <div class="ulp-form-section">
            <input type="submit" class="btn btn-primary pointer" name="update_user_data" value="<?php esc_html_e('Update Info', 'ulp');?>" />
        </div>
	</div>
    </div>
    </div>

	<div class="ulp-profile-instructor-info-section ulp-display-none">
			<div class="ulp-profile-tab-section"><?php esc_html_e( 'Instructor Informations', 'ulp' ); ?></div>
      <div class="ulp-instructor-edit-row">
    	<div class="ulp-inst-col-12">
        <h4><?php esc_html_e('Public description', 'ulp');?></h4>
     	<div class="ulp-form-section">
          <label><?php esc_html_e('Biography', 'ulp');?></label>
					<textarea name="description" class="ulp-instrutor-question-post-content"><?php echo stripslashes($data['description']);?></textarea>
      </div>
			<?php
						$userSettings = [
															'ulp_instructor_show_avatar'                => esc_html__('Show avatar', 'ulp'),
										          'ulp_instructor_show_average_rating'        => esc_html__('Show average rating', 'ulp'),
										          'ulp_instructor_show_number_of_reviews'     => esc_html__('Show number of reviews', 'ulp'),
										          'ulp_instructor_show_instructor_name'       => esc_html__('Show instructor name', 'ulp'),
										          'ulp_instructor_show_biography'             => esc_html__('Show biography', 'ulp'),
										          'ulp_instructor_show_number_of_courses'     => esc_html__('Show number of courses', 'ulp'),
										          'ulp_instructor_show_number_of_students'    => esc_html__('Show number of students', 'ulp'),
						];
			?>
            <h4><?php esc_html_e('Public Details to show up', 'ulp');?></h4>
            <p><?php esc_html_e('You can decide what data will be displayed on your public Instructor page.', 'ulp');?></p>
			<?php foreach ($userSettings as $optionName => $theLabel):?>
				<div class="ulp-form-section">

					 <input type="checkbox" class="checkbox-big" <?php echo (isset($data['instructorPageSettings'][$optionName])) ? 'checked' : '';?> onClick="ulpCheckAndH(this, '<?php echo esc_attr('#' . $optionName);?>');" /> <span><?php echo esc_html($theLabel);?></span>
					 <input type="hidden" name="<?php echo esc_attr($optionName);?>" id="<?php echo esc_attr($optionName);?>" value="<?php echo esc_attr($data['instructorPageSettings'][$optionName]);?>" />
       	</div>
			<?php endforeach;?>
        <div class="ulp-form-section">
            <input type="submit" class="btn btn-primary pointer" name="update_user_info" value="<?php esc_html_e('Save Changes', 'ulp');?>" />
        </div>
	</div>
	</div>
    </div>

	<div class="ulp-profile-password-section ulp-display-none">
    		<div class="ulp-profile-tab-section"><?php esc_html_e( 'Reset Your Password', 'ulp' ); ?></div>
         <div class="ulp-instructor-edit-row">
    	<div class="ulp-inst-col-6">
        <div class="ulp-form-section">
						<label><?php esc_html_e( 'Old password', 'ulp' ); ?></label>
						<div>
								<input type="password" id="old_pass" class="ulp-form-control" name="old_pass" autocomplete="off" />
						</div>
				</div>
				<div class="ulp-form-section">
					<label><?php esc_html_e( 'New password', 'ulp' ); ?></label>
					<div>
						<input type="password" name="pass1" class="ulp-form-control" id="pass1" value="" />
					</div>
				</div>
				<div class="ulp-form-section">
					<label><?php esc_html_e( 'Confirmation password', 'ulp' ); ?></label>
					<div>
						<input name="pass2" type="password" class="ulp-form-control" id="pass2" value="" />
					</div>
				</div>
        <div class="ulp-profile-row">
            <input type="submit" class="btn btn-primary pointer" name="update_user_password" value="<?php esc_html_e('Change Password', 'ulp');?>" />
        </div>
	</div>
    </div>
    </div>

	<div class="ulp-profile-avatar-section ulp-display-none">
    	<div class="ulp-profile-tab-section"><?php esc_html_e( 'Your Profile Image', 'ulp' ); ?></div>
        <div class="ulp-profile-row">
        <?php
        wp_enqueue_script('ulp_jquery_form_module', ULP_URL . 'assets/js/jquery.form.js', array('jquery'), '3.7' );
        wp_enqueue_script('ulp-jquery.uploadfile', ULP_URL . 'assets/js/jquery.uploadfile.min.js', array('jquery'), '3.7' );
				$str = '';
				$rand = rand(1,10000);
				$str .= '<div id="ulp_fileuploader_wrapp_' . $rand . '" class="ulp-wrapp-file-upload ulp-wrapp-file-upload-instr ulp-js-file-upload-settings"
                    data-rand="'.$rand.'"
                    data-url="'.ULP_URL.'classes/public/ajax-upload.php"
                    >';
				if (isset($data['avatar']) && $data['avatar'] != ''){
					if (strpos($data['avatar'], "http")===0){
						$url = $data['avatar'];
					} else {
						$data_ing = wp_get_attachment_image_src($data['avatar']);
						if (!empty($data_ing[0])){
							$url = $data_ing[0];
						}
					}
					if (isset($url)){
						$str .= '<img src="' . $url . '" class="ulp-member-photo" /><div class="ulp-clear"></div>';
						if (strpos($data['avatar'], "http")===0){
							$str .= '<div onClick=\'ulpDeleteFileViaAjax("", '.$data['user_id'].', "#ulp_fileuploader_wrapp_' . $rand . '", "ulp_avatar", "#ulp_upload_hidden_'.$rand.'" );\' class="ulp-delete-attachment-bttn">' . esc_html__("Remove", 'ulp') . '</div>';
						} else {
							$str .= '<div onClick=\'ulpDeleteFileViaAjax(' . $data['avatar'] . ', '.$data['user_id'].', "#ulp_fileuploader_wrapp_' . $rand . '", "ulp_avatar", "#ulp_upload_hidden_'.$rand.'" );\' class="ulp-delete-attachment-bttn">' . esc_html__("Remove", 'ulp') . '</div>';
						}
						$str .= '<div class="ulp-file-upload ulp-file-upload-button ulp-display-none" >' . esc_html__("Upload", 'ulp') . '</div>';
						$str .= '<input type="hidden" value="'.$data['avatar'].'" name="ulp_avatar"  id="ulp_upload_hidden_'.$rand.'" />';
					} else {
						/// No image
						$str .= '<div class="ulp-file-upload ulp-file-upload-button ulp-display-block" ">' . esc_html__("Upload", 'ulp') . '</div>';
						$str .= '<input type="hidden" value="" name="ulp_avatar"  id="ulp_upload_hidden_'.$rand.'" />';
					}
				} else {
					$str .= '<div class="ulp-no-avatar ulp-member-photo"></div>';
					$str .= '<div class="ulp-file-upload ulp-file-upload-button ulp-display-block">' . esc_html__("Upload", 'ulp') . '</div>';
					$str .= '<input type="hidden" value="" name="ulp_avatar"  id="ulp_upload_hidden_'.$rand.'" />';
				}
				$str .= '</div>';
				echo esc_ulp_content($str);
        ?>

        <div class="ulp-profile-row">
            <input type="submit" class="btn btn-primary pointer" name="update_user_avatar" value="<?php esc_html_e('Change Avatar', 'ulp');?>" />
        </div>
		</div>
    </div>

	<div class="ulp-profile-notifications-section ulp-display-none">
			<div class="ulp-profile-tab-section"><?php esc_html_e( 'Notifications', 'ulp' ); ?></div>
             <div class="ulp-instructor-edit-row">
    	<div class="ulp-inst-col-12">
				<?php
							$instructorNoft = [
																'ulp_instructor_notifications-student_reply_on_question'          => esc_html__('Student reply on question', 'ulp'),
											          'ulp_instructor_notifications-on_student_ask_question'        		=> esc_html__('Student ask question', 'ulp'),
											          'ulp_instructor_notifications-on_student_comment_on_announcement' => esc_html__('Student comment on announcement', 'ulp'),
																'ulp_instructor_notifications-user_enroll_course'									=> esc_html__('Student enroll on course', 'ulp'),
							];
				?>
				<?php foreach ($instructorNoft as $optionName => $theLabel):?>
					<div class="ulp-form-section">

						 <input type="checkbox" class="checkbox-big" <?php echo (isset($data['instructorNotfsettings'][$optionName])) ? 'checked' : '';?> onClick="ulpCheckAndH(this, '<?php echo esc_attr('#' . $optionName);?>');" /><span><?php echo esc_ulp_content($theLabel);?></span>
						 <input type="hidden" name="<?php echo esc_attr($optionName);?>" id="<?php echo esc_attr($optionName);?>" value="<?php echo esc_attr($data['instructorNotfsettings'][$optionName]);?>" />
	       	</div>
				<?php endforeach;?>

			<div class="ulp-form-section">
					<input type="submit" class="btn btn-primary pointer" name="update_user_notf" value="<?php esc_html_e('Save Changes', 'ulp');?>" />
			</div>
	</div>
    </div>
    </div>

</form>
</div>
</div>
