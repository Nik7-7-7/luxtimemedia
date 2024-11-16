<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('zapier');//save update metas
}

$data['metas'] = ihc_return_meta_arr('zapier');//getting metas

echo ihc_check_default_pages_set();//set default pages message

echo ihc_check_payment_gateways();

echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>

<form class="ihc-zapier-wrapper" method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Zapier Integration', 'ihc');?></h3>

		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Zapier Integration', 'ihc');?></h2>
				<p><?php esc_html_e('Connect Ultimate Membership Pro with other apps via Zapier platform. A "Trigger" will send data to Zapier when changes are in action on your website.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_zapier_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_zapier_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_zapier_enabled" value="<?php echo esc_attr($data['metas']['ihc_zapier_enabled']);?>" id="ihc_zapier_enabled" />
			</div>
			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Zapier Triggers', 'ihc');?></h3>
	<div class="inside">

      <div class="iump-form-line">
        <h4><?php esc_html_e('New user', 'ihc');?></h4>
        <p><?php esc_html_e('When a new user is registered into your website data are sent to Zapier.', 'ihc');?></p>
		<div>
        <label class="iump_label_shiwtch ihc-switch-button-margin">
          <?php $checked = ($data['metas']['ihc_zapier_new_user_enabled']) ? 'checked' : '';?>
          <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_zapier_new_user_enabled');" <?php echo esc_attr($checked);?> />
          <div class="switch ihc-display-inline"></div>
        </label>
        </div>
         <input type="text" name="ihc_zapier_new_user_webhook" value="<?php echo esc_attr($data['metas']['ihc_zapier_new_user_webhook']);?>" id="ihc_zapier_new_user_webhook" />
		<input type="hidden" name="ihc_zapier_new_user_enabled" value="<?php echo esc_attr($data['metas']['ihc_zapier_new_user_enabled']);?>" id="ihc_zapier_new_user_enabled" />
      </div>

      <div class="iump-form-line">
        <h4><?php esc_html_e('New order', 'ihc');?></h4>
        <p><?php esc_html_e('When a new Order is created on Ultimate Membership Pro data are sent to Zapier.', 'ihc');?></p>
		<div>
        <label class="iump_label_shiwtch ihc-switch-button-margin">
          <?php $checked = ($data['metas']['ihc_zapier_new_order_enabled']) ? 'checked' : '';?>
          <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_zapier_new_order_enabled');" <?php echo esc_attr($checked);?> />
          <div class="switch ihc-display-inline"></div>
        </label>
		</div>

        <input type="text" name="ihc_zapier_new_order_webhook" value="<?php echo esc_attr($data['metas']['ihc_zapier_new_order_webhook']);?>" id="ihc_zapier_new_order_webhook" />
		<input type="hidden" name="ihc_zapier_new_order_enabled" value="<?php echo esc_attr($data['metas']['ihc_zapier_new_order_enabled']);?>" id="ihc_zapier_new_order_enabled" />
      </div>


      <div class="iump-form-line">
        <h4><?php esc_html_e('Order completed', 'ihc');?></h4>
        <p><?php esc_html_e('When an exitent Order is set as Completed data are sent to Zapier.', 'ihc');?></p>
        <div>
        <label class="iump_label_shiwtch ihc-switch-button-margin">
          <?php $checked = ($data['metas']['ihc_zapier_order_completed_enabled']) ? 'checked' : '';?>
          <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_zapier_order_completed_enabled');" <?php echo esc_attr($checked);?> />
          <div class="switch ihc-display-inline"></div>
        </label>
        </div>
		<input type="text" name="ihc_zapier_order_completed_webhook" value="<?php echo esc_attr($data['metas']['ihc_zapier_order_completed_webhook']);?>" id="ihc_zapier_order_completed_webhook" />
		<input type="hidden" name="ihc_zapier_order_completed_enabled" value="<?php echo esc_attr($data['metas']['ihc_zapier_order_completed_enabled']);?>" id="ihc_zapier_order_completed_enabled" />
      </div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>

<?php
