<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('Front-End Course Creation', 'ulp');?></h3>
    		<div class="inside">
        	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                				<h2><?php esc_html_e('Activate/Hold Front-End Course Creation', 'ulp');?></h2>
                        <div><?php esc_html_e('Course Authors can create their courses from the front-end of your website', 'ulp');?></div>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_frontendcourse_enabled']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_frontendcourse_enabled');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_frontendcourse_enabled" value="<?php echo esc_attr($data['metas']['ulp_frontendcourse_enabled']);?>" id="ulp_frontendcourse_enabled" />
                			</div>

              </div>
            </div>
      </div>

      <div class="ulp-line-break"></div>
	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<h4><?php esc_html_e('How it works', 'ulp');?></h4>

				<div><?php esc_html_e('Once this module is eanabled a dedicated section on Instructor Account Page will be available and access on WP Dashboard restricted.', 'ulp');?></div>
				<div><?php esc_html_e('Instructors/Authors can manage and Add new Courses with all features that are currently available on ULP Dashboard. Items such Lessons, Quizzes and Questions or Announcements can be managed also.', 'ulp');?></div>
                <div><?php esc_html_e('New submitted Courses will be stored in Pending to be approved by the Admin first.', 'ulp');?></div>


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
