<div class="uap-wrapper">
	<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Pushover Notifications', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/pushover-notifications/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">

			<div class="uap-form-line">
				<h2><?php esc_html_e('Activate/Hold Pushover Notifications', 'uap');?></h2>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_pushover_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_pushover_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_pushover_enabled" value="<?php echo esc_attr($data['metas']['uap_pushover_enabled']);?>" id="uap_pushover_enabled" />
			</div>
			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('App Token', 'uap');?></span>
							<input type="text" class="uap-field-text-with-padding form-control" name="uap_pushover_app_token" value="<?php echo esc_attr($data['metas']['uap_pushover_app_token']);?>" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('Admin Personal User Token', 'uap');?></span>
							<input type="text" class="uap-field-text-with-padding form-control" name="uap_pushover_admin_token" value="<?php echo esc_attr($data['metas']['uap_pushover_admin_token']);?>" />
						</div>
						<div>
							<?php esc_html_e("Use this to get 'Admin Notifications' on your own device.", 'uap');?>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('URL', 'uap');?></span>
							<input type="text" class="uap-field-text-with-padding form-control" name="uap_pushover_url" value="<?php echo esc_url($data['metas']['uap_pushover_url']);?>" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon"><?php esc_html_e('URL Title', 'uap');?></span>
							<input type="text" class="uap-field-text-with-padding form-control" name="uap_pushover_url_title" value="<?php echo esc_attr($data['metas']['uap_pushover_url_title']);?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="row">
					<div>
						<ul class="uap-info-list">
							<li><?php echo esc_html__("1. Go to ", 'uap') . '<a href="https://pushover.net/" target="_blank">https://pushover.net/</a>' . esc_html__(" login with your credentials or sign up for a new account.", 'uap');?></li>
							<li><?php echo esc_html__("2. After that go to ", 'uap') . '<a href="https://pushover.net/apps/build" target="_blank">https://pushover.net/apps/build</a>' .  esc_html__(" and create new App.", 'uap');?></li>
							<li><?php esc_html_e("3. Set the type of App at 'Application'.", 'uap');?></li>
							<li><?php esc_html_e("4. Copy and paste API Token/Key.", 'uap');?></li>
						</ul>
					</div>
				</div>
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="uap_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Notification Sound', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h4><?php esc_html_e('Default Sound for mobile notification', 'uap');?></h4>
				<select name="uap_pushover_sound">
					<?php
						$possible = array(
											'bike' => esc_html__('Bike', 'uap'),
											'bugle' => esc_html__('Bugle', 'uap'),
											'cash_register' => esc_html__('Cash Register', 'uap'),
											'classical' => esc_html__('Classical', 'uap'),
											'cosmic' => esc_html__('Cosmic', 'uap'),
											'falling' => esc_html__('Falling', 'uap'),
											'gamelan' => esc_html__('Gamelan', 'uap'),
											'incoming' => esc_html__('Incoming', 'uap'),
											'intermission' => esc_html__('Intermission', 'uap'),
											'magic' => esc_html__('Magic', 'uap'),
											'mechanical' => esc_html__('Mechanical', 'uap'),
											'piano_bar' => esc_html__('Piano Bar', 'uap'),
											'siren' => esc_html__('Siren', 'uap'),
											'space_alarm' => esc_html__('Space Alarm', 'uap'),
											'tug_boat' => esc_html__('Tug Boat', 'uap'),
						);
					?>
					<?php foreach ($possible as $k=>$v):?>
						<?php $selected = ($data['metas']['uap_pushover_sound']==$k) ? 'selected' : '';?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
					<?php endforeach;?>
 				</select>
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="uap_save" class="button button-primary button-large" />
			</div>
		</div>
	</div>

</form>
</div>
