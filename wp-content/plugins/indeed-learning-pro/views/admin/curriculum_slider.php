<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('Curriculum Slider', 'ulp');?></h3>
    		<div class="inside">
        	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                				<h2><?php esc_html_e('Activate/Hold Curriculum Slider', 'ulp');?></h2>
                                <p><?php esc_html_e('A new box will slideIn from the left side on Lessons, Quizzes pages.', 'ulp');?></p>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_curriculum_slider_enabled']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_curriculum_slider_enabled');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_curriculum_slider_enabled" value="<?php echo esc_attr($data['metas']['ulp_curriculum_slider_enabled']);?>" id="ulp_curriculum_slider_enabled" />
                			</div>
                  </div>
                </div>
			</div>
            <div class="ulp-line-break"></div>
            <div class="ulp-inside-item">
                <div class="row">
                    <div class="col-xs-6">
                  			<div class="ulp-form-line">
                  				<h2><?php esc_html_e('Box Label', 'ulp');?></h2>
                                <p><?php esc_html_e('Max 20 characters are allowed', 'ulp');?></p>
                                <div class="input-group ulp-input-group-max">
                                <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label', 'ulp');?></span>
                          <input type="text" class="form-control" name="ulp_curriculum_slider_label" maxlength="20" value="<?php echo stripslashes($data['metas']['ulp_curriculum_slider_label']);?>" />
                  			</div>
                    </div>
                </div>
			</div>
            <div class="ulp-line-break"></div>
            <div class="ulp-inside-item">
								<div class="row">
										<div class="col-xs-6">
												<div class="ulp-form-line">
														<h5><?php esc_html_e('Custom CSS', 'ulp');?></h5>
														<textarea name="ulp_curriculum_slider_custom_css" class="ul-admin-custom-css"><?php echo esc_attr($data['metas']['ulp_curriculum_slider_custom_css']);?></textarea>
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
