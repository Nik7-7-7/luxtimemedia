<?php wp_enqueue_script( 'ihc-print-this' );?>
<?php
	if (!empty($data['orders'])){
		$orderMeta = new \Indeed\Ihc\Db\OrderMeta();
		?>

				<table class="wp-list-table ihc-account-tranz-list">
						<thead>
							<tr>
								<th class="ihc-content-left">
									<span>
										<?php esc_html_e('Code', 'ihc');?>
									</span>
								</th>

								<?php	if( !empty($data['settings']['ihc_show_order_memberships_column'] )):?>
									<th class="ihc-content-left">
										<span>
											<?php esc_html_e('Memberships', 'ihc');?>
										</span>
									</th>
							<?php endif; ?>

								<?php if ( ihc_is_magic_feat_active( 'taxes' ) ):?>
									<?php	if( !empty( $data['settings']['ihc_show_taxes_column']) ): ?>
									<th>
											<span><?php esc_html_e('Net Amount', 'ihc');?></span>
									</th>
									<th>
											<span><?php esc_html_e('Taxes', 'ihc');?></span>
									</th>
								<?php endif;?>
							<?php endif; ?>

								<?php if( !empty($data['settings']['ihc_show_total_amount_column']) ) : ?>
									<th>
										<span><?php echo esc_html__('Total Amount', 'ihc');?></span>
									</th>
							<?php endif ?>

							<?php if( !empty($data['settings']['ihc_show_payment_method_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Payment Method', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

							<?php if( !empty($data['settings']['ihc_show_date_column']) ) : ?>
								<th class="manage-column ihc-content-right">
									<span>
										<?php esc_html_e('Date', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

							<?php if( !empty($data['settings']['ihc_show_coupon_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Coupon', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

							<?php if( !empty($data['settings']['ihc_show_transaction_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Transaction', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

								<?php if (!empty($data['show_invoices'])):?>
									<?php if( !empty($data['settings']['ihc_show_invoice_column']) ) : ?>
									<th>
										<span>
											<?php esc_html_e('Invoice', 'ihc');?>
										</span>
									</th>
								<?php endif;?>
							<?php endif;?>

						<?php	if( !empty($data['settings']['ihc_show_order_status_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Status', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>
							</tr>
						</thead>

				<?php
				foreach ($data['orders'] as $k=>$array){

					$firstChage = false;
					$firstAmount = false;

					$firstAmount = $orderMeta->get( $array['id'], 'first_amount' );
					if(isset($firstAmount) && $firstAmount == $array['amount_value']){
						$firstChage = true;
					}

					$taxes = $orderMeta->get( $array['id'], 'taxes_amount' );
						if ( $taxes == null ){
								$taxes = $orderMeta->get( $array['id'], 'tax_value' );
						}
						if ( $taxes == null ){
							if(isset($firstChage) && $firstChage == true){
								$taxes = $orderMeta->get( $array['id'], 'first_amount_taxes' );
							}else{
								$taxes = $orderMeta->get( $array['id'], 'taxes' );
							}
						}
					?>
					<tr>
						<td data-title="<?php esc_html_e('Code', 'ihc');?>"><?php
								if (!empty($array['metas']['code'])){
									echo esc_html($array['metas']['code']);
								} else {
									echo esc_html('-');
								}
						?></td>

						<?php if( !empty( $data['settings']['ihc_show_order_memberships_column']) ): ?>
							<td class="manage-column ihc-content-left"  data-title="<?php esc_html_e('Membership', 'ihc');?>"><span class="ihc-level-name"><?php echo esc_html($array['level']);?></span></td>
						<?php endif; ?>

						<?php if ( ihc_is_magic_feat_active( 'taxes' ) ):?>
								<?php	if( !empty( $data['settings']['ihc_show_taxes_column']) ): ?>

							<?php if(isset($firstChage) && $firstChage == true && isset($firstAmount)){ ?>
								<td data-title="<?php esc_html_e('Net Amount', 'ihc');?>">
									<?php $netAmount = $firstAmount;
									if ( $taxes != false ){
										$netAmount = $firstAmount - $taxes;
									}
									echo esc_html($netAmount) . ' ' . esc_html($array['amount_type']); ?>
						</td>
					<?php  }else{ ?>
						<td data-title="<?php esc_html_e('Amount', 'ihc');?>">
										<?php $value = $orderMeta->get( $array['id'], 'base_price' );?>
										<?php if ( $value !== null ):?>
												<?php echo esc_html($value) . ' ' . esc_html($array['amount_type']);?>
										<?php elseif ( $taxes != false ):?>
												<?php $netAmount = $array['amount_value'] - $taxes;?>
												<?php echo esc_html($netAmount) . ' ' . esc_html($array['amount_type']);?>
										<?php else :?>
												<?php echo esc_html($array['amount_value']) . ' ' . esc_html($array['amount_type']);?>
										<?php endif;?>
						<?php } ?>
						</td>
						<?php if ( $taxes !== null ):?>
						<td data-title="<?php esc_html_e('Taxes', 'ihc');?>">

									<?php echo esc_html($taxes) . ' ' . esc_html($array['amount_type']);?>

						</td>
						<?php endif;?>
					<?php endif; ?>
					<?php endif; ?>

					<?php if( !empty($data['settings']['ihc_show_total_amount_column']) ) : ?>

						<td class="manage-column ihc-data-highlighted" data-title="<?php esc_html_e('Total Amount', 'ihc');?>">
							<span class="level-payment-list"><?php echo ihc_format_price_and_currency($array['amount_type'], $array['amount_value']);?></span>
						</td>
					<?php endif; ?>

					<?php if( !empty($data['settings']['ihc_show_payment_method_column']) ) : ?>
						<td class="ihc-content-capitalize" data-title="<?php esc_html_e('Payment Type', 'ihc');?>"><?php
							if (empty($array['metas']['ihc_payment_type'])):
								echo esc_html('-');
							else:
								if (!empty($array['metas']['ihc_payment_type'])){
										$gateway_key = $array['metas']['ihc_payment_type'];

										if (isset($data['payment_types'][$gateway_key])){
		                    echo esc_html($data['payment_types'][$gateway_key]);
		                } else {
		                    echo esc_html($gateway_key);
		                }
								}
							endif;
						?></td>
					<?php endif; ?>

					<?php if( !empty($data['settings']['ihc_show_date_column']) ) : ?>
						<td class="manage-column ihc-content-right" data-title="<?php esc_html_e('Date', 'ihc');?>">
							<span>
								<?php echo ihc_convert_date_to_us_format($array['create_date']);//date("F j, Y, g:i a", strtotime($array['create_date']));?>
							</span>
						</td>
					<?php endif; ?>

					<?php if( !empty($data['settings']['ihc_show_coupon_column']) ) : ?>
						<td  data-title="<?php esc_html_e('Coupon', 'ihc');?>"><?php
								$coupon = $orderMeta->get( $array['id'], 'coupon_used' );
								if ($coupon) {
									echo esc_html($coupon);
								}
								else {
									echo esc_html('-');
								}
						?></td>
					<?php endif; ?>

					<?php if( !empty($data['settings']['ihc_show_transaction_column']) ) : ?>

						<td  data-title="<?php esc_html_e('Transaction', 'ihc');?>"><?php
								$transactionId = $orderMeta->get( $array['id'], 'transaction_id' );
								echo esc_html($transactionId);
						?></td>
					<?php endif; ?>

						<?php if (!empty($data['show_invoices'])):?>
							<?php if( !empty($data['settings']['ihc_show_invoice_column']) ) : ?>
								<?php if (!empty($data['show_only_completed_invoices']) && $array['status'] !== 'Completed' ):?>
											<td data-title="<?php esc_html_e('Membership', 'ihc');?>">-</td>
									<?php else:?>
											<td data-title="<?php esc_html_e('Invoice', 'ihc');?>">
												<i class="fa-ihc fa-invoice-preview-ihc iump-pointer" onClick="iumpGenerateInvoice(<?php echo esc_attr($array['id']);?>);"></i>
											</td>
							<?php endif;?>
						<?php endif;?>
						<?php endif;?>

		<?php	if( !empty($data['settings']['ihc_show_order_status_column']) ) : ?>

						<td class="manage-column ihc-data-highlighted" data-title="<?php esc_html_e('Status', 'ihc');?>">
						 	<?php
								switch ($array['status']){
									case 'Completed':
										esc_html_e('Completed', 'ihc');
										break;
									case 'pending':
										esc_html_e('Pending', 'ihc');
										break;
									case 'fail':
									case 'failed':
										esc_html_e('Failed', 'ihc');
										break;
									case 'error':
										esc_html_e('Error', 'ihc');
										break;
									default:
										echo esc_html($array['status']);
										break;
								}
						 	?>
						</td>
					<?php endif; ?>
					</tr>
				<?php
				}///end of foreach
				?>
						<tfoot>
							<tr>
								<th class="ihc-content-left">
									<span>
										<?php esc_html_e('Code', 'ihc');?>
									</span>
								</th>
								<?php	if( !empty( $data['settings']['ihc_show_order_memberships_column']) ): ?>
								<th class="ihc-content-left">
									<span>
										<?php esc_html_e('Memberships', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

					<?php if ( ihc_is_magic_feat_active( 'taxes' ) ):?>
						<?php	if( !empty( $data['settings']['ihc_show_taxes_column']) ): ?>

								<th>
										<span><?php esc_html_e('Net Amount', 'ihc');?></span>
								</th>
								<th>
										<span><?php esc_html_e('Taxes', 'ihc');?></span>
								</th>
								<?php endif;?>
							<?php endif; ?>

								<?php if( !empty($data['settings']['ihc_show_total_amount_column']) ) : ?>
								<th>
									<span><?php echo esc_html__('Total Amount', 'ihc');?></span>
								</th>
							<?php endif; ?>

							<?php if( !empty($data['settings']['ihc_show_payment_method_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Payment Method', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

							<?php if( !empty($data['settings']['ihc_show_date_column']) ) : ?>
								<th class="manage-column ihc-content-right">
									<span>
										<?php esc_html_e('Date', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

						<?php	if( !empty($data['settings']['ihc_show_coupon_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Coupon', 'ihc');?>
									</span>
								</th>
						<?php endif; ?>

						<?php if( !empty($data['settings']['ihc_show_transaction_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Transaction', 'ihc');?>
									</span>
								</th>
						<?php endif; ?>

								<?php if (!empty($data['show_invoices'])):?>
									<?php if( !empty($data['settings']['ihc_show_invoice_column']) ) : ?>
									<th>
										<span>
											<?php esc_html_e('Invoice', 'ihc');?>
										</span>
									</th>
									<?php endif;?>
								<?php endif;?>

								<?php if( !empty($data['settings']['ihc_show_order_status_column']) ) : ?>
								<th>
									<span>
										<?php esc_html_e('Status', 'ihc');?>
									</span>
								</th>
							<?php endif; ?>

							</tr>
						</tfoot>
			</table>

			<?php if (!empty($data['pagination'])):?>
				<?php echo esc_ump_content($data['pagination']);?>
			<?php endif;?>

	<?php
	} else {
	?>
    <div class="ihc-additional-message">
    <?php esc_html_e("No Orders have been made yet. Look for available ", 'ihc'); ?>
    <a href="<?php echo esc_url($data['subscription_link']);?>"><?php esc_html_e("Subscriptions", 'ihc'); ?></a>
    </div>
	<?php
	}

$invoiceCss = get_option( 'ihc_invoices_custom_css' );
if ( $invoiceCss !== false && $invoiceCss != '' ){
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes( $invoiceCss ) );
}
	?>
