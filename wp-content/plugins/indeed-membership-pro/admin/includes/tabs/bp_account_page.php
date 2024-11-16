<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('ihc_bp');//save update metas
}

$data['metas'] = ihc_return_meta_arr('ihc_bp');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />
	
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('BuddyPress Account Page Integration', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
					<h2><?php esc_html_e('Activate/Hold BuddyPress Account Page Integration', 'ihc');?></h2>
					<p><?php esc_html_e('Fully integrate a user account with their "BuddyPress Account Page". Once activated, a new tab in their "BuddyPress" menu will show up.', 'ihc');?></p>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_bp_account_page_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_bp_account_page_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_bp_account_page_enable" value="<?php echo esc_attr($data['metas']['ihc_bp_account_page_enable']);?>" id="ihc_bp_account_page_enable" />
			</div>

			<div class="iump-form-line">
				<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Menu Label', 'ihc');?></span>
					<input type="text" class="form-control" name="ihc_bp_account_page_name" value="<?php echo esc_attr($data['metas']['ihc_bp_account_page_name']);?>" />
					</div>
				</div>
				</div>
			</div>
			<div class="iump-form-line">
				<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Menu Position', 'ihc');?></span>
					<input type="number" class="form-control" name="ihc_bp_account_page_position" value="<?php echo esc_attr($data['metas']['ihc_bp_account_page_position']);?>" min=1 />
					</div>
				</div>
				</div>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>
