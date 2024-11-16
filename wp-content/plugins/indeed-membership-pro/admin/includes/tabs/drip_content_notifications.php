<?php
if (!empty($_GET['do_cleanup_logs']) && isset( $_GET['older_then'] ) && $_GET['older_then']  !== '' ){
	$older_then = indeed_get_unixtimestamp_with_timezone() - sanitize_text_field($_GET['older_then']) * 24 * 60 * 60;
	Ihc_Db::delete_logs('drip_content_notifications', $older_then);
}
ihc_save_update_metas('drip_content_notifications');//save update metas
$data['metas'] = ihc_return_meta_arr('drip_content_notifications');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Drip Content Notifications', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Drip Content Notifications', 'ihc');?></h2>
            <p><?php esc_html_e('Alert members when a new post is released by "Drip Content" strategy.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_drip_content_notifications_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_drip_content_notifications_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_drip_content_notifications_enabled" value="<?php echo esc_attr($data['metas']['ihc_drip_content_notifications_enabled']);?>" id="ihc_drip_content_notifications_enabled" />
			</div>



							<div class="row ihc-row-no-margin">
								<div class="col-xs-5 ihc-col-no-padding">
									<div class="iump-form-line input-group">
									<span class="input-group-addon"><?php esc_html_e('Time between notifications', 'ihc');?></span>
									<input type="number" min="0"  class="form-control" name="ihc_drip_content_notifications_sleep" value="<?php echo esc_attr($data['metas']['ihc_drip_content_notifications_sleep']);?>" />
									<div class="input-group-addon"><?php esc_html_e('Seconds', 'ihc');?></div>
							</div>
						</div>
			</div>
			<div class="iump-form-line">
            	<h4><?php esc_html_e('Proceed the Notification script manually', 'ihc');?></h4>
				<span onClick="ihcRunAjaxProcess('drip_content_notifications');"  class="button button-primary button-large" target="_blank"><?php esc_html_e('Run Now', 'ihc');?></span>
				<div><span class="spinner" id="ihc_ajax_run_process_spinner"></span></div>
			</div>

			<div class="iump-form-line">
				<h4><?php esc_html_e('Activate/Hold Drip notification Logs', 'ihc');?></h4>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_drip_content_notifications_logs_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_drip_content_notifications_logs_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_drip_content_notifications_logs_enabled" value="<?php echo esc_attr($data['metas']['ihc_drip_content_notifications_logs_enabled']);?>" id="ihc_drip_content_notifications_logs_enabled" />
			</div>

			<?php $we_have_logs = Ihc_User_Logs::get_count_logs('drip_content_notifications');?>
			<?php if ($we_have_logs):?>
				<div class="iump-form-line">
					<div class="row ihc-row-no-margin">
					 <div class="col-xs-5 ihc-col-no-padding">
						 <div class="input-group"><span class="input-group-addon"><?php esc_html_e('Clean Up Logs older than:', 'ihc');?></span>
							 <select id="older_then_select" class="form-control">
						<option value="">...</option>
						<option value="1"><?php esc_html_e('One Day', 'ihc');?></option>
						<option value="7"><?php esc_html_e('One Week', 'ihc');?></option>
						<option value="30"><?php esc_html_e('One Month', 'ihc');?></option>
					</select></div>
					<div class="button button-primary button-large" onClick="ihcDoCleanUpLogs('<?php echo admin_url('admin.php?page=ihc_manage&tab=drip_content_notifications');?>');"><?php esc_html_e('Clean Up', 'ihc');?></div>

				</div>
			</div>
				</div>
			</div>
				<div class="iump-form-line">
					<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=view_drip_content_notifications_logs');?>" target="_blank"><?php esc_html_e('View Logs', 'ihc');?></a>
				</div>
			<?php endif;?>


			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>

</form>
