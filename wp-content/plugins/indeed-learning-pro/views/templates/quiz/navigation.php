<div class="ulp-lesson-nav">
	<?php if ($data['prev_url']):?>
		<div class="ulp-lesson-nav-prev">
        	<div class="ulp-lesson-nav-prev-label"><?php echo esc_html_e('Previous','ulp');  ?></div>
			<a href="<?php echo esc_url($data['prev_url']);?>" ><?php echo esc_ulp_content($data['prev_label']);?></a>
		</div>
	<?php endif;?>
	<?php if ($data['next_url']):?>
		<div class="ulp-lesson-nav-next">
        	<div class="ulp-lesson-nav-prev-label"><?php echo esc_html_e('Next','ulp');  ?></div>
			<a href="<?php echo esc_url($data['next_url']);?>" ><?php echo esc_ulp_content($data['next_label']);?></a>
		</div>
	<?php endif;?>
	<div class="ulp-clear"></div>
</div>
