
<div class="topcourse-links-wrapper">
  <div class="topcourse-links-box">
    <div class="ulp-prebox"></div>
    <ul>
        <li><a href="<?php echo esc_url($data['special_settings_link']); ?>" target="_blank"><?php esc_html_e('Special Settings', 'ulp');?></a></li>
        <?php if (get_option('lesson_drip_content_enable')){ ?>
        <li><a href="#ulp_drip_content"><?php esc_html_e('Drip Content', 'ulp');?></a></li>
        <?php } ?>
    </ul>
    <div class="ulp-clear"></div>
  </div>
</div>
