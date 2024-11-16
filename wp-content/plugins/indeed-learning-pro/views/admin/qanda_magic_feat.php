<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('Q&A (Questions&Answers) Section', 'ulp');?></h3>
    		<div class="inside">
        	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                				<h2><?php esc_html_e('Activate/Hold Q&A (Questions&Answers)', 'ulp');?></h2>
                        		<div><?php esc_html_e('Students can submit new questions to Author/Instructor for each course or search for previous questions', 'ulp');?></div>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_qanda_enabled']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_qanda_enabled');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_qanda_enabled" value="<?php echo esc_attr($data['metas']['ulp_qanda_enabled']);?>" id="ulp_qanda_enabled" />
                			</div>

              </div>
            </div>
      </div>
		<div class="ulp-line-break"></div>
        <div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<h4><?php esc_html_e('How it works', 'ulp');?></h4>

				<div><?php esc_html_e('Once is enabled you will find:', 'ulp');?></div>
				<ol>
                	<li><?php esc_html_e('New column and dedicated section on Courses table', 'ulp');?></li>
                    <li><?php esc_html_e('New Tab on Course page for enrolled students: Q&A', 'ulp');?></li>
                    <li><?php esc_html_e('New Notification available for Admin/Instructor when a student submit a question', 'ulp');?></li>
                    <li><?php esc_html_e('New Notification available for Students when Author/Instructor reply on his question.', 'ulp');?></li>
                    <li><?php esc_html_e('Students can Search for questions or submit a new one from Course page under Q&A tab', 'ulp');?></li>
                    <li><?php esc_html_e('Admin or Instructor can manage and Comment to questions from  Q&A section', 'ulp');?></li>

                </ol>


              </div>
            </div>
      </div>
		<div class="ulp-inside-item">
      <div class="row">
        <div class="col-xs-6">
			      <div class="ulp-submit-form">
				          <input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
            </div>
        </div>
      </div>
    </div>

    </div>
  </div>

</form>
