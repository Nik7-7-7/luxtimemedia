<div class="uap-settings-box-wrapper">
  <div class="uap-settings-tab-wrapper">
    <?php $current_url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];?>
    	<?php
    		foreach ($data['submenu'] as $url=>$name){
    			$selected = ($current_url==$url) ? 'uap-subtab-selected' : '';
    			?>
    			<a href="<?php echo esc_url($url);?>" class="uap-subtab-menu-item <?php echo esc_attr($selected);?>"><?php echo esc_html($name);?></a>
    			<?php
    		}
    	?>
    	<div class="clear"></div>
  </div>
  <div class="uap-settings-container">
