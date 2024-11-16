<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('Manage Announcements', 'ulp');?></h3>
    		<div class="inside">
        	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                				<h2><?php esc_html_e('Activate/Hold Announcements', 'ulp');?></h2>
                        <div><?php esc_html_e('Especially for promoting your courses and update students about new content or features.', 'ulp');?></div>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_announcements_enabled']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_announcements_enabled');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_announcements_enabled" value="<?php echo esc_attr($data['metas']['ulp_announcements_enabled']);?>" id="ulp_announcements_enabled" />
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
                    <li><?php esc_html_e('New Tab on Course page for enrolled students', 'ulp');?></li>
                    <li><?php esc_html_e('New Notification available for Students triggered when a new announcement is submitted', 'ulp');?></li>
                    <li><?php esc_html_e('New Notification available for Admin/Instructor when a student comment on posted announcement', 'ulp');?></li>
                    <li><?php esc_html_e('Admin or Instructor can manage comments and submit new announcements from Announcement section', 'ulp');?></li>

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
