<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		if ( !empty( $_POST['ihc_save'] ) && isset( $_POST['ihc_pushover_enabled'] ) ){
				if ( empty( $_POST['ihc_pushover_enabled'] ) ){
						\Ihc_Db::deactivateApTab( 'pushover' );
				} else {
						\Ihc_Db::activateApTab( 'pushover' );
				}
		}
		ihc_save_update_metas('pushover');//save update metas
}
$data['metas'] = ihc_return_meta_arr('pushover');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">

	 <input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />
	 
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Pushover Notifications', 'ihc');?></h3>
		<div class="inside">

		  <div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Pushover Notifications', 'ihc');?></h2>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_pushover_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_pushover_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_pushover_enabled" value="<?php echo esc_attr($data['metas']['ihc_pushover_enabled']);?>" id="ihc_pushover_enabled" />
		  </div>
		  <div class="iump-form-line">
			<div class="row">
				<div class="col-xs-4">
			   		<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('App Token', 'ihc');?></span>
						<input type="text" name="ihc_pushover_app_token" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_pushover_app_token']);?>" />
			   		</div>
				</div>
			</div>
		</div>
		<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-4">
			   		<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Admin Personal User Token', 'ihc');?></span>
						<input type="text" name="ihc_pushover_admin_token" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_pushover_admin_token']);?>" />
			   		</div>
					<div>
						<?php esc_html_e("Use this to get 'Admin Notifications' on Your own device.", 'ihc');?>
					</div>
				</div>
			</div>
		</div>
		<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-4">
			   		<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('URL', 'ihc');?></span>
						<input type="text" name="ihc_pushover_url" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_pushover_url']);?>" />
			   		</div>
				</div>
			</div>
		</div>
		<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-4">
			   		<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('URL Title', 'ihc');?></span>
						<input type="text" name="ihc_pushover_url_title" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_pushover_url_title']);?>" />
			   		</div>
				</div>
			</div>
		</div>
		<div class="iump-form-line">
			<div class="row">
				<div>
					<ul class="ihc-info-list">
						<li><?php echo esc_html__("1. Go to ", 'ihc') . '<a href="https://pushover.net/" target="_blank">https://pushover.net/</a>' . esc_html__(" and login with your credentials or sign up for a new account.", 'ihc');?></li>
						<li><?php echo esc_html__("2. After that go to ", 'ihc') . '<a href="https://pushover.net/apps/build" target="_blank">https://pushover.net/apps/build</a>' .  esc_html__(" and create a new App.", 'ihc');?></li>
						<li><?php esc_html_e("3. Set the type of App to 'Application'.", 'ihc');?></li>
						<li><?php esc_html_e("4. Copy and paste API Token/Key.", 'ihc');?></li>
					</ul>
				</div>
			</div>
		 </div>
		 <div class=" ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
	   </div>
	</div>
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Notification Sound', 'ihc');?></h3>
		<div class="inside">
		  <div class="iump-form-line">
				<h4><?php esc_html_e('Default Sound for mobile notification', 'ihc');?></h4>
				<select name="ihc_pushover_sound">
					<?php
						$possible = array(
											'bike' => esc_html__('Bike', 'ihc'),
											'bugle' => esc_html__('Bugle', 'ihc'),
											'cash_register' => esc_html__('Cash Register', 'ihc'),
											'classical' => esc_html__('Classical', 'ihc'),
											'cosmic' => esc_html__('Cosmic', 'ihc'),
											'falling' => esc_html__('Falling', 'ihc'),
											'gamelan' => esc_html__('Gamelan', 'ihc'),
											'incoming' => esc_html__('Incoming', 'ihc'),
											'intermission' => esc_html__('Intermission', 'ihc'),
											'magic' => esc_html__('Magic', 'ihc'),
											'mechanical' => esc_html__('Mechanical', 'ihc'),
											'piano_bar' => esc_html__('Piano Bar', 'ihc'),
											'siren' => esc_html__('Siren', 'ihc'),
											'space_alarm' => esc_html__('Space Alarm', 'ihc'),
											'tug_boat' => esc_html__('Tug Boat', 'ihc'),
						);
					?>
					<?php foreach ($possible as $k=>$v):?>
						<?php $selected = ($data['metas']['ihc_pushover_sound']==$k) ? 'selected' : '';?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
					<?php endforeach;?>
 				</select>
		</div>



			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

</form>
