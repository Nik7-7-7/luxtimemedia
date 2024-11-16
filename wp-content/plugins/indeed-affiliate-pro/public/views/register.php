<?php $data['template_with_cols'] = array('uap-register-6', 'uap-register-11', 'uap-register-12', 'uap-register-13');?>
<div class="uap-register-form  <?php echo esc_attr($data['template']);?>">

	<?php if (!empty($data['css'])):
						wp_register_style( 'dummy-handle', false );
						wp_enqueue_style( 'dummy-handle' );
						wp_add_inline_style( 'dummy-handle', stripslashes($data['css']) );
	 endif; ?>

	<form action="<?php echo (isset($data['action'])) ? $data['action'] : '';?>" method="post" name="<?php echo esc_attr($data['form_name']);?>" id="<?php echo esc_attr($data['form_id']);?>" class="uap-form-create-edit" enctype="multipart/form-data"  >

		<?php do_action('uap_register_form_before_form_fields');?>

		<?php if (!empty($data['form_fields'])):
				$i = 0;
				$stop = 0;
				if(isset($data['count_register_fields'])){
					$stop = ceil($data['count_register_fields']/2);
				}


		?>
			<?php foreach ($data['form_fields'] as $form_field): ?>
				<?php
					if (empty($form_field)){
						continue;
					}
					$i++;
					if (in_array($data['template'], $data['template_with_cols']) ):
						if ($i==1):?>
							<div class="uap-register-col">
						<?php endif;
						if ($i-1==$stop):	?>
							</div><div class="uap-register-col">
						<?php endif;?>
					<?php endif;?>
					<?php echo esc_uap_content($form_field);?>
			<?php endforeach;?>
		<?php endif;?>
		<?php if (in_array($data['template'], $data['template_with_cols']) ):?>
			</div>
		<?php endif;?>
		<?php if ($data['template']=='uap-register-7'):?>
			<div class="uap-temp7-row">
		<?php endif;?>
		<?php if (!empty($data['hiddens'])):?>
			<?php foreach ($data['hiddens'] as $value): ?>
				<?php echo esc_uap_content($value);?>
			<?php endforeach;?>
		<?php endif;?>

		<?php do_action('uap_register_form_before_submit_button');?>

		<?php if ($data['template']=='uap-register-14'):?>
        	<div class="uap-register-row-left">
        <?php endif;?>

		<div class="uap-submit-form">
			<?php echo esc_uap_content($data['submit_button']);?>
		</div>

        <?php if ($data['template']=='uap-register-14' && is_user_logged_in() == false):?>
        	</div>
            <div class="uap-register-row-right">
            <?php
				$pag_id = get_option('uap_general_login_default_page');
				if($pag_id!==FALSE){
					$login_page = get_permalink( $pag_id );
					if (!$login_page){
						 $login_page = get_home_url();
					}
					echo esc_uap_content('<div class="uap-login-link"><a href="'.$login_page.'">'.esc_html__('LogIn', 'uap').'</a></div>');
				}
			?>
            </div>
            <div class="uap-clear"></div>
        <?php endif;?>

		<?php if ($data['template']=='uap-register-7'):?>
		</div>
		<?php endif;?>

		<?php do_action('uap_register_form_after_form_fields');?>

	</form>
</div>

<?php if (!empty($data['js'])): ?>
	<?php
			wp_register_script( 'uap-public-dynamic', UAP_URL . 'assets/js/public.js', ['jquery'], '9.3' );
			wp_add_inline_script( 'uap-public-dynamic', $data['js'] );
			wp_enqueue_script( 'uap-public-dynamic' );
	?>
<?php endif;?>
