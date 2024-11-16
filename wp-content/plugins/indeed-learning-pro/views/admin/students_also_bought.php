<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('Students also Bought', 'ulp');?></h3>
    		<div class="inside">
        		<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                					<h2><?php esc_html_e('Activate/Hold students also Bought Box', 'ulp');?></h2>
                                    <p><?php esc_html_e('Display courses that has been bought by the current enrolled Students.', 'ulp');?></p>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_student_also_bought_enable']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_student_also_bought_enable');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_student_also_bought_enable" value="<?php echo esc_attr($data['metas']['ulp_student_also_bought_enable']);?>" id="ulp_student_also_bought_enable" />
                			</div>
            			</div>
        		  </div>
            </div>
			<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
            		<p><?php esc_html_e('An additional box can be displayed to show what students from current course have bought also. That will encoruge other students to enroll on more courses.', 'ulp');?></p>
                    <p><?php esc_html_e('Use this shortcode in order to display the items: [ulp_students_also_bought course_id=""]. More about this shortcode can be found into Shortcodes section.', 'ulp');?></p>
                    	</div>
        		  </div>
            </div>
            <div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-4">
           			 <div class="input-group">
            		<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Max Limit', 'ulp');?></span>
            		<input type="number" min=1 class="form-control" value="<?php echo esc_attr($data['metas']['ulp_student_also_bought_limit']);?>" name="ulp_student_also_bought_limit" />
            		</div>
							</div>
						</div>
						<div class="row">
								<div class="col-xs-4">
					<div class="input-group">
            		<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Minimum', 'ulp');?></span>
            		<input type="number" min=1 class="form-control" value="<?php echo esc_attr($data['metas']['ulp_student_also_bought_minimum_limit']);?>" name="ulp_student_also_bought_minimum_limit" />

            		</div>
					 <p><?php esc_html_e('The Box will not show up if there are not enought Courses to be displayed.', 'ulp');?></p>
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

</form>
