
<div class="ulp-stuffbox">

	<form  method="post" role = "form">
			<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
			
			<h3 class="ulp-h3"><?php esc_html_e('About the Instructor', 'ulp');?></h3>
			<div class="inside">
            <div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
				<div class="ulp-form-line">
						<h2><?php esc_html_e('Activate About the Instructor', 'ulp');?></h2>
						<label class="ulp_label_shiwtch ulp-switch-button-margin">
							<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_about_the_instructor_mf');" <?php echo ($data['metas']['ulp_about_the_instructor_mf']) ? 'checked' : '';?> />
							<div class="switch ulp-display-inline"></div>
						</label>
						<input type="hidden" name="ulp_about_the_instructor_mf" id="ulp_about_the_instructor_mf" value="<?php echo esc_attr($data['metas']['ulp_about_the_instructor_mf']);?>" />
				</div>
			   <div>

            	<h4><?php esc_html_e('How it works', 'ulp');?></h4>
        <p><?php esc_html_e('An additional box can be displayed to show more details about the main Instructor of the current course', 'ulp');?></p>
        <p><?php esc_html_e('To show up this additional section use the [ulp_about_the_instructor instructor_id={your desired instructor}] shortcode into your course template. More details about this shortcode and required or available attributes can be found into Shortcodes section.', 'ulp');?></p>
			  </div>
				<div class="ulp-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer">
				</div>
		   </div>
          </div>
        </div>
		</div>
	</form>
</div>
