<table class="ihc-account-subscr-list">
	<thead>
		<tr>
			<th><?php esc_html_e('Gift Code', 'ihc');?></th>
			<th><?php esc_html_e('Discount Value', 'ihc');?></th>
			<th><?php esc_html_e('Discount for Membership', 'ihc');?></th>
			<th class="ihc-remove-onmobile"><?php esc_html_e('Gift Status', 'ihc');?></th>
		</tr>
	</thead>
	<?php foreach ($gifts as $gift):?>
		<tr>
			<td><?php echo esc_html($gift['code']);?></td>
			<td><?php
				if ($gift['discount_type']=='price'){
					echo esc_html(ihc_format_price_and_currency($currency, $gift['discount_value']));
				} else {
					echo esc_html($gift['discount_value'] . '%');
				}
			?></td>
			<td>
				<?php
					$l = $gift['target_level'];
					if (isset($levels[$l]) && isset($levels[$l]['label'])){
						echo esc_html($levels[$l]['label']);
					}
				?>
			</td>
			<td class="ihc-remove-onmobile"><?php
				if ($gift['is_active']):
					esc_html_e('Unused', 'ihc');
				else :
					esc_html_e('Used', 'ihc');
				endif;
			?></td>
		</tr>
	<?php endforeach;?>
</table>
