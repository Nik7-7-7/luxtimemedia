<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Uploads Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h2><?php esc_html_e("Accepted Files Extensions", 'uap');?></h2>
				<p><?php esc_html_e('When Upload File field is required into Registration/Profile form you may restrict the extensions of files that may be uploaded', 'uap');?></p>

					<div class="row">
						<div class="col-xs-8">
					<textarea name="uap_upload_extensions" class="uap-custom-css-box"><?php echo esc_uap_content($data['metas']['uap_upload_extensions']);?></textarea>
					<div><?php esc_html_e("Write the extensions with comma between values! ex: pdf,jpg,mp3", 'uap');?></div>
				</div>
			</div>
			</div>

			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e("Upload File Maximum File Size", 'uap');?></label>
					<span class="uap-form-setting-item">
						<div class="input-group">
							<input type="number" class="form-control" value="<?php echo esc_attr($data['metas']['uap_upload_max_size']);?>" name="uap_upload_max_size" min="0.1" step="0.1" />
							<div class="input-group-addon">MB</div>
						</div>
					</span>
					<p class="uap-form-setting-description"></p>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="uap-form-setting-wrapper">
					<label class="uap-form-setting-label"><?php esc_html_e("Avatar Maximum File Size", 'uap');?></label>
					<span class="uap-form-setting-item">
						<div class="input-group">
						 <input type="number" class="form-control" value="<?php echo esc_attr($data['metas']['uap_avatar_max_size']);?>" name="uap_avatar_max_size" min="0.1" step="0.1" />
						 <div class="input-group-addon">MB</div>
					 </div>
					</span>
					<p class="uap-form-setting-description"></p>
				</div>
			</div>


			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>
</div>
