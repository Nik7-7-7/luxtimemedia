
<div class="topcourse-links-wrapper">
  <div class="topcourse-links-box">
    <div class="ulp-prebox"></div>
    <ul>
        <li><a href="<?php echo esc_url($data['special_settings_link']); ?>" target="_blank"><?php esc_html_e('Special Settings', 'ulp');?></a></li>
        <li><a href="#modulesMetaBox"><?php esc_html_e('Course Sections', 'ulp');?></a></li>
        <li><a href="#studentsMetaBox"><?php esc_html_e('Students', 'ulp');?></a></li>
        <li><a href="#authordiv"><?php esc_html_e('Author', 'ulp');?></a></li>
        <?php if (get_option('ulp_course_reviews_enabled')){ ?>
        <li><a href="<?php echo esc_url($data['reviews_link']); ?>" target="_blank"><?php esc_html_e('Reviews', 'ulp');?></a></li>
        <?php } ?>
        <li><a href="<?php echo esc_url($data['permalink']); ?>" target="_blank"><?php esc_html_e('View Course', 'ulp');?></a></li>
    </ul>
    <div class="ulp-clear"></div>
  </div>
</div>
