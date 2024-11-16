<div class="ulp-course-enroll-wrapp" id="<?php echo esc_attr('ulp_enroll_box_course_' . $data['course_id']);?>">
	<?php if (empty($data['already_enrolled'])):?>
		<?php if ($data['user_can_enroll']):?>
				<div class="ulp-enroll-course-the-button" data-cid="<?php echo esc_attr($data['course_id']);?>" id="<?php echo esc_attr('enroll_course_' . $data['course_id']);?>" ><?php esc_html_e('Enroll', 'ulp');?></div>
		<?php else:?>
				<div class="ulp-enroll-course-the-button-disabled" ><?php esc_html_e('Enroll', 'ulp');?></div>
				<div class="ulp-course-enroll-message-danger"><?php echo stripslashes($data['reason']);?></div>
		<?php endif;?>
	<?php else:?>
		<div class="ulp-course-enroll-message-success"><?php esc_html_e('Already Enrolled', 'ulp');?></div>
	<?php endif;?>
</div>
