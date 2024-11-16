<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('ihc_cheat_off');//save update metas
}

$data['metas'] = ihc_return_meta_arr('ihc_cheat_off');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Cheat Off', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Cheat Off', 'ihc');?></h2>
				<p><?php esc_html_e('Prevent your customers from sharing their login credentials by keeping only one user logged in at a time. If a new user logs in using the same credentials, the previous one will be logged out and redirected to a "Warning Page".', 'ihc');?></p>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_cheat_off_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_cheat_off_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_cheat_off_enable" value="<?php echo esc_attr($data['metas']['ihc_cheat_off_enable']);?>" id="ihc_cheat_off_enable" />
			</div>

			<div class="iump-form-line">
				<h2><?php esc_html_e('Cookie Expire Time', 'ihc');?></h2>
				<p><?php esc_html_e('A cookie is a small file or part of a file stored on a user computer created and subsequently read by a website server
				containing personal information (such as a user identification code, customized preferences, or a record of pages visited)','ihc');?></p>
				<input type="number" name="ihc_cheat_off_cookie_time" value="<?php echo esc_attr($data['metas']['ihc_cheat_off_cookie_time']);?>" min="1"  /> <?php esc_html_e('Days', 'ihc');?>
			</div>

			<?php
			$pages = ihc_get_all_pages() + ihc_get_redirect_links_as_arr_for_select();
			?>
			<div class="iump-form-line">
				<div class="row ihc-row-no-margin">
				 <div class="col-xs-5 ihc-col-no-padding">

			<h2><?php esc_html_e('Warning Redirect:', 'ihc');?></h2>
				<select name="ihc_cheat_off_redirect">
					<option value="-1" <?php if($data['metas']['ihc_cheat_off_redirect']==-1){
						echo esc_attr('selected');
					}
					?> >...</option>
					<?php
						if ($pages){
							foreach ($pages as $k=>$v){
							?>
								<option value="<?php echo esc_attr($k);?>" <?php if($data['metas']['ihc_cheat_off_redirect']==$k){
									echo esc_attr('selected');
								}
								?> ><?php echo esc_html($v);?></option>
							<?php
							}
						}
					?>
				</select>
			</div>
		</div>
	</div>
			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>
