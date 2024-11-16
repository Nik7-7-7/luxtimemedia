<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Payout Settings', 'uap');?></h3>
		<div class="inside">

	<div class="uap-form-line">
		<div class="row">
				<div class="col-xs-12">
					<h4><?php esc_html_e('Default Payment System', 'uap');?></h4>
					<p><?php esc_html_e('When a new Affiliate SignUp this will be his payment system for Payout.', 'uap');?></p>
				</div>
				<div class="col-xs-8">
						<select name="uap_default_payment_system" class="form-control m-bot15"><?php
							if ($data['payment_types']){
								foreach ($data['payment_types'] as $k=>$v):
									$selected = ($k==$data['metas']['uap_default_payment_system']) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
									<?php
								endforeach;
							}
						?></select>
					</div>
			</div>
		</div>
		<div class="uap-form-line">
			<div class="row">
					<div class="col-xs-12">
						<h4><?php esc_html_e('Referral Grace Period Days', 'uap');?></h4>
						<p><?php esc_html_e('Choose the Grace Period number of days for Verified Referrals. This one is used during Payout process to minimize the chances of paying Affiliates for Referrals while you are still liable to issue a refund. Choose the value related to your store refund policy.', 'uap');?></p>
					</div>
					<div class="col-xs-8">
								<input type="number" min="0" name="uap_payments_grace_period" value="<?php echo stripslashes( $data['metas']['uap_payments_grace_period'] );?>" id="uap_payments_grace_period"  class="form-control"/>
						</div>
				</div>
			</div>
			<div class="uap-form-line">
				<div class="row">
						<div class="col-xs-12">
							<h4><?php esc_html_e('Minimum Payout Amount', 'uap');?></h4>
							<p><?php esc_html_e("Establish the minimum payout threshold. This figure is employed to avoid disbursing negligible sums to affiliates. Affiliates won't appear in Payments section until their unpaid referrals, meeting the selected criteria, reach or exceed this set amount.", 'uap');?></p>
						</div>
						<div class="col-xs-8">
							<div class="input-group">
									<input type="number" min="0" name="uap_payments_minimum_amount" value="<?php echo stripslashes( $data['metas']['uap_payments_minimum_amount'] );?>" id="uap_payments_minimum_amount"  class="form-control"/>
									<div class="input-group-addon"><?php echo $currency ; ?></div>
							</div>
							</div>
					</div>
				</div>
		<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-12">
						<h4><?php esc_html_e('Hide Payment Warnings', 'uap');?></h4>
						<p><?php esc_html_e("A notification will appear if an affiliate user hasn't finalized the payment settings for Payout", 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_hide_payments_warnings']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_hide_payments_warnings');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_hide_payments_warnings" value="<?php echo esc_attr($data['metas']['uap_hide_payments_warnings']);?>" id="uap_hide_payments_warnings" />
					</div>
				</div>
					<div class="row">
						<div class="col-xs-12">
							<h5><?php esc_html_e('Custom Warning Message when Payout Service is missing', 'uap');?></h5>
						</div>
						<div class="col-xs-8">
							<div class="form-group">
								<input type="text" name="uap_payments_warnings_message" value="<?php echo stripslashes( $data['metas']['uap_payments_warnings_message'] );?>" id="uap_payments_warnings_message"  class="form-control"/>
							</div>
						</div>
						</div>
					</div>

			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-12">
						<h4><?php esc_html_e('Disable Direct Deposit', 'uap');?></h4>
						<p><?php esc_html_e('Affiliates will not be able to choose Direct Deposit anymore as Payment option for Payout.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_disable_bt_payment_system']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_disable_bt_payment_system');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_disable_bt_payment_system" value="<?php echo esc_attr($data['metas']['uap_disable_bt_payment_system']);?>" id="uap_disable_bt_payment_system" />
						</div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>


		</div>
	</div>
</form>
</div>
