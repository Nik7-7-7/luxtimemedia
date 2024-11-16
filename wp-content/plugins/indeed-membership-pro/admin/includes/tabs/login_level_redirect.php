<?php
$levels = \Indeed\Ihc\Db\Memberships::getAll();
if (!empty($_POST['ihc_save']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	update_option('ihc_login_level_redirect_on', sanitize_text_field($_POST['ihc_login_level_redirect_on']) );
}
if (!empty($_POST['ihc_login_level_redirect_rules']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	$priorities = array();
	$values = array();
	foreach ($levels as $lid=>$arr){
		if (isset($_POST['ihc_login_level_redirect_rules'][$lid])){
			$values[$lid] = sanitize_text_field($_POST['ihc_login_level_redirect_rules'][$lid]);
		}
		if (isset($_POST['ihc_login_level_redirect_priority'][$lid])){
			$key = sanitize_text_field($_POST['ihc_login_level_redirect_priority'][$lid]);
			while (isset($priorities[$key])){
				$key++;
			}
			$priorities[$key] = $lid;
		}
	}
	update_option('ihc_login_level_redirect_rules', $values);
	if ($priorities){
		$i = 1;
		ksort($priorities);
		$store_value = array();
		foreach ($priorities as $lid){
			$store_value[$i] = $lid;
			$i++;
		}
		update_option('ihc_login_level_redirect_priority', $store_value);
	}
}
$check = get_option('ihc_login_level_redirect_on');
$values = get_option('ihc_login_level_redirect_rules');
$default = get_option('ihc_general_login_redirect');
$priorities = get_option('ihc_login_level_redirect_priority');

echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$pages_arr = ihc_get_all_pages() + ihc_get_redirect_links_as_arr_for_select();
$pages_arr[-1] = '...';
?>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Login Redirects+', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Login Redirects+', 'ihc');?></h2>
				<p><?php esc_html_e('Replace the default redirect after login with a custom one based on the user assigned membership. Because UMP is a MultiMembership system, a user can have multiple memberships assigned but only one redirect can take place. You can set membership priorities to manage that.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($check) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_login_level_redirect_on');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_login_level_redirect_on" value="<?php echo (int)$check;?>" id="ihc_login_level_redirect_on" />
			</div>
			<div class="iump-form-line">
			<p><strong><?php esc_html_e('Important: In order for the custom Login Redirect to work, the membership of the user needs to be active. If expired or on hold, the custom redirect will not work.', 'ihc');?></strong></p>
			</div>
			<?php if ($levels):?>

				<div class="iump-form-line">
					<div class="row ihc-row-no-margin">
						<div class="col-xs-5 ihc-col-no-padding">
				<h2><?php esc_html_e('Custom Redirections:', 'ihc');?></h2>
				<?php foreach ($levels as $id=>$array):?>
					<?php
						$value = (isset($values[$id])) ? $values[$id] : $default;
					?>


					<div class="iump-form-line">
						<div class="input-group"><span class="input-group-addon"><?php echo esc_html($array['label']);?></span>
						<select name="ihc_login_level_redirect_rules[<?php echo esc_attr($id);?>]" class="form-control">
							<?php foreach ($pages_arr as $post_id=>$title):?>
								<?php $selected = ($value==$post_id) ? 'selected' : '';?>
								<option value="<?php echo esc_attr($post_id);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($title);?></option>
							<?php endforeach;?>
						</select>
					</div>
					</div>
				<?php endforeach;?>
				</div>
			</div>
		</div>
			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>
	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Memberships Priorities:', 'ihc');?></h3>
		<div class="inside">
					<p><?php esc_html_e('Because UMP is a MultiMembership system, a user can have multiple memberships assigned but only one redirect can take place. You can set membership priorities to manage that.', 'ihc');?></p>
					<div class="row ihc-row-no-margin">
					 <div class="col-xs-5 ihc-col-no-padding">

					<?php $i = 1;?>
					<?php foreach ($levels as $id=>$array):?>
						<?php
							if ($priorities && is_array($priorities)){
								$key = array_search($id, $priorities);
							}

							if (!empty($key)){
								$priority = $key;
							} else {
								$priority = $i;
							}
						?>
						<div class="iump-form-line">
						<div class="input-group"><span class="input-group-addon"><?php echo esc_html($array['label']);?></span>
						<input type="number" min="1" name="ihc_login_level_redirect_priority[<?php echo esc_attr($id);?>]" value="<?php echo esc_attr($priority);?>" class="form-control" />
						</div>
						</div>
						<?php $i++;?>
					<?php endforeach;?>

			<?php endif;?>

					</div>
				</div>
			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>
