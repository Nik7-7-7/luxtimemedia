<div class="ulp-quiz-print-grade">
	<!--label><?php echo esc_html($data['label']);?></label-->
	<div>
		<?php if ($data['quiz_passed']):?>
			<?php esc_html_e('You have passed the Quiz with ', 'ulp'); echo esc_html($data['grade']);?>
		<?php else:?>
			<?php esc_html_e('You have failed the Quiz with ', 'ulp');  echo esc_html($data['grade']);?>
		<?php endif;?>
	</div>

</div>
