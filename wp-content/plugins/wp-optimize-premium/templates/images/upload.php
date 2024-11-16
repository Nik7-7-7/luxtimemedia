<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<?php wp_nonce_field('wpo_media_filter_nonce', 'wpo_media_filter_nonce'); ?>
<select id="wpo_image_optimization_status" name="wpo_image_optimization_status">
	<?php foreach ($dropdown_options as $value => $label) : ?>
		<option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value, true); ?>><?php echo esc_html($label); ?></option>
	<?php endforeach; ?>
</select>
