<?php
$levels = \Indeed\Ihc\Db\Memberships::getAll();
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		if (!empty($_POST['ihc_save'])){
			update_option('ihc_register_redirects_by_level_enable', sanitize_text_field($_POST['ihc_register_redirects_by_level_enable']) );
		}
		if (!empty($_POST['ihc_register_redirects_by_level_rules'])){
			$priorities = array();
			$values = array();
			foreach ($levels as $lid=>$arr){
				if (isset($_POST['ihc_register_redirects_by_level_rules'][$lid])){
					$values[$lid] = sanitize_text_field($_POST['ihc_register_redirects_by_level_rules'][$lid]);
				}

			}
			update_option('ihc_register_redirects_by_level_rules', $values);
		}
}

$check = get_option('ihc_register_redirects_by_level_enable');
$values = get_option('ihc_register_redirects_by_level_rules');
$default = get_option('ihc_general_register_redirect');

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
		<h3 class="ihc-h3"><?php esc_html_e('Register Redirects+', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Register Redirects+ action', 'ihc');?></h2>
				<p><?php esc_html_e('Replace the default redirect after register with a custom one based on the user assigned membership.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($check) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_register_redirects_by_level_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_register_redirects_by_level_enable" value="<?php echo (int)$check;?>" id="ihc_register_redirects_by_level_enable" />
			</div>

			<?php if ($levels):?>
				<div class="iump-form-line">
				<h2><?php esc_html_e('Custom Redirections:', 'ihc');?></h2>
				<div class="row ihc-row-no-margin">
				 <div class="col-xs-5 ihc-col-no-padding">
				<?php foreach ($levels as $id=>$array):?>
					<?php
						$value = (isset($values[$id])) ? $values[$id] : $default;
					?>
					<div class="iump-form-line">
						<div class="input-group"><span class="input-group-addon"><?php echo esc_html($array['label']);?></span>
						<select name="ihc_register_redirects_by_level_rules[<?php echo esc_attr($id);?>]" class="form-control">
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
      <?php endif;?>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

</form>
