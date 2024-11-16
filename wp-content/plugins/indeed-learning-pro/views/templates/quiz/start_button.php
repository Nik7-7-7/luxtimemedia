<?php if ($data ['can_run_quiz']):?>
<div class="ulp-quiz-start">
	<div class="ulp-quiz-start-the-button" id="ulp_start_quiz_bttn"><?php esc_html_e('Start Quiz', 'ulp');?></div>
</div>
<span class="ulp-js-quiz-start-bttn" data-quiz_id="<?php echo esc_attr($data['quiz_id']);?>" data-course_id="<?php echo esc_attr($data['courseId']);?>" ></span>

<?php else :?>
	<div class="ulp-quiz-start">
		<div class="ulp-quiz-start-the-button ulp-disabled-button"><?php esc_html_e('Start Quiz', 'ulp');?></div>
	</div>
<?php endif;?>
