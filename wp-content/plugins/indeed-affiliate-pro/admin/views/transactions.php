<div class="uap-wrapper">
	<div class="uap-page-title"><?php esc_html_e('Manage Transactions', 'uap');?></div>

		<?php if (!empty($data['subtitle'])):?>
			<h4><?php echo esc_uap_content($data['subtitle']);?></h4>
		<?php endif;?>
	<div class="uap-special-box uap-transactions-export-box" >
		<div class="uap-general-date-filter-wrap">
			<h3 class="uap-transactions-export-title"><?php esc_html_e('Export Pending Transactions', 'uap');?></h3>
			<p><?php esc_html_e('Generate a CSV file to export pending transactions for manual Payout processing', 'uap');?></p>
			<div class="uap-transactions-filter-box">
			<!--label class="uap-label">Start:</label-->
			<input type="text" value="" class="uap-general-date-filter" placeholder="<?php esc_html_e('From - mm/dd/yyyy', 'uap');?>" id="csv_min_date" />
			<!--label class="uap-label">Until:</label-->-
			<input type="text" value="" class="uap-general-date-filter" placeholder="<?php esc_html_e('To - mm/dd/yyyy', 'uap');?>" id="csv_max_date" />
			</div>
			<div class="uap-transactions-filter-box">
				<label><?php esc_html_e('Payment System', 'uap');?></label>
				<select id="csv_payment_type">
					<option value=""><?php esc_html_e('All', 'uap');?></option>
					<option value="bank_transfer"><?php esc_html_e('Direct Deposit', 'uap');?></option>
					<option value="paypal"><?php esc_html_e('Paypal', 'uap');?></option>
					<option value="stripe_v3"><?php esc_html_e('Stripe', 'uap');?></option>
				</select>
			</div>
			<div class="uap-transactions-filter-box">
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<input type="checkbox" class="uap-switch" onclick="uapCheckAndH(this, '#csv_switch_status');" checked />
					<div class="switch uap-display-inline"></div>
				</label>
				<label><?php esc_html_e("Change the transaction status to Complete ", 'uap');?></label>
				<input type="hidden" id="csv_switch_status" value="1" />
			</div>
			<div class="uap-transactions-filter-box">
				<div class="uap-special-button uap-transactions-export-bt" onclick="uapGeneratePaymentsCsv();"><i class="fa-uap fa-export-csv"></i>Export CSV</div>
			</div>
			<div class="uap-clear"></div>
			<div class="uap-hidden-download-link uap-display-none"><a href="" target="_blank"><?php esc_html_e("Click on this if download doesn't start automatically in 20 seconds!", 'uap');?></a></div>
		</div>
	</div>

	<?php if ( !empty( $data['error_users'] ) || !empty( $data['error_details_for_users'] ) ):?>

		<div class="uap-wrapp-the-errors">
			<?php if ( !empty( $data['error_users'] ) ):?>
					<?php foreach ($data['error_users'] as $user ):?>
						<div><?php echo esc_html__('The Payment cannot be proceed for affiliate ', 'uap') . $user . esc_html__(' because of the payment settings.', 'uap');?></div>
					<?php endforeach;?>
			<?php endif;?>
			<?php if ( !empty( $data['error_details_for_users'] ) ):?>
					<?php foreach ($data['error_details_for_users'] as $details ):?>
						<div><?php echo esc_html__('The Payment cannot be proceed for affiliate ', 'uap') . $details['username'] . '. ' . $details['error_message'];?></div>
					<?php endforeach;?>
			<?php endif;?>
		</div>

	<?php endif;?>

	<?php if ( isset( $data['general_error_for_payment'] ) && $data['general_error_for_payment'] !== '' ):?>
			<div class="uap-wrapp-the-errors"><?php echo $data['general_error_for_payment'];?></div>
	<?php endif;?>

	<?php if (!empty($data['listing_items'])) : ?>
	<div class="uap-special-box">
	<?php echo esc_uap_content($data['filter']);?>
	</div>

	<div class="uap-transactions-check-payment">
		<button class="button button-primary button-large uap-js-location-reload" data-url="<?php echo esc_url($data['update_payments']);?>" ><?php esc_html_e("Check Payments Status", 'uap');?></button>
	</div>




	<form  method="post" id="form_payments">

			<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

					<table class="wp-list-table widefat fixed tags uap-admin-tables">
						<thead>
							<tr>
								<th><?php esc_html_e('Affiliate', 'uap');?></th>
								<th><?php esc_html_e('Amount', 'uap');?></th>
								<th><?php esc_html_e('Payment Type', 'uap');?></th>
								<th><?php esc_html_e('Created Date', 'uap');?></th>
								<th><?php esc_html_e('Updated Date', 'uap');?></th>
								<th><?php esc_html_e('Payment Service Status', 'uap');?></th>
								<th><?php esc_html_e('Status', 'uap');?></th>
								<th class="uap-transactions-actions-col"><?php esc_html_e('Action', 'uap');?></th>
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
								<td><strong><?php echo uap_format_price_and_currency($array['currency'], $array['amount']);?></strong></td>
								<td><span class="uap-admin-aff-payment-type uap-payment-type-active-<?php echo esc_attr($array['payment_type']);?>"><?php echo esc_html($array['payment_type']);?></span></td>
								<td><?php echo uap_convert_date_to_us_format($array['create_date']);?></td>
								<td><?php echo uap_convert_date_to_us_format($array['update_date']);?></td>
								<td><?php if ($array['payment_special_status']) {
									echo esc_html($array['payment_special_status']);
								} else {
									echo esc_html('-');
								}?></td>
								<td><?php
									switch ($array['status']){
										case 0:
											?>
												<div><strong><?php esc_html_e('Failed', 'uap');?></strong></div>
											<?php
											break;
										case 1:
											?>
												<div><strong><?php esc_html_e('Pending', 'uap');?></strong>
													<?php if ($array['payment_type']=='paypal'):?>
															<div class="uap-transactions-warning"><?php esc_html_e("Press the 'Check Payments Status' button to get the lattest status of transaction.", 'uap');?></div>
													<?php endif;?>
												</div>
											<?php
											break;
										case 2:
											?>
												<div><strong><?php esc_html_e('Complete', 'uap');?></strong></div>
											<?php
											break;
									}
								?></td>
								<td>
									<div class="referral-status-verified"><a  href="<?php echo esc_url($data['view_transaction_url'] . '&id=' . $array['id']);?>"><?php esc_html_e('View Details', 'uap');?></a></div>
									<div>

									<?php
										if ($array['status']==2){
											?>
											<span class="refferal-chang-status uap-js-transactions-change-status"
												data-id="<?php echo esc_attr($array['id']);?>"
												data-status="1"><?php esc_html_e('Mark as Pending', 'uap');?></span>
											<span>|</span>
											<?php
										} else if ($array['status']==1){
											?>
											<span class="refferal-chang-status uap-js-transactions-change-status"
												data-id="<?php echo esc_attr($array['id']);?>"
												data-status="2"
											 	><?php esc_html_e('Mark as Complete', 'uap');?></span><span>|</span> <?php
										}
									?>
									<span class="refferal-chang-status uap-js-transactions-delete-transaction"
											data-id="<?php echo esc_attr($array['id']);?>"><?php esc_html_e('Delete', 'uap');?></span>
									</div>
								</td>
							</tr>

							<?php endforeach;?>
						</tbody>
						<tfoot>
							<tr>
								<th><?php esc_html_e('Affiliate', 'uap');?></th>
								<th><?php esc_html_e('Amount', 'uap');?></th>
								<th><?php esc_html_e('Payment Type', 'uap');?></th>
								<th><?php esc_html_e('Created Date', 'uap');?></th>
								<th><?php esc_html_e('Updated Date', 'uap');?></th>
								<th><?php esc_html_e('Payment Service Status', 'uap');?></th>
								<th><?php esc_html_e('Status', 'uap');?></th>
								<th><?php esc_html_e('Action', 'uap');?></th>
							</tr>
						</tfoot>
					</table>
			<input type="hidden" name="transaction_id" id="transaction_id" value="" />
			<input type="hidden" name="new_status" id="new_status" value="" />
			<input type="hidden" name="delete_transaction" id="delete_transaction" value="" />
	</form>
<?php else : ?>
	<!-- developer -->
	<table class="wp-list-table widefat fixed tags uap-admin-tables">
		<thead>
			<tr>
				<th><?php esc_html_e('Affiliate', 'uap');?></th>
				<th><?php esc_html_e('Amount', 'uap');?></th>
				<th><?php esc_html_e('Payment Type', 'uap');?></th>
				<th><?php esc_html_e('Created Date', 'uap');?></th>
				<th><?php esc_html_e('Updated Date', 'uap');?></th>
				<th><?php esc_html_e('Payment Service Status', 'uap');?></th>
				<th><?php esc_html_e('Status', 'uap');?></th>
				<th class="uap-transactions-actions-col"><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</thead>

		<tbody class="ui-sortable uap-alternate">
			<tr>
				<td><?php esc_html_e('No items found.', 'uap');?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th><?php esc_html_e('Affiliate', 'uap');?></th>
				<th><?php esc_html_e('Amount', 'uap');?></th>
				<th><?php esc_html_e('Payment Type', 'uap');?></th>
				<th><?php esc_html_e('Created Date', 'uap');?></th>
				<th><?php esc_html_e('Updated Date', 'uap');?></th>
				<th><?php esc_html_e('Payment Service Status', 'uap');?></th>
				<th><?php esc_html_e('Status', 'uap');?></th>
				<th><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</tfoot>
	</table>
	<!-- end dev -->
	<?php endif;?>
	<?php if (!empty($data['pagination'])) : ?>
		<?php echo esc_uap_content($data['pagination']);?>
	<?php endif;?>
</div>
