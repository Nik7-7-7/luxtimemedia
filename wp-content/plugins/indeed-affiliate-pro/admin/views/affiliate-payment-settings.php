<div class="uap-wrapper">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Affiliate Payment Settings');?></h3>
		<div class="inside">
			<?php
			if (!empty($data['metas']['uap_affiliate_payment_type'])):
				$types = array('stripe'=>'Stripe', 'paypal'=>'PayPal', 'bt'=>'Direct Deposit', 'stripe_v2' => 'Stripe', 'stripe_v3' => 'Stripe');
				echo esc_uap_content("<div><label>" . esc_html__('Payment Type:', 'uap') . "</label> " . $types[$data['metas']['uap_affiliate_payment_type']] . "</div>");
				switch ($data['metas']['uap_affiliate_payment_type']){
					case 'stripe':
						?>
						<div><label><?php echo esc_html__("Name on Card:", 'uap');?></label> <?php echo esc_html($data['metas']['uap_affiliate_stripe_name']);?></div>
						<div><label><?php echo esc_html__("Card Number:", 'uap');?></label> <?php  echo esc_html($data['metas']['uap_affiliate_stripe_card_number']);?></div>
						<!-- div><label><?php echo esc_html__("CVC:", 'uap');?></label> <?php echo esc_html($data['metas']['uap_affiliate_stripe_cvc']);?></div -->
						<div><label><?php echo esc_html__("Expiration:", 'uap');?></label> <?php echo esc_html($data['metas']['uap_affiliate_stripe_expiration_month']) . '/'. esc_html($data['metas']['uap_affiliate_stripe_expiration_year']);?></div>
						<div><label><?php echo esc_html__("Type:", 'uap');?></label> <?php echo esc_html($data['metas']['uap_affiliate_stripe_card_type']);?></div>
						<?php
						break;
					case 'bt':
						?>
						<div><label><?php echo esc_html__("Bank Transfer Details:", 'uap');?></label> <?php echo esc_html($data['metas']['uap_affiliate_bank_transfer_data']);?></div>
						<?php
						break;
					case 'paypal':
						?>
						<div><label><?php echo esc_html__("PayPal E-mail Address:", 'uap');?></label> <?php echo esc_html($data['metas']['uap_affiliate_paypal_email']);?></div>
						<?php
						break;
					case 'stripe_v2':
						$uids = 0;
						if(isset($_GET['uid'])){
							$uids = sanitize_text_field($_GET['uid']);
						}
						$stripe_v2_data = $indeed_db->get_affiliate_stripe_v2_payment_settings($uids);
						$possible = array(
											'first_name' => esc_html__('First Name', 'uap'),
											'last_name' => esc_html__('Last Name', 'uap'),
											'first_name' => esc_html__('First Name', 'uap'),
											'day' => esc_html__('Birth day', 'uap'),
											'month' => esc_html__('Month', 'uap'),
											'year' => esc_html__('Year', 'uap'),
											'country' => esc_html__('Country', 'uap'),
											'state' => esc_html__('State', 'uap'),
											'city' => esc_html__('City', 'uap'),
											'line1' => esc_html__('Line1', 'uap'),
											'postal_code' => esc_html__('Postal Code', 'uap'),
											'user_type' => esc_html__('User Type', 'uap'),
											'routing_number' => esc_html__('Routing Number', 'uap'),
											'account_number' => esc_html__('Account Number', 'uap'),
											'ssn_last_4' => esc_html__('SSN last 4', 'uap'),
											'personal_id_number' => esc_html__('Personal id number', 'uap'),
											'business_name' => esc_html__('Business name', 'uap'),
											'business_tax_id' => esc_html__('Business tax id', 'uap'),
											'personal_address.city' => esc_html__('Personal Address City', 'uap'),
											'personal_address.line1' => esc_html__('Personal Address Line1', 'uap'),
											'personal_address.postal_code' => esc_html__('Personal Address Postal Code', 'uap'),
						);
						?>

						<?php foreach ($possible as $key=>$label):?>
							<?php if (isset($stripe_v2_data[$key])):?>
							<div><label><?php echo esc_html($label);?>:</label> <?php echo esc_html($stripe_v2_data[$key]);?></div>
							<?php endif;?>
						<?php endforeach;?>

						<?php
						break;
					case 'stripe_v3':
						$accountId = get_user_meta( $uid, 'uap_stripe_v3_user_account_id', true );
						if ( $accountId != false && $accountId != '' ):
							$stripe_link = '';
							$sandbox = get_option( 'uap_stripe_v3_sandbox' );
							if ( $sandbox ){
									$stripe_link = 'https://dashboard.stripe.com/test/connect/accounts/'.$accountId;
							}else{
									$stripe_link = 'https://dashboard.stripe.com/connect/accounts/'.$accountId;
							}
							?>
							<div class="uap-payment-details-do-payment">
									<a href="<?php echo esc_url($stripe_link);?>" target="_blank"><?php
									_e( 'View Stripe Affiliate Account', 'uap');
							?></a></div>
					<?php else :?>
						<div class="uap-payment-details-do-payment"><?php esc_html_e('Incomplete Payment Settings', 'uap');?></div>
						<?php endif;
						break;
				}
			endif;
			?>
		</div>
	</div>
</div>
