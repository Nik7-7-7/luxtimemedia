<div class="ulp-subtab-menu">
  <?php foreach ($data['subtabs'] as $slug => $label):?>
	     <a class="ulp-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=settings&subtab=') . $slug;?>"><?php echo esc_html($label);?></a>
  <?php endforeach;?>
  <div class="ulp-clear"></div>
</div>
