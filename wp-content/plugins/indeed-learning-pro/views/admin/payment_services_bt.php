<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Bank Transfer Payment Integration', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate/Hold Bank Transfer Payment Integration', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_bt_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_bt_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_bt_enable" value="<?php echo esc_attr($data['metas']['ulp_bt_enable']);?>" id="ulp_bt_enable" />
			</div>
		</div>
      </div>
    </div>
            <div class="ulp-line-break"></div>

            <div class="ulp-inside-item ulp-input-group-space">
            <div class="row">
               <div class="col-xs-10">
			<h4><?php esc_html_e('Front-end Bank Transfer details', 'ulp');?></h4>
			<div class="ulp-form-line ulp-input-group-space">

                <div class="ulp-admin-bt-editor">
								<?php wp_editor( stripslashes($data['metas']['ulp_bt_message']), 'ulp_bt_message', array('textarea_name'=>'ulp_bt_message', 'quicktags'=>TRUE) );?>
							</div>
							<div class="ulp-admin-bt-constants">
								<div>{site_url}</div>
								<div>{blogname}</div>
								<div>{username}</div>
								<div>{user_email}</div>
								<div>{display_name}</div>
								<div>{first_name}</div>
								<div>{last_name}</div>
								<div>{user_id}</div>
								<div>{course_id}</div>
								<div>{course_name}</div>
								<div>{amount}</div>
								<div>{currency}</div>
							</div>

			</div>
		   </div>
              </div>
            </div>
            <div class="ulp-line-break"></div>

            <div class="ulp-inside-item ulp-input-group-space">
            <div class="row">
               <div class="col-xs-6">
               <h2><?php esc_html_e('Display options', 'ulp');?></h2>
					<div class="input-group ulp-input-group-max ulp-input-group-space">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label', 'ulp');?></span>
								<input type="text" class="form-control" name="ulp_bt_label" value="<?php echo stripslashes($data['metas']['ulp_bt_label']);?>" id="ulp_bt_label" />
						</div>
					<div class="input-group ulp-input-group-max ulp-input-group-space">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Description', 'ulp');?></span>
								<input type="text" class="form-control" name="ulp_bt_description" value="<?php echo stripslashes($data['metas']['ulp_bt_description']);?>" id="ulp_bt_description" />
						</div>
					<div class="input-group ulp-input-group-max ulp-input-group-space">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Multi-Payment order', 'ulp');?></span>
								<input type="number" class="form-control" min="1" name="ulp_bt_multipayment_order" value="<?php echo esc_attr($data['metas']['ulp_bt_multipayment_order']);?>" id="ulp_bt_multipayment_order" />
						</div>

		 </div>
       </div>
      </div>
			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>

		</div>
</div>

</form>
