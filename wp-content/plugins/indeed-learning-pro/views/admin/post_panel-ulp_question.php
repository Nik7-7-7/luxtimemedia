<form action="<?php echo esc_url($data['form_submit_url']);?>" method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"> <?php esc_html_e('Special Settings', 'ulp');?></h3>

		<div class="inside">

			<?php do_action('ulp_admin_before_question_special_settings');?>
            <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Quiz Points', 'ulp')?></h2>
                        <p><?php esc_html_e('Set how many points to complete the Quiz a user receives for answering this question.', 'ulp');?></p>
                        <div class="input-group">
                        	<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Points', 'ulp');?></span>
                        	<input type="number" min="0" class="form-control" name="ulp_question_points" value="<?php echo esc_attr($data['ulp_question_points']);?>" />
                        </div>
                        </div>
                    </div>
            </div>
            <div class="ulp-line-break"></div>
            <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Hint Message', 'ulp')?></h2>
                        <p><?php esc_html_e('Write a message to help your users answer this question.', 'ulp');?></p>
                        <div class="form-group">
                        	<textarea name="ulp_question_hint" class="form-control text-area ulp-question-text-area"><?php echo stripslashes($data['ulp_question_hint']);?></textarea>
                        </div>
                        </div>
                    </div>
            </div>
            <div class="ulp-line-break"></div>
            <div class="ulp-inside-item">
                	<div class="row">
                    	<div class="col-xs-6">
                        <h2><?php esc_html_e('Question Explanation', 'ulp')?></h2>
                        <p><?php esc_html_e('Add a text which better explains the question.', 'ulp');?></p>
                        <div class="form-group">
                        	<textarea name="ulp_question_explanation" class="form-control text-area ulp-question-text-area"><?php echo stripslashes($data['ulp_question_explanation']);?></textarea>
                        </div>
                        </div>
                    </div>

										<div class="ulp-inside-item "
							                        <div class="row">
							                            <div class="col-xs-6">
																						<div  class="ulp-wrapp-submit-bttn">
																								<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
																							</div>
														</div>
													</div>
							             </div>

            </div>


			<?php do_action('ulp_admin_after_question_special_settings');?>



		</div>



	<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />

</form>
