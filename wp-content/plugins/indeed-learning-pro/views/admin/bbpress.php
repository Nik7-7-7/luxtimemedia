<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('bbPress Integration', 'ulp');?></h3>
		<div class="inside">

			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate/Hold bbPress Integration', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_bbpress_integration_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_bbpress_integration_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_bbpress_integration_enable" value="<?php echo esc_attr($data['metas']['ulp_bbpress_integration_enable']);?>" id="ulp_bbpress_integration_enable" />
			</div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>

		</div>
	</div>

</form>
