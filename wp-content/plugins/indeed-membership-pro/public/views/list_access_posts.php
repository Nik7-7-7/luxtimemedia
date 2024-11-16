<?php if (!empty($data['metas']['ihc_list_access_posts_custom_css'])):
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes($data['metas']['ihc_list_access_posts_custom_css']) );
 endif;?>
<div class="iump-list-access-posts-wrapp <?php echo esc_attr($data['metas']['ihc_list_access_posts_template']);?>">

	<?php if (!empty($data['metas']['ihc_list_access_posts_title'])):?>
		<div class="iump-list-access-posts-title"><h2><?php echo esc_html($data['metas']['ihc_list_access_posts_title']);?></h2></div>
	<?php endif;?>

	<?php if (!empty($data['items'])):?>
		<?php foreach ($data['items'] as $item):?>
			<div class="iump-list-access-posts-item-wrapp">
				<?php if (!empty($item['feature_image'])):?>
					<div class="iump-list-access-posts-the-feature-image">
					     <a href="<?php echo esc_url($item['permalink']);?>">
							<img src="<?php echo esc_url($item['feature_image']);?>" />
						</a>
					</div>
				<?php endif;?>
				<div class="iump-list-access-posts-item-content">
					<?php if (!empty($item['title'])):?>
					  <div class="iump-list-title">
						<a href="<?php echo esc_url( $item['permalink'] );?>" class="iump-permalink">
							<?php echo esc_html( $item['title'] );?>
						</a>
					   </div>
					<?php endif;?>
					<div class="iump-list-details">
					<?php if (!empty($item['post_date'])):?>
						<div class="iump-list-access-posts-date">
						 <?php esc_html_e('Posted', 'ihc');?>
						  <a href="<?php echo esc_url( $item['permalink'] );?>">
							<?php echo esc_html($item['post_date']);?>
						  </a>
						</div>
					<?php endif;?>
					<?php if (!empty($item['post_author'])):?>
						<div class="iump-list-access-posts-author">
					     <?php esc_html_e('By', 'ihc');?>
						 <a href="<?php echo esc_url( $item['permalink'] );?>">
							<?php echo esc_html( $item['post_author'] );?>
						</a>
						</div>
					<?php endif;?>
					<span class="ihc-clear"></span>
					</div>
					<?php if (!empty($item['post_excerpt'])):?>
						<div class="iump-list-access-posts-the-excerpt">
							<?php echo esc_html($item['post_excerpt']);?>
						</div>
					<?php endif;?>
				</div>
				<div class="ihc-clear"></div>
			</div>
		<?php endforeach;?>
		<?php if (!empty($data['pagination'])):?>
			<?php echo esc_ump_content($data['pagination']);?>
		<?php endif;?>
	<?php endif;?>

</div>
