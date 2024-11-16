<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Multiple instructors', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
				<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Multiple instructors', 'ulp');?></h2>

						<label class="ulp_label_shiwtch ulp-switch-button-margin">
							<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_multiple_instructors_enable');" <?php echo ($data['metas']['ulp_multiple_instructors_enable']) ? 'checked' : '';?> />
							<div class="switch ulp-display-inline"></div>
						</label>

	  			<input type="hidden" name="ulp_multiple_instructors_enable" id="ulp_multiple_instructors_enable" value="<?php echo esc_attr($data['metas']['ulp_multiple_instructors_enable']);?>" />
				</div>

                <div class="ulp-form-line">
						<di><?php esc_html_e('By activating this feature you can add multiple instructors to a course.', 'ulp');?></div>
                        <div><?php esc_html_e('Once you Add/Edit a course check the "Author" meta box.', 'ulp');?></div>
                        <div><a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course');?>"><?php esc_html_e('Courses List', 'ulp');?></a></div>
				</div>
          </div>
       </div>
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
	      <div class="form-group row">
	          <div class="col-4">
							<div class="ulp-submit-form">
	              <input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
	          	</div>
						</div>
	      </div>
		 </div>
       </div>
      </div>
    </div>
</div>
</form>
