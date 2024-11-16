<form action="<?php echo esc_url($saveLink);?>" method="post">
	<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

	<div class="ulp-instructor-edit ulp-instructor-edit-quiz-settings">
		<h2 class="ulp-instructor-edit-top-title"><?php echo esc_ulp_content('<span class="ulp-post-title">'.$post_title . '</span>'). esc_html__(' - Special Settings', 'ulp');?></h2>
			<input type="hidden" name="ID" value="<?php echo esc_attr($postId);?>" />
			<div class="ulp-instructor-edit-row">
                   <div class="ulp-inst-col-6">
                        <h3><?php _e('Quiz Timeout', 'ulp');?></h3>
                        <p><?php _e('After a set amount of minutes, the quiz will end.', 'ulp');?></p>
                        <div class="ulp-form-section">
                         <div class="ulp-input-group">
                         	<span class="ulp-input-group-addon" id="basic-addon1"><?php _e('Time', 'ulp');?></span>
                         	<input type="number" class="ulp-form-control" name="quiz_time" min="1" value="<?php echo esc_attr($data['quiz_time']);?>" />
                            <div class="ulp-input-group-addon"> <?php _e('minutes', 'ulp');?></div>
                         </div>
                        </div>
                    </div>
             </div>
             <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php _e('Assessments', 'ulp');?></h3>
                        <p><?php _e('Determine if the grade will be percentage or point based. Here you can also establish the passing grade for the quiz and the number of reward points given to the user after passing the quiz', 'ulp');?></p>
                        <h4 class="ulp-margin-top"><?php _e('Grade based on', 'ulp')?></h4>
                        <div class="ulp-input-group">
                        	<select name="ulp_quiz_grade_type"  class="ulp-form-control m-bot15" onChange="ulpShowSelectorIf('#ulp_point_description', this.value, 'point');">
								<?php
                                    $values = array(
                                                        'percentage' => esc_html__('Percentage', 'ulp'),
                                                        'point' => esc_html__('Points', 'ulp')
                                    );
                                ?>
                                <?php foreach ($values as $k => $v):?>
                                    <?php $selected = ($data['ulp_quiz_grade_type']==$k) ? 'selected' : '';?>
                                    <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                                <?php endforeach;?>
                            </select>
														<div id="ulp_point_description"><?php
																_e('In order to give grade based on points, you must set a number of points for each of quiz questions.', 'ulp');
														?></div>
                        </div>

                        <h4><?php _e('Passing Grade', 'ulp')?></h4>
						<div class="ulp-input-group">
							<input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_quiz_grade_value']);?>" name="ulp_quiz_grade_value" min="0" />
                        </div>

						<h4 class="ulp-margin-top"><?php _e('Reward Points', 'ulp')?></h4>
                        <p><?php _e('Optional. Student can receive points if he completes the current Quiz', 'ulp');?></p>
						<div class="ulp-input-group ulp-input-group-max">
							<input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_post_reward_points']);?>" min="0" name="ulp_post_reward_points" />
                            <div class="ulp-input-group-addon"><?php _e('points ', 'ulp');?></div>
                        </div>
                        </div>
                    </div>
             </div>
             <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php _e('Display options', 'ulp');?></h3>
                        <h4 class="ulp-margin-top"><?php _e('Hint Message', 'ulp')?></h4>
                        <div class="ulp-input-group" >
                        	<?php $checked = ($data['ulp_quiz_show_hint']) ? 'checked' : '';?>
                               <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_quiz_show_hint');" <?php echo esc_attr($checked);?> /><span><?php _e('Show hint message for each question', 'ulp')?></span>
                           <input type="hidden" name="ulp_quiz_show_hint" value="<?php echo esc_attr($data['ulp_quiz_show_hint']);?>" id="ulp_quiz_show_hint" />
                        </div>
                        <h4 class="ulp-margin-top"><?php _e('Explanation Message', 'ulp')?></h4>
                        <div class="ulp-input-group">
                        	<?php $checked = ($data['ulp_quiz_show_explanation']) ? 'checked' : '';?>
                               <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_quiz_show_explanation');" <?php echo esc_attr($checked);?> /><span><?php _e('Show explanation message for each question', 'ulp')?></span>
                           <input type="hidden" name="ulp_quiz_show_explanation" value="<?php echo esc_attr($data['ulp_quiz_show_explanation']);?>" id="ulp_quiz_show_explanation" />
                        </div>
                        <h4 class="ulp-margin-top"><?php _e('Question Order', 'ulp')?></h4>
                        <div class="ulp-input-group">
                         <?php $checked = ($data['ulp_quiz_display_questions_random']) ? 'checked' : '';?>
                             <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_quiz_display_questions_random');" <?php echo esc_attr($checked);?> /><span><?php _e('Show questions randomly every time', 'ulp')?></span>
                         <input type="hidden" name="ulp_quiz_display_questions_random" value="<?php echo esc_attr($data['ulp_quiz_display_questions_random']);?>" id="ulp_quiz_display_questions_random" />
                        </div>
                        <h4 class="ulp-margin-top"><?php _e('Answer Order', 'ulp')?></h4>
                        <div class="ulp-input-group">
                          <?php $checked = ($data['ulp_quiz_display_answers_random']) ? 'checked' : '';?>
                             <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_quiz_display_answers_random');" <?php echo esc_attr($checked);?> /><span><?php _e('Multi choice answers appear in a random order every time', 'ulp')?></span>
                         <input type="hidden" name="ulp_quiz_display_answers_random" value="<?php echo esc_attr($data['ulp_quiz_display_answers_random']);?>" id="ulp_quiz_display_answers_random" />
                        </div>
                         <h4 class="ulp-margin-top"><?php _e("Previous Question button", 'ulp')?></h4>
                        <div class="ulp-input-group">
                         <?php $checked = ($data['enable_back_button']) ? 'checked' : '';?>
                             <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#enable_back_button');" <?php echo esc_attr($checked);?> /><span><?php _e("Show previous question button (available only in default workflow)", 'ulp')?></span>
                         <input type="hidden" name="enable_back_button" value="<?php echo esc_attr($data['enable_back_button']);?>" id="enable_back_button" />
                        </div>
                        </div>
                    </div>
             </div>
             <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php _e('Additional Settings', 'ulp');?></h3>
                        <h4><?php _e("Quiz Workflow", 'ulp')?></h4>
                        <div class="ulp-input-group">
                        	<select name="quiz_workflow" class="ulp-form-control">
								<?php
									$values = array(
														'default' => esc_html__('Default', 'ulp'),
														'result_message' => esc_html__('User can see correct/wrong message after each question', 'ulp')
									);
								?>
								<?php foreach ($values as $k => $v):?>
									<?php $selected = ($data['quiz_workflow']==$k) ? 'selected' : '';?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php endforeach;?>
							</select>
                        </div>
                         <h4 class="ulp-margin-top"><?php _e("Retake Quiz", 'ulp')?></h4>
                         <p><?php _e('How many times a student can try to proceed the quiz', 'ulp')?></p>
                         <div class="ulp-input-group">
                            <span class="ulp-input-group-addon" id="basic-addon1"><?php _e('Max', 'ulp');?></span>
                         	<input type="number" class="ulp-form-control" name="retake_limit" min="1" value="<?php echo esc_attr($data['retake_limit']);?>" />
                         </div>
                        </div>
                    </div>
             </div>
             <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
							<input type="submit" name="save_special_settings" value="<?php _e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer ulp-submit-button" />
							</div>
						</div>
             </div>

	</div>
	<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />
</form>
<span class="ulp-js-quizes-special-settings" data-quiz_grade_type="<?php echo esc_attr($data['ulp_quiz_grade_type']);?>" ></span>
