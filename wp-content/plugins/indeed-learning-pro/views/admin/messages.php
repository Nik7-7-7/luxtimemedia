<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Messages', 'ulp');?></h3>

		<div class="inside">

			<h4><?php esc_html_e('Enroll process', 'ulp');?></h4>
        <div class="form-group row">
						<div class="col-sm-4">
  			     	<label class="col-form-label"><?php esc_html_e('Enroll error - user is not logged', 'ulp');?></label>
						 	<textarea name="ulp_messages_enroll_error_user_not_logged" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_enroll_error_user_not_logged']);?></textarea>
						</div>
				</div>

				    <div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Enroll error - maximum number of students', 'ulp');?></label>
							 	<textarea name="ulp_messages_enroll_error_on_maximum_num_of_students" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_enroll_error_on_maximum_num_of_students']);?></textarea>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Enroll error - you already enrolled', 'ulp');?></label>
							 	<textarea name="ulp_messages_enroll_error_user_is_already_enroll" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_enroll_error_user_is_already_enroll']);?></textarea>
							</div>
						</div>

				    <div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Enroll error - prerequest courses', 'ulp');?></label>
							 	<textarea name="ulp_messages_course_prerequest_courses" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_course_prerequest_courses']);?></textarea>
							</div>
						</div>

				    <div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Enroll error - prerequest points', 'ulp');?></label>
							 	<textarea name="ulp_messages_course_prerequest_reward_points" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_course_prerequest_reward_points']);?></textarea>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Enroll error - retake limit', 'ulp');?></label>
							 			<textarea name="ulp_messages_enroll_error_retake_course_limit" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_enroll_error_retake_course_limit']);?></textarea>
							</div>
						</div>



				<h4><?php esc_html_e('Become instructor', 'ulp');?></h4>
						<div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Become instructor - error - user not logged', 'ulp');?></label>
							 	<textarea name="ulp_messages_become_instructor_user_not_logged" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_become_instructor_user_not_logged']);?></textarea>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Become instructor - button label', 'ulp');?></label>
							 	<textarea name="ulp_messages_become_instructor_button" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_become_instructor_button']);?></textarea>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Become instructor - error - already registered', 'ulp');?></label>
							 	<textarea name="ulp_messages_become_instructor_already_registered" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_become_instructor_already_registered']);?></textarea>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-4">
				  			  	<label class="col-form-label"><?php esc_html_e('Become instructor - pending', 'ulp');?></label>
							 	<textarea name="ulp_messages_become_instructor_pending" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_become_instructor_pending']);?></textarea>
							</div>
						</div>

				<h4><?php esc_html_e('Checkout', 'ulp');?></h4>
				<div class="form-group row">
					<div class="col-sm-4">
								<label class="col-form-label"><?php esc_html_e('Checkout - amount label', 'ulp');?></label>
						<textarea name="ulp_messages_checkout_amount" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_checkout_amount']);?></textarea>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-sm-4">
								<label class="col-form-label"><?php esc_html_e('Checkout - payment type', 'ulp');?></label>
						<textarea name="ulp_messages_checkout_payment_type" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_checkout_payment_type']);?></textarea>
					</div>
				</div>

				<div class="form-group row">
						<div class="col-sm-4">
								<label class="col-form-label"><?php esc_html_e('Checkout - error - user is not logged in', 'ulp');?></label>
								<textarea name="ulp_messages_checkout_user_not_logged" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_checkout_user_not_logged']);?></textarea>
						</div>
				</div>


		<h4><?php esc_html_e('Badges', 'ulp');?></h4>
				<div class="form-group row">
					<div class="col-sm-4">
								<label class="col-form-label"><?php esc_html_e('List Badges title', 'ulp');?></label>
						<textarea name="ulp_messages_list_badges_title" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_list_badges_title']);?></textarea>
					</div>
				</div>

				<h4><?php esc_html_e('Courses', 'ulp');?></h4>
					<div class="form-group row">
							<div class="col-sm-4">
										<label class="col-form-label"><?php esc_html_e('Not enrolled', 'ulp');?></label>
								<textarea name="ulp_messages_list_courses_not_enrolled" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_list_courses_not_enrolled']);?></textarea>
							</div>
					</div>

											<div class="form-group row">
														<div class="col-sm-4">
																	<label class="col-form-label"><?php esc_html_e('Buy course button', 'ulp');?></label>
															<textarea name="ulp_messages_buy_course_bttn" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_buy_course_bttn']);?></textarea>
														</div>
											</div>

					<h4><?php esc_html_e('List notes', 'ulp');?></h4>
						<div class="form-group row">
								<div class="col-sm-4">
											<label class="col-form-label"><?php esc_html_e('No notes available', 'ulp');?></label>
									<textarea name="ulp_messages_list_notes_zero" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_list_notes_zero']);?></textarea>
								</div>
						</div>

			<h4><?php esc_html_e('Quizes', 'ulp');?></h4>
				<div class="form-group row">
						<div class="col-sm-4">
							<label class="col-form-label"><?php esc_html_e('Quiz not completed', 'ulp');?></label>
							<textarea name="ulp_messages_quiz_not_completed" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_quiz_not_completed']);?></textarea>
						</div>
				</div>
				<div class="form-group row">
						<div class="col-sm-4">
							<label class="col-form-label"><?php esc_html_e('Quiz result', 'ulp');?></label>
							<textarea name="ulp_messages_quiz_result" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_quiz_result']);?></textarea>
						</div>
				</div>

			<h4><?php esc_html_e('Stripe payment', 'ulp');?></h4>
				<div class="form-group row">
						<div class="col-sm-4">
							<label class="col-form-label"><?php esc_html_e('Payment completed', 'ulp');?></label>
							<textarea name="ulp_messages_stripe_completed" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_stripe_completed']);?></textarea>
						</div>
				</div>
				<div class="form-group row">
						<div class="col-sm-4">
							<label class="col-form-label"><?php esc_html_e('Payment fail', 'ulp');?></label>
							<textarea name="ulp_messages_stripe_not_completed" class="ulp-admin-messages-textarea"><?php echo esc_ulp_content($data ['metas']['ulp_messages_stripe_not_completed']);?></textarea>
						</div>
				</div>

						<div class="form-group row">
								<div class="col-4">
									<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
						    </div>
						</div>
	</div>
</div>
</form>
