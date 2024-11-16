<?php
global $post;
$meta_arr = ihc_post_metas($post->ID);
if($meta_arr['ihc_mb_block_type']=='replace'){
	//display the box
	$custom_css = '';
	$custom_css .= "
	#ihc_replace_content{
		display: block;
	}
	";
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', $custom_css );
}
$settings = array(
					'media_buttons' => true,
					'textarea_name' => 'ihc_replace_content',
				 );
$meta_arr['ihc_replace_content'] = stripslashes($meta_arr['ihc_replace_content']);
$meta_arr['ihc_replace_content'] = htmlspecialchars_decode($meta_arr['ihc_replace_content']);
wp_editor( $meta_arr['ihc_replace_content'], 'ihc-replace-content', $settings );
