<div class="ulp-grid-list-students">
	<div class="student-img">
    <?php if (!empty($student->feat_image)):?>
            <img src="<?php echo esc_url($student->feat_image);?>"  />
    <?php endif;?>
    </div>
    <div class="student-content">
	<?php if (!empty($student->full_name)):?>
        <div class="student-name">
            <?php echo esc_html($student->full_name);?>
        </div>
    <?php endif;?>
    <?php if (!empty($student->points)):?>
        <div class="student-points" style= " color:#<?php echo esc_attr($shortcode_attributes['color_scheme']);?>;">
            <?php echo ($student->points != '' ? $student->points: '0'). esc_html__(' Points', 'ulp'); ?>
        </div>
    <?php endif;?>
    	<div class="student-desc">
    	<?php if (!empty($student->user_email)):?>
        <div class="student-email">
            <?php echo esc_html($student->user_email);?>
        </div>
    	<?php endif;?>
    	<?php if (!empty($student->user_registered)):?>
        <div class="student-registered">
            <?php echo  esc_html__('Since: ', 'ulp') . ulp_print_date_like_wp($student->user_registered);?>
        </div>
    	<?php endif;?>
      </div>
   </div>
</div>
