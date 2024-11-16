<div class="ihc-top-message-new-extension">
	<?php echo esc_html_e('Extend your ', 'ihc');?> <strong>Ultimate Membership Pro</strong><?php esc_html_e(' system with extra features and functionality. Check additional available ', 'ihc');?> <strong>Extensions</strong> <a href="https://ultimatemembershippro.com/pro-addons/" target="_blank"><?php esc_html_e('here', 'ihc');?></a>
</div>
<?php
$data['feature_types'] = ihcGetListOfMagicFeatures();
foreach ($data['feature_types'] as $k=>$v):?>
	<div class="ihc-magic-box-wrap <?php echo ($v['enabled']) ? '' : 'ihc-disabled-box';?>">
		<a href="<?php echo esc_url($v['link']);?>" <?php if($k == 'new_extension'):
			echo esc_ump_content(' target="_blank" ');
		endif;
		?>
		>
			<div class="ihc-magic-feature <?php echo esc_attr($k);?> <?php echo esc_attr($v['extra_class']);?>">
				<?php if (isset($v['pro']) && $v['pro'] === TRUE){ ?>
						<div class="ihc-adm-ribbon ihc-adm-ribbon-top-left"><span>PRO</span></div>
				<?php } ?>
				<div class="ihc-magic-box-icon"><i class="fa-ihc <?php echo esc_attr($v['icon']);?>"></i></div>
				<div class="ihc-magic-box-title"><?php echo esc_html($v['label']);?></div>
				<div class="ihc-magic-box-desc"><?php echo esc_html($v['description']);?></div>
			</div>
		</a>
	</div>
<?php endforeach;?>
