<span class="ulp-js-course-review" data-url="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course_review');?>"></span>

<div class="ulp-stuffbox">
	<form  method="post" role = "form">
			<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
			
			<h3 class="ulp-h3"><?php esc_html_e('Course reviews', 'ulp');?></h3>
			<div class="inside">
				<div class="ulp-form-line">
						<h2><?php esc_html_e('Activate Course reviews', 'ulp');?></h2>
                        <p><?php esc_html_e('Let students write a review about your courses.', 'ulp');?></p>
						<label class="ulp_label_shiwtch ulp-switch-button-margin">
							<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_course_reviews_enabled');" <?php echo ($data['metas']['ulp_course_reviews_enabled']) ? 'checked' : '';?> />
							<div class="switch ulp-display-inline"></div>
						</label>
						<input type="hidden" name="ulp_course_reviews_enabled" id="ulp_course_reviews_enabled" value="<?php echo esc_attr($data['metas']['ulp_course_reviews_enabled']);?>" />
				</div>

				<div class="ulp-form-line">
							<h4><?php esc_html_e('Restrict student to write only one review per course', 'ulp');?></h4>
							<label class="ulp_label_shiwtch ulp-switch-button-margin">
								<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_course_reviews_limit_one');" <?php echo ($data['metas']['ulp_course_reviews_limit_one']) ? 'checked' : '';?> />
								<div class="switch ulp-display-inline"></div>
							</label>
							<input type="hidden" name="ulp_course_reviews_limit_one" id="ulp_course_reviews_limit_one" value="<?php echo esc_attr($data['metas']['ulp_course_reviews_limit_one']);?>" />
				</div>


				<div class="ulp-submit-form">
						<input type="submit" value="Save Changes" name="submit" class="btn btn-primary pointer">
				</div>

		</div>
	</form>
</div>


<div class="ulp-stuffbox">
	<div class="inside">
			<div class="wrap">
					<?php
						$data['add_new'] = admin_url('post-new.php?post_type=ulp_course_review' );
						if (!empty($_GET['list_by_course_id'])){
								$data['add_new'] .= '&course_id='. sanitize_text_field($_GET['list_by_course_id']);
						}
					?>
					<a href="<?php echo esc_url($data['add_new']);?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e("Add new Course Review", 'ulp');?></a>
			</div>
			<form method="get" >
				<input type="hidden" name="page" value="ultimate_learning_pro" />
				<input type="hidden" name="tab" value="ulp_course_review" />
			<?php
			require_once ULP_PATH . 'classes/admin/listing_table/ListCourseReviews.class.php';
			$ListCourses = new ListCourseReviews();
			$ListCourses->finalOutput();
			?>
			</form>
	</div>
</div>
