<form action="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_student_badges');?>" method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">



		<h3 class="ulp-h3"><?php esc_html_e('Add/Edit student badge', 'ulp');?></h3>

		<div class="inside">

		<div class="ulp-inside-item">

              <div class="row">

               <div class="col-xs-6">



				<h2><?php esc_html_e('Badge Info', 'ulp');?></h2>

				 <div class="input-group ulp-input-group-max">

					<input type="hidden" name="id" value="<?php echo esc_attr($data['id']);?>" />

                    <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Title', 'ulp');?></span>

                   <input type="text" class="form-control" name="badge_title" value="<?php echo esc_attr($data ['item']['badge_title']);?>" />

				</div>

				<h4 class="ulp-input-group-space"><?php esc_html_e('Badge Photo', 'ulp');?></h4>

            	<div class="input-group ulp-input-group-max">

            		 <span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Upload', 'ulp');?></span>

                     <input type="text" class="form-control" name="badge_image" value="<?php echo esc_url($data ['item']['badge_image']);?>" onClick="openMediaUp(this, '#ulp_preview_badge');"/>

				</div>



				<div  class="ulp-student-badges-image-wrapper">

						<img src="<?php echo esc_url($data ['item']['badge_image']);?>" id="ulp_preview_badge" class="uap-admin-badge-image ulp-display-none"/>

				</div>

            </div>

          </div>

      </div>

      <div class="ulp-line-break"></div>

      <div class="ulp-inside-item">

          <div class="row">

              <div class="col-xs-4">

            	<h2><?php esc_html_e('Achievements', 'ulp');?></h2>

                <p><?php esc_html_e('Decides when the badge will be assigned to your students', 'ulp');?></p>



                <h4 class="ulp-input-group-space"><?php esc_html_e('Rule Type', 'ulp');?></h4>

				<div class="ulp-form-line" >

						<select name="badge_type" onchange="ulp_change_rules();" class="form-control m-bot15">

								<?php foreach (['tier' => esc_html__('Tier', 'ulp'), 'static' => esc_html__('Static', 'ulp')] as $k => $v):?>

								<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['item']['badge_type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

								<?php endforeach;?>

						</select>

				</div>

				<div  id="rule_types_static">

					<h4 class="ulp-input-group-space"><?php esc_html_e('Request', 'ulp');?></h4>

					<div class="ulp-form-line">

                    <select name="rule_types_static" class="form-control m-bot15">

							<?php foreach (['finish_course' => esc_html__('Finish Course', 'ulp'), 'finish_quiz' => esc_html__('Finish Quiz', 'ulp')] as $k => $v):?>

									<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['item']['badge_type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

							<?php endforeach;?>

					</select>

                    </div>

				</div>

				<div  id="rule_types_tier">

					<h4 class="ulp-input-group-space"><?php esc_html_e('Request', 'ulp');?></h4>

                    <div class="ulp-form-line">

					<select name="rule_types_tier" class="form-control m-bot15">

							<?php foreach (['reward_points' => esc_html__('Reward points', 'ulp')] as $k => $v):?>

									<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['item']['badge_type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

							<?php endforeach;?>

					</select>

                    </div>

				</div>



				<div class="input-group ulp-input-group-max">

					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Rule Value', 'ulp');?></span>

					<input type="text" class="form-control" name="rule_value" value="<?php echo esc_attr($data['item']['rule_value']);?>" />

				</div>



				<div class="ulp-form-line">

						<i id="badge_rules_examples">

						</i>

				</div>

            </div>

          </div>

      </div>

      <div class="ulp-line-break"></div>

      <div class="ulp-inside-item">

          <div class="row">

              <div class="col-xs-4">

              <h2><?php esc_html_e('Additional Details', 'ulp');?></h2>

            	<h4 class="ulp-input-group-space"><?php esc_html_e('Description:', 'ulp');?></h4>

        		<div class="ulp-form-line">

            		<textarea name="badge_content" rows="5" class="ulp-student-badges-content"><?php echo esc_html($data ['item']['badge_content']);?></textarea>

				</div>

            </div>

          </div>

      </div>

      <div class="ulp-inside-item">

          <div class="row">

              <div class="col-xs-6">

      <div class="form-group row">

          <div class="col-4">

              <input type="submit" name="save_badge" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />

          </div>

      </div>

			</div>

		 </div>

       </div>

	</div>

 </div>

</form>

<span class="ulp-js-student-badge-add-edit-ulp-change-rules"></span>
