<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Certificates', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">

			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Courses Certificates', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_certificates_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_certificates_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_certificates_enable" value="<?php echo esc_attr($data['metas']['ulp_certificates_enable']);?>" id="ulp_certificates_enable" />
			</div>

			<div>
      	<h4><?php esc_html_e('How it works', 'ulp');?></h4>
				<div><?php esc_html_e('Enabling this feature will let you add certificates for courses.', 'ulp');?></div>
				<div><?php esc_html_e('Example: If your students finish “Course A”, they are awarded a certificate.', 'ulp');?></div>
			</div>


			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>

		 </div>
       </div>
      </div>
		</div>
	</div>

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Give Certificate to User', 'ulp');?></h3>
		<div class="inside">
		<div class="ulp-inside-item">
              <div class="row">
               <div class="col-xs-6">
			<div class="ulp-form-line">
            <p><?php esc_html_e('You can manually assign a certificate to Students from this section', 'ulp');?></p>
					<label><?php esc_html_e('Course', 'ulp');?></label>
					<div>
							<select name="cid" class="form-control">
								  <option value="-1" >...</option>
									<?php if ($data ['courses']):?>
											<?php foreach ($data ['courses'] as $course_array):?>
													<option value="<?php echo esc_attr($course_array ['ID']);?>" ><?php echo esc_html($course_array ['post_title']);?></option>
											<?php endforeach;?>
									<?php endif;?>
							</select>
					</div>
			</div>
			<div class="ulp-form-line">
					<label><?php esc_html_e('Student', 'ulp');?></label>
					<input type="text" class="form-control" value="" name="username" id="username"/>
			</div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Assign Certificate', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>

		 </div>
       </div>
      </div>
		</div>
	</div>

</form>

<span class="ulp-js-certificates-magic-feat-autocomplete" ></span>
