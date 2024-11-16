<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Stripe Payment Integration', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Stripe Payment Integration', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_stripe_payment_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_stripe_payment_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_stripe_payment_enable" value="<?php echo esc_attr($data['metas']['ulp_stripe_payment_enable']);?>" id="ulp_stripe_payment_enable" />
			</div>

			<div class="ulp-form-line">
				<div><?php esc_html_e('This feature integrates the direct payment through Stripe.', 'ulp');?></div>
	      <div><?php esc_html_e('You can find the settings in the following section:', 'ulp');?> <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_stripe');?>"><?php esc_html_e('Settings', 'ulp');?></a></div>
			</div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>
		 </div>
       </div>
      </div>
		</div>
	</div>


</form>
<?php
