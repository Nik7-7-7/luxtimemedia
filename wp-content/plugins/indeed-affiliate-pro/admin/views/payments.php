<div class="uap-wrapper">
	<div class="uap-page-title"><?php esc_html_e('Unpaid Referrals', 'uap');?></div>

	<div class="uap-payments-stats">
		<div class="row">
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-visits-uap"></i>
					<div class="stats">
						<h4><?php echo esc_html($data['stats']['affiliates']);?></h4>
						<?php esc_html_e('Total Affiliates', 'uap');?>
					</div>
				</div>
			</div>

			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-rank-uap"></i>
					<div class="stats">
						<h4><?php echo esc_html($data['stats']['referrals']);?></h4>
						<?php esc_html_e('Total Referrals', 'uap');?>
					</div>
				</div>
			</div>

			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
					<div class="stats">
						<h4><?php echo uap_format_price_and_currency($data['stats']['currency'], round($data['stats']['unpaid_payments_value'], 2));?></h4>
						<?php esc_html_e('Total Unpaid Referrals', 'uap');?>
					</div>
				</div>
			</div>

			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-payments-paided-uap"></i>
					<div class="stats">
						<h4><?php echo uap_format_price_and_currency($data['stats']['currency'], round($data['stats']['paid_payments_value'], 2));?></h4>
						<?php esc_html_e('Total Paid Referrals', 'uap');?>
					</div>
				</div>
			</div>

			<div class="col-xs-4">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-paid-referrals-uap"></i>
					<div class="stats">
						<h4><?php echo esc_uap_content($data['stats']['paid_referrals_count']);?></h4>
						<?php esc_html_e('Total number of Paid Referrals', 'uap');?>
					</div>
				</div>
			</div>

			<div class="col-xs-4">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-referrals-uap"></i>
					<div class="stats">
						<h4><?php echo esc_uap_content($data['stats']['unpaid_referrals_count']);?></h4>
						<?php esc_html_e('Total number of Unpaid Referrals', 'uap');?>
					</div>
				</div>
			</div>

			<div class="col-xs-4">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-payments-paid-uap"></i>
					<div class="stats">
						<h4><?php echo esc_uap_content($data['stats']['payments']);?></h4>
						<?php esc_html_e('Total number of Transactions', 'uap');?>
					</div>
				</div>
			</div>

	  </div>
	</div>

	<?php if (!empty($data['listing_items'])) : ?>
	<form method="post" id="form_payments">
					<table class="wp-list-table widefat fixed tags uap-admin-tables">
						<thead>
							<tr>
								<th><?php esc_html_e('Affiliate', 'uap');?></th>
								<th><?php esc_html_e('Paid Referrals', 'uap');?></th>
								<th><?php esc_html_e('Unpaid Referrals', 'uap');?></th>
								<th><?php esc_html_e('Paid Amount', 'uap');?></th>
								<th><?php esc_html_e('Unpaid Amount', 'uap');?></th>
								<th><?php esc_html_e('All Transactions', 'uap');?></th>
							</tr>
						</thead>
						<tbody class="ui-sortable uap-alternate">
							<?php foreach ($data['listing_items'] as $id => $array): ?>
							<tr>
								<td>
									<div class="uap-list-affiliates-name-label"><?php
										$uid = $indeed_db->get_uid_by_affiliate_id($id);
										echo esc_uap_content($this->print_flag_for_affiliate($uid) . $array['username']);
									?></div>
									<?php
										if (!empty($data['payments_settings']) && !empty($data['payments_settings'][$id])):
											echo esc_html(" - ");
											$inside_array = $data['payments_settings'][$id];
											switch ($inside_array['type']):
												case 'paypal':
													$payment_class = ($inside_array['is_active']) ? 'uap-payment-type-active-paypal' : '';
													?>
													<span class="uap-admin-aff-payment-type <?php echo esc_attr($payment_class);?>">PayPal</span>
													<?php
													break;
												case 'bt':
													$payment_class = ($inside_array['is_active']) ? 'uap-payment-type-active-bt' : '';
													?>
													<span class="uap-admin-aff-payment-type <?php echo esc_attr($payment_class);?>"><?php esc_html_e('Direct Deposit', 'uap');?></span>
													<?php
													break;
												case 'stripe':
													$payment_class = '';
													if ($inside_array['is_active'] && !empty($inside_array['settings']) && !empty($inside_array['settings']['uap_affiliate_stripe_name'])
														&& !empty($inside_array['settings']['uap_affiliate_stripe_card_number']) && !empty($inside_array['settings']['uap_affiliate_stripe_expiration_month'])
														&& !empty($inside_array['settings']['uap_affiliate_stripe_expiration_year']) ) //&& !empty($inside_array['settings']['uap_affiliate_stripe_cvc'])
													{
														$payment_class = 'uap-payment-type-active-stripe';
													}
													?>
													<span class="uap-admin-aff-payment-type <?php echo esc_attr($payment_class);?>">Stripe</span>
													<?php
													break;
											endswitch;
										else :
											?>
											<?php
										endif;
									?>
								</td>
								<td><?php
									echo esc_html($array['count_paid']);
									if ($array['count_paid']){
										?>
										<div><a href="<?php echo esc_url($data['paid_referrals'] . '&affiliate=' . $id);?>"><?php esc_html_e('View', 'uap');?></a></div>
										<?php
									}
								?></td>
								<td><?php
									echo esc_uap_content('<strong class="uap-special-color">'.$array['count_unpaid'].'</strong>');
									if (!empty($array['count_unpaid'])):
										?>
										<div><a href="<?php echo esc_url($data['unpaid_link'] . '&affiliate=' . $id);?>"><?php esc_html_e('View', 'uap');?></a></div>
										<?php
									endif;
								?></td>

								<td><?php echo uap_format_price_and_currency($array['paid_currency'], round($array['total_paid'], 2));?></td>

								<td><?php
									echo esc_uap_content('<strong class="uap-special-color">' . uap_format_price_and_currency($array['unpaid_currency'], round($array['total_unpaid'], 2) ) . '</strong>');
									if (!empty($array['total_unpaid']) && (float)$array['total_unpaid'] > 0 ){
										?>
										<div><a href="<?php echo esc_url($data['pay_link'] . '&affiliate=' . $id);?>"><?php esc_html_e('Pay All', 'uap');?></a></div>
										<?php
									}
								?></td>
								<td><?php
									if ($array['has_transactions']){
										?>
											<div class="referral-status-verified"><a href="<?php echo esc_url($data['paid_link'] . '&affiliate=' . $id);?>"><?php esc_html_e('View', 'uap');?></a></div>
										<?php
									}
								?></td>
							</tr>

							<?php endforeach;?>
						</tbody>

						<tfoot>
							<tr>
								<th><?php esc_html_e('Affiliate', 'uap');?></th>
								<th><?php esc_html_e('Paid Referrals', 'uap');?></th>
								<th><?php esc_html_e('Unpaid Referrals', 'uap');?></th>
								<th><?php esc_html_e('Paid Amount', 'uap');?></th>
								<th><?php esc_html_e('Unpaid Amount', 'uap');?></th>
								<th><?php esc_html_e('All Transactions', 'uap');?></th>
							</tr>
						</tfoot>
					</table>
	</form>
	<?php endif;?>
	<?php if (!empty($data['pagination'])) : ?>
		<?php echo esc_uap_content($data['pagination']);?>
	<?php endif;?>
</div>
