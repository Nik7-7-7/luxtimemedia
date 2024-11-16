<?php wp_enqueue_script('ulp_main_easytimer');?>

<span class="ulp-js-lesson-complete-bttn"
			data-seconds_remain="<?php echo esc_attr($data['seconds_remain']);?>"
			></span>

<div class="ulp-lesson-complete-wrapp">

	<?php
	if (empty($data['is_completed'])):?>
		<?php $class = $data['seconds_remain']<1 ? 'ulp-lesson-complete-the-button' : 'ulp-lesson-complete-the-button-wait ulp-cursor-default';?>
		<?php if ($data['seconds_remain']>0):?>
          <div class="ulp_lesson_countdown-wrapper">
			<div class="ulp_lesson_countdown" id="ulp_lesson_countdown">
            <?php
			$start_time = '00:00';
			if( $data['seconds_remain']> 3600){
				$start_time = '00:00:00';
			}
			if( $data['seconds_remain']> 86400){
				$start_time = '00:00:00:00';
			}
			echo esc_ulp_content($start_time);
			?>
            </div>
            <div class="ulp_lesson_countdown-text"><?php esc_html_e('you can complete the lesson only after the time ends', 'ulp');?></div>
         </div>
		<?php endif;?>
        <div class="<?php echo esc_attr($class);?>" data-lesson_id="<?php echo esc_attr($data['lesson_id']);?>" id="ulp_lesson_complete_bttn"><?php esc_html_e('Complete Lesson', 'ulp');?></div>
	<?php else:?>
		<div class="ulp-lesson-completed"><?php esc_html_e('Completed Lesson', 'ulp');?></div>
	<?php endif;?>

</div>
