<div class="uap-wrapper">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('View Referral Details', 'uap');?></h3>
		<div class="inside">
			<?php if (!empty($data['metas'])):?>
				<div>
					<label><?php echo esc_html__('Visit Id:', 'uap');?></label>
					<?php echo esc_html($data['metas']['visit_id']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Referral User WP Id:', 'uap');?></label>
					<?php echo esc_html($data['metas']['refferal_wp_uid']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Campaign:', 'uap');?></label>
					<?php echo esc_html($data['metas']['campaign']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Affiliate:', 'uap');?></label>
					<?php echo esc_html($data['metas']['affiliate_id']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Description:', 'uap');?></label>
					<?php echo esc_uap_content($data['metas']['description']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Source:', 'uap');?></label>
					<?php echo uap_service_type_code_to_title($data['metas']['source']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Reference:', 'uap');?></label>
					<?php
						$link = '';
						if (!empty($data['metas']['reference'])){
							switch ($data['metas']['source']){
								case 'woo':
									if (!empty($data['woo_order_base_link'])){
										$link = $data['woo_order_base_link'] . $data['metas']['reference'] . '&action=edit';
									}
									break;
								case 'edd':
									if (!empty($data['edd_order_base_link'])){
										$link = $data['edd_order_base_link'] . $data['metas']['reference'];
									}
									break;
								case 'ump':
									if (function_exists('ihc_get_payment_id_by_order_id')){
										$payment_id = ihc_get_payment_id_by_order_id($data['metas']['reference']);
										if ($payment_id){
											if (!empty($data['ump_order_base_link'])){
												$link = $data['ump_order_base_link'] . $payment_id;
											}
										}
									}
									break;
								case 'mlm':
									$the_ref = $array['reference'];
									$the_ref = str_replace('mlm_', '', $the_ref);
									$link = $data['mlm_order_base_link'] . $the_ref;
									break;
							}
						}
						if (!empty($link)){
							echo esc_uap_content('<a href="' . esc_url($link) . '" target="_blank">' . $data['metas']['reference'] . '</a>');
						} else {
							echo esc_uap_content($data['metas']['reference']);
						}
				?>
				</div>

				<div>
					<label><?php echo esc_html__('Reference Details:', 'uap');?></label>
					<?php echo esc_uap_content($data['metas']['reference_details']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Referral Parent:', 'uap');?></label>
					<?php echo esc_uap_content($data['metas']['parent_referral_id']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Referral Child:', 'uap');?></label>
					<?php echo esc_uap_content($data['metas']['child_referral_id']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Amount:', 'uap');?></label>
					<?php echo esc_uap_content($data['metas']['amount'] . ' ' . $data['currency']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Date:', 'uap');?></label>
					<?php echo esc_uap_content($data['metas']['date']);?>
				</div>

				<div>
					<label><?php echo esc_html__('Status:', 'uap');?></label>
					<?php
						switch ($data['metas']['status']){
							case 0:
								_e('Rejected', 'uap');
								break;
							case 1:
								_e('Unverfied', 'uap');
								break;
							case 2:
								_e('Verified', 'uap');
								break;
						}
					?>
				</div>

				<div>
					<label><?php echo esc_html__('Payment Status:', 'uap');?></label>
					<?php
						switch ($data['metas']['payment']){
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
					?>
				</div>


			<?php else: ?>
				<?php esc_html_e("No details available!", 'uap');?>
			<?php endif;?>
		</div>
	</div>
</div>
