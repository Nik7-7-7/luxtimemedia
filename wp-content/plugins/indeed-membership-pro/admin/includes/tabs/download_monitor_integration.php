<?php
$levels = array();
$levels = \Indeed\Ihc\Db\Memberships::getAll();

if(!empty($levels) && is_array($levels)){
	$levels = array('reg' => array('label' => esc_html__('Users with no active Membership', 'ihc'))) + $levels;
}else{
	$levels = array('reg' => array('label' => esc_html__('Users with no active Membership', 'ihc')));
}

if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('download_monitor_integration');//save update metas
		if (isset($_POST['ihc_save'])){
			if ($levels){
				$array = array();
				foreach ($levels as $id=>$level){
					if (isset($_POST['level_' . $id])){
						$array['level_' . $id ] = sanitize_text_field($_POST['level_' . $id ]);
					}
				}
				update_option('ihc_download_monitor_values', $array);
			}
		}
}

$data['metas'] = ihc_return_meta_arr('download_monitor_integration');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Download Monitor Integration', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Download Monitor Integration', 'ihc');?></h2>
				<p><?php esc_html_e('Limit the number of downloads (per file or per user) for each membership / subscription.', 'ihc');?></p>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_download_monitor_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_download_monitor_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_download_monitor_enabled" value="<?php echo esc_attr($data['metas']['ihc_download_monitor_enabled']);?>" id="ihc_download_monitor_enabled" />
			<p><strong>Requires <a href="https://wordpress.org/plugins/download-monitor/" target="_blank">Download Monitor</a> Plugin installed and active.</strong></p>
			</div>

			<div class="iump-form-line">
				<h2><?php esc_html_e('Limit Type', 'ihc');?></h2>
				<select name="ihc_download_monitor_limit_type">
					<option value="files" <?php if ($data['metas']['ihc_download_monitor_limit_type']=='files'){
						 echo esc_attr('selected');
					}
					?> ><?php esc_html_e('Downloaded Files', 'ihc');?></option>
					<option value="downloads" <?php if ($data['metas']['ihc_download_monitor_limit_type']=='downloads'){
						 echo esc_attr('selected');
					}
					?> ><?php esc_html_e('Total Downloads', 'ihc');?></option>
				</select>
			</div>

			<div class="iump-form-line">
				<?php if (!empty($levels)):?>
					<h2><?php esc_html_e('Memberships limits', 'ihc');?></h2>
					<?php foreach ($levels as $id => $level):?>
					<div class="row">
						<div class="col-xs-5">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php echo esc_attr($level['label']);?></span>
								<?php
									$value = '';
									if (isset($data['metas']['ihc_download_monitor_values']['level_' . $id ])){
										$value = $data['metas']['ihc_download_monitor_values']['level_' . $id ];
									}
								?>
								<input type="number" class="form-control" value="<?php echo esc_attr($value);?>" name="<?php echo esc_attr('level_' . $id);?>" min="0" />
							</div>
						</div>
					</div>

					<?php endforeach;?>
				<?php endif;?>
			</div>
			<div class="ihc-wrapp-submit-bttn iump-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>
</div>
<?php
