<form action="" method="post">

	<div class="uap-stuffbox">

  		<h3 class="uap-h3"><?php esc_html_e('Weekly Email Summary', 'uap');?></h3>
  		<div class="inside">
  			<div class="uap-form-line">
          <h2><?php  esc_html_e('Activate/Hold ', 'uap');?> <?php esc_html_e('Weekly Email Summary', 'uap');?></h2>
          <p><?php esc_html_e('Administrator will receive a weekly report with informations like New registered Affiliates, Total Clicks, Total Earnings, Total Referrals and Conversion Rate.', 'uap'); ?></p>
  				<label class="uap_label_shiwtch uap-checkbox">
  					<?php $checked = ($data['uap_wes_enabled']) ? 'checked' : '';?>
  					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_wes_enabled');" <?php echo $checked;?> />
  					<div class="switch uap_wes"></div>
  				</label>
  				<input type="hidden" name="uap_wes_enabled" value="<?php echo $data[ 'uap_wes_enabled'];?>" id="uap_wes_enabled" />
  			</div>
  	    <div class="uap-line-break"></div>

      	<div class="uap-form-line">
          	<h2><?php esc_html_e('Day of Notification', 'uap');?></h2>
						<p><?php esc_html_e('The chosen day will correspond to the day the administrator will receive the report.', 'uap');?></p>
    				<select name="uap_wes_day_of_week"><?php
              foreach ( $data['days_of_week'] as $dayNumber => $dayName ){
                  $selected = $dayNumber === ( (int)$data['uap_wes_day_of_week'] ) ? 'selected' : '';
                  ?><option value="<?php echo $dayNumber;?>" <?php echo $selected;?> ><?php echo $dayName;?></option><?php
              }
            ?></select>
         </div>

      	 <div class="uap-form-line">
          		<div class="inside">
          				<div id="uap_save_changes" class="uap-submit-form uap-btn">
          						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
          				</div>
          		</div>
      	 </div>

  </div>

</form>
