<div class="ulp-stuffbox">
	<h3 class="ulp-h3"><?php esc_html_e('Export', 'ulp');?></h3>
	<div class="inside">
		<div class="ulp-form-line">
			<span class="ulp-labels-special"></span>
			<div class="ulp-form-line">
				<span class="ulp-import-export-label"><?php esc_html_e('Students', 'ulp');?></span>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<input type="checkbox" class="ulp-switch" onclick="ulpCheckAndH(this, '#import_students');">
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="import_students" value="0" id="import_students" />
			</div>
			<div class="ulp-form-line">
				<span class="ulp-import-export-label"><?php esc_html_e('Instructors', 'ulp');?></span>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<input type="checkbox" class="ulp-switch" onclick="ulpCheckAndH(this, '#import_instructors');">
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="import_instructors" value="0" id="import_instructors" />
			</div>
			<div class="ulp-form-line">
				<span class="ulp-import-export-label"><?php esc_html_e('Settings', 'ulp');?></span>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<input type="checkbox" class="ulp-switch" onclick="ulpCheckAndH(this, '#import_settings');">
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="import_settings" value="0" id="import_settings" />
			</div>
			<div class="ulp-form-line">
				<span class="ulp-import-export-label"><?php esc_html_e('All plugin custom post types (courses, lessons, quizzes, etc)', 'ulp');?></span>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<input type="checkbox" class="ulp-switch" onclick="ulpCheckAndH(this, '#import_custom_post_types');">
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="import_custom_post_types" value="0" id="import_custom_post_types" />
			</div>
		</div>

		<div class="ulp-hidden-download-link ulp-display-none"><a href="" target="_blank" download="">export.xml</a></div>

		<div class="ulp-wrapp-submit-bttn">
			<div class="button button-primary button-large ulp-display-inline ulp-vertical-align-top" onclick="ulpMakeExportFile();"><?php esc_html_e('Export', 'ulp');?></div>
			<div class="ulp-display-inline ulp-vertical-align-top" id="ulp_loading_gif"><span class="spinner"></span></div>
		</div>
	</div>
</div>

<form  method="post" enctype="multipart/form-data">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Import', 'ulp');?></h3>
		<div class="inside">
			<div class="ulp-form-line">
				<span class="ulp-labels-special"><?php esc_html_e('File', 'ulp');?></span>
				<input type="file" name="import_file">
			</div>

			<div class="ulp-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Import', 'ulp');?>" name="import" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>
