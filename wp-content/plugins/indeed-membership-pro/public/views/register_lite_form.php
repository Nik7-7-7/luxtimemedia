<div class="iump-register-form  <?php echo (isset($data['template'])) ? $data['template'] : '';?>">
	<?php
			do_action('ihc_print_content_before_register_lite_form');
			// @description Insert content before register lite form. @param none
	?>
	<?php if($data['css'] !== ''){
		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', stripslashes($data['css']) );
	} ?>
	<form method="post" name="createuser" id="createuser" class="ihc-form-create-edit" enctype="multipart/form-data" >

		<?php if ($data['template']=='ihc-register-6'):?>
			<div class="ihc-register-col">
		<?php endif;?>

		<?php if ($data['email_fields']):?>
			<?php echo esc_html($data['email_fields']);?>
		<?php endif;?>

		<div class="impu-temp7-row">
			<div class="iump-submit-form">
				<?php echo esc_html($data['submit_button']);?>
			</div>
		</div>

		<?php foreach ($data['hidden_fields'] as $hidden_field):?>
			<?php echo esc_html($hidden_field);?>
		<?php endforeach;?>

		<?php if ($data['template']==''):?>
			</div>
		<?php endif;?>

	</form>
	<?php
			do_action('ihc_print_content_after_register_lite_form');
			// @description Insert content after register lite form. @param none
	?>
</div>

<?php if (!empty($data['js'])): ?>
	<?php
			wp_add_inline_script( 'ihc-public-dynamic', $data['js'] );
			wp_enqueue_script( 'ihc-public-dynamic' );
	?>
<?php endif;?>
