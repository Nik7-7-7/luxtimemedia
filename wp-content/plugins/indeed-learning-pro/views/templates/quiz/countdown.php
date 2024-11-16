<?php wp_enqueue_script('ulp_main_easytimer');?>
<span class="ulp-js-the-countdown" data-seconds_remain="<?php echo esc_attr($data['seconds_remain']);?>"></span>

<div class="ulp-quiz-countdown-wrapper"><span class="ulp-quiz-countdown"><span id="ulp_quiz_countdown">
<?php
			$start_time = '00:00';
			if( $data['seconds_remain']> 3600){
				$start_time = '00:00:00';
			}
			if( $data['seconds_remain']> 86400){
				$start_time = '00:00:00:00';
			}
			echo esc_ulp_content($start_time);
			?></span></span></div>
