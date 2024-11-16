<div class="ihc-ap-wrap">
	<?php if (!empty($data['title'])):?>
		<h3><?php echo do_shortcode($data['title']);?></h3>
	<?php endif;?>
	<?php if (!empty($data['content'])):?>
		<p><?php echo do_shortcode($data['content']);?></p>
	<?php endif;?>
	<!--div class="iump-account-content-title"><?php esc_html_e('Transactions History', 'ihc');?></div-->
<?php
	if (!empty($data['items'])){
		?>
				<table class="wp-list-table ihc-account-tranz-list">
						<thead>
							<tr>
								<th class="ihc-content-left">
									<span>
										<?php esc_html_e('Level', 'ihc');?>
									</span>
								</th>
								<th>
									<span>
										<?php esc_html_e('Amount', 'ihc');?>
									</span>
								</th>
								<th>
									<span>
										<?php esc_html_e('Payment Type', 'ihc');?>
									</span>
								</th>
								<th>
									<span>
										<?php esc_html_e('Status', 'ihc');?>
									</span>
								</th>
								<th class="manage-column ihc-content-right">
									<span>
										<?php esc_html_e('Date', 'ihc');?>
									</span>
								</th>
							</tr>
						</thead>
				<?php
				foreach ($data['items'] as $k=>$v){
					if ( isset( $v->payment_data ) ){
							$data_payment = json_decode($v->payment_data);
					} else {
							$data_payment = [];
					}
					$lid = isset( $data_payment->lid ) ? $data_payment->lid : -1;
					?>
					<tr>
						<td class="manage-column ihc-content-left"  data-title="<?php esc_attr_e('Level', 'ihc');?>">
							<div class="level-type-list">
				 			<?php
				 				if (isset($data_payment->level)){
									//2checkout
									$level_data_arr = ihc_get_level_by_id($data_payment->level);
									echo esc_html($level_data_arr['label']);
								} else if (isset($data_payment->item_name)){
									echo esc_html($data_payment->item_name);
								} elseif (isset($data_payment->x_description)){
									echo esc_html($data_payment->x_description);
								} else {
									$levelName = \Ihc_Db::get_level_name_by_lid( $lid );
									if ( $levelName != '' ){
											echo esc_html($levelName);
									} else {
											echo esc_html('--');
									}
								}
							?>
							</div>
						</td>
						<td class="manage-column" data-title="<?php esc_html_e('Amount', 'ihc');?>">
							<span class="level-payment-list">
							<?php
								$payment_value = ihc_return_transaction_amount_for_user_level($v->history, $v->payment_data);
								if (empty($payment_value)){
									echo esc_html('--');
								} else {
									$currency = '';
									if (!empty($data_payment->currency_code)){
										$currency = $data_payment->currency_code;
									} else if (!empty($data_payment->currency)){
										$currency = $data_payment->currency;
									} else if (!empty($data_payment->mc_currency)){
										$currency = $data_payment->mc_currency;
									}
									echo esc_html(ihc_format_price_and_currency($currency, $payment_value));
								}
							?>
							</span>
						</td>
						<?php
							if (isset($data_payment->ihc_payment_type)){
								$payment_type = $data_payment->ihc_payment_type;
							} else {
								$payment_type = get_option('ihc_payment_selected');
							}
							if ( isset( $data['payment_types'][ $payment_type ] ) ){
									$payment_type = $data['payment_types'][ $payment_type ];
							}
						?>
						<td class="ihc-content-capitalize"  data-title="<?php esc_attr_e('Payment Type', 'ihc');?>"><?php echo esc_html($payment_type);?></td>
						<td class="manage-column ihc-content-oswald" data-title="<?php esc_attr_e('Status', 'ihc');?>">
						 	<?php
								if (!empty($data_payment->payment_status)){
									echo esc_html($data_payment->payment_status);
								} else if (isset($data_payment->x_response_code) && ($data_payment->x_response_code == 1)){
									echo esc_html__("Confirmed", "ihc");
								} else if (isset($data_payment->code) && ($data_payment->code == 2)){
									echo esc_html__("Confirmed", "ihc");
								} else if(isset($data_payment->message) && $data_payment->message=='success'){
									echo esc_html__("Confirmed", "ihc");
								}  else {
									echo esc_html('--');
								}
							?>
						</td>
						<td class="manage-column ihc-content-right" data-title="<?php esc_attr_e('Date', 'ihc');?>">
							<span>
								<?php echo indeed_timestamp_to_date_without_timezone( strtotime($v->paydate), "F j, Y, g:i a" );?>
							</span>
						</td>
					</tr>
				<?php
				}///end of foreach
				?>
						<tfoot>
							<tr>
								<th class="ihc-content-left">
									<span><?php echo esc_html__('Level', 'ihc');?></span>
								</th>
								<th>
									<span><?php echo esc_html__('Amount', 'ihc');?></span>
								</th>
								<th>
									<span><?php echo esc_html__('Payment Type', 'ihc');?></span>
								</th>
								<th>
									<span><?php echo esc_html__('Status', 'ihc');?></span>
								</th>
								<th class="manage-column ihc-content-right">
									<span><?php echo esc_html__('Date', 'ihc');?></span>
								</th>
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
    <?php
		esc_html_e("No Transactions have been made yet", 'ihc');
	?>
    </div>
	<?php
	}
	?>
</div>
