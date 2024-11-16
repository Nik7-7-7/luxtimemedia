<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Notification settings', 'ulp');?></h3>

		<div class="inside">
	<div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-4">

     	<h2><?php esc_html_e('Additional Notifications options', 'ulp');?></h2>

        <div class="input-group ulp-input-group-max ulp-input-group-space">
  			     <span class="input-group-addon" id="basic-addon1"><?php esc_html_e("'From' E-mail Address:", 'ulp');?></span>
						 <input type="text" class="form-control" name="ulp_notifications_from_email_addr" value="<?php echo esc_attr($data['metas']['ulp_notifications_from_email_addr']);?>" />
  			</div>

		<div class="input-group ulp-input-group-max ulp-input-group-space">
  			     <span class="input-group-addon" id="basic-addon1"><?php esc_html_e("'From' name:", 'ulp');?></span>
						 <input type="text" class="form-control" name="ulp_notifications_from_name" value="<?php echo stripslashes($data['metas']['ulp_notifications_from_name']);?>" />
  			</div>

		<div class="input-group ulp-input-group-max ulp-input-group-space">
				  	<span class="input-group-addon" id="basic-addon1"><?php esc_html_e("Admin E-mail Address:", 'ulp');?></span>
					  <input type="text" class="form-control" name="ulp_notifications_admin_email" value="<?php echo esc_attr($data['metas']['ulp_notifications_admin_email']);?>" />
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
