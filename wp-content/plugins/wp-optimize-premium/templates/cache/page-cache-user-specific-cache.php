<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<div class="wpo-fieldgroup__subgroup">
	<label for="enable_user_specific_cache">
		<input name="enable_user_specific_cache" id="enable_user_specific_cache" class="cache-settings enable_user_specific_cache wpo-select-group" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_user_specific_cache']); ?>>
		<?php esc_html_e('Enable user specific cache', 'wp-optimize'); ?>
	</label>

	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e('Notice:', 'wp-optimize'); ?></strong>
			<?php esc_html_e('This option will create cache files for each user.', 'wp-optimize'); ?>
			<span><?php esc_html_e('As a result, the cache size might become large if there are many users on your website.', 'wp-optimize'); ?></span>
		</p>
		<?php if ($is_nginx) { ?>

			<p>
				<strong><?php esc_html_e('Important:', 'wp-optimize'); ?></strong><br>
				<?php echo sprintf(esc_html__('As the user specific cache might contain personal information, it is highly advised to configure your server to disallow direct access to %s.', 'wp-optimize'), esc_html($path_to_cache)); ?>
			</p>
			<p>
				<?php echo sprintf(esc_html__('Nginx configuration example:', 'wp-optimize')); ?><br>
<pre class="code">
location <?php echo esc_html($path_to_cache); ?> {
	deny all; 
}
</pre>	
			</p>
		<?php } ?>
	</div>
</div>