<?php
if (defined('WORDPRESS_SOCIAL_LOGIN_ABS_PATH')){
	$is_set = TRUE;
}

?>
<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('WP Social Login Integration', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/wp-social-login-integration/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>

		<div class="inside">


				<div class="uap-form-line">
					<h2><?php esc_html_e('Activate/Hold WP Social Login Integration', 'uap');?></h2>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_wp_social_login_on']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_wp_social_login_on');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_wp_social_login_on" value="<?php echo esc_attr($data['metas']['uap_wp_social_login_on']);?>" id="uap_wp_social_login_on" />
				</div>

				<?php if (empty($is_set)):?>
					<div class="uap-form-line">
					<?php echo esc_html__("Wp Social Login it's not active on Your system. You can find ", 'uap') . '<a href="https://wordpress.org/plugins/wordpress-social-login/" target="_blank">' . esc_html__('here', 'uap') . '.</a>';?>
					</div>
				<?php else:?>

				<div class="uap-form-line">
					<h2><?php esc_html_e('Login/Register Redirect', 'uap');?></h2>
					<div class="uap-form-line">
						<select name="uap_wp_social_login_redirect_page">
							<?php foreach ($data['pages'] as $post_id=>$title):?>
								<?php $selected = ($data['metas']['uap_wp_social_login_redirect_page']==$post_id) ? 'selected' : '';?>
								<option value="<?php echo esc_attr($post_id);?>" <?php echo esc_attr($selected);?> ><?php echo esc_uap_content($title);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>

				<div class="uap-form-line">
					<h2><?php esc_html_e('WP Role', 'uap');?></h2>
					<div><strong><?php esc_html_e('Predefined Wordpress Role Assign to new Users:', 'uap');?></strong></div>
					<select name="uap_wp_social_login_default_role">
					<?php
						if (empty($data['metas']['uap_wp_social_login_default_role'])){
							$data['metas']['uap_wp_social_login_default_role'] = get_option('uap_register_new_user_role');
						}
						$roles = uap_get_wp_roles_list();
						if ($roles){
							foreach ($roles as $k=>$v){
								$selected = ($data['metas']['uap_wp_social_login_default_role']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						}
					?>
					</select>
				</div>

				<div class="uap-form-line">
					<?php
						if (empty($data['metas']['uap_wp_social_login_default_rank'])){
							$data['metas']['uap_wp_social_login_default_rank'] = get_option('uap_register_new_user_rank');
						}
					?>
					<div><strong><?php esc_html_e('Rank assigned to new User', 'uap');?></strong></div>
					<select name="uap_wp_social_login_default_rank">
						<option value="0" <?php echo ($data['metas']['uap_wp_social_login_default_rank']==-1) ? 'selected' : '';?> ><?php esc_html_e('None', 'uap');?></option>
						<?php
							if ($data['ranks'] && count($data['ranks'])){
								foreach ($data['ranks'] as $key=>$object){
								?>
									<option value="<?php echo esc_attr($object->id);?>" <?php echo ($data['metas']['uap_wp_social_login_default_rank']==$object->id) ? 'selected' : '';?> ><?php echo esc_html($object->label);?></option>
								<?php
								}
							}
						?>
					</select>
				</div>


				<h4>Wordpress Social Login - Shortocode:</h4>
				<div class="uap-user-list-shortcode-wrapp">
					<div class="content-shortcode uap-text-align-center">
						<span class="the-shortcode">[wordpress_social_login]</span>
					</div>
				</div>

				<div>
					<a href="<?php echo admin_url('options-general.php?page=wordpress-social-login');?>"><?php esc_html_e('Wordpress Social Login - Settings', 'uap');?></a>
				</div>

				<div class="uap-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
				</div>

			<?php endif;?>

		</div>
	</div>
</form>
</div>
<?php
