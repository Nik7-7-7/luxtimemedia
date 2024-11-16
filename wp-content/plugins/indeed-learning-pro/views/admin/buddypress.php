<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('BuddyPress Integration', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate/Hold BuddyPress Integration', 'ulp');?></h2>
                    <p><?php esc_html_e('This feature will add a new tab in your BuddyPress Public Profile.', 'ulp');?></p>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_buddypress_integration_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_buddypress_integration_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_buddypress_integration_enable" value="<?php echo esc_attr($data['metas']['ulp_buddypress_integration_enable']);?>" id="ulp_buddypress_integration_enable" />
			</div>
			</div>
		 </div>
       </div>
            <div class="ulp-line-break"></div>

		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
            <h4><?php esc_html_e('BuddyPress Menu settings', 'ulp');?></h4>
			<p><?php esc_html_e('You can decide the menu position, this tab will give you access to Ultimate Learning Pro Student Profile.', 'ulp');?></p>
      <div class="input-group ulp-input-group-max">
          <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label', 'ulp');?></span>
          <input type="text" class="form-control" name="ulp_buddypress_menu_label" value="<?php echo stripslashes($data ['metas']['ulp_buddypress_menu_label']);?>" />
      </div>

      <div class="input-group ulp-input-group-max ulp-input-group-space">
          <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Menu position', 'ulp');?></span>
          <input type="number" class="form-control" min="1" name="ulp_buddypress_menu_possition" value="<?php echo esc_attr($data ['metas']['ulp_buddypress_menu_possition']);?>" />
      </div>
			</div>
		 </div>
       </div>
            <div class="ulp-line-break"></div>

		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>
			</div>
		 </div>
       </div>
		</div>
	</div>

</form>
