<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Public Workflow Settings', 'uap');?></h3>
		<div class="inside">

			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-8">
						<h2><?php esc_html_e('Sources Custom Names', 'uap');?></h2>
						<p><?php esc_html_e("Customize the Referral sources displayed on the Affiliate Dashboard according to your preferences", 'uap');?></p>
						<?php
								$types = [
													'ump' => 'Ultimate Membership Pro',
													'ulp' => 'Ultimate Learning Pro',
													'woo' => 'WooCommerce',
													'edd' => 'Easy Download Digital',
													'bonus' => 'Bonus',
													'mlm' => 'MLM',
													'user_signup' => 'User SignUp',
													'landing_commissions' => 'Landing commisions',
													'ppc' => 'Pay per Click',
													'cpm' => 'CPM Commission',
								];
						?>
						<?php foreach ($types as $name=>$label):?>
								<div class="uap-form-line">
									<div class="input-group">
									<span class="input-group-addon"><?php echo esc_html($label);?></span>
										<input type="text" class="form-control"  value="<?php echo esc_attr($data['metas']['uap_custom_source_name_' . $name]);?>" name="<?php echo esc_attr('uap_custom_source_name_' . $name);?>" />
									</div>
								</div>
						<?php endforeach;?>
					</div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>


		</div>
	</div>
</form>
</div>
