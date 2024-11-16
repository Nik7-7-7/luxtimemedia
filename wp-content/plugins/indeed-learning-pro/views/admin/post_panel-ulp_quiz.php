<form action="<?php echo esc_url($data['form_submit_url']);?>" method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<?php do_action('ulp_admin_before_quiz_special_settings');?>
	<?php

	if( !isset($data['post_title'])) {
		$title = '';
	} else {
		$title =  $data['post_title'] . ' - ';
	}

	?>
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php echo esc_ulp_content($title) . esc_html__(' Special Settings', 'ulp');?></h3>
		<div class="inside">
			<div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Quiz Timeout', 'ulp');?></h2>
                        <p><?php esc_html_e('After a set amount of minutes, the quiz will end.', 'ulp');?></p>
                         <div class="input-group ulp-input-group-max">
                         	<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Time', 'ulp');?></span>
                         	<input type="number" class="form-control" name="quiz_time" min="1" value="<?php echo esc_attr($data['quiz_time']);?>" />
                            <div class="input-group-addon"> <?php esc_html_e('minutes', 'ulp');?></div>
                         </div>
                        </div>
                    </div>
             </div>
             <div class="ulp-line-break"></div>
             <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Assessments', 'ulp');?></h2>
                        <p><?php esc_html_e('Determine if the grade will be percentage or point based. Here you can also establish the passing grade for the quiz and the number of reward points given to the user after passing the quiz', 'ulp');?></p>

                        <h4 class="ulp-input-group-space"><?php esc_html_e('Grade based on', 'ulp')?></h4>
                        <div  class="ulp-input-group-max ulp-margin-bottom">
                        	<select name="ulp_quiz_grade_type"  class="form-control m-bot15" onChange="ulpShowSelectorIf('#ulp_point_description', this.value, 'point');">
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
																esc_html_e('In order to give grade based on points, you must set a number of points for each of quiz questions.', 'ulp');
														?></div>
                        </div>

                        <h4><?php esc_html_e('Passing Grade', 'ulp')?></h4>
						<div class="input-group ulp-input-group-max">
							<input type="number" class="form-control" value="<?php echo esc_attr($data['ulp_quiz_grade_value']);?>" name="ulp_quiz_grade_value" min="0" />
                        </div>

						<h4 class="ulp-input-group-space"><?php esc_html_e('Reward Points', 'ulp')?></h4>
                        <p><?php esc_html_e('Optional. Student can receive points if he completes the current Quiz', 'ulp');?></p>
						<div class="input-group ulp-input-group-max">
							<input type="number" class="form-control" value="<?php echo esc_attr($data['ulp_post_reward_points']);?>" min="0" name="ulp_post_reward_points" />
                            <div class="input-group-addon"><?php esc_html_e('points ', 'ulp');?></div>
                        </div>

                        </div>
                    </div>
             </div>
             <div class="ulp-line-break"></div>

             <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Display options', 'ulp');?></h2>

                        <h4 class="ulp-input-group-space"><?php esc_html_e('Hint Message', 'ulp')?></h4>
                        <p><?php esc_html_e('Show hint message for each question', 'ulp')?></p>
                        <div class="input-group ulp-input-group-max">
                        	<label class="ulp_label_shiwtch ulp-switch-button-margin">
							   							 <?php $checked = ($data['ulp_quiz_show_hint']) ? 'checked' : '';?>
                               <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_quiz_show_hint');" <?php echo esc_attr($checked);?> />
                               <div class="switch ulp-display-inline"></div>
                           </label>
                           <input type="hidden" name="ulp_quiz_show_hint" value="<?php echo esc_attr($data['ulp_quiz_show_hint']);?>" id="ulp_quiz_show_hint" />
                        </div>

                        <h4 class="ulp-input-group-space"><?php esc_html_e('Explanation Message', 'ulp')?></h4>
                        <p><?php esc_html_e('Show explanation message for each question', 'ulp')?></p>
                        <div class="input-group ulp-input-group-max">
                        	<label class="ulp_label_shiwtch ulp-switch-button-margin">
							   							 <?php $checked = ($data['ulp_quiz_show_explanation']) ? 'checked' : '';?>
                               <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_quiz_show_explanation');" <?php echo esc_attr($checked);?> />
                               <div class="switch ulp-display-inline"></div>
                           </label>
                           <input type="hidden" name="ulp_quiz_show_explanation" value="<?php echo esc_attr($data['ulp_quiz_show_explanation']);?>" id="ulp_quiz_show_explanation" />
                        </div>

                        <h4 class="ulp-input-group-space"><?php esc_html_e('Question Order', 'ulp')?></h4>
                        <p><?php esc_html_e('Show questions randomly every time', 'ulp')?></p>
                        <div class="input-group ulp-input-group-max">
                          <label class="ulp_label_shiwtch ulp-switch-button-margin">
							 							 <?php $checked = ($data['ulp_quiz_display_questions_random']) ? 'checked' : '';?>
                             <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_quiz_display_questions_random');" <?php echo esc_attr($checked);?> />
                             <div class="switch ulp-display-inline"></div>
                         </label>
                         <input type="hidden" name="ulp_quiz_display_questions_random" value="<?php echo esc_attr($data['ulp_quiz_display_questions_random']);?>" id="ulp_quiz_display_questions_random" />
                        </div>

                        <h4 class="ulp-input-group-space"><?php esc_html_e('Answer Order', 'ulp')?></h4>
                        <p><?php esc_html_e('Multi choice answers appear in a random order every time', 'ulp')?></p>
                        <div class="input-group ulp-input-group-max">
                          <label class="ulp_label_shiwtch ulp-switch-button-margin">
							 							 <?php $checked = ($data['ulp_quiz_display_answers_random']) ? 'checked' : '';?>
                             <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_quiz_display_answers_random');" <?php echo esc_attr($checked);?> />
                             <div class="switch ulp-display-inline"></div>
                         </label>
                         <input type="hidden" name="ulp_quiz_display_answers_random" value="<?php echo esc_attr($data['ulp_quiz_display_answers_random']);?>" id="ulp_quiz_display_answers_random" />
                        </div>

                         <h4 class="ulp-input-group-space"><?php esc_html_e("Previous Question button", 'ulp')?></h4>
                        <p><?php esc_html_e("Show previous question button (available only in default workflow)", 'ulp')?></p>
                        <div class="input-group ulp-input-group-max">
                          <label class="ulp_label_shiwtch ulp-switch-button-margin">
							 							 <?php $checked = ($data['enable_back_button']) ? 'checked' : '';?>
                             <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#enable_back_button');" <?php echo esc_attr( $checked );?> />
                             <div class="switch ulp-display-inline"></div>
                         </label>
                         <input type="hidden" name="enable_back_button" value="<?php echo esc_attr($data['enable_back_button']);?>" id="enable_back_button" />

                        </div>


                        </div>
                    </div>
             </div>
             <div class="ulp-line-break"></div>

             <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Additional Settings', 'ulp');?></h2>

                        <h4><?php esc_html_e("Quiz Workflow", 'ulp')?></h4>
                        <div class="ulp-input-group-max">
                        	<select name="quiz_workflow" class="form-control m-bot15">
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

                         <h4 class="ulp-input-group-space"><?php esc_html_e("Retake Quiz", 'ulp')?></h4>
                         <p><?php esc_html_e('How many times a student can try to proceed the quiz', 'ulp')?></p>
                         <div class="input-group ulp-input-group-max">
                            <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Max', 'ulp');?></span>
                         	<input type="number" class="form-control" name="retake_limit" min="1" value="<?php echo esc_attr($data['retake_limit']);?>" />
                         </div>

                        </div>
                    </div>
             </div>
             <div class="ulp-line-break"></div>


			<?php do_action('ulp_admin_after_quiz_special_settings');?>


			<div class="ulp-inside-item">
                        <div class="row">
                            <div class="col-xs-6">
							<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
							</div>
						</div>
             </div>

		</div>

	</div>

	<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />

</form>



<span class="ulp-js-post-panel-ulp-quiz" data-ulp_quiz_grade_type="<?php echo esc_attr($data['ulp_quiz_grade_type']);?>"></span>
