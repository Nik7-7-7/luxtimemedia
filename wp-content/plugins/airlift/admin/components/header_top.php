<?php
	$plugin_slug = "airlift";
	$brand_name = "AirLift";
	$plugin_logo = plugins_url("/../../img/airlift-logo.svg", __FILE__);
	$title = "Ultra Fast WordPress Sites with the Click of a Button";
	$intro_video_url = "https://youtu.be/sv1uFVZL9Vo";
	$header_logo_link = "https://airlift.net/?utm_source=bv_plugin_lp_logo&utm_medium=logo_link&utm_campaign=bv_plugin_lp_header&utm_term=header_logo&utm_content=image_link";
?>
<div class="header-top">
	<div class="logo-img">
		<a href="<?php echo esc_url($header_logo_link); ?>" target="_blank" rel="noopener noreferrer">
			<img height="81" src="<?php echo esc_url($plugin_logo); ?>" alt="Logo">
		</a>
	</div>
	<h2 class="text-center heading"><?php echo esc_html($title); ?></h2>
	<div class="text-center intro-video">
		<a href="<?php echo esc_url($intro_video_url); ?>" target="_blank" rel="noopener noreferrer">
			<img src="<?php echo esc_url(plugins_url("/../../img/play-video.png", __FILE__)); ?>"/>
			Watch the <?php echo esc_html($brand_name); ?> Video
		</a>
	</div>
</div>