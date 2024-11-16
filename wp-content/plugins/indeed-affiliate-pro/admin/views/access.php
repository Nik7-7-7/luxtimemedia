<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

 	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('WordPress Roles Management', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line uap-no-border">
						<h2><?php esc_html_e('Enable access for specific WordPress Roles', 'uap');?></h2>
						<p><?php esc_html_e('By Default Only Administrator WP Role will have access into WordPress Dashboard. All other logged users will be kept on FrontEnd Side. You can enable access to certain Users by on their WP Role using below On/Off buttons', 'uap');?></p>
						<p><?php esc_html_e('Warning: This option will manage access to WordPress Dashboard only. Ultimate Affiliate Pro Dashboard will continue to be accessible ONLY by users with Administrator WP Role', 'uap');?></p>
					</div>
			<div class="uap-half-block">
				<div class="uap-form-line uap-access-opacity">
					<span class="uap-access-label"><?php esc_html_e('Administrator', 'uap');?></span>
					<label class="uap_label_shiwtch uap-access-switch">
						<input type="checkbox" class="uap-switch" checked disabled/>
						<div class="switch uap-inline-block"></div>
					</label>
				</div>
				<?php
					$roles = get_editable_roles();
					if (!empty($roles['administrator'])){
						unset($roles['administrator']);
					}
					if (!empty($roles['pending_user'])){
						unset($roles['pending_user']);
					}
					$count = count($roles) + 1;
					$break = ceil($count/2);
					$i = 1;
					foreach ($roles as $role=>$arr){
					?>
						<div class="uap-form-line">
							<span class="uap-access-label"><?php echo esc_html($arr['name']);?></span>
							<label class="uap_label_shiwtch uap-access-switch">
								<?php $checked = (in_array($role, $meta_values)) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapMakeInputhString(this, '<?php echo esc_attr($role);?>', '#uap_dashboard_allowed_roles');" <?php echo esc_attr($checked);?>/>
								<div class="switch uap-inline-block"></div>
							</label>
						</div>
					<?php
					$i++;
						if ($count>7 && $i==$break){
						?>
						</div>
						<div class="uap-half-block">
						<?php
						}
					}///end of foreach
				?>
			</div>
			<input type="hidden" name="uap_dashboard_allowed_roles" id="uap_dashboard_allowed_roles" value="<?php echo esc_attr($meta_value);?>" />
			<div id="uap_save_changes" class="uap-wrapp-submit-bttn iump-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

</form>
