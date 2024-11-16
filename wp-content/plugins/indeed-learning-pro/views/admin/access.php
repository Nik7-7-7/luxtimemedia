<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Roles allowed to enter into WordPress Admin Dashboard:', 'ulp');?></h3>
		<div class="inside">
			<div class="ulp-wp-role-list-col">
				<div class="ulp-form-line ulp-wp-role-label-admin">
					<span class="ulp-wp-role-label"><?php esc_html_e('Administrator', 'ulp');?></span>
					<label class="ulp_label_shiwtch ulp-switch-button-margin">
						<input type="checkbox" class="ulp-switch" onClick="" checked disabled/>
						<div class="switch ulp-display-inline"></div>
					</label>
				</div>
				<?php
					if (!empty($data['roles']['administrator'])){
						      unset($data['roles']['administrator']);
					}
					if (!empty($data['roles']['pending_user'])){
						      unset($data['roles']['pending_user']);
					}
					$count = count($data['roles']) + 1;
					$break = ceil($count/2);
					$i = 1;
					foreach ($data['roles'] as $role=>$arr){
					?>
						<div class="ulp-form-line">
							<span class="ulp-wp-role-label"><?php echo esc_html($arr['name']);?></span>
							<label class="ulp_label_shiwtch ulp-switch-button-margin">
								<?php $checked = in_array($role, $data['metas']['ulp_dashboard_allowed_roles_as_array']) ? 'checked' : '';?>
								<input type="checkbox" class="ulp-switch" onClick="ulpSecondMakeInputhString(this, '<?php echo esc_attr($role);?>', '#ulp_dashboard_allowed_roles');" <?php echo esc_attr($checked);?>/>
								<div class="switch ulp-display-inline"></div>
							</label>
						</div>
					<?php
					$i++;
						if ($count>7 && $i==$break){
						?>
						</div>
						<div class="ulp-wp-role-list-col">
						<?php
						}
					}///end of foreach
				?>
			</div>
			<input type="hidden" name="ulp_dashboard_allowed_roles" id="ulp_dashboard_allowed_roles" value="<?php echo esc_attr($data['metas']['ulp_dashboard_allowed_roles']);?>" />
      <div class="form-group row">
          <div class="col-4">
              <input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
          </div>
      </div>

	</div>

</form>
