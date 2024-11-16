<?php if (!empty($data['uap_login_custom_css'])):
					wp_register_style( 'dummy-handle', false );
					wp_enqueue_style( 'dummy-handle' );
					wp_add_inline_style( 'dummy-handle', stripslashes($data['uap_login_custom_css']) );
	?>
<?php endif;?>
<div class="uap-logout-wrap <?php echo (isset($data['metas']['uap_login_template'])) ? $data['metas']['uap_login_template'] : '';?>">
	<a href="<?php echo esc_url($data['logout_link']);?>"><?php echo esc_html($data['logout_label']);?></a>
</div>
