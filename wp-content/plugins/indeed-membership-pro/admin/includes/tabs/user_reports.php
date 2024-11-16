<?php
if (!empty($_GET['do_cleanup_logs'])){
	$older_then = indeed_get_unixtimestamp_with_timezone() - sanitize_text_field($_GET['older_then']) * 24 * 60 * 60;
	Ihc_Db::delete_logs('user_logs', $older_then);
}
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	ihc_save_update_metas('user_reports');//save update metas
}
$data['metas'] = ihc_return_meta_arr('user_reports');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Members Reports', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">

				<h2><?php esc_html_e('Activate/Hold Members Reports', 'ihc');?></h2>
				<p><?php esc_html_e('With this action activated you may follow members activities such as membership assignation, orders placed or coupons they used.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_user_reports_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_user_reports_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_user_reports_enabled" value="<?php echo esc_attr($data['metas']['ihc_user_reports_enabled']);?>" id="ihc_user_reports_enabled" />
			</div>
			<div class="iump-form-line">
				<p><?php esc_html_e('Once activated a new "User Reports" button will be available in ','ihc');?> <a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=users');?>" target="_blank"><?php esc_html_e(' Members ','ihc');?></a><?php esc_html_e(' listing table for those members who registered or acquired one or more memberships.','ihc');?></p>
			</div>
			<div class="row ihc-row-no-margin">
			 <div class="col-xs-5 ihc-col-no-padding">
			<?php $we_have_logs = Ihc_User_Logs::get_count_logs('user_logs');?>
			<?php if ($we_have_logs):?>
				<div class="iump-form-line">
					<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Clean Up Users Reports older then:', 'ihc');?></span>
					<select id="older_then_select" class="form-control">
						<option value="">...</option>
						<option value="1"><?php esc_html_e('One Day', 'ihc');?></option>
						<option value="7"><?php esc_html_e('One Week', 'ihc');?></option>
						<option value="30"><?php esc_html_e('One Month', 'ihc');?></option>
					</select></div>
				</div>
				<div class="iump-form-line">
				<div class="button button-primary button-large" onClick="ihcDoCleanUpLogs('<?php echo admin_url('admin.php?page=ihc_manage&tab=user_reports');?>');"><?php esc_html_e('Clean Up', 'ihc');?></div>
			</div>
			</div>
		</div>
				<div class="iump-form-line">
					<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=view_user_logs&type=user_logs');?>" target="_blank"><?php esc_html_e('View All User Reports', 'ihc');?></a>
				</div>
			<?php endif;?>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>



</form>
