<form action="<?php echo esc_url($data['form_submit_url']);?>" method="post">
		<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
		
		<div class="ulp-stuffbox">
			<?php

			if( !isset($data['post_title'])) {
				$title = '';
			} else {
				$title =  $data['post_title'] . ' - ';
			}

			?>
			<h3 class="ulp-h3"><?php echo esc_ulp_content($title) . esc_html__(' Special Settings', 'ulp');?></h3>

			<?php do_action('ulp_admin_before_lesson_special_settings');?>

			<div class="inside">
				<div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Preview lesson', 'ulp');?></h2>
                        <p><?php esc_html_e('Activate this option to make this lesson available for everyone so that they can get an idea of how the course is like.', 'ulp');?></p>
                        <label class="ulp_label_shiwtch ulp-switch-button-margin">
						 <?php $checked = ($data['ulp_lesson_preview']) ? 'checked' : '';?>
						 <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_lesson_preview');" <?php echo esc_attr($checked);?> />
						 <div class="switch ulp-display-inline"></div>
					 </label>
					 <input type="hidden" name="ulp_lesson_preview" value="<?php echo esc_attr($data['ulp_lesson_preview']);?>" id="ulp_lesson_preview" />

                        </div>
                    </div>
				</div>
				<div class="ulp-line-break"></div>
                <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Assessments', 'ulp');?></h2>
                        <p><?php esc_html_e('The number of reward points a user earns by completing this lesson.', 'ulp');?></p>
                        <div class="input-group ulp-input-group-max">
                            <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Reward Points: ', 'ulp');?></span>
                            <input type="number" class="form-control" value="<?php echo esc_attr($data['ulp_post_reward_points']);?>" min="0" name="ulp_post_reward_points"/>
                        </div>

                        </div>
                    </div>
				</div>
				<div class="ulp-line-break"></div>
                <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Display Options', 'ulp');?></h2>

                        <h4><?php esc_html_e('Links', 'ulp');?></h4>
                         <p><?php esc_html_e("Show 'back to course' link: ", 'ulp');?></p>
                        <div class="input-group">
                         <label class="ulp_label_shiwtch ulp-switch-button-margin">
                             <?php $checked = ($data['ulp_lesson_show_back_to_course_link']) ? 'checked' : '';?>
                             <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_lesson_show_back_to_course_link');" <?php echo esc_attr($checked);?> />
                             <div class="switch ulp-display-inline"></div>
                         </label>
                         <input type="hidden" name="ulp_lesson_show_back_to_course_link" value="<?php echo esc_attr($data['ulp_lesson_show_back_to_course_link']);?>" id="ulp_lesson_show_back_to_course_link" />

                        </div>
                        </div>
                    </div>
				</div>
				<div class="ulp-line-break"></div>
                <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                         <h2><?php esc_html_e('Additional Settings', 'ulp');?></h2>

                         <h4><?php esc_html_e('Duration', 'ulp');?></h4>
                         <div class="input-group ulp-input-group-max">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Duration', 'ulp');?></span>
							<input type="number" class="form-control" name="ulp_lesson_duration" value="<?php echo esc_attr($data['ulp_lesson_duration']);?>" min="1" />
                    	</div>
                        <div class="ulp-input-group-max ulp-input-group-space">
                          <select name="ulp_lesson_duration_type" class="form-control m-bot15">
                              <?php $values = ulp_get_time_types();?>
                              <?php foreach ($values as $k => $v):?>
                                  <?php $selected = $data ['ulp_lesson_duration_type']==$k ? 'selected' : '';?>
                                  <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                              <?php endforeach;?>
                          </select>
						</div>
                        </div>
                    </div>
				</div>
				<div class="ulp-line-break"></div>

				<?php do_action('ulp_admin_after_lesson_special_settings');?>

			<div class="ulp-inside-item">
                        <div class="row">
                            <div class="col-xs-6">
															<div class="ulp-submit-form">

																	<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
				  											</div>

								</div>
						</div>
			</div>
			</div>

		</div>

		<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />

</form>
