
<?php
$custom_css = '';
foreach ($data['tabs'] as $slug => $array):
	if (!empty($array['icon'])):
	$custom_css .= ".fa-" . $slug . "-account-ulp:before{".
		"content: '\\".$array['icon']."';".
	"}";
	endif;
endforeach;
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

 ?>
<div class="ulp-ap-menu <?php echo (!empty($data ['metas']['ulp_ap_theme']) ? $data ['metas']['ulp_ap_theme'] : '');?>">

    <?php if ($data['tabs']):?>
        <?php foreach ($data['tabs'] as $slug => $array):?>
            <div class="ulp-ap-menu-item">
                <a href="<?php echo esc_url($array['url']);?>" >
                    <i class="<?php echo esc_attr("fa-ulp fa-".$slug."-account-ulp fa-ulp-public-menu-item");?>"></i>
                    <?php echo esc_ulp_content($array['label']);?>
                </a>
            </div>
        <?php endforeach;?>
    <?php endif;?>
    <div class="ulp-clear"></div>

</div>

<div class="ulp-user-page-content">
