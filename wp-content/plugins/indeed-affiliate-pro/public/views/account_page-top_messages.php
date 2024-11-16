<?php if (!empty($data['warning_messages'])):?>
	<div class="uap-warning-box">
		<?php foreach ($data['warning_messages'] as $message):?>
			<?php echo esc_uap_content($message);?>
		<?php endforeach;?>
	</div>
<?php endif;?>
