<div class="ultp-edit-user-courses-list ulp-become-instructor-wrapper">
	<h2>Ultimate Learning Pro - <?php esc_html_e('Instructor settings', 'ulp');?></h2>
	<div class="ulp-edit-wp-user-status">
		<?php if ($data['already_instructor']): ?>
			<div class="ulp-fullrow-notification"><p><strong><?php esc_html_e('Already registered as Instructor.', 'uap');?></strong></p></div>
			<div>
				<button type="button" class="button button-secondary" onclick="ulpMakeInstructorUser(<?php echo esc_attr($data['uid']);?>);"><?php esc_html_e('Remove from Instructors list', 'ulp');?></button>
			</div>
		<?php else:?>
			<button type="button" class="button button-secondary" onclick="ulpMakeUserInstructor(<?php echo esc_attr($data['uid']);?>);"><?php esc_html_e('Make This User Instructor', 'ulp');?></button>
		<?php endif?>
	</div>
</div>
