<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Lesson drip content', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
				<div class="ulp-form-line">
                		<h2><?php esc_html_e('Activate Lesson drip content', 'ulp');?></h2>
						<label class="ulp_label_shiwtch ulp-switch-button-margin">
							<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#lesson_drip_content_enable');" <?php echo ($data['metas']['lesson_drip_content_enable']) ? 'checked' : '';?> />
							<div class="switch ulp-display-inline"></div>
						</label>
	  			<input type="hidden" name="lesson_drip_content_enable" id="lesson_drip_content_enable" value="<?php echo esc_attr($data['metas']['lesson_drip_content_enable']);?>" />
				</div>

                <div class="ulp-form-line">
						<div><?php esc_html_e('By activating this feature you can decide when a lesson become available.', 'ulp');?></div>
                         <div><?php esc_html_e('Once you Add/Edit a lesson check "Drip Content" meta box.', 'ulp');?></div>
                        <div><a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_lesson');?>"><?php esc_html_e('Lessons List', 'ulp');?></a></div>
				</div>

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
