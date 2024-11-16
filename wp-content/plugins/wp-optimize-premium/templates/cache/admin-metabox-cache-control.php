<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<div>
	<label for="wpo_disable_single_post_caching">
		<input id="wpo_disable_single_post_caching" type="checkbox" data-id="<?php echo esc_attr($post_id); ?>" <?php checked($disable_caching); ?> >
		<?php echo sprintf(esc_html__('Do not cache this %s', 'wp-optimize'), esc_html($post_type)); ?>
	</label>
</div>

<?php
	$post_types_select = '<select id="wpo_always_purge_this_post_type_select" data-id="'. esc_attr($post_id) .'" multiple>';
	foreach ($post_types as $ptype => $ptype_title) {
		$post_types_select .= '<option value="'.esc_attr($ptype).'" '.selected(in_array($ptype, $always_purge_post_type), true, false).'>'.esc_html($ptype_title).'</option>';
	}
	$post_types_select .= '</select>';
?>
<br>
<div>
	<label for="wpo_always_purge_this_post_type">
		<?php printf(esc_html__('Always purge this %s when saving any %s', 'wp-optimize'), esc_html($post_type), $post_types_select); ?>
	</label>
</div>
