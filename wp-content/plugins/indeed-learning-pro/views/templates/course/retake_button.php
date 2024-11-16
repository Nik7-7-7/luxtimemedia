<div class="ulp-course-finish-wrapper">
<div class="ulp-course-finish">
	<button class="ulp-general-bttn" data-course_id="<?php echo esc_attr($data['course_id']);?>" id="ulp_retake_course_bttn">
	<?php esc_html_e('Retake Course', 'ulp');?>
        <?php if(count($data['retake']) > 0):
		  echo esc_ulp_content('<span> (+'.($data['retake']['limit']-$data['retake']['retaken']).')</span>');
		  endif;
	?>
    </button>
</div>
<div id="ulp_retake_box_message_<?php echo esc_attr($data['course_id']);?>"></div>
</div>
