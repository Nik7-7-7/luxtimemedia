<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
    		<h3 class="ulp-h3"><?php esc_html_e('Coming soon Course', 'ulp');?></h3>
    		<div class="inside">
        	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<div class="ulp-form-line">
                				<h2><?php esc_html_e('Activate/Hold Coming soon Course', 'ulp');?></h2>
                        <div><?php esc_html_e('The best way to promote your next coming courses and convinge Students to set them into their WhishList', 'ulp');?></div>
                				<label class="ulp_label_shiwtch ulp-switch-button-margin">
                					<?php $checked = ($data['metas']['ulp_coming_soon_enabled']) ? 'checked' : '';?>
                					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_coming_soon_enabled');" <?php echo esc_attr($checked);?> />
                					<div class="switch ulp-display-inline"></div>
                				</label>
                				<input type="hidden" name="ulp_coming_soon_enabled" value="<?php echo esc_attr($data['metas']['ulp_coming_soon_enabled']);?>" id="ulp_coming_soon_enabled" />
                			</div>

              </div>
            </div>
      </div>
      <div class="ulp-line-break"></div>
	<div class="ulp-inside-item">
              <div class="row">
                  <div class="col-xs-6">
                			<h4><?php esc_html_e('How it works', 'ulp');?></h4>

				<div><?php esc_html_e('Once this module is eanabled you will find an additional box into Special Settings section of each Course.', 'ulp');?></div>
				<div><?php esc_html_e('The Coming Soon brief description will replace the Course content with an additional Count Down listed if is desirable.', 'ulp');?></div>
                <div><?php esc_html_e('When the time expire Course content will become public automatically.', 'ulp');?></div>


              </div>
            </div>
      </div>

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
