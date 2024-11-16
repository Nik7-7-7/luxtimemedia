<div class="ulp-dashboard-wrap">
<div class="ulp-admin-header">
	<div class="ulp-top-menu-section">
		<div class="ulp-dashboard-logo">
			<a href="<?php echo esc_url($data['dashboard_url']);?>">
				<img src="<?php echo esc_url(ULP_URL) . 'assets/images/dashboard-logo.gif'; ?> "/>
				<div class="ulp-plugin-version"><?php echo esc_html(ULP_PLUGIN_VER);?></div>
			</a>
		</div>
		<div class="ulp-dashboard-menu">
			<ul>
				<?php foreach ($data['tabs'] as $k=>$v):?>
					<?php $selected = ($data['current_tab']==$k) ? 'selected' : '';?>
					<?php if (in_array($k, $data ['excluded_tabs'])):?>
						<li class="<?php echo esc_attr($selected);?>" >
								<div class="ulp-page-title link-<?php echo esc_attr($k); ?> ulp-inactive-tab">
									<i class="fa-ulp fa-ulp-menu fa-<?php echo esc_attr($k);?>-ulp"></i>
								<div><?php echo esc_html__($v, 'ulp');?></div>
								</div>
						</li>
					<?php else :?>
						<li class="<?php echo esc_attr($selected);?>"  >
							<a href="<?php echo esc_url($data['base_url']  . $k );?>" title="<?php echo esc_attr($v);?>">
								<div class="ulp-page-title link-<?php echo esc_attr($k); ?>">
									<i class="fa-ulp fa-ulp-menu fa-<?php echo esc_attr($k);?>-ulp"></i>
									<div><?php echo esc_html__($v, 'ulp');?></div>
								</div>
							</a>
						</li>
					<?php endif;?>

				<?php endforeach;?>
			</ul>
		</div>
	</div>
</div>

<div class="ulp-right-menu">
	<?php
		foreach ($data['right_tabs'] as $k=>$v){
		?>
		<div class="ulp-right-menu-item">
			<a href="<?php echo esc_url($data['base_url']  . $k);?>" title="<?php echo esc_attr($v);?>">
				<div class="ulp-page-title-right-menu">
					<i class="fa-ulp fa-ulp-menu fa-<?php echo esc_attr($k);?>-ulp"></i>
					<div class="ulp-right-menu-title"><?php echo esc_html__($v, 'ulp');?></div>
				</div>
			</a>
		</div>
		<?php
		}
	?>
</div>
<div class="ulp-mascot-wrapper"></div>
<div class="ulp-clear"></div>
