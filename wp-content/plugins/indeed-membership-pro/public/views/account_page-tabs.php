<?php
$custom_css = '';
foreach ($available_tabs as $slug => $array):
	if (!empty($array['icon'])):
	$custom_css .= ".fa-" . $slug . "-account-ihc:before{".
		"content: '\\".$array['icon']."';".
	"}";
	endif;
endforeach;
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

 ?>
<div class="ihc-mobile-bttn-wrapp"><i class="ihc-mobile-bttn"></i></div>
<div class="ihc-ap-menu">
	<?php if ($data['menu']):?>
		<?php foreach ($data['menu'] as $k => $array):?>
			<div class="<?php echo esc_attr($array['class']);?>"><a href="<?php echo esc_url($array['url']);?>"><i class="<?php echo esc_attr('fa-ihc fa-' . $k . '-account-ihc');?>"></i><?php echo esc_html($array['title']);?></a></div>
		<?php endforeach;?>
	<?php endif;?>
	<div class="ihc-clear"></div>
</div>
