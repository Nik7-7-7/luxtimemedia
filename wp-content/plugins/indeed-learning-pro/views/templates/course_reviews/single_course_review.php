<div class="ulp-course-review-item">
  <div class="ulp-course-review-photo">
      <img alt="<?php echo esc_attr($fullName);?>" src="<?php echo esc_url($authorImage);?>" class="avatar avatar-95 photo" height="95" width="95">
    </div>
    <div class="ulp-course-review-content">
      <div class="ulp-course-review-author-name"><?php echo esc_html($fullName);?></div>
        <div class="ulp-course-review-stars">
           <?php echo ulp_generate_stars($stars);?>
         </div>
        <div class="ulp-course-review-time"><?php echo indeed_time_elapsed_string($createdTime);?></div>
        <div class="ulp-course-review-text">
          <span><?php echo esc_ulp_content(stripslashes($title));?></span>
            <?php echo indeed_format_str_like_wp($content);?>
        </div>
    </div>
    <div class="ulp-clear"></div>
</div>
