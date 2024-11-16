<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Admin Workflow', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
      	<div class="row">

	      		<div class="col-xs-12">
	              <h2><?php esc_html_e('Show dashboard notifications', 'ulp');?></h2>
	              <p><?php esc_html_e('New studends and New Orders.', 'ulp');?></p>

	              <div class="form-group row">
	                  <label class="ulp_label_shiwtch ulp-switch-button-margin">
	                  <?php $checked = ($data['metas']['ulp_dashboard_notifications']) ? 'checked' : '';?>
	                  <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_dashboard_notifications');" <?php echo esc_attr($checked);?> />
	                  <div class="switch ulp-display-inline"></div>
	                  </label>
	                  <input type="hidden" name="ulp_dashboard_notifications" value="<?php echo esc_attr($data['metas']['ulp_dashboard_notifications']);?>" id="ulp_dashboard_notifications" />
	              </div>
	          </div>

						<div class="col-xs-12">
	              <h2><?php esc_html_e('Special settings', 'ulp');?></h2>
	              <p><?php esc_html_e('Can entry instructors access special settings?', 'ulp');?></p>
	              <div class="form-group row">
	      						<label class="ulp_label_shiwtch ulp-switch-button-margin">
			                  <?php $checked = ($data['metas']['ulp_show_special_settings_for_entry_instructors']) ? 'checked' : '';?>
			                  <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_show_special_settings_for_entry_instructors');" <?php echo esc_attr($checked);?> />
			                  <div class="switch ulp-display-inline"></div>
	                  </label>
	                  <input type="hidden" name="ulp_show_special_settings_for_entry_instructors" value="<?php echo esc_attr($data['metas']['ulp_show_special_settings_for_entry_instructors']);?>" id="ulp_show_special_settings_for_entry_instructors" />
	              </div>
	          </div>

						<div class="col-xs-12">
								<h2><?php esc_html_e('Unistall settings', 'ulp');?></h2>
								<p><?php esc_html_e('Keep data after delete plugin', 'ulp');?></p>
								<div class="form-group row">
										<label class="ulp_label_shiwtch ulp-switch-button-margin">
												<?php $checked = ($data['metas']['ulp_keep_data_after_delete']) ? 'checked' : '';?>
												<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_keep_data_after_delete');" <?php echo esc_attr($checked);?> />
												<div class="switch ulp-display-inline"></div>
										</label>
										<input type="hidden" name="ulp_keep_data_after_delete" value="<?php echo esc_attr($data['metas']['ulp_keep_data_after_delete']);?>" id="ulp_keep_data_after_delete" />
								</div>
						</div>
	      </div>
    </div>
  	<div class="ulp-line-break"></div>
	<div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-6">
                  <div class="form-group row">
                          <div class="col-4">
                                  <input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
                        </div>
                  </div>
		  </div>
         </div>
       </div>
	</div>

</form>
