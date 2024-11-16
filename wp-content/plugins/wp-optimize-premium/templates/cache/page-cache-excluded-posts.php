<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<?php if (!empty($wpo_cache_excluded_posts)) { ?>
<table id="wpo_cache_excluded_posts">
	<thead>
		<tr>
			<th style="width: 80%;"><?php esc_html_e('Excluded posts:', 'wp-optimize'); ?></th>
			<th><?php esc_html_e('Post type', 'wp-optimize'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($wpo_cache_excluded_posts as $post): ?>
		<tr>
			<td><a href="<?php echo esc_url(admin_url('/post.php?action=edit&post='.$post['ID'])); ?>" target="_blank"><?php echo esc_html($post['post_title']); ?></a></td>
			<?php
			$post_type_obj = get_post_type_object($post['post_type']);
			?>
			<td><?php echo esc_html($post_type_obj->labels->singular_name); ?></td>
			<td><a href="javascript:;" class="wpo-exclude-from-cache wpo-delete" data-id="<?php echo esc_attr($post['ID']); ?>"><?php esc_html_e('Delete', 'wp-optimize'); ?></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php }
