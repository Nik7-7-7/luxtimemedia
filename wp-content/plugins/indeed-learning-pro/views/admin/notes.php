<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Notes Integration', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Student Notes', 'ulp');?></h2>
                    <div><?php esc_html_e('Use this magic feature to give your students the option to take notes during courses.', 'ulp');?></div>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['lesson_notes_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#lesson_notes_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="lesson_notes_enable" value="<?php echo esc_attr($data['metas']['lesson_notes_enable']);?>" id="lesson_notes_enable" />
			</div>

			<div>
				<h4><?php esc_html_e('How it works', 'ulp');?></h4>
				<div><?php esc_html_e('To generate a new form which students can use to take notes, use this shortcode [ulp_notes_form].', 'ulp');?></div>
				<div><?php esc_html_e('Use this shortcode [ulp_list_notes] to display all the notes that a student has taken.', 'ulp');?></div>
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
