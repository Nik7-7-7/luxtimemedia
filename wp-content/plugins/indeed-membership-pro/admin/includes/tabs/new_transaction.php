<?php
	$order_id = (empty($_GET['order_id'])) ? 0 : sanitize_text_field($_GET['order_id']);
	$data = Ihc_Db::get_order_data_by_id($order_id);
	if ($data):
		$subtab = admin_url('admin.php?page=ihc_manage&tab=orders');
?>
<form action="<?php echo esc_url($subtab);?>" method="post">
	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Add New Transaction', 'ihc');?></h3>
		<div class="inside">

			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon ihc-special-input-label" id="basic-addon1"><?php esc_html_e('Customer Username:', 'ihc');?></span>
					<input type="text" class="form-control" name="" disabled="disabled" value="<?php echo esc_attr($data['user']);?>" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon ihc-special-input-label" id="basic-addon1"><?php esc_html_e('Transaction Item:', 'ihc');?></span>
					<input type="text" class="form-control" name="" disabled="disabled" value="<?php echo esc_attr($data['level']);?>" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon ihc-special-input-label" id="basic-addon1"><?php esc_html_e('Total Amount:', 'ihc');?></span>
					<input type="text" class="form-control" name="" disabled="disabled" value="<?php echo esc_attr($data['amount_value']);?>" />
					<div class="input-group-addon"><?php echo esc_html($data['amount_type']);?></div>
					</div>
				</div>
			</div>
			<?php if (empty($data['metas']['ihc_payment_type']) || 1==1):?>
			<div class="row">
				<div class="col-xs-5">
				<h4><?php echo esc_html__('Payment Method', 'ihc');?></h4>
				<select name="ihc_payment_type" class="form-control m-bot15">
					<?php
						$payments = ihc_get_active_payment_services();
						if ($payments):
							foreach ($payments as $k=>$v):
								$selected = ($k=='bank_transfer') ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							endforeach;
						endif;
					?>
				</select>
				</div>
			</div>
			<?php else:?>

			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
					<span class="input-group-addon ihc-special-input-label" id="basic-addon1"><?php esc_html_e('Payment Service:', 'ihc');?></span>
					<input type="text" class="form-control" name="" disabled="disabled" value="<?php echo esc_attr($data['metas']['ihc_payment_type']);?>" />
					</div>
				</div>
			</div>
			<input type="hidden" value="<?php echo esc_attr($data['metas']['ihc_payment_type']);?>" name="ihc_payment_type" />
			<?php endif;?>
			<div class="row">
				<div class="col-xs-5">
					<h2><?php echo esc_html__('Details: ', 'ihc');?></h2>
					<p><?php echo esc_html__('Provide some particular details about this Transaction manually set ', 'ihc');?></p>
					<textarea name="details" ></textarea>
				</div>
			</div>


			<?php if (!empty($data['metas']['txn_id'])) :?>
				<input type="hidden" value="<?php echo esc_attr($data['metas']['txn_id']);?>" name="txn_id" />
			<?php endif;?>
			<input type="hidden" value="<?php echo esc_attr($data['uid']);?>" name="uid" />
			<input type="hidden" value="<?php echo esc_attr($data['lid']);?>" name="level" />
			<input type="hidden" value="<?php echo esc_attr($order_id);?>" name="order_id" />
			<input type="hidden" value="<?php echo esc_attr($data['amount_value']);?>" name="amount" />
			<input type="hidden" value="<?php echo esc_attr($data['amount_type']);?>" name="currency" />

			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Add Transaction', 'ihc');?>" name="submit_new_payment" class="button button-primary button-large" />
			</div>
		</div>

	</div>
</form>
<?php else:?>
	<div><?php esc_html_e('No details available!', 'ihc');?></div>
<?php endif;?>
