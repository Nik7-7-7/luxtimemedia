<?php
function uap_create_ranks_graphic($ranks_arr, $current){
	/*
	 * @param array
	 * @return string
	 */
	if (is_array($ranks_arr)){
		$new_arr = uap_reorder_ranks($ranks_arr);//reorder ranks by order attr
		$output = '';
		$padding = 7;
		foreach ($new_arr as $k=>$v){
			$class = 'uap-rank-item';
			if ($v->id==$current){
				$current_printed = TRUE;
				$class .= ' uap-current-rank';
			}
			$output .= '<div class="'.$class.'" style = "padding: ' . $padding . 'px 5px;">' . $v->label . '</div>';
			$padding += 7;
		}
		if (empty($current_printed)){
			$output .= '<div class="uap-rank-item uap-current-rank" style = "padding: ' . $padding . 'px 5px;">' . esc_html__('Current Rank', 'uap') . '</div>';
		}
		$output = '<div class="rank-graphic-representation">' . $output . '</div>';
		return $output;
	}
	return '';
}

function uap_return_errors(){
	/*
	 * @param none
	 * @return string
	 */
	$output = '';
	global $uap_error_register, $indeed_db;
	if (!empty($uap_error_register)){
		$output = '<div class="uap-error-message">'.esc_html__('It seems that there was an issue saving your data: ', 'uap');
		$fields = $indeed_db->register_get_custom_fields();
		$labels = array();
		if (!empty($fields)){
			foreach ($fields as $field){
				$labels[$field['name']] = $field['label'];
			}
		}

		foreach ($uap_error_register as $key=>$err){
			$output .= esc_html__('field ', 'uap') . $labels[$key] . ': ' . $err. "; ";
		}
		$output .= '</div>';
	}
	return $output;
}

function uap_return_payment_details_for_admin_table($payment_details=array()){
	/*
	 * @param array
	 * @return string
	 */
	 $output = '-';
	 if (!empty($payment_details['type'])){
	 	switch ($payment_details['type']){
			case 'bt':
				if (!empty($payment_details['settings'])){
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Bank Transfer Details: ', 'uap') . $payment_details['settings'] . '</div>';
				} else {
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				}
				break;
			case 'paypal':
				if (!empty($payment_details['settings'])){
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('PayPal E-mail Address: ', 'uap') . $payment_details['settings'] . '</div>';
				} else {
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				}
				break;
			case 'stripe':
				if (!empty($payment_details['settings']['uap_affiliate_stripe_name'])
					&& !empty($payment_details['settings']['uap_affiliate_stripe_card_number'])
					&& !empty($payment_details['settings']['uap_affiliate_stripe_expiration_month']) && !empty($payment_details['settings']['uap_affiliate_stripe_expiration_year'])){ //&& !empty($payment_details['settings']['uap_affiliate_stripe_cvc'])
					$output = '<div class="uap-payment-details-do-payment">';
					$output .= esc_html__("Name on Card: ", 'uap') . $payment_details['settings']['uap_affiliate_stripe_name'] . ', ';
					$output .= esc_html__("Card Number: ", 'uap') . $payment_details['settings']['uap_affiliate_stripe_card_number'] . ', ';

					$output .= esc_html__("Expiration: ", 'uap') . $payment_details['settings']['uap_affiliate_stripe_expiration_month'] . '/' . $payment_details['settings']['uap_affiliate_stripe_expiration_year'];
					$output .= '</div>';
				} else {
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				}
				break;
			case 'stripe_v2':
				$output = '';
				break;
			case 'stripe_v3':
				if ( empty( $payment_details['account_id'] ) ){
						$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				} else {
					$stripe_link = '';
					$sandbox = get_option( 'uap_stripe_v3_sandbox' );
					if ( $sandbox ){
							$stripe_link = 'https://dashboard.stripe.com/test/connect/accounts/'.$payment_details['account_id'];
					}else{
							$stripe_link = 'https://dashboard.stripe.com/connect/accounts/'.$payment_details['account_id'];
					}
						$output = '<div class="uap-payment-details-do-payment"><a href="' . $stripe_link . '" target="_blank">' . esc_html__( 'View Stripe Affiliate Account', 'uap') . '</a></div>';
				}
				break;
	 	}
	 }
	 return $output;
}

if ( !function_exists( 'uap_admin_get_payment_gateway_label_for_affiliate' ) ):
/**
 * @param int
 * @param int
 * @return string
 */
function uap_admin_get_payment_gateway_label_for_affiliate( $affiliateId=0, $uid=0 )
{
		global $indeed_db;
			if ( $affiliateId === 0 ){
					return '';
			}
			$data = $indeed_db->get_affiliate_payment_type( 0, $affiliateId );

			if ( empty( $data ) ){
					return '<span class="uap-admin-aff-payment-type">-</span>';
			}
			$base_view_payment_settings_url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=view_payment_settings&uid=');
			$output = '';
			switch ($data['type']){
				case 'paypal':
					$payment_class = ($data['is_active']) ? 'uap-payment-type-active-paypal' : '';
					$output = '<a href="'.esc_url($base_view_payment_settings_url . $uid).'" target="_blank">
											<span class="uap-admin-aff-payment-type ' . esc_attr($payment_class) . '">' . esc_html__('PayPal', 'uap') . '</span>
					</a>';
					break;
				case 'bt':
					$payment_class = ($data['is_active']) ? 'uap-payment-type-active-bt' : '';
					$output = '<a href="' . esc_url($base_view_payment_settings_url . $uid) . '" target="_blank">
						<span class="uap-admin-aff-payment-type ' . esc_attr($payment_class) . '">' . esc_html__('Direct Deposit', 'uap') . '</span>
					</a>';
					break;
				case 'stripe':
					$payment_class = ($data['is_active']) ? 'uap-payment-type-active-stripe' : '';
					$output = '<a href="' . esc_url($base_view_payment_settings_url . $uid) . '" target="_blank">
											<span class="uap-admin-aff-payment-type ' . esc_attr($payment_class) . '">' . esc_html__('Stripe', 'uap') . '</span>
										</a>';
					break;
				case 'stripe_v2':
					$payment_class = ($data['is_active']) ? 'uap-payment-type-active-stripe' : '';
					$output = '<a href="' . esc_url($base_view_payment_settings_url . $uid) . '" target="_blank">
						<span class="uap-admin-aff-payment-type ' . esc_attr($payment_class) . '">' . esc_html__('Stripe', 'uap') . '</span>
					</a>';
					break;
				case 'stripe_v3':
					$payment_class = ($data['is_active']) ? 'uap-payment-type-active-stripe' : '';
					$output = '<a href="'.esc_url($base_view_payment_settings_url . $uid).'" target="_blank">
											<span class="uap-admin-aff-payment-type ' . esc_attr($payment_class) . '">' . esc_html__('Stripe', 'uap') . '</span>
										</a>';
					break;
			}
			return $output;
}
endif;
