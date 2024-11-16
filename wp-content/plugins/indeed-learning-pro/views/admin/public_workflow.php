<?php if (!empty($data['error'])):?>
		<div class="ulp-error-global-dashboard-message"><?php echo esc_html($data['error']);?></div>
<?php endif;?>
<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Public Workflow', 'ulp');?></h3>

		<div class="inside">
		<div class="ulp-inside-item">
            <div class="row">
                <div class="col-xs-8">
                 <h2><?php esc_html_e('Images Size', 'ulp');?></h2>
                 <p><?php esc_html_e('Manage how Images on frontend will be displayed.', 'ulp');?></p>
                 <h4 class="ulp-input-group-space"><?php esc_html_e('Single Course Thumbnail', 'ulp');?></h4>
                 	<div class="input-group ulp-admin-public-workflow-field">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Width', 'ulp');?></span>
							<input type="number" class="form-control" name="ulp_singlecourse_imagesize_width" value="<?php echo esc_attr($data['metas']['ulp_singlecourse_imagesize_width']); ?>" min="1" />
                            <div class="input-group-addon">px</div>
                    </div>
                    <div class="input-group ulp-admin-public-workflow-special-field">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Height', 'ulp');?></span>
							<input type="number" class="form-control" name="ulp_singlecourse_imagesize_height" value="<?php echo esc_attr($data['metas']['ulp_singlecourse_imagesize_height']);?>" min="1" />
                            <div class="input-group-addon">px</div>
                    </div>
                    <div class="ulp-clear"></div>
                    <h4 class="ulp-input-group-space"><?php esc_html_e('Courses List Thumbnail', 'ulp');?></h4>
                 	<div class="input-group ulp-admin-public-workflow-field">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Width', 'ulp');?></span>
							<input type="number" class="form-control" name="ulp_multiplecourses_imagesize_width" value="<?php echo esc_attr($data['metas']['ulp_multiplecourses_imagesize_width']);?>" min="1" />
                            <div class="input-group-addon">px</div>
                    </div>
                    <div class="input-group ulp-admin-public-workflow-special-field">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Height', 'ulp');?></span>
							<input type="number" class="form-control" name="ulp_multiplecourses_imagesize_height" value="<?php echo esc_attr($data['metas']['ulp_multiplecourses_imagesize_height']);?>" min="1" />
                            <div class="input-group-addon">px</div>
                    </div>
                    <div class="ulp-clear"></div>
                </div>
            </div>
        </div>
		<div class="ulp-line-break"></div>

				<div class="ulp-inside-item">
						<h3><?php esc_html_e('Query variables', 'ulp');?></h3>
						<p><?php esc_html_e("Change this values only if you know what you're doing. Do not use custom post type slugs!", 'ulp');?>
 (ulp_course, ulp_quiz, ulp_question, ulp_lesson, etc)
						</p>
						<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Courses', 'ulp');?></span>
								<input type="text" name="ulp_course_custom_query_var" value="<?php echo esc_attr($data['metas']['ulp_course_custom_query_var']);?>" />
						</div>
						<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Quizes', 'ulp');?></span>
								<input type="text" name="ulp_quiz_custom_query_var" value="<?php echo esc_attr($data['metas']['ulp_quiz_custom_query_var']);?>" />
						</div>
						<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Lessons', 'ulp');?></span>
								<input type="text" name="ulp_lesson_custom_query_var" value="<?php echo esc_attr($data['metas']['ulp_lesson_custom_query_var']);?>" />
						</div>
						<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Questions', 'ulp');?></span>
								<input type="text" name="ulp_question_custom_query_var" value="<?php echo esc_attr($data['metas']['ulp_question_custom_query_var']);?>" />
						</div>
						<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Announcement', 'ulp');?></span>
								<input type="text" name="ulp_announcement_custom_query_var" value="<?php echo esc_attr($data['metas']['ulp_announcement_custom_query_var']);?>" />
						</div>
						<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Q&A', 'ulp');?></span>
								<input type="text" name="ulp_qanda_custom_query_var" value="<?php echo esc_attr($data['metas']['ulp_qanda_custom_query_var']);?>" />
						</div>
				</div>

				<div class="col-xs-12">
						<h2><?php esc_html_e('Show course curriculum as tab', 'ulp');?></h2>
						<div class="form-group row">
								<label class="ulp_label_shiwtch ulp-switch-button-margin">
										<?php $checked = ($data['metas']['ulp_show_curriculum_as_tab']) ? 'checked' : '';?>
										<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_show_curriculum_as_tab');" <?php echo esc_attr($checked);?> />
										<div class="switch ulp-display-inline"></div>
								</label>
								<input type="hidden" name="ulp_show_curriculum_as_tab" value="<?php echo esc_attr($data['metas']['ulp_show_curriculum_as_tab']);?>" id="ulp_show_curriculum_as_tab" />
						</div>
				</div>

			<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
    						<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
              	  </div>
              </div>
        </div>

	</div>
  </div>

</form>
