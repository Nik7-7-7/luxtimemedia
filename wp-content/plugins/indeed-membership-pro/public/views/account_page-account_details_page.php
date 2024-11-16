<?php if (!empty($data['style'])):
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes($data['style']) );
 endif;?>

<div class="ihc-ap-wrap">
	<?php if (!empty($data['title'])):?>
		<h3><?php echo do_shortcode($data['title']);?></h3>
	<?php endif;?>
	<?php if (!empty($data['content'])):?>
		<p><?php echo do_shortcode($data['content']);?></p>
	<?php endif;?>

	<?php if ( $data['show_form'] ):?>
			<?php echo do_shortcode( '[ihc-edit-profile-form]' );?>
	<?php endif;?>

</div>
