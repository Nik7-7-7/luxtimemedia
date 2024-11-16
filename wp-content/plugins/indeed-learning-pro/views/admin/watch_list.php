<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Wish List', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Wish list', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_watch_list_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_watch_list_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_watch_list_enable" value="<?php echo esc_attr($data['metas']['ulp_watch_list_enable']);?>" id="ulp_watch_list_enable" />
			</div>

			<div>
            	<h4><?php esc_html_e('How it works', 'ulp');?></h4>
					<p><?php esc_html_e('Enable this feature to allow your students to save a course to their personal wish list. ', 'ulp');?></p>
                    <p><?php esc_html_e('To generate a new button which students can use to save courses, use this shortcode: ', 'ulp');?> <strong>[ulp_watch_list_bttn]</strong></p>
										<p><b><?php echo esc_html( 'course_id' );?></b><?php esc_html_e(" parameter is required in order to generate the button.", 'ulp');?></p>
<p><?php esc_html_e('Use this shortcode [ulp_list_watch_list] to display all the stored wishlist items ', 'ulp');?></p>

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
