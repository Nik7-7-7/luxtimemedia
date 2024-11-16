<?php
$i = 0;
if ( $data['settings']['uap_info_affiliate_bar_logo'] ){
   $i++;
}
if ( $data['settings']['uap_info_affiliate_bar_links_section_enabled'] ){
   $i++;
}
if ( $data['settings']['uap_info_affiliate_bar_banner_section_enabled'] && $data['banner'] ){
   $i++;
}
if ( $data['settings']['uap_info_affiliate_bar_social_section_enabled'] ){
   $i++;
}
if ( $data['settings']['uap_info_affiliate_bar_stats_general_section_enabled']  ||  $data['settings']['uap_info_affiliate_bar_stats_personal_section_enabled'] ){
   $i++;
}
if ( $data['settings']['uap_info_affiliate_bar_menu_section_enabled'] ){
   $i++;
}
$width = 99 / $i;
$custom_css = '';
$custom_css .= "
.uap-info-affiliate-bar-item {
    width: calc(".$width."%-1px);
}
";
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
?>

<div id="uap_info_affiliate_bar" class="uap-info-affiliate-bar-wrapper">

    <span class="uap-js-iab-simple-affiliate-link-label" data-value="<?php echo esc_html__('Simple Affiliate Link', 'uap'); ?>"></span>
    <span class="uap-js-iab-banner-affiliate-link-label" data-value="<?php echo esc_html__('Banner Affiliate Link', 'uap'); ?>"></span>


        <div class="uap-info-affiliate-bar-item uap-info-affiliate-bar-logo">
        <?php if ( $data['settings']['uap_info_affiliate_bar_logo'] ):?>
       	 	<img src="<?php echo esc_url($data['settings']['uap_info_affiliate_bar_logo']);?>" class="uap-iab-logo" />

    	<?php endif;?>
    	</div>

		<div class="uap-info-affiliate-bar-item uap-info-affiliate-bar-links">
        	<div class="uap-info-affiliate-bar-extralabel">
				<?php echo stripslashes($data['settings']['uap_info_affiliate_bar_links_get_label']); ?>
        	</div>
            <ul class="uap-info-affiliate-bar-getlinks">
    <?php if ( $data['settings']['uap_info_affiliate_bar_links_section_enabled'] ): ?>
            <li class="uap-info-affiliate-bar-btn uap-info-affiliate-bar-btn-default" id="uap_js_info_affiliate_bar_links_trigger"><i class="fa-uap fa-info_affiliate_bar-link"></i><span class="uap-info-affiliate-sublabel"><?php echo stripslashes($data['settings']['uap_info_affiliate_bar_links_label']);?></span></li>
            <span class="uap-display-none" id="uap_js_iab_linkts_triggers_section_data"><?php echo esc_uap_content($data['links_section']);?></span>
    <?php endif;?>


    <?php if ( $data['settings']['uap_info_affiliate_bar_banner_section_enabled'] && $data['banner'] ): ?>
            <li class="uap-info-affiliate-bar-btn uap-info-affiliate-bar-btn-default" id="uap_info_affiliate_bar_banner_section"><i class="fa-uap fa-info_affiliate_bar-banner"></i><span class="uap-info-affiliate-sublabel"><?php echo stripslashes($data['settings']['uap_info_affiliate_bar_banner_label']);?></span></li>
            <div id="uap_info_affiliate_bar_banner_extra_info" data-affiliate_id="<?php echo esc_attr($data['affiliate_id']);?>" data-uid="<?php echo esc_attr($data['uid']);?>" ></div>
<span class="uap-display-none" id="uap_js_iab_banner_section_data"><?php echo esc_uap_content($data['bannerSection']);?></span>

    <?php endif;?>
    </ul>
    <div class="uap-clear"></div>
  </div>
    <?php if ( $data['settings']['uap_info_affiliate_bar_social_section_enabled'] ): ?>
        <div class="uap-info-affiliate-bar-item uap-info-affiliate-bar-social">
        	<span class="uap-info-affiliate-bar-extralabel uap-info-affiliate-bar-full-line">
				<?php echo stripslashes($data['settings']['uap_info_affiliate_bar_social_label']); ?>
        	</span>
            <?php echo esc_uap_content($data['socialLinks']);?>
        </div>
    <?php endif;?>

    <div class="uap-info-affiliate-bar-item uap-info-affiliate-bar-menu">
    <?php if ( $data['settings']['uap_info_affiliate_bar_menu_section_enabled'] ):?>
          <div class="uap-info-affiliate-bar-btn uap-info-affiliate-bar-btn-default"
                      id="popover_for_iab_menu"
                      data-title = "<?php echo stripslashes($data['settings']['uap_info_affiliate_bar_menu_label']);?>"
                      data-content="
                        <ul>
                          <li><a href='<?php echo esc_url($data['profile_permalink']);?>' target='_blank'><?php echo esc_html__('Your Profile', 'uap'); ?></a></li>
                          <li><a href='<?php echo esc_url($data['settings_permalink']);?>' target='_blank'><?php echo esc_html__('Extra Settings', 'uap'); ?></a></li>
                          <li><a href='<?php echo esc_url($data['tips_permalink']);?>'  target='_blank'><?php echo esc_html__('Learn more', 'uap'); ?></a></li>
                        </ul>
                        <div class='uap-pointer uap-affiliate-bar-temporary' onClick='uapDoHideInfoAffiliateBar( this );' ><?php echo esc_html__('Temporary Hide FlashBar', 'uap'); ?></div>
                        <div class='uap-affiliate-bar-info'><?php  esc_html_e('FlashBar will be disabled for 24hrs', 'uap'); ?></div>
                    "
                      data-shift = "-2"
                      data-defineClass = "bord"
          ><i class="fa-uap fa-info_affiliate_bar-menu"></i></div>
    <?php endif;?>
	 </div>


    <?php if ( $data['settings']['uap_info_affiliate_bar_stats_general_section_enabled']  ||  $data['settings']['uap_info_affiliate_bar_stats_personal_section_enabled'] ):?>
        <div class="uap-info-affiliate-bar-item uap-info-affiliate-bar-stats">
        	<span class="uap-info-affiliate-bar-extralabel uap-info-affiliate-bar-full-line">
				<?php echo stripslashes($data['settings']['uap_info_affiliate_bar_stats_label']);?>
        	</span>
            <?php if ( $data['settings']['uap_info_affiliate_bar_stats_general_section_enabled'] ):?>
            <div class="uap-info-affiliate-bar-stats-content">
            	<div class="uap-info-affiliate-bar-extralabel">
					<?php echo stripslashes($data['settings']['uap_info_affiliate_bar_insigts_label']); ?>
        		</div>
                <ul class="uap-info-affiliate-bar-stats-list">
                	<li><?php echo esc_html($data['personalVisits']);?> <?php echo stripslashes($data['settings']['uap_info_affiliate_bar_visits_label']); ?></li>
                    <li><?php echo esc_html($data['personalReferrals']);?> <?php echo stripslashes($data['settings']['uap_info_affiliate_bar_referrals_label']); ?></li>
                </ul>
    			<div class="uap-clear"></div>
            </div>
            <?php endif;?>
            <?php if ( $data['settings']['uap_info_affiliate_bar_stats_personal_section_enabled'] ):?>
            <div class="uap-info-affiliate-bar-stats-content">
            	<div class="uap-info-affiliate-bar-extralabel">
					<?php echo stripslashes($data['settings']['uap_info_affiliate_bar_overall_performance_label']); ?>
        		</div>
                <?php
                    if ( $data['generalReferrals'] == 0 || $data['generalVisits'] == 0 ){
                        $conversion = 0;
                    } else {
                        $conversion = $data['generalReferrals'] * 100 / $data['generalVisits'];
                    }
                ?>
                <ul class="uap-info-affiliate-bar-stats-list">
                	<li><?php echo esc_html($conversion.'%');?> <?php echo stripslashes($data['settings']['uap_info_affiliate_bar_conversion_rate_label']); ?></li>
                </ul>
    			<div class="uap-clear"></div>
            </div>
            <?php endif;?>
        </div>
    <?php endif;?>


    <div class="uap-clear"></div>
</div>
