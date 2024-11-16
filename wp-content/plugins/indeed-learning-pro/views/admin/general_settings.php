<form  method="post" role = "form">
  <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
  
<div class="ulp-stuffbox">



    <h3 class="ulp-h3"><?php esc_html_e('General settings', 'ulp');?></h3>



    <div class="inside">

	<div class="ulp-inside-item">

      <div class="row">

          <div class="col-xs-6">

            <h2><?php esc_html_e('Courses Settings', 'ulp');?></h2>

            <p><?php esc_html_e('Additional settings related to coursers.', 'ulp');?></p>

        	<h4 class="ulp-input-group-space"><?php esc_html_e('Course progress', 'ulp');?></h4>

        		<div class="ulp-input-group-space ulp-input-group-max">

						 <select name="ulp_course_progress_type" class="form-control m-bot15">

							 	<?php $types = array('completed_lessons_and_quizes' => esc_html__('Completed lessons and quizzes', 'ulp'), 'reward_points' => esc_html__('Reward Points', 'ulp'));?>

								<?php foreach ($types as $k=>$v):?>

										<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['ulp_course_progress_type']) ? 'selected' : '';?> ><?php echo esc_attr($v);?></option>

								<?php endforeach;?>

						 </select>

                  </div>

                   <div class="ulp-input-group-space">

                       <div><strong><?php esc_html_e('Course progress is displayed as a percentage. This is based on one of two options:', 'ulp');?></strong></div>

                       <div><?php esc_html_e('1. Reward points: % is based on how many points a student has from the sum total of points from that course.', 'ulp');?></div>

                       <div><?php esc_html_e('2. Completed lessons and quizzes: % is based on how many lessons / quizzes the student has completed out of the sum total from that course.', 'ulp');?></div>

                   </div>

          </div>

      </div>

    </div>

  	<div class="ulp-line-break"></div>

	<div class="ulp-inside-item">

      <div class="row">

          <div class="col-xs-6">

          	  <h4 class="ulp-input-group-space"><?php esc_html_e('Course auto-enroll', 'ulp');?></h4>

				<div class="form-group row">





						 <label class="ulp_label_shiwtch ulp-switch-button-margin">

		 					<?php $checked = ($data['metas']['ulp_course_auto_enroll']) ? 'checked' : '';?>

		 					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_course_auto_enroll');" <?php echo esc_attr($checked);?> />

		 					<div class="switch ulp-display-inline"></div>

		 				</label>

		 				<input type="hidden" name="ulp_course_auto_enroll" value="<?php echo esc_attr($data['metas']['ulp_course_auto_enroll']);?>" id="ulp_course_auto_enroll" />



						<div>

								<?php esc_html_e('After a user purchases a course, he is automatically enrolled in that course.', 'ulp');?>

						</div>



  			</div>

        <div class="form-group row">
          	<h4 class="ulp-input-group-space"><?php esc_html_e('Enable Gutenberg editor on custom post types', 'ulp');?></h4>
      			<div class="form-group row">
      					<label class="ulp_label_shiwtch ulp-switch-button-margin">
      		 			    <?php $checked = ($data['metas']['ulp_enable_gutenberg']) ? 'checked' : '';?>
      		 			    <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_enable_gutenberg');" <?php echo esc_attr($checked);?> />
      		 			    <div class="switch ulp-display-inline"></div>
      		 		  </label>
      		 		  <input type="hidden" name="ulp_enable_gutenberg" value="<?php echo esc_attr($data['metas']['ulp_enable_gutenberg']);?>" id="ulp_enable_gutenberg" />
    			  </div>
        </div>

    		<div class="form-group row">

    				<div class="col-4">

    						<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />

    			  </div>

    		</div>

        </div>

      </div>

    </div>

  </div>


</form>
