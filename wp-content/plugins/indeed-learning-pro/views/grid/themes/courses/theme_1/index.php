<div class="ulp-grid-list-courses">
  <div class="ulp-feat-img-wrapp">
    <?php if (!empty($course->feat_image)):?>
        <a href="<?php echo Ulp_Permalinks::getForCourse($course->ID); ?>" target="_blank" class="ulp-feat-img-single-course" style= " background-image:url(<?php echo esc_url($course->feat_image);?>);"></a>
    <?php endif;?>
    <?php if (!empty($course->price)):?>
    	<div class="ulp-course-price"><?php echo esc_html($course->price);?></div>
    <?php endif;?>
  </div>
  <div class="ulp-list-courses-item-wrapp-content">
    <?php if (!empty($course->post_title)):?>
        <div class="ulp-list-courses-item-title"><a href="<?php echo Ulp_Permalinks::getForCourse($course->ID); ?>" target="_blank"><?php echo esc_html($course->post_title);?></a></div>
    <?php endif;?>
    <?php if (!empty($course->categories)):?>
        <div class="ulp-list-courses-item-wrapp-second-content"><?php echo esc_html($course->categories);?></div>
    <?php endif;?>
  </div>
</div>
