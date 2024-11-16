<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<h3><?php esc_html_e('Purge cache permissions', 'wp-optimize'); ?></h3>

<div id="wp-optimize-purge-cache-permissions-settings" class="wpo-fieldgroup">
	<p>
		<?php esc_html_e('Select user roles which can purge the cache', 'wp-optimize'); ?>
	</p>
	<p>
		<select id="purge_cache_permissions" name="purge_cache_permissions[]" multiple>
		<?php foreach ($roles as $role) { ?>
			<option value="<?php echo esc_attr($role['role']); ?>" <?php selected($role['selected']); ?>><?php echo esc_html($role['name']); ?></option>
		<?php } ?>
		</select>
	</p>
</div>