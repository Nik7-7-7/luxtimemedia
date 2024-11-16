<form action="<?php echo esc_url($saveLink);?>" method="post">
  <div class="ulp-instructor-edit ulp-instructor-edit-lesson-settings">
			<h2 class="ulp-instructor-edit-top-title"><?php echo esc_html('<span class="ulp-post-title">' . $post_title . '</span>'). esc_html__(' - Special Settings', 'ulp');?></h2>
				<input type="hidden" name="ID" value="<?php echo esc_attr($postId);?>" />
					<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
                        <h3><?php esc_html_e('Preview lesson', 'ulp');?></h3>
                        <p><?php esc_html_e('Activate this option to make this lesson available for everyone so that they can get an idea of how the course is like.', 'ulp');?></p>
                        <div class="ulp-form-section">
                        <label class="ulp_label_shiwtch">
						 <?php $checked = ($data['ulp_lesson_preview']) ? 'checked' : '';?>
						 <input type="checkbox"  class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_lesson_preview');" <?php echo esc_attr( $checked);?> />
						 <div class="switch ulp-display-inline"></div>
					 </label><span><strong><?php esc_html_e('Activate/Deactivate', 'ulp');?></strong></span>
					 <input type="hidden" name="ulp_lesson_preview" value="<?php echo esc_attr($data['ulp_lesson_preview']);?>" id="ulp_lesson_preview" />
                     </div>
                    </div>
				</div>
				<div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php esc_html_e('Assessments', 'ulp');?></h3>
                        <p><?php esc_html_e('The number of reward points a user earns by completing this lesson.', 'ulp');?></p>
                        <div class="ulp-input-group">
                            <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Reward Points: ', 'ulp');?></span>
                            <input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_post_reward_points']);?>" min="0" name="ulp_post_reward_points"/>
                        </div>
                        </div>
                    </div>
				</div>
				<div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php esc_html_e('Display Options', 'ulp');?></h3>
                        <h4 class="ulp-margin-top"><?php esc_html_e('Links', 'ulp');?></h4>
                         <p><?php esc_html_e("Show 'back to course' link: ", 'ulp');?></p>
                        <div class="ulp-input-group">
                         <label>
                             <?php $checked = ($data['ulp_lesson_show_back_to_course_link']) ? 'checked' : '';?>
                             <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_lesson_show_back_to_course_link');" <?php echo esc_attr($checked);?> />
                          </label><span><strong><?php esc_html_e('Enable this Link', 'ulp');?></strong></span>
                         <input type="hidden" name="ulp_lesson_show_back_to_course_link" value="<?php echo esc_url($data['ulp_lesson_show_back_to_course_link']);?>" id="ulp_lesson_show_back_to_course_link" />
                        </div>
                        </div>
                    </div>
				</div>
				<div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                         <h3><?php esc_html_e('Additional Settings', 'ulp');?></h3>
                         <h4><?php esc_html_e('Duration', 'ulp');?></h4>
                         <div class="ulp-input-group">
							<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Duration', 'ulp');?></span>
							<input type="number" class="ulp-form-control" name="ulp_lesson_duration" value="<?php echo esc_attr($data['ulp_lesson_duration']);?>" min="1" />
                    	</div>
                        <div class="ulp-margin-top">
                          <select name="ulp_lesson_duration_type" class="ulp-form-control">
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
				<div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
							<input type="submit" name="save_special_settings" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer ulp-submit-button" />
				  			</div>
						</div>
			</div>
		</div>
		<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />
</form>
