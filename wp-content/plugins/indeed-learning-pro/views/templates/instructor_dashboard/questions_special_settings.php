<form action="<?php echo esc_url($saveLink);?>" method="post">
	<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />
	
	<div class="ulp-instructor-edit ulp-instructor-edit-quiz-settings">
		<h2 class="ulp-instructor-edit-top-title"><?php echo esc_html__('Special Settings', 'ulp');?></h2>
		<div class="ulp-display-none">
				<?php echo esc_ulp_content($postContent);?>
		</div>
				<input type="hidden" name="ID" value="<?php echo esc_attr($postId);?>" />
           <div class="ulp-instructor-edit-row">
                   <div class="ulp-inst-col-6">
                        <h3><?php esc_html_e('Quiz Points', 'ulp')?></h3>
                        <p><?php esc_html_e('Set how many points to complete the Quiz a user receives for answering this question.', 'ulp');?></p>
                        <div class="ulp-form-section">
                         <div class="ulp-input-group">
                        	<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Points', 'ulp');?></span>
                        	<input type="number" min="0" class="ulp-form-control" name="ulp_question_points" value="<?php echo esc_attr($data['ulp_question_points']);?>" />
                        </div>
                        </div>
                    </div>
            </div>
            <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php esc_html_e('Hint Message', 'ulp')?></h3>
                        <p><?php esc_html_e('Write a message to help your users answer this question.', 'ulp');?></p>
                        <div class="ulp-form-group">
                        	<textarea name="ulp_question_hint" class="ulp-form-control text-area ulp-instrutor-qestion-hint"><?php echo stripslashes($data['ulp_question_hint']);?></textarea>
                        </div>
                        </div>
                    </div>
            </div>
             <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
                        <h3><?php esc_html_e('Question Explanation', 'ulp')?></h3>
                        <p><?php esc_html_e('Add a text which better explains the question.', 'ulp');?></p>
                        <div class="ulp-form-group">
                        	<textarea name="ulp_question_explanation" class="ulp-form-control text-area ulp-instrutor-qestion-hint"><?php echo stripslashes($data['ulp_question_explanation']);?></textarea>
                        </div>
                        </div>
                    </div>
            </div>
            <div class="ulp-instructor-edit-line-break"></div>
                	<div class="ulp-instructor-edit-row">
                    	<div class="ulp-inst-col-6">
   							<div class="ulp-form-section">
							<input type="submit" name="save_special_settings" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer ulp-submit-button" />
							</div>
						</div>
             </div>
	</div>
	<input type="hidden" name="post_id" value="<?php echo sanitize_text_field($_GET ['id']);?>" />
</form>
