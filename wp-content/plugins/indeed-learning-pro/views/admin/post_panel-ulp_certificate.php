<form action="<?php echo esc_url($data['form_submit_url']);?>" method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">
		<?php
			if( !isset($data['post_title'])) {
				$title = '';
			} else {
				$title =  $data['post_title'] . ' - ';
			}
		?>
		<h3 class="ulp-h3"><?php echo esc_html($title) . esc_html__(' Special Settings', 'ulp');?></h3>


		<div class="inside">
        <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
					<h2><?php esc_html_e('Target Course', 'ulp');?></h2>
                    <p><?php esc_html_e('Choose for which course the cerificate will be assigned once the student completes the course', 'ulp');?></p>
				<div class="form-group row ulp-input-group-space ulp-input-group-max">
          <select name="ulp_course_certificate[]"  class="form-control m-bot15" >
              <option value="-1" <?php echo (empty($data['ulp_course_certificate'])) ? 'selected' : '';?> >...</option>
              <?php foreach ($data['courses'] as $course_data):?>
                  <option value="<?php echo esc_attr($course_data['ID']);?>" <?php echo (in_array($course_data['ID'], $data['ulp_course_certificate'])) ? 'selected' : '';?> ><?php echo esc_html($course_data['post_title']);?></option>
              <?php endforeach;?>
          </select>
				</div>

				<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />
                  </div>
                    </div>
                </div>
		<div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
		<div class="form-group row">
				<div class="col-4">
						<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
			  </div>
		</div>
		         </div>
                    </div>
                </div>
	</div>
    </div>

</form>
