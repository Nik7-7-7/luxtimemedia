<?php
/**
 * Template Questions
 */
?>

<div class="ulp-question-wrapp">

	<?php if ($data['legend']):?>
			<div class="ulp-question-legend"><?php echo esc_ulp_content($data['legend']);?></div>
	<?php endif;?>

	<div class="ulp-question-input">
		<div class="ulp-question-content"><?php echo (isset($data['question_content'])) ? do_shortcode($data['question_content']) : '';?>
		  <?php if (!empty($data['points']) && $data['points'] > 0):?>
          <div class="ulp-question-points"><?php echo esc_ulp_content('('.$data['points'].esc_html__(' points', 'ulp').')');?></div>
          <?php endif;?>
        </div>
        <div class="ulp-question-options"><?php echo (isset($data['question'])) ? $data['question'] : '';?></div>
		<?php if (!empty($data['explanation'])):?>
			<div class="ulp-question-explanation" id="ulp_question_explanation"><?php echo esc_ulp_content('<strong class="ulp-font-style-normal">'.esc_html__('Explanation: ', 'ulp').'</strong>') .
			do_shortcode($data['explanation']);?></div>
		<?php endif;?>
        <?php if (!empty($data['hint'])):?>
			<div class="ulp-hint-hide" id="ulp_the_hint"><?php echo esc_ulp_content('<strong class="ulp-font-style-normal">'. esc_html__('Hint: ', 'ulp').'</strong>') .
			do_shortcode($data['hint']);?></div>
		<?php endif;?>
	</div>
    <div class="ulp-question-responses">
    </div>
    <div class="ulp-question-buttons-wrapper">
	<?php if (!empty($data['print_prev'])):?>
	<?php echo ulp_quiz_prev_bttn();?>
	<?php endif;?>
    <?php if (!empty($data['hint'])):?>
    <span class="ulp-hint-button" id="ulp_hint_link"><?php esc_html_e('Hint', 'ulp');?></span>
    <?php endif;?>
	<?php if (!empty($data['print_next'])):?>
		<?php echo ulp_quiz_next_bttn();?>
	<?php endif;?>
	<?php if (!empty($data['print_ajax_submit_bttn'])):?>
			<?php echo ulp_quiz_submit_bttn();?>
	<?php endif;?>

    </div>
</div>


<span class="ulp-js-single-question-data"
		data-quiz_id="<?php echo esc_attr($data['quiz_id']);?>"
		data-course_id="<?php echo esc_attr($data['course_id']);?>"
		data-question_id="<?php echo esc_attr($data['question_id']);?>"
		data-is_last_question="<?php echo esc_attr($data['is_last_question']);?>"
		data-correct_answer_msg="<?php esc_html_e('Correct Answer', 'ulp');?>"
		data-wrong_answer_msg="<?php esc_html_e('Wrong Answer', 'ulp');?>"
		data-submit_quiz_button="<?php esc_html_e('Complete Question', 'ulp');?>"
		data-next_bttn="<?php esc_html_e('Next Question', 'ulp');?>"
></span>
