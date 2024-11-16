
<div class="uap-all-integrations">
	<?php foreach ($data['integrations'] as $k=>$v):?>
		<?php if ( !isset( $v['label'] ) )continue;?>
			<?php $disabled = ''; if ( empty( $v['installed'] ) ): $disabled = 'uap-integration-box-disabled'; endif; ?>
		<div class="uap-integration-box <?php echo esc_attr($disabled); ?>">
				<?php if ( !empty( $disabled ) ): echo '<div class="uap-integration-not-found"></div>'; endif; ?>
			<div class="uap-integration-box-content">
				<h2><?php echo esc_uap_content($v['label']);?></h2>
				<div class="uap-integration-author"><?php echo esc_html__('By','uap').' '.'<a href="'.esc_url($v['author-link']).'" target="_blank">'.esc_uap_content($v['author']).'</a>';?></div>
				<p><?php echo esc_uap_content($v['description']);?></p>
			</div>
			<div class="uap-integration-box-actions">
				<div class="uap-integration-box-actions-content">
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($v['status']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch uap-js-do-switch-value" data-type="<?php echo esc_attr($k);?>" data-target="uap-integration-<?php echo esc_attr($k);?>" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap-integration-<?php echo esc_attr($k);?>" value="<?php echo esc_attr($v['status']);?>" id="uap-integration-<?php echo esc_attr($k);?>" />
					<span class="uap-integration-status uap-js-integration-status-for-<?php echo esc_attr( $k );?>" data-enabled="<?php echo esc_html__('Enabled', 'uap');?>" data-disabled="<?php echo esc_html__('Disabled', 'uap');?>" >
						<?php echo ($v['status'] == 1) ? esc_html__('Enabled','uap') : esc_html__('Disabled','uap');?>
					</span>
					<?php if ( !empty($v['extra-details-link'])): ?>
						<a href="<?php echo esc_url($v['extra-details-link']); ?>" target="_blank" class="uap-extra-button"><?php echo esc_html__('More Info','uap');?></a>
					<?php endif;?>
					<?php if ( !empty($v['extra-settings-link'])): ?>
						<a href="<?php echo esc_url($v['extra-settings-link']); ?>" target="_blank" class="uap-extra-button uap-extra-settings-button"><?php echo esc_html__('Settings','uap');?></a>
					<?php endif;?>
				</div>
			</div>
		</div>
	<?php endforeach;?>
</div>
