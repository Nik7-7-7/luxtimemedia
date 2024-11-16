<div class="uap_info_bar_banner_wrapp"><?php echo esc_uap_content($data['banner']);?></div>
<div class="uap-info-bar-banner-size">
  <div class="uap-ap-label uap-special-label">
    <?php echo esc_html__('Banner Size', 'uap'); ?>
  </div>
  <select id="uap_iab_banner_select_size" onChange="" class="uap-public-form-control ">
      <option value="thumbnail"><?php echo esc_html__('Small', 'uap'); ?></option>
      <option value="medium"><?php echo esc_html__('Medium', 'uap'); ?></option>
      <option value="large"><?php echo esc_html__('Large', 'uap'); ?></option>
  </select>
</div>
<textarea id="uap_info_bar_banner_the_value" readonly class="uap-account-url" onclick="this.select()" onfocus="this.select()"><?php echo esc_uap_content($data['banner']);?></textarea>
<div id="uap_info_affiliate_bar_extra_info" data-affiliate_id="<?php echo isset( $data['affiliate_id'] ) ? $data['affiliate_id'] : '';?>" data-affiliate_id="<?php echo isset( $data['uid'] ) ? $data['uid'] : '';?>"></div>
