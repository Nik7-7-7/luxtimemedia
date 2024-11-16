<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('kissmetrics');//save update metas	
}

$data['metas'] = ihc_return_meta_arr('kissmetrics');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>

<div class="ihc-kissmetrics-settings-wrapper">
<form method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Kissmetrics Integration', 'ihc');?></h3>

		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Kissmetrics Integration', 'ihc');?></h2>
				<p><?php esc_html_e('Track multiple membership events and User actions with Kissmetrics service ', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_kissmetrics_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_kissmetrics_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_kissmetrics_enabled" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_enabled']);?>" id="ihc_kissmetrics_enabled" />
			</div>
    <div class="iump-form-line">
    	<div class="input-group">
        <span class="input-group-addon" ><?php esc_html_e('Api Key', 'ihc');?></span>
        <input type="text" name="ihc_kissmetrics_apikey" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_apikey']);?>" id="ihc_kissmetrics_apikey" />
		</div>
      </div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Events', 'ihc');?></h3>
		<div class="inside">
     <div class="iump-form-line">
				<h4><?php esc_html_e('User Register', 'ihc');?></h4>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_kissmetrics_events_user_register']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_kissmetrics_events_user_register');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>

				<input type="hidden" name="ihc_kissmetrics_events_user_register" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_register']);?>" id="ihc_kissmetrics_events_user_register" />

        <div class="input-group">
        <span class="input-group-addon"><?php esc_html_e('Event Label', 'ihc');?></span>
          <input type="text" name="ihc_kissmetrics_events_user_register_label" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_register_label']);?>" id="ihc_kissmetrics_events_user_register_label" />
        </div>
      </div>

      <div class="iump-form-line">
				<h4><?php esc_html_e('User get new Membership', 'ihc');?></h4>

				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_kissmetrics_events_user_get_level']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_kissmetrics_events_user_get_level');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_kissmetrics_events_user_get_level" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_get_level']);?>" id="ihc_kissmetrics_events_user_get_level" />

        <div class="input-group">
        <span class="input-group-addon"><?php esc_html_e('Event Label', 'ihc');?></span>
          <input type="text" name="ihc_kissmetrics_events_user_get_level_label" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_get_level_label']);?>" class="form-control" id="ihc_kissmetrics_events_user_get_level_label" />
        </div>
      </div>

      <div class="iump-form-line">
				<h4><?php esc_html_e('User finish Payment', 'ihc');?></h4>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_kissmetrics_events_user_finish_payment']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_kissmetrics_events_user_finish_payment');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_kissmetrics_events_user_finish_payment" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_finish_payment']);?>" id="ihc_kissmetrics_events_user_finish_payment" />

        <div class="input-group">
        <span class="input-group-addon"><?php esc_html_e('Event Label', 'ihc');?></span>
          <input type="text" name="ihc_kissmetrics_events_user_finish_payment_label" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_finish_payment_label']);?>" class="form-control" id="ihc_kissmetrics_events_user_finish_payment_label" />
        </div>
      </div>

      <div class="iump-form-line">
				<h4><?php esc_html_e('User login', 'ihc');?></h4>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_kissmetrics_events_user_login']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_kissmetrics_events_user_login');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_kissmetrics_events_user_login" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_login']);?>" id="ihc_kissmetrics_events_user_login" />

        <div class="input-group">
        <span class="input-group-addon"><?php esc_html_e('Event Label', 'ihc');?></span>
          <input type="text" name="ihc_kissmetrics_events_user_login_label" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_user_login_label']);?>" class="form-control" id="ihc_kissmetrics_events_user_login_label" />
        </div>
      </div>

  <div class="iump-form-line">
      <h4><?php esc_html_e('Remove Membership from user', 'ihc');?></h4>

      <label class="iump_label_shiwtch ihc-switch-button-margin">
          <?php $checked = ($data['metas']['ihc_kissmetrics_events_remove_user_level']) ? 'checked' : '';?>
          <input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_kissmetrics_events_remove_user_level');" <?php echo esc_attr($checked);?> />
          <div class="switch ihc-display-inline"></div>
      </label>
      <input type="hidden" name="ihc_kissmetrics_events_remove_user_level" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_remove_user_level']);?>" id="ihc_kissmetrics_events_remove_user_level" />

<div class="input-group">
        <span class="input-group-addon"><?php esc_html_e('Event Label', 'ihc');?></span>
<input type="text" name="ihc_kissmetrics_events_remove_user_level_label" value="<?php echo esc_attr($data['metas']['ihc_kissmetrics_events_remove_user_level_label']);?>" class="form-control" id="ihc_kissmetrics_events_remove_user_level_label" />
</div>
</div>

      <div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

</form>
</div>
<?php
