<?php
$siteUrl = site_url();
$siteUrl = trailingslashit($siteUrl);
 ?>
<form  method="post">
  <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('PayPal Payment Integration', 'ulp');?></h3>
		<div class="inside">

			<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
				<h2><?php esc_html_e('Main PayPal settings', 'ulp');?></h2>

                <div class="input-group ulp-input-group-max ulp-input-group-space">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('E-mail address', 'ulp');?></span>
				    <input type="text" class="form-control"  name="ulp_paypal_email" value="<?php echo esc_attr($data['metas']['ulp_paypal_email']);?>" id="ulp_paypal_email" />
			      </div>

						<div class="ulp-form-line">
              <p>Please enter your PayPal Email address. This is required in order to take payments via PayPal.</p>
              </div>
							<h4 class="ulp-input-group-space"><?php esc_html_e('How to setup', 'ulp');?></h4>
									<div class="ulp-setup-steps-wrapper">
									<div>1. <?php esc_html_e('Login to ', 'ulp');?> <a target="_blank" href="https://www.paypal.com/">paypal.com</a></div>
									<div>2. <?php esc_html_e('Go to Account Settings -> Seller Tools', 'ulp'); ?></div>
									<div>3. <?php esc_html_e('Look after Instant payment notifications and update it with ', 'ulp'); ?><a target="_blank" href="<?php echo esc_url($siteUrl . '?ulp_action=paypal');?>"><?php echo esc_url($siteUrl . '?ulp_action=paypal');?></a></p></div>
									</div>
                 <h4  class="ulp-input-group-space"><?php esc_html_e('SandBox Mode', 'ulp');?></h4>
                <label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_paypal_sandbox']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_paypal_sandbox');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_paypal_sandbox" value="<?php echo esc_attr($data['metas']['ulp_paypal_sandbox']);?>" id="ulp_paypal_sandbox" />
				<div class="ulp-form-line">
					<p><?php esc_html_e('PayPal sandbox mode can be used to testing purpose. A Sandbox merchant account and additional Sandbox buyer account is required.', 'ulp');?></p>
				</div>
        <div class="ulp-setup-steps-wrapper">
          <div><?php esc_html_e('1. Log in to', 'ulp');?> <a target="_blank" href="https://www.sandbox.paypal.com/">sandbox.paypal.com</a></div>
          <div><?php esc_html_e('2. Go to user Account Settings and go to Notifications from Business Profile.', 'ulp');?></div>
          <div><?php esc_html_e('3. Update Instant payment notifications with the same url:', 'ulp');?> <?php echo esc_ulp_content('<b>'.$siteUrl . '?ulp_action=paypal'.'</b>' . __(' and turn ON.', 'ulp'));?></div>
        </div>
				 </div>

              </div>
            </div>
            <div class="ulp-line-break"></div>

      	   <div class="ulp-inside-item ulp-input-group-space">
            <div class="row">
               <div class="col-xs-6">
               <h2><?php esc_html_e('Display options', 'ulp');?></h2>
			<div class="input-group ulp-input-group-max ulp-input-group-space">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label', 'ulp');?></span>
					<input type="text" class="form-control" name="ulp_paypal_label" value="<?php echo stripslashes($data['metas']['ulp_paypal_label']);?>" id="ulp_paypal_label" />
			</div>
			<div class="input-group ulp-input-group-max ulp-input-group-space">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Description', 'ulp');?></span>
					<input type="text" class="form-control" name="ulp_paypal_description" value="<?php echo stripslashes($data['metas']['ulp_paypal_description']);?>" id="ulp_paypal_description" />
			</div>
			<div class="input-group ulp-input-group-max ulp-input-group-space">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Multi-Payment order', 'ulp');?></span>
					<input type="number" min="1" class="form-control" name="ulp_paypal_multipayment_order" value="<?php echo esc_attr($data['metas']['ulp_paypal_multipayment_order']);?>" id="ulp_paypal_multipayment_order" />
			</div>
				</div>

            </div>
           </div>
					 <div class="ulp-submit-form">
		 				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
		 			</div>
          </div>
		</div>

</form>
