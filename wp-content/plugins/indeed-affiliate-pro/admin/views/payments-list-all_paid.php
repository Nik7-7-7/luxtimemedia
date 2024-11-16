<div class="uap-wrapper">
	<div class="uap-page-title"><?php esc_html_e('Paid Referrals', 'uap');?></div>

		<?php if (!empty($data['subtitle'])):?>
			<h4><?php echo esc_uap_content($data['subtitle']);?></h4>
		<?php endif;?>

	<?php if (!empty($data['listing_items'])) : ?>

		<?php
			if (!empty($data['filter'])):
				echo esc_uap_content('<div class="uap-special-box">'.$data['filter'].'</div>');
			endif;
		?>

		<table class="wp-list-table widefat fixed tags uap-admin-tables">
			<thead>
				<tr>
					<th><?php esc_html_e('Affiliate', 'uap');?></th>
					<th><?php esc_html_e('Reference', 'uap');?></th>
					<th><?php esc_html_e('Amount', 'uap');?></th>
					<th><?php esc_html_e('Created Time', 'uap');?></th>
					<th><?php esc_html_e('Paid', 'uap');?></th>
				</tr>
			</thead>

			<tbody class="ui-sortable uap-alternate">
				<?php foreach ($data['listing_items'] as $key => $array): ?>
				<tr>
					<td><div class="uap-list-affiliates-name-label"><?php
					if (empty($u_ids[$array['affiliate_id']])){
						$u_ids[$array['affiliate_id']] = $indeed_db->get_uid_by_affiliate_id($array['affiliate_id']);
					}
					echo esc_uap_content($this->print_flag_for_affiliate($u_ids[$array['affiliate_id']]) . $array['username']);
					?></div></td>
					<td><?php echo esc_html($array['reference']);?></td>
					<td><?php echo uap_format_price_and_currency($array['currency'], $array['amount']);?></td>
					<td><?php echo uap_convert_date_to_us_format($array['date']);?></td>
					<td><?php
						switch ($array['payment']){
							case 0:
								_e('Unpaid', 'uap');
								break;
							case 1:
								_e('Pending', 'uap');
								break;
							case 2:
								_e('Complete', 'uap');
								break;
						}
					?></td>
				</tr>
				<?php endforeach;?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e('Affiliate', 'uap');?></th>
					<th><?php esc_html_e('Reference', 'uap');?></th>
					<th><?php esc_html_e('Amount', 'uap');?></th>
					<th><?php esc_html_e('Created Time', 'uap');?></th>
					<th><?php esc_html_e('Paid', 'uap');?></th>
				</tr>
			</tfoot>
		</table>

	<?php else: ?>
		<!-- developer -->
		<table class="wp-list-table widefat fixed tags uap-admin-tables">
			<thead>
				<tr>
					<th><?php esc_html_e('Affiliate', 'uap');?></th>
					<th><?php esc_html_e('Reference', 'uap');?></th>
					<th><?php esc_html_e('Amount', 'uap');?></th>
					<th><?php esc_html_e('Created Time', 'uap');?></th>
					<th><?php esc_html_e('Paid', 'uap');?></th>
				</tr>
			</thead>

			<tbody class="ui-sortable uap-alternate">
				<tr>
					<td><?php esc_html_e('No items found.', 'uap');?> </td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e('Affiliate', 'uap');?></th>
					<th><?php esc_html_e('Reference', 'uap');?></th>
					<th><?php esc_html_e('Amount', 'uap');?></th>
					<th><?php esc_html_e('Created Time', 'uap');?></th>
					<th><?php esc_html_e('Paid', 'uap');?></th>
				</tr>
			</tfoot>
		</table>
		<!-- end dev -->
	<?php endif;?>

	<?php if (!empty($data['payments_settings']['type'])):?>
		<h3><?php esc_html_e('Payment Details', 'uap')?></h3>
		<?php if (!empty($data['payment_details_on_transaction'])):?>
			<?php if (!empty($data['payment_details_on_transaction']['payment_type'])):?>
				<div>
					<label><?php esc_html_e('Payment Type', 'uap');?>: </label><?php echo esc_html($data['payment_details_on_transaction']['payment_type']);?>
					<?php unset($data['payment_details_on_transaction']['payment_type']);?>
				</div>
			<?php endif;?>

			<?php if (!empty($data['payment_details_on_transaction']['errors'])):?>
				<div>
					<label><?php esc_html_e( 'Errors:', 'uap' );?> </label> <?php echo esc_html($data['payment_details_on_transaction']['errors']);?>
					<?php unset($data['payment_details_on_transaction']['errors']);?>
				</div>
			<?php endif;?>

			<?php foreach ($data['payment_details_on_transaction'] as $temp_array):?>
				<div>
					<label><?php echo esc_html($temp_array['label']);?>: </label> <?php echo esc_html($temp_array['value']);?>
				</div>
			<?php endforeach;?>
		<?php endif;?>
		<?php endif;?>

	<?php if (!empty($data['pagination'])) : ?>
		<?php echo esc_uap_content($data['pagination']);?>
	<?php endif;?>
</div>
