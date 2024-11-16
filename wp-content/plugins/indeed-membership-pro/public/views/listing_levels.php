<?php if (!empty($data['levels'])):?>
	<?php if (!empty($data['custom_css'])):
		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', stripslashes($data['custom_css']) );
	 endif;?>
	<div class="iump-wrapp-listing-levels">
		<?php foreach ($data['levels'] as $level):?>
			<?php $is_expired_class = (empty($level['is_expired'])) ? '' : 'ihc-expired-level';?>
			<?php if (!empty($attr['badges']) && !empty($level['badge_image_url'])):?>
				<div class="iump-badge-wrapper <?php echo esc_attr($is_expired_class);?>">
					<img src="<?php echo esc_url($level['badge_image_url']);?>" class="iump-badge" title="<?php echo esc_attr($level['label']);?>"  alt="<?php echo esc_attr($level['label']);?>"/>
				</div>
			<?php else:?>
				<div class="iump-listing-levels-label  <?php echo esc_attr($is_expired_class);?>"><?php echo esc_html($level['label']);?></div>
			<?php endif;?>
		<?php endforeach;?>
	</div>
<?php endif;?>
