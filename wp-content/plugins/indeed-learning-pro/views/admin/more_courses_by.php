<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('More Courses Box', 'ulp');?></h3>
    		<div class="inside">
        	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                					<h2><?php esc_html_e('Activate/Hold More Courses Box', 'ulp');?></h2>
                                    <p><?php esc_html_e('Display a list of courses submitted by current main Instructor', 'ulp');?></p>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_more_courses_by_enabled']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_more_courses_by_enabled');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_more_courses_by_enabled" value="<?php echo esc_attr($data['metas']['ulp_more_courses_by_enabled']);?>" id="ulp_more_courses_by_enabled" />
                			</div>

          <div>
			   	<h4><?php esc_html_e('How it works', 'ulp');?></h4>
        		<p><?php esc_html_e('An additional box can be displayed to show other courses managed and submitted by current main Instructor. You can place this box into course template page.', 'ulp');?></p>
        		<p><?php esc_html_e('To show up this additional section use the [ulp-more-courses-by instructor_id=" " course_id=" " limit=" "] shortcode into your course template. More details about this shortcode and required or available attributes can be found into Shortcodes section.', 'ulp');?></p>

          </div>
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
