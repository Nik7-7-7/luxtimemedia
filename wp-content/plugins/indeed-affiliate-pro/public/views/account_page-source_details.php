<?php if (!empty($data['fields_data'])):?>
	<h4>
	<?php echo esc_html('Referral #' . $referral_id);?>
	<?php if (!empty($data['fields_data']['order_amount'])):?>
		<?php echo esc_uap_content( ', ' . esc_html__('Amount:', 'uap') . ' ' . $data['fields_data']['order_amount'] );?>
	<?php endif;?>
	</h4>
	<?php foreach ($data['all_fields'] as $key=>$label):?>
		<?php if (isset($data['fields_data'][$key]) && $data['fields_data'][$key]!=''):?>
			<div><b><?php echo esc_uap_content($label);?>: </b><?php echo esc_uap_content($data['fields_data'][$key]);?></div>
		<?php endif;?>
	<?php endforeach;?>

<?php endif;?>
