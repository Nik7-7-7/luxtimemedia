<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Course Estimation Time', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Course Estimation Time', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_course_time_period_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_course_time_period_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_course_time_period_enable" value="<?php echo esc_attr($data['metas']['ulp_course_time_period_enable']);?>" id="ulp_course_time_period_enable" />
			</div>
		  <div>
            	<h4><?php esc_html_e('How it works', 'ulp');?></h4>
					<?php esc_html_e('Once is enabled a new attribute for courses will be available and an estimated time can be provided ', 'ulp');?>
			</div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>
			</div>
		 </div>
       </div>
		</div>
	</div>

</form>
