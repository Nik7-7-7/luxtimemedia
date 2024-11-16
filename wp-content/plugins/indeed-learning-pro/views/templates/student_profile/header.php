<?php
wp_enqueue_script('ulp_mouse_wheel', ULP_URL . 'assets/js/jquery.mousewheel.min.js', array('jquery'), '3.7' );
wp_enqueue_script('ulp_croppic', ULP_URL . 'assets/js/croppic.js', array('jquery'), '3.7' );
wp_enqueue_script('ulp_account_page-banner', ULP_URL . 'assets/js/account_page-banner.js', array('jquery'), '3.7' );
wp_enqueue_style('ulp_croppic', ULP_URL . 'assets/css/croppic.css', array(), '3.7' );
?>
<span class="ulp-js-student-profile-header" data-url="<?php echo ULP_URL . "ajax_upload.php";?>" ></span>

<?php
$custom_css = '';
$custom_css .= stripslashes($data ['metas']['ulp_account_page_custom_css']);
if ($data ['metas']['ulp_ap_edit_background']){
$custom_css .= ".ulp-user-page-top-ap-background{
    height: 250px;
    top: 0;
    background-image: url('". $data ['metas']['ulp_ap_top_background_image']."');

}
";
}else{
  $custom_css .= ".ulp-user-page-top-wrapper{
  		padding-top:75px;
  	}
";
}

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
?>
<div class="ulp-user-page-wrapper">
<div class="ulp-user-page-top-wrapper <?php echo (!empty($data ['metas']['ulp_ap_top_template']) ? $data ['metas']['ulp_ap_top_template'] : '');?>" >
	<div class="ulp-left-side">
	<div class="ulp-user-page-details">
		<?php if (!empty($data['avatar'])):?>
			<div class="ulp-user-page-avatar"><img src="<?php echo esc_url($data['avatar']);?>" class="ulp-member-photo"/></div>
		<?php endif;?>
	 </div>
	</div>
    <div class="ulp-middle-side">
		<div class="ulp-account-page-top-mess"><?php echo do_shortcode($data['content']);?></div>
	</div>
    <?php if($data ['metas']['ulp_ap_edit_show_points'] || $data ['metas']['ulp_ap_edit_show_badges']):?>
    <div class="ulp-right-side">
     <?php if($data ['metas']['ulp_ap_edit_show_badges']): ?>
    	<div class="ulp-top-badges">
        	<?php echo do_shortcode("[ulp_list_badges]"); ?>
        </div>
        <?php endif; ?>
         <?php if($data ['metas']['ulp_ap_edit_show_points']): ?>
        <div class="ulp-top-points">
        	<div class="ulp-stats-label"><?php esc_html_e('Points', 'ulp'); ?></div>
        	<div class="ulp-stats-content"><?php echo do_shortcode("[ulp-reward-points]"); ?></div>
        </div>
        <?php endif; ?>
        <div class="ulp-clear"></div>
    </div>
    <?php endif; ?>
    <div class="ulp-clear"></div>

   <?php if (!empty($data ['notices'])):?>
		<div class="ulp-danger-message">
				<?php foreach ($data ['notices'] as $notice):?>
						<div><?php echo esc_ulp_content($notice);?></div>
				<?php endforeach;?>
		</div>
	<?php endif;?>

    <?php if (!empty($data ['metas']['ulp_ap_edit_background'])):
  	$bk_styl='';
    $banner = '';
    if (!empty($data['ulp_account_page_personal_header'])){
      $banner = $data['ulp_account_page_personal_header'];
    } else if (!empty($data ['metas']['ulp_ap_top_background_image'])){
  	 	  $banner = $data ['metas']['ulp_ap_top_background_image'];
  	}
   $bk_styl = 'style= " background-image:url('. esc_url($banner).');"';
   ?>
  <div class="ulp-user-page-top-background" <?php echo esc_ulp_content($bk_styl);?> data-banner="<?php echo esc_url($banner);?>">
      <div class="ulp-edit-top-ap-banner" id="js_ulp_edit_top_ap_banner"></div>
  </div>
  <?php endif;?>
</div>


<!--div class="ulp-user-page-top-ap-background"></div>
<?php if (!empty($data ['avatar'])):?>
    <div class="ulp-user-page-avatar"><img src="<?php echo esc_url($data ['avatar']);?>" /></div>
<?php endif;?>
<div class="ulp-student-profile-the-header"><?php echo esc_ulp_content(stripslashes($data ['content']));?></div>

<div class="ulp-clear"></div-->
