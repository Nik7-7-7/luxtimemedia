<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Pushover Notifications', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Pushover notifications', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_pushover_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_pushover_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_pushover_enable" value="<?php echo esc_attr($data['metas']['ulp_pushover_enable']);?>" id="ulp_pushover_enable" />
			</div>
            </div>
          </div>
      </div>
      <div class="ulp-line-break"></div>
      <div class="ulp-inside-item">
          <div class="row">
              <div class="col-xs-4">
              <h4><?php esc_html_e('Credentials settings', 'ulp');?></h4>
      <div class="input-group ulp-input-group-space">

          <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('App Token', 'ulp');?></span>
          <input type="text" class="form-control" name="ulp_pushover_app_token" value="<?php echo esc_attr($data['metas']['ulp_pushover_app_token']);?>" />
      </div>

      <div class="input-group ulp-input-group-space">

          <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Admin Personal User Token', 'ulp');?></span>
          <input type="text" class="form-control" name="ulp_pushover_app_token" value="<?php echo esc_attr($data['metas']['ulp_pushover_app_token']);?>" />
      </div>
       <p><?php esc_html_e("Use this to get 'Admin Notifications' on Your own device.", 'ulp');?></p>

      <div class="input-group ulp-input-group-space">

          <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('URL', 'ulp');?></span>
          <input type="text" class="form-control" name="ulp_pushover_url" value="<?php echo stripslashes($data['metas']['ulp_pushover_url']);?>" />
      </div>

      <div class="input-group ulp-input-group-space">

          <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('URL title', 'ulp');?></span>
          <input type="text" class="form-control" name="ulp_pushover_url_title" value="<?php echo stripslashes($data['metas']['ulp_pushover_url_title']);?>" />
      </div>

      <div class="ulp-setup-steps-wrapper">
          <div><?php echo esc_html__('1. Go to ', 'ulp') . '<a href="https://pushover.net/" target="_blank">https://pushover.net/</a>' . esc_html__(' and login with your credentials or sign up for a new account.', 'ulp');?></div>
          <div><?php echo esc_html__('2. After that go to ', 'ulp') . '<a href="https://pushover.net/apps/build" target="_blank">https://pushover.net/apps/build</a>' . esc_html__(' and create a new App.', 'ulp');?></div>
          <div><?php esc_html_e("3. Set the type of App to 'Application'.", 'ulp');?></div>
          <div><?php esc_html_e('4. Copy and paste API Token/Key.', 'ulp');?></div>
      </div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>
			</div>
		 </div>
       </div>
		</div>
	</div>

  <div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Notification Sound', 'ulp');?></h3>
		<div class="inside">
          <h4><?php esc_html_e('Default Sound for mobile notification', 'ulp');?></h4>
      <div class="ulp-form-line">
          <select name="ulp_pushover_sound">
  					<?php
  						$possible = array(
  											'bike' => esc_html__('Bike', 'ulp'),
  											'bugle' => esc_html__('Bugle', 'ulp'),
  											'cash_register' => esc_html__('Cash Register', 'ulp'),
  											'classical' => esc_html__('Classical', 'ulp'),
  											'cosmic' => esc_html__('Cosmic', 'ulp'),
  											'falling' => esc_html__('Falling', 'ulp'),
  											'gamelan' => esc_html__('Gamelan', 'ulp'),
  											'incoming' => esc_html__('Incoming', 'ulp'),
  											'intermission' => esc_html__('Intermission', 'ulp'),
  											'magic' => esc_html__('Magic', 'ulp'),
  											'mechanical' => esc_html__('Mechanical', 'ulp'),
  											'piano_bar' => esc_html__('Piano Bar', 'ulp'),
  											'siren' => esc_html__('Siren', 'ulp'),
  											'space_alarm' => esc_html__('Space Alarm', 'ulp'),
  											'tug_boat' => esc_html__('Tug Boat', 'ulp'),
  						);
  					?>
  					<?php foreach ($possible as $k=>$v):?>
  						<?php $selected = ($data['metas']['ulp_pushover_sound']==$k) ? 'selected' : '';?>
  						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
  					<?php endforeach;?>
   				</select>
      </div>


      <div class="ulp-submit-form">
          <input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
      </div>

    </div>
  </div>

</form>
<?php
