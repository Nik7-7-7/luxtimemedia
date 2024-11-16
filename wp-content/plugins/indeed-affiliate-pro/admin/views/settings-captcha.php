<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('ReCaptcha Setup', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-8">
				<h4><?php esc_html_e( 'Recaptcha Integration', 'uap' );?></h4>
				<select name="uap_recaptcha_version" class="js-uap-change-recaptcha-version form-control" >
							<?php
									if ( empty( $data['metas']['uap_recaptcha_version'] ) ){
											$data['metas']['uap_recaptcha_version'] = 'v2';
									}
							?>
							<option value="v2" <?php echo ( $data['metas']['uap_recaptcha_version'] == 'v2' ) ? 'selected' : '';?> ><?php esc_html_e( 'reCAPTCHA v2', 'uap');?></option>
							<option value="v3" <?php echo ( $data['metas']['uap_recaptcha_version'] == 'v3' ) ? 'selected' : '';?> ><?php esc_html_e( 'reCAPTCHA v3', 'uap');?></option>
				</select>
					</div>
				</div>
			</div>
			<div class="js-uap-recaptcha-v2-wrapp <?php echo ( $data['metas']['uap_recaptcha_version'] == 'v3' ) ? 'uap-display-none' : '';?>" >

			<div class="uap-form-line">
				<h4><?php esc_html_e( 'reCAPTCHA V2', 'uap');?></h4>
				<div class="row">
					<div class="col-xs-8">
            	<div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('SITE KEY:', 'uap');?></span>
                <input type="text" name="uap_recaptcha_public" value="<?php echo esc_attr($data['metas']['uap_recaptcha_public']);?>" class="form-control uap-deashboard-middle-text-input" />
               </div>
				 		</div>
				 	</div>
				 	<div class="row">
				 		<div class="col-xs-8">
				<div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('SECRET KEY:', 'uap');?></span>
                <input type="text" name="uap_recaptcha_private" value="<?php echo esc_attr($data['metas']['uap_recaptcha_private']);?>" class="form-control uap-deashboard-middle-text-input" />
                </div>
				</div>
			</div>
		</div>
		<div class="uap-form-line">
											<p><strong><?php esc_html_e('How to setup', 'uap');?></strong></p>
                                            <p>	<?php esc_html_e('1. Get Public and Private Keys from', 'uap');?> <a href="https://www.google.com/recaptcha/admin#list" target="_blank"><?php esc_html_e('here', 'uap');?></a>.</p>
                                            <p>	<?php esc_html_e('2. Click on "Create" button.', 'uap');?></p>
                                            <p>	<?php esc_html_e('3. Choose "reCAPTCHA v2" with "Im not a robot" Checkbox.', 'uap');?></p>
                                            <p>	<?php esc_html_e('4. Add curent WP website main domain', 'uap');?></p>
                                            <p> <?php esc_html_e('5. Accept terms and conditions and Submit', 'uap');?></p>
		</div>
	 </div>

            <div class="js-uap-recaptcha-v3-wrapp <?php echo ( $data['metas']['uap_recaptcha_version'] == 'v2' ) ? 'uap-display-none' : '';?>" >



			<div class="uap-form-line">
				            <h4><?php esc_html_e( 'reCAPTCHA V3', 'uap');?></h4>
				<div class="row">
					<div class="col-xs-6">
            <div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('SITE KEY:', 'uap');?></span>
                <input type="text" name="uap_recaptcha_public_v3" value="<?php echo esc_attr($data['metas']['uap_recaptcha_public_v3']);?>" class="form-control uap-deashboard-middle-text-input" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6">
			<div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('SECRET KEY:', 'uap');?></span>
                <input type="text" name="uap_recaptcha_private_v3" value="<?php echo esc_attr($data['metas']['uap_recaptcha_private_v3']);?>" class="form-control uap-deashboard-middle-text-input" />
			</div>
		</div>
	</div>
		</div>
		<div class="uap-form-line">
                                        	<p><strong><?php esc_html_e('How to setup', 'uap');?></strong></p>
											<p> <?php esc_html_e('1. Get Public and Private Keys from', 'uap');?> <a href="https://www.google.com/recaptcha/admin#list" target="_blank"><?php esc_html_e('here', 'uap');?></a>.</p>
                                            <p>	<?php esc_html_e('2. Click on "Create" button.', 'uap');?></p>
                                            <p>	<?php esc_html_e('3. Choose "reCAPTCHA v3".', 'uap');?></p>
                                            <p>	<?php esc_html_e('4. Add curent WP website main domain', 'uap');?></p>
                                            <p> <?php esc_html_e('5. Accept terms and conditions and Submit', 'uap');?></p>
		</div>
  </div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" onClick="" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>

</div>
