<?php if (empty($data['metas']['id'])):?>
	<span class="ulp-js-notification-add-edit-load-notification-sample"></span>
<?php endif;?>


<form action="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=notifications');?>" method="post">

	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

<div class="ulp-wrapper">

		<div class="ulp-stuffbox">

				<h3 class="ulp-h3"><?php esc_html_e('Add/Edit Notification', 'ulp');?></h3>

				<div class="inside">

					<div class="ulp-form-line">

						<label class="ulp-labels-special"><?php esc_html_e('Action:', 'ulp');?></label>

						<select name="type" id="notf_type" onChange="ulpReturnNotification();">

							<optgroup label="<?php esc_html_e('Admin Notifications', 'ulp');?>">

								<?php foreach ($data['action_types']['admin'] as $k=>$v):?>

                		<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

								<?php endforeach;?>

							</optgroup>

							<optgroup label="<?php esc_html_e('Student Notifications', 'ulp');?>">

								<?php foreach ($data['action_types']['student'] as $k=>$v):?>

                		<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

								<?php endforeach;?>

							</optgroup>

							<optgroup label="<?php esc_html_e('Announcements Notifications', 'ulp');?>">
								<?php foreach ($data['action_types']['announcements'] as $k=>$v):?>
                		<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
								<?php endforeach;?>
							</optgroup>
							<optgroup label="<?php esc_html_e('Q&A Notifications', 'ulp');?>">
								<?php foreach ($data['action_types']['qanda'] as $k=>$v):?>
										<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
								<?php endforeach;?>
							</optgroup>
							<optgroup label="<?php esc_html_e('Other Notifications', 'ulp');?>">
								<?php foreach ($data['action_types']['others'] as $k=>$v):?>
										<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
								<?php endforeach;?>
							</optgroup>
						</select>

					</div>

					<div class="ulp-form-line">

						<label class="ulp-labels-special"><?php esc_html_e('Course:', 'ulp')?></label>

						<select name="course_id">

                <option value="-1" ><?php esc_html_e('All courses', 'ulp');?></option>

                <?php if (!empty($data['courses'])):?>

                  <?php foreach ($data['courses'] as $array):?>

                    <option value="<?php echo esc_attr($array['ID']);?>" <?php echo ($array['ID']==$data['metas']['course_id']) ? 'selected' : '';?> ><?php echo esc_ulp_content($array['post_title']);?></option>

                  <?php endforeach;?>

                <?php endif;?>

						</select>

						<div class="ulp-admin-notification-available"><?php echo esc_html__('Available only for: ', 'ulp') . $data['all_courses_list_labels'];?></div>

					</div>

					<div class="ulp-form-line">

						<label class="ulp-labels-special"><?php esc_html_e('Subject:', 'ulp');?></label>

						<input type="text" value="<?php echo esc_ulp_content($data['metas']['subject']);?>" name="subject" id="notf_subject" class="ulp-admin-notification-subject-field"/>

					</div>

					<div class="ulp-form-line">

						<label  class="ulp-labels-special ulp-vertical-align-top"><?php esc_html_e('Message:', 'ulp');?></label>

						<div class="ulp-admin-notification-editor">

							<?php

							wp_editor( $data['metas']['message'], 'notf_message', array('textarea_name'=>'message', 'quicktags'=>TRUE) );?>

						</div>

						<div class="ulp-admin-notification-constants">

						<?php

							$constants = [

                        "{username}",

												"{user_email}",

												"{first_name}",

												"{last_name}",

												'{blogname}',

												'{blogurl}',

												"{account_page}",

												"{watch_list_page}",

												"{list_courses_page}",

												"{current_date}",

                        '{course_name}',

												"{course_price}",

												"{lesson_title}",

												"{quiz_title}",

												"{quiz_grade}",

												"{amount}",

												"{currency}",

												"{user_id}",

												"{course_id}",

												"{username}",

							];

							$extra_constants = array();

							foreach ($constants as $v){

								?>

								<div><?php echo esc_html($v);?></div>

								<?php

							}

              if ($extra_constants):

							echo esc_ulp_content("<h4>" . esc_html__('Custom Fields constants', 'ulp') . "</h4>");

							foreach ($extra_constants as $k=>$v){

								?>

								<div><?php echo esc_html($k);?></div>

								<?php

							}

            endif;

						?>

							<h4><?php esc_html_e('Announcements constants', 'ulp');?></h4>
							<?php
							$extra_constants = [
								'{course_name}',
								'{course_link}',
								'{announcement_content}',
								'{announcement_title}',
								'{announcement_link}',
								'{author_name}',
							];
							foreach ($constants as $v){
								?>
								<div><?php echo esc_html($v);?></div>
								<?php
							}
							?>
							<h4><?php esc_html_e('Q&A constants', 'ulp');?></h4>
							<?php
							$extra_constants = [
								'{course_name}',
								'{course_link}',
								'{student_name}',
								'{qanda_content}',
								'{qanda_title}',
								'{qanda_link}',
								'{comment_content}',
							];
							foreach ($constants as $v){
								?>
								<div><?php echo esc_html($v);?></div>
								<?php
							}
							?>
						</div>



						<div class="ulp-clear"></div>



					<div class="ulp-submit-form">

						<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="save" class="btn btn-primary">

					</div>

				</div>

			</div>

		</div>



	<input type="hidden" name="status" value="1" />

	<input type="hidden" name="id" value="<?php echo esc_attr($data['metas']['id']);?>" />



	<!-- PUSHOVER -->

	<?php if (get_option('ulp_pushover_enable')):?>

		<div class="ulp-stuffbox">

			<h3 class="ulp-h3"><?php esc_html_e('Pushover Notification', 'ulp');?></h3>

			<div class="inside">

				<div class="ulp-form-line">

					<label class="ulp-labels-special"><?php esc_html_e('Send Pushover Notification', 'ulp');?></label>

					<label class="ulp_label_shiwtch ulp-switch-button-margin">

						<?php $checked = (empty($data['metas']['pushover_status'])) ? '' : 'checked';?>

						<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#pushover_status');" <?php echo esc_attr($checked);?> />

						<div class="switch ulp-display-inline"></div>

					</label>

					<input type="hidden" name="pushover_status" value="<?php echo (isset($data['metas']['pushover_status'])) ? $data['metas']['pushover_status'] : '';?>" id="pushover_status" />

				</div>



				<div class="ulp-form-line ulp-admin-notification-pushover-wrapper">

					<label class="ulp-labels-special"><?php esc_html_e('Pushover Message:', 'ulp');?></label>

					<textarea name="pushover_message" class="ulp-admin-notification-pushover-message" onBlur="ulpCheckFieldLimit(1024, this);"><?php echo (isset($data['metas']['pushover_message'])) ? stripslashes($data['metas']['pushover_message']) : '';?></textarea>

					<div class="ulp-admin-notification-pushover-desc"><?php esc_html_e('Only Plain Text and up to ', 'ulp');?><span>1024</span><?php esc_html_e(' characters are available!', 'ulp');?></div>

				</div>



				<div class="ulp-submit-form">

					<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="save" class="btn btn-primary">

				</div>



			</div>

		</div>

	<?php else :?>

		<input type="hidden" name="pushover_message" value=""/>

		<input type="hidden" name="pushover_status" value=""/>

	<?php endif;?>

	<!-- PUSHOVER -->



	</form>
