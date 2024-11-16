	<div class="uap-padding">
		<div><strong><?php esc_html_e('Set the Page as:', 'uap');?></strong></div>
		<select class="uap-fullwidth uap-select" name="uap_set_page_as_default_something">
			<option value="-1">...</option>
			<?php
				foreach ($data['types'] as $name=>$label):
					$selected = ($name==$data['current_page_type']) ? 'selected' : '';
				?>
					<option <?php echo esc_attr($selected);?> value="<?php echo esc_attr($name);?>"><?php echo esc_html($label) . ' ' . esc_html__('Page', 'uap');?></option>
				<?php
				endforeach;
			?>
		</select>
		<input type="hidden" name="uap_post_id" value="<?php echo esc_attr($post->ID);?>" />
	</div>

	<div class="uap-page-meta-box-field">
		<?php if (!empty($data['unset_pages'])): ?>
			<?php foreach ($data['unset_pages'] as $page_name): ?>
				<div class="uap-metabox-not-set"><?php echo esc_html__('Default ', 'uap') . $page_name . ' ' . esc_html__('Page', 'uap');?> <b> <?php esc_html__('Not Set!', 'uap');?></b></div>
			<?php endforeach;?>
		<?php endif;?>
	</div>
