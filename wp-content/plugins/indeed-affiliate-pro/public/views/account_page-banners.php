<div class="uap-banners-wrapp">

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

	<?php if (!empty($data['listing_items'])) : ?>
		<div class="uap-banners-list">
		<?php $marketingBuilder = new \Indeed\Uap\AffiliateMarketingBuilder();?>
		<?php foreach ($data['listing_items'] as $arr) : ?>
			<?php $alt = isset( $arr->alt_text ) && $arr->alt_text !== false ? $arr->alt_text : $arr->name;?>
			<div class="uap-banner">
				<div class="uap-banner-title"><?php echo esc_uap_content($arr->name);?></div>
				<div class="uap-banner-content">
						  <?php if ( $arr->content_type === '' || $arr->content_type === false || $arr->content_type === 'image' ):?>
							<div class="uap-banner-img">
									<a href="<?php echo esc_url($arr->url);?>" target="_blank" >
										<img src="<?php echo esc_url($arr->image);?>" alt="<?php echo esc_attr( $alt );?>" />
									</a>
							</div>
							<?php else :?>
								<div class="uap-creatives-text">
										<a href="<?php echo esc_url($arr->url);?>" target="_blank">
											<?php $arr->text_content;?>
										</a>
								</div>
							<?php endif;?>
					<div class="uap-banner-description"><span class="uap-special-label"><?php echo esc_html__('Creative Description', 'uap') . '</span>' . uap_correct_text($arr->description);?></div>
					<?php $size = uap_get_image_size($arr->image);?>
					<?php if ( isset( $size['width'] ) && isset( $size['height'] ) ):?>
							<div><span class="uap-special-label"><?php echo esc_html__('Creative Banner Size', 'uap');?></span> <?php echo esc_uap_content($size['width'] . 'px x ' . $size['height'] . 'px.');?></div>
					<?php endif;?>
					<div><span class="uap-special-label"><?php echo esc_html__('Target URL', 'uap');?></span> <?php echo esc_url($arr->url);?></div>

					<?php if ( !isset( $arr->content_type ) || $arr->content_type === 'image' ):?>
							<div class="uap-banner-copypaste">
								<span class="uap-special-label"><?php esc_html_e('HTML Embed Code', 'uap');?></span>
								<textarea><a href="<?php echo esc_url($arr->url);?>" target="_blank" alt="<?php echo $alt;?>"><img src="<?php echo esc_url($arr->image);?>"  alt="<?php echo esc_attr($arr->name);?>" /><?php echo esc_uap_content($data['pixel_tracking']);?></a></textarea>
							</div>
					<?php else :?>
						<div class="uap-banner-copypaste">
							<span class="uap-special-label"><?php esc_html_e('HTML Embed Code', 'uap');?></span>
							<textarea><a href="<?php echo esc_url($arr->url);?>" target="_blank"><?php echo esc_uap_content($arr->text_content);?></a></textarea>
						</div>
					<?php endif;?>

					<?php if ( $data['show_social'] ):?>
							<?php echo esc_uap_content($marketingBuilder->getSocialForCreatives( $data['social_shortcode'], uap_correct_text($arr->description), $arr->url, $arr->image ));?>
					<?php endif;?>

				</div>
			</div>
		<?php endforeach;?>
	</div>
	<?php else : ?>
		 <div class="uap-warning-box uap-extra-margin-top"><?php esc_html_e("There aren't any creatives available.", 'uap');?></div>
	<?php endif;?>
</div>
