<form action="<?php echo esc_url($saveLink);?>" method="post" role = "form">
	<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

	<div class="ulp-instructor-edit ulp-instructor-edit-course-settings">
  	<h2 class="ulp-instructor-edit-top-title"><?php echo esc_ulp_content('<span class="ulp-post-title">'.$post_title . '</span>').esc_html__(' - Special Settings', 'ulp');?></h2>
				<input type="hidden" name="ID" value="<?php echo esc_attr($postId);?>" />
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
                        	<h3><?php esc_html_e('Featured Course', 'ulp');?></h3>

                            <div class="ulp-form-section">
					 		<label class="ulp_label_shiwtch">
								 <?php $checked = ($data['ulp_course_featured']) ? 'checked' : '';?>
					 			 <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_course_featured');" <?php echo esc_attr($checked);?> />
				 			</label><span><strong><?php esc_html_e('Activate this option to highlight your course in the Public Section.', 'ulp');?></strong></span>
							<input type="hidden" name="ulp_course_featured" value="<?php echo esc_attr($data['ulp_course_featured']);?>" id="ulp_course_featured" />

                            </div>
                        </div>
                    </div>
                <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        	<h3><?php esc_html_e('Prerequisites', 'ulp');?></h3>
							<p><?php esc_html_e('Users can access this course if they have certain courses already completed, or a certain number of reward points.', 'ulp');?></p>
                            <h4 class="ulp-margin-top"><?php esc_html_e('Pre-request Courses', 'ulp');?></h4>
                            <p><?php esc_html_e('Create a list of courses required to be finished before the user can access this course. A list of course ids separated by commas.', 'ulp');?></p>
                            <div class="ulp-input-group ulp-input-group-max">
                            	<span class="ulp-input-group-addon" id="basic-addon1">Courses</span>
                                <input type="text" class="ulp-form-control" placeholder="Add course IDs" name="ulp_course_prerequest_courses" value="<?php echo esc_attr($data['ulp_course_prerequest_courses']);?>" aria-describedby="basic-addon1">
                            </div>
                            <h4 class="ulp-margin-top"><?php esc_html_e('Pre-request rewarded points', 'ulp');?></h4>
                            <p><?php esc_html_e('Set the minimal value of reward points required by a user, in order to access this course.', 'ulp');?></p>
                            <div class="ulp-input-group ulp-input-group-max">
                            	<span class="ulp-input-group-addon" id="basic-addon1">Points</span>
                                <input type="number" class="ulp-form-control" placeholder="Set a minimum required points" name="ulp_course_prerequest_reward_points" value="<?php echo esc_attr($data['ulp_course_prerequest_reward_points']);?>" aria-describedby="basic-addon1">
                            </div>
                          </div>
                		</div>
                    </div>
                <div class="ulp-instructor-edit-line-break"></div>
                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-4">
                        	<h3><?php esc_html_e('Payment request', 'ulp');?></h3>
                            <div class="ulp-form-section">
                                <select name="ulp_course_payment" class="ulp-form-control" onchange="ulpShowSelectorIf('#ulp_course_price_num', this.value, 1);">
                                    <?php $values = array(0 =>esc_html__('Free', 'ulp'), 1 =>esc_html__('Paid', 'ulp'));?>
                                    <?php foreach ($values as $k => $v):?>
                                        <?php $selected = ($data['ulp_course_payment']==$k) ? 'selected' : '';?>
                                        <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                                    <?php endforeach;?>
                                </select>
							</div>
							<div class="ulp-form-section"  id="ulp_course_price_num">
									<div class="ulp-input-group ulp-input-group-max">
										<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Price', 'ulp')?></span>
										<input type="number" min="0" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_course_price']);?>" name="ulp_course_price" />
		                                <div class="ulp-input-group-addon"><?php echo ulp_currency();?></div>
									</div>
							</div>
							<div class="ulp-form-section ulp-margin-tiny-top">
								<div class="ulp-input-group ulp-input-group-max">
									<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Initial Price', 'ulp')?></span>
									<input type="number" min="0" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_course_initial_price']);?>" name="ulp_course_initial_price" />
									<div class="ulp-input-group-addon"><?php echo ulp_currency();?></div>
								</div>
							</div>
                    </div>
                 </div>
                <div class="ulp-instructor-edit-line-break"></div>
                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-12">
                        <h3><?php esc_html_e('Assessments', 'ulp');?></h3>
                        <div><?php esc_html_e('Determine how the user can pass the course, and the minimal value required. The field Passing Value is a percentage value.', 'ulp');?></div>
<div><?php esc_html_e('As an example, if it is set at 70%', 'ulp');?></div>
<div><?php esc_html_e('Lessons: the user must complete 70% of the course’s lessons to pass;', 'ulp');?></div>
<div><?php esc_html_e('Quiz average: the average value of all the quiz grades must be at least 70%;', 'ulp');?></div>
<div><?php esc_html_e('Final Quiz: the grade of the final quiz must be at least 70%;', 'ulp');?></div>
						</div>
                 </div>
                 <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-4">
						<h4 class="ulp-margin-top"><?php esc_html_e('Result based on', 'ulp')?></h4>
                        <div class="ulp-form-section">
                          <select name="ulp_course_assessments" class="ulp-form-control" onChange="ulpShowSelectorIf('#ulp_zuiq_average', this.value, 'quizes');" >
                              <?php $values = array('lessons' =>esc_html__('Lessons', 'ulp'), 'quizes' =>esc_html__('Quizzes average', 'ulp'), 'final_quiz' =>esc_html__('Final Quiz', 'ulp'));?>
                              <?php foreach ($values as $k => $v):?>
                                  <?php $selected = ($data['ulp_course_assessments']==$k) ? 'selected' : '';?>
                                  <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                              <?php endforeach;?>
                          </select>
						</div>
                        <h4><?php esc_html_e('Passing Value ', 'ulp')?></h4>
                        <div class="ulp-form-section ulp-margin-tiny-top">
				 		<div class="ulp-input-group ulp-input-group-max">
							<input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_course_assessments_passing_value']);?>" min="1" name="ulp_course_assessments_passing_value" />
                            <div class="ulp-input-group-addon">%</div>
						</div>
                        </div>

						<div class="ulp-form-section ulp-margin-top ulp-display-none"  id="ulp_zuiq_average">
						 <div class="ulp-input-group ulp-input-group-max">
						    <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Minimum grade for each Quiz:', 'ulp')?></span>
						                <input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_course_quizes_min_grade']);?>" min="1" name="ulp_course_quizes_min_grade" />
						   </div>

                          <p><?php esc_html_e('Determine the minimum grade required to pass a quiz. This option affects all the quizzes.
Example: If a course has 5 quizzes, a user must pass each quiz with at least the minimum grade.', 'ulp');?></p>
						</div>
                        <h4 class="ulp-margin-top"><?php esc_html_e('Rewards: ', 'ulp')?></h4>
                        <p><?php esc_html_e('Set how many reward points a user receives for completing this course.', 'ulp');?></p>
                        <div class="ulp-form-section">
                          <div class="ulp-input-group ulp-input-group-max">
                              <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Points: ', 'ulp');?></span>
                              <input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_post_reward_points']);?>" min="0" name="ulp_post_reward_points"/>
                          </div>
                        </div>
                        </div>
                    </div>
                <div class="ulp-instructor-edit-line-break"></div>
                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
                        <h2><?php esc_html_e('Display options', 'ulp');?></h2>
                        <h4 class="ulp-margin-top"><?php esc_html_e('Modules items', 'ulp')?></h4>
                        <div class="ulp-form-section">
					 		<p><?php esc_html_e('Order by', 'ulp')?></p>
                          <select name="ulp_modules_order_items_by" class="ulp-form-control">
                              <?php $values = array(
                                                                          'default' =>esc_html__('Default', 'ulp'),
                                                                          'post_title' =>esc_html__('Title', 'ulp'),
                                                                          'post_date' =>esc_html__('Date', 'ulp'),
                                                                          'ulp_post_reward_points' =>esc_html__('Points', 'ulp'),
                              );?>
                              <?php foreach ($values as $k => $v):?>
                                  <?php $selected = ($data['ulp_modules_order_items_by']==$k) ? 'selected' : '';?>
                                  <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                              <?php endforeach;?>
                          </select>
                      </div>

                      <div  class="ulp-form-section ulp-margin-top">
                          <label><?php esc_html_e('Order type', 'ulp')?></label>
                          <select name="ulp_modules_order_items_type" class="ulp-form-control">
                              <?php $values = array(
                                                                          'asc' =>esc_html__('Ascending ', 'ulp'),
                                                                          'desc' =>esc_html__('Descending', 'ulp'),
                              );?>
                              <?php foreach ($values as $k => $v):?>
                                  <?php $selected = ($data['ulp_modules_order_items_type']==$k) ? 'selected' : '';?>
                                  <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                              <?php endforeach;?>
                          </select>
                      </div>
                       <div  class="ulp-form-section">
                          <div class="ulp-input-group ulp-margin-top">
                             <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Modules per page ', 'ulp');?></span>
                            <input type="number" class="ulp-form-control" value="<?php echo esc_attr($data['ulp_modules_per_page']);?>" min="0" name="ulp_modules_per_page"/>
                          </div>
                      </div>
                      </div>
                    </div>
                <div class="ulp-instructor-edit-line-break"></div>
                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-5">
                        <h2><?php esc_html_e('Additional Settings', 'ulp');?></h2>
                        <h4><?php esc_html_e('Duration', 'ulp');?></h4>
                         <div  class="ulp-form-section">
                          <div class="ulp-input-group">
                              <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Duration', 'ulp');?></span>
                              <input type="number" class="ulp-form-control" name="ulp_course_duration" value="<?php echo esc_attr($data['ulp_course_duration']);?>" min="1" />
                          </div>
                          </div>
                        <div  class="ulp-form-section ulp-margin-top">
                              <select name="ulp_course_duration_type" class="ulp-form-control">
                                  <?php
                                      $values = array(
                                                      'm' =>esc_html__('Minutes', 'ulp'),
                                                      'h' =>esc_html__('Hours', 'ulp'),
                                                      'd' =>esc_html__('Days', 'ulp'),
                                                      'w' =>esc_html__('Weeks', 'ulp')
                                      );
                                  ?>
                                  <?php foreach ($values as $k => $v):?>
                                      <?php $selected = $data['ulp_course_duration_type']==$k ? 'selected' : '';?>
                                      <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
                                  <?php endforeach;?>
                              </select>
                          </div>
                          <h4 class="ulp-margin-top"><?php esc_html_e('Limitted enrolls', 'ulp');?></h4>
                          <p><?php esc_html_e('The maximum number of students that can join this course.', 'ulp');?></p>
                           <div  class="ulp-form-section">
                          <div class="ulp-input-group">
                               <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Students', 'ulp');?></span>
                              <input type="number" class="ulp-form-control"  name="ulp_course_max_students" value="<?php echo esc_attr($data['ulp_course_max_students']);?>" min="1" />
                          </div>
                          </div>
                          <h4 class="ulp-margin-top"><?php esc_html_e('Re-take course', 'ulp');?></h4>
                          <p><?php esc_html_e('How many times a user can enroll to this course.', 'ulp');?></p>
                          <div  class="ulp-form-section">
                          <div class="ulp-input-group">
                               <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Repeat', 'ulp');?></span>
                              <input type="number" class="ulp-form-control" name="ulp_course_retake_course" value="<?php echo esc_attr($data['ulp_course_retake_course']);?>" min="1" />
                          </div>
                          </div>
                          <h4 class="ulp-margin-top"><?php esc_html_e('Access item', 'ulp');?></h4>
                          <div  class="ulp-form-section">
                           <div class="ulp-input-group" >
                                   <?php $checked = ($data['ulp_course_access_item_only_if_prev']) ? 'checked' : '';?>
                                   <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_course_access_item_only_if_prev');" <?php echo esc_attr($checked);?> />
                                   <span><strong><?php esc_html_e('Activate the Option', 'ulp');?></strong></span>
                               <input type="hidden" name="ulp_course_access_item_only_if_prev" value="<?php echo esc_attr($data['ulp_course_access_item_only_if_prev']);?>" id="ulp_course_access_item_only_if_prev" />
                          </div>
                          </div>
                          <div><?php esc_html_e('Access item(lesson or quiz) only if the previous is completed', 'ulp');?></div>
                          <div><?php esc_html_e('Activate this option if you want to force the user to finish the prior lesson or quiz before moving on to the next one. ', 'ulp');?></div>
                          <div><?php esc_html_e('Example: Lesson A is the first one, in order to go to the next lesson, the user must first finish A.', 'ulp');?></div>


                      </div>
                    </div>
<div class="ulp-instructor-edit-line-break"></div>

        <?php if (!empty($data ['course_difficulty_types'])):?>
                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">

                            <h2><?php esc_html_e('Course difficulty', 'ulp');?></h2>
                            <div  class="ulp-form-section">
                             <div class="ulp-1form-control ulp-margin-top ulp-input-group-max">
                            <select name="ulp_course_difficulty" class="form-control m-bot15">
                                    <?php foreach ($data['course_difficulty_types'] as $slug => $label):?>
                                    <option value="<?php echo esc_attr($slug);?>" <?php echo ($slug==$data ['ulp_course_difficulty']) ? 'selected' : '';?> ><?php echo esc_html($label);?></option>
                                    <?php endforeach;?>
                            </select>
       						</div>
                            </div>
                   	</div>
                  </div>
                <div class="ulp-instructor-edit-line-break"></div>
	   <?php else :?>
                <input type="hidden" name="ulp_course_difficulty" value="<?php echo esc_attr($data ['ulp_course_difficulty']);?>" />
       <?php endif;?>

		<?php if (!empty($data ['ulp_course_time_period_enable'])):?>

                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-5">
                                    <h2 class=" ulp-margin-top"><?php esc_html_e('Course Estimation Time', 'ulp');?></h2>
                                    <p><?php esc_html_e('Additional attribute for Courses which will inform stundents how will take to complete the course', 'ulp');?></p>
                                    <div  class="ulp-form-section">
                                    <div class="ulp-input-group  ulp-margin-top ulp-input-group-max">
		                              <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Duration', 'ulp');?></span>
		                              <input type="number" class="ulp-form-control" name="ulp_course_time_period_duration" value="<?php echo esc_attr($data['ulp_course_time_period_duration']);?>" min="1" />
		                          </div>
                                  </div>

		                          <div  class="ulp-form-section  ulp-margin-top">
		                              <select name="ulp_course_time_period_duration_type" class="ulp-form-contro">
		                                  <?php
		                                      $values = ulp_get_time_types();
		                                  ?>
		                                  <?php foreach ($values as $k => $v):?>
		                                      <?php $selected = $data['ulp_course_time_period_duration_type']==$k ? 'selected' : '';?>
		                                      <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
		                                  <?php endforeach;?>
		                              </select>
		                          </div>
                        </div>
                    </div>
					<div class="ulp-instructor-edit-line-break"></div>
		<?php else :?>
			<input type="hidden" name="ulp_course_time_period_duration" value="<?php echo esc_attr($data ['ulp_course_time_period_duration']);?>" />
			<input type="hidden" name="ulp_course_time_period_duration_type" value="<?php echo esc_attr($data ['ulp_course_time_period_duration_type']);?>" />
		<?php endif;?>


<?php if (!empty($data['coming_soon'])):?>

                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-12">

				<h2><?php esc_html_e('Coming Soon', 'ulp');?></h2>
                <div  class="ulp-form-section">
				<div class="ulp-input-group">
					<label class="ulp_label_shiwtch ulp-switch-button-margin">
								 <?php $checked = ($data['ulp_course_coming_soon_enabled']) ? 'checked' : '';?>
								 <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_course_coming_soon_enabled');" <?php echo esc_attr($checked);?> />
						 </label><span><strong><?php esc_html_e('Enable this workflow and Course content will be replaced with a custom message', 'ulp');?></strong></span>
						 <input type="hidden" name="ulp_course_coming_soon_enabled" value="<?php echo esc_attr($data['ulp_course_coming_soon_enabled']);?>" id="ulp_course_coming_soon_enabled" />
				</div>
                </div>

				<?php
						wp_enqueue_script('ulp-jquery.uploadfile', ULP_URL . 'assets/js/jquery.uploadfile.min.js', array('jquery'), '3.7' );
						wp_enqueue_script( 'ulp_ckeditor', ULP_URL . 'assets/js/ckeditor/ckeditor.js', ['jquery'], '3.7' , false);
						wp_enqueue_style('ulp_jquery_ui', ULP_URL . 'assets/css/jquery-ui.min.css', array(), '3.7' );
						wp_enqueue_script('jquery-ui-datepicker');
				?>
				<span class="ulp-js-include-ckeditor-comming-soon-message" data-base_path="<?php echo ULP_URL . 'assets/js/ckeditor/';?>" ></span>

				<h4><?php esc_html_e('The message', 'ulp');?></h4>
				<div class="ulp-wp_editor ulp-course-special-editor">
					<textarea name="ulp_course_coming_soon_message" id="ulp_course_coming_soon_message"><?php echo stripslashes($data['ulp_course_coming_soon_message']);?></textarea>
			  </div>

				<div class="ulp-clear"></div>
				<div class="ulp-input-group  ulp-margin-top ulp-input-group-max">
				    <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('End time', 'ulp');?></span>
				    <input type="text" class="ulp-form-control ulp-datetime" name="ulp_course_coming_soon_end_time" value="<?php echo esc_attr($data['ulp_course_coming_soon_end_time']);?>"/>
				</div>

				<div  class="ulp-form-section ulp-margin-top">
				<div class="ulp-input-group">
								 <?php $checked = ($data['ulp_course_coming_soon_show_count_down']) ? 'checked' : '';?>
								 <input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_course_coming_soon_show_count_down');" <?php echo esc_attr($checked);?> />
                                 <span><strong><?php esc_html_e('Show Countdown on Course page', 'ulp');?></strong></span>
						 <input type="hidden" name="ulp_course_coming_soon_show_count_down" value="<?php echo esc_attr($data['ulp_course_coming_soon_show_count_down']);?>" id="ulp_course_coming_soon_show_count_down" />
				</div>
                </div>
			</div>
		</div>
		<div class="ulp-instructor-edit-line-break"></div>

<?php endif;?>

                <div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
                        <div  class="ulp-form-section ulp-margin-top">
                            <input type="submit" name="save_special_settings" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer ulp-submit-button" />
                         </div>
                            </div>
                       </div>
					<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['postId']);?>" />
</div>
</form>
<span class="ulp-js-instructor-courses-special-settings"
			data-ulp_course_assessments="<?php echo esc_attr($data['ulp_course_assessments']);?>"
			data-ulp_course_payment="<?php echo esc_attr($data['ulp_course_payment']);?>" ></span>
